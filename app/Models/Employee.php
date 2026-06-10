<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['pers_id'];
    protected $primaryKey = 'pers_id';

    protected $table = 'tbl201_persinfo';

    public static function employeeList($stat = '')
    {
        if($stat){
            return DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                     ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->where('ji_remarks', $stat)
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->orderBy('pers_midname', 'asc')
            ->get();
        }

        return DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                     ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->orderBy('pers_midname', 'asc')
            ->get();
    }

    public static function createEmployee($data)
    {            
        DB::table('tbl201_persinfo')->insert([
            // 'pers_id' => 
            'pers_empno' => $data['new-employee-number'],
            'pers_lastname' => $data['new-employee-lastname'],
            'pers_midname' => $data['new-employee-middlename'],
            'pers_firstname' => $data['new-employee-firstname'],
            // 'pers_suffix' => $data['new-employee-suffix'],
            // 'pers_maidenname' => $data[]
            // 'pers_prefname' => $data[]
            'pers_civilstat' => $data['new-employee-civil-status'],
            'pers_sex' => $data['new-employee-sex'],
            'pers_religion' => $data['new-employee-religion'],
            'pers_birthdate' => $data['new-employee-birthdate'],
            'pers_bloodtype' => $data['new-employee-bloodtype'],
            'pers_dialect' => $data['new-employee-dialect'],
            'pers_height' => $data['new-employee-height'],
            'pers_weight' => $data['new-employee-weight']
        ]);

        DB::table('tbl201_contact')->insert([
            'cont_empno' => $data['new-employee-number'],
            'cont_person_num' => $data['new-employee-contact'],
            'cont_company_num' => $data['new-employee-company-contact'],
            'cont_telephone' => $data['new-employee-telephone'],
            'cont_email' => $data['new-employee-email'],
            'cont_status' => 1
        ]);

        DB::table('tbl201_address')->insert([
            'add_empno' => $data['new-employee-number'],
            'add_perm_prov' => $data['new-employee-padd-province'],
            'add_perm_city' => $data['new-employee-padd-city'],
            'add_perm_brngy' => $data['new-employee-padd-barangay'],
            'add_cur_prov' => $data['new-employee-cadd-province'],
            'add_cur_city' => $data['new-employee-cadd-city'],
            'add_cur_brngy' => $data['new-employee-cadd-barangay'],
            'add_birth_prov' => $data['new-employee-badd-province'],
            'add_birth_city' => $data['new-employee-badd-city'],
            'add_birth_brngy' => $data['new-employee-badd-barangay'],
            'add_perm_location' => $data['new-employee-padd-specific'],
            'add_cur_location' => $data['new-employee-cadd-specific'],
            'add_birth_location' => $data['new-employee-badd-specific'],
            'add_status' => 1
        ]);

        DB::table('tbl201_gov_req')->insert([
            'gov_empno' => $data['new-employee-number'],
            'gov_sss' => $data['new-employee-sss'],
            'gov_pagibig' => $data['new-employee-hdmf'],
            'gov_philhealth' => $data['new-employee-phic'],
            'gov_tin' => $data['new-employee-tin'],
            'gov_status' => 1
        ]);

        DB::table('tbl201_jobinfo')->insert([
            'ji_empno' => $data['new-employee-number'],
            'ji_datehired' => $data['new-employee-date-hired'],
            // 'ji_regdate' => $data[],
            // 'ji_resdate' => $data[],
            'ji_remarks' => 'Active',
            // 'ji_rmksdescription' => $data[],
            // 'ji_separation' => $data[]
        ]);

        DB::table('tbl201_jobrec')->insert([
            'jrec_empno' => $data['new-employee-number'],
            'jrec_company' => $data['new-employee-company'],
            'jrec_department' => $data['new-employee-department'],
            'jrec_section' => $data['new-employee-section'],
            'jrec_area' => $data['new-employee-area'],
            'jrec_outlet' => $data['new-employee-outlet'],
            'jrec_position' => $data['new-employee-position'],
            'jrec_jobgrade' => $data['new-employee-job-grade'],
            'jrec_step' => $data['new-employee-job-step'],
            'jrec_effectdate' => $data['new-employee-date-hired'],
            'jrec_reportto' => $data['new-employee-reportto'],
            'jrec_status' => 'Primary',
            'jrec_timestamp' => now(),
            'jrec_sharedservice' => ($data['new-employee-company'] == 'TNGC' ? 1 : 0),
            'jrec_type' => 'Primary'
        ]);

        DB::table('tbl201_emplstatus')->insert([
            'estat_empno' => $data['new-employee-number'],
            'estat_effectdate' => $data['new-employee-date-hired'],
            'estat_empstat' => $data['new-employee-employment-status'],
            'estat_stat' => 'Active',
            'estat_timestamp' => now()
        ]);
    }

    public static function personalInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->leftJoin('tbl201_contact as b', 'b.cont_empno', '=', 'a.pers_empno')
            ->leftJoin('tbl201_address as c', 'c.add_empno', '=', 'a.pers_empno')
            ->leftJoin('tbl201_gov_req as d', 'd.gov_empno', '=', 'a.pers_empno')
            // ->leftJoin('tbl201_persinfo as b', function ($join) {
            //     $join->on('b.pers_empno', '=', 'a.bi_empno')   // First condition: employee_id
            //          ->where('b.datastat', '=', 'current');        // Second condition: status is 'active'
            // })
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->select(['*', DB::raw('TIMESTAMPDIFF(YEAR, pers_birthdate, CURDATE()) as age')])
            ->get()
            ->first();

        // if ($result) {
        //     $result->age = Carbon::parse($result->pers_birthdate)->age;
        // }

        return $result;
    }

    public static function savePersonalInfo($data)
    {
        DB::table('tbl201_persinfo')
        ->where('pers_empno', '=', $data['employee-number'])
        ->update([
            'pers_lastname' => $data['personal-lastname'],
            'pers_midname' => $data['personal-middlename'],
            'pers_firstname' => $data['personal-firstname'],
            // 'pers_suffix' => $data['personal-suffix'],
            // 'pers_maidenname' => $data[]
            // 'pers_prefname' => $data[]
            'pers_civilstat' => $data['personal-civil-status'],
            'pers_sex' => $data['personal-sex'],
            'pers_religion' => $data['personal-religion'],
            'pers_birthdate' => $data['personal-birthdate'],
            'pers_bloodtype' => $data['personal-bloodtype'],
            'pers_dialect' => $data['personal-dialect'],
            'pers_height' => $data['personal-height'],
            'pers_weight' => $data['personal-weight']
        ]);

        if(DB::table('tbl201_contact')->where('cont_empno', $data['employee-number'])->exists()){
            DB::table('tbl201_contact')
            ->where('cont_empno', '=', $data['employee-number'])
            ->update([
                'cont_person_num' => $data['personal-contact'],
                'cont_company_num' => $data['personal-company-contact'],
                'cont_telephone' => $data['personal-telephone'],
                'cont_email' => $data['personal-email'],
                'cont_status' => 1
            ]);
        }else{
            DB::table('tbl201_contact')->insert([
                'cont_empno' => $data['employee-number'],
                'cont_person_num' => $data['personal-contact'],
                'cont_company_num' => $data['personal-company-contact'],
                'cont_telephone' => $data['personal-telephone'],
                'cont_email' => $data['personal-email'],
                'cont_status' => 1
            ]);
        }

        if(DB::table('tbl201_address')->where('add_empno', $data['employee-number'])->exists()){
            DB::table('tbl201_address')
            ->where('add_empno', '=', $data['employee-number'])
            ->update([
                'add_perm_prov' => $data['personal-padd-province'],
                'add_perm_city' => $data['personal-padd-city'],
                'add_perm_brngy' => $data['personal-padd-barangay'],
                'add_cur_prov' => $data['personal-cadd-province'],
                'add_cur_city' => $data['personal-cadd-city'],
                'add_cur_brngy' => $data['personal-cadd-barangay'],
                'add_birth_prov' => $data['personal-badd-province'],
                'add_birth_city' => $data['personal-badd-city'],
                'add_birth_brngy' => $data['personal-badd-barangay'],
                'add_perm_location' => $data['personal-padd-specific'],
                'add_cur_location' => $data['personal-cadd-specific'],
                'add_birth_location' => $data['personal-badd-specific'],
                'add_status' => 1
            ]);
        }else{
            DB::table('tbl201_address')->insert([
                'add_empno' => $data['employee-number'],
                'add_perm_prov' => $data['personal-padd-province'],
                'add_perm_city' => $data['personal-padd-city'],
                'add_perm_brngy' => $data['personal-padd-barangay'],
                'add_cur_prov' => $data['personal-cadd-province'],
                'add_cur_city' => $data['personal-cadd-city'],
                'add_cur_brngy' => $data['personal-cadd-barangay'],
                'add_birth_prov' => $data['personal-badd-province'],
                'add_birth_city' => $data['personal-badd-city'],
                'add_birth_brngy' => $data['personal-badd-barangay'],
                'add_perm_location' => $data['personal-padd-specific'],
                'add_cur_location' => $data['personal-cadd-specific'],
                'add_birth_location' => $data['personal-badd-specific'],
                'add_status' => 1
            ]);
        }

        if(DB::table('tbl201_gov_req')->where('gov_empno', $data['employee-number'])->exists()){
            DB::table('tbl201_gov_req')
            ->where('gov_empno', '=', $data['employee-number'])
            ->update([
                'gov_sss' => $data['personal-sss'],
                'gov_pagibig' => $data['personal-hdmf'],
                'gov_philhealth' => $data['personal-phic'],
                'gov_tin' => $data['personal-tin'],
                'gov_status' => 1
            ]);
        }else{
            DB::table('tbl201_gov_req')->insert([
                'gov_empno' => $data['employee-number'],
                'gov_sss' => $data['personal-sss'],
                'gov_pagibig' => $data['personal-hdmf'],
                'gov_philhealth' => $data['personal-phic'],
                'gov_tin' => $data['personal-tin'],
                'gov_status' => 1
            ]);
        }
    }

    //
    public static function familyInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_family as b', 'b.fam_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('fam_id', 'asc')
            // ->select(['*', DB::raw('TIMESTAMPDIFF(YEAR, fam_birthdate, CURDATE()) as age')])
            ->select(['*'])
            ->addSelect(DB::raw('TIMESTAMPDIFF(YEAR, fam_birthdate, CURDATE()) as age'))
            ->get();

        return $result;
    }

    public static function saveFamilyInfo($data)
    {
        if(DB::table('tbl201_family')
            ->where('fam_id', '!=', $data['family-id'])
            ->where('fam_empno', '=', $data['employee-number'])
            ->where('fam_relationship', '=', $data['family-relationship'])
            ->where('fam_firstname', '=', $data['family-firstname'])
            ->where('fam_midname', '=', $data['family-middlename'])
            ->where('fam_lastname', '=', $data['family-lastname'])
            ->where('fam_suffix', '=', $data['family-suffix'])
            ->where('fam_maidenname', '=', $data['family-maidenname'])
            ->where('fam_birthdate', '=', $data['family-birthdate'])
            ->where('fam_sex', '=', $data['family-sex'])
            ->where('fam_contact', '=', $data['family-contact'])
            ->where('fam_add', '=', $data['family-address'])
            ->where('fam_occupation', '=', $data['family-occupation'])
            ->where('fam_workplace', '=', $data['family-workplace'])
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['family-id'])){
            DB::table('tbl201_family')
            ->where('fam_id', '=', $data['family-id'])
            ->where('fam_empno', '=', $data['employee-number'])
            ->update([
                'fam_relationship' => $data['family-relationship'],
                'fam_firstname' => $data['family-firstname'],
                'fam_midname' => $data['family-middlename'],
                'fam_lastname' => $data['family-lastname'],
                'fam_suffix' => $data['family-suffix'],
                'fam_maidenname' => $data['family-maidenname'],
                'fam_birthdate' => $data['family-birthdate'],
                'fam_sex' => $data['family-sex'],
                'fam_contact' => $data['family-contact'],
                'fam_add' => $data['family-address'],
                'fam_occupation' => $data['family-occupation'],
                'fam_workplace' => $data['family-workplace']
            ]);
        }else{
            DB::table('tbl201_family')->insert([
                'fam_empno' => $data['employee-number'],
                'fam_relationship' => $data['family-relationship'],
                'fam_firstname' => $data['family-firstname'],
                'fam_midname' => $data['family-middlename'],
                'fam_lastname' => $data['family-lastname'],
                'fam_suffix' => $data['family-suffix'],
                'fam_maidenname' => $data['family-maidenname'],
                'fam_birthdate' => $data['family-birthdate'],
                'fam_sex' => $data['family-sex'],
                'fam_contact' => $data['family-contact'],
                'fam_add' => $data['family-address'],
                'fam_occupation' => $data['family-occupation'],
                'fam_workplace' => $data['family-workplace']
            ]);
        }
    }

    public static function removeFamilyInfo($empno, $id)
    {
        DB::table('tbl201_family')
        ->where('fam_id', '=', $id)
        ->where('fam_empno', '=', $empno)
        ->delete();
    }

    //
    public static function skillsInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_skills as b', 'b.skill_empno', '=', 'a.pers_empno')
            ->join('tbl_skill_category as c', 'c.sc_id', '=', 'b.skill_category')
            ->leftJoin('tbl_skill_type as d', 'd.id', '=', 'b.skill_type')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('skill_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveSkillsInfo($data)
    {
        if(DB::table('tbl201_skills')
            ->where('skill_id', '!=', $data['skill-id'])
            ->where('skill_empno', '=', $data['employee-number'])
            ->where('skill_category', '=', $data['skill-category'])
            ->where('skill_type', '=', $data['skill-type'])
            ->where('skill_others', '=', $data['skill-other'])
            // ->where('status', '=', 1)
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['skill-id'])){
            DB::table('tbl201_skills')
            ->where('skill_id', '=', $data['skill-id'])
            ->where('skill_empno', '=', $data['employee-number'])
            ->update([
                'skill_category' => $data['skill-category'],
                'skill_type' => $data['skill-type'],
                'skill_others' => $data['skill-other'],
                'status' => 1
            ]);
        }else{
            DB::table('tbl201_skills')->insert([
                'skill_empno' => $data['employee-number'],
                'skill_category' => $data['skill-category'],
                'skill_type' => $data['skill-type'],
                'skill_others' => $data['skill-other'],
                'status' => 1
            ]);
        }
    }

    public static function removeSkillsInfo($empno, $id)
    {
        DB::table('tbl201_skills')
        ->where('skill_id', '=', $id)
        ->where('skill_empno', '=', $empno)
        ->delete();
    }

    //
    public static function educationInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_education as b', 'b.educ_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderByRaw("
            CASE 
                WHEN educ_level = 'Primary' THEN 1
                WHEN educ_level = 'Secondary' THEN 2
                WHEN educ_level = 'Tertiary' THEN 3
                ELSE 4
            END ASC
        ")
            ->orderBy('educ_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveEducationInfo($data)
    {
        if(DB::table('tbl201_education')
            ->where('educ_id', '!=', $data['education-id'])
            ->where('educ_empno', '=', $data['employee-number'])
            ->where('educ_level', '=', $data['education-level'])
            ->where('educ_degreetitle', '=', $data['education-degree'])
            ->where('educ_major', '=', $data['education-major'])
            ->where('educ_school', '=', $data['education-school'])
            ->where('educ_schooladd', '=', $data['education-address'])
            ->where('educ_yeargrad', '=', $data['education-year-graduated'])
            ->where('educ_currStatus', '=', $data['education-curstat'])
            ->where('status', '=', 1)
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['education-id'])){
            DB::table('tbl201_education')
            ->where('educ_id', '=', $data['education-id'])
            ->where('educ_empno', '=', $data['employee-number'])
            ->update([
                'educ_level' => $data['education-level'],
                'educ_degreetitle' => $data['education-degree'],
                'educ_major' => $data['education-major'],
                'educ_school' => $data['education-school'],
                'educ_schooladd' => $data['education-address'],
                'educ_yeargrad' => $data['education-year-graduated'],
                'educ_currStatus' => $data['education-curstat'],
                'status' => 1
            ]);
        }else{
            DB::table('tbl201_education')->insert([
                'educ_empno' => $data['employee-number'],
                'educ_level' => $data['education-level'],
                'educ_degreetitle' => $data['education-degree'],
                'educ_major' => $data['education-major'],
                'educ_school' => $data['education-school'],
                'educ_schooladd' => $data['education-address'],
                'educ_yeargrad' => $data['education-year-graduated'],
                'educ_currStatus' => $data['education-curstat'],
                'status' => 1
            ]);
        }
    }

    public static function removeEducationInfo($empno, $id)
    {
        DB::table('tbl201_education')
        ->where('educ_id', '=', $id)
        ->where('educ_empno', '=', $empno)
        ->delete();
    }

    //
    public static function licenseInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_eligibility as b', 'b.el_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('el_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveLicenseInfo($data)
    {
        if(DB::table('tbl201_eligibility')
            ->where('el_id', '!=', $data['license-id'])
            ->where('el_empno', '=', $data['employee-number'])
            ->where('el_type', '=', $data['license-type'])
            ->where('el_profession', '=', $data['license-profession'])
            ->where('el_regdate', '=', $data['license-registration-date'])
            ->where('el_expdate', '=', $data['license-valid-until'])
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['license-id'])){
            DB::table('tbl201_eligibility')
            ->where('el_id', '=', $data['license-id'])
            ->where('el_empno', '=', $data['employee-number'])
            ->update([
                'el_type' => $data['license-type'],
                'el_profession' => $data['license-profession'],
                'el_regdate' => $data['license-registration-date'],
                'el_expdate' => $data['license-valid-until'],
                'el_file' => !empty($data['license-attachment']) ? $data['license-attachment'] : ($data['license-attachment-current'] ?? '')
                // 'el_status' => $data['el_status']
            ]);
        }else{
            DB::table('tbl201_eligibility')->insert([
                'el_empno' => $data['employee-number'],
                'el_type' => $data['license-type'],
                'el_profession' => $data['license-profession'],
                'el_regdate' => $data['license-registration-date'],
                'el_expdate' => $data['license-valid-until'],
                'el_file' => $data['license-attachment'] ?? ''
            ]);
        }
    }

    public static function removeLicenseInfo($empno, $id)
    {
        DB::table('tbl201_eligibility')
        ->where('el_id', '=', $id)
        ->where('el_empno', '=', $empno)
        ->delete();
    }

    //
    public static function eduCertificateInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_certificate as b', 'b.cert_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('cert_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveEduCertificateInfo($data)
    {
        if(DB::table('tbl201_certificate')
            ->where('cert_id', '!=', $data['educcertificate-id'])
            ->where('cert_empno', '=', $data['employee-number'])
            ->where('cert_title', '=', $data['educcertificate-title'])
            ->where('cert_address', '=', $data['educcertificate-location'])
            ->where('cert_date', '=', $data['educcertificate-completion-date'])
            ->where('cert_speaker', '=', $data['educcertificate-speaker'])
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['educcertificate-id'])){
            DB::table('tbl201_certificate')
            ->where('cert_id', '=', $data['educcertificate-id'])
            ->where('cert_empno', '=', $data['employee-number'])
            ->update([
                'cert_title' => $data['educcertificate-title'],
                'cert_address' => $data['educcertificate-location'],
                'cert_date' => $data['educcertificate-completion-date'],
                'cert_speaker' => $data['educcertificate-speaker'],
                'cert_file' => !empty($data['educcertificate-attachment']) ? $data['educcertificate-attachment'] : ($data['educcertificate-attachment-current'] ?? '')
            ]);
        }else{
            DB::table('tbl201_certificate')->insert([
                'cert_empno' => $data['employee-number'],
                'cert_title' => $data['educcertificate-title'],
                'cert_address' => $data['educcertificate-location'],
                'cert_date' => $data['educcertificate-completion-date'],
                'cert_speaker' => $data['educcertificate-speaker'],
                'cert_file' => $data['educcertificate-attachment']
            ]);
        }
    }

    public static function removeEduCertificateInfo($empno, $id)
    {
        DB::table('tbl201_certificate')
        ->where('cert_id', '=', $id)
        ->where('cert_empno', '=', $empno)
        ->delete();
    }

    //
    public static function jobInfo($empno)
    {
        $result['jobinfo'] = DB::table('tbl201_persinfo as a')
            ->join('tbl201_jobinfo as b', 'b.ji_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->select(['b.*'])
            ->get()->first();

        $result['jobrec'] = DB::table('tbl201_persinfo as a')
            ->join('tbl201_jobrec as b', 'b.jrec_empno', '=', 'a.pers_empno')
            ->join('tbl_jobdescription as c', 'c.jd_code', '=', 'b.jrec_position')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('jrec_effectdate', 'desc')
            ->select(['b.*', 'jd_title'])
            ->get()->toArray();

        return $result;
    }

    public static function showCurrentJobInfo($empno)
    {
        $result['jobinfo'] = DB::table('tbl201_persinfo as a')
            ->join('tbl201_jobinfo as b', 'b.ji_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->select(['b.*'])
            ->get()->first();

        $result['jobrec'] = DB::table('tbl201_persinfo as a')
            ->join('tbl201_jobrec as b', 'b.jrec_empno', '=', 'a.pers_empno')
            ->join('tbl_jobdescription as c', 'c.jd_code', '=', 'b.jrec_position')
            ->where([
                ['a.pers_empno', '=', $empno],
                ['b.jrec_status', '=', 'Primary']
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('jrec_effectdate', 'desc')
            ->select(['b.*', 'jd_title'])
            ->first();

        return $result;
    }

    public static function employeeLatestJobInfo()
    {
        $result['jobinfo'] = DB::table('tbl201_persinfo as a')
            ->join('tbl201_jobinfo as b', 'b.ji_empno', '=', 'a.pers_empno')
            ->orderBy('pers_id', 'desc')
            ->select(['b.*'])
            ->get();

        $result['jobrec'] = DB::table('tbl201_persinfo as a')
            ->join('tbl201_jobrec as b', 'b.jrec_empno', '=', 'a.pers_empno')
            ->join('tbl_jobdescription as c', 'c.jd_code', '=', 'b.jrec_position')
            ->join('tbl_department as d', 'd.Dept_Code', '=', 'b.jrec_department')
            ->join('tbl_company as e', 'e.C_Code', '=', 'b.jrec_company')
            ->where('b.jrec_status', 'Primary')
            ->orderBy('pers_id', 'desc')
            ->orderBy('jrec_effectdate', 'desc')
            ->select(['b.*', 'jd_title', 'Dept_Name', 'C_Name'])
            ->get();

        return $result;
    }

    public static function saveJobInfo($data)
    {
        if(DB::table('tbl201_jobinfo')->where('ji_empno', '=', $data['employee-number'])->exists()){
            DB::table('tbl201_jobinfo')
            ->where('ji_empno', '=', $data['employee-number'])
            ->update([
                'ji_datehired' => $data['jobinfo-date-hired'],
                'ji_regdate' => $data['jobinfo-date-regular'],
                'ji_resdate' => $data['jobinfo-date-resigned'],
                'ji_remarks' => $data['jobinfo-remarks'],
                'ji_rmksdescription' => $data['jobinfo-separation-type'],
                'ji_separation' => $data['jobinfo-remarks-description']
            ]);
        }else{
            DB::table('tbl201_jobinfo')->insert([
                'ji_empno' => $data['employee-number'],
                'ji_datehired' => $data['jobinfo-date-hired'],
                'ji_regdate' => $data['jobinfo-date-regular'],
                'ji_resdate' => $data['jobinfo-date-resigned'],
                'ji_remarks' => $data['jobinfo-remarks'],
                'ji_rmksdescription' => $data['jobinfo-separation-type'],
                'ji_separation' => $data['jobinfo-remarks-description']
            ]);
        }
    }

    public static function saveJobRecord($data)
    {
        if(!empty($data['jobrec-id'])){
            $updateData = [
                'jrec_effectdate' => $data['jobrec-date-effect'],
                'jrec_company' => $data['jobrec-company'],
                'jrec_department' => $data['jobrec-department'],
                'jrec_section' => $data['jobrec-section'],
                'jrec_area' => $data['jobrec-area'],
                'jrec_outlet' => $data['jobrec-outlet'],
                'jrec_position' => $data['jobrec-position'],
                'jrec_jobgrade' => $data['jobrec-job-grade'],
                'jrec_step' => $data['jobrec-job-step'],
                'jrec_reportto' => $data['jobrec-reportto'] ?? '',
                'jrec_status' => $data['jobrec-status'],
                'jrec_timestamp' => now(),
                'jrec_sharedservice' => ($data['jobrec-company'] == 'TNGC' ? 1 : 0)
            ];

            if($data['jobrec-status'] != 'Inactive'){
                $updateData['jrec_type'] = $data['jobrec-status'];
            }

            DB::table('tbl201_jobrec')
            ->where('jrec_empno', '=', $data['employee-number'])
            ->where('jrec_id', '=', $data['jobrec-id'])
            ->update($updateData);

        }else{
            DB::table('tbl201_jobrec')->insert([
                'jrec_empno' => $data['employee-number'],
                'jrec_effectdate' => $data['jobrec-date-effect'],
                'jrec_company' => $data['jobrec-company'],
                'jrec_department' => $data['jobrec-department'],
                'jrec_section' => $data['jobrec-section'],
                'jrec_area' => $data['jobrec-area'],
                'jrec_outlet' => $data['jobrec-outlet'],
                'jrec_position' => $data['jobrec-position'],
                'jrec_jobgrade' => $data['jobrec-job-grade'],
                'jrec_step' => $data['jobrec-job-step'],
                'jrec_reportto' => $data['jobrec-reportto'] ?? '',
                'jrec_status' => $data['jobrec-status'],
                'jrec_timestamp' => now(),
                'jrec_sharedservice' => ($data['jobrec-company'] == 'TNGC' ? 1 : 0),
                'jrec_type' => $data['jobrec-status']
            ]);
        }
    }

    public static function removeJobRecord($empno, $id)
    {
        DB::table('tbl201_jobrec')
        ->where('jrec_id', '=', $id)
        ->where('jrec_empno', '=', $empno)
        ->delete();
    }

    //
    public static function employmentInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_employment as b', 'b.empl_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('empl_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveEmploymentInfo($data)
    {
        if(DB::table('tbl201_employment')
            ->where('empl_id', '!=', $data['employment-id'])
            ->where('empl_empno', '=', $data['employee-number'])
            ->where('empl_from', '=', $data['employment-start-date'])
            ->where('empl_to', '=', $data['employment-end-date'])
            ->where('empl_company', '=', $data['employment-company'])
            ->where('empl_address', '=', $data['employment-address'])
            ->where('empl_position', '=', $data['employment-position'])
            ->where('empl_contact', '=', $data['employment-contact'])
            ->where('empl_supervisor', '=', $data['employment-supervisor'])
            ->where('empl_reason', '=', $data['employment-reason'])
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['employment-id'])){
            DB::table('tbl201_employment')
            ->where('empl_id', '=', $data['employment-id'])
            ->where('empl_empno', '=', $data['employee-number'])
            ->update([
                'empl_from' => $data['employment-start-date'],
                'empl_to' => $data['employment-end-date'],
                'empl_company' => $data['employment-company'],
                'empl_address' => $data['employment-address'],
                'empl_position' => $data['employment-position'],
                'empl_contact' => $data['employment-contact'],
                'empl_supervisor' => $data['employment-supervisor'],
                'empl_reason' => $data['employment-reason'],
                'empl_timestamp' => now()
            ]);
        }else{
            DB::table('tbl201_employment')->insert([
                'empl_empno' => $data['employee-number'],
                'empl_from' => $data['employment-start-date'],
                'empl_to' => $data['employment-end-date'],
                'empl_company' => $data['employment-company'],
                'empl_address' => $data['employment-address'],
                'empl_position' => $data['employment-position'],
                'empl_contact' => $data['employment-contact'],
                'empl_supervisor' => $data['employment-supervisor'],
                'empl_reason' => $data['employment-reason'],
                'empl_timestamp' => now()
            ]);
        }
    }

    public static function removeEmploymentInfo($empno, $id)
    {
        DB::table('tbl201_employment')
        ->where('empl_id', '=', $id)
        ->where('empl_empno', '=', $empno)
        ->delete();
    }

    //
    public static function workCertificateInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_inter_cert as b', 'b.ic_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('ic_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveWorkCertificateInfo($data)
    {
        if(DB::table('tbl201_inter_cert')
            ->where('ic_id', '!=', $data['internalcertificate-id'])
            ->where('ic_empno', '=', $data['employee-number'])
            ->where('ic_title', '=', $data['internalcertificate-title'])
            ->where('ic_address', '=', $data['internalcertificate-location'])
            ->where('ic_date', '=', $data['internalcertificate-completion-date'])
            ->where('ic_speaker', '=', $data['internalcertificate-speaker'])
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['internalcertificate-id'])){
            DB::table('tbl201_inter_cert')
            ->where('ic_id', '=', $data['internalcertificate-id'])
            ->where('ic_empno', '=', $data['employee-number'])
            ->update([
                'ic_title' => $data['internalcertificate-title'],
                'ic_address' => $data['internalcertificate-location'],
                'ic_date' => $data['internalcertificate-completion-date'],
                'ic_speaker' => $data['internalcertificate-speaker'],
                'ic_file' => !empty($data['internalcertificate-attachment']) ? $data['internalcertificate-attachment'] : ($data['internalcertificate-attachment-current'] ?? '')
                // 'ic_status' => $data['ic_status']
            ]);
        }else{
            DB::table('tbl201_inter_cert')->insert([
                'ic_empno' => $data['employee-number'],
                'ic_title' => $data['internalcertificate-title'],
                'ic_address' => $data['internalcertificate-location'],
                'ic_date' => $data['internalcertificate-completion-date'],
                'ic_speaker' => $data['internalcertificate-speaker'],
                'ic_file' => $data['internalcertificate-attachment']
            ]);
        }
    }

    public static function removeWorkCertificateInfo($empno, $id)
    {
        DB::table('tbl201_inter_cert')
        ->where('ic_id', '=', $id)
        ->where('ic_empno', '=', $empno)
        ->delete();
    }

    //
    public static function characterrefInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_reference as b', 'b.ref_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('ref_id', 'asc')
            ->get();

        return $result;
    }

    public static function saveCharacterrefInfo($data)
    {
        if(DB::table('tbl201_reference')
            ->where('ref_id', '!=', $data['characterref-id'])
            ->where('ref_empno', '=', $data['employee-number'])
            ->where('ref_fullname', '=', $data['characterref-name'])
            ->where('ref_position', '=', $data['characterref-position'])
            ->where('ref_company', '=', $data['characterref-company'])
            ->where('ref_address', '=', $data['characterref-address'])
            ->where('ref_contact', '=', $data['characterref-contact'])
            ->where('ref_relationship', '=', $data['characterref-relationship'])
            ->exists()){
            // throw new \Exception('Record already exist.');
            return true;
        }

        if(!empty($data['characterref-id'])){
            DB::table('tbl201_reference')
            ->where('ref_id', '=', $data['characterref-id'])
            ->where('ref_empno', '=', $data['employee-number'])
            ->update([
                'ref_fullname' => $data['characterref-name'],
                'ref_position' => $data['characterref-position'],
                'ref_company' => $data['characterref-company'],
                'ref_address' => $data['characterref-address'],
                'ref_contact' => $data['characterref-contact'],
                'ref_relationship' => $data['characterref-relationship'],
                'ref_timestamp' => now()
            ]);
        }else{
            DB::table('tbl201_reference')->insert([
                'ref_empno' => $data['employee-number'],
                'ref_fullname' => $data['characterref-name'],
                'ref_position' => $data['characterref-position'],
                'ref_company' => $data['characterref-company'],
                'ref_address' => $data['characterref-address'],
                'ref_contact' => $data['characterref-contact'],
                'ref_relationship' => $data['characterref-relationship'],
                'ref_timestamp' => now()
            ]);
        }
    }

    public static function removeCharacterrefInfo($empno, $id)
    {
        DB::table('tbl201_reference')
        ->where('ref_id', '=', $id)
        ->where('ref_empno', '=', $empno)
        ->delete();
    }

    public static function contractsInfo($empno)
    {
        return Contract::where('ci_empno', $empno)
            ->orderByRaw('ci_startdate, ci_enddate')
            ->get();
    }


    public static function enneagramInfo($empno)
    {
        $list = [
            1 => 'PERFECTIONIST',
            2 => 'HELPER',
            3 => 'ACHIEVER',
            4 => 'ROMANTIC',
            5 => 'OBSERVER',
            6 => 'QUESTIONER',
            7 => 'ADVENTURER',
            8 => 'ASSERTER',
            9 => 'PEACEMAKER'
        ];

        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_enneagramtest as b', 'b.enneagram_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('enneagram_id', 'asc')
            ->get();

        return $result->map(function($item) use($list){
            $score = collect($item)->filter(fn($v, $k) => preg_match('/\d+_(.*)/', $k, $matches) && in_array(strtoupper($matches[1]), $list));
            $top_score = $score->sortDesc()->take(3);
            $third_score = $top_score->values()->last();
            $top_score_final = $score->filter(fn($val) => $val >= $third_score)->sortDesc()->mapWithKeys(fn($v2, $k2) => [strtoupper(preg_replace('/\d+_/', '', $k2)) => $v2]);
            $item->result = $top_score_final;

            return $item;
        });
    }

    public static function taptInfo($empno)
    {
        $list = [
            'E' => 'Extraverts',
            'S' => 'Sensors',
            'T' => 'Thinkers',
            'J' => 'Judgers',
            'I' => 'Introverts',
            'N' => 'Intuitives',
            'F' => 'Feelers',
            'P' => 'Perceivers'
        ];

        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_tapt as b', 'b.tapt_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('tapt_id', 'asc')
            ->get();

        return $result->map(function($item) use($list){
            $list2 = [];
            $list2[] = $list[$item->e_i];
            $list2[] = $list[$item->s_n];
            $list2[] = $list[$item->t_f];
            $list2[] = $list[$item->j_p];
            $item->result = collect($list2);

            return $item;
        });
    }

    public static function discInfo($empno)
    {
        // $list = [
        //     'D' => 'Dominance',
        //     'I' => 'Influence',
        //     'S' => 'Steadiness',
        //     'C' => 'Conscientiousness'
        // ];

        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_disc as b', 'b.disc_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('disc_id', 'asc')
            ->get();

        return $result->map(function($item){
            $score = collect([
                'Dominance' => $item->_d,
                'Influence' => $item->_i,
                'Steadiness' => $item->_s,
                'Conscientiousness' => $item->_c
            ]);

            $top_score = $score->sortDesc()->take(2);
            $score_val = $top_score->values()->last();
            $top_score_final = $score->filter(fn($item) => $item >= $score_val)->sortDesc();
            $item->result = $top_score_final;
            return $item;
        });
    }

    public static function miqInfo($empno)
    {
        $list = [
            1 => 'LINGUISTIC',
            2 => 'LOGICAL/MATHEMATICAL',
            3 => 'VISUAL/SPATIAL',
            4 => 'BODY KINESTHETIC',
            5 => 'MUSICAL/ARTISTIC',
            6 => 'INTERPERSONAL',
            7 => 'INTRAPERSONAL',
            8 => 'NATURALIST'
        ];

        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_miq as b', 'b.miq_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('miq_id', 'asc')
            ->get();

        return $result->map(function($item) use($list) {
            $ans = explode(',', $item->miq_ans);

            $answer_list = [
                1 => [1,9,17,25,33,41,49,57,65,73],
                2 => [2,10,18,26,34,42,50,58,66,74],
                3 => [3,11,19,27,35,43,51,59,67,75],
                4 => [4,12,20,28,36,44,52,60,68,76],
                5 => [5,13,21,29,37,45,53,61,69,77],
                6 => [6,14,22,30,38,46,54,62,70,78],
                7 => [7,15,23,31,39,47,55,63,71,79],
                8 => [8,16,24,32,40,48,56,64,72,80]
            ];

            $score = collect($list)->mapWithKeys(fn($v, $k) => [$v => count(array_intersect($ans, $answer_list[$k]))]);
            $top_score = $score->sortDesc()->take(3);
            $score_val = $top_score->values()->last();
            $top_score_final = $score->filter(fn($item) => $item >= $score_val)->sortDesc();
            $item->result = $top_score_final;
            return $item;
        });
    }

    public static function colorInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_whatcolorareyou as b', 'b.wcay_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('wcay_id', 'asc')
            ->get();

        return $result->map(function($item){
            $score = collect([
                'Blue' => $item->_1,
                'Green' => $item->_2,
                'Red' => $item->_3,
                'Yellow' => $item->_4
            ]);

            $top_score = $score->sortDesc()->take(1);
            $score_val = $top_score->values()->last();
            $top_score_final = $score->filter(fn($item) => $item >= $score_val)->sortDesc();
            $item->result = $top_score_final;
            return $item;
        });
    }

    public static function vakInfo($empno)
    {
        $result = DB::table('tbl201_persinfo as a')
            ->join('tbl201_vak as b', 'b.vak_empno', '=', 'a.pers_empno')
            ->where([
                ['a.pers_empno', '=', $empno]
            ])
            ->orderBy('pers_id', 'desc')
            ->orderBy('vak_id', 'asc')
            ->get();

        return $result->map(function($item){
            $score = collect([
                'Visual' => $item->_a,
                'Auditory' => $item->_b,
                'Kinesthetic' => $item->_c
            ]);

            $top_score = $score->sortDesc()->take(1);
            $score_val = $top_score->values()->last();
            $top_score_final = $score->filter(fn($item) => $item >= $score_val)->sortDesc();
            $item->result = $top_score_final;
            return $item;
        });
    }

    public static function retentionReport($ym)
    {
        $dt_start = $ym.'-01';
        $dt_end = date('Y-m-t', strtotime($dt_start));

        $ecf = DB::table('db_ecf2.tbl_request as a')->whereNotIn('ecf_status', ['draft', 'cancelled'])->get();
        $ecf = $ecf->groupBy('ecf_empno')->map(fn($g) => $g->groupBy('ecf_lastday'));



        // $sql = "SELECT * FROM tbl201_basicinfo
        //         LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
        //         LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
        //         LEFT JOIN tbl201_emplstatus ON estat_empno = bi_empno AND estat_stat = 'Active'
        //         LEFT JOIN tbl_company ON C_Code = jrec_company
        //         WHERE (YEAR(ji_resdate) <= ? OR YEAR(ji_datehired) <= ?) AND datastat = 'current' AND C_owned = 'True';";
        // $query = $hr_pdo->prepare($sql);
        // $query->execute([ date("Y", strtotime($date)), date("Y", strtotime($date)) ]);
        // $result = $query->fetchall(PDO::FETCH_ASSOC);

        // $arrresigned = [];
        // $arrhired = [];
        // $arrdept = [];

        // foreach ($result as $k => $v) {
        //     // echo $v['ji_resdate']."<br>";
        //     if((empty($v['ji_resdate']) || $v['ji_resdate'] > $date) && in_array($v['estat_empstat'], ['REG', 'PROB', 'Trainee'])){
        //         $arrdept[$v['jrec_company']][$v['jrec_department']][] = [$v['bi_empno'], $v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])];
        //     }
        //     if($v['ji_resdate'] > $date && $v['ji_resdate'] <= $date_end){
        //         $arrresigned[$v['jrec_company']][$v['jrec_department']][] = [$v['bi_empno'], $v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])];
        //     }
        //     if($v['ji_datehired'] >= $date && $v['ji_datehired'] <= $date_end){
        //         $arrhired[$v['jrec_company']][$v['jrec_department']][] = [$v['bi_empno'], $v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])];
        //     }
        // }
        // // print_r($arrresigned);

        // echo "<table class='table table-bordered' style='width: 100%;'>";
        // echo "<thead>";
        // echo "<tr>";
        // echo "<th>Company</th>";
        // echo "<th></th>";
        // echo "<th>#As of " . date("F Y", strtotime($date)) . "</th>";
        // echo "<th># resigned/awol/terminated for " . date("F Y", strtotime($date)) . "</th>";
        // echo "<th># New Hires as of " . date("F Y", strtotime($date)) . "</th>";
        // echo "<th># remaining</th>";
        // echo "<th>Retention rate</th>";
        // echo "</tr>";
        // echo "</thead>";
        // echo "<tbody>";
        // $companylist = array_unique(array_keys($arrdept));
        // $companycode = "";
        // foreach ($companylist as $k => $v) {
        //     $disp = "";
        //     $total = 0;
        //     $total_resign = 0;
        //     $total_hire = 0;
        //     foreach ($arrdept[$v] as $k2 => $v2) {
        //         $dept_total = count($v2);
        //         $dept_total_resign = (isset($arrresigned[$v][$k2]) ? count($arrresigned[$v][$k2]) : 0);
        //         $dept_total_hire = (isset($arrhired[$v][$k2]) ? count($arrhired[$v][$k2]) : 0);
        //         $dept_remain = $dept_total - $dept_total_resign;
        //         $rate = round($dept_remain / $dept_total, 2) * 100;

        //         $total += $dept_total;
        //         $total_resign += $dept_total_resign;
        //         $total_hire += $dept_total_hire;

        //         $disp .= "<tr class='list2 list-" . $v . "' listsub='" . $v.$k2 . "' style='background: lightgray; cursor: pointer; display: none;'>";
        //         $disp .= "<td></td>";
        //         $disp .= "<td>" . $k2 . "</td>";
        //         $disp .= "<td>" . $dept_total . "</td>";
        //         $disp .= "<td>" . $dept_total_resign . "</td>";
        //         $disp .= "<td>" . $dept_total_hire . "</td>";
        //         $disp .= "<td>" . $dept_remain . "</td>";
        //         $disp .= "<td>" . $rate . "</td>";
        //         $disp .= "</tr>";
        //         if($dept_total > 0 || $dept_total_resign > 0 || $dept_total_hire > 0){
        //             $disp .= "<tr class='list3 list-" . $v . " list-" . $v.$k2 . "' style='background: white; display: none;'>";
        //             $disp .= "<td></td>";
        //             $disp .= "<td></td>";
        //             $disp .= "<td>" . ($dept_total > 0 ? "- ".implode("<br>- ", array_column($v2, 1)) : "") . "</td>";
        //             $disp .= "<td>" . ($dept_total_resign > 0 ? "- ".implode("<br>- ", array_column($arrresigned[$v][$k2], 1)) : "") . "</td>";
        //             $disp .= "<td>" . ($dept_total_hire > 0 ? "- ".implode("<br>- ", array_column($arrhired[$v][$k2], 1)) : "") . "</td>";
        //             $disp .= "<td></td>";
        //             $disp .= "<td></td>";
        //             $disp .= "</tr>";
        //         }
        //     }

        //     $total_remain = $total - $total_resign;
        //     $rate = round($total_remain / $total, 2) * 100;
        //     echo "<tr style='background: gray; color: white; cursor: pointer;' class='list1 list-" . $v . "' listsub='" . $v . "'>";
        //     echo "<td>" . $v . "</td>";
        //     echo "<td></td>";
        //     echo "<td>" . $total . "</td>";
        //     echo "<td>" . $total_resign . "</td>";
        //     echo "<td>" . $total_hire . "</td>";
        //     echo "<td>" . $total_remain . "</td>";
        //     echo "<td>" . $rate . "</td>";
        //     echo "</tr>";
        //     echo $disp;
        // }
        // echo "</tbody>";
        // echo "</table>";
    }

    public static function outgoingList()
    {
        $ecf = DB::table('db_ecf2.tbl_request as a')
        ->whereNotIn('ecf_status', ['draft', 'cancelled'])
        ->get()
        ->sortBy([
            fn ($a, $b) => $a->ecf_empno <=> $b->ecf_empno,
            fn ($a, $b) => $a->ecf_lastday <=> $b->ecf_lastday
        ]);

        return DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                     ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_company', 'C_Code', '=', 'jrec_company')
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jrec_position')
            ->orderBy('C_Name', 'asc')
            ->orderBy('Dept_Name', 'asc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->get()
            ->filter(function($e) use($ecf){
                $last_day = $ecf->where('ecf_empno', $e->pers_empno)->where('ecf_lastday', '>', $e->ji_datehired)->first()->ecf_lastday ?? null;
                $e->ji_resdate = $last_day ?? $e->ji_resdate;
                return $e->ji_remarks == 'Inactive' || $e->ji_resdate > $e->ji_datehired;
            })
            ->sortBy(fn ($e) => [
                $e->C_Name,
                $e->Dept_Name,
                $e->pers_lastname,
                $e->pers_firstname,
                $e->ji_resdate
            ]);
    }

    public static function retentionList($ym)
    {
        $ecf = DB::table('db_ecf2.tbl_request as a')
        ->whereRaw("DATE_FORMAT(ecf_lastday, '%Y-%m') = ?", [$ym])
        ->whereNotIn('ecf_status', ['draft', 'cancelled'])
        ->get()
        ->sortBy([
            fn ($a, $b) => $a->ecf_empno <=> $b->ecf_empno,
            fn ($a, $b) => $a->ecf_lastday <=> $b->ecf_lastday
        ])->pluck('ecf_empno')->toArray();

        return DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', function ($join) use($ym) {
                $join->on('ji_empno', '=', 'pers_empno')
                ->whereRaw("(DATE_FORMAT(ji_resdate, '%Y-%m') = ? OR ji_remarks = 'Active')", [$ym]);
            })
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                     ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_company', 'C_Code', '=', 'jrec_company')
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jrec_position')
            ->where('C_owned', 'True')
            ->orderBy('C_Name', 'asc')
            ->orderBy('Dept_Name', 'asc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->select([
                'pers_empno',
                'pers_lastname',
                'pers_firstname',
                'ji_datehired',
                'ji_resdate',
                'jrec_company',
                'jrec_department',
                'C_Code',
                'C_Name',
                'Dept_Name'
            ])
            ->get()
            ->map(function($e) use($ecf, $ym){
                $last_day = $ecf['ecf_empno'] ?? null;
                $e->ji_resdate = $last_day ?? $e->ji_resdate;
                return $e;
            });
    }

    public static function payslipInfo($empno)
    {
        return null;
    }

    // public function getAgeAttribute()
    // {
    //     return Carbon::parse($this->pers_birthdate)->age;
    // }
}
