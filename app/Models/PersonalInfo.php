<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl201_persinfo';

    public function addresses()
    {
        return $this->hasMany(EmployeeAddress::class, 'add_empno', 'pers_empno');
    }

    public function contacts()
    {
        return $this->hasMany(EmployeeContact::class, 'cont_empno', 'pers_empno');
    }

}
