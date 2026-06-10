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

    [notification-cnt]:not([notification-cnt=""])::after {
        content: attr(notification-cnt);
        /*color: red;
        font-size: 11px;
        align-self: flex-start !important;
        font-family: Arial, sans-serif;*/
        display: inline-block;
        border-radius: 3px; /* Make it circular */
        /*background-color: red;*/
        border: 1px solid red;
        color: red;
        text-align: center;
        font-size: 0.7rem;   /* Adjust number size */
        padding-left: 3px;
        padding-right: 3px;
    }

    [data-bs-toggle="tab"][notification-cnt]:not([notification-cnt=""])::after {
        margin-left: 5px;
    }
</style>

<script type="text/javascript">
    $(function(){
        const link_item = $("#page-tabs a[href='{{ config('app.url').'/grievance/'.$maincat }}']").parent()[0];
        if(link_item){
            // Scroll the item into view horizontally
            link_item.scrollIntoView({
                // behavior: 'smooth',  // Smooth scrolling
                block: 'center'     // Scroll the element to the center horizontally
            });
        }

        fetchNotifications('ir');
        fetchNotifications('13a');
        fetchNotifications('13b');
    });

    function fetchNotifications(type) {
        // Call the API to fetch new notifications
        fetch('/grievance/'+type+'/notifications')
            .then(response => response.json())
            .then(notifications => {
                tab = type == 'ir' ? 'irTab' : '_'+type+'Tab';
                $('#'+tab+' [notification-cnt]').attr('notification-cnt', '');
                let total = 0;
                for(i in notifications){
                    $('#'+tab+' #'+i.replace(' ', '-')+'-tab').attr('notification-cnt', notifications[i] || '');
                    total += parseInt(notifications[i]);
                }
                $('#page-tabs a[href="'+'/grievance/'+type+'"]').attr('notification-cnt', total || '');
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }
</script>

<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="page-tabs">
            <li class="nav-item"><a href="{{ config('app.url') }}/grievance/ir" class="nav-link gap-2 icon-link d-flex {{$maincat == 'ir' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/ir.png') }}"> IR</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/grievance/13a" class="nav-link gap-2 icon-link d-flex {{$maincat == '13a' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/13a.png') }}"> 13A</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/grievance/13b" class="nav-link gap-2 icon-link d-flex {{$maincat == '13b' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/13b.png') }}"> 13B</a></li>
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