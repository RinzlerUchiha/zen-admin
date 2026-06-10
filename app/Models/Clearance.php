<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Object_;

class Clearance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'hrd2';
    protected $table = 'db_ecf2.tbl_request';
    protected $primaryKey = 'ecf_id';
    public $timestamps = false;

    public static function showList($stat)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user_empno = $user->Emp_No;
        $viewall = $user->userAccess('ecfreq', 'viewitems', 'ECF');

        $departmentList = Setting::departmentList(0);

        $data = Clearance::where('ecf_status', $stat)
            ->orderBy('ecf_lastday', 'desc')
            ->select('ecf_id', 'ecf_no', 'ecf_name', 'ecf_company', 'ecf_outlet', 'ecf_lastday', 'ecf_dtcleared', 'ecf_empno', 'ecf_separation', 'ecf_resigndt', 'ecf_status', 'ecf_salholddt', 'ecf_dept')
            ->get()
            ->map(function($item) use($departmentList){
                $item->Dept_Name = $departmentList->where('Dept_Code', '=', $item->ecf_dept)->last()?->Dept_Name;
                return $item;
            });

        $categories = DB::connection('hrd2')->table('db_ecf2.tbl_req_category AS c')
            ->leftJoin('db_ecf2.tbl_category AS d', 'd.cat_id', '=', 'c.catstat_cat')
            ->whereIn('c.catstat_ecfid', $data->pluck('ecf_id'))
            ->orderBy('c.catstat_ecfid')
            ->orderBy('d.cat_priority')
            ->get();

        return $data->map(function ($v) use ($categories) {
            $v->cat_list = $categories->where('catstat_ecfid', '=', $v->ecf_id);
            return $v;
        })
            ->filter(function ($r) use ($stat, $user_empno, $viewall) {
                $cat = $r->cat_list->filter(fn($i) => $viewall || $r->ecf_reqby == $user_empno || $i->catstat_emp == $user_empno || strpos($i->cat_checker, $user_empno) !== false);
                $pending_cat = $cat->filter(fn($i) => empty($i->catstat_sign) || $i->catstat_stat == 'pending')->first();
                return (
                    ($stat == 'checked'
                        && $cat->filter(fn($i) => empty($i->catstat_sign) || $i->catstat_stat == 'pending')->count() == 0)
                    ||
                    ($stat == 'pending'
                        && $pending_cat
                        && $r->cat_list->filter(fn($i) => $i->cat_priority < $pending_cat?->cat_priority)->count() == 0)
                    ||
                    ($stat == 'cleared'
                        && $r->cat_list->filter(fn($i) => empty($i->catstat_sign) || $i->catstat_stat == 'pending')->count() == 0)
                    ||
                    !in_array($stat, ['pending', 'checked', 'cleared'])
                )
                    &&
                    ($cat->count() > 0
                        || $r->ecf_reqby == $user_empno
                        || $viewall);
            });
    }

    public static function store($data)
    {
        $salary_hold_date = '';
        if (date("d", strtotime($data['lastDay'])) == 11) {
            $salary_hold_date = date("F 26, Y", strtotime($data['lastDay'] . " -1 months")) . " - " . date("F 10, Y", strtotime($data['lastDay'])) . " and " . date("F d, Y", strtotime($data['lastDay']));
        } else if (date("d", strtotime($data['lastDay'])) == 26) {
            $salary_hold_date = date("F 11, Y", strtotime($data['lastDay'])) . " - " . date("F 25, Y", strtotime($data['lastDay'])) . " and " . date("F d, Y", strtotime($data['lastDay']));
        } else if (date("d", strtotime($data['lastDay'])) < 26 && date("d", strtotime($data['lastDay'])) > 11) {
            $salary_hold_date = date("F 26, Y", strtotime($data['lastDay'] . " -1 months")) . " - " . date("F 10, Y", strtotime($data['lastDay'])) . " and " . date("F 11, Y", strtotime($data['lastDay'])) . " - " . date("F d, Y", strtotime($data['lastDay']));
        } else if (date("d", strtotime($data['lastDay'])) > 26) {
            $salary_hold_date = date("F 11, Y", strtotime($data['lastDay'])) . " - " . date("F 25, Y", strtotime($data['lastDay'])) . " and " . date("F 26, Y", strtotime($data['lastDay'])) . " - " . date("F d, Y", strtotime($data['lastDay']));
        } else if (date("d", strtotime($data['lastDay'])) < 11) {
            $salary_hold_date = date("F 11, Y", strtotime($data['lastDay'] . " -1 months")) . " - " . date("F 25, Y", strtotime($data['lastDay'] . " -1 months")) . " and " . date("F 26, Y", strtotime($data['lastDay'] . " -1 months")) . " - " . date("F d, Y", strtotime($data['lastDay']));
        }

        $prefix = 'ECF-' . $data['jobrec']?->jrec_department;

        if ($data['id']) {
            $tbl = Clearance::find($data['id']);
            if (empty($data['saveAsDraft']) && !$tbl->ecf_no) {
                $last_ecf_no = str_replace($prefix, '', Clearance::where('ecf_status', '!=', 'draft')
                    ->whereRaw("REPLACE(ecf_no, ?, '') REGEXP '^[0-9]+$'", [$prefix])
                    ->orderbyRaw("REPLACE(ecf_no, ?, '') + 10 DESC", [$prefix])
                    ->select('ecf_no')
                    ->first()?->ecf_no);
                $tbl->ecf_no = $prefix . (str_pad(intval($last_ecf_no) + 1, 5, '0', STR_PAD_LEFT) );
            }
            $tbl->ecf_empno = $data['empno'];
            $tbl->ecf_name = trim(ucwords($data['persinfo']?->pers_firstname) . ' ' . getNameInitials($data['persinfo']?->pers_midname)) . ' ' . ucwords($data['persinfo']?->pers_lastname);
            $tbl->ecf_company = $data['jobrec']?->jrec_company;
            $tbl->ecf_dept = $data['jobrec']?->jrec_department;
            $tbl->ecf_outlet = $data['jobrec']?->jrec_outlet;
            $tbl->ecf_pos = $data['jobrec']?->jrec_position;

            $tbl->ecf_empstatus = $data['emplstat'];

            $tbl->ecf_lastday = $data['lastDay'];
            $tbl->ecf_separation = $data['separationType'];
            $tbl->ecf_reqby = Auth::user()->Emp_No;
            $tbl->ecf_reqdate = !empty($data['isPosted']) ? date('Y-m-d') : $tbl->ecf_reqdate;
            $tbl->ecf_salholddt = $salary_hold_date;
            $tbl->ecf_status = !empty($data['isPosted']) ? 'pending' : $tbl->ecf_status;
            $tbl->ecf_resigndt = $data['resignDate'];
            // $tbl->ecf_dtcleared = $data['lastDay'];
            $tbl->save();
        }else{
            if (empty($data['saveAsDraft'])) {
                $last_ecf_no = str_replace($prefix, '', Clearance::where('ecf_status', '!=', 'draft')
                    ->whereRaw("REPLACE(ecf_no, ?, '') REGEXP '^[0-9]+$'", [$prefix])
                    ->orderbyRaw("REPLACE(ecf_no, ?, '') + 10 DESC", [$prefix])
                    ->select('ecf_no')
                    ->first()?->ecf_no);
                $ecf_no = $prefix . (str_pad(intval($last_ecf_no) + 1, 5, '0', STR_PAD_LEFT) );
            }

            $data['id'] = Clearance::create([
                'ecf_empno' => $data['empno'],
                'ecf_no' => $ecf_no ?? '',
                'ecf_name' => trim(ucwords($data['persinfo']?->pers_firstname) . ' ' . getNameInitials($data['persinfo']?->pers_midname)) . ' ' . ucwords($data['persinfo']?->pers_lastname),
                'ecf_company' => $data['jobrec']?->jrec_company,
                'ecf_dept' => $data['jobrec']?->jrec_department,
                'ecf_outlet' => $data['jobrec']?->jrec_outlet,
                'ecf_pos' => $data['jobrec']?->jrec_position,
                'ecf_empstatus' => $data['emplstat'],
                'ecf_lastday' => $data['lastDay'],
                'ecf_separation' => $data['separationType'],
                'ecf_reqby' => Auth::user()->Emp_No,
                'ecf_reqdate' => !empty($data['isPosted']) ? date('Y-m-d 00:00:00') : null,
                'ecf_salholddt' => $salary_hold_date,
                'ecf_status' => !empty($data['isPosted']) ? 'pending' : 'draft',
                'ecf_resigndt' => $data['resignDate']
            ])?->ecf_id;
        }

        $is_pending = 0;
        if(!empty($data['id'])){
            DB::connection('hrd2')->table('db_ecf2.tbl_req_category')
            ->where('catstat_ecfid', $data['id'])
            ->whereNotIn('catstat_cat', array_column($data['checkerList'], 0))
            ->delete();

            foreach ($data['checkerList'] as $v) {
                $req_cat = DB::connection('hrd2')->table('db_ecf2.tbl_req_category')
                ->where('catstat_ecfid', $data['id'])
                ->where('catstat_cat', $v[0])
                ->orderByDesc('catstat_id')
                ->first();

                if($req_cat && $req_cat->catstat_emp != $v[1]){
                    DB::connection('hrd2')->table('db_ecf2.tbl_req_category')
                    ->where('catstat_id', $req_cat->catstat_id)
                    ->update([
                        'catstat_emp' => $v[1],
                        'catstat_ecfid' => $data['id'],
                        'catstat_dtchecked' => null,
                        'catstat_sign' => null,
                        'catstat_stat' => 'pending'
                    ]);
                    $is_pending = 1;
                }else if(!$req_cat){
                    DB::connection('hrd2')->table('db_ecf2.tbl_req_category')->insert([
                        'catstat_cat' => $v[0],
                        'catstat_emp' => $v[1],
                        'catstat_ecfid' => $data['id']
                    ]);
                }
            }
        }

        if($is_pending){
            Clearance::where('ecf_id', $data['id'])->whereNot('ecf_status', 'draft')->update(['ecf_status', 'pending']);
        }
    }

    public static function checkRequirements($data)
    {        
        $cat = DB::connection('hrd2')->table('db_ecf2.tbl_req_category')
        ->where('catstat_ecfid', $data['id'])
        ->where('catstat_id', $data['cat'])
        ->where('catstat_emp', Auth::user()->Emp_No)
        ->first();

        DB::connection('hrd2')->table('db_ecf2.tbl_req_category')
        ->where('catstat_id', $cat->catstat_id)
        ->update([
            'catstat_dtchecked' => now(),
            'catstat_sign' => $data['signature'] ?? null,
            'catstat_stat' => !in_array($data['stat'], ['cleared', 'uncleared']) ? $cat->catstat_stat : $data['stat']
        ]);

        foreach ($data['requirements'] as $v) {
            $req_item = DB::connection('hrd2')->table('db_ecf2.tbl_cat_req')
                ->where('catreq_ecfid', $data['id'])
                ->where('catreq_catstatid', $data['cat'])
                ->where('catreq_reqid', $v['reqid'])
                ->orderByDesc('catreq_id')
                ->first();

            if($req_item){
                DB::connection('hrd2')->table('db_ecf2.tbl_cat_req')
                ->where('catreq_id', $req_item->catreq_id)
                ->update([
                    'catreq_dtcleared' => $v['required'] && $v['date'] ? $v['date'] : null,
                    'catreq_clearedby' => $v['required'] && $v['verifiedby'] ? $v['verifiedby'] : null,
                    'catreq_remarks' => $v['required'] && $v['remarks'] ? $v['remarks'] : null,
                    'catreq_required' => $v['required']
                ]);
            }else{
                DB::connection('hrd2')->table('db_ecf2.tbl_cat_req')
                ->insert([
                    'catreq_ecfid' => $data['id'],
                    'catreq_catstatid' => $data['cat'],
                    'catreq_reqid' => $v['reqid'],
                    'catreq_dtcleared' => $v['required'] && $v['date'] ? $v['date'] : null,
                    'catreq_clearedby' => $v['required'] && $v['verifiedby'] ? $v['verifiedby'] : null,
                    'catreq_remarks' => $v['required'] && $v['remarks'] ? $v['remarks'] : null,
                    'catreq_required' => $v['required']
                ]);
            }
        }

        $cat_list = DB::connection('hrd2')->table('db_ecf2.tbl_req_category')->where('catstat_ecfid', $data['id'])->get();
        if($cat_list->count() > 0 && $cat_list->count() == $cat_list->where('catstat_stat', '=', 'cleared')->count()){
            Clearance::where('ecf_id', $data['id'])->update(['ecf_status' => 'cleared']);
        }
    }

    public static function setCategory($data)
    {
        if(!empty($data['id'])){
            DB::connection('hrd2')->table('db_ecf2.tbl_category')
            ->where('cat_id', $data['id'])
            ->update([
                'cat_title' => $data['title'],
                'cat_desc' => $data['desc'],
                'cat_company' => $data['company'],
                'cat_priority' => $data['priority'],
                'cat_order' => $data['order'],
                'cat_status' => $data['stat'],
                'cat_checker' => $data['checker']
            ]);
        }else{
            DB::connection('hrd2')->table('db_ecf2.tbl_category')->insert([
                'cat_title' => $data['title'],
                'cat_desc' => $data['desc'],
                'cat_company' => $data['company'],
                'cat_priority' => $data['priority'],
                'cat_order' => $data['order'],
                'cat_status' => $data['stat'],
                'cat_checker' => $data['checker']
            ]);
        }
    }

    public static function setRequirement($data)
    {
        if(!empty($data['id'])){
            DB::connection('hrd2')->table('db_ecf2.tbl_requirement')
            ->where('req_id', $data['id'])
            ->where('req_cat', $data['cat'])
            ->update([
                'req_name' => $data['name'],
                'req_status' => $data['stat']
            ]);

            return $data['id'];
        }else{
            return DB::connection('hrd2')->table('db_ecf2.tbl_requirement')->insertGetId([
                'req_cat' => $data['cat'],
                'req_name' => $data['name'],
                'req_status' => $data['stat']
            ], 'req_id');
        }
    }
}
