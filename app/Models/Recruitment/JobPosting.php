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
    ];

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