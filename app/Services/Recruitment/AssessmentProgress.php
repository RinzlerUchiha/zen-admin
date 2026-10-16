<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\AssessmentDecision;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Where an applicant's assessments stand, and what HR concluded.
 *
 * READ-ONLY OVER THE ASSESSMENT ENGINE
 *
 * Nothing here writes to an attempt, a result, or an access code. It reads
 * AssessmentResults — the same service the applicant profile uses — and adds
 * two things the engine does not answer: are we waiting on anything, and what
 * did HR decide.
 *
 * NO INVENTED THRESHOLDS
 *
 * There is no pass mark for any of these assessments anywhere in the system,
 * and inventing one would make the software the author of a hiring rule nobody
 * approved. So no result is ever marked pass or fail here. Where the job
 * specification defines an expected profile, the applicant's result is shown
 * BESIDE it and the comparison is left to a person. Where it does not, the
 * comparison is reported as unspecified rather than quietly treated as a pass.
 *
 * COMPLETION IS NOT PROGRESSION
 *
 * Finishing every assessment advances nobody. HR records proceed, hold or
 * stop, with a reason, and that decision is the only thing that moves an
 * application.
 */
class AssessmentProgress
{
    /**
     * Which expected-profile field on the job specification each assessment
     * corresponds to.
     *
     * Only the ones that genuinely have a counterpart. An assessment missing
     * from this map has no expected profile to compare against at all, which
     * is reported as such rather than guessed at.
     */
    private const EXPECTED_PROFILE = [
        'tapt' => 'jspec_tapt',
        'enneagram' => 'jspec_enneagram',
        'vak' => 'jspec_learnstyle',
        'career_anchors' => 'jspec_career',
        'why_i_work' => 'jspec_motivation',
        'disc' => 'jspec_personality',
        'abstract_reasoning' => 'jspec_ravenl',
    ];

    /**
     * The whole picture for one application.
     *
     * @return array{assessments:array,counts:array,outstanding:array,decision:array|null}
     */
    public static function forApplication(int $applicationId): array
    {
        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            return [
                'unavailable' => 'That application could not be found.',
                'assessments' => [],
                'counts' => self::counts([]),
                'outstanding' => [],
                'decision' => null,
            ];
        }

        $summaries = AssessmentResults::summaries((int) $application->app_id);
        $spec = self::specificationFor($application);
        $delimiter = config('qualification_matching.delimiters.item', '%#');

        $assessments = [];

        foreach ($summaries as $summary) {
            $field = self::EXPECTED_PROFILE[$summary->key] ?? null;
            $expectedRaw = $field && $spec ? trim((string) ($spec->{$field} ?? '')) : '';

            $assessments[] = [
                'key' => $summary->key,
                'label' => $summary->label,
                'kind' => $summary->kind,
                'status' => $summary->status,
                'taken' => (bool) $summary->hasResult,
                'taken_on' => $summary->takenOn?->toDateString(),

                /*
                | The expected profile, and whether there is one.
                |
                | 'unspecified' is said out loud. A blank expected profile must
                | never read as a pass, and HR needs to know the difference
                | between "this matches" and "nobody said what to look for".
                */
                'expected' => $expectedRaw === ''
                    ? null
                    : array_values(array_filter(array_map('trim', explode($delimiter, $expectedRaw)))),
                'comparison' => match (true) {
                    $field === null => 'unavailable',
                    $expectedRaw === '' => 'unspecified',
                    !$summary->hasResult => 'not_taken',
                    default => 'available',
                },
                'comparison_note' => match (true) {
                    $field === null => 'This assessment has no expected profile on the job specification.',
                    $expectedRaw === '' => 'The job specification does not define an expected profile, '
                        . 'so there is nothing to compare against.',
                    !$summary->hasResult => 'Not taken yet.',
                    default => 'Compare the result with the expected profile below. '
                        . 'There is no pass mark; this is for you to read.',
                },
            ];
        }

        $outstanding = collect($assessments)->where('taken', false)->pluck('label')->values()->all();

        return [
            'application_id' => (int) $application->id,
            'app_id' => (int) $application->app_id,
            'assessments' => $assessments,
            'counts' => self::counts($assessments),
            'outstanding' => $outstanding,

            /* Said plainly: everything is in, and that is still not a verdict. */
            'all_taken' => empty($outstanding),
            'decision' => self::current($applicationId)?->only([
                'decision', 'reason', 'decided_by', 'decided_at',
            ]),
        ];
    }

    /**
     * Record HR's decision after the assessments.
     *
     * A reason is always required. Each of these is a judgement about a person,
     * and 'because the results were in' is not one.
     */
    public static function decide(int $applicationId, string $decision, string $reason, ?string $empno): AssessmentDecision
    {
        if (!in_array($decision, AssessmentDecision::DECISIONS, true)) {
            throw ValidationException::withMessages([
                'decision' => 'A decision is proceed, hold or stop.',
            ]);
        }

        $reason = trim($reason) ?: null;

        if ($reason === null) {
            throw ValidationException::withMessages([
                'reason' => 'A reason is needed, and it is kept with the decision.',
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

        $snapshot = self::forApplication($applicationId);

        return DB::connection('applicant')->transaction(function () use (
            $applicationId, $decision, $reason, $empno, $snapshot
        ) {
            AssessmentDecision::where('application_id', $applicationId)
                ->whereNull('superseded_at')
                ->update(['superseded_at' => now()]);

            $record = AssessmentDecision::create([
                'application_id' => $applicationId,
                'decision' => $decision,
                'results_snapshot' => $snapshot,
                'reason' => $reason,
                'decided_by' => $empno,
                'decided_at' => now(),
            ]);

            /*
            | Only 'stop' ends anything, and it ends it as Not Selected: HR
            | interviewed and assessed this candidate and decided against
            | them, which is exactly what that status means.
            |
            | 'proceed' deliberately changes NOTHING about the application. The
            | candidate carries on through the workflow they already have, and
            | advancing a stage stays the separate, explicit act it has always
            | been.
            */
            if ($decision === AssessmentDecision::STOP && $empno !== null) {
                ApplicationDecision::notSelected($applicationId, $empno, $reason);
            }

            return $record;
        });
    }

    /** The decision that stands, if one has been made. */
    public static function current(int $applicationId): ?AssessmentDecision
    {
        return AssessmentDecision::where('application_id', $applicationId)
            ->whereNull('superseded_at')
            ->first();
    }

    /* ------------------------------------------------------------------ */

    /**
     * The tallies.
     *
     * Deliberately counts of what was TAKEN and what could be COMPARED. There
     * is no count of passes, because nothing here decides a pass.
     */
    private static function counts(array $assessments): array
    {
        $all = collect($assessments);

        return [
            'total' => $all->count(),
            'taken' => $all->where('taken', true)->count(),
            'outstanding' => $all->where('taken', false)->count(),
            'comparable' => $all->where('comparison', 'available')->count(),
            /* How much of the expected profile the specification never filled
               in -- worth seeing, because it is the usual reason a comparison
               is unavailable. */
            'unspecified' => $all->where('comparison', 'unspecified')->count(),
            'no_profile' => $all->where('comparison', 'unavailable')->count(),
        ];
    }

    /** The specification behind this application's posting. */
    private static function specificationFor(ApplicantApplication $application): ?object
    {
        $jspecId = null;

        if ($application->job_posting_id) {
            $jspecId = DB::table('tbl_job_posting')
                ->where('id', $application->job_posting_id)
                ->value('jobspec_id');
        }

        if (!$jspecId && $application->request_position_id) {
            $jspecId = DB::table('tbl_manpower_request_position')
                ->where('id', $application->request_position_id)
                ->value('jobspec_id');
        }

        return $jspecId
            ? DB::table('tbl_manpower_jobspec')->where('jspec_id', $jspecId)->first()
            : null;
    }
}
