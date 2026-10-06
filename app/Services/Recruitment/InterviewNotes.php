<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\Interview;
use App\Models\Applicant\InterviewAssignment;
use App\Models\Applicant\InterviewNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Observations recorded against one interview.
 *
 * Deliberately lighter than evaluations. An evaluation is a conclusion, filed
 * once and locked so it cannot be quietly rewritten. A note is an observation,
 * and the people who need to record one are not only the interviewers:
 *
 *   "Candidate rang to say they will be fifteen minutes late."
 *   "Asked to move the schedule; offered Thursday."
 *   "Mentioned they are available from next month."
 *
 * HR coordinating the process has as much reason to write those down as anyone
 * who sat in the room, which is why adding a note is not restricted to the
 * assigned interviewers — but every note belongs to its author, so nobody can
 * overwrite anybody else's account.
 *
 * Notes hang off the INTERVIEW, which belongs to a workflow step, which belongs
 * to one application. A note written about one job can never appear under
 * another application belonging to the same person.
 */
class InterviewNotes
{
    /**
     * Add a note.
     *
     * Anyone who may act on the application, plus anyone conducting this
     * particular interview. An interviewer who is not otherwise an HR user
     * still needs somewhere to write down what they saw.
     */
    public static function add(int $applicationId, int $interviewId, string $empno, string $body, bool $canManage = false): InterviewNote
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        $body = trim($body);

        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'A note needs something in it.',
            ]);
        }

        $assignment = InterviewAssignment::where('interview_id', $interview->id)
            ->where('empno', $empno)
            ->first();

        if (!$canManage && !$assignment) {
            throw ValidationException::withMessages([
                'note' => 'Only HR or an interviewer on this interview may add a note.',
            ]);
        }

        return InterviewNote::create([
            'interview_id' => $interview->id,
            'author_empno' => $empno,
            /* Prefer the name recorded when they were assigned; otherwise read
               the employee records once and keep it. */
            'author_name' => $assignment->interviewer_name ?? self::employeeName($empno) ?? $empno,
            'body' => $body,
        ]);
    }

    /**
     * Revise a note.
     *
     * Only its author, and only the wording — a note is not a formal record
     * that needs a supersede chain to change, but a reader has to be able to
     * see that it was changed after it was written, so the time is kept.
     */
    public static function edit(int $applicationId, int $noteId, string $empno, string $body): InterviewNote
    {
        $note = InterviewNote::find($noteId);

        if (!$note) {
            throw ValidationException::withMessages(['note' => 'That note could not be found.']);
        }

        /* Ownership, through the interview and its step. */
        self::interviewOrFail($applicationId, (int) $note->interview_id);

        if ($note->author_empno !== $empno) {
            throw ValidationException::withMessages([
                'note' => 'Only the person who wrote a note may change it.',
            ]);
        }

        $body = trim($body);

        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'A note needs something in it.',
            ]);
        }

        $note->update(['body' => $body, 'edited_at' => now()]);

        return $note->fresh();
    }

    /**
     * The thread for one interview.
     *
     * Readable by anyone who may read the interview: a note is part of the
     * recruitment record, not private commentary.
     */
    public static function forInterview(int $applicationId, int $interviewId)
    {
        $interview = self::interviewOrFail($applicationId, $interviewId);

        $assigned = InterviewAssignment::where('interview_id', $interview->id)
            ->pluck('empno')->all();

        return InterviewNote::forInterview($interview->id)->get()->map(fn ($n) => [
            'id' => $n->id,
            'author_empno' => $n->author_empno,
            'author_name' => $n->author_name,
            /* Who was writing: someone who sat in, or someone coordinating.
               Derived, so it stays true if assignments change. */
            'author_role' => in_array($n->author_empno, $assigned, true) ? 'interviewer' : 'hr',
            'body' => $n->body,
            'created_at' => $n->created_at?->toDateTimeString(),
            'edited_at' => $n->edited_at?->toDateTimeString(),
        ])->values();
    }

    /** How many notes each of these interviews carries, for the summary line. */
    public static function countsFor($interviewIds): array
    {
        $ids = collect($interviewIds)->filter()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return InterviewNote::whereIn('interview_id', $ids)
            ->select('interview_id', DB::raw('COUNT(*) as n'))
            ->groupBy('interview_id')
            ->pluck('n', 'interview_id')
            ->all();
    }

    /* ------------------------------------------------------------------ */

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
