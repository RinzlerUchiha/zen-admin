@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #form-personal {
        font-size: 12px;
    }

    /* #personal-img-preview:hover {
        transform: scale(1.7);
    } */
</style>

<div class="container-fluid">
    <div id="form-personal" class="mb-3">
        {{-- <input type="file" id="personal-img-input" accept="image/*" style="display:none;"> --}}
        {{-- <fieldset disabled> --}}
            <div class="row g-3">
                <div class="col-lg">
                    <span>Position Applied</span>
                    <p class="fw-bold">{{ $applicant?->app_posapplied }}</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-lg-1">
                    <img id="personal-img-preview" src="{{ $applicant?->app_img ? config('app.url')."/file/app-img/".$applicant?->app_img : asset('no-file.png') }}" class="img-thumbnail img-fluid object-fit-contain" alt="..." style="height: 70px; width: 70px;">
                </div>

                <div class="col-lg">
                    <span>First Name</span>
                    <p class="fw-bold">{{ $applicant?->app_fname }}</p>
                </div>
                <div class="col-lg">
                    <span>Middle Name</span>
                    <p class="fw-bold">{{ $applicant?->app_mname }}</p>
                </div>
                <div class="col-lg">
                    <span>Last Name</span>
                    <p class="fw-bold">{{ $applicant?->app_lname }}</p>
                </div>
                <div class="col-lg-2">
                    <span>Suffix</span>
                    <p class="fw-bold">{{ $applicant?->app_suffix }}</p>
                </div>
            </div>
            
            <!-- contact -->
            <h6 class="mt-3">Contact Info</h6>
            <div class="row g-3">
                <div class="col-lg-4">
                    <span>Email</span>
                    <p class="fw-bold">{{ $applicant?->app_email }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Personal Contact</span>
                    <p class="fw-bold">{{ $applicant?->app_mobile }}</p>
                </div>
                <div class="col-lg">
                    <span>Telephone</span>
                    <p class="fw-bold">{{ $applicant?->app_telephone }}</p>
                </div>
            </div>

            <!-- address -->
            <h6 class="mt-3">Permanent Address</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <span>Province</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_perm_prov }}</p>
                </div>
                <div class="col-lg-3">
                    <span>City</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_perm_city }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Barangay</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_perm_brngy }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Street/House #</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_perm_location }}</p>
                </div>
            </div>

            <h6 class="mt-3">Current Address</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <span>Province</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_cur_prov }}</p>
                </div>
                <div class="col-lg-3">
                    <span>City</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_cur_city }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Barangay</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_cur_brngy }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Street/House #</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_cur_location }}</p>
                </div>
            </div>

            <h6 class="mt-3">Place Of Birth</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <span>Province</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_birth_prov }}</p>
                </div>
                <div class="col-lg-3">
                    <span>City</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_birth_city }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Barangay</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_birth_brngy }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Street/House #</span>
                    <p class="fw-bold">{{ $applicant?->address?->add_birth_location }}</p>
                </div>
            </div>

            <!-- Basic Info -->
            <h6 class="mt-3">Basic Info</h6>
            <div class="row g-3">
                <div class="col-lg-auto">
                    <span>Birth Date</span>
                    <p class="fw-bold">{{ $applicant?->app_bdate }}</p>
                </div>
                <div class="col-lg-1">
                    <span>Age</span>
                    <p class="fw-bold">{{ $applicant?->app_age }}</p>
                </div>
                <div class="col-lg-auto">
                    <span>Civil Status</span>
                    <p class="fw-bold">{{ $applicant?->app_cstatus }}</p>
                </div>
                <div class="col-lg-auto">
                    <span>Sex</span>
                    <p class="fw-bold">{{ $applicant?->app_sex }}</p>
                </div>
                <div class="col-lg-auto">
                    <span>Blood Type</span>
                    <p class="fw-bold">{{ $applicant?->app_btype }}</p>
                </div>
                <div class="col-lg-2">
                    <span>Height (cm)</span>
                    <p class="fw-bold">{{ $applicant?->app_height }}</p>
                </div>
                <div class="col-lg-2">
                    <span>Weight (kg)</span>
                    <p class="fw-bold">{{ $applicant?->app_weight }}</p>
                </div>

                <div class="col-lg-auto">
                    <span>Nationality</span>
                    <p class="fw-bold">{{ $applicant?->app_nationality }}</p>
                </div>

                <div class="col-lg-auto">
                    <span>Religion</span>
                    <p class="fw-bold">{{ $applicant?->app_religion }}</p>
                </div>
                <div class="col-lg-auto">
                    <span>Dialect</span>
                    <p class="fw-bold">{{ $applicant?->app_dialect }}</p>
                </div>
            </div>

            <!-- gov accounts -->
            <h6 class="mt-3">Government Identification Numbers</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <span>SSS #</span>
                    <p class="fw-bold">{{ $applicant?->app_sss }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Pagibig #</span>
                    <p class="fw-bold">{{ $applicant?->app_pagibig }}</p>
                </div>
                <div class="col-lg-3">
                    <span>Philhealth #</span>
                    <p class="fw-bold">{{ $applicant?->app_philhealth }}</p>
                </div>
                <div class="col-lg-3">
                    <span>TIN #</span>
                    <p class="fw-bold">{{ $applicant?->app_tin }}</p>
                </div>
            </div>
        {{-- </fieldset> --}}
    </form>
</div>

@stop