<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * An applicant's attempt at one assessment (tblapp_assessment_attempts),
 * written by zen-applicants. HR only reads it: where each assessment stands,
 * and — for an interrupted one — how much time is left when it resumes.
 */
class ApplicantAssessmentAttempt extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_assessment_attempts';

    protected $guarded = ['*'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'interrupted_at' => 'datetime',
        'ended_at' => 'datetime',
        'result_saved' => 'boolean',
    ];

    protected $hidden = ['draft', 'tab_token', 'session_hash', 'seed'];

    /**
     * The status as it stands now. zen-applicants brings an attempt up to date
     * when the applicant next opens it; until then a "running" attempt whose
     * page went quiet is shown by the same rule it will be recorded by.
     */
    public function getCurrentStatusAttribute(): string
    {
        if ($this->status !== 'active') {
            return $this->status;
        }

        $grace = config('applicant_assessments.grace_seconds');
        $gap = max(0, now()->getTimestamp() - $this->last_seen_at->getTimestamp());

        return match (true) {
            $this->time_used_seconds + min($gap, $grace) >= $this->duration_seconds => 'timed_out',
            $gap > $grace => 'interrupted',
            default => 'active',
        };
    }

    /** Minutes left on the clock (an interrupted attempt's clock is stopped). */
    public function getMinutesLeftAttribute(): int
    {
        return (int) ceil(max(0, $this->duration_seconds - $this->time_used_seconds) / 60);
    }
}
