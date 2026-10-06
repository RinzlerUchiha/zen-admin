<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\AcquiredApplicantIntake;
use App\Services\Recruitment\ApplicationRecommendationService;
use App\Services\Recruitment\ApplicantDocumentReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ApplicantIntakeController extends Controller
{
    public function index()
    {
        return view('pages.recruitment', [
            // Applicants asking for a new assessment access code.
            'accessRequests' => \Illuminate\Support\Facades\Gate::allows('applicant-assessments.issue-access')
                ? \App\Services\Recruitment\AssessmentAccessCodes::openRequests()
                : collect(),
            'main_link' => 'recruitment',
            'sub_link' => 'applicant-intake',
            'maincat' => 'applicant-intake',
            'page' => 'pages.recruitment.applicant-intake.index-content',
        ]);
    }

    private function baseQuery()
    {
        return DB::connection('applicant')->table('tblapp_applications as a')
            ->leftJoin('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->select(
                'a.id',
                'a.app_id',
                'a.request_position_id',
                'a.job_posting_id',
                'a.status',
                'a.applied_at',
                'a.closed_at',
                'a.closed_by',
                'a.closed_note',
                'p.app_fname',
                'p.app_mname',
                'p.app_lname',
                'p.app_email',
                'p.app_mobile'
            );
    }

    /**
     * Batched replacement for the old per-row enrich().
     *
     * Resolves posting_title and mr_no for the whole application set using
     * exactly two lookup queries (one per foreign connection) regardless of
     * how many applications are returned, instead of two queries per row.
     *
     * Output per application is identical to the previous implementation,
     * including the '—' fallback when the FK is null or points at a row
     * that no longer exists.
     */
    private function enrichAll($applications)
    {
        // 1 & 4. Distinct, non-null FK values. filter() drops null/0/'' —
        // those rows fall through to the '—' fallback exactly as before.
        $postingIds  = $applications->pluck('job_posting_id')->filter()->unique()->values();
        $positionIds = $applications->pluck('request_position_id')->filter()->unique()->values();

        // 2 & 3. One query on the zen-admin (default) connection, keyed by id.
        $postingTitles = $postingIds->isEmpty()
            ? collect()
            : DB::connection('mysql')->table('tbl_job_posting')
                ->whereIn('id', $postingIds)
                ->pluck('posting_title', 'id');

        // 5 & 6. One query on the portal_db (HireFlow) connection, keyed by position id.
        // Aliased in the SELECT so pluck() never has to guess at a qualified name.
        $positionMrNos = $positionIds->isEmpty()
            ? collect()
            : DB::connection('mysql')->table('tbl_manpower_request_position as pos')
                ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
                ->whereIn('pos.id', $positionIds)
                ->select('pos.id as position_id', 'r.mr_no')
                ->pluck('mr_no', 'position_id');

        // 7. Enrich from memory. No queries inside this loop.
        return $applications->map(function ($app) use ($postingTitles, $positionMrNos) {
            $app->applicant_name = trim(
                $app->app_fname . ' ' .
                ($app->app_mname ? substr($app->app_mname, 0, 1) . '. ' : '') .
                $app->app_lname
            );

            $app->posting_title = $postingTitles[$app->job_posting_id] ?? '—';
            $app->mr_no         = $positionMrNos[$app->request_position_id] ?? '—';

            return $app;
        });
    }

    /**
     * JSON data source for the Applicant Intake DataTable.
     * One entry per applicant (app_id), each carrying its own
     * applications array for the row.child() expansion.
     */
    /**
     * People who registered on the Applicants portal but have never applied.
     *
     * tblapp_persinfo IS the account (zen-applicants' User model points at it
     * with app_id as its key), so "registered, no application" is simply a
     * persinfo row with no application rows. Read over the existing applicant
     * connection — nothing is copied into portal_db.
     */
    private function accountsWithoutApplications()
    {
        return DB::connection('applicant')->table('tblapp_persinfo as p')
            ->leftJoin('tblapp_applications as a', 'a.app_id', '=', 'p.app_id')
            ->whereNull('a.id')
            ->select('p.app_id', 'p.app_fname', 'p.app_mname', 'p.app_lname',
                     'p.app_email', 'p.app_mobile', 'p.app_date')
            ->orderByDesc('p.app_date')
            ->get()
            ->map(fn ($p) => [
                'app_id' => $p->app_id,
                'applicant_name' => trim(preg_replace('/\s+/', ' ',
                    $p->app_fname . ' ' . ($p->app_mname ?? '') . ' ' . $p->app_lname)),
                'app_email' => $p->app_email,
                'app_mobile' => $p->app_mobile,
                'application_count' => 0,
                'latest_applied_at' => null,
                'registered_at' => $p->app_date,
                'documents' => null,
                'applications' => [],
            ]);
    }

    public function data()
    {
        $applications = $this->enrichAll(
            $this->baseQuery()
                ->orderByDesc('a.applied_at')
                ->get()
        );

        // Document completeness per applicant, for HR allowed to see documents.
        // Two queries for the whole list, not per row.
        $documentSummaries = Gate::allows('applicant-documents.view')
            ? ApplicantDocumentReview::summaries($applications->pluck('app_id'))
            : null;

        /*
        | How many recommendations each application has produced, and which
        | applications a recommendation created.
        |
        | One query each for the whole set, not one per row — the same batching
        | the rest of this screen uses.
        |
        | The count decides whether HR is offered the Recommended closure: that
        | status means "ended because the applicant went forward for another
        | post", which is only true if a recommendation actually happened.
        */
        $recommendationLinks = ApplicationRecommendationService::linksFor($applications->pluck('id'));

        $grouped = $applications->groupBy('app_id')->map(function ($apps) use ($documentSummaries, $recommendationLinks) {
            $first = $apps->first();
            $registeredAt = null;
            $docs = $documentSummaries[$first->app_id] ?? null;

            return [
                'documents' => $docs ? [
                    'accepted' => $docs['required_accepted'],
                    'required' => $docs['required_total'],
                    'complete' => $docs['complete'],
                    'pending' => $docs['pending'],
                    'rejected' => $docs['rejected'],
                    'open_requests' => $docs['open_requests'],
                ] : null,
                'app_id' => $first->app_id,
                'applicant_name' => $first->applicant_name,
                'app_email' => $first->app_email,
                'app_mobile' => $first->app_mobile,
                'application_count' => $apps->count(),
                'latest_applied_at' => $apps->max('applied_at'),
                'registered_at' => $registeredAt,
                'applications' => $apps->map(fn ($a) => [
                    'id' => $a->id,
                    'app_id' => $a->app_id,
                    'posting_title' => $a->posting_title,
                    'mr_no' => $a->mr_no,
                    'status' => $a->status,
                    'applied_at' => $a->applied_at,
                    // What the status means comes from config/applications.php
                    // through the model, so Intake and the portal agree (M3).
                    'is_closed' => (bool) (ApplicantApplication::statusDefinition($a->status)['closed'] ?? false),
                    'in_candidate_pool' => (bool) (ApplicantApplication::statusDefinition($a->status)['candidate_pool'] ?? false),
                    'closed_at' => $a->closed_at,
                    'closed_by' => $a->closed_by,
                    'closed_note' => $a->closed_note,
                    'reapply_on' => ApplicantApplication::cooldownEndsFor($a->status, $a->closed_at)?->toDateString(),

                    // Where this application came from, and what came of it.
                    // One hop each way: following it repeatedly walks a chain
                    // without holding the whole graph.
                    'recommended_from' => $recommendationLinks[$a->id]['from'] ?? null,
                    'recommended_to' => $recommendationLinks[$a->id]['to'] ?? [],
                    'recommended_count' => count($recommendationLinks[$a->id]['to'] ?? []),
                ])->values(),
            ];
        })->values();

        $rows = $grouped->concat($this->accountsWithoutApplications())
            ->sortByDesc(fn ($r) => $r['latest_applied_at'] ?? $r['registered_at'] ?? '')
            ->values();

        return response()->json(['data' => $rows]);
    }

    /**
     * The Candidate Pool: applications that closed without the candidate
     * progressing and are worth revisiting.
     *
     * Uses the existing per-application definition from
     * config/applications.php via ApplicantApplication — there is deliberately
     * no second pool mechanism, and pool membership is never a property of the
     * applicant as a whole.
     */
    /**
     * The postings HR can record a walk-in against.
     *
     * Published only — the same rule the careers portal applies. Entering
     * someone by hand is not a reason to attach them to a closed posting.
     */
    public function openPostings()
    {
        $postings = DB::connection('mysql')->table('tbl_job_posting as jp')
            ->leftJoin('tbl_manpower_request_position as pos', 'pos.id', '=', 'jp.request_position_id')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
            ->where('jp.status', 'Published')
            ->orderBy('jp.posting_title')
            ->get(['jp.id', 'jp.posting_title', 'r.mr_no']);

        return response()->json(['data' => $postings]);
    }

    /**
     * Record an applicant HR acquired in person.
     *
     * Not for walk-ins: those register themselves at the office PC through
     * the Careers Portal, and the bookmarked link's ?src=walk_in is what
     * records the office as their origin. This is for people who are not at a
     * keyboard — met at a job fair, referred by someone.
     *
     * Source, outcome and current work stay separate: a job-fair hire is
     * source=job_fair AND outcome=Hired AND still missing documents.
     */
    public function storeAcquired(Request $request)
    {
        $validated = $request->validate([
            'app_lname' => 'required|string|max:100',
            'app_fname' => 'required|string|max:100',
            'app_mname' => 'nullable|string|max:100',
            'app_email' => 'required_without:app_mobile|nullable|email|max:150',
            'app_mobile' => 'required_without:app_email|nullable|string|max:50',
            // HR sets this and tells the applicant, who signs in with it
            // later. Never generated here, never echoed back.
            'app_code' => 'required|string|min:8|max:100',
            'job_posting_id' => 'required|integer',
            'source_channel' => 'required|string|max:40',
            // Only meaningful for a job fair; the service ignores it otherwise.
            'outcome' => 'nullable|string|in:hired,not_selected,processing',
        ], [
            'app_email.required_without' => 'Give an email address or a mobile number — the applicant signs in with one of them.',
            'app_mobile.required_without' => 'Give an email address or a mobile number — the applicant signs in with one of them.',
            'app_code.min' => 'The sign-in code must be at least 8 characters.',
            'source_channel.required' => 'Choose where this applicant came from.',
        ]);

        $result = AcquiredApplicantIntake::create($validated, Auth::user()->Emp_No ?? null);

        return response()->json([
            'success' => true,
            'app_id' => $result['app_id'],
            'application_id' => $result['application_id'],
            'status' => $result['status'],
            'message' => $this->acquiredMessage($result),
        ]);
    }

    /** What HR needs to know happened, in one sentence. */
    private function acquiredMessage(array $result): string
    {
        $account = $result['reused_account']
            ? 'This person already had an account — the application was added to it.'
            : 'Account created.';

        return $account . ' ' . match ($result['status']) {
            // Said explicitly, because "hired" usually implies "finished" and
            // here it does not.
            'Hired' => 'Recorded as hired. The application stays open until '
                . 'requirements, any remaining exams and onboarding are done.',
            'Not Selected' => 'Closed as not selected and added to the Candidate Pool.',
            default => 'The application is open and follows the normal process.',
        };
    }

    /** The sources HR may record by hand, and whether each asks for an outcome. */
    public function intakeSources()
    {
        return response()->json(['data' => AcquiredApplicantIntake::sources()]);
    }

    public function candidatePool()
    {
        $pooled = ApplicantApplication::candidatePoolStatuses();

        $rows = $this->enrichAll(
            $this->baseQuery()->whereIn('a.status', $pooled)->orderByDesc('a.closed_at')->get()
        )->map(fn ($a) => [
            'app_id' => $a->app_id,
            'applicant_name' => $a->applicant_name,
            'app_email' => $a->app_email,
            'app_mobile' => $a->app_mobile,
            'posting_title' => $a->posting_title,
            'mr_no' => $a->mr_no,
            'status' => $a->status,
            'applied_at' => $a->applied_at,
            'closed_at' => $a->closed_at,
            'closed_note' => $a->closed_note,
            'reapply_on' => ApplicantApplication::cooldownEndsFor($a->status, $a->closed_at)?->toDateString(),
        ])->values();

        return response()->json(['data' => $rows]);
    }
}