<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationStep;
use App\Models\Applicant\Offer;
use App\Models\Applicant\OfferEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Offers: who may have one, what may happen to one, and who did it.
 *
 * WHAT AN OFFER IS NOT
 *
 * It is not a stage of the application and it is not a hire. The application
 * records what was decided about it and how far it got; the offer records
 * terms put to somebody and their answer. An accepted offer is the applicant
 * saying yes — the hire is a separate act HR records on the application, with
 * its own status and its own rules, and nothing in this service performs it.
 *
 * APPROVAL IS NOT DELIVERY
 *
 * Two different events, and once one status for both. The HR Head approving
 * the compensation commitment is `approved`; the offer reaching the applicant
 * is `sent`. SENT IS REACHABLE ONLY FROM APPROVED, so there is no path that
 * puts unapproved terms in front of a candidate.
 *
 * THREE ANSWERS, NOT TWO
 *
 * The HR Head may approve, decline, or send it back for revision. Without the
 * third an approver who wants a salary corrected would have to decline —
 * which is terminal — or approve something they believe is wrong.
 * `revision_requested` returns it to HR, and every cycle is kept.
 *
 * NOTHING EXPIRES ON ITS OWN
 *
 * A deadline passing is not an act. An overdue offer stays `sent` and waits
 * for a person: extend it, or end it. No code path here declines an offer
 * because of a date, and none ever should.
 *
 * NO BUDGET LOGIC
 *
 * There is no authoritative salary band or budget source in this system, so
 * nothing here computes a variance or judges terms against one. The HR Head
 * is shown the proposed terms and the recruitment evidence, and decides.
 *
 * EVERY CHANGE IS GUARDED AND RECORDED
 *
 * transition() is the only way an offer's status moves. It refuses anything
 * the table below does not allow, takes the actor, and writes the event in
 * the same transaction as the change — so there is no path that moves an
 * offer without leaving a trail.
 */
class OfferWorkflow
{
    /**
     * What may follow what.
     *
     * A closed offer is terminal: rejected, declined, withdrawn and expired
     * have no successors, so a finished offer cannot be quietly re-answered.
     * Re-offering is a NEW offer, which the one-live index permits precisely
     * because the old one is closed.
     *
     * `accepted` may still become `declined`: a candidate who accepts and
     * then pulls out is a real event, and refusing to record it would leave
     * the trail describing something that is not true. Reporting on that case
     * must read the events, not only the final status.
     */
    public const ALLOWED = [
        Offer::DRAFT => [Offer::PENDING_APPROVAL, Offer::WITHDRAWN],
        Offer::PENDING_APPROVAL => [
            Offer::APPROVED, Offer::REJECTED, Offer::REVISION_REQUESTED, Offer::WITHDRAWN,
        ],
        Offer::REVISION_REQUESTED => [Offer::PENDING_APPROVAL, Offer::WITHDRAWN],
        Offer::APPROVED => [Offer::SENT, Offer::WITHDRAWN],
        Offer::SENT => [Offer::ACCEPTED, Offer::DECLINED, Offer::EXPIRED, Offer::WITHDRAWN],
        Offer::ACCEPTED => [Offer::CONTRACT_RECEIVED, Offer::DECLINED, Offer::WITHDRAWN],
        Offer::CONTRACT_RECEIVED => [],
        Offer::REJECTED => [],
        Offer::DECLINED => [],
        Offer::WITHDRAWN => [],
        Offer::EXPIRED => [],
    ];

    /**
     * Transitions where the reason IS the record, so it is required.
     *
     * Each of these either refuses something or ends something, and in six
     * months the only question anybody will ask about it is why.
     */
    private const REASON_REQUIRED = [
        Offer::REJECTED, Offer::REVISION_REQUESTED, Offer::WITHDRAWN,
        Offer::DECLINED, Offer::EXPIRED,
    ];

    /**
     * Is this application ready to be offered to?
     *
     * Read from records that exist, never inferred from a status:
     *
     *   - the application is open (a closed one is not being hired)
     *   - it has workflow steps, and none of them is still pending
     *   - it has no offer already in play
     *
     * "No pending steps" is the whole test. It covers interviews and exams
     * alike without this service knowing what either is, and it cannot drift
     * from the workflow because it IS the workflow.
     */
    public static function eligibility(int $applicationId): array
    {
        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            return ['eligible' => false, 'reason' => 'That application could not be found.'];
        }

        if ($application->is_closed) {
            return ['eligible' => false, 'reason' => 'This application is closed.'];
        }

        $steps = ApplicationStep::forApplication($applicationId)->get();

        if ($steps->isEmpty()) {
            return ['eligible' => false, 'reason' => 'This application has no recruitment steps yet.'];
        }

        $pending = $steps->where('state', ApplicationStep::STATE_PENDING);

        if ($pending->isNotEmpty()) {
            return [
                'eligible' => false,
                'reason' => $pending->count() === 1
                    ? 'Still to do: ' . $pending->first()->label
                    : $pending->count() . ' recruitment steps are still outstanding.',
            ];
        }

        if (self::liveFor($applicationId)) {
            return ['eligible' => false, 'reason' => 'This application already has an offer in play.'];
        }

        return ['eligible' => true, 'reason' => null];
    }

    /** The offer still in play for an application, if there is one. */
    public static function liveFor(int $applicationId): ?Offer
    {
        return Offer::where('application_id', $applicationId)
            ->whereIn('status', Offer::LIVE)
            ->first();
    }

    /** Offers waiting on the HR Head. Nothing else is. */
    public static function awaitingApproval()
    {
        return Offer::where('status', Offer::PENDING_APPROVAL)->orderBy('submitted_at');
    }

    /**
     * Raise a draft offer.
     *
     * A draft, never an approved or sent one: writing the terms, having them
     * approved, and putting them to somebody are three acts by two different
     * people.
     */
    public static function draft(int $applicationId, array $terms, ?string $empno): Offer
    {
        $check = self::eligibility($applicationId);

        if (!$check['eligible']) {
            throw ValidationException::withMessages(['offer' => $check['reason']]);
        }

        return DB::connection('applicant')->transaction(function () use ($applicationId, $terms, $empno) {
            $offer = Offer::create([
                'application_id' => $applicationId,
                'status' => Offer::DRAFT,
                'salary_amount' => $terms['salary_amount'] ?? null,
                'salary_currency' => $terms['salary_currency'] ?? 'PHP',
                'employment_status' => $terms['employment_status'] ?? null,
                'start_date' => $terms['start_date'] ?? null,
                'response_deadline' => $terms['response_deadline'] ?? null,
                'notes' => $terms['notes'] ?? null,
                'created_by' => $empno,
            ]);

            self::record($offer, null, Offer::DRAFT, $terms['reason'] ?? null, $empno);

            return $offer;
        });
    }

    /**
     * Change the terms of an offer that has not gone out yet.
     *
     * A draft, or one the HR Head sent back — those are exactly the states in
     * which HR is the person holding it. Once it has been approved or put to
     * somebody, the terms they saw are part of the record, and changing them
     * silently would make the trail describe an offer nobody received.
     */
    public static function reviseDraft(int $offerId, array $terms, ?string $empno): Offer
    {
        $offer = self::find($offerId);

        if (!in_array($offer->status, [Offer::DRAFT, Offer::REVISION_REQUESTED], true)) {
            throw ValidationException::withMessages([
                'offer' => $offer->status === Offer::PENDING_APPROVAL
                    ? 'This offer is with the HR Head. Wait for their decision before changing it.'
                    : 'Only a draft, or an offer sent back for revision, can be changed. Withdraw this offer and raise another.',
            ]);
        }

        $offer->fill(array_intersect_key($terms, array_flip([
            'salary_amount', 'salary_currency', 'employment_status',
            'start_date', 'response_deadline', 'notes',
        ])));

        $offer->save();

        return $offer;
    }

    /**
     * Submit an offer for the HR Head's approval, or resubmit a revised one.
     *
     * Approval is required for EVERY offer. There is no threshold, no
     * seniority exemption and no department that skips it, because the gate is
     * the compensation commitment itself rather than its size.
     */
    public static function submit(int $offerId, ?string $empno, ?string $note = null): Offer
    {
        return self::transition($offerId, Offer::PENDING_APPROVAL, $empno, $note);
    }

    /**
     * Send an approved offer to the applicant.
     *
     * A response deadline is required here and nowhere earlier: it starts
     * running when the offer actually reaches them. There is deliberately no
     * default — the company policy for it has not been established, and
     * inventing a duration would make the system the author of a rule nobody
     * approved.
     */
    public static function send(int $offerId, ?string $deadline, ?string $empno): Offer
    {
        $offer = self::find($offerId);

        $deadline = trim((string) ($deadline ?? '')) ?: null;

        if ($deadline === null && $offer->response_deadline === null) {
            throw ValidationException::withMessages([
                'response_deadline' => 'Give the applicant a date to answer by before sending this offer.',
            ]);
        }

        if ($deadline !== null) {
            $offer->response_deadline = $deadline;
            $offer->save();
        }

        return self::transition($offerId, Offer::SENT, $empno);
    }

    /**
     * Move a deadline that has not been answered yet.
     *
     * Allowed on purpose: the alternative is HR ending an offer the candidate
     * is still considering. It is recorded with a reason and counted, so
     * repeated extension is visible without reading the whole trail.
     */
    public static function extendDeadline(int $offerId, string $deadline, string $reason, ?string $empno): Offer
    {
        $offer = self::find($offerId);

        if ($offer->status !== Offer::SENT) {
            throw ValidationException::withMessages([
                'offer' => 'Only an offer that is with the applicant has a deadline to move.',
            ]);
        }

        $reason = trim($reason) ?: null;

        if ($reason === null) {
            throw ValidationException::withMessages([
                'reason' => 'Moving a deadline needs a reason, and it is kept with the offer.',
            ]);
        }

        $was = $offer->response_deadline?->toDateString() ?? 'none';

        return DB::connection('applicant')->transaction(function () use ($offer, $deadline, $reason, $empno, $was) {
            $offer->response_deadline = $deadline;
            $offer->deadline_extended_count = (int) $offer->deadline_extended_count + 1;
            $offer->save();

            /* from and to are the same: the offer has not moved, its deadline
               has. Recorded as an event all the same, because it changes what
               the applicant was promised. */
            self::record($offer, Offer::SENT, Offer::SENT,
                'Deadline moved from ' . $was . ' to ' . $deadline . ' — ' . $reason, $empno);

            return $offer->refresh();
        });
    }

    /**
     * Record the applicant's answer.
     *
     * The applicant does not use this system, so an authorised person records
     * it on their behalf — and is named in the record, separately from
     * whoever drafted, approved or sent the offer.
     */
    public static function recordResponse(int $offerId, string $answer, ?string $reason, ?string $empno): Offer
    {
        if (!in_array($answer, [Offer::ACCEPTED, Offer::DECLINED], true)) {
            throw ValidationException::withMessages([
                'answer' => 'An answer is either accepted or declined.',
            ]);
        }

        $offer = self::transition($offerId, $answer, $empno, $reason);

        $offer->response_recorded_by = $empno;
        $offer->save();

        /*
        | A declined offer ends the application, with its own disposition.
        |
        | 'Offer Declined' rather than 'Not Selected': the applicant made this
        | decision, and recording it as HR's judgement of them would be false
        | and would attach a cooldown they have not earned. They stay in the
        | Candidate Pool — somebody who got as far as an offer is exactly who
        | is worth asking again.
        |
        | Acceptance deliberately closes nothing. 'Hired' is a separate act on
        | the application, and the contract is still to come.
        */
        if ($answer === Offer::DECLINED && $empno !== null) {
            $application = ApplicantApplication::find($offer->application_id);

            if ($application && !$application->is_closed) {
                ApplicationDecision::offerDeclined(
                    (int) $offer->application_id,
                    $empno,
                    $reason ?: 'Offer declined by the applicant'
                );
            }
        }

        return $offer->refresh();
    }

    /**
     * Record the signed contract.
     *
     * A separate fact from acceptance, and deliberately a separate state: an
     * applicant who accepted verbally and never returned the contract is a
     * gap HR needs to see, and one status for both would hide it.
     */
    public static function recordContract(int $offerId, array $details, ?string $empno): Offer
    {
        $offer = self::find($offerId);

        if ($offer->status !== Offer::ACCEPTED) {
            throw ValidationException::withMessages([
                'offer' => 'A contract is recorded against an accepted offer.',
            ]);
        }

        return DB::connection('applicant')->transaction(function () use ($offer, $details, $empno) {
            $offer->contract_reference = $details['contract_reference'] ?? null;
            $offer->contract_file = $details['contract_file'] ?? null;
            $offer->contract_received_at = $details['contract_received_at'] ?? now();
            $offer->contract_received_by = $empno;
            $offer->save();

            return self::transition($offer->id, Offer::CONTRACT_RECEIVED, $empno,
                $details['contract_reference'] ?? null);
        });
    }

    /**
     * Move an offer from one status to another.
     *
     * The only way the status changes. Refuses anything ALLOWED does not
     * permit, refuses the transitions whose reason is the point when no reason
     * is given, and writes the event in the same transaction — so a moved
     * offer with no trail is not a state this code can produce.
     */
    public static function transition(int $offerId, string $to, ?string $empno, ?string $reason = null): Offer
    {
        $offer = self::find($offerId);
        $from = (string) $offer->status;

        if (!in_array($to, self::ALLOWED[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'offer' => $offer->isClosed()
                    ? 'This offer is already ' . self::readable($from) . ' and cannot be changed.'
                    : 'An offer that is ' . self::readable($from) . ' cannot become ' . self::readable($to) . '.',
            ]);
        }

        $reason = trim((string) $reason) ?: null;

        if (in_array($to, self::REASON_REQUIRED, true) && $reason === null) {
            throw ValidationException::withMessages([
                'reason' => 'A reason is needed, and it is kept with the offer.',
            ]);
        }

        return DB::connection('applicant')->transaction(function () use ($offer, $from, $to, $empno, $reason) {
            $offer->status = $to;

            /* Each event stamps its own time and its own actor. They are
               separate columns rather than one pair reused, because "who
               approved this" and "who sent it" are different questions and a
               shared column could only answer the last one asked. */
            match ($to) {
                Offer::PENDING_APPROVAL => self::stampSubmission($offer, $empno),
                Offer::APPROVED => self::stamp($offer, 'approved', $empno),
                Offer::REJECTED => self::stampRejection($offer, $empno, $reason),
                Offer::REVISION_REQUESTED => self::stampRevision($offer, $empno, $reason),
                Offer::SENT => self::stamp($offer, 'sent', $empno),
                default => null,
            };

            /* The applicant's answer, whenever it is what settled the offer. */
            if (in_array($to, [Offer::ACCEPTED, Offer::DECLINED], true)) {
                $offer->responded_at = now();
            }

            $offer->save();

            self::record($offer, $from, $to, $reason, $empno);

            return $offer->refresh();
        });
    }

    /* ------------------------------------------------------------------ */

    private static function stamp(Offer $offer, string $event, ?string $empno): void
    {
        $offer->{$event . '_at'} = now();
        $offer->{$event . '_by'} = $empno;
    }

    /**
     * A submission, which may be the first or a resubmission.
     *
     * submitted_at holds the most recent one. The earlier ones are not lost:
     * every submission writes its own event, so the cycle is readable in full
     * from tblapp_offer_events.
     */
    private static function stampSubmission(Offer $offer, ?string $empno): void
    {
        self::stamp($offer, 'submitted', $empno);
    }

    private static function stampRejection(Offer $offer, ?string $empno, ?string $reason): void
    {
        self::stamp($offer, 'rejected', $empno);
        $offer->rejected_reason = $reason;
    }

    /**
     * Sent back for revision.
     *
     * The counter is incremented here rather than derived, so a screen can say
     * "third time round" without reading the trail — and the trail still holds
     * every individual request and its reason.
     */
    private static function stampRevision(Offer $offer, ?string $empno, ?string $reason): void
    {
        $offer->revision_requested_at = now();
        $offer->revision_requested_by = $empno;
        $offer->revision_reason = $reason;
        $offer->revision_count = (int) $offer->revision_count + 1;
    }

    /** Status names as a person would say them. */
    private static function readable(string $status): string
    {
        return match ($status) {
            Offer::PENDING_APPROVAL => 'awaiting approval',
            Offer::REVISION_REQUESTED => 'back with HR for revision',
            Offer::CONTRACT_RECEIVED => 'signed',
            default => $status,
        };
    }

    /** The trail. Written only from inside the transaction that moved it. */
    private static function record(Offer $offer, ?string $from, string $to, ?string $reason, ?string $empno): void
    {
        OfferEvent::create([
            'offer_id' => $offer->id,
            'from_status' => $from,
            'to_status' => $to,
            'reason' => $reason,
            'actor_empno' => $empno,
            'occurred_at' => now(),
        ]);
    }

    private static function find(int $offerId): Offer
    {
        $offer = Offer::find($offerId);

        if (!$offer) {
            throw ValidationException::withMessages(['offer' => 'That offer could not be found.']);
        }

        return $offer;
    }
}
