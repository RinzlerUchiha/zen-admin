<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicationStep;
use App\Models\Applicant\StageHistory;
use App\Models\Recruitment\WorkflowStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * An application's journey through the hiring process.
 *
 * The workflow belongs to the application. Its steps are copied from the
 * catalogue once, at the start, and from then on they are its own: HR adding a
 * Technical round for one candidate cannot move anybody else, and editing the
 * catalogue cannot reach an application that already exists. That is why there
 * is no template to pin against — the pinning is structural.
 *
 * Everything here is about WHERE an application is. Nothing here decides
 * whether someone is Hired, Withdrawn or Not Selected: that is `status`, a
 * separate question with a separate answer.
 */
class ApplicationWorkflow
{
    /**
     * Give an application its workflow.
     *
     * Copies the baseline stages — Initial, Exams, Final — into rows this
     * application owns, and records its arrival at the first one. Idempotent:
     * an application that already has steps is left exactly as it is, because
     * re-running this on a live application would wipe real interview history.
     *
     * @return \Illuminate\Support\Collection<ApplicationStep>
     */
    public static function initialize(int $applicationId, ?string $empno = null, ?string $reason = null)
    {
        $existing = ApplicationStep::forApplication($applicationId)->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $baseline = WorkflowStage::baseline()->get();

        if ($baseline->isEmpty()) {
            throw ValidationException::withMessages([
                'workflow' => 'No baseline workflow stages are configured, so an application cannot be started.',
            ]);
        }

        return DB::connection('applicant')->transaction(function () use ($applicationId, $baseline, $empno, $reason) {
            $position = 0;

            foreach ($baseline as $stage) {
                $position += ApplicationStep::POSITION_GAP;

                ApplicationStep::create([
                    'application_id' => $applicationId,
                    'stage_key' => $stage->stage_key,
                    // Snapshotted, so a later rename of the catalogue entry
                    // never rewrites what this step was called at the time.
                    'label' => $stage->label,
                    'kind' => $stage->kind,
                    'position' => $position,
                    'required' => $stage->required_by_default,
                    'satisfied_by' => $stage->satisfied_by,
                    'state' => ApplicationStep::STATE_PENDING,
                ]);
            }

            $first = $baseline->first();
            self::recordStage($applicationId, null, $first->stage_key, $reason ?? 'Application entered the workflow', $empno);

            return ApplicationStep::forApplication($applicationId)->get();
        });
    }

    /**
     * Add an intermediate step to ONE application.
     *
     * This is the `+` between two steps. The stage must already exist in the
     * catalogue — a typo typed into a form should not quietly become a
     * permanent, reusable stage that everyone then sees. Adding a genuinely
     * new stage to the catalogue is a separate, deliberate act.
     *
     * @param int|null $afterStepId the step to sit behind; null puts it first
     */
    public static function addStep(int $applicationId, string $stageKey, ?int $afterStepId = null, ?string $empno = null): ApplicationStep
    {
        $stage = WorkflowStage::available()->where('stage_key', $stageKey)->first();

        if (!$stage) {
            throw ValidationException::withMessages([
                'stage_key' => 'That stage is not in the catalogue. Add it there first if it is one you will use again.',
            ]);
        }

        $steps = ApplicationStep::forApplication($applicationId)->get();

        if ($steps->isEmpty()) {
            throw ValidationException::withMessages([
                'application' => 'This application has no workflow yet.',
            ]);
        }

        if ($steps->contains('stage_key', $stageKey)) {
            throw ValidationException::withMessages([
                'stage_key' => 'This application already has a ' . $stage->label . ' step.',
            ]);
        }

        return DB::connection('applicant')->transaction(function () use ($applicationId, $stage, $afterStepId, $steps, $empno) {
            $position = self::positionAfter($steps, $afterStepId);

            return ApplicationStep::create([
                'application_id' => $applicationId,
                'stage_key' => $stage->stage_key,
                'label' => $stage->label,
                'kind' => $stage->kind,
                'position' => $position,
                'required' => $stage->required_by_default,
                'satisfied_by' => $stage->satisfied_by,
                'state' => ApplicationStep::STATE_PENDING,
                'decided_by' => null,
            ]);
        });
    }

    /**
     * Where a new step sits.
     *
     * Halfway between the step it follows and the next one. Gaps of 10 mean
     * that normally works without touching anything else; when two steps end up
     * adjacent, the pending steps are spaced out again rather than refusing the
     * insert, which would be a baffling thing for HR to be told.
     */
    private static function positionAfter($steps, ?int $afterStepId): int
    {
        $ordered = $steps->sortBy('position')->values();

        // Before everything: halve the first position, or make room first if
        // there is none to halve.
        if ($afterStepId === null) {
            if ((int) $ordered->first()->position <= 1) {
                self::respace($ordered);
                $ordered = ApplicationStep::forApplication((int) $ordered->first()->application_id)->get()->values();
            }

            return (int) floor((int) $ordered->first()->position / 2);
        }

        $index = $ordered->search(fn ($s) => (int) $s->id === $afterStepId);

        if ($index === false) {
            throw ValidationException::withMessages([
                'after' => 'That step is not part of this application.',
            ]);
        }

        $before = (int) $ordered[$index]->position;
        $after = isset($ordered[$index + 1]) ? (int) $ordered[$index + 1]->position : null;

        if ($after === null) {
            return $before + ApplicationStep::POSITION_GAP;
        }

        if ($after - $before > 1) {
            return (int) floor(($before + $after) / 2);
        }

        self::respace($ordered);

        $refreshed = ApplicationStep::forApplication((int) $ordered->first()->application_id)->get()->values();
        $i = $refreshed->search(fn ($s) => (int) $s->id === $afterStepId);

        return (int) $refreshed[$i]->position + (int) floor(ApplicationStep::POSITION_GAP / 2);
    }

    /**
     * The step, but only if it belongs to this application.
     *
     * Every step mutation goes through here. A step id arriving from a request
     * is just a number, and without this an HR user looking at one application
     * could act on another application's step by passing its id — the exact
     * mixing of two candidates' records the per-application design exists to
     * prevent.
     *
     * The refusal deliberately does not say whether the step exists somewhere
     * else. If it is not part of this application, that is all the caller
     * needs to know.
     */
    private static function stepFor(int $applicationId, int $stepId): ApplicationStep
    {
        $step = ApplicationStep::where('id', $stepId)
            ->where('application_id', $applicationId)
            ->first();

        if (!$step) {
            throw ValidationException::withMessages([
                'step' => 'That step is not part of this application.',
            ]);
        }

        return $step;
    }

    /** Spread positions back out to multiples of the gap. Order is preserved. */
    private static function respace($ordered): bool
    {
        $position = 0;

        foreach ($ordered->sortBy('position') as $step) {
            $position += ApplicationStep::POSITION_GAP;
            $step->update(['position' => $position]);
        }

        return true;
    }

    /**
     * Take a step off ONE application.
     *
     * A completed step is history and stays. The two fixed stages stay too —
     * an application without an Initial or a Final is not a shorter process,
     * it is an incomplete record. To say one did not happen, mark it
     * not_applicable, which is a fact rather than an absence.
     */
    public static function removeStep(int $applicationId, int $stepId): void
    {
        $step = self::stepFor($applicationId, $stepId);

        if ($step->state === ApplicationStep::STATE_COMPLETED) {
            throw ValidationException::withMessages([
                'step' => 'That step has already been completed, so it stays on the record.',
            ]);
        }

        $stage = WorkflowStage::where('stage_key', $step->stage_key)->first();

        if ($stage && $stage->is_fixed) {
            throw ValidationException::withMessages([
                'step' => $step->label . ' cannot be removed. Mark it not applicable instead.',
            ]);
        }

        $step->delete();
    }

    /**
     * Record what happened at a step.
     *
     * Completing a step does not move the application on its own — advancing
     * is a separate decision, because HR may complete a round and still be
     * waiting on something else before the candidate progresses.
     */
    public static function completeStep(int $applicationId, int $stepId, array $outcome, ?string $empno = null): ApplicationStep
    {
        $step = self::stepFor($applicationId, $stepId);

        $step->update([
            'state' => ApplicationStep::STATE_COMPLETED,
            'result' => $outcome['result'] ?? null,
            'notes' => $outcome['notes'] ?? null,
            'satisfied_by' => $outcome['satisfied_by'] ?? $step->satisfied_by,
            'decided_at' => now(),
            'decided_by' => $empno,
        ]);

        return $step->fresh();
    }

    /**
     * Mark a step as never having applied to this application.
     *
     * The job-fair case: somebody acquired at an event did not skip their
     * Initial Interview and is not waiting for one — it was not part of how
     * they were hired. Saying 'not applicable' records that honestly, where
     * 'completed' would invent an interview and 'pending' would put them in a
     * queue forever.
     */
    public static function markNotApplicable(int $applicationId, int $stepId, ?string $reason = null, ?string $empno = null): ApplicationStep
    {
        $step = self::stepFor($applicationId, $stepId);

        if ($step->state === ApplicationStep::STATE_COMPLETED) {
            throw ValidationException::withMessages([
                'step' => 'That step was completed, so it did apply.',
            ]);
        }

        $step->update([
            'state' => ApplicationStep::STATE_NOT_APPLICABLE,
            'notes' => $reason,
            'decided_at' => now(),
            'decided_by' => $empno,
        ]);

        return $step->fresh();
    }

    /** Put a not-applicable step back in play. Completed steps stay as they are. */
    public static function reopenStep(int $applicationId, int $stepId, ?string $empno = null): ApplicationStep
    {
        $step = self::stepFor($applicationId, $stepId);

        if ($step->state === ApplicationStep::STATE_COMPLETED) {
            throw ValidationException::withMessages([
                'step' => 'A completed step cannot be reopened; record a new one instead.',
            ]);
        }

        $step->update([
            'state' => ApplicationStep::STATE_PENDING,
            'decided_at' => null,
            'decided_by' => $empno,
        ]);

        return $step->fresh();
    }

    /**
     * Move an application to a stage, and write it down.
     *
     * The history row is the point. A stage that changes without one leaves a
     * question nobody can answer later.
     */
    public static function moveTo(int $applicationId, string $stageKey, ?string $reason = null, ?string $empno = null): void
    {
        DB::connection('applicant')->transaction(function () use ($applicationId, $stageKey, $reason, $empno) {
            $current = DB::connection('applicant')->table('tblapp_applications')
                ->where('id', $applicationId)->value('stage_key');

            if ($current === $stageKey) {
                return;
            }

            DB::connection('applicant')->table('tblapp_applications')
                ->where('id', $applicationId)
                ->update(['stage_key' => $stageKey, 'updated_at' => now()]);

            self::recordStage($applicationId, $current, $stageKey, $reason, $empno);
        });
    }

    /** One append-only line in the application's history. */
    private static function recordStage(int $applicationId, ?string $from, string $to, ?string $reason, ?string $empno): void
    {
        StageHistory::create([
            'application_id' => $applicationId,
            'from_stage' => $from,
            'to_stage' => $to,
            'reason' => $reason,
            'actor_empno' => $empno,
            'occurred_at' => now(),
        ]);

        DB::connection('applicant')->table('tblapp_applications')
            ->where('id', $applicationId)
            ->update(['stage_key' => $to]);
    }

    /**
     * The next thing this application is waiting on.
     *
     * Required and still pending, earliest first. Optional steps are skipped
     * because they do not hold anybody up, and not-applicable ones are not
     * waiting at all.
     */
    public static function nextStep(int $applicationId): ?ApplicationStep
    {
        return ApplicationStep::forApplication($applicationId)
            ->where('state', ApplicationStep::STATE_PENDING)
            ->where('required', true)
            ->first();
    }

    /** Every required step is settled — nothing is blocking. */
    public static function isComplete(int $applicationId): bool
    {
        return !ApplicationStep::forApplication($applicationId)
            ->where('state', ApplicationStep::STATE_PENDING)
            ->where('required', true)
            ->exists();
    }

    /** The whole workflow as HR would read it, in order. */
    public static function summary(int $applicationId): array
    {
        $steps = ApplicationStep::forApplication($applicationId)->get();

        return [
            'steps' => $steps->map(fn ($s) => [
                'id' => $s->id,
                'stage_key' => $s->stage_key,
                'label' => $s->label,
                'kind' => $s->kind,
                'position' => $s->position,
                'required' => $s->required,
                'state' => $s->state,
                'result' => $s->result,
                'decided_at' => $s->decided_at?->toDateTimeString(),
            ])->values()->all(),
            'next' => self::nextStep($applicationId)?->label,
            'complete' => self::isComplete($applicationId),
        ];
    }
}
