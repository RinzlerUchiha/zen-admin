<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\InterviewWorkload;

/**
 * Every interview HR has, in one place.
 *
 * The interview engine was complete and entirely application-scoped: 22
 * routes, every one of them under /applications/{application}/interviews, and
 * every service method taking an $applicationId. So the only way to answer
 * "what is happening this week" or "who still owes me an evaluation" was to
 * open applicants one at a time and remember.
 *
 * THIS IS A LENS, NOT A SECOND WORKFLOW
 *
 * Nothing here schedules, assigns, marks held or evaluates. Every row links
 * to the per-application page that already does those things, and that page
 * remains the only place an interview changes. This controller issues reads
 * and nothing else — no writes, no new tables, no state of its own.
 *
 * It is gated on applicant-interviews.view, the same right the
 * per-application page uses, so it shows nobody anything they could not
 * already reach by opening the application directly. Interviewers reach
 * their own interviews through the separate interviewer API, which checks
 * assignment per interview; nothing here touches that path or widens it.
 */
class InterviewWorkspaceController extends Controller
{
    /** Scheduled this many days ahead still counts as "coming up". */
    private const UPCOMING_DAYS = 7;

    public function index()
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'interviews',
            'maincat' => 'interviews',
            /* Its own view. pages.recruitment.interviews belongs to the
               PER-APPLICATION interview page and must not be shadowed. */
            'page' => 'pages.recruitment.interviews-workspace.index-content',
        ]);
    }

    /**
     * The four views, in one answer.
     *
     * The deriving is done by InterviewWorkload, which the Work Queue reads
     * too — so "needs scheduling" cannot mean one thing here and another
     * there. This method is the HTTP wrapper and nothing else.
     */
    public function data()
    {
        return response()->json(InterviewWorkload::collect());
    }
}
