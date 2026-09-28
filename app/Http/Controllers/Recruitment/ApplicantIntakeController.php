<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\ApplicantDocumentReview;
use Illuminate\Support\Facades\DB;
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

        $grouped = $applications->groupBy('app_id')->map(function ($apps) use ($documentSummaries) {
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