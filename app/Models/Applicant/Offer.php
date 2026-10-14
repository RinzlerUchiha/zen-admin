<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * An offer made against one application.
 *
 * It carries terms and a life of its own: issued, then accepted, declined,
 * withdrawn or left to lapse. None of that is the application's status, and
 * none of it is a hire — an accepted offer is the applicant saying yes, and
 * the hire is a separate act HR records on the application.
 */
class Offer extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_offers';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'expires_at' => 'date',
        'issued_at' => 'datetime',
        'responded_at' => 'datetime',
        'salary_amount' => 'decimal:2',
    ];

    /** Written up, not yet put to the applicant. */
    public const DRAFT = 'draft';

    /** Put to them; waiting on their answer. */
    public const ISSUED = 'issued';

    /** They said yes. NOT a hire. */
    public const ACCEPTED = 'accepted';

    public const DECLINED = 'declined';

    /** HR took it back before it was answered. */
    public const WITHDRAWN = 'withdrawn';

    /** It lapsed without an answer. */
    public const EXPIRED = 'expired';

    /** Still in play: exactly the states the one-live-offer index covers. */
    public const LIVE = [self::DRAFT, self::ISSUED];

    /** Answered or abandoned — nothing further happens to these. */
    public const SETTLED = [self::ACCEPTED, self::DECLINED, self::WITHDRAWN, self::EXPIRED];

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

    /**
     * Issued, past its date, and still unanswered.
     *
     * Derived rather than stored: nothing expires an offer on its own,
     * because a status change nobody performed is not an auditable act. This
     * is what lets the queue say "this has lapsed" and HR record it.
     */
    public function hasLapsed(): bool
    {
        return $this->status === self::ISSUED
            && $this->expires_at !== null
            && $this->expires_at->endOfDay()->isPast();
    }
}
