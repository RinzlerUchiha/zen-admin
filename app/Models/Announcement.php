<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl_announcement';
    protected $primaryKey = 'ann_id';
    public $timestamps = false;

    // public static function FunctionName($value='')
    // {
    //     // code...
    // }
}
