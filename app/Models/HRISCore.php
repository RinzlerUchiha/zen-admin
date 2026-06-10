<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HRISCore
{
    //

    // try {
    //     $dbName = 'server_copy_tngc_hrd2' ;
    //     $dbHost = 'mariadb' ;
    //     $dbUsername = 'admin';
    //     $dbUserPassword = 'Administr@t0r';
    //     $dsn='mysql:host='.$dbHost.';dbname='.$dbName;
    //     $con = new PDO($dsn, $dbUsername, $dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    //     $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    //     $province = $con->query("SELECT pr_name FROM portal_db.tbl_province")->fetchAll(PDO::FETCH_NUM)[0];
    //     $city = $con->query("SELECT ct_name FROM portal_db.tbl_municipality")->fetchAll(PDO::FETCH_NUM)[0];
    //     $barangay = $con->query("SELECT br_name FROM portal_db.tbl_barangay")->fetchAll(PDO::FETCH_NUM)[0];
    
    //     $persinfo = $con->prepare("INSERT INTO portal_db.tbl201_persinfo (
    //         pers_empno,
    //         pers_lastname,
    //         pers_midname,
    //         pers_firstname,
    //         pers_maidenname,
    //         pers_prefname,
    //         pers_civilstat,
    //         pers_sex,
    //         pers_religion,
    //         pers_birthdate,
    //         pers_bloodtype,
    //         pers_dialect,
    //         pers_height,
    //         pers_weight
    //     ) VALUES (
    //         :empno,
    //         :lastname,
    //         :midname,
    //         :firstname,
    //         :maidenname,
    //         :prefname,
    //         :civilstat,
    //         :sex,
    //         :religion,
    //         :birthdate,
    //         :bloodtype,
    //         :dialect,
    //         :height,
    //         :weight
    //     )");
    
    //     $address = $con->prepare("INSERT INTO portal_db.tbl201_address (
    //         add_empno,
    //         add_perm_prov,
    //         add_perm_city,
    //         add_perm_brngy,
    //         add_cur_prov,
    //         add_cur_city,
    //         add_cur_brngy,
    //         add_birth_prov,
    //         add_birth_city,
    //         add_birth_brngy,
    //         add_perm_location,
    //         add_cur_location,
    //         add_birth_location,
    //         add_status
    //     ) VALUES (
    //         :empno,
    //         :perm_prov,
    //         :perm_city,
    //         :perm_brngy,
    //         :cur_prov,
    //         :cur_city,
    //         :cur_brngy,
    //         :birth_prov,
    //         :birth_city,
    //         :birth_brngy,
    //         :perm_location,
    //         :cur_location,
    //         :birth_location,
    //         1
    //     )");
    
    //     $contact = $con->prepare("INSERT INTO portal_db.tbl201_contact (
    //         cont_empno,
    //         cont_person_num,
    //         cont_company_num,
    //         cont_telephone,
    //         cont_email,
    //         cont_status
    //     ) VALUES (
    //         :empno,
    //         :person_num,
    //         :company_num,
    //         :telephone,
    //         :email,
    //         1
    //     )");
    
    //     $gov = $con->prepare("INSERT INTO portal_db.tbl201_gov_req (
    //         gov_empno,
    //         gov_sss,
    //         gov_pagibig,
    //         gov_philhealth,
    //         gov_tin,
    //         gov_status
    //     ) VALUES (
    //         :empno,
    //         :sss,
    //         :pagibig,
    //         :philhealth,
    //         :tin,
    //         1
    //     )");
    
    //     foreach (
    //         $con->query("SELECT *
    //         FROM tbl201_basicinfo a
    //         LEFT JOIN tbl201_persinfo b ON b.pi_empno = a.bi_empno AND b.datastat = 'current'
    //         WHERE a.datastat = 'current'") as $v
    //     ) {
    
    //         if($con->query("SELECT COUNT(*) FROM portal_db.tbl201_persinfo WHERE pers_empno = '{$v['bi_empno']}'")->fetch(PDO::FETCH_NUM)[0] == 0){
    //             $persinfo->execute([
    //                 ':empno' => $v['bi_empno'],
    //                 ':lastname' => $v['bi_emplname'],
    //                 ':midname' => $v['bi_empmname'],
    //                 ':firstname' => $v['bi_empfname'],
    //                 ':maidenname' => '',
    //                 ':prefname' => $v['bi_empnickname'],
    //                 ':civilstat' => $v['pi_cstatus'],
    //                 ':sex' => $v['pi_sex'],
    //                 ':religion' => $v['pi_religion'],
    //                 ':birthdate' => $v['pi_dbirth'],
    //                 ':bloodtype' => $v['pi_bloodtype'],
    //                 ':dialect' => $v['pi_dialect'],
    //                 ':height' => $v['pi_height'],
    //                 ':weight' => $v['pi_weight']
    //             ]);
    //         }
    
    //         if($con->query("SELECT COUNT(*) FROM portal_db.tbl201_address WHERE add_empno = '{$v['bi_empno']}'")->fetch(PDO::FETCH_NUM)[0] == 0){
    //             $data = [
    //                 ':empno' => $v['bi_empno'],
    //                 ':perm_prov' => getMatch($v['pi_padd'], $province),
    //                 ':perm_city' => getMatch($v['pi_permanentadd'], $city),
    //                 ':perm_brngy' => getMatch($v['pi_permanentadd'], $barangay),
    //                 ':cur_prov' => getMatch($v['pi_padd'], $province),
    //                 ':cur_city' => getMatch($v['pi_cadd'], $city),
    //                 ':cur_brngy' => getMatch($v['pi_cadd'], $barangay),
    //                 ':birth_prov' => getMatch($v['pi_padd'], $province),
    //                 ':birth_city' => getMatch($v['pi_pbirth'], $city),
    //                 ':birth_brngy' => getMatch($v['pi_pbirth'], $barangay)
    //             ];
    
    //             $data[':perm_location'] = str_ireplace($data[':perm_prov'], '', $v['pi_permanentadd']);
    //             $data[':perm_location'] = str_ireplace($data[':perm_city'], '', $v['perm_location']);
    //             $data[':perm_location'] = str_ireplace($data[':perm_brngy'], '', $v['perm_location']);
    
    //             $data[':cur_location'] = str_ireplace($data[':cur_prov'], '', $v['pi_cadd']);
    //             $data[':cur_location'] = str_ireplace($data[':cur_city'], '', $v['cur_location']);
    //             $data[':cur_location'] = str_ireplace($data[':cur_brngy'], '', $v['cur_location']);
    
    //             $data[':birth_location'] = str_ireplace($data[':birth_prov'], '', $v['pi_pbirth']);
    //             $data[':birth_location'] = str_ireplace($data[':birth_city'], '', $v['birth_location']);
    //             $data[':birth_location'] = str_ireplace($data[':birth_brngy'], '', $v['birth_location']);
    
    //             $address->execute($data);
    //         }
    
    //         if($con->query("SELECT COUNT(*) FROM portal_db.tbl201_contact WHERE cont_empno = '{$v['bi_empno']}'")->fetch(PDO::FETCH_NUM)[0] == 0){
    //             $contact->execute([
    //                 ':empno' => $v['bi_empno'],
    //                 ':person_num' => $v['pi_mobileno'],
    //                 ':company_num' => $v['pi_cmobileno'],
    //                 ':telephone' => $v['pi_telno'],
    //                 ':email' => $v['pi_emailaddress']
    //             ]);
    //         }
    
    //         if($con->query("SELECT COUNT(*) FROM portal_db.tbl201_gov_req WHERE gov_empno = '{$v['bi_empno']}'")->fetch(PDO::FETCH_NUM)[0] == 0){
    //             $gov->execute([
    //                 ':empno' => $v['bi_empno'],
    //                 ':sss' => $v['pi_sssno'],
    //                 ':pagibig' => $v['pi_pagibigno'],
    //                 ':philhealth' => $v['pi_philhealthno'],
    //                 ':tin' => $v['pi_tinno']
    //             ]);
    //         }
    //     }
    
    //     // $jobinfo = $con->prepare("INSERT INTO portal_db.tbl201_jobinfo (
    //     //     ji_empno,
    //     //     ji_datehired,
    //     //     ji_regdate,
    //     //     ji_resdate,
    //     //     ji_remarks,
    //     //     ji_rmksdescription,
    //     //     ji_separation
    //     // ) VALUES (
    //     //     :empno,
    //     //     :datehired,
    //     //     :regdate,
    //     //     :resdate,
    //     //     :remarks,
    //     //     :rmksdescription,
    //     //     :separation
    //     // )");
    
    //     // foreach ($con->query("SELECT * FROM tbl201_jobinfo") as $v) {
    //     //     if($con->query("SELECT COUNT(*) FROM portal_db.tbl201_jobinfo WHERE ji_empno = '{$v['ji_empno']}'")->fetch(PDO::FETCH_NUM)[0] == 0){
    //     //         $jobinfo->execute([
    //     //             ':empno' => $v['ji_empno'],
    //     //             ':datehired' => $v['ji_datehired'],
    //     //             ':regdate' => $v['ji_regdate'],
    //     //             ':resdate' => $v['ji_resdate'],
    //     //             ':remarks' => $v['ji_remarks'],
    //     //             ':rmksdescription' => $v['ji_rmksdescription'],
    //     //             ':separation' => $v['ji_separation']
    //     //         ]);
    //     //     }
    //     // }
    
    // } catch (\Exception $th) {
    //     echo $th->getMessage();
    // }
    
    
    // function getMatch($str, $list) {
    //     foreach ($list as $item) {
    //         if (preg_match('/' . preg_quote(strtolower($item), '/') . '/', strtolower($str))) {
    //             return $item;
    //         }
    //     }
    //     return false;
    // }
}
