<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ExitInterview;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class ExitInterviewController extends Controller
{
    public static function showListByEmpNo($empno)
    {
        $user = Auth::user();
        $emp = Employee::personalInfo($empno);
        return view('pages.report.exit-interview', [
            'list' => ExitInterview::showListByEmpNo($empno),
            'empno' => $empno,
            'name' => $emp ? ucwords(trim($emp->pers_lastname.', '.$emp->pers_firstname)) : '',
            'user_empno' => $user->Emp_No,
            'positionList' => Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]),
            'departmentList' => Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]),
            'employees' => collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            })
        ]);
    }

    public static function showInfo($id)
    {
        $user = Auth::user();
        
        $data = ExitInterview::showInfo($id);
        if(empty($data)){
            $data = new stdClass();
            $data->xintvw_id = null;
            $data->xintvw_empno = null;
			$data->xintvw_interviewer = $user->Emp_No;
            $data->xintvw_intvwdate = date("Y-m-d h:i A");
			$data->xintvw_receivedby = $user->Emp_No;
			$data->xintvw_receiveddate = date("Y-m-d h:i A");
            $data->xintvw_empsign = null;
            $data->xintvw_pos = null;
            $data->xintvw_dthired = null;
            $data->xintvw_superior = null;
            $data->xintvw_dtresign = null;
            $data->xintvw_dept = null;
            $data->xintvw_lastday = null;
        }

        return view('pages.report.exit-interview', [
            'data' => $data,
            'user_empno' => $user->Emp_No,
            'positionList' => Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]),
            'departmentList' => Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]),
            'questions' => ExitInterview::getInterviewQuestion(),
            'employees' => collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            })
        ]);
    }

    public static function newInterview($empno)
    {
        $user = Auth::user();
        
        $data = new stdClass();
        $jobinfo = Employee::showCurrentJobInfo($empno);

        $data->xintvw_id = null;
        $data->xintvw_empno = $empno ?? '';
        $data->xintvw_interviewer = $user->Emp_No;
        $data->xintvw_intvwdate = date("Y-m-d h:i A");
        $data->xintvw_receivedby = $user->Emp_No;
        $data->xintvw_receiveddate = date("Y-m-d h:i A");
        $data->xintvw_empsign = null;
        $data->xintvw_pos = $jobinfo['jobrec']?->jrec_position;
        $data->xintvw_dthired = $jobinfo['jobinfo']?->ji_datehired;
        $data->xintvw_superior = $jobinfo['jobrec']?->jrec_reportto;
        $data->xintvw_dtresign = null;
        $data->xintvw_dept = $jobinfo['jobrec']?->jrec_department;
        $data->xintvw_lastday = null;

        return view('pages.report.exit-interview', [
            'data' => $data,
            'user_empno' => $user->Emp_No,
            'positionList' => Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]),
            'departmentList' => Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]),
            'questions' => ExitInterview::getInterviewQuestion(),
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
                'emp' => 'required|string',
                'pos' => 'nullable|string',
                'superior' => 'nullable|string',
                'dept' => 'nullable|string',
                'dtresign' => 'required|date',
                'lastdt' => 'required|date',
                'hiredt' => 'required|date',
                'intervewer' => 'required|string',
                'interviewdt' => 'required|date',
                'receivedby' => 'required|string',
                'receivedt' => 'required|date',
                'ans' => 'required|string'
            ]);

            $validated['ans'] = json_decode($validated['ans'], true);

            $interview = ExitInterview::store($validated);

            if ($validated['id']) {
                return response()->json(['success' => true]);
            }
            return ExitInterviewController::showInfo($interview);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function sign(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'empno' => 'required|string',
                'sign' => 'required|string'
            ]);

            ExitInterview::sign($validated);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
