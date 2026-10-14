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

    /* Recommended is not a rejection — it reads as informational, not as a
       warning, so it must not borrow Not Selected's colour. */
    .ai-chip-recommended {
        background: var(--zn-info-soft, var(--zn-surface-2));
        color: var(--zn-info, var(--zn-ink-2));
    }

    /* A posting HR cannot pick, shown with the reason rather than hidden: an
       option that silently vanishes reads as a broken screen. */
    .ai-rec-option-blocked {
        color: var(--zn-ink-3, #888);
    }

    .ai-rec-provenance {
        font-size: .85em;
        color: var(--zn-ink-2);
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

    /*
     | Barred from new applications.
     |
     | The same warning colour Not Selected uses, because it is the same kind
     | of fact -- a door that is shut -- and a third colour would only invite
     | HR to work out what it meant. Smaller than a status chip: it sits beside
     | the name rather than in the status column, and it must not out-shout the
     | applications it is standing next to.
     */
    /* Sits with the filters, reading as a way out rather than another chip. */
    .ai-pool-link {
        margin-left: auto;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        color: var(--zn-accent-dark);
        text-decoration: none;
        white-space: nowrap;
    }

    .ai-pool-link:hover { text-decoration: underline; }

    .ai-chip-barred {
        background: var(--zn-warn-soft);
        color: var(--zn-warn);
    }

    .ai-chip-xs {
        padding: 1px 9px;
        font-size: 11px;
        vertical-align: middle;
    }

    .ai-chip-xs::before { width: 5px; height: 5px; }

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
    /*
    | The entry points are shown, not hidden behind a disclosure.
    |
    | They used to be a <details> that HR had to open every time. Which doors
    | are open -- Walk-in Mode, a running job fair -- is a thing to be SEEN,
    | not remembered to go and check: a fair left switched on quietly tags
    | every application that follows. A click to find that out is a click too
    | many.
    */
    .ai-entry-points {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        margin-bottom: 12px;
    }

    /* Compact: this reports which doors are open, it is not the page's work,
       and it used to take a third of the screen before a single applicant
       appeared. */
    .ai-entry-points > .ai-ep-head { padding: 7px 12px; }
    .ai-entry-points .ai-ep-body { padding: 0 12px 10px; }
    .ai-entry-points .ep-card { padding: 8px 10px; }
    .ai-entry-points .ep-sub { margin: 2px 0 0; font-size: var(--zn-fs-xs); }

    .ai-entry-points > .ai-ep-head {
        margin: 0;
        padding: 10px 14px;
        font-size: var(--zn-fs);
        font-weight: 600;
        color: var(--zn-ink);
        display: flex;
        align-items: baseline;
        gap: 10px;
        flex-wrap: wrap;
    }
    .ai-ep-hint { font-weight: 400; font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }
    .ai-ep-body { padding: 0 14px 14px; }

    /*
    | Recording an applicant by hand.
    |
    | The dialog is a sequence of small questions rather than one block of
    | fields: who is this, what are they called, how do they sign in, what for.
    | The order matters -- the contact details come first because they are what
    | the lookup needs, and the lookup is what tells HR whether they are
    | creating an account or adding to one that already exists.
    */
    .ai-step-label {
        margin: 14px 0 6px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
    }
    .ai-step-label:first-of-type { margin-top: 4px; }

    /* The answer to "do we already know this person?". */
    .ai-known {
        margin: 8px 0 2px;
        padding: 10px 12px;
        border: 1px solid var(--zn-accent);
        border-left-width: 4px;
        border-radius: var(--zn-radius);
        background: var(--zn-accent-soft);
    }

    /* Blacklisted is a refusal, not a convenience, so it does not wear the
       same colour as "we found them". */
    .ai-known.is-blocked {
        border-color: var(--zn-warn);
        background: var(--zn-warn-soft);
    }

    .ai-known-title {
        margin: 0;
        font-weight: 700;
        font-size: var(--zn-fs-sm);
        color: var(--zn-accent-dark);
    }
    .ai-known.is-blocked .ai-known-title { color: var(--zn-warn); }

    .ai-known-name { margin: 1px 0 0; font-weight: 600; }
    .ai-known-note { margin: 3px 0 0; font-size: var(--zn-fs-sm); color: var(--zn-ink-2); }

    .ai-known-apps {
        margin: 6px 0 0;
        padding-left: 18px;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
    }

    .ai-known-link {
        display: inline-block;
        margin-top: 6px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
    }

    /* The sign-in details, shown once after saving. The code is stored hashed,
       so this is genuinely the only time it can be read. */
    .ai-added-hand {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        align-items: baseline;
        font-size: var(--zn-fs-sm);
    }
    .ai-added-hand span {
        padding: 2px 8px;
        border-radius: var(--zn-radius);
        background: var(--zn-surface);
        border: 1px solid var(--zn-line);
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    }

    .ai-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin: 4px 0 12px;
    }

    .ai-result-count { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); white-space: nowrap; }

    /* The primary action keeps its place at the end of the toolbar rather than
       being pushed around by how wide the search happens to be. */
    .ai-toolbar .ai-add { flex: none; margin-left: auto; }

    /*
    | The search leads the toolbar and takes the room left over, so it is the
    | widest thing on the row rather than an afterthought at the right-hand
    | edge. The magnifier sits inside it, which is what makes it read as a
    | search box at a glance rather than a text field.
    */
    .ai-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1 1 260px;
        min-width: 0;
    }

    .ai-search-ico {
        position: absolute;
        left: 10px;
        color: var(--zn-ink-3);
        pointer-events: none;
        font-size: 14px;
    }

    .ai-search-wrap input[type="search"] { padding-left: 30px; }

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

    /* The name goes straight to the profile. Not underlined at rest, because
       every row would then be a line of blue rules; underlined on hover and
       always outlined on focus, so it is still obviously a link. */
    .ai-card-link { color: inherit; text-decoration: none; }
    .ai-card-link:hover { color: var(--zn-accent-dark); text-decoration: underline; }
    .ai-card-link:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: 2px; border-radius: 3px; }

    /*
    | The photo sits over the initials rather than instead of them.
    |
    | A photo that cannot be loaded -- removed, or simply not on this
    | machine's disk -- then falls back to something readable instead of a
    | broken-image icon, which is worse than no photo at all.
    */
    .ai-avatar { position: relative; overflow: hidden; }
    .ai-avatar-initials { position: absolute; inset: 0; display: inline-flex; align-items: center; justify-content: center; }
    .ai-avatar img { position: relative; width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

    /* One control, so it should look like one rather than like a word. */
    .ai-dots { line-height: 1; padding: 2px 9px; font-size: 15px; letter-spacing: 1px; }

    .ai-actions-menu { max-width: min(320px, calc(100vw - 32px)); }
    .ai-actions-menu .dropdown-item { white-space: normal; }
    .ai-actions-menu .dropdown-item b { display: block; }
    .ai-actions-menu .dropdown-item small { display: block; color: var(--zn-ink-3); }

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

    {{--
        Controlled entry points.

        A status strip, not a panel. Which doors are open is something HR needs
        to SEE -- a job fair left switched on silently tags every application
        after it -- but it is not what the page is for, and it used to sit
        between the filters and the list taking a third of the screen before
        a single applicant appeared.

        Source tracking only: these decide what an application's source is
        recorded as, nothing about its progress.
    --}}
    <section class="ai-entry-points" id="ai-entry-points" aria-labelledby="ai-ep-heading">
        <h2 class="ai-ep-head" id="ai-ep-heading">
            <span>Application entry points</span>
            <span class="ai-ep-hint">Walk-in Mode &amp; Job Fair events</span>
        </h2>
        <div class="ai-ep-body">
            @include('pages.recruitment.applicant-intake.entry-points')
        </div>
    </section>

    {{--
        One toolbar, directly above the list it controls.

        Searching was the thing HR reached for first and it was the last thing
        on the page -- below the entry points, pushed to the right-hand edge,
        narrower than the filter chips above it. It leads now, at the width it
        deserves, with the filters beside it and the count of what they left.
    --}}
    <div class="ai-toolbar" role="group" aria-label="Find applicants">
        <div class="ai-search-wrap">
            <i class="bi bi-search ai-search-ico" aria-hidden="true"></i>
            <input type="search" id="ai-search" aria-label="Search applicants"
                placeholder="Search name, email, position, MR no…">
            <button type="button" id="ai-search-clear" style="display:none;" aria-label="Clear search">&times;</button>
        </div>

        <select id="ai-posting-filter" class="form-select form-select-sm" aria-label="Filter by position">
            <option value="">Every position</option>
        </select>

        @can('applicant-applications.decide')
            {{-- Walk-ins, referrals and job-fair candidates. HR opens the
                 account here; the applicant signs in at the desk and fills in
                 their own details, so there is no second intake workflow. --}}
            <button type="button" class="btn btn-sm btn-primary ai-add" id="ai-add-btn"
                data-bs-toggle="modal" data-bs-target="#aiWalkInModal">
                <i class="bi bi-person-plus"></i> Record applicant
            </button>
        @endcan
    </div>

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
        <span class="ai-result-count" id="ai-result-count"></span>

        {{-- The pool has a page of its own now. The chip above stays exactly
             as it was -- this is a way through to the fuller view, not a
             replacement for the filter. --}}
        <a class="ai-pool-link" href="{{ url('/recruitment/candidate-pool') }}">Open the Candidate Pool &rarr;</a>
    </div>

    {{-- One card per person. The nine-column table scattered a single
         applicant across the width of the screen; here the person is the
         unit and their applications sit underneath them. --}}
    <div id="ai-list" class="ai-list" aria-live="polite"></div>

    <div class="ai-empty" id="ai-empty" style="display:none;"></div>

</div>

@can('applicant-applications.decide')
    {{-- Recording someone HR met in person.

         Not walk-ins: those register themselves at the office PC through the
         Careers Portal, and the bookmarked ?src=walk_in link is what records
         the office as their origin. This is for people who are not at a
         keyboard — met at a job fair, referred by someone.

         Only what HR can know when they meet someone: who they are, how to
         reach them, where they came from, the posting, and a sign-in code.
         Everything else the applicant fills in themselves. --}}
    <div class="modal fade" id="aiWalkInModal" tabindex="-1" aria-labelledby="aiWalkInTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="aiWalkInTitle">Record an applicant</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="aiWalkInForm" autocomplete="off">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            For applicants you met in person. This opens a portal account — hand them the
                            sign-in details so they can log in and complete their own application,
                            documents and assessments.
                            <div class="mt-1">
                                <strong>Walk-ins do not belong here.</strong> Let them register on the office PC
                                using the Careers link bookmarked there; it records them as a walk-in by itself.
                            </div>
                        </div>

                        <div id="aiWalkInError" class="alert alert-danger py-2 small d-none"></div>

                        {{--
                            Who is this?

                            The contact details come first and are checked as
                            they are typed. Intake used to find an existing
                            account inside the save, so HR filled in the whole
                            form before learning the person was already known --
                            or, worse, blacklisted. Asking first turns that from
                            a surprise at the end into the first thing HR sees.
                        --}}
                        <p class="ai-step-label">1 · Who is this?</p>

                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label class="form-label small" for="wi-email">Email</label>
                                <input type="email" class="form-control form-control-sm" id="wi-email" name="app_email" maxlength="150">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small" for="wi-mobile">Mobile</label>
                                <input class="form-control form-control-sm" id="wi-mobile" name="app_mobile" maxlength="50">
                            </div>
                            <div class="col-12">
                                <div class="form-text">They sign in with the email or the mobile number — at least one is needed.</div>
                            </div>
                        </div>

                        {{-- Filled in by the lookup when the person is already known. --}}
                        <div id="wi-known" class="ai-known d-none" role="status"></div>

                        <p class="ai-step-label">2 · Their name</p>

                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label class="form-label small" for="wi-lname">Last name <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm" id="wi-lname" name="app_lname" maxlength="100" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small" for="wi-fname">First name <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm" id="wi-fname" name="app_fname" maxlength="100" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small" for="wi-mname">Middle name</label>
                                <input class="form-control form-control-sm" id="wi-mname" name="app_mname" maxlength="100">
                            </div>
                        </div>

                        <p class="ai-step-label">3 · How they sign in</p>

                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small" for="wi-code">Sign-in code <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm" id="wi-code" name="app_code"
                                        minlength="8" maxlength="100" required autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="wi-code-new">New</button>
                                    <button class="btn btn-outline-secondary" type="button" id="wi-code-copy">Copy</button>
                                </div>
                                {{-- Offered rather than invented. HR used to think one up on the
                                     spot, which produced weak and forgettable codes at exactly the
                                     moment they were being read aloud to somebody. Still editable. --}}
                                <div class="form-text">Read this out to the applicant — it is shown only here.
                                    They can change it once they have signed in.</div>
                            </div>
                        </div>

                        <p class="ai-step-label">4 · What they are applying for</p>

                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small" for="wi-posting">Applying for <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="wi-posting" name="job_posting_id" required>
                                    <option value="">Loading open postings…</option>
                                </select>
                                <div class="form-text">This is what tags the applicant to the manpower request.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small" for="wi-source">Where they came from <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="wi-source" name="source_channel" required>
                                    <option value="">Loading…</option>
                                </select>
                                <div class="form-text">Recorded on the application. It says where they came from —
                                    it does not decide where they are in the process.</div>
                            </div>

                            {{-- Only a job fair decides hiring on the spot, so this
                                 appears only for one source. --}}
                            <div class="col-12 d-none" id="wi-outcome-wrap">
                                <label class="form-label small" for="wi-outcome">What happened at the event <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="wi-outcome" name="outcome">
                                    <option value="hired">Acquired — hired on the spot</option>
                                    <option value="processing">For further processing</option>
                                    <option value="not_selected">Not selected</option>
                                </select>
                                <div class="form-text" id="wi-outcome-help"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="aiWalkInSubmit">Add applicant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    <p class="text-muted mb-3" id="aiDecideRecommendedHelp" hidden>
                        The applicant is going forward for another job post instead. This closes
                        <b>this</b> application only — the one created for the other post carries on with its own
                        interviews and exams. <b>No waiting period applies</b>, so they may apply here again.
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

    {{-- Recommending the applicant onward to a different job post.

         This creates a SEPARATE application with its own workflow — the
         applicant starts the other post's process from the beginning, because
         passing a warehouse interview is not passing a logistics one. It does
         not close this application: HR normally talks the options over with the
         applicant, who may still prefer this post. --}}
    <div class="modal fade" id="aiRecModal" tabindex="-1" aria-labelledby="aiRecTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content" id="aiRecForm">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-6" id="aiRecTitle">Recommend to another job post</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="font-size:var(--zn-fs)">
                    <p class="mb-2 fw-semibold" id="aiRecWho"></p>

                    <p class="text-muted mb-3">
                        This creates a <b>new application</b> for the post you choose, with its own interviews
                        and exams starting from the beginning. This application stays open and keeps its own
                        history — close it separately once the applicant has decided.
                    </p>

                    <label class="form-label" for="aiRecTarget">Recommend to</label>
                    <select class="form-select form-select-sm" name="target_job_posting_id" id="aiRecTarget" required>
                        <option value="">Loading job posts…</option>
                    </select>
                    <div class="form-text" id="aiRecTargetHelp">Only published job posts can be chosen.</div>

                    <label class="form-label mt-3" for="aiRecReason">Reason <span class="text-muted">(optional, kept with the recommendation)</span></label>
                    <textarea class="form-control form-control-sm" name="reason" id="aiRecReason" rows="3" maxlength="1000"
                        placeholder="e.g. Stronger fit for logistics work"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="aiRecSubmit" disabled>Recommend</button>
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
        'Not Selected': 'ai-chip-notselected',
        'Recommended': 'ai-chip-recommended'
    };

    // HR may withdraw an application for the applicant, or mark it Not
    // Selected (eappprofile view + directedit or hire). Enforced again on the
    // routes; this only decides whether to show the buttons.
    const CAN_DECIDE = @json(Gate::allows('applicant-applications.decide'));
    const COOLDOWN_MONTHS = @json((int) (config('applications.cooldowns')['Not Selected']['months'] ?? 0));
    const DECIDE_URL = {
        withdraw: @json(route('recruitment.applicant-intake.withdraw', ['application' => '__ID__'])),
        notSelected: @json(route('recruitment.applicant-intake.not-selected', ['application' => '__ID__'])),
        recommended: @json(route('recruitment.applicant-intake.recommendation.close', ['application' => '__ID__']))
    };

    // Recommending onward: the eligible postings are fetched per application,
    // because what is eligible depends on this applicant's other applications.
    const RECOMMEND_URL = {
        targets: @json(route('recruitment.applicant-intake.recommendation.targets', ['application' => '__ID__'])),
        store: @json(route('recruitment.applicant-intake.recommendation.store', ['application' => '__ID__']))
    };

    // The interview workspace belongs to the APPLICATION, so it is reached from
    // the application's own row rather than from the applicant.
    const INTERVIEWS_URL = @json(route('applicant.show', ['id' => '__APPID__', 'tab' => 'interview-details']));
    const CAN_VIEW_INTERVIEWS = @json(Gate::allows('applicant-interviews.view'));

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
        if (!CAN_DECIDE) return '';

        const data = ' data-id="' + esc(app.id) + '" data-title="' + esc(app.posting_title) +
            '" data-mr="' + esc(app.mr_no) + '" data-name="' + esc(app.applicant_name) + '"';

        // Recommending stays available on a CLOSED application. Closing ends
        // that application, not the company's interest in the person — HR often
        // realises afterwards that someone suits another post, and the record
        // of where that came from belongs on the application it came from.
        const recommend = '<li><button type="button" class="dropdown-item js-recommend"' + data + '>' +
            '<b>Recommend to another post</b>' +
            '<small>Creates a new application there. This one is not closed.</small></button></li>';

        // Interviews stay readable whatever the application's outcome: the
        // record of what was asked and concluded does not stop mattering when
        // the application closes.
        // The applicant's profile is where interviews are managed; the tab
        // shows each of their applications separately.
        const interviews = CAN_VIEW_INTERVIEWS
            ? '<li><a class="dropdown-item ai-card-open" href="' +
              INTERVIEWS_URL.replace('__APPID__', encodeURIComponent(app.app_id)) + '">' +
              '<b>Interviews</b><small>Rounds, who is conducting them, and what they concluded.</small></a></li>'
            : '';

        /*
        | One menu, carrying everything that can be done to THIS application.
        |
        | There were three controls in a row -- a link, a button and a second
        | dropdown -- which meant the row's width decided how much HR could
        | see, and the two menus looked like alternatives to each other rather
        | than one list of actions. Every item carries this application's own
        | id in `data`, so an applicant with several of them cannot have the
        | wrong one acted on.
        |
        | A closed application has already reached its outcome, so the
        | closures are left out; recommending and reading the interviews stay,
        | because neither of those stops mattering when an application ends.
        */
        const closures = app.is_closed ? '' :
            '<li><hr class="dropdown-divider"></li>' +
            '<li><h6 class="dropdown-header">Close this application</h6></li>' +
            '<li><button type="button" class="dropdown-item js-decide" data-action="withdraw"' + data + '>' +
            '<b>Applicant withdrew</b><small>They asked to stop this application. No waiting period.</small></button></li>' +
            '<li><button type="button" class="dropdown-item js-decide" data-action="notSelected"' + data + '>' +
            '<b>Not Selected</b><small>Our decision. ' + COOLDOWN_MONTHS + '-month wait before they can apply to this posting again.</small></button></li>' +
            (app.recommended_count > 0
                ? '<li><button type="button" class="dropdown-item js-decide" data-action="recommended"' + data + '>' +
                  '<b>Recommended</b><small>They are going forward for another post instead. No waiting period.</small></button></li>'
                : '');

        return '<div class="dropdown ai-close-menu">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary ai-dots" data-bs-toggle="dropdown"' +
            ' aria-expanded="false" aria-label="Actions for ' + esc(app.posting_title) + '" title="Actions">⋯</button>' +
            '<ul class="dropdown-menu dropdown-menu-end ai-actions-menu">' +
            interviews + recommend + closures +
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

    // The link between two applications, in both directions.
    //
    // Named by JOB POST rather than application number: HR recognises "Warehouse
    // Supervisor", not "#1871". The two applications stay entirely separate —
    // this says where someone came from, never what happened to them there.
    function recommendationLines(app) {
        let html = '';

        if (app.recommended_from) {
            html += '<div class="ai-rec-provenance"><i class="bi bi-arrow-down-right"></i> ' +
                'Recommended from <b>' + esc(app.recommended_from.posting_title) + '</b>' +
                (app.recommended_from.recommended_at ? ' · ' + shortDate(app.recommended_from.recommended_at) : '') +
                '</div>';
        }

        const to = app.recommended_to || [];
        if (to.length) {
            // Naming them is more useful than counting them, until there are
            // enough that the list stops being readable.
            const posts = to.map(t => esc(t.posting_title)).join(', ');
            html += '<div class="ai-rec-provenance"><i class="bi bi-arrow-up-right"></i> ' +
                (to.length <= 2
                    ? 'Recommended to <b>' + posts + '</b>'
                    : 'Recommended to <b>' + to.length + ' other posts</b> · ' + posts) +
                '</div>';
        }

        return html;
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
                    ${recommendationLines(app)}
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

        /*
         | Where we are in the list right now, so the profile can come back to
         | it rather than to an unfiltered list.
         |
         | The search and filters live in the query string, so path + search is
         | exactly the view HR is looking at. The profile accepts it only if it
         | is a relative applicant-intake path -- it arrives as a query
         | parameter, so it is checked there rather than trusted from here.
         */
        function intakeReturn() {
            return window.location.pathname + window.location.search;
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

        /* One place that knows where a profile lives, and that HR should come
           back to the list they were working. */
        function profileUrl(appId) {
            return baseUrlForDocs + '/applicant/info/' + encodeURIComponent(appId) +
                '?from=' + encodeURIComponent(intakeReturn());
        }

        /*
        | The applicant's face, or their initials.
        |
        | The initials are always rendered and the photo sits on top of them,
        | so a photo that fails to load -- deleted, or not on this machine's
        | disk -- falls back to something readable instead of a broken image
        | icon. That is the pattern the profile header already uses.
        |
        | photo_url is built server-side through the file route, which is
        | what enforces access. Nothing is copied between disks.
        */
        function avatar(row) {
            const letters = '<span class="ai-avatar-initials">' + esc(initials(row.applicant_name)) + '</span>';

            return '<span class="ai-avatar" aria-hidden="true">' + letters +
                (row.photo_url
                    ? '<img src="' + esc(row.photo_url) + '" alt="" loading="lazy" ' +
                      'onerror="this.remove()">'
                    : '') +
                '</span>';
        }

        function cardHtml(row) {
            return '' +
            '<article class="ai-card" data-app-id="' + esc(row.app_id) + '">' +
              /*
              | The header: one thing that expands, one thing that navigates.
              |
              | It used to be a role="button" wrapping both a link and a
              | button, so the whole header claimed to be a control while two
              | of its children were different controls -- which is what made
              | the clickable area feel arbitrary. The expander is now the
              | header itself and the exceptions are explicit: anything inside
              | .ai-card-open does its own thing and never toggles.
              */
              '<div class="ai-card-main" role="button" tabindex="0" aria-expanded="false"' +
                   ' aria-label="Show this applicant\'s applications">' +
                avatar(row) +
                '<div class="ai-card-who">' +
                  /* Whether this person may be given a new application at all
                     is the first thing about them, so it sits with the name
                     and not behind an expanded row. The reason is HR's own
                     note and stays on the profile with the decision. */
                  '<span class="ai-card-name">' +
                    '<a class="ai-card-link ai-card-open" href="' + profileUrl(row.app_id) + '">' +
                      esc(row.applicant_name) +
                    '</a>' +
                    (row.blacklisted
                      ? ' <span class="ai-chip ai-chip-barred ai-chip-xs">Blacklisted</span>'
                      : '') +
                  '</span>' +
                  '<span class="ai-card-sub">' +
                    [row.app_email, row.app_mobile].filter(Boolean).map(esc).join(' · ') +
                  '</span>' +
                  '<span class="ai-card-sub">' + activityLine(row) + '</span>' +
                '</div>' +
                '<div class="ai-card-state">' + statusCell(row) + '</div>' +
                '<div class="ai-card-docs">' + docsCell(row) + '</div>' +
                '<div class="ai-card-go">' +
                  '<a class="btn btn-sm btn-outline-primary ai-card-open" href="' +
                    profileUrl(row.app_id) + '">Open profile</a>' +
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
            /*
            | Anything that does its own thing says so.
            |
            | This used to exclude every a, button and .dropdown in the
            | header, which worked but meant the rule lived in a selector
            | nobody reading the markup would see -- and any control added
            | later silently inherited it. .ai-card-open marks the exceptions
            | where they are written.
            */
            if ($(e.target).closest('.ai-card-open, a, button, .dropdown').length) { return; }
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

        /*
         | ===== Walk-in intake =====
         |
         | HR opens the account and attaches the first application. Everything
         | after — details, documents, assessments, the offer — happens on the
         | portal under that same account, so nothing else here is special.
         */
        let walkInPostingsLoaded = false;
        let intakeSources = [];

        // What each job-fair outcome actually does, said before HR commits.
        const OUTCOME_HELP = {
            hired: 'Recorded as hired. The application stays OPEN — requirements, any remaining '
                 + 'exams and onboarding are still ahead, and they are not sent back through interviews.',
            processing: 'Joins the normal process from the beginning.',
            not_selected: 'Closed as not selected and added to the Candidate Pool.'
        };

        function syncOutcomeVisibility() {
            const source = intakeSources.find(s => s.key === $('#wi-source').val());
            const offers = !!(source && source.offers_outcomes);

            $('#wi-outcome-wrap').toggleClass('d-none', !offers);
            $('#wi-outcome').prop('disabled', !offers);
            $('#wi-outcome-help').text(offers ? (OUTCOME_HELP[$('#wi-outcome').val()] || '') : '');
        }

        $('#aiWalkInModal').on('show.bs.modal', function () {
            $('#aiWalkInError').addClass('d-none').text('');

            if (!intakeSources.length) {
                $.getJSON(urlPrefix + '/recruitment/applicant-intake/intake-sources')
                    .done(function (json) {
                        intakeSources = json.data || [];
                        const select = $('#wi-source').empty()
                            .append($('<option>').val('').text('Choose…'));

                        intakeSources.forEach(function (source) {
                            select.append($('<option>').val(source.key)
                                .text(source.name).attr('title', source.description));
                        });

                        syncOutcomeVisibility();
                    })
                    .fail(function () {
                        $('#wi-source').empty().append($('<option>').val('').text('Could not load sources'));
                    });
            }

            if (walkInPostingsLoaded) { return; }

            $.getJSON(urlPrefix + '/recruitment/applicant-intake/open-postings')
                .done(function (json) {
                    const select = $('#wi-posting').empty()
                        .append($('<option>').val('').text('Choose a posting…'));

                    (json.data || []).forEach(function (posting) {
                        // The MR No. is what makes two same-titled postings
                        // from different departments tellable apart.
                        const label = posting.posting_title
                            + (posting.mr_no ? ' · ' + posting.mr_no : '');
                        select.append($('<option>').val(posting.id).text(label));
                    });

                    if (!(json.data || []).length) {
                        select.empty().append($('<option>').val('')
                            .text('No published postings — publish one first'));
                    }

                    walkInPostingsLoaded = true;
                })
                .fail(function () {
                    $('#wi-posting').empty().append($('<option>').val('').text('Could not load postings'));
                });
        });

        $('#wi-source').on('change', syncOutcomeVisibility);
        $('#wi-outcome').on('change', function () {
            $('#wi-outcome-help').text(OUTCOME_HELP[this.value] || '');
        });

        /* ------------------------------- intake: who is this person? -- */

        /*
         | The contact details are looked up as HR types them.
         |
         | Finding a duplicate -- or a blacklisted applicant -- used to happen
         | inside the save, so HR filled in the whole form before learning
         | either. Asking first means the answer arrives while it can still
         | change what HR does.
         */
        let lookupTimer = null;

        function renderKnown(info) {
            const $box = $('#wi-known');

            if (!info || !info.found) {
                $box.addClass('d-none').removeClass('is-blocked').empty();
                $('#aiWalkInSubmit').prop('disabled', false);
                return;
            }

            const apps = (info.applications || []).map(function (a) {
                return '<li>' + esc(a.posting_title) + ' \u2014 <span class="' +
                    (a.closed ? 'text-muted' : 'fw-semibold') + '">' + esc(a.status) + '</span></li>';
            }).join('');

            const blocked = !!info.blacklisted;

            $box.toggleClass('is-blocked', blocked).removeClass('d-none').html(
                '<p class="ai-known-title">' +
                    (blocked ? 'This applicant is blacklisted' : 'We already know this person') +
                '</p>' +
                '<p class="ai-known-name">' + esc(info.name) + '</p>' +
                (blocked
                    ? '<p class="ai-known-note">A new application cannot be recorded for them. ' +
                      'Lift the blacklist on their profile first if that is no longer right.</p>'
                    : '<p class="ai-known-note">The application will be added to their existing account \u2014 ' +
                      'no second account is created.</p>') +
                (apps ? '<ul class="ai-known-apps">' + apps + '</ul>' : '') +
                '<a class="ai-known-link" href="' + esc(info.profile_url) + '" target="_blank" rel="noopener">Open their profile</a>'
            );

            /* Their name is already known; filling it saves retyping it and
               stops a second spelling of the same person entering the record. */
            if (!blocked) {
                if (!$('#wi-lname').val()) { $('#wi-lname').val(info.app_lname || ''); }
                if (!$('#wi-fname').val()) { $('#wi-fname').val(info.app_fname || ''); }
                if (!$('#wi-mname').val()) { $('#wi-mname').val(info.app_mname || ''); }
            }

            $('#aiWalkInSubmit').prop('disabled', blocked);
        }

        async function lookupApplicant() {
            const email = $('#wi-email').val().trim();
            const mobile = $('#wi-mobile').val().trim();

            if (!email && !mobile) { renderKnown(null); return; }

            try {
                const res = await fetch(urlPrefix + '/recruitment/applicant-intake/lookup?' +
                    new URLSearchParams({ app_email: email, app_mobile: mobile }), {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) { return; }   // a failed lookup must not block intake
                renderKnown(await res.json());
            } catch (err) {
                /* The lookup is a convenience. If it cannot run, intake carries
                   on and the save still refuses a blacklisted applicant. */
            }
        }

        $('#wi-email, #wi-mobile').on('input', function () {
            clearTimeout(lookupTimer);
            lookupTimer = setTimeout(lookupApplicant, 350);
        });

        /* ---------------------------------------- intake: sign-in code -- */

        function freshCode() {
            /* Readable aloud: no look-alike characters, grouped in fours. */
            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            const bytes = new Uint32Array(12);
            crypto.getRandomValues(bytes);
            const raw = Array.from(bytes).map(function (b) {
                return alphabet[b % alphabet.length];
            }).join('');
            return raw.slice(0, 4) + '-' + raw.slice(4, 8) + '-' + raw.slice(8, 12);
        }

        $('#wi-code-new').on('click', function () { $('#wi-code').val(freshCode()); });

        $('#wi-code-copy').on('click', async function () {
            const $b = $(this);
            try {
                await navigator.clipboard.writeText($('#wi-code').val());
                $b.text('Copied');
            } catch (e) {
                /* Clipboard access can be refused; selecting it still works. */
                $('#wi-code').trigger('select');
                $b.text('Select & copy');
            }
            setTimeout(function () { $b.text('Copy'); }, 1800);
        });

        /* A code is ready the moment the dialog opens, so HR never has to think
           one up while somebody is waiting in front of them. */
        $('#aiWalkInModal').on('show.bs.modal', function () {
            $('#aiWalkInForm')[0].reset();
            renderKnown(null);
            $('#aiWalkInError').addClass('d-none').text('');
            $('#wi-code').val(freshCode());
        });

        $('#aiWalkInForm').on('submit', async function (e) {
            e.preventDefault();

            const $btn = $('#aiWalkInSubmit');
            const original = $btn.text();
            $btn.prop('disabled', true).text('Adding…');
            $('#aiWalkInError').addClass('d-none').text('');

            const payload = {};
            $(this).serializeArray().forEach(f => { payload[f.name] = f.value; });

            try {
                const res = await fetch(urlPrefix + '/recruitment/applicant-intake/acquired', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok) {
                    // Validation comes back as {errors: {field: [msg]}}.
                    const messages = data.errors
                        ? Object.values(data.errors).flat()
                        : [data.message || 'The applicant could not be added.'];
                    $('#aiWalkInError').text(messages.join(' ')).removeClass('d-none');
                    return;
                }

                /*
                 | What HR does next.
                 |
                 | The old toast said what had happened and left HR on the list.
                 | The two things actually wanted at that moment are the sign-in
                 | details to hand over -- shown once here and nowhere else
                 | afterwards, because the code is stored hashed -- and a way
                 | into the profile that was just created.
                 */
                const signIn = $('#wi-email').val().trim() || $('#wi-mobile').val().trim();
                const code = $('#wi-code').val();

                bootstrap.Modal.getInstance(document.getElementById('aiWalkInModal')).hide();
                $('#aiWalkInForm')[0].reset();
                renderKnown(null);

                $('#ai-alert').html(
                    '<div class="alert alert-success alert-dismissible fade show ai-added">' +
                        '<p class="mb-2">' + esc(data.message) + '</p>' +
                        '<p class="ai-added-hand mb-2">Give the applicant these to sign in \u2014 they are not shown again:' +
                            '<span><b>' + esc(signIn) + '</b></span>' +
                            '<span>code <b>' + esc(code) + '</b></span>' +
                        '</p>' +
                        '<a class="btn btn-sm btn-outline-primary" href="' + urlPrefix +
                            '/applicant/info/' + encodeURIComponent(data.app_id) +
                            '?from=' + encodeURIComponent(intakeReturn()) + '">Open their profile</a> ' +
                        '<button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#aiWalkInModal">Record another</button>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>'
                );
                load();
            } catch (err) {
                $('#aiWalkInError').text('The applicant could not be added: ' + err.message).removeClass('d-none');
            } finally {
                $btn.prop('disabled', false).text(original);
            }
        });

        // One dialog for both decisions, filled from the button pressed, so the
        // application being decided is always named in it.
        const DECIDE_WORDING = {
            withdraw: {
                title: 'Withdraw application — ',
                submit: 'Withdraw application',
                help: '#aiDecideWithdrawHelp',
                danger: false
            },
            notSelected: {
                title: 'Mark Not Selected — ',
                submit: 'Mark Not Selected',
                help: '#aiDecideNotSelectedHelp',
                danger: true
            },
            recommended: {
                title: 'Close as Recommended — ',
                submit: 'Close as Recommended',
                help: '#aiDecideRecommendedHelp',
                // Not a rejection, so it does not get the destructive styling.
                danger: false
            }
        };

        $('#ai-list').on('click', '.js-decide', function () {
            const btn = $(this);
            const action = btn.data('action');
            const wording = DECIDE_WORDING[action];

            // An unknown action would otherwise be given another outcome's
            // wording, which is how HR ends up confirming the wrong thing.
            if (!wording) return;

            $('#aiDecideForm').attr('action', DECIDE_URL[action].replace('__ID__', encodeURIComponent(btn.data('id'))));
            $('#aiDecideTitle').text(wording.title + btn.data('title'));
            $('#aiDecideWho').text(btn.data('name') + ' · ' + btn.data('title') + ' · ' + btn.data('mr'));

            Object.values(DECIDE_WORDING).forEach(w => $(w.help).prop('hidden', true));
            $(wording.help).prop('hidden', false);

            $('#aiDecideSubmit')
                .text(wording.submit)
                .toggleClass('btn-danger', wording.danger)
                .toggleClass('btn-secondary', !wording.danger);
            $('#aiDecideNote').val('');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('aiDecideModal')).show();
        });

        // Recommending onward. The eligible postings are fetched per
        // application, because eligibility depends on this applicant's other
        // applications — so the list cannot be built once and reused.
        $('#ai-list').on('click', '.js-recommend', function () {
            const btn = $(this);
            const id = encodeURIComponent(btn.data('id'));

            $('#aiRecForm').attr('action', RECOMMEND_URL.store.replace('__ID__', id));
            $('#aiRecTitle').text('Recommend to another job post');
            $('#aiRecWho').text(btn.data('name') + ' · currently ' + btn.data('title'));
            $('#aiRecReason').val('');
            $('#aiRecSubmit').prop('disabled', true);
            /* Cleared on every open, not only on the paths that set it: the
               dialog is reused, and a selector left disabled by the last
               applicant would silently block the next one. */
            $('#aiRecTarget')
                .prop('disabled', false)
                .html('<option value="">Loading job posts…</option>');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('aiRecModal')).show();

            $.getJSON(RECOMMEND_URL.targets.replace('__ID__', id))
                .done(function (data) {
                    const targets = (data && data.targets) || [];
                    const eligible = targets.filter(t => t.eligible);

                    /*
                     | An obstacle that is about the applicant, not a posting.
                     |
                     | Said once, above the selector, instead of being written
                     | onto every posting in the list. HR finds out before
                     | typing a reason rather than after submitting it.
                     |
                     | This only explains. The server refuses the same request
                     | whether or not this ran -- see
                     | ApplicationRecommendationService::recommend().
                     */
                    if (data && data.blocked) {
                        $('#aiRecTarget').html('<option value="">No job post can be chosen</option>')
                            .prop('disabled', true);
                        $('#aiRecTargetHelp').text(data.blocked);
                        $('#aiRecSubmit').prop('disabled', true);
                        return;
                    }

                    $('#aiRecTarget').prop('disabled', false);

                    if (!targets.length) {
                        $('#aiRecTarget').html('<option value="">No published job posts</option>');
                        $('#aiRecTargetHelp').text('There are no published job posts to recommend to.');
                        return;
                    }

                    let html = '<option value="">Choose a job post…</option>';
                    let lastDept = null;
                    let open = false;

                    targets.forEach(function (t) {
                        // Grouped by department: position titles repeat across
                        // departments, and HR needs to tell them apart.
                        const dept = t.department || 'Other';
                        if (dept !== lastDept) {
                            if (open) html += '</optgroup>';
                            html += '<optgroup label="' + esc(dept) + '">';
                            lastDept = dept;
                            open = true;
                        }

                        // Ineligible posts are shown disabled WITH the reason.
                        // One that simply vanished would read as a broken list.
                        // The MR number distinguishes two openings that carry
                        // the same title — without it they are identical lines
                        // and HR is guessing which one they are choosing.
                        html += '<option value="' + esc(t.id) + '"' +
                            (t.eligible ? '' : ' disabled class="ai-rec-option-blocked"') + '>' +
                            esc(t.title) +
                            (t.mr_no ? ' — ' + esc(t.mr_no) : '') +
                            (t.eligible ? '' : ' · ' + esc(t.reason)) +
                            '</option>';
                    });
                    if (open) html += '</optgroup>';

                    $('#aiRecTarget').html(html);
                    $('#aiRecTargetHelp').text(
                        eligible.length
                            ? eligible.length + ' job post' + (eligible.length === 1 ? '' : 's') + ' available.'
                            : 'No job post here can be chosen for this applicant right now.'
                    );
                })
                .fail(function () {
                    $('#aiRecTarget').html('<option value="">Could not load job posts</option>');
                    $('#aiRecTargetHelp').text('The job posts could not be loaded. Close this and try again.');
                });
        });

        // Only enable the button once a real post is chosen, so the form cannot
        // be submitted empty and bounced back by the server.
        $('#aiRecTarget').on('change', function () {
            $('#aiRecSubmit').prop('disabled', !$(this).val());
        });

        load();
    });
</script>
