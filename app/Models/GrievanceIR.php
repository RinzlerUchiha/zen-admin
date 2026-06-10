<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrievanceIR extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'tbl_ir';
    protected $primaryKey = 'ir_id';
    public $timestamps = false;
    
    public static function loadList($stat, User $user)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $query = DB::connection('hrd2')->table('tbl_ir AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.ir_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.ir_to')
            ->leftJoin('tbl_ir_forward AS d', function($join) use($user) {
                $join->on('d.irf_irid', '=', 'a.ir_id')
                ->where('d.irf_to', $user->Emp_No);
            })
            ->whereRaw('FIND_IN_SET(ir_stat, ?) > 0', [$stat]);

        if(!$user->userAccess('grievance','review')){
            $query->where(function ($q) use($user) {
                $q->whereRaw("FIND_IN_SET(?, ir_from) > 0", [$user->Emp_No])
                ->orWhereRaw("(FIND_IN_SET(?, ir_cc) > 0 AND ir_stat != 'draft')", [$user->Emp_No])
                ->orWhereRaw("(FIND_IN_SET(?, ir_to) > 0 AND ir_stat != 'draft')", [$user->Emp_No])
                ->orWhereRaw("(d.irf_irid != '' AND d.irf_irid IS NOT NULL AND ir_stat != 'draft')");
            });
        }

        $query->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->orderBy('ir_date', 'desc');


        $list = $query->get()->map(function($item) use($persinfo){

            $item->from_name = isset($persinfo[$item->ir_from]) ? trim($persinfo[$item->ir_from]['pers_lastname'].", ".$persinfo[$item->ir_from]['pers_firstname']) : '';

            $item->to_name = isset($persinfo[$item->ir_to]) ? trim($persinfo[$item->ir_to]['pers_lastname'].", ".$persinfo[$item->ir_to]['pers_firstname']) : '';

            return $item;
        })->sortBy([
            ['ir_date', 'desc']
        ]);

        return $list;
    }

    public static function findIR($value, $column = 'ir_id')
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_ir AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.ir_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.ir_to')
            ->where($column, $value)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->get()
            ->map(function($item) use($persinfo){
                $item->from_name = isset($persinfo[$item->ir_from]) ? trim($persinfo[$item->ir_from]['pers_lastname'].", ".$persinfo[$item->ir_from]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->ir_to]) ? trim($persinfo[$item->ir_to]['pers_lastname'].", ".$persinfo[$item->ir_to]['pers_firstname']) : '';

                return $item;
            });

        // // If no result found, throw ModelNotFoundException
        // if (!$data) {
        //     throw new ModelNotFoundException("Record not found.");
        // }

        return $data;
    }

    public static function find13AOfIR($ir)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = DB::connection('hrd2')->table('tbl_13a AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.13a_from')
            // ->leftJoin('tbl201_persinfo AS c', 'c.pers_empno', '=', 'a.13a_to')
            ->whereRaw('FIND_IN_SET(?, 13a_ir) > 0', [$ir])
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS from_name')])
            // ->addSelect([DB::raw('TRIM(CONCAT(c.pers_lastname, ", ", c.pers_firstname)) AS to_name')])
            ->get()
            ->map(function($item) use($persinfo){
                $item->from_name = isset($persinfo[$item->{'13a_from'}]) ? trim($persinfo[$item->{'13a_from'}]['pers_lastname'].", ".$persinfo[$item->{'13a_from'}]['pers_firstname']) : '';

                $item->to_name = isset($persinfo[$item->{'13a_to'}]) ? trim($persinfo[$item->{'13a_to'}]['pers_lastname'].", ".$persinfo[$item->{'13a_to'}]['pers_firstname']) : '';

                return $item;
            });

        return $data;
    }

    public static function findForwardList($ir)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        return DB::connection('hrd2')->table('tbl_ir_forward AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.irf_to')
            ->where('irf_irid', $ir)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS forwardedTo')])
            ->get()
            ->map(function($item) use($persinfo){
                $item->forwardedTo = isset($persinfo[$item->irf_to]) ? trim($persinfo[$item->irf_to]['pers_lastname'].", ".$persinfo[$item->irf_to]['pers_firstname']) : '';

                return $item;
            });
    }

    public static function findRemarks($ir)
    {
        $persinfo = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        return DB::connection('hrd2')->table('tbl_grievance_remarks AS a')
            // ->leftJoin('tbl201_persinfo AS b', 'b.pers_empno', '=', 'a.gr_empno')
            ->where('gr_type', 'ir')
            ->where('gr_typeid', $ir)
            ->select('a.*')
            // ->addSelect([DB::raw('TRIM(CONCAT(b.pers_lastname, ", ", b.pers_firstname)) AS comment_by')])
            ->get()
            ->map(function($item) use($persinfo){
                $item->comment_by = isset($persinfo[$item->gr_empno]) ? trim($persinfo[$item->gr_empno]['pers_lastname'].", ".$persinfo[$item->gr_empno]['pers_firstname']) : '';

                return $item;
            });
    }

    public static function findAttachments($ir)
    {
        $data = DB::connection('hrd2')->table('tbl_ir_attachment AS a')
        ->where('ira_irid', $ir)
        ->get();

         $data->map(function ($item) {
            $item->fileType = pathinfo(basename($item->ira_content),PATHINFO_EXTENSION);
            return $item;
        });

        return $data;
    }

    public static function saveAttachment($data) {
        DB::connection('hrd2')->table('tbl_ir_attachment')->insert([
            'ira_irid' => $data['ir'],
            'ira_type' => $data['attach_type'],
            'ira_content' => $data['file'],
            'ira_auditdate' => $data['audit_date']
        ]);
    }

    public static function deleteAttachment($ir, $id) {
        DB::connection('hrd2')->table('tbl_ir_attachment')
        ->where('ira_id', '=', $id)
        ->where('ira_irid', '=', $ir)
        ->delete();
    }

    public static function deleteAttachmentByIR($ir) {
        DB::connection('hrd2')->table('tbl_ir_attachment')
        ->where('ira_irid', '=', $ir)
        ->delete();
    }


    public static function saveRemark($data) {
        DB::connection('hrd2')->table('tbl_grievance_remarks')->insert([
            'gr_empno' => $data['empno'],
            'gr_type' => 'ir',
            'gr_typeid' => $data['ir'],
            'gr_remarks' => $data['remarks'],
            'gr_timestamp' => now()
        ]);
    }

    public static function deleteRemarkByIR($ir) {
        DB::connection('hrd2')->table('tbl_grievance_remarks')
        ->where('gr_type', '=', 'ir')
        ->where('gr_typeid', '=', $ir)
        ->delete();
    }

    public static function saveForward($data) {
        DB::connection('hrd2')->table('tbl_ir_forward')->insert([
            'irf_to' => $data['to'],
            'irf_irid' => $data['ir']
        ]);
    }

    public static function deleteForwardByIR($ir) {
        DB::connection('hrd2')->table('tbl_ir_forward')
        ->where('irf_irid', '=', $ir)
        ->delete();
    }
}
