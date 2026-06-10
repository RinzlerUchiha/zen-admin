<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Kamustahan;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KamustahanController extends Controller
{
    public static function loadList($empno = '')
    {
        $user = Auth::user();
        $emplist = DB::table('tbl201_persinfo')
                ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
                ->leftJoin('tbl201_jobrec', function ($join) {
                    $join->on('jrec_empno', '=', 'pers_empno')
                        ->on('jrec_status', '=', DB::raw("'Primary'"));
                })
                ->leftJoin('tbl_company', 'C_Code', '=', 'jrec_company')
                ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
                ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jrec_position')
                ->orderBy('C_Name', 'asc')
                ->orderBy('Dept_Name', 'asc')
                ->orderBy('pers_lastname', 'asc')
                ->orderBy('pers_firstname', 'asc')
                ->get();

        $filter = $emplist->where('pers_empno', '=', $empno)->first();
        $empname = $filter ? ucwords(trim($filter->pers_lastname.', '.$filter->pers_firstname)) : '';

        return view('pages.kamustahan', [
            'main_link' => 'kamustahan',
            'sub_link' => '',
            'empno' => $empno,
            'empname' => $empname,
            'list' => Kamustahan::getKamustahanList($empno),
            'user_empno' => $user->Emp_No,
            'emplist' => $emplist
        ]);
    }

    public static function show(Request $request, $id = null)
    {
        $user = Auth::user();
        $employeeLatestJobInfo = Employee::employeeLatestJobInfo();
        $data = Kamustahan::findRecord($id);
        if(!$data->ekmst_empno){
            $data->ekmst_empno = $request->empno ?? '';
            $data->ekmst_pos = $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $data->ekmst_empno)->jrec_position ?? "";
            $data->ekmst_dept = $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $data->ekmst_empno)->jrec_department ?? "";
            $data->ekmst_superior = $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $data->ekmst_empno)->jrec_reportto ?? "";
            $data->ekmst_interviewer = $user->Emp_No;
        }
        
        return view('pages.kamustahan-info', [
            'main_link' => 'kamustahan',
            'sub_link' => '',
            'data' => $data,
            'questions' => Kamustahan::getQuestions(),
            'user_empno' => $user->Emp_No,
            'positionList' => Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]),
            'departmentList' => Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]),
            'employeeLatestJobInfo' => $employeeLatestJobInfo,
            'employees' => collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
                ->mapWithKeys(function ($item) {
                    return [$item['pers_empno'] => $item];
                })
        ]);
    }

    public static function store(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'empno' => 'required|string',
                'position' => 'nullable|string',
                'dept' => 'nullable|string',
                'superior' => 'nullable|string',
                'interviewer' => 'required|string',
                'datetime' => 'required|date',
                'answers' => 'nullable|string',
            ]);

            $validated['answers'] = !empty($validated['answers']) ? json_decode($validated['answers'], true) : [];

            Kamustahan::store($validated);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function storeRemark(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'empno' => 'required|string',
                'remark' => 'required|string'
            ]);

            Kamustahan::storeRemark($validated);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
