<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantWhyIWork extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $connection = 'applicant';
    protected $table = 'tblapp_whyiwork';
    protected $primaryKey = 'wiw_id';
    public $timestamps = false;

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}
