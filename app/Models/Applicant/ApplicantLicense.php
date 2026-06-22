<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantLicense extends Model
{
    use HasFactory;

    protected $connection = 'applicant';
    protected $table = 'tblapp_eligibility';
    protected $primaryKey = 'el_id';
    public $timestamps = false;

    protected $fillable = [
        'app_id',
        'el_type',
        'el_regdate',
        'el_expdate',
        'el_profession',
        'el_file',
    ];

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}