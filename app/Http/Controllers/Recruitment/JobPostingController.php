<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerRequest;
use App\Models\Recruitment\HireflowManpowerPosition;
use App\Models\Recruitment\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobPostingController extends Controller
{
    public function index()
    {
        // Fetch active-posting position IDs from portal_db first — a
        // cross-connection whereDoesntHave would query tbl_job_posting
        // against the hrd2 connection and fail, since JobPosting lives
        // on the default connection, not hrd2.
        $postedPositionIds = JobPosting::whereIn('status', ['Draft', 'Published'])
            ->pluck('request_position_id');

        $eligibleRequests = HireflowManpowerRequest::approved()
            ->with(['positions' => function ($q) use ($postedPositionIds) {
                $q->whereNotIn('id', $postedPositionIds);
            }])
            ->get();

        $postings = JobPosting::with('hireflowPosition')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.recruitment', [
            'eligibleRequests' => $eligibleRequests,
            'postings' => $postings,
            'main_link' => 'recruitment',
            'sub_link' => 'job-postings',
            'maincat' => 'job-postings',
            'page' => 'pages.recruitment.job-postings.index-content',
        ]);
    }

    public function draft(HireflowManpowerPosition $position)
    {
        return response()->json([
            'title' => $position->positionTitle(),
            'description' => $position->draftPostingDescription(),
        ]);
    }

    public function show(JobPosting $jobPosting)
    {
        $jobPosting->load('hireflowPosition.request');

        return view('pages.recruitment.job-postings.show', [
            'jobPosting' => $jobPosting,
            'main_link' => 'recruitment',
            'sub_link' => 'job-postings',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_position_id' => 'required|integer',
            'posting_title'       => 'required|string|max:255',
            'posting_description' => 'nullable|string',
        ]);

        $position = HireflowManpowerPosition::findOrFail($validated['request_position_id']);

        // Pull jobspec_id off the position's jobSpec() lookup rather than requiring it from the form
        $jobSpec = $position->jobSpec();

        $jobPosting = JobPosting::create([
            'request_position_id' => $position->id,
            'jobspec_id'           => $jobSpec->jspec_id ?? null,
            'posting_title'        => $validated['posting_title'],
            'posting_description'  => $validated['posting_description'] ?? null,
            'status'                => 'Draft',
            'created_by'            => Auth::user()->Emp_No ?? null,
        ]);

        return redirect()
            ->route('recruitment.job-postings.show', $jobPosting)
            ->with('success', 'Job posting created.');
    }

    public function updateStatus(Request $request, JobPosting $jobPosting)
    {
        $validated = $request->validate([
            'status' => 'required|in:Draft,Published,Closed',
        ]);

        $jobPosting->status = $validated['status'];

        if ($validated['status'] === 'Published' && !$jobPosting->posted_at) {
            $jobPosting->posted_at = now();
        }

        if ($validated['status'] === 'Closed' && !$jobPosting->closed_at) {
            $jobPosting->closed_at = now();
        }

        $jobPosting->save();

        return back()->with('success', 'Status updated.');
    }
}
