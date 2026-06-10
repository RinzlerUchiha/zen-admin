<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public static function index($type = 'outlet')
    {
        /** @var \App\Models\User $user */
        // $user = Auth::user();

        $arremp = DB::table('tbl201_persinfo as a')
        ->leftJoin('tbl201_jobinfo as b', 'b.ji_empno', '=', 'a.pers_empno')
        ->leftJoin('tbl_user2 as c', 'c.Emp_No', '=', 'a.pers_empno')
        ->get()
        ->mapWithKeys(function($i) {
            return [
                $i->pers_empno => [
                    "name" => trim($i->pers_lastname." ".$i->pers_suffix).", ".$i->pers_firstname,
                    "empno" => $i->pers_empno,
                    "status" => $i->ji_remarks
                ]
            ];
        });

        return view('pages.maintenance', [
            'main_link' => 'maintenance',
            'sub_link' => '',
            'user_empno' => Auth::user()->Emp_No,
            'maincat' => $type,
            'page' => "pages.maintenance.{$type}",
            'area' => $type == 'outlet' ? Setting::areaList(0) : null,
            'province' => $type == 'city' ? Setting::provinceList(0) : null,
            'city' => $type == 'barangay' ? Setting::municipalityList(0) : null,
            'arremp' => $arremp,
            // 'employees' => Employee::employeeList()
        ]);
    }

    public static function showList($type, $extra = null)
    {
        switch ($type) {
            case 'company':
                return self::companyTable();
                break;

            case 'department':
                return self::departmentTable();
                break;

            case 'position':
                return self::positionTable();
                break;

            case 'outlet':
                return self::outletTable();
                break;

            case 'area':
                return self::areaTable();
                break;

            case 'province':
                return self::provinceTable();
                break;

            case 'city':
                return self::cityTable();
                break;

            case 'barangay':
                return self::barangayTable();
                break;

            case 'leave-bal':
                // return self::outletTable();
                break;

            case 'assignment':
                return self::assignmentTable($extra);
                break;
            
            default:
                return "";
                break;
        }
    }

    public function saveSetting($type, Request $request)
    {
        try {

            switch ($type) {
                case 'company':

                    $validated = $request->validate([
                        'code' => 'required|string',
                        'name' => 'required|string',
                        'description' => 'nullable|string',
                        'tin' => 'nullable|string',
                        'sss' => 'nullable|string',
                        'phic' => 'nullable|string',
                        'hdmf' => 'nullable|string',
                        'address' => 'nullable|string',
                        'owned' => 'nullable|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveCompany($validated);
                    });
                    
                    break;

                case 'department':

                    $validated = $request->validate([
                        'code' => 'required|string',
                        'name' => 'required|string',
                        'description' => 'nullable|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveDepartment($validated);
                    });
                    
                    break;

                case 'position':

                    $validated = $request->validate([
                        'code' => 'required|string',
                        'name' => 'required|string',
                        'summary' => 'nullable|string',
                        'duties' => 'nullable|string',
                        'specification' => 'nullable|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::savePosition($validated);
                    });
                    
                    break;

                case 'area':

                    $validated = $request->validate([
                        'code' => 'required|string',
                        'name' => 'required|string',
                        'description' => 'nullable|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveArea($validated);
                    });
                    
                    break;

                case 'outlet':

                    $validated = $request->validate([
                        'code' => 'required|string',
                        'name' => 'required|string',
                        'area' => 'required|string',
                        'openingdt' => 'nullable|date',
                        'closingdt' => 'nullable|date',
                        'size' => 'required|integer',
                        'type' => 'required|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveOutlet($validated);
                    });
                    
                    break;

                case 'province':

                    $validated = $request->validate([
                        'id' => 'nullable|integer',
                        'code' => 'required|string',
                        'name' => 'required|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveProvince($validated);
                    });
                    
                    break;

                case 'city':

                    $validated = $request->validate([
                        'id' => 'nullable|integer',
                        'province' => 'required|string',
                        'name' => 'required|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveMunicipality($validated);
                    });
                    
                    break;

                case 'barangay':

                    $validated = $request->validate([
                        'id' => 'nullable|integer',
                        'city' => 'required|string',
                        'name' => 'required|string',
                        'status' => 'required|string'
                    ]);
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveBarangay($validated);
                    });
                    
                    break;

                case 'assign':

                    $validated = $request->validate([
                        'typeList' => 'nullable|string',
                        'emp' => 'nullable|string',
                        'assignment' => 'nullable|string',
                        'remove' => 'nullable|string',

                        'id' => 'nullable|integer',
                        'type' => 'nullable|string',
                        'src' => 'nullable|string',
                        'emparrsrc' => 'nullable|string',
                        'emparrtarget' => 'nullable|string'
                    ]);

                    // if(!empty($validated['assignment'])){
                    //     $validated['assignment'] = json_decode($validated['assignment'], true);
                    // }

                    if(!empty($validated['typeList'])){
                        $validated['typeList'] = json_decode($validated['typeList'], true);
                    }

                    if(!empty($validated['remove'])){
                        $validated['remove'] = json_decode($validated['remove'], true);
                    }

                    if(!empty($validated['emparrsrc'])){
                        $validated['emparrsrc'] = json_decode($validated['emparrsrc'], true);
                    }

                    if(!empty($validated['emparrtarget'])){
                        $validated['emparrtarget'] = json_decode($validated['emparrtarget'], true);
                    }
                    
                    DB::transaction(function () use ($validated) {
                        Setting::saveAssignment($validated);
                    });
                    
                    break;
                
                default:
                    # code...
                    break;
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function delSetting($type, $id)
    {
        try {

            switch ($type) {
                case 'company':

                    DB::table('tbl_company')->where('C_Code', $id)->delete();
                    
                    break;

                case 'department':

                    DB::table('tbl_department')->where('Dept_Code', $id)->delete();
                    
                    break;

                case 'position':

                    DB::table('tbl_jobdescription')->where('jd_code', $id)->delete();
                    
                    break;

                case 'area':

                    DB::table('tbl_area')->where('Area_Code', $id)->delete();
                    
                    break;

                case 'outlet':

                    DB::table('tbl_outlet')->where('OL_Code', $id)->delete();
                    
                    break;

                case 'province':

                    DB::table('tbl_province')->where('pr_code', $id)->delete();
                    
                    break;

                case 'city':

                    DB::table('tbl_municipality')->where('ct_id', $id)->delete();
                    
                    break;

                case 'barangay':

                    DB::table('tbl_barangay')->where('br_id', $id)->delete();
                    
                    break;

                case 'assign':

                    DB::table('tbl_dept_authority')->where('auth_id', $id)->delete();
                    
                    break;
                
                default:
                    # code...
                    break;
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function companyTable()
    {
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Code</th>';
        $html .= '<th>Name</th>';
        $html .= '<th>Description</th>';
        $html .= '<th>TIN</th>';
        $html .= '<th>SSS</th>';
        $html .= '<th>PHIC</th>';
        $html .= '<th>HDMF</th>';
        $html .= '<th>Address</th>';
        $html .= '<th>Owned</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::companyList(0) as $v) {
            $html .= '<td>' . $v->C_Code . '</td>';
            $html .= '<td>' . $v->C_Name . '</td>';
            $html .= '<td>' . $v->C_Description . '</td>';
            $html .= '<td>' . $v->C_tin . '</td>';
            $html .= '<td>' . $v->C_sss . '</td>';
            $html .= '<td>' . $v->C_phic . '</td>';
            $html .= '<td>' . $v->C_hdmf . '</td>';
            $html .= '<td>' . $v->C_address . '</td>';
            $html .= '<td>' . $v->C_owned . '</td>';
            $html .= '<td>' . ucwords($v->C_Remarks) . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-company"
                        data-code="'.$v->C_Code.'"
                        data-name="'.$v->C_Name.'"
                        data-description="'.$v->C_Description.'"
                        data-tin="'.$v->C_tin.'"
                        data-sss="'.$v->C_sss.'"
                        data-phic="'.$v->C_phic.'"
                        data-hdmf="'.$v->C_hdmf.'"
                        data-address="'.$v->C_address.'"
                        data-owned="'.$v->C_owned.'"
                        data-status="'.$v->C_Remarks.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_company('.$v->C_Code.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function departmentTable()
    {
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Code</th>';
        $html .= '<th>Name</th>';
        $html .= '<th>Description</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::departmentList(0) as $v) {
            $html .= '<td>' . $v->Dept_Code . '</td>';
            $html .= '<td>' . $v->Dept_Name . '</td>';
            $html .= '<td>' . $v->Dept_Description . '</td>';
            $html .= '<td>' . ucwords($v->Dept_Stat) . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-department"
                        data-code="'.$v->Dept_Code.'"
                        data-name="'.$v->Dept_Name.'"
                        data-description="'.$v->Dept_Description.'"
                        data-status="'.$v->Dept_Stat.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_department('.$v->Dept_Code.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function positionTable()
    {
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Code</th>';
        $html .= '<th>Name</th>';
        $html .= '<th>Summary</th>';
        $html .= '<th>Duties</th>';
        $html .= '<th>Specification</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::positionList(0) as $v) {
            $html .= '<td>' . $v->jd_code . '</td>';
            $html .= '<td>' . $v->jd_title . '</td>';
            $html .= '<td>' . $v->jd_summary . '</td>';
            $html .= '<td>' . $v->jd_duties . '</td>';
            $html .= '<td>' . $v->jd_specification . '</td>';
            $html .= '<td>' . ucwords($v->jd_stat) . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-position"
                        data-code="'.$v->jd_code.'"
                        data-name="'.$v->jd_title.'"
                        data-summary="'.$v->jd_summary.'"
                        data-duties="'.$v->jd_duties.'"
                        data-specification="'.$v->jd_specification.'"
                        data-status="'.$v->jd_stat.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_position('.$v->jd_code.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function outletTable()
    {
        $area = Setting::areaList(0)->keyBy('Area_Code');
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Code</th>';
        $html .= '<th class="text-start">Name</th>';
        $html .= '<th class="text-start">Area</th>';
        $html .= '<th class="text-start">Opening Date</th>';
        $html .= '<th class="text-start">Closing Date</th>';
        $html .= '<th>Size</th>';
        $html .= '<th>Type</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::outletList(0) as $v) {
            $html .= '<td>' . $v->OL_Code . '</td>';
            $html .= '<td class="text-start">' . $v->OL_Name . '</td>';
            $html .= '<td class="text-start">' . "({$v->Area_Code}) " . ($area[$v->Area_Code] ?? null)?->Area_Name . '</td>';
            $html .= '<td class="text-start">' . $v->OL_opendt . '</td>';
            $html .= '<td class="text-start">' . $v->OL_closedt . '</td>';
            $html .= '<td>' . $v->OL_Size . '</td>';
            $html .= '<td>' . $v->OL_Type . '</td>';
            $html .= '<td>' . ucwords($v->OL_stat) . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-outlet"
                        data-code="'.$v->OL_Code.'"
                        data-name="'.$v->OL_Name.'"
                        data-area="'.$v->Area_Code.'"
                        data-openingdt="'.$v->OL_opendt.'"
                        data-closingdt="'.$v->OL_closedt.'"
                        data-size="'.$v->OL_Size.'"
                        data-type="'.$v->OL_Type.'"
                        data-status="'.$v->OL_stat.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_outlet('.$v->OL_Code.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function areaTable()
    {
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Code</th>';
        $html .= '<th class="text-start">Name</th>';
        $html .= '<th class="text-start">Description</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::areaList(0) as $v) {
            $html .= '<td>' . $v->Area_Code . '</td>';
            $html .= '<td class="text-start">' . $v->Area_Name . '</td>';
            $html .= '<td class="text-start">' . $v->Area_Description . '</td>';
            $html .= '<td>' . ucwords($v->Area_stat) . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-area"
                        data-code="'.$v->Area_Code.'"
                        data-name="'.$v->Area_Name.'"
                        data-description="'.$v->Area_Description.'"
                        data-status="'.$v->Area_stat.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_area('.$v->Area_Code.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function provinceTable()
    {
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Code</th>';
        $html .= '<th class="text-start">Name</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::provinceList(0) as $v) {
            $html .= '<td>' . $v->pr_code . '</td>';
            $html .= '<td class="text-start">' . $v->pr_name . '</td>';
            $html .= '<td>' . ($v->pr_status ? 'Active' : 'Inactive') . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-province"
                        data-id="'.$v->pr_id.'"
                        data-code="'.$v->pr_code.'"
                        data-name="'.$v->pr_name.'"
                        data-status="'.$v->pr_status.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_province('.$v->pr_code.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function cityTable()
    {
        $province = Setting::provinceList(0)->keyBy('pr_code');
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="text-start">Name</th>';
        $html .= '<th class="text-start">Province</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::municipalityList(0) as $v) {
            $html .= '<td class="text-start">' . $v->ct_name . '</td>';
            $html .= '<td class="text-start">' . ($province[$v->ct_province] ?? null)?->pr_name . '</td>';
            $html .= '<td>' . ($v->ct_status ? 'Active' : 'Inactive') . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-city"
                        data-id="'.$v->ct_id.'"
                        data-province="'.$v->ct_province.'"
                        data-name="'.$v->ct_name.'"
                        data-status="'.$v->ct_status.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_city('.$v->ct_id.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function barangayTable()
    {
        $city = Setting::municipalityList(0)->keyBy('ct_id');
        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="text-start">Name</th>';
        $html .= '<th class="text-start">City</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Setting::barangayList(0) as $v) {
            $html .= '<td class="text-start">' . $v->br_name . '</td>';
            $html .= '<td class="text-start">' . ($city[$v->br_city] ?? null)?->ct_name . '</td>';
            $html .= '<td>' . ($v->br_status ? 'Active' : 'Inactive') . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary m-1"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-barangay"
                        data-id="'.$v->br_id.'"
                        data-city="'.$v->br_city.'"
                        data-name="'.$v->br_name.'"
                        data-status="'.$v->br_status.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger m-1" onclick="remove_barangay('.$v->br_id.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function assignmentTable($type)
    {
        $assignment = Setting::assignmentList($type);
        $html = '';
        foreach ($assignment['assignment'] as $r) {
            
            if(empty($assignment['arremp'][$r['auth_emp']])) continue;

            $assignedarr = explode("|", $r['auth_assignation']);
            $html .= "<div class='col'>";
            $html .= "<div class='card divassigngrp' style='width: 200px; max-width: 200px; display: inline-block; margin: 1px; overflow-x: hidden;'>";
            $html .= "<div class='card-header d-flex p-2 text-bg-dark' style='max-height: 50px; min-height: 55px; " . ($assignment['arremp'][$r['auth_emp']]["status"] != 'Active' || $assignment['arremp'][$r['auth_emp']]["accountstat"] != 'Active' ? "color: red;" : "") . "'>";
            $html .= "<button class='btn btn-danger btn-sm me-1' style='height: fit-content;' onclick=\"remove_assign('".$r['auth_id']."')\"><i class='fa fa-trash'></i></button> ";
            $html .= "<span>";
            $html .= ($assignment['arremp'][$r['auth_emp']]["status"] != 'Active' || $assignment['arremp'][$r['auth_emp']]["accountstat"] != 'Active' ? "*" : "") . ucwords(mb_strtolower($assignment['arremp'][$r['auth_emp']]["name"]));
            $html .= "</span>";
            $html .= "</div>";
            $html .= "<div class='card-body p-1'>";
            $html .= "<div data-draggable=\"target\" data-empno='".$r['auth_emp']."' data-id=\"".$r['auth_id']."\" style='max-height: 200px; min-height: 200px; overflow-y: auto; overflow-x: hidden;'>";
            foreach ($assignedarr as $k => $v) {
                if(isset($assignment['arremp'][$v])){
                    $name = ucwords(mb_strtolower($assignment['arremp'][$v]["name"]));
                    $html .= "<div data-empno='".$v."' style='" . ($assignment['arremp'][$v]["status"] != 'Active' || $assignment['arremp'][$v]["accountstat"] != 'Active' ? "color: red;" : "") . "' class='assign-item' draggable='true' title='".$name."'>";
                    $html .= "<button class='btn btn-danger btn-sm m-1' onclick=\"update_list(this)\"><i class='fa fa-trash'></i></button>";
                    // $html .= ($assignment['arremp'][$v]["status"] != 'Active' || $assignment['arremp'][$v]["accountstat"] != 'Active' ? "*" : "");
                    $html .= $name;
                    $html .= "</div>";
                }
            }
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
        }

        return $html;
    }
}















/* 
hris code
switch ($action) {
    case 'add':
        $assign=$_POST['assign'];
        $dept=$_POST['dept'];
        $for=$_POST['for'];
        $emp=$_POST['emp'];
        $remove= isset($_POST['remove']) ? $_POST['remove'] : [];

        $for = is_array($for) ? $for : [$for];

        if(count($remove) > 0 && count($for) > 0){
            $sql1 = $hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation=REGEXP_REPLACE(auth_assignation, ?, '') WHERE FIND_IN_SET(auth_emp, ?) > 0 AND FIND_IN_SET(auth_for, ?) > 0");
            $sql1->execute(["(^" . str_replace("|", "\\||^", $assign) . "\\|)|(\\|?" . str_replace("|", "|\\|?", $assign) . ")", implode(",", $remove), implode(",", $for)]);
        }

        foreach ($for as $kfor => $vfor) {
            $cnt = 0;
            $sql1 = $hr_pdo->prepare("SELECT * FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?");
            $sql1->execute([$emp, $vfor]);
            foreach ($sql1->fetchall(PDO::FETCH_ASSOC) as $k => $v) {
                $arr1 = explode("|", $assign);
                $arr2 = explode("|", $v['auth_assignation']);
                $arrayemp = array_unique (array_merge ($arr1, $arr2));
                $arrayemp = implode("|", $arrayemp);

                $arr1 = explode("|", $dept);
                $arr2 = explode("|", $v['auth_dept']);
                if($dept){
                    $arraydept = array_unique (array_merge ($arr1, $arr2));
                    $arraydept = implode("|", $arraydept);
                }else{
                    $arraydept = $v['auth_dept'];
                }

                $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation = ?, auth_dept = ? WHERE auth_id=?");
                if($sql->execute(array($arrayemp, $arraydept,$v['auth_id']))){
                    // echo "1";
                    _log("Updated authority for $vfor to ".get_emp_name($emp).". ID: ".$v['auth_id']);
                }

                $cnt++;
            }
            if($cnt == 0){
                $sql=$hr_pdo->prepare("INSERT INTO tbl_dept_authority(auth_assignation,auth_dept,auth_emp,auth_for) VALUES(?,?,?,?)");
                if($sql->execute(array($assign,$dept,$emp,$vfor))){
                    // echo "1";
                    _log("Set authority for $vfor to ".get_emp_name($emp).". ID: ".$hr_pdo->lastInsertId());
                }
            }

        }
        echo "1";
        break;
    
    case 'edit':
        $id=$_POST['id'];
        $assign=$_POST['assign'];
        $dept=$_POST['dept'];
        $for=$_POST['for'];
        $emp=$_POST['emp'];
        $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation=?, auth_dept=?, auth_emp=?, auth_for=? WHERE auth_id=?");
        if($sql->execute(array($assign,$dept,$emp,$for,$id))){
            echo "1";
            _log("Updated authority for $for to ".get_emp_name($emp).". ID: $id");
        }
        break;

    case 'del':
        $id=$_POST['id'];
        $sql=$hr_pdo->prepare("DELETE FROM tbl_dept_authority WHERE auth_id=?");
        if($sql->execute(array($id))){
            echo "1";
            _log("Removed authority. ID: $id");
        }
        break;


    case 'updatedrag':

        $id = $_POST['id'];
        $type=$_POST['type'];
        // $empno = $_POST['empno'];
        $src = $_POST['src'];
        $emparrsrc = isset($_POST['emparrsrc']) && count($_POST['emparrsrc']) > 0 ? implode("|", $_POST['emparrsrc']) : "";
        $emparrtarget = isset($_POST['emparrtarget']) && count($_POST['emparrtarget']) > 0 ? implode("|", $_POST['emparrtarget']) : "";

        if($id){
            $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation=? WHERE auth_for=? AND auth_id=?");
            if($sql->execute(array($emparrtarget, $type, $id))){
                _log("Updated authority for $type. ID: $id");
            }
        }

        if($src){
            $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation=? WHERE auth_for=? AND auth_id=?");
            if($sql->execute(array($emparrsrc, $type, $src))){
                _log("Updated authority for $type. ID: $id");
            }
        }
        
        echo "1";

        break;
} */