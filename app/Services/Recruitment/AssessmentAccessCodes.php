<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * HR's one-time assessment access codes (tblapp_assessment_access).
 *
 * The applicant enters the code in zen-applicants, which opens the assessments
 * in that browser session. Issuing a code retires any earlier code that has not
 * been used, so only the newest one works. The code itself is stored hashed and
 * shown to HR only once, when it is issued.
 *
 * Applicants whose access has ended can ask for a new code
 * (tblapp_assessment_access_requests, written by zen-applicants). Issuing a
 * code answers that request.
 */
class AssessmentAccessCodes
{
    /**
     * Issue a new code and return it in plain text (the only time it is shown).
     *
     * Refused unless the applicant has completed an Initial Interview, which is
     * the prerequisite for being assessed at all. HR may still proceed by
     * giving $overrideReason -- there are legitimate cases and a hard refusal
     * would leave HR stuck -- and that reason is stored on the row beside
     * issued_by and issued_at, so the decision stays answerable.
     *
     * This is eligibility, not authentication. The code is still required in
     * every case; nothing here weakens it.
     *
     * @throws ValidationException when the prerequisite is unmet and no reason is given
     */
    public static function issue(int $appId, string $empno, ?string $overrideReason = null): array
    {
        $overrideReason = $overrideReason !== null ? trim($overrideReason) : null;

        if ($overrideReason === '') {
            $overrideReason = null;
        }

        $status = AssessmentEligibility::status($appId);

        if (!$status['met'] && $overrideReason === null) {
            throw ValidationException::withMessages([
                'prerequisite' => $status['reason']
                    . ' Assessments come after the Initial Interview. To issue a code anyway, give a reason.',
            ]);
        }

        // Met normally: nothing to explain, so nothing is recorded as an override.
        if ($status['met']) {
            $overrideReason = null;
        }

        $length = config('applicant_assessments.code_length');
        $code = str_pad((string) random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
        $expires = now()->addMinutes(config('applicant_assessments.code_minutes'));

        DB::connection('applicant')->transaction(function () use ($appId, $empno, $code, $expires, $overrideReason) {
            $db = DB::connection('applicant');

            // Same serialization point zen-applicants uses for this applicant.
            $db->table('tblapp_persinfo')->where('app_id', $appId)->lockForUpdate()->first();

            $db->table('tblapp_assessment_access')
                ->where('app_id', $appId)
                ->whereNull('redeemed_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            $db->table('tblapp_assessment_access')->insert([
                'app_id' => $appId,
                'code_hash' => Hash::make($code),
                'issued_by' => $empno,
                'issued_at' => now(),
                'prerequisite_override_reason' => $overrideReason,
                'expires_at' => $expires,
                'failed_attempts' => 0,
            ]);

            // The applicant's request for a code, if they made one, is answered.
            $db->table('tblapp_assessment_access_requests')
                ->where('app_id', $appId)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now(), 'resolved_by' => $empno]);
        });

        return [
            'code' => $code,
            'expires_at' => $expires,
            'overridden' => $overrideReason !== null,
        ];
    }

    /** The applicant's open request for a new code, if any. */
    public static function openRequest(int $appId): ?object
    {
        return DB::connection('applicant')->table('tblapp_assessment_access_requests')
            ->where('app_id', $appId)
            ->whereNull('resolved_at')
            ->orderByDesc('id')
            ->first();
    }

    /** Every open request, oldest first, with the applicant's name. */
    public static function openRequests(): \Illuminate\Support\Collection
    {
        return DB::connection('applicant')->table('tblapp_assessment_access_requests as r')
            ->join('tblapp_persinfo as p', 'p.app_id', '=', 'r.app_id')
            ->whereNull('r.resolved_at')
            ->orderBy('r.requested_at')
            ->get(['r.id', 'r.app_id', 'r.reason', 'r.assessment', 'r.requested_at', 'r.times_asked',
                'p.app_fname', 'p.app_lname']);
    }

    /** The newest code, for showing whether it is waiting, used or expired. */
    public static function latest(int $appId): ?object
    {
        return DB::connection('applicant')->table('tblapp_assessment_access')
            ->where('app_id', $appId)
            ->orderByDesc('id')
            ->first(['id', 'issued_by', 'issued_at', 'expires_at', 'redeemed_at', 'unlocked_until', 'revoked_at', 'failed_attempts']);
    }
}
