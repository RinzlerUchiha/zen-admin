<style>
    /* ===== Layout ===== */
    #manpower-list { font-size: var(--zn-fs); }

    .mpr-page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .mpr-page-head h5 {
        font-weight: 800;
        color: var(--zn-ink);
        letter-spacing: -.2px;
        margin: 0;
    }

    /* ===== Tabs (pill style) ===== */
    .mpr-tabs {
        display: flex;
        gap: 6px;
        background: var(--zn-surface-2);
        border-radius: var(--zn-radius-lg);
        padding: 5px;
        margin-bottom: 20px;
        width: fit-content;
        flex-wrap: wrap;
    }

    .mpr-tabs .mpr-tab-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        background: transparent;
        color: var(--zn-ink-2);
        font-weight: 700;
        font-size: var(--zn-fs);
        padding: 8px 16px;
        border-radius: var(--zn-radius-lg);
        cursor: pointer;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .mpr-tabs .mpr-tab-link:hover { color: var(--zn-ink); }

    .mpr-tabs .mpr-tab-link.active {
        background: var(--zn-surface);
        color: var(--zn-accent-dark);
        box-shadow: 0 2px 6px rgba(32, 26, 22,.08);
    }

    .mpr-tab-badge {
        font-size: var(--zn-fs-sm);
        font-weight: 700;
        background: var(--zn-line);
        color: var(--zn-ink-2);
        border-radius: 20px;
        padding: 1px 8px;
        min-width: 20px;
        text-align: center;
    }

    .mpr-tabs .mpr-tab-link.active .mpr-tab-badge {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .mpr-tabs .mpr-tab-change { gap: 6px; }
    .mpr-tabs .mpr-tab-change i { font-size: var(--zn-fs-sm); }

    /* only once something is actually waiting */
    .mpr-tabs .mpr-tab-change.has-open {
        color: var(--zn-accent-dark);
    }

    .mpr-tabs .mpr-tab-change.has-open .mpr-tab-badge {
        background: var(--zn-accent);
        color: var(--zn-on-accent);
    }

    /* ===== Status chip (shared) ===== */
    .mpv-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: var(--zn-fs-sm);
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

    .mpv-chip-draft     { background: var(--zn-surface-2); color: var(--zn-ink-2); }
    .mpv-chip-pending   { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .mpv-chip-approved  { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .mpv-chip-returned  { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .mpv-chip-rejected  { background: var(--zn-warn-soft); color: var(--zn-warn); }
    .mpv-chip-cancelled { background: var(--zn-surface-2); color: var(--zn-ink-2); }

    .mpv-type-chip {
        display: inline-block;
        white-space: nowrap;
        border-radius: var(--zn-radius);
        padding: 3px 10px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
    }

    .mpv-type-replacement { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .mpv-type-additional  { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }

    /* ===== Open Edit/Cancel ask (read-only; decided in HireFlow) ===== */
    .mpr-change-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        border: 1px solid var(--zn-accent);
        border-radius: 20px;
        padding: 3px 11px;
        font-size: var(--zn-fs-sm);
        font-weight: 700;
        color: var(--zn-accent-dark);
        background: var(--zn-accent-soft);
    }

    .mpr-change-chip i { font-size: var(--zn-fs-xs); }

    /* left rail so a flagged card is findable by scanning the list */
    .mpr-card-flagged {
        border-color: var(--zn-accent);
        box-shadow: inset 3px 0 0 var(--zn-accent), 0 1px 3px rgba(32, 26, 22, .04);
    }

    .mpr-change-note {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 12px;
        padding: 11px 14px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-accent-soft);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-ui);
    }

    .mpr-change-note > i {
        margin-top: 3px;
        color: var(--zn-accent);
    }

    .mpr-change-reason {
        margin-top: 4px;
        color: var(--zn-ink);
        white-space: pre-line;
        word-break: break-word;
    }

    .mpr-change-where {
        margin-top: 4px;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
    }

    /* ===== Card list (replaces dense DataTable look) ===== */
    .mpr-card-list { display: flex; flex-direction: column; gap: 10px; }

    .mpr-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        box-shadow: 0 1px 3px rgba(32, 26, 22,.04);
        transition: box-shadow .15s ease, border-color .15s ease;
        overflow: hidden;
    }

    .mpr-card:hover {
        border-color: var(--zn-line-2);
        box-shadow: 0 4px 14px rgba(32, 26, 22,.07);
    }

    .mpr-card-row {
        display: grid;
        grid-template-columns: 40px 1.4fr 1.6fr 1fr 1.1fr 110px;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        cursor: pointer;
    }

    .mpr-toggle-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-sm);
        transition: background .15s ease, color .15s ease, transform .2s ease;
    }

    .mpr-card.mpr-open .mpr-toggle-btn {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
        transform: rotate(90deg);
    }

    .mpr-mrno {
        font-weight: 800;
        color: var(--zn-ink);
        font-size: var(--zn-fs);
    }

    .mpr-date { color: var(--zn-ink-3); font-size: var(--zn-fs-ui); }

    .mpr-requestor { font-weight: 600; color: var(--zn-ink); }
    .mpr-dept { color: var(--zn-ink-2); font-size: var(--zn-fs-ui); }

    .mpr-positions-count {
        font-weight: 700;
        color: var(--zn-ink);
        text-align: center;
    }
    .mpr-positions-count small {
        display: block;
        font-weight: 500;
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-xs);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    /* Subject capsule — opens the request details modal */
    .mpr-subject-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--zn-line);
        background: var(--zn-surface);
        color: var(--zn-ink);
        font-size: var(--zn-fs-ui);
        font-weight: 600;
        border-radius: 999px;
        padding: 5px 13px;
        text-align: left;
        transition: background .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .mpr-subject-btn i {
        font-size: var(--zn-fs-xs);
        color: var(--zn-line-2);
        transition: color .15s ease, transform .15s ease;
    }

    .mpr-subject-btn:hover {
        background: var(--zn-accent-soft);
        border-color: var(--zn-line-2);
        color: var(--zn-accent-dark);
        box-shadow: 0 2px 8px rgba(93, 37, 2,.12);
    }

    .mpr-subject-btn:hover i {
        color: var(--zn-accent-dark);
        transform: translate(1px, -1px);
    }

    .mpr-subject-btn:focus-visible {
        outline: 2px solid var(--zn-accent);
        outline-offset: 2px;
    }

    .mpr-detail-hint {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
        padding: 8px 4px 0;
    }

    .mpr-detail-hint i {
        margin-right: 4px;
        color: var(--zn-line-2);
    }

    /* ===== Expanded detail ===== */
    .mpr-detail-wrap {
        padding: 0 16px 16px;
        display: none;
    }

    .mpr-card.mpr-open .mpr-detail-wrap { display: block; }

    .mpr-detail-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        overflow: hidden;
        background: var(--zn-surface-2);
    }

    table.mpr-detail-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }

    table.mpr-detail-table thead th {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        padding: 8px 12px;
        background: var(--zn-surface-2);
        border-bottom: 1px solid var(--zn-line);
        text-align: left;
    }

    table.mpr-detail-table tbody td:first-child {
        padding-left: 8px;
    }

    table.mpr-detail-table tbody td {
        padding: 8px 12px;
        border-bottom: 1px solid var(--zn-lock-soft);
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink);
    }

    table.mpr-detail-table tbody tr:last-child td { border-bottom: none; }

    /* Clickable position row — opens the request details modal */
    table.mpr-detail-table tbody tr.mpr-pos-row {
        cursor: pointer;
        transition: background .15s ease;
    }

    table.mpr-detail-table tbody tr.mpr-pos-row:hover {
        background: var(--zn-accent-soft);
    }

    table.mpr-detail-table tbody tr.mpr-pos-row:hover .mpr-subject-btn {
        background: var(--zn-surface);
        border-color: var(--zn-line-2);
        color: var(--zn-accent-dark);
        box-shadow: 0 2px 8px rgba(93, 37, 2,.12);
    }

    table.mpr-detail-table tbody tr.mpr-pos-row:hover .mpr-subject-btn i {
        color: var(--zn-accent-dark);
        transform: translate(1px, -1px);
    }

    .mpr-empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--zn-ink-3);
    }

    .mpr-empty-state i { font-size: 28px; margin-bottom: 8px; display: block; }

    /* ===== Modal shell polish ===== */
    #modal-mpr-view .modal-content {
        border-radius: var(--zn-radius-lg);
        border: none;
        box-shadow: 0 12px 32px rgba(32, 26, 22,.16);
        overflow: hidden;
    }

    #modal-mpr-view .modal-header {
        border-bottom: none;
        padding: 22px 26px 4px;
    }

    #modal-mpr-view .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--zn-ink);
        letter-spacing: -.2px;
    }

    #modal-mpr-view .modal-body { padding: 14px 26px 24px; }

    #modal-mpr-view .modal-footer {
        border-top: 1px solid var(--zn-surface-2);
        background: var(--zn-surface-2);
        padding: 14px 26px;
    }

    .mpv-header-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        background: var(--zn-bg);
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 14px 18px;
        margin-bottom: 18px;
    }

    .mpv-header-item .mpv-label {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        margin-bottom: 3px;
    }

    .mpv-header-item .mpv-value {
        font-size: var(--zn-fs);
        font-weight: 700;
        color: var(--zn-ink);
    }

    .mpv-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 18px 0 10px;
        font-weight: 700;
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink);
    }

    .mpv-section-divider .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--zn-accent), var(--zn-accent-dark));
        box-shadow: 0 0 0 3px var(--zn-accent-soft);
    }

    .mpv-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--zn-surface-2);
    }

    .mpv-table-wrap {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        overflow: hidden;
        margin-bottom: 8px;
        box-shadow: 0 2px 8px rgba(32, 26, 22,.04);
    }

    .mpv-table-wrap table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-bottom: 0;
        font-size: var(--zn-fs-ui);
    }

    .mpv-table-wrap thead th {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        padding: 10px 14px;
        border-bottom: 1px solid var(--zn-line);
        background: var(--zn-bg);
        text-align: left;
    }

    .mpv-table-wrap tbody td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--zn-surface-2);
        color: var(--zn-ink);
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .mpv-table-wrap th:nth-child(1), .mpv-table-wrap td:nth-child(1) { width: 130px; }
    .mpv-table-wrap th:nth-child(5), .mpv-table-wrap td:nth-child(5) { white-space: nowrap; width: 90px; }
    .mpv-table-wrap th:nth-child(6), .mpv-table-wrap td:nth-child(6) { width: 32%; }
    .mpv-table-wrap th:nth-child(7), .mpv-table-wrap td:nth-child(7) { white-space: nowrap; width: 50px; text-align: center; }

    .mpv-table-wrap tbody tr:hover { background: var(--zn-accent-soft); }
    .mpv-table-wrap tbody tr:last-child td { border-bottom: none; }

    .mpv-empty-row { padding: 18px; text-align: center; color: var(--zn-ink-3); font-size: var(--zn-fs-ui); }

    .mpv-hireflow-note {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink-3);
    }

    /* ===== Month filter banner (arrives from the dashboard) ===== */
    .mpr-month-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        padding: 8px 12px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-accent-soft);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-ui);
    }

    .mpr-month-filter i { color: var(--zn-accent); }

    .mpr-month-filter button {
        margin-left: auto;
        border: 1px solid var(--zn-accent);
        background: transparent;
        color: var(--zn-accent-dark);
        font-size: var(--zn-fs-sm);
        font-weight: 700;
        border-radius: var(--zn-radius);
        padding: 2px 10px;
        cursor: pointer;
    }

    .mpr-month-filter button:hover {
        background: var(--zn-accent);
        color: var(--zn-on-accent);
    }

    /* ===== Search/controls bar ===== */
    .mpr-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 12px;
    }

    .mpr-toolbar input[type="search"] {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 7px 12px;
        font-size: var(--zn-fs-ui);
        width: 220px;
    }

    /* responsive: stack card grid on small screens */
    @media (max-width: 900px) {
        .mpr-card-row {
            grid-template-columns: 32px 1fr;
            row-gap: 6px;
        }

        .mpr-card-row > *:not(:first-child) {
            grid-column: 2;
        }

        .mpr-positions-count {
            text-align: left;
        }
    }
</style>

<script type="text/javascript">
    let currentStat = 'draft';
    let currentMonth = null; // 'YYYY-MM' or null

    // Tabs this page will honour in ?stat=, so a link from elsewhere in
    // HireFlow can open the list already filtered.
    const MPR_TABS = ['draft', 'pending', 'approved', 'declined', 'cancelled', 'change-pending'];

    /** '' or '?month=YYYY-MM', appended to the list and counts requests. */
    function mprMonthQuery() {
        return currentMonth ? '?month=' + encodeURIComponent(currentMonth) : '';
    }

    function mprShowMonth() {
        if (!currentMonth) {
            $('#mpr-month-filter').hide();
            return;
        }
        const [y, m] = currentMonth.split('-');
        const shown = new Date(Number(y), Number(m) - 1, 1)
            .toLocaleString(undefined, { month: 'long', year: 'numeric' });
        $('#mpr-month-label').text(shown);
        $('#mpr-month-filter').show();
    }

    function mprSelectTab(stat) {
        currentStat = stat;
        $('#mpr-tabs .mpr-tab-link').removeClass('active');
        $(`#mpr-tabs .mpr-tab-link[data-stat="${stat}"]`).addClass('active');
        load_manpower_list(stat);
    }

    $(function() {
        const params = new URLSearchParams(window.location.search);
        const wanted = params.get('stat');
        const month = params.get('month');

        currentMonth = /^\d{4}-(0[1-9]|1[0-2])$/.test(month || '') ? month : null;
        mprShowMonth();

        // Arriving with only ?month= means "show me that month", and the
        // approved requests are the ones worth landing on.
        const startStat = MPR_TABS.includes(wanted) ? wanted : (currentMonth ? 'approved' : 'draft');

        $('#mpr-month-clear').on('click', function () {
            currentMonth = null;
            mprShowMonth();
            load_counts();
            load_manpower_list(currentStat);
        });

        load_counts();
        mprSelectTab(startStat);

        $('#modal-mpr-view').on('shown.bs.modal', async function(e) {
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

        const change = data.pending_change || null;
        if (change) {
            $('#mpr-view-change-title').text(
                (change.change_type === 'cancel' ? 'Cancel' : 'Edit') + ' requested'
            );
            $('#mpr-view-change-when').text(
                change.created_at ? ' — asked on ' + mprFormatDateTime(change.created_at) + '.' : '.'
            );
            $('#mpr-view-change-reason').text(
                (change.reason || '').trim() !== '' ? change.reason : 'No reason given.'
            );
            $('#mpr-view-change').show();
        } else {
            $('#mpr-view-change').hide();
        }

        let positions = data.positions || [];
        $('#mpr-view-position-rows').html(mpvRenderAllRows(positions));
    }

    /**
     * MySQL DATETIME comes back as "YYYY-MM-DD HH:MM:SS"; Safari refuses that
     * in new Date(), so normalise before formatting and fall back to the raw
     * string rather than printing "Invalid Date".
     */
    function mprFormatDateTime(value) {
        const parsed = new Date(String(value).replace(' ', 'T'));
        if (isNaN(parsed)) { return value; }
        return parsed.toLocaleString(undefined, {
            year: 'numeric', month: 'short', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function load_counts() {
        fetch('/recruitment/manpower/counts' + mprMonthQuery())
            .then(res => res.json())
            .then(data => {
                MPR_TABS.forEach(stat => {
                    $(`#mpr-badge-${stat}`).text(data[stat] ?? 0);
                });

                // Highlight the Edit/Cancel tab only while something is open.
                $('#mpr-tabs .mpr-tab-change').toggleClass('has-open', (data['change-pending'] ?? 0) > 0);
            });
    }

    function load_manpower_list(stat) {
        currentStat = stat;
        $('#manpower-list').html('<div class="mpr-empty-state"><i class="bi bi-hourglass-split"></i>Loading…</div>');

        fetch('/recruitment/manpower/list/' + stat + mprMonthQuery())
            .then(res => res.text())
            .then(html => {
                $('#manpower-list').html(html);

                // Card expand/collapse — reads from the <template> partial per card.
                $('#manpower-list').off('click.mprToggle').on('click.mprToggle', '.mpr-card-row', function(e) {
                    if ($(e.target).closest('[data-bs-toggle="modal"]').length) return;

                    const $card = $(this).closest('.mpr-card');
                    const $wrap = $card.find('.mpr-detail-wrap');

                    if ($card.hasClass('mpr-open')) {
                        $card.removeClass('mpr-open');
                        return;
                    }

                    if (!$wrap.data('loaded')) {
                        const tpl = document.getElementById('mpr-positions-' + $card.data('id'));
                        $wrap.html(tpl ? tpl.innerHTML : '<p class="text-muted small mb-0">No positions on this request.</p>');
                        $wrap.data('loaded', true);
                    }

                    $card.addClass('mpr-open');
                });

                // Position row → request details modal.
                // The capsule button is the real Bootstrap trigger (and the
                // keyboard path); clicking anywhere else on the row forwards
                // to it, so the modal only ever opens once.
                $('#manpower-list').off('click.mprRow').on('click.mprRow', '.mpr-pos-row', function(e) {
                    if ($(e.target).closest('.mpr-subject-btn').length) return;

                    const btn = this.querySelector('.mpr-subject-btn');
                    if (btn) btn.click();
                });

                // simple client-side filter (search box)
                $('#mpr-search').off('input').on('input', function() {
                    const q = $(this).val().toLowerCase();
                    $('.mpr-card').each(function() {
                        const text = $(this).text().toLowerCase();
                        $(this).toggle(text.includes(q));
                    });
                });
            })
            .catch(err => console.error('Error fetching the list:', err));
    }
</script>

<div class="container-fluid">
    <div class="mpr-page-head">
        <h5>Manpower Requests</h5>
    </div>

    <ul class="mpr-tabs list-unstyled mb-0" id="mpr-tabs">
        @foreach (['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'declined' => 'Declined', 'cancelled' => 'Cancelled'] as $stat => $label)
            <li class="d-inline-block m-0">
                <button type="button" class="mpr-tab-link" data-stat="{{ $stat }}"
                    onclick="mprSelectTab('{{ $stat }}');">
                    {{ $label }} <span class="mpr-tab-badge" id="mpr-badge-{{ $stat }}">0</span>
                </button>
            </li>
        @endforeach

        {{-- Not a status: approved requests whose Requestor is waiting on an
             edit/cancel decision. HR needs to spot these; the decision itself
             stays with the Approver in HireFlow. --}}
        <li class="d-inline-block m-0">
            <button type="button" class="mpr-tab-link mpr-tab-change" data-stat="change-pending"
                onclick="mprSelectTab('change-pending');"
                title="Approved requests waiting on an edit or cancel decision">
                <i class="fa fa-clock"></i> Edit/Cancel
                <span class="mpr-tab-badge" id="mpr-badge-change-pending">0</span>
            </button>
        </li>
    </ul>

    {{-- Set by the dashboard's "requests raised" chart. It sits across every
         tab, and the badges honour it too, so a count never disagrees with
         the list under it. --}}
    <div class="mpr-month-filter" id="mpr-month-filter" style="display:none;">
        <i class="fa fa-calendar-day"></i>
        <span>Showing requests raised in <strong id="mpr-month-label"></strong></span>
        <button type="button" id="mpr-month-clear">Clear</button>
    </div>

    <div class="mpr-toolbar">
        <input type="search" id="mpr-search" placeholder="Search requestor, MR no, dept…">
    </div>

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

                <div class="mpr-change-note" id="mpr-view-change" style="display:none;">
                    <i class="fa fa-clock"></i>
                    <div>
                        <strong id="mpr-view-change-title"></strong>
                        <span id="mpr-view-change-when"></span>
                        <div class="mpr-change-reason" id="mpr-view-change-reason"></div>
                        <div class="mpr-change-where">
                            Waiting on the Requestor&rsquo;s Approver in HireFlow. Approve or decline it there.
                        </div>
                    </div>
                </div>

                <div class="mpv-section-divider"><span class="dot"></span> Positions</div>
                <div class="mpv-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Subject/Position</th>
                                <th>Number Needed</th>
                                <th>Reason</th>
                                <th>Date Needed</th>
                                <th>Non-Negotiable</th>
                                <th>Fill</th>
                            </tr>
                        </thead>
                        <tbody id="mpr-view-position-rows"></tbody>
                    </table>
                </div>

                <div class="mpv-hireflow-note">
                    <i class="bi bi-info-circle"></i>
                    This request is managed in HireFlow. To edit, cancel, or take action on it, please use HireFlow
                    directly.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>