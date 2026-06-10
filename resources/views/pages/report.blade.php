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
        /*transition: all 0.3s ease;*/
        position: sticky;
        top: var(--my-top-space);
        border-right: 1px solid lightgray;
        overflow: auto;
    }

    /* #page-tabs:hover {
        overflow: auto;
    } */

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

    /* Adjusting scrollbar thickness */
    #page-tabs::-webkit-scrollbar {
        width: 7px;  /* Vertical scrollbar width */
        height: 7px; /* Horizontal scrollbar height */
    }

    /* Customize the scrollbar thumb (draggable part) */
    #page-tabs::-webkit-scrollbar-thumb {
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
        const link_item = $("#page-tabs a[href='{{ config('app.url').'/report/'.$maincat }}']").parent()[0];
        if(link_item){
            // Scroll the item into view horizontally
            link_item.scrollIntoView({
                // behavior: 'smooth',  // Smooth scrolling
                block: 'center'     // Scroll the element to the center horizontally
            });
        }
    })
</script>

<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="page-tabs">
            <li class="nav-item">
                <a href="{{ config('app.url') }}/report/eei" class="nav-link gap-2 icon-link {{$maincat == 'eei' ? 'active' : ''}}">
                    <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/eei.png') }}"> EEI
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ config('app.url') }}/report/retention" class="nav-link gap-2 icon-link {{$maincat == 'retention' ? 'active' : ''}}">
                    <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/retention.png') }}"> Retention
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ config('app.url') }}/report/facetime" class="nav-link gap-2 icon-link {{$maincat == 'facetime' ? 'active' : ''}}">
                    <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/facetime.png') }}"> Facetime
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ config('app.url') }}/report/pa" class="nav-link gap-2 icon-link {{$maincat == 'pa' ? 'active' : ''}}">
                    <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/pa.png') }}"> PA
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ config('app.url') }}/report/outgoing" class="nav-link gap-2 icon-link {{$maincat == 'outgoing' ? 'active' : ''}}">
                    <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/resigning.png') }}"> Outgoing
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ config('app.url') }}/report/manpower" class="nav-link gap-2 icon-link {{$maincat == 'manpower' ? 'active' : ''}}">
                    <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/schedule.png') }}"> Manpower Line-up
                </a>
            </li>
        </ul>
    </div>

    <div class="col-md-10">
        <div class="row">
            <div class="col-12">
                @includeIf($page)
            </div>
        </div>
    </div>
</div>
@stop