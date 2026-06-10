<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $connection = 'applicant';
    protected $table = 'tblapp_education';
    protected $primaryKey = 'educ_id';
    public $timestamps = false;

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}
