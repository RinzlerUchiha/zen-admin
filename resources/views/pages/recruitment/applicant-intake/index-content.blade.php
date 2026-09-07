<style>
    .ai-page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .ai-page-head h5 {
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.2px;
        margin: 0;
    }

    /* ===== Status chip (shared pattern) ===== */
    .mpv-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .mpv-chip::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .mpv-chip-pending {
        background: #E8F0FE;
        color: #1B4FB0;
    }

    .mpv-chip-approved {
        background: #E7F6EC;
        color: #1E9E4C;
    }

    .mpv-chip-rejected {
        background: #FCEBEB;
        color: #791F1F;
    }

    .mpv-chip-draft {
        background: #F1F2F5;
        color: #5B6474;
    }

    /* ===== Table card wrap ===== */
    .ai-table-card {
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
        background: #fff;
        padding: 4px 4px 0;
    }

    #applicant-intake-table {
        border-collapse: collapse !important;
        font-size: 13px;
    }

    #applicant-intake-table thead th {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #8A93A3;
        padding: 12px 14px !important;
        background: #F5F6F9;
        border-bottom: 1px solid #E7E9EE !important;
        border-top: none !important;
        white-space: nowrap;
    }

    #applicant-intake-table tbody td {
        padding: 12px 14px !important;
        border-bottom: 1px solid #F1F2F5 !important;
        border-top: none !important;
        vertical-align: middle;
        color: #1F2430;
    }

    #applicant-intake-table.table-bordered,
    #applicant-intake-table.table-bordered td,
    #applicant-intake-table.table-bordered th {
        border: none;
    }

    #applicant-intake-table.table-striped tbody tr:nth-of-type(odd) {
        background: transparent;
    }

    #applicant-intake-table tbody tr:hover {
        background: #FAFBFF !important;
    }

    /* ===== dt-control (expand toggle) ===== */
    td.dt-control {
        cursor: pointer;
        width: 36px;
        text-align: center;
    }

    td.dt-control::before {
        content: '';
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #F1F2F5;
        color: #5B6474;
        font-size: 10px;
        transition: background .15s ease, color .15s ease, transform .2s ease;
    }

    td.dt-control::before {
        content: '\f105';
        /* fa chevron-right */
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
    }

    tr:hover td.dt-control::before {
        background: #E8F0FE;
        color: #1B4FB0;
    }

    tr.shown td.dt-control::before {
        background: #E8F0FE;
        color: #1B4FB0;
        transform: rotate(90deg);
    }

    /* ===== Child (expanded) row ===== */
    td.child {
        background: #FAFBFC !important;
        padding: 4px 14px 14px !important;
    }

    .applicant-intake-child-card {
        border: 1px solid #E7E9EE;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    table.applicant-intake-child-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .applicant-intake-child-table th {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #8A93A3;
        padding: 8px 12px;
        background: #F5F6F9;
        border-bottom: 1px solid #E7E9EE;
        text-align: left;
    }

    .applicant-intake-child-table td {
        padding: 9px 12px;
        border-bottom: 1px solid #F1F2F5;
        font-size: 12.5px;
        color: #1F2430;
    }

    .applicant-intake-child-table tbody tr:last-child td {
        border-bottom: none;
    }

    .ai-view-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E7E9EE;
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 700;
        color: #5B6474;
        text-decoration: none;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .ai-view-link:hover {
        background: #E8F0FE;
        color: #1B4FB0;
        border-color: #C7D8F7;
    }

    /* ===== DataTables control bar polish ===== */
    #applicant-intake-table_wrapper .dataTables_length,
    #applicant-intake-table_wrapper .dataTables_filter,
    #applicant-intake-table_wrapper .dataTables_info,
    #applicant-intake-table_wrapper .dataTables_paginate {
        padding: 10px 14px;
        font-size: 12.5px;
        color: #5B6474;
    }

    #applicant-intake-table_wrapper .dataTables_length select,
    #applicant-intake-table_wrapper .dataTables_filter input {
        border: 1px solid #E7E9EE;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 12.5px;
    }

    #applicant-intake-table_wrapper .dataTables_filter input:focus,
    #applicant-intake-table_wrapper .dataTables_length select:focus {
        outline: none;
        border-color: #9FB8ED;
        box-shadow: 0 0 0 3px #E8F0FE;
    }

    #applicant-intake-table_wrapper .dataTables_paginate .paginate_button {
        border-radius: 7px !important;
        border: 1px solid transparent !important;
        padding: 5px 11px !important;
        margin-left: 3px !important;
        background: transparent !important;
        color: #5B6474 !important;
    }

    #applicant-intake-table_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #2F6FE4, #1B4FB0) !important;
        border-color: transparent !important;
        color: #fff !important;
    }

    #applicant-intake-table_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #F1F2F5 !important;
        border-color: #E7E9EE !important;
        color: #1F2430 !important;
    }
</style>

<div class="container-fluid">
    <div class="ai-page-head">
        <h5>Applicant Intake</h5>
    </div>

    <div class="ai-table-card">
        <table id="applicant-intake-table" class="table table-sm table-bordered table-hover table-striped"
            style="width: 100%;">
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
</div>

<script>
    function formatApplicantChild(applicant) {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;

        const STATUS_CLASS = {
            'Pending': 'mpv-chip-pending',
            'Approved': 'mpv-chip-approved',
            'Rejected': 'mpv-chip-rejected'
        };

        let rows = applicant.applications.map(app => `
            <tr>
                <td>${new Date(app.applied_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })}</td>
                <td>${app.posting_title}</td>
                <td>${app.mr_no}</td>
                <td><span class="mpv-chip ${STATUS_CLASS[app.status] || 'mpv-chip-pending'}">${app.status}</span></td>
                <td class="text-end">
                    <a href="${baseUrl}/applicant/info/${app.app_id}" class="ai-view-link">View</a>
                </td>
            </tr>
        `).join('');

        return `
            <div class="applicant-intake-child-card">
                <table class="applicant-intake-child-table">
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
            </div>
        `;
    }

    $(function() {
        const urlPrefix = document.querySelector('meta[name="url-prefix"]')?.getAttribute('content') || '';

        const table = $('#applicant-intake-table').DataTable({
            ajax: {
                url: urlPrefix + '/recruitment/applicant-intake/data',
                dataSrc: 'data'
            },
            columns: [{
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                {
                    data: 'latest_applied_at',
                    render: (data) => new Date(data).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit'
                    })
                },
                {
                    data: 'applicant_name'
                },
                {
                    data: 'app_email'
                },
                {
                    data: 'app_mobile'
                },
                {
                    data: 'application_count'
                }
            ],
            order: [
                [1, 'desc']
            ],
            scrollY: '55vh',
            scrollCollapse: true,
            lengthMenu: [50, 100, {
                label: 'All',
                value: -1
            }]
        });

        $('#applicant-intake-table tbody').on('click', 'td.dt-control', function() {
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
