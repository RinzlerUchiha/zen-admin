<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\EligibilityDecision;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HR's shortlisting decision, and the record of it.
 *
 * THE SYSTEM ADVISES; HR DECIDES
 *
 * Nothing in here is automatic. The matcher produces a recommendation, and a
 * person either follows it or does not — and either way the advice as it stood
 * is stored beside the decision, because the specification, its classification
 * and the applicant's own profile all change over time. Without the snapshot a
 * past shortlist cannot be explained, which is the one thing an audit record
 * must never be.
 *
 * AN OVERRIDE IS NOT A MISTAKE
 *
 * Shortlisting somebody with an unmet mandatory requirement, or declining
 * somebody who matched everything, is allowed. HR is accountable for the
 * decision and the matcher is not. What is required is a reason.
 *
 * WHAT NOT SHORTLISTING MEANS
 *
 * The application closes as 'Not Shortlisted' — pooled, and with no cooldown.
 * It is deliberately NOT 'Not Selected': that means interviewed and not
 * chosen, and conflating them would make it impossible to tell screening loss
 * from interview loss, and would attach a six-month cooldown nobody earned.
 */
class ShortlistDecision
{
    public const SHORTLISTED = 'shortlisted';
    public const NOT_SHORTLISTED = 'not_shortlisted';

    /**
     * Record a decision, superseding any earlier one.
     *
     * @param  string|null  $reason  Required when overriding the recommendation.
     */
    public static function record(int $applicationId, string $decision, ?string $reason, ?string $empno): EligibilityDecision
    {
        if (!in_array($decision, [self::SHORTLISTED, self::NOT_SHORTLISTED], true)) {
            throw ValidationException::withMessages([
                'decision' => 'A decision is either shortlisted or not shortlisted.',
            ]);
        }

        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            throw ValidationException::withMessages([
                'application' => 'That application could not be found.',
            ]);
        }

        if ($application->is_closed) {
            throw ValidationException::withMessages([
                'application' => 'This application is closed as ' . $application->status . '.',
            ]);
        }

        /* The advice as it stands right now, which is what HR is acting on. */
        $recommendation = QualificationMatcher::forApplication($applicationId);
        $overrode = self::contradictsAdvice($decision, $recommendation);

        $reason = trim((string) $reason) ?: null;

        if ($overrode && $reason === null) {
            throw ValidationException::withMessages([
                'reason' => 'This goes against the recommendation, so it needs a reason.',
            ]);
        }

        return DB::connection('applicant')->transaction(function () use (
            $applicationId, $decision, $recommendation, $overrode, $reason, $empno
        ) {
            /* One live decision per application; an earlier one is superseded
               rather than deleted, so the trail shows what changed. */
            EligibilityDecision::where('application_id', $applicationId)
                ->whereNull('superseded_at')
                ->update(['superseded_at' => now()]);

            $record = EligibilityDecision::create([
                'application_id' => $applicationId,
                'decision' => $decision,
                'recommendation_snapshot' => $recommendation,
                'overrode' => $overrode,
                'reason' => $reason,
                'decided_by' => $empno,
                'decided_at' => now(),
            ]);

            /*
            | Not shortlisting ends the application. Shortlisting changes
            | nothing about it: the candidate carries on through the workflow
            | they already have, and scheduling is unaffected -- shortlisting
            | advises, it is not a gate on arranging an interview.
            */
            if ($decision === self::NOT_SHORTLISTED && $empno !== null) {
                ApplicationDecision::notShortlisted(
                    $applicationId,
                    $empno,
                    $reason ?: 'Not shortlisted for this posting'
                );
            }

            return $record;
        });
    }

    /** The live decision for an application, if one has been made. */
    public static function current(int $applicationId): ?EligibilityDecision
    {
        return EligibilityDecision::where('application_id', $applicationId)
            ->whereNull('superseded_at')
            ->first();
    }

    /**
     * Does this decision go against what the matcher advised?
     *
     * Only one thing counts as contradicting it: shortlisting somebody with an
     * unmet MANDATORY requirement. Declining a strong match is not an override
     * — the matcher never advises hiring anybody, and HR has reasons it has no
     * access to. Calling that an override would make the word meaningless and
     * demand a justification for every ordinary decision.
     */
    private static function contradictsAdvice(string $decision, array $recommendation): bool
    {
        if ($decision !== self::SHORTLISTED) {
            return false;
        }

        return (int) ($recommendation['counts']['mandatory_unmet'] ?? 0) > 0;
    }
}
