<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class Grievance13A extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'tbl_13a';
    protected $primaryKey = '13a_id';
    public $timestamps = false;

    public static function loadList($stat, User $user)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $reviewer = $user->userAccess('grievance', 'review');
        // $reviewer = true;
        $query = DB::connection('hrd2')->table('tbl_13a AS a')
            ->leftJoin('tbl_13a_reply', '13ar_13aid', '=', 'a.13a_id')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13a_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13a_to')
            ->whereRaw('FIND_IN_SET(13a_stat, ?) > 0', [$stat]);

        if (!$reviewer) {
            $query->whereRaw("FIND_IN_SET(?, CONCAT_WS(',',13a_to, 13a_cc, 13a_from, 13a_issuedby, 13a_notedby)) > 0", [$user->Emp_No]);
        }

        // $query->select('a.*')
        // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
        // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
        $query->orderBy('13a_date', 'desc');

        $data = $query->get()->map(function ($item) use ($persinfo) {
            $item->from_name = isset($persinfo[$item->{'13a_from'}]) ? trim($persinfo[$item->{'13a_from'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_from'}]['pers_firstname']) : '';

            $item->to_name = isset($persinfo[$item->{'13a_to'}]) ? trim($persinfo[$item->{'13a_to'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_to'}]['pers_firstname']) : '';

            return $item;
        });

        $filteredData = $data->filter(function ($d) use ($user, $reviewer) {
            $cnt13b = DB::connection('hrd2')->table('tbl_13b AS a')->where('13b_13a', $d->{'13a_id'})->count();

            $sign_issued = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13a_id'}}' AND gs_type='13a' AND gs_signtype='issued'"));

            $sign_noted = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13a_id'}}' AND gs_type='13a' AND gs_signtype='reviewed' AND gs_empno='{$user->Emp_No}'"));

            $sign_witness = (DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13a_id'}}' AND gs_type='13a' AND gs_signtype='witness' AND gs_empno='{$user->Emp_No}'"));

            if ($d->{'13a_stat'} == "checked" && $sign_noted > 0) {
                $d->{'13a_stat'} = "reviewed";
            }

            $remarks = DB::connection('hrd2')->table('tbl_grievance_remarks AS a')
                ->where([
                    ['gr_typeid', '=', $d->{'13a_id'}],
                    ['gr_type', '=', '13a']
                ])
                ->orderBy('gr_id', 'desc')
                ->first()->gr_remarks ?? '';

            $d->remarks = $remarks;

            return (
                $user->Emp_No == $d->{'13a_from'} ||
                strpos($d->{'13a_cc'}, $user->Emp_No) !== false ||
                ($reviewer && $d->{'13a_stat'} != "draft") ||
                ($user->Emp_No == $d->{'13a_issuedby'} &&
                    ($d->{'13a_stat'} == "reviewed" ||
                        $d->{'13a_stat'} == "refused" ||
                        $d->{'13a_stat'} == "received" ||
                        $sign_issued == 0)
                ) ||
                ($sign_issued > 0 && $d->{'13a_stat'} == "checked" && strpos($d->{'13a_notedby'}, $user->Emp_No) !== false && $sign_noted == 0) ||
                ($d->{'13a_stat'} == "reviewed" && strpos($d->{'13a_notedby'}, $user->Emp_No) !== false) ||
                ($d->{'13a_stat'} == "refused" && strpos($d->{'13a_witness'}, $user->Emp_No) !== false) ||
                ($user->Emp_No == $d->{'13a_to'} &&
                    ($d->{'13a_stat'} == "issued" ||
                        $d->{'13a_stat'} == "received" ||
                        $d->{'13a_stat'} == "refused")
                )
            );
        });

        return $filteredData;
    }

    public static function find13A($value, $column = '13a_id')
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_13a AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13a_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13a_to')
            // ->leftJoin('tbl201_persinfo AS d', 'd.pers_empno', '=', 'a.13a_from')
            // ->leftJoin('tbl201_persinfo AS e', 'e.pers_empno', '=', 'a.13a_to')
            ->where($column, $value)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            // ->addSelect([DB::raw("CONCAT(TRIM(CONCAT(b.pers_firstname, ' ', REGEXP_REPLACE(b.pers_midname, '(?<=\\b\\w)(?:\\w|-)+', '.'))), ' ', b.pers_lastname) AS from_name_init")])
            // ->addSelect([DB::raw("CONCAT(TRIM(CONCAT(c.pers_firstname, ' ', REGEXP_REPLACE(c.pers_midname, '(?<=\\b\\w)(?:\\w|-)+', '.'))), ' ', c.pers_lastname) AS to_name_init")])
            ->get()
            ->map(function ($item) use ($persinfo) {
                $item->from_name = isset($persinfo[$item->{'13a_from'}]) ? trim($persinfo[$item->{'13a_from'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_from'}]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->{'13a_to'}]) ? trim($persinfo[$item->{'13a_to'}]['pers_lastname'] . ", " . $persinfo[$item->{'13a_to'}]['pers_firstname']) : '';

                return $item;
            });

        // If no result found, throw ModelNotFoundException
        // if (!$data) {
        //     throw new ModelNotFoundException("Record not found.");
        // }

        return $data;
    }

    public static function findByIROf13A($id)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_13a AS a')
            ->join('tbl_ir AS ir', DB::raw('FIND_IN_SET(ir_id, 13a_ir)'), '>', DB::raw('0'))
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'ir.ir_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'ir.ir_to')
            ->where('13a_id', $id)
            ->select('ir.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->get()
            ->map(function ($item) use ($persinfo) {
                $item->from_name = isset($persinfo[$item->ir_from]) ? trim($persinfo[$item->ir_from]['pers_lastname'] . ", " . $persinfo[$item->ir_from]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->ir_to]) ? trim($persinfo[$item->ir_to]['pers_lastname'] . ", " . $persinfo[$item->ir_to]['pers_firstname']) : '';

                return $item;
            });

        return $data;
    }

    public static function findBy13BOf13A($id)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_13b AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13b_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13b_to')
            ->whereRaw('FIND_IN_SET(?, 13b_13a) > 0', [$id])
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->first();

        if ($data) {
            $data->from_name = isset($persinfo[$data->{'13b_from'}]) ? trim($persinfo[$data->{'13b_from'}]['pers_lastname'] . ", " . $persinfo[$data->{'13b_from'}]['pers_firstname']) : '';

            $data->to_name = isset($persinfo[$data->{'13b_to'}]) ? trim($persinfo[$data->{'13b_to'}]['pers_lastname'] . ", " . $persinfo[$data->{'13b_to'}]['pers_firstname']) : '';
        }

        return $data;
    }

    public static function findRemarks($id)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        return DB::connection('hrd2')->table('tbl_grievance_remarks AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.gr_empno')
            ->where('gr_type', '13a')
            ->where('gr_typeid', $id)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS comment_by')])
            ->get()
            ->map(function ($item) use ($persinfo) {
                $item->comment_by = isset($persinfo[$item->gr_empno]) ? trim($persinfo[$item->gr_empno]['pers_lastname'] . ", " . $persinfo[$item->gr_empno]['pers_firstname']) : '';

                return $item;
            });
    }

    public static function findViolation13A($id)
    {
        return DB::connection('hrd2')->table('tbl_13a_violation AS a')->where('13av_13a', $id)->get();
    }

    public static function find13ASignatures($id)
    {
        return DB::connection('hrd2')->table('tbl_grievance_sign AS a')
            ->where('gs_typeid', $id)
            ->where('gs_type', '13a')
            ->get();
    }

    public static function find13AReply($id)
    {
        return DB::connection('hrd2')->table('tbl_13a_reply AS a')
            ->where('13ar_13aid', $id)
            ->first();
    }

    public static function find13AHearingTranscript($id)
    {
        return DB::connection('hrd2')->table('tbl_hearing_transcript AS a')
            ->where('ht_13a', $id)
            ->first();
    }

    public static function find13ACommitmentPlan($id)
    {
        return DB::connection('hrd2')->table('tbl_commitment_plan AS a')
            ->where('commit_13a', $id)
            ->first();
    }

    public static function save13AViolations($data, $_13a)
    {
        $tbl = DB::connection('hrd2')->table('tbl_13a_violation');
        $idList = array_filter(array_column($data, 'vid'));
        if (!empty($idList)) {
            $tbl->where('13av_13a', $_13a)
                ->whereNotIn('13av_id', $idList)
                ->delete();
        }
        foreach ($data as $v) {
            if ($v['vid']) {
                $tbl->where([
                    ['13av_13a', '=', $_13a],
                    ['13av_id', '=', $v['vid']]
                ])
                    ->update(
                        [
                            '13av_article' => $v['articleCode'],
                            '13av_section' => $v['sectionCode'],
                            '13av_articlename' => $v['articleName'],
                            '13av_sectionname' => $v['sectionName'],
                            '13av_desc' => $v['sectionDesc'],
                            '13av_13a' => $_13a,
                            '13av_othersrc' => $v['othersrc']
                        ]
                    );
            } else {
                $tbl->insert([
                    '13av_article' => $v['articleCode'],
                    '13av_section' => $v['sectionCode'],
                    '13av_articlename' => $v['articleName'],
                    '13av_sectionname' => $v['sectionName'],
                    '13av_desc' => $v['sectionDesc'],
                    '13av_13a' => $_13a,
                    '13av_othersrc' => $v['othersrc']
                ]);
            }
        }
    }

    public static function sign13A($data)
    {
        DB::connection('hrd2')->table('tbl_grievance_sign')
            ->updateOrInsert(
                [
                    'gs_empno' => $data['empno'],
                    'gs_type' => '13a',
                    'gs_typeid' => $data['id'],
                    'gs_signtype' => $data['signtype']
                ],
                ['gs_sign' => $data['sign']]
            );

        if (!$tbl = Grievance13A::find($data['id'])) {
            return;
        }

        if ($data['signtype'] == 'reviewed') {
            $notedby = $tbl->{'13a_notedby'} ?? '';
            $notedby_cnt = count(explode(',', $notedby));
            $sign = DB::connection('hrd2')->table('tbl_grievance_sign')
                ->where([
                    ['gs_type', '=', '13a'],
                    ['gs_typeid', '=', $data['id']],
                    ['gs_signtype', '=', 'reviewed']
                ])
                ->whereRaw("FIND_IN_SET(gs_empno, ?) > 0", $notedby)
                ->get();

            if ($notedby_cnt != $sign->count()) {
                return;
            }

            $tbl->update([
                '13a_stat' => 'reviewed',
                '13a_read' => ''
            ]);
        } else if ($data['signtype'] == 'received') {
            $tbl->update([
                '13a_stat' => 'received',
                '13a_datereceived' => now(),
                '13a_receivedby' => $data['empno'],
                '13a_read' => $data['empno']
            ]);
        }
    }

    public static function explain13A($data)
    {
        $insert = DB::connection('hrd2')->table('tbl_grievance_remarks')
            ->insert([
                'gr_empno' => $data['empno'],
                'gr_type' => '13a',
                'gr_typeid' => $data['id'],
                'gr_remarks' => $data['remarks']
            ]);

        if (!($insert && $tbl = Grievance13A::find($data['id']))) {
            return;
        }

        $tbl->update([
            '13a_stat' => 'needs explanation',
            '13a_read' => ''
        ]);
    }
}
