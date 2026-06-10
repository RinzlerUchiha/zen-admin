<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExitInterview extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function showListByEmpNo($empno)
    {
        return DB::connection('hrd2')->table('tbl201_exit_intvw')->where('xintvw_empno', $empno)->get();
    }

    public static function showInfo($id)
    {
        $tbl = DB::connection('hrd2')->table('tbl201_exit_intvw')->where('xintvw_id', $id)->first();
        $answer = DB::connection('hrd2')->table('tbl201_exit_intvw_ans')->where('xans_exitid', $tbl->xintvw_id)->get();
        $tbl->answers = $answer->mapWithKeys(fn ($a) => ['xansq_'.$a->xans_question => $a->xans_ans]);
        return $tbl;
    }

    public static function getInterviewQuestion()
    {
        return DB::connection('hrd2')->table('tbl_exit_intvw')->where('exit_qstat', 'active')->get();
    }
    
    public static function store($data)
    {
        if($data['id']){
            $tbl = DB::connection('hrd2')->table('tbl201_exit_intvw')->where('xintvw_id', $data['id']);
            if(!$tbl){
                return;
            }
            $tbl->update([
                'xintvw_empno' => $data['emp'],
                'xintvw_interviewer' => $data['intervewer'],
                'xintvw_intvwdate' => $data['interviewdt'],
                'xintvw_receivedby' => $data['receivedby'],
                'xintvw_receiveddate' => $data['receivedt'],
                // 'xintvw_empsign' => $data[''],
                'xintvw_pos' => $data['pos'],
                'xintvw_dthired' => $data['hiredt'],
                'xintvw_superior' => $data['superior'],
                'xintvw_dtresign' => $data['dtresign'],
                'xintvw_dept' => $data['dept'],
                'xintvw_lastday' => $data['lastdt']
            ]);
            
            foreach ($data['ans'] as $v) {
                $val = explode('|', $v);
                DB::connection('hrd2')->table('tbl201_exit_intvw_ans')->updateOrInsert(
                    [
                        'xans_exitid' => $data['id'],
                        'xans_empno' => $data['emp'],
                        'xans_question' => $val[0]
                    ],
                    ['xans_ans' => $val[1]]
                );
            }

            return $data['id'];
        }else{
            $tbl = DB::connection('hrd2')->table('tbl201_exit_intvw')->insertGetId([
                'xintvw_empno' => $data['emp'],
                'xintvw_interviewer' => $data['intervewer'],
                'xintvw_intvwdate' => $data['interviewdt'],
                'xintvw_receivedby' => $data['receivedby'],
                'xintvw_receiveddate' => $data['receivedt'],
                'xintvw_pos' => $data['pos'],
                'xintvw_dthired' => $data['hiredt'],
                'xintvw_superior' => $data['superior'],
                'xintvw_dtresign' => $data['dtresign'],
                'xintvw_dept' => $data['dept'],
                'xintvw_lastday' => $data['lastdt']
            ]);

            foreach ($data['ans'] as $v) {
                $val = explode('|', $v);
                DB::connection('hrd2')->table('tbl201_exit_intvw_ans')->insert([
                    'xans_exitid' => $tbl,
                    'xans_empno' => $data['emp'],
                    'xans_question' => $val[0],
                    'xans_ans' => $val[1]
                ]);
            }

            return $tbl;
        }
    }

    public static function sign($data)
    {
        DB::connection('hrd2')->table('tbl201_exit_intvw')
        ->where('xintvw_id', $data['id'])
        ->where('xintvw_empno', $data['empno'])
        ->update(['xintvw_empsign' => $data['sign']]);
    }
}