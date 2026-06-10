<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManpowerRequest extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $connection = 'hrd2';
    protected $table = 'tbl_manpower';
    protected $primaryKey = 'mp_id';
    public $timestamps = false;
}
