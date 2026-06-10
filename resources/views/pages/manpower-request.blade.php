@extends('layouts.layout')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

    <style>
        #mprTabContent,
        #modal-mpr,
        #modal-view-mpr,
        .mpr-fill-td input {
            font-size: 12px;
        }

        .bootstrap-select{
            max-width: 100% !important;
        }

        #form-mpr-jobspec [type="checkbox"],
        #form-mpr-jobspec [type="radio"] {
            border: 1px solid var(--bs-dark);
        }
    </style>

    <div class="row justify-content-center" id="mpr-content">
        <div class="col-md-7">
            <div class="d-flex justify-content-end">
                <button class="ms-auto btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-mpr">New Request</button>
                <button class="ms-1 btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-mpr-jobspec">Job Specification</button>
            </div>
            <ul class="nav nav-underline" id="mprTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-tab-pane" type="button" role="tab" aria-controls="draft-tab-pane" aria-selected="false">Draft</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab" aria-controls="pending-tab-pane" aria-selected="true">Pending</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-tab-pane" type="button" role="tab" aria-controls="approved-tab-pane" aria-selected="false">Approved</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="update-tab" data-bs-toggle="tab" data-bs-target="#update-tab-pane" type="button" role="tab" aria-controls="update-tab-pane" aria-selected="false">Update</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled-tab-pane" type="button" role="tab" aria-controls="cancelled-tab-pane" aria-selected="false">Cancelled</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="declined-tab" data-bs-toggle="tab" data-bs-target="#declined-tab-pane" type="button" role="tab" aria-controls="declined-tab-pane" aria-selected="false">Declined</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="jobspec-tab" data-bs-toggle="tab" data-bs-target="#jobspec-tab-pane" type="button" role="tab" aria-controls="jobspec-tab-pane" aria-selected="false">Job Specification</button>
                </li>
            </ul>
            <div class="tab-content" id="mprTabContent">
                <div class="pt-3 tab-pane fade" id="draft-tab-pane" role="tabpanel" aria-labelledby="draft-tab" tabindex="0"></div>
                <div class="pt-3 tab-pane fade show active" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0"></div>
                <div class="pt-3 tab-pane fade" id="approved-tab-pane" role="tabpanel" aria-labelledby="approved-tab" tabindex="0"></div>
                <div class="pt-3 tab-pane fade" id="update-tab-pane" role="tabpanel" aria-labelledby="update-tab" tabindex="0"></div>
                <div class="pt-3 tab-pane fade" id="cancelled-tab-pane" role="tabpanel" aria-labelledby="cancelled-tab" tabindex="0"></div>
                <div class="pt-3 tab-pane fade" id="declined-tab-pane" role="tabpanel" aria-labelledby="declined-tab" tabindex="0"></div>
                <div class="pt-3 tab-pane fade" id="jobspec-tab-pane" role="tabpanel" aria-labelledby="jobspec-tab" tabindex="0"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-mpr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-mpr-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-mpr-label">Manpower Request</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-mpr">
                    <div class="modal-body">
                        <div class="row" id="mpr-err"></div>
                        <input type="hidden" id="mpr-id" value="">
                        <div class="row mb-3">
                            <label class="col-form-label col-12 fs-6">REPLACEMENT</label>
                            <div class="col-12">
                                <table class="table table-sm table-bordered w-100" id="mpr-replacement-table">
                                    <thead>
                                        <tr>
                                            <th>Subject/Position</th>
                                            <th>Number Needed</th>
                                            <th>Reason</th>
                                            <th>Date Needed</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="border-0 form-select form-select-sm mpr-replacement-position" required>
                                                    <option value disabled selected>-</option>
                                                    @foreach ($jobspec->where('jspec_department', $userJobInfo?->jrec_department) as $j)
                                                        <option value="{{ $j->jspec_position }}">{{ $j->jd_title }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="max-width: 100px;">
                                                <input type="number" class="border-0  mpr-replacement-number form-control form-control-sm" min="1">
                                                <input type="hidden" class="mpr-replacement-fill">
                                            </td>
                                            <td>
                                                <select class="border-0  mpr-replacement-reason form-select form-select-sm">
                                                    <option value="Resignation">Resignation</option>
                                                    <option value="Terminated w/ cause">Terminated w/ cause</option>
                                                    <option value="End of contract">End of contract</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="date" class="border-0  mpr-replacement-dateneed form-control form-control-sm">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-del"><i class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="border-0">
                                        <tr class="border-0">
                                            <th colspan="5" class="border-0">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-add-row"><i class="fa fa-plus"></i></button>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-form-label col-12 fs-6">ADDITIONAL</label>
                            <div class="col-12">
                                <table class="table table-sm table-bordered w-100" id="mpr-additional-table">
                                    <thead>
                                        <tr>
                                            <th>Subject/Position</th>
                                            <th>Number Needed</th>
                                            <th>Reason</th>
                                            <th>Date Needed</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="border-0  form-select form-select-sm mpr-additional-position" required>
                                                    <option value disabled selected>-</option>
                                                    @foreach ($jobspec->where('jspec_department', $userJobInfo?->jrec_department) as $j)
                                                        <option value="{{ $j->jspec_position }}">{{ $j->jd_title }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="max-width: 100px;">
                                                <input type="number" class="border-0  mpr-additional-number form-control form-control-sm" min="1">
                                                <input type="hidden" class="mpr-additional-fill">
                                            </td>
                                            <td>
                                                <input type="text" class="border-0  mpr-additional-reason form-control form-control-sm">
                                            </td>
                                            <td>
                                                <input type="date" class="border-0  mpr-additional-dateneed form-control form-control-sm">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-del"><i class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="border-0">
                                        <tr class="border-0">
                                            <th colspan="5" class="border-0">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-add-row"><i class="fa fa-plus"></i></button>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-form-label col-12 fs-6">NON-NEGOTIABLE</label>
                            <div class="col-12">
                                <textarea id="mpr-nonnegotiable" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-form-label col-md-3">Requested by:</label>
                            <div class="col-md-9">
                                <labe class="col-form-label"></labe>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn-post-mpr">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-view-mpr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-view-mpr-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-view-mpr-label">Manpower Request</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-view-mpr">
                    <div class="modal-body">
                        <div class="row" id="view-mpr-err"></div>
                        <input type="hidden" id="view-mpr-id" value="">
                        <div class="row mb-3">
                            <label class="col-form-label col-12 fs-6">REPLACEMENT</label>
                            <div class="col-12">
                                <table class="table table-sm table-bordered w-100" id="view-mpr-replacement-table">
                                    <thead>
                                        <tr>
                                            <th width="30%">Subject/Position</th>
                                            <th width="70px">Number Needed</th>
                                            <th>Reason</th>
                                            <th width="100px">Date Needed</th>
                                            <th width="100px">Fill</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-form-label col-12 fs-6">ADDITIONAL</label>
                            <div class="col-12">
                                <table class="table table-sm table-bordered w-100" id="view-mpr-additional-table">
                                    <thead>
                                        <tr>
                                            <th width="30%">Subject/Position</th>
                                            <th width="70px">Number Needed</th>
                                            <th>Reason</th>
                                            <th width="100px">Date Needed</th>
                                            <th width="100px">Fill</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-form-label col-12 fs-6">NON-NEGOTIABLE</label>
                            <div class="col-12">
                                <p id="view-mpr-nonnegotiable" class="col-form-label"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-form-label col-md-2">Requested by:</label>
                            <label id="view-mpr-requestby" class="col-form-label col-md"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-view-mpr">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-decline-mpr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-decline-mpr-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-decline-mpr-label">Manpower Request</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-decline-mpr">
                    <div class="modal-body">
                        <div class="row" id="decline-mpr-err"></div>
                        <input type="hidden" id="decline-mpr-id" value="">
                        <div class="row mb-3">
                            <label class="col-form-label col-12">Reason</label>
                            <div class="col-12">
                                <textarea id="decline-mpr-reason" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-mpr-update" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-mpr-update-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-mpr-update-label">Manpower Request Update/Cancel</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-mpr-update">
                    <div class="modal-body">
                        <div class="row" id="mpr-update-err"></div>
                        <input type="hidden" id="mpr-update-id" value="">
                        <input type="hidden" id="mpr-update-action" value="">
                        <div class="row mb-1">
                            <label class="col-form-label col-form-label-sm col-auto">Action:</label>
                            <label class="col-form-label col-form-label-sm col-auto" id="mpr-update-action-label"></label>
                        </div>
                        <div class="row mb-1">
                            <label class="col-form-label col-form-label-sm col-12">Reason:</label>
                            <div class="col-12">
                                <textarea id="mpr-update-reason" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-mpr-jobspec" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    aria-labelledby="modal-mpr-jobspec-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-mpr-jobspec-label">Manpower Request Update</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-mpr-jobspec">
                    <div class="modal-body">
                        <div class="row" id="mpr-jobspec-err"></div>
                        <input type="hidden" id="mpr-jobspec-id" value="">
                        <fieldset>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-3">Department</label>
                                        <div class="col-9">
                                            <select class="form-control form-control-sm selectpicker" data-width="auto" id="mpr-jobspec-dept" title="Select Department" data-live-search="true" required>
                                                @foreach ($department as $v)
                                                    @if($v->Dept_Stat == 'active' || strpos(check_assign($user_empno, 'PR', true), $v->Dept_Code) !== false || $userJobInfo->jd_code == $v->Dept_Code)
                                                        <option value="{{ $v->Dept_Code }}">{{ $v->Dept_Name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-3">Section</label>
                                        <div class="col-9">
                                            <select class="form-control form-control-sm selectpicker" data-width="auto" id="mpr-jobspec-section" title="Select Section" data-live-search="true" required>
                                                @foreach ($section as $v)
                                                    {{-- @if($v->sec_stat == 'active') --}}
                                                        <option value="{{ $v->sec_code }}">{{ $v->sec_name }}</option>
                                                    {{-- @endif --}}
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-3">Position</label>
                                        <div class="col-9">
                                            <select class="form-control form-control-sm selectpicker" data-width="auto" id="mpr-jobspec-pos" title="Select Position" data-live-search="true" required>
                                                @foreach ($position as $v)
                                                    <option value="{{ $v->jd_code }}">{{ $v->jd_title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-3">Employment Status</label>
                                        <div class="col-9">
                                            <select class="form-select form-select-sm" id="mpr-jobspec-emplstat" required>
                                                @foreach ($emplstat as $v)
                                                    <option value="{{ $v->es_name }}">{{ $v->es_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-3">Gender</label>
                                        <div class="col-auto">
                                            <select class="form-select form-select-sm" id="mpr-jobspec-gender" required>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Either">Either</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-3">Age</label>
                                        <div class="col-9">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Min</span>
                                                <input type="text" class="form-control" aria-label="Min age" min="0" max="0" id="mpr-jobspec-agemin" required>
                                                <span class="input-group-text">-</span>
                                                <input type="text" class="form-control" aria-label="Max age" min="0" max="0" id="mpr-jobspec-agemax" required>
                                                <span class="input-group-text">Max</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED: (Please check box of preferred option)</h6>

                            <div class="row mpr-jobspec-edu">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="High School Graduate" id="mpr-jobspec-edu1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-edu1">High School Graduate</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mpr-jobspec-edu">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Vocational Course Graduate" id="mpr-jobspec-edu2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-edu2">Vocational Course Graduate</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1 mpr-jobspec-edu">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="College Graduate (4 year course)" id="mpr-jobspec-edu3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-edu3">College Graduate (4 year course):</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm edu-detail" placeholder="Course/Degree">
                                </div>
                            </div>

                            <div class="row mb-1 mpr-jobspec-edu">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Five-year course Graduate" id="mpr-jobspec-edu4">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-edu4">Five-year course Graduate:</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm edu-detail">
                                </div>
                            </div>

                            <div class="row mb-1 mpr-jobspec-edu">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Masterate / Doctoral**Specify" id="mpr-jobspec-edu5">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-edu5">Masterate / Doctoral**Specify:</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm edu-detail">
                                </div>
                            </div>

                            <div class="row mb-3 mpr-jobspec-edu">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Licensed" id="mpr-jobspec-edu6">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-edu6">Licensed:</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm edu-detail">
                                </div>
                            </div>

                            <h6 class="mt-4">WORK EXPERIENCE(S) REQUIRED: (Please check box of preferred option)</h6>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-workexp" type="checkbox" value="Not Necessary (none)" id="mpr-jobspec-workexp1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-workexp1">Not Necessary (none)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-workexp" type="checkbox" value="6 months to 1 year" id="mpr-jobspec-workexp2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-workexp2">6 months to 1 year</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-workexp" type="checkbox" value="1 to 2 years" id="mpr-jobspec-workexp3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-workexp3">1 to 2 years</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-workexp" type="checkbox" value="2 years or more" id="mpr-jobspec-workexp4">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-workexp4">2 years or more</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-workexp" type="checkbox" value="5 years or more" id="mpr-jobspec-workexp5">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-workexp5">5 years or more</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED: (Please enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <textarea class="form-control form-control-sm" id="mpr-jobspec-duties" rows="3"></textarea>
                                </div>
                            </div>

                            <h6 class="mt-4">TECHNICAL COMPETENCIES</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <textarea class="form-control form-control-sm" id="mpr-jobspec-technical" rows="3"></textarea>
                                </div>
                            </div>

                            <h6 class="mt-4">Competencies Needed to Perform Responsibilities (Ex. Knows how to prepare financial statement, knows Computer Programming). Please enumerate.</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <textarea class="form-control form-control-sm" id="mpr-jobspec-competenciesneeded" rows="3"></textarea>
                                </div>
                            </div>

                            <h6 class="mt-4">Computer skills: (Please check box of preferred option/s)</h6>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-compskill" type="checkbox" value="Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )" id="mpr-jobspec-compskill1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-compskill1">Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-compskill" type="checkbox" value="Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)" id="mpr-jobspec-compskill2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-compskill2">Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-compskill" type="checkbox" value="Layout Designing Skills (using Publisher, Corel, PageMaker etc.)" id="mpr-jobspec-compskill3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-compskill3">Layout Designing Skills (using Publisher, Corel, PageMaker etc.)</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">Other Skills: (Ex. Driving; 4-wheel, 2-Wheel Vehicles)</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <textarea class="form-control form-control-sm" id="mpr-jobspec-otherskill" rows="3"></textarea>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">META PROGRAM</h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">A.APPROACH TO PROBLEM</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-a" value="Towards" id="mpr-jobspec-metaprog-a-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-a-opt1">Towards</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-a" value="Away from" id="mpr-jobspec-metaprog-a-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-a-opt2">Away from</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-a" value="Both" id="mpr-jobspec-metaprog-a-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-a-opt3">Both</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">B.TIME FRAME</label>
                                        <label class="col-form-label col-form-label-sm col-12">(Terms)</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-b1" value="Long- Term" id="mpr-jobspec-metaprog-b1-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-b1-opt1">Long- Term</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-b1" value="Medium-Term" id="mpr-jobspec-metaprog-b1-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-b1-opt2">Medium-Term</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-b1" value="Short-Term" id="mpr-jobspec-metaprog-b1-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-b1-opt3">Short-Term</label>
                                            </div>
                                        </div>
                                        <label class="col-form-label col-form-label-sm col-12">(Time)</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-b2" value="Past" id="mpr-jobspec-metaprog-b2-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-b2-opt1">Past</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-b2" value="Present" id="mpr-jobspec-metaprog-b2-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-b2-opt2">Present</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-b2" value="Future" id="mpr-jobspec-metaprog-b2-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-b2-opt3">Future</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">C. LOCUS OF CONTROL</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-c" value="Internal" id="mpr-jobspec-metaprog-c-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-c-opt1">Internal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-c" value="External" id="mpr-jobspec-metaprog-c-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-c-opt2">External</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-c" value="Both" id="mpr-jobspec-metaprog-c-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-c-opt3">Both</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">D. MODE OF COMPARISON</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-d" value="Match" id="mpr-jobspec-metaprog-d-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-d-opt1">Match</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-d" value="Mismatch" id="mpr-jobspec-metaprog-d-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-d-opt2">Mismatch</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-d" value="Both" id="mpr-jobspec-metaprog-d-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-d-opt3">Both</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">E. Chunk Size</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-e" value="Generalities" id="mpr-jobspec-metaprog-e-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-e-opt1">Generalities</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-e" value="Details" id="mpr-jobspec-metaprog-e-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-e-opt2">Details</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-e" value="Both" id="mpr-jobspec-metaprog-e-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-e-opt3">Both</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">F.APPROACH TO SOLVING PROBLEMS</label>
                                        <label class="col-form-label col-form-label-sm col-12">(Task)</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-f1" value="Choice" id="mpr-jobspec-metaprog-f1-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-f1-opt1">Choice</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-f1" value="Procedure" id="mpr-jobspec-metaprog-f1-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-f1-opt2">Procedure</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-f1" value="Both" id="mpr-jobspec-metaprog-f1-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-f1-opt3">Both</label>
                                            </div>
                                        </div>
                                        <label class="col-form-label col-form-label-sm col-12">(Relationship)</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-f2" value="Self" id="mpr-jobspec-metaprog-f2-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-f2-opt1">Self</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-f2" value="Others" id="mpr-jobspec-metaprog-f2-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-f2-opt2">Others</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-f2" value="We, Both, Team" id="mpr-jobspec-metaprog-f2-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-f2-opt3">We, Both, Team</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">G. THINKING STYLE</label>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-g" value="Vision" id="mpr-jobspec-metaprog-g-opt1">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-g-opt1">Vision</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-g" value="Action" id="mpr-jobspec-metaprog-g-opt2">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-g-opt2">Action</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mpr-jobspec-metaprog-g" value="Emotion" id="mpr-jobspec-metaprog-g-opt3">
                                                <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-metaprog-g-opt3">Emotion</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">TAPT</h6>

                            <div class="row mb-3 gap-1">
                                <label class="col-form-label col-form-label-sm col-12">Please check four preferred personality type combination:</label>
                                <div class="col-md border rounded">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt1" value="Extrovert" id="mpr-jobspec-tapt1-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt1-opt1">Extrovert</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt1" value="Introvert" id="mpr-jobspec-tapt1-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt1-opt2">Introvert</label>
                                    </div>
                                </div>
                                <div class="col-md border rounded">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt2" value="Sensitive" id="mpr-jobspec-tapt2-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt2-opt1">Sensitive</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt2" value="Intuitive" id="mpr-jobspec-tapt2-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt2-opt2">Intuitive</label>
                                    </div>
                                </div>
                                <div class="col-md border rounded">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt3" value="Thinking" id="mpr-jobspec-tapt3-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt3-opt1">Thinking</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt3" value="Feeling" id="mpr-jobspec-tapt3-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt3-opt2">Feeling</label>
                                    </div>
                                </div>
                                <div class="col-md border rounded">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt4" value="Judging" id="mpr-jobspec-tapt4-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt4-opt1">Judging</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-tapt" type="radio" name="mpr-jobspec-tapt4" value="Perceiving" id="mpr-jobspec-tapt4-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-tapt4-opt2">Perceiving</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">ENNEAGRAM</h6>

                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-12">Please check box of preferred option:</label>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Perfectionist" id="mpr-jobspec-enneagram-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt1">Perfectionist</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Helper" id="mpr-jobspec-enneagram-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt2">Helper</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Achiever" id="mpr-jobspec-enneagram-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt3">Achiever</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Romantic" id="mpr-jobspec-enneagram-opt4">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt4">Romantic</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Observer" id="mpr-jobspec-enneagram-opt5">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt5">Observer</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Questioner" id="mpr-jobspec-enneagram-opt6">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt6">Questioner</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Adventurer" id="mpr-jobspec-enneagram-opt7">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt7">Adventurer</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Asserter" id="mpr-jobspec-enneagram-opt8">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt8">Asserter</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-enneagram" type="checkbox" value="Peacemaker" id="mpr-jobspec-enneagram-opt9">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-enneagram-opt9">Peacemaker</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">LEARNING STYLE</h6>

                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-12">Please check box of preferred option:</label>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-learnstyle" type="checkbox" value="Visual" id="mpr-jobspec-learnstyle-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-learnstyle-opt1">Visual</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-learnstyle" type="checkbox" value="Auditory" id="mpr-jobspec-learnstyle-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-learnstyle-opt2">Auditory</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-learnstyle" type="checkbox" value="Kinesthetic" id="mpr-jobspec-learnstyle-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-learnstyle-opt3">Kinesthetic</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">CAREER ANCHOR</h6>

                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-12">Please check top 3 preferred choices:</label>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Technical/Functional Competence" id="mpr-jobspec-career-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt1">Technical/Functional Competence</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Autonomy/Independence" id="mpr-jobspec-career-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt2">Autonomy/Independence</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Entrepreneurial Creativity" id="mpr-jobspec-career-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt3">Entrepreneurial Creativity</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Pure Challenge" id="mpr-jobspec-career-opt4">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt4">Pure Challenge</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="General/Managerial Competence" id="mpr-jobspec-career-opt5">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt5">General/Managerial Competence</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Security/ Stability" id="mpr-jobspec-career-opt6">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt6">Security/ Stability</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Sense of Service/Dedication to A Cause" id="mpr-jobspec-career-opt7">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt7">Sense of Service/Dedication to A Cause</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-career" type="checkbox" value="Lifestyle" id="mpr-jobspec-career-opt8">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-career-opt8">Lifestyle</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">MOTIVATION TO WORK</h6>

                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-12">Please check top 3 preferred choices:</label>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Achievement" id="mpr-jobspec-motivation-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt1">Achievement</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Personal Growth" id="mpr-jobspec-motivation-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt2">Personal Growth</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Prestige" id="mpr-jobspec-motivation-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt3">Prestige</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Family" id="mpr-jobspec-motivation-opt4">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt4">Family</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Pleasure" id="mpr-jobspec-motivation-opt5">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt5">Pleasure</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Recognition" id="mpr-jobspec-motivation-opt6">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt6">Recognition</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Independence" id="mpr-jobspec-motivation-opt7">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt7">Independence</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Power" id="mpr-jobspec-motivation-opt8">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt8">Power</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Security" id="mpr-jobspec-motivation-opt9">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt9">Security</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Money" id="mpr-jobspec-motivation-opt10">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt10">Money</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Pressure" id="mpr-jobspec-motivation-opt11">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt11">Pressure</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-motivation" type="checkbox" value="Self-Esteem" id="mpr-jobspec-motivation-opt12">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-motivation-opt12">Self-Esteem</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">PERSONALITY TYPE</h6>

                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-12">Please check box preferred choices:</label>
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-personality" type="checkbox" value="Controller" id="mpr-jobspec-personality-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-personality-opt1">Controller</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-personality" type="checkbox" value="Analyst" id="mpr-jobspec-personality-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-personality-opt2">Analyst</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-personality" type="checkbox" value="Promoter" id="mpr-jobspec-personality-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-personality-opt3">Promoter</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-personality" type="checkbox" value="Supporter" id="mpr-jobspec-personality-opt4">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-personality-opt4">Supporter</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">RAVEN</h6>

                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-12">Please check box of preferred option:</label>
                                <div class="col-md-4">
                                    <label class="col-form-label col-form-label-sm">LOW</label>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-low" type="checkbox" value="Low" id="mpr-jobspec-raven-low-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-low-opt1">Low</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-low" type="checkbox" value="Average" id="mpr-jobspec-raven-low-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-low-opt2">Average</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-low" type="checkbox" value="High" id="mpr-jobspec-raven-low-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-low-opt3">High</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label col-form-label-sm">AVERAGE</label>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-average" type="checkbox" value="Low" id="mpr-jobspec-raven-average-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-average-opt1">Low</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-average" type="checkbox" value="Average" id="mpr-jobspec-raven-average-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-average-opt2">Average</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-average" type="checkbox" value="High" id="mpr-jobspec-raven-average-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-average-opt3">High</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label col-form-label-sm">HIGH</label>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-high" type="checkbox" value="Low" id="mpr-jobspec-raven-high-opt1">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-high-opt1">Low</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-high" type="checkbox" value="Average" id="mpr-jobspec-raven-high-opt2">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-high-opt2">Average</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input mpr-jobspec-raven-high" type="checkbox" value="High" id="mpr-jobspec-raven-high-opt3">
                                        <label class="form-check-label col-form-label-sm py-0" for="mpr-jobspec-raven-high-opt3">High</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1 d-none">LEADERSHIP STYLE (To be filled up by HR)</h6>

                            <div class="row mb-3 d-none">
                                <div class="col">
                                    <textarea class="form-control form-control-sm" id="mpr-jobspec-leadership" rows="3"></textarea>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">REMARKS:</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <textarea class="form-control form-control-sm" id="mpr-jobspec-remarks" rows="3"></textarea>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-mpr-view-jobspec" data-bs-keyboard="true" tabindex="-1"
    aria-labelledby="modal-mpr-view-jobspec-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-mpr-view-jobspec-label">Manpower Request Update</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row" id="mpr-view-jobspec-err"></div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm col-3">Department</label>
                                    <label class="col-form-label col-form-label-sm col-9" id="mpr-view-jobspec-dept">(Department)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm col-3">Section</label>
                                    <label class="col-form-label col-form-label-sm col-9" id="mpr-view-jobspec-section">(Section)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm col-3">Position</label>
                                    <label class="col-form-label col-form-label-sm col-9" id="mpr-view-jobspec-pos">(Position)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm col-3">Employment Status</label>
                                    <label class="col-form-label col-form-label-sm col-9" id="mpr-view-jobspec-emplstat">(Employment Status)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm col-3">Gender</label>
                                    <label class="col-form-label col-form-label-sm col-9" id="mpr-view-jobspec-gender">(Gender)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm col-3">Age</label>
                                    <label class="col-form-label col-form-label-sm col-9" id="mpr-view-jobspec-age">(Min-Max)</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4">EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED: (Please check box of preferred option)</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-edu"></div>

                        <h6 class="mt-4">WORK EXPERIENCE(S) REQUIRED: (Please check box of preferred option)</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-workexp"></div>

                        <h6 class="mt-4">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED: (Please enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</h6>

                        <div class="row mb-3">
                            <div class="col">
                                <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-duties" style="white-space: pre-line;"></p>
                            </div>
                        </div>

                        <h6 class="mt-4">TECHNICAL COMPETENCIES</h6>

                        <div class="row mb-3">
                            <div class="col">
                                <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-technical" style="white-space: pre-line;"></p>
                            </div>
                        </div>

                        <h6 class="mt-4">Competencies Needed to Perform Responsibilities (Ex. Knows how to prepare financial statement, knows Computer Programming). Please enumerate.</h6>

                        <div class="row mb-3">
                            <div class="col">
                                <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-competenciesneeded" style="white-space: pre-line;"></p>
                            </div>
                        </div>

                        <h6 class="mt-4">Computer skills: (Please check box of preferred option/s)</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-compskill"></div>

                        <h6 class="mt-4">Other Skills: (Ex. Driving; 4-wheel, 2-Wheel Vehicles)</h6>

                        <div class="row mb-3">
                            <div class="col">
                                <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-otherskill" style="white-space: pre-line;"></p>
                            </div>
                        </div>

                        <h6 class="mt-3 pt-1">META PROGRAM</h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">A.APPROACH TO PROBLEM</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-a">(Answer)</label>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">B.TIME FRAME</label>
                                    <label class="col-form-label col-form-label-sm col-12">(Terms)</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-b1">(Answer)</label>
                                    <label class="col-form-label col-form-label-sm col-12">(Time)</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-b2">(Answer)</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">C. LOCUS OF CONTROL</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-c">(Answer)</label>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">D. MODE OF COMPARISON</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-d">(Answer)</label>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">E. Chunk Size</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-e">(Answer)</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">F.APPROACH TO SOLVING PROBLEMS</label>
                                    <label class="col-form-label col-form-label-sm col-12">(Task)</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-f1">(Answer)</label>
                                    <label class="col-form-label col-form-label-sm col-12">(Relationship)</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-f2">(Answer)</label>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">G. THINKING STYLE</label>
                                    <label class="col-form-label col-form-label-sm col-12" id="mpr-view-jobspec-metaprog-g">(Answer)</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3 pt-1">TAPT</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-tapt"></div>

                        <h6 class="mt-4">ENNEAGRAM</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-enneagram"></div>

                        <h6 class="mt-3 pt-1">LEARNING STYLE</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-learnstyle"></div>

                        <h6 class="mt-4">CAREER ANCHOR</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-career"></div>

                        <h6 class="mt-4">MOTIVATION TO WORK</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-motivation"></div>

                        <h6 class="mt-4">PERSONALITY TYPE</h6>

                        <div class="row mb-3" id="mpr-view-jobspec-personality"></div>

                        <h6 class="mt-3 pt-1">RAVEN</h6>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm">LOW</label>
                                    <div class="col" id="mpr-view-jobspec-raven-low"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm">AVERAGE</label>
                                    <div class="col" id="mpr-view-jobspec-raven-average"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <label class="col-form-label col-form-label-sm">HIGH</label>
                                    <div class="col" id="mpr-view-jobspec-raven-high"></div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3 pt-1 d-none">LEADERSHIP STYLE (To be filled up by HR)</h6>

                        <div class="row mb-3 d-none">
                            <div class="col">
                                <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-leadership" style="white-space: pre-line;"></p>
                            </div>
                        </div>

                        <h6 class="mt-3 pt-1">REMARKS:</h6>

                        <div class="row mb-3">
                            <div class="col">
                                <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-remarks" style="white-space: pre-line;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let curtab = 'pending';
        $(function(){
            const tr_replacement = $('#mpr-replacement-table').find('tbody tr').first();
            const tr_additional = $('#mpr-additional-table').find('tbody tr').first();

            $('#mprTab .nav-item .nav-link').click(function(){
                let stat = $(this).attr('id').replace('-tab', '');
                if((curtab == stat && !$('#' + stat + '-tab-pane').is(':empty')) || (curtab != stat && $('#' + stat + '-tab-pane').is(':empty'))){
                    loadMPR(stat);
                }
                curtab = stat;
            });

            $('#mpr-replacement-table .btn-add-row').click(function(){
                $(this).closest('table').find('tbody').append(tr_replacement);
            });

            $('#mpr-additional-table .btn-add-row').click(function(){
                $(this).closest('table').find('tbody').append(tr_additional);
            });

            $('#form-mpr').on('click', '.btn-del', function(){
                $(this).closest('tr').remove();
            });

            $('#modal-mpr').on('show.bs.modal', function(e){
                let btn = $(e.relatedTarget);
                // tr_replacement.find('input, select').val('');
                // tr_additional.find('input, select').val('');
                $('#mpr-replacement-table').find('tbody').empty();
                $('#mpr-additional-table').find('tbody').empty();

                let replacement = (btn.data('replacement') || '').match(/\[([^\]]+)\]/g);
                replacement = (replacement || []).map(group =>
                    group.replace(/[\[\]]/g, '').split('|')
                );

                let additional = (btn.data('additional') || '').match(/\[([^\]]+)\]/g);
                additional = (additional || []).map(group =>
                    group.replace(/[\[\]]/g, '').split('|')
                );
                
                $('#mpr-id').val(btn.data('id') || '');
                $('#mpr-nonnegotiable').val(btn.data('nonnegotiable') || '');
                
                replacement.forEach(i => {
                    const tr = tr_replacement.clone();
                    tr.find('.mpr-replacement-position').val(i[0]);
                    tr.find('.mpr-replacement-number').val(i[1]);
                    tr.find('.mpr-replacement-reason').val(i[2]);
                    tr.find('.mpr-replacement-dateneed').val(i[3]);
                    $('#mpr-replacement-table').find('tbody').append(tr);
                });

                additional.forEach(i => {
                    const tr = tr_additional.clone();
                    tr.find('.mpr-additional-position').val(i[0]);
                    tr.find('.mpr-additional-number').val(i[1]);
                    tr.find('.mpr-additional-reason').val(i[2]);
                    tr.find('.mpr-additional-dateneed').val(i[3]);
                    $('#mpr-additional-table').find('tbody').append(tr);
                });
            });

            $('#form-mpr').submit(async function(e){
                e.preventDefault();

                $('#mpr-err').html("");

                let replacement = [];
                $(this).find('.mpr-replacement-position').each(function(){
                    if(this.value){
                        replacement.push({
                            position: this.value,
                            count: $(this).closest('tr').find('.mpr-replacement-number').val(),
                            reason: $(this).closest('tr').find('.mpr-replacement-reason').val(),
                            date: $(this).closest('tr').find('.mpr-replacement-dateneed').val()
                        });
                    }
                });

                let additional = [];
                $(this).find('.mpr-additional-position').each(function(){
                    if(this.value){
                        additional.push({
                            position: this.value,
                            count: $(this).closest('tr').find('.mpr-additional-number').val(),
                            reason: $(this).closest('tr').find('.mpr-additional-reason').val(),
                            date: $(this).closest('tr').find('.mpr-additional-dateneed').val()
                        });
                    }
                });
            
                let formData = new FormData();
                formData.append('id', $('#mpr-id').val());
                formData.append('replacement', JSON.stringify(replacement));
                formData.append('additional', JSON.stringify(additional));
                formData.append('nonnegotiable', $('#mpr-nonnegotiable').val());

                let response = await fetch('/manpower/save', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.modal').modal('hide');
                    alert('Saved');
                    $('#mprTab button.active').click();
                } else {
                    $('#mpr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
                }
            });

            $('#form-decline-mpr').submit(async function(e){
                e.preventDefault();

                $('#decline-mpr-err').html("");

                let formData = new FormData();
                formData.append('id', $('#decline-mpr-id').val());
                formData.append('stat', 'declined');
                formData.append('reason', $('#decline-mpr-reason').val());

                let response = await fetch('/manpower/stat', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.modal').modal('hide');
                    alert('Declined');
                    $('#mprTab button.active').click();
                } else {
                    $('#decline-mpr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
                }
            });


            $('#modal-view-mpr').on('show.bs.modal', function(e){
                if ($(e.relatedTarget).is('button')) return;

                let main_tr = $(e.relatedTarget);
                $('#view-mpr-replacement-table').find('tbody').empty();
                $('#view-mpr-additional-table').find('tbody').empty();

                let replacement = (main_tr.data('replacement') || []);
                let additional = (main_tr.data('additional') || []);
                
                $('#view-mpr-id').val(main_tr.data('id') || '');
                $('#view-mpr-nonnegotiable').html((main_tr.data('nonnegotiable') || '').replace(/\r?\n/g, '<br>'));
                
                replacement.forEach(i => {
                    const tr = $('<tr/>');
                    tr.addClass('view-mpr-replacement-item');
                    tr.attr('position', i[1]);
                    tr.attr('number', i[2]);
                    tr.attr('reason', i[3]);
                    tr.attr('dateneed', i[4]);
                    tr.append('<td onclick="view_jobspec(\'' + i[1] + '\')" style="cursor: pointer;">'+i[0]+'</td>');
                    tr.append('<td>'+i[2]+'</td>');
                    tr.append('<td>'+i[3]+'</td>');
                    tr.append('<td>'+i[4]+'</td>');
                    tr.append('<td class="mpr-fill-td"><input type="number" min="0" max="'+i[2]+'" class="form-cotrol form-control-sm view-mpr-replacement-fill" value="'+i[5]+'"></td>');
                    $('#view-mpr-replacement-table').find('tbody').append(tr);
                });

                additional.forEach(i => {
                    const tr = $('<tr/>');
                    tr.addClass('view-mpr-additional-item');
                    tr.attr('position', i[1]);
                    tr.attr('number', i[2]);
                    tr.attr('reason', i[3]);
                    tr.attr('dateneed', i[4]);
                    tr.append('<td onclick="view_jobspec(\'' + i[1] + '\')" style="cursor: pointer;">'+i[0]+'</td>');
                    tr.append('<td>'+i[2]+'</td>');
                    tr.append('<td>'+i[3]+'</td>');
                    tr.append('<td>'+i[4]+'</td>');
                    tr.append('<td class="mpr-fill-td"><input type="number" min="0" max="'+i[2]+'" class="form-cotrol form-control-sm view-mpr-additional-fill" value="'+i[5]+'"></td>');
                    $('#view-mpr-additional-table').find('tbody').append(tr);
                });

                $('#view-mpr-requestby').text(main_tr.find('td').eq(1).text());
            });

            $('#form-view-mpr').submit(async function(e){
                e.preventDefault();

                $('#view-mpr-err').html("");

                let replacement = [];
                $(this).find('.view-mpr-replacement-item').each(function(){
                    if($(this).attr('position')){
                        replacement.push({
                            position: $(this).attr('position'),
                            count: $(this).attr('number'),
                            reason: $(this).attr('reason'),
                            date: $(this).attr('dateneed'),
                            fill: $(this).find('.view-mpr-replacement-fill').val()
                        });
                    }
                });

                let additional = [];
                $(this).find('.view-mpr-additional-item').each(function(){
                    if($(this).attr('position')){
                        additional.push({
                            position: $(this).attr('position'),
                            count: $(this).attr('number'),
                            reason: $(this).attr('reason'),
                            date: $(this).attr('dateneed'),
                            fill: $(this).find('.view-mpr-additional-fill').val()
                        });
                    }
                });
            
                let formData = new FormData();
                formData.append('id', $('#view-mpr-id').val());
                formData.append('replacement', JSON.stringify(replacement));
                formData.append('additional', JSON.stringify(additional));

                let response = await fetch('/manpower/fill', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.modal').modal('hide');
                    alert('Saved');
                    $('#mprTab button.active').click();
                } else {
                    $('#view-mpr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
                }
            });


            $('#modal-mpr-update').on('show.bs.modal', function(e){
                let btn = $(e.relatedTarget);
                $('#mpr-update-id').val(btn.data('id') || '');
                $('#mpr-update-action').val(btn.data('action') || '');
                $('#mpr-update-action-label').text((btn.data('action') || '').toUpperCase());
            });

            $('#form-mpr-update').submit(async function(e){
                e.preventDefault();
                $('#mpr-update-err').html("");
            
                let formData = new FormData();
                formData.append('id', $('#mpr-update-id').val());
                formData.append('action', $('#mpr-update-action').val());
                formData.append('reason', $('#mpr-update-reason').val());

                let response = await fetch('/manpower/update', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.modal').modal('hide');
                    alert('Saved');
                    $('#mprTab button.active').click();
                } else {
                    $('#mpr-update-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
                }
            });


            $('#form-mpr-jobspec').submit(async function(e){
                e.preventDefault();
                $('#mpr-jobspec-err').html("");

                let edu = [];
                $('.mpr-jobspec-edu [type="checkbox"]:checked').each(function() {
                    let item = $(this).val();
                    const detail = $(this).closest('.mpr-jobspec-edu').find('.edu-detail').val();
                    if(detail){
                        item += '%&' + detail;
                    }
                    edu.push(item);
                });

                edu = edu.join('%#');

                let formData = new FormData();
                formData.append('id', $('#mpr-jobspec-id').val());
                formData.append('department', $('#mpr-jobspec-dept').val());
                formData.append('section', $('#mpr-jobspec-section').val());
                formData.append('position', $('#mpr-jobspec-pos').val());
                formData.append('emplstat', $('#mpr-jobspec-emplstat').val());
                formData.append('sex', $('#mpr-jobspec-gender').val());
                formData.append('agerange', $('#mpr-jobspec-agemin').val() + '-' + $('#mpr-jobspec-agemax').val());
                formData.append('education', edu);
                formData.append('workexp', $('.mpr-jobspec-workexp:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('duties', $('#mpr-jobspec-duties').val());
                formData.append('techcompetencies', $('#mpr-jobspec-technical').val());
                formData.append('competencies', $('#mpr-jobspec-competencies').val());
                formData.append('computerskill', $('.mpr-jobspec-compskill:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('otherskill', $('#mpr-jobspec-otherskill').val());
                formData.append('mpa', $('[name="mpr-jobspec-metaprog-a"]:checked').val());
                formData.append('mpb', $('[name="mpr-jobspec-metaprog-b1"]:checked').val()+($('[name="mpr-jobspec-metaprog-b2"]:checked').val() ? '|' + $('[name="mpr-jobspec-metaprog-b2"]:checked').val() : ''));
                formData.append('mpc', $('[name="mpr-jobspec-metaprog-c"]:checked').val());
                formData.append('mpd', $('[name="mpr-jobspec-metaprog-d"]:checked').val());
                formData.append('mpe', $('[name="mpr-jobspec-metaprog-e"]:checked').val());
                formData.append('mpf', $('[name="mpr-jobspec-metaprog-f1"]:checked').val()+($('[name="mpr-jobspec-metaprog-f2"]:checked').val() ? '|' + $('[name="mpr-jobspec-metaprog-f2"]:checked').val() : ''));
                formData.append('mpg', $('[name="mpr-jobspec-metaprog-g"]:checked').val());
                formData.append('tapt', $('.mpr-jobspec-tapt:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('enneagram', $('.mpr-jobspec-enneagram:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('learnstyle', $('.mpr-jobspec-learnstyle:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('career', $('.mpr-jobspec-career:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('motivation', $('.mpr-jobspec-motivation:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('personality', $('.mpr-jobspec-personality:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('ravenl', $('.mpr-jobspec-raven-low:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('ravena', $('.mpr-jobspec-raven-average:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('ravenh', $('.mpr-jobspec-raven-high:checked').map((_, el) => el.value).get().join('%#'));
                formData.append('leadership', $('#mpr-jobspec-leadership').val());
                formData.append('remarks', $('#mpr-jobspec-remarks').val());

                let response = await fetch('/manpower/jobspec/save', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.modal').modal('hide');
                    alert('Saved');
                    $('#jobspec-tab').click();
                } else {
                    $('#mpr-jobspec-err').html(`<p style="color: red;">Unable to proceed: ${result.error}</p>`);
                    $('#modal-mpr-jobspec').animate({
                        scrollTop: $('#mpr-jobspec-err').offset().top
                    }, 500);
                }
            });

            $('#modal-mpr-jobspec').on('show.bs.modal', async function(e){
                try {

                    const pos = $(e.relatedTarget).data('pos');

                    $('#form-mpr-jobspec textarea').height('auto');
                    $('#form-mpr-jobspec textarea').autoResize();

                    $('#form-mpr-jobspec').find('textarea, select, input:not([type="checkbox"], [type="radio"])').val('');
                    $('#form-mpr-jobspec').find('[type="checkbox"], [type="radio"]').prop('checked', false);

                    $('.selectpicker').selectpicker('refresh');

                    $('#form-mpr-jobspec fieldset').prop('disabled', true);

                    if(!pos){
                        $('#form-mpr-jobspec fieldset').prop('disabled', false);
                        return;
                    }

                    const data = await get_spec(pos);

                    if(!data['id']){
                        $('#form-mpr-jobspec fieldset').prop('disabled', false);
                        return;
                    }

                    $('#mpr-jobspec-id').val(data['id']);
                    $('#mpr-jobspec-dept').val(data['department']);
                    $('#mpr-jobspec-section').val(data['section']);
                    $('#mpr-jobspec-pos').val(data['position']);
                    $('#mpr-jobspec-emplstat').val(data['emplstat']);
                    $('#mpr-jobspec-gender').val(data['sex']);
                    $('#mpr-jobspec-agemin').val(data['agerange'][0] || '');
                    $('#mpr-jobspec-agemax').val(data['agerange'][1] || '');
                    
                    for(i of (data['education']) || []) {
                        let chk = $('.mpr-jobspec-edu input[value="' + (i[0] || '') + '"]');
                        chk.prop('checked', true);
                        chk.closest('.mpr-jobspec-edu').find('.edu-detail').val((i[1] || ''));
                    }

                    for(i of (data['workexp']) || []) {
                        $('input.mpr-jobspec-workexp[value="' + i + '"]').prop('checked', true);
                    }
                    
                    $('#mpr-jobspec-duties').val(data['duties']);
                    $('#mpr-jobspec-technical').val(data['techcompetencies']);
                    $('#mpr-jobspec-competenciesneeded').val(data['competencies']);

                    for(i of (data['computerskill']) || []) {
                        $('input.mpr-jobspec-compskill[value="' + i + '"]').prop('checked', true);
                    }
                    
                    $('#mpr-jobspec-otherskill').val(data['otherskill']);

                    $('input[name="mpr-jobspec-metaprog-a"][value="' + data['mpa'] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-b1"][value="' + data['mpb'][0] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-b2"][value="' + data['mpb'][1] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-c"][value="' + data['mpc'] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-d"][value="' + data['mpd'] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-e"][value="' + data['mpe'] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-f1"][value="' + data['mpf'][0] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-f2"][value="' + data['mpf'][1] + '"]').prop('checked', true);
                    $('input[name="mpr-jobspec-metaprog-g"][value="' + data['mpg'] + '"]').prop('checked', true);

                    for(i of (data['tapt'] || [])) {
                        $('input.mpr-jobspec-tapt[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['enneagram'] || [])) {
                        $('input.mpr-jobspec-enneagram[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['learnstyle'] || [])) {
                        $('input.mpr-jobspec-learnstyle[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['career'] || [])) {
                        $('input.mpr-jobspec-career[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['motivation'] || [])) {
                        $('input.mpr-jobspec-motivation[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['personality'] || [])) {
                        $('input.mpr-jobspec-personality[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['ravenl'] || [])) {
                        $('input.mpr-jobspec-raven-low[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['ravena'] || [])) {
                        $('input.mpr-jobspec-raven-average[value="' + i + '"]').prop('checked', true);
                    }

                    for(i of (data['ravenh'] || [])) {
                        $('input.mpr-jobspec-raven-high[value="' + i + '"]').prop('checked', true);
                    }

                    $('#mpr-jobspec-leadership').val(data['leadership']);
                    $('#mpr-jobspec-remarks').val(data['remarks']);

                    $('#form-mpr-jobspec fieldset').prop('disabled', false);

                    $('.selectpicker').selectpicker('refresh');

                } catch (error) {
                    console.error('Error fetching the data:', error);
                }
            });

            loadMPR('pending');
        })

        async function view_jobspec(pos) {
            try {

                if(!pos) return;

                const data = await get_spec(pos);

                if(!data['id']) return;

                $('#mpr-view-jobspec-dept').text(data['department_name']);
                $('#mpr-view-jobspec-section').text(data['section_name']);
                $('#mpr-view-jobspec-pos').text(data['position_name']);
                $('#mpr-view-jobspec-emplstat').text(data['emplstat']);
                $('#mpr-view-jobspec-gender').text(data['sex']);
                $('#mpr-view-jobspec-age').text(data['agerange'] ? data['agerange'].join('-') : '');
                
                $('#mpr-view-jobspec-edu').html('');
                for(i of (data['education']) || []) {
                    $('#mpr-view-jobspec-edu').append('<label class="col-form-label col-form-label-sm col-12">- ' + i.join(': ') + '</label>');
                }

                $('#mpr-view-jobspec-workexp').html('');
                for(i of (data['workexp']) || []) {
                    $('#mpr-view-jobspec-workexp').append('<label class="col-form-label col-form-label-sm col-12">- ' + i + '</label>');
                }
                
                $('#mpr-view-jobspec-duties').text(data['duties']);
                $('#mpr-view-jobspec-technical').text(data['techcompetencies']);
                $('#mpr-view-jobspec-competenciesneeded').text(data['competencies']);

                $('#mpr-view-jobspec-compskill').html('');
                for(i of (data['computerskill']) || []) {
                    $('#mpr-view-jobspec-compskill').append('<label class="col-form-label col-form-label-sm col-12">- ' + i + '</label>');
                }
                
                $('#mpr-view-jobspec-otherskill').text(data['otherskill']);

                $('#mpr-view-jobspec-metaprog-a').text('-' + data['mpa']);
                $('#mpr-view-jobspec-metaprog-b1').text('-' + data['mpb'][0]);
                $('#mpr-view-jobspec-metaprog-b2').text('-' + data['mpb'][1]);
                $('#mpr-view-jobspec-metaprog-c').text('-' + data['mpc']);
                $('#mpr-view-jobspec-metaprog-d').text('-' + data['mpd']);
                $('#mpr-view-jobspec-metaprog-e').text('-' + data['mpe']);
                $('#mpr-view-jobspec-metaprog-f1').text('-' + data['mpf'][0]);
                $('#mpr-view-jobspec-metaprog-f2').text('-' + data['mpf'][1]);
                $('#mpr-view-jobspec-metaprog-g').text('-' + data['mpg']);

                $('#mpr-view-jobspec-tapt').html('');
                for(i of (data['tapt'] || [])) {
                    $('#mpr-view-jobspec-tapt').append('<label class="col-form-label col-form-label-sm col-md">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-enneagram').html('');
                for(i of (data['enneagram'] || [])) {
                    $('#mpr-view-jobspec-enneagram').append('<label class="col-form-label col-form-label-sm col-md">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-learnstyle').html('');
                for(i of (data['learnstyle'] || [])) {
                    $('#mpr-view-jobspec-learnstyle').append('<label class="col-form-label col-form-label-sm col-md">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-career').html('');
                for(i of (data['career'] || [])) {
                    $('#mpr-view-jobspec-career').append('<label class="col-form-label col-form-label-sm col-md">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-motivation').html('');
                for(i of (data['motivation'] || [])) {
                    $('#mpr-view-jobspec-motivation').append('<label class="col-form-label col-form-label-sm col-md">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-personality').html('');
                for(i of (data['personality'] || [])) {
                    $('#mpr-view-jobspec-personality').append('<label class="col-form-label col-form-label-sm col-md">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-raven-low').html('');
                for(i of (data['ravenl'] || [])) {
                    $('#mpr-view-jobspec-raven-low').append('<label class="col-form-label col-form-label-sm col-12">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-raven-average').html('');
                for(i of (data['ravena'] || [])) {
                    $('#mpr-view-jobspec-raven-average').append('<label class="col-form-label col-form-label-sm col-12">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-raven-high').html('');
                for(i of (data['ravenh'] || [])) {
                    $('#mpr-view-jobspec-raven-high').append('<label class="col-form-label col-form-label-sm col-12">- ' + i + '</label>');
                }

                $('#mpr-view-jobspec-leadership').text(data['leadership']);
                $('#mpr-view-jobspec-remarks').text(data['remarks']);

                $('#modal-mpr-view-jobspec').modal('show')

            } catch (error) {
                console.error('Error fetching the data:', error);
            }
        }

        async function loadMPR(stat, id = null) {
            $('#'+stat+'-tab-pane').html('Loading...');
            try {
                // Make the fetch request to the Laravel controller
                const response = await fetch('/manpower/list/' + stat);
                
                if (!response.ok) { // Check if the response was successful
                    throw new Error('Network response was not ok');
                }

                // Get the response text (HTML)
                const html = await response.text();

                // Inject the received HTML into the DOM
                $('#'+stat+'-tab-pane').html(html);
                $('#'+stat+'-tab-pane > table').DataTable({
                    scrollY: '55vh',
                    scrollCollapse: true,
                    lengthMenu: [50, 100, { label: 'All', value: -1 }],
                    ordering: false
                });
            } catch (error) {
                console.error('Error fetching the list:', error);
            }
        }

        async function get_spec(pos) {
            try {
                const response = await fetch('/manpower/jobspec/' + pos);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                return data;

            } catch (error) {
                console.error('Error fetching the data:', error);
                return;
            }
        }

        async function remove_mpr(id) {
            try {
                if (confirm("Are you sure?")) {

                    let response = await fetch('/manpower/delete/'+id, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    let result = await response.json();

                    if (response.ok && result.success) {
                        alert('Removed');
                        $('#mprTab button.active').click();
                    } else {
                        alert('Failed remove to post');
                        console.log(`Error: ${result.error}`);
                    }
                }

            } catch (error) {
                console.error('Error fetching the list:', error);
            }
        }

        async function approve(id) {
            try {
                if (confirm("Are you sure?")) {
                    let formData = new FormData();
                    formData.append('id', id);
                    formData.append('stat', 'approved');

                    let response = await fetch('/manpower/stat', {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    let result = await response.json();

                    if (response.ok && result.success) {
                        alert('Approved');
                        $('#mprTab button.active').click();
                    } else {
                        alert('Failed remove to post');
                        console.log(`Error: ${result.error}`);
                    }
                }

            } catch (error) {
                console.error('Error fetching the list:', error);
            }
        }

        async function approve_update(id) {
            try {
                if (confirm("Are you sure?")) {
                    let response = await fetch('/manpower/update/approve/' + id, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    let result = await response.json();

                    if (response.ok && result.success) {
                        alert('Approved');
                        $('#mprTab button.active').click();
                    } else {
                        alert('Failed remove to post');
                        console.log(`Error: ${result.error}`);
                    }
                }

            } catch (error) {
                console.error('Error fetching the list:', error);
            }
        }

        async function decline_update(id) {
            try {
                if (confirm("Are you sure?")) {
                    let response = await fetch('/manpower/update/decline/' + id, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    let result = await response.json();

                    if (response.ok && result.success) {
                        alert('Declined');
                        $('#mprTab button.active').click();
                    } else {
                        alert('Failed remove to post');
                        console.log(`Error: ${result.error}`);
                    }
                }

            } catch (error) {
                console.error('Error fetching the list:', error);
            }
        }

        function decline(id) {
            $('#decline-mpr-id').val(id);
            $('#decline-mpr-reason').val('');
            $('#modal-decline-mpr').modal('show');
        }
    </script>
@stop