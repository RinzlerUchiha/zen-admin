<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantSkill extends Model
{
    use HasFactory;

    const CATEGORY_OTHERS = 7;

    protected $connection = 'applicant';
    protected $table = 'tblapp_skills';
    protected $primaryKey = 'skill_id';
    public $timestamps = false;

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }

    /**
     * Returns the display name, falling back to skill_others
     * when the category is "Others".
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->skill_category === self::CATEGORY_OTHERS
            ? ($this->skill_others ?? '')
            : ($this->skill_name ?? '');
    }
}