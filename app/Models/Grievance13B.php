<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Grievance13B extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'tbl_13b';
    protected $primaryKey = '13b_id';
    public $timestamps = false;

    public static function loadList($stat, User $user)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $reviewer = $user->userAccess('grievance', 'review');
        // $reviewer = true;
        $query = DB::connection('hrd2')->table('tbl_13b AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13b_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13b_to')
            ->whereRaw('FIND_IN_SET(13b_stat, ?) > 0', [$stat]);

        if (!$reviewer) {
            $query->whereRaw("FIND_IN_SET(?, CONCAT_WS(',',13b_to, 13b_cc, 13b_from, 13b_issuedby, 13b_notedby)) > 0", [$user->Emp_No]);
        }

        $query->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->orderBy('13b_date', 'desc');

        $data = $query->get()->map(function($item) use($persinfo){
                $item->from_name = isset($persinfo[$item->{'13b_from'}]) ? trim($persinfo[$item->{'13b_from'}]['pers_lastname'].", ".$persinfo[$item->{'13b_from'}]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->{'13b_to'}]) ? trim($persinfo[$item->{'13b_to'}]['pers_lastname'].", ".$persinfo[$item->{'13b_to'}]['pers_firstname']) : '';

                return $item;
            });

        $filteredData = $data->filter(function ($d) use ($user, $reviewer) {

            $sign_issued = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13b_id'}}' AND gs_type='13b' AND gs_signtype='issued'"));

            $sign_noted = count(DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13b_id'}}' AND gs_type='13b' AND gs_signtype='reviewed' AND gs_empno='{$user->Emp_No}'"));

            $sign_witness = (DB::connection('hrd2')->select("SELECT gs_sign, gs_empno FROM tbl_grievance_sign WHERE gs_typeid='{$d->{'13b_id'}}' AND gs_type='13b' AND gs_signtype='witness' AND gs_empno='{$user->Emp_No}'"));

            if ($d->{'13b_stat'} == "checked" && $sign_noted > 0) {
                $d->{'13b_stat'} = "reviewed";
            }

            $remarks = DB::connection('hrd2')->table('tbl_grievance_remarks AS a')
                ->where([
                    ['gr_typeid', '=', $d->{'13b_id'}],
                    ['gr_type', '=', '13b']
                ])
                ->orderBy('gr_id', 'desc')
                ->first()->gr_remarks ?? '';

            $d->remarks = $remarks;

            return (
                $user->Emp_No == $d->{'13b_from'} ||
                strpos($d->{'13b_cc'}, $user->Emp_No) !== false ||
                ($reviewer && $d->{'13b_stat'} != "draft") ||
                ($user->Emp_No == $d->{'13b_issuedby'} &&
                    ($d->{'13b_stat'} == "reviewed" ||
                        $d->{'13b_stat'} == "refused" ||
                        $d->{'13b_stat'} == "received" ||
                        $sign_issued == 0)
                ) ||
                ($sign_issued > 0 && $d->{'13b_stat'} == "pending" && strpos($d->{'13b_notedby'}, $user->Emp_No) !== false && $sign_noted == 0) ||
                ($d->{'13b_stat'} == "refused" && strpos($d->{'13b_witness'}, $user->Emp_No) !== false) ||
                ($user->Emp_No == $d->{'13b_to'} &&
                    ($d->{'13b_stat'} == "issued" ||
                        $d->{'13b_stat'} == "received" ||
                        $d->{'13b_stat'} == "refused")
                )
            );
        });

        return $filteredData;
    }

    public static function find13B($value, $column = '13b_id')
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_13b AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13b_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13b_to')
            ->where($column, $value)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->get()
            ->map(function($item) use($persinfo){
                $item->from_name = isset($persinfo[$item->{'13b_from'}]) ? trim($persinfo[$item->{'13b_from'}]['pers_lastname'].", ".$persinfo[$item->{'13b_from'}]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->{'13b_to'}]) ? trim($persinfo[$item->{'13b_to'}]['pers_lastname'].", ".$persinfo[$item->{'13b_to'}]['pers_firstname']) : '';

                return $item;
            });

        return $data;
    }

    public static function findBy13AOf13B($id)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_13b AS a')
            ->join('tbl_13a AS b', DB::raw('FIND_IN_SET(b.13a_id, 13b_13a)'), '>', DB::raw('0'))
            ->where('13b_id', $id)
            ->select('b.*')
            ->get()
            ->map(function($item) use($persinfo){
                $item->from_name = isset($persinfo[$item->{'13a_from'}]) ? trim($persinfo[$item->{'13a_from'}]['pers_lastname'].", ".$persinfo[$item->{'13a_from'}]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->{'13a_to'}]) ? trim($persinfo[$item->{'13a_to'}]['pers_lastname'].", ".$persinfo[$item->{'13a_to'}]['pers_firstname']) : '';

                return $item;
            });

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
            ->where('gr_type', '13b')
            ->where('gr_typeid', $id)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS comment_by')])
            ->get()
            ->map(function ($item) use ($persinfo) {
                $item->comment_by = isset($persinfo[$item->gr_empno]) ? trim($persinfo[$item->gr_empno]['pers_lastname'] . ", " . $persinfo[$item->gr_empno]['pers_firstname']) : '';

                return $item;
            });
    }
    public static function find13BSignatures($id)
    {
        return DB::connection('hrd2')->table('tbl_grievance_sign AS a')
            ->where('gs_typeid', $id)
            ->where('gs_type', '13b')
            ->get();
    }
    public static function sign13B($data)
    {
        DB::connection('hrd2')->table('tbl_grievance_sign')
            ->updateOrInsert(
                [
                    'gs_empno' => $data['empno'],
                    'gs_type' => '13b',
                    'gs_typeid' => $data['id'],
                    'gs_signtype' => $data['signtype']
                ],
                ['gs_sign' => $data['sign']]
            );

        if (!$tbl = Grievance13B::find($data['id'])) {
            return;
        }

        if ($data['signtype'] == 'reviewed') {
            $notedby = $tbl->{'13b_notedby'} ?? '';
            $notedby_cnt = count(explode(',', $notedby));
            $sign = DB::connection('hrd2')->table('tbl_grievance_sign')
                ->where([
                    ['gs_type', '=', '13b'],
                    ['gs_typeid', '=', $data['id']],
                    ['gs_signtype', '=', 'reviewed']
                ])
                ->whereRaw("FIND_IN_SET(gs_empno, ?) > 0", $notedby)
                ->get();

            if ($notedby_cnt != $sign->count()) {
                return;
            }

            $tbl->update([
                '13b_stat' => 'reviewed',
                '13b_read' => ''
            ]);
        } else if ($data['signtype'] == 'received') {
            $tbl->update([
                '13b_stat' => 'received',
                '13b_datereceived' => now(),
                '13b_receivedby' => $data['empno'],
                '13b_read' => $data['empno']
            ]);
        }
    }
}
