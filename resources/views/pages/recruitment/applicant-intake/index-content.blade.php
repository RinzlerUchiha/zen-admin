<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>Applicant Intake</h5>
    </div>

    <table id="applicant-intake-table" class="table table-sm table-bordered table-hover table-striped" style="width: 100%;">
        <thead>
            <tr>
                <th></th>
                <th>Date Applied (Latest)</th>
                <th>Applicant</th>
                <th>Email</th>
                <th>Contact</th>
                <th># Applications</th>
            </tr>
        </thead>
    </table>
</div>

<style>
    td.dt-control {
        cursor: pointer;
    }
    td.dt-control::before {
        content: '▶';
        display: inline-block;
        font-size: 10px;
        transition: transform 0.15s ease;
    }
    tr.shown td.dt-control::before {
        transform: rotate(90deg);
    }
    .applicant-intake-child-table th {
        font-size: 11px;
        text-transform: uppercase;
        color: #8A93A3;
        border-bottom: 1px solid #E7E9EE;
    }
</style>

<script>
    function formatApplicantChild(applicant) {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;

        let rows = applicant.applications.map(app => `
            <tr>
                <td>${new Date(app.applied_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })}</td>
                <td>${app.posting_title}</td>
                <td>${app.mr_no}</td>
                <td><span class="mpv-chip mpv-chip-pending">${app.status}</span></td>
                <td class="text-end">
                    <a href="${baseUrl}/applicant/info/${app.app_id}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
            </tr>
        `).join('');

        return `
            <table class="table table-sm table-borderless mb-0 applicant-intake-child-table">
                <thead>
                    <tr>
                        <th>Date Applied</th>
                        <th>Position</th>
                        <th>REQ ID</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;
    }

    $(function () {
        const urlPrefix = document.querySelector('meta[name="url-prefix"]')?.getAttribute('content') || '';

        const table = $('#applicant-intake-table').DataTable({
            ajax: {
                url: urlPrefix + '/recruitment/applicant-intake/data',
                dataSrc: 'data'
            },
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                {
                    data: 'latest_applied_at',
                    render: (data) => new Date(data).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
                },
                { data: 'applicant_name' },
                { data: 'app_email' },
                { data: 'app_mobile' },
                { data: 'application_count' }
            ],
            order: [[1, 'desc']],
            scrollY: '55vh',
            scrollCollapse: true,
            lengthMenu: [50, 100, { label: 'All', value: -1 }]
        });

        $('#applicant-intake-table tbody').on('click', 'td.dt-control', function () {
            const tr = $(this).closest('tr');
            const row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatApplicantChild(row.data())).show();
                tr.addClass('shown');
            }
        });
    });
</script>