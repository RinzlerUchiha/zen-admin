<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PA;
use Illuminate\Http\Request;

class PAController extends Controller
{
    public static function loadList($year)
    {
        $month = [
            1 => 'Jan', // January
            2 => 'Feb', // February
            3 => 'Mar', // March
            4 => 'Apr', // April
            5 => 'May', // May
            6 => 'Jun', // June
            7 => 'Jul', // July
            8 => 'Aug', // August
            9 => 'Sep', // September
            10 => 'Oct', // October
            11 => 'Nov', // November
            12 => 'Dec' // December
        ];

        $data = PA::loadList($year);
        $data = $data->groupBy('paf_empno')->map(fn($g) => $g->groupBy('paf_period'));
        // dd($data);

        $employees = collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
            ->whereIn('pers_empno', $data->keys())
            ->sortBy([
                ['jrec_department', 'asc'],
                ['pers_lastname', 'asc'],
                ['pers_firstname', 'asc']
            ])
            ->mapWithKeys(function ($item) {
                return [$item['pers_empno'] => $item];
            });

        $html = '<table class="table table-sm table-hover table-striped table-bordered" style="width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Name</th>';
        $html .= '<th>Department</th>';
        for ($i = 1; $i <= 12; $i++) {
            $html .= '<th class="text-center">' . $month[$i] . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach ($employees as $k => $v) {
            $html .= '<tr>';
            $html .= '<td>' . trim(ucwords($v['pers_lastname'].', '.$v['pers_firstname'])) . '</td>';
            $html .= '<td>' . $v['jrec_department'] . '</td>';
            for ($i = 1; $i <= 12; $i++) {
                $ym = $year.'-'.str_pad($i, 2, '0', STR_PAD_LEFT);
                $paf = $data[$k][$ym] ?? [];
                $html .= '<td class="text-nowrap text-center">';
                foreach ($paf as $pa_item) {
                    if($pa_item->paf_deptheadsign || $pa_item->paf_ratersign){
                        $html .= '<span class="d-block">'.$pa_item->weighted_rating_total.'</span>';
                    }
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '<tfoot>';
        $html .= '<tr>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        for ($i = 1; $i <= 12; $i++) {
            $html .= '<th></th>';
        }
        $html .= '</tr>';
        $html .= '</tfoot>';

        $html .= '</table>';

        return $html;
        // return response($html)->header('Content-Type', 'text/html');
    }
}
