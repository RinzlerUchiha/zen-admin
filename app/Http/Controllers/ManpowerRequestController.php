<?php

namespace App\Http\Controllers;

use App\Models\ManpowerRequest;
use App\Models\Setting;
use App\Models\Applicant\ApplicantPersonal;
use App\Models\Applicant\InterviewDeets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManpowerRequestController extends Controller
{
    /** Allowed values for mp_status when transitioning via updateStat() */
    private const ALLOWED_STATUSES = ['draft', 'pending', 'approved', 'cancelled', 'declined', 'reviewed'];

    public static function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userJobInfo = $user->JobPosition;
        $jobSpec = Setting::jobSpecList();
        if (!$user->userAccess('personnelreq', 'viewall')) {
            $jobSpec = $jobSpec->filter(fn($r) => strpos(check_assign($user->Emp_No, 'PR'), $r->jspec_department) !== false);
        }

        $userJobSpec = $jobSpec->where('jspec_department', $userJobInfo?->jrec_department);

        return view('pages.manpower-request', [
            'user_empno' => $user->Emp_No,
            'jobspec' => $jobSpec,
            'userJobSpec' => $userJobSpec,
            'userJobInfo' => $userJobInfo,
            'department' => Setting::departmentList(0),
            'section' => Setting::sectionList(0),
            'position' => Setting::positionList(),
            'emplstat' => Setting::emplStatusList(),
            'applicants' => self::applicantList(),
            'main_link' => 'manpower-request',
            'sub_link' => ''
        ]);
    }

    public static function showList($stat)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user_empno = $user->Emp_No;
        // approval authority now comes from tbl_dept_authority (check_assign) alone

        // ── jobspec tab ──────────────────────────────────────────────────────
        if ($stat == 'jobspec') {
            return self::renderJobSpecTable($user);
        }

        // ── shared lookups ───────────────────────────────────────────────────
        $employee = self::employeeList();
        $positionList = Setting::positionList(0);

        // ── main query ───────────────────────────────────────────────────────
        $query = self::buildListQuery($stat, $user, $user_empno);

        if (in_array($stat, ['update', 'pending_update', 'cancelled'])) {
            $query->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id')
                ->select(
                    'tbl_manpower.*',
                    'tbl_mpupdate.mpu_id',
                    'tbl_mpupdate.mpu_mpid',
                    'tbl_mpupdate.mpu_req',
                    'tbl_mpupdate.mpu_reason',
                    'tbl_mpupdate.mpu_stat',
                    'tbl_mpupdate.mpu_by'
                );
        }

        $query->orderBy('mp_filled', 'desc')->orderBy('mp_id', 'desc');
        $data = $query->get();

        $btn_action_show = self::showActionColumn($stat, $user, $user_empno, $data);

        return self::renderListTable($stat, $data, $employee, $positionList, $user, $user_empno, $btn_action_show);
    }

    /**
     * Builds the base ManpowerRequest query for a given status tab, scoped
     * to what the current user is allowed to see.
     */
    private static function buildListQuery(string $stat, $user, string $user_empno)
    {
        $canViewAll = $user->userAccess('personnelreq', 'viewall');

        if ($canViewAll) {
            if ($stat == 'pending_update') {
                return ManpowerRequest::query()
                    ->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id')
                    ->whereRaw("mpu_stat='pending'");
            }

            if ($stat == 'update') {
                return ManpowerRequest::query()
                    ->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id')
                    ->whereRaw("mpu_stat = 'approved'");
            }

            $query = ManpowerRequest::where('mp_status', $stat);
            if (!in_array($stat, ['update', 'pending_update', 'cancelled', 'draft'])) {
                $query->whereRaw("mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat IN ('pending','approved'))");
            }
            return $query;
        }

        $assigned = check_assign($user->Emp_No, 'PR');

        if ($stat == 'pending_update') {
            return ManpowerRequest::query()
                ->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id')
                ->whereRaw("(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?) AND (mpu_stat='pending')",
                    [$assigned, $user_empno]
                );
        }

        if ($stat == 'update') {
            return ManpowerRequest::query()
                ->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id')
                ->whereRaw("(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?) AND (mpu_stat = 'approved')",
                    [$assigned, $user_empno]
                );
        }

        $query = ManpowerRequest::where('mp_status', $stat)
            ->whereRaw("(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?)", [$assigned, $user_empno]);

        if (!in_array($stat, ['update', 'pending_update', 'cancelled'])) {
            $query->whereRaw("mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat IN ('pending','approved'))");
        }

        return $query;
    }

    /** Whether $user is the tbl_dept_authority-designated PR approver for the
     *  department that owns $requestorEmpno (i.e. $requestorEmpno appears in
     *  the user's check_assign('PR') list). */
    private static function isDeptApprover($user, ?string $requestorEmpno): bool
    {
        if (!$requestorEmpno) {
            return false;
        }
        return strpos(check_assign($user->Emp_No, 'PR'), $requestorEmpno) !== false;
    }

    /** Whether the trailing actions <th>/<td> column should be rendered for this tab. */
    private static function showActionColumn(string $stat, $user, string $user_empno, $data): bool
    {
        $isOwnRequest = in_array($user_empno, $data->pluck('mp_requestby')->toArray());

        $isApproverForAnyRow = $stat == 'pending'
            && $data->pluck('mp_requestby')->contains(fn($empno) => self::isDeptApprover($user, $empno));

        $isApproverForAnyUpdateRow = in_array($stat, ['update', 'pending_update'])
            && $data->pluck('mp_requestby')->contains(fn($empno) => self::isDeptApprover($user, $empno));

        $isAdminUpdateTab = in_array($stat, ['update', 'pending_update'])
            && $user->userAccess('personnelreq', 'viewall');

        return $isOwnRequest || $isApproverForAnyRow || $isApproverForAnyUpdateRow || $isAdminUpdateTab;
    }

    private static function renderJobSpecTable($user): string
    {
        $data = Setting::jobSpecList();
        if (!$user->userAccess('personnelreq', 'viewall')) {
            $data = $data->filter(
                fn($r) => strpos(check_assign($user->Emp_No, 'PR'), $r->jspec_department) !== false
            );
        }

        $html  = '<table class="table table-sm mpr-table">';
        $html .= '<thead><tr>';
        $html .= '<th>Department</th>';
        $html .= '<th>Position</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($data as $v) {
            $html .= '<tr data-bs-toggle="modal" data-bs-target="#modal-mpr-jobspec"'
                . ' data-pos="' . e($v->jspec_position) . '">';
            $html .= '<td>' . e($v->Dept_Name) . '</td>';
            $html .= '<td>' . e($v->jd_title)  . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private static function renderListTable(
        string $stat,
        $data,
        $employee,
        $positionList,
        $user,
        string $user_empno,
        bool $btn_action_show
    ): string {
        $html  = '<table class="table table-sm mpr-table">';
        $html .= '<thead><tr>';
        $html .= '<th style="width:110px">Date Prepared</th>';
        $html .= '<th>Prepared by</th>';
        $html .= '<th>Department</th>';

        if ($stat == 'approved') {
            $html .= '<th>Approved By</th>';
        } elseif ($stat == 'update' || $stat == 'pending_update') {
            $html .= '<th>Request Update By</th>';
            $html .= '<th>Action</th>';
            $html .= '<th>Reason</th>';
        } elseif ($stat == 'cancelled') {
            $html .= '<th>Reason</th>';
        } elseif ($stat == 'declined') {
            $html .= '<th>Declined By</th>';
            $html .= '<th>Reason</th>';
        }

        if ($stat == 'approved' || $stat == 'update' || $stat == 'pending_update') {
            $html .= '<th>Filled</th>';
        }

        if ($btn_action_show) {
            $html .= '<th></th>';
        }

        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($data as $v) {
            $progress = explode(',', $v->mp_progress ?? '');

            $replacement = self::parseSlots($v->mp_replacement, $positionList);
            $additional  = self::parseSlots($v->mp_additional, $positionList);

            $html .= '<tr class="mpr-clickable-row"'
                . ' data-id="'             . e($v->mp_id)                    . '"'
                . ' data-replacement="'   . e(json_encode($replacement))     . '"'
                . ' data-additional="'    . e(json_encode($additional))      . '"'
                . ' data-nonnegotiable="' . e($v->mp_nonnegotiable ?? '')    . '">';

            $html .= '<td class="text-start">' . e($v->mp_dtprepared) . '</td>';

            $requestor = $employee->where('pers_empno', $v->mp_requestby)->first();
            $html .= '<td>' . e($requestor?->empname  ?? '—') . '</td>';
            $html .= '<td>' . e($requestor?->Dept_Name ?? '—') . '</td>';

            if ($stat == 'approved') {
                $approver = $employee->where('pers_empno', $v->mp_approvedby ?? null)->first();
                $html .= '<td>' . e($approver?->empname ?? '—') . '</td>';
            } elseif ($stat == 'update' || $stat == 'pending_update') {
                $updater = $employee->where('pers_empno', $v->mpu_by ?? null)->first();
                $html .= '<td>' . e($updater?->empname ?? '—') . '</td>';
                $html .= '<td>' . e(strtoupper($v->mpu_req ?? '')) . '</td>';
                $html .= '<td>' . nl2br(htmlentities($v->mpu_reason ?? '', ENT_QUOTES)) . '</td>';
            } elseif ($stat == 'cancelled') {
                $html .= '<td>' . nl2br(htmlentities($v->mpu_reason ?? '', ENT_QUOTES)) . '</td>';
            } elseif ($stat == 'declined') {
                $decliner = $employee->where('pers_empno', $v->mp_declinedby ?? null)->first();
                $html .= '<td>' . e($decliner?->empname ?? '—') . '</td>';
                $html .= '<td>' . nl2br(htmlentities($v->mp_decline_reason ?? '', ENT_QUOTES)) . '</td>';
            }

            if ($stat == 'approved' || $stat == 'update' || $stat == 'pending_update') {
                $html .= '<td class="text-center">' . e($progress[1] ?? '') . '</td>';
            }

            $btn_action = self::renderRowActions($stat, $v, $user, $user_empno);

            if ($btn_action_show) {
                $html .= '<td><div class="mpr-row-actions">' . $btn_action . '</div></td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /** Builds the per-row action buttons (edit/delete/approve/decline/etc). */
    private static function renderRowActions(string $stat, $v, $user, string $user_empno): string
    {
        $btn_action = '';

        if ($user_empno == $v->mp_requestby) {
            if ($stat == 'draft' || $stat == 'pending') {
                $btn_action .= '<button class="m-1 btn btn-sm btn-outline-secondary"'
                    . ' data-bs-toggle="modal" data-bs-target="#modal-mpr"'
                    . ' data-id="'             . e($v->mp_id)               . '"'
                    . ' data-replacement="'   . e($v->mp_replacement ?? '') . '"'
                    . ' data-additional="'    . e($v->mp_additional  ?? '') . '"'
                    . ' data-nonnegotiable="' . e($v->mp_nonnegotiable ?? '') . '"'
                    . ' data-submit-mode="'   . e($v->mp_status)            . '">'
                    . '<i class="fa fa-edit"></i></button>';

                if ($stat == 'draft') {
                    $btn_action .= '<button class="m-1 btn btn-sm btn-outline-primary"'
                        . ' data-bs-toggle="modal" data-bs-target="#modal-mpr"'
                        . ' data-id="'             . e($v->mp_id)               . '"'
                        . ' data-replacement="'   . e($v->mp_replacement ?? '') . '"'
                        . ' data-additional="'    . e($v->mp_additional  ?? '') . '"'
                        . ' data-nonnegotiable="' . e($v->mp_nonnegotiable ?? '') . '"'
                        . ' data-submit-mode="pending"'
                        . ' title="Post request">'
                        . '<i class="fa fa-paper-plane"></i></button>';
                }

                $btn_action .= '<button class="m-1 btn btn-sm btn-danger"'
                    . ' onclick="remove_mpr(' . (int)$v->mp_id . ')">'
                    . '<i class="fa fa-trash"></i></button>';
            } elseif ($stat == 'approved') {
                $btn_action .= '<button class="m-1 btn btn-sm btn-outline-secondary"'
                    . ' data-bs-toggle="modal" data-bs-target="#modal-mpr-update"'
                    . ' data-id="' . e($v->mp_id) . '" data-action="edit"'
                    . ' title="Request to Edit"><i class="fa fa-edit"></i></button>';

                $btn_action .= '<button class="m-1 btn btn-sm btn-danger"'
                    . ' data-bs-toggle="modal" data-bs-target="#modal-mpr-update"'
                    . ' data-id="' . e($v->mp_id) . '" data-action="cancel"'
                    . ' title="Request to Cancel"><i class="fa fa-cancel"></i></button>';
                    } elseif (in_array($stat, ['update', 'pending_update']) && isset($v->mpu_stat) && $v->mpu_stat == 'approved') {
                        $btn_action .= '<button class="m-1 btn btn-sm btn-outline-secondary"'
                            . ' data-bs-toggle="modal" data-bs-target="#modal-mpr"'
                            . ' data-id="'             . e($v->mp_id)               . '"'
                            . ' data-replacement="'   . e($v->mp_replacement ?? '') . '"'
                            . ' data-additional="'    . e($v->mp_additional  ?? '') . '"'
                            . ' data-nonnegotiable="' . e($v->mp_nonnegotiable ?? '') . '"'
                            . ' data-reason="'        . e($v->mpu_reason ?? '')      . '">'
                            . '<i class="fa fa-edit"></i></button>';
                    }
        }

        if ($stat == 'pending' && self::isDeptApprover($user, $v->mp_requestby)) {
            $btn_action .= '<button class="m-1 btn btn-sm btn-outline-primary approve"'
                . ' onclick="approve(' . (int)$v->mp_id . ')">'
                . '<i class="fa fa-check"></i></button>';

            $btn_action .= '<button class="m-1 btn btn-sm btn-outline-danger decline"'
                . ' onclick="decline(' . (int)$v->mp_id . ')">'
                . '<i class="fa fa-times"></i></button>';
        }

        if (in_array($stat, ['update', 'pending_update'])
            && ($user->userAccess('personnelreq', 'viewall') || self::isDeptApprover($user, $v->mp_requestby))
        ) {
            $btn_action .= '<button class="m-1 btn btn-sm btn-outline-primary approve"'
                . ' onclick="approve_update(' . (int)$v->mpu_id . ')">'
                . '<i class="fa fa-check"></i></button>';

            $btn_action .= '<button class="m-1 btn btn-sm btn-outline-danger decline"'
                . ' onclick="decline_update(' . (int)$v->mpu_id . ')">'
                . '<i class="fa fa-times"></i></button>';
        }

        return $btn_action;
    }

    /**
     * Parses a pipe/bracket-delimited slot string (mp_replacement /
     * mp_additional) into an array of [title, code, count, reason, date,
     * applicants_csv, fill] tuples.
     */
    private static function parseSlots(?string $raw, $positionList): array
    {
        preg_match_all('/\[([^\]]+)\]/', $raw ?? '', $matches);

        return array_map(function ($group) use ($positionList) {
            $group = explode('|', $group);
            return [
                trim($positionList->where('jd_code', $group[0])->first()?->jd_title ?? ''),
                $group[0],          // position code
                $group[1] ?? '',    // count
                $group[2] ?? '',    // reason
                $group[3] ?? '',    // date
                $group[4] ?? '',    // applicants CSV
                $group[5] ?? 0,     // fill
            ];
        }, $matches[1]);
    }

    /**
     * Serializes an array of slot rows (each indexed by the given $fields,
     * in order) back into the bracketed pipe-delimited storage format.
     * Example: serializeSlots($rows, ['position','count','reason','date','applicants_csv','fill'])
     */
    private static function serializeSlots(array $rows, array $fields): string
    {
        if (empty($rows)) {
            return '';
        }

        $parts = array_map(function ($row) use ($fields) {
            $values = array_map(fn($f) => $row[$f] ?? '', $fields);
            return implode('|', $values);
        }, $rows);

        return '[' . implode('][', $parts) . ']';
    }

    private static function employeeList()
    {
        return DB::table('tbl201_persinfo')
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
    }

    /** Shared applicant dropdown source, used by index() and showList(). */
    private static function applicantList()
    {
        return ApplicantPersonal::query()
            ->select([
                'app_id',
                DB::raw("CONCAT_WS(
                    ' ',
                    app_fname,
                    NULLIF(CONCAT(LEFT(app_mname, 1), '.'), '.'),
                    app_lname
                ) AS app_name"),
            ])
            ->orderBy('app_lname')
            ->orderBy('app_fname')
            ->get();
    }

    public static function viewSpec($pos)
    {
        try {
            $data = Setting::jobSpecList(0, $pos)->first();
            if ($data?->jspec_education) {
                $data->jspec_education = explode('%#', $data?->jspec_education);
                $data->jspec_education = array_map(fn($item) => explode('%&', $item), $data?->jspec_education);
            }

            $json = [
                'id' => $data?->jspec_id,
                'department' => $data?->jspec_department,
                'section' => $data?->jspec_section,
                'position' => $data?->jspec_position,

                'department_name' => $data?->Dept_Name,
                'section_name' => $data?->sec_name,
                'position_name' => $data?->jd_title,

                'sex' => $data?->jspec_sex,
                'agerange' => explode('-', $data?->jspec_agerange),
                'emplstat' => $data?->jspec_emplstat,
                'education' => $data?->jspec_education,
                'workexp' => explode('%#', $data?->jspec_workexp),
                'duties' => $data?->jspec_duties,
                'techcompetencies' => $data?->jspec_techcompetencies,
                'competencies' => $data?->jspec_competencies,
                'computerskill' => explode('%#', $data?->jspec_computerskill),
                'otherskill' => $data?->jspec_otherskill,
                'mpa' => $data?->jspec_mpa,
                'mpb' => explode('|', $data?->jspec_mpb),
                'mpc' => $data?->jspec_mpc,
                'mpd' => $data?->jspec_mpd,
                'mpe' => $data?->jspec_mpe,
                'mpf' => explode('|', $data?->jspec_mpf),
                'mpg' => $data?->jspec_mpg,
                'tapt' => explode('%#', $data?->jspec_tapt),
                'enneagram' => explode('%#', $data?->jspec_enneagram),
                'learnstyle' => explode('%#', $data?->jspec_learnstyle),
                'career' => explode('%#', $data?->jspec_career),
                'motivation' => explode('%#', $data?->jspec_motivation),
                'personality' => explode('%#', $data?->jspec_personality),
                'ravenl' => explode('%#', $data?->jspec_ravenl),
                'ravena' => explode('%#', $data?->jspec_ravena),
                'ravenh' => explode('%#', $data?->jspec_ravenh),
                'leadership' => $data?->jspec_leadership,
                'remarks' => $data?->jspec_remarks
            ];

            return response()->json($json);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    private static function resolveRequestStatus(?string $submitMode, ?string $currentStatus): string
    {
        if ($currentStatus && $currentStatus !== 'draft') {
            return $currentStatus;
        }

        if ($submitMode === 'draft') {
            return 'draft';
        }

        return 'pending';
    }

    public static function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'replacement' => 'nullable|string',
                'additional' => 'nullable|string',
                'nonnegotiable' => 'nullable|string',
                'submit_mode' => 'nullable|string|in:draft,pending'
            ]);

            $validated['replacement'] = json_decode($validated['replacement'], true);
            $validated['additional'] = json_decode($validated['additional'], true);

            $currentStatus = null;
            if ($validated['id']) {
                $currentStatus = ManpowerRequest::where('mp_id', $validated['id'])->value('mp_status');
            }

            $progress[0] = '0%';
            $progress[1] = '0/' . (array_sum(array_column($validated['replacement'], 'count')) + array_sum(array_column($validated['additional'], 'count')));

            $normalize = function ($i) {
                $i['applicants_csv'] = implode(',', array_filter($i['applicants'] ?? [], fn($a) => !is_null($a)));
                return $i;
            };

            $fields = ['position', 'count', 'reason', 'date', 'applicants_csv'];

            $replacementRows = array_map($normalize, $validated['replacement']);
            $additionalRows  = array_map($normalize, $validated['additional']);

            $data = [
                'mp_replacement' => self::serializeSlots($replacementRows, $fields),
                'mp_additional' => self::serializeSlots($additionalRows, $fields),
                'mp_nonnegotiable' => $validated['nonnegotiable'],
                'mp_progress' => implode(',', $progress)
            ];

            $resolvedStatus = self::resolveRequestStatus($validated['submit_mode'] ?? null, $currentStatus);

            if ($validated['id']) {
                $data['mp_status'] = $resolvedStatus;
                ManpowerRequest::where('mp_id', $validated['id'])->update($data);

                if ($currentStatus === 'approved') {
                    DB::table('tbl_mpupdate')
                        ->where('mpu_mpid', $validated['id'])
                        ->where('mpu_req', 'edit')
                        ->where('mpu_stat', 'approved')
                        ->update(['mpu_stat' => 'completed']);
                }
            } else {
                $data['mp_dtprepared'] = date('Y-m-d');
                $data['mp_requestby'] = Auth::user()->Emp_No;
                $data['mp_status'] = $resolvedStatus;
                $data['mp_progress'] = '';
                $data['mp_filled'] = 'Not';
                ManpowerRequest::insert($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function saveSpec(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'department' => 'required|string',
                'section' => 'nullable|string',
                'position' => 'required|string',
                'emplstat' => 'required|string',
                'sex' => 'required|string',
                'agerange' => 'required|string',
                'education' => 'required|string',
                'workexp' => 'required|string',
                'duties' => 'required|string',
                'techcompetencies' => 'nullable|string',
                'competencies' => 'nullable|string',
                'computerskill' => 'nullable|string',
                'otherskill' => 'nullable|string',
                'mpa' => 'required|string',
                'mpb' => 'required|string',
                'mpc' => 'required|string',
                'mpd' => 'required|string',
                'mpe' => 'required|string',
                'mpf' => 'required|string',
                'mpg' => 'required|string',
                'tapt' => 'required|string',
                'enneagram' => 'required|string',
                'learnstyle' => 'required|string',
                'career' => 'required|string',
                'motivation' => 'required|string',
                'personality' => 'required|string',
                'ravenl' => 'required|string',
                'ravena' => 'required|string',
                'ravenh' => 'required|string',
                'leadership' => 'nullable|string',
                'remarks' => 'nullable|string'
            ]);

            // The front-end submits these fields as JSON-encoded arrays, but
            // viewSpec() reads them back with explode() on legacy delimiters
            // ('-', '%#', '%&', '|'). Convert here so both directions stay in
            // sync — this is also what was overflowing jspec_agerange (a
            // JSON array is longer than "20-30").
            $toDelimited = function ($json, string $glue) {
                $arr = json_decode($json, true);
                return is_array($arr) ? implode($glue, $arr) : (string) $json;
            };

            $agerange = json_decode($validated['agerange'], true) ?: [];
            $validated['agerange'] = ($agerange[0] ?? '') . '-' . ($agerange[1] ?? '');

            $education = json_decode($validated['education'], true) ?: [];
            $validated['education'] = implode('%#', array_map(
                fn($e) => ($e['value'] ?? '') . '%&' . ($e['detail'] ?? ''),
                $education
            ));

            $validated['workexp'] = $toDelimited($validated['workexp'], '%#');
            $validated['computerskill'] = $toDelimited($validated['computerskill'], '%#');
            $validated['tapt'] = $toDelimited($validated['tapt'], '%#');
            $validated['enneagram'] = $toDelimited($validated['enneagram'], '%#');
            $validated['learnstyle'] = $toDelimited($validated['learnstyle'], '%#');
            $validated['career'] = $toDelimited($validated['career'], '%#');
            $validated['motivation'] = $toDelimited($validated['motivation'], '%#');
            $validated['personality'] = $toDelimited($validated['personality'], '%#');
            $validated['ravenl'] = $toDelimited($validated['ravenl'], '%#');
            $validated['ravena'] = $toDelimited($validated['ravena'], '%#');
            $validated['ravenh'] = $toDelimited($validated['ravenh'], '%#');

            $mpb = json_decode($validated['mpb'], true) ?: [];
            $validated['mpb'] = ($mpb[0] ?? '') . '|' . ($mpb[1] ?? '');

            $mpf = json_decode($validated['mpf'], true) ?: [];
            $validated['mpf'] = ($mpf[0] ?? '') . '|' . ($mpf[1] ?? '');

            $data = [
                'jspec_department' => $validated['department'],
                'jspec_section' => $validated['section'],
                'jspec_position' => $validated['position'],
                'jspec_sex' => $validated['sex'],
                'jspec_agerange' => $validated['agerange'],
                'jspec_emplstat' => $validated['emplstat'],
                'jspec_education' => $validated['education'],
                'jspec_workexp' => $validated['workexp'],
                'jspec_duties' => $validated['duties'],
                'jspec_techcompetencies' => $validated['techcompetencies'],
                'jspec_competencies' => $validated['competencies'],
                'jspec_computerskill' => $validated['computerskill'],
                'jspec_otherskill' => $validated['otherskill'],
                'jspec_mpa' => $validated['mpa'],
                'jspec_mpb' => $validated['mpb'],
                'jspec_mpc' => $validated['mpc'],
                'jspec_mpd' => $validated['mpd'],
                'jspec_mpe' => $validated['mpe'],
                'jspec_mpf' => $validated['mpf'],
                'jspec_mpg' => $validated['mpg'],
                'jspec_tapt' => $validated['tapt'],
                'jspec_enneagram' => $validated['enneagram'],
                'jspec_learnstyle' => $validated['learnstyle'],
                'jspec_career' => $validated['career'],
                'jspec_motivation' => $validated['motivation'],
                'jspec_personality' => $validated['personality'],
                'jspec_ravenl' => $validated['ravenl'],
                'jspec_ravena' => $validated['ravena'],
                'jspec_ravenh' => $validated['ravenh'],
                'jspec_leadership' => $validated['leadership'],
                'jspec_remarks' => $validated['remarks']
            ];

            if (DB::table('tbl_jobspec')->where([
                ['jspec_position', '=', $validated['position']],
                ['jspec_id', '!=', $validated['id']]
            ])->count() > 0) {
                return response()->json(['success' => false, 'error' => 'Job specification already exist']);
            }

            if ($validated['id']) {
                DB::table('tbl_jobspec')->where('jspec_id', $validated['id'])->update($data);
            } else {
                DB::table('tbl_jobspec')->insert($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            ManpowerRequest::where('mp_id', $id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function updateStat(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|numeric',
                'stat' => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_STATUSES)],
                'reason' => 'nullable|string',
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $data['mp_status'] = $validated['stat'];
            if ($validated['stat'] == 'approved') {
                $data['mp_dtapproved'] = date('Y-m-d');
                $data['mp_approvedby'] = $user->Emp_No;
            } elseif ($validated['stat'] == 'reviewed') {
                $data['mp_reviewedby'] = $user->Emp_No;
            } elseif ($validated['stat'] == 'declined') {
                $data['mp_declinedby'] = $user->Emp_No;
                $data['mp_decline_reason'] = $validated['reason'];
            }

            ManpowerRequest::where('mp_id', $validated['id'])->update($data);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function fillRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'replacement' => 'nullable|string',
                'additional' => 'nullable|string'
            ]);

            $validated['replacement'] = json_decode($validated['replacement'], true);
            $validated['additional'] = json_decode($validated['additional'], true);

            $count = array_sum(array_column($validated['replacement'], 'count'))
                   + array_sum(array_column($validated['additional'], 'count'));
            $fill  = array_sum(array_column($validated['replacement'], 'fill'))
                   + array_sum(array_column($validated['additional'], 'fill'));
            $progress = ($count > 0 ? round(($fill / $count) * 100) : 0) . '%,' . $fill . '/' . $count;

            // Slot order: position|count|reason|date|applicants_csv|fill
            $fields = ['position', 'count', 'reason', 'date', 'applicants_csv', 'fill'];

            // Preserve original default behaviour (fill/applicants_csv default).
            $withDefaults = fn($rows) => array_map(function ($i) {
                $i['fill'] = $i['fill'] ?? 0;
                $i['applicants_csv'] = $i['applicants_csv'] ?? '';
                return $i;
            }, $rows);

            $data = [
                'mp_replacement' => self::serializeSlots($withDefaults($validated['replacement']), $fields),
                'mp_additional'  => self::serializeSlots($withDefaults($validated['additional']), $fields),
                'mp_progress'    => $progress,
            ];

            if ($validated['id']) {
                ManpowerRequest::where('mp_id', $validated['id'])->update($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function updateRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|numeric',
                'action' => 'required|string|in:edit,cancel',
                'reason' => 'nullable|string',
            ]);

            if ($validated['action'] === 'cancel') {
                ManpowerRequest::where('mp_id', $validated['id'])->update([
                    'mp_status' => 'cancelled',
                    'mp_decline_reason' => $validated['reason'] ?? null,
                ]);

                return response()->json(['success' => true]);
            }

            DB::table("tbl_mpupdate")->insert([
                'mpu_mpid' => $validated['id'],
                'mpu_req' => $validated['action'],
                'mpu_reason' => $validated['reason'] ?? '',
                'mpu_stat' => 'pending',
                'mpu_by' => Auth::user()->Emp_No
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function approveUpdate($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $table = DB::table("tbl_mpupdate")->where('mpu_id', $id);
            $data = $table->first();

            if (!$data) {
                return response()->json(['success' => false, 'error' => 'Not found'], 404);
            }

            $action = $data->mpu_req;
            $mp_id = $data->mpu_mpid;
            $requestorEmpno = ManpowerRequest::where('mp_id', $mp_id)->value('mp_requestby');

            $authorized = $user->userAccess('personnelreq', 'viewall')
                || self::isDeptApprover($user, $requestorEmpno);

            if (!$authorized) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            if ($action == 'cancel') {
                DB::table("tbl_manpower")
                    ->where('mp_id', $mp_id)
                    ->update(['mp_status' => 'cancelled']);
            }

            $table->update(['mpu_stat' => 'approved']);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function declineUpdate($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $data = DB::table("tbl_mpupdate")->where('mpu_id', $id)->first();

            if (!$data) {
                return response()->json(['success' => false, 'error' => 'Not found'], 404);
            }

            $requestorEmpno = ManpowerRequest::where('mp_id', $data->mpu_mpid)->value('mp_requestby');

            $authorized = $user->userAccess('personnelreq', 'viewall')
                || self::isDeptApprover($user, $requestorEmpno);

            if (!$authorized) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            DB::table("tbl_mpupdate")
                ->where('mpu_id', $id)
                ->update(['mpu_stat' => 'denied']);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function applicantInterviews($id)
    {
        try {
            $records = InterviewDeets::where('app_id', $id)->get()->keyBy('interview_type');

            $empNos = $records->pluck('interviewer_empno')->filter()->unique()->values();

            $employees = DB::connection('hrd2')
                ->table('tbl201_basicinfo')
                ->whereIn('bi_empno', $empNos)
                ->select('bi_empno', 'bi_empfname', 'bi_empmname', 'bi_emplname')
                ->get()
                ->keyBy('bi_empno');

            $result = [];

            foreach (['Initial', '2nd Prelim', 'Final'] as $type) {
                $record = $records->get($type);
                if (!$record) {
                    continue;
                }

                $emp = $employees->get($record->interviewer_empno);
                $interviewerName = $record->interviewer_name;
                if (!$interviewerName && $emp) {
                    $mInitial = $emp->bi_empmname ? substr($emp->bi_empmname, 0, 1) . '.' : '';
                    $interviewerName = trim("{$emp->bi_empfname} {$mInitial} {$emp->bi_emplname}");
                }

                $result[$type] = [
                    'interviewer_name' => $interviewerName,
                    'interview_date' => $record->interview_date,
                    'company' => $record->company,
                    'department' => $record->department,
                    'position' => $record->position,
                    'remarks' => $record->remarks,
                    'recommendation' => $record->recommendation,
                    'verdict' => $record->verdict,
                ];
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public static function counts()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $counts = [];

        foreach (['draft', 'pending', 'approved', 'cancelled', 'declined'] as $stat) {
            $query = ManpowerRequest::where('mp_status', $stat);

            if ($user->userAccess('personnelreq', 'viewall')) {
                if (!in_array($stat, ['cancelled', 'draft'])) {
                    $query->whereRaw("mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat IN ('pending','approved'))");
                }
            } else {
                $query->whereRaw("(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?)", [
                    check_assign($user->Emp_No, 'PR'),
                    $user->Emp_No
                ]);
                if (!in_array($stat, ['cancelled'])) {
                    $query->whereRaw("mp_id NOT IN (SELECT mpu_mpid FROM tbl_mpupdate WHERE mpu_stat IN ('pending','approved'))");
                }
            }

            if ($stat == 'cancelled') {
                $query->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id');
            }

            $counts[$stat] = $query->count();
        }

        $pendingUpdateQuery = ManpowerRequest::query()
            ->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id');

        if ($user->userAccess('personnelreq', 'viewall') || $user->userAccess('personnelreq', 'viewer')) {
            $pendingUpdateQuery->whereRaw("(mpu_stat='pending')");
        } else {
            $pendingUpdateQuery->whereRaw(
                "(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?) AND (mpu_stat='pending')",
                [check_assign($user->Emp_No, 'PR'), $user->Emp_No]
            );
        }
        $counts['pending_update'] = $pendingUpdateQuery->count();

        $updateQuery = ManpowerRequest::query()
            ->leftJoin('tbl_mpupdate', 'mpu_mpid', '=', 'mp_id');

        if ($user->userAccess('personnelreq', 'viewall') || $user->userAccess('personnelreq', 'viewer')) {
            $updateQuery->whereRaw("mpu_stat = 'approved'");
        } else {
            $updateQuery->whereRaw(
                "(FIND_IN_SET(mp_requestby, ?) > 0 OR mp_requestby = ?) AND mpu_stat = 'approved'",
                [check_assign($user->Emp_No, 'PR'), $user->Emp_No]
            );
        }
        $counts['update'] = $updateQuery->count();

        // jobspec tab
        $jobspec = Setting::jobSpecList();
        if (!$user->userAccess('personnelreq', 'viewall')) {
            $jobspec = $jobspec->filter(
                fn($r) => strpos(check_assign($user->Emp_No, 'PR'), $r->jspec_department) !== false
            );
        }
        $counts['jobspec'] = $jobspec->count();

        return response()->json($counts);
    }
}