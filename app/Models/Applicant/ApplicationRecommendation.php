<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * HR's recommendation of an applicant from one application to another job post.
 *
 * A record of a decision, not a relationship between two rows. One application
 * may be recommended to several postings, and each of those is its own row with
 * its own reason, actor and time — which is why this is not a column on
 * tblapp_applications.
 *
 * The two applications it links stay entirely separate. Nothing here implies
 * shared interviews, workflow steps or history.
 */
class ApplicationRecommendation extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_application_recommendations';

    protected $fillable = [
        'source_application_id', 'target_job_posting_id', 'target_application_id',
        'recommended_by', 'recommended_at', 'reason',
    ];

    protected $casts = ['recommended_at' => 'datetime'];

    /** What came of this application — its recommendations, oldest first. */
    public function scopeFromApplication($query, int $applicationId)
    {
        return $query->where('source_application_id', $applicationId)->orderBy('recommended_at');
    }

    /**
     * Where this application came from.
     *
     * At most one row: an application is created by a single recommendation.
     * Following this one hop at a time is how an A -> B -> C chain is read,
     * without needing to hold the whole graph.
     */
    public function scopeThatCreated($query, int $applicationId)
    {
        return $query->where('target_application_id', $applicationId);
    }

    public function sourceApplication()
    {
        return $this->belongsTo(ApplicantApplication::class, 'source_application_id');
    }

    public function targetApplication()
    {
        return $this->belongsTo(ApplicantApplication::class, 'target_application_id');
    }
}
