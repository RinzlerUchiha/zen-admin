<style type="text/css">
	.bootstrap-select .bs-actionsbox button{
		white-space: nowrap;
	}

	.bootstrap-select{
		max-width: 100% !important;
	}

	textarea {
		max-width: 100%;
		min-width: 100%;
		width: 100%;
		min-height: 30px;
	}

	#signature-pad-wrapper:not(.show) {
		display: none !important;
	}

	@media (min-width: 768px) {

	    #signature-pad-wrapper.show {
			position: fixed;
			top: 0;
			left: 0;
			bottom: 0;
			right: 0;
			margin: auto;
			z-index: 9999;
			/*display: flex;*/
			/*flex-direction: column;*/
			background: white;
			height: 100vh;
			width: 100%;
		}
	}

	@media (max-width: 768px) {
		#signature-pad-wrapper.show {
			display: none !important;
		}
	}

    .div-signature {
    	/*min-height: 100px;*/
    	width: 150px;
    	position: relative;
    	height: fit-content;
    }

    .div-signature svg {
	    /*position: absolute;*/
	    top: 0;
	    left: 0;
	    bottom: 0;
	    right: 0;
	    display: block;
	    width: 100%;
	    height: 100%;
	    overflow: unset;
	}
</style>

<div class="container-fluid">
	<div class="card">
		<div class="card-header">
			<span class="float-end">
				{{-- <a href="?page=grievance" class="btn btn-outline-secondary btn-sm"><i class="fa fa-list"></i></a> --}}
				@if ($data->{'13a_id'} != "" && (($user_empno == $data->{'13a_from'} && $data->{'13a_stat'} == "draft") || Auth::user()->userAccess('grievance','review')))
					<button class="btn btn-danger btn-sm" onclick="del_13a()"><i class="fa fa-trash"></i></button>&emsp;|&emsp;
				@endif
				<button class="btn btn-close" onclick="close13A()"></button>
			</span>
			<label>13A - Form</label>
		</div>
		<div class="card-body">
			@if (in_array($data->{'13a_stat'}, ["issued", "received", "refused"]))
				<div style="width: 8.5in; margin: auto;">
					<input type="hidden" id="_13a-id" value="{{ $data->{'13a_id'} }}">
					<input type="hidden" id="_13a-stat" value="{{ $data->{'13a_stat'} }}">
					<p>HRD Form13A</p>
					<p>&nbsp;</p>
					<center>
						<p>MEMORANDUM NO. <u>{{ $data->{'13a_memo_no'} }}</u></p>
					</center>
					<p>&nbsp;</p>
					<table width="100%">
						<tr>
							<td width="100px">TO:</td>
							<td>{{ isset($employees[$data->{'13a_to'}]) ? trim(ucwords($employees[$data->{'13a_to'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13a_to'}]['pers_midname']))." ".$employees[$data->{'13a_to'}]['pers_lastname']) : "" }}</td>
							<td>DATE:</td>
							<td>{{ date("F d, Y", strtotime($data->{'13a_date'})) }}</td>
						</tr>
						<tr>
							<td width="100px">POSITION:</td>
							<td>{{ isset($positionList[$data->{'13a_pos'}]) ? $positionList[$data->{'13a_pos'}]->jd_title : "" }}</td>
							<td>DEPT/BRANCH:</td>
							<td>{{ isset($departmentList[$data->{'13a_dept'}]) ? $departmentList[$data->{'13a_dept'}]->Dept_Name : "" }}</td>
						</tr>
						<tr>
							<td width="100px">COMPANY:</td>
							<td>{{ isset($companyList[$data->{'13a_company'}]) ? $companyList[$data->{'13a_company'}]->C_Name : "" }}</td>
						</tr>
					</table>
					<p>&nbsp;</p>
					<table width="100%">
						<tr>
							<td width="100px" style="vertical-align: top;">RE:</td>
							<td>{{ $data->{'13a_regarding'} }}</td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<td width="100px">FROM:</td>
							<td>{{ isset($employees[$data->{'13a_from'}]) ? trim(ucwords($employees[$data->{'13a_from'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13a_from'}]['pers_midname']))." ".$employees[$data->{'13a_from'}]['pers_lastname']) : "" }}</td>
							<td>POSITION:</td>
							<td>{{ isset($positionList[$data->{'13a_frompos'}]) ? $positionList[$data->{'13a_frompos'}]->jd_title : "" }}</td>
						</tr>
					</table>
					<p>&nbsp;</p>
					<p>On the following date/s you allegedly committed the following act/s or omission/s, namely:</p>
					<p>&nbsp;</p>
					<u>
						<p>{!! nl2br($data->{'13a_act'}) !!}</p>
					</u>
					<p>&nbsp;</p>
					<p>Which is a violation of {!! nl2br($violation_str) !!}</p>
					<p>&nbsp;</p>
					<div style="display: inline-table;">In this regard, please show cause by making a written explanation or justification within 120 hours from receipt of this memorandum and submit your reply personally to explain your side&nbsp;

						<table style="display: inline-table; ">
							<tr style="">
								<td style="vertical-align: baseline; text-decoration: underline;">&emsp;{{ date("F d, Y", strtotime($data->{'13a_datetime'})) }}&emsp;</td>
							</tr>
							<tr>
								<td>&emsp;(Date)</td>
							</tr>
						</table>
						<table style="display: inline-table; ">
							<tr style="">
								<td style="vertical-align: baseline; text-decoration: underline;">{{ date("h:i A", strtotime($data->{'13a_datetime'})) }}&emsp;&emsp;</td>
							</tr>
							<tr>
								<td>(Time)</td>
							</tr>
						</table>
						<table style="display: inline-table; ">
							<tr style="">
								<td style="vertical-align: baseline; text-decoration: underline;">{{ $data->{'13a_place'} }}&emsp;</td>
							</tr>
							<tr>
								<td>(Place)</td>
							</tr>
						</table>
						,<br>why you should not be <br>
						<br>
						<table width="100%">
							<tr>
								<td style="text-align: center; width: 33.33%; vertical-align: top;">{!! ($data->{'13a_penalty'} == "Issued a written Reprimand or warning" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;Issued a written Reprimand or warning</td>
								<td style="text-align: center; width: 33.33%; vertical-align: top;">{!! ($data->{'13a_penalty'} == "suspended for" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;suspended for {!! $data->{'13a_suspendday'} !!} day/s</td>
								<td style="text-align: center; width: 33.33%; vertical-align: top;">{!! ($data->{'13a_penalty'} == "terminated with cause" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;terminated with cause</td>
							</tr>
						</table>
						<br>

						For committing the&emsp;&emsp;
						{!! ($data->{'13a_offense'} == "1st offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;1st offense&emsp;&emsp;
						{!! ($data->{'13a_offense'} == "2nd offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;2nd offense&emsp;&emsp;
						{!! ($data->{'13a_offense'} == "3rd offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;3rd offense
						<br><br>
						of a&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
						{!! ($data->{'13a_offensetype'} == "minor offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;minor offense&emsp;&emsp;
						{!! ($data->{'13a_offensetype'} == "major offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;major offense&emsp;&emsp;
						{!! ($data->{'13a_offensetype'} == "grave offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;grave offense

					</div>
					<br><br>
					<p>Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</p>

					@if($data->{'13a_immediate_action'} == 1)
					<p>Furthermore, considering the gravity of the said offense you are hereby placed under <b>PREVENTIVE SUSPENSION</b> effective immediately and for a period of fifteen (15) days while this matter is being investigated. Please turn over all accountabilities. Note that preventive suspension is not a penalty, but a part of the process of investigation.</p>
					@endif

					<p>&emsp;&emsp;For your compliance.</p>
					<br>
					<table width="100%">
						<tr>
							<td style="vertical-align: middle; width: 55%;">
								<div>
									Noted by:
									@foreach (explode(',', $data->{'13a_notedby'}) as $k => $v)
										<table>
											<tr>
												<td align="center">
													<div id="div-signature-reviewed" class="div-signature" align="center">
														{!! !empty($signatures['reviewed']) && $signatures['reviewed']->where('gs_empno', $v)->first() ? $signatures['reviewed']->where('gs_empno', $v)->first()->gs_sign : '' !!}
													</div>
												</td>
											</tr>
											<tr>
												<td style='width:250px; text-align: center;'>{{ isset($employees[$v]) ? trim(ucwords($employees[$v]['pers_firstname']." ".getNameInitials($employees[$v]['pers_midname']))." ".$employees[$v]['pers_lastname']) : "" }}</td>
											</tr>
											<tr style='border-top: solid black 1px;'>
												<td style='text-align: center;'>{{ isset($data->{'13a_notedbypos'}[$k]) && isset($positionList[$data->{'13a_notedbypos'}[$k]]) ? $positionList[$data->{'13a_notedbypos'}[$k]]->jd_title : "" }}</td>
											</tr>
										</table>
										<br>
									@endforeach
								</div>
							</td>
							<td>
								<div>
									Issued by:
									<table>
										<tr>
											<td align="center">
												<div id="div-signature-issued" class="div-signature" align="center">
													{!! !empty($signatures['issued']) && $signatures['issued']->first() ? $signatures['issued']->first()->gs_sign : '' !!}
												</div>
											</td>
										</tr>
										<tr style="">
											<td style="width: 250px; text-align: center;">{{ isset($employees[$data->{'13a_issuedby'}]) ? trim(ucwords($employees[$data->{'13a_issuedby'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13a_issuedby'}]['pers_midname']))." ".$employees[$data->{'13a_issuedby'}]['pers_lastname']) : "" }}</td>
										</tr>
										<tr style="border-top: solid black 1px;">
											<td style="text-align: center;">(BH/DS/Dept. Head)</td>
										</tr>
									</table>
								</div>
								<br><br>
								<div>
									<table>
										<tr>
											<td align="center" colspan="2">
												<div id="div-signature-received" class="div-signature" align="center">
													{!! !empty($signatures['received']) && $signatures['received']->where('gs_empno', $data->{'13a_to'})->first() ? $signatures['received']->where('gs_empno', $data->{'13a_to'})->first()->gs_sign : '' !!}
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">{{ isset($employees[$data->{'13a_to'}]) ? trim(ucwords($employees[$data->{'13a_to'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13a_to'}]['pers_midname']))." ".$employees[$data->{'13a_to'}]['pers_lastname']) : "" }}</td>
										</tr>
										<tr style="border-top: solid black 1px;">
											<td colspan="2">Employee</td>
										</tr>
										<tr>
											<td>Date Received: </td>
											<td style="width: 100px; border-bottom: solid 1px black;">{{ !($data->{'13a_datereceived'} == "" || $data->{'13a_datereceived'} == "0000-00-00") ? date("F d, Y", strtotime($data->{'13a_datereceived'})) : "" }}</td>
										</tr>
										<tr>
											<td>Time: </td>
											<td style="width: 100px; border-bottom: solid 1px black;">{{ !($data->{'13a_datereceived'} == "" || $data->{'13a_datereceived'} == "0000-00-00") ? date("h:i A", strtotime($data->{'13a_datereceived'})) : "" }}</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
					</table>


					@if ($data->{'13a_stat'} == "refused")
						<div class="row mb-3">
							<label class="col-form-label col-md-12">REFUSED TO ACKNOWLEDGE RECEIPT</label>
							<label class="col-form-label col-md-12">Witnesses:</label>
							<div class="col-md-12">
								@if ($data->{'13a_witness'} != "")
									@foreach (explode(',', $data->{'13a_witness'}) as $k => $v)
										<table style="display: inline-grid;">
											<tr>
												<td align="center">
													<div id="div-signature-witness-{{ $v }}" class="div-signature" align="center">
														{!! !empty($signatures['witness']) && $signatures['witness']->where('gs_empno', $v)->first() ? $signatures['witness']->where('gs_empno', $v)->first()->gs_sign : '' !!}
													</div>
												</td>
												<td style="vertical-align: bottom;">
													@if (($signed_witness == 0 && $user_empno == $v) || ($user_empno == $data->{'13a_issuedby'} && !(!empty($signatures['witness']) && $signatures['witness']->contains('gs_empno', $v))))
														<button type="button" class="btn btn-primary btn-click-to-sign" onclick="sign_13a('witness', '{{ $v }}')" id="btn-click-to-sign-witness-{{ $v }}">Sign</button>
													@endif
												</td>
											</tr>
											<tr>
												<td style='width:250px; text-align: center;'>{{ isset($employees[$v]) ? trim(ucwords($employees[$v]['pers_firstname']." ".getNameInitials($employees[$v]['pers_midname']))." ".$employees[$v]['pers_lastname']) : "" }}</td>
											</tr>
											<tr style='border-top: solid black 1px;'>
												<td style='text-align: center;'>{{ isset($data->{'13a_witnesspos'}[$k]) && isset($positionList[$data->{'13a_witnesspos'}[$k]]) ? $positionList[$data->{'13a_witnesspos'}[$k]]->jd_title : "" }}</td>
											</tr>
										</table>
										&emsp;&emsp;&emsp;
									@endforeach
								@endif
								@if ($data->{'13a_issuedby'} == $user_empno && $data->{'13a_stat'} == "refused")
									<button type="button" class="btn btn-outline-secondary" onclick="edit_witness('{{ $data->{'13a_witness'} }}')">{{ ($data->{'13a_witness'} != "" ? "Edit" : "Add") }}</button>
								@endif
							</div>
						</div>
					@endif
				</div>
			@else
				<form id="form-13a">
					<input type="hidden" id="_13a-id" value="{{ $data->{'13a_id'} }}">
					<input type="hidden" id="_13a-stat" value="{{ $data->{'13a_stat'} }}">
					<input type="hidden" id="_13a-ir" value="{{ $data->{'13a_ir'} }}">
					<fieldset {{ ($data->{'13a_id'} != "" ? "disabled" : "") }}>
						@if ($data->{'13a_memo_no'} != "")
							<div class="row mb-3">
								<label class="col-form-label col-md-2">MEMORANDUM NO.</label>
								<div class="col-md-4">
									<!-- <input type="text" id="_13a-memo-no" class="form-control" required> -->
									<label>{{ $data->{'13a_memo_no'} }}</label>
								</div>
							</div>
						@endif
						<div class="row mb-3">
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">TO:</label>
									<div class="col-md-9">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<select class="form-control selectpicker" id="_13a-to" title="Select Employee" data-live-search="true">
												@foreach ($employees as $k => $v)
													@if($v['ji_remarks'] == 'Active' || $data->{'13a_to'} == $v['pers_empno'])
														<option 
															attr_pos="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->jd_title : "" }}" 
															attr_dept="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->Dept_Name : "" }}"
															attr_company="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->C_Name : "" }}"
															value="{{ $v['pers_empno'] }}" {{ $data->{'13a_to'} == $v['pers_empno'] ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
													@endif
												@endforeach
											</select>
										@else
											<label class="col-form-label">{{ $data->{'to_name'} }}</label>
										@endif
									</div>
								</div>
								<div class="row mb-3">
									<label class="col-form-label col-md-3">CC:</label>
									<div class="col-md-9">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<select class="form-control selectpicker" id="_13a-cc" title="Select Employee" data-live-search="true" multiple data-actions-box="true" required>
												@foreach ($employees as $k => $v)
													@if($v['ji_remarks'] == 'Active' || strpos($data->{'13a_cc'}, $v['pers_empno']) !== false)
														<option value="{{ $v['pers_empno'] }}" {{ strpos($data->{'13a_cc'}, $v['pers_empno']) !== false ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
													@endif
												@endforeach
											</select>
										@else
											@foreach (explode(',', $data->{'13a_cc'}) as $cc_k)
											<label class="col-form-label">{{ isset($employees[$cc_k]) ? trim(ucwords($employees[$cc_k]['pers_lastname'].", ".$employees[$cc_k]['pers_firstname'])) : "" }}</label>
											@endforeach
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">DATE:</label>
									<div class="col-md-5">
										<label class="col-form-label">{{ date("F d, Y", strtotime($data->{'13a_date'})) }}</label>
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">POSITION:</label>
									<div class="col-md-9">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<label class="col-form-label" id="_13a-position">{{ isset($positionList[$data->{'13a_pos'}]) ? $positionList[$data->{'13a_pos'}]->jd_title : "" }}</label>
										@else
											<label class="col-form-label">{{ isset($positionList[$data->{'13a_pos'}]) ? $positionList[$data->{'13a_pos'}]->jd_title : "" }}</label>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">DEPT/BRANCH:</label>
									<div class="col-md-9">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<label class="col-form-label" id="_13a-dept">{{ isset($departmentList[$data->{'13a_dept'}]) ? $departmentList[$data->{'13a_dept'}]->Dept_Name : "" }}</label>
										@else
											<label class="col-form-label">{{ isset($departmentList[$data->{'13a_dept'}]) ? $departmentList[$data->{'13a_dept'}]->Dept_Name : "" }}</label>
										@endif
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">COMPANY:</label>
									<div class="col-md-9">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<label class="col-form-label" id="_13a-company">{{ isset($companyList[$data->{'13a_company'}]) ? $companyList[$data->{'13a_company'}]->C_Name : "" }}</label>
										@else
											<label class="col-form-label">{{ isset($companyList[$data->{'13a_company'}]) ? $companyList[$data->{'13a_company'}]->C_Name : "" }}</label>
										@endif
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">RE:</label>
									<div class="col-md-9">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<input type="text" class="form-control" id="_13a-regarding" value="{{ $data->{'13a_regarding'} }}" required>
										@else
											<label class="col-form-label">{{ $data->{'13a_regarding'} }}</label>
										@endif
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">FROM:</label>
									<div class="col-md-7">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<select class="form-control selectpicker" id="_13a-from" title="Select Employee" data-live-search="true" disabled>
												@foreach ($employees as $k => $v)
													@if($v['ji_remarks'] == 'Active' || $data->{'13a_from'} == $v['pers_empno'])
														<option _job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}" {{ ($data->{'13a_from'} == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
													@endif
												@endforeach
											</select>
										@else
											<label class="col-form-label">{{ $data->{'from_name'} }}</label>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">POSITION:</label>
									<div class="col-md-7">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<select id="_13a-posfrom" name="13a-posfrom" class="form-control selectpicker" data-live-search="true" title="Select Position" disabled>
												@foreach ($positionList as $v) {
													<option value="{{ $v->jd_code }}" {{ ($data->{'13a_frompos'} == $v->jd_code ? "selected" : "") }}>{{ $v->jd_title }}</option>
												@endforeach
											</select>
										@else
											<label class="col-form-label">{{ isset($positionList[$data->{'13a_frompos'}]) ? $positionList[$data->{'13a_frompos'}]->jd_title : "" }}</label>
										@endif
									</div>
								</div>
							</div>
						</div>
						<hr>
						<div class="row mb-3">
							<label class="col-form-label col-md-12">Committed the following act/s or omission/s, namely:</label>
							<div class="col-md-12">
								@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
									<textarea class="form-control" id="_13a-act" required>{{ $data->{'13a_act'} }}</textarea>
								@else
									<label class="col-form-label">{!! nl2br($data->{'13a_act'}) !!}</label>
								@endif
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-md-12">Violation Code:</label>
							<div class="col-md-12">
								<table class="table table-bordered table-sm">
									<thead>
										<tr>
											<th>Article</th>
											<th>Section</th>
											<th>Description</th>
											@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
												<th></th>
											@endif
										</tr>
									</thead>
									<tbody id="violation-list">
										@foreach ($_13a_violations as $v)
											<tr articleCode="{{ $v->{'13av_article'} }}" 
											articleName="{{ $v->{'13av_articlename'} }}" 
											sectionCode="{{ $v->{'13av_section'} }}" 
											sectionName="{{ $v->{'13av_sectionname'} }}" 
											sectionDesc="{{ $v->{'13av_desc'} }}" 
											vid="{{ $v->{'13av_id'} }}" 
											othersrc="{{ $v->{'13av_othersrc'} }}">

											<td><span style='display: block; font-weight: bold;'>{{ $v->{'13av_othersrc'} }}</span>{{ $v->{'13av_article'}.": ".$v->{'13av_articlename'} }}</td>
											<td>{{ $v->{'13av_section'} . ": " . $v->{'13av_sectionname'} }}</td>
											<td>{{ $v->{'13av_desc'} }}</td>
											@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
												<td><button type="button" class="btn btn-outline-secondary btn-sm" onclick="delviolation(this)"><i class="fa fa-times"></i></button></td>
											@endif
											</tr>
										@endforeach
									</tbody>
								</table>
								@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
									<button type="button" class="btn btn-outline-secondary" onclick="addviolation()"><i class="fa fa-plus"></i></button>
								@endif
							</div>
						</div>
						<hr>
						<div class="row mb-3">
							<label class="col-form-label col-md-12">Time and Location of Response:</label>
							<div class="col-md-12">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">Date and Time <br><i>(mm/dd/yyyy hh:mm AM/PM)</i>:</label>
									<div class="col-md-3">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<input type="datetime-local" id="_13a-datetime" class="form-control" min1="{{ date("Y-m-d\TH:i") }}" value="{{ !($data->{'13a_datetime'} == "" || $data->{'13a_datetime'} == "0000-00-00") ? date("Y-m-d\TH:i", strtotime($data->{'13a_datetime'})) : "" }}" required>
										@else
											<p>{{ !($data->{'13a_datetime'} == "" || $data->{'13a_datetime'} == "0000-00-00") ? date("F d, Y h:i A", strtotime($data->{'13a_datetime'})) : "" }}</p>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="row mb-3">
									<label class="col-form-label col-md-3">Place:</label>
									<div class="col-md-7">
										@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
											<input type="text" id="_13a-place" class="form-control" placeholder="Place" value="{{ $data->{'13a_place'} }}" required>
										@else
											<p>{{ $data->{'13a_place'} }}</p>
										@endif
									</div>
								</div>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-md-3">Penalty/Punishment:</label>
							<div class="col-md-5">
								@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
									<select class="form-control" id="_13a-penalty" required>
										<option value disabled {{ ($data->{'13a_penalty'} == "" ? "selected" : "") }}>-Select-</option>
										<option value="Issued a written Reprimand or warning" {{ ($data->{'13a_penalty'} == "Issued a written Reprimand or warning" ? "selected" : "") }}>Issued a written Reprimand or warning</option>
										<option value="suspended for" {{ ($data->{'13a_penalty'} == "suspended for" ? "selected" : "") }}>suspended for</option>
										<option value="terminated with cause" {{ ($data->{'13a_penalty'} == "terminated with cause" ? "selected" : "") }}>terminated with cause</option>
									</select>
								@else
									<p>{{ $data->{'13a_penalty'} == "suspended for" ? $data->{'13a_penalty'} . " " . $data->{'13a_suspendday'} . " day/s" : $data->{'13a_penalty'} }}</p>
								@endif
							</div>
							<div class="col-md-3" id="div-suspendday" style="display: none;">
								<input type="number" id="_13a-suspendday" value="{{ $data->{'13a_suspendday'} }}" style="width: 100px;">
								<label>&nbsp;day/s</label>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-form-label col-md-3">Offense:</label>
							<div class="col-md-5">
								@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
									<select class="form-control" id="_13a-offense" required>
										<option value disabled {{ ($data->{'13a_offense'} == "" ? "selected" : "") }}>-Select-</option>
										<option value="1st offense" {{ ($data->{'13a_offense'} == "1st offense" ? "selected" : "") }}>1st offense</option>
										<option value="2nd offense" {{ ($data->{'13a_offense'} == "2nd offense" ? "selected" : "") }}>2nd offense</option>
										<option value="3rd offense" {{ ($data->{'13a_offense'} == "3rd offense" ? "selected" : "") }}>3rd offense</option>
									</select>
								@else
									<p>{{ $data->{'13a_offense'} }}</p>
								@endif
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-form-label col-md-3">Offense type:</label>
							<div class="col-md-5">
								@if ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "" || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation")))
									<select class="form-control" id="_13a-offense-type" required>
										<option value disabled {{ ($data->{'13a_offensetype'} == "" ? "selected" : "") }}>-Select-</option>
										<option value="minor offense" {{ ($data->{'13a_offensetype'} == "minor offense" ? "selected" : "") }}>minor offense</option>
										<option value="major offense" {{ ($data->{'13a_offensetype'} == "major offense" ? "selected" : "") }}>major offense</option>
										<option value="grave offense" {{ ($data->{'13a_offensetype'} == "grave offense" ? "selected" : "") }}>grave offense</option>
									</select>
								@else
									<p>{{ $data->{'13a_offensetype'} }}</p>
								@endif
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-md-12">Failure to do so would mean that you are waiving your right to be heard and that appropriate action may be taken by the company based on the violation of the above cited policy/ies and procedures.</label>

							<label class="col-form-label col-md-12 immediate-action {{ ($data->{'13a_immediate_action'} == 1 ? "checked" : "") }}"><input type="checkbox" id="immediate_action" {{ ($data->{'13a_immediate_action'} == 1 ? "checked" : "") }}> Furthermore, considering the gravity of the said offense you are hereby placed under <b>PREVENTIVE SUSPENSION</b> effective immediately and for a period of fifteen (15) days while this matter is being investigated. Please turn over all accountabilities. Note that preventive suspension is not a penalty, but a part of the process of investigation.</label>

							<label class="col-form-label col-md-3">For your compliance.</label>
						</div>

					</fieldset>

					<div class="row mb-3">
						<label class="col-form-label col-md-3">Issued by:</label>
						<div class="col-md-7">
							<table>
								<tr>
									<td align="center">
										<div id="div-signature-issued" class="div-signature" align="center">
											{!! !empty($signatures['issued']) && $signatures['issued']->first() ? $signatures['issued']->first()->gs_sign : '' !!}
										</div>
									</td>
									<td style="vertical-align: bottom;">
										@if ($signed_issued == "" && $data->{'13a_stat'} == "checked" && $data->{'13a_issuedby'} == $user_empno)
											<button type="button" class="btn btn-outline-secondary btn-click-to-sign" onclick="sign_13a('issued', '{{ $data->{'13a_issuedby'} }}')" id="btn-click-to-sign-issued">Sign</button>
										@endif
									</td>
								</tr>
								<tr>
									<td style="width: 250px; text-align: center;">{{ isset($employees[$data->{'13a_issuedby'}]) ? trim(ucwords($employees[$data->{'13a_issuedby'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13a_issuedby'}]['pers_midname']))." ".$employees[$data->{'13a_issuedby'}]['pers_lastname']) : "" }}</td>
								</tr>
								<tr style="border-top: solid black 1px;">
									<td style="text-align: center;">{{ isset($positionList[$data->{'13a_issuedbypos'}]) ? $positionList[$data->{'13a_issuedbypos'}]->jd_title : "" }}</td>
								</tr>
							</table>
							@if (Auth::user()->userAccess('grievance','review') && in_array($data->{'13a_stat'}, ['draft', 'pending', 'needs explanation']))
								<button type="button" class="btn btn-outline-secondary" onclick="edit_issued('{{ $data->{'13a_issuedby'} }}')">{{ ($data->{'13a_issuedby'} != "" ? "Edit" : "Add") }}</button>
							@endif
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-form-label col-md-3">Noted by:</label>
						<div class="col-md-7">
							@foreach (explode(',', $data->{'13a_notedby'}) as $k => $v)
								<table>
									<tr>
										<td align="center">
											<div id="div-signature-reviewed" class="div-signature" align="center">
												{!! !empty($signatures['reviewed']) && $signatures['reviewed']->where('gs_empno', $v)->first() ? $signatures['reviewed']->where('gs_empno', $v)->first()->gs_sign : '' !!}
											</div>
										</td>
									</tr>
									<tr>
										<td style='width:250px; text-align: center;'>{{ isset($employees[$v]) ? trim(ucwords($employees[$v]['pers_firstname']." ".getNameInitials($employees[$v]['pers_midname']))." ".$employees[$v]['pers_lastname']) : "" }}</td>
									</tr>
									<tr style='border-top: solid black 1px;'>
										<td style='text-align: center;'>{{ isset($data->{'13a_notedbypos'}[$k]) && isset($positionList[$data->{'13a_notedbypos'}[$k]]) ? $positionList[$data->{'13a_notedbypos'}[$k]]->jd_title : "" }}</td>
									</tr>
								</table>
								<br>
							@endforeach
							@if (Auth::user()->userAccess('grievance','review') && in_array($data->{'13a_stat'}, ['draft', 'pending', 'needs explanation']))
								<button type="button" class="btn btn-outline-secondary" onclick="edit_noted('{{ $data->{'13a_notedby'} }}')">{{ ($data->{'13a_notedby'} != "" ? "Edit" : "Add") }}</button>
							@endif
						</div>
					</div>

					<button type="submit" style="display: none;"></button>
				</form>
			@endif

			@if ($data->{'13a_hearing_loc'} != '')
				<hr>
				<div class="card card-info">
					<div class="card-body">
						<h4>- Hearing -
							@if ($data->{'13a_issuedby'} == $user_empno)
								<span class="">
									<button class="btn btn-outline-secondary btn-sm" onclick="$('#hearingModal ').modal('show')"><i class="fa fa-edit"></i></button>
								</span>
							@endif
						</h4>
						<div>
							<div class="row mb-3">
								<label class="col-form-label col-md-2">Date and Time:</label>
								<div class="col-md-5">
									{{ !($data->{'13a_hearing_time'} == "" || $data->{'13a_hearing_time'} == "0000-00-00") ? date("F d, Y h:i A", strtotime($data->{'13a_hearing_time'})) : "" }}
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-form-label col-md-2">Place:</label>
								<div class="col-md-5">
									{{ $data->{'13a_hearing_loc'} }}
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif

			@if ($remarks->count() > 0)
				<br>
				<hr>
				<div class="card card-danger">
					<div class="card-header">
						<label>Remarks</label>
					</div>
					<div class="card-body">
						<div>
							@foreach ($remarks as $v)
								<div class="row mb-3">
									<label class="col-form-label col-md-3">{{ get_emp_name($v->gr_empno) }} :</label>
									<div class="col-md-7">
										{{ nl2br($v->gr_remarks) }}
									</div>
								</div>
								<hr>
							@endforeach
						</div>
					</div>
				</div>
				<br>
			@endif

			<div align="center">
				@if($data->{'13a_id'} != "" && Auth::user()->userAccess('grievance','review') && ($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == ""  || $data->{'13a_stat'} == "pending" || $data->{'13a_stat'} == "needs explanation"))
					<button id="btn-save-13a" class="btn btn-primary" style="{{ ($data->{'13a_id'} != "" ? "display: none;" : "") }}">Save</button>

					<button id="btn-edit-13a" class="btn btn-success" style="{{ ($data->{'13a_id'} == "" ? "display: none;" : "") }}">Edit</button>
					@if($data->{'13a_stat'} == "draft" /*|| $data->{'13a_stat'} == ""*/)
						&emsp;|&emsp;
						<button class="btn btn-primary" id="btn-check-13a" onclick="_13a_checked()">Checked</button>
					@endif
				@elseif ((($data->{'13a_stat'} == "draft" || $data->{'13a_stat'} == "needs explanation") && $data->{'13a_from'} == $user_empno) || $data->{'13a_id'} == "")
					<button id="btn-save-13a" class="btn btn-primary" style="{{ ($data->{'13a_id'} != "" ? "display: none;" : "") }}">Save</button>

					<button id="btn-edit-13a" class="btn btn-success" style="{{ ($data->{'13a_id'} == "" ? "display: none;" : "") }}">Edit</button>
					@if(!($data->{'13a_id'} == "" && Auth::user()->userAccess('grievance','review')))
						&emsp;|&emsp;
						<button class="btn btn-primary" id="btn-post-13a">Submit For Checking</button>
					@endif
				@endif

				@if ($data->{'13a_stat'} == "pending" && (Auth::user()->userAccess('grievance','review') || $data->{'13a_issuedby'} == $user_empno))
					<!-- <button id="btn-edit-13a" class="btn btn-success" style="{{ ($data->{'13a_id'} == "" ? "display: none;" : "") }}">Edit</button> -->
					<!-- <button id="btn-save-13a" class="btn btn-primary" style="{{ ($data->{'13a_id'} != "" ? "display: none;" : "") }}">Save</button> -->
				@endif
			</div>

			<div class="float-start">
				<br>
				<table class="table">
					<thead>
						<tr>
							<th colspan="2" style="text-align: center;">View IR</th>
							<th>
								@if ($data->{'13a_id'} != "" && Auth::user()->userAccess('grievance','review'))
									<button class="btn btn-sm btn-primary" onclick="$('#otheriryModal').modal('show');">Attach IR</button>
								@endif
							</th>
						</tr>
						<!-- <tr>
							<th>Memo No</th>
							<th></th>
						</tr> -->
					</thead>
					<tbody>
						@foreach ($ir as $v)
							<tr>
								<td>{{ date("F d, Y", strtotime($v->ir_date)) }}</td>
								<td>{{ $v->ir_subject }}</td>
								<td>
									<button class="btn btn-info" onclick="viewIR('{{ $v->ir_id }}')"><i class="fa fa-eye"></i></button>

									@if ($data->{'13a_id'} != "" && Auth::user()->userAccess('grievance','review'))
										<button class="btn btn-danger" onclick="del_otherir('{{ $v->ir_id }}')"><i class="fa fa-times"></i></button>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<br>
			<div class="float-end">
				@if (($data->{'13a_issuedby'} == $user_empno || Auth::user()->userAccess('grievance','review')) && in_array($data->{'13a_stat'}, ['received', 'pending', 'reviewed', 'issued', 'checked']))
					<button class="btn btn-sm btn-danger" onclick="$('#cancelModal').modal('show')">Cancel</button>
				@endif
				@if ($data->{'13a_id'} != "" && in_array($data->{'13a_stat'}, ["issued", "received", "refused"]) && ($user_empno == $data->{'13a_to'} || $reply_id != ""))
					{{-- <a class="btn btn-outline-secondary btn-sm" href="?page=13a-reply&_13a={{ $data->{'13a_id'} }}&id={{ $reply_id }}">Letter of Reply {{ ($reply_read ? "<i class='fa fa-exclamation-circle' style='color: red;'></i>" : "") }}</a> --}}
					<button id="btn-reply" class="btn btn-outline-secondary btn-sm" onclick="viewLetterOfReply('{{ $data->{'13a_id'} }}')">Letter of Reply {!! ($reply_id != "" && !$reply_read ? "<i class='fa fa-exclamation-circle' style='color: red;'></i>" : "") !!}</button>
					@if($reply_id != "" && !$reply_read)
						<script type="text/javascript">
							$('html, body').animate({
							    scrollTop: $('#btn-reply').offset().top
							}, 1000);
						</script>
					@endif
				@endif
				@if (($data->{'13a_issuedby'} == $user_empno || Auth::user()->userAccess('grievance','review')) && $data->{'13a_stat'} == "received")
					<!-- <button class="btn btn-outline-secondary" onclick="$('#hearingModal').modal('show')">Set Hearing Schedule</button> -->
					<button class="btn btn-outline-secondary btn-sm" onclick="viewTranscript('{{ $data->{'13a_id'} }}')">Transcript</button>
				@endif
				@if ($data->{'13a_hearing_loc'} == "" && $data->{'13a_hearing_time'} == "" && ($data->{'13a_issuedby'} == $user_empno || $commit_id != "" || Auth::user()->userAccess('grievance','review')) && $data->{'13a_stat'} == "received")
					{{-- <a href="?page=commitment-plan&_13a={{ $data->{'13a_id'} }}" class="btn btn-outline-secondary btn-sm">Commitment Plan</a> --}}
					<button class="btn btn-outline-secondary btn-sm" onclick="viewCommitment('{{ $data->{'13a_id'} }}')">Commitment Plan</button>
				@endif

				@if ($data->{'13a_stat'} == "pending" && Auth::user()->userAccess('grievance','review'))
					<button class="btn btn-outline-secondary btn-sm" onclick="$('#explanationModal').modal('show')">Needs Explanation</button>
					<button class="btn btn-primary btn-sm" onclick="_13a_checked()">Checked</button>
				@elseif ($data->{'13a_stat'} == "checked" && $signed_issued != "" && $signed_noted == 0 && strpos($data->{'13a_notedby'}, $user_empno) !== false)
					<button type="button" class="btn btn-primary btn-sm btn-click-to-sign" onclick="sign_13a('reviewed', '{{ $user_empno }}')" id="btn-click-to-sign-reviewed">Reviewed</button>
				@elseif ($data->{'13a_stat'} == "reviewed" && ($data->{'13a_issuedby'} == $user_empno || Auth::user()->userAccess('grievance','review')))
					<button type="button" class="btn btn-primary btn-sm" onclick="issue_13a()">Issue</button>
				@elseif (($data->{'13a_stat'} == "issued" || $data->{'13a_stat'} == "received" || $data->{'13a_stat'} == "refused") && Auth::user()->userAccess('grievance','review') && $_13b_id == "")
					{{-- <a href="?page=13b&_13a={{ $data->{'13a_id'} }}" class="btn btn-primary btn-sm">Create 13B</a> --}}
					<button onclick="view13B('', '{{ $data->{'13a_id'} }}')" class="btn btn-primary btn-sm">Create 13B</button>
				@endif
				@if ($data->{'13a_stat'} == "issued" && ($user_empno == $data->{'13a_to'} || $data->{'13a_issuedby'} == $user_empno))
					<!-- <button class="btn btn-primary" onclick="_13a_receive()">Receive</button> -->
					<button class="btn btn-primary btn-sm btn-click-to-sign" onclick="sign_13a('received', '{{ $data->{'13a_to'} }}')" id="btn-click-to-sign-received">Receive</button>
					<button class="btn btn-danger btn-sm" onclick="_13a_refuse()">Refuse</button>
				@endif
				@if ($_13b_id != "")
					{{-- <a href="?page=13b&no={{ $_13b_id }}&_13a={{ $data->{'13a_id'} }}" class="btn btn-info btn-sm">View 13B</a> --}}
					<button onclick="view13B('{{ $_13b_id }}')" class="btn btn-info btn-sm">View 13B</button>
				@endif
				@if ($data->{'13a_id'} != "")
					<button type="button" class="btn btn-outline-secondary btn-sm" onclick="print_13a()"><i class="fa fa-print"></i></button>
				@endif
			</div>
		</div>
	</div>
</div>

<div id="signature-pad-wrapper" class="signature-pad-wrapper d-flex flex-column">
	<input type="hidden" id="sign-type" value="">
	<input type="hidden" id="sign-empno" value="">
	<div id="signature-pad" class="signature-pad flex-grow-1">
  		<canvas id="signature-pad-canvas" class="signature-pad-canvas h-100 w-100"></canvas>
	</div>
  	<div class="d-grid d-block">
  	  	<div id="btn-for-sign" class="btn-group">
			<button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" onclick="cancel_13a_sign()">Cancel</button>
			<button type="button" class="btn btn-outline-secondary btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
			<button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" onclick="save_13a_sign()">Save</button>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="issuedbyModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-issuedby">
				<div class="modal-header">
					<h4 class="modal-title" id="modalTitle">
						<center>Issued by</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<select class="form-control selectpicker" id="_13a-issuedby" title="Issued by Dept Head" data-live-search="true" required>
						@foreach ($employees as $k => $v)
							@if($v['ji_remarks'] == 'Active' || $data->{'13a_issuedby'} == $v['pers_empno'])
								<option job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}" {{ ($data->{'13a_issuedby'} == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
							@endif
						@endforeach
					</select>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="notedbyModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle2">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-notedby">
				<div class="modal-header">
					<h4 class="modal-title" id="modalTitle2">
						<center>Noted by</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<select class="form-control selectpicker" id="_13a-notedby" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
						@foreach ($employees as $k => $v)
							@if($v['ji_remarks'] == 'Active' || strpos($data->{'13a_notedby'}, $v['pers_empno']) !== false)
								<option job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
							@endif
						@endforeach
					</select>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="otheriryModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle3">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-otherir">
				<div class="modal-header">
					<h4 class="modal-title" id="modalTitle3">
						<center>Attach IR</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<select class="form-control selectpicker" id="_13a-otherir" title="Select IR/s" data-live-search="true" multiple data-actions-box="true" required>
						@foreach ($irList as $v)
							<option value="{{ $v->ir_id }}">{{ '('. date("m/d/Y", strtotime($v->ir_date)) . ') ' .$v->ir_subject . " - " . $v->from_name }}</option>
						@endforeach
					</select>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="explanationModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle4">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-explanation">
				<div class="modal-header">
					<h4 class="modal-title" id="modalTitle4">
						<center>Remarks</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<textarea id="_13a-remarks" class="form-control"></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="witnessModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle5">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-witness">
				<div class="modal-header">
					<h4 class="modal-title" id="modalTitle5">
						<center>Witnessess</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<select class="form-control selectpicker" id="_13a-witness" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
						@foreach ($employees as $k => $v)
							@if($v['ji_remarks'] == 'Active' || strpos($data->{'13a_witness'}, $v['pers_empno']) !== false)
								<option job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
							@endif
						@endforeach
					</select>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="hearingModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle6">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-hearing">
				<div class="modal-header">
					<h4 class="modal-title" id="modalTitle6">
						<center>Update Response Time and Location:</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<label class="col-form-label col-md-3">Date and Time:</label>
						<div class="col-md-7">
							<input class="form-control" type="datetime-local" id="_13a-hearing-datetime" value="{{ !($data->{'13a_hearing_time'} == "" || $data->{'13a_hearing_time'} == "0000-00-00") ? date("Y-m-d\TH:i", strtotime($data->{'13a_hearing_time'})) : "" }}" required>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-form-label col-md-3">Location:</label>
						<div class="col-md-7">
							<input class="form-control" type="text" id="_13a-hearing-place" value="{{ $data->{'13a_hearing_loc'} }}" required>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="violationModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle7">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="modalTitle7">
					<center>Violation</center>
				</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div>
					<div class="row mb-3">

						<div class="col-md-6">
							<div class="row mb-3">
								<label class="control-label col-md-3">Article:</label>
								<div class="col-md-9">
									<select id="_13a-article" class="selectpicker form-control" data-live-search="true" title="Select" required>
										@foreach ($rnrList as $v)
											<option value="{{ $v->rnrart_articlecode }}" articleName="{{ htmlentities($v->rnrart_articlename, ENT_QUOTES) }}">{{ $v->rnrart_articlecode . "-" . $v->rnrart_articlename }}</option>
										@endforeach
										<option value="other">Other</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<div class="row mb-3">
								<label class="control-label col-md-3">Section:</label>
								<div class="col-md-9">
									<select id="_13a-section" class="selectpicker form-control" data-live-search="true" title="Select" required>
										@foreach ($rnrList as $v)
											@foreach ($v->sections as $v2)
											<option class="rnrsec" _article="{{ $v->rnrart_articlecode }}" value="{{ $v2->rnrsec_section }}" sectionName="{{ htmlentities($v2->rnrsec_sectionname, ENT_QUOTES) }}">{{ $v2->rnrsec_section . "-" . $v2->rnrsec_sectionname }}</option>
											@endforeach
										@endforeach
									</select>
								</div>
							</div>
						</div>

					</div>
					<div class="row mb-3" id="div-section-desc" style="display: none;">
						<label class="col-form-label col-md-12">Description:</label>
						<div class="col-md-12">
							<p id="_13a-section-desc"></p>
						</div>
					</div>

					<div id="divother" style="display: none; border-top: 1px solid gray; padding-top: 10px;">

						<div class="row mb-3">
							<label class="control-label col-md-3">Source:</label>
							<div class="col-md-9">
								<input type="text" class="form-control" id="_13a-other-src" maxlength="300">
							</div>
						</div>

						<div class="row mb-3">
							<label class="control-label col-md-3">Article Code:</label>
							<div class="col-md-9">
								<input type="text" class="form-control" id="_13a-article-code-other" maxlength="15">
							</div>
						</div>

						<div class="row mb-3">
							<label class="control-label col-md-3">Article Name:</label>
							<div class="col-md-9">
								<textarea class="form-control" id="_13a-article-name-other" wrap="soft"></textarea>
							</div>
						</div>

						<div class="row mb-3">
							<label class="control-label col-md-3">Section Code:</label>
							<div class="col-md-9">
								<input type="text" class="form-control" id="_13a-section-code-other" maxlength="15">
							</div>
						</div>

						<div class="row mb-3">
							<label class="control-label col-md-3">Section Name:</label>
							<div class="col-md-9">
								<input type="text" class="form-control" id="_13a-section-name-other">
							</div>
						</div>

						<div class="row mb-3">
							<label class="control-label col-md-12">Description:</label>
							<div class="col-md-12">
								<textarea class="form-control" id="_13a-section-desc-other"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="btn-add-violation">Add</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelmodalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="form-cancel">
				<div class="modal-header">
					<h4 class="modal-title" id="cancelmodalTitle">
						<center>Cancel</center>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<textarea id="cancel-remarks" class="form-control" placeholder="Remarks..." required></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<iframe src="" id="print_13a" style="display: none;"></iframe>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" defer></script>

<script type="text/javascript">
	var canvas, signaturePad;

	function resizeCanvas() {
		if (canvas) {
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.clear();
		}
	}

	$(document).ready(function() {
		canvas = document.getElementById("signature-pad").querySelector("canvas");
		if(canvas){
			signaturePad = new SignaturePad(canvas, {
				backgroundColor: 'rgb(255, 255, 255)'
			});

			signaturePad.minWidth = 3;
			signaturePad.maxWidth = 3;
		}

		window.onresize = resizeCanvas;

		$('#btn-for-sign button[data-action="clear"]').click(function(){
			signaturePad.clear();
		});

		$('.selectpicker').selectpicker('refresh');

		$("#_13a-position").text($("#_13a-to option:selected").attr("attr_pos"));
		$("#_13a-dept").text($("#_13a-to option:selected").attr("attr_dept"));
		$("#_13a-company").text($("#_13a-to option:selected").attr("attr_company"));

		$("#immediate_action").change(function(){
			if($(this).is(':checked')){
				$(this).parent().addClass('checked');
			}else{
				$(this).parent().removeClass('checked');
			}
		});

		$("#_13a-article").change(function() {
			var _art = $(this).val();
			$("#_13a-section-desc").text("");
			$("#_13a-other-src").val("");
			$("#_13a-article-code-other").val("");
			$("#_13a-article-name-other").val("");
			$("#_13a-section-code-other").val("");
			$("#_13a-section-name-other").val("");
			$("#_13a-section-desc-other").val("");
			if (_art != "other") {
				$("#div-section-desc").show();
				$("#divother").hide();
				$("#_13a-section").prop("disabled", false);
				$("#_13a-section").find("option.rnrsec").each(function() {
					if ($(this).attr("_article") == _art) {
						$(this).show();
						$(this).css("display", "");
					} else {
						$(this).hide();
						$(this).css("display", "none");
					}
				});
			} else {
				$("#div-section-desc").hide();
				$("#_13a-section").prop("disabled", true);
				$("#divother").show();
			}

			$("#_13a-section").val("");
			$("#_13a-violation-desc").val("");
			$("#_13a-section").selectpicker("refresh");
		});

		$("#_13a-section").change(function() {
			$("#_13a-section-desc").text("");
			$.post("rnr.php", {
				rnrcontent: $("#_13a-article").val() + "||" + $("#_13a-section").val()
			},
			function(res1) {
				$("#_13a-section-desc").text(res1);
			});
		});

		$("#_13a-to").change(function() {
			$("#_13a-position").text($("#_13a-to option:selected").attr("attr_pos"));
			$("#_13a-dept").text($("#_13a-to option:selected").attr("attr_dept"));
			$("#_13a-company").text($("#_13a-to option:selected").attr("attr_company"));
		});

		if ($("#_13a-penalty").val() == "suspended for") {
			$("#div-suspendday").show();
			$("#_13a-suspendday").attr("required", true);
		} else {
			$("#div-suspendday").hide();
			$("#_13a-suspendday").attr("required", false);
			$("#_13a-suspendday").val(1);
		}

		$("#_13a-penalty").change(function() {
			if ($(this).val() == "suspended for") {
				$("#div-suspendday").show();
				$("#_13a-suspendday").attr("required", true);
			} else {
				$("#div-suspendday").hide();
				$("#_13a-suspendday").attr("required", false);
				$("#_13a-suspendday").val(1);
			}
		});

		$("#_13a-from").change(function() {
			$("#_13a-posfrom").val($("#_13a-from option:selected").attr("_job")).selectpicker("refresh");
		});

		$("#btn-save-13a").click(function() {
			$("#_13a-stat").val("draft");
			$("#form-13a [type='submit']").click();
		});

		$("#btn-edit-13a").click(function() {
			$("#form-13a fieldset").attr("disabled", false);
			$("#btn-save-13a").show();
			$(this).hide();
		});

		$("#btn-post-13a").click(function() {
			$("#_13a-stat").val("pending");
			$("#form-13a [type='submit']").click();
		});

		$("#form-13a").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let violation_list = [];
			$("#violation-list tr").each(function() {
				violation_list.push({
					articleCode: $(this).attr("articleCode"),
					articleName: $(this).attr("articleName"),
					sectionCode: $(this).attr("sectionCode"),
					sectionName: $(this).attr("sectionName"),
					sectionDesc: $(this).attr("sectionDesc"),
					vid: $(this).attr("vid"),
					othersrc: $(this).attr("othersrc")
				});
			});

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("to", $("#_13a-to").val());
			formData.append("cc", $("#_13a-cc option:selected").map((_, el) => el.value).get().join(","));
			formData.append("from", $("#_13a-from").val());
			formData.append("frompos", $("#_13a-posfrom").val());
			formData.append("act", $("#_13a-act").val());
			formData.append("violation", JSON.stringify(violation_list));
			formData.append("datetime", $("#_13a-datetime").val());
			formData.append("place", $("#_13a-place").val());
			formData.append("penalty", $("#_13a-penalty").val());
			formData.append("offense", $("#_13a-offense").val());
			formData.append("offensetype", $("#_13a-offense-type").val());
			formData.append("regarding", $("#_13a-regarding").val());
			formData.append("stat", $("#_13a-stat").val());
			formData.append("suspendday", $("#_13a-suspendday").val());
			formData.append("ir", "{{ $data->{'13a_ir'} }}");
			formData.append("immediate_action", $("#immediate_action:checked").length);

			let response = await fetch('/grievance/13a/save', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			const contentType = response.headers.get('Content-Type');
			let errmsg = '';
			let result = null;
			if (contentType && contentType.includes('application/json')) {
				result = await response.json();
				errmsg = !result.success ? result.error : '';
			} else {
				result = await response.text();
			}

			if (response.ok && !errmsg) {
				if($("#_13a-stat").val() != "draft"){
					alert("13A posted");
					if($('#_13aTab button.active').length){
						close13A();
						$('#_13aTab button.active').click();
					}else{
						window.location = '/grievance/13a';
					}
				}else if($("#_13a-id").val()){
					alert("13A saved");
					$('.modal').modal('hide');
					view13A($("#_13a-id").val());
				}else{
					alert("13A saved");
					window.location = '/grievance/13a';
				}
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${errmsg}</p>`);
			}
		});

		$("#form-notedby").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("noted", $("#_13a-notedby option:selected").map((_, el) => el.value).get().join(","));
			formData.append("notedpos", $("#_13a-notedby option:selected").map((_, el) => $(el).attr("job") || '').get().join(","));

			let response = await fetch('/grievance/13a/set/notedby', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Saved');
				$('.modal').modal('hide');
				view13A($("#_13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-issuedby").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("issued", $("#_13a-issuedby").val());
			formData.append("issuedpos", $("#_13a-issuedby option:selected").attr("job") || '');

			let response = await fetch('/grievance/13a/set/issuedby', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Saved');
				$('.modal').modal('hide');
				view13A($("#_13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-explanation").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("remarks", $("#_13a-remarks").val());

			let response = await fetch('/grievance/13a/explanation', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Sent to Needs Explanation');
				close13A();
				$('#_13aTab button.active').click();
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-witness").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("witness", $("#_13a-witness option:selected").map((_, el) => el.value).get().join(","));
			formData.append("witnesspos", $("#_13a-witness option:selected").map((_, el) => $(el).attr("job") || '').get().join(","));

			let response = await fetch('/grievance/13a/set/witness', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Saved');
				$('.modal').modal('hide');
				view13A($("#_13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-hearing").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("datetime", $("#_13a-hearing-datetime").val());
			formData.append("place", $("#_13a-hearing-place").val());

			let response = await fetch('/grievance/13a/set/hearing', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Saved');
				$('.modal').modal('hide');
				view13A($("#_13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-otherir").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());
			formData.append("ir", $("#_13a-otherir option:selected").map((_, el) => el.value).get().join(","));

			let response = await fetch('/grievance/13a/set/ir', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Saved');
				$('.modal').modal('hide');
				view13A($("#_13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$('textarea').autoResize();

		$("#_13a-article-name-other").keypress("input", function(e) {
			if (e.which === 13) e.preventDefault();
		});

		$("#_13a-article-name-other").on("input", function(e) {
			$(this).val($(this).val().replace(/\n/g, ''));
			this.style.height = 'auto';
			this.style.height = (this.scrollHeight + 5) + 'px';
		});

		$("#btn-add-violation").click(function() {
			let v_src = $("#_13a-other-src").val();
			let v_article_code = $("#_13a-article").val() == "other" ? $("#_13a-article-code-other").val() : $("#_13a-article").val();
			let v_article_name = $("#_13a-article").val() == "other" ? $("#_13a-article-name-other").val() : $("#_13a-article option:selected").attr("articleName");

			let v_section_code = $("#_13a-article").val() == "other" ? $("#_13a-section-code-other").val() : $("#_13a-section").val();
			let v_section_name = $("#_13a-article").val() == "other" ? $("#_13a-section-name-other").val() : $("#_13a-section option:selected").attr("sectionName");

			let v_desc = $("#_13a-article").val() == "other" ? $("#_13a-section-desc-other").val() : $("#_13a-section-desc").text();

			let tr = "<tr articleCode=\"" + encodeHtmlEntities(v_article_code) + "\" ";
			tr += "articleName=\"" + encodeHtmlEntities(v_article_name) + "\" ";
			tr += "sectionCode=\"" + encodeHtmlEntities(v_section_code) + "\" ";
			tr += "sectionName=\"" + encodeHtmlEntities(v_section_name) + "\" ";
			tr += "sectionDesc=\"" + encodeHtmlEntities(v_desc) + "\" ";
			tr += "vid=\"\" ";
			tr += "othersrc=\"" + encodeHtmlEntities(v_src) + "\">";

			tr += "<td><span style='display: block; font-weight: bold;'>" + v_src + "</span>" + v_article_code + ": " + v_article_name + "</td>";
			tr += "<td>" + v_section_code + ": " + v_section_name + "</td>";
			tr += "<td>" + v_desc + "</td>";
			tr += "<td><button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" onclick=\"delviolation(this)\"><i class=\"fa fa-times\"></i></button></td>";

			tr += "</tr>";

			$("#violation-list").append(tr);
			$("#violationModal").modal("hide");
		});


		$("#form-cancel").submit(async function(e) {
			e.preventDefault();
			$('#err-msg').html("");

			if (confirm("Proceed?")) {
				let formData = new FormData();
				formData.append("id", $("#_13a-id").val());
				formData.append("remarks", $("#cancel-remarks").val());

				let response = await fetch('/grievance/13a/cancel', {
					method: "POST",
					body: formData,
					headers: {
						"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
					}
				});

				if (response.ok) {
					alert('Cancelled');
					close13A();
					$('#_13aTab button.active').click();
				} else {
					let result = await response.json();
					$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
				}
			}
		});
	});

	function encodeHtmlEntities(str) {
		return $('<div/>').text(str).html();
	}

	function addviolation() {
		$("#violationModal input, #violationModal textarea, #violationModal select").val("");
		$("#_13a-section-desc").text("");
		$("#div-section-desc").show();
		$("#divother").hide();
		$("#_13a-section").prop("disabled", false);
		$("#_13a-section").find("option.rnrsec").hide();
		$("#_13a-section").find("option.rnrsec").css("display", "none");
		$("#violationModal select").selectpicker("refresh");
		$("#violationModal").modal("show");
	}

	function delviolation(e) {
		$(e).parents("tr").remove();
	}

	async function del_otherir(_irid) {
		if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let response = await fetch('/grievance/13a/delete/ir/'+$("#_13a-id").val()+'/'+_irid, {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Removed');
				$('.modal').modal('hide');
				view13A($("#_13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	async function update_violation() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13a-id").val());
		formData.append("violation", $("#_13a-article").val() + "|" + $("#_13a-section").val());
		formData.append("desc", $("#_13a-violation-desc").val());

		let response = await fetch('/grievance/13a/set/violation', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Saved');
			$('.modal').modal('hide');
			view13A($("#_13a-id").val());
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	function edit_witness(_witness1) {
		$("#_13a-witness").val(_witness1.split(",")).selectpicker("refresh");
		$("#witnessModal").modal("show");
	}

	function edit_noted(_noted1) {
		$("#_13a-notedby").val(_noted1.split(",")).selectpicker("refresh");
		$("#notedbyModal").modal("show");
	}

	function edit_issued(_issued1) {
		$("#_13a-issuedby").val(_issued1).selectpicker("refresh");
		$("#issuedbyModal").modal("show");
	}

	async function _13a_checked() {
		if ("{{ $data->{'13a_issuedby'} }}" == "") {
			alert("Please add Issued By");
		} else if ("{{ $data->{'13a_notedby'} }}" == "") {
			alert("Please add Noted By");
		} else {
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());

			let response = await fetch('/grievance/13a/check', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Checked');
				close13A();
				$('#_13aTab button.active').click();
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	function sign_13a(_type1, _id) {
		if ($(window).height() > $(window).width()) {
	        alert("Please rotate phone to landscape");
	    } else {
	    	$("#sign-type").val(_type1);
			$("#sign-empno").val(_id);

			$("body").addClass('overflow-hidden');
			$("#signature-pad-wrapper").addClass('show');

			$("#div_sign").css({"width": "100%", "height": "100vh"});
	    	$("#signature-pad").css({"width": "100%", "height": "90%"});

			// setTimeout(function(){
				resizeCanvas();
			// }, 1000);

	    }
	}

	function cancel_13a_sign() {
		$("body").removeClass('overflow-hidden');
		$("#signature-pad-wrapper").removeClass('show');
	}

	async function save_13a_sign() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13a-id").val());
		formData.append("sign", signaturePad.toSVG());
		formData.append("signtype", $("#sign-type").val());
		formData.append("empno", $("#sign-empno").val());

		let response = await fetch('/grievance/13a/sign', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			$("body").removeClass('overflow-hidden');
			alert('Signed');
			close13A();
			$('#_13aTab button.active').click();
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function issue_13a() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13a-id").val());

		let response = await fetch('/grievance/13a/issue', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Issued');
			close13A();
			$('#_13aTab button.active').click();
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function del_13a() {
		if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let response = await fetch('/grievance/13a/delete/'+$("#_13a-id").val(), {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Removed');
				close13A();
				$('#_13aTab button.active').click();
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	async function _13a_receive() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13a-id").val());
		formData.append("emp", "{{ $data->{'13a_to'} }}");

		let response = await fetch('/grievance/13a/receive', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Received');
			close13A();
			$('#_13aTab button.active').click();
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function _13a_refuse() {
		if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13a-id").val());

			let response = await fetch('/grievance/13a/refuse', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Refused');
				close13A();
				$('#_13aTab button.active').click();
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	async function print_13a() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13a-id").val());

		let response = await fetch('/grievance/13a/print', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Received');
			const html = await response.text();
			$("#print_13a").attr("srcdoc", html);
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}
</script>