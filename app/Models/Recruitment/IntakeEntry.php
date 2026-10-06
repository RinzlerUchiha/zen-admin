<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A controlled entry point: the office kiosk, or one job-fair event.
 *
 * HR activates one and gets a URL and a QR code. Applications made after
 * arriving through it are recorded with this entry's source, so nobody has to
 * ask the applicant how they found us — and nobody has to trust the answer.
 *
 * This is source tracking and nothing else. An entry point says where an
 * application came from; it has no opinion about stage, interviews, exams,
 * documents or whether anyone is hired.
 */
class IntakeEntry extends Model
{
    protected $table = 'tbl_intake_entry';

    protected $fillable = [
        'kind', 'label', 'event_date', 'token',
        'activated_by', 'activated_at', 'deactivated_by', 'deactivated_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    /** The office kiosk: one standing entry, not an event. */
    public const KIND_WALK_IN = 'walk_in';

    /** One job fair. There may be many over time, and several live at once. */
    public const KIND_JOB_FAIR = 'job_fair';

    /**
     * A fresh token.
     *
     * Generated on every activation rather than reused, so a QR code printed
     * for a previous run of the same event stops working when that run ended.
     */
    public static function freshToken(): string
    {
        return Str::random(48);
    }

    /** Live right now. NULL deactivated_at is the whole test. */
    public function scopeActive($query)
    {
        return $query->whereNull('deactivated_at');
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /** What HR calls this one. The kiosk has no name of its own. */
    public function displayName(): string
    {
        if (filled($this->label)) {
            return $this->label;
        }

        return $this->kind === self::KIND_WALK_IN ? 'Office kiosk' : 'Job fair';
    }
}
