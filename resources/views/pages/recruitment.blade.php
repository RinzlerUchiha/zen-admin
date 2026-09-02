@extends('layouts.layout')

@section('content')
<style>
    :root {
        --my-top-space: calc(var(--main-top-margin) + .25rem);
    }

    #page-tabs {
        width: 100%;
        height: calc(100vh - var(--my-top-space));
        max-height: calc(100vh - var(--my-top-space));
        flex-wrap: nowrap;
        position: sticky;
        top: var(--my-top-space);
        border-right: 1px solid lightgray;
        overflow: auto;
    }

    #page-tabs li a {
        font-size: 12px;
        color: black;
    }

    #page-tabs li a.active {
        font-weight: bold;
        color: var(--bs-primary);
    }

    #page-tabs li:hover {
        background-color: #d1d1d1;
    }

    #page-tabs li a.disabled {
        color: #adb5bd;
        pointer-events: none;
        cursor: default;
    }

    #page-tabs li.disabled:hover {
        background-color: transparent;
    }

    #page-tabs::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    #page-tabs::-webkit-scrollbar-thumb {
        background: #8b8a8a;
        border-radius: 10px;
    }
</style>

<script type="text/javascript">
    $(function(){
        const link_item = $("#page-tabs a[href='{{ config('app.url').'/recruitment/'.$maincat }}']").parent()[0];
        if(link_item){
            link_item.scrollIntoView({
                block: 'center'
            });
        }
    });
</script>

<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="page-tabs">
            <li class="nav-item"><a href="{{ config('app.url') }}/recruitment/manpower" class="nav-link gap-2 icon-link d-flex {{$maincat == 'manpower' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/prf.png') }}"> Manpower Request</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/recruitment/job-postings" class="nav-link gap-2 icon-link d-flex {{$maincat == 'job-postings' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/job-posting.png') }}"> Job Postings</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/recruitment/applicant-intake" class="nav-link gap-2 icon-link d-flex {{$maincat == 'applicant-intake' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/applicant.png') }}"> Applicant Intake</a></li>
            <li class="nav-item"><span class="nav-link gap-2 icon-link d-flex disabled"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/screening.png') }}"> Screening</span></li>
            <li class="nav-item"><span class="nav-link gap-2 icon-link d-flex disabled"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/interview.png') }}"> Interview / Offer</span></li>
            <li class="nav-item"><span class="nav-link gap-2 icon-link d-flex disabled"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/onboarding.png') }}"> Onboarding</span></li>
            <li class="nav-item"><span class="nav-link gap-2 icon-link d-flex disabled"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/probation.png') }}"> Probation</span></li>
        </ul>
    </div>

    <div class="col-md-10">
        <div class="row">
            <div class="col-12">
                @includeIf($page ?? '')
            </div>
        </div>
    </div>
</div>
@stop