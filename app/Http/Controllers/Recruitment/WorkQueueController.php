<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationStep;
use App\Services\Recruitment\ApplicantDocumentReview;
use App\Models\Applicant\Offer;
use App\Services\Recruitment\AssessmentEligibility;
use App\Services\Recruitment\AssessmentProgress;
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
        | Screening, which the process puts between applying and the Initial
        | Interview and which nothing was asking for.
        |
        | The queue went from "documents accepted" straight to "interview to
        | schedule", so in practice interviews were arranged for applicants
        | nobody had screened. This raises the step; it does not perform it,
        | and it never rejects anybody -- HR reads the match and decides, as
        | they already could.
        |
        | Ranked above the interview reasons on purpose: screening comes
        | first, and a queue that says "schedule the interview" while the
        | screening is undone is telling HR to skip a step.
        */
        'screening_due'         => ['label' => 'Screen and shortlist',    'tone' => 'attention'],

        /*
        | The two moments the process needs HR to act on and nothing was
        | telling them about.
        |
        | The flow is: the Initial Interview is passed, so HR gives the
        | applicant their code; they sit the assessments; HR reads the results
        | and decides. The middle part the applicant drives, but both ends are
        | HR's -- and until now the only assessment prompt here was the
        | applicant ASKING for a code, which is the exception rather than the
        | normal path.
        |
        | Placed AFTER the original five, which keep their precedence: an
        | application that raised a document or deadline reason yesterday must
        | raise the same one today.
        */
        'assessment_code_due'   => ['label' => 'Exams open — send the code', 'tone' => 'attention'],
        'assessment_decision'   => ['label' => 'Assessments done — decide', 'tone' => 'attention'],

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
        /* A finished round whose pass/fail HR never recorded. It outranks the
           other interview reasons because it is the one that silently holds up
           everything downstream: no outcome, no assessments. */
        'outcome_unrecorded'    => ['label' => 'Outcome not recorded',      'tone' => 'overdue'],
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
        'offer_overdue'         => ['label' => 'Offer overdue',           'tone' => 'overdue'],
        /* The HR Head sent it back. HR's work, and nobody else's -- which is
           exactly why an offer awaiting approval raises nothing here. */
        'offer_revision'        => ['label' => 'Offer needs revising',    'tone' => 'overdue'],
        'offer_accepted'        => ['label' => 'Offer accepted',          'tone' => 'attention'],
        'offer_contract'        => ['label' => 'Contract to record',      'tone' => 'attention'],
        'offer_to_send'         => ['label' => 'Approved — ready to send', 'tone' => 'progress'],
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

        /*
        | Applications waiting to be screened.
        |
        | Gated on the right that records the decision: a user who cannot
        | shortlist is not told shortlisting is due.
        */
        $screening = Gate::allows('applicant-applications.decide')
            ? $this->screeningActions($applications, $documents)
            : [];

        /*
        | Where each applicant stands on the assessments.
        |
        | Gated on the right that issues codes, for the same reason the
        | document reasons are gated on seeing documents: a user who cannot act
        | on it is not told there is something waiting.
        */
        $assessments = Gate::allows('applicant-assessments.issue-access')
            ? $this->assessmentActions($applications)
            : [];

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

        $rows = $applications->map(function ($application) use ($documents, $accessRequests, $titles, $interviews, $offers, $assessments, $screening) {
            $docs = $documents[$application->app_id] ?? null;
            $reason = $this->reasonFor(
                $application,
                $docs,
                $accessRequests,
                $offers[$application->id]
                    ?? $interviews[$application->id]
                    ?? $assessments[$application->id]
                    ?? null,
                $screening[$application->id] ?? null
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
                /* Carried so the screening row can link to that posting's
                   own matching screen. */
                'job_posting_id' => $application->job_posting_id ? (int) $application->job_posting_id : null,
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
    private function reasonFor($application, ?array $docs, $accessRequests, ?array $interview = null, ?array $screening = null): array
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
        /*
        | Screening before interviews -- but never before an offer.
        |
        | The process is: apply, screen, then interview. Checked before the
        | interview row so a queue entry never says "schedule the Initial
        | Interview" for somebody whose match nobody has looked at. An offer
        | reason always wins: an application that has reached an offer is long
        | past screening, and the offer is the live act.
        */
        $isOffer = $interview && str_starts_with($interview['key'], 'offer_');

        if ($screening && !$isOffer) {
            return $screening;
        }

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

        /*
        | What each live offer is waiting on, and whether it is HR waiting.
        |
        | pending_approval raises NOTHING. This list is what HR must do, and
        | an offer with the HR Head is waiting on somebody else -- putting it
        | here would turn the queue into a list of things to feel anxious
        | about rather than a list of work.
        |
        | A closed offer raises nothing either: rejected, declined, withdrawn
        | and expired are over.
        */
        foreach ($offers as $offer) {
            $row = match (true) {
                /* Sent back by the HR Head: HR has to change it and resubmit. */
                $offer->status === Offer::REVISION_REQUESTED => [
                    'key' => 'offer_revision',
                    'detail' => 'The HR Head asked for changes'
                        . ($offer->revision_reason ? ': ' . $offer->revision_reason : '')
                        . ' — revise and resubmit',
                    'deadline' => null,
                ],

                /* Past its date with no answer. Still sent, still awaiting a
                   reply: HR extends the deadline or ends it, and nothing here
                   does either on its own. */
                $offer->isOverdue() => [
                    'key' => 'offer_overdue',
                    'detail' => 'No answer by ' . $offer->response_deadline->toFormattedDateString()
                        . ' — extend the deadline or end the offer',
                    'deadline' => $offer->response_deadline->toDateString(),
                ],

                $offer->status === Offer::APPROVED => [
                    'key' => 'offer_to_send',
                    'detail' => 'Approved by the HR Head — ready to send to the applicant',
                    'deadline' => null,
                ],

                $offer->status === Offer::ACCEPTED => [
                    'key' => 'offer_accepted',
                    'detail' => 'Accepted ' . ($offer->responded_at?->diffForHumans() ?? '')
                        . ' — record the signed contract, then the hire',
                    'deadline' => null,
                ],

                $offer->status === Offer::CONTRACT_RECEIVED => [
                    'key' => 'offer_contract',
                    'detail' => 'Contract received — record the hire on the application',
                    'deadline' => null,
                ],

                default => null,
            };

            if ($row !== null) {
                $byApplication[(int) $offer->application_id] = $row;
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

    /**
     * Applications whose documents are in and whose screening is not done.
     *
     * WHAT THIS IS NOT
     *
     * It is not a gate and not an approval. Nothing here rejects anybody or
     * prevents an interview being arranged -- the previous design deliberately
     * left scheduling ungated and that is unchanged. It raises a step the
     * process asks for and the queue was silent about.
     *
     * WHEN IT STOPS ASKING
     *
     * As soon as a shortlisting decision exists, or as soon as an interview
     * has actually been held. The second condition matters: an application
     * that reached its Initial Interview has plainly been screened in
     * practice, and nagging about it afterwards would be noise.
     *
     * @return array<int,array{key:string,detail:string,deadline:null}>
     */
    private function screeningActions($applications, array $documents): array
    {
        $byApplication = [];
        $ids = $applications->pluck('id');

        if ($ids->isEmpty()) {
            return [];
        }

        $c = DB::connection('applicant');

        /* Already decided -- shortlisted or not. */
        $decided = $c->table('tblapp_eligibility_decisions')
            ->whereIn('application_id', $ids)
            ->whereNull('superseded_at')
            ->pluck('application_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
        | Already past it.
        |
        | Any completed step means the process has moved on -- an application
        | at the offer stage has plainly been screened, whatever this table
        | says. A held interview counts too, for the gap between the interview
        | happening and HR completing the round.
        |
        | Without both of these the prompt outranked the offer reasons and
        | buried them, which is the opposite of the problem it exists to fix.
        */
        $movedOn = $c->table('tblapp_application_steps')
            ->whereIn('application_id', $ids)
            ->where('state', ApplicationStep::STATE_COMPLETED)
            ->pluck('application_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $interviewed = $c->table('tblapp_interviews as i')
            ->join('tblapp_application_steps as s', 's.id', '=', 'i.step_id')
            ->whereIn('s.application_id', $ids)
            ->where('i.attendance', 'held')
            ->pluck('s.application_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $interviewed = array_unique(array_merge($interviewed, $movedOn));

        foreach ($applications as $application) {
            $id = (int) $application->id;

            if (in_array($id, $decided, true) || in_array($id, $interviewed, true)) {
                continue;
            }

            /* Documents first. The document reasons already outrank this, so
               raising it before they are in would just be a second voice
               saying the same thing. */
            $docs = $documents[$application->app_id] ?? null;

            if ($docs !== null && !($docs['complete'] ?? false)) {
                continue;
            }

            $byApplication[$id] = [
                'key' => 'screening_due',
                'detail' => $application->job_posting_id
                    ? 'Documents are in — check how they match this posting and shortlist'
                    : 'Documents are in — screen this application',
                'deadline' => null,
            ];
        }

        return $byApplication;
    }

    /**
     * The two assessment moments that are HR's to act on.
     *
     * CODE DUE  the Initial Interview is passed, so the exams are open to this
     *           applicant -- but nobody has issued them a code. The applicant
     *           cannot start without one and has no way to know they may.
     *
     * DECIDE    every assessment is in and HR has not recorded what they
     *           concluded. Nothing else surfaces this, so an applicant can sit
     *           finished indefinitely waiting on a decision nobody knows is
     *           due.
     *
     * Read from records that exist -- a passed step, an unlock row, a result
     * row, a decision row -- never inferred from a status.
     *
     * @return array<int,array{key:string,detail:string,deadline:null}>
     */
    private function assessmentActions($applications): array
    {
        $byApplication = [];
        $appIds = $applications->pluck('app_id')->unique()->values();

        if ($appIds->isEmpty()) {
            return [];
        }

        $c = DB::connection('applicant');

        /* Who has a live unlock already. Revoked ones do not count: the
           applicant cannot use them. */
        $unlocked = $c->table('tblapp_assessment_access')
            ->whereIn('app_id', $appIds)
            ->whereNull('revoked_at')
            ->pluck('app_id')
            ->unique()
            ->all();

        /* Decisions already recorded, so a decided application stops asking. */
        $decided = $c->table('tblapp_assessment_decisions')
            ->whereIn('application_id', $applications->pluck('id'))
            ->whereNull('superseded_at')
            ->pluck('application_id')
            ->all();

        foreach ($applications as $application) {
            /* Only applicants the gate has actually opened for. */
            if (!AssessmentEligibility::prerequisiteMet((int) $application->app_id)) {
                continue;
            }

            if (in_array((int) $application->id, array_map('intval', $decided), true)) {
                continue;
            }

            $progress = AssessmentProgress::forApplication((int) $application->id);
            $counts = $progress['counts'] ?? [];

            /* Everything in, nothing decided: the decision is the hold-up. */
            if (($progress['all_taken'] ?? false) && ($counts['total'] ?? 0) > 0) {
                $byApplication[(int) $application->id] = [
                    'key' => 'assessment_decision',
                    'detail' => 'All ' . $counts['total'] . ' assessments are in — read the results '
                        . 'and record whether they proceed',
                    'deadline' => null,
                ];

                continue;
            }

            /* Open to them, but they have nothing to open them with. */
            if (!in_array((int) $application->app_id, array_map('intval', $unlocked), true)) {
                $byApplication[(int) $application->id] = [
                    'key' => 'assessment_code_due',
                    'detail' => 'Passed the Initial Interview — send them an access code so they '
                        . 'can sit the ' . (($counts['outstanding'] ?? 0) ?: 'remaining')
                        . ' assessments',
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

        /*
        | Finished rounds with no recorded outcome.
        |
        | Assigned last, so it wins: of everything on this list it is the only
        | one that leaves an applicant stuck without saying so anywhere else.
        | A round completed before outcomes were recorded lands here, which is
        | the point -- those decisions are outstanding, not absent.
        */
        foreach ($this->awaitingOutcome() as $applicationId => $label) {
            $byApplication[$applicationId] = [
                'key' => 'outcome_unrecorded',
                'detail' => $label . ' is complete — record whether it was passed',
                'deadline' => null,
            ];
        }

        return $byApplication;
    }

    /**
     * Completed rounds on open applications with no outcome recorded.
     *
     * Interview rounds only: those are the ones the assessment gate reads, and
     * a document step has no pass or fail to settle.
     *
     * @return array<int,string> application id => the round's own label
     */
    private function awaitingOutcome(): array
    {
        $rows = DB::connection('applicant')->table('tblapp_application_steps as s')
            ->join('tblapp_applications as a', 'a.id', '=', 's.application_id')
            ->whereNull('a.closed_at')
            ->where('s.kind', 'interview')
            ->where('s.state', ApplicationStep::STATE_COMPLETED)
            ->whereNull('s.outcome')
            ->orderBy('s.position')
            ->get(['s.application_id', 's.label']);

        $byApplication = [];

        foreach ($rows as $row) {
            /* The earliest unsettled round is the one holding things up, and
               orderBy position means it is the first one seen. */
            $byApplication[(int) $row->application_id] ??= (string) $row->label;
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
