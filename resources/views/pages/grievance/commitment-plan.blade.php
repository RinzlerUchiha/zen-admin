<style>
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
				<button onclick="view13A('{{ $_13a->{'13a_id'} }}')" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i></button>
			</span>
			<label>Commitment Plan</label>
		</div>

		<div class="card-body">
			<form class="form-horizontal" id="form-commitment">
				<input type="hidden" id="commit-id" value="{{ $commitment->commit_id }}">
				<input type="hidden" id="commit-13a" value="{{ $_13a->{'13a_id'} }}">
				<input type="hidden" id="commit-preparedby" value="{{ $commitment->commit_preparedby }}">
				<input type="hidden" id="commit-agreedby" value="{{ $commitment->commit_agreedby }}">
				<fieldset {{ ($commitment->commit_id != "" ? "disabled" : "") }}>
					<div class="row">
						<div class="col-md-5">
							<div class="row mb-3">
								<label class="col-md-2 col-form-label">Name:</label>
								<div class="col-md-8">
									<label class="col-form-label">{{ $_13a->to_name_init }}</label>
								</div>
							</div>
						</div>
						<div class="col-md-7">
							<div class="row mb-3">
								<label class="col-md-3 col-form-label">Position / Department:</label>
								<div class="col-md-9">
									<label class="col-form-label">{{ $_13a->pos_name.' / '.$_13a->dept_name }}</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-5">
							<div class="row mb-3">
								<label class="col-md-2 col-form-label">Date:</label>
								<div class="col-md-9">
									<label class="col-form-label">{{ $commitment->commit_date }}</label>
								</div>
							</div>
						</div>
						<div class="col-md-7">
							<div class="row mb-3">
								<label class="col-md-3 col-form-label">Memo No:</label>
								<div class="col-md-9">
									<label class="col-form-label">{{ $_13a->{'13a_memo_no'} }}</label>
								</div>
							</div>
						</div>
					</div>
					<table class="table table-bordered" width="100%" id="tbl-commitment">
						<thead>
							<tr>
								<th width="30%" style="text-align: center;">What did you learn from this experience?</th>
								<th width="30%" style="text-align: center;">What do you commit to do differently after this is resolved?</th>
								<th style="text-align: center;" {{ ($commitment->commit_preparedby_sign=="" ? "width='40%' colspan='2'" : "width='30%'") }}>When will you start?</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($commitment->plan_info as $cpi_k)
								@if($commitment->commit_preparedby_sign=="")
									<tr>
										<td>
											<textarea style="width: 100%;" name="cp_row_learn">{{ $cpi_k->cpinfo_learn }}</textarea>
										</td>
										<td>
											<textarea style="width: 100%;" name="cp_row_commit">{{ $cpi_k->cpinfo_commit }}</textarea>
										</td>
										<td>
											<textarea style="width: 100%;" name="cp_row_start">{{ $cpi_k->cpinfo_start }}</textarea>
										</td>
										<td>
											<input type="hidden" name="cp_row_id" value="{{ $cpi_k->cpinfo_id }}">
											<button class="btn btn-danger" onclick="del_commitment(this)"><i class="fa fa-times"></i></button>
										</td>
									</tr>
								@else
									<tr>
										<td>
											{!! nl2br($cpi_k->cpinfo_learn) !!}
										</td>
										<td>
											{!! nl2br($cpi_k->cpinfo_commit) !!}
										</td>
										<td>
											{!! nl2br($cpi_k->cpinfo_start) !!}
										</td>
									</tr>
								@endif
							@endforeach
						</tbody>
					</table>
					@if($commitment->commit_preparedby_sign=="")
						<button type="button" class="btn btn-outline-secondary btn-sm" onclick="add_commitment()"><i class="fa fa-plus"></i> Add Row</button>
					@endif
				</fieldset>
				<br><br>
				<div class="row mb-3">
					<div class="col-md-12">
						<label class="col-md-12">Prepared by: </label>
						<div class="col-md-12">
							<table>
								<tbody>
									<tr>
										<td align="center">
											@if ($user_empno == $commitment->commit_preparedby && $commitment->commit_id)
												<div class="float-end">
													<button type="button" class="btn btn-outline-secondary" onclick="sign_commitment('{{ $commitment->commit_preparedby }}', 'preparedby')">Sign</button>
												</div>
											@endif
											<div class="div-signature" align="center">
												{!! $commitment->commit_preparedby_sign !!}
											</div>
										</td>
									</tr>
									<tr>
										<td style='width:250px; text-align: center;'>{{ $commitment->commit_preparedby_name_init }}</td>
									</tr>
									<tr style='border-top: solid black 1px;'>
										<td style='text-align: center;'>Employee</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-md-12">
						<label class="col-md-12">Agreed by: </label>
						<div class="col-md-12">
							<table>
								<tbody>
									<tr>
										<td align="center">
											@if ($user_empno == $commitment->commit_agreedby && $commitment->commit_id)
												<div class="float-end">
													<button type="button" class="btn btn-outline-secondary" onclick="sign_commitment('{{ $commitment->commit_agreedby }}', 'agreedby')">Sign</button>
												</div>
											@endif
											<div class="div-signature" align="center">
												{!! $commitment->commit_agreedby_sign !!}
											</div>
										</td>
									</tr>
									<tr>
										<td style='width:250px; text-align: center;'>
											{{ $commitment->commit_agreedby_name_init }}
										</td>
									</tr>
									<tr style='border-top: solid black 1px;'>
										<td style='text-align: center;'>Immediate Head </td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				@if($commitment->commit_preparedby_sign=="" && $commitment->commit_agreedby_sign=="")
					<div align="center">
						@if($commitment->commit_id!="")
							<button class="btn btn-success" type="button" id="btn-cp-edit">Edit</button>
							<button class="btn btn-danger" type="button" id="btn-cp-cancel" style="display: none;">Cancel</button>
						@endif
						<button class="btn btn-primary" type="submit" id="btn-cp-save" style="{{ ($commitment->commit_id!="" ? "display: none;" : "") }}">Save</button>
					</div>
				@endif
			</form>

			<span class="float-start">
				<button class="btn btn-info" onclick="view13A('{{ $_13a->{'13a_id'} }}')">View 13A</button>
			</span>
			<span class="float-end">
				{{-- @if(($_13a->{'13a_stat'}=="issued" || $_13a->{'13a_stat'}=="received" || $_13a->{'13a_stat'}=="refused") && Auth::user()->userAccess('grievance','review',$user_empno) && $_13b_id=="")
					<a href="?page=13b&_13a={{ $_13a->{'13a_id'} }}" class="btn btn-primary">Create 13B</a>
				@elseif($_13b_id!="")
					<a href="?page=13b&no={{ $_13b_id }}&_13a={{ $_13a->{'13a_id'} }}" class="btn btn-info">View 13B</a>
				@endif --}}
				@if($commitment->commit_id!="")
					<button type="button" class="btn btn-outline-secondary" onclick="print_commitment()"><i class="fa fa-print"></i></button>
				@endif
			</span>
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
			<button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" onclick="cancel_sign_commitment()">Cancel</button>
			<button type="button" class="btn btn-outline-secondary btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
			<button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" onclick="save_sign_commitment()">Save</button>
		</div>
	</div>
</div>

<iframe src="" id="print_commitment" style="display: none;"></iframe>

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

		$('#btn-for-sign button[data-action="clear"]').click(function(){
			signaturePad.clear();
		});

		$('textarea').autoResize();

		$("#btn-cp-edit").click(function(){
			$("#btn-cp-cancel").show();
			$("#btn-cp-save").show();
			$(this).hide();

			$("#form-commitment fieldset").attr("disabled",false);
		});

		$("#btn-cp-cancel").click(function(){
			$("#btn-cp-edit").show();
			$("#btn-cp-save").hide();
			$(this).hide();

			$("#form-commitment fieldset").attr("disabled",true);
		});

		$("#form-commitment").submit(async function(e){
			e.preventDefault();

			var arr_cp = [];
			
			$("#tbl-commitment tbody").find("tr").each(function(){
				arr_cp.push([ $(this).find("[name='cp_row_learn']").val(), $(this).find("[name='cp_row_commit']").val(), $(this).find("[name='cp_row_start']").val(), $(this).find("[name='cp_row_id']").val() ]);
			});

			$('#err-msg').html("");

			let formData = new FormData();
			formData.append("id", $("#commit-id").val());
			formData.append("_13a", $("#commit-13a").val());
			formData.append("preparedby", $("#commit-preparedby").val());
			formData.append("agreedby", $("#commit-agreedby").val());
			formData.append('cp', JSON.stringify(arr_cp));

			let response = await fetch('/grievance/commitment/save', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				$("body").removeClass('overflow-hidden');
				alert('Saved');
				viewCommitment($("#commit-13a").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		});
	});

	function add_commitment() {
		var txt1="";

		txt1+="<tr>";
		txt1+="<td><textarea style='width: 100%;' name='cp_row_learn'></textarea></td>";
		txt1+="<td><textarea style='width: 100%;' name='cp_row_commit'></textarea></td>";
		txt1+="<td><textarea style='width: 100%;' name='cp_row_start'></textarea></td>";
		txt1+="<td><input type='hidden' name='cp_row_id' value=''><button class='btn btn-danger btn-sm' onclick='del_commitment(this)'><i class='fa fa-times'></i></button></td>";
		txt1+="</tr>";

		$("#tbl-commitment tbody").append(txt1);
	}

	function del_commitment(_this1) {
		$(_this1).parents("tr").remove();
	}

	function sign_commitment(empno, type1) {
		if ($(window).height() > $(window).width()) {
	        alert("Please rotate phone to landscape");
	    } else {
	    	$("#sign-type").val(type1);
			$("#sign-empno").val(empno);

			$("body").addClass('overflow-hidden');
			$("#signature-pad-wrapper").addClass('show');

			$("#div_sign").css({"width": "100%", "height": "100vh"});
	    	$("#signature-pad").css({"width": "100%", "height": "90%"});
			resizeCanvas();
	    }
	}
	function cancel_sign_commitment() {
		$("body").removeClass('overflow-hidden');
		$("#signature-pad-wrapper").removeClass('show');
	}

	async function save_sign_commitment() {
		$('#err-msg').html("");

		let formData = new FormData();
		formData.append("id", $("#commit-id").val());
		formData.append("_13a", $("#commit-13a").val());
		formData.append("sign", signaturePad.toSVG());
		formData.append("type", $("#sign-type").val());
		formData.append("empno", $("#sign-empno").val());

		let response = await fetch('/grievance/commitment/sign', {
			method: "POST",
			body: formData,
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
			}
		});

		if (response.ok) {
			$("body").removeClass('overflow-hidden');
			alert('Signed');
			viewCommitment($("#commit-13a").val());
		} else {
			let result = await response.json();
			$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
		}
	}

	async function print_commitment(){
		try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/grievance/commitment/print/'+$("#commit-13a").val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $("#print_commitment").attr("srcdoc",html);
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
	}
</script>