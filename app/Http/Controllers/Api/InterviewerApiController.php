<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicationStep;
use App\Services\Recruitment\InterviewEvaluationService;
use App\Services\Recruitment\InterviewerAccess;
use App\Services\Recruitment\InterviewNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * What an interviewer in Zen-Hub can reach.
 *
 * A thin bridge. Every rule about notes and evaluations — who may write one,
 * when a submitted evaluation locks, how a correction supersedes it — lives in
 * the services this calls, which HR's own screens use too. Reimplementing any
 * of it here is how the two surfaces would quietly start disagreeing.
 *
 * The identity comes from the signed request and nothing else. The client
 * sends an interview id; this resolves whether that interview is actually
 * assigned to that employee, and derives the application from the interview
 * rather than accepting one. So an interviewer who edits an id in a request
 * reaches nothing: not another interviewer's interview, not its application,
 * not its notes or evaluations.
 *
 * Read and write only what an interviewer owns. No scheduling, no assigning,
 * no completing a round, no advancing an application — those stay with HR.
 */
class InterviewerApiController extends Controller
{
    /**
     * Who this request is acting for, and whether they may act at all.
     *
     * Zen-Hub asks this to decide whether to show the interviewer menu.
     *
     * Reaching this at all means the signature verified and the employee has at
     * least one interview assigned, so the answer is always yes; the count
     * tells Zen-Hub whether there is anything waiting.
     */
    public function me(Request $request)
    {
        $empno = $this->empno($request);

        return response()->json([
            'empno' => $empno,
            /* True by the time we are here: the middleware refuses anyone
               without an assignment, and an assignment is what makes someone an
               interviewer. Kept in the response because Zen-Hub reads it. */
            'is_interviewer' => true,
            'assigned_count' => count(InterviewerAccess::assignedInterviewIds($empno)),
        ]);
    }

    /** The interviews this employee is conducting. */
    public function index(Request $request)
    {
        $empno = $this->empno($request);
        $ids = InterviewerAccess::assignedInterviewIds($empno);

        if (empty($ids)) {
            return response()->json(['interviews' => []]);
        }

        $interviews = DB::connection('applicant')->table('tblapp_interviews as i')
            ->join('tblapp_application_steps as s', 's.id', '=', 'i.step_id')
            ->join('tblapp_applications as a', 'a.id', '=', 's.application_id')
            ->join('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->whereIn('i.id', $ids)
            ->orderByDesc('i.scheduled_at')
            ->get([
                'i.id', 'i.scheduled_at', 'i.attendance', 'i.venue', 'i.meeting_link',
                's.label as round',
                'a.job_posting_id',
                'p.app_fname', 'p.app_lname',
            ]);

        /* Posting title and the manpower request it came from, so an
           interviewer can tell which vacancy they are interviewing for. */
        $postings = DB::table('tbl_job_posting as p')
            ->leftJoin('tbl_manpower_request_position as rp', 'rp.id', '=', 'p.request_position_id')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'rp.request_id')
            ->whereIn('p.id', $interviews->pluck('job_posting_id')->filter()->unique()->all())
            ->get(['p.id', 'p.posting_title', 'r.mr_no'])
            ->keyBy('id');

        /*
        | Whether THIS interviewer has filed their own evaluation.
        |
        | Their own outstanding work is the question the list exists to answer,
        | and it is not visible from the schedule. Scoped to this employee: one
        | interviewer's list never reveals whether anybody else has submitted.
        */
        $submitted = DB::connection('applicant')->table('tblapp_interview_evaluations')
            ->whereIn('interview_id', $ids)
            ->where('evaluator_empno', $empno)
            ->whereNull('superseded_at')
            ->whereNotNull('submitted_at')
            ->pluck('interview_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return response()->json([
            'interviews' => $interviews->map(fn ($i) => [
                'id' => (int) $i->id,
                'applicant' => trim($i->app_fname . ' ' . $i->app_lname),
                'position' => $postings[$i->job_posting_id]->posting_title ?? 'Posting no longer available',
                'mr_no' => $postings[$i->job_posting_id]->mr_no ?? null,
                'round' => $i->round,
                'scheduled_at' => $i->scheduled_at,
                'attendance' => $i->attendance,
                'venue' => $i->venue,
                'meeting_link' => $i->meeting_link,
                'my_evaluation_submitted' => in_array((int) $i->id, $submitted, true),
                /* Deliberately NOT the application id. An interviewer has no
                   use for it, and not sending it is one less thing that could
                   be sent back and trusted. */
            ])->values(),
        ]);
    }

    /** One interview, with the context needed to conduct it. */
    public function show(Request $request, $interview)
    {
        $empno = $this->empno($request);
        $record = InterviewerAccess::interviewFor($empno, (int) $interview);

        if (!$record) {
            return $this->notYours();
        }

        $step = ApplicationStep::find($record->step_id);
        $application = DB::connection('applicant')->table('tblapp_applications')
            ->where('id', $step->application_id)->first();
        $applicant = DB::connection('applicant')->table('tblapp_persinfo')
            ->where('app_id', $application->app_id)->first();
        $posting = DB::table('tbl_job_posting as p')
            ->leftJoin('tbl_manpower_request_position as rp', 'rp.id', '=', 'p.request_position_id')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'rp.request_id')
            ->where('p.id', $application->job_posting_id)
            ->first(['p.posting_title', 'r.mr_no']);

        /* Who else is conducting it — names only. Enough to know whether to
           wait for a colleague; nothing about what they concluded, which is
           theirs. */
        $others = DB::connection('applicant')->table('tblapp_interview_interviewers')
            ->where('interview_id', $record->id)
            ->where('empno', '!=', $empno)
            ->pluck('interviewer_name')
            ->all();

        return response()->json([
            'interview' => [
                'id' => (int) $record->id,
                'round' => $step->label,
                'scheduled_at' => $record->scheduled_at?->toDateTimeString(),
                'attendance' => $record->attendance,
                'venue' => $record->venue,
                'meeting_link' => $record->meeting_link,
                'applicant' => trim(($applicant->app_fname ?? '') . ' ' . ($applicant->app_lname ?? '')),
                'position' => $posting->posting_title ?? 'Posting no longer available',
                'mr_no' => $posting->mr_no ?? null,
                'applied_at' => $application->applied_at,
                'other_interviewers' => $others,
                'can_evaluate' => $record->acceptsEvaluations(),
            ],
        ]);
    }

    /* --------------------------------------------------------------- notes */

    public function notes(Request $request, $interview)
    {
        $scope = $this->scopeFor($request, (int) $interview);

        if (!$scope) {
            return $this->notYours();
        }

        return response()->json([
            'notes' => InterviewNotes::forInterview($scope['application_id'], $scope['interview_id']),
        ]);
    }

    public function addNote(Request $request, $interview)
    {
        $scope = $this->scopeFor($request, (int) $interview);

        if (!$scope) {
            return $this->notYours();
        }

        $validated = $request->validate(['body' => 'required|string|max:5000']);

        /* Not passed as a manager: an interviewer writes as someone who was in
           the room, and the service checks they are assigned. */
        return response()->json([
            'note' => InterviewNotes::add(
                $scope['application_id'],
                $scope['interview_id'],
                $scope['empno'],
                $validated['body'],
                false
            ),
        ], 201);
    }

    /**
     * Revise one's own note.
     *
     * The note's interview is looked up first and checked against this
     * employee's assignments, so a note id belonging to somebody else's
     * interview is refused before the service is reached — and the service
     * refuses again on authorship.
     */
    public function editNote(Request $request, $note)
    {
        $empno = $this->empno($request);

        $interviewId = DB::connection('applicant')->table('tblapp_interview_notes')
            ->where('id', (int) $note)->value('interview_id');

        if (!$interviewId) {
            return $this->notYours();
        }

        $scope = $this->scopeFor($request, (int) $interviewId);

        if (!$scope) {
            return $this->notYours();
        }

        $validated = $request->validate(['body' => 'required|string|max:5000']);

        return response()->json([
            'note' => InterviewNotes::edit($scope['application_id'], (int) $note, $empno, $validated['body']),
        ]);
    }

    /* ---------------------------------------------------------- evaluation */

    /** This interviewer's own evaluation and its revisions. */
    public function evaluation(Request $request, $interview)
    {
        $scope = $this->scopeFor($request, (int) $interview);

        if (!$scope) {
            return $this->notYours();
        }

        $mine = InterviewEvaluationService::effectiveFor($scope['application_id'], $scope['interview_id'])
            ->firstWhere('evaluator_empno', $scope['empno']);

        return response()->json([
            'evaluation' => $mine ? [
                'id' => $mine->id,
                'outcome' => $mine->outcome,
                'recommendation' => $mine->recommendation,
                'remarks' => $mine->remarks,
                'submitted_at' => $mine->submitted_at?->toDateTimeString(),
                'revision' => $mine->revision,
                'is_draft' => $mine->isDraft(),
            ] : null,
            'history' => InterviewEvaluationService::historyFor(
                $scope['application_id'], $scope['interview_id'], $scope['empno']
            )->map(fn ($e) => [
                'revision' => $e->revision,
                'outcome' => $e->outcome,
                'submitted_at' => $e->submitted_at?->toDateTimeString(),
                'superseded_at' => $e->superseded_at?->toDateTimeString(),
                'correction_reason' => $e->correction_reason,
            ])->values(),
        ]);
    }

    /** Save a draft, or submit. */
    public function saveEvaluation(Request $request, $interview)
    {
        $scope = $this->scopeFor($request, (int) $interview);

        if (!$scope) {
            return $this->notYours();
        }

        $validated = $request->validate([
            'outcome' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'remarks' => 'nullable|string',
            'submit' => 'nullable|boolean',
        ]);

        $evaluation = $request->boolean('submit')
            ? InterviewEvaluationService::submit($scope['application_id'], $scope['interview_id'], $scope['empno'], $validated)
            : InterviewEvaluationService::saveDraft($scope['application_id'], $scope['interview_id'], $scope['empno'], $validated);

        return response()->json(['evaluation' => $evaluation], $request->boolean('submit') ? 201 : 200);
    }

    /**
     * Correct one's own submitted evaluation.
     *
     * Same scoping as a note: the evaluation's interview must be one this
     * employee is on, and the service then enforces that only its author may
     * correct it, that the original is preserved, and that the chain stays
     * linear.
     */
    public function correctEvaluation(Request $request, $evaluation)
    {
        $interviewId = DB::connection('applicant')->table('tblapp_interview_evaluations')
            ->where('id', (int) $evaluation)->value('interview_id');

        if (!$interviewId) {
            return $this->notYours();
        }

        $scope = $this->scopeFor($request, (int) $interviewId);

        if (!$scope) {
            return $this->notYours();
        }

        $validated = $request->validate([
            'outcome' => 'required|string',
            'recommendation' => 'nullable|string',
            'remarks' => 'nullable|string',
            'correction_reason' => 'required|string|max:300',
        ]);

        return response()->json([
            'evaluation' => InterviewEvaluationService::correct(
                $scope['application_id'], (int) $evaluation, $scope['empno'], $validated
            ),
        ], 201);
    }

    /* ------------------------------------------------------------------ */

    /**
     * The employee this request is acting for.
     *
     * Set by the signature middleware from the signed header. Never read from
     * the body, the query or the route, because those are the caller's to
     * choose.
     */
    private function empno(Request $request): string
    {
        return (string) $request->attributes->get('hireflow_empno');
    }

    /**
     * Resolve an interview id into something safe to act on.
     *
     * The single gate every interviewer operation passes through. It returns
     * the application id DERIVED from the interview — never one the caller
     * supplied — so the existing services can be reused exactly as HR's
     * screens use them, without the caller ever choosing which application it
     * is working in.
     *
     * Null means "not yours", which is all a caller needs to know.
     *
     * @return array{empno:string, interview_id:int, application_id:int}|null
     */
    private function scopeFor(Request $request, int $interviewId): ?array
    {
        $empno = $this->empno($request);
        $applicationId = InterviewerAccess::applicationIdFor($empno, $interviewId);

        if (!$applicationId) {
            return null;
        }

        return [
            'empno' => $empno,
            'interview_id' => $interviewId,
            'application_id' => $applicationId,
        ];
    }

    /**
     * One refusal for everything out of reach.
     *
     * Not found rather than forbidden, and identical whether the record is
     * someone else's or does not exist: distinguishing them would let a caller
     * map out which interviews exist by watching the status code change.
     */
    private function notYours()
    {
        return response()->json(['message' => 'Not found.'], 404);
    }
}
