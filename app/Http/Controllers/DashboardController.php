<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ManpowerRequest;
use App\Models\Memo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public static function getIR()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->userAccess('grievance', 'review')) {
            $data = DB::connection('hrd2')->select(
                "SELECT * 
                FROM tbl_ir 
                WHERE (ir_stat = 'needs explanation' OR ir_stat = 'posted') 
                OR (ir_stat = 'resolved' AND FIND_IN_SET(:empno, ir_read) = 0)
                ORDER BY
                    IF(FIND_IN_SET(:empno, ir_read) > 0, 1, 0) ASC,
                    ir_date DESC,
                    ir_id ASC",
                [':empno' => $user->Emp_No]
            );
        } else {
            $data = DB::connection('hrd2')->select(
                "SELECT a.*
                FROM tbl_ir a
                LEFT JOIN tbl_ir_forward b ON b.irf_irid = a.ir_id AND b.irf_to = :empno
                WHERE 
                    (
                        (
                            FIND_IN_SET(:empno, ir_from) > 0 
                            OR FIND_IN_SET(:empno, ir_to) > 0
                            OR (b.irf_irid != '' AND b.irf_irid IS NOT NULL)
                        )
                        AND (ir_stat = 'posted' OR ir_stat = 'needs explanation')
                    )
                    OR (
                        ir_stat != 'draft' 
                        AND ir_stat != 'resolved' 
                        AND FIND_IN_SET(:empno, ir_cc) > 0 
                        AND FIND_IN_SET(:empno, ir_read) = 0
                    )
                ORDER BY
                    IF(FIND_IN_SET(:empno, ir_read) > 0, 1, 0) ASC,
                    ir_date DESC,
                    ir_id ASC",
                [':empno' => $user->Emp_No]
            );
        }

        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = collect($data)->map(function ($d) use ($employees) {
            $d->empname = isset($employees[$d->ir_from]) ? trim($employees[$d->ir_from]['pers_lastname'] . ", " . $employees[$d->ir_from]['pers_firstname']) : '';
            return $d;
        });

        $arr['recent'] = $data->filter(function ($row) use ($user) {
            return strpos($row->ir_read, $user->Emp_No) === false;
        });

        $arr['unresolved_cnt'] = $data->filter(function ($row) use ($user) {
            return $row->ir_stat != 'resolved';
        })->count();

        return $arr;
    }

    public static function get13A()
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reviewer = $user->userAccess('grievance', 'review');
        // $reviewer = true;
        $query = DB::connection('hrd2')->table('tbl_13a AS a')
            ->leftJoin('tbl_13a_reply', '13ar_13aid', '=', 'a.13a_id')
            ->where('13a_stat', '!=', 'draft');

        if (!$reviewer) {
            $query->whereRaw("FIND_IN_SET(?, CONCAT_WS(',',13a_to, 13a_cc, 13a_from, 13a_issuedby, 13a_notedby)) > 0", [$user->Emp_No]);
        }

        $query->orderBy('13a_date', 'desc');

        $data = $query->get()->map(function ($item) use ($persinfo) {
            $item->from_name = isset($persinfo[$item->{'13a_from'}]) ? trim($persinfo[$item->{'13a_from'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_from'}]['pers_firstname']) : '';

            $item->to_name = isset($persinfo[$item->{'13a_to'}]) ? trim($persinfo[$item->{'13a_to'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_to'}]['pers_firstname']) : '';

            return $item;
        });

        $filteredData = $data->filter(function ($d) use ($user) {
            $d->status = (!empty($d->{'13ar_id'}) && strpos($d->{'13ar_read'}, $user->Emp_No) === false) ? "Replied" : $d->{'13a_stat'};

            return !in_array($d->{'13a_stat'}, ['draft', 'received', 'refused', 'cancelled']) ||
                (($d->{'13a_stat'} == "received" || $d->{'13a_stat'} == "refused" || $d->{'13a_stat'} == "cancelled") && strpos($d->{'13a_read'}, $user->Emp_No) === false) ||
                (!empty($d->{'13ar_id'}) && strpos($d->{'13ar_read'}, $user->Emp_No) === false);
        });

        return $filteredData;
    }

    public static function get13B()
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reviewer = $user->userAccess('grievance', 'review');
        // $reviewer = true;
        $query = DB::connection('hrd2')->table('tbl_13b AS a')
            ->where('13b_stat', '!=', 'draft');

        if (!$reviewer) {
            $query->whereRaw("FIND_IN_SET(?, CONCAT_WS(',',13b_to, 13b_cc, 13b_from, 13b_issuedby, 13b_notedby)) > 0", [$user->Emp_No]);
        }

        $query->select('a.*')
            ->orderBy('13b_date', 'desc');

        $data = $query->get()->map(function ($item) use ($persinfo) {
            $item->from_name = isset($persinfo[$item->{'13b_from'}]) ? trim($persinfo[$item->{'13b_from'}]['pers_lastname'] . ", " . $persinfo[$item->{'13b_from'}]['pers_firstname']) : '';

            $item->to_name = isset($persinfo[$item->{'13b_to'}]) ? trim($persinfo[$item->{'13b_to'}]['pers_lastname'] . ", " . $persinfo[$item->{'13b_to'}]['pers_firstname']) : '';

            return $item;
        });

        $filteredData = $data->filter(function ($d) use ($user) {

            return !in_array($d->{'13b_stat'}, ['draft', 'received', 'refused', 'cancelled']) ||
                (
                    ($d->{'13b_stat'} == "received" || $d->{'13b_stat'} == "refused" || $d->{'13b_stat'} == "cancelled") &&
                    strpos($d->{'13b_read'}, $user->Emp_No) === false
                );
        });

        return $filteredData;
    }

    public static function getClearance()
    {
        $request = DB::connection('hrd2')
            ->table('db_ecf2.tbl_request')
            ->where('ecf_status', 'pending')
            ->orderBy('ecf_lastday', 'desc')
            ->orderBy('ecf_name', 'asc')
            ->get();

        $signed = DB::connection('hrd2')
            ->table('db_ecf2.tbl_req_category')
            ->whereIn('catstat_ecfid', $request->pluck('ecf_id'))
            ->get();

        $request = $request->map(function ($r) use ($signed) {
            $cat_list = $signed->where('catstat_ecfid', $r->ecf_id);
            $r->cat_cnt = $cat_list->count();
            $r->cat_clr = $cat_list->where('catstat_stat', 'cleared')->count();
            return $r;
        });
        
        return view('pages.dashboard.clearance', ['list' => $request]);
    }

    public static function getExitInterview()
    {
        $emplist = DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->orderBy('ji_resdate', 'desc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->get();

        $clearance = DB::connection('hrd2')
            ->table('db_ecf2.tbl_request')
            ->where('ecf_status', 'pending')
            ->orderBy('ecf_lastday', 'desc')
            ->orderBy('ecf_name', 'asc')
            ->get();

        $exitInterviews = DB::connection('hrd2')
            ->table('tbl201_exit_intvw')
            ->selectRaw('MAX(xintvw_dtresign) AS latest_resdt, xintvw_empno')
            ->groupBy('xintvw_empno')
            ->get();

        // return view('pages.dashboard.exit-interview', ['list' => $emplist]);

        $exitList = $emplist->filter(function ($r) use ($clearance) {
            $clr = $clearance->where('ecf_empno', $r->pers_empno)->first();
            return (($r->ji_remarks == 'Inactive' && $r->ji_resdate >= date('Y-m-d')) || $clr?->ecf_lastday >= date('Y-m-d'));
        })
            ->map(function ($r) use ($exitInterviews) {
                $r->exitInterview = $exitInterviews->where('xintvw_empno', $r->pers_empno)->where('latest_resdt', '>=', $r->ji_datehired)->first()?->latest_resdt;
                return $r;
            });

        return [
            'forInterview' => $exitList->where('exitInterview', '=', null)->count(),
            'exitList' => $exitList->count()
        ];
    }

    public static function getManpowerRequest()
    {
        $employee = DB::table('tbl201_persinfo')
            ->selectRaw("pers_empno, Dept_Name, TRIM(CONCAT(pers_lastname, ', ', pers_firstname)) as empname")
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->orderBy('Dept_Name', 'asc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->get();

        $query = ManpowerRequest::leftJoin('tbl_mpupdate', function($join){
            $join->on('mpu_mpid', '=', 'mp_id')
                ->whereRaw("(mpu_stat='pending' OR mpu_stat='approved')");
        })
        ->where('mp_status', 'approved')
        ->orWhereRaw("(mp_status = 'approved' AND mpu_id IS NOT NULL)")
        ->orderBy('mpu_id', 'desc')
        ->orderBy('mp_dtprepared', 'desc')
        ->get();

        $data = [];
        foreach ($query as $v) {
            $v->empname = $employee->where('pers_empno', $v->mp_requestby)->first()?->empname;

            $v->mp_progress = explode(',', $v->mp_progress ?? '');

            $v->mpu_req = ucwords($v->mpu_req);

            if(!isset($v->mp_progress[0])){
                $v->mp_progress[0] = '0%';
            }

            if(!isset($v->mp_progress[1])){
                $v->mp_progress = [$v->mp_progress[0], ''];
            }

            if(preg_match('/^(\d+)\/(\d+)$/', ($v->mp_progress[1] ?? ''), $matches) && (int)$matches[1] >= (int)$matches[2]){
                continue;
            }

            if(!empty($v->mpu_id)){
                $data['update'][] = $v;
            }else{
                $data['approve'][] = $v;
            }
        }

        return $data;
    }

    public static function getCounters()
    {
        $today = date('Y-m-d');
        $employees = DB::table('tbl201_persinfo')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->join('tbl_company', function($join){
                $join->on('C_Code', '=', 'jrec_company')
                ->where('C_owned', 'True');
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->leftJoin('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->where(function($q){
                $q->where('ji_remarks', 'Active')
                ->where(function($q2){
                    $q2->whereNull('ji_resdate')
                    ->orWhere('ji_resdate', '')
                    ->orWhere('ji_resdate', '0000-00-00');
                });
            })
            ->orWhere('ji_resdate', '>=', $today)
            ->get();

        $employee_cnt = $employees->where('ji_remarks', '=', 'Active')
        ->groupBy('jrec_company')
        ->map(fn ($v, $k) => [
            'count' => $v->count(),
            'breakdown' => $v->groupBy('Dept_Name')->map(fn ($r) => $r->count())
        ]);

        $manpower_cnt = ManpowerRequest::leftJoin('tbl_mpupdate', function($join){
            $join->on('mpu_mpid', '=', 'mp_id')
                ->whereRaw("(mpu_stat='pending' OR mpu_stat='approved')");
        })
        ->where('mp_status', 'approved')
        ->orWhereRaw("(mpu_id IS NOT NULL AND mpu_req = 'edit')")
        ->orderBy('mp_id', 'desc')
        ->get()
        ->map(function($item){
            $item->mp_progress = explode(',', $item->mp_progress);
            $fill = explode('/', $item->mp_progress[1] ?? '');
            if (empty($fill[1])) {
                // Match all groups enclosed in square brackets
                preg_match_all('/\[([^\]]+)\]/', $item->mp_replacement, $r_matches);

                // Split each group by "|" and build the final array
                $fill_count = array_reduce($r_matches[1], function ($v1, $v2) {
                    $v1['require'] += $v2[1];
                    $v1['fill'] += $v2[4] ?? 0;
                    return $v1;
                }, ['require' => 0, 'fill' => 0]);

                preg_match_all('/\[([^\]]+)\]/', $item->mp_additional, $a_matches);
                $fill_count = array_reduce($a_matches[1], function ($v1, $v2) {
                    $v1['require'] += $v2[1];
                    $v1['fill'] += $v2[4] ?? 0;
                    return $v1;
                }, ['require' => $fill_count['require'], 'fill' => $fill_count['fill']]);
            }else{
                $fill_count = [
                    'require' => $fill[1],
                    'fill' => $fill[0]
                ];
            }

            return $fill_count;
        })
        ->reduce(fn($carry, $item) => [
            'require' => $carry['require'] + $item['require'],
            'fill' => $carry['fill'] + $item['fill']
        ], ['require' => 0, 'fill' => 0]);

        return response()->json([
            'manpower' => $manpower_cnt,
            'employees' => $employee_cnt,
            'exits' => self::getExitInterview()
        ]);
    }

    public static function getTimeoff()
    {
        $employees = Employee::employeeList();

        $dt1 = date('Y-m-d');
        // $dt1 = '2024-09-09';
        $dt_soon = date('Y-m-d', strtotime($dt1.' +3 days'));

        $data = [];

        foreach (DB::connection('hrd2')->table('tbl201_leave as a')
        ->whereRaw("la_status IN ('approved', 'confirmed') AND (? <= la_start OR (? BETWEEN la_start AND la_end) OR FIND_IN_SET(?, la_dates) > 0)", [$dt1, $dt1, $dt1])
        ->get() as $v) {
            $empinfo = $employees->where('pers_empno', $v->la_empno)->first();
            $v->pic = url('/').'/file/get/emp-img/'.$v->la_empno;
            $v->empname = ucwords(trim($empinfo?->pers_lastname.', '.$empinfo?->pers_firstname));
            $v->timeoff_type = $v->la_type;
            $v->ongoing = ($dt1 >= $v->la_start);
            $v->soon = ($dt1 <= $v->la_start && $dt_soon >= $v->la_start);
            $data[$v->la_start][] = $v;
        }

        foreach (DB::connection('hrd2')->table('tbl201_offset as a')
        ->leftJoin('tbl201_offset_details as b', 'osd_osid', '=', 'os_id')
        ->whereRaw("os_status IN ('approved', 'confirmed') AND (DATE_FORMAT(os_offsetdt, '%Y-%m-%d') = ? OR ? <= DATE_FORMAT(os_offsetdt, '%Y-%m-%d'))", [$dt1, $dt1])
        ->get() as $v) {
            $empinfo = $employees->where('pers_empno', $v->os_empno)->first();
            $v->pic = url('/').'/file/get/emp-img/'.$v->os_empno;
            $v->empname = ucwords(trim($empinfo?->pers_lastname.', '.$empinfo?->pers_firstname));
            $v->timeoff_type = 'Offset';
            $v->ongoing = ($dt1 >= date('Y-m-d', strtotime($v->os_offsetdt)));
            $v->soon = ($dt1 <= date('Y-m-d', strtotime($v->os_offsetdt)) && $dt_soon >= date('Y-m-d', strtotime($v->os_offsetdt)));
            $data[date('Y-m-d', strtotime($v->os_offsetdt))][] = $v;
        }

        $sorted = collect($data)
        ->sortKeys()
        ->map(function ($group) {
            return collect($group)->sortBy(function ($item) {
                return [
                    $item->la_start ?? date('Y-m-d', strtotime($item->os_offsetdt)), 
                    $item->la_end ?? '',
                    $item->empname
                ];
            });
        });

        return view('pages.dashboard.timeoff', ['list' => $sorted]);
    }

    public static function getTravel()
    {
        $employees = Employee::employeeList();

        $dt1 = date('Y-m-d');
        $dt_soon = date('Y-m-d', strtotime($dt1.' +3 days'));

        $data = [];

        foreach (DB::connection('hrd2')->table('tbl_edtr_hours as a')
        ->whereRaw("LOWER(dtr_stat) IN ('approved', 'confirmed') AND (LOWER(day_type) LIKE '%travel%' OR LOWER(day_type) LIKE '%training%') AND ? <= date_dtr", [$dt1])
        ->get() as $v) {
            $empinfo = $employees->where('pers_empno', $v->emp_no)->first();
            $v->pic = url('/').'/file/get/emp-img/'.$v->emp_no;
            $v->empname = ucwords(trim($empinfo?->pers_lastname.', '.$empinfo?->pers_firstname));
            $v->ongoing = ($dt1 >= $v->date_dtr);
            $v->soon = ($dt1 <= $v->date_dtr && $dt_soon >= $v->date_dtr);
            $data[$v->date_dtr][] = $v;
        }

        $sorted = collect($data)
        ->sortKeys()
        ->map(function ($group) {
            return collect($group)->sortBy(function ($item) {
                return [
                    $item->date_dtr,
                    $item->empname
                ];
            });
        });

        return view('pages.dashboard.travel', ['list' => $sorted]);
    }

    public static function getMemo()
    {
        $user = Auth::user();
        $jobInfo = $user->JobPosition;
        $department = $jobInfo?->jrec_department;
        $area = $jobInfo?->jrec_area;
        $company = $jobInfo?->jrec_company;
        
        $data = Memo::leftJoin('tbl_memo_read', function($join) use($user){
            $join->on('read_memo_no', '=', 'memo_no')
            ->where('read_empno', '=', $user->Emp_No);
        })
        ->whereRaw("memo_recipienttype = 'All' 
        OR (memo_recipienttype = 'Employee' AND FIND_IN_SET(?, memo_recipient) > 0)
        OR (memo_recipienttype = 'Area' AND FIND_IN_SET(?, memo_recipient) > 0)
        OR (memo_recipienttype = 'Department' AND FIND_IN_SET(?, memo_recipientdept) > 0)
        OR (memo_recipienttype = 'Company' AND FIND_IN_SET(?, memo_recipientcompany) > 0)", [
            $user->Emp_No,
            $area,
            $department,
            $company
        ])
        ->orderBy('memo_date', 'desc')
        ->get();

        return view('pages.dashboard.memo', [
            'list' => $data,
            'companyList' => Setting::companyList()->mapWithKeys(fn($v) => [$v->C_Code => $v]),
            'departmentList' => Setting::departmentList()->mapWithKeys(fn($v) => [$v->Dept_Code => $v]),
            'areaList' => Setting::areaList()->mapWithKeys(fn($v) => [$v->Area_Code => $v]),
            'outletList' => Setting::outletList()->mapWithKeys(fn($v) => [$v->OL_Code => $v]),
            'employeeList' => collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
                ->mapWithKeys(function ($item) {
                    return [$item['pers_empno'] => $item];
                })
        ]);
    }

    public static function getRetention6Months()
    {
        $retention = [];
        $ym = date('Y-m');
        for ($cur_ym = date('Y-m', strtotime($ym.'-01 -5 months')); $cur_ym <= $ym; $cur_ym = date('Y-m', strtotime($cur_ym.'-01 +1 month'))) { 
            $data = collect();
            foreach (Employee::retentionList($cur_ym) as $v) {
                // $v->duration = ($v->ji_datehired ? date('m/d/Y', strtotime($v->ji_datehired)) : 'N/A') . '-' . ($v->ji_resdate ? date('m/d/Y', strtotime($v->ji_resdate)) : 'N/A');

                if(!$v->C_Code){
                    $v->C_Code = 'N/A';
                }

                if(!$v->Dept_Name){
                    $v->Dept_Name = 'N/A';
                }

                if(!$data->has($v->C_Code)){
                    $data->put($v->C_Code, collect());
                }
                if(!$data[$v->C_Code]->has($v->Dept_Name)){
                    $data[$v->C_Code]->put($v->Dept_Name, collect());
                }
                if(date('Y-m', strtotime($v->ji_resdate)) == $cur_ym){
                    if(!$data[$v->C_Code][$v->Dept_Name]->has('separated')){
                        $data[$v->C_Code][$v->Dept_Name]->put('separated', collect());
                    }
                    $data[$v->C_Code][$v->Dept_Name]['separated']->push($v);
                }
                if(date('Y-m', strtotime($v->ji_datehired)) == $cur_ym){
                    if(!$data[$v->C_Code][$v->Dept_Name]->has('new')){
                        $data[$v->C_Code][$v->Dept_Name]->put('new', collect());
                    }
                    $data[$v->C_Code][$v->Dept_Name]['new']->push($v);
                }
                if(date('Y-m', strtotime($v->ji_datehired)) < $cur_ym){
                    if(!$data[$v->C_Code][$v->Dept_Name]->has('old')){
                        $data[$v->C_Code][$v->Dept_Name]->put('old', collect());
                    }
                    $data[$v->C_Code][$v->Dept_Name]['old']->push($v);
                }
            }

            foreach ($data as $c => $cv) {
                $old = $cv->sum(fn ($item) => ($item['old'] ?? null)?->count());
                $new = $cv->sum(fn ($item) => ($item['new'] ?? null)?->count());
                $separated = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->count());
                // $separated_old = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->filter(fn ($i) => date('Y-m', strtotime($i->ji_datehired)) != date('Y-m', strtotime($i->ji_resdate)))->count());
                // $average_employee = ($old + ($old + $new - $separated)) / 2;
                $remaining = ($old + $new - $separated);
                $total = ($old + $new);

                $retention['company'][$c][date('M Y', strtotime($cur_ym))] = round(($total ? $remaining / $total : 0) * 100);

                foreach ($cv as $d => $dv) {
                    $old = ($dv['old'] ?? null)?->count();
                    $new = ($dv['new'] ?? null)?->count();
                    $separated = ($dv['separated'] ?? null)?->count();
                    // $separated_old = ($dv['separated'] ?? null)?->filter(fn ($i) => date('Y-m', strtotime($i->ji_datehired)) != date('Y-m', strtotime($i->ji_resdate)))->count();
                    // $average_employee = ($old + ($old + $new - $separated)) / 2;
                    $remaining = ($old + $new - $separated);
                    $total = ($old + $new);

                    $retention['dept'][$c][$d][date('M Y', strtotime($cur_ym))] = round(($total ? $remaining / $total : 0) * 100);
                }
            }

            $retention['months'][] = date('M Y', strtotime($cur_ym));
        }

        return response()->json($retention);
    }

    public static function getRetention($ym = '')
    {
        $retention = [];
        if(!$ym) $ym = date('Y-m');
        $data = collect();
        foreach (Employee::retentionList($ym) as $v) {

            if(!$data->has($v->C_Code)){
                $data->put($v->C_Code, collect());
            }
            if(!$data[$v->C_Code]->has($v->Dept_Name)){
                $data[$v->C_Code]->put($v->Dept_Name, collect());
            }
            if(date('Y-m', strtotime($v->ji_resdate)) == $ym){
                if(!$data[$v->C_Code][$v->Dept_Name]->has('separated')){
                    $data[$v->C_Code][$v->Dept_Name]->put('separated', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['separated']->push($v);
            }
            if(date('Y-m', strtotime($v->ji_datehired)) == $ym){
                if(!$data[$v->C_Code][$v->Dept_Name]->has('new')){
                    $data[$v->C_Code][$v->Dept_Name]->put('new', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['new']->push($v);
            }
            if(date('Y-m', strtotime($v->ji_datehired)) < $ym){
                if(!$data[$v->C_Code][$v->Dept_Name]->has('old')){
                    $data[$v->C_Code][$v->Dept_Name]->put('old', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['old']->push($v);
            }
        }

        foreach ($data as $c => $cv) {
            $old = $cv->sum(fn ($item) => ($item['old'] ?? null)?->count());
            $new = $cv->sum(fn ($item) => ($item['new'] ?? null)?->count());
            $separated = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->count());
            // $separated_old = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->filter(fn ($i) => date('Y-m', strtotime($i->ji_datehired)) != date('Y-m', strtotime($i->ji_resdate)))->count());
            // $average_employee = ($old + ($old + $new - $separated)) / 2;
            $remaining = ($old + $new - $separated);
            $total = ($old + $new);

            $retention['company'][$c] = round(($total ? $remaining / $total : 0) * 100);

            foreach ($cv as $d => $dv) {
                $old = ($dv['old'] ?? null)?->count();
                $new = ($dv['new'] ?? null)?->count();
                $separated = ($dv['separated'] ?? null)?->count();
                // $separated_old = ($dv['separated'] ?? null)?->filter(fn ($i) => date('Y-m', strtotime($i->ji_datehired)) != date('Y-m', strtotime($i->ji_resdate)))->count();
                // $average_employee = ($old + ($old + $new - $separated)) / 2;
                $remaining = ($old + $new - $separated);
                $total = ($old + $new);

                $retention['dept'][$c][$d] = round(($total ? $remaining / $total : 0) * 100);
            }
        }

        return response()->json($retention);
    }

    public static function getPA($ym = '')
    {
        $ym = $ym ?: now()->format('Y-m');
        $employees = DB::table('tbl201_persinfo')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->where('jrec_status', '=', 'Primary');
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->join('tbl201_jobinfo', function ($join) {
                $join->on('ji_empno', '=', 'pers_empno');
                    // ->where('ji_remarks', 'Active');
            })
            ->selectRaw("pers_empno, CONCAT(pers_lastname, ', ', pers_firstname) AS empname, Dept_Name AS dept")
            ->get();

        $data = DB::connection('hrd2')->table('tbl_pa_form')
            ->where('paf_status', 'active')
            ->where('paf_period', $ym)
            ->select('paf_empno')
            ->addSelect([DB::raw("(SELECT 
                    SUM( IF( pa_attainment != '' AND pa_weight != '', 
                                IF( pa_attainment >= 96, 4, 
                                    IF( pa_attainment >= 91, 3, 
                                        IF( pa_attainment >= 85, 2, 1 ) 
                                    ) 
                                ) * (pa_weight / 100), 
                            0 )
                        ) FROM tbl_pa WHERE pa_pafid = paf_id 
                ) AS weighted_rating_total")])
            ->get();
        
        $data2 = DB::connection('hrd2')->table('tbl_paf_sji')
            ->where('paf_status', '1')
            ->where('paf_period', $ym)
            ->select('paf_empno', 'paf_qtyscore AS weighted_rating_total')
            ->get();

        $data = $data->merge($data2)->map(function($item) use($employees){
                $item->weighted_rating_total = round($item->weighted_rating_total, 2);
                $merged = (object) array_merge((array) $item, (array) $employees->where('pers_empno', '=', $item->paf_empno)->first());
                if(empty($merged->pers_empno)){
                    $merged->pers_empno = $merged->paf_empno;
                    $merged->empname = $merged->paf_empno;
                    $merged->dept = '';
                }
                return $merged;
            })
            ->sortBy([
                ['dept', 'asc'],
                ['weighted_rating_total', 'desc'],
                ['empname', 'asc']
            ])
            ->groupBy('dept')
            ->map(function($item){
                return (object)[
                    'emp' => $item,
                    'rating' => $item->count() > 0 ? round($item->sum('weighted_rating_total') / $item->count(), 2) : 0
                ];
            });
        return response()->json($data);
    }

    public static function getProbationary()
    {
        return DB::table('tbl201_persinfo')
            ->selectRaw("pers_empno AS empno, Dept_Name AS dept, TRIM(CONCAT(pers_lastname, ', ', pers_firstname)) as empname, ji_datehired AS dt_hired")
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->where('jrec_status', 'Primary');
            })
            ->join('tbl_company', function ($join) {
                $join->on('C_Code', '=', 'jrec_company')
                    ->where('C_owned', 'True');
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->join('tbl201_jobinfo', function ($join) {
                $join->on('ji_empno', '=', 'pers_empno')
                    ->where('ji_remarks', 'Active');
                    // ->where('ji_datehired', '>=', now()->subMonths(6)->format('Y-m-d'));
            })
            ->join('tbl201_emplstatus', function ($join) {
                $join->on('estat_empno', '=', 'pers_empno')
                    ->where('estat_stat', 'Active')
                    ->where('estat_empstat', 'PROB');
            })
            ->orderBy('ji_datehired', 'desc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->get();
    }

    public static function getAcademy()
    {
        return DB::connection('academy')
        ->table('tbl_courses AS a')
        ->leftJoin('module AS b', function($join){
            $join->on('b.course_id', '=', 'a.id')
            ->where('b.status', 'active');
        })
        ->leftJoin('content AS c', function($join){
            $join->on('c.module_id', '=', 'b.id')
            ->where('c.status', 'active');
        })
        // ->leftJoin('')
        ->where('a.status', 'active')
        ->orderBy('a.id')
        ->orderBy('b.id')
        ->orderBy('c.id')
        ->select('a.title AS course', 'b.title AS module', 'c.title AS topic')
        ->get()
        ->groupBy('course')
        ->map(function($sub1) {
            return $sub1->groupBy('module')->map(function($sub2) {
                return $sub2->pluck('topic');
            });
        });
    }
}
