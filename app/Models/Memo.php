<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    protected $guarded = [];

    // protected $connection = 'hrd2';
    protected $table = 'tbl_memo';
    protected $primaryKey = 'memo_id';
    public $timestamps = false;
}
