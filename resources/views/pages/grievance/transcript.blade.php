<style>
    #form-ht {
        counter-reset: htq;
    }

    .htq {
        counter-increment: htq;
    }

    .htq:before {
        content: counter(htq) '.) ';
    }

    #form-ht .control-label {
        text-align: left;
    }

    #form-ht tbody td
    {
        padding: 8px;
        vertical-align: top;
    }

    .tdsign
    {
        height: 1px;
    }

    .td-v-align-bot
    {
        vertical-align: bottom !important;
        height: 1px;
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
    <div class="col-md-8 col-md-offset-2">
        <div class="card">
            <div class="card-header">
                <button onclick="view13A('{{ $_13a->{'13a_id'} }}')" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i></button>
                <label>Administrative Hearing Transcription</label>
                @if($hearing_transcript->ht_id!="" && Auth::user()->userAccess('grievance','review'))
                    <span class="float-end">
                        <button class="btn btn-danger btn-sm" onclick="del_transcript()"><i class="fa fa-trash"></i></button>
                    </span>
                @endif
            </div>
            <div class="card-body">
                <form id="form-ht">
                    <input type="hidden" id="ht-id" value="{{ $hearing_transcript->ht_id }}">
                    <input type="hidden" id="ht-employee" value="{{ $hearing_transcript->ht_employee }}">
                    <input type="hidden" id="ht-13a" value="{{ $_13a->{'13a_id'} }}">
                    <fieldset>
                        <center>Memorandum No. <u>{{ $_13a->{'13a_memo_no'} }}</u></center>
                        <br>

                        <div class="row mb-3">
                            <p class="col-md-12">Alleged violation of {!! nl2br($violation_str) !!}</p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p>Committee Members:</p>
                                <table>
                                    <tbody id="div-committee">
                                        @foreach($hearing_committee as $k1 => $v1)
                                            <tr class="committee-item" empno="{{ $v1->hc_empno }}">
                                                <td class="td-v-align-bot">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary btndelcommittee"><i class="fa fa-times"></i></button>
                                                </td>
                                                <td class="td-v-align-bot">
                                                    {{ trim(ucwords($v1->pers_firstname) . " " . strtoupper(getNameInitials($v1->pers_midname)) . " " . ucwords($v1->pers_lastname)) }}
                                                </td>
                                                <td id="div-committee_{{ $v1->hc_empno }}" class="tdsign">
                                                    <div class="div-signature">{!! $v1->hc_sign !!}</div>
                                                    @if($v1->hc_sign == "" && $user_empno == $v1->hc_empno)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-sign" onclick="starthtsign('{{ $v1->hc_empno }}', 'committee')">Sign</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <br>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addcommittee()"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <p class="col-md-12">Presiding Officer</p>
                            <div class="col-md-12">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="td-v-align-bot">
                                                <select class="form-control selectpicker" id="presiding-officer" data-width="fit" title="Select Employee" data-live-search="true" >
                                                    @foreach ($employees as $k => $v)
                                                        @if($v['ji_remarks'] == 'Active' || $hearing_transcript->ht_presiding_officer == $v['pers_empno'])
                                                            <option 
                                                                attr_pos="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->jd_title : "" }}" 
                                                                attr_dept="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->Dept_Name : "" }}"
                                                                attr_company="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->C_Name : "" }}"
                                                                value="{{ $v['pers_empno'] }}" {{ $hearing_transcript->ht_presiding_officer == $v['pers_empno'] ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            @if($hearing_transcript->ht_id!="")
                                                <td id="div-officer_{{ $hearing_transcript->ht_presiding_officer }}" class="tdsign">
                                                    <div class="div-signature">{!! $hearing_transcript->ht_officersign !!}</div>
                                                    @if($hearing_transcript->ht_officersign == "" && $user_empno == $hearing_transcript->ht_presiding_officer)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-sign" onclick="starthtsign('_{{ $hearing_transcript->ht_presiding_officer }}', 'officer')">Sign</button>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <p class="col-md-12">Scribe</p>
                            <div class="col-md-12">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="td-v-align-bot">
                                                <select class="form-control selectpicker" id="scribe" data-width="fit" title="Select Employee" data-live-search="true" >
                                                    @foreach ($employees as $k => $v)
                                                        @if($v['ji_remarks'] == 'Active' || $hearing_transcript->ht_scribe == $v['pers_empno'])
                                                            <option 
                                                                attr_pos="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->jd_title : "" }}" 
                                                                attr_dept="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->Dept_Name : "" }}"
                                                                attr_company="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k) ? $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->C_Name : "" }}"
                                                                value="{{ $v['pers_empno'] }}" {{ $hearing_transcript->ht_scribe == $v['pers_empno'] ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            @if($hearing_transcript->ht_id != "")
                                                <td id="div-scribe_{{ $hearing_transcript->ht_scribe }}" class="tdsign">
                                                    <div class="div-signature">{!! $hearing_transcript->ht_scribesign !!}</div>
                                                    @if($hearing_transcript->ht_scribesign == "" && $user_empno == $hearing_transcript->ht_scribe)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-sign" onclick="starthtsign('{{ $hearing_transcript->ht_scribe }}', 'scribe')">Sign</button>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <p class="col-md-12">Alleged Employee:</p>
                            <div class="col-md-12">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="td-v-align-bot">
                                                {{ isset($employees[$hearing_transcript->ht_employee]) ? trim(ucwords($employees[$hearing_transcript->ht_employee]['pers_firstname']." ".getNameInitials($employees[$hearing_transcript->ht_employee]['pers_midname']))." ".$employees[$hearing_transcript->ht_employee]['pers_lastname']) : "" }}
                                            </td>
                                            @if($hearing_transcript->ht_id!="")
                                                <td id="div-emp_{{ $hearing_transcript->ht_employee }}" class="tdsign">
                                                    <div class="div-signature">{!! $hearing_transcript->ht_empsign !!}</div>
                                                    @if($hearing_transcript->ht_empsign == "" && $user_empno == $hearing_transcript->ht_employee)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-sign" onclick="starthtsign('{{ $hearing_transcript->ht_employee }}', 'emp')">Sign</button>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <p class="col-md-3 control-label">Date and Time Started:</p>
                            <div class="col-md-9">
                                <input type="datetime-local" class="form-control" style="width: auto;" id="datetime-started" value="{{ $hearing_transcript->ht_datetime_started }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <p class="col-md-12">Presiding Officer: Reading of Incident Report</p>
                            <p class="col-md-12">Presiding Officer: Reading of Memorandum No. <u>{{ $_13a->{'13a_memo_no'} }}</u></p>
                            <p class="col-md-12">Presiding Officer: Reading of employee’s response to Memorandum No. <u>{{ $_13a->{'13a_memo_no'} }}</u></p>
                        </div>

                        @foreach($hq_arr as $k1 => $v1)
                            <div class="row mb-3">
                                <p class="col-md-12">{!! $v1[0] !!}</p>
                                <span class="htq" style="display: none;">{!! $v1[0] !!}</span>
                                <div class="col-md-12">
                                    <textarea class="form-control hta" hqid="{{ (isset($v1[2]) ? $v1[2] : "") }}" htanum="{{ $k1+1 }}">{{ $v1[1] }}</textarea>
                                </div>
                            </div>
                        @endforeach
         
                        <div class="row mb-3">
                            <p class="col-md-2 control-label">Time Ended:</p>
                            <div class="col-md-10">
                                <input type="time" class="form-control" id="time-ended" style="width: auto;" value="{{ $hearing_transcript->ht_time_ended }}">
                            </div>
                        </div>

                    </fieldset>

                    <center id="btn-ht-save" style="{{ ($hearing_transcript->ht_id!="" ? "display: none;" : "") }}"><button type="submit" class="btn btn-primary">Save</button></center>
                    <center id="btn-ht-edit" style="{{ ($hearing_transcript->ht_id=="" ? "display: none;" : "") }}"><button type="button" class="btn btn-success">Edit</button></center>
                </form>
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
			<button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" data-action="clear" onclick="cancel_sign()">Cancel</button>
			<button type="button" class="btn btn-outline-secondary btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
			<button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" id="btnhcsign">Save</button>
		</div>
	</div>
</div>

<div class="modal fade" data-bs-backdrop="static" id="htModal" tabindex="-1" role="dialog" aria-labelledby="">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select class="form-control selectpicker" id="htaddcommittee" title="Select Employee/s" data-live-search="true" multiple data-actions-box="true" required>
                    @foreach ($employees as $k => $v)
                        @if($v['ji_remarks'] == 'Active')
                            <option value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnaddcommittee">Add</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" defer></script>

<script>
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

    $(function(){
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

		$('.selectpicker').selectpicker('refresh');

        if($('#ht-id').val() != ""){
            $("#form-ht fieldset").find("input, button, textarea, select").not(".btn-sign").prop("disabled", true);
            $(".selectpicker").selectpicker("refresh");
        }

        $("#btnaddcommittee").click(function(){

            $("#htaddcommittee option:selected").each(function(){
                $("#div-committee").append("<tr class='committee-item' empno='" + this.value + "'><td class=\"td-v-align-bot\"><button type='button' class='btn btn-xs btn-outline-secondary btndelcommittee'><i class='fa fa-times'></i></button></td><td class=\"td-v-align-bot\">" + $(this).text() + "</td><td id='div-committee_" + this.value + "' class=\"tdsign\"></td></tr>");
            });

            $("#htaddcommittee option:selected").prop("disabled", true);
            $("#htaddcommittee").selectpicker("refresh");

            $("#htModal").modal("hide");
        });

        $("#div-committee").on("click", ".btndelcommittee", function(){
            $("#htaddcommittee option[value='" + $(this).parent().parent().attr("empno") + "']").prop("disabled", false);
            $(this).parent().parent().remove();
        });

        $("#form-ht").submit(async function(e){
            e.preventDefault();

            let committee = [];
            $(".committee-item").each(function(){
                committee.push($(this).attr("empno"));
            });

            let hq_arr = [];
            $(".htq").each(function(){
                hq_arr.push([
                    $(this).text(),
                    $(this).parent().find(".hta").val(),
                    $(this).parent().find(".hta").attr("hqid")
                ]);
            });

            let formData = new FormData();
			formData.append("id", $("#ht-id").val());
			formData.append("presiding_officer", $("#presiding-officer").val());
            formData.append("scribe", $("#scribe").val());
            formData.append("employee", $("#ht-employee").val());
            formData.append("datetime_started", $("#datetime-started").val());
            formData.append("time_ended", $("#time-ended").val());
            formData.append("_13a", $("#ht-13a").val());
            formData.append("committee", committee.join(","));
            formData.append("questions", JSON.stringify(hq_arr));

			let response = await fetch('/grievance/transcript/save', {
				method: "POST",
				body: formData,
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			const result = await response.json();

			if (response.ok && !result.error) {
                alert("Saved");
				viewTranscript($("#ht-13a").val());
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.error}</p>`);
			}
        });

        $("#committee-item").each(function(){
            $("#htaddcommittee option[value='" + $(this).attr("empno") + "']").prop("disabled", true);
        });

        $("#btn-ht-edit").click(function(){
            // $("#form-ht fieldset").prop("disabled", false);
            $("#form-ht fieldset").find("input, button, textarea, select").not(".btn-sign").prop("disabled", false);
            $(".selectpicker").selectpicker("refresh");
            $("#div-committee")
            $("#btn-ht-save").show();
            $("#btn-ht-edit").hide();
        });

        $("#btnhcsign").click(async function(){
            if(signaturePad.isEmpty()){
                alert("Please Sign");
            }else{
                $('#err-msg').html("");

                let formData = new FormData();
                formData.append("id", $("#ht-id").val());
                formData.append("_13a", $("#ht-13a").val());
                formData.append("sign", signaturePad.toSVG());
                formData.append("type", $("#sign-type").val());
                formData.append("empno", $("#sign-empno").val());

                let response = await fetch('/grievance/transcript/sign', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (response.ok) {
                    $("body").removeClass('overflow-hidden');
                    $("#signature-pad-wrapper").removeClass('show');
                    alert('Signed');
                    viewTranscript($("#ht-13a").val());
                } else {
                    let result = await response.json();
                    $('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
                }
            }
        });
    });

    function starthtsign(_emp, _type1) {
        if ($(window).height() > $(window).width()) {
	        alert("Please rotate phone to landscape");
	    } else {
	    	$("#sign-type").val(_type1);
			$("#sign-empno").val(_emp);

			$("body").addClass('overflow-hidden');
			$("#signature-pad-wrapper").addClass('show');

			$("#div_sign").css({"width": "100%", "height": "100vh"});
	    	$("#signature-pad").css({"width": "100%", "height": "90%"});

			// setTimeout(function(){
				resizeCanvas();
			// }, 1000);

	    }
    }

    function cancel_sign(_type1, _id = '') {
		$("body").removeClass('overflow-hidden');
		$("#signature-pad-wrapper").removeClass('show');
	}

    function addcommittee() {
        $("#htModal").modal("show");
        $("#htaddcommittee").val("").selectpicker("refresh");
    }

    async function deltranscript(_id, _13a) {
        if (confirm("Are you sure?")) {
			$('#err-msg').html("");

			let response = await fetch('/grievance/transcript/delete/'+$("#13a-id").val(), {
				method: "DELETE",
				headers: {
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				}
			});

			if (response.ok) {
				alert('Removed');
				view13A($("#13a-id").val());
			} else {
				let result = await response.json();
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
		}
    }
</script>