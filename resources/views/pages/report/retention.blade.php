<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

<style type="text/css">
	#input-group-eei-ym,
    #rptmonth {
        max-width: fit-content;
    }

    #rptyear {
        max-width: 100px;
    }

    #rptdisplay * {
        font-size: 12px;
    }

    #rptdisplay table tr.list1 td {
        background: gray;
        color: white;
        cursor: pointer;
    }

    #rptdisplay table tr.list2 td {
        background: lightgray; 
        cursor: pointer;
    }

    #rptdisplay table tr.list3 td {
        background: white;
    }
</style>

<div class="container-fluid">
	<div class="d-flex justify-content-end mb-3">
        <div class="input-group input-group-sm" id="input-group-eei-ym">
            <span class="input-group-text">MONTH</span>
            <select class="form-select form-select-sm" id="rptmonth">
                <option value="01">JANUARY</option>
				<option value="02">FEBRUARY</option>
				<option value="03">MARCH</option>
				<option value="04">APRIL</option>
				<option value="05">MAY</option>
				<option value="06">JUNE</option>
				<option value="07">JULY</option>
				<option value="08">AUGUST</option>
				<option value="09">SEPTEMBER</option>
				<option value="10">OCTOBER</option>
				<option value="11">NOVEMBER</option>
				<option value="12">DECEMBER</option>
            </select>
            <input type="number" class="form-control form-control-sm" id="rptyear" value="{{ date('Y') }}">
            <button class="btn btn-outline-secondary" type="button" onclick="getrpt()"><i class="fa fa-search"></i></button>
        </div>
    </div>
    <div id="rptdisplay"></div>
</div>

<script type="text/javascript">
    $("#rptdisplay").on('click', '.list1', function(){
        var code = $(this).attr("listsub");
        $(this).parent().find(".list2.list-"+code).toggle();
        $(this).parent().find(".list3.list-"+code+":visible").toggle();
    });

    $("#rptdisplay").on('click', '.list2', function(){
        var code = $(this).attr("listsub");
        $(this).parent().find(".list3.list-"+code).toggle();
    });

    async function getrpt() {
        $('#rptdisplay').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/report/retention/list/'+$('#rptyear').val()+'-'+$('#rptmonth').val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#rptdisplay').html(html);
            // $('#rptdisplay table').DataTable({
            //     "scrollY": "400px",
            //     "scrollX": "100%",
            //     "scrollCollapse": true,
            //     'paging':false,
            //     'ordering':false,
            //     'searching': false,
            //     'autoWidth': true
            //     // dom: 'Bflrtip',
            //     // buttons: [
            //     //     {
            //     //         extend: 'excelHtml5',
            //     //         text: '<i style="color:green;font-size:20px;"><i class="fa fa-file-excel-o"></i></i>',
            //     //         className: 'btn btn-default'
            //     //     },
            //     //     {
            //     //         extend: 'pdfHtml5',
            //     //         text: '<i style="font-size:20px;color:red;"><i class="fa fa-file-pdf-o"></i></i>',
            //     //         className: 'btn btn-default',
            //     //         orientation: 'landscape'
            //     //     },
            //     //     {
            //     //         extend: 'copyHtml5',
            //     //         text: '<i style="font-size:20px;"><i class="fa fa-copy"></i></i>',
            //     //         className: 'btn btn-default'
            //     //     }
            //     // ]
            // });
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>
{{-- 
if(isset($_POST['rptrange'])){
    include '../db/database.php';
    require"../db/core.php";
    include('../db/mysqlhelper.php'); 
    // $pdo = Database::connect();
    $hr_pdo = HRDatabase::connect();

    $date = $_POST['rptrange'] . "-01";
    $date_end = date("Y-m-t", strtotime($date));

    $sql = "SELECT * FROM tbl201_basicinfo
            LEFT JOIN tbl201_jobinfo ON ji_empno = bi_empno
            LEFT JOIN tbl201_jobrec ON jrec_empno = bi_empno AND jrec_status = 'Primary'
            LEFT JOIN tbl201_emplstatus ON estat_empno = bi_empno AND estat_stat = 'Active'
            LEFT JOIN tbl_company ON C_Code = jrec_company
            WHERE (YEAR(ji_resdate) <= ? OR YEAR(ji_datehired) <= ?) AND datastat = 'current' AND C_owned = 'True';";
    $query = $hr_pdo->prepare($sql);
    $query->execute([ date("Y", strtotime($date)), date("Y", strtotime($date)) ]);
    $result = $query->fetchall(PDO::FETCH_ASSOC);

    $arrresigned = [];
    $arrhired = [];
    $arrdept = [];

    foreach ($result as $k => $v) {
        // echo $v['ji_resdate']."<br>";
        if((empty($v['ji_resdate']) || $v['ji_resdate'] > $date) && in_array($v['estat_empstat'], ['REG', 'PROB', 'Trainee'])){
            $arrdept[$v['jrec_company']][$v['jrec_department']][] = [$v['bi_empno'], $v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])];
        }
        if($v['ji_resdate'] > $date && $v['ji_resdate'] <= $date_end){
            $arrresigned[$v['jrec_company']][$v['jrec_department']][] = [$v['bi_empno'], $v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])];
        }
        if($v['ji_datehired'] >= $date && $v['ji_datehired'] <= $date_end){
            $arrhired[$v['jrec_company']][$v['jrec_department']][] = [$v['bi_empno'], $v['bi_emplname'].", ".trim($v['bi_empfname']." ".$v['bi_empext'])];
        }
    }
    // print_r($arrresigned);

    echo "<table class='table table-bordered' style='width: 100%;'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Company</th>";
    echo "<th></th>";
    echo "<th>#As of " . date("F Y", strtotime($date)) . "</th>";
    echo "<th># resigned/awol/terminated for " . date("F Y", strtotime($date)) . "</th>";
    echo "<th># New Hires as of " . date("F Y", strtotime($date)) . "</th>";
    echo "<th># remaining</th>";
    echo "<th>Retention rate</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    $companylist = array_unique(array_keys($arrdept));
    $companycode = "";
    foreach ($companylist as $k => $v) {
        $disp = "";
        $total = 0;
        $total_resign = 0;
        $total_hire = 0;
        foreach ($arrdept[$v] as $k2 => $v2) {
            $dept_total = count($v2);
            $dept_total_resign = (isset($arrresigned[$v][$k2]) ? count($arrresigned[$v][$k2]) : 0);
            $dept_total_hire = (isset($arrhired[$v][$k2]) ? count($arrhired[$v][$k2]) : 0);
            $dept_remain = $dept_total - $dept_total_resign;
            $rate = round($dept_remain / $dept_total, 2) * 100;

            $total += $dept_total;
            $total_resign += $dept_total_resign;
            $total_hire += $dept_total_hire;

            $disp .= "<tr class='list2 list-" . $v . "' listsub='" . $v.$k2 . "' style='background: lightgray; cursor: pointer; display: none;'>";
            $disp .= "<td></td>";
            $disp .= "<td>" . $k2 . "</td>";
            $disp .= "<td>" . $dept_total . "</td>";
            $disp .= "<td>" . $dept_total_resign . "</td>";
            $disp .= "<td>" . $dept_total_hire . "</td>";
            $disp .= "<td>" . $dept_remain . "</td>";
            $disp .= "<td>" . $rate . "</td>";
            $disp .= "</tr>";
            if($dept_total > 0 || $dept_total_resign > 0 || $dept_total_hire > 0){
                $disp .= "<tr class='list3 list-" . $v . " list-" . $v.$k2 . "' style='background: white; display: none;'>";
                $disp .= "<td></td>";
                $disp .= "<td></td>";
                $disp .= "<td>" . ($dept_total > 0 ? "- ".implode("<br>- ", array_column($v2, 1)) : "") . "</td>";
                $disp .= "<td>" . ($dept_total_resign > 0 ? "- ".implode("<br>- ", array_column($arrresigned[$v][$k2], 1)) : "") . "</td>";
                $disp .= "<td>" . ($dept_total_hire > 0 ? "- ".implode("<br>- ", array_column($arrhired[$v][$k2], 1)) : "") . "</td>";
                $disp .= "<td></td>";
                $disp .= "<td></td>";
                $disp .= "</tr>";
            }
        }

        $total_remain = $total - $total_resign;
        $rate = round($total_remain / $total, 2) * 100;
        echo "<tr style='background: gray; color: white; cursor: pointer;' class='list1 list-" . $v . "' listsub='" . $v . "'>";
        echo "<td>" . $v . "</td>";
        echo "<td></td>";
        echo "<td>" . $total . "</td>";
        echo "<td>" . $total_resign . "</td>";
        echo "<td>" . $total_hire . "</td>";
        echo "<td>" . $total_remain . "</td>";
        echo "<td>" . $rate . "</td>";
        echo "</tr>";
        echo $disp;
        // if($companycode != "" && $companycode != $v){
        //  echo "<tr>";
        //  echo "<td style='background: white;'>&nbsp;</td>";
        //  echo "<td style='background: white;'>&nbsp;</td>";
        //  echo "<td style='background: white;'>&nbsp;</td>";
        //  echo "<td style='background: white;'>&nbsp;</td>";
        //  echo "<td style='background: white;'>&nbsp;</td>";
        //  echo "<td style='background: white;'>&nbsp;</td>";
        //  echo "</tr>";
        // }
        // $companycode = $v;
    }
    echo "</tbody>";
    echo "</table>";

<div class="container-fluid">
    <div class="col-md-9 col-md-offset-1">
        <div class="panel panel-default">
            <div class="panel-heading">
                <label>Retention Report</label>
            </div>
            <div class="panel-body">
                <div class="">
                    <label>Select Month:</label>
                    <select id="rptmonth">
                        <option value="01">JANUARY</option>
                        <option value="02">FEBRUARY</option>
                        <option value="03">MARCH</option>
                        <option value="04">APRIL</option>
                        <option value="05">MAY</option>
                        <option value="06">JUNE</option>
                        <option value="07">JULY</option>
                        <option value="08">AUGUST</option>
                        <option value="09">SEPTEMBER</option>
                        <option value="10">OCTOBER</option>
                        <option value="11">NOVEMBER</option>
                        <option value="12">DECEMBER</option>
                    </select>
                    <input type="number" id="rptyear" style="max-width: 100px;" value="<?=date("Y")?>">
                    <button onclick="getrpt()"><i class="fa fa-search"></i></button>
                </div>

                <div class="">
                    <div id="rptdisplay"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $("#rptdisplay").on('click', '.list1', function(){
        var code = $(this).attr("listsub");
        $(this).parent().find(".list2.list-"+code).toggle();
        $(this).parent().find(".list3.list-"+code+":visible").toggle();
    });
    $("#rptdisplay").on('click', '.list2', function(){
        var code = $(this).attr("listsub");
        console.log(code);
        $(this).parent().find(".list3.list-"+code).toggle();
    });
    function getrpt() {
        $("#rptdisplay").html("<img src='../../img/loading.gif' width='20%'>");
        $.post("retention-report.php", { rptrange: $("#rptyear").val()+"-"+$("#rptmonth").val() }, function(data){
            $("#rptdisplay").html(data);

            table_rpt=$('#rptdisplay table').DataTable({
                            "scrollY": "400px",
                            "scrollX": "100%",
                            "scrollCollapse": true,
                            'paging':false,
                            'ordering':false,
                            'searching': false,
                            dom: 'Bflrtip',
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    text: '<i style="color:green;font-size:20px;"><i class="fa fa-file-excel-o"></i></i>',
                                    className: 'btn btn-default'
                                },
                                {
                                    extend: 'pdfHtml5',
                                    text: '<i style="font-size:20px;color:red;"><i class="fa fa-file-pdf-o"></i></i>',
                                    className: 'btn btn-default',
                                    orientation: 'landscape'
                                },
                                {
                                    extend: 'copyHtml5',
                                    text: '<i style="font-size:20px;"><i class="fa fa-copy"></i></i>',
                                    className: 'btn btn-default'
                                }
                            ]
                        });
        });
    }
</script> --}}