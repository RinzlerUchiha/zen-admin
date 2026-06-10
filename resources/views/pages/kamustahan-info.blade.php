@extends('layouts.layout')

@section('content')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

    <style>
        #kamustahan-wrapper {
            font-size: 12px;
        }

        .breadcrumb-chevron {
            --bs-breadcrumb-divider: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            gap: .5rem;
        }
        .breadcrumb-chevron .breadcrumb-item {
            display: flex;
            gap: inherit;
            align-items: center;
            padding-left: 0;
            line-height: 1;
        }
        .breadcrumb-chevron .breadcrumb-item::before {
            gap: inherit;
            float: none;
            width: 1rem;
            height: 1rem;
        }
    </style>

    <script>
        $(function(){
            $('.selectpicker').selectpicker();
            $('textarea').autoResize();

            $('#btn-kamustahan-edit').click(function(){
                $(this).hide();
                $('#btn-kamustahan-save').show();
                $("#form-kamustahan fieldset").attr('disabled', false);
            });

            $("#form-kamustahan").submit(async function(e){
                e.preventDefault();
                $('#err-msg').html("");

                let formData = new FormData();
                formData.append("id", $("#kamustahan-id").val());
                formData.append("empno", $("#kamustahan-empno").val());
                formData.append("position", $("#kamustahan-position").val());
                formData.append("dept", $("#kamustahan-dept").val());
                formData.append("superior", $("#kamustahan-superior").val());
                formData.append("interviewer", $("#kamustahan-interviewer").val());
                formData.append("datetime", $("#kamustahan-datetime").val());
                let arr_answers = [];
                $(".div-kamustahan-ansq").each(function(){
                    arr_answers.push([$(this).find('.kamustahan-question').val(), $(this).find(".kamustahan-answer").val()]);
                });
                formData.append("answers", JSON.stringify(arr_answers));

                let response = await fetch('/kamustahan/save', {
                    method: "POST",
                    body: formData,
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') }
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
                    if($("#kamustahan-id").val()){
                        alert("Saved");
                        $('#btn-kamustahan-edit').show();
                        $('#btn-kamustahan-save').hide();
                        $("#form-kamustahan fieldset").attr('disabled', true);
                    }else{
                        alert("Saved");
                        window.location = '/kamustahan/list/' + $('#kamustahan-empno').val();
                    }
                } else {
                    // let result = await response.json();
                    $('#err-msg').html(`<p style="color: red;">Error: ${errmsg}</p>`);
                }
            });
        });

        async function save_remark() {
            $('#err-msg').html("");

            let formData = new FormData();
            formData.append("id", $("#kamustahan-id").val());
            formData.append("empno", $("#kremarks-empno").val());
            formData.append("remark", $("#kremarks-content").val());

            let response = await fetch('/kamustahan/remark/save', {
                method: "POST",
                body: formData,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') }
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
                alert("Saved");
                let tr = '<tr>';
                tr += '<td>' + $('#kremarks-empno').closest('td').text() + '</td>';
                tr += '<td>' + $('#kremarks-content').val() + '</td>';
                tr += '<td>Now</td>';
                tr += '</tr>';
                $('#row-input').before(tr);
                $('#kremarks-content').val('');
            } else {
                // let result = await response.json();
                $('#err-msg').html(`<p style="color: red;">Error: ${errmsg}</p>`);
            }
        }
    </script>
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-chevron">
                <li class="breadcrumb-item"><a class="link-body-emphasis fw-semibold text-decoration-none" href="{{ config('app.url') }}/kamustahan">Kamustahan</a></li>
                <li class="breadcrumb-item"><a class="link-body-emphasis fw-semibold text-decoration-none" href="{{ config('app.url') }}/kamustahan/list/{{ $data->ekmst_empno }}">{{ isset($employees[$data->ekmst_empno]) ? trim(ucwords($employees[$data->ekmst_empno]['pers_lastname'].", ".$employees[$data->ekmst_empno]['pers_firstname'])) : '' }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ ($data->ekmst_id != "" ? date('M d, Y h:i A',strtotime($data->ekmst_intvwdate)) : "New") }}</li>
            </ol>
        </nav>
        <div class="row mb-3" id="kamustahan-wrapper">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        {{-- <div class="d-flex">
                            <a href="{{ '/kamustahan/list/' . $data->ekmst_empno }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i></a>
                        </div> --}}
                        <form id="form-kamustahan" class="mb-3">
                            <fieldset {{ $data->ekmst_id ? "disabled" : "" }}>
                                <input type="hidden" name="kamustahan-id" id="kamustahan-id" value="{{ $data->ekmst_id }}">
                                <div class="row mb-3">
                                    <label for="kamustahan-empno" class="col-form-label col-md-3">Name</label>
                                    <div class="col-md-8">
                                        @if (Auth::user()->userAccess("kamustahan","view"))
                                            <select class="form-control form-control-sm selectpicker" data-width="fit" name="kamustahan-empno" id="kamustahan-empno" title="Select Employee" data-live-search="true" required>
                                                @foreach ($employees as $k => $v)
                                                    @if($v['ji_remarks'] == 'Active' || $data->ekmst_empno == $v['pers_empno'])
                                                        <option 
                                                        attr_pos="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->jd_title ?? "" }}" 
                                                        attr_dept="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->Dept_Name ?? "" }}"
                                                        attr_sup="{{ $employeeLatestJobInfo['jobrec']->firstWhere('jrec_empno', $k)->jrec_reportto ?? "" }}"
                                                        value="{{ $k }}" {{ ($data->ekmst_empno == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="hidden" name="kamustahan-empno" id="kamustahan-empno" value="{{ $data->ekmst_empno }}">
                                            <label class="col-form-label">{{ isset($employees[$data->ekmst_empno]) ? trim(ucwords($employees[$data->ekmst_empno]['pers_firstname']." ".getNameInitials($employees[$data->ekmst_empno]['pers_midname']))." ".$employees[$data->ekmst_empno]['pers_lastname']) : '' }}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-3">Position</label>
                                    <div class="col-md-8">
                                        <input type="hidden" name="kamustahan-position" id="kamustahan-position" value="{{ $data->ekmst_pos }}">
                                        <label class="col-form-label">{{ isset($positionList[$data->ekmst_pos]) ? $positionList[$data->ekmst_pos]->jd_title : "" }}</label>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-3">Department</label>
                                    <div class="col-md-8">
                                        <input type="hidden" name="kamustahan-dept" id="kamustahan-dept" value="{{ $data->ekmst_dept }}">
                                        <label class="col-form-label">{{ isset($departmentList[$data->ekmst_dept]) ? $departmentList[$data->ekmst_dept]->Dept_Name : "" }}</label>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="kamustahan-superior" class="col-form-label col-md-3">Immediate Superior</label>
                                    <div class="col-md-8">
                                        @if (Auth::user()->userAccess("kamustahan","view"))
                                            <select class="form-control form-control-sm selectpicker" data-width="fit" name="kamustahan-superior" id="kamustahan-superior" title="Select Employee" data-live-search="true" required>
                                                @foreach ($employees as $v)
                                                    @if($v['ji_remarks'] == 'Active' || $data->ekmst_superior == $v['pers_empno'])
                                                        <option value="{{ $v['pers_empno'] }}" {{ ($data->ekmst_superior == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="hidden" name="kamustahan-superior" id="kamustahan-superior" value="{{ $data->ekmst_superior }}">
                                            <label class="col-form-label">{{ isset($employees[$data->ekmst_superior]) ? trim(ucwords($employees[$data->ekmst_superior]['pers_firstname']." ".getNameInitials($employees[$data->ekmst_superior]['pers_midname']))." ".$employees[$data->ekmst_superior]['pers_lastname']) : '' }}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-12">Questions for Kamustahan</label>
                                </div>
                                @foreach ($questions as $k => $item)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h5 class="card-title">{{ $k }}</h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($item as $v)
                                                <div class="row mb-3 div-kamustahan-ansq">
                                                    <label class="col-form-label col-md-12"><input type="hidden" class="kamustahan-question" value="{{ $v->kmst_id }}"> - {{ $v->kmst_question }}</label>
                                                    <div class="col-md-12">
                                                        <textarea name="kamustahan-answer" oninput="this.value.replace(/[^a-zA-Z0-9-ñÑ%#,.?() ]/g, '');" class="form-control kamustahan-answer">{{ $data->answers["kmstansq_".$v->kmst_id] ?? "" }}</textarea>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                
                                <div class="row mb-3">
                                    <label for="kamustahan-interviewer" class="col-form-label col-md-3">Interviewed by (ER/RO)</label>
                                    <div class="col-md-8">
                                        @if (Auth::user()->userAccess("kamustahan","view"))
                                            <select class="form-control form-control-sm selectpicker" data-width="fit" name="kamustahan-interviewer" id="kamustahan-interviewer" title="Select Employee" data-live-search="true" required>
                                                @foreach ($employees as $v)
                                                    @if($v['ji_remarks'] == 'Active' || $data->ekmst_interviewer == $v['pers_empno'])
                                                        <option value="{{ $v['pers_empno'] }}" {{ ($data->ekmst_interviewer == $v['pers_empno'] ? "selected" : "") }}>{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="hidden" name="kamustahan-interviewer" id="kamustahan-interviewer" value="{{ $data->ekmst_interviewer }}">
                                            <label class="col-form-label">{{ isset($employees[$data->ekmst_interviewer]) ? trim(ucwords($employees[$data->ekmst_interviewer]['pers_firstname']." ".getNameInitials($employees[$data->ekmst_interviewer]['pers_midname']))." ".$employees[$data->ekmst_interviewer]['pers_lastname']) : '' }}</label>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="kamustahan-datetime" class="col-form-label col-md-3">Date and Time: </label>
                                    <div class="col-md-8">
                                        <input type="datetime-local" name="kamustahan-datetime" id="kamustahan-datetime" value="{{ ($data->ekmst_intvwdate != "" ? date('Y-m-d\TH:i',strtotime($data->ekmst_intvwdate)) : "") }}" required>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="flex justify-content-end">
                                <button type="submit" id="btn-kamustahan-save" class="btn btn-primary" {!! $data->ekmst_id ? "style='display: none;'" : "" !!}>Save</button>
                                <button type="button" id="btn-kamustahan-edit" class="btn btn-outline-secondary" {!! !$data->ekmst_id ? "style='display: none;'" : "" !!}>Edit</button>
                            </div>
                        </form>
                        @if($data->ekmst_id)
                            <h5>Remarks:</h5>
                            <table id="tbl-remarks" class="table table-sm table-striped">
                                @foreach ($data->remarks as $v)
                                    <tr>
                                        <td>{{ isset($employees[$v->kmstre_empno]) ? trim(ucwords($employees[$v->kmstre_empno]['pers_lastname'].", ".$employees[$v->kmstre_empno]['pers_firstname'])) : '' }}</td>
                                        <td>{!! nl2br($v->kmstre_remarks) !!}</td>
                                        <td>{{ date('Y-m-d h:i A',strtotime($v->kmstre_timestamp)) }}</td>
                                    </tr>
                                @endforeach
                                <tr id="row-input">
                                    <td>
                                        <input type="hidden" id="kremarks-empno" value="{{ $user_empno }}">
                                        {{ isset($employees[$user_empno]) ? trim(ucwords($employees[$user_empno]['pers_lastname'].", ".$employees[$user_empno]['pers_firstname'])) : '' }}
                                    </td>
                                    <td style="height: 20px;">
                                        <textarea class="form-control" id="kremarks-content"></textarea>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="save_remark()">Save</button>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop