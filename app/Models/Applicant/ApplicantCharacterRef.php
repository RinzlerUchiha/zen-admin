<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantCharacterRef extends Model
{
    use HasFactory;

    protected $connection = 'applicant';
    protected $table = 'tblapp_reference';
    protected $primaryKey = 'ref_id';
    public $timestamps = false;

    protected $fillable = [
        'app_id',
        'ref_fullname',
        'ref_company',
        'ref_address',
        'ref_position',
        'ref_contact',
        'ref_relationship',
    ];

    public function profile()
    {
        return $this->belongsTo(ApplicantPersonal::class, 'app_id');
    }
}