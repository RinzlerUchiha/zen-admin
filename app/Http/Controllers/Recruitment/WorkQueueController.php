<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\ApplicantDocumentReview;
use App\Models\Applicant\Offer;
use App\Services\Recruitment\InterviewWorkload;
use App\Services\Recruitment\OfferWorkflow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * The work queue: open applications that need HR to do something.
 *
 * This is a WORK QUEUE, not a stage, and it was called Screening -- which
 * made it look like the fourth step of the pipeline when it never was one.
 * Nothing here changes an application's status or its position in the
 * process; it only answers "what should I work on next?" by reading state
 * that already exists. Everything Phase 2.5 built (document review,
 * deadlines, assessment access) was reachable only by opening one applicant
 * at a time; this is the list that was missing.
 *
 * Each row carries the ONE reason it is here, chosen by urgency, so the queue
 * can be worked top to bottom instead of read.
 *
 * The reasons are deliberately unchanged by the rename. As later stages land,
 * this is where their actionable items join -- "evaluation outstanding",
 * "ready for offer" -- each one routing to the module that does the work.
 */
class WorkQueueController extends Controller
{
    /** A deadline this close is worth surfacing before it passes. */
    private const DUE_SOON_DAYS = 3;

    /*
    | Why an application is in the queue, most urgent first. The order is the
    | sort order and the priority: an application matching several reasons is
    | listed under the most pressing one, because HR can only do one thing
    | first. "ready" is last and is not a problem — it is the queue's useful
    | end state, and in Phase 3 it becomes "ready to schedule".
    */
    private const REASONS = [
        'deadline_passed'       => ['label' => 'Deadline passed',         'tone' => 'overdue'],
        'deadline_soon'         => ['label' => 'Deadline approaching',    'tone' => 'attention'],
        'documents_pending'     => ['label' => 'Documents to review',     'tone' => 'attention'],
        'access_requested'      => ['label' => 'Assessment code asked',   'tone' => 'attention'],
        'documents_incomplete'  => ['label' => 'Documents outstanding',   'tone' => 'progress'],

        /*
        | The interview reasons, added below the original five on purpose.
        |
        | All five above keep the precedence they already had, so an
        | application that raised "documents to review" yesterday raises it
        | today. What these replace is the FALLBACK: an application with
        | nothing wrong used to read "Ready to progress", which is true and
        | says nothing about what to do. Now it names the actual next act.
        |
        | Every one is derived from a record that exists -- a scheduled
        | interview, an assignment without an evaluation, a held round whose
        | step is still open. None is inferred from a status.
        */
        'interview_outcome'     => ['label' => 'Interview needs an outcome', 'tone' => 'overdue'],
        'evaluation_outstanding' => ['label' => 'Evaluation outstanding',  'tone' => 'attention'],
        'interview_decision'    => ['label' => 'Round awaiting decision',  'tone' => 'attention'],
        'interview_to_schedule' => ['label' => 'Interview to schedule',    'tone' => 'progress'],

        /*
        | The offer reasons.
        |
        | Each is a fact about an offer record, not a guess from a status:
        | an issued offer past the date it stood until, an offer accepted and
        | not yet turned into a hire, and an application whose steps are all
        | finished with no offer against it.
        */
        'offer_lapsed'          => ['label' => 'Offer has lapsed',        'tone' => 'overdue'],
        'offer_accepted'        => ['label' => 'Offer accepted',          'tone' => 'attention'],
        'offer_to_make'         => ['label' => 'Ready for an offer',      'tone' => 'progress'],

        'ready'                 => ['label' => 'Ready to progress',       'tone' => 'done'],
    ];

    public function index()
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'work-queue',
            'maincat' => 'work-queue',
            'page' => 'pages.recruitment.work-queue.index-content',
            'reasons' => self::REASONS,
        ]);
    }

    /**
     * The queue itself.
     *
     * Open applications only — closed ones are history and belong to the
     * Candidate Pool, not to a work list. "Open" comes from
     * config/applications.php through the model, so this and the applicant
     * portal can never disagree about what closed means.
     */
    public function data()
    {
        $openStatuses = array_values(array_diff(
            array_keys(config('applications.statuses', [])),
            ApplicantApplication::closedStatuses()
        ));

        $applications = DB::connection('applicant')->table('tblapp_applications as a')
            ->leftJoin('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->whereIn('a.status', $openStatuses)
            ->orderBy('a.applied_at')
            ->get([
                'a.id', 'a.app_id', 'a.status', 'a.applied_at',
                'a.request_position_id', 'a.job_posting_id',
                'p.app_fname', 'p.app_mname', 'p.app_lname', 'p.app_email',
            ]);

        if ($applications->isEmpty()) {
            return response()->json(['data' => [], 'counts' => $this->emptyCounts()]);
        }

        $appIds = $applications->pluck('app_id')->unique()->values();

        // Document state for the whole list in one call, not per row.
        // Null when this user may not see documents — the queue then simply
        // cannot raise document reasons for them, rather than leaking counts.
        $documents = Gate::allows('applicant-documents.view')
            ? ApplicantDocumentReview::summaries($appIds)
            : [];

        $accessRequests = DB::connection('applicant')->table('tblapp_assessment_access_requests')
            ->whereIn('app_id', $appIds)
            ->whereNull('resolved_at')
            ->get()
            ->groupBy('app_id');

        $titles = $this->postingTitles($applications);

        /*
        | What the interviews still need, per application.
        |
        | Derived by InterviewWorkload, which the Interviews workspace reads
        | too, so "needs scheduling" cannot mean one thing on that page and
        | another here. Null when this user may not see interviews: the queue
        | then simply cannot raise an interview reason for them, exactly as it
        | already behaves for documents.
        */
        $interviews = Gate::allows('applicant-interviews.view')
            ? $this->interviewActions()
            : [];

        /*
        | Offers outstanding, for whoever may act on them.
        |
        | Gated on the right that governs hiring decisions, exactly as the
        | document reasons are gated on seeing documents: a user who cannot
        | act on an offer is not told there is one waiting.
        */
        $offers = Gate::allows('applicant-applications.decide')
            ? $this->offerActions($applications)
            : [];

        $rows = $applications->map(function ($application) use ($documents, $accessRequests, $titles, $interviews, $offers) {
            $docs = $documents[$application->app_id] ?? null;
            $reason = $this->reasonFor(
                $application,
                $docs,
                $accessRequests,
                $offers[$application->id] ?? $interviews[$application->id] ?? null
            );

            return [
                'id' => $application->id,
                'app_id' => $application->app_id,
                'applicant_name' => trim(implode(' ', array_filter([
                    $application->app_fname, $application->app_mname, $application->app_lname,
                ]))) ?: 'Unnamed applicant',
                'app_email' => $application->app_email,
                'status' => $application->status,
                'applied_at' => $application->applied_at,
                'posting_title' => $titles['postings'][$application->job_posting_id] ?? null,
                'mr_no' => $titles['mrNos'][$application->request_position_id] ?? null,
                'reason' => $reason['key'],
                'reason_label' => self::REASONS[$reason['key']]['label'],
                'reason_tone' => self::REASONS[$reason['key']]['tone'],
                'detail' => $reason['detail'],
                'deadline_at' => $reason['deadline'],
                'documents' => $docs ? [
                    'accepted' => $docs['required_accepted'],
                    'required' => $docs['required_total'],
                    'pending' => $docs['pending'],
                    'open_requests' => $docs['open_requests'],
                ] : null,
            ];
        });

        // Most urgent first, then longest-waiting — the order HR should work.
        $order = array_flip(array_keys(self::REASONS));
        $rows = $rows->sortBy(
            fn ($row) => str_pad((string) $order[$row['reason']], 2, '0', STR_PAD_LEFT)
                . '|' . ($row['applied_at'] ?? '')
        )->values();

        return response()->json([
            'data' => $rows,
            'counts' => array_merge($this->emptyCounts(), $rows->countBy('reason')->all()),
        ]);
    }

    /** Every reason at zero, so the filter chips render before any rows load. */
    private function emptyCounts(): array
    {
        return array_fill_keys(array_keys(self::REASONS), 0);
    }

    /**
     * The single most pressing reason this application is in the queue.
     *
     * Returns 'ready' when nothing is outstanding — that is not an error
     * state, it is the application waiting for HR to move it forward.
     */
    private function reasonFor($application, ?array $docs, $accessRequests, ?array $interview = null): array
    {
        // An open document process with a deadline outranks everything: it is
        // the only thing here that closes an application on its own.
        $process = collect($docs['processes'] ?? [])->firstWhere('outcome_at', null);

        if ($process && !empty($process->deadline_at)) {
            $deadline = Carbon::parse($process->deadline_at);

            if ($deadline->isPast()) {
                return [
                    'key' => 'deadline_passed',
                    'detail' => 'Document deadline passed ' . $deadline->diffForHumans(),
                    'deadline' => $deadline->toDateTimeString(),
                ];
            }

            if ($deadline->lessThanOrEqualTo(now()->addDays(self::DUE_SOON_DAYS))) {
                return [
                    'key' => 'deadline_soon',
                    'detail' => 'Documents due ' . $deadline->diffForHumans(),
                    'deadline' => $deadline->toDateTimeString(),
                ];
            }
        }

        if ($docs && $docs['pending'] > 0) {
            return [
                'key' => 'documents_pending',
                'detail' => $docs['pending'] . ' ' . \Illuminate\Support\Str::plural('document', $docs['pending']) . ' awaiting review',
                'deadline' => null,
            ];
        }

        $asked = $accessRequests->get($application->app_id);

        if ($asked && $asked->isNotEmpty()) {
            return [
                'key' => 'access_requested',
                'detail' => $asked->count() === 1
                    ? 'Asked for an assessment code'
                    : 'Asked for ' . $asked->count() . ' assessment codes',
                'deadline' => null,
            ];
        }

        if ($docs && !$docs['complete']) {
            $missing = $docs['required_total'] - $docs['required_accepted'];

            return [
                'key' => 'documents_incomplete',
                'detail' => $missing . ' required ' . \Illuminate\Support\Str::plural('document', $missing)
                    . ' not yet accepted' . ($docs['open_requests'] ? ' · request sent' : ''),
                'deadline' => null,
            ];
        }

        /*
        | Nothing is wrong, so say what happens next.
        |
        | This sits where "Ready to progress" used to be the only answer. The
        | five reasons above are untouched: an application that matched one of
        | them still matches it and still reads the same. Only the fallback
        | has become more specific.
        |
        | One reason per application, the most pressing first, so the same
        | outstanding act is never listed twice.
        */
        if ($interview) {
            return $interview;
        }

        return [
            'key' => 'ready',
            'detail' => $docs ? 'Documents complete' : 'Nothing outstanding',
            'deadline' => null,
        ];
    }

    /**
     * One interview reason per application, most pressing first.
     *
     * Read from InterviewWorkload so the rule lives in one place. The order
     * here is the order HR should act: an interview whose time has passed and
     * that nobody has said happened yet blocks everything behind it; a round
     * nobody has written up blocks the decision; a decision blocks the next
     * round; and a round with no date at all is the ordinary next task.
     *
     * @return array<int, array{key: string, detail: string, deadline: ?string}>
     *         keyed by application id
     */
    /**
     * One offer reason per application, most pressing first.
     *
     * Read from the offer records themselves. "Ready for an offer" is the
     * eligibility OfferWorkflow already defines -- every recruitment step
     * settled and no offer in play -- so the queue and the Offer page can
     * never disagree about who is ready.
     *
     * An offer reason outranks an interview one because an application with
     * a live offer has, by that same definition, no interview steps left
     * outstanding; the two cannot both be true of the same application.
     *
     * @return array<int, array{key: string, detail: string, deadline: ?string}>
     */
    private function offerActions($applications): array
    {
        $byApplication = [];

        $offers = Offer::whereIn('application_id', $applications->pluck('id'))->get();

        /* Nothing outstanding on a settled offer except an accepted one,
           which is waiting on the hire being recorded. */
        foreach ($offers as $offer) {
            if ($offer->status === Offer::ACCEPTED) {
                $byApplication[(int) $offer->application_id] = [
                    'key' => 'offer_accepted',
                    'detail' => 'Accepted ' . ($offer->responded_at?->diffForHumans() ?? '')
                        . ' — record the hire on the application when it happens',
                    'deadline' => null,
                ];

                continue;
            }

            if ($offer->hasLapsed()) {
                $byApplication[(int) $offer->application_id] = [
                    'key' => 'offer_lapsed',
                    'detail' => 'Issued offer stood until ' . $offer->expires_at->toFormattedDateString()
                        . ' and has had no answer',
                    'deadline' => $offer->expires_at->toDateString(),
                ];
            }
        }

        /* Ready, and nothing raised yet. Asked of OfferWorkflow so there is
           one definition of ready. */
        foreach ($applications as $application) {
            if (isset($byApplication[(int) $application->id])) {
                continue;
            }

            if (OfferWorkflow::eligibility((int) $application->id)['eligible']) {
                $byApplication[(int) $application->id] = [
                    'key' => 'offer_to_make',
                    'detail' => 'Every recruitment step is settled — no offer raised yet',
                    'deadline' => null,
                ];
            }
        }

        return $byApplication;
    }

    private function interviewActions(): array
    {
        $work = InterviewWorkload::collect();
        $byApplication = [];

        /* Most pressing last, so each assignment overwrites a lesser one and
           the most pressing is what remains. */
        foreach ($work['needs_scheduling'] as $row) {
            $byApplication[$row['application_id']] = [
                'key' => 'interview_to_schedule',
                'detail' => $row['round'] . ' has no date yet',
                'deadline' => null,
            ];
        }

        foreach ($work['evaluations_outstanding'] as $row) {
            $byApplication[$row['application_id']] = $row['kind'] === 'decision'
                ? [
                    'key' => 'interview_decision',
                    'detail' => $row['round'] . ' is written up and waiting on a decision',
                    'deadline' => null,
                ]
                : [
                    'key' => 'evaluation_outstanding',
                    'detail' => $row['round'] . ' — waiting on ' . implode(', ', $row['awaiting']),
                    'deadline' => null,
                ];
        }

        /* Only the ones whose time has passed. A scheduled interview that has
           not happened yet is not an outstanding act; it is a diary entry, and
           the Interviews page is where diaries are read. */
        foreach ($work['due'] as $row) {
            if (empty($row['overdue'])) {
                continue;
            }

            $byApplication[$row['application_id']] = [
                'key' => 'interview_outcome',
                'detail' => $row['round'] . ' was ' . Carbon::parse($row['scheduled_at'])->diffForHumans()
                    . ' — record whether it happened',
                'deadline' => $row['scheduled_at'],
            ];
        }

        return $byApplication;
    }

    /**
     * Posting titles and MR numbers for the listed applications.
     *
     * Two queries against portal_db, joined in PHP — tbl_job_posting and
     * tbl_manpower_request live on another connection, so this follows the
     * same pattern as Applicant Intake rather than attempting a cross-database
     * join, which this codebase never does.
     */
    private function postingTitles($applications): array
    {
        $postingIds = $applications->pluck('job_posting_id')->filter()->unique()->values();
        $positionIds = $applications->pluck('request_position_id')->filter()->unique()->values();

        return [
            'postings' => $postingIds->isEmpty()
                ? []
                : DB::connection('mysql')->table('tbl_job_posting')
                    ->whereIn('id', $postingIds)
                    ->pluck('posting_title', 'id')->all(),

            'mrNos' => $positionIds->isEmpty()
                ? []
                : DB::connection('mysql')->table('tbl_manpower_request_position as pos')
                    ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
                    ->whereIn('pos.id', $positionIds)
                    ->select('pos.id as position_id', 'r.mr_no')
                    ->pluck('mr_no', 'position_id')->all(),
        ];
    }
}
