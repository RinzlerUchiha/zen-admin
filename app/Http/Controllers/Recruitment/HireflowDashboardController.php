<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\HireflowStats;
use Illuminate\Support\Facades\Auth;

/**
 * The HireFlow landing page: what needs attention now, and where the hiring
 * work is sitting. Every figure is read-only and every chart segment links
 * into a page that already exists.
 */
class HireflowDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $stats = new HireflowStats($user);

        $statusCounts = $stats->statusCounts();
        $pendingChange = $stats->pendingChangeCount();

        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'dashboard',
            'maincat' => 'dashboard',
            'page' => 'pages.recruitment.dashboard.index-content',
            'user_empno' => $user->Emp_No,

            // Tiles: the things a person can act on today.
            'tiles' => [
                [
                    'key'   => 'pending',
                    'label' => 'Pending approvals',
                    'hint'  => 'Requests waiting on an Approver',
                    'icon'  => 'fa-hourglass-half',
                    'value' => $statusCounts['pending'],
                    'url'   => url('/recruitment/manpower') . '?stat=pending',
                ],
                [
                    'key'   => 'change-pending',
                    'label' => 'Pending Edit/Cancel',
                    'hint'  => 'Approved requests awaiting a change decision',
                    'icon'  => 'fa-clock',
                    'value' => $pendingChange,
                    'url'   => url('/recruitment/manpower') . '?stat=change-pending',
                ],
                [
                    'key'   => 'awaiting-posting',
                    'label' => 'Positions to post',
                    'hint'  => 'Approved positions with no job posting yet',
                    'icon'  => 'fa-bullhorn',
                    'value' => $stats->positionsAwaitingPosting(),
                    'url'   => url('/recruitment/job-postings'),
                ],
                [
                    'key'   => 'open-headcount',
                    'label' => 'Open headcount',
                    'hint'  => 'Approved headcount not yet filled',
                    'icon'  => 'fa-user-plus',
                    'value' => $stats->openHeadcount(),
                    'url'   => url('/recruitment/manpower') . '?stat=approved',
                ],
            ],

            // Chart datasets, consumed by Chart.js in the view.
            'charts' => [
                'status'     => $statusCounts,
                'postings'   => $stats->jobPostingCounts(),
                'headcount'  => $stats->headcountByDepartment(),
                'months'     => $stats->requestsByMonth(),
                'types'      => $stats->positionsByType(),
            ],
        ]);
    }
}
