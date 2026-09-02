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

    private function enrich($app)
    {
        $app->applicant_name = trim(
            $app->app_fname . ' ' .
            ($app->app_mname ? substr($app->app_mname, 0, 1) . '. ' : '') .
            $app->app_lname
        );

        $posting = DB::connection('mysql')->table('tbl_job_posting')
            ->where('id', $app->job_posting_id)
            ->first();
        $app->posting_title = $posting->posting_title ?? '—';

        $position = DB::connection('hrd2')->table('tbl_manpower_request_position as pos')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
            ->select('r.mr_no')
            ->where('pos.id', $app->request_position_id)
            ->first();
        $app->mr_no = $position->mr_no ?? '—';

        return $app;
    }

    /**
     * JSON data source for the Applicant Intake DataTable.
     * One entry per applicant (app_id), each carrying its own
     * applications array for the row.child() expansion.
     */
    public function data()
    {
        $applications = $this->baseQuery()
            ->orderByDesc('a.applied_at')
            ->get()
            ->map(fn ($app) => $this->enrich($app));

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

    public function counts()
    {
        $counts = $this->baseQuery()
            ->selectRaw('a.status, count(*) as total')
            ->groupBy('a.status')
            ->pluck('total', 'status');

        return response()->json([
            'Applied' => $counts['Applied'] ?? 0,
            'total' => $counts->sum(),
        ]);
    }
}