<?php

namespace App\Http\Middleware\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * The mechanics every signed Zen-Hub request is checked with.
 *
 * Shared because they are genuinely the same question in both directions —
 * is this recent, has it been used before, does the signature match, and how
 * is a refusal phrased. What is NOT shared is anything that decides WHO is
 * asking or WHAT they may do: each root builds its own canonical string, holds
 * its own secret, and applies its own final authorisation gate.
 *
 * That split is the point. The interviewer root and the approver root must not
 * be able to accept each other's signatures, so the one thing they must never
 * have in common is the string being signed.
 *
 * The using class provides WINDOW_SECONDS and NONCE_TTL.
 */
trait VerifiesSignedHireflowRequest
{
    /** The four headers every signed request carries. */
    protected function signedHeaders(Request $request): array
    {
        return [
            'empno' => (string) $request->header('X-HF-Employee', ''),
            'timestamp' => (string) $request->header('X-HF-Timestamp', ''),
            'nonce' => (string) $request->header('X-HF-Nonce', ''),
            'signature' => (string) $request->header('X-HF-Signature', ''),
        ];
    }

    /**
     * A timestamp at all.
     *
     * Kept separate from the window check because the two are different
     * failures and are told apart in the refusal: a malformed timestamp is a
     * badly built request, an old one is a replay or a wrong clock.
     */
    protected function timestampIsNumeric(string $timestamp): bool
    {
        return ctype_digit($timestamp);
    }

    /**
     * Recent enough to be worth serving.
     *
     * A request signed minutes ago is almost certainly a replay or a badly
     * wrong clock; neither is worth serving.
     */
    protected function timestampInWindow(string $timestamp): bool
    {
        return abs(time() - (int) $timestamp) <= static::WINDOW_SECONDS;
    }

    /**
     * Checked before use, so the cache key cannot be steered by an unusually
     * long or odd value.
     */
    protected function nonceLooksRight(string $nonce): bool
    {
        return (bool) preg_match('/^[a-f0-9]{16,64}$/i', $nonce);
    }

    /**
     * Claim a nonce, returning false if it has already been used.
     *
     * add() writes only if the key is absent, which is what makes this a check
     * and a claim in one step rather than two. Callers must only reach this
     * AFTER the signature verifies, so an unauthenticated caller cannot burn
     * through nonces and lock out legitimate requests.
     *
     * The namespace is per-root, so one root cannot consume the other's
     * nonces — or be made to look like it had.
     */
    protected function claimNonce(string $namespace, string $nonce): bool
    {
        return Cache::add(
            'hireflow:nonce:' . $namespace . ':' . strtolower($nonce),
            true,
            static::NONCE_TTL
        );
    }

    /**
     * One shape of refusal.
     *
     * Deliberately vague about which check failed: telling a caller whether
     * the signature or the authorisation was the problem tells them which to
     * work on.
     */
    protected function deny(string $message, int $status = 401): Response
    {
        return response()->json(['message' => $message], $status);
    }
}
