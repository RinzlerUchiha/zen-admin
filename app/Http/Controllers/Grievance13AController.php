<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Grievance13A;
use App\Models\GrievanceIR;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class Grievance13AController extends Controller
{
    public static function loadList($stat)
    {
        $user = Auth::user();

        $html = '<table class="table table-sm table-hover table-striped" style="width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Memo No</th>';
        $html .= '<th>Date</th>';
        $html .= '<th>From</th>';
        $html .= '<th>To</th>';
        $html .= '<th>Regarding</th>';
        if ($stat == 'needs explanation' || $stat == 'cancelled') {
            $html .= '<th>Remarks</th>';
        }
        $html .= '<th>Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Grievance13A::loadList($stat, Auth::user()) as $v) {
            $html .= '<tr class="position-relative" ondblclick=view13A("' . $v->{'13a_id'} . '")>';
            $html .= '<td class="text-nowrap">' . $v->{'13a_memo_no'} . '</td>';
            $html .= '<td class="text-nowrap">' . $v->{'13a_date'} . '</td>';
            $html .= '<td>' . $v->from_name . '</td>';
            $html .= '<td>' . $v->to_name . '</td>';
            $html .= '<td>' . $v->{'13a_regarding'} . '</td>';
            if ($stat == 'needs explanation' || $stat == 'cancelled') {
                $html .= '<td>' . $v->remarks . '</td>';
            }
            $html .= '<td>' . ((!empty($v->{'13ar_id'}) && strpos($v->{'13ar_read'}, $user->Emp_No) === false) || strpos($v->{'13a_read'}, $user->Emp_No) === false ? 'Unread' : 'Read') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
        return response($html)->header('Content-Type', 'text/html');
    }

    public static function show(Request $request, $id = null)
    {
        $user = Auth::user();
        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $ir_id = $request->{'ir'} ?? '';

        if ($id) {
            $data = Grievance13A::find13A($id)->first();
        }

        if (empty($data)) {
            $data = new Grievance13A();
            $data->{'13a_from'} = $user->Emp_No;
            $data->from_name = $user->LastFirstName;
            $data->{'13a_frompos'} = $user->JobPosition->jrec_position ?? '';
            $data->{'13a_date'} = date('Y-m-d');

            $ir_data = GrievanceIR::findIR($ir_id);
            $ir_data_first = $ir_data->first();

            $data->{'13a_act'} = $ir_data_first->ir_desc ?? '';
            $data->{'13a_cc'} = $ir_data_first->ir_cc ?? '';
            $data->{'13a_to'} = $ir_data_first->ir_involved ?? '';
            $data->{'13a_ir'} = $ir_data_first->ir_id ?? '';
        } else {
            $ir_data = Grievance13A::findByIROf13A($id);

            $data->{'13a_read'} = array_filter(explode(',', $data->{'13a_read'}));
            $data->{'13a_read'}[] = $user->Emp_No;

            DB::connection('hrd2')->table('tbl_13a')
                ->where('13a_id', $data->{'13a_id'})
                ->update([
                    '13a_read' => implode(',', array_unique($data->{'13a_read'}))
                ]);
        }

        $reply = Grievance13A::find13AReply($id);

        $signatures = Grievance13A::find13ASignatures($id)->groupBy('gs_signtype');
        // if($signatures){
        //     $signatures = $signatures->groupBy('gs_signtype');
        // }

        $signed_noted = !empty($signatures['reviewed']) ? $signatures['reviewed']->contains(fn($sign) => strpos($data->{'13a_notedby'}, $sign->gs_empno) !== false && $sign->gs_empno == $user->Emp_No) : false;

        $signed_issued = !empty($signatures['issued']) ? $signatures['issued']->contains(fn($sign) => strpos($data->{'13a_issuedby'}, $sign->gs_empno) !== false) : false;

        $signed_witness = !empty($signatures['witness']) ? $signatures['witness']->contains(fn($sign) => strpos($data->{'13a_witness'}, $sign->gs_empno) !== false && $sign->gs_empno == $user->Emp_No) : false;

        $positionList = Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]);
        $departmentList = Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]);
        $companyList = Setting::companyList()->mapWithKeys(fn($c) => [$c->C_Code => $c]);
        $employeeLatestJobInfo = Employee::employeeLatestJobInfo();
        // $employeeLatestJobInfo['jobrec'] = $employeeLatestJobInfo['jobrec']->groupBy('jrec_empno');

        // $data->{'13a_notedby'} = explode(',', $data->{'13a_notedby'});
        $data->{'13a_notedbypos'} = explode(',', $data->{'13a_notedbypos'} ?? '');

        // $data->{'13a_witness'} = explode(',', $data->{'13a_witness'});
        $data->{'13a_witnesspos'} = explode(',', $data->{'13a_witnesspos'} ?? '');

        $violations = Grievance13A::findViolation13A($id);

        $_13a_violation_unique = [];
        foreach ($violations as $vv) {
            $othersrc = $vv->{'13av_othersrc'} ? $vv->{'13av_othersrc'} : "Code of Employee Discipline";
            if (empty($_13a_violation_unique[$othersrc][$vv->{'13av_article'}]['section'][$vv->{'13av_section'}])) {
                $_13a_violation_unique[$othersrc][$vv->{'13av_article'}] = [
                    "name" => $vv->{'13av_articlename'},
                    "section" => [
                        $vv->{'13av_section'} => [
                            "name" => $vv->{'13av_sectionname'},
                            "desc" => $vv->{'13av_desc'}
                        ]
                    ]
                ];
            } else {
                $_13a_violation_unique[$othersrc][$vv->{'13av_article'}]['section'][$vv->{'13av_section'}] = [
                    "name" => $vv->{'13av_sectionname'},
                    "desc" => $vv->{'13av_desc'}
                ];
            }
        }

        $violation_str = "";
        $othersrc_cnt = 1;
        $total_othersrc = count($_13a_violation_unique);

        foreach ($_13a_violation_unique as $k => $v) {

            $article_cnt = 1;
            $total_article = count($v);
            foreach ($v as $k2 => $v2) {

                if ($article_cnt == 1) {
                    $violation_str .= ($othersrc_cnt > 1 ? ($othersrc_cnt == $total_othersrc && count($v2['section']) == 1 ? "; and " : "; ") : "") . ($k == "Code of Employee Discipline" ? "our " : "") . $k . " ";
                }

                $violation_str .= ($article_cnt > 1 ? ($article_cnt == $total_article && $othersrc_cnt == $total_othersrc ? "; and " : "; ") : "") . $k2 . " ";

                $section_cnt = 1;
                $total_section = count($v2['section']);

                foreach ($v2['section'] as $k3 => $v3) {
                    $violation_str .= ($section_cnt > 1 ? ($section_cnt == $total_section ? "; and " : "; ") : "") . $k3 . " &#8212; " . $v3['desc'];
                    $section_cnt++;
                }
                $article_cnt++;
            }
            $othersrc_cnt++;
        }

        return view('pages.grievance.13a', [
            'data' => $data,
            'user_empno' => $user->Emp_No,
            'employees' => $employees,
            'positionList' => $positionList,
            'departmentList' => $departmentList,
            'companyList' => $companyList,
            'employeeLatestJobInfo' => $employeeLatestJobInfo,
            'remarks' => Grievance13A::findRemarks($id),
            'ir' => $ir_data,
            '_13a_violations' => $violations,
            'rnrList' => Setting::rnrList(),
            'signatures' => $signatures,
            'signed_noted' => $signed_noted,
            'signed_witness' => $signed_witness,
            'signed_issued' => $signed_issued,
            'reply_id' => $reply->{'13ar_id'} ?? '',
            'reply_read' => $reply ? strpos($reply->{'13ar_read'}, $user->Emp_No) !== false : false,
            'hearing_transcript' => Grievance13A::find13AHearingTranscript($id)->ht_id ?? '',
            '_13b_id' => Grievance13A::findBy13BOf13A($id)->{'13b_id'} ?? '',
            'irList' => GrievanceIR::loadList('posted,resolved', $user),
            'commit_id' => Grievance13A::find13ACommitmentPlan($id)->commit_id ?? '',
            'violation_str' => $violation_str
        ]);
    }

    public static function save13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'to' => 'required|string',
                'cc' => 'nullable|string',
                'from' => 'nullable|string',
                'frompos' => 'nullable|string',
                'act' => 'nullable|string',
                'violation' => 'nullable|string',
                'datetime' => 'nullable|date',
                'place' => 'nullable|string',
                'penalty' => 'nullable|string',
                'offense' => 'nullable|string',
                'offensetype' => 'nullable|string',
                'regarding' => 'nullable|string',
                'stat' => 'required|string',
                'suspendday' => 'nullable|numeric',
                'ir' => 'nullable|string',
                'immediate_action' => 'nullable|numeric'
            ]);

            $user_empno = Auth::user()->Emp_No;
            $to_details = Employee::showCurrentJobInfo($validated['to']);
            $validated['pos'] = $to_details['jobrec']->jrec_position;
            $validated['dept'] = $to_details['jobrec']->jrec_department;
            $validated['company'] = $to_details['jobrec']->jrec_company;
            $validated['violation'] = !empty($validated['violation']) ? json_decode($validated['violation'], true) : [];

            if ($validated['id']) {
                $table = Grievance13A::find($validated['id']);
                $table->update([
                    '13a_to' => $validated['to'],
                    '13a_cc' => $validated['cc'],
                    '13a_pos' => $validated['pos'],
                    '13a_company' => $validated['company'],
                    '13a_dept' => $validated['dept'],
                    '13a_regarding' => $validated['regarding'],
                    '13a_from' => $validated['from'],
                    '13a_frompos' => $validated['frompos'],
                    '13a_act' => $validated['act'],
                    '13a_datetime' => $validated['datetime'],
                    '13a_place' => $validated['place'],
                    '13a_penalty' => $validated['penalty'],
                    '13a_offense' => $validated['offense'],
                    '13a_offensetype' => $validated['offensetype'],
                    // '13a_issuedby' => $validated['issuedby'],
                    // '13a_issuedbypos' => $validated['issuedbypos'],
                    '13a_stat' => $validated['stat'],
                    '13a_suspendday' => $validated['suspendday'],
                    '13a_read' => $user_empno,
                    '13a_immediate_action' => $validated['immediate_action']
                ]);
            } else {
                $validated['issuedby'] = $validated['from'];
                $validated['issuedbypos'] = $validated['frompos'];

                $cnt_13a = Grievance13A::where('13a_date')->count();
                $table = Grievance13A::create([
                    '13a_memo_no' => date("mdy") . "-" . str_pad($cnt_13a + 1, 2, "0", STR_PAD_LEFT),
                    '13a_to' => $validated['to'],
                    '13a_cc' => $validated['cc'],
                    '13a_pos' => $validated['pos'],
                    '13a_company' => $validated['company'],
                    '13a_date' => date('Y-m-d'),
                    '13a_dept' => $validated['dept'],
                    '13a_regarding' => $validated['regarding'],
                    '13a_from' => $validated['from'] ?? $user_empno,
                    '13a_frompos' => $validated['frompos'] ?? Auth::user()->getJobPositionAttribute->jrec_position,
                    '13a_act' => $validated['act'],
                    '13a_datetime' => $validated['datetime'],
                    '13a_place' => $validated['place'],
                    '13a_penalty' => $validated['penalty'],
                    '13a_offense' => $validated['offense'],
                    '13a_offensetype' => $validated['offensetype'],
                    '13a_issuedby' => $validated['issuedby'],
                    '13a_issuedbypos' => $validated['issuedbypos'],
                    '13a_ir' => $validated['ir'],
                    '13a_stat' => $validated['stat'],
                    '13a_suspendday' => $validated['suspendday'],
                    '13a_read' => $user_empno,
                    '13a_immediate_action' => $validated['immediate_action']
                ]);
            }

            Grievance13A::save13AViolations($validated['violation'], $table->{'13a_id'});

            if ($validated['stat'] == 'pending') {
                return response()->json(['success' => true]);
            }

            return Grievance13AController::show(Request::create('/', 'GET'), $table->{'13a_id'});
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete13A($id)
    {
        try {
            DB::connection('hrd2')->transaction(function () use ($id) {
                $table = Grievance13A::find($id);
                if ($table) {
                    DB::connection('hrd2')->table('tbl_grievance_remarks')
                        ->where([
                            ['gr_typeid', '=', $table->{'13a_id'}],
                            ['gr_type', '=', '13a']
                        ])->delete();

                    DB::connection('hrd2')->table('tbl_grievance_sign')
                        ->where([
                            ['gs_typeid', '=', $table->{'13a_id'}],
                            ['gs_type', '=', '13a']
                        ])->delete();

                    $table->delete();
                }
            });
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function save13ANotedBy(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'noted' => 'required|string',
                'notedpos' => 'nullable|string'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_notedby' => $validated['noted'],
                '13a_notedbypos' => $validated['notedpos']
            ]);

            DB::connection('hrd2')->table('tbl_grievance_sign')
                ->whereRaw("FIND_IN_SET(gs_empno, ?) = 0 AND gs_type = '13a' AND gs_typeid = ? AND gs_signtype = 'reviewed'", [$validated['noted'], $validated['id']])->delete();

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function save13AIssuedBy(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'issued' => 'required|string',
                'issuedpos' => 'nullable|string'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_issuedby' => $validated['issued'],
                '13a_issuedbypos' => $validated['issuedpos']
            ]);

            DB::connection('hrd2')->table('tbl_grievance_sign')
                ->whereRaw("FIND_IN_SET(gs_empno, ?) = 0 AND gs_type = '13a' AND gs_typeid = ? AND gs_signtype = 'issued'", [$validated['issued'], $validated['id']])->delete();

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function save13AWitness(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'witness' => 'required|string',
                'witnesspos' => 'nullable|string'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_witness' => $validated['witness'],
                '13a_witnesspos' => $validated['witnesspos']
            ]);

            DB::connection('hrd2')->table('tbl_grievance_sign')
                ->whereRaw("FIND_IN_SET(gs_empno, ?) = 0 AND gs_type = '13a' AND gs_typeid = ? AND gs_signtype = 'witness'", [$validated['witness'], $validated['id']])->delete();

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function save13AHearing(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'datetime' => 'required|string',
                'place' => 'required|string'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_hearing_time' => $validated['datetime'],
                '13a_hearing_loc' => $validated['place'],
                '13a_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function save13AIR(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'ir' => 'required|string'
            ]);

            $table = Grievance13A::find($validated['id']);
            $ir = array_filter(explode(',', $table->{'13a_ir'} ?? ''));
            $ir[] = $validated['ir'];

            $table->update([
                '13a_ir' => implode(',', array_unique($ir))
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function check13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_stat' => 'checked',
                '13a_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function sign13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'signtype' => 'required|string',
                'empno' => 'required|string',
                'sign' => 'required|string'
            ]);

            Grievance13A::sign13A($validated);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function explain13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'remarks' => 'required|string'
            ]);

            $validated['empno'] = Auth::user()->Emp_No;

            Grievance13A::explain13A($validated);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function issue13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_stat' => 'issued',
                '13a_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function refuse13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_stat' => 'refused',
                '13a_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function cancel13A(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'remarks' => 'required|string'
            ]);

            $table = Grievance13A::find($validated['id']);
            $table->update([
                '13a_stat' => 'cancelled',
                '13a_cancel_remarks' => $validated['remarks'],
                '13a_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete13AIR($id, $ir)
    {
        try {
            $table = Grievance13A::find($id);
            if ($table) {
                $irList = explode(',', ($table->{'13a_ir'} ?? ''));
                $newIRList = implode(',', array_filter($irList, fn($item) => $item !== $ir));
                $table->update(['13a_ir' => $newIRList]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function showTranscript($id)
    {
        $user = Auth::user();
        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = Grievance13A::find13A($id)->first();

        $positionList = Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]);
        $departmentList = Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]);
        $companyList = Setting::companyList()->mapWithKeys(fn($c) => [$c->C_Code => $c]);
        $employeeLatestJobInfo = Employee::employeeLatestJobInfo();

        $violations = Grievance13A::findViolation13A($id);

        $_13a_violation_unique = [];
        foreach ($violations as $vv) {
            $othersrc = $vv->{'13av_othersrc'} ? $vv->{'13av_othersrc'} : "Code of Employee Discipline";
            if (empty($_13a_violation_unique[$othersrc][$vv->{'13av_article'}]['section'][$vv->{'13av_section'}])) {
                $_13a_violation_unique[$othersrc][$vv->{'13av_article'}] = [
                    "name" => $vv->{'13av_articlename'},
                    "section" => [
                        $vv->{'13av_section'} => [
                            "name" => $vv->{'13av_sectionname'},
                            "desc" => $vv->{'13av_desc'}
                        ]
                    ]
                ];
            } else {
                $_13a_violation_unique[$othersrc][$vv->{'13av_article'}]['section'][$vv->{'13av_section'}] = [
                    "name" => $vv->{'13av_sectionname'},
                    "desc" => $vv->{'13av_desc'}
                ];
            }
        }

        $violation_str = "";
        $othersrc_cnt = 1;
        $total_othersrc = count($_13a_violation_unique);

        foreach ($_13a_violation_unique as $k => $v) {

            $article_cnt = 1;
            $total_article = count($v);
            foreach ($v as $k2 => $v2) {

                if ($article_cnt == 1) {
                    $violation_str .= ($othersrc_cnt > 1 ? ($othersrc_cnt == $total_othersrc && count($v2['section']) == 1 ? "; and " : "; ") : "") . ($k == "Code of Employee Discipline" ? "our " : "") . $k . " ";
                }

                $violation_str .= ($article_cnt > 1 ? ($article_cnt == $total_article && $othersrc_cnt == $total_othersrc ? "; and " : "; ") : "") . $k2 . " ";

                $section_cnt = 1;
                $total_section = count($v2['section']);

                foreach ($v2['section'] as $k3 => $v3) {
                    $violation_str .= ($section_cnt > 1 ? ($section_cnt == $total_section ? "; and " : "; ") : "") . $k3 . " &#8212; " . $v3['desc'];
                    $section_cnt++;
                }
                $article_cnt++;
            }
            $othersrc_cnt++;
        }

        $hearing_transcript = DB::connection('hrd2')->table('tbl_hearing_transcript as a')
            ->where('ht_13a', $data->{'13a_id'})
            ->first();

        if (!$hearing_transcript) {
            $hearing_transcript = new stdClass();;
            $hearing_transcript->ht_id = '';
            $hearing_transcript->ht_article = '';
            $hearing_transcript->ht_section = '';
            $hearing_transcript->ht_presiding_officer = '';
            $hearing_transcript->ht_scribe = '';
            $hearing_transcript->ht_employee = $data->{'13a_to'};
            $hearing_transcript->ht_datetime_started = '';
            $hearing_transcript->ht_time_ended = '';
            $hearing_transcript->ht_13a = $data->{'13a_id'};
            $hearing_transcript->ht_timestamp = '';
            $hearing_transcript->ht_empsign = '';
            $hearing_transcript->ht_officersign = '';
            $hearing_transcript->ht_scribesign = '';
        }

        $hearing_committee = DB::connection('hrd2')->table('tbl_hearing_transcript as a')
            ->leftJoin('tbl_hearing_committee as b', 'hc_htid', '=', 'ht_id')
            // ->leftJoin('tbl201_persinfo as c', 'pers_empno', '=', 'hc_empno')
            ->where('ht_13a', $data->{'13a_id'})
            // ->select('b.*', 'pers_lastname', 'pers_firstname', 'pers_midname')
            ->get()
            ->map(function ($item) use ($employees) {
                $item->pers_lastname = $employees[$item->{'hc_empno'}]['pers_lastname'] ?? '';
                $item->pers_firstname = $employees[$item->{'hc_empno'}]['pers_firstname'] ?? '';
                $item->pers_midname = $employees[$item->{'hc_empno'}]['pers_midname'] ?? '';

                return $item;
            });

        $hearing_question = DB::connection('hrd2')->table('tbl_hearing_transcript as a')
            ->leftJoin('tbl_hearing_question as b', 'hq_htid', '=', 'ht_id')
            ->where('ht_13a', $data->{'13a_id'})
            ->select('b.*')
            ->get();

        $hq_arr = [];
        foreach ($hearing_question as $v1) {
            $fnd_hq_key = array_search(trim($v1->hq_question), array_column($hq_arr, 0));
            if ($fnd_hq_key !== false) {
                $hq_arr[$fnd_hq_key][1] = $v1->hq_answer;
                $hq_arr[$fnd_hq_key][2] = $v1->hq_id;
            } else {
                $hq_arr[] = [
                    $v1->hq_question,
                    $v1->hq_answer,
                    $v1->hq_id
                ];
            }
        }

        if (empty($hq_arr)) {
            $hq_arr = [
                ["Please state your name and position.", ""],
                ["How long have you been with STI?", ""],
                ["Can you relate to the Committee your duties and responsibilities as <u>" . (isset($positionList[$data->{'13a_pos'}]) ? $positionList[$data->{'13a_pos'}]->jd_title : "") . "</u>?", ""],
                ["Why do you think is this administrative hearing being conducted?", ""],
                ["Are you aware of our policy in our Code of Conduct Article V Section/s <u></u>?", ""],
                ["Can you tell the Committee what happened? ", ""],
                ["What were your reasons? ", ""],
                ["Do you have anyone you would like to call to give us more information about this matter?", ""],
                ["What did you learn from this experience?", ""],
                ["What will you do differently after this administrative hearing is concluded?", ""],
                ["What do you think is the outcome of this administrative hearing?", ""],
                ["Do you have anything to say to the Committee? Or do you want to add anything?", ""],
                ["Do you have any questions for the Committee?", ""]
            ];
        }

        return view('pages.grievance.transcript', [
            '_13a' => $data,
            'user_empno' => $user->Emp_No,
            'employees' => $employees,
            'positionList' => $positionList,
            'departmentList' => $departmentList,
            'companyList' => $companyList,
            'employeeLatestJobInfo' => $employeeLatestJobInfo,
            'violation_str' => $violation_str,
            'hearing_transcript' => $hearing_transcript,
            'hq_arr' => $hq_arr,
            'hearing_committee' => $hearing_committee
        ]);
    }

    public static function saveTranscript(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'presiding_officer' => 'nullable|string',
                'scribe' => 'nullable|string',
                'employee' => 'nullable|string',
                'datetime_started' => 'nullable|date',
                'time_ended' => 'nullable|string',
                '_13a' => 'required|numeric',
                'committee' => 'nullable|string',
                'questions' => 'nullable|string'
            ]);

            $validated['questions'] = !empty($validated['questions']) ? json_decode($validated['questions'], true) : [];

            $ht_table = DB::connection('hrd2')->table('tbl_hearing_transcript')->where('ht_13a', $validated['_13a'])->first();
            if ($ht_table) {
                DB::connection('hrd2')->statement(
                    "UPDATE tbl_hearing_transcript SET 
                        ht_empsign = IF(ht_employee = ?, ht_empsign, ''),
                        ht_officersign = IF(ht_presiding_officer = ?, ht_officersign, ''),
                        ht_scribesign = IF(ht_scribe = ?, ht_scribesign, ''),

                        ht_presiding_officer = ?, 
                        ht_scribe = ?, 
                        ht_employee = ?, 
                        ht_datetime_started = ?, 
                        ht_time_ended = ?
                    WHERE
                        ht_13a = ?",
                    [
                        $validated['employee'],
                        $validated['presiding_officer'],
                        $validated['scribe'],
                        $validated['presiding_officer'],
                        $validated['scribe'],
                        $validated['employee'],
                        $validated['datetime_started'],
                        $validated['time_ended'],
                        $validated['_13a']
                    ]
                );
            } else {
                DB::connection('hrd2')->table('tbl_hearing_transcript')
                    ->insert([
                        'ht_13a' => $validated['_13a'],
                        'ht_presiding_officer' => $validated['presiding_officer'],
                        'ht_scribe' => $validated['scribe'],
                        'ht_employee' => $validated['employee'],
                        'ht_datetime_started' => $validated['datetime_started'],
                        'ht_time_ended' => $validated['time_ended']
                    ]);
            }

            $ht_id = DB::connection('hrd2')->table('tbl_hearing_transcript')->where('ht_13a', $validated['_13a'])->first()->ht_id ?? '';
            if ($ht_id) {
                if (is_array($validated['questions'])) {
                    $qid_list = array_filter(array_column($validated['questions'], 2));
                    DB::connection('hrd2')->table('tbl_hearing_question')
                        ->whereRaw('FIND_IN_SET(hq_id, ?) = 0 AND hq_htid = ?', [implode(',', $qid_list), $ht_id])
                        ->delete();
                    foreach ($validated['questions'] as $v) {
                        if (!empty($v[2])) {
                            DB::connection('hrd2')->table('tbl_hearing_question')
                                ->where([
                                    ['hq_htid', '=', $ht_id],
                                    ['hq_id', '=', $v[2]]
                                ])
                                ->update([
                                    'hq_question' => $v[0],
                                    'hq_answer' => $v[1]
                                ]);
                        } else {
                            DB::connection('hrd2')->table('tbl_hearing_question')
                                ->insert([
                                    'hq_question' => $v[0],
                                    'hq_answer' => $v[1],
                                    'hq_htid' => $ht_id
                                ]);
                        }
                    }
                }

                $comlist = [];
                $table_committee = DB::connection('hrd2')->table('tbl_hearing_committee')
                    ->whereRaw('FIND_IN_SET(hc_empno, ?) > 0 AND hc_htid = ?', [$validated['committee'], $ht_id])
                    ->get();
                foreach ($table_committee as $v) {
                    $comlist[] = $v['hc_empno'];
                }

                DB::connection('hrd2')->table('tbl_hearing_committee')
                    ->whereRaw('FIND_IN_SET(hc_empno, ?) = 0 AND hc_htid = ?', [$validated['committee'], $ht_id])
                    ->delete();

                foreach (explode(",", $validated['committee']) as $v) {
                    if (!in_array($v, $comlist)) {
                        DB::connection('hrd2')->table('tbl_hearing_committee')
                            ->insert([
                                'hc_empno' => $v,
                                'hc_htid' => $ht_id
                            ]);
                    }
                }
            }

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function signTranscript(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                '_13a' => 'required|numeric',
                'type' => 'required|string',
                'empno' => 'required|string',
                'sign' => 'required|string'
            ]);

            if ($validated['type'] == 'committee') {
                DB::connection('hrd2')->table('tbl_hearing_committee')
                    ->where([
                        ['hc_empno', '=', $validated['empno']],
                        ['hc_htid', '=', $validated['id']]
                    ])
                    ->update(['hc_sign' => $validated['sign']]);
            } elseif ($validated['type'] == 'officer') {
                DB::connection('hrd2')->table('tbl_hearing_transcript')
                    ->where([
                        ['ht_id', '=', $validated['id']]
                    ])
                    ->update(['ht_officersign' => $validated['sign']]);
            } elseif ($validated['type'] == 'scribe') {
                DB::connection('hrd2')->table('tbl_hearing_transcript')
                    ->where([
                        ['ht_id', '=', $validated['id']]
                    ])
                    ->update(['ht_scribesign' => $validated['sign']]);
            } elseif ($validated['type'] == 'emp') {
                DB::connection('hrd2')->table('tbl_hearing_transcript')
                    ->where([
                        ['ht_id', '=', $validated['id']]
                    ])
                    ->update(['ht_empsign' => $validated['sign']]);
            }

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function deleteTranscript($id13a)
    {
        try {
            DB::connection('hrd2')->transaction(function () use ($id13a) {
                $ht = DB::connection('hrd2')->table('tbl_hearing_transcript')
                    ->where('ht_13a', $id13a);

                $ht_id = $ht ? $ht->first()->ht_id : '';

                $ht->delete();

                if ($ht_id) {
                    DB::connection('hrd2')->table('tbl_hearing_question')->where('hq_htid', $ht_id)->delete();
                    DB::connection('hrd2')->table('tbl_hearing_committee')->where('hc_htid', $ht_id)->delete();
                }
            });
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function getNotification()
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
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13a_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13a_to')
            ->where('13a_stat', '!=', 'draft');

        if (!$reviewer) {
            $query->whereRaw("FIND_IN_SET(?, CONCAT_WS(',',13a_to, 13a_cc, 13a_from, 13a_issuedby, 13a_notedby)) > 0", [$user->Emp_No]);
        }

        // $query->select('a.*')
        // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
        // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
        $query->orderBy('13a_date', 'desc');

        $data = $query->get()->map(function ($item) use ($persinfo) {
            $item->from_name = isset($persinfo[$item->{'13a_from'}]) ? trim($persinfo[$item->{'13a_from'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_from'}]['pers_firstname']) : '';

            $item->to_name = isset($persinfo[$item->{'13a_to'}]) ? trim($persinfo[$item->{'13a_to'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_to'}]['pers_firstname']) : '';

            return $item;
        });

        $filteredData = $data->filter(function ($d) use ($user, $reviewer) {
            $cnt13b = DB::connection('hrd2')->table('tbl_13b AS a')->where('13b_13a', $d->{'13a_id'})->count();

            $sign_issued = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13a_id'}}' AND gs_type='13a' AND gs_signtype='issued'"));

            $sign_noted = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13a_id'}}' AND gs_type='13a' AND gs_signtype='reviewed' AND gs_empno='{$user->Emp_No}'"));

            $sign_witness = (DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13a_id'}}' AND gs_type='13a' AND gs_signtype='witness' AND gs_empno='{$user->Emp_No}'"));

            if ($d->{'13a_stat'} == "checked" && $sign_noted > 0) {
                $d->{'13a_stat'} = "reviewed";
            }

            $remarks = DB::connection('hrd2')->table('tbl_grievance_remarks AS a')
                ->where([
                    ['gr_typeid', '=', $d->{'13a_id'}],
                    ['gr_type', '=', '13a']
                ])
                ->orderBy('gr_id', 'desc')
                ->first()->gr_remarks ?? '';

            $d->remarks = $remarks;

            return (
                ($user->Emp_No == $d->{'13a_from'} && $d->{'13a_stat'} == 'needs explanation') ||
                (
                    (
                        $reviewer ||
                        strpos($d->{'13a_cc'}, $user->Emp_No) !== false ||
                        $user->Emp_No == $d->{'13a_from'}
                    ) &&
                    (
                        $d->{'13a_stat'} == 'pending' ||
                        (
                            ($d->{'13a_stat'} == "received" || $d->{'13a_stat'} == "refused" || $d->{'13a_stat'} == "cancelled") &&
                            strpos($d->{'13a_read'}, $user->Emp_No) === false
                        )
                    )
                ) ||

                (
                    $user->Emp_No == $d->{'13a_issuedby'} &&
                    (
                        $d->{'13a_stat'} == "reviewed" ||
                        ($d->{'13a_stat'} == "refused" && !$d->{'13a_witness'}) ||
                        $sign_issued == 0
                    )
                ) ||

                ($sign_issued > 0 && $d->{'13a_stat'} == "checked" && strpos($d->{'13a_notedby'}, $user->Emp_No) !== false && $sign_noted == 0) ||

                ($d->{'13a_stat'} == "refused" && strpos($d->{'13a_witness'}, $user->Emp_No) !== false && $sign_witness == 0) ||

                ($user->Emp_No == $d->{'13a_to'} &&
                    (
                        $d->{'13a_stat'} == "issued" ||
                        ($d->{'13a_stat'} == "refused" && strpos($d->{'13a_read'}, $user->Emp_No) === false)
                    )
                ) ||
                (!empty($d->{'13ar_id'}) && strpos($d->{'13ar_read'}, $user->Emp_No) === false)
            );
        });

        $group = $filteredData->groupBy('13a_stat');

        return $group->map(fn($i) => $i->count());
    }

    public static function showCommitmentPlan($id13a)
    {
        $user = Auth::user();
        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data_13a = Grievance13A::find13A($id13a)->first();
        $positionList = Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]);
        $departmentList = Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]);
        // $companyList = Setting::companyList()->mapWithKeys(fn($c) => [$c->C_Code => $c]);
        // $employeeLatestJobInfo = Employee::employeeLatestJobInfo();
        if ($data_13a) {
            $data_13a->to_name_init = isset($employees[$data_13a->{'13a_to'}]) ? trim(ucwords($employees[$data_13a->{'13a_to'}]['pers_firstname'] . " " . getNameInitials($employees[$data_13a->{'13a_to'}]['pers_midname'])) . " " . $employees[$data_13a->{'13a_to'}]['pers_lastname']) : "";
            $data_13a->pos_name = isset($positionList[$data_13a->{'13a_pos'}]) ? $positionList[$data_13a->{'13a_pos'}]->jd_title : "";
            $data_13a->dept_name = isset($departmentList[$data_13a->{'13a_dept'}]) ? $departmentList[$data_13a->{'13a_dept'}]->Dept_Name : "";
        }

        $commitment = DB::connection('hrd2')->table('tbl_commitment_plan')->where('commit_13a', $id13a)->first();

        if (empty($commitment)) {
            $commitment = new stdClass();
            $commitment->commit_id = null;
            $commitment->commit_13a = $id13a;
            $commitment->commit_preparedby = $data_13a->{'13a_to'} ?? null;
            $commitment->commit_agreedby = $data_13a->{'13a_issuedby'} ?? null;
            $commitment->commit_date = date("Y-m-d");
            $commitment->commit_preparedby_sign = null;
            $commitment->commit_agreedby_sign = null;
            $commitment->commit_read = null;
            $commitment->plan_info = [];
        } else {
            $commitment->plan_info = DB::connection('hrd2')->table('tbl_commitment_plan_info')->where('cpinfo_commitid', $commitment->commit_id)->get();

            $commitment->commit_read = array_filter(explode(',', $commitment->commit_read));
            $commitment->commit_read[] = $user->Emp_No;

            DB::connection('hrd2')->table('tbl_commitment_plan')
                ->where('commit_13a', $id13a)
                ->update([
                    'commit_read' => implode(',', array_unique($commitment->commit_read))
                ]);
        }

        $commitment->commit_preparedby_name_init = isset($employees[$commitment->commit_preparedby]) ? trim(ucwords($employees[$commitment->commit_preparedby]['pers_firstname'] . " " . getNameInitials($employees[$commitment->commit_preparedby]['pers_midname'])) . " " . $employees[$commitment->commit_preparedby]['pers_lastname']) : "";

        $commitment->commit_agreedby_name_init = isset($employees[$commitment->commit_agreedby]) ? trim(ucwords($employees[$commitment->commit_agreedby]['pers_firstname'] . " " . getNameInitials($employees[$commitment->commit_agreedby]['pers_midname'])) . " " . $employees[$commitment->commit_agreedby]['pers_lastname']) : "";

        return view('pages.grievance.commitment-plan', [
            '_13a' => $data_13a,
            'user_empno' => $user->Emp_No,
            'commitment' => $commitment,
            '_13b_id' => Grievance13A::findBy13BOf13A($data_13a->{'13a_id'} ?? '')->{'13b_id'} ?? ''
        ]);
    }

    public static function saveCommitmentPlan(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                '_13a' => 'required|numeric',
                'preparedby' => 'required|string',
                'agreedby' => 'required|string',
                'cp' => 'nullable|string'
            ]);

            $validated['cp'] = !empty($validated['cp']) ? json_decode($validated['cp'], true) : [];

            $cpinfo_id = !empty($validated['cp']) ? array_column($validated['cp'], 3) : [];

            $commit = DB::connection('hrd2')->table('tbl_commitment_plan')->where('commit_13a', $validated['_13a'])->first();
            $commit_id = $commit->commit_id ?? '';
            if ($commit) {
                $update = DB::connection('hrd2')->statement(
                    "UPDATE tbl_commitment_plan SET 
                        commit_preparedby = ?,
                        commit_agreedby = ?
                    WHERE
                        commit_13a = ?",
                    [
                        $validated['preparedby'],
                        $validated['agreedby'],
                        $validated['_13a']
                    ]
                );
            } else {
                $commit_id = DB::connection('hrd2')->table('tbl_commitment_plan')
                    ->insertGetId([
                        'commit_13a' => $validated['_13a'],
                        'commit_preparedby' => $validated['preparedby'],
                        'commit_agreedby' => $validated['agreedby'],
                        'commit_date' => date('Y-m-d')
                    ], 'commit_id');
            }

            DB::connection('hrd2')->delete("DELETE FROM tbl_commitment_plan_info WHERE cpinfo_commitid = ? AND FIND_IN_SET(cpinfo_id, ?) = 0", [$commit_id, implode(',', $cpinfo_id)]);

            foreach ($validated['cp'] as $v) {
                if ($v[3] != "") {
                    DB::connection('hrd2')->update("UPDATE tbl_commitment_plan_info SET cpinfo_learn = ?, cpinfo_commit = ?, cpinfo_start = ? WHERE cpinfo_id = ? AND cpinfo_commitid = ?", [$v[0], $v[1], $v[2], $v[3], $commit_id]);
                } else {
                    DB::connection('hrd2')->insert("INSERT INTO tbl_commitment_plan_info (cpinfo_learn, cpinfo_commit, cpinfo_start, cpinfo_commitid) VALUES (?, ?, ?, ?)", [$v[0], $v[1], $v[2], $commit_id]);
                }
            }

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function signCommitmentPlan(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                '_13a' => 'required|numeric',
                'type' => 'required|string',
                'empno' => 'nullable|string',
                'sign' => 'required|string'
            ]);

            if ($validated['type'] == 'preparedby') {
                DB::connection('hrd2')->table('tbl_commitment_plan')
                    ->where([
                        ['commit_13a', '=', $validated['_13a']],
                        ['commit_id', '=', $validated['id']]
                    ])
                    ->update(['commit_preparedby_sign' => $validated['sign']]);
            } elseif ($validated['type'] == 'agreedby') {
                DB::connection('hrd2')->table('tbl_commitment_plan')
                    ->where([
                        ['commit_13a', '=', $validated['_13a']],
                        ['commit_id', '=', $validated['id']]
                    ])
                    ->update(['commit_agreedby_sign' => $validated['sign']]);
            }

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function showLetterOfReply($id13a)
    {
        $user = Auth::user();
        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data_13a = Grievance13A::find13A($id13a)->first();
        $positionList = Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]);
        $departmentList = Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]);
        $companyList = Setting::companyList()->mapWithKeys(fn($c) => [$c->C_Code => $c]);

        $data_13a->{'13a_issuedbydept'} = empty($data_13a->{'13a_issuedbydept'}) && isset($employees[$data_13a->{'13a_issuedby'}]) ? $employees[$data_13a->{'13a_issuedby'}]['jrec_department'] : ($data_13a->{'13a_issuedbydept'} ?? '');
        $data_13a->{'13a_issuedbycompany'} = empty($data_13a->{'13a_issuedbycompany'}) && isset($employees[$data_13a->{'13a_issuedby'}]) ? $employees[$data_13a->{'13a_issuedby'}]['jrec_company'] : ($data_13a->{'13a_issuedbycompany'} ?? '');

        if ($data_13a) {
            $data_13a->to_name_init = isset($employees[$data_13a->{'13a_to'}]) ? trim(ucwords($employees[$data_13a->{'13a_to'}]['pers_firstname'] . " " . getNameInitials($employees[$data_13a->{'13a_to'}]['pers_midname'])) . " " . $employees[$data_13a->{'13a_to'}]['pers_lastname']) : "";
            $data_13a->pos_name = isset($positionList[$data_13a->{'13a_pos'}]) ? $positionList[$data_13a->{'13a_pos'}]->jd_title : "";
            $data_13a->dept_name = isset($departmentList[$data_13a->{'13a_dept'}]) ? $departmentList[$data_13a->{'13a_dept'}]->Dept_Name : "";

            $data_13a->issuedby_name_init = isset($employees[$data_13a->{'13a_issuedby'}]) ? trim(ucwords($employees[$data_13a->{'13a_issuedby'}]['pers_firstname'] . " " . getNameInitials($employees[$data_13a->{'13a_issuedby'}]['pers_midname'])) . " " . $employees[$data_13a->{'13a_issuedby'}]['pers_lastname']) : "";
            $data_13a->issuedby_pos_name = isset($positionList[$data_13a->{'13a_issuedbypos'}]) ? $positionList[$data_13a->{'13a_issuedbypos'}]->jd_title : "";
            $data_13a->issuedby_dept_name = isset($departmentList[$data_13a->{'13a_issuedbydept'}]) ? $departmentList[$data_13a->{'13a_issuedbydept'}]->Dept_Name : "";
            $data_13a->issuedby_company_name = isset($companyList[$data_13a->{'13a_issuedbycompany'}]) ? $companyList[$data_13a->{'13a_issuedbycompany'}]->C_Name : "";
        }

        $letter = DB::connection('hrd2')->table('tbl_13a_reply')->where('13ar_13aid', $id13a)->first();

        if (empty($letter)) {
            $letter = new stdClass();
            $letter->{'13ar_id'} = null;
            $letter->{'13ar_13aid'} = $id13a;
            $letter->{'13ar_reply'} = "Dear Ma’am/Sir,\r\n\r\n";
            $letter->{'13ar_sign'} = null;
            $letter->{'13ar_read'} = null;
            $letter->{'13ar_timestamp'} = null;
        } else {
            $letter->{'13ar_read'} = array_filter(explode(',', $letter->{'13ar_read'}));
            $letter->{'13ar_read'}[] = $user->Emp_No;

            DB::connection('hrd2')->table('tbl_13a_reply')
                ->where('13ar_13aid', $id13a)
                ->update([
                    '13ar_read' => implode(',', array_unique($letter->{'13ar_read'}))
                ]);
        }

        return view('pages.grievance.13a-reply', [
            '_13a' => $data_13a,
            'user_empno' => $user->Emp_No,
            'letter' => $letter
        ]);
    }

    public static function saveLetterOfReply(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                '_13a' => 'required|numeric',
                'reply' => 'required|string',
                'sign' => 'nullable|string'
            ]);

            $commit = DB::connection('hrd2')->table('tbl_13a_reply')->where('13ar_13aid', $validated['_13a'])->first();
            if ($commit) {
                DB::connection('hrd2')->statement(
                    "UPDATE tbl_13a_reply SET 
                        13ar_reply = ?,
                        13ar_sign = ?,
                        13ar_read = ?
                    WHERE
                        13ar_13aid = ?",
                    [
                        $validated['reply'],
                        $validated['sign'],
                        Auth::user()->Emp_No,
                        $validated['_13a']
                    ]
                );
            } else {
                DB::connection('hrd2')->table('tbl_13a_reply')
                    ->insert([
                        '13ar_13aid' => $validated['_13a'],
                        '13ar_reply' => $validated['reply'],
                        '13ar_sign' => $validated['sign'],
                        '13ar_read' => Auth::user()->Emp_No
                    ]);
            }

            DB::connection('hrd2')->table('tbl_13a')
                ->where('13a_id', $validated['_13a'])
                ->update([
                    '13a_read' => Auth::user()->Emp_No
                ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
