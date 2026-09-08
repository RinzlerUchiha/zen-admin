<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ApplicantIntakeController extends Controller
{
    public function index()
    {
        return view('pages.recruitment', [
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

        // 5 & 6. One query on the hrd2 (HireFlow) connection, keyed by position id.
        // Aliased in the SELECT so pluck() never has to guess at a qualified name.
        $positionMrNos = $positionIds->isEmpty()
            ? collect()
            : DB::connection('hrd2')->table('tbl_manpower_request_position as pos')
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
    public function data()
    {
        $applications = $this->enrichAll(
            $this->baseQuery()
                ->orderByDesc('a.applied_at')
                ->get()
        );

        $grouped = $applications->groupBy('app_id')->map(function ($apps) {
            $first = $apps->first();

            return [
                'app_id' => $first->app_id,
                'applicant_name' => $first->applicant_name,
                'app_email' => $first->app_email,
                'app_mobile' => $first->app_mobile,
                'application_count' => $apps->count(),
                'latest_applied_at' => $apps->max('applied_at'),
                'applications' => $apps->map(fn ($a) => [
                    'id' => $a->id,
                    'app_id' => $a->app_id,
                    'posting_title' => $a->posting_title,
                    'mr_no' => $a->mr_no,
                    'status' => $a->status,
                    'applied_at' => $a->applied_at,
                ])->values(),
            ];
        })->values();

        return response()->json(['data' => $grouped]);
    }
}