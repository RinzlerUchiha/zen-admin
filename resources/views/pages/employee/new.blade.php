@extends('layouts.layout')

@section('content')
<style>
    #form-new-employee .form-floating > .form-control,
    #form-new-employee .form-floating > .form-control-plaintext,
    #form-new-employee .form-floating > .form-select {
        font-size: 12px;
        height: calc(3rem + calc(var(--bs-border-width)* 2));
        min-height: calc(3rem + calc(var(--bs-border-width)* 2));
        padding-top: 30px;
        padding-bottom: 3px;
    }

    #form-new-employee {
        margin-bottom: 100px;
    }
</style>
<div class="row justify-content-center pt-1">
    <div class="col-md-8">
        <form id="form-new-employee" action="{{ route('save_new_employee') }}" method="POST">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row g-3">
                <div class="col-lg-1">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Sample_User_Icon.png" class="img-thumbnail img-fluid" alt="...">
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-number" id="new-employee-number" value="">
                        <label for="new-employee-number">Employee Number</label>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-firstname" id="new-employee-firstname" value="">
                        <label for="new-employee-firstname">First Name</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-middlename" id="new-employee-middlename" value="">
                        <label for="new-employee-middlename">Middle Name</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-lastname" id="new-employee-lastname" value="">
                        <label for="new-employee-lastname">Last Name</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-suffix" id="new-employee-suffix" value="">
                        <label for="new-employee-suffix">Suffix</label>
                    </div>
                </div>
            </div>
            
            <!-- contact -->
            <h6 class="mt-3">Contact Info</h6>
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control-plaintext border-bottom" name="new-employee-email" id="new-employee-email" value="">
                        <label for="new-employee-email">Email</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-contact" id="new-employee-contact" value="">
                        <label for="new-employee-contact">Personal Contact</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-company-contact" id="new-employee-company-contact" value="">
                        <label for="new-employee-company-contact">Company Contact</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-telephone" id="new-employee-telephone" value="">
                        <label for="new-employee-telephone">Telephone</label>
                    </div>
                </div>
            </div>
    
            <!-- address -->
            <h6 class="mt-3">Permanent Address</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-province" name="new-employee-padd-province" id="new-employee-padd-province" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($provinceList as $list)
                                <option value="{{ $list->pr_code }}">{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-padd-province">Province</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-city" name="new-employee-padd-city" id="new-employee-padd-city" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($municipalityList as $list)
                                <option province="{{ $list->ct_province }}" value="{{ $list->ct_id }}">{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-padd-city">City</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-barangay" name="new-employee-padd-barangay" id="new-employee-padd-barangay" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($barangayList as $list)
                                <option city="{{ $list->br_city }}" value="{{ $list->br_id }}">{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-padd-barangay">Barangay</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-padd-specific" id="new-employee-padd-specific" value="">
                        <label for="new-employee-padd-specific">Street/House #</label>
                    </div>
                </div>
            </div>
    
            <h6 class="mt-3">Current Address</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-province" name="new-employee-cadd-province" id="new-employee-cadd-province" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($provinceList as $list)
                                <option value="{{ $list->pr_code }}">{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-cadd-province">Province</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-city" name="new-employee-cadd-city" id="new-employee-cadd-city" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($municipalityList as $list)
                                <option province="{{ $list->ct_province }}" value="{{ $list->ct_id }}">{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-cadd-city">City</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-barangay" name="new-employee-cadd-barangay" id="new-employee-cadd-barangay" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($barangayList as $list)
                                <option city="{{ $list->br_city }}" value="{{ $list->br_id }}">{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-cadd-barangay">Barangay</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-cadd-specific" id="new-employee-cadd-specific" value="">
                        <label for="new-employee-cadd-specific">Street/House #</label>
                    </div>
                </div>
            </div>
    
            <h6 class="mt-3">Place Of Birth</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-province" name="new-employee-badd-province" id="new-employee-badd-province" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($provinceList as $list)
                                <option value="{{ $list->pr_code }}">{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-badd-province">Province</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-city" name="new-employee-badd-city" id="new-employee-badd-city" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($municipalityList as $list)
                                <option province="{{ $list->ct_province }}" value="{{ $list->ct_id }}">{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-badd-city">City</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-barangay" name="new-employee-badd-barangay" id="new-employee-badd-barangay" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($barangayList as $list)
                                <option city="{{ $list->br_city }}" value="{{ $list->br_id }}">{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-badd-barangay">Barangay</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-badd-specific" id="new-employee-badd-specific" value="">
                        <label for="new-employee-badd-specific">Street/House #</label>
                    </div>
                </div>
            </div>
    
            <!-- Basic Info -->
            <h6 class="mt-3">Basic Info</h6>
            <div class="row g-3">
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control-plaintext border-bottom" name="new-employee-birthdate" id="new-employee-birthdate" value="">
                        <label for="new-employee-birthdate">Birth Date</label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div class="form-floating mb-3">
                        <input type="text" readonly class="form-control-plaintext border-bottom" name="new-employee-age" id="new-employee-age" value="">
                        <label for="new-employee-age">Age</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-civil-status" id="new-employee-civil-status" aria-label="">
                            <option value="">-Select-</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Separated/Divorced">Separated/Divorced</option>
                            <option value="Widow/Widower">Widow/Widower</option>
                        </select>
                        <label for="new-employee-civil-status">Civil Status</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-sex" id="new-employee-sex" aria-label="">
                            <option value="">-Select-</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        <label for="new-employee-sex">Sex</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-bloodtype" id="new-employee-bloodtype" aria-label="">
                            <option value="">-Select-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                        <label for="new-employee-bloodtype">Blood Type</label>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-height" id="new-employee-height" value="">
                        <label for="new-employee-height">Height (cm)</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-weight" id="new-employee-weight" value="">
                        <label for="new-employee-weight">Weight (kg)</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-religion" id="new-employee-religion" value="">
                        <label for="new-employee-religion">Religion</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-dialect" id="new-employee-dialect" value="">
                        <label for="new-employee-dialect">Dialect</label>
                    </div>
                </div>
            </div>
    
            <!-- gov accounts -->
            <h6 class="mt-3">Government Identification Numbers</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-sss" id="new-employee-sss" value="">
                        <label for="new-employee-sss">SSS #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-hdmf" id="new-employee-hdmf" value="">
                        <label for="new-employee-hdmf">Pagibig #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-phic" id="new-employee-phic" value="">
                        <label for="new-employee-phic">Philhealth #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="new-employee-tin" id="new-employee-tin" value="">
                        <label for="new-employee-tin">TIN #</label>
                    </div>
                </div>
            </div>


            <!-- job record -->
            <h6 class="mt-3">Job Information</h6>
            <div class="row g-3">
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control-plaintext border-bottom" name="new-employee-date-hired" id="new-employee-date-hired" value="">
                        <label for="new-employee-date-hired">Date Hired #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-employment-status" id="new-employee-employment-status" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($emplStatusList as $list)
                                <option value="{{ $list->es_code }}">{{ $list->es_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-employment-status">Employement Status</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-company" id="new-employee-company" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($companyList as $list)
                                <option value="{{ $list->C_Code }}">{{ $list->C_Name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-company">Company</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-department" id="new-employee-department" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($departmentList as $list)
                                <option value="{{ $list->Dept_Code }}">{{ $list->Dept_Name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-department">Department</label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-section" id="new-employee-section" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($sectionList as $list)
                                <option value="{{ $list->sec_code }}">{{ $list->sec_name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-section">Section</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-position" id="new-employee-position" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($positionList as $list)
                                <option value="{{ $list->jd_code }}">{{ $list->jd_title }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-position">Position</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-job-step" id="new-employee-job-step" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($jobStepList as $list)
                                <option value="{{ $list }}">{{ $list }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-job-step">Job Step</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-job-grade" id="new-employee-job-grade" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($jobGradeList as $list)
                                <option value="{{ $list->jg_code }}">{{ $list->jg_grade }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-job-grade">Job Grade</label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-area" id="new-employee-area" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($areaList as $list)
                                <option value="{{ $list->Area_Code }}">{{ $list->Area_Name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-area">Area</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-outlet" id="new-employee-outlet" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($outletList as $list)
                                <option value="{{ $list->OL_Code }}">{{ $list->OL_Name }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-outlet">Outlet</label>
                    </div>
                </div>

                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="new-employee-reportto" id="new-employee-reportto" aria-label="">
                            <option value="">-Select-</option>
                            @foreach($employeeList as $emp)
                                <option value="{{ $emp->pers_empno }}">{{ ucwords(trim($emp->pers_lastname.', '.$emp->pers_firstname.' '.($emp->pers_ext ?? ''))) }}</option>
                            @endforeach
                        </select>
                        <label for="new-employee-reportto">Reports To</label>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@stop