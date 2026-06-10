<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl201_contractinfo';
    protected $primaryKey = 'ci_id';
    public $timestamps = false;

    public static function getList()
    {
        return Contract::leftJoin('tbl201_persinfo as b', 'b.pers_empno', '=', 'ci_empno')
            ->select('*')
            ->addSelect(DB::raw("TRIM(CONCAT(pers_lastname, ', ', pers_firstname)) as empname"))
            ->orderByRaw('pers_lastname, pers_firstname, ci_startdate, ci_enddate')
            ->get();
    }

    public static function store($data) {
        if($data['id']){
            Contract::where('ci_id', $data['id'])->update([
                'ci_empno' => $data['emp'],
                'ci_description' => $data['description'],
                'ci_startdate' => $data['start-date'],
                'ci_enddate' => $data['end-date'],
                'ci_file' => $data['filenames']
            ]);

            return $data['id'];
        }else{
            $tbl = Contract::create([
                'ci_empno' => $data['emp'],
                'ci_description' => $data['description'],
                'ci_startdate' => $data['start-date'],
                'ci_enddate' => $data['end-date'],
                'ci_file' => $data['filenames']
            ]);

            return $tbl->ci_id;
        }
    }
}
