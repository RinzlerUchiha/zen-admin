<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One run at collecting an applicant's outstanding documents
 * (tblapp_document_processes).
 *
 *   active                 waiting on the applicant
 *   complete               every request was accepted; they move on
 *   non_responsive         the deadline passed with requests unresolved
 *   requirements_not_met   the attempts ran out with requests unresolved
 *   withdrawn              the applicant pulled out
 *
 * Everything but "active" is terminal: nothing reopens a finished run, and the
 * candidate is kept rather than deleted.
 */
class ApplicantDocumentProcess extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_document_processes';
    protected $guarded = ['id'];

    public const ACTIVE = 'active';
    public const COMPLETE = 'complete';
    public const NON_RESPONSIVE = 'non_responsive';
    public const REQUIREMENTS_NOT_MET = 'requirements_not_met';
    public const WITHDRAWN = 'withdrawn';

    /** Outcomes that end the run. */
    public const TERMINAL = [
        self::COMPLETE,
        self::NON_RESPONSIVE,
        self::REQUIREMENTS_NOT_MET,
        self::WITHDRAWN,
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'started_at' => 'datetime',
        'outcome_at' => 'datetime',
        'deadline_days' => 'integer',
        'max_attempts' => 'integer',
        'attempts_used' => 'integer',
    ];

    public function requests()
    {
        return $this->hasMany(ApplicantDocumentRequest::class, 'process_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::ACTIVE);
    }

    /**
     * Runs the deadline sweep may close: still active, and overdue.
     */
    public function scopeOverdue($query, $asOf = null)
    {
        return $query->active()->where('deadline_at', '<', $asOf ?? now());
    }

    public function scopeInCandidatePool($query)
    {
        return $query->whereIn('status', config('applicant_documents.completion.pool_statuses', []));
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function getIsTerminalAttribute(): bool
    {
        return in_array($this->status, self::TERMINAL, true);
    }

    /**
     * In the Candidate Pool: a candidate who did not progress but whose record
     * is kept. Derived from the outcome rather than stored separately, so the
     * two can never disagree.
     */
    public function getInCandidatePoolAttribute(): bool
    {
        return in_array($this->status, config('applicant_documents.completion.pool_statuses', []), true);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('applicant_documents.completion.statuses.' . $this->status, $this->status);
    }

    public function getAttemptsLeftAttribute(): int
    {
        return max(0, $this->max_attempts - $this->attempts_used);
    }
}
