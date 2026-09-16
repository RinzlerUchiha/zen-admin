<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * HR asking an applicant for a document (tblapp_document_requests).
 *
 *   open       HR is waiting on the applicant
 *   submitted  the applicant uploaded; HR has not checked it yet
 *   closed     HR accepted what was sent
 *   cancelled  HR no longer needs it
 */
class ApplicantDocumentRequest extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_document_requests';
    protected $guarded = ['id'];

    public const ACTIVE = ['open', 'submitted'];

    protected $casts = [
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE);
    }
}
