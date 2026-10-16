<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationStep;
use App\Models\Applicant\Offer;
use App\Services\Recruitment\InterviewWorkflow;
use App\Services\Recruitment\OfferWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The Offer workspace.
 *
 * Two lists: offers that exist, and applications that have finished their
 * recruitment steps and could be offered to. Both are read from records that
 * already exist — an application's own steps decide the second, not a status
 * anybody typed.
 *
 * Every change goes through OfferWorkflow, which is where the transition
 * table, the reason requirements and the audit trail live. This controller
 * validates the shape of a request and hands it on; it decides nothing.
 *
 * Nothing here marks anybody hired. An accepted offer is the applicant
 * saying yes; the hire is recorded on the application, by HR, through the
 * status flow that already exists.
 */
class OfferController extends Controller
{
    public function index()
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'offers',
            'maincat' => 'offers',
            'page' => 'pages.recruitment.offers.index-content',
        ]);
    }

    /**
     * Offers, and who could be offered to.
     *
     * One round trip: the page shows both together, and they are read from
     * the same set of applications.
     */
    public function data()
    {
        $offers = Offer::query()->orderByDesc('created_at')->get();
        $applicationIds = $offers->pluck('application_id')->map(fn ($id) => (int) $id);

        /* Open applications with nothing left outstanding: the candidates a
           decision is now due on. Derived from the steps themselves, so this
           cannot drift from the workflow. */
        $open = ApplicantApplication::open()->get();
        $readyIds = $open->filter(
            fn ($a) => OfferWorkflow::eligibility((int) $a->id)['eligible']
        )->pluck('id')->map(fn ($id) => (int) $id);

        $context = $this->contextFor($applicationIds->merge($readyIds)->unique()->values()->all());

        return response()->json([
            'offers' => $offers->map(fn ($offer) => $this->offerRow($offer, $context))->values(),
            'ready' => $open->filter(fn ($a) => $readyIds->contains((int) $a->id))
                ->map(fn ($a) => ($context[(int) $a->id] ?? []) + [
                    'application_id' => (int) $a->id,
                    'status' => $a->status,
                ])->values(),
            'counts' => [
                'draft' => $offers->where('status', Offer::DRAFT)->count(),
                'pending_approval' => $offers->where('status', Offer::PENDING_APPROVAL)->count(),
                'revision_requested' => $offers->where('status', Offer::REVISION_REQUESTED)->count(),
                'approved' => $offers->where('status', Offer::APPROVED)->count(),
                'sent' => $offers->where('status', Offer::SENT)->count(),
                /* Overdue is derived, never stored: an offer past its date is
                   still sent and still waiting on an answer. */
                'overdue' => $offers->filter(fn ($o) => $o->isOverdue())->count(),
                'accepted' => $offers->where('status', Offer::ACCEPTED)->count(),
                'contract_received' => $offers->where('status', Offer::CONTRACT_RECEIVED)->count(),
                'closed' => $offers->whereIn('status', Offer::CLOSED)->count(),
                'ready' => $readyIds->count(),
            ],
        ]);
    }

    /** One offer, with its trail — what HR reads before deciding anything. */
    public function show($offer)
    {
        $record = Offer::with('events')->find((int) $offer);

        if (!$record) {
            return response()->json(['message' => 'That offer could not be found.'], 404);
        }

        $context = $this->contextFor([(int) $record->application_id]);

        return response()->json([
            'offer' => $this->offerRow($record, $context),
            'events' => $record->events->map(fn ($e) => [
                'from' => $e->from_status,
                'to' => $e->to_status,
                'reason' => $e->reason,
                'actor' => $e->actor_empno,
                'at' => $e->occurred_at?->format('M d, Y h:i A'),
            ])->values(),

            /* What was decided before the offer: the rounds and how they
               concluded. Read from the interview engine, not copied. */
            'rounds' => collect(InterviewWorkflow::summary((int) $record->application_id))
                ->map(fn ($round) => [
                    'label' => $round['display_label'],
                    'state' => $round['state'],
                    'result' => $round['result'] ?? null,
                ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required|integer',
            'salary_amount' => 'nullable|numeric|min:0|max:99999999',
            'salary_currency' => 'nullable|string|size:3',
            'employment_status' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'response_deadline' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $offer = OfferWorkflow::draft(
            (int) $validated['application_id'],
            $validated,
            $this->empno()
        );

        return response()->json(['success' => true, 'id' => $offer->id, 'status' => $offer->status]);
    }

    public function update(Request $request, $offer)
    {
        $validated = $request->validate([
            'salary_amount' => 'nullable|numeric|min:0|max:99999999',
            'salary_currency' => 'nullable|string|size:3',
            'employment_status' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'response_deadline' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $record = OfferWorkflow::reviseDraft((int) $offer, $validated, $this->empno());

        return response()->json(['success' => true, 'status' => $record->status]);
    }

    /**
     * Submit for approval, or resubmit after a revision request.
     *
     * Every offer needs the HR Head's approval, whatever the salary, position,
     * department or seniority — the gate is the compensation commitment
     * itself, not its size. There is no threshold and no bypass.
     */
    public function submit(Request $request, $offer)
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $record = OfferWorkflow::submit((int) $offer, $this->empno(), $validated['note'] ?? null);

        return response()->json([
            'success' => true,
            'status' => $record->status,
            'message' => $record->revision_count > 0
                ? 'Resubmitted for approval.'
                : 'Submitted to the HR Head for approval.',
        ]);
    }

    /**
     * Send an approved offer to the applicant.
     *
     * Reachable only from approved, so unapproved terms cannot reach a
     * candidate. A response deadline is required here and nowhere earlier,
     * because it starts running when the offer actually arrives.
     */
    public function send(Request $request, $offer)
    {
        $validated = $request->validate([
            'response_deadline' => 'nullable|date',
        ]);

        $record = OfferWorkflow::send(
            (int) $offer,
            $validated['response_deadline'] ?? null,
            $this->empno()
        );

        return response()->json([
            'success' => true,
            'status' => $record->status,
            'message' => 'Offer sent. The applicant has until '
                . $record->response_deadline->toFormattedDateString() . ' to answer.',
        ]);
    }

    /**
     * Move the response deadline.
     *
     * Allowed on purpose: the alternative is ending an offer the candidate is
     * still considering. Recorded with a reason, and counted so repeated
     * extension is visible.
     */
    public function extendDeadline(Request $request, $offer)
    {
        $validated = $request->validate([
            'response_deadline' => 'required|date',
            'reason' => 'required|string|max:500',
        ]);

        $record = OfferWorkflow::extendDeadline(
            (int) $offer,
            $validated['response_deadline'],
            $validated['reason'],
            $this->empno()
        );

        return response()->json([
            'success' => true,
            'status' => $record->status,
            'response_deadline' => $record->response_deadline->toDateString(),
            'message' => 'Deadline moved. The offer is still awaiting an answer.',
        ]);
    }

    /**
     * Record the applicant's answer.
     *
     * They do not use this system, so an authorised person records it for
     * them and is named separately from whoever drafted, approved or sent it.
     */
    public function respond(Request $request, $offer)
    {
        $validated = $request->validate([
            'answer' => 'required|string|in:' . Offer::ACCEPTED . ',' . Offer::DECLINED,
            'reason' => 'nullable|string|max:500',
        ]);

        $record = OfferWorkflow::recordResponse(
            (int) $offer,
            $validated['answer'],
            $validated['reason'] ?? null,
            $this->empno()
        );

        return response()->json([
            'success' => true,
            'status' => $record->status,

            /*
            | Said plainly, because it is the question HR asks next.
            |
            | An accepted offer is not a hire. The application's status is
            | theirs to set, on the application, once the hire actually
            | happens — nothing here touches it.
            */
            'message' => $record->status === Offer::ACCEPTED
                ? 'Offer accepted. The application is not marked Hired by this — record the hire on the application when it happens.'
                : 'Offer declined. Recorded against the offer, not the applicant.',
        ]);
    }

    /**
     * Record the signed contract.
     *
     * A separate act from acceptance: someone who accepted verbally and never
     * returned the contract is a gap HR has to be able to see.
     */
    public function contract(Request $request, $offer)
    {
        $validated = $request->validate([
            'contract_reference' => 'nullable|string|max:100',
            'contract_received_at' => 'nullable|date',
            'contract_file' => 'nullable|string|max:255',
        ]);

        $record = OfferWorkflow::recordContract((int) $offer, $validated, $this->empno());

        return response()->json([
            'success' => true,
            'status' => $record->status,
            'message' => 'Signed contract recorded. This is still not the hire.',
        ]);
    }

    /**
     * End an offer: withdraw it, or expire one the deadline has passed on.
     *
     * Only these two. Approving, declining and requesting a revision belong
     * to the HR Head and happen through the Zen-Hub approver API, not here —
     * so HR cannot approve their own offer through a side door. The
     * applicant's answer has its own endpoint above.
     *
     * Expiring is HR ending an overdue offer deliberately. Nothing expires on
     * its own, and a passing date never declines anything.
     */
    public function transition(Request $request, $offer)
    {
        $validated = $request->validate([
            'to' => 'required|string|in:' . Offer::WITHDRAWN . ',' . Offer::EXPIRED,
            'reason' => 'required|string|max:500',
        ]);

        $record = OfferWorkflow::transition(
            (int) $offer,
            $validated['to'],
            $this->empno(),
            $validated['reason']
        );

        return response()->json([
            'success' => true,
            'status' => $record->status,
            'message' => 'Offer ' . $record->status . '.',
        ]);
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
                'a.id', 'a.app_id', 'a.job_posting_id', 'a.request_position_id',
                'p.app_fname', 'p.app_mname', 'p.app_lname', 'p.app_email',
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
                'app_id' => (int) $row->app_id,
                'applicant_name' => trim(implode(' ', array_filter([
                    $row->app_fname, $row->app_mname, $row->app_lname,
                ]))) ?: 'Unnamed applicant',
                'app_email' => $row->app_email,
                'posting_title' => $postings[$row->job_posting_id] ?? null,
                'mr_no' => $mrNos[$row->request_position_id] ?? null,
            ];
        }

        return $context;
    }

    private function offerRow(Offer $offer, array $context): array
    {
        return ($context[(int) $offer->application_id] ?? []) + [
            'id' => (int) $offer->id,
            'application_id' => (int) $offer->application_id,
            'status' => $offer->status,
            /* Derived on read. Not a status, and never terminal. */
            'overdue' => $offer->isOverdue(),
            'awaiting_approval' => $offer->awaitingApproval(),
            'salary_amount' => $offer->salary_amount,
            'salary_currency' => $offer->salary_currency,
            'employment_status' => $offer->employment_status,
            'start_date' => $offer->start_date?->toDateString(),
            'response_deadline' => $offer->response_deadline?->toDateString(),
            'deadline_extended_count' => (int) $offer->deadline_extended_count,
            'submitted_at' => $offer->submitted_at?->format('M d, Y h:i A'),
            'approved_at' => $offer->approved_at?->format('M d, Y h:i A'),
            'approved_by' => $offer->approved_by,
            'rejected_at' => $offer->rejected_at?->format('M d, Y h:i A'),
            'rejected_reason' => $offer->rejected_reason,
            /* What the HR Head asked to be changed, and how many times. */
            'revision_requested_at' => $offer->revision_requested_at?->format('M d, Y h:i A'),
            'revision_reason' => $offer->revision_reason,
            'revision_count' => (int) $offer->revision_count,
            'sent_at' => $offer->sent_at?->format('M d, Y h:i A'),
            'responded_at' => $offer->responded_at?->format('M d, Y h:i A'),
            'response_recorded_by' => $offer->response_recorded_by,
            'contract_reference' => $offer->contract_reference,
            'contract_received_at' => $offer->contract_received_at?->format('M d, Y h:i A'),
            'notes' => $offer->notes,
            'allowed' => OfferWorkflow::ALLOWED[$offer->status] ?? [],
        ];
    }

    private function empno(): ?string
    {
        return Auth::user()->Emp_No ?? null;
    }
}
