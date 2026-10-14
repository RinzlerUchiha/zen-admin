<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One thing that happened to an offer.
 *
 * Append-only by intent: a correction is another row, never an edit of the
 * first. The offer row says where it is now; these say how it got there and
 * who decided.
 */
class OfferEvent extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_offer_events';

    protected $guarded = [];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }
}
