<?php

namespace App\Http\Controllers;

use App\Models\EEI;
use App\Models\Employee;
use Illuminate\Http\Request;

class EEIController extends Controller
{
    public static function loadList($ym)
    {
        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $data = EEI::loadList($ym);
        $data = $data->map(function($eei) use($employees) {
            $eei->pers_lastname = $employees[$eei->resp_empno]['pers_lastname'] ?? '';
            $eei->pers_firstname = $employees[$eei->resp_empno]['pers_firstname'] ?? '';
            return $eei;
        })
        ->sortBy(function ($eei) {
            return [
                $eei->pers_lastname, 
                $eei->pers_firstname,
                $eei->resp_setnum,
                str_replace('-', '', $eei->resp_setitem)
            ];
        });

        $html = "<table id='tbl-eei' class='table table-bordered table-sm' style='width: 100%;'>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th style='display: none;'>Name</th>";
        $html .= "<th style='display: none;'>Item</th>";
        $html .= "<th style='display: none;'>Answer</th>";
        $html .= "<th style='display: none;'></th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";
        $cur_emp = '';
        $cur_item = '';
        $cur_set = '';
        foreach ($data as $v) {

            if ($cur_emp && $cur_emp != $v->resp_empno) {
                $html .= "<tr>";
                $html .= "<td colspan='4' style='background: black;'></td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "</tr>";
                $cur_set = '';
            }

            if($cur_set && $cur_set != $v->resp_setnum){
                $html .= "<tr>";
                $html .= "<td class='position-relative' style='text-wrap: nowrap;'><span class='position-sticky top-20'>" . trim(ucwords($v->pers_lastname.', '.$v->pers_firstname)) . "</span></td>";
                $html .= "<td colspan='3' style='background: gray;'></td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "</tr>";
            }

            if ($cur_item != $v->resp_setnum . '-' . $v->resp_setitem && in_array($v->resp_setnum . '-' . $v->resp_setitem, ['3-3-2', '3-4-2', '3-5-2'])) {
                $html .= "<tr>";
                $html .= "<td class='position-relative' style='text-wrap: nowrap;'><span class='position-sticky top-20'>" . trim(ucwords($v->pers_lastname.', '.$v->pers_firstname)) . "</span></td>";
                $html .= "<td colspan='3'>" . $v->set_description . "</td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "</tr>";
            }

            $html .= "<tr>";
            $html .= "<td class='position-relative' style='text-wrap: nowrap;'><span class='position-sticky top-20'>" . trim(ucwords($v->pers_lastname.', '.$v->pers_firstname)) . "</span></td>";

            if (in_array($v->resp_setnum . '-' . $v->resp_setitem, ['3-1', '3-5-3'])) {
                $html .= "<td>" . $v->set_description . "</td>";
                $html .= "<td colspan='2'>" . $v->resp_opt . "</td>";
                $html .= "<td style='display: none;'></td>";
            }elseif (in_array($v->resp_setnum . '-' . $v->resp_setitem, ['3-3-2', '3-4-2', '3-5-2'])) {
                $html .= "<td colspan='3'>&emsp;" . $v->resp_opt . "</td>";
                $html .= "<td style='display: none;'></td>";
                $html .= "<td style='display: none;'></td>";
            }else{
                $html .= "<td>" . $v->set_description . "</td>";
                $html .= "<td>" . $v->resp_opt . "</td>";
                $html .= "<td class='text-nowrap'>" . $v->resp_text . "</td>";
            }

            $html .= "</tr>";

            $cur_emp = $v->resp_empno;
            $cur_item = $v->resp_setnum . '-' . $v->resp_setitem;
            $cur_set = $v->resp_setnum;
        }
        $html .= "</tbody>";
        $html .= "</table>";

        return $html;
    }
}
