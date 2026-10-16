<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\VerifiesSignedHireflowRequest;
use App\Services\Recruitment\HrHeadAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proves a request really came from Zen-Hub, on behalf of a real HR Head.
 *
 * The sibling of VerifyHireflowRequest, and deliberately not the same class.
 * The mechanics are shared; the three things that decide anything are not.
 *
 * TWO ROOTS THAT MUST NOT ACCEPT EACH OTHER'S SIGNATURES
 *
 * The interviewer canonical string covers only the path RELATIVE to its own
 * API root, because the two applications sit at different base paths and
 * either may move. That is right, but it means a second root signing the same
 * way with the same secret would accept the first root's signatures: a request
 * signed for "/me" would verify under both.
 *
 * Two independent defences, either of which would be enough:
 *
 *   1. AUDIENCE. This root's canonical string begins with a literal
 *      'hireflow-approver' line, so it is a different SHAPE from the
 *      interviewer's. A signature made for one cannot verify under the other
 *      in either direction.
 *
 *   2. A SEPARATE SECRET. Compromising the interviewer secret does not reach
 *      offer approval, and vice versa.
 *
 * WHAT THE SIGNATURE ESTABLISHES
 *
 * Only WHO is asking. What they may do is decided here, from the role the
 * employee actually holds — and then again by the controller, which resolves
 * offers awaiting approval rather than trusting an id. Zen-Hub gates its own
 * page on the same role, but that check is on the far side of the bridge and
 * is never relied on.
 */
class VerifyHireflowApproverRequest
{
    use VerifiesSignedHireflowRequest;

    /** How far out of step the two clocks may be before a request is refused. */
    public const WINDOW_SECONDS = 300;

    /** A nonce is remembered for longer than the window it could be used in. */
    public const NONCE_TTL = 600;

    /** This root's nonce namespace, kept apart from the interviewer's. */
    private const NONCE_SCOPE = 'approver';

    /**
     * The audience line.
     *
     * The whole reason a signature for one root cannot be replayed against the
     * other. It is a constant rather than derived from the URL: deriving it
     * would reintroduce the problem the moment a path changed.
     */
    public const AUDIENCE = 'hireflow-approver';

    /** Where this root is mounted, for reducing a path to its endpoint. */
    private const ROOT = 'api/hireflow/approver';

    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.hireflow.approver_secret');

        /*
        | No secret, no access.
        |
        | Failing closed matters more here than anywhere else: a deployment
        | that forgets the secret would otherwise accept every request, signed
        | or not, and the failure would look like everything working.
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

        if (!$this->timestampInWindow($timestamp)) {
            return $this->deny('This request has expired.');
        }

        if (!$this->nonceLooksRight($nonce)) {
            return $this->deny('This request is not signed correctly.');
        }

        $expected = hash_hmac('sha256', self::canonical(
            $request->getMethod(),
            $request->getPathInfo(),
            $timestamp,
            $nonce,
            $empno,
            $request->getContent()
        ), $secret);

        /* Constant time: a comparison that returns early leaks, one character
           at a time, what the correct signature would have been. */
        if (!hash_equals($expected, $signature)) {
            return $this->deny('This request could not be verified.');
        }

        /* Claimed only AFTER the signature checks out, so an unauthenticated
           caller cannot burn through nonces and lock out real requests. */
        if (!$this->claimNonce(self::NONCE_SCOPE, $nonce)) {
            return $this->deny('This request has already been used.');
        }

        /*
        | Signed correctly, but may this employee approve anything?
        |
        | Asked here, of this application's own record of the role, and asked
        | again on every request — an account disabled a minute ago stops
        | working now rather than at the end of a session.
        */
        if (!HrHeadAccess::isApprover($empno)) {
            return $this->deny('This employee may not approve offers.', 403);
        }

        /* The one identity everything downstream trusts, taken from the signed
           header rather than from anything in the body or the route. */
        $request->attributes->set('hireflow_empno', $empno);

        return $next($request);
    }

    /**
     * The canonical string both sides sign.
     *
     * The audience comes FIRST, so this string can never coincide with the
     * interviewer root's for any method, path or body.
     */
    public static function canonical(string $method, string $path, string $timestamp, string $nonce, string $empno, string $body): string
    {
        return implode("\n", [
            self::AUDIENCE,
            strtoupper($method),
            self::relativePath($path),
            $timestamp,
            $nonce,
            $empno,
            hash('sha256', $body),
        ]);
    }

    /**
     * Reduce any form of the path to the part after this API root.
     *
     * The two applications sit at different base paths and may be served from
     * elsewhere in another environment. Signing the deployed path would make
     * every request fail the moment either one moved — and fail looking like
     * an attack rather than a configuration change.
     */
    public static function relativePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        $position = strpos($path, self::ROOT);

        if ($position !== false) {
            $path = substr($path, $position + strlen(self::ROOT));
        }

        return '/' . ltrim($path, '/');
    }
}
