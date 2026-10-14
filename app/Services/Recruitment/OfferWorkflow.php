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
 * WHY THERE IS NO NEW WORKFLOW STAGE
 *
 * Eligibility is READ from the workflow that already exists: an application
 * whose steps are all settled has nothing left to do and is therefore ready
 * to be offered to. Adding an "offer" stage to tbl_workflow_stage would have
 * changed the sequence every future application is built with, and invented a
 * state the rest of the system has no rules for.
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
     * A settled offer is terminal: accepted, declined, withdrawn and expired
     * have no successors, so an answered offer cannot be quietly re-answered.
     * Re-offering is a NEW offer, which the one-live index permits precisely
     * because the old one is settled.
     */
    public const ALLOWED = [
        Offer::DRAFT => [Offer::ISSUED, Offer::WITHDRAWN],
        Offer::ISSUED => [Offer::ACCEPTED, Offer::DECLINED, Offer::WITHDRAWN, Offer::EXPIRED],
        Offer::ACCEPTED => [],
        Offer::DECLINED => [],
        Offer::WITHDRAWN => [],
        Offer::EXPIRED => [],
    ];

    /** Transitions where HR's reason is the point, so it is required. */
    private const REASON_REQUIRED = [Offer::WITHDRAWN, Offer::DECLINED];

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

    /**
     * Raise a draft offer.
     *
     * A draft, never an issued one: writing the terms and putting them to
     * somebody are two acts, and HR does the first while still deciding.
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
                'expires_at' => $terms['expires_at'] ?? null,
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
     * Only a draft. Once it has been put to somebody, the terms they were
     * shown are part of the record — changing them silently would make the
     * audit trail describe an offer nobody ever received.
     */
    public static function reviseDraft(int $offerId, array $terms, ?string $empno): Offer
    {
        $offer = self::find($offerId);

        if ($offer->status !== Offer::DRAFT) {
            throw ValidationException::withMessages([
                'offer' => 'Only a draft can be changed. Withdraw this offer and raise another.',
            ]);
        }

        $offer->fill(array_intersect_key($terms, array_flip([
            'salary_amount', 'salary_currency', 'employment_status',
            'start_date', 'expires_at', 'notes',
        ])));

        $offer->save();

        return $offer;
    }

    /**
     * Move an offer from one status to another.
     *
     * The only way the status changes. Refuses anything ALLOWED does not
     * permit, refuses a withdrawal or a decline with no reason, and writes
     * the event in the same transaction — so a moved offer with no trail is
     * not a state this code can produce.
     */
    public static function transition(int $offerId, string $to, ?string $empno, ?string $reason = null): Offer
    {
        $offer = self::find($offerId);
        $from = (string) $offer->status;

        if (!in_array($to, self::ALLOWED[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'offer' => in_array($from, Offer::SETTLED, true)
                    ? 'This offer is already ' . $from . ' and cannot be changed.'
                    : 'An offer that is ' . $from . ' cannot become ' . $to . '.',
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

            if ($to === Offer::ISSUED) {
                $offer->issued_at = now();
                $offer->issued_by = $empno;
            }

            /* The applicant's answer, whenever it is what settled the offer. */
            if (in_array($to, [Offer::ACCEPTED, Offer::DECLINED], true)) {
                $offer->responded_at = now();
            }

            $offer->save();

            self::record($offer, $from, $to, $reason, $empno);

            return $offer->refresh();
        });
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
