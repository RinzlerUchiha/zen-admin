<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Grievance13A;
use App\Models\GrievanceIR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GrievanceIRController extends Controller
{
    public function index()
    {
        return view('pages.grievance.ir', ['list' => GrievanceIR::all()->groupBy('ir_stat')]);
    }

    public static function show($id = null)
    {
        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $user = Auth::user();

        if ($id) {
            $data = GrievanceIR::findIR($id)->first();
        }

        if (empty($data)) {
            $data = new GrievanceIR();
            $data->ir_from = $user->Emp_No;
            $data->from_name = $user->LastFirstName;
            $data->ir_date = date('Y-m-d');
        } else {
            $data->ir_read = array_filter(explode(',', $data->ir_read));
            $data->ir_read[] = $user->Emp_No;

            DB::connection('hrd2')->table('tbl_ir')
                ->where('ir_id', $data->ir_id)
                ->update([
                    'ir_read' => implode(',', array_unique($data->ir_read))
                ]);
        }

        $forwardList = GrievanceIR::findForwardList($id);

        return view('pages.grievance.ir', [
            'data' => $data,
            'user_empno' => $user->Emp_No,
            'employees' => $employees,
            'forwardList' => $forwardList,
            'remarks' => GrievanceIR::findRemarks($id),
            'attachments' => GrievanceIR::findAttachments($id),
            '_13a' => GrievanceIR::find13AOfIR($id),
            'forwarded_to_me' => $forwardList->contains('irf_to', $user->Emp_No)
        ]);
    }

    public static function loadList($stat)
    {
        $html = '<table class="table table-sm table-hover table-striped" style="width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Date</th>';
        $html .= '<th>From</th>';
        $html .= '<th>To</th>';
        $html .= '<th>Subject</th>';
        $html .= $stat == 'resolved' ? '<th>Remarks</th>' : '';
        $html .= '<th>Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (GrievanceIR::loadList($stat, Auth::user()) as $v) {
            $html .= '<tr class="position-relative" ondblclick=viewIR("' . $v->ir_id . '")>';
            $html .= '<td class="text-nowrap">' . $v->ir_date . '</td>';
            $html .= '<td>' . $v->from_name . '</td>';
            $html .= '<td>' . $v->to_name . '</td>';
            $html .= '<td>' . $v->ir_subject . '</td>';
            $html .= $stat == 'resolved' ? '<td>' . $v->ir_resolve_remarks . '</td>' : '';
            $html .= '<td>' . (strpos($v->ir_read, Auth::user()->Emp_No) !== false ? 'Read' : 'Unread') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
        return response($html)->header('Content-Type', 'text/html');
    }

    public static function saveIR(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'to' => 'required|string',
                'cc' => 'nullable|string',
                'subject' => 'required|string',
                'incidentdt' => 'required|date|before_or_equal:today',
                'incidentloc' => 'nullable|string',
                'auditfind' => 'required|string|max:3',
                'persinvolved' => 'required|string',
                'violation' => 'required|string',
                'amount' => 'nullable|string',
                'desc' => 'nullable|string',
                'resp1' => 'nullable|string',
                'resp2' => 'nullable|string',
                'stat' => 'required|string'
            ]);

            // if ($request->hasFile('educcertificate-attachment')) {
            //     $file = $request->file('educcertificate-attachment');
            //     $fileName = time() . '_' . $file->getClientOriginalName();
            //     $path = $file->storeAs('', $fileName, 'custom_s3');
            // }

            // DB::transaction(function () use ($validated) {
            //     // Insert the data into the table
            // });

            $user_empno = Auth::user()->Emp_No;
            $ir_creator_details = Employee::showCurrentJobInfo($user_empno);

            if ($validated['id']) {
                $ir = GrievanceIR::find($validated['id']);
                $ir->update([
                    'ir_to' => $validated['to'],
                    'ir_cc' => $validated['cc'],
                    'ir_from' => $user_empno,
                    'ir_date' => now()->format('Y-m-d'),
                    'ir_subject' => $validated['subject'],
                    'ir_incidentdate' => $validated['incidentdt'],
                    'ir_incidentloc' => $validated['incidentloc'],
                    'ir_auditfindings' => $validated['auditfind'],
                    'ir_involved' => $validated['persinvolved'],
                    'ir_violation' => $validated['violation'],
                    'ir_amount' => $validated['amount'],
                    'ir_desc' => $validated['desc'],
                    'ir_reponsibility_1' => $validated['resp1'],
                    'ir_reponsibility_2' => $validated['resp2'],
                    'ir_pos' => $ir_creator_details['jobrec']->jrec_position,
                    'ir_outlet' => $ir_creator_details['jobrec']->jrec_outlet,
                    'ir_dept' => $ir_creator_details['jobrec']->jrec_department,
                    'ir_stat' => $validated['stat'],
                    'ir_read' => $user_empno
                ]);
            } else {
                $ir = GrievanceIR::create([
                    'ir_to' => $validated['to'],
                    'ir_cc' => $validated['cc'],
                    'ir_from' => $user_empno,
                    'ir_date' => now()->format('Y-m-d'),
                    'ir_subject' => $validated['subject'],
                    'ir_incidentdate' => $validated['incidentdt'],
                    'ir_incidentloc' => $validated['incidentloc'],
                    'ir_auditfindings' => $validated['auditfind'],
                    'ir_involved' => $validated['persinvolved'],
                    'ir_violation' => $validated['violation'],
                    'ir_amount' => $validated['amount'],
                    'ir_desc' => $validated['desc'],
                    'ir_reponsibility_1' => $validated['resp1'],
                    'ir_reponsibility_2' => $validated['resp2'],
                    'ir_pos' => $ir_creator_details['jobrec']->jrec_position,
                    'ir_outlet' => $ir_creator_details['jobrec']->jrec_outlet,
                    'ir_dept' => $ir_creator_details['jobrec']->jrec_department,
                    'ir_stat' => $validated['stat'],
                    'ir_read' => $user_empno
                ]);
            }

            if ($validated['stat'] == 'posted') {
                return response()->json(['success' => true]);
            }
            return GrievanceIRController::show($ir->ir_id);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function deleteIR($id)
    {
        try {
            DB::connection('hrd2')->transaction(function () use ($id) {
                $ir = GrievanceIR::find($id);
                if ($ir) {
                    $ir->delete();
                    GrievanceIR::deleteAttachmentByIR($id);
                    GrievanceIR::deleteForwardByIR($id);
                    GrievanceIR::deleteRemarkByIR($id);
                }
            });
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveIRWitness(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'witnesses' => 'required|string'
            ]);

            $ir = GrievanceIR::find($validated['ir']);
            $ir->update([
                'ir_witness' => $validated['witnesses'],
                'ir_read' => Auth::user()->Emp_No
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveIRAttachment(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'attach_type' => 'required|string',
                'audit_date' => 'nullable|date',
                'irattachments.*' => 'mimes:jpg,jpeg,png,pdf'
            ]);

            if ($request->hasFile('irattachments')) {
                $uploadedFiles = $request->file('irattachments');
                foreach ($uploadedFiles as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    // $file->storeAs('ir', $fileName, 's3');

                    if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                        $fileName = basename(reduceImageFileSizeToWebP(
                            's3',
                            $file->getRealPath(), 
                            1024, 
                            'ir/'.$fileName
                        ));
                    }else{
                        $file->storeAs('ir', $fileName, 's3');
                    }

                    GrievanceIR::saveAttachment([
                        'ir' => $validated['ir'],
                        'attach_type' => $validated['attach_type'],
                        'audit_date' => $validated['audit_date'],
                        'file' => $fileName
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function deleteIRAttachment($ir, $id)
    {
        try {
            GrievanceIR::deleteAttachment($ir, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function serveAttachment($filename)
    {
        // Check if the user is authorized to access the file
        if (Auth::check()) {
            // Check if the file exists in the private disk
            if (Storage::disk('s3')->exists($filename)) {
                // Get the file contents
                $file = Storage::disk('s3')->get($filename);

                return response($file, 200)
                    // ->header('Content-Type', 'image/jpeg')  // Adjust the MIME type based on your file
                    ->header('Content-Type', 'application/octet-stream')
                    ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
            }
        }

        // If not authorized or the file doesn't exist, return a 404 response
        abort(404, 'File not found or unauthorized');
    }

    public static function saveIRExplanation(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'remarks' => 'required|string'
            ]);

            $validated['empno'] = Auth::user()->Emp_No;

            $ir = GrievanceIR::find($validated['ir']);
            $ir->update([
                'ir_stat' => 'needs explanation',
                'ir_read' => Auth::user()->Emp_No
            ]);

            GrievanceIR::saveRemark($validated);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveIRMeeting(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'place' => 'required|string',
                'datetime' => 'required|date_format:Y-m-d H:i:s'
            ]);

            $ir = GrievanceIR::find($validated['ir']);
            $ir->update([
                'ir_meetplace' => $validated['place'],
                'ir_meetdatetime' => $validated['datetime'],
                'ir_read' => Auth::user()->Emp_No
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveIRForward(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'to' => 'required|string'
            ]);

            GrievanceIR::saveForward($validated);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveIRResolve(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'remarks' => 'required|string',
            ]);

            $ir = GrievanceIR::find($validated['ir']);
            $ir->update([
                'ir_resolve_remarks' => $validated['remarks'],
                'ir_stat' => 'resolved'
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveIRSign(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'ir' => 'required|numeric',
                'sign' => 'required|string',
            ]);

            $ir = GrievanceIR::find($validated['ir']);
            $ir->update(['ir_signature' => $validated['sign']]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function getNotification()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->userAccess('grievance', 'review')) {
            $data = DB::connection('hrd2')->select("SELECT ir_stat, COUNT(ir_id) AS cnt 
                FROM tbl_ir 
                WHERE ir_stat = 'needs explanation' OR ir_stat = 'posted'
                GROUP BY ir_stat");
        } else {
            $data = DB::connection('hrd2')->select(
                "SELECT a.ir_stat, COUNT(a.ir_id) AS cnt 
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
                GROUP BY ir_stat",
                [':empno' => $user->Emp_No]
            );
        }

        return collect($data)->mapWithKeys(fn($i) => [$i->ir_stat => $i->cnt]);
    }
}
