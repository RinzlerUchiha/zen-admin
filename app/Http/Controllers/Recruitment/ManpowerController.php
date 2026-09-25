<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerRequest;
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

    public function list(Request $request, string $stat)
    {
        $changePendingOnly = $stat === self::CHANGE_PENDING;

        if (!$changePendingOnly && !array_key_exists($stat, self::STATUS_MAP)) {
            abort(404);
        }

        // An open edit/cancel ask can only exist on an approved request.
        $status = $changePendingOnly ? 'Approved' : self::STATUS_MAP[$stat];

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Matches HireFlow's own auth.php lookup exactly: tbl201_basicinfo
        // on the hrd2 connection, not tbl201_persinfo on the default connection.
        $employee = DB::connection('hrd2')->table('tbl201_basicinfo')
            ->selectRaw("bi_empno as pers_empno, Dept_Name, TRIM(CONCAT(bi_emplname, ', ', bi_empfname)) as empname")
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'bi_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->get();

        $data = $this->buildListQuery($status, $user, $changePendingOnly, $this->monthFilter($request->query('month')))
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($v) use ($employee) {
                $requestor = $employee->where('pers_empno', $v->requestor_employee_id)->first();
                $v->requestor_name = $requestor?->empname ?? '—';
                $v->requestor_dept = $requestor?->Dept_Name ?? '—';
                $v->total_headcount = $v->positions->sum('headcount');
                $v->position_count = $v->positions->count();

                $v->positions->each(function ($position) {
                    $position->position_title = $position->positionTitle();
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

        return response()->json($counts);
    }

    public function show($id)
    {
        $data = HireflowManpowerRequest::with(['positions', 'pendingChange'])->findOrFail($id);

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
