<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EEI extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'db_eei.tbl_response';
    protected $primaryKey = 'resp_id';
    public $timestamps = false;

    public static function loadList($ym)
    {
        return DB::connection('hrd2')->table('db_eei.tbl_response as a')
            ->leftJoin('db_eei.tbl_set as b', function ($join){
                $join->on('b.set_num', '=', 'a.resp_setnum')
                ->on('b.set_item', '=', 'a.resp_setitem');
            })
            ->whereRaw("DATE_FORMAT(a.resp_date, '%Y-%m') = ?", [$ym])
            ->orderBy('a.resp_empno', 'asc')
            ->orderBy('a.resp_setnum', 'asc')
            ->orderByRaw("REPLACE(a.resp_setitem, '-', '') ASC")
            ->get();
    }
}
