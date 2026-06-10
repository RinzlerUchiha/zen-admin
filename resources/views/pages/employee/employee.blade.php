@extends('layouts.layout')

@section('content')
<style>
    :root {
        --my-top-space: calc(var(--main-top-margin) + .25rem);
    }

    #profile-tabs {
        width: 100%;
        max-height: calc(100vh - var(--my-top-space));
        flex-wrap: nowrap;
        /*transition: all 0.3s ease;*/
        position: sticky;
        top: var(--my-top-space);
        border-right: 1px solid lightgray;
        overflow: auto;
    }

    /* #profile-tabs:hover {
        overflow: auto;
    } */

    #profile-tabs h6 {
        font-size: .9rem;
    }

    #profile-tabs li a {
        font-size: 12px;
        color: black;
        @if($empno == 'new')
            pointer-events: none;  /* Disable clicking */
            color: #888;           /* Gray out the link text */
            text-decoration: none; /* Remove underline */
        @endif
    }

    #profile-tabs li a.active {
        font-weight: bold;
        color: var(--bs-primary);
    }

    #profile-tabs li:hover {
        background-color: #d1d1d1;
    }

    /* Adjusting scrollbar thickness */
    #profile-tabs::-webkit-scrollbar {
        width: 7px;  /* Vertical scrollbar width */
        height: 7px; /* Horizontal scrollbar height */
    }

    /* Customize the scrollbar thumb (draggable part) */
    #profile-tabs::-webkit-scrollbar-thumb {
        background: #8b8a8a;  /* Color of the thumb */
        border-radius: 10px;  /* Rounded corners for thumb */
    }

    .form-control-plaintext.border-bottom {
        padding-bottom: 1px !important;
    }

    fieldset:disabled select {
        -webkit-appearance: none; /* For Safari, Chrome */
        -moz-appearance: none;    /* For Firefox */
        appearance: none;         /* For modern browsers */
        /*background: transparent;*/  /* Make the background transparent if needed */
        border: none;             /* Optional: remove the border */
    }
</style>

<script type="text/javascript">
    $(function(){
        const link_item = $("#profile-tabs a[href='{{ config('app.url').'/employee/'.$maincat.'/'.$subcat.'/'.($empno ?? '') }}']").parent()[0];
        if(link_item){
            // Scroll the item into view horizontally
            link_item.scrollIntoView({
                // behavior: 'smooth',  // Smooth scrolling
                block: 'center'     // Scroll the element to the center horizontally
            });
        }

        $('#select-employee').change(function(){
            window.location = '{{ config('app.url') }}/employee/{{ $maincat }}/{{ $subcat }}/' + this.value;
        });
    })
</script>

<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="profile-tabs">

            <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                <span>Profile</span>
            </h6>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/profile/personal/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'profile/personal' ? 'active' : ''}}">Personal</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/profile/family/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'profile/family' ? 'active' : ''}}">Family Background</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/profile/skills/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'profile/skills' ? 'active' : ''}}">Special Skills</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/profile/education/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'profile/education' ? 'active' : ''}}">Education</a></li>

            <hr class="my-3">

            <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                <span>Professional</span>
            </h6>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/professional/license/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'professional/license' ? 'active' : ''}}">Eligibility/Licenses</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/professional/certificate/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'professional/certificate' ? 'active' : ''}}">Certificate</a></li>

            <hr class="my-3">

            <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                <span>Work</span>
            </h6>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/work/employment/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'work/employment' ? 'active' : ''}}">Employment Record</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/work/job/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'work/job' ? 'active' : ''}}">Job Record</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/work/certificate/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'work/certificate' ? 'active' : ''}}">Internal Certificate</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/work/payslip/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'work/payslip' ? 'active' : ''}}">Payslip</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/work/contracts/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'work/contracts' ? 'active' : ''}}">Contracts</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/work/characterref/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'work/characterref' ? 'active' : ''}}">Character Reference</a></li>

            <hr class="my-3">

            <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                <span>Personality Test</span>
            </h6>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/personality/enneagram/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'personality/enneagram' ? 'active' : ''}}">Enneagram</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/personality/tapt/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'personality/tapt' ? 'active' : ''}}">TAPT</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/personality/disc/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'personality/disc' ? 'active' : ''}}">DISC</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/personality/miq/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'personality/miq' ? 'active' : ''}}">Multiple Intelligent Quotient</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/personality/color/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'personality/color' ? 'active' : ''}}">What color are you?</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/employee/personality/vak/{{ $empno ?? '' }}" class="nav-link align-items-center gap-2 {{$maincat.'/'.$subcat == 'personality/vak' ? 'active' : ''}}">VAK</a></li>
        </ul>
    </div>
    <!-- <div class="card">
        <div class="card-body">
            {{-- @includeIf($page) --}}
            {{-- @yield('content-form') --}}
        </div>
    </div> -->

    <div class="col-md-10">
        <div class="row">
            <div class="col-md-5">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text">Employee</span>
                    <select class="form-select" id="select-employee">
                        <option {{ empty($empno) ? 'selected' : '' }}>-Select-</option>
                        {{-- <option value="new" {{ $empno == 'new' ? 'new' : '' }}>NEW</option> --}}
                        @foreach($employeeList as $emp)
                            <option value="{{ $emp->pers_empno }}" {{ !empty($empno) && $empno == $emp->pers_empno ? 'selected' : '' }}>{{ ucwords(trim($emp->pers_lastname.', '.$emp->pers_firstname.' '.($emp->pers_ext ?? ''))) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                @includeIf($page)
            </div>
        </div>
    </div>
</div>
@stop