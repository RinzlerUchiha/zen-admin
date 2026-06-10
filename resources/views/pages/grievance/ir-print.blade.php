<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IR FORM - {{ mb_strtoupper($data->data->to_name) }}</title>

    <style type="text/css">
		@media print,screen{
			@page{
				size: letter;
			}
			html, body{
				height: 100%;
				margin: 0 !important;
				padding: 0 !important;
			}
			body{
				padding: .5in !important;
				font-size: 13px !important;
			}
			body, body>* {
				-webkit-print-color-adjust: exact !important;
			}
			table td{
				font-size: 13px !important;
				font-family: Calibri !important;
				padding: 5px;
			}

			.page-break-auto {
				page-break-inside: auto;
			}

			p, label, li, h5{
				font-size: 13px !important;
				font-family: Calibri !important;
			}
			p{
				margin: 0 !important;
				padding: 0 !important;
			}

			ol {
			  padding-left: 30px !important;
			}
            
			ol li::before {
			  font-size: 13px !important;
			}

			ol li{
			  padding-left: 10px !important;
			}

			#div-witness{
		        page-break-inside: avoid;
		    }

		    div{
		    	font-size: 13px !important;
				font-family: Calibri !important;
		    }
		    #head1{
		    	font-size: 17px !important;
				font-family: Cambria !important;
				font-weight: bold;
		    }

		    .divsign 
		    {
		        width: 150px;
		        position: relative;
		    }

		    .divsign
		    {
		        height: 50px;
		    }

		    .divsign svg 
		    {
		        position: absolute;
		        top: 0;
		        left: 0;
		        bottom: 0;
		        right: 0;
		        display: block;
		        width: 100%;
		        height: 100%;
		        overflow: unset;
		    }

		    .font_11 * {
		    	font-size: 11pt !important;
		    }

		    .font_10 * {
		    	font-size: 10pt !important;
		    }
		}
	</style>
</head>
<body>
    <center id="head1">Incident Report Form</center>
    <div>
        <table width="100%" class="font_11">
            <tr style="border: 1px solid black;">
                <td width="200px">
                    TO (Para kay):
                </td>
                <td>
                    {{ $data->to_name }}
                </td>
            </tr>
            <tr style="border: 1px solid black;">
                <td>
                    FROM (Galing Kay):
                </td>
                <td>
                    {{ $data->from_name }}
                </td>
            </tr>
            <tr style="border: 1px solid black;">
                <td>
                    DATE (Petsa):
                </td>
                <td>
                    {{ date("F d, Y",strtotime($data->ir_date)) }}
                </td>
            </tr>
            <tr style="border: 1px solid black;">
                <td>
                    SUBJECT (Tungkol sa):
                </td>
                <td>
                    {{$ir_subject}}
                </td>
            </tr>
        </table>

        <br>

        <table width="100%" class="font_10">
            <tr style="border: 1px solid black;">
                <td colspan="3">
                    INFORMATION ABOUT THE INCIDENT
                </td>
            </tr>
            <tr style="border: 1px solid black;">
                <td style="border: 1px solid black;">
                    Date of Incident (Kailan nangyari)<br>
                    {{date("Y-m-d",strtotime($ir_incidentdate))}}
                </td>
                <td style="border: 1px solid black;">
                    Location of Incident (Saan nangyari)<br>
                    {{$ir_incidentloc}}
                </td>
                <td style="border: 1px solid black;">
                    Audit Finding/s&emsp;<i class="fa {{($ir_auditfindings=="yes" ? "fa-check-square-o" : "fa-square-o")}}"></i> Yes &emsp;<i class="fa {{($ir_auditfindings=="no" ? "fa-check-square-o" : "fa-square-o")}}"></i> No
                </td>
            </tr>
            <tr style="border: 1px solid black;">
                <td style="border: 1px solid black;">
                    Person Involved (Taong sangkot)<br>
                    {{get_emp_name_init($ir_involved)}}
                </td>
                <td style="border: 1px solid black;">
                    Expected Performance/Standard violated<br>
                    {{$ir_violation}}
                </td>
                <td style="border: 1px solid black;">
                    Amount Involved, if any. (Magkano)<br>
                    {{$ir_amount}}
                </td>
            </tr>
            <tr style="border: 1px solid black;" class="page-break-auto">
                <td colspan="3" class="page-break-auto">
                    Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible. 
                    (attached additional sheets if necessary) (pakisulat dito kung ano ang nangyari, paano nangyari, sinu-sino ang mga kasali. Mas maraming detalye, mas mabuti)
                    <p>&nbsp;</p>

                    {{nl2br($ir_desc)}}
                    <p>&nbsp;</p>

                    As part of his/her responsibilities (Responsibilidad niya ang), is expected to:<br><br>

                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_reponsibility_1!="" ? "check-" : "")}}square-o"></i>&nbsp;Follow the SOP of (sumunod sa SOP na) <u>{{($ir_reponsibility_1!="" ? "&emsp;".$ir_reponsibility_1."&emsp;" : "_______________________________")}}</u> <br>
                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_reponsibility_2!="" ? "check-" : "")}}square-o"></i>&nbsp;Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng) <br>
                    &emsp;&emsp;&emsp;&emsp;<u>{{($ir_reponsibility_2!="" ? "&emsp;".$ir_reponsibility_2."&emsp;" : "_________________________________")}}</u>
                    <br>
                    In support of this, I have attached the following documents (Inilagay rin ang sumusunod na papeles para magpatibay sa report na ito):
                    <br>
                    <?php
                            $ir_receipts_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='receipts' AND ira_irid='$ir_id'"))->rowCount();
                            $ir_pic_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='pictures' AND ira_irid='$ir_id'"))->rowCount();
                            $ir_witness_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='witnesses' AND ira_irid='$ir_id'"))->rowCount();
                            $ir_item_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='items' AND ira_irid='$ir_id'"))->rowCount();
                            $ir_doc_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='docs' AND ira_irid='$ir_id'"))->rowCount();
                            $ir_audit_cnt=($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='audit' AND ira_irid='$ir_id'"))->rowCount();

                            $irwitness=[];
                            foreach ($hr_pdo->query("SELECT * FROM tbl_ir_attachment WHERE ira_type='witnesses' AND ira_irid='$ir_id'") as $ira) {
                                $irwitness[]=$ira["ira_content"];
                            }
                            $irwitness=implode(", ", $irwitness);

                            $auditdt=[];
                            foreach ($hr_pdo->query("SELECT DISTINCT(ira_auditdate) FROM tbl_ir_attachment WHERE ira_type='audit' AND ira_irid='$ir_id' AND NOT(ira_auditdate IS NULL OR ira_auditdate='0000-00-00')") as $ira) {
                                $auditdt[]=date("F d, Y",strtotime($ira["ira_auditdate"]));
                            }

                            $auditdt=implode(",&emsp;", $auditdt);

                    ?>

                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_receipts_cnt>0 ? "check-" : "")}}square-o"></i>&nbsp;Receipts<br>
                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_pic_cnt>0 ? "check-" : "")}}square-o"></i>&nbsp;Pictures<br>
                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_witness_cnt>0 ? "check-" : "")}}square-o"></i>&nbsp;Statements of witnesses namely {{($irwitness!="" ? "&emsp;".$irwitness."&emsp;" : "____________________________")}}<br>
                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_item_cnt>0 ? "check-" : "")}}square-o"></i>&nbsp;Item/Items damaged<br>
                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_doc_cnt>0 ? "check-" : "")}}square-o"></i>&nbsp;Related documents<br>
                    &emsp;&emsp;&emsp;<i class="fa fa-{{($ir_doc_cnt>0 ? "check-" : "")}}square-o"></i>&nbsp;Audit report dated {{($auditdt!="" ? "&emsp;".$auditdt."&emsp;" : "_________________")}}

                    <br>
                    I am reporting this matter to you so that the proper proceedings according to company policy may be begun (Pinapaalam ko ito sa inyo para magawa ang nakalagay sa company policy tungkol dito).
                </td>
            </tr>
            <tr style="border: 1px solid black;">
                <td colspan="3">
                    I hereby certify that the above information is true and correct (Ang nakasulat sa itaas ay tama at pawang katotohanan lamang).
                    <br>
                    <table style="display: inline-table; float:left;">
                        <tr>
                            <td>
                                <div id="div-signature" class="divsign" align="center">
                                    {{$ir_signature}}
                                </div>
                            </td>
                        </tr>
                        <tr >
                            <td align="center">{{strtoupper(get_emp_name_init($ir_from))}}</td>
                        </tr>
                        <tr style="border-top: 1px solid black;">
                            <td align="center">Signature over printed name</td>
                        </tr>
                    </table>
                    <table style="display: inline-table; float: right;">
                        <tr>
                            <td>
                                <div class="divsign" align="center">
                                    
                                </div>
                            </td>
                        </tr>
                        <tr >
                            <td style="text-align: center;">{{strtoupper(getName("position", $ir_pos)."/".(!($ir_pos=="" || $ir_outlet=="") && $ir_outlet!="ADMIN" ? $ir_outlet : ($ir_dept!="" ? getName("department",$ir_dept) : "&nbsp;")))}}</td>
                        </tr>
                        <tr style="border-top: 1px solid black;">
                            <td align="center">Position/Outlet or Department</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.print();
        });
    </script>
</body>
</html>