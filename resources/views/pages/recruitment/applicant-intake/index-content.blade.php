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
        font-size: var(--zn-fs-sm);
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
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
        margin-top: 3px;
        max-width: 42ch;
    }

    .ai-actions .btn {
        font-size: var(--zn-fs-sm);
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
        font-size: var(--zn-fs-xs);
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
        font-size: var(--zn-fs);
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

    /* ===== Filters ===== */
    .ai-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .ai-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ai-filter {
        border: 1px solid var(--zn-line);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: 999px;
        padding: 5px 13px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
    }

    .ai-filter:hover {
        border-color: var(--zn-accent);
        color: var(--zn-accent);
    }

    .ai-filter.is-on {
        background: var(--zn-accent);
        border-color: var(--zn-accent);
        color: var(--zn-on-accent);
    }

    #ai-posting-filter {
        width: auto;
        min-width: 190px;
    }

    /* ===== Closing an application ===== */
    .ai-close-menu .dropdown-menu {
        max-width: 320px;
    }

    .ai-close-menu .dropdown-item {
        white-space: normal;
        padding: 8px 14px;
    }

    .ai-close-menu .dropdown-item b {
        display: block;
        font-size: var(--zn-fs);
    }

    .ai-close-menu .dropdown-item small {
        display: block;
        color: var(--zn-ink-3);
    }

    /* ===== Access code requests ===== */
    .ai-access-requests {
        background: var(--zn-caution-soft);
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 10px 14px;
        margin-bottom: 12px;
        font-size: var(--zn-fs-ui);
    }

    .ai-access-requests-head {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--zn-ink);
    }

    .ai-access-requests-head i {
        color: var(--zn-caution);
    }

    .ai-access-requests ul {
        margin: 6px 0 0;
        padding-left: 26px;
    }

    .ai-access-requests li span {
        color: var(--zn-ink-3);
    }

    /* ===== Table card wrap ===== */
    .ai-table-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        /* auto, not hidden: below roughly 1000px the last columns were being
           cut off with no way to scroll to them */
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(32, 26, 22,.04);
        background: var(--zn-surface);
        padding: 4px 4px 0;
    }

    /* Fewer, wider columns now, so it needs less room before scrolling. */
    #applicant-intake-table { min-width: 720px; }

    /* ===== Applicant cards =====
       One card per person. The person leads; their applications, documents
       and status sit with them instead of being spread across nine columns.
    */
    .ai-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin: 4px 0 12px;
    }

    .ai-result-count { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); margin-right: auto; }

    .ai-search-wrap { position: relative; display: inline-flex; align-items: center; }

    .ai-search-wrap input[type="search"] {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 7px 28px 7px 12px;
        font-size: var(--zn-fs-ui);
        width: 280px;
        max-width: 100%;
    }

    .ai-search-wrap #ai-search-clear {
        position: absolute;
        right: 6px;
        border: none;
        background: transparent;
        color: var(--zn-ink-3);
        font-size: 18px;
        line-height: 1;
        padding: 2px 6px;
        cursor: pointer;
        border-radius: var(--zn-radius);
    }

    .ai-search-wrap #ai-search-clear:hover { background: var(--zn-surface-2); color: var(--zn-ink); }

    .ai-list { display: flex; flex-direction: column; gap: 10px; }

    .ai-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        box-shadow: 0 1px 3px rgba(32, 26, 22, .04);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .ai-card:hover, .ai-card.is-open { border-color: var(--zn-accent); }

    .ai-card-main {
        display: grid;
        grid-template-columns: 42px minmax(0, 1.6fr) minmax(0, 1fr) minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        cursor: pointer;
    }

    .ai-card-main:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: -2px; }

    .ai-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
        font-weight: 800;
        font-size: var(--zn-fs-sm);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .ai-card-who { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .ai-card-name { font-weight: 700; color: var(--zn-ink); }

    .ai-card-sub {
        display: block;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
        overflow-wrap: anywhere;
    }

    .ai-card-state { display: flex; flex-wrap: wrap; gap: 4px; }
    .ai-card-docs { display: flex; flex-direction: column; gap: 2px; }
    .ai-doc-link { text-decoration: none; }
    .ai-card-go { justify-self: end; }

    .ai-card-detail {
        border-top: 1px solid var(--zn-line);
        border-bottom-left-radius: var(--zn-radius-lg);
        border-bottom-right-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        padding: 12px 16px;
    }

    .ai-loading { padding: 28px; text-align: center; color: var(--zn-ink-3); }

    .ai-card-detail .applicant-intake-child-table { min-width: 640px; }

    @media (max-width: 991.98px) {
        /* Only now is there too little room for the table: let it scroll
           rather than squeeze. .is-menu-open lifts the clip while a
           dropdown inside it is showing. */
        .ai-card-detail .applicant-intake-child-card { overflow-x: auto; }
        .ai-card-detail .applicant-intake-child-card.is-menu-open { overflow: visible; }

        .ai-card-main {
            grid-template-columns: 42px minmax(0, 1fr);
            row-gap: 10px;
        }

        .ai-card-state, .ai-card-docs, .ai-card-go {
            grid-column: 2;
            justify-self: start;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
    }

    /* ===== Applicant cell: the person, not three columns ===== */
    .ai-person { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .ai-person-name { font-weight: 700; color: var(--zn-ink); }
    .ai-person-contact {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
        overflow-wrap: anywhere;
    }

    .ai-activity { display: flex; flex-direction: column; gap: 1px; }
    .ai-activity-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }
    .ai-none { color: var(--zn-ink-3); font-size: var(--zn-fs-sm); }

    /* ===== Narrow screens ===== */
    @media (max-width: 991.98px) {
        .ai-filters {
            align-items: stretch;
            justify-content: flex-start;
        }

        .ai-filters .dataTables_filter,
        .ai-filters input[type="search"] {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .ai-filter-chips { width: 100%; }

        .ai-filter {
            flex: 1 1 auto;
            text-align: center;
        }

        .ai-page-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }

    #applicant-intake-table {
        border-collapse: collapse !important;
        font-size: var(--zn-fs);
    }

    #applicant-intake-table thead th {
        font-size: var(--zn-fs-xs);
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
        font-size: var(--zn-fs-xs);
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
        background: var(--zn-surface);
    }

    .applicant-intake-child-table tr:first-child th:first-child,
    .applicant-intake-child-table tr:first-child td:first-child {
        border-top-left-radius: var(--zn-radius-lg);
    }

    .applicant-intake-child-table tr:first-child th:last-child,
    .applicant-intake-child-table tr:first-child td:last-child {
        border-top-right-radius: var(--zn-radius-lg);
    }

    .applicant-intake-child-table tr:last-child td:first-child {
        border-bottom-left-radius: var(--zn-radius-lg);
    }

    .applicant-intake-child-table tr:last-child td:last-child {
        border-bottom-right-radius: var(--zn-radius-lg);
    }

    table.applicant-intake-child-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .applicant-intake-child-table th {
        font-size: var(--zn-fs-xs);
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
        font-size: var(--zn-fs-ui);
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
        font-size: var(--zn-fs-sm);
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
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink-2);
    }

    #applicant-intake-table_wrapper .dataTables_length select,
    #applicant-intake-table_wrapper .dataTables_filter input {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 5px 10px;
        font-size: var(--zn-fs-ui);
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

    @if (($accessRequests ?? collect())->isNotEmpty())
        {{-- Applicants whose assessment access ended and who asked for a new
             code. Issuing one from their Assessment access tab answers it. --}}
        <div class="ai-access-requests" role="region" aria-label="Access code requests">
            <div class="ai-access-requests-head">
                <i class="bi bi-send-exclamation"></i>
                <b>{{ $accessRequests->count() }} {{ Str::plural('applicant', $accessRequests->count()) }} asked for a new assessment access code</b>
            </div>
            <ul>
                @foreach ($accessRequests as $req)
                    <li>
                        <a href="{{ route('applicant.show', ['id' => $req->app_id, 'tab' => 'assessment-access']) }}">
                            {{ trim($req->app_lname . ', ' . $req->app_fname, ', ') }}</a>
                        <span>· {{ config('applicant_assessments.request_reasons.' . $req->reason, $req->reason) }}
                            @if ($req->assessment)
                                ({{ config('applicant_assessments.list.' . $req->assessment . '.label', $req->assessment) }})
                            @endif
                            · {{ \Illuminate\Support\Carbon::parse($req->requested_at)->format('M j, g:i A') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ai-filters" role="group" aria-label="Filter applications">
        <div class="ai-filter-chips">
            <button type="button" class="ai-filter is-on" data-filter="all">All applicants</button>
            <button type="button" class="ai-filter" data-filter="needs-action">Needs action</button>
            <button type="button" class="ai-filter" data-filter="open">With an open application</button>
            <button type="button" class="ai-filter" data-filter="closed">Closed only</button>
            <button type="button" class="ai-filter" data-filter="pool">In the Candidate Pool</button>
            {{-- People who registered on the portal but never applied. They
                 exist in tblapp_persinfo with no application rows. --}}
            <button type="button" class="ai-filter" data-filter="no-application">Registered, no application</button>
        </div>
        <select id="ai-posting-filter" class="form-select form-select-sm" aria-label="Filter by position">
            <option value="">Every position</option>
        </select>
    </div>

    <div class="ai-toolbar">
        <span class="ai-result-count" id="ai-result-count"></span>
        <div class="ai-search-wrap">
            <input type="search" id="ai-search" aria-label="Search applicants"
                placeholder="Search name, email, position, MR no…">
            <button type="button" id="ai-search-clear" style="display:none;" aria-label="Clear search">&times;</button>
        </div>
    </div>

    {{-- One card per person. The nine-column table scattered a single
         applicant across the width of the screen; here the person is the
         unit and their applications sit underneath them. --}}
    <div id="ai-list" class="ai-list" aria-live="polite"></div>

    <div class="ai-empty" id="ai-empty" style="display:none;"></div>

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
                <div class="modal-body" style="font-size:var(--zn-fs)">
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
    const COOLDOWN_MONTHS = @json((int) (config('applications.cooldowns')['Not Selected']['months'] ?? 0));
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

    // Closing ONE application. Both outcomes live behind one menu, each
    // spelling out what it does, because the difference between them is the
    // waiting period and not the wording "withdraw" vs "not selected".
    function decisionButtons(app) {
        if (!CAN_DECIDE || app.is_closed) return '';

        const data = ' data-id="' + esc(app.id) + '" data-title="' + esc(app.posting_title) +
            '" data-mr="' + esc(app.mr_no) + '" data-name="' + esc(app.applicant_name) + '"';

        return '<div class="dropdown ai-close-menu">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' +
            'Close application</button>' +
            '<ul class="dropdown-menu dropdown-menu-end">' +
            '<li><button type="button" class="dropdown-item js-decide" data-action="withdraw"' + data + '>' +
            '<b>Applicant withdrew</b><small>They asked to stop this application. No waiting period.</small></button></li>' +
            '<li><button type="button" class="dropdown-item js-decide" data-action="notSelected"' + data + '>' +
            '<b>Not Selected</b><small>Our decision. ' + COOLDOWN_MONTHS + '-month wait before they can apply to this posting again.</small></button></li>' +
            '</ul></div>';
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

        let ALL = [];              // every applicant, as returned by the feed
        let rowFilter = 'all';
        let postingFilter = '';

        // ?posting=<title> arrives from a job posting's panel.
        const wantedPosting = new URLSearchParams(window.location.search).get('posting');

        function initials(name) {
            return (name || '?').split(/\s+/).filter(Boolean).slice(0, 2)
                .map(w => w[0].toUpperCase()).join('');
        }

        /** Documents, as a chip plus what is outstanding. */
        function docsCell(row) {
            const docs = row.documents;
            if (!docs) { return ''; }

            const cls = docs.complete ? 'ai-chip-approved'
                : (docs.rejected ? 'ai-chip-rejected' : 'ai-chip-pending');
            let html = '<a class="ai-doc-link" href="' + baseUrlForDocs + '/applicant/info/' +
                encodeURIComponent(row.app_id) + '/documents"><span class="ai-chip ' + cls + '">' +
                esc(docs.accepted + '/' + docs.required + ' documents') + '</span></a>';

            const extra = [];
            if (docs.pending) { extra.push(docs.pending + ' to check'); }
            if (docs.open_requests) { extra.push(docs.open_requests + ' requested'); }
            if (extra.length) { html += '<span class="ai-card-sub">' + esc(extra.join(' · ')) + '</span>'; }

            return html;
        }

        /** The status chips for a person: one per distinct application status. */
        function statusCell(row) {
            const apps = row.applications || [];
            if (!apps.length) {
                return '<span class="ai-chip ai-chip-unknown">No application yet</span>';
            }

            const tally = {};
            apps.forEach(a => { tally[a.status] = (tally[a.status] || 0) + 1; });

            return Object.keys(tally).map(function (status) {
                const suffix = tally[status] > 1 ? ' \u00D7' + tally[status] : '';
                const cls = STATUS_CLASS[status] || 'ai-chip-unknown';
                return '<span class="ai-chip ' + cls + '">' + esc(status) + suffix + '</span>';
            }).join('') +
            (apps.some(a => a.in_candidate_pool)
                ? '<span class="ai-chip ai-chip-pool">Candidate Pool</span>' : '');
        }

        function activityLine(row) {
            if (!row.application_count) {
                const reg = shortDate(row.registered_at);
                return reg ? 'Registered ' + esc(reg) : 'Registered';
            }
            const last = shortDate(row.latest_applied_at);
            return row.application_count +
                (row.application_count === 1 ? ' application' : ' applications') +
                (last ? ' · last ' + esc(last) : '');
        }

        function cardHtml(row) {
            return '' +
            '<article class="ai-card" data-app-id="' + esc(row.app_id) + '">' +
              '<div class="ai-card-main" role="button" tabindex="0" aria-expanded="false">' +
                '<span class="ai-avatar" aria-hidden="true">' + esc(initials(row.applicant_name)) + '</span>' +
                '<div class="ai-card-who">' +
                  '<span class="ai-card-name">' + esc(row.applicant_name) + '</span>' +
                  '<span class="ai-card-sub">' +
                    [row.app_email, row.app_mobile].filter(Boolean).map(esc).join(' · ') +
                  '</span>' +
                  '<span class="ai-card-sub">' + activityLine(row) + '</span>' +
                '</div>' +
                '<div class="ai-card-state">' + statusCell(row) + '</div>' +
                '<div class="ai-card-docs">' + docsCell(row) + '</div>' +
                '<div class="ai-card-go">' +
                  '<a class="btn btn-sm btn-outline-primary" href="' + baseUrlForDocs +
                    '/applicant/info/' + encodeURIComponent(row.app_id) + '">Open profile</a>' +
                '</div>' +
              '</div>' +
              '<div class="ai-card-detail" hidden></div>' +
            '</article>';
        }

        /** The filters, applied to one applicant. */
        function passes(row) {
            const apps = row.applications || [];

            if (postingFilter && !apps.some(a => a.posting_title === postingFilter)) { return false; }

            switch (rowFilter) {
                case 'no-application': return apps.length === 0;
                case 'open':   return apps.some(a => !a.is_closed);
                case 'closed': return apps.length > 0 && apps.every(a => a.is_closed);
                case 'pool':   return apps.some(a => a.in_candidate_pool);
                case 'needs-action': {
                    const d = row.documents;
                    return apps.some(a => !a.is_closed)
                        && !!d && (d.pending > 0 || d.open_requests > 0 || !d.complete);
                }
                default: return true;   // "All" keeps registered-only accounts
            }
        }

        function matchesSearch(row, q) {
            if (!q) { return true; }
            const hay = [row.applicant_name, row.app_email, row.app_mobile]
                .concat((row.applications || []).map(a => a.posting_title))
                .concat((row.applications || []).map(a => a.mr_no))
                .join(' ').toLowerCase();
            return hay.includes(q);
        }

        function render() {
            const q = ($('#ai-search').val() || '').trim().toLowerCase();
            const rows = ALL.filter(r => passes(r) && matchesSearch(r, q));

            $('#ai-list').html(rows.map(cardHtml).join(''));
            $('#ai-search-clear').toggle(q !== '');
            $('#ai-result-count').text(
                rows.length === ALL.length
                    ? ALL.length + (ALL.length === 1 ? ' applicant' : ' applicants')
                    : 'Showing ' + rows.length + ' of ' + ALL.length
            );

            const $empty = $('#ai-empty');
            if (rows.length) {
                $empty.hide();
            } else {
                $empty.html('<i class="bi bi-' + (ALL.length ? 'search' : 'inbox') + '"></i>' +
                    (ALL.length
                        ? 'No applicant matches this filter or search.'
                        : 'Nobody here yet. Applicants appear once they register on the Careers portal, ' +
                          'whether or not they have applied.')).show();
            }
        }

        function load() {
            $('#ai-list').html('<div class="ai-loading">Loading applicants…</div>');

            $.getJSON(urlPrefix + '/recruitment/applicant-intake/data')
                .done(function (json) {
                    ALL = json.data || [];

                    // The positions on offer come from the loaded applications.
                    const titles = [...new Set(ALL.flatMap(r => (r.applications || [])
                        .map(a => a.posting_title)))].filter(Boolean).sort();
                    const select = $('#ai-posting-filter');
                    const current = select.val();
                    select.find('option:not(:first)').remove();
                    titles.forEach(t => select.append($('<option>').val(t).text(t)));
                    if (current) { select.val(current); }

                    if (wantedPosting && titles.includes(wantedPosting)) {
                        select.val(wantedPosting);
                        postingFilter = wantedPosting;
                    }

                    render();
                })
                .fail(function (xhr) {
                    console.error('[applicant-intake]', xhr.status, xhr.responseText);
                    $('#ai-list').empty();
                    $('#ai-alert').html(
                        '<div class="alert alert-danger">Could not load applicants. ' +
                        'Please refresh the page — if it keeps happening, your session may have expired.</div>'
                    );
                });
        }

        $('.ai-filter').on('click', function () {
            $('.ai-filter').removeClass('is-on').attr('aria-pressed', 'false');
            $(this).addClass('is-on').attr('aria-pressed', 'true');
            rowFilter = $(this).data('filter');
            render();
        });

        $('#ai-posting-filter').on('change', function () {
            postingFilter = this.value;
            render();
        });

        $('#ai-search').on('input', render);
        $('#ai-search-clear').on('click', function () {
            $('#ai-search').val('').trigger('input').focus();
        });

        // Expanding a card shows that person's applications and the decision
        // menu — the same markup the old child row used.
        $('#ai-list').on('click', '.ai-card-main', function (e) {
            if ($(e.target).closest('a, button, .dropdown').length) { return; }
            const $card = $(this).closest('.ai-card');
            const $detail = $card.find('.ai-card-detail');
            const open = !$detail.prop('hidden');

            if (open) {
                $detail.prop('hidden', true).empty();
            } else {
                const row = ALL.find(r => String(r.app_id) === String($card.data('app-id')));
                $detail.html(row ? formatApplicantChild(row) : '').prop('hidden', false);
            }
            $(this).attr('aria-expanded', open ? 'false' : 'true');
            $card.toggleClass('is-open', !open);
        });

        $('#ai-list').on('keydown', '.ai-card-main', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') { return; }
            e.preventDefault();
            $(this).trigger('click');
        });

        /*
         | Below 992px the detail table scrolls sideways, and a scroller clips
         | vertically as well as horizontally — which swallowed this menu.
         | Lift the clip while the menu is open, then put the scroll position
         | back so the table does not jump to the start.
         */
        $('#ai-list')
            .on('show.bs.dropdown', '.ai-close-menu', function () {
                const scroller = $(this).closest('.applicant-intake-child-card');
                scroller.data('scroll-left', scroller.scrollLeft()).addClass('is-menu-open');
            })
            .on('hidden.bs.dropdown', '.ai-close-menu', function () {
                const scroller = $(this).closest('.applicant-intake-child-card');
                scroller.removeClass('is-menu-open').scrollLeft(scroller.data('scroll-left') || 0);
            });

        // One dialog for both decisions, filled from the button pressed, so the
        // application being decided is always named in it.
        $('#ai-list').on('click', '.js-decide', function () {
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

        load();
    });
</script>
