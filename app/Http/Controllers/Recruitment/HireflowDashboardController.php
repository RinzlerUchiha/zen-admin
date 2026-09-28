<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\HireflowStats;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * The HireFlow landing page: what needs attention now, and where the hiring
 * work is sitting. Every figure is read-only and every tile and chart segment
 * links into a page that already exists.
 *
 * Two kinds of figure live here, and they count differently on purpose:
 *
 *  - Navigation figures (the tiles, the status doughnut) use the same slugs
 *    the Manpower tabs use, so a tile saying "2" opens a tab with 2 rows.
 *    'approved' there means approved-but-not-yet-served.
 *  - Volume figures (headcount, position types, requests over time) describe
 *    all approved work regardless of posting state, because posting progress
 *    is not what they are about.
 */
class HireflowDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $stats = new HireflowStats($user);

        $counts = $stats->statusCounts();
        $manpower = url('/recruitment/manpower');
        $intake = url('/recruitment/applicant-intake');

        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'dashboard',
            'maincat' => 'dashboard',
            'page' => 'pages.recruitment.dashboard.index-content',
            'user_empno' => $user->Emp_No,

            // Waiting on somebody. Each opens the tab that holds exactly these.
            'actionTiles' => [
                [
                    'label' => 'Overdue positions',
                    'hint'  => 'Past their date needed, still unposted',
                    'icon'  => 'fa-triangle-exclamation',
                    'tone'  => 'overdue',
                    'value' => $stats->overduePositions(),
                    'url'   => url('/recruitment/job-postings') . '#jp-eligible',
                ],
                [
                    'label' => 'Pending approvals',
                    'hint'  => 'Waiting on an Approver in HireFlow',
                    'icon'  => 'fa-hourglass-half',
                    'tone'  => 'attention',
                    'value' => $counts['pending'],
                    'url'   => $manpower . '?stat=pending',
                ],
                [
                    'label' => 'Awaiting revision',
                    'hint'  => 'Returned to the Requestor for changes',
                    'icon'  => 'fa-rotate-left',
                    'tone'  => 'attention',
                    'value' => $counts['update'],
                    'url'   => $manpower . '?stat=update',
                ],
                [
                    'label' => 'Pending Edit/Cancel',
                    'hint'  => 'Approved requests awaiting a change decision',
                    'icon'  => 'fa-clock',
                    'tone'  => 'attention',
                    'value' => $stats->pendingChangeCount(),
                    'url'   => $manpower . '?stat=change-pending',
                ],
                [
                    'label' => 'Positions to post',
                    'hint'  => 'Approved positions with no open job posting',
                    'icon'  => 'fa-bullhorn',
                    'tone'  => 'attention',
                    'value' => $stats->positionsAwaitingPosting(),
                    'url'   => url('/recruitment/job-postings') . '#jp-eligible',
                ],
                [
                    'label' => 'Due within 7 days',
                    'hint'  => 'Needed soon, not yet posted',
                    'icon'  => 'fa-calendar-day',
                    'tone'  => 'progress',
                    'value' => $stats->dueSoonPositions(),
                    'url'   => url('/recruitment/job-postings') . '#jp-eligible',
                ],
                [
                    'label' => 'Draft postings',
                    'hint'  => 'Created but never published',
                    'icon'  => 'fa-file-pen',
                    'tone'  => 'progress',
                    'value' => $stats->draftPostings(),
                    'url'   => url('/recruitment/job-postings'),
                ],
            ],

            // Where the approved work has got to.
            'progressTiles' => [
                [
                    'label' => 'Approved, not yet posted',
                    'hint'  => 'No position has a job posting yet',
                    'icon'  => 'fa-circle-check',
                    'tone'  => 'progress',
                    'value' => $counts['approved'],
                    'url'   => $manpower . '?stat=approved',
                ],
                [
                    'label' => 'Served',
                    'hint'  => 'At least one position has a job posting',
                    'icon'  => 'fa-square-check',
                    'tone'  => 'done',
                    'value' => $counts['served'],
                    'url'   => $manpower . '?stat=served',
                ],
                [
                    'label' => 'Open headcount',
                    'hint'  => 'Approved headcount not yet marked filled',
                    'icon'  => 'fa-user-plus',
                    'tone'  => 'progress',
                    'value' => $stats->openHeadcount(),
                    'url'   => $manpower . '?stat=approved',
                ],
            ],

            /*
             | Steps 3 onward. Read over the existing 'applicant' connection —
             | the same one Applicant Intake uses — so the dashboard finally
             | covers the whole pipeline. Nothing is copied into portal_db.
             */
            'intakeTiles' => [
                [
                    'label' => 'Documents to review',
                    'hint'  => 'Uploaded and waiting on an HR decision',
                    'icon'  => 'fa-file-circle-check',
                    'tone'  => 'attention',
                    'value' => $stats->documentsAwaitingReview(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'Access code requests',
                    'hint'  => 'Applicants waiting for an assessment code',
                    'icon'  => 'fa-key',
                    'tone'  => 'attention',
                    'value' => $stats->assessmentAccessRequestsPending(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'Stalled applications',
                    'hint'  => 'Open with no movement for 14 days',
                    'icon'  => 'fa-hourglass-end',
                    'tone'  => 'overdue',
                    'value' => $stats->stalledApplications(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'New applications',
                    'hint'  => 'Received in the last 7 days',
                    'icon'  => 'fa-inbox',
                    'tone'  => 'progress',
                    'value' => $stats->newApplications(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'Open applications',
                    'hint'  => 'Not yet closed',
                    'icon'  => 'fa-folder-open',
                    'tone'  => 'progress',
                    'value' => $stats->applicationsNeedingAction(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'Registered, not applied',
                    'hint'  => 'Accounts with no application yet',
                    'icon'  => 'fa-user-clock',
                    'tone'  => 'inactive',
                    'value' => $stats->accountsWithoutApplications(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'Candidate Pool',
                    'hint'  => 'Closed applications worth revisiting',
                    'icon'  => 'fa-users',
                    'tone'  => 'done',
                    'value' => $stats->candidatePoolSize(),
                    'url'   => $intake,
                ],
                [
                    'label' => 'Pooled this month',
                    'hint'  => 'Added to the pool in the last 30 days',
                    'icon'  => 'fa-user-plus',
                    'tone'  => 'done',
                    'value' => $stats->candidatePoolAdditions(),
                    'url'   => $intake,
                ],
            ],

            'hiring' => [
                'open' => $stats->openHeadcount(),
                'filled' => $stats->filledHeadcount(),
            ],

            /*
             | Timing, shown as text rather than tiles: these are trends, not
             | work queues, and a median of nothing is a dash, never a zero.
             */
            'timings' => [
                ['label' => 'Median time to approve', 'value' => $stats->medianDaysToApprove(), 'unit' => 'days'],
                ['label' => 'Median time to post', 'value' => $stats->medianDaysToPost(), 'unit' => 'days'],
                ['label' => 'Oldest open posting', 'value' => $stats->oldestOpenPostingDays(), 'unit' => 'days old'],
            ],

            'charts' => [
                'status'     => $counts,
                'postings'   => $stats->jobPostingCounts(),
                'headcount'  => $stats->headcountByDepartment(),
                'months'     => $stats->requestsByMonth(),
                'types'      => $stats->positionsByType(),
            ],

            // These figures are read once, at page load; say so rather than
            // letting a tab left open all afternoon look current.
            'generatedAt' => Carbon::now(),
        ]);
    }
}
