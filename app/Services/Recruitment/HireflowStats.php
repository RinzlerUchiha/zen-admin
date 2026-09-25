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

        return $counts;
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
