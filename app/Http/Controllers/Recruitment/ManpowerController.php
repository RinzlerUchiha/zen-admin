<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    private function buildListQuery(string $status, $user)
    {
        $canViewAll = $user->userAccess('personnelreq', 'viewall');

        $query = HireflowManpowerRequest::with('positions')->where('status', $status);

        if ($canViewAll) {
            return $query;
        }

        $assigned = check_assign($user->Emp_No, 'PR');

        return $query->whereRaw(
            "(FIND_IN_SET(requestor_employee_id, ?) > 0 OR requestor_employee_id = ?)",
            [$assigned, $user->Emp_No]
        );
    }

    public function list(string $stat)
    {
        if (!array_key_exists($stat, self::STATUS_MAP)) {
            abort(404);
        }

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

        $data = $this->buildListQuery(self::STATUS_MAP[$stat], $user)
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

    public function counts()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $counts = [];

        foreach (self::STATUS_MAP as $slug => $status) {
            $counts[$slug] = $this->buildListQuery($status, $user)->count();
        }

        return response()->json($counts);
    }

    public function show($id)
    {
        $data = HireflowManpowerRequest::with('positions')->findOrFail($id);

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
