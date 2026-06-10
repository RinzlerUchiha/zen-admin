<style>
	.bootstrap-select .bs-actionsbox button{
		white-space: nowrap;
	}

	.bootstrap-select{
		max-width: 100% !important;
	}

	#sign-ir:not(.show) {
		display: none !important;
	}

	@media (min-width: 768px) {
		#sign-ir.show {
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
		#sign-ir.show {
			display: none !important;
		}
	}

    #div-signature {
    	/*min-height: 100px;*/
    	width: 150px;
    	position: relative;
    	height: fit-content;
    }

    #div-signature svg {
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
{{-- <div class="container-fluid">
	<div class="col-md-auto"> --}}
		<div class="card">
			<div class="card-header">
				<span class="float-end">
					{{-- <a href="?page=grievance" class="btn btn-light btn-sm"><i class="fa fa-list"></i></a> --}}
					@if($data->ir_id!="" && (($user_empno == $data->ir_from && $data->ir_stat == "draft") || Auth::user()->userAccess('grievance','review')))
						<button class="btn btn-danger btn-sm" onclick="del_ir()"><i class="fa fa-trash"></i></button>&emsp;|&emsp;
					@endif
					<button class="btn btn-close" onclick="closeIR()"></button>
				</span>
				<label>Incident Report Form</label>
			</div>
			<div class="card-body">
				<div id="err-msg"></div>	
				<br>
				@if( (($data->ir_stat == "draft" || $data->ir_stat == "needs explanation")  && $data->ir_from == $user_empno) || $user_empno == '045-2017-068')
					<form id="form-ir" action="{{ config('app.url') }}/grievance/ir/save">
						<fieldset {{ ($data->ir_id!="" ? "disabled" : "") }}>
							<input type="hidden" id="ir-id" value="{{ $data->ir_id }}">
							<input type="hidden" id="ir-stat" value="draft">
							<input type="hidden" id="ir-from" value="{{ $data->ir_id ? $data->ir_from : $user_empno }}">
							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-2">To:</label>
								<div class="col-md-auto">
									<select class="form-control form-control-sm selectpicker" data-width="auto" id="ir-to" title="Select Employee" data-live-search="true" required>
										@foreach ($employees as $v)
											@if($v['ji_remarks'] == 'Active' || $data->ir_to == $v['pers_empno'])
												<option value="{{ $v['pers_empno'] }}" {{ ($data->ir_to == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
											@endif
					                  	@endforeach
									</select>
								</div>
							</div>

							<div class="row row-cols-md-2 mb-3">
								<label class="col-form-label col-form-label-sm col-md-2">CC:</label>
								<div class="col-md">
									<select class="form-control form-control-sm selectpicker" data-width="fit" id="ir-cc" title="Select Employee" data-live-search="true" multiple data-actions-box="true" data-multiple-separator=" | ">
										@foreach ($employees as $v)
											@if($v['ji_remarks'] == 'Active' || strpos($data->ir_cc, $v['pers_empno']) !== false)
												<option value="{{ $v['pers_empno'] }}" {{ (strpos($data->ir_cc, $v['pers_empno']) !== false ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
											@endif
					                  	@endforeach
									</select>
								</div>
							</div>

							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-2">From:</label>
								<div class="col-md-7">
									<label>{{ $data->from_name }}</label>
								</div>
							</div>

							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-2">Date:</label>
								<div class="col-md-7">
									<label>{{ date("F d, Y",strtotime($data->ir_date)) }}</label>
								</div>
							</div>

							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-2">Subject:</label>
								<div class="col-md-7">
									<input type="text" id="ir-subject" class="form-control form-control-sm" value="{{ $data->ir_subject }}" >
								</div>
							</div>

							<hr>

							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-12">INFORMATION ABOUT THE INCIDENT</label>
							</div>

							<div class="row mb-3">
								<div class="col-md-4">
									<div class="row mb-3">
										<label class="col-form-label col-form-label-sm col-md-12">Date of Incident</label>
										<div class="col-md-12">
											<input type="date" id="ir-incident-date" class="form-control form-control-sm" max="{{ date("Y-m-d") }}" value="{{ !($data->ir_incidentdate == "" || $data->ir_incidentdate == "0000-00-00") ? date("Y-m-d",strtotime($data->ir_incidentdate)) : "" }}" >
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="row mb-3">
										<label class="col-form-label col-form-label-sm col-md-12">Location of Incident</label>
										<div class="col-md-12">
											<input type="text" id="ir-incident-location" class="form-control form-control-sm" value="{{ $data->ir_incidentloc }}" >
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="row mb-3">
										<label class="col-form-label col-form-label-sm col-md-12">Audit Finding/s</label>
										<div class="col-md-12">
											<label><input type="radio" name="ir-audit-findings" id="ir-audit-findings-yes" value="yes" {{ ($data->ir_auditfindings == "yes" ? "checked" : "") }}> Yes</label>
											<label><input type="radio" name="ir-audit-findings" id="ir-audit-findings-no" value="no" {{ ($data->ir_auditfindings == "no" ? "checked" : "") }}> No</label>
										</div>
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-md-4">
									<label class="col-form-label col-form-label-sm col-md-12"> Person Involved</label>
									<div class="col-md-12">
										<select class="form-control form-control-sm selectpicker" id="ir-person-involved" title="Select Employee" data-live-search="true" required>
											@foreach ($employees as $v)
												@if($v['ji_remarks'] == 'Active' || strpos($data->ir_involved, $v['pers_empno']) !== false)
							                        <option value="{{ $v['pers_empno'] }}" {{ (strpos($data->ir_involved, $v['pers_empno']) !== false ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
												@endif
						                  	@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<label class="col-form-label col-form-label-sm col-md-12">Expected Performance/Standard violated</label>
									<div class="col-md-12">
										<input type="text" id="ir-expected-violation" class="form-control form-control-sm" value="{{ $data->ir_violation }}" >
									</div>
								</div>
								<div class="col-md-4">
									<label class="col-form-label col-form-label-sm col-md-12">Amount Involved, if any.</label>
									<div class="col-md-12">
										<input type="number" id="ir-amount" class="form-control form-control-sm" value="{{ $data->ir_amount }}" >
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-12">Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.</label>
								<div class="col-md-12">
									<textarea class="form-control form-control-sm" id="ir-desc" >{{ $data->ir_desc }}</textarea>
								</div>
							</div>

							<hr>

							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-md-12">As part of his/her responsibilities (Responsibilidad niya ang), is expected to:</label>
								<div class="col-md-12">
									<div class="row mb-3">
										<label class="col-form-label col-form-label-sm col-md-12"> Follow the SOP of (sumunod sa SOP na)</label>
										<div class="col-md-12">
											<input type="text" id="ir-responsibility-1" class="form-control form-control-sm" value="{{ $data->ir_reponsibility_1 }}" >
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-form-label col-form-label-sm col-md-12"> Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)</label>
										<div class="col-md-12">
											<input type="text" id="ir-responsibility-2" class="form-control form-control-sm" value="{{ $data->ir_reponsibility_2 }}" >
										</div>
									</div>
								</div>
							</div>
						</fieldset>
						<div class="row mb-3">
							<div class="col-md-12" align="right">
								<button type="submit" style="display: none;"></button>
								<button id="btn-save-ir" type="button" class="btn btn-light border" style="{{ ($data->ir_id!="" ? "display: none;" : "") }}">Save</button>
								<button id="btn-edit-ir" type="button" class="btn btn-success" style="{{ ($data->ir_id == "" ? "display: none;" : "") }}">Edit</button>
							</div>
						</div>
					</form>
				@else
					<div class="form-horizontal">
						<input type="hidden" id="ir-id" value="{{ $data->ir_id }}">
						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-xs-2">To:</label>
							<div class="col-xs-7">
									<p>{{ $data->to_name }}</p>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-xs-2">CC:</label>
							<div class="col-xs-7">
								@foreach (explode(',', $data->ir_cc) as $cc_k)
									<p>{{ isset($employees[$cc_k]) ? trim(ucwords($employees[$cc_k]['pers_lastname'].", ".$employees[$cc_k]['pers_firstname'])) : "" }}</p>
								@endforeach
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-xs-2">From:</label>
							<div class="col-xs-7">
								<p>{{ $data->from_name }}</p>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-xs-2">Date:</label>
							<div class="col-xs-7">
								<p>{{ date("F d, Y",strtotime($data->ir_date)) }}</p>
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-md-2">Subject:</label>
							<div class="col-md-7">
								<p>{{ $data->ir_subject }}</p>
							</div>
						</div>

						@if($forwardList->count() > 0)
							<div class="row mb-3">
								<label class="col-form-label col-form-label-sm col-xs-2">Forwarded To:</label>
								<div class="col-xs-7">
									@foreach ($forwardList as $irfv)
										<p>{{ $irfv->forwardedTo }}</p>
									@endforeach
								</div>
							</div>
						@endif

						<hr>

						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-md-12">INFORMATION ABOUT THE INCIDENT</label>
						</div>

						<div class="row mb-3">
							<div class="col-md-4">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Date of Incident</label>
									<div class="col-md-12">
										<p>{{ date("F d, Y",strtotime($data->ir_incidentdate)) }}</p>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Location of Incident</label>
									<div class="col-md-12">
										<p>{{ $data->ir_incidentloc }}</p>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Audit Finding/s</label>
									<div class="col-md-12">
										<span><i class="{{ ($data->ir_auditfindings == "yes" ? "fa-regular fa-square-check" : "fa-regular fa-square") }}"></i> Yes</span>
										&emsp;
										<span><i class="fa {{ ($data->ir_auditfindings == "no" ? "fa-regular fa-square-check" : "fa-regular fa-square") }}"></i> No</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-4">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Person Involved</label>
									<div class="col-md-12">
										@foreach (explode(',', $data->ir_involved) as $v)
											<p>{{ isset($employees[$v]) ? trim(ucwords($employees[$v]['pers_firstname']." ".getNameInitials($employees[$v]['pers_midname']))." ".$employees[$v]['pers_lastname']) : "" }}</p>
										@endforeach
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Expected Performance/Standard violated</label>
									<div class="col-md-12">
										<p>{{ $data->ir_violation }}</p>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Amount Involved, if any.</label>
									<div class="col-md-12">
										<p>{{ $data->ir_amount }}</p>
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-md-12">Description of Incident (what happened, how it happened, person/s involved) Be as specific as possible.</label>
							<div class="col-md-12">
								<p>{!! nl2br($data->ir_desc) !!}</p>
							</div>
						</div>

						<hr>

						<div class="row mb-3">
							<label class="col-form-label col-form-label-sm col-md-12">As part of his/her responsibilities (Responsibilidad niya ang), is expected to:</label>
							<div class="col-md-12">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Follow the SOP of (sumunod sa SOP na)</label>
									<div class="col-md-12">
										<p>{{ $data->ir_reponsibility_1 }}</p>
									</div>
								</div>
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-12">Protect the Interests of the Company by (protektahan ang kompanya sa pamamagitan ng)</label>
									<div class="col-md-12">
										<p>{{ $data->ir_reponsibility_2 }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				@endif

				<div id="div-attachment-disp" style="{{ $data->ir_id == "" ? "display: none;" : "" }}">
					<p>In support of this, I have attached the following documents (Inilagay rin ang sumusunod na papeles para magpatibay sa report na ito):</p>
					<div class="card">
						<div class="card-header">
							<label>Files</label>
						</div>
						<div class="card-body">
							@if($data->ir_stat == "draft" || $data->ir_stat == "needs explanation")
								<button class="btn btn-light" onclick="_ir_attachment()">Add <i class="fa fa-plus"></i></button>
							@endif
							<table class="table" width="100%" id="tbl-ir-receipts">
								@foreach ($attachments as $ira)
									<tr>
										<td>
											<label style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#attach_{{ $ira->ira_id }}">{{ $ira->ira_content }}</label>
											<div id="attach_{{ $ira->ira_id }}" class="collapse">
												{{ $ira->ira_type == "audit" ? "<label>Date: ".( !($ira->ira_auditdate == "0000-00-00" || $ira->ira_auditdate == "") ? date("F d, Y",strtotime($ira->ira_auditdate)) : "" )."</label><br>" : "" }}
												@if(in_array($ira->fileType, ["pdf","PDF","png","PNG","jpeg","JPEG", "jpg", "JPG"]))
													{{-- <embed src="../ir/{{ $data->ir_id."/".$ira->ira_content }}" width="100%" height="100%"></embed> --}}
													<embed src="{{ "/grievance/ir/file/".$ira->ira_content }}" style="max-width: 100%;" height="100%"></embed>
												@else
													{{-- <a href="../ir/{{ $data->ir_id."/".$ira->ira_content }}">Download</a> --}}
													<a href="{{ "/grievance/ir/file/".$ira->ira_content }}">Download</a>
												@endif
											</div>
										</td>
										@if($data->ir_stat == "draft" || $data->ir_stat == "needs explanation")
											<td>
												<button class="btn btn-danger" onclick="del_attachment('{{ $ira->ira_irid }}', '{{ $ira->ira_id }}')"><i class="fa fa-times"></i></button>
											</td>
										@endif
									</tr>
								@endforeach
							</table>
						</div>
					</div>
					<p>&nbsp;</p>
					<div class="card">
						<div class="card-header">
							<label>Statements of witnesses namely</label>
						</div>
						<div class="card-body">
							<form id="frm-ir-witnesses" action="{{ config('app.url') }}/grievance/ir/witness/save">
								@csrf
								@if($data->ir_stat == "draft" || $data->ir_stat == "needs explanation")
									<div class="row mb-3">
										<div class="col-md-12" align="right">
											<button type="button" class="btn btn-light" id="btn-edit-witness"><i class="fa fa-edit"></i></button>
											<button type="submit" class="btn btn-primary" id="btn-save-witness" style="display: none;">Save</button>
											<button type="reset" class="btn btn-danger" id="btn-cancel-witness" style="display: none;">Cancel</button>
										</div>
									</div>
								@endif
								<fieldset disabled>
									<div class="row mb-3">
										<div class="col-md-12">
											<textarea class="form-control form-control-sm" id="ir-witnesses">{{ $data->ir_witness }}</textarea>
										</div>
									</div>
								</fieldset>
							</form>
						</div>
					</div>

					<hr>
					<label>I am reporting this matter to you so that the proper proceedings according to company policy may be begun (Pinapaalam ko ito sa inyo para magawa ang nakalagay sa company policy tungkol dito).</label>
					<br>
					<label>I hereby certify that the above information is true and correct (Ang nakasulat sa itaas ay tama at pawang katotohanan lamang).</label>
					<br>
					<div style="width: 100%;" align="center">
						<table>
							<tr>
								<td style="">
									<div id="div-signature" align="center">
										{!! $data->ir_signature !!}
									</div>
									@if($user_empno == $data->ir_from)
									<div id="sign-ir" class="d-flex flex-column">
								    	<div id="signature-pad" class="flex-grow-1">
								      		<canvas id="signature-pad-canvas" class="h-100 w-100"></canvas>
								    	</div>
								  	  	<div class="d-grid d-block">
									  	  	<div id="btn-for-sign" class="btn-group">
												<button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" onclick="cancel_ir_sign()">Cancel</button>
												<button type="button" class="btn btn-light btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
												<button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" onclick="save_ir_sign()">Save</button>
											</div>
										</div>
									</div>
									@endif
								</td>
								<td style="vertical-align: bottom;">
									@if($user_empno == $data->ir_from && $data->ir_stat == "draft")
									<div id="btn-for-sign" style="display: none;">
										<button type="button" class="btn btn-light" data-action="clear">Clear</button>
										&nbsp;|&nbsp;
										<button type="button" class="btn btn-primary" onclick="save_ir_sign()">Save</button>
										&nbsp;|&nbsp;
										<button type="button" class="btn btn-danger" onclick="cancel_ir_sign()">Cancel</button>
									</div>
									<button type="button" class="btn btn-light" onclick="sign_ir()" id="btn-click-to-sign">Click to sign</button>
									@endif
								</td>
							</tr>
							<tr>
								<td align="center">{{ mb_strtoupper(isset($employees[$data->ir_from]) ? trim(ucwords($employees[$data->ir_from]['pers_firstname']." ".getNameInitials($employees[$data->ir_from]['pers_midname']))." ".$employees[$data->ir_from]['pers_lastname']) : "") }}</td>
							</tr>
							<tr style="border-top: 1px solid black;">
								<td align="center">Signature over printed name</td>
							</tr>
						</table>
					</div>
				</div>
				@if($data->ir_meetplace!='')
					<hr>
					<div class="card card-info">
						<div class="card-body">
							<h4>- Meeting -
								@if($data->ir_to == $user_empno)
									<span class="">
										<button class="btn btn-light btn-sm" onclick="$('#meetModal').modal('show')"><i class="fa fa-edit"></i></button>
									</span>
								@endif
							</h4>
							<div class="form-horizontal">
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-2">Date and Time:</label>
									<div class="col-md-5">
										{{ !($data->ir_meetdatetime == "" || $data->ir_meetdatetime == "0000-00-00") ? date("F d, Y h:i A",strtotime($data->ir_meetdatetime)) : "" }}
									</div>
								</div>
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-2">Place:</label>
									<div class="col-md-5">
										{{ $data->ir_meetplace }}
									</div>
								</div>
							</div>
						</div>
					</div>
				@endif
				
				@if($remarks->count() > 0)
					<br>
					<hr>
					<div class="card card-danger">
						<div class="card-header">
							<label>Remarks</label>
						</div>
						<div class="card-body">
							<div class="form-horizontal">
							@foreach ($remarks as $grk)
								<div class="row mb-3">
									<label class="col-form-label col-form-label-sm col-md-3">{{ isset($employees[$grk->gr_empno]) ? trim(ucwords($employees[$grk->gr_empno]['pers_lastname'].", ".$employees[$grk->gr_empno]['pers_firstname'])) : "" }} :</label>
									<div class="col-md-7">
										{{ nl2br($grk->gr_remarks) }}
									</div>
								</div>
								<hr>
							@endforeach
							</div>
						</div>
					</div>
					<br>
				@endif
				<br>
				<div class="float-end">

					@if($data->ir_id!="" && ($data->ir_to == $user_empno || $forwarded_to_me == true))
						<button type="button" class="btn btn-sm btn-light" onclick="forward()">Forward <i class="fa fa-arrow-right"></i></button>
					@endif
					@if(($data->ir_stat == "draft" || $data->ir_stat == "needs explanation") && $data->ir_from == $user_empno && $data->ir_id!="" && $data->ir_signature!="")
						<button type="button" class="btn btn-sm btn-primary" id="btn-post-ir">Post</button>
					@elseif($data->ir_id!="" && ($data->ir_to == $user_empno || $forwarded_to_me == true || Auth::user()->userAccess('grievance','review')) && $data->ir_stat!="resolved")
						<!-- <button class="btn btn-sm btn-light" onclick="$('#meetModal').modal('show')">Set Meeting</button> -->
						<button class="btn btn-sm btn-success" onclick="$('#resolveModal').modal('show')">Resolved</button>
						@if($_13a->count() == 0)
							<button onclick="view13A('', '{{ $data->ir_id }}')" class="btn btn-sm btn-primary">Create 13A</button>
						@endif
					@endif
					@if(($data->ir_stat == "posted" || $data->ir_stat == "needs explanation") && ($data->ir_to == $user_empno || $data->ir_from == $user_empno || Auth::user()->userAccess('grievance','review')))
						<button class="btn btn-sm btn-light" onclick="$('#explanationModal').modal('show')">{{ ($data->ir_stat == "needs explanation" ? "Reply" : "Needs Explanation") }}</button>
					@endif
					@if($data->ir_id!="")
						<button type="button" class="btn btn-sm btn-light" onclick="print_ir()"><i class="fa fa-print"></i></button>
					@endif
					<br>
					<br>
					<table class="table">
						<thead>
							<tr>
								<th colspan="2" style="text-align: center;">View 13A</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($_13a as $v)
								<tr>
									<td>{{ $v->{'13a_memo_no'} }}</td>
									<td>
										<button onclick="view13A('{{ $v->{'13a_id'} }}')" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></button>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
{{-- 	</div>
</div> --}}

<div id="iraModal" class="modal fade" data-bs-backdrop="static" role="dialog">
	<div class="modal-dialog">
	
		<!-- Modal content-->
		<div class="modal-content">
		  	<div class="modal-header">
		  	 	<button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
		  	 	<h4 class="modal-title">Attachment</h4>
		  	</div>
		  	<form id="frm-ir-attachments" action="{{ config('app.url') }}/grievance/ir/attachment/save">
		  		@csrf
			  	<div class="modal-body">
			  		<div class="row mb-3">
			  	 		<label class="col-form-label col-form-label-sm col-md-3">Type: </label>
			  	 		<div class="col-md-5">
			  	 			<select class="form-select form-select-sm" id="attach_type" name="attach_type" required>
			  	 				<option value="receipts">Receipts</option>
								<option value="pictures">Pictures</option>
								<option value="items">Item/Items damaged</option>
								<option value="docs">Related documents</option>
								<option value="audit">Audit report</option>
			  	 			</select>
			  	 		</div>
			  	 	</div>
			  		<div class="row mb-3" id="div-aduitdate" style="display: none;">
			  	 		<label class="col-form-label col-form-label-sm col-md-3">Audit Date: </label>
			  	 		<div class="col-md-5">
			  	 			<input type="date" class="form-control form-control-sm" id="audit_date" name="audit_date">
			  	 		</div>
			  	 	</div>
		  			<div class="row mb-3">
			  	 		<label class="col-form-label col-form-label-sm col-md-3">File: </label>
			  	 		<div class="col-md-9">
			  	 			<input type="file" class="form-control form-control-sm" id="irattachments" name="irattachments[]" multiple required>
			  	 			<input type="hidden" name="ir" value="{{ $data->ir_id }}">
			  	 		</div>
			  	 	</div>
			  	</div>
			  	<div class="modal-footer">
			  	 	<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
			  	 	<button type="submit" class="btn btn-primary">Save</button>
			  	</div>
		  	</form>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="explanationModal" tabindex="-1" role="dialog" aria-labelledby="">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<form id="form-explanation" action="{{ config('app.url') }}/grievance/ir/explanation/save">
         		<div class="modal-header">
            		<h4 class="modal-title" id="modalTitle"><center>Remarks</center></h4>
            		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         		</div>
         		<div class="modal-body">
         			<textarea id="ir-remarks" class="form-control form-control-sm" required></textarea>
         		</div>
         		<div class="modal-footer">
           			<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
           			<button type="submit" class="btn btn-primary">Save</button>
         		</div>
      		</form>
    	</div>
  	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="meetModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<form id="form-meeting" action="{{ config('app.url') }}/grievance/ir/meeting/save">
         		<div class="modal-header">
            		<h4 class="modal-title" id="modalTitle"><center>Set Meeting</center></h4>
            		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         		</div>
         		<div class="modal-body">
         			<div class="row mb-3">
         				<label class="col-form-label col-form-label-sm col-md-3">Date and Time:</label>
         				<div class="col-md-7">
         					<input class="form-control form-control-sm" type="datetime-local" id="ir-datetime" value="{{ !($data->ir_meetdatetime=="" || $data->ir_meetdatetime=="0000-00-00") ? date("Y-m-d\TH:i",strtotime($data->ir_meetdatetime)) : "" }}" required>
         				</div>
         			</div>
         			<div class="row mb-3">
         				<label class="col-form-label col-form-label-sm col-md-3">Location:</label>
         				<div class="col-md-7">
         					<input class="form-control form-control-sm" type="text" id="ir-place" value="{{ $data->ir_meetplace }}" required>
         				</div>
         			</div>
         		</div>
         		<div class="modal-footer">
           			<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
           			<button type="submit" class="btn btn-primary">Save</button>
         		</div>
      		</form>
    	</div>
  	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="forwardModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<form id="form-forward" action="{{ config('app.url') }}/grievance/ir/forward/save">
         		<div class="modal-header">
            		<h4 class="modal-title" id="modalTitle"><center>Forward</center></h4>
            		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         		</div>
         		<div class="modal-body">
         			<select class="form-control form-control-sm selectpicker" id="ir-forward-to" title="Select Receipient" data-live-search="true" required>
						@foreach ($employees as $v)
							@if($v['ji_remarks'] == 'Active' || $data->ir_to == $v['pers_empno'] || strpos($data->ir_cc, $v['pers_empno']) !== false || strpos($data->ir_involved, $v['pers_empno']) !== false)
								<option value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
							@endif
	                  	@endforeach
					</select>
         		</div>
         		<div class="modal-footer">
           			<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
           			<button type="submit" class="btn btn-primary">Save</button>
         		</div>
      		</form>
    	</div>
  	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="resolveModal" tabindex="-1" role="dialog" aria-labelledby="resolvemodalTitle">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<form id="form-resolve" action="{{ config('app.url') }}/grievance/ir/resolve/save">
         		<div class="modal-header">
            		<h4 class="modal-title" id="resolvemodalTitle"><center>Resolve</center></h4>
            		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         		</div>
         		<div class="modal-body">
         			<textarea id="resolve-remarks" class="form-control form-control-sm" placeholder="Remarks..." required></textarea>
         		</div>
         		<div class="modal-footer">
           			<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
           			<button type="submit" class="btn btn-primary">Save</button>
         		</div>
      		</form>
    	</div>
  	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" defer></script>

<iframe src="" id="print_ir" style="display: none;"></iframe>

<script type="text/javascript">
	var canvas, signaturePad

	function resizeCanvas() {
		if(canvas){
			const ratio =  Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.fromData(signaturePad.toData());
		}
	}

	$(function(){
		if($("#signature-pad").length > 0){
			canvas = document.getElementById("signature-pad").querySelector("canvas");
			if(canvas){
				signaturePad = new SignaturePad(canvas, {
					backgroundColor: 'rgb(255, 255, 255)'
				});

				signaturePad.minWidth = 3;
				signaturePad.maxWidth = 3;
			}
		}

		$('#sign-ir button[data-action="clear"]').click(function(){
			signaturePad.clear();
		});

		$('.selectpicker').selectpicker();

		$('textarea').autoResize();

		// ir
		$("#btn-edit-ir").click(function(){
			$("#form-ir fieldset").attr("disabled",false);
			$("#btn-save-ir").show();
			$(this).hide();
		});

		$("#btn-save-ir").click(function(){
			$("#ir-stat").val("draft");
			$("#form-ir").find("[type='submit']").click();
		});

		$("#btn-post-ir").click(function(){
			$("#ir-stat").val("posted");
			$("#form-ir").find("[type='submit']").click();
		});

		$("#form-ir").submit(async function(e){
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#ir-id").val());
			formData.append("to", $("#ir-to").val());
			formData.append("cc", $("#ir-cc option:selected").map((_, el) => el.value).get().join(","));
			formData.append("subject", $("#ir-subject").val());
			formData.append("incidentdt", $("#ir-incident-date").val());
			formData.append("incidentloc", $("#ir-incident-location").val());
			formData.append("auditfind", $("[name='ir-audit-findings']:checked").val());
			formData.append("persinvolved", $("#ir-person-involved").val());
			formData.append("violation", $("#ir-expected-violation").val());
			formData.append("amount", $("#ir-amount").val());
			formData.append("desc", $("#ir-desc").val());
			formData.append("resp1", $("#ir-responsibility-1").val());
			formData.append("resp2", $("#ir-responsibility-2").val());
			formData.append("stat", $("#ir-stat").val());

            let response = await fetch(this.action, {
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
                if($("#ir-stat").val() == "posted"){
					alert("IR posted");
					closeIR();
					$('#irTab button.active').click();
				}else if($("#ir-id").val()){
					alert("IR saved");
					viewIR($("#ir-id").val());
				}else{
					alert("IR saved");
					// let result = await response.text();
					$('#irInfo').html(result);
				}
            } else {
				// let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${errmsg}</p>`);
            }
		});

		// witness
		$("#btn-edit-witness").click(function(){
			$(this).hide();
			$("#btn-save-witness").show();
			$("#btn-cancel-witness").show();
			$("#frm-ir-witnesses fieldset").attr("disabled",false);
		});

		$("#btn-cancel-witness").click(function(){
			$(this).hide();
			$("#btn-save-witness").hide();
			$("#btn-edit-witness").show();
			$("#frm-ir-witnesses fieldset").attr("disabled",true);
		});

		$("#frm-ir-witnesses").submit(async function(e){
	        e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("ir", $("#ir-id").val());
			formData.append("witnesses", $("#ir-witnesses").val());

			let response = await fetch(this.action, {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok) {
				viewIR($("#ir-id").val());
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
      	});

		// explanation
		$("#form-explanation").submit(async function(e){
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("ir", $("#ir-id").val());
			formData.append("remarks", $("#ir-remarks").val());

			let response = await fetch(this.action, {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok && result.success) {
				$('.modal').modal('hide');
				closeIR();
				$('#irTab button.active').click();
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		// meeting
		$("#form-meeting").submit(async function(e){
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("ir", $("#ir-id").val());
			formData.append("place", $("#ir-place").val());
			formData.append("datetime", $("#ir-datetime").val());

			let response = await fetch(this.action, {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok && result.success) {
				$('.modal').modal('hide');
				viewIR($("#ir-id").val());
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		// attachment
		$("#attach_type").change(function(){
      		if($(this).val()=="audit"){
      			$("#div-aduitdate").css("display","");
				$("#audit_date").attr("required",true);
      		}else{
      			$("#div-aduitdate").css("display","none");
				$("#audit_date").attr("required",false);
      		}
      	});

      	$("#frm-ir-attachments").submit(async function(e){
	        e.preventDefault();
			$('#err-msg').html("");

			let response = await fetch(this.action, {
				method: "POST",
				body: new FormData(this),
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok && result.success) {
				$('.modal').modal('hide');
				viewIR($("#ir-id").val());
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
      	});

		$("#form-forward").submit(async function(e){
			e.preventDefault();
			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("ir", $("#ir-id").val());
			formData.append("to", $("#ir-forward-to").val());

			let response = await fetch(this.action, {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok && result.success) {
				$('.modal').modal('hide');
				alert("Forwarded to " + $("#ir-forward-to option:selected").text());
				closeIR();
				$('#irTab button.active').click();
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});

		$("#form-resolve").submit(async function(e){
			e.preventDefault();

			if(confirm("Proceed?")){
				$('#err-msg').html("");

				let formData = new FormData();
				formData.append("ir", $("#ir-id").val());
				formData.append("remarks", $("#resolve-remarks").val());

				let response = await fetch(this.action, {
					method: "POST",
					body: formData,
					headers: {
						"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
					}
				});

				let result = await response.json();

				if (response.ok && result.success) {
					$('.modal').modal('hide');
					alert("Resolved");
					closeIR();
					$('#irTab button.active').click();
				} else {
					$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
				}
			}
		});
	});

	function forward() {
		$("#forwardModal").modal("show");
	}

	function _ir_attachment(){
      	$("#iraModal").modal("show");
	}

	async function del_attachment(ir, id) {
		if(confirm("Are you sure?")){
			$('#err-msg').html("");
			
			let response = await fetch('/grievance/ir/attachment/delete/'+ir+'/'+id, {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok) {
				alert("Attachment removed");
				viewIR(ir);
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	
	async function save_ir_sign(){
		$("body").removeClass('overflow-hidden');
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("ir", $("#ir-id").val());
		formData.append("sign", signaturePad.toSVG());

		let response = await fetch('/grievance/ir/sign', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		let result = await response.json();

		if (response.ok) {
			alert("IR signed");
			viewIR($("#ir-id").val());
		} else {
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function del_ir(){
		if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let response = await fetch('/grievance/ir/delete/'+$("#ir-id").val(), {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			let result = await response.json();

			if (response.ok) {
				alert("IR removed");
				closeIR();
				$('#irTab button.active').click();
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
	}

	function sign_ir(){
		if ($(window).height() > $(window).width()) {
	        alert("Please rotate phone to landscape");
	    } else {
			signaturePad.clear();
			$("body").addClass('overflow-hidden');
			$("#sign-ir").addClass('show');
			// $("#div-signature").hide();

			// $("#btn-for-sign").show();
			// $("#btn-click-to-sign").hide();

			$("#div_sign").css({"width": "100%", "height": "100vh"});
	    	$("#signature-pad").css({"width": "100%", "height": "90%"});
			// $('html, body').animate({
	        //     scrollTop: $("#btnclearsign").offset().top + 100
	        // }, 1000);

			setTimeout(function(){
				resizeCanvas();
			}, 1000);

	    }
	}

	function cancel_ir_sign(){
		$("body").removeClass('overflow-hidden');
		$("#sign-ir").removeClass('show');
		// $("#div-signature").show();

		// $("#btn-for-sign").hide();
		// $("#btn-click-to-sign").show();
	}

	async function print_ir(){
		$('#err-msg').html("");
		
		let formData = new FormData();
		formData.append("id", $("#ir-id").val());

		let response = await fetch('/grievance/ir/print', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});


		if (response.ok) {
			const html = await response.text();
			$("#print_ir").attr("srcdoc",html);
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}
</script>