

<style>
    #manpower-list {
        font-size: 13px;
    }
    .mpr-tab-badge {
        font-size: 11px;
        margin-left: 4px;
    }

    /* Modal shell polish */
    #modal-mpr-view .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 12px 32px rgba(31, 36, 48, .16);
        overflow: hidden;
    }
    #modal-mpr-view .modal-header {
        border-bottom: none;
        padding: 22px 26px 4px;
    }
    #modal-mpr-view .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.2px;
    }
    #modal-mpr-view .modal-body {
        padding: 14px 26px 24px;
    }
    #modal-mpr-view .modal-footer {
        border-top: 1px solid #F1F2F5;
        background: #FAFBFC;
        padding: 14px 26px;
    }

    /* Header info card */
    .mpv-header-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        background: #F5F6F9;
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 18px;
    }
    .mpv-header-item .mpv-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-bottom: 3px;
    }
    .mpv-header-item .mpv-value {
        font-size: 14px;
        font-weight: 700;
        color: #1F2430;
    }

    /* Status chip */
    .mpv-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2px;
        box-shadow: 0 2px 6px rgba(31, 36, 48, .06);
    }
    .mpv-chip::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }
    .mpv-chip-draft     { background: #F1F2F5; color: #5B6474; }
    .mpv-chip-pending   { background: #E8F0FE; color: #1B4FB0; }
    .mpv-chip-approved  { background: #E7F6EC; color: #1E9E4C; }
    .mpv-chip-returned  { background: #FFF1EC; color: #5C2A18; }
    .mpv-chip-rejected  { background: #FCEBEB; color: #791F1F; }
    .mpv-chip-cancelled { background: #F1F2F5; color: #5B6474; }

    .mpv-type-chip {
        display: inline-block;
        white-space: nowrap;
        border-radius: 6px;
        padding: 3px 10px;
        font-size: 10.5px;
        font-weight: 700;
    }
    .mpv-type-replacement { background: #F1EEFE; color: #6A4FE0; }
    .mpv-type-additional  { background: #E8F0FE; color: #1B4FB0; }

    /* Section dividers */
    .mpv-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 18px 0 10px;
        font-weight: 700;
        font-size: 12.5px;
        color: #1F2430;
    }
    .mpv-section-divider .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2F6FE4, #1B4FB0);
        box-shadow: 0 0 0 3px #E8F0FE;
    }
    .mpv-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #F1F2F5;
    }

    /* Position tables */
    .mpv-table-wrap {
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 8px;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
    }
    .mpv-table-wrap table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-bottom: 0;
        font-size: 12.5px;
    }
    .mpv-table-wrap thead th {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #8A93A3;
        padding: 10px 14px;
        border-bottom: 1px solid #E7E9EE;
        background: #F5F6F9;
        text-align: left;
    }
    .mpv-table-wrap tbody td {
        padding: 10px 14px;
        border-bottom: 1px solid #F1F2F5;
        color: #1F2430;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .mpv-table-wrap th:nth-child(1),
    .mpv-table-wrap td:nth-child(1) {
        width: 130px;
    }
    .mpv-table-wrap th:nth-child(5),
    .mpv-table-wrap td:nth-child(5) {
        white-space: nowrap;
        width: 90px;
    }
    .mpv-table-wrap th:nth-child(6),
    .mpv-table-wrap td:nth-child(6) {
        width: 32%;
    }
    .mpv-table-wrap th:nth-child(7),
    .mpv-table-wrap td:nth-child(7) {
        white-space: nowrap;
        width: 50px;
        text-align: center;
    }
    .mpv-table-wrap tbody tr:hover {
        background: #FAFBFF;
    }
    .mpv-table-wrap tbody tr:last-child td {
        border-bottom: none;
    }
    .mpv-empty-row {
        padding: 18px;
        text-align: center;
        color: #B0B6C0;
        font-size: 12px;
    }

    /* Non-negotiable block */
    .mpv-nn-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-top: 18px;
    }
    .mpv-nn-value {
        background: #F5F6F9;
        border: 1px solid #E7E9EE;
        border-radius: 10px;
        padding: 10px 14px;
        margin-top: 6px;
        font-size: 13px;
        color: #1F2430;
    }

    .mpv-hireflow-note {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        font-size: 12px;
        color: #8A93A3;
    }
</style>

<script type="text/javascript">
    let currentStat = 'draft';

    $(function () {
        load_counts();
        load_manpower_list(currentStat);

        $('#modal-mpr-view').on('shown.bs.modal', async function (e) {
            let btn = $(e.relatedTarget);
            let id = btn.data('id');
            $('#mpr-view-id').val(id);

            try {
                const response = await fetch('/recruitment/manpower/' + id);
                const data = await response.json();
                populateMprView(data);
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load request.');
            }
        });

    });

    const MP_STATUS_CLASS = {
        'Draft': 'mpv-chip-draft',
        'Pending': 'mpv-chip-pending',
        'Approved': 'mpv-chip-approved',
        'Returned': 'mpv-chip-returned',
        'Rejected': 'mpv-chip-rejected',
        'Cancelled': 'mpv-chip-cancelled'
    };

    function mpvRenderRows(rows) {
        if (rows.length === 0) {
            return '<tr><td colspan="5" class="mpv-empty-row">No positions added.</td></tr>';
        }
        return rows.map(p => `
            <tr>
                <td>${p.position_title || p.position}</td>
                <td>${p.headcount}</td>
                <td>${p.reason || '—'}</td>
                <td>${p.date_needed || '—'}</td>
                <td>${p.filled ?? 0}</td>
            </tr>
        `).join('');
    }

    const MP_TYPE_CLASS = {
        'replacement': 'mpv-type-replacement',
        'additional': 'mpv-type-additional'
    };

    function mpvRenderAllRows(rows) {
        if (rows.length === 0) {
            return '<tr><td colspan="7" class="mpv-empty-row">No positions added.</td></tr>';
        }
        return rows.map(p => `
            <tr>
                <td><span class="mpv-type-chip ${MP_TYPE_CLASS[p.type] || ''}">${p.type ? p.type.charAt(0).toUpperCase() + p.type.slice(1) : '—'}</span></td>
                <td>${p.position_title || p.position}</td>
                <td>${p.headcount}</td>
                <td>${p.reason || '—'}</td>
                <td>${p.date_needed || '—'}</td>
                <td>${p.nonnegotiable || '—'}</td>
                <td>${p.filled ?? 0}</td>
            </tr>
        `).join('');
    }

    function populateMprView(data) {
        $('#mpr-view-mrno').text(data.mr_no || '—');
        $('#mpr-view-requestor').text(data.requestor_name || '—');
        $('#mpr-view-dept').text(data.requestor_dept || '—');

        $('#mpr-view-status')
            .text(data.status)
            .attr('class', 'mpv-chip ' + (MP_STATUS_CLASS[data.status] || 'mpv-chip-draft'));

        let positions = data.positions || [];
        $('#mpr-view-position-rows').html(mpvRenderAllRows(positions));
    }

    function load_counts() {
        fetch('/recruitment/manpower/counts')
            .then(res => res.json())
            .then(data => {
                ['draft', 'pending', 'approved', 'declined', 'cancelled'].forEach(stat => {
                    $(`#mpr-badge-${stat}`).text(data[stat] ?? 0);
                });
            });
    }

    function load_manpower_list(stat) {
        currentStat = stat;
        $('#manpower-list').html('Loading...');

        fetch('/recruitment/manpower/list/' + stat)
            .then(res => res.text())
            .then(html => {
                $('#manpower-list').html(html);
                $('#manpower-list > table').DataTable({
                    scrollY: '55vh',
                    scrollCollapse: true,
                    lengthMenu: [50, 100, { label: 'All', value: -1 }],
                    ordering: false
                });
            })
            .catch(err => console.error('Error fetching the list:', err));
    }
</script>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>Manpower Requests</h5>
    </div>

    <ul class="nav nav-tabs mb-2" id="mpr-tabs">
        @foreach (['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'declined' => 'Declined', 'cancelled' => 'Cancelled'] as $stat => $label)
            <li class="nav-item">
                <a class="nav-link {{ $stat == 'draft' ? 'active' : '' }}" href="#" onclick="load_manpower_list('{{ $stat }}'); $('#mpr-tabs .nav-link').removeClass('active'); $(this).addClass('active'); return false;">
                    {{ $label }} <span class="badge bg-secondary mpr-tab-badge" id="mpr-badge-{{ $stat }}">0</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div id="manpower-list"></div>
</div>

<!-- View Modal: read-only -->
<div class="modal fade" id="modal-mpr-view" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1300px;">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Request Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mpr-view-id" value="">

                <div class="mpv-header-card">
                    <div class="mpv-header-item">
                        <div class="mpv-label">Requestor</div>
                        <div class="mpv-value" id="mpr-view-requestor"></div>
                    </div>
                    <div class="mpv-header-item">
                        <div class="mpv-label">Department</div>
                        <div class="mpv-value" id="mpr-view-dept"></div>
                    </div>
                    <div class="mpv-header-item">
                        <div class="mpv-label">MR No.</div>
                        <div class="mpv-value" id="mpr-view-mrno"></div>
                    </div>
                    <span id="mpr-view-status" class="mpv-chip"></span>
                </div>

                <div class="mpv-section-divider"><span class="dot"></span> Positions</div>
                <div class="mpv-table-wrap">
                    <table>
                        <thead>
                            <tr><th>Type</th><th>Subject/Position</th><th>Number Needed</th><th>Reason</th><th>Date Needed</th><th>Non-Negotiable</th><th>Fill</th></tr>
                        </thead>
                        <tbody id="mpr-view-position-rows"></tbody>
                    </table>
                </div>

                <div class="mpv-hireflow-note">
                    <i class="bi bi-info-circle"></i>
                    This request is managed in HireFlow. To edit, cancel, or take action on it, please use HireFlow directly.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

