<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\EligibilityCheck;
use App\Services\Recruitment\QualificationMatcher;
use App\Services\Recruitment\ShortlistDecision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Candidate matching and shortlisting, for one posting.
 *
 * Reads the posting's job specification, reads what each applicant told us,
 * and shows every requirement as met, not met or unknown with the evidence
 * beside it. Then HR decides.
 *
 * WHAT THIS CONTROLLER WILL NOT DO
 *
 * It will not rank by a score, because there is no authoritative weighting for
 * these criteria and any number would be invented. It will not reject anybody:
 * an unmet requirement is surfaced, never acted on. And it will not treat a
 * missing answer as a failure — an unknown names the field we do not hold and
 * nothing more.
 */
class MatchingController extends Controller
{
    /**
     * Which posting to match against.
     *
     * Matching is always about one posting, so a sidebar entry needs somewhere
     * to land. This is that landing place and nothing more: it lists the open
     * postings with how many applications are waiting to be screened, and
     * every row goes to the SAME matching screen below. No second matcher, no
     * second set of rules.
     */
    public function postings()
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'matching',
            'maincat' => 'matching',
            'page' => 'pages.recruitment.matching.postings-content',
        ]);
    }

    /** The postings worth matching against, with how much is waiting. */
    public function postingsData()
    {
        $postings = DB::table('tbl_job_posting')
            ->whereIn('status', ['Published', 'Draft'])
            ->orderByDesc('posted_at')
            ->get(['id', 'posting_title', 'status', 'jobspec_id', 'posted_at']);

        if ($postings->isEmpty()) {
            return response()->json(['postings' => []]);
        }

        /* Open applications per posting, and how many of them nobody has
           screened yet -- the number that makes this list worth reading. */
        $open = ApplicantApplication::open()
            ->whereIn('job_posting_id', $postings->pluck('id'))
            ->get(['id', 'job_posting_id']);

        $decided = DB::connection('applicant')->table('tblapp_eligibility_decisions')
            ->whereIn('application_id', $open->pluck('id'))
            ->whereNull('superseded_at')
            ->pluck('application_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $byPosting = $open->groupBy('job_posting_id');

        return response()->json([
            'postings' => $postings->map(function ($posting) use ($byPosting, $decided) {
                $applications = $byPosting->get($posting->id, collect());

                return [
                    'id' => (int) $posting->id,
                    'posting_title' => $posting->posting_title,
                    'status' => $posting->status,
                    /* Said plainly: without a specification there is nothing
                       to match against, and the screen will say so. */
                    'has_specification' => (bool) $posting->jobspec_id,
                    'applications' => $applications->count(),
                    'unscreened' => $applications
                        ->reject(fn ($a) => in_array((int) $a->id, $decided, true))
                        ->count(),
                ];
            })->values(),
        ]);
    }

    public function index($posting)
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'matching',
            'maincat' => 'matching',
            'page' => 'pages.recruitment.matching.index-content',
            'postingId' => (int) $posting,
        ]);
    }

    /**
     * Every open application to this posting, matched and ranked.
     *
     * The ranking rules travel with the data so the screen can print them.
     * A ranking whose rules are not on the page is the opaque scoring this
     * design exists to avoid.
     */
    public function data($posting)
    {
        $posting = (int) $posting;

        $applications = ApplicantApplication::open()
            ->where('job_posting_id', $posting)
            ->get(['id', 'app_id', 'status', 'applied_at']);

        $ranked = QualificationMatcher::rank($applications->pluck('id')->all());
        $context = $this->contextFor($applications->pluck('id')->map(fn ($id) => (int) $id)->all());

        $rows = [];

        foreach ($ranked as $position => $match) {
            $id = (int) ($match['application_id'] ?? 0);

            if ($id === 0) {
                continue;
            }

            $decision = ShortlistDecision::current($id);

            $rows[] = ($context[$id] ?? []) + [
                'rank' => $position + 1,
                'application_id' => $id,
                'match' => $match,
                'eligibility' => EligibilityCheck::forApplication($id),
                'decision' => $decision ? [
                    'decision' => $decision->decision,
                    'overrode' => (bool) $decision->overrode,
                    'reason' => $decision->reason,
                    'decided_by' => $decision->decided_by,
                    'decided_at' => $decision->decided_at?->format('M d, Y h:i A'),
                ] : null,
            ];
        }

        return response()->json([
            'posting' => DB::table('tbl_job_posting')->where('id', $posting)
                ->first(['id', 'posting_title', 'status', 'jobspec_id']),
            'candidates' => $rows,
            'ranking_rules' => QualificationMatcher::rankingRules(),

            /*
            | Said on the page, not buried here: the matcher reports what the
            | records show, and an unknown is a gap in what we were told
            | rather than a fact about the applicant.
            */
            'disclaimer' => 'This is a recommendation from the records held. Unknown means the '
                . 'information needed was not recorded — it is never counted against an applicant. '
                . 'HR decides who is shortlisted.',
        ]);
    }

    /** One candidate in full, for the panel beside the list. */
    public function show($posting, $application)
    {
        $application = (int) $application;

        return response()->json([
            'match' => QualificationMatcher::forApplication($application),
            'eligibility' => EligibilityCheck::forApplication($application),
            'ranking_rules' => QualificationMatcher::rankingRules(),
        ]);
    }

    /**
     * Record HR's shortlisting decision.
     *
     * The service requires a reason when the decision goes against the
     * recommendation, and stores the recommendation as it stood either way.
     */
    public function decide(Request $request, $posting, $application)
    {
        $validated = $request->validate([
            'decision' => 'required|string|in:' . ShortlistDecision::SHORTLISTED
                . ',' . ShortlistDecision::NOT_SHORTLISTED,
            'reason' => 'nullable|string|max:500',
        ]);

        $record = ShortlistDecision::record(
            (int) $application,
            $validated['decision'],
            $validated['reason'] ?? null,
            Auth::user()->Emp_No ?? null
        );

        return response()->json([
            'success' => true,
            'decision' => $record->decision,
            'overrode' => (bool) $record->overrode,
            'message' => $record->decision === ShortlistDecision::SHORTLISTED
                ? 'Shortlisted. Their workflow is unchanged — arrange an interview when ready.'
                : 'Recorded as not shortlisted. The application is closed and the candidate '
                    . 'stays in the Candidate Pool, with no cooldown.',
        ]);
    }

    /** The applicant behind each application, in one query. */
    private function contextFor(array $applicationIds): array
    {
        if (empty($applicationIds)) {
            return [];
        }

        $rows = DB::connection('applicant')->table('tblapp_applications as a')
            ->leftJoin('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->whereIn('a.id', $applicationIds)
            ->get([
                'a.id', 'a.app_id', 'a.status', 'a.applied_at',
                'p.app_fname', 'p.app_mname', 'p.app_lname', 'p.app_email',
            ]);

        $context = [];

        foreach ($rows as $row) {
            $context[(int) $row->id] = [
                'app_id' => (int) $row->app_id,
                'applicant_name' => trim(implode(' ', array_filter([
                    $row->app_fname, $row->app_mname, $row->app_lname,
                ]))) ?: 'Unnamed applicant',
                'app_email' => $row->app_email,
                'status' => $row->status,
                'applied_at' => $row->applied_at,
            ];
        }

        return $context;
    }
}
