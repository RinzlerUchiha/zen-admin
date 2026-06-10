<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Grievance13A;
use App\Models\Grievance13B;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Grievance13BController extends Controller
{
    public static function loadList($stat)
    {
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
        foreach (Grievance13B::loadList($stat, Auth::user()) as $v) {
            $html .= '<tr class="position-relative" ondblclick=view13B("' . $v->{'13b_id'} . '")>';
            $html .= '<td class="text-nowrap">' . $v->{'13b_memo_no'} . '</td>';
            $html .= '<td class="text-nowrap">' . $v->{'13b_date'} . '</td>';
            $html .= '<td>' . $v->from_name . '</td>';
            $html .= '<td>' . $v->to_name . '</td>';
            $html .= '<td>' . $v->{'13b_regarding'} . '</td>';
            if ($stat == 'needs explanation' || $stat == 'cancelled') {
                $html .= '<td>' . $v->remarks . '</td>';
            }
            $html .= '<td>' . (strpos($v->{'13b_read'}, Auth::user()->Emp_No) !== false ? 'Read' : 'Unread') . '</td>';
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

        $_13a_id = $request->{'13a'};

        if ($id) {
            $data = Grievance13B::find13B($id)->first();
            $_13a_id = $data->{'13b_13a'};
        }

        if (!$id && !$_13a_id) {
            abort(404);
        }

        $_13a_data = Grievance13A::find($_13a_id);

        if (empty($data)) {
            $data = new Grievance13B();
            $data->{'13b_from'} = $user->Emp_No;
            $data->from_name = $user->LastFirstName;
            $data->{'13b_frompos'} = $user->JobPosition->jrec_position ?? '';
            $data->{'13b_date'} = date('Y-m-d');

            $data->{'13b_regarding'} = $_13a_data->{'13a_regarding'} ?? '';
            $data->{'13b_to'} =  $_13a_data->{'13a_to'} ?? '';
            $data->{'13b_pos'} = $_13a_data->{'13a_pos'} ?? '';
            $data->{'13b_company'} = $_13a_data->{'13a_company'} ?? '';
            $data->{'13b_dept'} = $_13a_data->{'13a_dept'} ?? '';
            $data->{'13b_issuedby'} = $_13a_data->{'13a_issuedby'} ?? '';
            $data->{'13b_notedby'} = $_13a_data->{'13a_notedby'} ?? '';
            $data->{'13b_memo_no'} = $_13a_data->{'13a_memo_no'} ?? '';
            $data->{'13b_memo_no_reply'} = $_13a_data->{'13a_memo_no'} ?? '';
            $data->{'13b_cc'} = $_13a_data->{'13a_cc'} ?? '';
            $data->{'13b_suspendday'} = $_13a_data->{'13a_suspendday'} ?? '';
        } else {
            $data->{'13b_read'} = array_filter(explode(',', $data->{'13b_read'}));
            $data->{'13b_read'}[] = $user->Emp_No;

            DB::connection('hrd2')->table('tbl_13b')
                ->where('13b_id', $data->{'13b_id'})
                ->update([
                    '13b_read' => implode(',', array_unique($data->{'13b_read'}))
                ]);
        }

        $signatures = Grievance13B::find13BSignatures($id)->groupBy('gs_signtype');

        $signed_noted = !empty($signatures['reviewed']) ? $signatures['reviewed']->contains(fn($sign) => strpos($data->{'13b_notedby'}, $sign->gs_empno) !== false && $sign->gs_empno == $user->Emp_No) : false;

        $signed_issued = !empty($signatures['issued']) ? $signatures['issued']->contains(fn($sign) => strpos($data->{'13b_issuedby'}, $sign->gs_empno) !== false) : false;

        $signed_witness = !empty($signatures['witness']) ? $signatures['witness']->contains(fn($sign) => strpos($data->{'13b_witness'}, $sign->gs_empno) !== false && $sign->gs_empno == $user->Emp_No) : false;

        $positionList = Setting::positionList()->mapWithKeys(fn($pos) => [$pos->jd_code => $pos]);
        $departmentList = Setting::departmentList()->mapWithKeys(fn($d) => [$d->Dept_Code => $d]);
        $companyList = Setting::companyList()->mapWithKeys(fn($c) => [$c->C_Code => $c]);
        $employeeLatestJobInfo = Employee::employeeLatestJobInfo();
        // $employeeLatestJobInfo['jobrec'] = $employeeLatestJobInfo['jobrec']->groupBy('jrec_empno');

        $data->{'13b_notedbypos'} = explode(',', $data->{'13b_notedbypos'} ?? '');
        $data->{'13b_witnesspos'} = explode(',', $data->{'13b_witnesspos'} ?? '');

        $violations = Grievance13A::findViolation13A($_13a_data->{'13a_id'});

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

        $hr_dir = DB::table('tbl201_jobinfo as a')
            ->join('tbl201_jobrec as b', function ($join) {
                $join->on('jrec_empno', '=', 'ji_empno')
                    ->where([
                        ['jrec_status', '=', 'Primary'],
                        ['jrec_position', '=', 'HRD']
                    ]);
            })
            ->where('ji_remarks', 'Active')
            ->first()->jrec_empno ?? '';

        return view('pages.grievance.13b', [
            'data' => $data,
            'user_empno' => $user->Emp_No,
            'employees' => $employees,
            'positionList' => $positionList,
            'departmentList' => $departmentList,
            'companyList' => $companyList,
            'employeeLatestJobInfo' => $employeeLatestJobInfo,
            'remarks' => Grievance13B::findRemarks($id),
            '_13a' => $_13a_data,
            '_13a_violations' => $violations,
            'rnrList' => Setting::rnrList(),
            'signatures' => $signatures,
            'signed_noted' => $signed_noted,
            'signed_witness' => $signed_witness,
            'signed_issued' => $signed_issued,
            'violation_str' => $violation_str,
            'hr_dir' => $hr_dir
        ]);
    }

    public static function save13B(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'cc' => 'nullable|string',
                'from' => 'nullable|string',
                'frompos' => 'nullable|string',
                'verdict' => 'nullable|string',
                'reason' => 'nullable|string',
                'effectdt' => 'nullable|date',
                'penalty' => 'nullable|string',
                'notification' => 'nullable|date',
                'issuedby' => 'nullable|string',
                'issuedbypos' => 'nullable|string',
                'notedby' => 'nullable|string',
                'notedbypos' => 'nullable|string',
                'stat' => 'required|string',
                'suspend' => 'nullable|numeric',
                '_13a' => 'required|numeric'
            ]);

            $user_empno = Auth::user()->Emp_No;
            if ($validated['id']) {
                $table = Grievance13B::find($validated['id']);
                $table->update([
                    '13b_cc' => $validated['cc'],
                    '13b_date' => date("Y-m-d"),
                    '13b_from' => $validated['from'],
                    '13b_frompos' => $validated['frompos'],
                    '13b_verdict' => $validated['verdict'],
                    '13b_verdictreason' => $validated['reason'],
                    '13b_verdicteffectdt' => $validated['effectdt'],
                    '13b_penalty' => $validated['penalty'],
                    '13b_notification' => $validated['notification'],
                    '13b_issuedby' => $validated['issuedby'],
                    '13b_issuedbypos' => $validated['issuedbypos'],
                    '13b_notedby' => $validated['notedby'],
                    '13b_notedbypos' => $validated['notedbypos'],
                    '13b_stat' => $validated['stat'],
                    '13b_suspendday' => $validated['suspend'],
                    '13b_read' => $user_empno
                ]);
            } else {
                $_13a_data = Grievance13A::find($validated['_13a']);
                $table = Grievance13B::create([
                    '13b_memo_no' => $_13a_data->{'13a_memo_no'},
                    '13b_memo_no_reply' => $_13a_data->{'13a_memo_no'},
                    '13b_to' => $_13a_data->{'13a_to'},
                    '13b_cc' => $validated['cc'],
                    '13b_pos' => $_13a_data->{'13a_pos'},
                    '13b_company' => $_13a_data->{'13a_company'},
                    '13b_date' => date("Y-m-d"),
                    '13b_dept' => $_13a_data->{'13a_dept'},
                    '13b_regarding' => $_13a_data->{'13a_regarding'},
                    '13b_from' => $validated['from'],
                    '13b_frompos' => $validated['frompos'],
                    '13b_verdict' => $validated['verdict'],
                    '13b_verdictreason' => $validated['reason'],
                    '13b_verdicteffectdt' => $validated['effectdt'],
                    '13b_penalty' => $validated['penalty'],
                    '13b_notification' => $validated['notification'],
                    '13b_issuedby' => $validated['issuedby'],
                    '13b_issuedbypos' => $validated['issuedbypos'],
                    '13b_notedby' => $validated['notedby'],
                    '13b_notedbypos' => $validated['notedbypos'],
                    '13b_stat' => $validated['stat'],
                    '13b_13a' => $_13a_data->{'13a_id'},
                    '13b_suspendday' => $validated['suspend'],
                    '13b_read' => $user_empno
                ]);
            }

            // if ($validated['stat'] == 'pending') {
            //     return response()->json(['success' => true]);
            // }
            return Grievance13BController::show(Request::create('/', 'GET'), $table->{'13b_id'});
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function save13BWitness(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'witness' => 'required|string',
                'witnesspos' => 'nullable|string'
            ]);

            $table = Grievance13B::find($validated['id']);
            $table->update([
                '13b_witness' => $validated['witness'],
                '13b_witnesspos' => $validated['witnesspos']
            ]);

            DB::connection('hrd2')->table('tbl_grievance_sign')
                ->whereRaw("FIND_IN_SET(gs_empno, ?) = 0 AND gs_type = '13b' AND gs_typeid = ? AND gs_signtype = 'witness'", [$validated['witness'], $validated['id']])->delete();

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function cancel13B(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'remarks' => 'required|string'
            ]);

            $table = Grievance13B::find($validated['id']);
            $table->update([
                '13b_stat' => 'cancelled',
                '13b_cancel_remarks' => $validated['remarks'],
                '13b_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function sign13B(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric',
                'signtype' => 'required|string',
                // 'empno' => 'required|string',
                'sign' => 'required|string'
            ]);

            $validated['empno'] = Auth::user()->Emp_No;

            Grievance13B::sign13B($validated);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function issue13B(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric'
            ]);

            $table = Grievance13B::find($validated['id']);
            $table->update([
                '13b_stat' => 'issued',
                '13b_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function refuse13B(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'required|numeric'
            ]);

            $table = Grievance13B::find($validated['id']);
            $table->update([
                '13b_stat' => 'refused',
                '13b_read' => ''
            ]);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // \Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete13B($id)
    {
        try {
            DB::connection('hrd2')->transaction(function () use ($id) {
                $table = Grievance13B::find($id);
                if ($table) {
                    DB::connection('hrd2')->table('tbl_grievance_remarks')
                        ->where([
                            ['gr_typeid', '=', $table->{'13b_id'}],
                            ['gr_type', '=', '13b']
                        ])->delete();

                    DB::connection('hrd2')->table('tbl_grievance_sign')
                        ->where([
                            ['gs_typeid', '=', $table->{'13b_id'}],
                            ['gs_type', '=', '13b']
                        ])->delete();

                    $table->delete();
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
        $query = DB::connection('hrd2')->table('tbl_13b AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13b_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13b_to')
            ->where('13b_stat', '!=', 'draft');

        if (!$reviewer) {
            $query->whereRaw("FIND_IN_SET(?, CONCAT_WS(',',13b_to, 13b_cc, 13b_from, 13b_issuedby, 13b_notedby)) > 0", [$user->Emp_No]);
        }

        $query->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->orderBy('13b_date', 'desc');

        $data = $query->get()->map(function ($item) use ($persinfo) {
            $item->from_name = isset($persinfo[$item->{'13b_from'}]) ? trim($persinfo[$item->{'13b_from'}]['pers_lastname'] . ", " . $persinfo[$item->{'13b_from'}]['pers_firstname']) : '';

            $item->to_name = isset($persinfo[$item->{'13b_to'}]) ? trim($persinfo[$item->{'13b_to'}]['pers_lastname'] . ", " . $persinfo[$item->{'13b_to'}]['pers_firstname']) : '';

            return $item;
        });

        $filteredData = $data->filter(function ($d) use ($user, $reviewer) {

            $sign_issued = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13b_id'}}' AND gs_type='13b' AND gs_signtype='issued'"));

            $sign_noted = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13b_id'}}' AND gs_type='13b' AND gs_signtype='reviewed' AND gs_empno='{$user->Emp_No}'"));

            $sign_witness = (DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13b_id'}}' AND gs_type='13b' AND gs_signtype='witness' AND gs_empno='{$user->Emp_No}'"));

            if ($d->{'13b_stat'} == "checked" && $sign_noted > 0) {
                $d->{'13b_stat'} = "reviewed";
            }

            $remarks = DB::connection('hrd2')->table('tbl_grievance_remarks AS a')
                ->where([
                    ['gr_typeid', '=', $d->{'13b_id'}],
                    ['gr_type', '=', '13b']
                ])
                ->orderBy('gr_id', 'desc')
                ->first()->gr_remarks ?? '';

            $d->remarks = $remarks;

            return (
                (
                    (
                        $reviewer ||
                        strpos($d->{'13b_cc'}, $user->Emp_No) !== false
                    ) &&
                    (
                        $d->{'13b_stat'} == 'pending' ||
                        (
                            ($d->{'13b_stat'} == "received" || $d->{'13b_stat'} == "refused" || $d->{'13b_stat'} == "cancelled") &&
                            strpos($d->{'13b_read'}, $user->Emp_No) === false
                        )
                    )
                ) ||

                (
                    $user->Emp_No == $d->{'13b_issuedby'} &&
                    (
                        $d->{'13b_stat'} == "reviewed" ||
                        ($d->{'13b_stat'} == "refused" && !$d->{'13b_witness'}) ||
                        $sign_issued == 0
                    )
                ) ||

                ($sign_issued > 0 && $d->{'13b_stat'} == "checked" && strpos($d->{'13b_notedby'}, $user->Emp_No) !== false && $sign_noted == 0) ||

                ($d->{'13b_stat'} == "refused" && strpos($d->{'13b_witness'}, $user->Emp_No) !== false && $sign_witness == 0) ||

                ($user->Emp_No == $d->{'13b_to'} &&
                    (
                        $d->{'13b_stat'} == "issued" ||
                        ($d->{'13b_stat'} == "refused" && strpos($d->{'13b_read'}, $user->Emp_No) === false)
                    )
                )
            );
        });

        $group = $filteredData->groupBy('13b_stat');

        return $group->map(fn($i) => $i->count());
    }
}
