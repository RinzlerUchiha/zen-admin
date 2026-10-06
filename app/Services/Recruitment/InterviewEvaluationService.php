<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\Interview;
use App\Models\Applicant\InterviewAssignment;
use App\Models\Applicant\InterviewEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * What interviewers concluded, and how they correct it.
 *
 * Each interviewer writes their own evaluation of an interview they attended.
 * The evaluations are independent: two people may reach opposite conclusions
 * and both stand as written. Nothing here averages, scores or ranks them —
 * HR reads them and decides the round's result separately.
 *
 * An outcome is about THIS round — whether the candidate should go on. It is
 * not a hiring decision, which belongs to a later phase and to people with the
 * authority to make it.
 *
 * A draft belongs to its author and can be reworked freely. A submitted
 * evaluation is final: to change what stands, the author files a correction,
 * which is a new record superseding the old one. The original is kept, because
 * an interviewer changing their mind is part of the record rather than a
 * mistake to be tidied away.
 */
class InterviewEvaluationService
{
    /**
     * Start or revise a draft.
     *
     * Only someone assigned to the interview may write one: being HR is not
     * enough, the account has to come from someone who was in the room.
     */
    public static function saveDraft(int $applicationId, int $interviewId, string $empno, array $data): InterviewEvaluation
    {
        $interview = self::evaluableInterviewOrFail($applicationId, $interviewId);
        $name = self::assignedNameOrFail($interview, $empno);

        self::guardOutcome($data['outcome'] ?? null, false);

        $existing = self::currentFor($interview->id, $empno);

        if ($existing && $existing->isLocked()) {
            throw ValidationException::withMessages([
                'evaluation' => 'Your evaluation has already been submitted. File a correction to change it.',
            ]);
        }

        if ($existing) {
            $existing->update([
                'outcome' => $data['outcome'] ?? $existing->outcome,
                'recommendation' => $data['recommendation'] ?? $existing->recommendation,
                'remarks' => $data['remarks'] ?? $existing->remarks,
            ]);

            return $existing->fresh();
        }

        return InterviewEvaluation::create([
            'interview_id' => $interview->id,
            'evaluator_empno' => $empno,
            'evaluator_name' => $name,
            'outcome' => $data['outcome'] ?? null,
            'recommendation' => $data['recommendation'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'revision' => 1,
        ]);
    }

    /**
     * Submit an evaluation, after which it cannot be edited.
     *
     * An outcome is required at this point: a submitted evaluation with no
     * conclusion tells the next reader nothing, and the draft exists precisely
     * so an interviewer can think about it first.
     */
    public static function submit(int $applicationId, int $interviewId, string $empno, array $data): InterviewEvaluation
    {
        $interview = self::evaluableInterviewOrFail($applicationId, $interviewId);
        $name = self::assignedNameOrFail($interview, $empno);

        self::guardOutcome($data['outcome'] ?? null, true);

        $existing = self::currentFor($interview->id, $empno);

        if ($existing && $existing->isLocked()) {
            throw ValidationException::withMessages([
                'evaluation' => 'You have already submitted an evaluation for this interview. File a correction to change it.',
            ]);
        }

        if ($existing) {
            $existing->update([
                'outcome' => $data['outcome'],
                'recommendation' => $data['recommendation'] ?? $existing->recommendation,
                'remarks' => $data['remarks'] ?? $existing->remarks,
                'submitted_at' => now(),
            ]);

            return $existing->fresh();
        }

        return InterviewEvaluation::create([
            'interview_id' => $interview->id,
            'evaluator_empno' => $empno,
            'evaluator_name' => $name,
            'outcome' => $data['outcome'],
            'recommendation' => $data['recommendation'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'submitted_at' => now(),
            'revision' => 1,
        ]);
    }

    /**
     * Replace a submitted evaluation with a corrected one.
     *
     * One transaction, and submitted immediately: there is no draft stage for a
     * correction, because the original has to stay in force until the moment
     * the correction replaces it, and two live evaluations from one person
     * cannot exist.
     *
     * The original is never edited or removed. It is marked superseded and kept
     * for good.
     */
    public static function correct(int $applicationId, int $evaluationId, string $empno, array $data): InterviewEvaluation
    {
        $original = InterviewEvaluation::find($evaluationId);

        if (!$original) {
            throw ValidationException::withMessages([
                'evaluation' => 'That evaluation could not be found.',
            ]);
        }

        /* Ownership, through the interview and its step. */
        $interview = self::evaluableInterviewOrFail($applicationId, (int) $original->interview_id);

        if ($original->evaluator_empno !== $empno) {
            throw ValidationException::withMessages([
                'evaluation' => 'Only the interviewer who wrote an evaluation may correct it.',
            ]);
        }

        /*
        | Authorship is not enough: they must still be conducting it.
        |
        | A correction writes a new revision, so it is an evaluation action like
        | any other, and the rule for those is assignment. Without this, somebody
        | taken off an interview could go on rewriting their conclusion about a
        | candidate they are no longer responsible for — the one thing removing
        | them was meant to stop.
        |
        | What it does NOT do is touch the record. Their existing evaluation and
        | every earlier revision stay exactly as they are, still attributed to
        | them, still readable by anyone whose permissions allow it.
        */
        if (!InterviewAssignment::isAssigned($interview->id, $empno)) {
            throw ValidationException::withMessages([
                'evaluation' => 'You are no longer conducting this interview, so it cannot be corrected. '
                    . 'Your evaluation stays on the record as it is.',
            ]);
        }

        if ($original->isDraft()) {
            throw ValidationException::withMessages([
                'evaluation' => 'That evaluation has not been submitted yet, so edit it rather than correcting it.',
            ]);
        }

        if ($original->isSuperseded()) {
            throw ValidationException::withMessages([
                'evaluation' => 'That evaluation has already been corrected. Correct the current one instead.',
            ]);
        }

        $reason = trim((string) ($data['correction_reason'] ?? ''));

        if ($reason === '') {
            throw ValidationException::withMessages([
                'correction_reason' => 'Please say why you are correcting this evaluation.',
            ]);
        }

        self::guardOutcome($data['outcome'] ?? null, true);

        return DB::connection('applicant')->transaction(function () use ($original, $interview, $empno, $data, $reason) {
            /* Superseded first, so the correction can take the one effective
               slot within the same transaction. */
            $original->update(['superseded_at' => now()]);

            return InterviewEvaluation::create([
                'interview_id' => $interview->id,
                'evaluator_empno' => $empno,
                'evaluator_name' => $original->evaluator_name,
                'outcome' => $data['outcome'],
                'recommendation' => $data['recommendation'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'submitted_at' => now(),
                'revision' => (int) $original->revision + 1,
                'supersedes_id' => $original->id,
                'correction_reason' => $reason,
            ])->fresh();
        });
    }

    /**
     * The evaluations that currently stand for an interview.
     *
     * Read from the superseded flag rather than by sorting revisions: the
     * database guarantees one per interviewer, so this cannot quietly return
     * two versions of the same person's view.
     */
    public static function effectiveFor(int $applicationId, int $interviewId)
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        return InterviewEvaluation::forInterview($interview->id)
            ->effective()
            ->orderBy('evaluator_name')
            ->get();
    }

    /** Every revision one interviewer has written, oldest first. */
    public static function historyFor(int $applicationId, int $interviewId, string $empno)
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        return InterviewEvaluation::forInterview($interview->id)
            ->where('evaluator_empno', $empno)
            ->orderBy('revision')
            ->get();
    }

    /** Whether everyone assigned has submitted. Informational, never a gate. */
    public static function allSubmitted(int $applicationId, int $interviewId): bool
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        $assigned = InterviewAssignment::where('interview_id', $interview->id)->count();

        if ($assigned === 0) {
            return false;
        }

        $submitted = InterviewEvaluation::forInterview($interview->id)
            ->effective()->submitted()->count();

        return $submitted >= $assigned;
    }

    /* ------------------------------------------------------------------ */

    /** The evaluation currently occupying this interviewer's slot, if any. */
    private static function currentFor(int $interviewId, string $empno): ?InterviewEvaluation
    {
        return InterviewEvaluation::forInterview($interviewId)
            ->effective()
            ->where('evaluator_empno', $empno)
            ->first();
    }

    /** Only a held interview can be evaluated — there is nothing to judge otherwise. */
    private static function evaluableInterviewOrFail(int $applicationId, int $interviewId): Interview
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        if (!$interview->acceptsEvaluations()) {
            throw ValidationException::withMessages([
                'interview' => 'This interview is recorded as ' . $interview->attendance
                    . ', so there is nothing to evaluate.',
            ]);
        }

        return $interview;
    }

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

    /** The interviewer's name, if they are conducting this interview. */
    private static function assignedNameOrFail(Interview $interview, string $empno): string
    {
        $assignment = InterviewAssignment::where('interview_id', $interview->id)
            ->where('empno', $empno)
            ->first();

        if (!$assignment) {
            throw ValidationException::withMessages([
                'evaluation' => 'Only an interviewer assigned to this interview may evaluate it.',
            ]);
        }

        return $assignment->interviewer_name;
    }

    private static function guardOutcome(?string $outcome, bool $required): void
    {
        if ($outcome === null) {
            if ($required) {
                throw ValidationException::withMessages([
                    'outcome' => 'Please record whether this candidate should proceed.',
                ]);
            }

            return;
        }

        if (!in_array($outcome, InterviewEvaluation::OUTCOMES, true)) {
            throw ValidationException::withMessages([
                'outcome' => 'That is not a recognised interview outcome.',
            ]);
        }
    }
}
