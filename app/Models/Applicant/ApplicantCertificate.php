<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantCertificate extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $connection = 'applicant';
    protected $table = 'tblapp_certificate';
    protected $primaryKey = 'cert_id';
    public $timestamps = false;

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}
