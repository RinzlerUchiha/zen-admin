@if(!empty($data))
    <style>
        #form-exit-interview * {
            font-size: 12px;
        }

        #form-exit-interview [type="date"] {
            width: fit-content;
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
                <div class="clearfix">
                    <div class="float-start">
                        <button class="btn btn-light" onclick="viewInfo('{{ $data->xintvw_empno }}')"><i class="fa fa-arrow-left"></i></button>
                    </div>
                    <div class="float-end">
                        <button class="btn btn-close" onclick="closeInfo()"></button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="form-exit-interview">
                    <input type="hidden" id="exit-id" value="{{ $data->xintvw_id }}">
                    <div class="row mb-3">
                        <label class="col-form-label col-md-2">Name:</label>
                        <div class="col-md-4">
                            <input type="hidden" id="exit-emp" value="{{ $data->xintvw_empno }}">
                            <label class="col-form-label">{{ isset($employees[$data->xintvw_interviewer]) ? trim(ucwords($employees[$data->xintvw_empno]['pers_firstname']." ".getNameInitials($employees[$data->xintvw_empno]['pers_midname'])).$employees[$data->xintvw_empno]['pers_lastname']) : "" }}</label>
                        </div>

						<label class="col-form-label col-md-2 offset-md-1">Date of Resignation: </label>
						<div class="col-md-3">
							<input type="date" id="exit-dtresign" class="form-control" value="{{ $data->xintvw_dtresign }}">
						</div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-md-2">Position:</label>
                        <div class="col-md-4">
                            <input type="hidden" id="exit-pos" value="{{ $data->xintvw_pos }}">
                            <label class="col-form-label">{{ isset($positionList[$data->xintvw_pos]) ? $positionList[$data->xintvw_pos]->jd_title : "" }}</label>
                        </div>

                        <label class="col-form-label col-md-2 offset-md-1">Department:</label>
                        <div class="col-md-3">
                            <input type="hidden" id="exit-dept" value="{{ $data->xintvw_dept }}">
                            <label class="col-form-label">{{ isset($departmentList[$data->xintvw_dept]) ? $departmentList[$data->xintvw_dept]->Dept_Name : "" }}</label>
                        </div>
                    </div>

                    <div class="row mb-3">
						<label class="col-form-label col-md-2">Date of Hire: </label>
						<div class="col-md-4">
							<input type="date" id="exit-hiredt" class="form-control" value="{{ $data->xintvw_dthired }}">
						</div>
						<label class="col-form-label col-md-2 offset-md-1">Last Workday: </label>
						<div class="col-md-3">
							<input type="date" id="exit-lastdt" class="form-control" value="{{ $data->xintvw_lastday }}">
						</div>
					</div>

                    <div class="row mb-3">
                        <label class="col-form-label col-md-2">Immediate Superior:</label>
                        <div class="col-md-4">
                            <select class="form-control selectpicker" id="exit-superior" title="Select Employee" data-live-search="true">
                                @foreach ($employees as $k => $v)
                                    @if($v['ji_remarks'] == 'Active' || $data->xintvw_superior == $v['pers_empno'])
                                        <option value="{{ $v['pers_empno'] }}" {{ $data->xintvw_superior == $v['pers_empno'] ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

						<label class="col-form-label col-md-2 offset-md-1">Employee Number: </label>
						<div class="col-md-3">
                            <label class="col-form-label">{{ $data->xintvw_empno }}</label>
						</div>
                    </div>

                    {{-- fix this --}}
                    <div class="row mb-3">
						<label class="col-form-label col-md-12">What are your reasons for leaving?</label>
						<div class="col-md-12">
					        @foreach ($questions->where('exit_type', 'reason') as $q)
								<div class="row mb-3">
									<label class="col-form-label col-md-12"><input type="checkbox" class="xreason" value="{{ $q->exit_id }}"  {{ (isset($data->answers["xansq_".$q->exit_id]) ? "checked" : "") }}>{{ $q->exit_question }}</label>
									<div class="col-md-12 div-xansq" style="{{ (!isset($data->answers["xansq_".$q->exit_id]) ? "display: none;" : "") }}">
										<textarea name="exit-ans" oninput="this.value.replace(/[^a-zA-Z0-9-ñÑ%#,.?() ]/g, '');" class="form-control" {{ (isset($data->answers["xansq_".$q->exit_id]) ? "required" : "") }}>{{ (isset($data->answers["xansq_".$q->exit_id]) ? $data->answers["xansq_".$q->exit_id] : "") }}</textarea>
									</div>
								</div>
					        @endforeach
						</div>
					</div>

                    @foreach ($questions->where('exit_type', 'question') as $q)
                        <div class="row mb-3">
                            <label class="col-form-label col-md-12"><input type="hidden" class="xquestion" value="{{ $q->exit_id }}">{{ $q->exit_question }}</label>
                            <div class="col-md-12 div-xansq">
                                <textarea name="exit-ans" oninput="this.value.replace(/[^a-zA-Z0-9-ñÑ%#,.?() ]/g, '');" class="form-control" required>{{ (isset($data->answers["xansq_".$q->exit_id]) ? $data->answers["xansq_".$q->exit_id] : "") }}</textarea>
                            </div>
                        </div>
                    @endforeach

					<div class="row mb-3">
						<div class="col-md-12">
							<p>Thank you for your feedback.<br><br>You are reminded of the restriction in your contract against sharing of confidential information you acquired here in SJI/STI/TNGC. Even after you leave us, you are not allowed to disclose any information you acquired here as you agreed to in your contract. You are also not allowed to work for a competing company like (Oro, Oro Italia, Jewels by Audrey, Gaisano Jewelry, Helen, Dalton and Billions, Eden, Tai Sui, etc) or (AMA, St. Augustine, Comtech, UZ, Southern, Winzelle, etc.) for a period of 2 years/6 months from your last day of employment here in STI/SJI/TNGC.</p>
						</div>
					</div>

					@if($data->xintvw_id!="")
                        <div class="row mb-3">
                            <div class="col-md-5" align="center">
                                <div id="exit-signature" class="div-signature" align="center">
                                    {!! $data->xintvw_empsign !!}
                                </div>
                                <center>
                                    @if($data->xintvw_empsign == "")
                                        <div class="pull-right">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sign()">Sign</button>
                                        </div>
                                    @endif
                                    <label id="exit-empsign">{{ isset($employees[$data->xintvw_interviewer]) ? trim(ucwords($employees[$data->xintvw_empno]['pers_firstname']." ".getNameInitials($employees[$data->xintvw_empno]['pers_midname'])).$employees[$data->xintvw_empno]['pers_lastname']) : "" }}</label>
                                    <hr style="margin: 0px; border: .5px solid black;">
                                    <p>Employee Name and Signature</p>
                                </center>
                            </div>
                        </div>
					@endif
         			<div class="row mb-3">
						<label class="control-label col-md-2">Interviewed by (ER): </label>
						<div class="col-md-7">
							<select id="exit-interviewer" class="selectpicker" data-live-search="true" title="Select">
                                @foreach ($employees as $k => $v)
                                    @if($v['ji_remarks'] == 'Active' || $data->xintvw_interviewer == $v['pers_empno'])
                                        <option value="{{ $v['pers_empno'] }}" {{ $data->xintvw_interviewer == $v['pers_empno'] ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                    @endif
                                @endforeach
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<label class="control-label col-md-2">Date and Time: </label>
						<div class="col-md-7">
							<input type="datetime-local" id="exit-intvw-datetime" value="{{ date('Y-m-d\TH:i',strtotime($data->xintvw_intvwdate)) }}" required>
						</div>
					</div>
					<div class="row mb-3">
						<label class="control-label col-md-2">Received by (SIS): </label>
						<div class="col-md-7">
							<select id="exit-receiver" class="selectpicker" data-live-search="true" title="Select">
                                @foreach ($employees as $k => $v)
                                    @if($v['ji_remarks'] == 'Active' || $data->xintvw_receivedby == $v['pers_empno'])
                                        <option value="{{ $v['pers_empno'] }}" {{ $data->xintvw_receivedby == $v['pers_empno'] ? "selected" : "" }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                    @endif
                                @endforeach
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<label class="control-label col-md-2">Date and Time: </label>
						<div class="col-md-7">
							<input type="datetime-local" id="exit-received-datetime" value="{{ date('Y-m-d\TH:i',strtotime($data->xintvw_receiveddate)) }}" required>
						</div>
					</div>
					<div>
						<button class="btn btn-primary" type="submit">Submit</button>
					</div>
                </form>
            </div>
        </div>
    </div>

    <div id="signature-pad-wrapper" class="signature-pad-wrapper d-flex flex-column">
        <div id="signature-pad" class="signature-pad flex-grow-1">
              <canvas id="signature-pad-canvas" class="signature-pad-canvas h-100 w-100"></canvas>
        </div>
          <div class="d-grid d-block">
                <div id="btn-for-sign" class="btn-group">
                <button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" onclick="cancel_sign()">Cancel</button>
                <button type="button" class="btn btn-outline-secondary btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
                <button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" onclick="save_sign()">Save</button>
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

            $('.selectpicker').selectpicker('refresh');

            $('textarea').autoResize();
            
            $("#form-exit-interview").submit(async function(e){
                e.preventDefault();
                $('#err-msg').html("");

                var arr_ans=[];
                $(".xreason:checked").each(function(){
                    arr_ans.push($(this).val()+"|"+$(this).parent().parent().find(".div-xansq").find("[name='exit-ans']").val());
                });

                $(".xquestion").each(function(){
                    arr_ans.push($(this).val()+"|"+$(this).parent().parent().find(".div-xansq").find("[name='exit-ans']").val());
                });

                let formData = new FormData();
                formData.append("id", $("#exit-id").val());
                formData.append("emp", $("#exit-emp").val());
                formData.append("pos", $("#exit-pos").val());
                formData.append("superior", $("#exit-superior").val());
                formData.append("dept", $("#exit-dept").val());
                formData.append("dtresign", $("#exit-dtresign").val());
                formData.append("lastdt", $("#exit-lastdt").val());
                formData.append("hiredt", $("#exit-hiredt").val());
                formData.append("intervewer", $("#exit-interviewer").val());
                formData.append("interviewdt", $("#exit-intvw-datetime").val());
                formData.append("receivedby", $("#exit-receiver").val());
                formData.append("receivedt", $("#exit-received-datetime").val());
                formData.append("ans", JSON.stringify(arr_ans));

                let response = await fetch('/exit-interview/save', {
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
                } else {
                    result = await response.text();
                }
                errmsg = !result.success ? result.error : '';

                if (response.ok && !errmsg) {
                    if($("#exit-id").val()){
                        alert("Saved");
                        viewInterview($("#exit-id").val());
                    }else{
                        alert("Saved");
                        $('#outgoing-info').html(result);
                    }
                } else {
                    $('#err-msg').html(`<p style="color: red;">Error: ${errmsg}</p>`);
                }
            });

            $(".xreason").change(function(){
                if($(this).is(":checked")){
                    $(this).parent().parent().find(".div-xansq").show();
                    $(this).parent().parent().find(".div-xansq").find("[name='exit-ans']").attr("required",true);
                }else{
                    $(this).parent().parent().find(".div-xansq").hide();
                    $(this).parent().parent().find(".div-xansq").find("[name='exit-ans']").attr("required",false);
                }
            });
        });
        
        function sign() {
            if ($(window).height() > $(window).width()) {
                alert("Please rotate phone to landscape");
            } else {
                $("body").addClass('overflow-hidden');
                $("#signature-pad-wrapper").addClass('show');

                $("#div_sign").css({"width": "100%", "height": "100vh"});
                $("#signature-pad").css({"width": "100%", "height": "90%"});

                resizeCanvas();
            }
        }

        function cancel_sign() {
            $("body").removeClass('overflow-hidden');
            $("#signature-pad-wrapper").removeClass('show');
        }

        async function save_sign() {
            $('#err-msg').html("");

            let formData = new FormData();
            formData.append("id", $("#exit-id").val());
            formData.append("sign", signaturePad.toSVG());
            formData.append("empno", $("#exit-emp").val());

            let response = await fetch('/exit-interview/sign', {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                $("body").removeClass('overflow-hidden');
                alert('Signed');
                viewInterview($("#exit-id").val());
            } else {
                let result = await response.json();
                $('#err-msg').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        }
    </script>

@else

    <div class="container-fluid">
        <div class="card">
            <div class="card-header clearfix">
                <div class="float-end">
                    <button class="btn btn-close" onclick="closeInfo()"></button>
                </div>
                <div class="card-title">{{ $name }}</div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Interview date</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Interviewer</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($list as $l)
                            <tr ondblclick="viewInterview('{{ $l->xintvw_id }}')">
                                <td>{{ date("F d Y h:i A",strtotime($l->xintvw_intvwdate)) }}</td>
                                <td>{{ isset($departmentList[$l->xintvw_dept]) ? $departmentList[$l->xintvw_dept]->Dept_Name : "" }}</td>
                                <td>{{ isset($positionList[$l->xintvw_pos]) ? $positionList[$l->xintvw_pos]->jd_title : "" }}</td>
                                <td>{{ isset($employees[$l->xintvw_interviewer]) ? trim(ucwords($employees[$l->xintvw_interviewer]['pers_lastname'].", ".$employees[$l->xintvw_interviewer]['pers_firstname'])) : "" }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="float-end">
                    <button class="btn btn-primary btn-sm" onclick="newInterview('{{ $empno }}')">New</button>
                </div>
            </div>
        </div>
    </div>

@endif