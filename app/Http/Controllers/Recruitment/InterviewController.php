<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\ApplicationWorkflow;
use App\Services\Recruitment\AssessmentResults;
use App\Services\Recruitment\InterviewEvaluationService;
use App\Services\Recruitment\InterviewNotes;
use App\Services\Recruitment\InterviewScheduling;
use App\Services\Recruitment\InterviewWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HR's interview workspace for ONE application.
 *
 * Every route carries the application in its path, and every service call takes
 * it alongside the record id. An interview id arriving from a request is just a
 * number; without that pairing, HR looking at one application could act on an
 * interview belonging to another — which is exactly what the legacy screen does
 * by keying on the applicant.
 *
 * Thin by design. The rules about what may happen to an interview live in the
 * services, where they are tested once and cannot be bypassed by reaching the
 * route directly. This layer validates the SHAPE of a request and nothing else.
 *
 * Authorization is on the routes: reading behind applicant-interviews.view,
 * everything that changes something behind applicant-applications.decide. The
 * further rule that only an assigned interviewer may evaluate is enforced in
 * the service, because it depends on the record rather than on the user alone.
 */
class InterviewController extends Controller
{
    /* ----------------------------------------------------------- reading */

    /**
     * The workspace for one application's interviews.
     *
     * A page per APPLICATION, not per applicant: someone interviewing for two
     * roles has two separate processes, and showing them together is the
     * failing of the screen this replaces.
     */
    public function workspace($application)
    {
        $app = ApplicantApplication::find((int) $application);

        if (!$app) {
            abort(404);
        }

        $applicant = $app->applicant;
        $posting = DB::table('tbl_job_posting')->where('id', $app->job_posting_id)->first(['posting_title']);

        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'interviews',
            'maincat' => 'interviews',
            'page' => 'pages.recruitment.interviews.index-content',
            'application' => $app,
            'applicantName' => trim(($applicant->app_fname ?? '') . ' ' . ($applicant->app_lname ?? '')),
            'applicantId' => $app->app_id,
            'postingTitle' => $posting->posting_title ?? 'Posting no longer available',
        ]);
    }

    /** The application's interview rounds and what has happened on each. */
    public function data($application)
    {
        return response()->json([
            'rounds' => InterviewWorkflow::summary((int) $application),
            /* The one line a new HR user needs before reading anything else. */
            'next_action' => InterviewWorkflow::nextAction((int) $application),
            /*
            | The Exams round reads the existing assessment system and changes
            | nothing in it. Results belong to the APPLICANT — one DISC profile
            | per person — while the requirement to have sat them belongs to
            | this application, which is why they are fetched by app_id here and
            | shown read-only.
            */
            'assessments' => AssessmentResults::summaries((int) ApplicantApplication::find((int) $application)->app_id)
                ->map(fn ($a) => [
                    'key' => $a->key,
                    'label' => $a->label,
                    'status' => $a->status,
                    'hasResult' => $a->hasResult,
                    'takenOn' => $a->takenOn?->format('M d, Y'),
                ])->values(),
        ]);
    }

    /** The evaluations that currently stand for one interview. */
    public function evaluations($application, $interview)
    {
        return response()->json([
            'evaluations' => InterviewEvaluationService::effectiveFor((int) $application, (int) $interview)
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'evaluator_empno' => $e->evaluator_empno,
                    'evaluator_name' => $e->evaluator_name,
                    'outcome' => $e->outcome,
                    'recommendation' => $e->recommendation,
                    'remarks' => $e->remarks,
                    'submitted_at' => $e->submitted_at?->toDateTimeString(),
                    'revision' => $e->revision,
                    'is_draft' => $e->isDraft(),
                    'is_correction' => $e->isCorrection(),
                ])->values(),
            'all_submitted' => InterviewEvaluationService::allSubmitted((int) $application, (int) $interview),
        ]);
    }

    /**
     * Every revision one interviewer has written for an interview.
     *
     * Corrections do not replace what came before, so the earlier versions are
     * readable here — the point of keeping them.
     */
    public function evaluationHistory($application, $interview, $empno)
    {
        return response()->json([
            'history' => InterviewEvaluationService::historyFor((int) $application, (int) $interview, $empno)
                ->map(fn ($e) => [
                    'revision' => $e->revision,
                    'outcome' => $e->outcome,
                    'recommendation' => $e->recommendation,
                    'remarks' => $e->remarks,
                    'submitted_at' => $e->submitted_at?->toDateTimeString(),
                    'superseded_at' => $e->superseded_at?->toDateTimeString(),
                    'correction_reason' => $e->correction_reason,
                ])->values(),
        ]);
    }

    /* --------------------------------------------------------------- notes */

    /** The note thread for one interview. Readable by anyone who may read it. */
    public function notes($application, $interview)
    {
        return response()->json([
            'notes' => InterviewNotes::forInterview((int) $application, (int) $interview),
        ]);
    }

    /**
     * Add a note.
     *
     * HR coordinating the process may write one even if they did not conduct
     * the interview; the service also admits the assigned interviewers, who may
     * not otherwise have the right to act on the application.
     */
    public function addNote(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        return response()->json([
            'note' => InterviewNotes::add(
                (int) $application,
                (int) $interview,
                $this->empno(),
                $validated['body'],
                \Illuminate\Support\Facades\Gate::allows('applicant-applications.decide')
            ),
        ], 201);
    }

    /** Revise a note. Its author only; the service enforces that. */
    public function editNote(Request $request, $application, $note)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        return response()->json([
            'note' => InterviewNotes::edit(
                (int) $application,
                (int) $note,
                $this->empno(),
                $validated['body']
            ),
        ]);
    }

    /* ------------------------------------------------- arranging an interview */

    /** Arrange an interview for one round. */
    public function store(Request $request, $application, $step)
    {
        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer',
            'venue' => 'nullable|string|max:150',
            'meeting_link' => 'nullable|string|max:500',
        ]);

        $interview = InterviewScheduling::create(
            (int) $application,
            (int) $step,
            $validated,
            $this->empno()
        );

        return response()->json(['interview' => $interview], 201);
    }

    /** Set or change the arrangements for an upcoming interview. */
    public function schedule(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer',
            'venue' => 'nullable|string|max:150',
            'meeting_link' => 'nullable|string|max:500',
        ]);

        return response()->json([
            'interview' => InterviewScheduling::schedule(
                (int) $application,
                (int) $interview,
                $validated,
                $this->empno()
            ),
        ]);
    }

    /**
     * Move an interview to a new date.
     *
     * The old record is kept and cancelled; this returns the new one.
     */
    public function reschedule(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer',
            'venue' => 'nullable|string|max:150',
            'meeting_link' => 'nullable|string|max:500',
            'copy_interviewers' => 'nullable|boolean',
        ]);

        return response()->json([
            'interview' => InterviewScheduling::reschedule(
                (int) $application,
                (int) $interview,
                $validated,
                $this->empno(),
                $request->boolean('copy_interviewers', true)
            ),
        ], 201);
    }

    /* ----------------------------------------------------- who conducts it */

    public function assignInterviewer(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'empno' => 'required|string|max:20',
        ]);

        return response()->json([
            'interviewer' => InterviewScheduling::assignInterviewer(
                (int) $application,
                (int) $interview,
                $validated['empno'],
                $this->empno()
            ),
        ], 201);
    }

    public function unassignInterviewer($application, $interview, $empno)
    {
        InterviewScheduling::unassignInterviewer((int) $application, (int) $interview, $empno);

        return response()->json(['removed' => true]);
    }

    /* --------------------------------------------------------- what happened */

    public function markHeld($application, $interview)
    {
        return response()->json([
            'interview' => InterviewScheduling::markHeld((int) $application, (int) $interview, $this->empno()),
        ]);
    }

    /** Cancelling requires a reason; the service enforces it too. */
    public function cancel(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:300',
        ]);

        return response()->json([
            'interview' => InterviewScheduling::cancel(
                (int) $application,
                (int) $interview,
                $validated['reason'],
                $this->empno()
            ),
        ]);
    }

    public function markNoShow($application, $interview)
    {
        return response()->json([
            'interview' => InterviewScheduling::markNoShow((int) $application, (int) $interview, $this->empno()),
        ]);
    }

    /* ------------------------------------------------------------ evaluating */

    /** Save a draft. Only an assigned interviewer may; the service checks. */
    public function saveDraft(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'outcome' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        return response()->json([
            'evaluation' => InterviewEvaluationService::saveDraft(
                (int) $application,
                (int) $interview,
                $this->empno(),
                $validated
            ),
        ]);
    }

    /** Submit, after which the evaluation cannot be edited. */
    public function submitEvaluation(Request $request, $application, $interview)
    {
        $validated = $request->validate([
            'outcome' => 'required|string',
            'recommendation' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        return response()->json([
            'evaluation' => InterviewEvaluationService::submit(
                (int) $application,
                (int) $interview,
                $this->empno(),
                $validated
            ),
        ], 201);
    }

    /**
     * File a correction.
     *
     * Submitted immediately and irreversibly, which is why the interface asks
     * for confirmation before calling this.
     */
    public function correctEvaluation(Request $request, $application, $evaluation)
    {
        $validated = $request->validate([
            'outcome' => 'required|string',
            'recommendation' => 'nullable|string',
            'remarks' => 'nullable|string',
            'correction_reason' => 'required|string|max:300',
        ]);

        return response()->json([
            'evaluation' => InterviewEvaluationService::correct(
                (int) $application,
                (int) $evaluation,
                $this->empno(),
                $validated
            ),
        ], 201);
    }

    /* -------------------------------------------------------- rounds and stage */

    /**
     * Add another interview round.
     *
     * Takes nothing: the number and the placement both follow from the
     * application's existing rounds, so there is no label to supply and no
     * insertion point to choose.
     */
    public function addStep(Request $request, $application)
    {
        return response()->json([
            'step' => InterviewWorkflow::addInterviewStep((int) $application, $this->empno()),
        ], 201);
    }

    /** Remove a round, if nothing has happened on it. Refused otherwise. */
    public function removeStep($application, $step)
    {
        InterviewWorkflow::removeInterviewStep((int) $application, (int) $step);

        return response()->json(['removed' => true]);
    }

    /**
     * Record the result of a round.
     *
     * HR's decision, informed by the evaluations but not computed from them.
     * It does not move the application: that is a separate, explicit act.
     */
    public function completeStep(Request $request, $application, $step)
    {
        $validated = $request->validate([
            'result' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            /* Optional here: HR may settle the round and record its outcome
               in one act, or complete it now and decide after reading the
               evaluations. What is never done is assuming one. */
            'outcome' => 'nullable|in:passed,failed',
        ]);

        return response()->json([
            'step' => InterviewWorkflow::completeStep(
                (int) $application,
                (int) $step,
                $validated,
                $this->empno()
            ),
        ]);
    }

    /**
     * Record or correct the official outcome of a completed round.
     *
     * This is the gate the assessments read: only a recorded 'passed' opens
     * them. An unrecorded outcome opens nothing, which is why this exists as
     * its own act rather than being inferred from completion.
     */
    public function recordOutcome(Request $request, $application, $step)
    {
        $validated = $request->validate([
            'outcome' => 'required|in:passed,failed',
            'reason' => 'nullable|string|max:300',
        ]);

        return response()->json([
            'step' => ApplicationWorkflow::recordOutcome(
                (int) $application,
                (int) $step,
                $validated['outcome'],
                $validated['reason'] ?? null,
                $this->empno()
            ),
        ]);
    }

    /** Move the application to its next outstanding round. */
    public function advance(Request $request, $application)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:200',
        ]);

        return response()->json([
            'moved_to' => InterviewWorkflow::advance(
                (int) $application,
                $validated['reason'] ?? null,
                $this->empno()
            ),
        ]);
    }

    private function empno(): string
    {
        return (string) auth()->user()->Emp_No;
    }
}
