<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'tbl_holiday';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
