<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Memo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MemoController extends Controller
{
    public static function index()
    {
        // $user = Auth::user();
        return view('pages.memo', [
            'main_link' => 'memo',
            'sub_link' => '',
            'user_empno' => Auth::user()->Emp_No,
            'maincat' => 'company',
            'page' => 'pages.memo',
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

    public static function list()
    {
        // $user = Auth::user();

        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Memo #</th>';
        $html .= '<th class="text-start">Date</th>';
        $html .= '<th>Subject</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (
            Memo::all()->sortBy([
                ['memo_date', 'desc'],
                ['memo_no', 'asc']
            ]) as $v
        ) {
            $html .= '<td class="text-nowrap">' . $v->memo_no . '</td>';
            $html .= '<td class="text-nowrap text-start">' . $v->memo_date . '</td>';
            $html .= '<td>' . $v->memo_subject . '</td>';
            $html .= '<td class="text-nowrap">';
            $html .= '<button class="m-1 btn btn-sm btn-outline-info"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-view-memo"
                        data-id="' . $v->memo_id . '"
                        data-subject="' . $v->memo_subject . '"
                        data-file="' . config('app.url') . '/file/get/memo/' . $v->memo_pdf . '"><i class="fa fa-eye"></i></button>';

            $html .= '<button class="m-1 btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-memo"
                        data-id="' . $v->memo_id . '"
                        data-subject="' . $v->memo_subject . '"
                        data-recipient-type="' . $v->memo_recipienttype . '"
                        data-company="' . $v->memo_recipientcompany . '"
                        data-department="' . $v->memo_recipientdept . '"
                        data-area="' . ($v->memo_recipienttype == 'Area' ? $v->memo_recipient : '') . '"
                        data-outlet="' . ($v->memo_recipienttype == 'Outlet' ? $v->memo_recipient : '') . '"
                        data-employee="' . ($v->memo_recipienttype == 'Employee' ? $v->memo_recipient : '') . '"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="m-1 btn btn-sm btn-outline-danger" onclick="remove_memo(' . $v->memo_id . ')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function store(Request $request)
    {
        try {
            $user = Auth::user();
            $user_pos = $user->JobPosition;

            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'subject' => 'required|string',
                'recipient-type' => 'required|string',
                'recipient-list' => 'required|string',
                'file' => 'nullable|file|mimes:pdf',
            ]);

            $validated['filename'] = '';
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . str_replace(',', ' ', $file->getClientOriginalName());
                // $file->storeAs('announcement', $fileName, 'custom_s3');
                // $file->move($_SERVER['DOCUMENT_ROOT'] . '/zen/assets/memo', $fileName);
                // $file->storeAs('memo', $fileName, 's3');

                if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                    $fileName = basename(reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1024, 
                        'memo/'.$fileName
                    ));
                }else{
                    $file->storeAs('memo', $fileName, 's3');
                }

                $validated['filename'] = $fileName;
            }

            // $employeeList = DB::table('tbl201_persinfo')
            //     ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            //     ->leftJoin('tbl201_jobrec', function ($join) {
            //         $join->on('jrec_empno', '=', 'pers_empno')
            //             ->on('jrec_status', '=', DB::raw("'Primary'"));
            //     })
            //     ->leftJoin('tbl_company', 'C_Code', '=', 'jrec_company')
            //     ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            //     ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jrec_position')
            //     ->whereRaw('FIND_IN_SET(pers_empno, ?) > 0', [$validated['recipient-list']])
            //     ->orderBy('C_Name', 'asc')
            //     ->orderBy('Dept_Name', 'asc')
            //     ->orderBy('pers_lastname', 'asc')
            //     ->orderBy('pers_firstname', 'asc')
            //     ->get();

            // $recipients = $employeeList->filter(fn($e) => strpos($validated['recipient-list'], $e->pers_empno) !== false);

            $data = [
                'memo_subject' => $validated['subject'],
                'memo_date' => date('Y-m-d'),
                'memo_sender' => $user->Emp_No,
                'memo_senderpos' => $user_pos->jrec_position,
                'memo_recipienttype' => $validated['recipient-type'],
                'memo_recipient' => !in_array($validated['recipient-type'], ['Company', 'Department']) ? $validated['recipient-list'] : '',
                // 'memo_recipientpos' => $validated['recipient-type'] == 'Employee' ? $recipients->pluck('jrec_pos')->implode(',') : '',
                'memo_recipientdept' => $validated['recipient-type'] == 'Department' ? $validated['recipient-list'] : '',
                'memo_recipientcompany' => $validated['recipient-type'] == 'Company' ? $validated['recipient-list'] : ''
            ];

            if ($validated['filename']) {
                $data['memo_pdf'] = $validated['filename'];
            }

            if ($validated['id']) {
                $memo = Memo::where('memo_id', $validated['id']);
                $memo->update($data);
                DB::table('tbl_memo_read')->where('read_memo_no', $memo->first()->memo_no)->delete();
            } else {
                $last_memo_no = Memo::where('memo_senderdept', $user_pos->jrec_department)
                    ->orderBy('memo_id', 'desc')
                    ->first()
                    ->memo_no;
                $last_memo_no = explode('-', $last_memo_no);
                $data['memo_no'] = strtoupper(implode('-', [
                    date('y-m'),
                    str_pad((int)$last_memo_no[2], 3, '0', STR_PAD_LEFT),
                    $user_pos->jrec_department
                ]));
                Memo::insert($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            Memo::where('memo_id', $id)->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function readMemo($id)
    {
        try {
            $memo = Memo::where('memo_id', $id)->first();
            DB::table('tbl_memo_read')->insert([
                'read_memo_no' => $memo->memo_no,
                'read_empno' => Auth::user()->Emp_No
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
