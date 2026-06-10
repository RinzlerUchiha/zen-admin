<style>
    #form-jobinfo input,
    #form-jobinfo textarea,
    #form-jobinfo select,

    #form-jobrec input,
    #form-jobrec textarea,
    #form-jobrec select,
    #jobrec-list {
        font-size: 12px;
    }

    #form-jobinfo textarea {
        vertical-align: middle;
    }

    #jobrec-list {
        min-width: 50vw;
        width: fit-content;
    }

    input[type="file"] {
        padding-top: 1.7rem !important;
    }

    /*input[type="file"]::-webkit-file-upload-button {
        vertical-align: middle;
        height: 100%;
    }*/

    .ji-description {
        max-height: 100%;
        height: 100%;
    }

    #jobinfo-remarks-description {
        max-height: calc(100% - 1rem);
        min-height: calc(100% - 1rem);
        height: calc(100% - 1rem);
    }

    #form-jobinfo .form-floating>label {
        font-size: 14px;
    }
</style>

<script type="text/javascript">
    function update_jobrec(e) {
        let btn = $(e);
        $('#form-jobrec input, #form-jobrec select').not('#jobrec-employee-number, [name="_token"]').val("");

        $('#jobrec-id').val(btn.data('id') || '');
        $('#jobrec-company').val(btn.data('company') || '');
        $('#jobrec-department').val(btn.data('department') || '');
        $('#jobrec-section').val(btn.data('section') || '');
        $('#jobrec-area').val(btn.data('area') || '');
        $('#jobrec-outlet').val(btn.data('outlet') || '');
        $('#jobrec-position').val(btn.data('position') || '');
        $('#jobrec-job-grade').val(btn.data('jobgrade') || '');
        $('#jobrec-job-step').val(btn.data('jobstep') || '');
        $('#jobrec-date-effect').val(btn.data('effectdate') || '');
        $('#jobrec-reportto').val(btn.data('reportto') || '');
        $('#jobrec-status').val(btn.data('status') || '');

        $('#form-jobrec').toggleClass('d-none');
        $('#jobrec-list').toggleClass('d-none');
    }

    $(function(){
        $('#btn-edit-jobinfo').click(function(){
            $('#form-jobinfo fieldset').prop('disabled', false);
            $('#form-jobinfo [type="submit"]').toggleClass('d-none');
            $('#btn-cancel-jobinfo').toggleClass('d-none');
            $(this).toggleClass('d-none');
        });

        $('#btn-cancel-jobinfo').click(function(){
            $('#form-jobinfo fieldset').prop('disabled', true);
            $('#form-jobinfo #btn-edit-jobinfo').toggleClass('d-none');
            $('#form-jobinfo [type="submit"]').toggleClass('d-none');
            $(this).toggleClass('d-none');
        });

        $('#btn-cancel-jobrec').click(function(){
            $('#form-jobrec').toggleClass('d-none');
            $('#jobrec-list').toggleClass('d-none');
        });
    });
</script>

<div id="jobrec-list" class="w-100">
    <!-- Success Message -->
    @if(session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form id="form-jobinfo" name="form-jobinfo" class="mb-5" action="{{ config('app.url') }}/save/work/jobinfo" method="POST">
        @csrf
        <input type="hidden" name="employee-number" id="jobinfo-employee-number" value="{{ $empno }}">
        <fieldset disabled>
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-lg-auto">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control-plaintext border-bottom" name="jobinfo-date-hired" id="jobinfo-date-hired" value="{{ $empData['jobinfo']->ji_datehired }}">
                                <label for="jobinfo-date-hired">Date Hired</label>
                            </div>
                        </div>
                        <div class="col-lg-auto">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control-plaintext border-bottom" name="jobinfo-date-regular" id="jobinfo-date-regular" value="{{ $empData['jobinfo']->ji_regdate }}">
                                <label for="jobinfo-date-regular">Date Regular</label>
                            </div>
                        </div>
                        <div class="col-lg-auto flex-fill">
                            <div class="form-floating mb-3">
                                <select class="form-control-plaintext border-bottom" name="jobinfo-remarks" id="jobinfo-remarks" aria-label="">
                                    <option value {{ !$empData['jobinfo']->ji_remarks ? 'selected' : '' }}>-Select-</option>
                                    <option value="Active" {{ $empData['jobinfo']->ji_remarks == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ $empData['jobinfo']->ji_remarks == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <label for="jobinfo-remarks">Status</label>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-even g-3">
                        <div class="col-lg-auto">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control-plaintext border-bottom" name="jobinfo-date-resigned" id="jobinfo-date-resigned" value="{{ $empData['jobinfo']->ji_resdate }}">
                                <label for="jobinfo-date-resigned">Date Resigned</label>
                            </div>
                        </div>
                        <div class="col-lg-auto">
                            <div class="form-floating mb-3">
                                <select class="form-control-plaintext border-bottom" name="jobinfo-separation-type" id="jobinfo-separation-type" aria-label="">
                                    <option value {{ !$empData['jobinfo']->ji_separation ? 'selected' : '' }}>-Select-</option>
                                    <option value="Resignation" {{ $empData['jobinfo']->ji_separation == 'Resignation' ? 'selected' : '' }}>Resignation</option>
                                    <option value="Retirement" {{ $empData['jobinfo']->ji_separation == 'Retirement' ? 'selected' : '' }}>Retirement</option>
                                    <option value="Termination" {{ $empData['jobinfo']->ji_separation == 'Termination' ? 'selected' : '' }}>Termination</option>
                                    <option value="Non-renewal" {{ $empData['jobinfo']->ji_separation == 'Non-renewal' ? 'selected' : '' }}>Non-renewal</option>
                                    <option value="AWOL" {{ $empData['jobinfo']->ji_separation == 'AWOL' ? 'selected' : '' }}>AWOL</option>
                                    <option value="Death" {{ $empData['jobinfo']->ji_separation == 'Death' ? 'selected' : '' }}>Death</option>
                                    <option value="Retrenched" {{ $empData['jobinfo']->ji_separation == 'Retrenched' ? 'selected' : '' }}>Retrenched</option>
                                    <option value="Cancelled" {{ $empData['jobinfo']->ji_separation == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="Indefinite Leave" {{ $empData['jobinfo']->ji_separation == 'Indefinite Leave' ? 'selected' : '' }}>Indefinite Leave</option>
                                </select>
                                <label for="jobinfo-separation-type">Separation Type</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3 ji-description">
                        <textarea class="form-control-plaintext border-bottom border-start" name="jobinfo-remarks-description" id="jobinfo-remarks-description">{{ $empData['jobinfo']->ji_rmksdescription }}</textarea>
                        <label for="jobinfo-remarks-description">Description</label>
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="row">
            <div class="col-lg-8 text-end">
                <button type="button" id="btn-edit-jobinfo" class="btn btn-outline-secondary btn-sm">Edit</button>
                <button type="button" id="btn-cancel-jobinfo" class="btn btn-danger btn-sm mx-1 d-none">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm d-none">Save</button>
            </div>
        </div>
    </form>

    <table class="table table-sm table-striped table-hover" id="jobrec-list-table">
        <thead>
            <tr>
                <th>Effective Date</th>
                <th>Company</th>
                <th>Department</th>
                <th>Section</th>
                <th>Area</th>
                <th>Outlet</th>
                <th>Position</th>
                <th>Job Grade</th>
                <th>Job Step</th>
                <th>Reports To</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($empData['jobrec'] as $list)
            <tr>
                <td class="text-nowrap">{{ $list->jrec_effectdate }}</td>
                <td class="text-nowrap">{{ $list->jrec_company }}</td>
                <td class="text-nowrap">{{ $list->jrec_department }}</td>
                <td class="text-nowrap">{{ $list->jrec_section }}</td>
                <td class="text-nowrap">{{ $list->jrec_area }}</td>
                <td class="text-nowrap">{{ $list->jrec_outlet }}</td>
                <td class="text-nowrap">{{ $list->jd_title }}</td>
                <td class="text-nowrap">{{ $list->jrec_jobgrade }}</td>
                <td class="text-nowrap">{{ $list->jrec_step }}</td>
                <td class="text-nowrap">{{ $list->jrec_reportto }}</td>
                <td class="text-nowrap">{{ $list->jrec_status }}</td>
                <td>
                    <div class="d-flex">
                        <button type="button" 
                        data-id="{{ $list->jrec_id }}"
                        data-effectdate="{{ $list->jrec_effectdate }}"
                        data-company="{{ $list->jrec_company }}"
                        data-department="{{ $list->jrec_department }}"
                        data-section="{{ $list->jrec_section }}"
                        data-area="{{ $list->jrec_area }}"
                        data-outlet="{{ $list->jrec_outlet }}"
                        data-position="{{ $list->jrec_position }}"
                        data-jobgrade="{{ $list->jrec_jobgrade }}"
                        data-jobstep="{{ $list->jrec_step }}"
                        data-reportto="{{ $list->jrec_reportto }}"
                        data-status="{{ $list->jrec_status }}"
                        class="btn btn-outline-secondary btn-sm m-1"
                        onclick="update_jobrec(this)">Edit</button>
                        <button type="button" 
                        data-id="{{ $list->jrec_id }}"
                        data-empno="{{ $empno }}"
                        class="btn btn-outline-danger btn-sm m-1">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="update_jobrec(this)">Add</button>
</div>

<form id="form-jobrec" name="form-jobrec" action="{{ config('app.url') }}/save/work/jobrec" method="POST" class="mb-3 d-none" style="width: fit-content;">
    @csrf
    <input type="hidden" name="employee-number" id="jobrec-employee-number" value="{{ $empno }}">
    <input type="hidden" name="jobrec-id" id="jobrec-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-status" id="jobrec-status" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <label for="jobrec-status">Status</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="jobrec-date-effect" id="jobrec-date-effect">
                <label for="jobrec-date-effect">Effective Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-company" id="jobrec-company" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($companyList as $list)
                        <option value="{{ $list->C_Code }}">{{ $list->C_Name }}</option>
                    @endforeach
                </select>
                <label for="jobrec-company">Company</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-department" id="jobrec-department" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($departmentList as $list)
                        <option value="{{ $list->Dept_Code }}">{{ $list->Dept_Name }}</option>
                    @endforeach
                </select>
                <label for="jobrec-department">Department</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-section" id="jobrec-section" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($sectionList as $list)
                        <option value="{{ $list->sec_code }}">{{ $list->sec_name }}</option>
                    @endforeach
                </select>
                <label for="jobrec-section">Section</label>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-position" id="jobrec-position" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($positionList as $list)
                        <option value="{{ $list->jd_code }}">{{ $list->jd_title }}</option>
                    @endforeach
                </select>
                <label for="jobrec-position">Position</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-job-step" id="jobrec-job-step" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($jobStepList as $list)
                        <option value="{{ $list }}">{{ $list }}</option>
                    @endforeach
                </select>
                <label for="jobrec-job-step">Job Step</label>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-job-grade" id="jobrec-job-grade" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($jobGradeList as $list)
                        <option value="{{ $list->jg_code }}">{{ $list->jg_grade }}</option>
                    @endforeach
                </select>
                <label for="jobrec-job-grade">Job Grade</label>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-area" id="jobrec-area" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($areaList as $list)
                        <option value="{{ $list->Area_Code }}">{{ $list->Area_Name }}</option>
                    @endforeach
                </select>
                <label for="jobrec-area">Area</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-outlet" id="jobrec-outlet" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($outletList as $list)
                        <option value="{{ $list->OL_Code }}">{{ $list->OL_Name }}</option>
                    @endforeach
                </select>
                <label for="jobrec-outlet">Outlet</label>
            </div>
        </div>

        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="jobrec-reportto" id="jobrec-reportto" aria-label="">
                    {{-- <option value="">-Select-</option> --}}
                    @foreach($employeeList as $emp)
                        <option value="{{ $emp->pers_empno }}">{{ ucwords(trim($emp->pers_lastname.', '.$emp->pers_firstname.' '.($emp->pers_ext ?? ''))) }}</option>
                    @endforeach
                </select>
                <label for="jobrec-reportto">Reports To</label>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12 text-end">
            <button type="button" id="btn-cancel-jobrec" class="btn btn-danger btn-sm mx-1">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
    </div>
</form>