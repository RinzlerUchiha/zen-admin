<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    // Default (zen-admin) connection — this table is NOT in hrd2
    protected $table = 'tbl_job_posting';

    protected $guarded = [];

    protected $casts = [
        'posted_at' => 'datetime',
        'closed_at' => 'datetime',
        'ad_is_custom' => 'boolean',
    ];

    /**
     * Recomposes the public ad from the linked jobspec, unless a person has
     * hand-edited it (ad_is_custom). Returns true when the stored ad actually
     * changed, so callers can report how many postings were refreshed.
     *
     * This is what keeps the careers page in step with HireFlow: correct the
     * jobspec there, and the ad follows on the next sync.
     */
    public function syncPublicAd(): bool
    {
        if ($this->ad_is_custom) {
            return false;
        }

        $position = $this->hireflowPosition;
        if (!$position) {
            return false;
        }

        $fresh = $position->draftPublicAd();

        if ($fresh === $this->public_description) {
            return false;
        }

        $this->public_description = $fresh;
        $this->save();

        return true;
    }

    /**
     * Sets ad_is_custom by comparing the supplied ad against a freshly
     * composed one. Text identical to the machine output stays auto-synced;
     * anything else is treated as hand-written and protected.
     *
     * Deriving the flag this way means clicking Generate and saving returns
     * a posting to auto-sync with no extra UI.
     */
    public function markAdOwnership(?string $ad): void
    {
        $position = $this->hireflowPosition;
        $composed = $position ? $position->draftPublicAd() : null;

        $this->ad_is_custom = trim((string) $ad) !== '' && trim((string) $ad) !== trim((string) $composed);
    }

    /**
     * Read-only pointer to the HireFlow position this posting was created from.
     * No enforced FK — request_position_id is a plain int referencing
     * hrd2.tbl_manpower_request_position.id.
     */
    public function hireflowPosition()
    {
        return $this->belongsTo(HireflowManpowerPosition::class, 'request_position_id', 'id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }
}