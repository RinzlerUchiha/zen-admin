<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\AcquiredApplicantIntake;
use App\Services\Recruitment\ApplicantBlacklist;
use App\Services\Recruitment\ApplicationRecommendationService;
use App\Services\Recruitment\ApplicantDocumentReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ApplicantIntakeController extends Controller
{
    public function index()
    {
        return view('pages.recruitment', [
            // Applicants asking for a new assessment access code.
            'accessRequests' => \Illuminate\Support\Facades\Gate::allows('applicant-assessments.issue-access')
                ? \App\Services\Recruitment\AssessmentAccessCodes::openRequests()
                : collect(),
            'main_link' => 'recruitment',
            'sub_link' => 'applicant-intake',
            'maincat' => 'applicant-intake',
            'page' => 'pages.recruitment.applicant-intake.index-content',
        ]);
    }

    private function baseQuery()
    {
        return DB::connection('applicant')->table('tblapp_applications as a')
            ->leftJoin('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->select(
                'a.id',
                'a.app_id',
                'a.request_position_id',
                'a.job_posting_id',
                'a.status',
                'a.applied_at',
                'a.closed_at',
                'a.closed_by',
                'a.closed_note',
                'p.app_fname',
                'p.app_mname',
                'p.app_lname',
                'p.app_email',
                'p.app_mobile',
                /* The applicant's own photo, for the list. Just the stored
                   file name -- the view builds the URL through the existing
                   file route, which is what enforces access to it. */
                'p.app_img'
            );
    }

    /**
     * Batched replacement for the old per-row enrich().
     *
     * Resolves posting_title and mr_no for the whole application set using
     * exactly two lookup queries (one per foreign connection) regardless of
     * how many applications are returned, instead of two queries per row.
     *
     * Output per application is identical to the previous implementation,
     * including the '—' fallback when the FK is null or points at a row
     * that no longer exists.
     */
    private function enrichAll($applications)
    {
        // 1 & 4. Distinct, non-null FK values. filter() drops null/0/'' —
        // those rows fall through to the '—' fallback exactly as before.
        $postingIds  = $applications->pluck('job_posting_id')->filter()->unique()->values();
        $positionIds = $applications->pluck('request_position_id')->filter()->unique()->values();

        // 2 & 3. One query on the zen-admin (default) connection, keyed by id.
        $postingTitles = $postingIds->isEmpty()
            ? collect()
            : DB::connection('mysql')->table('tbl_job_posting')
                ->whereIn('id', $postingIds)
                ->pluck('posting_title', 'id');

        // 5 & 6. One query on the portal_db (HireFlow) connection, keyed by position id.
        // Aliased in the SELECT so pluck() never has to guess at a qualified name.
        $positionMrNos = $positionIds->isEmpty()
            ? collect()
            : DB::connection('mysql')->table('tbl_manpower_request_position as pos')
                ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
                ->whereIn('pos.id', $positionIds)
                ->select('pos.id as position_id', 'r.mr_no')
                ->pluck('mr_no', 'position_id');

        // 7. Enrich from memory. No queries inside this loop.
        return $applications->map(function ($app) use ($postingTitles, $positionMrNos) {
            $app->applicant_name = trim(
                $app->app_fname . ' ' .
                ($app->app_mname ? substr($app->app_mname, 0, 1) . '. ' : '') .
                $app->app_lname
            );

            $app->posting_title = $postingTitles[$app->job_posting_id] ?? '—';
            $app->mr_no         = $positionMrNos[$app->request_position_id] ?? '—';

            return $app;
        });
    }

    /**
     * JSON data source for the Applicant Intake DataTable.
     * One entry per applicant (app_id), each carrying its own
     * applications array for the row.child() expansion.
     */
    /**
     * People who registered on the Applicants portal but have never applied.
     *
     * tblapp_persinfo IS the account (zen-applicants' User model points at it
     * with app_id as its key), so "registered, no application" is simply a
     * persinfo row with no application rows. Read over the existing applicant
     * connection — nothing is copied into portal_db.
     */
    private function accountsWithoutApplications()
    {
        return DB::connection('applicant')->table('tblapp_persinfo as p')
            ->leftJoin('tblapp_applications as a', 'a.app_id', '=', 'p.app_id')
            ->whereNull('a.id')
            ->select('p.app_id', 'p.app_fname', 'p.app_mname', 'p.app_lname',
                     'p.app_email', 'p.app_mobile', 'p.app_date', 'p.app_img')
            ->orderByDesc('p.app_date')
            ->get()
            ->map(fn ($p) => [
                'app_id' => $p->app_id,
                'applicant_name' => trim(preg_replace('/\s+/', ' ',
                    $p->app_fname . ' ' . ($p->app_mname ?? '') . ' ' . $p->app_lname)),
                'app_email' => $p->app_email,
                'app_mobile' => $p->app_mobile,
                'app_img' => $p->app_img,
                'application_count' => 0,
                'latest_applied_at' => null,
                'registered_at' => $p->app_date,
                'documents' => null,
                'applications' => [],
            ]);
    }

    public function data()
    {
        $applications = $this->enrichAll(
            $this->baseQuery()
                ->orderByDesc('a.applied_at')
                ->get()
        );

        // Document completeness per applicant, for HR allowed to see documents.
        // Two queries for the whole list, not per row.
        $documentSummaries = Gate::allows('applicant-documents.view')
            ? ApplicantDocumentReview::summaries($applications->pluck('app_id'))
            : null;

        /*
        | How many recommendations each application has produced, and which
        | applications a recommendation created.
        |
        | One query each for the whole set, not one per row — the same batching
        | the rest of this screen uses.
        |
        | The count decides whether HR is offered the Recommended closure: that
        | status means "ended because the applicant went forward for another
        | post", which is only true if a recommendation actually happened.
        */
        $recommendationLinks = ApplicationRecommendationService::linksFor($applications->pluck('id'));

        $grouped = $applications->groupBy('app_id')->map(function ($apps) use ($documentSummaries, $recommendationLinks) {
            $first = $apps->first();
            $registeredAt = null;
            $docs = $documentSummaries[$first->app_id] ?? null;

            return [
                'documents' => $docs ? [
                    'accepted' => $docs['required_accepted'],
                    'required' => $docs['required_total'],
                    'complete' => $docs['complete'],
                    'pending' => $docs['pending'],
                    'rejected' => $docs['rejected'],
                    'open_requests' => $docs['open_requests'],
                ] : null,
                'app_id' => $first->app_id,
                'applicant_name' => $first->applicant_name,
                'app_email' => $first->app_email,
                'app_mobile' => $first->app_mobile,
                'app_img' => $first->app_img,
                'application_count' => $apps->count(),
                'latest_applied_at' => $apps->max('applied_at'),
                'registered_at' => $registeredAt,
                'applications' => $apps->map(fn ($a) => [
                    'id' => $a->id,
                    'app_id' => $a->app_id,
                    'posting_title' => $a->posting_title,
                    'mr_no' => $a->mr_no,
                    'status' => $a->status,
                    'applied_at' => $a->applied_at,
                    // What the status means comes from config/applications.php
                    // through the model, so Intake and the portal agree (M3).
                    'is_closed' => (bool) (ApplicantApplication::statusDefinition($a->status)['closed'] ?? false),
                    'in_candidate_pool' => (bool) (ApplicantApplication::statusDefinition($a->status)['candidate_pool'] ?? false),
                    'closed_at' => $a->closed_at,
                    'closed_by' => $a->closed_by,
                    'closed_note' => $a->closed_note,
                    'reapply_on' => ApplicantApplication::cooldownEndsFor($a->status, $a->closed_at)?->toDateString(),

                    // Where this application came from, and what came of it.
                    // One hop each way: following it repeatedly walks a chain
                    // without holding the whole graph.
                    'recommended_from' => $recommendationLinks[$a->id]['from'] ?? null,
                    'recommended_to' => $recommendationLinks[$a->id]['to'] ?? [],
                    'recommended_count' => count($recommendationLinks[$a->id]['to'] ?? []),
                ])->values(),
            ];
        })->values();

        $rows = $grouped->concat($this->accountsWithoutApplications())
            ->sortByDesc(fn ($r) => $r['latest_applied_at'] ?? $r['registered_at'] ?? '')
            ->values();

        /*
        | Who is barred, for the whole list in one query.
        |
        | HR could only find this out by opening a profile, which is the wrong
        | way round: whether somebody may be given a new application at all is
        | the first thing about them, not the last. It is a flag rather than
        | the reason -- the reason is HR's own note and belongs on the profile
        | where the decision and its history are.
        |
        | Covers the registered-no-application accounts too: those are exactly
        | the people a bar is most likely to be sitting on.
        */
        $barred = ApplicantBlacklist::flagsFor(
            $rows->pluck('app_id')->map(fn ($id) => (int) $id)->all()
        );

        $rows = $rows->map(function ($row) use ($barred) {
            $row['blacklisted'] = isset($barred[(int) $row['app_id']]);

            /*
            | Where the photo lives, named by the route rather than by hand.
            |
            | The profile had this wrong once -- it built /file/app-img/{name}
            | as a string, which broke when the app moved under a subpath and
            | left a broken-image icon where the initials should have been.
            | route() is the one place that knows, and the route itself is
            | what enforces access to the file: nothing is copied anywhere and
            | no disk is bridged.
            |
            | Null when the applicant has no photo, which is most of them; the
            | view then shows their initials, as the profile does.
            */
            $row['photo_url'] = !empty($row['app_img'])
                /* missing=skip: a missing file has to 404 so the card's
                   onerror can fall back to the initials. Without it the route
                   answers 200 with a placeholder graphic, and every applicant
                   whose photo is not on this disk shows "FILE NOT AVAILABLE"
                   in a 42px circle. */
                ? route('applicant.file', ['src' => 'app-img', 'filename' => $row['app_img'], 'missing' => 'skip'])
                : null;

            unset($row['app_img']);

            return $row;
        })->values();

        return response()->json(['data' => $rows]);
    }

    /**
     * The Candidate Pool: applications that closed without the candidate
     * progressing and are worth revisiting.
     *
     * Uses the existing per-application definition from
     * config/applications.php via ApplicantApplication — there is deliberately
     * no second pool mechanism, and pool membership is never a property of the
     * applicant as a whole.
     */
    /**
     * The postings HR can record a walk-in against.
     *
     * Published only — the same rule the careers portal applies. Entering
     * someone by hand is not a reason to attach them to a closed posting.
     */
    public function openPostings()
    {
        $postings = DB::connection('mysql')->table('tbl_job_posting as jp')
            ->leftJoin('tbl_manpower_request_position as pos', 'pos.id', '=', 'jp.request_position_id')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
            ->where('jp.status', 'Published')
            ->orderBy('jp.posting_title')
            ->get(['jp.id', 'jp.posting_title', 'r.mr_no']);

        return response()->json(['data' => $postings]);
    }

    /**
     * Record an applicant HR acquired in person.
     *
     * Not for walk-ins: those register themselves at the office PC through
     * the Careers Portal, and the bookmarked link's ?src=walk_in is what
     * records the office as their origin. This is for people who are not at a
     * keyboard — met at a job fair, referred by someone.
     *
     * Source, outcome and current work stay separate: a job-fair hire is
     * source=job_fair AND outcome=Hired AND still missing documents.
     */
    /**
     * Do we already know this person?
     *
     * Asked BEFORE the form is filled in, not discovered after it is submitted.
     * Intake used to find the duplicate inside the save and report it in the
     * success message -- so HR typed out somebody's details, pressed Add, and
     * only then learned the account had existed all along and the form had been
     * largely pointless. Worse, a blacklisted applicant was refused at the very
     * end, after all the typing.
     *
     * Nothing here creates anything. It answers a question.
     */
    public function lookupApplicant(Request $request)
    {
        $validated = $request->validate([
            'app_email' => 'nullable|email|max:150',
            'app_mobile' => 'nullable|string|max:50',
        ]);

        $email = trim((string) ($validated['app_email'] ?? ''));
        $mobile = trim((string) ($validated['app_mobile'] ?? ''));

        if ($email === '' && $mobile === '') {
            return response()->json(['found' => false]);
        }

        $conn = DB::connection('applicant');

        $person = $conn->table('tblapp_persinfo')
            ->where(function ($q) use ($email, $mobile) {
                if ($email !== '') { $q->orWhere('app_email', $email); }
                if ($mobile !== '') { $q->orWhere('app_mobile', $mobile); }
            })
            ->first(['app_id', 'app_lname', 'app_fname', 'app_mname', 'app_email', 'app_mobile', 'app_date']);

        if (!$person) {
            return response()->json(['found' => false]);
        }

        /* Their applications, so HR can see whether this is a returning
           applicant or someone already in the middle of something. */
        $applications = $conn->table('tblapp_applications as a')
            ->leftJoin(DB::connection()->getDatabaseName() . '.tbl_job_posting as p', 'p.id', '=', 'a.job_posting_id')
            ->where('a.app_id', $person->app_id)
            ->orderByDesc('a.applied_at')
            ->limit(5)
            ->get(['a.id', 'a.status', 'a.applied_at', 'a.closed_at', 'p.posting_title']);

        $blacklist = ApplicantBlacklist::current((int) $person->app_id);

        return response()->json([
            'found' => true,
            'app_id' => (int) $person->app_id,
            'name' => trim($person->app_fname . ' ' . ($person->app_mname ? $person->app_mname . ' ' : '') . $person->app_lname),
            'app_lname' => $person->app_lname,
            'app_fname' => $person->app_fname,
            'app_mname' => $person->app_mname,
            'app_email' => $person->app_email,
            'app_mobile' => $person->app_mobile,
            'since' => $person->app_date,
            'blacklisted' => $blacklist !== null,
            'blacklist_reason' => $blacklist?->reason,
            'open_count' => $applications->whereNull('closed_at')->count(),
            'applications' => $applications->map(fn ($a) => [
                'posting_title' => $a->posting_title ?: '—',
                'status' => $a->status,
                'applied_at' => $a->applied_at,
                'closed' => $a->closed_at !== null,
            ])->values(),
            'profile_url' => route('applicant.show', ['id' => $person->app_id]),
        ]);
    }

    public function storeAcquired(Request $request)
    {
        $validated = $request->validate([
            'app_lname' => 'required|string|max:100',
            'app_fname' => 'required|string|max:100',
            'app_mname' => 'nullable|string|max:100',
            'app_email' => 'required_without:app_mobile|nullable|email|max:150',
            'app_mobile' => 'required_without:app_email|nullable|string|max:50',
            // HR sets this and tells the applicant, who signs in with it
            // later. Never generated here, never echoed back.
            'app_code' => 'required|string|min:8|max:100',
            'job_posting_id' => 'required|integer',
            'source_channel' => 'required|string|max:40',
            // Only meaningful for a job fair; the service ignores it otherwise.
            'outcome' => 'nullable|string|in:hired,not_selected,processing',
        ], [
            'app_email.required_without' => 'Give an email address or a mobile number — the applicant signs in with one of them.',
            'app_mobile.required_without' => 'Give an email address or a mobile number — the applicant signs in with one of them.',
            'app_code.min' => 'The sign-in code must be at least 8 characters.',
            'source_channel.required' => 'Choose where this applicant came from.',
        ]);

        $result = AcquiredApplicantIntake::create($validated, Auth::user()->Emp_No ?? null);

        return response()->json([
            'success' => true,
            'app_id' => $result['app_id'],
            'application_id' => $result['application_id'],
            'status' => $result['status'],
            'message' => $this->acquiredMessage($result),
        ]);
    }

    /** What HR needs to know happened, in one sentence. */
    private function acquiredMessage(array $result): string
    {
        $account = $result['reused_account']
            ? 'This person already had an account — the application was added to it.'
            : 'Account created.';

        return $account . ' ' . match ($result['status']) {
            // Said explicitly, because "hired" usually implies "finished" and
            // here it does not.
            'Hired' => 'Recorded as hired. The application stays open until '
                . 'requirements, any remaining exams and onboarding are done.',
            'Not Selected' => 'Closed as not selected and added to the Candidate Pool.',
            default => 'The application is open and follows the normal process.',
        };
    }

    /** The sources HR may record by hand, and whether each asks for an outcome. */
    public function intakeSources()
    {
        return response()->json(['data' => AcquiredApplicantIntake::sources()]);
    }

    public function candidatePool()
    {
        $pooled = ApplicantApplication::candidatePoolStatuses();

        /*
        | Somebody we are already talking to is not somebody to revisit.
        |
        | The pool answers one question: "who have we met before who might suit
        | this new post?" An applicant who withdrew from one posting but is
        | live on another is not that person -- they are mid-process, and
        | offering them up as a fresh prospect invites HR to recommend somebody
        | into a second pipeline while the first is still running.
        |
        | Pool membership stays a property of the APPLICATION, as designed.
        | This only hides those applications while their applicant still has an
        | open one; the moment that closes, they reappear with the record
        | intact. Nothing is deleted and no status changes.
        */
        $rows = $this->enrichAll(
            $this->baseQuery()
                ->whereIn('a.status', $pooled)
                /* The same condition the model's inCandidatePool() scope
                   applies, and the dashboard count now reads through. This
                   query joins and aliases, so it cannot use the scope
                   directly -- but it must not drift from it. */
                ->whereNotExists(function ($q) {
                    $q->from('tblapp_applications as pool_live')
                        ->whereColumn('pool_live.app_id', 'a.app_id')
                        ->whereNull('pool_live.closed_at');
                })
                ->orderByDesc('a.closed_at')
                ->get()
        );

        /*
        | Whether a bar sits on the person.
        |
        | The pool is where HR goes looking for somebody to put forward, so a
        | blacklist has to be visible BEFORE they pick one -- otherwise the
        | refusal arrives at the end, after the choosing. The service refuses
        | it either way; this only stops the wasted trip.
        */
        $barred = ApplicantBlacklist::flagsFor(
            $rows->pluck('app_id')->map(fn ($id) => (int) $id)->all()
        );

        $rows = $rows->map(fn ($a) => [
            /* The APPLICATION, which is what a recommendation is made from.
               app_id below is the person; the two are not interchangeable and
               the recommendation endpoints take this one. */
            'id' => $a->id,
            'app_id' => $a->app_id,
            'applicant_name' => $a->applicant_name,
            'app_email' => $a->app_email,
            'app_mobile' => $a->app_mobile,
            'posting_title' => $a->posting_title,
            'mr_no' => $a->mr_no,
            'status' => $a->status,
            'applied_at' => $a->applied_at,
            'closed_at' => $a->closed_at,
            'closed_note' => $a->closed_note,
            'reapply_on' => ApplicantApplication::cooldownEndsFor($a->status, $a->closed_at)?->toDateString(),
            'blacklisted' => isset($barred[(int) $a->app_id]),
        ])->values();

        return response()->json(['data' => $rows]);
    }
}