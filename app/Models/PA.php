<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PA extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'tbl_pa_form';
    protected $primaryKey = 'paf_id';
    public $timestamps = false;

    public static function loadList($year)
    {
        $data = DB::connection('hrd2')->table('tbl_pa_form')
            ->whereRaw("paf_status = 'active' AND YEAR(CONCAT(paf_period, '-01')) = ?", [$year])
            ->select('paf_empno', 'paf_period', 'paf_deptheadsign', 'paf_ratersign')
            ->addSelect([DB::raw("(SELECT 
                                    SUM( IF( pa_attainment != '' AND pa_weight != '', 
                                                IF( pa_attainment >= 96, 4, 
                                                    IF( pa_attainment >= 91, 3, 
                                                        IF( pa_attainment >= 85, 2, 1 ) 
                                                    ) 
                                                ) * (pa_weight / 100), 
                                            0 )
                                        ) FROM tbl_pa WHERE pa_pafid = paf_id 
                                ) AS weighted_rating_total")])
            ->get();

        $data2 = DB::connection('hrd2')->table('tbl_paf_sji')
            ->whereRaw("paf_status = '1' AND YEAR(CONCAT(paf_period, '-01')) = ?", [$year])
            ->select('paf_empno', 'paf_period', 'paf_approvedbysign AS paf_deptheadsign', 'paf_ratersign', 'paf_qtyscore AS weighted_rating_total')
            ->get();

        return $data->merge($data2);

        // if(!empty($data)){
        //     $details = DB::connection('hrd2')->table('tbl_pa')
        //     ->whereRaw("FIND_IN_SET(pa_pafid, ?) > 0", [$data->implode('paf_id', ',')])
        //     ->get();

        //     foreach ($data as $v) {
        //         $info = $details->where('pa_pafid', $v->paf_id);

        //         $score = 0;

        //         foreach ($info as $i) {
        //             // code...
        //         }

        //         $v->score = $score;
        //     }
        // }
    }
}
