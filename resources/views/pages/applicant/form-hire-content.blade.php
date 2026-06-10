<div class="row mb-3">
    <span class="col-md-4 col-form-label col-form-label-sm">Name:</span>
    <div class="col-md my-auto">
        {{ $applicant?->app_lname.', '.$applicant?->app_fname.trim(' '.$applicant?->app_suffix) }}
    </div>
</div>

<div class="row mb-3">
    <span class="col-md-4 col-form-label col-form-label-sm">Applied for:</span>
    <div class="col-md my-auto">
        {{ $applicant?->app_posapplied }}
    </div>
</div>

<div class="row mb-3">
    <label for="hire-dt" class="col-md-4 col-form-label col-form-label-sm">Date Hired:</label>
    <div class="col-md-auto">
        <input type="date" class="form-control form-control-sm" name="hire-dt" id="hire-dt" required>
    </div>
</div>


<div class="row mb-3">
    <label for="hire-employment-status" class="col-md-4 col-form-label col-form-label-sm">Employement Status:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-employment-status" id="hire-employment-status" required>
            <option disabled selected>-</option>
            @foreach ($employment_status ?? [] as $item)
                <option value="{{ $item->es_code }}">{{ $item->es_name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- <div class="row mb-3">
    <label for="hire-area" class="col-md-4 col-form-label col-form-label-sm">Area:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" id="hire-area">
            @foreach ($area_list ?? [] as $item)
                <option value="{{ $item->Area_Code }}">{{ $item->jd_title }}</option>
            @endforeach
        </select>
    </div>
</div> --}}

<div class="row mb-3">
    <label for="hire-outlet" class="col-md-4 col-form-label col-form-label-sm">Outlet:</label>
    <div class="col-md">
        <input type="hidden" name="hire-area" id="hire-area">
        <select class="form-select form-select-sm" name="hire-outlet" id="hire-outlet" required>
            <option disabled selected>-</option>
            @foreach ($outlet_list ?? [] as $item)
                <option data-area="{{ $item->Area_Code }}" value="{{ $item->OL_Code }}">{{ $item->Area_Code }} - {{ $item->OL_Name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-company" class="col-md-4 col-form-label col-form-label-sm">Company:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-company" id="hire-company" required>
            <option disabled selected>-</option>
            @foreach ($company_list ?? [] as $item)
                <option value="{{ $item->C_Code }}">{{ $item->C_Name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-department" class="col-md-4 col-form-label col-form-label-sm">Department:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-department" id="hire-department" required>
            <option disabled selected>-</option>
            @foreach ($department_list ?? [] as $item)
                <option value="{{ $item->Dept_Code }}">{{ $item->Dept_Name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-position" class="col-md-4 col-form-label col-form-label-sm">Position:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-position" id="hire-position" required>
            <option disabled selected>-</option>
            @foreach ($position_list ?? [] as $item)
                <option value="{{ $item->jd_code }}">{{ $item->jd_title }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-jobstep" class="col-md-4 col-form-label col-form-label-sm">Job Step:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-jobstep" id="hire-jobstep" required>
            <option disabled selected>-</option>
            @foreach ($jobstep_list ?? [] as $item)
                <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-jobgrade" class="col-md-4 col-form-label col-form-label-sm">Job Grade:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-jobgrade" id="hire-jobgrade" required>
            <option disabled selected>-</option>
            @foreach ($jobgrade_list ?? [] as $item)
                <option value="{{ $item->jg_code }}">{{ $item->jg_grade }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-section" class="col-md-4 col-form-label col-form-label-sm">Section:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-section" id="hire-section">
            <option value="">-</option>
            @foreach ($section_list ?? [] as $item)
                <option value="{{ $item->sec_code }}">{{ $item->sec_name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-reportto" class="col-md-4 col-form-label col-form-label-sm">Reports To:</label>
    <div class="col-md">
        <select class="form-select form-select-sm" name="hire-reportto" id="hire-reportto">
            <option value="">-</option>
            @foreach ($report_to ?? [] as $item)
                <option value="{{ $item->pers_empno }}">{{ ucwords($item->pers_lastname.', '.trim($item->pers_firstname.' '.$item->pers_suffix)) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-empno" class="col-md-4 col-form-label col-form-label-sm">Employee No:</label>
    <div class="col-md">
        <input type="text" class="form-control form-control-sm" name="hire-empno" id="hire-empno"  required>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-username" class="col-md-4 col-form-label col-form-label-sm">Username:</label>
    <div class="col-md">
        <input type="text" class="form-control form-control-sm" name="hire-username" id="hire-username"  required>
    </div>
</div>

<div class="row mb-3">
    <label for="hire-pw" class="col-md-4 col-form-label col-form-label-sm">Password:</label>
    <div class="col-md d-flex">
        <input type="password" class="form-control form-control-sm" name="hire-pw" id="hire-pw"  required>
        <button type="button" class="btn btn-light btn-sm ms-1" id="btn-hire-toggle-pw" onclick="toggleHirePw()">Show</button>
    </div>
</div>