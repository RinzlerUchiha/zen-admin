<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicationStep;
use App\Models\Applicant\Interview;
use App\Models\Applicant\InterviewAssignment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Arranging and conducting interviews.
 *
 * An interview belongs to a workflow step, and the step belongs to one
 * application. Every operation here takes the application id as well as the
 * record id and checks the two agree before touching anything: an id arriving
 * from a request is just a number, and without that check HR looking at one
 * application could reschedule an interview belonging to another.
 *
 * This service owns WHETHER AND WHEN an interview happens. It never touches
 * the workflow step's own state, the application's stage, or its disposition —
 * a cancelled interview does not cancel the Initial Interview, it cancels one
 * attempt at it.
 */
class InterviewScheduling
{
    /**
     * Create an interview for a workflow step.
     *
     * The date is optional: HR may record that an interview is going to happen
     * before the diary is settled. What is not optional is the step being an
     * interview step on this application and still open — an interview for a
     * round already concluded is a contradiction, not an edge case.
     */
    public static function create(int $applicationId, int $stepId, array $details, string $empno): Interview
    {
        $step = self::interviewStepOrFail($applicationId, $stepId);

        if ($step->state === ApplicationStep::STATE_COMPLETED) {
            throw ValidationException::withMessages([
                'step' => 'That round is already completed, so no further interview can be arranged for it.',
            ]);
        }

        $scheduledAt = $details['scheduled_at'] ?? null;

        self::guardScheduleDetails($details);

        try {
            return Interview::create([
                'step_id' => $step->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $details['duration_minutes'] ?? null,
                'venue' => $details['venue'] ?? null,
                'meeting_link' => $details['meeting_link'] ?? null,
                'attendance' => Interview::SCHEDULED,
                'created_by' => $empno,
            ]);
        } catch (QueryException $e) {
            /*
            | The database allows one upcoming interview per step. Catching it
            | here turns an integrity error into something HR can act on, and
            | the check stays in the database where a race cannot slip past it.
            */
            throw self::translateLiveClash($e, $step);
        }
    }

    /**
     * Set or change the arrangements for an upcoming interview.
     *
     * Only an upcoming interview can be arranged. Once it has been held,
     * cancelled or missed, its details describe something that already
     * happened and editing them would be rewriting the record.
     */
    public static function schedule(int $applicationId, int $interviewId, array $details, ?string $empno = null): Interview
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        self::guardNotTerminal($interview, 'rearranged');
        self::guardScheduleDetails($details);

        $interview->update(array_filter([
            'scheduled_at' => $details['scheduled_at'] ?? null,
            'duration_minutes' => $details['duration_minutes'] ?? null,
            'venue' => $details['venue'] ?? null,
            'meeting_link' => $details['meeting_link'] ?? null,
        ], fn ($value) => $value !== null));

        return $interview->fresh();
    }

    /**
     * Put an interviewer on an interview.
     *
     * The name is read from the employee directory once and stored alongside
     * the number, so the record still says who conducted the interview after
     * they have left and their directory entry has gone.
     */
    public static function assignInterviewer(int $applicationId, int $interviewId, string $interviewerEmpno, string $assignedBy): InterviewAssignment
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        /*
        | An interview that HAPPENED may still gain an interviewer: HR marks it
        | held, then realises a second person sat in and should record their own
        | view. Refusing would leave that account with nowhere to go, since only
        | an assigned interviewer may evaluate.
        |
        | Cancelled and missed interviews are different — nobody conducted them,
        | so there is nothing for a new assignee to evaluate.
        */
        if (in_array($interview->attendance, [Interview::CANCELLED, Interview::NO_SHOW], true)) {
            throw ValidationException::withMessages([
                'interviewer' => 'This interview is recorded as ' . $interview->attendance
                    . ', so there is nothing for another interviewer to conduct.',
            ]);
        }

        $name = self::employeeName($interviewerEmpno);

        if ($name === null) {
            throw ValidationException::withMessages([
                'interviewer' => 'That employee number does not match anyone in the employee records.',
            ]);
        }

        if (InterviewAssignment::isAssigned($interview->id, $interviewerEmpno)) {
            throw ValidationException::withMessages([
                'interviewer' => $name . ' is already conducting this interview.',
            ]);
        }

        return InterviewAssignment::create([
            'interview_id' => $interview->id,
            'empno' => $interviewerEmpno,
            'interviewer_name' => $name,
            'assigned_by' => $assignedBy,
        ]);
    }

    /**
     * Take an interviewer off an interview.
     *
     * Refused once they have recorded anything: an evaluation is their account
     * of an interview they attended, and removing the assignment would leave it
     * belonging to nobody.
     */
    public static function unassignInterviewer(int $applicationId, int $interviewId, string $interviewerEmpno): void
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        $assignment = InterviewAssignment::where('interview_id', $interview->id)
            ->where('empno', $interviewerEmpno)
            ->first();

        if (!$assignment) {
            throw ValidationException::withMessages([
                'interviewer' => 'That person is not conducting this interview.',
            ]);
        }

        /*
        | Having evaluated does NOT prevent removal.
        |
        | This used to refuse, on the reasoning that an evaluation needs its
        | assignment. It does not: an evaluation carries evaluator_empno and
        | evaluator_name of its own, as a note carries its author, so both stand
        | up without the assignment row and the history stays readable and
        | attributable after it goes.
        |
        | Refusing also got the real case backwards. Somebody put on an
        | interview in error, or moved off it, is exactly the person who most
        | needs taking off — and once they had filed anything, HR could not.
        |
        | What removal ends is their part in THIS interview: no access to it,
        | and no new evaluation for it, which InterviewEvaluationService checks
        | against this same table. Every other interview they are on, and
        | everything they already wrote, is untouched.
        */
        $assignment->delete();
    }

    /** The interview took place. Evaluations may be recorded against it from now on. */
    public static function markHeld(int $applicationId, int $interviewId, ?string $empno = null): Interview
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        self::guardAttendanceActor($interview, $empno);
        self::guardNotTerminal($interview, 'marked as held');

        /*
        | An interview that never had a date cannot be reported as having
        | happened: there would be no answer to "when?", and the record would
        | be unusable for every report that asks it.
        */
        if ($interview->scheduled_at === null) {
            throw ValidationException::withMessages([
                'interview' => 'This interview has no date yet, so it cannot be recorded as held.',
            ]);
        }

        $interview->update([
            'attendance' => Interview::HELD,
            'held_at' => now(),
        ]);

        return $interview->fresh();
    }

    /**
     * The interview was called off.
     *
     * A reason is required. A cancellation with no explanation is a gap in the
     * candidate's record that nobody can account for later.
     */
    public static function cancel(int $applicationId, int $interviewId, string $reason, ?string $empno = null): Interview
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        self::guardNotTerminal($interview, 'cancelled');

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Please say why this interview is being cancelled.',
            ]);
        }

        $interview->update([
            'attendance' => Interview::CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $empno,
            'cancel_reason' => trim($reason),
        ]);

        return $interview->fresh();
    }

    /**
     * The candidate did not attend.
     *
     * No reason required — the absence is the fact, and HR often does not know
     * why. Kept permanently: someone who misses two interviews has a history
     * worth seeing, and deleting the attempt would hide it.
     */
    public static function markNoShow(int $applicationId, int $interviewId, ?string $empno = null): Interview
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        self::guardAttendanceActor($interview, $empno);
        self::guardNotTerminal($interview, 'recorded as a no-show');

        $interview->update(['attendance' => Interview::NO_SHOW]);

        return $interview->fresh();
    }

    /**
     * Move an interview to a new date.
     *
     * The old record is never edited. It is cancelled, and a new interview is
     * created pointing back at it, so the chain shows every attempt that was
     * made rather than only the one that finally happened.
     *
     * Interviewers carry over by default, since the usual reason for moving an
     * interview is the diary rather than the panel.
     */
    public static function reschedule(int $applicationId, int $interviewId, array $details, string $empno, bool $copyInterviewers = true): Interview
    {
        $original = self::interviewOrFail($applicationId, $interviewId);

        self::guardNotTerminal($original, 'rescheduled');
        self::guardScheduleDetails($details);

        return DB::connection('applicant')->transaction(function () use ($original, $details, $empno, $copyInterviewers) {
            /* Freed first, so the new interview can take the step's one live
               slot in the same transaction. */
            $original->update([
                'attendance' => Interview::CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $empno,
                'cancel_reason' => 'rescheduled',
            ]);

            $replacement = Interview::create([
                'step_id' => $original->step_id,
                'scheduled_at' => $details['scheduled_at'] ?? $original->scheduled_at,
                'duration_minutes' => $details['duration_minutes'] ?? $original->duration_minutes,
                'venue' => $details['venue'] ?? $original->venue,
                'meeting_link' => $details['meeting_link'] ?? $original->meeting_link,
                'attendance' => Interview::SCHEDULED,
                'rescheduled_from_id' => $original->id,
                'created_by' => $empno,
            ]);

            if ($copyInterviewers) {
                foreach (InterviewAssignment::where('interview_id', $original->id)->get() as $assignment) {
                    InterviewAssignment::create([
                        'interview_id' => $replacement->id,
                        'empno' => $assignment->empno,
                        'interviewer_name' => $assignment->interviewer_name,
                        'assigned_by' => $empno,
                    ]);
                }
            }

            return $replacement->fresh();
        });
    }

    /** Every interview for one step, oldest first. */
    /**
     * Who may say whether an interview happened.
     *
     * Either the interviewer assigned to it -- who was in the room and is the
     * only person who actually knows -- or an HR user with the decide right,
     * who has always been able to and still can.
     *
     * This is the service-level check, and it is the one that counts. The
     * route now sits behind applicant-interviews.view, which is the right to
     * READ this interview; without this, that right would also have become
     * the right to record an outcome on anybody's interview.
     */
    private static function guardAttendanceActor(Interview $interview, ?string $empno): void
    {
        $empno = trim((string) $empno);

        /*
        | Assigned to THIS interview is the first and commonest way in.
        |
        | Asked before the gate, because it needs no user record at all: an
        | interviewer is identified by the assignment itself.
        */
        if ($empno !== '' && InterviewAssignment::where('interview_id', $interview->id)
                ->where('empno', $empno)->exists()) {
            return;
        }

        /*
        | Otherwise the decide right -- asked about the ACTOR, not about
        | whoever happens to be logged in.
        |
        | Gate::allows() reads the ambient session user, which is right inside
        | a request and meaningless outside one. The service is handed the
        | empno of the person doing this, and that is who should be
        | authorized. Asking the session instead made the answer depend on
        | context rather than on the actor: with nobody logged in it refused a
        | genuine HR empno, and with an HR user logged in it would have
        | authorized an action recorded against somebody else entirely.
        |
        | The ambient user is still consulted when no actor is named, which is
        | how every existing request path already behaves.
        */
        if ($empno !== '') {
            $actor = \App\Models\User::where('Emp_No', $empno)->first();
            $allowed = $actor
                && \Illuminate\Support\Facades\Gate::forUser($actor)->allows('applicant-applications.decide');
        } else {
            $allowed = \Illuminate\Support\Facades\Gate::allows('applicant-applications.decide');
        }

        if (!$allowed) {
            throw ValidationException::withMessages([
                'interview' => 'Only an interviewer assigned to this interview, or HR, may record what happened at it.',
            ]);
        }
    }

    /**
     * Is this interview due -- its time has come and nobody has said yet
     * whether it happened?
     *
     * DERIVED, never stored. A scheduled time passing is not evidence that an
     * interview took place: the candidate may not have come, the panel may
     * have been called away, the room may have been double-booked. So nothing
     * is written, no status changes, and "held" still means somebody said so.
     *
     * What it removes is the pretence that an interview scheduled for last
     * Tuesday is still merely "scheduled". It is waiting to be confirmed, and
     * saying that is the whole point.
     *
     * Only a live interview can be due. A cancelled or superseded one has
     * already reached its end, and a held or no-show one has been answered.
     */
    public static function isDue(Interview $interview): bool
    {
        return $interview->attendance === Interview::SCHEDULED
            && $interview->scheduled_at !== null
            && $interview->scheduled_at->isPast();
    }

    public static function forStep(int $applicationId, int $stepId)
    {
        self::interviewStepOrFail($applicationId, $stepId);

        return Interview::where('step_id', $stepId)
            ->orderBy('created_at')
            ->with(['interviewers', 'evaluations'])
            ->get();
    }

    /** Every interview across one application's workflow. */
    public static function forApplication(int $applicationId)
    {
        return Interview::forApplication($applicationId)
            ->orderBy('created_at')
            ->with(['interviewers', 'evaluations'])
            ->get();
    }

    /* ------------------------------------------------------------------ */

    /**
     * The step, if it is an interview step on this application.
     *
     * Exams and admin steps are part of the workflow but are not interviews;
     * arranging one for them would produce a record nobody could interpret.
     */
    private static function interviewStepOrFail(int $applicationId, int $stepId): ApplicationStep
    {
        $step = ApplicationStep::where('id', $stepId)
            ->where('application_id', $applicationId)
            ->first();

        if (!$step) {
            throw ValidationException::withMessages([
                'step' => 'That step is not part of this application.',
            ]);
        }

        if ($step->kind !== 'interview') {
            throw ValidationException::withMessages([
                'step' => $step->label . ' is not an interview, so an interview cannot be arranged for it.',
            ]);
        }

        return $step;
    }

    /**
     * The interview, if it belongs to this application.
     *
     * Reached through the step, which is the only route an interview has to an
     * application — there is no applicant key to take a shortcut through.
     */
    private static function interviewOrFail(int $applicationId, int $interviewId): Interview
    {
        $interview = Interview::where('id', $interviewId)
            ->whereIn('step_id', function ($sub) use ($applicationId) {
                $sub->select('id')->from('tblapp_application_steps')
                    ->where('application_id', $applicationId);
            })
            ->first();

        if (!$interview) {
            throw ValidationException::withMessages([
                'interview' => 'That interview is not part of this application.',
            ]);
        }

        return $interview;
    }

    /**
     * Refuse to change an interview that has already concluded.
     *
     * Held, cancelled and missed are statements about the past. The way to
     * change what happens next is a new interview, not an edit to the old one.
     */
    private static function guardNotTerminal(Interview $interview, string $action): void
    {
        if ($interview->isTerminal()) {
            throw ValidationException::withMessages([
                'interview' => 'This interview is already recorded as ' . $interview->attendance
                    . ', so it cannot be ' . $action . '. Arrange a new one instead.',
            ]);
        }
    }

    /** A date in the past is almost always a typo at the moment of booking. */
    private static function guardScheduleDetails(array $details): void
    {
        if (isset($details['duration_minutes']) && (int) $details['duration_minutes'] < 1) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'The duration must be at least one minute.',
            ]);
        }

        if (isset($details['meeting_link']) && trim((string) $details['meeting_link']) !== ''
            && !filter_var($details['meeting_link'], FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([
                'meeting_link' => 'That does not look like a valid meeting link.',
            ]);
        }
    }

    /** Turn the one-live-interview integrity error into HR's language. */
    private static function translateLiveClash(QueryException $e, ApplicationStep $step): ValidationException
    {
        if (str_contains($e->getMessage(), 'tblapp_interviews_one_live_unique')
            || str_contains($e->getMessage(), '1062')) {
            return ValidationException::withMessages([
                'interview' => $step->label . ' already has an interview arranged. '
                    . 'Reschedule or cancel it before arranging another.',
            ]);
        }

        throw $e;
    }

    /**
     * An employee's name from the HR records, which this system only reads.
     *
     * Null when the number matches nobody, which is how an interviewer who does
     * not exist is refused.
     */
    private static function employeeName(string $empno): ?string
    {
        $row = DB::connection('hrd2')->table('tbl201_basicinfo')
            ->where('bi_empno', $empno)
            ->first(['bi_empfname', 'bi_empmname', 'bi_emplname']);

        if (!$row) {
            return null;
        }

        $middle = $row->bi_empmname ? substr($row->bi_empmname, 0, 1) . '.' : '';

        return trim(preg_replace('/\s+/', ' ', "{$row->bi_empfname} {$middle} {$row->bi_emplname}"));
    }
}
