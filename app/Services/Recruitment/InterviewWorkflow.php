<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationStep;
use App\Models\Applicant\Interview;
use App\Models\Applicant\InterviewEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Where interviews meet the application's workflow.
 *
 * The only place the two systems touch. ApplicationWorkflow stays generic and
 * knows nothing about interviews or their numbering; everything interview-
 * shaped lives here.
 *
 * Three things stay deliberately separate, and this service does not collapse
 * them:
 *
 *   an interviewer submitting an evaluation   — their account of the round
 *   HR completing the workflow step           — the round's result
 *   the application advancing a stage         — where the candidate now is
 *
 * HR routinely finishes a round and still waits: on a second interviewer, on
 * documents, on the candidate. Advancing automatically would move people HR
 * had not decided to move.
 */
class InterviewWorkflow
{
    /**
     * Add another interview round to one application.
     *
     * HR supplies nothing but the decision to hold one. The round is numbered
     * and placed by the system, because both follow from the process rather
     * than from anyone's judgement: an extra round always sits between the
     * Initial and the Final, and it is always the next one in sequence.
     *
     * Asking HR to type "3rd Interview" invited two mistakes the system can
     * simply avoid — a number that disagrees with the order, and a name that
     * invents an interview type nobody agreed to.
     */
    public static function addInterviewStep(int $applicationId, ?string $empno = null): ApplicationStep
    {
        if (!ApplicantApplication::where('id', $applicationId)->exists()) {
            throw ValidationException::withMessages([
                'application' => 'That application could not be found.',
            ]);
        }

        $steps = ApplicationStep::forApplication($applicationId)->get();

        if ($steps->isEmpty()) {
            throw ValidationException::withMessages([
                'application' => 'This application has no workflow yet.',
            ]);
        }

        return DB::connection('applicant')->transaction(function () use ($applicationId, $empno, $steps) {
            /*
            | The key is a permanent identity, chosen from what this application
            | has already used so it can never collide. It is NOT the number HR
            | reads: that is worked out from the round's position every time the
            | workflow is displayed, so the sequence stays in order even after a
            | round is removed.
            */
            $key = self::nextOrdinalKey($steps);

            /* Stored as it reads today, for the record. Display uses position. */
            $ordinal = (int) substr($key, strlen('interview_'));

            return ApplicationStep::create([
                'application_id' => $applicationId,
                'stage_key' => $key,
                'label' => self::ordinalLabel($ordinal),
                'kind' => 'interview',
                'position' => self::positionBeforeFinal($applicationId, $steps),
                'required' => true,
                'state' => ApplicationStep::STATE_PENDING,
                'decided_by' => null,
            ]);
        });
    }

    /** "2nd Interview", "3rd Interview", "21st Interview". */
    public static function ordinalLabel(int $n): string
    {
        $suffix = match (true) {
            $n % 100 >= 11 && $n % 100 <= 13 => 'th',
            $n % 10 === 1 => 'st',
            $n % 10 === 2 => 'nd',
            $n % 10 === 3 => 'rd',
            default => 'th',
        };

        return $n . $suffix . ' Interview';
    }

    /**
     * Record the result of an interview round.
     *
     * HR's decision, not an interviewer's. The evaluations inform it — they may
     * disagree with each other, and HR is the one who settles what the round
     * concluded.
     *
     * Requires that an interview was actually held: completing a round nobody
     * attended would record a result for something that never happened.
     */
    public static function completeStep(int $applicationId, int $stepId, array $outcome, ?string $empno = null): ApplicationStep
    {
        $step = self::stepOrFail($applicationId, $stepId);

        if ($step->kind !== 'interview') {
            throw ValidationException::withMessages([
                'step' => 'This is not an interview round. Complete it through the workflow instead.',
            ]);
        }

        if ($step->state === ApplicationStep::STATE_COMPLETED) {
            throw ValidationException::withMessages([
                'step' => 'That round has already been completed.',
            ]);
        }

        $held = Interview::where('step_id', $step->id)
            ->where('attendance', Interview::HELD)
            ->orderByDesc('held_at')
            ->first();

        if (!$held) {
            throw ValidationException::withMessages([
                'step' => 'No interview has been held for this round yet, so there is no result to record.',
            ]);
        }

        /* Delegated, so completion rules stay in one place for every kind of
           step rather than diverging between interviews and everything else. */
        return ApplicationWorkflow::completeStep($applicationId, $step->id, [
            'result' => $outcome['result'] ?? null,
            'notes' => $outcome['notes'] ?? null,
            /* HR's pass/fail for the round, when they settle it here. Passed
               through rather than derived: the evaluations inform HR, they do
               not decide for them. */
            'outcome' => $outcome['outcome'] ?? null,
            /* Which interview settled it — the record, not a reconstruction. */
            'satisfied_by' => $outcome['satisfied_by'] ?? ('interview:' . $held->id),
        ], $empno);
    }

    /**
     * Move the application to its next outstanding round.
     *
     * A separate act from completing a round, and deliberately so: HR decides
     * when a candidate moves, and the stage history records that decision with
     * whoever made it.
     */
    public static function advance(int $applicationId, ?string $reason = null, ?string $empno = null): ?ApplicationStep
    {
        $next = ApplicationWorkflow::nextStep($applicationId);

        if (!$next) {
            throw ValidationException::withMessages([
                'application' => 'There is no outstanding step to move on to.',
            ]);
        }

        ApplicationWorkflow::moveTo($applicationId, $next->stage_key, $reason, $empno);

        return $next;
    }

    /**
     * Whether a round can be taken off this application.
     *
     * Once interviews have been arranged against a round, removing it would
     * throw away a record of something that happened to a real candidate — and
     * with submitted evaluations, somebody's recorded judgement. The step is
     * history at that point, not a plan.
     *
     * @return array{removable: bool, reason: ?string}
     */
    public static function removability(int $applicationId, int $stepId): array
    {
        $step = self::stepOrFail($applicationId, $stepId);

        if ($step->state === ApplicationStep::STATE_COMPLETED) {
            return ['removable' => false, 'reason' => 'That round has been completed, so it stays on the record.'];
        }

        $interviewIds = Interview::where('step_id', $step->id)->pluck('id');

        if ($interviewIds->isEmpty()) {
            return ['removable' => true, 'reason' => null];
        }

        $evaluations = InterviewEvaluation::whereIn('interview_id', $interviewIds)
            ->whereNotNull('submitted_at')
            ->count();

        if ($evaluations > 0) {
            return [
                'removable' => false,
                'reason' => 'Interviewers have recorded evaluations for this round, so it cannot be removed.',
            ];
        }

        $concluded = Interview::whereIn('id', $interviewIds)
            ->whereIn('attendance', Interview::TERMINAL)
            ->count();

        if ($concluded > 0) {
            return [
                'removable' => false,
                'reason' => 'This round already has interview history, so it cannot be removed.',
            ];
        }

        return ['removable' => true, 'reason' => null];
    }

    /**
     * Take a round off this application, if nothing has happened on it yet.
     *
     * Deliberately refuses rather than unwinding evaluation chains to satisfy
     * the database's cascade. Deleting a correction chain to make a removal
     * possible would destroy the history the chain exists to preserve.
     */
    public static function removeInterviewStep(int $applicationId, int $stepId): void
    {
        $verdict = self::removability($applicationId, $stepId);

        if (!$verdict['removable']) {
            throw ValidationException::withMessages(['step' => $verdict['reason']]);
        }

        ApplicationWorkflow::removeStep($applicationId, $stepId);
    }

    /**
     * One application's interview rounds, with what has happened on each.
     *
     * Scoped to the application throughout. Nothing here can reach the
     * applicant's other applications, because an interview has no route to one.
     */
    public static function summary(int $applicationId): array
    {
        $steps = ApplicationStep::forApplication($applicationId)
            ->where('kind', 'interview')
            ->get();

        /*
        | What is in front of each round, of ANY kind.
        |
        | The query above is interview steps only, so on its own this page
        | cannot see that the Examinations round sits between the Initial and
        | the Final interview -- and it offered "Arrange interview" on every
        | pending round equally. The intended order is: Initial passed, exams
        | unlocked, assessments completed, HR reviews them, THEN later
        | interviews.
        |
        | ADVISORY ONLY. Nothing here blocks scheduling, changes a permission
        | or alters a workflow transition: the page simply says what a round is
        | waiting on. Scheduling stays ungated, so the Job Fair case -- where
        | rounds happen at the event, out of order -- keeps working.
        */
        $outstanding = ApplicationStep::forApplication($applicationId)
            ->where('state', ApplicationStep::STATE_PENDING)
            ->where('required', true)
            ->get(['id', 'label', 'position', 'kind']);

        $interviews = Interview::whereIn('step_id', $steps->pluck('id'))
            ->with(['interviewers', 'evaluations'])
            ->orderBy('created_at')
            ->get()
            ->groupBy('step_id');

        /*
        | What each round is CALLED right now, worked out from where it sits.
        |
        | The stored label and the stage_key are left exactly as they were —
        | they are the record of what the round was when it happened. But a
        | sequence has to read in order: once a round is removed, a stored
        | "3rd Interview" sitting second would be simply wrong on screen.
        |
        | So intermediate rounds are numbered by their position among the
        | intermediates, while Initial and Final keep the names that make them
        | the two ends of the process.
        */
        $noteCounts = InterviewNotes::countsFor($interviews->flatten()->pluck('id'));

        $intermediateNumber = 1;
        $display = [];

        foreach ($steps->sortBy('position') as $s) {
            if ($s->stage_key === 'initial' || $s->stage_key === 'final') {
                $display[$s->id] = $s->label;
                continue;
            }

            $intermediateNumber++;
            $display[$s->id] = self::ordinalLabel($intermediateNumber);
        }

        return $steps->map(function ($step) use ($interviews, $display, $noteCounts, $outstanding) {
            $rounds = $interviews->get($step->id, collect());

            return [
                'step_id' => $step->id,
                'stage_key' => $step->stage_key,
                /* As recorded when the round was created — kept for the audit. */
                'label' => $step->label,
                /* As it reads today, in sequence. */
                'display_label' => $display[$step->id] ?? $step->label,
                'state' => $step->state,
                'result' => $step->result,
                /*
                | HR's pass/fail for the round, and null when they have not
                | settled it. Null is shown as outstanding rather than as a
                | pass: the assessments read this, so a blank here is what
                | holds an applicant at this round.
                */
                'outcome' => $step->outcome,
                'outcome_by' => $step->outcome_by,
                'outcome_at' => $step->outcome_at?->toDateTimeString(),
                'awaiting_outcome' => $step->state === ApplicationStep::STATE_COMPLETED
                    && $step->outcome === null,

                /*
                | The earliest outstanding step in front of this one, or null
                | when it is this round's turn. A label for the screen to show
                | -- never a reason to refuse anything.
                */
                'waiting_on' => $step->state === ApplicationStep::STATE_PENDING
                    ? $outstanding
                        ->filter(fn ($o) => (int) $o->position < (int) $step->position)
                        ->sortBy('position')
                        ->first()?->label
                    : null,
                'interviews' => $rounds->map(fn ($i) => [
                    'id' => $i->id,
                    'scheduled_at' => $i->scheduled_at?->toDateTimeString(),
                    'attendance' => $i->attendance,
                    /*
                    | Its time has come and nobody has said yet whether it
                    | happened.
                    |
                    | Worked out on every read rather than stored, so it is
                    | never stale and no job has to sweep for it. It is not a
                    | fifth attendance value and it does not replace
                    | 'scheduled': the interview IS still scheduled, it is
                    | simply overdue an answer. Nothing is recorded as held
                    | because a clock passed.
                    */
                    'due' => InterviewScheduling::isDue($i),
                    'venue' => $i->venue,
                    'cancel_reason' => $i->cancel_reason,
                    /*
                    | The employee number as well as the name.
                    |
                    | Names alone were enough to say who is conducting an
                    | interview, but not to take one off it: the screen needs
                    | something to address the unassign call with, and names are
                    | not unique enough to be that. The name is still the one
                    | snapshotted when they were assigned, so the record reads
                    | correctly even after somebody changes department.
                    */
                    'interviewers' => $i->interviewers->map(fn ($a) => [
                        'empno' => $a->empno,
                        'name' => $a->interviewer_name,
                    ])->values()->all(),
                    'note_count' => $noteCounts[$i->id] ?? 0,
                    'evaluations' => $i->evaluations->whereNull('superseded_at')->map(fn ($e) => [
                        'id' => $e->id,
                        /* The number as well as the name: a screen has to know
                           which evaluation belongs to the person reading it,
                           and names are not unique enough to decide that. */
                        'evaluator_empno' => $e->evaluator_empno,
                        'evaluator_name' => $e->evaluator_name,
                        'outcome' => $e->outcome,
                        'recommendation' => $e->recommendation,
                        'remarks' => $e->remarks,
                        'submitted' => $e->submitted_at !== null,
                        'revision' => $e->revision,
                    ])->values()->all(),
                ])->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * What HR should do next on this application, in one sentence.
     *
     * The page shows state; this says what to DO with it. Somebody new to the
     * process should not have to read every round and infer the next move from
     * which chips are missing.
     *
     * Returns null when nothing is outstanding — which is itself worth saying.
     *
     * @return array{step_id:int, label:string, action:string, sentence:string}|null
     */
    public static function nextAction(int $applicationId): ?array
    {
        $rounds = self::summary($applicationId);

        foreach ($rounds as $round) {
            if ($round['state'] !== ApplicationStep::STATE_PENDING) {
                continue;
            }

            $interviews = collect($round['interviews']);
            $live = $interviews->firstWhere('attendance', Interview::SCHEDULED);
            $held = $interviews->where('attendance', Interview::HELD);

            if ($live) {
                return [
                    'step_id' => $round['step_id'],
                    'label' => $round['display_label'],
                    'action' => 'held',
                    'sentence' => $round['display_label'] . ' is scheduled for '
                        . self::readableDate($live['scheduled_at'])
                        . '. Mark it held once it has taken place.',
                ];
            }

            if ($held->isNotEmpty()) {
                return [
                    'step_id' => $round['step_id'],
                    'label' => $round['display_label'],
                    'action' => 'complete',
                    'sentence' => $round['display_label'] . ' has taken place. Record what it concluded.',
                ];
            }

            return [
                'step_id' => $round['step_id'],
                'label' => $round['display_label'],
                'action' => 'arrange',
                'sentence' => $round['display_label'] . ' has not been arranged yet.',
            ];
        }

        return null;
    }

    private static function readableDate(?string $value): string
    {
        if (!$value) {
            return 'a date still to be set';
        }

        return \Illuminate\Support\Carbon::parse($value)->format('D j M, g:ia');
    }

    /* ------------------------------------------------------------------ */

    /**
     * The next unused interview_N for this application.
     *
     * Starts at 2, since the first interview round is the fixed Initial.
     * A number freed by removing a pending round is reused, which is safe
     * because a round that has history cannot be removed at all.
     */
    private static function nextOrdinalKey($steps): string
    {
        $used = $steps->pluck('stage_key')->all();

        for ($n = 2; $n < 100; $n++) {
            if (!in_array('interview_' . $n, $used, true)) {
                return 'interview_' . $n;
            }
        }

        throw ValidationException::withMessages([
            'step' => 'This application already has as many interview rounds as the system supports.',
        ]);
    }

    /**
     * Where a new round sits: immediately before the Final Interview.
     *
     * Placement is not a choice. An extra round happens BETWEEN the first
     * conversation and the last one — that is what makes it intermediate — and
     * letting a caller pass an arbitrary insertion point allowed a round to
     * land after Final, or before Initial, which is not a shorter or longer
     * process but a broken one.
     *
     * Spacing follows ApplicationWorkflow's convention: gaps of ten, and the
     * midpoint of the gap being filled. When the gap has closed, the pending
     * steps are spread out again rather than refusing the insert.
     */
    private static function positionBeforeFinal(int $applicationId, $steps): int
    {
        $ordered = $steps->sortBy('position')->values();
        $final = $ordered->firstWhere('stage_key', 'final');

        /* No Final on this application: the round goes at the end, which is
           where the last conversation would be anyway. */
        if (!$final) {
            return (int) $ordered->last()->position + ApplicationStep::POSITION_GAP;
        }

        $finalPosition = (int) $final->position;

        $before = $ordered->filter(fn ($s) => (int) $s->position < $finalPosition)
            ->sortBy('position')->last();

        /* Final is first — nothing precedes it — so halve its position. */
        if (!$before) {
            if ($finalPosition <= 1) {
                self::respace($applicationId);
                return self::positionBeforeFinal($applicationId, ApplicationStep::forApplication($applicationId)->get());
            }

            return (int) floor($finalPosition / 2);
        }

        $beforePosition = (int) $before->position;

        if ($finalPosition - $beforePosition > 1) {
            return (int) floor(($beforePosition + $finalPosition) / 2);
        }

        self::respace($applicationId);

        return self::positionBeforeFinal($applicationId, ApplicationStep::forApplication($applicationId)->get());
    }

    /** Spread positions back out to multiples of the gap, order preserved. */
    private static function respace(int $applicationId): void
    {
        $position = 0;

        foreach (ApplicationStep::forApplication($applicationId)->get() as $step) {
            $position += ApplicationStep::POSITION_GAP;
            $step->update(['position' => $position]);
        }
    }

    private static function stepOrFail(int $applicationId, int $stepId): ApplicationStep
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
}
