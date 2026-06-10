@extends('layouts.layout')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

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

    #page-tabs svg.bi {
        height: 30px;
        width: 30px;
    }    
</style>

<script type="text/javascript">
    $(function(){
        const link_item = $("#page-tabs a[href='{{ config('app.url').'/maintenance/'.$maincat }}']").parent()[0];
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

            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/company" class="nav-link gap-2 icon-link {{$maincat == 'company' ? 'active' : ''}}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building" viewBox="0 0 20 20">
                    <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                    <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/>
                </svg>
                Company</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/department" class="nav-link gap-2 icon-link {{$maincat == 'department' ? 'active' : ''}}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 20 20">
                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                </svg>
                Department</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/position" class="nav-link gap-2 icon-link {{$maincat == 'position' ? 'active' : ''}}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-gear" viewBox="0 0 16 16">
                    <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1zm3.63-4.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                </svg>
                Position</a></li>
            
            <hr class='my-1'>

            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/outlet" class="nav-link gap-2 icon-link {{$maincat == 'outlet' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/outlet.png') }}"> Outlet</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/area" class="nav-link gap-2 icon-link {{$maincat == 'area' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/area.png') }}"> Area</a></li>
            
            <hr class='my-1'>
            
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/province" class="nav-link gap-2 icon-link {{$maincat == 'province' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/province.png') }}"> Province</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/city" class="nav-link gap-2 icon-link {{$maincat == 'city' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/city.png') }}"> City</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/barangay" class="nav-link gap-2 icon-link {{$maincat == 'barangay' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/brngy.png') }}"> Barangay</a></li>
            
            <hr class='my-1'>

            {{-- <li class="nav-item"><a href="#" class="nav-link gap-2 icon-link {{$maincat == 'leave-balance' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/leave-balance.png') }}"> Leave Balance</a></li> --}}
            <li class="nav-item"><a href="{{ config('app.url') }}/maintenance/assign" class="nav-link gap-2 icon-link {{$maincat == 'auth-approval' ? 'active' : ''}}"><i class="bi bi-diagram-3" style="font-size: 30px; width: 30px; height: 30px;"></i> Authorization & Approval</a></li>
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