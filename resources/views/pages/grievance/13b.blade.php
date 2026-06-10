<style type="text/css">
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

<div class="card">
	<div class="card-header">
		<span class="float-end">
			@if($data->{'13b_id'} != "" && (($user_empno == $data->{'13b_from'} && $data->{'13b_stat'} == "draft") || Auth::user()->userAccess('grievance','review')))
				<button class="btn btn-danger btn-sm" onclick="del_13b()"><i class="fa fa-trash"></i></button>&emsp;|&emsp;
			@endif
			<button class="btn btn-close" onclick="close13B()"></button>
		</span>
		<label>13B - Form</label>
	</div>
	<div class="card-body">
		<input type="hidden" id="_13b-id" value="{{ $data->{'13b_id'} }}">
		<input type="hidden" id="_13b-to" value="{{ $data->{'13b_to'} ?? $_13a->{'13a_to'} }}">
		<input type="hidden" id="_13b-stat" value="{{ $data->{'13b_stat'} ?? "draft" }}">
		<input type="hidden" id="_13a-id" value="{{ $_13a->{'13a_id'} }}">
		<input type="hidden" id="_13a-suspendday" value="{{ $_13a->{'13a_suspendday'} }}">
		<input type="hidden" id="_13a-penalty" value="{{ $_13a->{'13a_penalty'} }}">
		@if(in_array($data->{'13b_stat'}, ["issued", "reviewed", "received"]))
			<div style="width: 8.5in; margin: auto;">
				<p>HRD Form13B</p>
				<p>&nbsp;</p>
				<center><p>MEMORANDUM NO. <u>{{ $data->{'13b_memo_no'} }}</u></p></center>
				<table width="100%">
					<tr>
						<td width="100px">TO:</td>
						<td>{{ isset($employees[$data->{'13b_to'}]) ? trim(ucwords($employees[$data->{'13b_to'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13b_to'}]['pers_midname']))." ".$employees[$data->{'13b_to'}]['pers_lastname']) : "" }}</td>	
						<td>DATE:</td>
						<td>{{ date("F d, Y",strtotime($data->{'13b_date'})) }}</td>	
					</tr>
					<tr>
						<td width="100px">POSITION:</td>
						<td>{{ isset($positionList[$data->{'13b_pos'}]) ? $positionList[$data->{'13b_pos'}]->jd_title : "" }}</td>	
						<td>DEPT/BRANCH:</td>
						<td>{{ isset($departmentList[$data->{'13b_dept'}]) ? $departmentList[$data->{'13b_dept'}]->Dept_Name : "" }}</td>	
					</tr>
					<tr>
						<td width="100px">COMPANY:</td>
						<td>{{ isset($companyList[$data->{'13b_company'}]) ? $companyList[$data->{'13b_company'}]->C_Name : "" }}</td>
					</tr>
				</table>
				<p>&nbsp;</p>
				<table width="100%">
					<tr>
						<td width="100px" style="vertical-align: top;">RE:</td>
						<td>{{ $data->{'13b_regarding'} }}</td>
					</tr>
				</table>
				<table width="100%">
					<tr>
						<td width="100px" >FROM:</td>
						<td>{{ isset($employees[$data->{'13b_from'}]) ? trim(ucwords($employees[$data->{'13b_from'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13b_from'}]['pers_midname']))." ".$employees[$data->{'13b_from'}]['pers_lastname']) : "" }}</td>
						<td>POSITION:</td>
						<td>{{ isset($positionList[$data->{'13b_frompos'}]) ? $positionList[$data->{'13b_frompos'}]->jd_title : "" }}</td>	
					</tr>
				</table>
				<p>&nbsp;</p>
				<p>This acknowledges your letter in reply to Memorandum no. <u>{{ $data->{'13b_memo_no'} }}</u>  to show cause why you should not be </p>
				<br>
				<table width="100%">
					<tr>
						<td style="text-align: center; width: 33.33%; vertical-align: top;">{!! ($_13a->{'$_13a_penalty'} == "Issued a written Reprimand or warning" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;Issued a written Reprimand or warning</td>
						<td style="text-align: center; width: 33.33%; vertical-align: top;">{!! ($_13a->{'$_13a_penalty'} == "suspended for" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;suspended for {{ $_13a->{'13a_suspendday'} }} day/s</td>
						<td style="text-align: center; width: 33.33%; vertical-align: top;">{!! ($_13a->{'$_13a_penalty'} == "terminated with cause" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;terminated with cause</td>
					</tr>
				</table>
				<br>
				<p>
					For committing the&emsp;&emsp;
					{!! ($_13a->{'13a_offense'} == "1st offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;1st offense&emsp;&emsp;
					{!! ($_13a->{'13a_offense'} == "2nd offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;2nd offense&emsp;&emsp;
					{!! ($_13a->{'13a_offense'} == "3rd offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;3rd offense
					<br><br>
					of a&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
					{!! ($_13a->{'13a_offensetype'} == "minor offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;minor offense&emsp;&emsp;
					{!! ($_13a->{'13a_offensetype'} == "major offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;major offense&emsp;&emsp;
					{!! ($_13a->{'13a_offensetype'} == "grave offense" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;grave offense
				</p>

				<p>Due to violation of {!! nl2br($violation_str) !!}</p>
				<p>&nbsp;</p>
				<p>After a serious study of the reasons stated in your reply letter, the Committee</p>
				<p>&nbsp;</p>

				<p>
					&emsp;&emsp;{!! ($data->{'13b_verdict'} == "Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.<br><br>

					&emsp;&emsp;{!! ($data->{'13b_verdict'} == "Does NOT find your reason/s acceptable due to the fact that" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;Does NOT find your reason/s acceptable due to the fact that <u>{{ $data->{'13b_verdictreason'}!="" ? $data->{'13b_verdictreason'} : "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;" }}</u><br><br>
					{!! ($data->{'13b_penalty'}!="" && (($data->{'13b_penalty'} == "suspended for" && $data->{'13b_suspendday'}<$_13a->{'13a_suspendday'}) || $data->{'13b_penalty'}!=$_13a->{'13a_penalty'}) ? "&emsp;&emsp;&emsp;Your sanction has however been mitigated from suspension but you are reminded to be more cautious and vigilant as the next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.<br><br>" : "") !!}
					@if($data->{'13b_verdict'} == "Does NOT find your reason/s acceptable due to the fact that")
						&emsp;&emsp;&emsp;&emsp;{!! !($data->{'13b_verdicteffectdt'} == "" || $data->{'13b_verdicteffectdt'} == "0000-00-00") ? "Effective <u>".date("F d, Y",strtotime($data->{'13b_verdicteffectdt'}))."</u> you" : "&emsp;&emsp;&emsp; </u> You" !!} are hereby<br><br>
					@else
						&emsp;&emsp;&emsp;&emsp;Effective <u>{!! !($data->{'13b_verdicteffectdt'} == "" || $data->{'13b_verdicteffectdt'} == "0000-00-00") ? date("F d, Y",strtotime($data->{'13b_verdicteffectdt'})) : "&emsp;&emsp;&emsp;" !!}</u> you are hereby<br><br>
					@endif

					&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;{!! ($data->{'13b_penalty'} == "Issued a written Reprimand or warning" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;Issued a written Reprimand or warning
						<br>
					&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;{!! ($data->{'13b_penalty'} == "suspended for" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;suspended for {{ $data->{'13b_suspendday'} }} day/s
					<br>
					&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;{!! ($data->{'13b_penalty'} == "terminated with cause" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;terminated with cause
					<br><br>
					&emsp;&emsp;{!! ($data->{'13b_verdict'} == "Finds that this needs further investigation thus, you will be notified not later than" ? '<i class="fa-regular fa-square-check"></i>' : '<i class="fa-regular fa-square"></i>') !!}&nbsp;Finds that this needs further investigation thus, you will be notified not later than <u>{{ !($data->{'13b_notification'} == "" || $data->{'13b_notification'} == "0000-00-00") ? date("Y-m-d",strtotime($data->{'13b_notification'})) : "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;" }}</u>
				</p>
				<br>

				<table width="100%">
					<tr>
						<td style="vertical-align: middle; width: 55%;">
							<div>
								Noted by:

								@foreach (explode(',', $data->{'13b_notedby'}) as $k => $v)
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
											<td style='text-align: center;'>{{ isset($data->{'13b_notedbypos'}[$k]) && isset($positionList[$data->{'13b_notedbypos'}[$k]]) ? $positionList[$data->{'13b_notedbypos'}[$k]]->jd_title : "" }}</td>
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
									<tr>
										<td style="width: 250px; text-align: center;">{{ isset($employees[$data->{'13b_issuedby'}]) ? trim(ucwords($employees[$data->{'13b_issuedby'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13b_issuedby'}]['pers_midname']))." ".$employees[$data->{'13b_issuedby'}]['pers_lastname']) : "" }}</td>
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
										<td colspan="2" align="center">
											<div id="div-signature-received" class="div-signature" align="center">
												{!! !empty($signatures['received']) && $signatures['received']->where('gs_empno', $data->{'13b_to'})->first() ? $signatures['received']->where('gs_empno', $data->{'13b_to'})->first()->gs_sign : '' !!}
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											{{ isset($employees[$data->{'13b_to'}]) ? trim(ucwords($employees[$data->{'13b_to'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13b_to'}]['pers_midname']))." ".$employees[$data->{'13b_to'}]['pers_lastname']) : "" }}
										</td>
									</tr>
									<tr style="border-top: solid black 1px;">
										<td colspan="2">Employee</td>
									</tr>
									<tr>
										<td>Date Received: </td>
										<td style="width: 200px; border-bottom: solid 1px black;">{{ !($data->{'13b_datereceived'} == "" || $data->{'13b_datereceived'} == "0000-00-00") ? date("F d, Y", strtotime($data->{'13b_datereceived'})) : "" }}</td>
									</tr>
									<tr>
										<td>Time: </td>
										<td style="width: 200px; border-bottom: solid 1px black;">{{ !($data->{'13b_datereceived'} == "" || $data->{'13b_datereceived'} == "0000-00-00") ? date("h:i A", strtotime($data->{'13b_datereceived'})) : "" }}</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>

				@if($data->{'13b_stat'} == "refused")
					<div id="div-witness">
						<p>REFUSED TO ACKNOWLEDGE RECEIPT</p>
						<p>Witnessess:</p>

						@if ($data->{'13b_witness'} != "")
							@foreach (explode(',', $data->{'13b_witness'}) as $k => $v)
								<table style="display: inline-table;">
									<tr>
										<td colspan="2" align="center">
											<div id="div-signature-witness-{{ $v }}" class="div-signature" align="center">
												{!! !empty($signatures['witness']) && $signatures['witness']->where('gs_empno', $v)->first() ? $signatures['witness']->where('gs_empno', $v)->first()->gs_sign : '' !!}
											</div>
										</td>
									</tr>
									<tr>
										<td>{{ $loop->iteration }}.</td>
										<td style='width:250px; text-align: center;'>{{ isset($employees[$v]) ? trim(ucwords($employees[$v]['pers_firstname']." ".getNameInitials($employees[$v]['pers_midname']))." ".$employees[$v]['pers_lastname']) : "" }}</td>
									</tr>
									<tr style='border-top: solid black 1px; text-align: center;'>
										<td colspan="2">(Signature over printed name)</td>
									</tr>
								</table>
							@endforeach
						@else
							<table style="display: inline-table;">
								<tr>
									<td style="height: 50px;">
									</td>
								</tr>
								<tr>
									<td style='width:250px;'>1.</td>
								</tr>
								<tr style='border-top: solid black 1px; text-align: center;'>
									<td>(Signature over printed name)</td>
								</tr>
							</table>
							&emsp;&emsp;&emsp;
							<table style="display: inline-table;">
								<tr>
									<td style="height: 50px;">
									</td>
								</tr>
								<tr>
									<td style='width:250px;'>2.</td>
								</tr>
								<tr style='border-top: solid black 1px; text-align: center;'>
									<td>(Signature over printed name)</td>
								</tr>
							</table>
						@endif
					</div>
				@endif
			</div>
		@else
			<form id="form-13b">
				<fieldset {{ ($data->{'13b_id'}!="" ? "disabled" : "") }}>

					<div class="row mb-3">
						<label class="col-md-2">MEMORANDUM NO.</label>
						<div class="col-md-4">
							<label>{{ $data->{'13b_memo_no'} }}</label>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">TO</label>
								<div class="col-md-9">
									<p>{{ isset($employees[$data->{'13b_to'}]) ? trim(ucwords($employees[$data->{'13b_to'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13b_to'}]['pers_midname']))." ".$employees[$data->{'13b_to'}]['pers_lastname']) : "" }}</p>
								</div>
							</div>

							<div class="row mb-3">
								<label class="col-md-3">CC:</label>
								<div class="col-md-9">
									@if ((($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "") && $data->{'13b_from'} == $user_empno) || (Auth::user()->userAccess('grievance','review') && ($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "" || $data->{'13b_stat'} == "pending")))
										<select class="form-control selectpicker" id="_13b-cc" title="Select Employee" data-live-search="true" multiple data-actions-box="true" required>
											@foreach ($employees as $k => $v)
												@if($v['ji_remarks'] == 'Active' || strpos($data->{'13b_cc'}, $v['pers_empno']) !==  false)
													<option value="{{ $v['pers_empno'] }}" {{ strpos($data->{'13b_cc'}, $v['pers_empno']) !==  false ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
												@endif
											@endforeach
										</select>
									@else
										@foreach (explode(',', $data->{'13b_cc'}) as $cc_k)
										<label class="col-form-label">{{ isset($employees[$cc_k]) ? trim(ucwords($employees[$cc_k]['pers_lastname'].", ".$employees[$cc_k]['pers_firstname'])) : "" }}</label>
										@endforeach
									@endif
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">DATE</label>
								<div class="col-md-5">
									<p>{{ date("F d, Y", strtotime($data->{'13b_date'})) }}</p>
								</div>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">POSITION</label>
								<div class="col-md-9">
		                        	<p>{{ isset($positionList[$data->{'13b_pos'}]) ? $positionList[$data->{'13b_pos'}]->jd_title : "" }}</p>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">DEPT/BRANCH</label>
								<div class="col-md-9">
		                          	<p>{{ isset($departmentList[$data->{'13b_dept'}]) ? $departmentList[$data->{'13b_dept'}]->Dept_Name : "" }}</p>
								</div>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">COMPANY</label>
								<div class="col-md-9">
		                          	<p>{{ isset($companyList[$data->{'13b_company'}]) ? $companyList[$data->{'13b_company'}]->C_Name : "" }}</p>
								</div>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">RE</label>
								<div class="col-md-9">
									<p>{{ $data->{'13b_regarding'} }}</p>
								</div>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<div class="row mb-3">
								<label class="col-md-3">FROM</label>
								<div class="col-md-7">
									@if ($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "")
										<select class="form-control selectpicker" id="_13b-from" title="Select Employee" data-live-search="true" disabled>
											@foreach ($employees as $k => $v)
												@if($v['ji_remarks'] == 'Active' || $data->{'13b_from'} == $v['pers_empno'])
													<option _job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}" {{ ($data->{'13b_from'} == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
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
								<label class="col-md-2">POSITION</label>
								<div class="col-md-7">
									@if($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "")
										<select id="_13b-posfrom" name="13b-posfrom" class="form-control selectpicker" data-live-search="true" title="Select Position" disabled>
											@foreach ($positionList as $v) {
												<option value="{{ $v->jd_code }}" {{ ($data->{'13b_frompos'} == $v->jd_code ? "selected" : "") }}>{{ $v->jd_title }}</option>
											@endforeach
										</select>
									@else
										<label class="col-form-label">{{ isset($positionList[$data->{'13b_frompos'}]) ? $positionList[$data->{'13b_frompos'}]->jd_title : "" }}</label>
									@endif
								</div>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-12">
							<p>This acknowledges your letter in reply to Memorandum no. <b>{{ $_13a->{'13a_memo_no'} }}</b> to show cause why you not be <b>{{ ($_13a->{'13a_penalty'} == "suspended for" ? $_13a->{'13a_penalty'}." ".$_13a->{'13a_suspendday'}." day/s" : $_13a->{'13a_penalty'}) }}</b> for committing the <b>{{ $_13a->{'13a_offense'} }}</b> of a <b>{{ $_13a->{'13a_offensetype'} }}</b></p>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-md-12">Due to violation of {!! nl2br($violation_str) !!}</label>
					</div>
					<div class="row mb-3">
						<label class="col-md-12">After a serious study of the reasons stated in your reply letter, the Committee</label>
						
						<p class="col-md-12"><input required type="radio" _optnum="1" name="13b-verdict" value="Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." {{ ($data->{'13b_verdict'} == "Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken." ? "checked" : "") }}> Has found the reason/s ACCEPTABLE. However you are reminded to be more vigilant as a next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.</p>
							
						<p class="col-md-12"><input type="radio" _optnum="2" name="13b-verdict" value="Does NOT find your reason/s acceptable due to the fact that" {{ ($data->{'13b_verdict'} == "Does NOT find your reason/s acceptable due to the fact that" ? "checked" : "") }}> Does NOT find your reason/s acceptable due to the fact that 
							@if($data->{'13b_stat'} == "pending") 
								<u>
									<b>{!! $data->{'13b_verdictreason'}!="" ? $data->{'13b_verdictreason'} : "<u>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</u>" !!}</b>
								</u>
							@else 
								<input type="text" id="_13b-verdict-reason" style="min-width: 450px;" value="{{ $data->{'13b_verdictreason'} }}">
							@endif
						</p>
						<p class="col-md-12" id="_13b-mitigate" style="display: {{ ($data->{'13b_penalty'} != "" && (($data->{'13b_penalty'} == "suspended for" && $data->{'13b_suspendday'}<$_13a->{'13a_suspendday'}) || $data->{'13b_penalty'}!=$_13a->{'13a_penalty'}) ? ";" : "none;") }}">
							&emsp;Your sanction has however been mitigated from suspension but you are reminded to be more cautious and vigilant as the next violation whether similar or not may no longer be acceptable and a higher disciplinary step shall be undertaken.
						</p>
						<p class="col-md-12">
							@if($data->{'13b_stat'} == "pending")
								&emsp;{!! ($data->{'13b_penalty'}!="Issued a written Reprimand or warning" ? "Effective <u><b>".(!($data->{'13b_verdicteffectdt'} == "" || $data->{'13b_verdicteffectdt'} == "0000-00-00") ? date("F d, Y", strtotime($data->{'13b_verdicteffectdt'})) : "<u>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</u>" )."</b></u>." : "") !!} You are hereby <u><b>{!! ($data->{'13b_penalty'} == "suspended for" ? $data->{'13b_penalty'}." ".$data->{'13b_suspendday'}." day/s" : ($data->{'13b_penalty'}!="" ? $data->{'13b_penalty'} : "<u>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</u>" )) !!}</b></u>
							@else
								You are hereby
								<select id="_13b-penalty">
									<option value="">-select</option>
									<option value="Issued a written Reprimand or warning" {{ ($data->{'13b_penalty'} == "Issued a written Reprimand or warning" ? "selected" : "") }}>Issued a written Reprimand or warning</option>
									<option value="suspended for" {{ ($data->{'13b_penalty'} == "suspended for" ? "selected" : "") }} {{-- {{ ($_13a->{'13a_penalty'}!="terminated with cause" ? ($_13a->{'13a_penalty'}!="suspended for" ? "disabled" : "") : "") }} --}}>suspended for</option>
									<option value="terminated with cause" {{ ($data->{'13b_penalty'} == "terminated with cause" ? "selected" : "") }} {{-- {{ ($_13a->{'13a_penalty'}!="terminated with cause" ? "disabled" : "") }} --}}>terminated with cause</option>
								</select>
								<span id="div-suspendday" style="display: none;">
									<input type="number" id="_13b-suspendday" value="{{ $data->{'13b_suspendday'} }}" min="1" max="{{-- {{ $_13a->{'13a_suspendday'} }} --}}" style="width: 80px;">
									&nbsp;day/s
								</span>
								.
								<span id="div-effectivedt"> Effective <input type="date" id="_13b-effectivedt" value="{{ !($data->{'13b_verdicteffectdt'} == "" || $data->{'13b_verdicteffectdt'} == "0000-00-00") ? date("Y-m-d",strtotime($data->{'13b_verdicteffectdt'})) : "" }}"></span>
							@endif
						</p>

						<p class="col-md-12"><input type="radio" _optnum="3" name="13b-verdict" value="Finds that this needs further investigation thus, you will be notified not later than"  {{ ($data->{'13b_verdict'} == "Finds that this needs further investigation thus, you will be notified not later than" ? "checked" : "") }}> Finds that this needs further investigation thus, you will be notified not later than <input type="date" id="_13b-notification" value="{{ !($data->{'13b_notification'} == "" || $data->{'13b_notification'} == "0000-00-00") ? date("Y-m-d",strtotime($data->{'13b_notification'})) : "" }}"></p>
					</div>
				</fieldset>
				<div class="row mb-3">
					<label class="col-md-3">Issued by:</label>
					<div class="col-md-7">
						@if($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "")
							<select class="form-control selectpicker" id="_13b-issuedby" title="Issued by Dept Head" data-live-search="true" require {{ ($data->{'13b_id'}!="" ? "disabled" : "") }}>
								@foreach ($employees as $k => $v)
									@if($v['ji_remarks'] == 'Active' || $data->{'13b_issuedby'} == $v['pers_empno'])
										<option job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}" {{ ($data->{'13b_issuedby'} == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
									@endif
								@endforeach
							</select>
						@else
							<table>
								<tr>
									<td align="center">
										<div id="div-signature-issued" class="div-signature" align="center">
											{!! !empty($signatures['issued']) && $signatures['issued']->first() ? $signatures['issued']->first()->gs_sign : '' !!}
										</div>
									</td>
									<td style="vertical-align: bottom;">
										@if ($signed_issued == "" && $data->{'13b_stat'} == "pending" && $data->{'13b_issuedby'} == $user_empno)
											<button type="button" class="btn btn-outline-secondary btn-click-to-sign" onclick="sign_13b('issued')" id="btn-click-to-sign-issued">Sign</button>
										@endif
									</td>
								</tr>
								<tr>
									<td style="width: 250px; text-align: center;">{{ isset($employees[$data->{'13b_issuedby'}]) ? trim(ucwords($employees[$data->{'13b_issuedby'}]['pers_firstname']." ".getNameInitials($employees[$data->{'13b_issuedby'}]['pers_midname']))." ".$employees[$data->{'13b_issuedby'}]['pers_lastname']) : "" }}</td>
								</tr>
								<tr style="border-top: solid black 1px;">
									<td style="text-align: center;">{{ isset($positionList[$data->{'13b_issuedbypos'}]) ? $positionList[$data->{'13b_issuedbypos'}]->jd_title : "" }}</td>
								</tr>
							</table>
						@endif
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-md-3">Noted by:</label>
					<div class="col-md-7">
						@if($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "")
							<select class="form-control selectpicker" id="_13b-notedby" title="Select Employee" data-live-search="true" multiple data-actions-box="true" required {{ ($data->{'13b_id'}!="" ? "disabled" : "") }}>
								@foreach ($employees as $k => $v)
									@if($v['ji_remarks'] == 'Active' || strpos($data->{'13b_notedby'}, $v['pers_empno']) !== false || $hr_dir == $v['pers_empno'])
										<option job="{{ $v['jrec_position'] }}" {{ strpos($data->{'13b_notedby'}, $v['pers_empno']) !== false || ($hr_dir == $v['pers_empno'] && $data->{'13b_notedby'} == "") ? "selected" : "" }} value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
									@endif
								@endforeach
							</select>
						@else
							@foreach (explode(',', $data->{'13b_notedby'}) as $k => $v)
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
										<td style='text-align: center;'>{{ isset($data->{'13b_notedbypos'}[$k]) && isset($positionList[$data->{'13b_notedbypos'}[$k]]) ? $positionList[$data->{'13b_notedbypos'}[$k]]->jd_title : "" }}</td>
									</tr>
								</table>
								<br>
							@endforeach
						@endif
					</div>
				</div>
				@if($data->{'13b_stat'} == "refused")
					<div class="row mb-3">
						<label class="col-md-12">REFUSED TO ACKNOWLEDGE RECEIPT</label>
						<label class="col-md-12">Witnesses:</label>
						<div class="col-md-12">
							@if ($data->{'13b_witness'} != "")
								@foreach (explode(',', $data->{'13b_witness'}) as $k => $v)
									<table style="display: inline-grid;">
										<tr>
											<td align="center">
												<div id="div-signature-witness-{{ $v }}" class="div-signature" align="center">
													{!! !empty($signatures['witness']) && $signatures['witness']->where('gs_empno', $v)->first() ? $signatures['witness']->where('gs_empno', $v)->first()->gs_sign : '' !!}
												</div>
											</td>
											<td style="vertical-align: bottom;">
												@if (($signed_witness == 0 && $user_empno == $v) || ($user_empno == $data->{'13b_issuedby'} && !(!empty($signatures['witness']) && $signatures['witness']->contains('gs_empno', $v))))
													<button type="button" class="btn btn-primary btn-click-to-sign" onclick="sign_13b('witness')" id="btn-click-to-sign-witness-{{ $v }}">Sign</button>
												@endif
											</td>
										</tr>
										<tr>
											<td style='width:250px; text-align: center;'>{{ isset($employees[$v]) ? trim(ucwords($employees[$v]['pers_firstname']." ".getNameInitials($employees[$v]['pers_midname']))." ".$employees[$v]['pers_lastname']) : "" }}</td>
										</tr>
										<tr style='border-top: solid black 1px;'>
											<td style='text-align: center;'>{{ isset($data->{'13b_witnesspos'}[$k]) && isset($positionList[$data->{'13b_witnesspos'}[$k]]) ? $positionList[$data->{'13b_witnesspos'}[$k]]->jd_title : "" }}</td>
										</tr>
									</table>
								@endforeach
							@endif
							@if (Auth::user()->userAccess('grievance','review') && $data->{'13b_stat'} == "refused")
								<button type="button" class="btn btn-outline-secondary" onclick="edit_witness('{{ $data->{'13b_witness'} }}')">{{ ($data->{'13b_witness'} != "" ? "Edit" : "Add") }}</button>
							@endif
						</div>
					</div>
				@endif
				<button type="submit" style="display: none;"></button>
			</form>
		@endif
		<div align="center">
			@if($data->{'13b_stat'} == "draft" || $data->{'13b_stat'} == "")
				<button id="btn-save-13b" class="btn btn-primary" style="{{ ($data->{'13b_id'}!="" ? "display: none;" : "") }}">Save</button>

				<button id="btn-edit-13b" class="btn btn-success" style="{{ ($data->{'13b_id'} == "" ? "display: none;" : "") }}">Edit</button>
				&emsp;|&emsp;
				<button class="btn btn-primary" id="btn-post-13b">post</button>
			@endif
		</div>
		<div class="float-start">
			<button class="btn btn-info" onclick="view13A('{{ $_13a->{'13a_id'} }}')">View 13A</button>
		</div>
		<div class="float-end">
			<br>
			@if(($data->{'13b_issuedby'} == $user_empno || Auth::user()->userAccess('grievance','review')) && in_array($data->{'13b_stat'}, ['received', 'pending', 'reviewed', 'issued']))
				<button class="btn btn-sm btn-danger" onclick="$('#cancelModal').modal('show')">Cancel</button>
			@endif
			@if($signed_issued!="" && $data->{'13b_stat'} == "pending" && $signed_noted == 0 && strpos($data->{'13b_notedby'}, $user_empno) !== false)
				<button type="button" class="btn btn-primary" onclick="sign_13b('reviewed')" id="btn-click-to-sign">Reviewed</button>
			@elseif($data->{'13b_stat'} == "reviewed" && $data->{'13b_issuedby'} == $user_empno)
				<button type="button" class="btn btn-primary" onclick="issue_13b()">Issue</button>
			@endif
			@if($data->{'13b_stat'} == "issued" && ($user_empno == $data->{'13b_to'} || $data->{'13b_issuedby'} == $user_empno))
				<!-- <button class="btn btn-primary" onclick="_13b_receive()">Receive</button> -->
				<button class="btn btn-primary" onclick="sign_13b('received')" id="btn-click-to-sign">Receive</button>
				<button class="btn btn-danger" onclick="_13b_refuse()">Refuse</button>
			@endif
			@if($data->{'13b_id'}!="")
				<button type="button" class="btn btn-outline-secondary" onclick="print_13b()"><i class="fa fa-print"></i></button>
			@endif
		</div>
	</div>
</div>

<div id="signature-pad-wrapper" class="signature-pad-wrapper d-flex flex-column">
	<input type="hidden" id="sign-type" value="">
	<input type="hidden" id="sign-empno" value="{{ $user_empno }}">
	<div id="signature-pad" class="signature-pad flex-grow-1">
  		<canvas id="signature-pad-canvas" class="signature-pad-canvas h-100 w-100"></canvas>
	</div>
  	<div class="d-grid d-block">
  	  	<div id="btn-for-sign" class="btn-group">
			<button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" onclick="cancel_13b_sign()">Cancel</button>
			<button type="button" class="btn btn-outline-secondary btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
			<button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" onclick="save_13b_sign()">Save</button>
		</div>
	</div>
</div>

<div class="modal fade" id="witnessModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<form class="form-horizontal" id="form-witness">
         		<div class="modal-header">
            		<h4 class="modal-title" id="modalTitle"><center>Witnessess</center></h4>
            		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         		</div>
         		<div class="modal-body">
         			<select class="form-control selectpicker" id="_13b-witness" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
						@foreach ($employees as $k => $v)
							@if($v['ji_remarks'] == 'Active' || strpos($data->{'13b_witness'}, $v['pers_empno']) !== false)
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

<div class="modal fade" data-bs-backdrop="static" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelmodalTitle">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<form class="form-horizontal" id="form-cancel">
         		<div class="modal-header">
            		<h4 class="modal-title" id="cancelmodalTitle"><center>Cancel</center></h4>
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

<iframe src="" id="print_13b" style="display: none;"></iframe>

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

	$(document).ready(function(){
		canvas = document.getElementById("signature-pad").querySelector("canvas");
		if(canvas){
			signaturePad = new SignaturePad(canvas, {
				backgroundColor: 'rgb(255, 255, 255)'
			});

			signaturePad.minWidth = 3;
			signaturePad.maxWidth = 3;
		}

		window.onresize = resizeCanvas;

		$('.selectpicker').selectpicker('refresh');

		$('#btn-for-sign button[data-action="clear"]').click(function(){
			signaturePad.clear();
		});

		$("#_13b-from").change(function(){
			$("#_13b-posfrom").val( $("#_13b-from option:selected").attr("_job") ).selectpicker("refresh");
		});

		$("#btn-save-13b").click(function(){
			$("#_13b-stat").val("draft");
			$("#form-13b").find("[type='submit']").click();
		});

		$("#btn-edit-13b").click(function(){
			$("#form-13b fieldset").attr("disabled",false);
			$("#btn-save-13b").show();
			$("#_13b-issuedby").attr("disabled",false).selectpicker("refresh");
			$("#_13b-notedby").attr("disabled",false).selectpicker("refresh");
			$(this).hide();
		});

		if($("#_13b-penalty").val() == "suspended for"){
			$("#div-suspendday").show();
			$("#_13b-suspendday").attr("required",true);
		}else{
			$("#div-suspendday").hide();
			$("#_13b-suspendday").attr("required",false);
			$("#_13b-suspendday").val(1);
		}

		if($("#_13b-penalty").val() == "Issued a written Reprimand or warning" || $("#_13b-penalty").val() == ""){
			$("#_13b-effectivedt").val("");
			$("#div-effectivedt").css("display","none");
			$("#_13b-effectivedt").attr("required",false);
		}else{
			$("#_13b-effectivedt").val("");
			$("#div-effectivedt").css("display","");
			$("#_13b-effectivedt").attr("required",true);
		}

		if($("#_13b-stat").val()!="pending"){
			if($("#_13b-penalty").val()!="" && (($("#_13b-penalty").val() == "suspended for" && $("#_13b-suspendday").val() < $("#_13a-suspendday").val()) || $("#_13b-penalty").val() != $("#_13a-penalty").val())){
				$("#_13b-mitigate").css("display","");
			}else{
				$("#_13b-mitigate").css("display","none");
			}
		}

		if($("#_13b-penalty").val() == "suspended for"){
			$("#div-suspendday").show();
			$("#_13b-suspendday").attr("required",true);
		}else{
			$("#div-suspendday").hide();
			$("#_13b-suspendday").attr("required",false);
			$("#_13b-suspendday").val(1);
		}

		$("#_13b-penalty").change(function(){
			if($(this).val() == "Issued a written Reprimand or warning" || $(this).val() == ""){
				$("#_13b-effectivedt").val("");
				$("#div-effectivedt").css("display","none");
				$("#_13b-effectivedt").attr("required",false);
			}else{
				$("#_13b-effectivedt").val("");
				$("#div-effectivedt").css("display","");
				$("#_13b-effectivedt").attr("required",true);
			}
			if($(this).val() == "suspended for"){
				$("#div-suspendday").show();
				$("#_13b-suspendday").attr("required",true);
			}else{
				$("#div-suspendday").hide();
				$("#_13b-suspendday").attr("required",false);
				$("#_13b-suspendday").val(1);
			}

			if($("#_13b-penalty").val()!="" && (($("#_13b-penalty").val() == "suspended for" && $("#_13b-suspendday").val() < $("#_13a-suspendday").val()) || $("#_13b-penalty").val()!=$("#_13a-penalty").val())){
				$("#_13b-mitigate").css("display","");
			}else{
				$("#_13b-mitigate").css("display","none");
			}

		});

		$("#_13b-suspendday").change(function(){
			if($("#_13b-penalty").val()!="" && (($("#_13b-penalty").val() == "suspended for" && $("#_13b-suspendday").val() < $("#_13a-suspendday").val()) || $("#_13b-penalty").val()!=$("#_13a-penalty").val())){
				$("#_13b-mitigate").css("display","");
			}else{
				$("#_13b-mitigate").css("display","none");
			}
		});

		$("#btn-post-13b").click(function(){
			$("#_13b-stat").val("pending");
			$("#form-13b").find("[type='submit']").click();
		});

		$("[name='13b-verdict']").click(function(){
			$("#_13b-verdict-reason").attr("required",false);
			$("#_13b-effectivedt").attr("required",false);
			$("#_13b-penalty").attr("required",false);
			$("#_13b-notification").attr("required",false);
			if($(this).is(":checked")){
				if($(this).attr("_optnum") == "2"){
					$("#_13b-verdict-reason").attr("required",true);
					$("#_13b-effectivedt").attr("required",true);
					$("#_13b-penalty").attr("required",true);
				}else if($(this).attr("_optnum") == "3"){
					$("#_13b-notification").attr("required",true);
				}
			}
		});

		$("#form-13b").submit(async function(e){
			e.preventDefault();
			if($("[name='13b-verdict']:checked").attr("_optnum")!="2"){
				$("#_13b-verdict-reason").val("");
				$("#_13b-effectivedt").val("");
				$("#_13b-penalty").val("");
			}
			if($("[name='13b-verdict']:checked").attr("_optnum")!="3"){
				$("#_13b-notification").val("");
			}

			$('#err-msg').html("");
			let formData = new FormData();
			// formData.append("cc", $("#_13a-cc option:selected").map((_, el) => el.value).get().join(","));
			formData.append("id", $("#_13b-id").val());
			formData.append("to", $("#_13b-to").val());
			formData.append("cc", $("#_13b-cc").val().join(","));
			formData.append("from", $("#_13b-from").val());
			formData.append("frompos", $("#_13b-posfrom").val());
			formData.append("verdict", $("[name='13b-verdict']:checked").val());
			formData.append("reason", $("#_13b-verdict-reason").val());
			formData.append("effectdt", $("#_13b-effectivedt").val());
			formData.append("penalty", $("#_13b-penalty").val());
			formData.append("notification", $("#_13b-notification").val());
			formData.append("issuedby", $("#_13b-issuedby").val());
			formData.append("issuedbypos", $("#_13b-issuedby option:selected").attr("job") || '');
			formData.append("notedby", $("#_13b-notedby").val().join(","));
			formData.append("notedbypos", $("#_13b-notedby option:selected").map((_, el) => $(el).attr("job") || '').get().join(","));
			formData.append("stat", $("#_13b-stat").val());
			formData.append("suspend", $("#_13b-suspendday").val());
			formData.append("_13a", $("#_13a-id").val());

			let response = await fetch('/grievance/13b/save', {
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
				if($("#_13b-stat").val() != "draft"){
					alert("13B posted");
					if($('#_13bTab button.active').length){
						close13B();
						$('#_13bTab button.active').click();
					}else{
						window.location = '/grievance/13b';
					}
				}else if($("#_13b-id").val()){
					alert("13B saved");
					$('.modal').modal('hide');
					view13B($("#_13b-id").val());
				}else{
					alert("13B saved");
					window.location = '/grievance/13b';
				}
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${errmsg}</p>`);
			}
		});

		$("#form-witness").submit(async function(e){
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13b-id").val());
			formData.append("witness", $("#_13b-witness").val().join(","));
			formData.append("witnesspos", $("#_13b-witness option:selected").map((_, el) => $(el).attr("job") || '').get().join(","));

			let response = await fetch('/grievance/13b/set/witness', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Saved');
				$('.modal').modal('hide');
				view13B($("#_13b-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-cancel").submit(async function(e){
			e.preventDefault();
			$('#err-msg').html("");

			if (confirm("Proceed?")) {
				let formData = new FormData();
				formData.append("id", $("#_13b-id").val());
				formData.append("remarks", $("#cancel-remarks").val());

				let response = await fetch('/grievance/13b/cancel', {
					method: "POST",
					body: formData,
					headers: {
						"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
					}
				});

				if (response.ok) {
					alert('Cancelled');
					close13B();
					$('#_13bTab button.active').click();
				} else {
					let result = await response.json();
					$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
				}
			}
		});
	});

	function edit_witness(_witness1){
		$("#_13b-witness").val(_witness1.split(",")).selectpicker("refresh");
		$("#witnessModal").modal("show");
	}	

	function sign_13b(_type1){
		if ($(window).height() > $(window).width()) {
	        alert("Please rotate phone to landscape");
	    } else {
	    	$("#sign-type").val(_type1);
			// $("#sign-empno").val(empno);

			$("body").addClass('overflow-hidden');
			$("#signature-pad-wrapper").addClass('show');

			$("#div_sign").css({"width": "100%", "height": "100vh"});
	    	$("#signature-pad").css({"width": "100%", "height": "90%"});

			resizeCanvas();
	    }
	}

	function cancel_13b_sign(){
		$("body").removeClass('overflow-hidden');
		$("#signature-pad-wrapper").removeClass('show');
	}

	async function save_13b_sign(_type1){
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13b-id").val());
		formData.append("sign", signaturePad.toSVG());
		formData.append("signtype", $("#sign-type").val());
		formData.append("empno", $("#sign-empno").val());

		let response = await fetch('/grievance/13b/sign', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			$("body").removeClass('overflow-hidden');
			alert('Signed');
			close13B();
			$('#_13bTab button.active').click();
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function issue_13b(){
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13b-id").val());

		let response = await fetch('/grievance/13b/issue', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Issued');
			close13B();
			$('#_13bTab button.active').click();
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function del_13b(){
		if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let response = await fetch('/grievance/13b/delete/'+$("#_13b-id").val(), {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Removed');
				close13B();
				$('#_13bTab button.active').click();
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	async function _13b_receive() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13b-id").val());
		formData.append("emp", "{{ $data->{'13b_to'} }}");

		let response = await fetch('/grievance/13b/receive', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Received');
			close13B();
			$('#_13bTab button.active').click();
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function _13b_refuse() {
		if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#_13b-id").val());

			let response = await fetch('/grievance/13b/refuse', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Refused');
				close13B();
				$('#_13bTab button.active').click();
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	async function print_13b(){
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#_13b-id").val());

		let response = await fetch('/grievance/13b/print', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			alert('Received');
			const html = await response.text();
			$("#print_13b").attr("srcdoc", html);
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}
</script>