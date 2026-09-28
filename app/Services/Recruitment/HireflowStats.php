<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\HireflowManpowerRequest;
use App\Models\Recruitment\JobPosting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Every number the HireFlow dashboard shows, counted in one place.
 *
 * It exists so the dashboard and the Manpower tabs can never disagree: both
 * go through the same visibility rule (the manpower-requests.view-all gate,
 * falling back to the approver assignment), so a tile that says "3 pending"
 * always opens a list with three rows in it.
 *
 * Read-only throughout. HireFlow (manpower/) remains the system of record.
 */
class HireflowStats
{
    /** URL slug => real HireFlow status value. Mirrors ManpowerController. */
    public const STATUS_MAP = [
        'draft'     => 'Draft',
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'update'    => 'Returned',
        'cancelled' => 'Cancelled',
        'declined'  => 'Rejected',
    ];

    private bool $canViewAll;

    public function __construct(private $user)
    {
        $this->canViewAll = Gate::forUser($user)->allows('manpower-requests.view-all');
    }

    /**
     * Requests this user may see - the same rule ManpowerController::list()
     * applies, so counts and lists line up.
     */
    private function requests()
    {
        $query = HireflowManpowerRequest::query();

        if ($this->canViewAll) {
            return $query;
        }

        $assigned = check_assign($this->user->Emp_No, 'PR');

        return $query->whereRaw(
            "(FIND_IN_SET(requestor_employee_id, ?) > 0 OR requestor_employee_id = ?)",
            [$assigned, $this->user->Emp_No]
        );
    }

    /** Positions belonging to visible requests, optionally of one status. */
    private function positions(?string $status = null)
    {
        $requestIds = $this->requests()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->select('id');

        return DB::table('tbl_manpower_request_position')
            ->whereIn('request_id', $requestIds);
    }

    // ---------------------------------------------------------------- tiles

    /**
     * Approved requests where every position already has a job posting.
     *
     * Same rule as ManpowerController::markPosted() and, behind that,
     * Zen-Hub's mp_mark_served(): a request is Served once AT LEAST ONE of its
     * positions has a posting row, whatever that posting's status. Covered by
     * a test that asserts the dashboard and the Manpower tabs agree, so this
     * cannot drift silently.
     */
    public function servedCount(): int
    {
        $rows = $this->requests()->with('positions')->where('status', 'Approved')->get();

        $positionIds = $rows->pluck('positions')->flatten()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (empty($positionIds)) {
            return 0;
        }

        $posted = JobPosting::whereIn('request_position_id', $positionIds)
            ->pluck('request_position_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        return $rows->filter(function ($r) use ($posted) {
            return $r->positions->contains(fn ($p) => $posted->has((int) $p->id));
        })->count();
    }

    /**
     * Counts keyed by the slugs the Manpower tabs use, so every figure on the
     * dashboard opens a tab showing exactly that many rows.
     *
     * 'approved' therefore excludes served requests, matching the tab; the
     * stored status is untouched, and 'served' is derived.
     */
    public function statusCounts(): array
    {
        $rows = $this->requests()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [];
        foreach (self::STATUS_MAP as $slug => $status) {
            $counts[$slug] = (int) ($rows[$status] ?? 0);
        }

        $counts['served'] = $this->servedCount();
        $counts['approved'] = max(0, $counts['approved'] - $counts['served']);

        return $counts;
    }

    /** Every approved request, served or not — for volume, not navigation. */
    public function approvedTotal(): int
    {
        return (int) $this->requests()->where('status', 'Approved')->count();
    }

    public function pendingChangeCount(): int
    {
        return (int) $this->requests()
            ->where('status', 'Approved')
            ->whereHas('pendingChange')
            ->count();
    }

    /**
     * Approved positions nobody has turned into a job posting yet - the exact
     * set JobPostingController offers as "eligible", so the tile and that page
     * always agree.
     */
    public function positionsAwaitingPosting(): int
    {
        $taken = JobPosting::whereIn('status', ['Draft', 'Published'])->pluck('request_position_id');

        return (int) $this->positions('Approved')->whereNotIn('id', $taken)->count();
    }

    /** Headcount approved but not yet marked filled. */
    public function openHeadcount(): int
    {
        $row = $this->positions('Approved')
            ->selectRaw('COALESCE(SUM(headcount), 0) AS heads, COALESCE(SUM(filled), 0) AS filled')
            ->first();

        return (int) max(0, ($row->heads ?? 0) - ($row->filled ?? 0));
    }

    // ------------------------------------------------- operational metrics

    /**
     * Approved positions whose date_needed has passed and which still have no
     * job posting. date_needed is captured on every position and was not used
     * anywhere until now; it is the only genuine urgency signal in the data.
     */
    public function overduePositions(): int
    {
        $taken = JobPosting::whereIn('status', ['Draft', 'Published'])->pluck('request_position_id');

        return (int) $this->positions('Approved')
            ->whereNotNull('date_needed')
            ->whereDate('date_needed', '<', Carbon::today())
            ->whereNotIn('id', $taken)
            ->count();
    }

    /** Approved, unposted positions due within the next week. */
    public function dueSoonPositions(int $days = 7): int
    {
        $taken = JobPosting::whereIn('status', ['Draft', 'Published'])->pluck('request_position_id');

        return (int) $this->positions('Approved')
            ->whereNotNull('date_needed')
            ->whereBetween('date_needed', [Carbon::today(), Carbon::today()->addDays($days)])
            ->whereNotIn('id', $taken)
            ->count();
    }

    /**
     * Median days from submission to the approval that decided it.
     *
     * Uses tbl_manpower_approval_log, the only record of when a decision was
     * taken. Null when nothing has been approved yet — the dashboard shows a
     * dash rather than inventing a zero.
     */
    public function medianDaysToApprove(): ?float
    {
        $ids = $this->requests()->where('status', 'Approved')->pluck('id');
        if ($ids->isEmpty()) {
            return null;
        }

        $days = DB::table('tbl_manpower_approval_log as l')
            ->join('tbl_manpower_request as r', 'r.id', '=', 'l.request_id')
            ->whereIn('l.request_id', $ids)
            ->where('l.action', 'Approved')
            ->groupBy('l.request_id', 'r.created_at')
            ->selectRaw('TIMESTAMPDIFF(HOUR, r.created_at, MIN(l.action_date)) / 24 AS d')
            ->pluck('d')
            ->map(fn ($d) => (float) $d)
            ->filter(fn ($d) => $d >= 0)
            ->sort()
            ->values();

        return $days->isEmpty() ? null : self::median($days->all());
    }

    /** Oldest still-open (Published) posting, in days. */
    public function oldestOpenPostingDays(): ?int
    {
        $visiblePositionIds = $this->positions()->select('id');

        $oldest = JobPosting::whereIn('request_position_id', $visiblePositionIds)
            ->where('status', 'Published')
            ->whereNotNull('posted_at')
            ->min('posted_at');

        return $oldest ? (int) Carbon::parse($oldest)->diffInDays(Carbon::now()) : null;
    }

    /** Median days from a position being approved to its posting going up. */
    public function medianDaysToPost(): ?float
    {
        $days = DB::table('tbl_job_posting as jp')
            ->join('tbl_manpower_request_position as p', 'p.id', '=', 'jp.request_position_id')
            ->join('tbl_manpower_request as r', 'r.id', '=', 'p.request_id')
            ->whereIn('p.request_id', $this->requests()->select('id'))
            ->whereNotNull('jp.posted_at')
            ->selectRaw('TIMESTAMPDIFF(HOUR, r.created_at, jp.posted_at) / 24 AS d')
            ->pluck('d')
            ->map(fn ($d) => (float) $d)
            ->filter(fn ($d) => $d >= 0)
            ->sort()
            ->values();

        return $days->isEmpty() ? null : self::median($days->all());
    }

    /** Draft postings — created but never published. */
    public function draftPostings(): int
    {
        return (int) JobPosting::whereIn('request_position_id', $this->positions()->select('id'))
            ->where('status', 'Draft')
            ->count();
    }

    // ------------------------------------------------ applicant-side figures
    //
    // Read over the existing 'applicant' connection, the same one Applicant
    // Intake uses. Nothing is copied into portal_db and no applicant table is
    // created there; these are counts, computed at page load.

    /** Applications received in the last N days. */
    public function newApplications(int $days = 7): int
    {
        return (int) DB::connection('applicant')->table('tblapp_applications')
            ->where('applied_at', '>=', Carbon::now()->subDays($days))
            ->count();
    }

    /**
     * Open applications whose documents are not finished — the queue HR works
     * through. "Open" comes from config/applications.php via the model, so
     * this and the portal always agree on what closed means.
     */
    public function applicationsNeedingAction(): int
    {
        $open = array_values(array_diff(
            array_keys(config('applications.statuses', [])),
            \App\Models\Applicant\ApplicantApplication::closedStatuses()
        ));

        return (int) DB::connection('applicant')->table('tblapp_applications')
            ->whereIn('status', $open)
            ->count();
    }

    /** Uploaded documents still waiting on an HR decision. */
    public function documentsAwaitingReview(): int
    {
        return (int) DB::connection('applicant')->table('tblapp_documents')
            ->whereNull('reviewed_at')
            ->count();
    }

    /** Applicants asking for an assessment access code. */
    public function assessmentAccessRequestsPending(): int
    {
        return (int) DB::connection('applicant')->table('tblapp_assessment_access_requests')
            ->whereNull('resolved_at')
            ->count();
    }

    /** Open applications with no movement for N days. */
    public function stalledApplications(int $days = 14): int
    {
        $open = array_values(array_diff(
            array_keys(config('applications.statuses', [])),
            \App\Models\Applicant\ApplicantApplication::closedStatuses()
        ));

        return (int) DB::connection('applicant')->table('tblapp_applications')
            ->whereIn('status', $open)
            ->where('applied_at', '<', Carbon::now()->subDays($days))
            ->count();
    }

    /** Candidate Pool size — per application, as config/applications.php defines. */
    public function candidatePoolSize(): int
    {
        return (int) DB::connection('applicant')->table('tblapp_applications')
            ->whereIn('status', \App\Models\Applicant\ApplicantApplication::candidatePoolStatuses())
            ->count();
    }

    /** Applications added to the pool in the last N days. */
    public function candidatePoolAdditions(int $days = 30): int
    {
        return (int) DB::connection('applicant')->table('tblapp_applications')
            ->whereIn('status', \App\Models\Applicant\ApplicantApplication::candidatePoolStatuses())
            ->where('closed_at', '>=', Carbon::now()->subDays($days))
            ->count();
    }

    /** Registered accounts that have never applied. */
    public function accountsWithoutApplications(): int
    {
        return (int) DB::connection('applicant')->table('tblapp_persinfo as p')
            ->leftJoin('tblapp_applications as a', 'a.app_id', '=', 'p.app_id')
            ->whereNull('a.id')
            ->count();
    }

    /** Headcount already marked filled, across approved requests. */
    public function filledHeadcount(): int
    {
        return (int) ($this->positions('Approved')->sum('filled') ?? 0);
    }

    private static function median(array $sorted): float
    {
        $n = count($sorted);
        $mid = intdiv($n, 2);

        return round($n % 2 ? $sorted[$mid] : ($sorted[$mid - 1] + $sorted[$mid]) / 2, 1);
    }

    // --------------------------------------------------------------- charts

    /** Draft / Published / Closed, for postings on visible requests. */
    public function jobPostingCounts(): array
    {
        $visiblePositionIds = $this->positions()->select('id');

        $rows = JobPosting::whereIn('request_position_id', $visiblePositionIds)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'Draft'     => (int) ($rows['Draft'] ?? 0),
            'Published' => (int) ($rows['Published'] ?? 0),
            'Closed'    => (int) ($rows['Closed'] ?? 0),
        ];
    }

    /**
     * Approved headcount against what has been filled, per department.
     *
     * department_id holds a Dept_Code; the readable name comes from the HRIS
     * in one extra query, explicitly qualified to the hrd2 connection because
     * portal_db carries an older twin of tbl_department.
     */
    public function headcountByDepartment(): array
    {
        $approved = $this->requests()->where('status', 'Approved')->select('id', 'department_id');

        $rows = DB::table('tbl_manpower_request_position as p')
            ->joinSub($approved, 'r', 'r.id', '=', 'p.request_id')
            ->groupBy('r.department_id')
            ->selectRaw('r.department_id, COALESCE(SUM(p.headcount), 0) AS heads, COALESCE(SUM(p.filled), 0) AS filled')
            ->orderByDesc('heads')
            ->get();

        if ($rows->isEmpty()) {
            return ['labels' => [], 'filled' => [], 'open' => []];
        }

        $names = DB::connection('hrd2')->table('tbl_department')
            ->whereIn('Dept_Code', $rows->pluck('department_id')->filter()->all())
            ->pluck('Dept_Name', 'Dept_Code');

        return [
            'labels' => $rows->map(fn ($r) => $names[$r->department_id] ?? ($r->department_id ?: 'Unassigned'))->all(),
            'filled' => $rows->map(fn ($r) => (int) $r->filled)->all(),
            'open'   => $rows->map(fn ($r) => (int) max(0, $r->heads - $r->filled))->all(),
        ];
    }

    /** Requests raised per month, oldest first, gaps filled with zero. */
    public function requestsByMonth(int $months = 6): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $rows = $this->requests()
            ->where('created_at', '>=', $start->toDateTimeString())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = $keys = $data = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $keys[] = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = (int) ($rows[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'keys' => $keys, 'data' => $data];
    }

    /** Replacement vs additional, across approved positions. */
    public function positionsByType(): array
    {
        $rows = $this->positions('Approved')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'replacement' => (int) ($rows['replacement'] ?? 0),
            'additional'  => (int) ($rows['additional'] ?? 0),
        ];
    }
}
