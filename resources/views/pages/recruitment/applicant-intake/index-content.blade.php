<style>
    .ai-page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .ai-page-head h5 {
        font-weight: 800;
        color: var(--zn-ink);
        letter-spacing: -.2px;
        margin: 0;
    }

    /* ===== Status chip =====
       Prefixed .ai- rather than .mpv-: the Manpower view defines the same
       .mpv-chip* names and both are rendered inside pages.recruitment, so
       identical names on one page let either view's edits silently
       restyle the other. ===== */
    .ai-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .ai-chip::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .ai-chip-pending {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .ai-chip-approved {
        background: var(--zn-ok-soft);
        color: var(--zn-ok);
    }

    .ai-chip-rejected {
        background: var(--zn-warn-soft);
        color: var(--zn-warn);
    }

    .ai-chip-draft {
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
    }

    /* Application statuses (config/applications.php). The three ways an
       application ends are coloured apart: the applicant's own decision, a
       missed document deadline, and HR's decision. */
    .ai-chip-applied {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .ai-chip-docs-complete {
        background: var(--zn-ok-soft);
        color: var(--zn-ok);
    }

    .ai-chip-withdrawn {
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
    }

    .ai-chip-nonresponsive {
        background: var(--zn-caution-soft);
        color: var(--zn-caution);
    }

    .ai-chip-notselected {
        background: var(--zn-warn-soft);
        color: var(--zn-warn);
    }

    .ai-chip-pool {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .ai-closure {
        font-size: 11px;
        color: var(--zn-ink-3);
        margin-top: 3px;
        max-width: 42ch;
    }

    .ai-actions .btn {
        font-size: 11.5px;
        padding: 1px 8px;
    }

    /* Any status not in the map renders neutral rather than borrowing
       another status's colour. */
    .ai-chip-unknown {
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
    }

    .ai-chip-stack {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    /* ===== Expand affordance ===== */
    td.dt-control {
        cursor: pointer;
        width: 34px;
    }

    /* DataTables paints its own marker on dt-control; ours replaces it. */
    td.dt-control::before {
        display: none !important;
    }

    .ai-chev {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
        font-size: 10px;
        transition: transform .2s ease, background .15s ease, color .15s ease;
    }

    tr.shown .ai-chev {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
        transform: rotate(90deg);
    }

    /* ===== Inline states ===== */
    #ai-alert .alert {
        border-radius: var(--zn-radius-lg);
        border: none;
        font-size: 13px;
    }

    .ai-empty {
        color: var(--zn-ink-3);
        padding: 26px 10px;
        text-align: center;
    }

    .ai-empty i {
        display: block;
        font-size: 24px;
        margin-bottom: 8px;
        color: var(--zn-line-2);
    }

    /* ===== Table card wrap ===== */
    .ai-table-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(32, 26, 22,.04);
        background: var(--zn-surface);
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
        color: var(--zn-ink-3);
        padding: 12px 14px !important;
        background: var(--zn-bg);
        border-bottom: 1px solid var(--zn-line) !important;
        border-top: none !important;
        white-space: nowrap;
    }

    #applicant-intake-table tbody td {
        padding: 12px 14px !important;
        border-bottom: 1px solid var(--zn-surface-2) !important;
        border-top: none !important;
        vertical-align: middle;
        color: var(--zn-ink);
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
        background: var(--zn-accent-soft) !important;
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
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
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
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    tr.shown td.dt-control::before {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
        transform: rotate(90deg);
    }

    /* ===== Child (expanded) row ===== */
    td.child {
        background: var(--zn-surface-2) !important;
        padding: 4px 14px 14px !important;
    }

    .applicant-intake-child-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        overflow: hidden;
        background: var(--zn-surface);
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
        color: var(--zn-ink-3);
        padding: 8px 12px;
        background: var(--zn-bg);
        border-bottom: 1px solid var(--zn-line);
        text-align: left;
    }

    .applicant-intake-child-table td {
        padding: 9px 12px;
        border-bottom: 1px solid var(--zn-surface-2);
        font-size: 12.5px;
        color: var(--zn-ink);
    }

    .applicant-intake-child-table tbody tr:last-child td {
        border-bottom: none;
    }

    .ai-view-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--zn-ink-2);
        text-decoration: none;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .ai-view-link:hover {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
        border-color: var(--zn-line-2);
    }

    /* ===== DataTables control bar polish ===== */
    #applicant-intake-table_wrapper .dataTables_length,
    #applicant-intake-table_wrapper .dataTables_filter,
    #applicant-intake-table_wrapper .dataTables_info,
    #applicant-intake-table_wrapper .dataTables_paginate {
        padding: 10px 14px;
        font-size: 12.5px;
        color: var(--zn-ink-2);
    }

    #applicant-intake-table_wrapper .dataTables_length select,
    #applicant-intake-table_wrapper .dataTables_filter input {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 5px 10px;
        font-size: 12.5px;
    }

    /* A native select sizes to its widest option, and the browser draws its
       arrow inside that width. "50"/"100"/"All" are short enough that the
       arrow would sit on top of the text, so reserve room for it. */
    #applicant-intake-table_wrapper .dataTables_length select {
        min-width: 76px;
        padding-right: 28px;
    }

    #applicant-intake-table_wrapper .dataTables_filter input:focus,
    #applicant-intake-table_wrapper .dataTables_length select:focus {
        outline: none;
        border-color: var(--zn-accent);
        box-shadow: 0 0 0 3px var(--zn-accent-soft);
    }

    #applicant-intake-table_wrapper .dataTables_paginate .paginate_button {
        border-radius: var(--zn-radius) !important;
        border: 1px solid transparent !important;
        padding: 5px 11px !important;
        margin-left: 3px !important;
        background: transparent !important;
        color: var(--zn-ink-2) !important;
    }

    #applicant-intake-table_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, var(--zn-accent), var(--zn-accent-dark)) !important;
        border-color: transparent !important;
        color: var(--zn-on-accent) !important;
    }

    #applicant-intake-table_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: var(--zn-surface-2) !important;
        border-color: var(--zn-line) !important;
        color: var(--zn-ink) !important;
    }
</style>

<div class="container-fluid">
    <div class="ai-page-head">
        <h5>Applicant Intake</h5>
    </div>

    <div id="ai-alert">
        @if (session('success'))
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
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
                    <th>Status</th>
                    @can('applicant-documents.view')
                        <th>Documents</th>
                    @endcan
                </tr>
            </thead>
        </table>
    </div>
</div>

@can('applicant-applications.decide')
    {{-- HR's decisions on ONE application. Each closes only the application named
         here; the applicant's other applications, profile and documents carry on. --}}
    <div class="modal fade" id="aiDecideModal" tabindex="-1" aria-labelledby="aiDecideTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content" id="aiDecideForm">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-6" id="aiDecideTitle">Decide</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="font-size:13px">
                    <p class="mb-2 fw-semibold" id="aiDecideWho"></p>

                    <p class="text-muted mb-3" id="aiDecideWithdrawHelp">
                        Use this when the applicant has asked to withdraw this application. It closes this
                        application only, with no waiting period — they can apply to this posting again while it
                        is open. It is recorded as withdrawn by you on their behalf.
                    </p>
                    <p class="text-muted mb-3" id="aiDecideNotSelectedHelp" hidden>
                        HR's decision not to proceed with this application. It closes this application only and
                        starts a <b>{{ (int) (config('applications.cooldowns')['Not Selected']['months'] ?? 0) }}-month waiting period before they can apply to this same posting again</b>.
                        Their other applications, and other postings, are not affected.
                    </p>

                    <label class="form-label" for="aiDecideNote">Note <span class="text-muted">(optional, kept with the application)</span></label>
                    <textarea class="form-control form-control-sm" name="note" id="aiDecideNote" rows="3" maxlength="1000"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-secondary" id="aiDecideSubmit">Confirm</button>
                </div>
            </form>
        </div>
    </div>
@endcan

<script>
    // Every application status (config/applications.php). Anything else
    // renders neutral instead of borrowing another status's colour.
    const STATUS_CLASS = {
        'Applied': 'ai-chip-applied',
        'Documents Complete': 'ai-chip-docs-complete',
        'Withdrawn': 'ai-chip-withdrawn',
        'Non-Responsive': 'ai-chip-nonresponsive',
        'Not Selected': 'ai-chip-notselected'
    };

    // HR may withdraw an application for the applicant, or mark it Not
    // Selected (eappprofile view + directedit or hire). Enforced again on the
    // routes; this only decides whether to show the buttons.
    const CAN_DECIDE = @json(Gate::allows('applicant-applications.decide'));
    const DECIDE_URL = {
        withdraw: @json(route('recruitment.applicant-intake.withdraw', ['application' => '__ID__'])),
        notSelected: @json(route('recruitment.applicant-intake.not-selected', ['application' => '__ID__']))
    };

    function shortDate(value) {
        return value ? new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' }) : '';
    }

    // What happened to a closed application, in HR's terms.
    function closureLine(app) {
        if (!app.is_closed) return '';

        let what;
        if (app.status === 'Withdrawn') {
            what = app.closed_by ? 'Withdrawn by HR (' + app.closed_by + ') for the applicant' : 'Withdrawn by the applicant';
        } else if (app.status === 'Non-Responsive') {
            what = 'Document deadline passed with requests outstanding';
        } else if (app.status === 'Not Selected') {
            what = 'Not Selected by ' + (app.closed_by || 'HR');
        } else {
            what = 'Closed';
        }

        let html = '<div class="ai-closure">' + esc(what + ' · ' + shortDate(app.closed_at)) + '</div>';
        if (app.reapply_on) {
            html += '<div class="ai-closure">' + esc('May apply to this posting again from ' + shortDate(app.reapply_on)) + '</div>';
        }
        // A person's note is shown. The deadline job's own note only repeats the
        // line above, so it is left out.
        const systemNote = app.status === 'Non-Responsive' && !app.closed_by;
        if (app.closed_note && !systemNote) {
            html += '<div class="ai-closure">\u201C' + esc(app.closed_note) + '\u201D</div>';
        }
        return html;
    }

    function decisionButtons(app) {
        if (!CAN_DECIDE || app.is_closed) return '';

        const data = ' data-id="' + esc(app.id) + '" data-title="' + esc(app.posting_title) +
            '" data-mr="' + esc(app.mr_no) + '" data-name="' + esc(app.applicant_name) + '"';

        return '<span class="ai-actions d-inline-flex gap-1 me-2">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary js-decide" data-action="withdraw"' + data + '>Withdraw</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger js-decide" data-action="notSelected"' + data + '>Not Selected</button>' +
            '</span>';
    }

    // Single place that turns a status string into a chip, used by both the
    // parent row summary and the expanded child table.
    // The child table and the chips are built as raw HTML, so every value
    // interpolated into them must be escaped. posting_title in particular is
    // operator-supplied through the Job Posting form. DataTables escapes its
    // own columns; this hand-built markup does not.
    function esc(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function statusChip(status) {
        const cls = STATUS_CLASS[status] || 'ai-chip-unknown';
        return '<span class="ai-chip ' + cls + '">' + esc(status) + '</span>';
    }

    function formatApplicantChild(applicant) {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;

        let rows = applicant.applications.map(app => `
            <tr>
                <td>${shortDate(app.applied_at)}</td>
                <td>${esc(app.posting_title)}</td>
                <td>${esc(app.mr_no)}</td>
                <td>
                    ${statusChip(app.status)}
                    ${app.in_candidate_pool ? '<span class="ai-chip ai-chip-pool" title="Retained for this application">Candidate Pool</span>' : ''}
                    ${closureLine(app)}
                </td>
                <td class="text-end text-nowrap">
                    ${decisionButtons(Object.assign({ applicant_name: applicant.applicant_name }, app))}
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
                            <th>MR No.</th>
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
        const baseUrlForDocs = document.querySelector('meta[name="base-url"]').content;

        // Surface load failures in the page instead of a browser alert.
        // Scoped by the fact that this view renders one table.
        $.fn.dataTable.ext.errMode = 'none';

        const table = $('#applicant-intake-table').DataTable({
            ajax: {
                url: urlPrefix + '/recruitment/applicant-intake/data',
                dataSrc: 'data'
            },
            columns: [{
                    className: 'dt-control',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: '<span class="ai-chev" aria-hidden="true">' +
                        '<i class="fa fa-chevron-right"></i></span>'
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
                },
                {
                    // Summarised from the applicant's own applications, so no
                    // extra query is needed. Counts appear only when an
                    // applicant has more than one at the same status.
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        const tally = {};
                        (row.applications || []).forEach(function (app) {
                            tally[app.status] = (tally[app.status] || 0) + 1;
                        });

                        const chips = Object.keys(tally).map(function (status) {
                            const suffix = tally[status] > 1 ? ' \u00D7' + tally[status] : '';
                            const cls = STATUS_CLASS[status] || 'ai-chip-unknown';
                            return '<span class="ai-chip ' + cls + '">' + esc(status) + suffix + '</span>';
                        });

                        return chips.length
                            ? '<span class="ai-chip-stack">' + chips.join('') + '</span>'
                            : '—';
                    }
                }
                @can('applicant-documents.view')
                ,{
                    // Required documents HR has accepted, and anything waiting.
                    data: 'documents',
                    orderable: false,
                    searchable: false,
                    render: function (docs, type, row) {
                        if (!docs) return '—';

                        const cls = docs.complete ? 'ai-chip-approved'
                            : (docs.rejected ? 'ai-chip-rejected' : 'ai-chip-pending');
                        let html = '<a class="text-decoration-none" href="' + baseUrlForDocs + '/applicant/info/' +
                            encodeURIComponent(row.app_id) + '/documents"><span class="ai-chip ' + cls + '">' +
                            esc(docs.accepted + '/' + docs.required + ' accepted') + '</span></a>';

                        const extra = [];
                        if (docs.pending) extra.push(docs.pending + ' to check');
                        if (docs.open_requests) extra.push(docs.open_requests + ' requested');
                        if (extra.length) html += '<div style="font-size:11px;color:var(--zn-ink-3);margin-top:3px">' + esc(extra.join(' · ')) + '</div>';

                        return html;
                    }
                }
                @endcan
            ],
            processing: true,
            language: {
                emptyTable: '<div class="ai-empty"><i class="bi bi-inbox"></i>' +
                    'No applications yet. Applications appear here once someone applies ' +
                    'through the Careers portal.</div>',
                zeroRecords: '<div class="ai-empty"><i class="bi bi-search"></i>' +
                    'No applicants match your search.</div>',
                processing: 'Loading applications…'
            },
            order: [
                [1, 'desc']
            ],
            scrollY: '55vh',
            scrollCollapse: true,
            // DataTables 1.13 does not support the { label, value } object
            // form introduced in 2.x — it stringifies to "[object Object]".
            // The paired-array form works on both.
            lengthMenu: [
                [50, 100, -1],
                [50, 100, 'All']
            ]
        });

        $('#applicant-intake-table').on('error.dt', function (e, settings, techNote, message) {
            console.error('[applicant-intake]', message);
            $('#ai-alert').html(
                '<div class="alert alert-danger">Could not load applications. ' +
                'Please refresh the page — if it keeps happening, your session may have expired.</div>'
            );
        });

        // One dialog for both decisions, filled from the button pressed, so the
        // application being decided is always named in it.
        $('#applicant-intake-table').on('click', '.js-decide', function () {
            const btn = $(this);
            const action = btn.data('action');
            const notSelected = action === 'notSelected';

            $('#aiDecideForm').attr('action', DECIDE_URL[action].replace('__ID__', encodeURIComponent(btn.data('id'))));
            $('#aiDecideTitle').text((notSelected ? 'Mark Not Selected — ' : 'Withdraw application — ') + btn.data('title'));
            $('#aiDecideWho').text(btn.data('name') + ' · ' + btn.data('title') + ' · ' + btn.data('mr'));
            $('#aiDecideWithdrawHelp').prop('hidden', notSelected);
            $('#aiDecideNotSelectedHelp').prop('hidden', !notSelected);
            $('#aiDecideSubmit')
                .text(notSelected ? 'Mark Not Selected' : 'Withdraw application')
                .toggleClass('btn-danger', notSelected)
                .toggleClass('btn-secondary', !notSelected);
            $('#aiDecideNote').val('');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('aiDecideModal')).show();
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
