@extends('layouts.layout')

@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

<style>
    :root {
        --my-top-space: calc(var(--main-top-margin) + .25rem);
    }
</style>

<script>
    $(function () {
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

<div class="row pt-1 justify-content-center">
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