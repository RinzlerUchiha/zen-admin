<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantPersonal extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $connection = 'applicant';
    protected $table = 'tblapp_persinfo';
    protected $primaryKey = 'app_id';
    public $timestamps = false;

    public function getAppAgeAttribute()
    {
        return \Carbon\Carbon::parse($this->app_bdate)->age;
    }

    public function getFirstLastNameAttribute()
    {
        return "{$this->app_fname} {$this->app_lname}";
    }

    public function address()
    {
        return $this->hasOne(ApplicantAddress::class, 'app_id');
    }

    public function family()
    {
        return $this->hasMany(ApplicantFamily::class, 'app_id');
    }

    public function education()
    {
        return $this->hasMany(ApplicantEducation::class, 'app_id');
    }

    public function skill()
    {
        return $this->hasMany(ApplicantSkill::class, 'app_id');
    }

    public function license()
    {
        return $this->hasMany(ApplicantLicense::class, 'app_id');
    }

    public function certificate()
    {
        return $this->hasMany(ApplicantCertificate::class, 'app_id');
    }

    public function employmentRec()
    {
        return $this->hasMany(ApplicantEmploymentRec::class, 'app_id');
    }

    public function characterRef()
    {
        return $this->hasMany(ApplicantCharacterRef::class, 'app_id');
    }

    public function enneagram()
    {
        return $this->hasone(ApplicantEnneagram::class, 'app_id');
    }

    public function tapt()
    {
        return $this->hasone(ApplicantTapt::class, 'app_id');
    }

    public function disc()
    {
        return $this->hasone(ApplicantDisc::class, 'app_id');
    }

    public function miq()
    {
        return $this->hasone(ApplicantMiq::class, 'app_id');
    }

    public function color()
    {
        return $this->hasone(ApplicantColor::class, 'app_id');
    }

    public function vak()
    {
        return $this->hasone(ApplicantVak::class, 'app_id');
    }

    public function whyIWork()
    {
        return $this->hasone(ApplicantWhyIWork::class, 'app_id');
    }

    public function careerAnchor()
    {
        return $this->hasone(ApplicantCareerAnchor::class, 'app_id');
    }

    public function basicAbstractReasoning()
    {
        return $this->hasone(ApplicantBasicAbstractReasoning::class, 'app_id');
    }

    public function basicMath()
    {
        return $this->hasone(ApplicantBasicMath::class, 'app_id');
    }

    public function maya()
    {
        return $this->hasone(ApplicantMaya::class, 'app_id');
    }
}
