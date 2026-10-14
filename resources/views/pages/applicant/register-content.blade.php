{{--
| The Applicant Register, inside the Recruitment workspace.
|
| It used to extend layouts.layout and carry its own copy of the HireFlow
| theme, so opening it from the pipeline dropped HR out of the workspace
| entirely -- no sidebar, no Applicants submenu, no way back except the
| browser. The page itself is unchanged; it is the shell around it that was
| wrong.
|
| The theme include and the .hf-theme wrapper are gone because
| pages/recruitment.blade.php already provides both. Including them twice
| would nest the scope inside itself.
--}}
<style>
    /* Only engages when the table is genuinely wider than the space. */
    .ap-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .ap-table-scroll > table { min-width: 720px; }
</style>

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
                {
                    data: 'name',
                    /*
                     | The name, and whether this person is barred.
                     |
                     | Beside the name rather than in a column of its own: a
                     | blacklist is rare, so a whole column would be empty
                     | almost everywhere and would cost width the five real
                     | columns need at 1366 and below.
                     |
                     | The name is escaped here. The column had no renderer
                     | before, and DataTables writes cell content as HTML, so
                     | adding one without escaping would turn a stored name
                     | into markup -- worse than what it replaced.
                     */
                    render: function (data, type, row) {
                        if (type !== 'display') { return data; }

                        const name = $('<div>').text(data == null ? '' : data).html();

                        return row.blacklisted
                            ? name + ' <span class="hf-chip hf-chip-warn">Blacklisted</span>'
                            : name;
                    }
                },
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
    <div class="col-12">
        <h5>Applicant List</h5>
        <select id="status-filter" class="form-control form-control-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="hired">Hired</option>
        </select>
        {{--
            A horizontal scroller around the table.

            Seven columns do not fit a narrow window, and without this the ones
            on the right were simply unreachable -- the page did not scroll
            sideways, so they were off the edge with no way to get to them.
            Wrapping the table rather than hiding columns keeps every column
            available at every width; at desktop sizes the scroller never
            engages and nothing looks different.
        --}}
        <div class="ap-table-scroll">
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
</div>
