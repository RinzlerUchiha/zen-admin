<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeContact extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl201_contact';

    public function personalInfo()
    {
        return $this->belongsTo(PersonalInfo::class, 'pers_empno', 'cont_empno');
    }
}
