<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\Interview;
use App\Models\Applicant\InterviewAssignment;
use Illuminate\Support\Facades\DB;

/**
 * Who may act as an interviewer, and on which interviews.
 *
 * Both questions are answered by the same records: the interviews HR has put
 * this employee on. Being asked to conduct an interview IS the authorization —
 * there is no separate step in which somebody is first declared an interviewer
 * and then given work.
 *
 *   workspace access   do they have ANY assignment at all?
 *   interview access   are they assigned to THIS one?
 *
 * The second is the one that matters, and it is checked on every request. The
 * first only decides whether the workspace is worth showing them: an employee
 * with no assignments has nothing to look at, and one whose last assignment is
 * taken away loses the page by the same act.
 *
 * Attendance deliberately plays no part. A cancelled or no-show interview is
 * still theirs to account for, and a held one still needs its evaluation —
 * often written afterwards, and sometimes corrected later still. Access that
 * expired when the appointment passed would lock people out of exactly the
 * work the workspace exists for.
 *
 * Revocation is unassignment. Immediate, with no second record to keep in step.
 *
 * Read-only: assigning and unassigning happen in InterviewScheduling.
 */
class InterviewerAccess
{
    /**
     * Does this employee have any interview assignment at all?
     *
     * Gates whether the workspace appears. It does NOT decide what they can
     * see inside it — that is per interview, below.
     *
     * Indexed on empno, so this stays cheap enough to ask on every request.
     */
    public static function hasAssignments(?string $empno): bool
    {
        if ($empno === null || trim($empno) === '') {
            return false;
        }

        return InterviewAssignment::where('empno', $empno)->exists();
    }

    /**
     * The interviews this employee is conducting.
     *
     * Derived from the assignment records, never from anything the caller
     * sends: a request may say who it claims to be, but what that person can
     * see is worked out here.
     *
     * @return array<int> interview ids
     */
    public static function assignedInterviewIds(?string $empno): array
    {
        if ($empno === null || trim($empno) === '') {
            return [];
        }

        return InterviewAssignment::where('empno', $empno)
            ->pluck('interview_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * One interview, if this employee may see it.
     *
     * The single gate every interviewer request passes through. Null means
     * "not yours", which is all the caller needs to know — whether the
     * interview exists at all is not their business.
     */
    public static function interviewFor(?string $empno, int $interviewId): ?Interview
    {
        if ($empno === null || trim($empno) === '') {
            return null;
        }

        if (!InterviewAssignment::isAssigned($interviewId, (string) $empno)) {
            return null;
        }

        return Interview::find($interviewId);
    }

    /**
     * The application an interview belongs to, if this employee may see it.
     *
     * The existing interview services all take an application id alongside the
     * record id. Resolving it here — from the interview the employee is proven
     * to be on — means the interviewer API can reuse those services without
     * ever letting the caller choose which application it is working in.
     */
    public static function applicationIdFor(?string $empno, int $interviewId): ?int
    {
        $interview = self::interviewFor($empno, $interviewId);

        if (!$interview) {
            return null;
        }

        $applicationId = DB::connection('applicant')
            ->table('tblapp_application_steps')
            ->where('id', $interview->step_id)
            ->value('application_id');

        return $applicationId ? (int) $applicationId : null;
    }
}
