<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAddress extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl201_address';

    public function personalInfo()
    {
        return $this->belongsTo(PersonalInfo::class, 'pers_empno', 'add_empno');
    }
}
