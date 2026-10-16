<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerRequest;
use App\Models\Recruitment\HireflowManpowerPosition;
use App\Models\Recruitment\JobPosting;
use App\Services\Recruitment\PositionGrouping;
use App\Services\Recruitment\Ad\JobShortDescription;
use App\Services\Recruitment\PostingChannels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        | The same job asked for by more than one request, read as one.
        |
        | $eligibleRequests stays exactly as it was -- it is grouped by MR and
        | other things on this page rely on it. This is a second view of the
        | same rows, grouped by the job instead, so the posting desk can see
        | that Junior Programmer is wanted three times across two requests
        | rather than having to add it up from separate cards.
        |
        | Nothing is merged. Each entry still lists its contributing requests
        | and each still creates its own posting against its own position, so
        | every request keeps its own relationship and the Served rule is
        | unaffected.
        */
        $waitingByJob = PositionGrouping::byJob(
            $eligibleRequests->pluck('positions')->flatten()
        );

        /* Every position on a standing request, so a posting can report how
           many of its job are wanted in total, not just its own slice. */
        $allPositions = HireflowManpowerPosition::with('request')->get()
            ->filter(fn ($p) => PositionGrouping::stillStands($p));

        $postingGroups = $postings->mapWithKeys(fn ($posting) => [
            $posting->id => PositionGrouping::forPosting($posting, $allPositions),
        ]);

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
            'waitingByJob' => $waitingByJob,
            'postingGroups' => $postingGroups,
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
            /*
            | Who made it, as a person rather than a payroll number.
            |
            | The panel printed "045-2025-001", which identifies somebody
            | exactly and tells you nothing. The number is kept alongside, so
            | it is still there when two people share a name and when somebody
            | needs to look the record up.
            */
            'created_by' => $jobPosting->created_by,
            'created_by_name' => $this->employeeName($jobPosting->created_by),
            'posted_at' => $jobPosting->posted_at?->format('M d, Y h:i A'),
            'closed_at' => $jobPosting->closed_at?->format('M d, Y h:i A'),

            /* Which job specification this posting is matched against, so the
               panel can offer the requirement-classification screen -- and
               hide it when the position was never given one. */
            'jobspec_id' => $jobPosting->jobspec_id
                ?: $jobPosting->hireflowPosition?->jobspec_id,
        ]);
    }

    /**
     * An employee's full name, from the directory.
     *
     * NOT tbl_user2.U_Name: that column holds the login handle -- "hpcoruña"
     * -- which is no more readable than the number it was meant to replace.
     * The names live in tngc_hrd2.tbl201_basicinfo, which is the same lookup
     * HireFlow's own auth and the manpower list already use.
     *
     * Read-only, and one row: this is called once per panel open.
     */
    private function employeeName(?string $empNo): ?string
    {
        $empNo = trim((string) $empNo);

        if ($empNo === '') {
            return null;
        }

        $row = DB::connection('hrd2')->table('tbl201_basicinfo')
            ->where('bi_empno', $empNo)
            ->first(['bi_empfname', 'bi_empmname', 'bi_emplname', 'bi_empext']);

        if (!$row) {
            return null;
        }

        /* Given name first: this reads as a byline, not a directory entry. */
        $name = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $row->bi_empfname,
            $row->bi_empmname,
            $row->bi_emplname,
            $row->bi_empext,
        ]))));

        return $name !== '' ? $name : null;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_position_id' => 'required|integer',
            'posting_title'       => 'required|string|max:255',
            'posting_description' => 'nullable|string',
            'public_description'  => 'nullable|string',
            'short_description'   => 'nullable|string|max:' . JobShortDescription::MAX,

            /*
            | Where HR intends to advertise it, chosen on the create form.
            |
            | Absent means "all of them" -- what an older client, or a form
            | whose ticks never loaded, would send. An empty array is instead
            | a deliberate "nowhere but the Careers page", and the two are
            | told apart by whether the key is there at all, not by its value.
            */
            'channels'            => 'sometimes|array',
            'channels.*'          => 'string|max:40',
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

        /*
        | The places we advertise, ticked to begin with.
        |
        | A posting used to start with no channels at all, so publishing it
        | handed HR no links and they had to go and add the same handful of
        | places every time. Every external channel in the catalogue starts
        | available; HR unticks the ones they are not using before they
        | publish, which is the rarer action and the one worth the effort.
        |
        | Not Posted, every one of them -- nothing here claims an ad has gone
        | up anywhere.
        */
        $chosen = $request->has('channels')
            ? array_values(array_filter((array) ($validated['channels'] ?? []), 'is_string'))
            : null;

        (new PostingChannels())->seedFor($jobPosting, $chosen);

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

        return response()->json($this->composeAd($position, $request->query('template')));
    }

    /**
     * The same composition, before the posting exists.
     *
     * Composing an ad never needed the posting: suggestAd() took one only to
     * reach its position, and the position is what the templates read. HR
     * writing a NEW posting had the first draft handed to them and then no
     * way to ask for another -- no Generate, and none of the templates --
     * until they had saved it and reopened it in the panel, which is the one
     * moment they are least likely to want to stop.
     */
    public function draftSuggestAd(Request $request, HireflowManpowerPosition $position)
    {
        return response()->json($this->composeAd($position, $request->query('template')));
    }

    /**
     * Compose a public ad and a short description for a position.
     *
     * One body for both doors, so the ad HR previews while writing a posting
     * cannot differ from the one they get after saving it.
     */
    private function composeAd(HireflowManpowerPosition $position, $wanted): array
    {
        $registry = new \App\Services\Recruitment\Ad\AdTemplateRegistry();

        // ?template=<key> writes the ad that way; without it, the rule
        // recommends. An unknown key is ignored rather than failing.
        $wanted = ($wanted !== null && $registry->get($wanted)) ? $wanted : null;

        $composer = (new \App\Services\Recruitment\Ad\JobAdComposer())->useTemplate($wanted);
        $ad = $composer->composeFor($position);
        $template = $composer->lastTemplate();

        return [
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
        ];
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

        /*
        | A posting made before seeding existed gets its channels now.
        |
        | Otherwise two postings side by side would behave differently with
        | nothing on screen to explain why -- and the posting that predates
        | seeding is, by definition, the live one HR actually uses.
        |
        | Draft and Published; closed postings are left alone, and only
        | channels it does not already have are added, so a posting HR has
        | curated is left exactly as they left it.
        */
        $channels->seedOpen($jobPosting);

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

        /*
        | The first time it goes live, and only then.
        |
        | posted_at is empty exactly once, which makes it the test for "this
        | is the moment it went live" -- so the channels are switched on then,
        | and a posting closed and re-published later keeps whatever ticks HR
        | has since made rather than having them all turned back on.
        */
        $goingLive = $validated['status'] === 'Published' && !$jobPosting->posted_at;

        if ($goingLive) {
            $jobPosting->posted_at = now();
        }

        if ($validated['status'] === 'Closed' && !$jobPosting->closed_at) {
            $jobPosting->closed_at = now();
        }

        $jobPosting->save();

        /* Where HR said they would advertise it is now switched on, so the
           links are live without anyone ticking the same boxes twice. */
        if ($goingLive) {
            (new PostingChannels())->enableOnPublish($jobPosting);
        }

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
