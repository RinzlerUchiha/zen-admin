<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerRequest;
use App\Models\Recruitment\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ManpowerController extends Controller
{
    // URL slug => real HireFlow status value
    private const STATUS_MAP = [
        'draft'     => 'Draft',
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'update'    => 'Returned',
        'cancelled' => 'Cancelled',
        'declined'  => 'Rejected',
    ];

    /*
    | Not a status of its own: approved requests whose Requestor has asked
    | their Approver for permission to edit or cancel, and is still waiting.
    | It rides the same list/{stat} route as the status tabs so HR reaches it
    | the same way, but it filters on tbl_manpower_change_request instead.
    */
    private const CHANGE_PENDING = 'change-pending';

    /*
    | Also not a status of its own. HireFlow calls an approved request
    | "Served" once every position on it has been turned into a job posting;
    | Zen-Hub derives it the same way (mp_mark_served) and likewise leaves the
    | stored status as 'Approved'.
    |
    | Approved and Served are mutually exclusive, matching Zen-Hub: its
    | dashboard buckets a served request as 'Served' rather than its status,
    | and manpower_status_rows.php filters each tab on that flag. So the
    | Approved tab here means "approved, still waiting for its postings".
    */
    private const SERVED = 'served';

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'manpower',
            'maincat' => 'manpower',
            'page' => 'pages.recruitment.manpower.index-content',
            'user_empno' => $user->Emp_No,
        ]);
    }

    /**
     * ?month=YYYY-MM, as sent by the dashboard's "requests over time" chart.
     * Anything else is ignored rather than rejected: a stale or hand-typed
     * link should still open the page.
     */
    private function monthFilter(?string $month): ?string
    {
        return ($month !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) ? $month : null;
    }

    private function buildListQuery(string $status, $user, bool $changePendingOnly = false, ?string $month = null)
    {
        $canViewAll = Gate::forUser($user)->allows('manpower-requests.view-all');

        $query = HireflowManpowerRequest::with(['positions', 'pendingChange'])
            ->where('status', $status);

        // The Edit/Cancel tab: only approved requests with an open ask.
        if ($changePendingOnly) {
            $query->whereHas('pendingChange');
        }

        if ($month !== null) {
            $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);
        }

        if ($canViewAll) {
            return $query;
        }

        $assigned = check_assign($user->Emp_No, 'PR');

        return $query->whereRaw(
            "(FIND_IN_SET(requestor_employee_id, ?) > 0 OR requestor_employee_id = ?)",
            [$assigned, $user->Emp_No]
        );
    }

    /**
     * Position ids that already have a job posting, for the given positions.
     *
     * Two queries for the whole page rather than one per row: HireFlow marks
     * an approved request "Served" once every position on it has been turned
     * into a posting, and HR needs the same distinction here because posting
     * is their job. Read-only, and it derives from tbl_job_posting exactly as
     * the Job Postings page does, so the two always agree.
     */
    private function postedPositionIds(array $positionIds): array
    {
        if (empty($positionIds)) {
            return [];
        }

        return JobPosting::whereIn('request_position_id', $positionIds)
            ->pluck('request_position_id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();
    }

    /**
     * Marks each approved request with how many of its positions already have
     * a job posting, and whether that makes it Served.
     *
     * Matches Zen-Hub's mp_mark_served() exactly: a request is Served once AT
     * LEAST ONE of its positions has a posting row, whatever that posting's
     * own status. Hiring has started, so the request leaves Approved and is
     * locked from edit/cancel; the remaining positions still read as Not
     * posted individually.
     *
     * (Note this differs from JobPostingController's "eligible to post" rule,
     * which ignores Closed postings — that answers a different question and is
     * deliberately left alone.)
     */
    private function markPosted($rows)
    {
        $posted = $this->postedPositionIds(
            $rows->pluck('positions')->flatten()->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return $rows->each(function ($v) use ($posted) {
            $v->positions->each(function ($position) use ($posted) {
                $position->is_posted = isset($posted[(int) $position->id]);
            });

            $v->posted_count = $v->positions->where('is_posted', true)->count();
            $v->is_served = $v->status === 'Approved' && $v->posted_count > 0;
        });
    }

    /**
     * Requestor name and department, keyed by employee number.
     *
     * This used to fetch the WHOLE employee directory (1,296 rows with two
     * joins, ~115ms) on every tab click to resolve a handful of names. Same
     * lookup as HireFlow's own auth.php — tbl201_basicinfo on the hrd2
     * connection — now limited to the people actually on screen.
     */
    private function requestorDirectory(array $employeeIds)
    {
        if (empty($employeeIds)) {
            return collect();
        }

        return DB::connection('hrd2')->table('tbl201_basicinfo')
            ->selectRaw("bi_empno as pers_empno, Dept_Name, TRIM(CONCAT(bi_emplname, ', ', bi_empfname)) as empname")
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'bi_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->whereIn('bi_empno', $employeeIds)
            ->get()
            ->keyBy('pers_empno');
    }

    /**
     * Position code => full title, in one query instead of one per position.
     *
     * HireflowManpowerPosition::positionTitle() hits tbl_jobdescription each
     * time it is called; over a list that is a query per row. Same source,
     * same fallback to the raw code when a description is missing.
     */
    private function positionTitles($positions)
    {
        $codes = $positions->pluck('position')->filter()->unique()->values()->all();

        if (empty($codes)) {
            return collect();
        }

        return DB::connection('hrd2')->table('tbl_jobdescription')
            ->whereIn('jd_code', $codes)
            ->pluck('jd_title', 'jd_code');
    }

    public function list(Request $request, string $stat)
    {
        $changePendingOnly = $stat === self::CHANGE_PENDING;
        $servedOnly = $stat === self::SERVED;
        $approvedOnly = $stat === 'approved';

        if (!$changePendingOnly && !$servedOnly && !array_key_exists($stat, self::STATUS_MAP)) {
            abort(404);
        }

        // Both pseudo-tabs are views over approved requests.
        $status = ($changePendingOnly || $servedOnly) ? 'Approved' : self::STATUS_MAP[$stat];

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rows = $this->markPosted(
            $this->buildListQuery($status, $user, $changePendingOnly, $this->monthFilter($request->query('month')))
                ->orderBy('id', 'desc')
                ->get()
        );

        // The two tabs split the approved set between them.
        if ($servedOnly) {
            $rows = $rows->filter(fn ($v) => $v->is_served)->values();
        } elseif ($approvedOnly) {
            $rows = $rows->filter(fn ($v) => !$v->is_served)->values();
        }

        $employee = $this->requestorDirectory($rows->pluck('requestor_employee_id')->unique()->all());
        $titles = $this->positionTitles($rows->pluck('positions')->flatten());

        $data = $rows
            ->map(function ($v) use ($employee, $titles) {
                $requestor = $employee[$v->requestor_employee_id] ?? null;
                $v->requestor_name = $requestor?->empname ?? '—';
                $v->requestor_dept = $requestor?->Dept_Name ?? '—';
                $v->total_headcount = $v->positions->sum('headcount');
                $v->position_count = $v->positions->count();

                $v->positions->each(function ($position) use ($titles) {
                    $position->position_title = $titles[$position->position] ?? $position->position;
                });

                return $v;
            });

        return view('pages.recruitment.manpower.list', [
            'stat' => $stat,
            'data' => $data,
            'user_empno' => $user->Emp_No,
        ]);
    }

    public function counts(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $counts = [];
        $month = $this->monthFilter($request->query('month'));

        foreach (self::STATUS_MAP as $slug => $status) {
            $counts[$slug] = $this->buildListQuery($status, $user, false, $month)->count();
        }

        $counts[self::CHANGE_PENDING] = $this->buildListQuery('Approved', $user, true, $month)->count();

        $counts[self::SERVED] = $this->markPosted($this->buildListQuery('Approved', $user, false, $month)->get())
            ->filter(fn ($v) => $v->is_served)
            ->count();

        // Served requests are shown under Served, not Approved, so the badge
        // matches the list beneath it.
        $counts['approved'] = max(0, $counts['approved'] - $counts[self::SERVED]);

        return response()->json($counts);
    }

    public function show($id)
    {
        $data = HireflowManpowerRequest::with(['positions', 'pendingChange'])->findOrFail($id);

        $this->markPosted(collect([$data]));
        $data->total_headcount = $data->positions->sum('headcount');

        // Resolve each position's short code to its full title, matching
        // how HireFlow itself displays positions.
        $data->positions->each(function ($position) {
            $position->position_title = $position->positionTitle();
        });

        // Matches HireFlow's own auth.php lookup exactly: tbl201_basicinfo
        // on the hrd2 connection, not tbl201_persinfo on the default connection.
        $requestor = DB::connection('hrd2')->table('tbl201_basicinfo')
            ->selectRaw("bi_empno as pers_empno, Dept_Name, TRIM(CONCAT(bi_emplname, ', ', bi_empfname)) as empname")
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'bi_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->where('bi_empno', $data->requestor_employee_id)
            ->first();

        $data->requestor_name = $requestor?->empname ?? '—';
        $data->requestor_dept = $requestor?->Dept_Name ?? '—';

        return response()->json($data);
    }
}
