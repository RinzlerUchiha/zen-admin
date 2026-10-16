<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant\Offer;
use App\Services\Recruitment\InterviewWorkflow;
use App\Services\Recruitment\OfferWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The HR Head's three decisions, reached from Zen-Hub.
 *
 * Zen-Hub holds no applicant data and no offer rules. It authenticates the
 * employee against its own session, gates its page on their role, and calls
 * these endpoints over the signed bridge. Everything about what an offer is,
 * what may happen to it and what gets recorded lives in OfferWorkflow, shared
 * with HR's own screens.
 *
 * WHAT IS RESOLVED RATHER THAN TRUSTED
 *
 * Every endpoint finds the offer among those AWAITING APPROVAL, not by id
 * alone. An id typed into a request therefore reaches nothing else: not a
 * draft, not one already approved, and not one sent back for revision and
 * still with HR.
 *
 * THREE ANSWERS
 *
 * Approve, decline, and send back for revision. The third exists because
 * without it an approver who wants a salary corrected would have to decline —
 * which is terminal and loses the offer — or approve something they believe is
 * wrong.
 *
 * NO BUDGET VERDICT
 *
 * There is no authoritative salary band or budget source in this system, so
 * nothing here computes a variance or says whether terms are "within budget".
 * The approver is shown the proposed terms and the recruitment evidence, and
 * decides.
 */
class ApproverApiController extends Controller
{
    /** Who the bridge says is asking. Never read from the body or the route. */
    private function empno(Request $request): string
    {
        return (string) $request->attributes->get('hireflow_empno', '');
    }

    /** Confirms to Zen-Hub who it is acting for. */
    public function me(Request $request)
    {
        return response()->json([
            'empno' => $this->empno($request),
            'may_approve' => true,
            'awaiting' => OfferWorkflow::awaitingApproval()->count(),
        ]);
    }

    /**
     * Offers awaiting approval.
     *
     * Only pending_approval. An offer sent back for revision is HR's work and
     * must not appear here: nobody is waiting on the HR Head for it.
     */
    public function index(Request $request)
    {
        $offers = OfferWorkflow::awaitingApproval()->get();
        $context = $this->contextFor($offers->pluck('application_id')->map(fn ($id) => (int) $id)->all());

        return response()->json([
            'offers' => $offers->map(fn ($offer) => $this->summary($offer, $context))->values(),
        ]);
    }

    /**
     * One offer, with what was decided before it.
     *
     * The rounds and their outcomes are read from the interview engine rather
     * than copied, so the approver sees what HR sees.
     */
    public function show(Request $request, $offer)
    {
        $record = $this->pending($offer);

        if (!$record) {
            return response()->json(['message' => 'That offer is not awaiting approval.'], 404);
        }

        $context = $this->contextFor([(int) $record->application_id]);

        return response()->json([
            'offer' => $this->summary($record, $context),

            /* The evidence: how each round concluded. */
            'rounds' => collect(InterviewWorkflow::summary((int) $record->application_id))
                ->map(fn ($round) => [
                    'label' => $round['display_label'],
                    'state' => $round['state'],
                    'outcome' => $round['outcome'] ?? null,
                    'result' => $round['result'] ?? null,
                ])->values(),

            /* Including every earlier round of revision, so an approver can
               see what they asked for last time and whether it was done. */
            'events' => $record->events->map(fn ($e) => [
                'from' => $e->from_status,
                'to' => $e->to_status,
                'reason' => $e->reason,
                'actor' => $e->actor_empno,
                'at' => $e->occurred_at?->format('M d, Y h:i A'),
            ])->values(),
        ]);
    }

    /** Approve the terms. The offer still has to be sent, by HR. */
    public function approve(Request $request, $offer)
    {
        $record = $this->pending($offer);

        if (!$record) {
            return response()->json(['message' => 'That offer is not awaiting approval.'], 404);
        }

        $updated = OfferWorkflow::transition(
            (int) $record->id,
            Offer::APPROVED,
            $this->empno($request),
            $request->input('note')
        );

        return response()->json([
            'success' => true,
            'status' => $updated->status,
            'message' => 'Approved. HR will send it to the applicant.',
        ]);
    }

    /**
     * Refuse the terms outright. Terminal, and the reason is the record.
     *
     * Not the same event as the applicant declining: that is recorded against
     * the offer separately, by HR, and the two are never conflated.
     */
    public function decline(Request $request, $offer)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $record = $this->pending($offer);

        if (!$record) {
            return response()->json(['message' => 'That offer is not awaiting approval.'], 404);
        }

        $updated = OfferWorkflow::transition(
            (int) $record->id,
            Offer::REJECTED,
            $this->empno($request),
            $validated['reason']
        );

        return response()->json([
            'success' => true,
            'status' => $updated->status,
            'message' => 'Declined. The reason is kept with the offer and HR can raise another.',
        ]);
    }

    /**
     * Send it back for HR to change and resubmit.
     *
     * The offer stays live — it is still this application's offer — but it
     * leaves the approver's queue entirely until HR resubmits it.
     */
    public function requestRevision(Request $request, $offer)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $record = $this->pending($offer);

        if (!$record) {
            return response()->json(['message' => 'That offer is not awaiting approval.'], 404);
        }

        $updated = OfferWorkflow::transition(
            (int) $record->id,
            Offer::REVISION_REQUESTED,
            $this->empno($request),
            $validated['reason']
        );

        return response()->json([
            'success' => true,
            'status' => $updated->status,
            'revision_count' => (int) $updated->revision_count,
            'message' => 'Sent back to HR. It will return here once they resubmit it.',
        ]);
    }

    /* ------------------------------------------------------------------ */

    /**
     * An offer awaiting approval, by id — or null.
     *
     * The id is a filter, never the authority: it is resolved from the set of
     * offers actually awaiting a decision, so nothing else can be reached by
     * editing it.
     */
    private function pending($offer): ?Offer
    {
        return OfferWorkflow::awaitingApproval()
            ->with('events')
            ->where('id', (int) $offer)
            ->first();
    }

    /**
     * What an approver needs to decide, and nothing more.
     *
     * No budget verdict and no salary band, because there is no authoritative
     * source for either. Deliberately narrower than HR's own view: the HR Head
     * is here to judge a compensation commitment, not to browse applicants.
     */
    private function summary(Offer $offer, array $context): array
    {
        return ($context[(int) $offer->application_id] ?? []) + [
            'id' => (int) $offer->id,
            'status' => $offer->status,
            'salary_amount' => $offer->salary_amount,
            'salary_currency' => $offer->salary_currency,
            'employment_status' => $offer->employment_status,
            'start_date' => $offer->start_date?->toDateString(),
            'notes' => $offer->notes,
            'submitted_at' => $offer->submitted_at?->format('M d, Y h:i A'),
            'submitted_by' => $offer->submitted_by,

            /* Shown so an approver knows this has been round before, and what
               they asked for last time. */
            'revision_count' => (int) $offer->revision_count,
            'revision_reason' => $offer->revision_reason,
        ];
    }

    /** The applicant and the posting behind each application, in two queries. */
    private function contextFor(array $applicationIds): array
    {
        if (empty($applicationIds)) {
            return [];
        }

        $rows = DB::connection('applicant')->table('tblapp_applications as a')
            ->leftJoin('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->whereIn('a.id', $applicationIds)
            ->get([
                'a.id', 'a.job_posting_id', 'a.request_position_id',
                'p.app_fname', 'p.app_mname', 'p.app_lname',
            ]);

        $postings = DB::connection('mysql')->table('tbl_job_posting')
            ->whereIn('id', $rows->pluck('job_posting_id')->filter()->unique())
            ->pluck('posting_title', 'id');

        $mrNos = DB::connection('mysql')->table('tbl_manpower_request_position as pos')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
            ->whereIn('pos.id', $rows->pluck('request_position_id')->filter()->unique())
            ->select('pos.id as position_id', 'r.mr_no')
            ->pluck('mr_no', 'position_id');

        $context = [];

        foreach ($rows as $row) {
            $context[(int) $row->id] = [
                'application_id' => (int) $row->id,
                'applicant_name' => trim(implode(' ', array_filter([
                    $row->app_fname, $row->app_mname, $row->app_lname,
                ]))) ?: 'Unnamed applicant',
                'posting_title' => $postings[$row->job_posting_id] ?? null,
                'mr_no' => $mrNos[$row->request_position_id] ?? null,
            ];
        }

        return $context;
    }
}
