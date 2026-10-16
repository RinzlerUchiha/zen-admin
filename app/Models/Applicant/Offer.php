<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * An offer made against one application.
 *
 * It carries terms and a life of its own, and that life has five distinct
 * events in it: HR Head approval, delivery to the applicant, the applicant's
 * answer, receipt of the signed contract, and — separately and elsewhere — the
 * hire. None of them is the application's status, and none of them is a hire:
 * an accepted offer is the applicant saying yes, and the hire is an act HR
 * records on the application itself.
 *
 * TWO REFUSALS, NOT ONE
 *
 * REJECTED is the HR Head refusing to approve the terms. DECLINED is the
 * applicant refusing the offer. They are different events by different people
 * and are never collapsed.
 */
class Offer extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_offers';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'response_deadline' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'contract_received_at' => 'datetime',
        'salary_amount' => 'decimal:2',
        'revision_count' => 'integer',
        'deadline_extended_count' => 'integer',
    ];

    /** Written up, not yet submitted for approval. */
    public const DRAFT = 'draft';

    /** Submitted; waiting on the HR Head. This is the approver's queue. */
    public const PENDING_APPROVAL = 'pending_approval';

    /**
     * The HR Head sent it back to be changed.
     *
     * HR's work, not the approver's: an offer in this state must never appear
     * as awaiting approval, because nobody is waiting on the HR Head for it.
     */
    public const REVISION_REQUESTED = 'revision_requested';

    /** Approved by the HR Head. Not yet with the applicant. */
    public const APPROVED = 'approved';

    /** The HR Head refused the terms. Terminal. */
    public const REJECTED = 'rejected';

    /** With the applicant; the response deadline is running. */
    public const SENT = 'sent';

    /** They said yes. NOT a hire. */
    public const ACCEPTED = 'accepted';

    /** The signed contract is in. Still not the hire. */
    public const CONTRACT_RECEIVED = 'contract_received';

    /** The APPLICANT said no. Terminal. */
    public const DECLINED = 'declined';

    /** HR took it back. Terminal. */
    public const WITHDRAWN = 'withdrawn';

    /** HR ended it after the deadline passed unanswered. Terminal. */
    public const EXPIRED = 'expired';

    /**
     * Still in play: exactly the states the one-live-offer index covers.
     *
     * An offer under revision is live — it is still this application's offer,
     * being fixed — so a second one cannot be raised alongside it.
     */
    public const LIVE = [
        self::DRAFT, self::PENDING_APPROVAL, self::REVISION_REQUESTED, self::APPROVED,
        self::SENT, self::ACCEPTED, self::CONTRACT_RECEIVED,
    ];

    /** Over. The application's live slot is free for a new offer. */
    public const CLOSED = [self::REJECTED, self::DECLINED, self::WITHDRAWN, self::EXPIRED];

    /**
     * Retained for the places that ask "is this finished with".
     *
     * Deliberately the CLOSED set: an accepted offer is settled in the sense
     * that the applicant has answered, but it is not finished with — the
     * contract and the hire are still to come.
     */
    public const SETTLED = self::CLOSED;

    public function application()
    {
        return $this->belongsTo(ApplicantApplication::class, 'application_id');
    }

    public function events()
    {
        return $this->hasMany(OfferEvent::class, 'offer_id')->orderBy('occurred_at');
    }

    public function isLive(): bool
    {
        return in_array($this->status, self::LIVE, true);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED, true);
    }

    /** Waiting on the HR Head — and only that state is. */
    public function awaitingApproval(): bool
    {
        return $this->status === self::PENDING_APPROVAL;
    }

    /**
     * Sent, past its deadline, and still unanswered.
     *
     * Derived on every read rather than stored, so it is never stale and no
     * job has to sweep for it. Overdue is NOT a status: the offer is still
     * `sent` and still awaiting an answer, and it stays that way until an
     * authorised person extends the deadline or ends it. Nothing here, and
     * nothing anywhere else, declines an offer because a date passed.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::SENT
            && $this->response_deadline !== null
            && $this->response_deadline->endOfDay()->isPast();
    }

    /**
     * Kept so older callers keep working, and meaning what it always did.
     *
     * @deprecated Prefer isOverdue(): an offer past its deadline has not
     *             expired, it is overdue, and only HR can end it.
     */
    public function hasLapsed(): bool
    {
        return $this->isOverdue();
    }
}
