<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantColor extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $connection = 'applicant';
    protected $table = 'tblapp_whatcolorareyou';
    protected $primaryKey = 'wcay_id';
    public $timestamps = false;

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}
