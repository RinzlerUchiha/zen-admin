<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\VerifiesSignedHireflowRequest;
use App\Services\Recruitment\InterviewerAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proves a request really came from Zen-Hub, on behalf of a real interviewer.
 *
 * Zen-Hub and this application have separate logins, so there is no session to
 * share. Instead Zen-Hub signs each request server-side with a secret the two
 * hold between them, and names the employee it is acting for.
 *
 * What the signature establishes is only WHO is asking. It does not establish
 * what they may see: that is worked out per request from the interview
 * assignments. A forged employee number would still reach nothing beyond that
 * employee's own assigned interviews.
 *
 * Four things are checked, in increasing cost:
 *
 *   1. a secret is configured at all     — a missing one must fail closed
 *   2. the timestamp is recent           — limits how long a captured request lives
 *   3. the nonce has not been seen       — so a captured one cannot be replayed
 *   4. the signature matches             — compared in constant time
 *
 * Then, and only then, that the employee has an interview to conduct at all.
 */
class VerifyHireflowRequest
{
    use VerifiesSignedHireflowRequest;

    /** How far out of step the two clocks may be before a request is refused. */
    public const WINDOW_SECONDS = 300;

    /** A nonce is remembered for longer than the window it could be used in. */
    public const NONCE_TTL = 600;

    /*
    | This root's nonce namespace.
    |
    | Per-root, so the approver bridge cannot consume this one's nonces or be
    | made to look as though it had.
    */
    private const NONCE_SCOPE = 'interviewer';

    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.hireflow.secret');

        /*
        | No secret, no access.
        |
        | Failing closed matters more here than anywhere else: a deployment that
        | forgets the secret would otherwise accept every request, signed or
        | not, and the failure would look like everything working.
        */
        if (!is_string($secret) || $secret === '') {
            return $this->deny('This interface is not configured.', 503);
        }

        ['empno' => $empno, 'timestamp' => $timestamp,
            'nonce' => $nonce, 'signature' => $signature] = $this->signedHeaders($request);

        if ($empno === '' || $timestamp === '' || $nonce === '' || $signature === '') {
            return $this->deny('This request is not signed.');
        }

        if (!$this->timestampIsNumeric($timestamp)) {
            return $this->deny('This request is not signed correctly.');
        }

        /* A request signed minutes ago is almost certainly a replay or a badly
           wrong clock; neither is worth serving. */
        if (!$this->timestampInWindow($timestamp)) {
            return $this->deny('This request has expired.');
        }

        /* Nonce length is checked before use so the cache key cannot be
           steered by an unusually long or odd value. */
        if (!$this->nonceLooksRight($nonce)) {
            return $this->deny('This request is not signed correctly.');
        }

        $expected = $this->sign($request, $empno, $timestamp, $nonce, $secret);

        /* Constant time: a comparison that returns early leaks, one character
           at a time, what the correct signature would have been. */
        if (!hash_equals($expected, $signature)) {
            return $this->deny('This request could not be verified.');
        }

        /*
        | The nonce is claimed only AFTER the signature checks out, so an
        | unauthenticated caller cannot burn through nonces and lock out
        | legitimate requests.
        |
        | add() writes only if the key is absent, which is what makes this a
        | check and a claim in one step rather than two.
        */
        if (!$this->claimNonce(self::NONCE_SCOPE, $nonce)) {
            return $this->deny('This request has already been used.');
        }

        /* Signed correctly, but is this employee an interviewer at all?
           Being assigned to an interview is what makes them one, so an employee
           whose last assignment was taken away is refused here, and one who was
           never assigned never gets in. */
        if (!InterviewerAccess::hasAssignments($empno)) {
            return $this->deny('This employee has no HireFlow interviews.', 403);
        }

        /* The one identity everything downstream trusts, taken from the signed
           header rather than from anything in the body or the route. */
        $request->attributes->set('hireflow_empno', $empno);

        return $next($request);
    }

    /**
     * The canonical string both sides sign.
     *
     * Method, path, employee and body are all covered, so a captured request
     * cannot be pointed at another endpoint, replayed as another employee, or
     * have its contents altered.
     */
    public static function canonical(string $method, string $path, string $timestamp, string $nonce, string $empno, string $body): string
    {
        return implode("\n", [
            strtoupper($method),
            self::relativePath($path),
            $timestamp,
            $nonce,
            $empno,
            hash('sha256', $body),
        ]);
    }

    /**
     * Reduce any form of the path to the part after the API root.
     *
     * The two applications sit at different base paths and may be served from
     * elsewhere in another environment. Signing the deployed path would make
     * every request fail the moment either one moved — and fail looking like an
     * attack rather than a configuration change. The part that identifies the
     * endpoint is the same wherever they are deployed.
     */
    public static function relativePath(string $path): string
    {
        $root = 'api/hireflow/interviewer';
        $path = '/' . trim($path, '/');
        $position = strpos($path, $root);

        if ($position !== false) {
            $path = substr($path, $position + strlen($root));
        }

        return '/' . ltrim($path, '/');
    }

    private function sign(Request $request, string $empno, string $timestamp, string $nonce, string $secret): string
    {
        return hash_hmac('sha256', self::canonical(
            $request->getMethod(),
            $request->getPathInfo(),
            $timestamp,
            $nonce,
            $empno,
            $request->getContent()
        ), $secret);
    }

}
