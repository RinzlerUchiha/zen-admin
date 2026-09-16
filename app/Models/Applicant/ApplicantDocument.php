<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * A document an applicant uploaded through zen-applicants (tblapp_documents).
 * HR only ever reviews it; the file itself is written by the applicant portal.
 */
class ApplicantDocument extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_documents';
    protected $guarded = ['id'];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'doc_size' => 'integer',
    ];

    public function getTypeLabelAttribute(): string
    {
        return config('applicant_documents.types.' . $this->doc_type, $this->doc_type);
    }

    /**
     * Key on the shared documents disk. Rows written before Milestone 2 held
     * only the filename, so those resolve against their folder.
     */
    public function getStoragePathAttribute(): string
    {
        if (str_contains($this->doc_file, '/')) {
            return $this->doc_file;
        }

        return config('applicant_documents.path') . '/' . $this->app_id . '/' . $this->doc_file;
    }

    /**
     * Identifies the exact file HR was looking at. Replacing a document keeps
     * the same row but changes the file, so a decision posted with a stale
     * token is refused rather than applied to a file HR never saw.
     */
    public function getVersionTokenAttribute(): string
    {
        return sha1($this->doc_file . '|' . $this->uploaded_at?->format('U.u'));
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->doc_mime === 'application/pdf';
    }

    public function getSizeForHumansAttribute(): string
    {
        $bytes = (int) $this->doc_size;

        if ($bytes < 1048576) {
            return max(1, round($bytes / 1024)) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getReviewReasonLabelAttribute(): ?string
    {
        return $this->review_reason
            ? config('applicant_documents.review_reasons.' . $this->review_reason . '.label', $this->review_reason)
            : null;
    }
}
