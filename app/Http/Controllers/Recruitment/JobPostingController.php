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
            ->get()
            ->filter(fn ($request) => $request->positions->isNotEmpty())
            ->values();

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
            'public_ad' => $position->draftPublicAd(),
        ]);
    }

    public function showJson(JobPosting $jobPosting)
    {
        $jobPosting->load('hireflowPosition.request');

        // Pick up any jobspec change since this ad was last composed.
        // No-op when a person has edited the wording.
        $jobPosting->syncPublicAd();

        return response()->json([
            'id' => $jobPosting->id,
            'posting_title' => $jobPosting->posting_title,
            'status' => $jobPosting->status,
            'position_title' => $jobPosting->hireflowPosition?->positionTitle() ?? '—',
            'mr_no' => $jobPosting->hireflowPosition?->request?->mr_no ?? '—',
            'posting_description' => $jobPosting->posting_description,
            'public_description' => $jobPosting->public_description,
            'ad_is_custom' => (bool) $jobPosting->ad_is_custom,
            'created_by' => $jobPosting->created_by,
            'posted_at' => $jobPosting->posted_at?->format('M d, Y h:i A'),
            'closed_at' => $jobPosting->closed_at?->format('M d, Y h:i A'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_position_id' => 'required|integer',
            'posting_title'       => 'required|string|max:255',
            'posting_description' => 'nullable|string',
            'public_description'  => 'nullable|string',
        ]);

        $position = HireflowManpowerPosition::findOrFail($validated['request_position_id']);

        // Pull jobspec_id off the position's jobSpec() lookup rather than requiring it from the form
        $jobSpec = $position->jobSpec();

        // A posting is never created without a public ad — if the form did not
        // send one, compose it from the jobspec so the careers page never has
        // to fall back to the internal description.
        $ad = $validated['public_description'] ?? $position->draftPublicAd();

        $jobPosting = JobPosting::create([
            'request_position_id' => $position->id,
            'jobspec_id'           => $jobSpec->jspec_id ?? null,
            'posting_title'        => $validated['posting_title'],
            'posting_description'  => $validated['posting_description'] ?? null,
            'public_description'   => $ad,
            'status'                => 'Draft',
            'created_by'            => Auth::user()->Emp_No ?? null,
        ]);

        // Untouched composer output stays auto-synced; edited wording is locked.
        $jobPosting->setRelation('hireflowPosition', $position);
        $jobPosting->markAdOwnership($ad);
        $jobPosting->save();

        return response()->json([
            'success' => true,
            'id' => $jobPosting->id,
            'ad_is_custom' => (bool) $jobPosting->ad_is_custom,
        ]);
    }

    /**
     * Re-composes the public ad from this posting's linked jobspec.
     * Returns the draft text only — it is not saved until the user
     * reviews it and hits Save in the panel.
     */
    public function suggestAd(JobPosting $jobPosting)
    {
        $position = $jobPosting->hireflowPosition;

        if (!$position) {
            return response()->json([
                'message' => 'This posting is not linked to a HireFlow position, so an ad cannot be composed automatically.',
            ], 422);
        }

        return response()->json([
            'public_description' => $position->draftPublicAd(),
        ]);
    }

    public function updateStatus(Request $request, JobPosting $jobPosting)
    {
        $validated = $request->validate([
            'status' => 'required|in:Draft,Published,Closed',
            'posting_description' => 'nullable|string',
        ]);

        if ($request->has('posting_description')) {
            $jobPosting->posting_description = $validated['posting_description'];
        }

        $jobPosting->status = $validated['status'];

        // Going live is the last moment the ad can be brought up to date.
        if ($validated['status'] === 'Published') {
            $jobPosting->syncPublicAd();
        }

        if ($validated['status'] === 'Published' && !$jobPosting->posted_at) {
            $jobPosting->posted_at = now();
        }

        if ($validated['status'] === 'Closed' && !$jobPosting->closed_at) {
            $jobPosting->closed_at = now();
        }

        $jobPosting->save();

        return response()->json([
            'success'   => true,
            'id'        => $jobPosting->id,
            'status'    => $jobPosting->status,
            'posted_at' => $jobPosting->posted_at?->format('M d, Y h:i A'),
            'closed_at' => $jobPosting->closed_at?->format('M d, Y h:i A'),
        ]);
    }

    public function updateDescription(Request $request, JobPosting $jobPosting)
    {
        if ($jobPosting->status === 'Closed') {
            return response()->json([
                'message' => 'A closed posting can no longer be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'posting_description' => 'nullable|string',
            'public_description'  => 'nullable|string',
        ]);

        // has() guards so a request carrying only one field cannot blank the other.
        if ($request->has('posting_description')) {
            $jobPosting->posting_description = $validated['posting_description'];
        }

        if ($request->has('public_description')) {
            $jobPosting->public_description = $validated['public_description'];

            // Text identical to the composer's output returns the posting to
            // auto-sync; anything else marks it hand-written and protected.
            $jobPosting->markAdOwnership($validated['public_description']);
        }

        $jobPosting->save();

        return response()->json([
            'success'             => true,
            'id'                  => $jobPosting->id,
            'posting_description' => $jobPosting->posting_description,
            'public_description'  => $jobPosting->public_description,
            'ad_is_custom'        => (bool) $jobPosting->ad_is_custom,
        ]);
    }
}
