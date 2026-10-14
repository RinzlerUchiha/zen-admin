<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Facades\DB;

/**
 * Whether an applicant may be given assessments yet.
 *
 * The recruitment order is Initial Interview -> Exams. Until now nothing
 * enforced it: a code could be issued to anybody, and the applicant side asked
 * only for the code.
 *
 * Two separate things, and both are required:
 *
 *   ELIGIBILITY    has this applicant got far enough to be assessed?
 *                  That is this class. It asks the workflow, not the OTP.
 *
 *   AUTHORISATION  is this the person, in the browser HR handed the code to?
 *                  That is the access code, and it is untouched.
 *
 * Eligibility is judged PER PERSON, because assessment access itself is per
 * person (tblapp_assessment_access.app_id is the applicant, not one of their
 * applications) and the assessments measure the person rather than their fit
 * for one posting. So completing the Initial Interview on any OPEN application
 * makes them eligible. A closed application does not count: a process that
 * ended should not keep opening doors.
 *
 * "Completed" means the workflow step is completed — HR recorded the round's
 * outcome — not merely that an interview was marked held. Held is the event;
 * completed is HR saying the round is done and the application may move on,
 * which is exactly the question being asked here.
 */
class AssessmentEligibility
{
    /** The stage that must be completed first. */
    public const PREREQUISITE_STAGE = 'initial';

    /**
     * Has this applicant completed an Initial Interview on an open application?
     */
    public static function prerequisiteMet(int $appId): bool
    {
        return self::completedSteps($appId)->exists();
    }

    /**
     * The whole picture, for HR's screen.
     *
     * Returns which open applications exist, which of them have the Initial
     * Interview behind them, and — when none do — what is actually holding it
     * up, so HR is told why rather than only that they cannot proceed.
     */
    public static function status(int $appId): array
    {
        $open = DB::connection('applicant')->table('tblapp_applications')
            ->where('app_id', $appId)
            ->whereNull('closed_at')
            ->pluck('id');

        if ($open->isEmpty()) {
            return [
                'met' => false,
                'reason' => 'This applicant has no open application, so there is no Initial Interview to complete.',
                'open_applications' => 0,
                'completed_on' => [],
            ];
        }

        $completed = self::completedSteps($appId)->pluck('application_id')->all();

        if ($completed) {
            return [
                'met' => true,
                'reason' => null,
                'open_applications' => $open->count(),
                'completed_on' => $completed,
            ];
        }

        return [
            'met' => false,
            'reason' => self::blockingReason($open->all()),
            'open_applications' => $open->count(),
            'completed_on' => [],
        ];
    }

    /**
     * Why the Initial Interview is not behind them yet — scheduled, held but
     * not concluded, or not arranged at all. HR can act on each of these.
     */
    private static function blockingReason(array $applicationIds): string
    {
        $c = DB::connection('applicant');

        $steps = $c->table('tblapp_application_steps')
            ->whereIn('application_id', $applicationIds)
            ->where('stage_key', self::PREREQUISITE_STAGE)
            ->pluck('id');

        if ($steps->isEmpty()) {
            return 'The Initial Interview round does not exist on this application yet.';
        }

        $held = $c->table('tblapp_interviews')
            ->whereIn('step_id', $steps)
            ->where('attendance', 'held')
            ->exists();

        if ($held) {
            return 'The Initial Interview has taken place but the round has not been completed yet.';
        }

        $arranged = $c->table('tblapp_interviews')
            ->whereIn('step_id', $steps)
            ->where('attendance', 'scheduled')
            ->exists();

        return $arranged
            ? 'The Initial Interview is arranged but has not taken place yet.'
            : 'The Initial Interview has not been arranged yet.';
    }

    /** Completed Initial Interview steps on this applicant's open applications. */
    private static function completedSteps(int $appId)
    {
        return DB::connection('applicant')->table('tblapp_application_steps as s')
            ->join('tblapp_applications as a', 'a.id', '=', 's.application_id')
            ->where('a.app_id', $appId)
            ->whereNull('a.closed_at')
            ->where('s.stage_key', self::PREREQUISITE_STAGE)
            ->where('s.state', 'completed')
            ->select('s.application_id');
    }
}
