<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerRequest;
use App\Models\Recruitment\HireflowManpowerPosition;
use App\Models\Recruitment\JobPosting;
use App\Services\Recruitment\Ad\JobShortDescription;
use App\Services\Recruitment\PostingChannels;
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

        $postings = JobPosting::with(['hireflowPosition.request', 'channels'])
            ->orderByDesc('created_at')
            ->get();

        /*
         | Posting-desk figures. Deliberately narrower than the dashboard's:
         | these describe this page's own list, so HR does not have to count
         | rows or leave to find out what needs doing.
         */
        $openIds = $postings->where('status', 'Published')->pluck('id');
        $withApplicants = $openIds->isEmpty()
            ? collect()
            : collect(\Illuminate\Support\Facades\DB::connection('applicant')
                ->table('tblapp_applications')
                ->whereIn('job_posting_id', $openIds->all())
                ->distinct()
                ->pluck('job_posting_id'))->map(fn ($id) => (int) $id);

        $oldestOpen = $postings->where('status', 'Published')->whereNotNull('posted_at')->min('posted_at');

        $postingStats = [
            'draft' => $postings->where('status', 'Draft')->count(),
            'published' => $postings->where('status', 'Published')->count(),
            'closed' => $postings->where('status', 'Closed')->count(),
            'no_applicants' => $openIds->reject(fn ($id) => $withApplicants->contains((int) $id))->count(),
            'waiting' => $eligibleRequests->sum(fn ($r) => $r->positions->count()),
            'oldest_open_days' => $oldestOpen
                ? (int) \Illuminate\Support\Carbon::parse($oldestOpen)->diffInDays(now())
                : null,
        ];

        return view('pages.recruitment', [
            'eligibleRequests' => $eligibleRequests,
            'postings' => $postings,
            'postingStats' => $postingStats,
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
            'short_description' => (new JobShortDescription())->composeFor($position),
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
            'short_description' => $jobPosting->short_description,
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
            'short_description'   => 'nullable|string|max:' . JobShortDescription::MAX,
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
            // Like the ad: never created without one.
            'short_description'    => trim((string) ($validated['short_description'] ?? '')) !== ''
                ? trim($validated['short_description'])
                : (new JobShortDescription())->composeFor($position),
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
    public function suggestAd(Request $request, JobPosting $jobPosting)
    {
        $position = $jobPosting->hireflowPosition;

        if (!$position) {
            return response()->json([
                'message' => 'This posting is not linked to a HireFlow position, so an ad cannot be composed automatically.',
            ], 422);
        }

        $registry = new \App\Services\Recruitment\Ad\AdTemplateRegistry();

        // ?template=<key> writes the ad that way; without it, the rule
        // recommends. An unknown key is ignored rather than failing.
        $wanted = $request->query('template');
        $wanted = ($wanted !== null && $registry->get($wanted)) ? $wanted : null;

        $composer = (new \App\Services\Recruitment\Ad\JobAdComposer())->useTemplate($wanted);
        $ad = $composer->composeFor($position);
        $template = $composer->lastTemplate();

        return response()->json([
            'public_description' => $ad,
            'short_description' => (new JobShortDescription())->composeFor($position),
            'template' => $template ? [
                'key' => $template['key'],
                'name' => $template['template']['name'] ?? $template['key'],
                'reason' => $template['reason'],
                'chosen_by_hr' => (bool) ($template['forced'] ?? false),
            ] : null,
            // Every template HR can switch to, so the panel can offer them.
            'templates' => collect($registry->all())->map(fn ($t, $key) => [
                'key' => $key,
                'name' => $t['name'],
                'description' => $t['description'],
            ])->values(),
        ]);
    }

    /**
     * Where this posting is advertised, and what each place brought in.
     *
     * The counts describe HireFlow's own Careers page: how many people
     * opened it through each channel's tagged link, and how many of them
     * applied. They are not the platform's impressions or views — we have
     * no API to any of these sites and never present one as if we did.
     */
    public function channels(JobPosting $jobPosting)
    {
        $channels = new PostingChannels();

        return response()->json([
            'channels' => $channels->summaryFor($jobPosting),
            // Everything HR could tick, including anything recorded before a
            // channel was catalogued.
            'available' => collect($channels->availableFor($jobPosting))
                ->map(fn ($channel, $key) => [
                    'key' => $key,
                    'name' => $channel['name'],
                    'description' => $channel['description'] ?? '',
                    'external' => (bool) ($channel['external'] ?? true),
                ])->values(),
            // False until CAREERS_URL is set, so the panel can say the links
            // are unavailable instead of handing out ones that go nowhere.
            'links_ready' => config('posting_channels.careers_url') !== '',
        ]);
    }

    /**
     * Record where HR is advertising this posting.
     *
     * Ticking a channel does not publish anything — HireFlow cannot post to
     * LinkedIn or JobStreet. It says "we are putting it here", hands back a
     * tagged link to paste there, and starts counting what returns.
     */
    public function updateChannels(Request $request, JobPosting $jobPosting)
    {
        if ($jobPosting->status === 'Closed') {
            return response()->json([
                'message' => 'A closed posting is no longer being advertised, so its channels cannot be changed.',
            ], 422);
        }

        $validated = $request->validate([
            'channels' => 'present|array',
            'channels.*.state' => 'nullable|string|in:Posted,Not Posted',
            'channels.*.external_url' => 'nullable|string|max:500|url',
        ], [
            'channels.*.external_url.url' => 'The link to the external post must be a full URL, e.g. https://…',
        ]);

        $channels = new PostingChannels();
        $result = $channels->sync($jobPosting, $validated['channels']);

        return response()->json([
            'success' => true,
            'channels' => $channels->summaryFor($jobPosting),
            // A channel with visits or applications behind it is kept rather
            // than deleted; say so, or the row reappearing looks like a bug.
            'retained' => $result['retained'],
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
            'short_description'   => 'nullable|string|max:' . JobShortDescription::MAX,
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

        if ($request->has('short_description')) {
            $jobPosting->short_description = trim((string) $validated['short_description']) ?: null;
        }

        $jobPosting->save();

        return response()->json([
            'success'             => true,
            'id'                  => $jobPosting->id,
            'posting_description' => $jobPosting->posting_description,
            'public_description'  => $jobPosting->public_description,
            'short_description'   => $jobPosting->short_description,
            'ad_is_custom'        => (bool) $jobPosting->ad_is_custom,
        ]);
    }
}
