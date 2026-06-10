<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function companyList($active = 1)
    {
        $query = DB::table('tbl_company')
            ->where('C_owned', '=', 'True');
        if ($active == 1) {
            $query->where('C_Remarks', '=', 'Active');
        }
        $query->orderBy('C_Name', 'asc');
        return $query->get();
    }

    public static function saveCompany($request)
    {
        DB::table('tbl_company')->insert([
            'C_Code' => $request['code'],
            'C_Name' => $request['name'],
            'C_Description' => $request['description'] ?? null,
            'C_tin' => $request['tin'] ?? null,
            'C_sss' => $request['sss'] ?? null,
            'C_phic' => $request['phic'] ?? null,
            'C_hdmf' => $request['hdmf'] ?? null,
            'C_address' => $request['address'] ?? null,
            'C_owned' => $request['owned'] ?? null,
            'C_Remarks' => $request['status']
        ]);
    }

    public static function departmentList($active = 1)
    {
        $query = DB::table('tbl_department');
        if ($active == 1) {
            $query->where('Dept_Stat', '=', 'active');
        }
        $query->orderBy('Dept_Name', 'asc');
        return $query->get();
    }

    public static function saveDepartment($request)
    {
        DB::table('tbl_department')->insert([
            'Dept_Code' => $request['code'],
            'Dept_Name' => $request['name'],
            'Dept_Description' => $request['description'] ?? null,
            'Dept_Stat' => $request['status']
        ]);
    }

    public static function sectionList($active = 1)
    {
        $query = DB::table('tbl_section');
        if ($active == 1) {
            $query->where('sec_stat', '=', 'active');
        }
        $query->orderBy('sec_name', 'asc');
        return $query->get();
    }

    public static function positionList($active = 1)
    {
        $query = DB::table('tbl_jobdescription');
        if ($active == 1) {
            $query->where('jd_stat', '=', 'active');
        }
        $query->orderBy('jd_title', 'asc');
        return $query->get();
    }

    public static function savePosition($request)
    {
        DB::table('tbl_jobdescription')->insert([
            'jd_code' => $request['code'],
            'jd_title' => $request['name'],
            'jd_summary' => $request['summary'] ?? null,
            'jd_duties' => $request['duties'] ?? null,
            'jd_specification' => $request['specification'] ?? null,
            'jd_stat' => $request['status']
        ]);
    }

    public static function jobSpecList($active = 1, $pos = null)
    {
        $query = DB::table('tbl_jobspec')
        ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jspec_position')
        ->leftJoin('tbl_department', 'Dept_Code', '=', 'jspec_department')
        ->leftJoin('tbl_section', 'sec_code', '=', 'jspec_section');
        if ($active == 1) {
            $query->where('jd_stat', '=', 'active');
        }
        if ($pos) {
            $query->where('jspec_position', '=', $pos);
        }
        $query->orderBy('jspec_department', 'asc')
        ->orderBy('jd_title', 'asc');
        return $query->get();
    }

    public static function jobGradeList()
    {
        $query = DB::table('tbl_jobgrade');
        return $query->get();
    }

    public static function jobStepList()
    {
        return collect(['Trainee', 'PROB', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']);
    }

    public static function areaList($active = 1)
    {
        $query = DB::table('tbl_area');
        if ($active == 1) {
            $query->where('Area_stat', '=', 'active');
        }
        $query->orderBy('Area_Name', 'asc');
        return $query->get();
    }

    public static function saveArea($request)
    {
        DB::table('tbl_area')->insert([
            'Area_Code' => $request['code'],
            'Area_Name' => $request['name'],
            'Area_Description' => $request['description'] ?? null,
            'Area_stat' => $request['status']
        ]);
    }

    public static function outletList($active = 1)
    {
        $query = DB::table('tbl_outlet');
        if ($active == 1) {
            $query->where('OL_stat', '=', 'active');
        }
        $query->orderBy('OL_Name', 'asc');
        return $query->get();
    }

    public static function saveOutlet($request)
    {
        DB::table('tbl_outlet')->insert([
            'OL_Code' => $request['code'],
            'OL_Name' => $request['name'],
            'Area_Code' => $request['area'],
            'OL_opendt' => $request['openingdt'] ?? null,
            'OL_closedt' => $request['closingdt'] ?? null,
            'OL_Size' => $request['size'],
            'OL_Type' => $request['type'],
            'OL_stat' => $request['status']
        ]);
    }

    public static function eventsList()
    {
        // code...
    }

    public static function provinceList($active = 1)
    {
        $query = DB::table('tbl_province');
        if ($active == 1) {
            $query->where('pr_status', '=', '1');
        }
        $query->orderBy('pr_name', 'asc');
        return $query->get();
    }

    public static function saveProvince($request)
    {
        DB::table('tbl_province')->insert([
            'pr_id' => $request['id'],
            'pr_code' => $request['code'],
            'pr_name' => $request['name'],
            'pr_status' => $request['status']
        ]);
    }

    public static function municipalityList($active = 1)
    {
        $query = DB::table('tbl_municipality as a')
        ->leftJoin('tbl_province as b', 'pr_code', '=', 'ct_province')
        ->select('a.*', 'b.pr_name as ct_province_name');
        if ($active == 1) {
            $query->where('ct_status', '=', '1');
        }
        $query->orderBy('ct_name', 'asc');
        return $query->get();
    }

    public static function saveMunicipality($request)
    {
        DB::table('tbl_municipality')->insert([
            'ct_id' => $request['id'],
            'ct_province' => $request['province'],
            'ct_name' => $request['name'],
            'ct_status' => $request['status']
        ]);
    }

    public static function barangayList($active = 1)
    {
        $query = DB::table('tbl_barangay as a')
        ->leftJoin('tbl_municipality as b', 'ct_id', '=', 'br_city')
        ->select('a.*', 'b.ct_name as br_city_name');
        if ($active == 1) {
            $query->where('br_status', '=', '1');
        }
        $query->orderBy('br_name', 'asc');
        return $query->get();
    }

    public static function saveBarangay($request)
    {
        DB::table('tbl_barangay')->insert([
            'br_id' => $request['id'],
            'br_city' => $request['city'],
            'br_name' => $request['name'],
            'br_status' => $request['status']
        ]);
    }

    public static function skillsCategoryList($active = 1)
    {
        $query = DB::table('tbl_skill_category');
        if ($active == 1) {
            $query->where('sc_stat', '=', '1');
        }
        $query->orderByRaw("IF(sc_id = 7, 1, 0) asc");
        $query->orderBy('sc_title', 'asc');
        return $query->get();
    }

    public static function skillsList($active = 1)
    {
        $query = DB::table('tbl_skill_type');
        if ($active == 1) {
            $query->where('status', '=', '1');
        }
        $query->orderBy('skill_name', 'asc');
        return $query->get();
    }

    public static function emplStatusList()
    {
        $query = DB::connection('hrd2')->table('tbl_empstatus');
        $query->orderBy('es_name', 'asc');
        return $query->get();
    }

    public static function rnrList()
    {
        $sections = DB::connection('hrd2')->table('tbl_rnr_sec')->get();
        $articles = DB::connection('hrd2')->table('tbl_rnr_article')->get();
        $articles->map(function($article) use($sections) {
            $article->sections = $sections->where('rnrsec_articleid', $article->rnrart_id);
        });
        return $articles;
    }

    public static function leaveBalance($type = '')
    {
        return collect(DB::connection('hrd2')->select("SELECT 
                bi_empno,
                bi_emplname,
                bi_empfname,
                
                -- First anniversary date
                DATE_ADD(ji_datehired, INTERVAL 1 YEAR) AS first_anniv_date,
                
                -- First anniversary year
                YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) AS first_anniv_year,

                -- Years employed
                TIMESTAMPDIFF(YEAR, ji_datehired, CURDATE()) AS years_employed,

                -- This year's entitlement
                CASE
                    WHEN CURDATE() < DATE_ADD(ji_datehired, INTERVAL 1 YEAR) THEN
                        0  -- Not eligible
                    WHEN YEAR(CURDATE()) = YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                        -- First anniversary this year → prorated
                        ROUND((12 - MONTH(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) + 1) / 12 * 9)
                    ELSE
                        9
                END AS entitlement,

                -- Last year's entitlement (for carryover)
                CASE
                    WHEN YEAR(CURDATE()) - 1 = YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                        -- First anniversary was last year → use prorated value
                        ROUND((12 - MONTH(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) + 1) / 12 * 9)
                    WHEN YEAR(CURDATE()) - 1 > YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                        9
                    ELSE
                        0  -- Not eligible yet last year
                END AS last_year_entitlement,

                -- Carryover: unused leave from last year (can be used until March 31 this year)
                CASE 
                    WHEN CURDATE() <= CONCAT(YEAR(CURDATE()), '-03-31') THEN
                        GREATEST(
                            0,
                            CASE
                                WHEN YEAR(CURDATE()) - 1 = YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                                    ROUND((12 - MONTH(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) + 1) / 12 * 9)
                                WHEN YEAR(CURDATE()) - 1 > YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                                    9
                                ELSE
                                    0
                            END - IFNULL(lt_last_year.total_used, 0)
                        )
                    ELSE
                        0
                END AS carryover,

                -- Leave used this year
                IFNULL(lt_this_year.total_used, 0) AS used_this_year,

                -- Final balance = entitlement + carryover - used
                (
                    CASE
                        WHEN CURDATE() < DATE_ADD(ji_datehired, INTERVAL 1 YEAR) THEN
                            0
                        WHEN YEAR(CURDATE()) = YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                            ROUND((12 - MONTH(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) + 1) / 12 * 9)
                        ELSE
                            9
                    END
                    +
                    CASE 
                        WHEN CURDATE() <= CONCAT(YEAR(CURDATE()), '-03-31') THEN
                            GREATEST(
                                0,
                                CASE
                                    WHEN YEAR(CURDATE()) - 1 = YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                                        ROUND((12 - MONTH(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) + 1) / 12 * 9)
                                    WHEN YEAR(CURDATE()) - 1 > YEAR(DATE_ADD(ji_datehired, INTERVAL 1 YEAR)) THEN
                                        9
                                    ELSE
                                        0
                                END - IFNULL(lt_last_year.total_used, 0)
                            )
                        ELSE
                            0
                    END
                    -
                    IFNULL(lt_this_year.total_used, 0)
                ) AS leave_balance

            FROM tbl201_basicinfo e
            JOIN tbl201_jobinfo ON ji_empno = bi_empno AND ji_remarks = 'Active'

            -- Leave used THIS YEAR
            LEFT JOIN (
                SELECT la_empno, SUM(la_days) AS total_used
                FROM tbl201_leave
                JOIN tbl_timeoff ON timeoff_name = la_type AND timeoff_name = :type
                WHERE la_start BETWEEN DATE_FORMAT(CURDATE(), '%Y-01-01') AND CURDATE()
                AND la_end BETWEEN DATE_FORMAT(CURDATE(), '%Y-01-01') AND CURDATE()
                GROUP BY la_empno
            ) lt_this_year ON bi_empno = lt_this_year.la_empno

            -- Leave used LAST YEAR (entitlement) before March 31 THIS YEAR
            LEFT JOIN (
                SELECT la_empno, SUM(la_days) AS total_used
                FROM tbl201_leave
                JOIN tbl_timeoff ON timeoff_name = la_type AND timeoff_name = :type
                WHERE la_start BETWEEN DATE_FORMAT(CURDATE() - INTERVAL 1 YEAR, '%Y-01-01')
                                    AND CONCAT(YEAR(CURDATE()), '-03-31')
                AND la_end BETWEEN DATE_FORMAT(CURDATE() - INTERVAL 1 YEAR, '%Y-01-01')
                                    AND CONCAT(YEAR(CURDATE()), '-03-31')
                GROUP BY la_empno
            ) lt_last_year ON bi_empno = lt_last_year.la_empno

            -- Only eligible employees (1 year or more)
            WHERE 
            e.datastat = 'current'
            AND bi_empno NOT LIKE '%SO-%'
            AND CURDATE() >= DATE_ADD(ji_datehired, INTERVAL 1 YEAR);
        ", [':type' => $type]));
    }

    public static function assignmentList($type)
    {
        $queryemp = DB::table('tbl201_persinfo as a')
        ->leftJoin('tbl201_jobinfo as b', 'b.ji_empno', '=', 'a.pers_empno')
        ->leftJoin('tbl_user2 as c', 'c.Emp_No', '=', 'a.pers_empno')
        ->get();

        $arremp = $queryemp->mapWithKeys(function($i) {
            return [
                $i->pers_empno => [
                    "name" => trim($i->pers_lastname." ".$i->pers_suffix).", ".$i->pers_firstname,
                    "empno" => $i->pers_empno,
                    "status" => $i->ji_remarks,
                    "accountstat" => stripos($i->pers_empno, "SO") !== false ? "Active" : $i->U_Remarks
                ]
            ];
        });

        $assignment = DB::table('tbl_dept_authority')
        ->where('auth_for', $type)
        ->get()
        ->map(function($item) {
            return (array) $item; // Cast each item to an associative array
        })
        ->toArray();

        return compact('assignment', 'arremp');
    }

    public static function saveAssignment($request)
    {
        if(empty($request['id'])){
            if(count($request['remove']) > 0 && count($request['typeList']) > 0){
                DB::update("UPDATE tbl_dept_authority SET auth_assignation = REGEXP_REPLACE(auth_assignation, ?, '') WHERE FIND_IN_SET(auth_emp, ?) > 0 AND FIND_IN_SET(auth_for, ?) > 0", [
                    "(^" . str_replace("|", "\\||^", $request['assignment']) . "\\|)|(\\|?" . str_replace("|", "|\\|?", $request['assignment']) . ")",
                    implode(",", $request['remove']),
                    implode(",", $request['typeList'])
                ]);
            }

            foreach ($request['typeList'] as $t) {
                $cnt = 0;
                $exist = DB::table('tbl_dept_authority')->where('auth_emp', $request['emp'])->where('auth_for', $t)->get();
                foreach ($exist as $v) {
                    $arr1 = explode("|", $request['assignment']);
                    $arr2 = explode("|", $v->auth_assignation);
                    $arrayemp = array_unique (array_merge ($arr1, $arr2));
                    $arrayemp = implode("|", $arrayemp);

                    // $arr1 = explode("|", $dept);
                    // $arr2 = explode("|", $v->auth_dept);
                    // if($dept){
                    //     $arraydept = array_unique (array_merge ($arr1, $arr2));
                    //     $arraydept = implode("|", $arraydept);
                    // }else{
                        $arraydept = $v->auth_dept;
                    // }

                    // $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation = ?, auth_dept = ? WHERE auth_id=?");
                    // if($sql->execute(array($arrayemp, $arraydept,$v->auth_id))){
                    //     // echo "1";
                    //     // _log("Updated authority for $t to ".get_emp_name($emp).". ID: ".$v['auth_id']);
                    // }

                    DB::table('tbl_dept_authority')
                    ->where('auth_id', $v->auth_id)
                    ->update([
                        'auth_assignation' => $arrayemp,
                        'auth_dept' => $arraydept
                    ]);

                    $cnt++;
                }
                if($cnt == 0){
                    // $sql=$hr_pdo->prepare("INSERT INTO tbl_dept_authority(auth_assignation,auth_dept,auth_emp,auth_for) VALUES(?,?,?,?)");
                    // if($sql->execute(array($request['assignment'],$dept,$emp,$t))){
                    //     // echo "1";
                    //     // _log("Set authority for $t to ".get_emp_name($emp).". ID: ".$hr_pdo->lastInsertId());
                    // }

                    DB::table('tbl_dept_authority')
                    ->insert([
                        'auth_assignation' => $request['assignment'],
                        // 'auth_dept' => ,
                        'auth_emp' => $request['emp'],
                        'auth_for' => $t
                    ]);
                }
            }
            return true;
        }else{
            if(!empty($request['id'])){
                // $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation=? WHERE auth_for=? AND auth_id=?");
                // if($sql->execute(array($emparrtarget, $type, $id))){
                //     _log("Updated authority for $type. ID: $id");
                // }
                DB::table('tbl_dept_authority')
                ->where('auth_for', $request['type'])
                ->where('auth_id', $request['id'])
                ->update(['auth_assignation' => implode('|', $request['emparrtarget'])]);
            }

            if(!empty($request['src'])){
                // $sql=$hr_pdo->prepare("UPDATE tbl_dept_authority SET auth_assignation=? WHERE auth_for=? AND auth_id=?");
                // if($sql->execute(array($emparrsrc, $type, $src))){
                //     _log("Updated authority for $type. ID: $id");
                // }
                DB::table('tbl_dept_authority')
                ->where('auth_for', $request['type'])
                ->where('auth_id', $request['src'])
                ->update(['auth_assignation' => implode('|', $request['emparrsrc'])]);
            }
            return true;
        }
    }

    public static function accessGroup($active = 1, $system = 'HRIS')
    {
        $query = DB::table('tbl_role_grp');
        $query->where('system_id', '=', $system);
        if ($active === 1) {
            $query->where('grp_status', '=', 'Active');
        }
        $query->orderBy('grp_role', 'asc');

        return $query->get();
    }

    public static function saveAccessGroup($code, $name, $status, $module = false, $system = 'HRIS'){
        DB::table('tbl_role_grp')->updateOrInsert(
            [
                'grp_code' => $code,
                'system_id' => $system,
            ],
            [
                'grp_role' => $name,
                'grp_status' => $status,
            ]
        );

        if($module === false) return;

        $to_update = collect($module)
            ->filter(fn($i) => !empty($i['id']))
            ->map(fn($i) => [
                'grpmod_id' => $i['id'],
                'grpmod_mod' => $i['mod'],
                'grpmod_grp' => $code,
                'grpmod_indv' => $i['indv'],
                'system_id' => $system
            ]);

        DB::table('tbl_grp_module')->upsert(
            $to_update->toArray(),
            ['grpmod_id']
        );

        $last_id = DB::table('tbl_grp_module')
            ->where('grpmod_grp', $code)
            ->where('system_id', $system)
            ->orderByDesc('grpmod_id')
            ->first('grpmod_id');

        $to_insert = collect($module)
            ->filter(fn($i) => empty($i['id']))
            ->map(fn($i) => [
                'grpmod_mod' => $i['mod'],
                'grpmod_grp' => $code,
                'grpmod_indv' => $i['indv'],
                'system_id' => $system
            ])->toArray();

        DB::table('tbl_grp_module')->insert($to_insert);

        $new_ids = DB::table('tbl_grp_module')
            ->where('grpmod_grp', $code)
            ->where('system_id', $system)
            ->where('grpmod_id', '>', $last_id)
            ->pluck('grpmod_id');

        $id_list = $new_ids->merge($to_update->pluck('grpmod_id'));

        DB::table('tbl_grp_module')
            ->where('grpmod_grp', $code)
            ->where('system_id', $system)
            ->whereNotIn('grpmod_id', $id_list)
            ->delete();
    }

    public static function accessModule($grp = false, $active = 1, $system = 'HRIS')
    {
        $query = DB::table('tbl_modules');
        $query->where('system_id', '=', $system);
        if ($active === 1) {
            $query->where('mod_status', '=', 'Active');
        }
        if($grp !== false){
            $query->join('tbl_grp_module', function($join) use($grp) {
                $join->on('grpmod_mod', '=', 'mod_code')
                    ->where('grpmod_grp', $grp);
            });
        }
        $query->orderBy('mod_name', 'asc');
        return $query->get();
    }

    public static function saveAccessModule($code, $name, $status, $indv = false, $system = 'HRIS'){
        DB::table('tbl_modules')->updateOrInsert(
            [
                'mod_code' => $code,
                'system_id' => $system,
            ],
            [
                'mod_name' => $name,
                'mod_status' => $status,
            ]
        );

        if($indv === false) return;

        $to_update = collect($indv)
            ->filter(fn($i) => !empty($i['id']))
            ->map(fn($i) => [
                'modindv_id' => $i['id'],
                'modindv_mod' => $code,
                'modindv_indv' => $i['indv'],
                'system_id' => $system
            ]);

        DB::table('tbl_mod_indv')->upsert(
            $to_update->toArray(),
            ['modindv_id']
        );

        $last_id = DB::table('tbl_mod_indv')
            ->where('modindv_mod', $code)
            ->where('system_id', $system)
            ->orderByDesc('modindv_id')
            ->first('modindv_id');

        $to_insert = collect($indv)
            ->filter(fn($i) => empty($i['id']))
            ->map(fn($i) => [
                'modindv_mod' => $code,
                'modindv_indv' => $i['indv'],
                'system_id' => $system
            ])->toArray();

        DB::table('tbl_mod_indv')->insert($to_insert);

        $new_ids = DB::table('tbl_mod_indv')
            ->where('modindv_mod', $code)
            ->where('system_id', $system)
            ->where('modindv_id', '>', $last_id)
            ->pluck('modindv_id');

        $id_list = $new_ids->merge($to_update->pluck('modindv_id'));

        DB::table('tbl_grp_module as a')
            ->join('tbl_mod_indv as b', function($j) use($code, $system, $id_list){
                $j->on('modindv_mod', '=', 'grpmod_mod')
                    ->on('modindv_indv', '=', 'grpmod_indv')
                    ->where('modindv_mod', $code)
                    ->whereNotIn('modindv_id', $id_list)
                    ->where('b.system_id', $system);
            })
            ->where('grpmod_mod', $code)
            ->where('a.system_id', $system)
            ->delete();

        DB::table('tbl_mod_indv')
            ->where('modindv_mod', $code)
            ->where('system_id', $system)
            ->whereNotIn('modindv_id', $id_list)
            ->delete();
    }

    public static function accessIndividual($mod = false, $active = 1, $system = 'HRIS')
    {
        $query = DB::table('tbl_role_indv');
        $query->where('system_id', '=', $system);
        if ($active === 1) {
            $query->where('indv_status', '=', 'Active');
        }
        if($mod !== false){
            $query->join('tbl_mod_indv', function($join) use($mod) {
                $join->on('modindv_indv', '=', 'indv_code')
                    ->where('modindv_mod', $mod);
            });
        }
        $query->orderBy('indv_role', 'asc');
        return $query->get();
    }

    public static function saveAccessIndividual($code, $name, $status, $system = 'HRIS'){
        DB::table('tbl_modules')->updateOrInsert(
            [
                'indv_code' => $code,
                'system_id' => $system,
            ],
            [
                'indv_role' => $name,
                'indv_status' => $status,
            ]
        );

        // DB::table('tbl_grp_module')
        //     ->where('grpmod_indv', $code)
        //     ->where('system_id', $system)
        //     ->delete();

        // DB::table('tbl_mod_indv')
        //     ->where('modindv_indv', $code)
        //     ->where('system_id', $system)
        //     ->delete();
    }

    public static function saveAccessUser($empno, $access, $status, $system = 'HRIS'){

        $to_update = collect($access)
            ->filter(fn($i) => !empty($i['id']))
            ->map(fn($i) => [
                'assign_id' => $i['id'],
                'assign_empno' => $empno,
                'assign_grp' => $i['grp'],
                'assign_mod' => $i['mod'],
                'assign_indv' => $i['indv'],
                'assign_status' => $status,
                'system_id' => $system
            ]);

        DB::table('tbl_sysassign')->upsert(
            $to_update->toArray(),
            ['assign_id']
        );

        $last_id = DB::table('tbl_sysassign')
            ->where('system_id', $system)
            ->orderByDesc('assign_id')
            ->first('assign_id');

        $to_insert = collect($access)
            ->filter(fn($i) => empty($i['id']))
            ->map(fn($i) => [
                'assign_empno' => $empno,
                'assign_grp' => $i['grp'],
                'assign_mod' => $i['mod'],
                'assign_indv' => $i['indv'],
                'assign_status' => $status,
                'system_id' => $system
            ])->toArray();

        DB::table('tbl_sysassign')->insert($to_insert);

        $new_ids = DB::table('tbl_sysassign')
            ->where('system_id', $system)
            ->where('assign_id', '>', $last_id)
            ->pluck('assign_id');

        $id_list = $new_ids->merge($to_update->pluck('assign_id'));

        DB::table('tbl_sysassign')
            ->where('system_id', $system)
            ->whereNotIn('assign_id', $id_list)
            ->delete();
    }

    public static function saveAccessUserWithGroup($empno, $grp, $system = 'HRIS'){

        $grp_access = DB::table('tbl_grp_module')
            ->where('grpmod_grp', $grp)
            ->where('system_id', $system)
            ->get();

        $to_insert = $grp_access->map(fn($i) => [
                'assign_empno' => $empno,
                'assign_grp' => $i->grpmod_grp,
                'assign_mod' => $i->grpmod_mod,
                'assign_indv' => $i->grpmod_indv,
                'assign_status' => 'Active',
                'system_id' => $system
            ])
            ->toArray();

        DB::table('tbl_sysassign')->insert($to_insert);
    }
}
