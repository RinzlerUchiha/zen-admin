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

<script>
    $(function () {
        const link_item = $("#page-tabs a[href='{{ config('app.url').'/applicant/list' }}']").parent()[0];
        if(link_item){
            link_item.scrollIntoView({ block: 'center' });
        }
        let table = $('#applicant-table').DataTable({
            processing: true,
            serverSide: true,
            // dom: '<"row"<"col-md-4"filt><"col-md-4"l><"col-md-4"f>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            pageLength: 10,       // default rows per page
            lengthMenu: [10, 25, 50, 100],
            ajax: {
                url: "{{ route('applicant.index') }}",
                data: function (d) {
                    d.status = $('#status-filter').val();
                }
            },
            columns: [
                { data: 'date_created' },
                { data: 'name' },
                { data: 'email' },
                { data: 'contact' },
                { data: 'position' },
            ],

            rowCallback: function (row, data) {
                $(row)
                    .css('cursor', 'pointer')
                    .on('click', function () {
                        window.location.href = data.show_url;
                    });
            },

            // alternative with $('#applicant-table tbody').on('click', 'tr', function () {})
            // createdRow: function (row, data) {
            //     $(row)
            //         .attr('data-href', data.show_url)
            //         .css('cursor', 'pointer');
            // },

            initComplete: function () {
                $('#status-filter')
                    .prependTo('.dt-length')
                    .addClass('me-2');
            }
        });

        // Reload table when dropdown changes
        $('#status-filter').on('change', function () {
            table.ajax.reload();
        });
    });
</script>

<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="page-tabs">
            <li class="nav-item"><a href="{{ config('app.url') }}/applicant/list" class="nav-link gap-2 icon-link {{ $maincat == '' ? 'active' : '' }}">
                <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/applicant.png') }}"> Applicant List</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/applicant/list" class="nav-link gap-2 icon-link {{ $maincat == '' ? 'active' : '' }}">
                <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/applicant.png') }}"> Applicant List</a></li>
        </ul>
    </div>
    <div class="col-md-10">
        <h5>Applicant List</h5>
        <select id="status-filter" class="form-control form-control-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="hired">Hired</option>
        </select>
        <table id="applicant-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Date Created</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Position</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@stop