<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantEmploymentRec extends Model
{
    use HasFactory;

    protected $connection = 'applicant';
    protected $table = 'tblapp_employment';
    protected $primaryKey = 'empl_id';
    public $timestamps = false;

    protected $fillable = [
        'app_id',
        'empl_company',
        'empl_address',
        'empl_position',
        'empl_supervisor',
        'empl_contact',
        'empl_from',
        'empl_to',
        'empl_reason',
    ];

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}