<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

class InterviewDeets extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_interviewdeets';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'app_id',
        'interview_type',
        'interview_date',
        'interviewer_name',
        'company',
        'position',
        'remarks',
        'verdict',
    ];

    public function applicant()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id', 'app_id');
    }
}