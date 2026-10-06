<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One recorded move through the hiring process. Append-only.
 *
 * Rows are never updated or deleted — a correction is another row. This is the
 * only place that can answer "when did this candidate reach Final, and who
 * moved them?", and it cannot be reconstructed after the fact.
 */
class StageHistory extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_stage_history';

    protected $fillable = [
        'application_id', 'from_stage', 'to_stage', 'reason', 'actor_empno', 'occurred_at',
    ];

    protected $casts = ['occurred_at' => 'datetime'];

    public function scopeForApplication($query, int $applicationId)
    {
        return $query->where('application_id', $applicationId)->orderBy('occurred_at');
    }
}
