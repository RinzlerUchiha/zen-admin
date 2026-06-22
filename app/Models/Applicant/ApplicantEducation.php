<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    use HasFactory;

    protected $connection = 'applicant';
    protected $table = 'tblapp_education';
    protected $primaryKey = 'educ_id';
    public $timestamps = false;

    protected $fillable = [
        'app_id',
        'educ_level',
        'educ_school',
        'educ_schooladd',
        'educ_degreetitle',
        'educ_major',
        'educ_yeargrad',
        'educ_currStatus',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    /**
     * Order by education level (highest first), then by year graduated descending.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw("FIELD(educ_level, 'College', 'Vocational', 'Senior High', 'Junior High', 'Elementary')")
            ->orderByDesc('educ_yeargrad');
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    /**
     * Returns a Bootstrap badge color class based on the current status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match (strtolower($this->educ_currStatus ?? '')) {
            'graduated' => 'success',
            'ongoing'   => 'primary',
            'dropped'   => 'danger',
            default     => 'secondary',
        };
    }
}