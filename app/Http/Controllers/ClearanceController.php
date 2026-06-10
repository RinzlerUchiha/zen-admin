<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// catreq_required = 1 means not required... for change

class ClearanceController extends Controller
{
    public static function index($page = 'list')
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $separationList = DB::connection('hrd2')->table('tbl_separation_type')->where('sep_stat', 'active')->get();

        return view('pages.clearance', [
            'user_empno' => $user->Emp_No,
            'employees' => collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
                ->mapWithKeys(function ($item) {
                    return [$item['pers_empno'] => $item];
                }),
            'separationList' => $separationList,
            'companyList' => Setting::companyList(),
            'main_link' => 'clearance',
            'sub_link' => '',
            'maincat' => $page,
            'page' => 'pages.clearance.' . $page
        ]);
    }

    public static function showList($stat)
    {
        $data = Clearance::showList($stat);
        $separationList = DB::connection('hrd2')->table('tbl_separation_type')->where('sep_stat', 'active')->get();

        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>ECF NO</th>';
        $html .= '<th>Name</th>';
        $html .= '<th>Company</th>';
        $html .= '<th>Department/Outlet</th>';
        $html .= '<th>Last Day</th>';
        if ($stat == 'checked') {
            $html .= '<th>Date Checked</th>';
            $html .= '<th>Status</th>';
        } else if ($stat == 'cleared') {
            $html .= '<th>Date Cleared</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach ($data as $v) {
            $html .= "<tr 
            data-bs-toggle=\"modal\" 
            data-bs-target=\"" . ($stat == 'draft' ? '#modal-clr' : '#modal-view-clr') . "\"
            data-id=\"" . $v->ecf_id . "\"
            data-company=\"" . $v->ecf_company . "\"
            data-empno=\"" . $v->ecf_empno . "\"
            data-empname=\"" . $v->ecf_name . "\"
            data-lastday=\"" . $v->ecf_lastday . "\"
            data-separation=\"" . $v->ecf_separation . "\"
            data-separationname=\"" . $separationList->where('sep_id', '=', $v->ecf_separation)->first()->sep_name . "\"
            data-salaryholddate=\"" . $v->ecf_salholddt . "\"
            data-resigndt=\"" . $v->ecf_resigndt . "\"
            data-stat=\"" . $v->ecf_status . "\">";
            $html .= "<td>" . $v->ecf_no . "</td>";
            $html .= "<td>" . ucwords($v->ecf_name) . "</td>";
            $html .= "<td>" . $v->ecf_company . "</td>";
            $html .= "<td>" . $v->Dept_Name . ($v->ecf_outlet && $v->ecf_outlet != 'ADMIN' ? " - " . $v->ecf_outlet : "") . "</td>";
            $html .= "<td>" . $v->ecf_lastday . "</td>";
            if ($stat == "checked") {
                $latest_checked = $v->cat_list->sortBy([
                    ['catstat_dtchecked', 'asc'],
                    ['cat_priority', 'asc'],
                ])->last();
                $html .= "<td>" . safeDate($latest_checked->catstat_dtchecked) . "</td>";
                $html .= "<td>" . $latest_checked->catstat_stat . "</td>";
            } else if ($stat == "cleared") {
                $html .= "<td>" . $v->ecf_dtcleared . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function showInfoById($id)
    {
        $data = Clearance::find($id);
        $separationList = DB::connection('hrd2')->table('tbl_separation_type')->where('sep_stat', 'active')->get();
        return response()->json([
            'id' => $data->ecf_id,
            'company' => $data->ecf_company,
            'empno' => $data->ecf_empno,
            'empname' => $data->ecf_name,
            'lastday' => $data->ecf_lastday,
            'separation' => $data->ecf_separation,
            'separationname' => $separationList->where('sep_id', '=', $data->ecf_separation)->first()->sep_name,
            'salaryholddate' => $data->ecf_salholddt,
            'resigndt' => $data->ecf_resigndt,
            'stat' => $data->ecf_status,
        ]);
    }

    public static function getCat($company, $id = null)
    {
        $cat = DB::connection('hrd2')->table('db_ecf2.tbl_category')
            ->leftJoin('db_ecf2.tbl_req_category', function ($join) use ($id) {
                $join->on('catstat_cat', '=', 'cat_id')
                    ->where('catstat_ecfid', $id);
            });
        if ($id) {
            $cat->whereRaw("(cat_status = 'active' AND cat_company = ?) OR catstat_id IS NOT NULL", [$company]);
        } else {
            $cat->where('cat_status', 'active')
                ->where('cat_company', $company);
        }
        $cat->orderBy('cat_company')
            ->orderBy('cat_priority')
            ->orderBy('cat_id');

        return view('pages.clearance.cat-list', [
            'catList' => $cat->get(),
            'employees' => Employee::employeeList()
        ]);
    }

    public static function getCatDetailsByRequestId($id = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user_empno = $user->Emp_No;
        $viewall = $user->userAccess('ecfreq', 'viewitems', 'ECF');
        $emplist = Employee::employeeList();

        $cat = DB::connection('hrd2')->table('db_ecf2.tbl_category')
            ->join('db_ecf2.tbl_req_category', function ($join) use ($id) {
                $join->on('catstat_cat', '=', 'cat_id')
                    ->where('catstat_ecfid', $id);
            })
            ->orderBy('cat_company')
            ->orderBy('cat_priority')
            ->orderBy('cat_id')
            ->get();

        $details = DB::connection('hrd2')->table('db_ecf2.tbl_requirement')
            ->leftJoin('db_ecf2.tbl_cat_req', function ($j) use ($id) {
                $j->on('catreq_reqid', '=', 'req_id')
                    ->where('catreq_ecfid', $id);
            })
            ->whereIn('req_cat', $cat->pluck('cat_id'))
            ->orWhereNotNull('catreq_id')
            ->orderBy('req_cat')
            ->orderBy('req_id')
            ->get()
            ->map(function ($item) use ($emplist) {
                if (!empty($item->catreq_clearedby)) {
                    $clearedby = $emplist->where('pers_empno', $item->catreq_clearedby)->last();
                    $item->clearedby = $clearedby->pers_lastname . trim(" " . ($clearedby->pers_suffix ?? '')) . ", " . $clearedby->pers_firstname;
                }

                return $item;
            });

        return view('pages.clearance.cat-list-detail', [
            'viewall' => $viewall,
            'user_empno' => $user_empno,
            'clearance' => Clearance::where('ecf_id', $id)
                ->orderBy('ecf_id', 'desc')
                ->first(),
            'catList' => $cat->map(function ($item) use ($details, $emplist, $user_empno, $viewall) {
                $clearedby = $emplist->where('pers_empno', $item->catstat_emp)->last();
                $item->clearedby = $clearedby->pers_lastname . trim(" " . ($clearedby->pers_suffix ?? '')) . ", " . $clearedby->pers_firstname;

                $item->requirements = $user_empno == $item->catstat_emp
                    || strpos($item->cat_checker, $user_empno) !== false
                    || $viewall ?
                    $details->filter(fn($r) => $r->req_cat == $item->cat_id && ((!empty($r->catreq_id) && $r->catreq_catstatid == $item->catstat_id) || empty($r->catreq_id))) : null;

                if ($item->catstat_stat == 'cleared') {
                    $item->requirements = $item->requirements?->sortBy([['catreq_required', 'asc'], ['catreq_reqid', 'asc']]); // catreq_required = 1 means not required... for change
                }

                $item->viewonly = (($user_empno == $item->catstat_emp || strpos($item->cat_checker, $user_empno) !== false) && $item->catstat_stat == 'cleared') || $viewall;

                return $item;
            }),
            'employees' => $emplist
        ]);
    }

    public static function store(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'empno' => 'required|string',
                // 'company' => 'required|string',
                'separationType' => 'required|string',
                'lastDay' => 'required|date',
                'resignDate' => 'required|date',
                'checkerList' => 'required|string',
                'saveAsDraft' => 'nullable|integer',
                'isPosted' => 'nullable|integer'
            ]);

            $validated['checkerList'] = json_decode($validated['checkerList'], true);
            $validated['persinfo'] = Employee::personalInfo($validated['empno']);
            $validated['jobrec'] = Employee::showCurrentJobInfo($validated['empno'])['jobrec'];
            $validated['emplstat'] = Employee::employmentInfo($validated['empno'])->where('estat_stat', 'Active')->last()?->estat_empstat;

            DB::connection('hrd2')->transaction(function () use ($validated) {
                Clearance::store($validated);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function checkRequirements(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'cat' => 'required|integer',
                'stat' => 'required|string',
                'requirements' => 'required|string',
                'signature' => 'nullable|string'
            ]);

            $validated['requirements'] = json_decode($validated['requirements'], true);

            DB::connection('hrd2')->transaction(function () use ($validated) {
                Clearance::checkRequirements($validated);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function getCatWithRequirements($company)
    {
        $emplist = Employee::employeeList();

        $cat = DB::connection('hrd2')->table('db_ecf2.tbl_category')
            ->where('cat_company', $company)
            ->orderBy('cat_priority')
            ->orderBy('cat_id')
            ->get()
            ->map(function ($item) use ($emplist) {
                $item->cat_checker_names = explode(',', $item->cat_checker);
                if (!empty($item->cat_checker_names)) {
                    $item->cat_checker_names = array_map(function ($i) use ($emplist) {
                        $checker = $emplist->where('pers_empno', $i)->last();
                        return $checker ? $checker->pers_lastname . trim(" " . ($checker->pers_suffix ?? '')) . ", " . $checker->pers_firstname : '';
                    }, $item->cat_checker_names);
                }
                return $item;
            });

        $requirement = DB::connection('hrd2')->table('db_ecf2.tbl_requirement')
            ->whereIn('req_cat', $cat->pluck('cat_id'))
            ->orderBy('req_cat')
            ->orderBy('req_id')
            ->get();

        return view('pages.clearance.settings-data', [
            'catList' => $cat->map(function ($item) use ($requirement) {
                $item->requirement = $requirement->where('req_cat', '=', $item->cat_id);
                return $item;
            }),
            'employees' => $emplist
        ]);
    }

    public static function setCategory(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'company' => 'required|string',
                'title' => 'required|string',
                'desc' => 'required|string',
                'priority' => 'required|integer',
                'order' => 'required|integer',
                'stat' => 'required|string',
                'checker' => 'nullable|string'
            ]);

            DB::connection('hrd2')->transaction(function () use ($validated) {
                Clearance::setCategory($validated);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function setRequirement(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'cat' => 'required|integer',
                'name' => 'required|string',
                'stat' => 'required|string'
            ]);

            DB::connection('hrd2')->transaction(function () use (&$validated) {
                $validated['id'] = Clearance::setRequirement($validated);
            });

            return response()->json(['success' => true, 'id' => $validated['id']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function printClearance($id, $type)
    {
        $emplist = Employee::employeeList();
        $departmentList = Setting::departmentList(0);
        $positionList = Setting::positionList(0);
        $emplStatusList = Setting::emplStatusList();

        $cat = DB::connection('hrd2')->table('db_ecf2.tbl_category')
            ->join('db_ecf2.tbl_req_category', function ($join) use ($id) {
                $join->on('catstat_cat', '=', 'cat_id')
                    ->where('catstat_ecfid', $id);
            })
            ->orderBy('cat_company')
            ->orderBy('cat_priority')
            ->orderBy('cat_id')
            ->get();

        $requirements = DB::connection('hrd2')->table('db_ecf2.tbl_requirement')
            ->leftJoin('db_ecf2.tbl_cat_req', function ($j) use ($id) {
                $j->on('catreq_reqid', '=', 'req_id')
                    ->where('catreq_ecfid', $id);
            })
            ->whereIn('req_cat', $cat->pluck('cat_id'))
            ->orWhereNotNull('catreq_id')
            ->orderBy('req_cat')
            ->orderBy('req_id')
            ->get()
            ->map(function ($item) use ($emplist) {
                if (!empty($item->catreq_clearedby)) {
                    $clearedby = $emplist->where('pers_empno', $item->catreq_clearedby)->last();
                    $item->clearedby = $clearedby->pers_lastname . trim(" " . ($clearedby->pers_suffix ?? '')) . ", " . $clearedby->pers_firstname;
                }

                return $item;
            });
        
        $clearance = Clearance::find($id);
        $clearance->job = Employee::showCurrentJobInfo($clearance->ecf_empno);
        $clearance->ecf_hireddt = $clearance->ecf_hireddt ?? $clearance->job['jobinfo']?->ji_datehired;
        $clearance->position_name = $positionList->where('jd_code', '=', $clearance->ecf_pos)->last()?->jd_title;
        $clearance->dept_name = $departmentList->where('Dept_Code', '=', $clearance->ecf_dept)->last()?->Dept_Name;
        $clearance->empinfo = Employee::personalInfo($clearance->ecf_empno);
        $clearance->sex = $clearance->empinfo?->pers_sex;
        $clearance->contact = $clearance->empinfo?->cont_person_num;
        
        if($clearance->sex == 'Male'){
            $clearance->pronoun = [
                'he', 'him', 'Mr.', 'his'
            ];
        }else if($clearance->sex == 'Female'){
            $clearance->pronoun = [
                'she', 'her', 'Ms.', 'her'
            ];
        }else{
            $clearance->pronoun = [
                'he/she', 'him/her', 'Mr./Ms.', 'his/her'
            ];
        }

        return view('pages.clearance.print', [
            'type' => $type,
            'clearance' => $clearance,
            'catList' => $cat->map(function ($item) use ($requirements, $emplist) {
                $clearedby = $emplist->where('pers_empno', $item->catstat_emp)->last();
                $item->clearedby = trim($clearedby->pers_firstname . ' ' . getNameInitials($clearedby->pers_midname)) . ' ' . trim($clearedby->pers_lastname . ' ' . ($clearedby->pers_suffix ?? ''));

                $item->requirements = $requirements->filter(fn($r) => $r->req_cat == $item->cat_id && ((!empty($r->catreq_id) && $r->catreq_catstatid == $item->catstat_id) || empty($r->catreq_id)));

                // if ($item->catstat_stat == 'cleared') {
                //     $item->requirements = $item->requirements?->sortBy([['catreq_required', 'desc'], ['catreq_reqid', 'asc']]);
                // }

                return $item;
            }),
            'employees' => $emplist
        ]);
    }

    public static function storeAttachment(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'clr' => 'required|integer',
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'desc' => 'required|string'
            ]);
            
            DB::connection('hrd2')->transaction(function () use (&$validated, $request) {
                $validated['filename'] = '';
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = time() . '_clr-' . str_replace(',', ' ', $file->getClientOriginalName());
                    // $file->move($_SERVER['DOCUMENT_ROOT'] . '/upload_files_here', $fileName);

                    if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                        $fileName = reduceImageFileSizeToWebP(
                            's3',
                            $file->getRealPath(), 
                            1024, 
                            'clearance/'.$fileName
                        );
                    }else{
                        $file->storeAs('clearance', $fileName, 's3');
                    }

                    $validated['filename'] = basename($fileName);
                }

                DB::connection('hrd2')->table('db_ecf2.tbl_uploads')->insert([
                    'up_ecfid' => $validated['clr'],
                    'up_desc' => $validated['desc'],
                    'up_file' => $validated['filename'],
                    'up_timestamp' => now()
                ]);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function removeAttachment($id)
    {
        try {
            DB::connection('hrd2')->table('db_ecf2.tbl_uploads')->where('up_id', $id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function getAttachments($id)
    {
        $data = DB::connection('hrd2')->table('db_ecf2.tbl_uploads')
            ->where('up_ecfid', $id)
            ->get();
        $html = '';
        $html .= '<table class="table table-sm">';
        foreach ($data as $v) {
            $html .= '<tr>';
            $html .= '<td><a target="_blank" href="' . config('app.url') . '/file/get/clearance/' . $v->up_file . '">' . $v->up_desc . '</a></td>';
            $html .= '<td><button class="btn btn-danger btn-sm py-0" onclick="delAttachment(' . $v->up_id . ')"><i class="fa fa-times"></i></button></td>';
            $html .= '</tr>';
        }
        
        if($data->count() == 0){
            $html .= '<tr><td>- No attachment -</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }
}
