<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\ManpowerRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManpowerController extends Controller
{
    private const STATUSES = ['draft', 'pending', 'approved', 'cancelled', 'declined'];

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('pages.recruitment.manpower.index', [
            'main_link' => 'recruitment',
            'sub_link' => 'manpower',
            'user_empno' => $user->Emp_No,
            'department' => Setting::departmentList(0),
            'position' => Setting::positionList(),
        ]);
    }

    /** Whether $user is the tbl_dept_authority-designated approver for $requestorEmpno */
    private function isDeptApprover($user, ?string $requestorEmpno): bool
    {
        if (!$requestorEmpno) {
            return false;
        }
        return strpos(check_assign($user->Emp_No, 'PR'), $requestorEmpno) !== false;
    }

    private function buildListQuery(string $stat, $user)
    {
        $canViewAll = $user->userAccess('personnelreq', 'viewall');

        if ($canViewAll) {
            return ManpowerRequest::where('mp_status', $stat);
        }

        $assigned = check_assign($user->Emp_No, 'PR');

        return ManpowerRequest::where('mp_status', $stat)
            ->whereRaw("(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?)", [$assigned, $user->Emp_No]);
    }

    public function list(string $stat)
    {
        if (!in_array($stat, self::STATUSES)) {
            abort(404);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $employee = DB::table('tbl201_persinfo')
            ->selectRaw("pers_empno, Dept_Name, TRIM(CONCAT(pers_lastname, ', ', pers_firstname)) as empname")
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->get();

        $data = $this->buildListQuery($stat, $user)
            ->orderBy('mp_id', 'desc')
            ->get()
            ->map(function ($v) use ($employee) {
                $requestor = $employee->where('pers_empno', $v->mp_requestby)->first();
                $v->requestor_name = $requestor?->empname ?? '—';
                $v->requestor_dept = $requestor?->Dept_Name ?? '—';
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

        foreach (self::STATUSES as $stat) {
            $counts[$stat] = $this->buildListQuery($stat, $user)->count();
        }

        return response()->json($counts);
    }

    public function show($id)
    {
        $data = ManpowerRequest::findOrFail($id);
        $data->replacement_slots = $this->parseSlots($data->mp_replacement);
        $data->additional_slots = $this->parseSlots($data->mp_additional);
        return response()->json($data);
    }

    /** Parses a bracketed pipe-delimited slot string into
     *  [position, count, reason, date, applicants_csv, fill] tuples. */
    private function parseSlots(?string $raw): array
    {
        preg_match_all('/\[([^\]]+)\]/', $raw ?? '', $matches);
        return array_map(fn($group) => explode('|', $group), $matches[1]);
    }

    /** Serializes an array of slot rows (each [position, count, reason, date,
     *  applicants_csv, fill]) back into the bracketed storage format. */
    private function serializeSlots(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }
        $parts = array_map(fn($row) => implode('|', [
            $row['position'] ?? '',
            $row['count'] ?? 1,
            $row['reason'] ?? '',
            $row['date'] ?? '',
            $row['applicants_csv'] ?? '',
            $row['fill'] ?? 0,
        ]), $rows);
        return '[' . implode('][', $parts) . ']';
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'replacement' => 'nullable|string',
                'additional' => 'nullable|string',
                'nonnegotiable' => 'nullable|string',
                'submit_mode' => 'nullable|string|in:draft,pending',
            ]);

            $replacement = json_decode($validated['replacement'] ?? '[]', true) ?: [];
            $additional = json_decode($validated['additional'] ?? '[]', true) ?: [];

            $status = $validated['submit_mode'] ?? 'draft';

            $count = array_sum(array_column($replacement, 'count')) + array_sum(array_column($additional, 'count'));
            $progress = '0%,0/' . $count;

            $data = [
                'mp_replacement' => $this->serializeSlots($replacement),
                'mp_additional' => $this->serializeSlots($additional),
                'mp_nonnegotiable' => $validated['nonnegotiable'] ?? null,
            ];

            if (!empty($validated['id'])) {
                $existing = ManpowerRequest::where('mp_id', $validated['id'])->first();

                if (!$existing || $existing->mp_requestby !== Auth::user()->Emp_No) {
                    return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
                }

                $current = $existing->mp_status;
                if ($current && $current !== 'draft') {
                    $status = $current;
                }
                $data['mp_status'] = $status;
                ManpowerRequest::where('mp_id', $validated['id'])->update($data);
            } else {
                $data['mp_status'] = $status;
                $data['mp_progress'] = $progress;
                $data['mp_filled'] = 'Not';
                $data['mp_dtprepared'] = date('Y-m-d');
                $data['mp_requestby'] = Auth::user()->Emp_No;
                ManpowerRequest::insert($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'stat' => 'required|string|in:approved,declined,cancelled',
                'reason' => 'nullable|string',
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $requestorEmpno = ManpowerRequest::where('mp_id', $id)->value('mp_requestby');
            $authorized = $user->userAccess('personnelreq', 'viewall') || $this->isDeptApprover($user, $requestorEmpno);

            if (!$authorized) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $data = ['mp_status' => $validated['stat']];

            if ($validated['stat'] == 'approved') {
                $data['mp_dtapproved'] = date('Y-m-d');
                $data['mp_approvedby'] = $user->Emp_No;
            } elseif ($validated['stat'] == 'declined') {
                $data['mp_declinedby'] = $user->Emp_No;
                $data['mp_decline_reason'] = $validated['reason'] ?? null;
            }

            ManpowerRequest::where('mp_id', $id)->update($data);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function requestUpdate(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|string|in:edit,cancel',
                'reason' => 'nullable|string',
            ]);

            if ($validated['action'] === 'cancel') {
                ManpowerRequest::where('mp_id', $id)->update([
                    'mp_status' => 'cancelled',
                    'mp_decline_reason' => $validated['reason'] ?? null,
                ]);
                return response()->json(['success' => true]);
            }

            DB::connection('hrd2')->table('tbl_mpupdate')->insert([
                'mpu_mpid' => $id,
                'mpu_req' => $validated['action'],
                'mpu_reason' => $validated['reason'] ?? '',
                'mpu_stat' => 'pending',
                'mpu_by' => Auth::user()->Emp_No,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function approveUpdate($mpuId)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $table = DB::connection('hrd2')->table('tbl_mpupdate')->where('mpu_id', $mpuId);
            $data = $table->first();

            if (!$data) {
                return response()->json(['success' => false, 'error' => 'Not found'], 404);
            }

            $requestorEmpno = ManpowerRequest::where('mp_id', $data->mpu_mpid)->value('mp_requestby');
            $authorized = $user->userAccess('personnelreq', 'viewall') || $this->isDeptApprover($user, $requestorEmpno);

            if (!$authorized) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            if ($data->mpu_req == 'cancel') {
                ManpowerRequest::where('mp_id', $data->mpu_mpid)->update(['mp_status' => 'cancelled']);
            }

            $table->update(['mpu_stat' => 'approved']);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function declineUpdate($mpuId)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $data = DB::connection('hrd2')->table('tbl_mpupdate')->where('mpu_id', $mpuId)->first();

            if (!$data) {
                return response()->json(['success' => false, 'error' => 'Not found'], 404);
            }

            $requestorEmpno = ManpowerRequest::where('mp_id', $data->mpu_mpid)->value('mp_requestby');
            $authorized = $user->userAccess('personnelreq', 'viewall') || $this->isDeptApprover($user, $requestorEmpno);

            if (!$authorized) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            DB::connection('hrd2')->table('tbl_mpupdate')->where('mpu_id', $mpuId)->update(['mpu_stat' => 'denied']);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $existing = ManpowerRequest::where('mp_id', $id)->first();

            if (!$existing || $existing->mp_requestby !== Auth::user()->Emp_No) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $existing->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}