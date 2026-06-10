<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kamustahan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'tbl201_kamustahan';
    protected $primaryKey = 'ekmst_id';
    public $timestamps = false;

    public static function getQuestions()
    {
        return DB::connection('hrd2')->table('tbl_kamustahan')->get()->groupBy('kmst_type');
    }

    public static function getKamustahanList($empno = '')
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });
        $tbl = DB::connection('hrd2')->table('tbl201_kamustahan');
        if ($empno) {
            $tbl->where('ekmst_empno', $empno);
        }
        return $tbl->get()->map(function ($item) use ($persinfo) {
            $item->empname = isset($persinfo[$item->ekmst_empno]) ? trim($persinfo[$item->ekmst_empno]['pers_lastname'] . ", " . $persinfo[$item->ekmst_empno]['pers_firstname']) : '';

            $item->interviewer_name = isset($persinfo[$item->ekmst_interviewer]) ? trim($persinfo[$item->ekmst_interviewer]['pers_lastname'] . ", " . $persinfo[$item->ekmst_interviewer]['pers_firstname']) : '';

            return $item;
        });
    }

    public static function findRecord($id)
    {
        $tbl = Kamustahan::find($id);
        if (!$tbl) {
            $tbl = new Kamustahan();
        }
        $answers = DB::connection('hrd2')->table('tbl201_kamustahan_ans')->where('kmstans_kmstid', $tbl->ekmst_id)->get()->mapWithKeys(fn($a) => ['kmstansq_' . $a->kmstans_question => $a->kmstans_ans]);
        // dd($answers);
        $tbl->answers = $answers;
        $tbl->remarks = Kamustahan::findRemarks($tbl->ekmst_id);

        return $tbl;
    }

    public static function findRemarks($kid)
    {
        return DB::connection('hrd2')->table('tbl_kamustahan_remarks')->where('kmstre_ekmstid', $kid)->get();
    }

    public static function store($data)
    {
        if ($data['id']) {
            Kamustahan::where('ekmst_id', $data['id'])->update([
                'ekmst_empno' => $data['empno'],
                'ekmst_interviewer' => $data['interviewer'],
                'ekmst_intvwdate' => $data['datetime'],
                'ekmst_pos' => $data['position'],
                'ekmst_dept' => $data['dept'],
                'ekmst_superior' => $data['superior'],
                'ekmst_timestamp' => now()
            ]);
        } else {
            $tbl = Kamustahan::create([
                'ekmst_empno' => $data['empno'],
                'ekmst_interviewer' => $data['interviewer'],
                'ekmst_intvwdate' => $data['datetime'],
                'ekmst_pos' => $data['position'],
                'ekmst_dept' => $data['dept'],
                'ekmst_superior' => $data['superior'],
                'ekmst_timestamp' => now()
            ]);

            $data['id'] = $tbl->ekmst_id;
        }
        
        foreach ($data['answers'] as $v) {
            if (DB::connection('hrd2')->table('tbl201_kamustahan_ans')
                ->where('kmstans_kmstid', $data['id'])
                ->where('kmstans_question', $v[0])
                ->count() > 0
            ) {
                DB::connection('hrd2')->update("UPDATE tbl201_kamustahan_ans SET kmstans_empno = ?, kmstans_ans = ?, kmstans_timestamp = ? WHERE kmstans_kmstid = ? AND kmstans_question = ?", [
                    $data['empno'],
                    $v[1],
                    now(),
                    $data['id'],
                    $v[0]
                ]);
            } else {
                DB::connection('hrd2')->insert("INSERT INTO tbl201_kamustahan_ans (kmstans_kmstid, kmstans_empno, kmstans_question, kmstans_ans, kmstans_timestamp) VALUES (?, ?, ?, ?, ?)", [
                    $data['id'],
                    $data['empno'],
                    $v[0],
                    $v[1],
                    now()
                ]);
            }
        }
    }

    public static function storeRemark($data)
    {
        DB::connection('hrd2')->insert("INSERT INTO tbl_kamustahan_remarks (kmstre_ekmstid, kmstre_empno, kmstre_remarks, kmstre_timestamp) VALUES (?, ?, ?, ?)", [
            $data['id'],
            $data['empno'],
            $data['remark'],
            now()
        ]);
    }
}
