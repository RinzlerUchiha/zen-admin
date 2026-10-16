<style>
    .jp-page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .jp-page-head h5 {
        font-weight: 800;
        color: var(--zn-ink);
        letter-spacing: -.2px;
        margin: 0;
    }

    /* ===== Eligible positions — capsule layout ===== */
    .jp-eligible-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        box-shadow: 0 1px 3px rgba(32, 26, 22,.04);
        padding: 18px 20px;
        margin-bottom: 22px;
        max-height: 420px;
        overflow-y: auto;
    }


    /*
    | Waiting to be posted, grouped by job.
    |
    | Auto-fit rather than a fixed two columns: at 1366 the old pair of columns
    | left the source lines cramped, and on a narrow window they had nowhere to
    | go. This gives each job a sensible minimum and lets the count of columns
    | follow the width.
    */
    .jp-job-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr));
        gap: 14px;
    }

    .jp-job {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        padding: 12px 14px;
        min-width: 0;
    }

    /* A job wanted by more than one request is the case this grouping exists
       for, so it is marked rather than left to be inferred from the list. */
    .jp-job.is-combined {
        border-color: var(--zn-accent);
        background: var(--zn-accent-soft);
    }

    .jp-job-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 2px;
    }

    .jp-job-title {
        font-weight: 700;
        color: var(--zn-ink);
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .jp-job-count {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
        white-space: nowrap;
    }

    .jp-job-count strong { color: var(--zn-ink); }
    .jp-job-filled { color: var(--zn-ink-3); }

    .jp-job-note {
        margin: 0 0 8px;
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-2);
    }

    .jp-job-sources {
        list-style: none;
        margin: 8px 0 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .jp-job-source {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        border: 1px solid var(--zn-line);
        border-radius: 999px;
        background: var(--zn-surface);
        padding: 4px 4px 4px 12px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .jp-job-source:hover {
        border-color: var(--zn-line-2);
        box-shadow: 0 1px 6px rgba(32, 26, 22, .06);
    }

    .jp-job-source-text {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        flex: 1;
        min-width: 0;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink);
    }

    .jp-job-source .mr-no {
        font-weight: 800;
        color: var(--zn-accent-dark);
    }
















    .jp-btn-create-mini {
        border: none;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--zn-accent), var(--zn-accent-dark));
        color: var(--zn-on-accent);
        font-weight: 700;
        font-size: var(--zn-fs-xs);
        padding: 5px 12px;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(93, 37, 2,.22);
        transition: filter .15s ease, transform .15s ease;
    }

    .jp-btn-create-mini:hover {
        filter: brightness(1.08);
        transform: translateY(-1px);
    }

    .jp-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--zn-ink-3);
        background: var(--zn-surface);
        border: 1px dashed var(--zn-line);
        border-radius: var(--zn-radius-lg);
    }

    .jp-empty-state i {
        font-size: 26px;
        margin-bottom: 8px;
        display: block;
    }

    /* ===== Status chip ===== */
    .jp-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: var(--zn-fs-sm);
        font-weight: 700;
        letter-spacing: .2px;
    }

    .jp-status-chip::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .jp-status-draft {
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
    }

    .jp-status-published {
        background: var(--zn-ok-soft);
        color: var(--zn-ok);
    }

    .jp-status-closed {
        background: var(--zn-lock-soft);
        color: var(--zn-ink);
    }

    /* ===== Section divider ===== */
    .jp-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0 14px;
        font-weight: 700;
        font-size: var(--zn-fs);
        color: var(--zn-ink);
    }

    .jp-section-divider .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--zn-accent), var(--zn-accent-dark));
        box-shadow: 0 0 0 3px var(--zn-accent-soft);
    }

    .jp-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--zn-surface-2);
    }

    .jp-eligible-card::-webkit-scrollbar {
        width: 8px;
    }

    .jp-eligible-card::-webkit-scrollbar-track {
        background: transparent;
    }

    .jp-eligible-card::-webkit-scrollbar-thumb {
        background: var(--zn-line);
        border-radius: var(--zn-radius-lg);
    }

    .jp-eligible-card::-webkit-scrollbar-thumb:hover {
        background: var(--zn-line-2);
    }

    /* ===== Existing postings table ===== */
    .jp-table-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        /* auto, not hidden: the table now has a column more, and a clipped
           column on a narrow screen cannot be scrolled to */
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(32, 26, 22,.04);
        background: var(--zn-surface);
    }

    /* ===== Publishing: the confirmation, and the links it produces ===== */

    .jp-pub-dlg { width: min(520px, calc(100vw - 32px)); }

    .jp-pub-label {
        margin: 14px 0 6px;
        font-size: var(--zn-fs-sm);
        font-weight: 700;
        color: var(--zn-ink-2);
    }

    .jp-pub-channels { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }

    .jp-pub-channel {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 8px 10px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius);
        background: var(--zn-surface-2);
    }

    .jp-pub-channel-name { font-weight: 700; font-size: var(--zn-fs-sm); }

    /* The link itself, allowed to wrap: a tagged careers URL is long and
       truncating it would hide the very part that identifies the channel. */
    .jp-pub-link {
        flex: 1 1 220px;
        min-width: 0;
        font-size: 11.5px;
        color: var(--zn-ink-3);
        overflow-wrap: anywhere;
    }

    /*
    | The links, after the posting really is published.
    |
    | Sits above everything and stays until it is dismissed -- the page
    | reloads only when HR closes it, because a reload that arrived while they
    | were copying would take the links away at the worst moment.
    */
    .jp-pub-done .jp-pub-channels { max-height: min(46vh, 420px); overflow: auto; }

    /* "It worked" reads as the heading, not as a colour on a corner card. */
    .jp-pub-done .hf-dlg-title { color: var(--zn-ok, var(--zn-ink)); }

    /*
    | The inline fallback, for when the browser has no clipboard to write to.
    |
    | Full width on its own line so a long tagged URL is readable rather than
    | truncated -- the query string is the part that identifies the channel,
    | and a field showing only the start of it would be useless.
    */
    .jp-copy-fallback {
        display: block;
        flex-basis: 100%;
        margin-top: 6px;
    }

    .jp-copy-fallback-label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--zn-ink-2);
        margin-bottom: 3px;
    }

    .jp-copy-fallback-input {
        width: 100%;
        border: 1px solid var(--zn-accent);
        border-radius: var(--zn-radius);
        padding: 6px 8px;
        font-size: 11.5px;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        color: var(--zn-ink);
        background: var(--zn-surface);
    }

    .jp-copy-fallback-input:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px var(--zn-accent-soft);
    }

    .jp-copy-fallback-hint {
        display: block;
        font-size: 11px;
        color: var(--zn-ink-3);
        margin-top: 3px;
    }

    table.jp-list-table { min-width: 720px; }

    /* ===== Toolbar: search, status filter, result count ===== */
    .jp-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin: 14px 0 12px;
    }

    .jp-result-count {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
        margin-right: auto;
    }

    .jp-toolbar select,
    .jp-toolbar input[type="search"] {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 7px 12px;
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink-2);
        background: var(--zn-surface);
    }

    .jp-search-wrap { position: relative; display: inline-flex; align-items: center; }
    .jp-search-wrap input[type="search"] { width: 240px; max-width: 100%; padding-right: 28px; }

    .jp-search-wrap #jp-search-clear {
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

    .jp-search-wrap #jp-search-clear:hover { background: var(--zn-surface-2); color: var(--zn-ink); }

    /* ===== Sortable headers ===== */
    table.jp-list-table th[data-sort] {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    table.jp-list-table th[data-sort]:hover { color: var(--zn-accent-dark); }

    table.jp-list-table th[data-sort]::after {
        content: '';
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 6px;
        vertical-align: middle;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        opacity: .35;
        border-top: 5px solid currentColor;
    }

    table.jp-list-table th.is-sorted-asc::after {
        opacity: 1;
        border-top: none;
        border-bottom: 5px solid currentColor;
    }

    table.jp-list-table th.is-sorted-desc::after { opacity: 1; }

    /* the manpower request a posting came from */
    .jp-mr-link { font-weight: 600; white-space: nowrap; }

    /* ===== Page metrics ===== */
    .jp-stats {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
        margin: 14px 0 18px;
    }

    .jp-stat {
        display: block;
        padding: 10px 14px;
        border: 1px solid var(--zn-line);
        border-left-width: 3px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        color: inherit;
        text-decoration: none;
        min-width: 0;
    }

    a.jp-stat:hover, .jp-stat[data-status]:hover {
        border-color: var(--zn-accent);
        color: inherit;
        text-decoration: none;
    }

    .jp-stat-value { display: block; font-size: 20px; font-weight: 800; color: var(--zn-ink); line-height: 1.2; }
    .jp-stat-label { display: block; font-size: var(--zn-fs-xs); color: var(--zn-ink-3); }

    /* one colour, one meaning — matching the dashboard */
    .jp-stat.tone-attention { border-left-color: var(--hf-attention); }
    .jp-stat.tone-overdue   { border-left-color: var(--hf-overdue); }
    .jp-stat.tone-progress  { border-left-color: var(--hf-progress); }
    .jp-stat.tone-done      { border-left-color: var(--hf-done); }
    .jp-stat.tone-inactive  { border-left-color: var(--zn-line-2); }

    /* ===== Which ad template was used ===== */
    .jp-template {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 10px;
        padding: 9px 12px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-accent-soft);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-sm);
    }

    .jp-template { flex-direction: column; }
    .jp-template-head { display: flex; gap: 10px; align-items: flex-start; }
    .jp-template-head i { color: var(--zn-accent); margin-top: 2px; }
    .jp-template-reason { color: var(--zn-ink-3); font-size: var(--zn-fs-xs); }

    /* ===== Posting channels ===== */
    .jp-channels { display: flex; flex-direction: column; gap: 6px; }

    .jp-channel {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius);
        background: var(--zn-surface);
    }

    /* the places HR actually posts to stand out from the counted-only ones */
    .jp-channel.is-on { border-color: var(--zn-accent); background: var(--zn-accent-soft); }
    .jp-channel.is-passive { background: var(--zn-surface-2); }

    .jp-channel-name { font-weight: 600; color: var(--zn-ink); }

    .jp-channel-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 10px;
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-3);
    }

    .jp-channel-actions { display: flex; gap: 6px; align-items: center; }

    .jp-channel-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;                      /* was nothing at all: icon against the C */
        border: 1px solid var(--zn-line-2);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: var(--zn-radius);
        padding: 3px 9px;
        font-size: var(--zn-fs-xs);
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }

    .jp-channel-link:hover:not(:disabled) { border-color: var(--zn-accent); color: var(--zn-accent-dark); }
    .jp-channel-link:disabled { opacity: .5; cursor: not-allowed; }

    .jp-channel-count { font-variant-numeric: tabular-nums; }

    /* PLATFORM(S) on the postings list */
    .jp-platforms { display: flex; flex-wrap: wrap; gap: 4px; }

    .jp-platform-chip {
        font-size: var(--zn-fs-xs);
        font-weight: 600;
        padding: 1px 8px;
        border-radius: 20px;
        background: var(--zn-surface-2);
        color: var(--zn-ink-3);
        white-space: nowrap;
    }

    .jp-platform-chip.is-posted { background: var(--hf-done-soft); color: var(--hf-done); }

    @media (max-width: 575.98px) {
        .jp-channel { grid-template-columns: auto minmax(0, 1fr); }
        .jp-channel-actions { grid-column: 2; justify-content: flex-start; }
    }

    .jp-template-picker {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--zn-line);
        width: 100%;
    }

    .jp-template-btn {
        border: 1px solid var(--zn-line-2);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        cursor: pointer;
    }

    .jp-template-btn:hover:not(:disabled) { border-color: var(--zn-accent); color: var(--zn-accent-dark); }
    .jp-template-btn:disabled { opacity: .55; cursor: default; }

    /* the one currently in the box */
    .jp-template-btn.is-on {
        background: var(--zn-accent);
        border-color: var(--zn-accent);
        color: var(--zn-on-accent);
    }

    @media (max-width: 1200px) {
        .jp-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 640px) {
        .jp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* step 3 for this posting */
    .jp-panel-jump {
        display: inline-block;
        margin-top: 4px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
    }

    /* ===== Expand/shrink icon: turned by CSS, never rewritten by script ===== */
    #jp-panel-expand-btn .jp-expand-ico {
        display: inline-flex;
        transition: transform .18s ease;
    }

    #jp-panel-expand-btn .jp-expand-ico svg { width: 15px; height: 15px; }
    #jp-panel-expand-btn.is-wide .jp-expand-ico { transform: rotate(180deg); }

    table.jp-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        font-size: var(--zn-fs);
    }

    table.jp-list-table thead th {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        padding: 12px 16px;
        background: var(--zn-bg);
        border-bottom: 1px solid var(--zn-line);
        text-align: left;
        white-space: nowrap;
    }

    table.jp-list-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--zn-surface-2);
        vertical-align: middle;
        color: var(--zn-ink);
    }

    table.jp-list-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.jp-list-table tbody tr.jp-row-link {
        cursor: pointer;
        transition: background .15s ease;
    }

    table.jp-list-table tbody tr.jp-row-link:hover {
        background: var(--zn-accent-soft);
    }

    .jp-row-chevron {
        color: var(--zn-line-2);
        font-size: var(--zn-fs-sm);
        transition: color .15s ease, transform .15s ease;
    }

    table.jp-list-table tbody tr.jp-row-link:hover .jp-row-chevron {
        color: var(--zn-accent-dark);
        transform: translateX(3px);
    }

    /* The create form's field labels carry a button, like the panel's do. */
    .cp-label-row {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    /* Where to advertise, on the create form. Wraps rather than scrolls:
       there are five of them and they must all be visible to be unticked. */
    .cp-channels {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        padding: 8px 10px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius);
        background: var(--zn-surface-2);
    }

    .cp-channel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: var(--zn-fs);
        font-weight: 600;
        color: var(--zn-ink-2);
        cursor: pointer;
        margin: 0;
    }

    .cp-channel input { margin: 0; cursor: pointer; }
    .cp-channel:has(input:checked) { color: var(--zn-ink); }
    .cp-channel input:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: 2px; }

    /* ===== Create Posting modal polish ===== */
    #modal-create-posting .modal-content {
        border-radius: var(--zn-radius-lg);
        border: none;
        box-shadow: 0 12px 32px rgba(32, 26, 22,.16);
        overflow: hidden;
    }

    #modal-create-posting .modal-header {
        border-bottom: none;
        padding: 22px 26px 4px;
    }

    #modal-create-posting .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--zn-ink);
        letter-spacing: -.2px;
    }

    #modal-create-posting .modal-body {
        padding: 14px 26px 24px;
    }

    #modal-create-posting .modal-footer {
        border-top: 1px solid var(--zn-surface-2);
        background: var(--zn-surface-2);
        padding: 14px 26px;
    }

    #modal-create-posting .form-label {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
    }

    #modal-create-posting .form-control {
        border-radius: var(--zn-radius-lg);
        border: 1px solid var(--zn-line);
    }

    #modal-create-posting .form-control:focus {
        border-color: var(--zn-accent);
        box-shadow: 0 0 0 3px var(--zn-accent-soft);
    }

    #alert-box .alert {
        border-radius: var(--zn-radius-lg);
        border: none;
        font-size: var(--zn-fs);
    }

    /* ===== Slide-over panel ===== */
    .jp-panel-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(32, 26, 22,.35);
        z-index: 1050;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    .jp-panel-backdrop.open {
        opacity: 1;
        pointer-events: auto;
    }

    .jp-panel {
        position: fixed;
        top: 0;
        right: 0;
        height: 100%;
        width: 420px;
        max-width: 92vw;
        background: var(--zn-surface);
        box-shadow: -12px 0 32px rgba(32, 26, 22,.16);
        z-index: 1051;
        transform: translateX(100%);
        transition: transform .25s ease, width .2s ease;
        display: flex;
        flex-direction: column;
    }

    .jp-panel.open {
        transform: translateX(0);
    }

    /* Expanded width — toggled by #jp-panel-expand-btn */
    .jp-panel.jp-panel-wide {
        width: 880px;
    }

    .jp-panel-wide .jp-panel-desc-textarea {
        min-height: 55vh;
    }

    /*
    | Expanding used to buy nothing but wider textareas: the facts stayed in
    | one column and the channels and photos stayed stacked under them, so
    | the panel simply got longer as well as wider.
    */
    .jp-dist {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 0 24px;
        align-items: start;
    }

    .jp-panel-head-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .jp-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 22px 24px 16px;
        border-bottom: 1px solid var(--zn-surface-2);
    }

    .jp-panel-head h6 {
        font-weight: 800;
        font-size: var(--zn-fs-lg);
        color: var(--zn-ink);
        margin: 0 0 8px;
    }

    .jp-panel-close {
        border: none;
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background .15s ease, color .15s ease;
    }

    .jp-panel-close:hover {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .jp-panel-body {
        padding: 20px 24px;
        overflow-y: auto;
        flex: 1;
    }

    /*
    | The facts, at a glance.
    |
    | Two columns, because each of these is a word or a date and giving each
    | a full row of its own spent the whole first screen on things nobody
    | needs to read twice.
    */
    .jp-facts {
        display: grid;
        /*
        | Columns from the space there actually is, not from which class the
        | panel is wearing. The wide panel is capped at 92vw, so on a laptop
        | "wide" can be barely wider than narrow -- and a fixed four-column
        | rule there squeezed "Oct 13, 2026 05:21 PM" onto three lines. The
        | floor is the width a column has at the narrow size, so a column is
        | never tighter than it already was.
        */
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px 16px;
        margin: 0 0 12px;
        padding: 12px 14px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
    }

    .jp-fact { min-width: 0; }

    .jp-fact dt {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        margin: 0 0 1px;
    }

    .jp-fact dt i { color: var(--zn-line-2); }

    .jp-fact dd {
        margin: 0;
        font-size: var(--zn-fs);
        color: var(--zn-ink);
        overflow-wrap: anywhere;
    }

    /* The link out is an action, so it reads as one rather than as a caption
       hanging off whichever fact happened to be above it. */
    .jp-jump-row { display: inline-block; margin-bottom: 18px; }

    /*
    | A quiet rule between the three things this panel is for: what the
    | posting is, the words that go out, and where they go. Nine identical
    | uppercase labels in a column gave no sense of which of them belonged
    | together.
    */
    .jp-section {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 4px 0 14px;
        font-size: var(--zn-fs-sm);
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--zn-ink-2);
    }

    .jp-section::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--zn-line);
    }

    /* The internal jobspec, folded. */
    .jp-fold > summary {
        display: flex;
        align-items: baseline;
        gap: 8px;
        flex-wrap: wrap;
        cursor: pointer;
        list-style: none;
        padding: 7px 10px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius);
        background: var(--zn-surface-2);
    }

    .jp-fold > summary::-webkit-details-marker { display: none; }

    .jp-fold > summary::before {
        content: "\25B8";                 /* a caret that turns when open */
        color: var(--zn-ink-3);
        transition: transform .15s ease;
        display: inline-block;
    }

    .jp-fold[open] > summary::before { transform: rotate(90deg); }
    .jp-fold[open] > summary { margin-bottom: 8px; }
    .jp-fold > summary:hover { border-color: var(--zn-line-2); }
    .jp-fold > summary:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: 2px; }
    .jp-fold > summary .jp-panel-label { margin-bottom: 0; }

    .jp-fold-note {
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-3);
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
    }

    .jp-panel-field {
        margin-bottom: 18px;
    }

    .jp-panel-field:last-child {
        margin-bottom: 0;
    }

    .jp-panel-field-inline {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .jp-panel-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        margin-bottom: 5px;
    }

    .jp-panel-label i {
        font-size: var(--zn-fs-sm);
        color: var(--zn-line-2);
    }

    .jp-panel-value {
        font-size: var(--zn-fs);
        color: var(--zn-ink);
    }

    .jp-panel-value.mono {
        white-space: pre-wrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: var(--zn-fs-ui);
        line-height: 1.6;
        background: var(--zn-bg);
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 14px 16px;
        max-height: 260px;
        overflow-y: auto;
    }

    .jp-panel-value.mono::-webkit-scrollbar {
        width: 6px;
    }

    .jp-panel-value.mono::-webkit-scrollbar-track {
        background: transparent;
    }

    .jp-panel-value.mono::-webkit-scrollbar-thumb {
        background: var(--zn-line-2);
        border-radius: var(--zn-radius);
    }

    .jp-panel-desc-textarea {
        width: 100%;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: var(--zn-fs-ui);
        line-height: 1.6;
        color: var(--zn-ink);
        background: var(--zn-bg);
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 14px 16px;
        resize: vertical;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }

    .jp-photos {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
        gap: 8px;
        margin-top: 6px;
    }

    .jp-photo,
    .jp-photo-add {
        position: relative;
        aspect-ratio: 4 / 3;
        border-radius: var(--zn-radius);
        overflow: hidden;
        background: var(--zn-surface-2);
    }

    .jp-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .jp-photo-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        border: 0;
        border-radius: 50%;
        background: rgba(32, 26, 22, .7);
        color: #fff;
        font-size: var(--zn-fs-sm);
        line-height: 22px;
        padding: 0;
    }

    .jp-photo-add {
        border: 1px dashed var(--zn-line);
        color: var(--zn-ink-3);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
    }

    .jp-photo-add:hover {
        border-color: var(--zn-accent);
        color: var(--zn-accent);
    }

    .jp-panel-desc-textarea:focus {
        outline: none;
        background: var(--zn-surface);
        border-color: var(--zn-accent);
        box-shadow: 0 0 0 3px var(--zn-accent-soft);
    }

    .jp-panel-desc-textarea::-webkit-scrollbar {
        width: 6px;
    }

    .jp-panel-desc-textarea::-webkit-scrollbar-track {
        background: transparent;
    }

    .jp-panel-desc-textarea::-webkit-scrollbar-thumb {
        background: var(--zn-line-2);
        border-radius: var(--zn-radius);
    }

    .jp-desc-hint {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
        margin-top: 6px;
    }

    .jp-gen-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
        border: 1px solid var(--zn-line-2);
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
        border-radius: 20px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: none;
        padding: 3px 10px;
        transition: background .15s ease;
    }

    .jp-gen-btn:hover { background: var(--zn-line-2); }

    .jp-gen-btn:disabled { opacity: .6; }

    .jp-gen-btn i { font-size: var(--zn-fs-xs); }

    .jp-gen-btn + .jp-field-tag { margin-left: 6px; }

    .jp-field-tag {
        margin-left: auto;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .04em;
        background: var(--zn-surface-2);
        color: var(--zn-ink-3);
        border-radius: 20px;
        padding: 2px 8px;
    }

    .jp-field-tag-live {
        background: var(--zn-ok-soft);
        color: var(--zn-ok);
    }

    .jp-sync-state {
        font-size: var(--zn-fs-sm);
        line-height: 1.5;
        border-radius: var(--zn-radius-lg);
        padding: 7px 11px;
        margin-bottom: 7px;
    }

    .jp-sync-auto {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .jp-sync-custom {
        background: var(--zn-bg);
        color: var(--zn-ink-2);
    }

    .jp-sync-state i { margin-right: 5px; }

    .jp-public-warning {
        margin-top: 8px;
        font-size: var(--zn-fs-sm);
        line-height: 1.5;
        color: var(--zn-caution);
        background: var(--zn-caution-soft);
        border: 1px solid var(--zn-line-2);
        border-radius: var(--zn-radius-lg);
        padding: 9px 12px;
    }

    .jp-public-warning i {
        margin-right: 5px;
    }

    .jp-panel-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--zn-surface-2);
        background: var(--zn-surface-2);
        display: flex;
        gap: 10px;
        box-shadow: 0 -4px 12px rgba(32, 26, 22,.05);
        flex-shrink: 0;
    }

    .jp-panel-btn {
        flex: 1;
        border: none;
        border-radius: var(--zn-radius-lg);
        font-weight: 700;
        font-size: var(--zn-fs);
        padding: 12px 18px;
        transition: filter .15s ease, transform .15s ease;
    }

    .jp-panel-btn:hover {
        filter: brightness(1.08);
        transform: translateY(-1px);
    }

    .jp-panel-btn:disabled,
    .jp-panel-btn:disabled:hover {
        opacity: .6;
        filter: none;
        transform: none;
        cursor: default;
    }

    .jp-row-link:focus-visible {
        outline: 2px solid var(--zn-accent);
        outline-offset: -2px;
    }

    .jp-panel-btn-publish {
        background: linear-gradient(135deg, var(--zn-ok), var(--zn-ok));
        color: var(--zn-on-accent);
        box-shadow: 0 2px 8px rgba(30, 123, 69,.25);
    }

    /* "Close Posting" — ends the posting. NOT the same as the X that
    dismisses the panel (.jp-panel-close). Kept visually distinct. */
 /*
 | Closing a posting is the end of it, and it was the loudest thing on the
 | panel: a solid red button beside an outlined Save. The eye went to the one
 | action HR performs once, away from the one they perform constantly, and a
 | mis-click there cannot be taken back by pressing Save again.
 |
 | It keeps the danger colour -- it IS the dangerous one -- but as an outline,
 | which is the shape of a thing you have to mean.
 */
 .jp-panel-btn-close {
     background: transparent;
     color: var(--zn-warn);
     border: 1px solid var(--zn-warn);
     box-shadow: none;
     flex: 0 1 auto;
 }

 .jp-panel-btn-close:hover {
     background: var(--zn-warn);
     color: var(--zn-on-accent);
     filter: none;
 }

 /* Saving is what this panel is mostly for, so it looks like it. */
 .jp-panel-btn-save {
     background: var(--zn-accent);
     color: var(--zn-on-accent);
     border: 1px solid var(--zn-accent);
     box-shadow: 0 2px 8px rgba(32, 26, 22,.12);
     flex: 1 1 auto;
 }

 /* Beside Publish, Save is not the point. */
 .jp-panel-btn-save.is-quiet {
     background: var(--zn-surface);
     color: var(--zn-accent-dark);
     border-color: var(--zn-line-2);
     box-shadow: none;
 }

 .jp-panel-btn-save:disabled {
     opacity: .65;
     transform: none;
 }

 /* The icon and the label are two boxes with a gap between them, not a
    glyph with a margin guessed around it. */
 .jp-panel-btn {
     display: inline-flex;
     align-items: center;
     justify-content: center;
     gap: 9px;
 }

 .jp-panel-btn i { margin-right: 0; }

 .jp-panel-footer:empty {
     display: none;
 }

    .jp-panel-loading {
        text-align: center;
        color: var(--zn-ink-3);
        padding: 40px 20px;
    }
</style>

<div class="container-fluid">
    <div class="jp-page-head">
        <h5>Job Postings</h5>
    </div>

    {{-- This page's own figures. Each states its meaning in words; the colour
         only reinforces it. --}}
    <div class="jp-stats">
        <a class="jp-stat tone-attention" href="#jp-eligible">
            <span class="jp-stat-value">{{ $postingStats['waiting'] }}</span>
            <span class="jp-stat-label">Waiting to be posted</span>
        </a>
        <span class="jp-stat tone-progress" data-status="Draft">
            <span class="jp-stat-value">{{ $postingStats['draft'] }}</span>
            <span class="jp-stat-label">Draft</span>
        </span>
        <span class="jp-stat tone-done" data-status="Published">
            <span class="jp-stat-value">{{ $postingStats['published'] }}</span>
            <span class="jp-stat-label">Published</span>
        </span>
        <span class="jp-stat tone-overdue" data-status="Published">
            <span class="jp-stat-value">{{ $postingStats['no_applicants'] }}</span>
            <span class="jp-stat-label">Published, no applicants</span>
        </span>
        <span class="jp-stat tone-inactive" data-status="Closed">
            <span class="jp-stat-value">{{ $postingStats['closed'] }}</span>
            <span class="jp-stat-label">Closed</span>
        </span>
        <span class="jp-stat tone-inactive">
            <span class="jp-stat-value">{{ $postingStats['oldest_open_days'] ?? '—' }}</span>
            <span class="jp-stat-label">
                @if ($postingStats['oldest_open_days'] === null)
                    No open posting
                @else
                    Days, oldest open posting
                @endif
            </span>
        </span>
    </div>

    <div id="alert-box"></div>

{{--
    Waiting to be posted, grouped by the JOB rather than by the request.

    It used to be a card per manpower request, so the same job raised twice
    appeared as two unrelated rows under different MR numbers and the real
    headcount had to be added up by eye. Grouping by jobspec puts the figure
    on the row and keeps every request that contributed to it named underneath,
    each with its own Create button -- a posting is still raised against one
    position on one request, so none of those relationships are lost.
--}}
@forelse ($waitingByJob as $job)
    @if ($loop->first)
        <div class="jp-eligible-card" id="jp-eligible">
            <div class="jp-job-grid">
    @endif

    <div class="jp-job{{ $job['is_combined'] ? ' is-combined' : '' }}">
        <div class="jp-job-head">
            <span class="jp-job-title">{{ $job['title'] }}</span>
            <span class="jp-job-count"
                  title="{{ $job['filled'] }} of {{ $job['headcount'] }} already filled">
                Headcount: <strong>{{ $job['headcount'] }}</strong>
                @if ($job['filled'] > 0)
                    <span class="jp-job-filled">({{ $job['filled'] }} filled)</span>
                @endif
            </span>
        </div>

        @if ($job['is_combined'])
            <p class="jp-job-note">
                Asked for by {{ $job['request_count'] }} manpower requests.
                Each is posted separately, so every request keeps its own record.
            </p>
        @endif

        <ul class="jp-job-sources">
            @foreach ($job['sources'] as $src)
                @php $typeClass = strtolower($src['type']) === 'additional' ? 'jp-type-additional' : 'jp-type-replacement'; @endphp
                <li class="jp-job-source">
                    <span class="jp-job-source-text">
                        <span class="mr-no">{{ $src['mr_no'] }}</span>
                        <span class="sep">·</span>
                        <span class="jp-type-inline {{ $typeClass }}">{{ ucfirst($src['type']) }}</span>
                        <span class="sep">·</span>
                        <span class="headcount">
                            {{ $src['headcount'] }} {{ \Illuminate\Support\Str::plural('position', $src['headcount']) }}
                            @if ($src['filled'] > 0) ({{ $src['filled'] }} filled) @endif
                        </span>
                    </span>
                    <button type="button" class="jp-btn-create-mini btn-open-create-posting"
                        data-position-id="{{ $src['position_id'] }}">
                        Create
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    @if ($loop->last)
            </div>
        </div>
    @endif
@empty
    <div class="jp-empty-state">
        <i class="bi bi-inbox"></i>
        No approved requests with eligible positions found.
    </div>
@endforelse

<div class="jp-section-divider"><span class="dot"></span> Existing Postings</div>

@if (!$postings->isEmpty())
    <div class="jp-toolbar">
        <span class="jp-result-count" id="jp-result-count"></span>
        <select id="jp-status-filter" aria-label="Filter postings by status">
            <option value="">All statuses</option>
            <option value="Draft">Draft</option>
            <option value="Published">Published</option>
            <option value="Closed">Closed</option>
        </select>
        <div class="jp-search-wrap">
            <input type="search" id="jp-search" aria-label="Search postings"
                placeholder="Search title or MR no…">
            <button type="button" id="jp-search-clear" style="display:none;" aria-label="Clear search">&times;</button>
        </div>
    </div>
@endif

@if ($postings->isEmpty())
    <div class="jp-empty-state">
        <i class="bi bi-file-earmark-post"></i>
        No postings created yet.
    </div>
@else
    <div class="jp-table-card">
        <table class="jp-list-table">
            <thead>
                <tr>
                    <th data-sort="title">Title</th>
                    <th data-sort="mrno">MR No.</th>
                    <th>Channels</th>
                    <th data-sort="status">Status</th>
                    <th data-sort="posted">Posted</th>
                    <th data-sort="closed">Closed</th>
                    <th data-sort="created">Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($postings as $posting)
                    @php
                        $statusClass = match ($posting->status) {
                            'Draft' => 'jp-status-draft',
                            'Published' => 'jp-status-published',
                            'Closed' => 'jp-status-closed',
                            default => 'jp-status-draft',
                        };
                    @endphp
                    @php $mrNo = $posting->hireflowPosition?->request?->mr_no; @endphp
                    <tr class="jp-row-link" tabindex="0" data-posting-id="{{ $posting->id }}"
                        data-status="{{ $posting->status }}"
                        data-mrno="{{ $mrNo ?? '' }}"
                        data-title="{{ $posting->posting_title }}"
                        data-posted="{{ $posting->posted_at?->format('Y-m-d') ?? '' }}"
                        data-closed="{{ $posting->closed_at?->format('Y-m-d') ?? '' }}"
                        data-created="{{ $posting->created_at->format('Y-m-d') }}">
                        <td>{{ $posting->posting_title }}</td>
                        <td>
                            @if ($mrNo)
                                {{-- The request this posting came from; opens it in Manpower Requests. --}}
                                <a class="jp-mr-link" href="{{ url('/recruitment/manpower') }}?stat=approved"
                                    title="Open this manpower request">{{ $mrNo }}</a>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            {{-- Where HR said they posted this. Highlighted ones
                                 are up; the rest are planned but not posted yet. --}}
                            @php $posted = $posting->channels->where('state', 'Posted'); @endphp
                            @if ($posted->isNotEmpty())
                                <div class="jp-platforms">
                                    @foreach ($posted as $channel)
                                        <span class="jp-platform-chip is-posted">{{ $channel->label() }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted" title="Not recorded as posted anywhere yet">Careers only</span>
                            @endif
                        </td>
                        <td><span class="jp-status-chip {{ $statusClass }}">{{ $posting->status }}</span></td>
                        <td>{{ $posting->posted_at?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $posting->closed_at?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $posting->created_at->format('M d, Y') }}</td>
                        <td class="text-end"><i class="fa fa-chevron-right jp-row-chevron"></i></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="jp-empty-state" id="jp-no-results" style="display:none;">
        <i class="bi bi-search"></i>
        No posting matches your search or filter.
    </div>
@endif
</div>

<!-- Create Posting modal -->
<div class="modal fade" id="modal-create-posting" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Create Job Posting</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="create-posting-loading" class="text-center text-muted py-4">Loading draft…</div>
                <div id="create-posting-form" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Posting Title</label>
                        <input type="text" id="cp-title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Internal Jobspec <span class="jp-field-tag">Not public</span></label>
                        <textarea id="cp-description" class="form-control" rows="8" style="font-family: monospace; font-size: var(--zn-fs-ui);"></textarea>
                        <div class="form-text">Pre-filled from this position's Job Specification. HR reference only —
                            never shown to applicants.</div>
                    </div>
                    {{--
                        Composing the ad while writing the posting.

                        The first draft was handed over when the form opened
                        and that was the end of it: no Generate, and none of
                        the templates, until the posting had been saved and
                        reopened in the panel. Which is the one moment HR is
                        least likely to want to stop -- the ad is being written
                        NOW, and trying another wording is the whole point of
                        having templates.

                        Composing needs the position, not the posting, so
                        there was never anything to wait for.
                    --}}
                    <div class="mb-2">
                        <label class="form-label cp-label-row">Public Ad
                            <button type="button" class="jp-gen-btn" id="cp-generate-btn"
                                title="Re-compose from the Job Specification"><i class="fa fa-wand-magic-sparkles"></i>
                                Generate</button>
                            <span class="jp-field-tag jp-field-tag-live">Careers page</span>
                        </label>
                        <textarea id="cp-public" class="form-control" rows="14" style="font-size: var(--zn-fs);"></textarea>
                        <div class="form-text">Auto-composed from the Job Specification. This is what applicants read —
                            edit the wording freely before saving.</div>

                        {{-- Three ways to write the same Job Specification.
                             Switching only rewrites the draft in the box. --}}
                        <div class="jp-template" id="cp-template" style="display:none;">
                            <div class="jp-template-head">
                                <i class="fa fa-wand-magic-sparkles" aria-hidden="true"></i>
                                <span>
                                    Template: <strong class="jp-template-name"></strong>
                                    <span class="jp-template-reason d-block"></span>
                                </span>
                            </div>
                            <div class="jp-template-picker" id="cp-template-picker" role="group"
                                aria-label="Rewrite this ad with another template"></div>
                        </div>

                        <div class="jp-public-warning d-none" id="cp-public-warning">
                            <i class="fa fa-exclamation-triangle"></i> No public ad written. The careers page will fall
                            back to the internal jobspec, which exposes age, sex and headcount publicly.
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label cp-label-row" for="cp-short">Short Description
                            <button type="button" class="jp-gen-btn" id="cp-short-generate-btn"
                                title="Draft from the Job Specification"><i class="fa fa-wand-magic-sparkles"></i>
                                Draft</button>
                            <span class="jp-field-tag jp-field-tag-live">Job boards &amp; sharing</span>
                        </label>
                        <textarea id="cp-short" class="form-control" rows="3" maxlength="{{ \App\Services\Recruitment\Ad\JobShortDescription::MAX }}"></textarea>
                        <div class="form-text"><span id="cp-short-count">0</span> / {{ \App\Services\Recruitment\Ad\JobShortDescription::MAX }}
                            characters — two or three plain sentences for anywhere the full ad does not fit: a job
                            board's summary, a social post, the careers list. No emoji needed.</div>
                    </div>

                    {{--
                        Pictures for the ad.

                        They were only ever reachable from a posting that already
                        existed, which is the wrong moment: HR has the photographs
                        in front of them while they are writing the ad, not after
                        saving it and reopening the panel.

                        They belong to the Job Specification, so the position this
                        posting is being raised against already names the folder --
                        there is nothing to attach to a posting that does not exist
                        yet, which is why this works before Save.
                    --}}
                    <div class="mb-3">
                        <label class="form-label">Photos <span class="jp-field-tag jp-field-tag-live">Careers page</span></label>
                        <div class="jp-photos" id="cp-photos"></div>
                        <input type="file" id="cp-photo-input" accept="image/jpeg,image/png" class="d-none">
                        <div class="form-text">Shown beside the ad on the careers page — up to
                            {{ \App\Services\Recruitment\JobSpecPhotos::MAX }}, JPG or PNG. They belong to this
                            position's Job Specification, so every posting of it shows the same photos.
                            <strong>Saved as soon as they are added</strong>, before the posting itself.</div>
                    </div>

                    {{--
                        Where this job is going to be advertised.

                        Every site starts ticked, because the places we advertise
                        do not change from posting to posting -- the exceptions do.
                        Unticking one here means no row is created for it, so it is
                        never offered a link and never counted.

                        A tick is NOT a claim that the ad has gone up. Nothing here
                        posts anything: HireFlow has no API to any of these sites.
                        It decides which tagged links HR is handed on publishing.
                    --}}
                    @php
                        $cpDefault = config('posting_channels.default', 'direct');
                        $cpChannels = collect(config('posting_channels.channels', []))
                            ->filter(fn ($c, $k) => ($c['external'] ?? true) && $k !== $cpDefault);
                    @endphp
                    <div class="mb-2">
                        <label class="form-label" id="cp-channels-label">Where to advertise it
                            <span class="jp-field-tag">Tagged links</span></label>
                        <div class="cp-channels" id="cp-channels" role="group" aria-labelledby="cp-channels-label">
                            @foreach ($cpChannels as $key => $channel)
                                <label class="cp-channel">
                                    <input type="checkbox" class="form-check-input cp-channel-tick"
                                        value="{{ $key }}" checked>
                                    <span>{{ $channel['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="form-text">Each one gets its own tagged Careers link to post by hand, and
                            HireFlow counts the visits and applications that come back through it. All on by
                            default — untick anywhere you are not posting this job. HireFlow cannot post to
                            these sites itself.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="cp-save-btn">Save as Draft</button>
                <button type="button" class="btn btn-success" id="cp-publish-btn">Publish</button>
            </div>
        </div>
    </div>
</div>

<!-- Slide-over panel: posting details -->
<div class="jp-panel-backdrop" id="jp-panel-backdrop"></div>
<div class="jp-panel" id="jp-panel" role="dialog" aria-modal="true" aria-labelledby="jpp-title" tabindex="-1">
    <div class="jp-panel-head">
        <div>
            <h6 id="jpp-title">—</h6>
            <span id="jpp-status" class="jp-status-chip jp-status-draft">—</span>
        </div>
        <div class="jp-panel-head-actions">
            <button type="button" class="jp-panel-close" id="jp-panel-expand-btn"
                title="Expand panel" aria-label="Expand panel" aria-pressed="false">
                <span class="jp-expand-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h6v6" /><path d="M9 21H3v-6" />
                        <path d="M21 3l-7 7" /><path d="M3 21l7-7" />
                    </svg>
                </span>
            </button>
            <button type="button" class="jp-panel-close" id="jp-panel-close-btn" title="Dismiss panel">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="jp-panel-body">
        <div id="jp-panel-loading" class="jp-panel-loading">Loading…</div>
        <div id="jp-panel-content" class="d-none">
            {{--
                What this posting IS, in one block.

                These five facts used to be five stacked rows, each with its
                own uppercase heading and its own line, and together they
                filled the panel before anything you could act on came into
                view. They are the things you read once and then ignore, so
                they are now read at a glance: two columns, four at the wider
                width, and the ad itself starts near the top of the panel
                instead of below the fold.
            --}}
            <dl class="jp-facts">
                <div class="jp-fact">
                    <dt><i class="fa fa-briefcase" aria-hidden="true"></i> Position</dt>
                    <dd id="jpp-position">—</dd>
                </div>
                <div class="jp-fact">
                    <dt><i class="fa fa-file-alt" aria-hidden="true"></i> Request</dt>
                    <dd id="jpp-mrno">—</dd>
                </div>
                <div class="jp-fact">
                    <dt><i class="fa fa-user" aria-hidden="true"></i> Created by</dt>
                    <dd id="jpp-createdby">—</dd>
                </div>
                <div class="jp-fact">
                    <dt><i class="fa fa-calendar-check" aria-hidden="true"></i> Posted</dt>
                    <dd id="jpp-postedat">—</dd>
                </div>
                <div class="jp-fact">
                    <dt><i class="fa fa-calendar-times" aria-hidden="true"></i> Closed</dt>
                    <dd id="jpp-closedat">—</dd>
                </div>
            </dl>

            {{-- Straight through to step 3 for this posting. An action, so it
                 sits on its own rather than buried under a fact. --}}
            <a class="jp-panel-jump jp-jump-row" id="jpp-applicants-link"
                href="{{ url('/recruitment/applicant-intake') }}">See applicants for this posting &rarr;</a>

            {{-- Matching and shortlisting for this posting. An action like the
                 one above, and the only way into either screen: both need a
                 posting or a specification to be about, so neither can sit in
                 the sidebar. --}}
            <a class="jp-panel-jump jp-jump-row" id="jpp-matching-link"
                href="#">Match and shortlist candidates &rarr;</a>

            <a class="jp-panel-jump jp-jump-row" id="jpp-requirements-link"
                href="#" style="display:none">Set mandatory requirements &rarr;</a>

            <h3 class="jp-section">The advertisement</h3>
            {{--
                Folded shut to begin with.

                It is the one box on this panel that nobody outside HR ever
                reads -- it says so itself -- and it was ten rows tall, sitting
                above the ad that applicants actually see. Closed it costs a
                line; open it is exactly what it was. Nothing about it is
                read-only or conditional: the textarea is still in the page,
                still saved by Save Description, still made readonly on a
                closed posting.
            --}}
            <details class="jp-panel-field jp-fold">
                <summary class="jp-fold-head">
                    <span class="jp-panel-label"><i class="fa fa-lock"></i> Internal Jobspec
                        <span class="jp-field-tag">Not public</span></span>
                    <span class="jp-fold-note">HR reference only — never shown to applicants</span>
                </summary>
                <textarea class="jp-panel-desc-textarea" id="jpp-description-input" rows="10"></textarea>
                <div class="jp-desc-hint">Auto-drafted from the jobspec. HR reference only — never shown on the
                    careers page.</div>
            </details>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-globe"></i> Public Ad
                    <button type="button" class="jp-gen-btn" id="jpp-generate-btn"
                        title="Re-compose from the Job Specification"><i class="fa fa-wand-magic-sparkles"></i>
                        Generate</button>
                    <span class="jp-field-tag jp-field-tag-live">Careers page</span>
                </div>
                <div class="jp-sync-state" id="jpp-sync-state"></div>
                <textarea class="jp-panel-desc-textarea" id="jpp-public-input" rows="14"
                    placeholder="Write the ad applicants will read. Emoji and Taglish are fine — this is the copy that goes live."></textarea>
                <div class="jp-desc-hint" id="jpp-desc-hint">Click <strong>Save Description</strong> below. Saving
                    does not change the posting status.</div>
                {{-- Three ways to write the same Job Specification. The rule
                     recommends one; HR can try the others and keep whichever
                     reads best. Switching only rewrites the draft in the box —
                     nothing is saved until Save Description. --}}
                <div class="jp-template" id="jpp-template" style="display:none;">
                    <div class="jp-template-head">
                        <i class="fa fa-wand-magic-sparkles" aria-hidden="true"></i>
                        <span>
                            Template: <strong class="jp-template-name"></strong>
                            <span class="jp-template-reason d-block"></span>
                        </span>
                    </div>
                    <div class="jp-template-picker" id="jpp-template-picker" role="group"
                        aria-label="Rewrite this ad with another template"></div>
                </div>

                <div class="jp-public-warning d-none" id="jpp-public-warning">
                    <i class="fa fa-exclamation-triangle"></i> No public ad written. The careers page will fall back
                    to the internal jobspec, which exposes age, sex and headcount publicly.
                </div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-align-left"></i> Short Description
                    <button type="button" class="jp-gen-btn" id="jpp-short-generate-btn"
                        title="Draft from the Job Specification"><i class="fa fa-wand-magic-sparkles"></i>
                        Draft</button>
                    <span class="jp-field-tag jp-field-tag-live">Job boards &amp; sharing</span>
                </div>
                <textarea class="jp-panel-desc-textarea" id="jpp-short-input" rows="4"
                    maxlength="{{ \App\Services\Recruitment\Ad\JobShortDescription::MAX }}"
                    placeholder="Two or three plain sentences about the job, for job boards, social posts and the careers list."></textarea>
                <div class="jp-desc-hint"><span id="jpp-short-count">0</span> / {{ \App\Services\Recruitment\Ad\JobShortDescription::MAX }}
                    characters · saved with <strong>Save Description</strong>.</div>
            </div>
            <h3 class="jp-section">Where it goes</h3>
            <div class="jp-dist">
            {{-- Where HR advertises this posting.

                 HireFlow cannot publish to LinkedIn or JobStreet — no API —
                 so ticking a channel does not post anything. It records that
                 HR is putting the job there and hands back a tagged link to
                 paste, which is what makes the traffic countable. --}}
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-share-nodes"></i> Posting Channels
                    <span class="jp-field-tag">Manual posting</span>
                </div>
                <div class="jp-channels" id="jpp-channels"></div>
                <div class="jp-desc-hint">
                    Tick where you are posting this job, then copy that channel's link and paste it there.
                    HireFlow counts visits and applications arriving through each link — these are
                    <strong>Careers page</strong> figures, not the job board's own view counts,
                    which those sites do not share with us.
                </div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-images"></i> Photos
                    <span class="jp-field-tag jp-field-tag-live">Careers page</span>
                </div>
                <div class="jp-photos" id="jpp-photos"></div>
                <input type="file" id="jpp-photo-input" accept="image/jpeg,image/png" class="d-none">
                <div class="jp-desc-hint">Shown beside the posting on the careers page — up to
                    {{ \App\Services\Recruitment\JobSpecPhotos::MAX }}, JPG or PNG. They belong to this position's Job
                    Specification, so every posting of it shows the same photos. Saved as soon as they are added.</div>
            </div>
            </div>{{-- /.jp-dist --}}
        </div>
    </div>
    <div class="jp-panel-footer" id="jp-panel-footer"></div>
</div>

<script>
    $(function() {
        let currentPositionId = null;
        let currentPostingId = null;
        let panelTrigger = null;

        /**
         * Escapes a value for dropping into hand-built HTML.
         *
         * Same helper the Applicant Intake list uses. Blade escapes what it
         * renders; markup assembled in JavaScript is on its own.
         */
        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        // One place to write and clear the banner, so a stale error cannot
        // survive a later successful action.
        function showAlert(type, message) {
            $('#alert-box').html('<div class="alert alert-' + type + '">' + message + '</div>');
        }

        function clearAlert() {
            $('#alert-box').empty();
        }

        /*
        | Whether the create modal has finished appearing.
        |
        | Bootstrap IGNORES hide() while a modal is still transitioning in, so
        | a Publish pressed in those 300ms leaves the modal and its backdrop
        | stacked behind the result dialog. The title is pre-filled from the
        | draft, so there is nothing a quick user has to type first -- which
        | makes this reachable rather than theoretical.
        */
        let cpModalOpen = false;
        let cpModalSettled = false;

        $('#modal-create-posting')
            .on('show.bs.modal', function () { cpModalOpen = true; cpModalSettled = false; })
            .on('shown.bs.modal', function () { cpModalSettled = true; })
            .on('hidden.bs.modal', function () { cpModalOpen = false; cpModalSettled = false; });

        // ===== Create Posting modal (unchanged behavior) =====
        $('.btn-open-create-posting').on('click', async function() {
            clearAlert();
            currentPositionId = $(this).data('position-id');

            $('#create-posting-loading').removeClass('d-none');
            $('#create-posting-form').addClass('d-none');
            $('#modal-create-posting').modal('show');

            try {
                const draft = await GET('recruitment/job-postings/draft/' + currentPositionId);
                $('#cp-title').val(draft.title);
                $('#cp-description').val(draft.description);
                $('#cp-public').val(draft.public_ad || '');
                $('#cp-short').val(draft.short_description || '');

                /* Every site ticked again: the form is a new posting each
                   time it opens, not the last one HR half-filled. */
                $('#cp-channels .cp-channel-tick').prop('checked', true);

                /* Same for the ad tools: no template left showing from the
                   last position, no stale warning, counter in step. */
                $('#cp-template').hide();
                $('#cp-public-warning').addClass('d-none');
                updateShortCount(JP_AD_CREATE);

                $('#create-posting-loading').addClass('d-none');
                $('#create-posting-form').removeClass('d-none');

                /* After the form is visible, so the widget has somewhere to
                   draw. Its own failure message covers a disk that cannot be
                   read; it must not stop the ad being written. */
                draftPhotos.load();
            } catch (err) {
                showAlert('danger', 'Failed to load draft.');
                $('#modal-create-posting').modal('hide');
            }
        });

        async function createPosting() {
            const title = $('#cp-title').val();
            const description = $('#cp-description').val();
            const publicAd = $('#cp-public').val();

            if (!title) {
                showAlert('danger', 'Posting title is required.');
                return null;
            }

            return await POST('recruitment/job-postings', {
                request_position_id: currentPositionId,
                posting_title: title,
                posting_description: description,
                public_description: publicAd,
                short_description: $('#cp-short').val(),

                /* Only the sites still ticked get a row, and only a channel
                   with a row is ever offered a link or counted. Sending an
                   empty array is meaningful -- "nowhere but Careers" -- which
                   is why it is always sent, even when nothing is ticked. */
                channels: cpTickedChannels().map(c => c.key)
            });
        }

        /*
        | The sites still ticked on the create form, in the shape the publish
        | dialog and the copy panel already speak.
        |
        | `link` is null because there is nothing to link to yet: the posting
        | does not exist, so neither does its tagged URL. The real links are
        | read back from the server after publishing.
        */
        function cpTickedChannels() {
            return $('#cp-channels .cp-channel-tick:checked').map(function () {
                return {
                    key: this.value,
                    name: $(this).next('span').text(),
                    link: null
                };
            }).get();
        }

        $('#cp-save-btn').on('click', async function() {
            try {
                const result = await createPosting();
                if (!result) return;
                location.reload();
            } catch (err) {
                showAlert('danger', 'Failed to create posting.');
            }
        });

        /*
        | Publishing straight from the create form.
        |
        | This used to create the posting, publish it and reload, with no
        | confirmation and nothing handed back -- the links HR needs in order
        | to actually advertise the job were silently lost at the one moment
        | they were wanted. The panel's publish button had already been given
        | a confirmation and a copy panel; this one had not, and this is the
        | button HR presses far more often.
        */
        $('#cp-publish-btn').on('click', async function() {
            const $btn = $(this);

            /* Checked before the dialog, so HR is never asked to confirm
               something that is then refused for a missing title. */
            if (!$('#cp-title').val()) {
                showAlert('danger', 'Posting title is required.');
                return;
            }

            /*
            | No dialog before this point.
            |
            | There was one, and it was wrong: it asked "publish this?" of
            | somebody who had just filled in a form and pressed Publish on
            | it, and then showed them links that did not exist yet. The
            | confirmation belongs AFTER the posting is live, where it can
            | report what actually happened and hand over the real links.
            |
            | Pressing Publish on a form you have just written is already the
            | deliberate act; the panel's own Publish button still asks first,
            | because that one acts on a posting already saved.
            */
            clearAlert();

            const original = $btn.html();
            $('#modal-create-posting .modal-footer .btn').prop('disabled', true);
            $btn.html('<i class="fa fa-spinner fa-spin"></i> Publishing…');

            try {
                const result = await createPosting();
                if (!result) { throw new Error('The posting was not created.'); }

                const res = await fetch(`${BASE}/recruitment/job-postings/${result.id}/status`, {
                    method: 'PATCH',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ status: 'Published' })
                });

                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(`Status ${res.status}: ${text}`);
                }

                /*
                | Published, on the server's word and not before.
                |
                | The links are read back rather than assembled here: what the
                | posting actually offers is whatever rows it ended up with,
                | which is the server's answer, not the form's.
                */
                const body = await res.json().catch(() => ({}));

                if (body.status === 'Published') {
                    let links = [];

                    try {
                        links = jpChannelsFromPayload(
                            await GET('recruitment/job-postings/' + result.id + '/channels'));
                    } catch (err) {
                        /* The posting IS published; failing to list its links
                           must not be reported as a failed publish. They are
                           still on its panel. */
                        console.error('[posting-channels] could not read the new posting back', err);
                    }

                    /*
                    | Out of the way first: the create modal's backdrop would
                    | sit over the result.
                    |
                    | Awaited, not fired and forgotten. Bootstrap ignores a
                    | hide() issued while the modal is still transitioning in,
                    | and the form is filled from the draft, so a quick Publish
                    | really can land inside that window -- leaving two stacked
                    | backdrops behind the result. The race is rare and the
                    | wait costs nothing, so it is waited on rather than hoped
                    | about. The timeout is there so a missed event can never
                    | strand the links.
                    */
                    const $createModal = $('#modal-create-posting');

                    /*
                    | Asked of the events, not of the class.
                    |
                    | .show is not on the element for the first moments of the
                    | transition, so testing for it skipped the hide entirely
                    | in exactly the case this code exists to handle, and left
                    | the modal open behind the result.
                    */
                    if (cpModalOpen) {
                        /* Let it finish arriving before asking it to leave --
                           a hide() issued mid-transition is simply dropped. */
                        if (!cpModalSettled) {
                            await new Promise(function (there) {
                                $createModal.one('shown.bs.modal', there);
                                setTimeout(there, 600);
                            });
                        }

                        await new Promise(function (gone) {
                            $createModal.one('hidden.bs.modal', gone);
                            setTimeout(gone, 600);
                            $createModal.modal('hide');
                        });
                    }

                    /* The reload waits for Done, so the links cannot vanish
                       before they have been used. */
                    jpShowCopyPanel(links, () => location.reload(), { title: $('#cp-title').val() });

                    return;
                }

                location.reload();
            } catch (err) {
                console.error(err);
                showAlert('danger', 'Failed to publish posting: ' + err.message);
                $('#modal-create-posting .modal-footer .btn').prop('disabled', false);
                $btn.html(original);
            }
        });

        // ===== Slide-over panel =====
        const STATUS_CLASS = {
            'Draft': 'jp-status-draft',
            'Published': 'jp-status-published',
            'Closed': 'jp-status-closed'
        };

        function openPanel() {
            $('#jp-panel-backdrop').addClass('open');
            $('#jp-panel').addClass('open');

            // Move focus into the panel so keyboard users are not left
            // behind the backdrop.
            $('#jp-panel-close-btn').trigger('focus');
        }

        function closePanel() {
            if (!$('#jp-panel').hasClass('open')) {
                return;
            }

            $('#jp-panel-backdrop').removeClass('open');
            $('#jp-panel').removeClass('open');
            setPanelWide(false);

            // Return focus to the row that opened the panel.
            if (panelTrigger && document.body.contains(panelTrigger)) {
                $(panelTrigger).trigger('focus');
            }
            panelTrigger = null;
        }

        function renderFooter(status) {
            const editable = status !== 'Closed';
            let html = '';

            /*
            | Which button is the loud one depends on what the posting is for.
            |
            | On a draft the point is to publish it, so Publish leads and Save
            | steps back. Once it is live, saving the wording is the thing HR
            | does over and over, and the only other button ends the posting
            | for good -- so Save leads and Close is the quiet one.
            */
            const quietSave = status === 'Draft';

            if (editable) {
                html +=
                    '<button type="button" class="jp-panel-btn jp-panel-btn-save' +
                    (quietSave ? ' is-quiet' : '') + '" id="jp-save-desc-btn">' +
                    '<i class="fa fa-save"></i>Save Description</button>';
            }

            if (status === 'Draft') {
                html +=
                    '<button type="button" class="jp-panel-btn jp-panel-btn-publish jp-panel-status-btn" data-status="Published">' +
                    '<i class="fa fa-bullhorn"></i>Publish Posting</button>';
            } else if (status === 'Published') {
                html +=
                    '<button type="button" class="jp-panel-btn jp-panel-btn-close jp-panel-status-btn" data-status="Closed">' +
                    '<i class="fa fa-lock"></i>Close Posting</button>';
            }

            $('#jp-panel-footer').html(html);
            $('#jpp-description-input').prop('readonly', !editable);
            $('#jpp-public-input').prop('readonly', !editable);
            $('#jpp-generate-btn').prop('disabled', !editable);
            $('#jpp-short-input').prop('readonly', !editable);
            $('#jpp-short-generate-btn').prop('disabled', !editable);
            $('#jpp-desc-hint').toggle(editable);
        }

        /*
         | ===== Posting channels =====
         |
         | Where HR advertises this posting. HireFlow has no API to LinkedIn,
         | JobStreet or PhilJobNet, so nothing here publishes anything: HR
         | ticks where they are posting, copies that channel's tagged link,
         | and pastes it there by hand.
         |
         | The counts are ours alone — visits to the Careers page that arrived
         | through each link, and the applications that followed. They are not
         | the job board's impressions, and the wording never implies they are.
         */
        let jpChannels = [];         // this posting's rows, as the server sees them
        let jpChannelsAvailable = []; // everything HR could tick
        let jpChannelsReady = false;  // is CAREERS_URL configured?

        function jpChannelRow(channel, available) {
            const on = channel && channel.state === 'Posted';
            const passive = !available.external;

            // Counted-only buckets (direct traffic, walk-ins) have no tick and
            // no link: nobody posts to them.
            const tick = passive
                ? '<span class="jp-channel-count" title="Counted, not posted to">' +
                  '<i class="fa fa-chart-simple text-muted"></i></span>'
                : '<input type="checkbox" class="form-check-input js-channel-tick" ' +
                  'data-key="' + esc(available.key) + '"' + (on ? ' checked' : '') +
                  ' aria-label="Posted on ' + esc(available.name) + '">';

            const visits = channel ? channel.visits : 0;
            const applications = channel ? channel.applications : 0;

            const meta = [];
            if (channel && channel.posted_at && on) { meta.push('Posted ' + esc(channel.posted_at)); }
            meta.push('<span class="jp-channel-count">' + visits + ' careers ' +
                (visits === 1 ? 'visit' : 'visits') + '</span>');
            meta.push('<span class="jp-channel-count">' + applications + ' ' +
                (applications === 1 ? 'application' : 'applications') + '</span>');
            if (channel && channel.conversion !== null && channel.conversion !== undefined) {
                meta.push(channel.conversion + '% applied');
            }

            let actions = '';
            if (!passive) {
                actions = jpChannelsReady
                    ? '<button type="button" class="jp-channel-link js-channel-copy" ' +
                      'data-link="' + esc((channel && channel.link) || '') + '">' +
                      '<i class="fa fa-copy"></i> Copy link</button>'
                    : '<button type="button" class="jp-channel-link" disabled ' +
                      'title="Set CAREERS_URL so HireFlow knows the public Careers address">' +
                      'Link unavailable</button>';
            }

            return '<div class="jp-channel' + (on ? ' is-on' : '') + (passive ? ' is-passive' : '') + '">' +
                tick +
                '<div><div class="jp-channel-name">' + esc(available.name) + '</div>' +
                '<div class="jp-channel-meta">' + meta.join('<span>·</span>') + '</div></div>' +
                '<div class="jp-channel-actions">' + actions + '</div>' +
            '</div>';
        }

        function jpRenderChannels(payload) {
            jpChannels = payload.channels || [];
            if (payload.available) { jpChannelsAvailable = payload.available; }
            if ('links_ready' in payload) { jpChannelsReady = !!payload.links_ready; }

            const byKey = {};
            jpChannels.forEach(c => { byKey[c.key] = c; });

            $('#jpp-channels').html(
                jpChannelsAvailable.map(a => jpChannelRow(byKey[a.key], a)).join('')
            );
        }

        async function jpLoadChannels(postingId) {
            $('#jpp-channels').html('<div class="jp-desc-hint">Loading channels…</div>');

            let payload;

            // Fetching and rendering are caught separately: lumping them
            // together once reported a rendering bug as a failed request,
            // which pointed the debugging at the wrong end of the wire.
            try {
                payload = await GET('recruitment/job-postings/' + postingId + '/channels');
            } catch (err) {
                console.error('[posting-channels] request failed', err);
                $('#jpp-channels').html('<div class="jp-desc-hint">Could not load posting channels: '
                    + esc(err.message) + '</div>');
                return;
            }

            try {
                jpRenderChannels(payload);
            } catch (err) {
                console.error('[posting-channels] render failed', err, payload);
                $('#jpp-channels').html('<div class="jp-desc-hint">Posting channels loaded but could not be '
                    + 'displayed: ' + esc(err.message) + '</div>');
            }
        }

        /** Saves the ticks as soon as one changes — there is no separate Save here. */
        async function jpSaveChannels() {
            const selections = {};
            $('#jpp-channels .js-channel-tick').each(function () {
                selections[$(this).data('key')] = {
                    state: this.checked ? 'Posted' : 'Not Posted',
                };
            });

            try {
                const data = await PUT('recruitment/job-postings/' + currentPostingId + '/channels',
                    { channels: selections });
                jpRenderChannels({ channels: data.channels });

                // A channel with traffic behind it is kept rather than deleted,
                // so say so — otherwise it looks like the untick did not take.
                if ((data.retained || []).length) {
                    showAlert('info', 'Kept ' + data.retained.length +
                        ' channel(s) that already have visits or applications recorded. ' +
                        'They are marked Not Posted rather than removed, so the figures survive.');
                }
            } catch (err) {
                showAlert('danger', 'Could not save posting channels: ' + err.message);
                jpLoadChannels(currentPostingId);
            }
        }

        $(document).on('change', '.js-channel-tick', jpSaveChannels);

        /*
        | Copying a channel's link, and what happens when the browser will not.
        |
        | navigator.clipboard needs a secure context: HTTPS, or localhost. Served
        | over plain HTTP from a hostname -- which is how an internal tool often
        | is -- it is simply not there, and the failure is not an edge case but
        | every copy, every time.
        |
        | The fallback used to be window.prompt, which put the link in a browser
        | dialog: unstyled, impossible to read on a phone, and the one native
        | dialog left in a flow where every other one had been replaced. It is
        | now a field in the panel itself, with the link already selected, so
        | Ctrl+C works immediately and the text can also be dragged or read.
        |
        | Nothing here publishes, re-publishes or reloads. A failed copy is a
        | failed copy; the posting is already live and the panel stays exactly
        | as it was.
        */
        function jpCopyFallback($btn, link) {
            /* One field per button, reused rather than stacked up if copying
               is attempted more than once. */
            let $box = $btn.siblings('.jp-copy-fallback');

            if (!$box.length) {
                $box = $(
                    '<span class="jp-copy-fallback">' +
                        '<label class="jp-copy-fallback-label">Copy this link</label>' +
                        '<input type="text" class="jp-copy-fallback-input" readonly>' +
                        '<span class="jp-copy-fallback-hint">Press Ctrl+C (or Cmd+C)</span>' +
                    '</span>'
                );
                $btn.after($box);

                const id = 'jp-copy-' + Math.random().toString(36).slice(2, 8);
                $box.find('.jp-copy-fallback-input').attr('id', id);
                $box.find('.jp-copy-fallback-label').attr('for', id);
            }

            const $field = $box.find('.jp-copy-fallback-input');
            $field.val(link);

            /* Focused and selected, so a keyboard user can copy without
               reaching for the mouse, and a mouse user sees it is ready. */
            $field.trigger('focus');
            $field[0].setSelectionRange(0, link.length);

            /* Selecting again on click: the first Ctrl+C often follows a
               stray click that collapsed the selection. */
            $field.off('click.jpcopy').on('click.jpcopy', function () {
                this.setSelectionRange(0, this.value.length);
            });

            return $box;
        }

        $(document).on('click', '.js-channel-copy', async function () {
            const link = $(this).data('link');
            if (!link) { return; }
            const $btn = $(this);

            /*
            | No clipboard API at all is the commonest case on plain HTTP, and
            | it throws a TypeError rather than rejecting, so it is checked
            | before being called.
            */
            if (!navigator.clipboard || !navigator.clipboard.writeText) {
                jpCopyFallback($btn, link);

                return;
            }

            try {
                await navigator.clipboard.writeText(link);
                const original = $btn.html();
                $btn.html('<i class="fa fa-check"></i> Copied');
                setTimeout(() => $btn.html(original), 1500);
                /* It worked, so any field left over from an earlier failure
                   is no longer telling the truth. */
                $btn.siblings('.jp-copy-fallback').remove();
            } catch (err) {
                jpCopyFallback($btn, link);
            }
        });

        /**
         * Names the template that composed the ad, and offers the others.
         *
         * Generating used to produce one ad with no alternative; the choice
         * was invisible and unchangeable.
         */
        /*
        | The two places an ad gets composed: the panel, and the create form.
        |
        | They differ in which boxes they fill and which URL they ask -- the
        | panel has a posting, the create form has only the position it is
        | being raised against. Everything else is the same, so it is the same
        | code with a scope rather than two copies that drift.
        */
        const JP_AD_PANEL = {
            gen: '#jpp-generate-btn',
            shortBtn: '#jpp-short-generate-btn',
            publicInput: '#jpp-public-input',
            shortInput: '#jpp-short-input',
            shortCount: '#jpp-short-count',
            templateBox: '#jpp-template',
            picker: '#jpp-template-picker',
            warning: '#jpp-public-warning',
            url: () => 'recruitment/job-postings/' + currentPostingId + '/suggest-ad'
        };

        const JP_AD_CREATE = {
            gen: '#cp-generate-btn',
            shortBtn: '#cp-short-generate-btn',
            publicInput: '#cp-public',
            shortInput: '#cp-short',
            shortCount: '#cp-short-count',
            templateBox: '#cp-template',
            picker: '#cp-template-picker',
            warning: '#cp-public-warning',
            url: () => 'recruitment/job-postings/draft/' + currentPositionId + '/suggest-ad'
        };

        function jpShowTemplate(template, templates, scope) {
            scope = scope || JP_AD_PANEL;

            const $box = $(scope.templateBox);
            if (!template) { $box.hide(); return; }

            $box.find('.jp-template-name').text(template.name);
            $box.find('.jp-template-reason').text(template.reason || '');

            const $picker = $(scope.picker).empty();
            (templates || []).forEach(function (t) {
                $('<button type="button" class="jp-template-btn"></button>')
                    .text(t.name)
                    .attr('title', t.description)
                    .attr('aria-pressed', t.key === template.key ? 'true' : 'false')
                    .toggleClass('is-on', t.key === template.key)
                    .on('click', function () { jpGenerateAd(t.key, scope); })
                    .appendTo($picker);
            });

            $box.show();
        }

        /**
         * Composes the public ad, optionally with a chosen template.
         * Only the draft in the box changes; nothing is stored until save.
         */
        async function jpGenerateAd(templateKey, scope) {
            scope = scope || JP_AD_PANEL;

            const $btn = $(scope.gen);
            const original = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Composing…');
            $(scope.picker).find('.jp-template-btn').prop('disabled', true);

            try {
                const query = templateKey ? ('?template=' + encodeURIComponent(templateKey)) : '';
                const data = await GET(scope.url() + query);
                $(scope.publicInput).val(data.public_description || '');
                $(scope.warning).addClass('d-none');
                jpShowTemplate(data.template, data.templates, scope);
                showAlert('info', data.template
                    ? (data.template.chosen_by_hr
                        ? 'Ad rewritten with the ' + data.template.name + ' template. Review it, then click Save Description.'
                        : 'Ad composed using the ' + data.template.name + ' template. Try another below, or click Save Description.')
                    : 'Ad composed from the Job Specification. Review it, then click Save Description.');
            } catch (err) {
                showAlert('danger', 'Ad not composed: ' + err.message);
            } finally {
                $btn.prop('disabled', false).html(original);
                $(scope.picker).find('.jp-template-btn').prop('disabled', false);
            }
        }

        /**
         * Search + status filter + column sort over the postings table.
         *
         * The list previously had none of these: every posting ever created
         * was shown, newest first, with no way to narrow it.
         */
        function jpApplyTableFilters() {
            const q = ($('#jp-search').val() || '').trim().toLowerCase();
            const status = $('#jp-status-filter').val();
            const $rows = $('.jp-list-table tbody tr');
            let shown = 0;

            $rows.each(function () {
                const r = $(this);
                const hitText = q === ''
                    || (r.data('title') || '').toString().toLowerCase().includes(q)
                    || (r.data('mrno') || '').toString().toLowerCase().includes(q);
                const hitStatus = status === '' || r.data('status') === status;
                const hit = hitText && hitStatus;
                r.toggle(hit);
                if (hit) { shown++; }
            });

            $('#jp-search-clear').toggle(q !== '');
            $('#jp-no-results').toggle(shown === 0);
            $('#jp-result-count').text(
                (q === '' && status === '') ? '' : 'Showing ' + shown + ' of ' + $rows.length
            );
        }

        let jpSort = { key: null, dir: 1 };

        function jpSortTable(key) {
            jpSort = { key: key, dir: jpSort.key === key ? -jpSort.dir : 1 };

            const $body = $('.jp-list-table tbody');
            const rows = $body.children('tr').get();

            rows.sort(function (a, b) {
                const av = ($(a).data(key) || '').toString().toLowerCase();
                const bv = ($(b).data(key) || '').toString().toLowerCase();
                if (av === bv) { return 0; }
                // Blanks ("—" dates) always sort last, whichever way we are going.
                if (av === '') { return 1; }
                if (bv === '') { return -1; }
                return av > bv ? jpSort.dir : -jpSort.dir;
            });

            $body.append(rows);
            $('.jp-list-table th[data-sort]')
                .removeClass('is-sorted-asc is-sorted-desc')
                .filter('[data-sort="' + key + '"]')
                .addClass(jpSort.dir === 1 ? 'is-sorted-asc' : 'is-sorted-desc');
        }

        $('#jp-search').on('input', jpApplyTableFilters);
        $('#jp-status-filter').on('change', jpApplyTableFilters);
        $('#jp-search-clear').on('click', function () {
            $('#jp-search').val('').trigger('input').focus();
        });
        $('.jp-list-table th[data-sort]').on('click', function () {
            jpSortTable($(this).data('sort'));
        });

        // A figure you can act on: clicking one filters the list to it.
        $('.jp-stat[data-status]').on('click', function () {
            $('#jp-status-filter').val($(this).data('status')).trigger('change');
            document.querySelector('.jp-list-table')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }).css('cursor', 'pointer');

        // ===== Expand / shrink panel (resets every time the panel closes) =====
        function setPanelWide(wide) {
            $('#jp-panel').toggleClass('jp-panel-wide', wide);
            // State lives on the button; CSS turns the arrows round. Nothing
            // here touches the icon element, so Font Awesome's <i> to <svg>
            // replacement cannot leave the icon stuck.
            $('#jp-panel-expand-btn')
                .toggleClass('is-wide', wide)
                .attr('title', wide ? 'Shrink panel' : 'Expand panel')
                .attr('aria-label', wide ? 'Shrink panel' : 'Expand panel')
                .attr('aria-pressed', wide ? 'true' : 'false');
        }

        $('#jp-panel-expand-btn').on('click', function() {
            setPanelWide(!$('#jp-panel').hasClass('jp-panel-wide'));
        });

        // Shows whether this ad tracks the jobspec or has been hand-written.
        function renderSyncState(isCustom) {
            const $el = $('#jpp-sync-state');

            if (isCustom) {
                $el.attr('class', 'jp-sync-state jp-sync-custom')
                    .html('<i class="fa fa-pen"></i> Hand-edited — this wording is protected and will not be ' +
                        'overwritten when the jobspec changes. Click Generate to return it to auto-sync.');
            } else {
                $el.attr('class', 'jp-sync-state jp-sync-auto')
                    .html('<i class="fa fa-rotate"></i> Auto-synced with the Job Specification — correct the ' +
                        'jobspec in HireFlow and this ad follows automatically.');
            }
        }

        // ===== Re-compose the public ad from the jobspec =====
        // Fills the textarea only — nothing is stored until Save.
        function updateShortCount(scope) {
            scope = scope || JP_AD_PANEL;
            $(scope.shortCount).text($(scope.shortInput).val().length);
        }

        /**
         * Wires Generate, Draft and the character counter for one scope.
         *
         * Called once for the panel and once for the create form. Every
         * selector comes from the scope, so neither set of buttons can reach
         * into the other's boxes -- which matters, because the create form
         * opens over a page that may still hold a panel's state.
         */
        function jpWireAdTools(scope) {
            $(document).on('input', scope.shortInput, function () { updateShortCount(scope); });

            $(document).on('click', scope.gen, async function () {
                clearAlert();

                const current = $(scope.publicInput).val().trim();

                if (current && !await hfConfirm({
                    title: 'Replace the public ad?',
                    message: 'A freshly composed ad will replace what is there now.',
                    detail: 'Your edits to this ad will be lost. Nothing is saved until you press Save.',
                    confirmLabel: 'Replace ad',
                    tone: 'warn'
                })) {
                    return;
                }

                // null = use the recommended template; the picker passes a key.
                await jpGenerateAd(null, scope);
            });

            // Fills the textarea only — nothing is stored until Save.
            $(document).on('click', scope.shortBtn, async function () {
                clearAlert();
                const $btn = $(this);

                if ($(scope.shortInput).val().trim() && !await hfConfirm({
                    title: 'Replace the short description?',
                    message: 'A fresh draft will replace what is there now.',
                    detail: 'Your edits to the short description will be lost.',
                    confirmLabel: 'Replace description',
                    tone: 'warn'
                })) {
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Drafting…');

                try {
                    const data = await GET(scope.url());
                    $(scope.shortInput).val(data.short_description || '');
                    updateShortCount(scope);
                    showAlert('info', 'Short description drafted from the Job Specification. Review it, then click Save Description.');
                } catch (err) {
                    console.error(err);
                    showAlert('danger', 'Could not draft a short description for this posting.');
                } finally {
                    $btn.prop('disabled', false).html('<i class="fa fa-wand-magic-sparkles"></i> Draft');
                }
            });
        }

        jpWireAdTools(JP_AD_PANEL);
        jpWireAdTools(JP_AD_CREATE);

        // ===== Photos (the Job Specification's; saved immediately) =====
        let photosEditable = true;

        async function photoRequest(method, url, body) {
            const res = await fetch(`${BASE}/${url}`, {
                method,
                redirect: 'error',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.message || (data.errors && Object.values(data.errors)[0][0]) || ('Status ' + res.status));
            }
            return data;
        }

        /*
        | The photo picker, mounted twice.
        |
        | Once in the posting panel and once in the Create Posting form. The
        | two differ in exactly two ways -- which element they draw into, and
        | which URL they talk to -- so they are the same widget rather than
        | the same sixty lines written out again.
        |
        | Every handler is scoped to its own container. A delegated
        | '.jp-photo-remove' would otherwise fire on both mounts at once and
        | the modal's remove would also act on the panel behind it.
        */
        function mountPhotos(opts) {
            const editable = () => (opts.editable ? opts.editable() : true);

            function render(data) {
                const photos = (data && data.photos) || [];
                const on = editable();

                let html = photos.map(p =>
                    '<div class="jp-photo"><img src="' + p.url + '" alt="" loading="lazy">' +
                    (on ? '<button type="button" class="jp-photo-remove" data-name="' + p.name +
                        '" title="Remove photo" aria-label="Remove photo"><i class="fa fa-times"></i></button>' : '') +
                    '</div>').join('');

                if (on && photos.length < (data ? data.max : 0)) {
                    html += '<button type="button" class="jp-photo-add"><i class="fa fa-plus"></i><span>Add photo</span></button>';
                }

                $(opts.container).html(html || '<span class="text-muted small">No photos.</span>');
            }

            async function load() {
                $(opts.container).html('<span class="text-muted small">Loading photos…</span>');
                try {
                    render(await GET(opts.url()));
                } catch (err) {
                    $(opts.container).html('<span class="text-muted small">Photos could not be loaded.</span>');
                }
            }

            $(document).on('click', opts.container + ' .jp-photo-add', function() {
                $(opts.input).val('').trigger('click');
            });

            $(document).on('change', opts.input, async function() {
                if (!this.files.length) return;
                clearAlert();

                const form = new FormData();
                form.append('photo', this.files[0]);
                $(opts.container + ' .jp-photo-add').prop('disabled', true).find('span').text('Uploading…');

                try {
                    render(await photoRequest('POST', opts.url(), form));
                } catch (err) {
                    showAlert('danger', 'Photo not added: ' + err.message);
                    load();
                }
            });

            $(document).on('click', opts.container + ' .jp-photo-remove', async function() {
                const name = $(this).data('name');

                if (!await hfConfirm({
                    title: 'Remove this photo?',
                    message: 'Photos belong to the Job Specification, not to one posting.',
                    detail: 'It will stop appearing beside every posting of this position, including ones already published.',
                    confirmLabel: 'Remove photo',
                    tone: 'warn'
                })) { return; }

                clearAlert();

                try {
                    render(await photoRequest('DELETE', opts.url() + '/' + encodeURIComponent(name)));
                } catch (err) {
                    showAlert('danger', 'Photo not removed: ' + err.message);
                }
            });

            return { render, load };
        }

        const panelPhotos = mountPhotos({
            container: '#jpp-photos',
            input: '#jpp-photo-input',
            url: () => 'recruitment/job-postings/' + currentPostingId + '/photos',
            editable: () => photosEditable
        });

        /* Addressed by POSITION, because the posting does not exist yet. */
        const draftPhotos = mountPhotos({
            container: '#cp-photos',
            input: '#cp-photo-input',
            url: () => 'recruitment/job-postings/draft/' + currentPositionId + '/photos'
        });

        // ===== Save description only (no status change) =====
        $(document).on('click', '#jp-save-desc-btn', async function() {
            clearAlert();
            const $btn = $(this);
            const posting_description = $('#jpp-description-input').val();
            const public_description = $('#jpp-public-input').val();
            const short_description = $('#jpp-short-input').val();

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>Saving…');

            try {
                const res = await fetch(`${BASE}/recruitment/job-postings/${currentPostingId}/description`, {
                    method: 'PATCH',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ posting_description, public_description, short_description })
                });

                if (!res.ok) {
                    throw new Error(`Status ${res.status}: ${await res.text()}`);
                }

                $('#jpp-public-warning').toggleClass('d-none', !!public_description.trim());
                try { renderSyncState((await res.clone().json()).ad_is_custom); } catch (e) {}
                $btn.html('<i class="fa fa-check"></i>Saved');
                setTimeout(function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i>Save Description');
                }, 1500);
            } catch (err) {
                console.error(err);
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i>Save Description');
                showAlert('danger', 'Failed to save description: ' + err.message );
            }
        });

        async function loadPosting(id) {
            clearAlert();
            currentPostingId = id;
            $('#jp-panel-loading').removeClass('d-none');
            $('#jp-panel-content').addClass('d-none');
            $('#jp-panel-footer').html('');
            openPanel();

            try {
                const data = await GET('recruitment/job-postings/' + id + '/json');

                $('#jpp-title').text(data.posting_title);
                $('#jpp-template').hide();
                $('#jpp-applicants-link').attr(
                    'href',
                    BASE + '/recruitment/applicant-intake?posting=' + encodeURIComponent(data.posting_title || '')
                );

                $('#jpp-matching-link').attr('href',
                    BASE + '/recruitment/postings/' + encodeURIComponent(id) + '/matching');

                /*
                | Classifying requirements needs the job specification, which a
                | posting only has if its position was given one. Hidden rather
                | than broken when there is none -- a link to a specification
                | that does not exist is worse than no link.
                */
                $('#jpp-requirements-link')
                    .attr('href', data.jobspec_id
                        ? BASE + '/recruitment/job-specifications/'
                            + encodeURIComponent(data.jobspec_id) + '/requirements'
                        : '#')
                    .toggle(!!data.jobspec_id);
                $('#jpp-status').text(data.status).attr('class', 'jp-status-chip ' + (STATUS_CLASS[data
                    .status] || 'jp-status-draft'));
                $('#jpp-position').text(data.position_title);
                $('#jpp-mrno').text(data.mr_no);
                $('#jpp-description-input').val(data.posting_description || '');
                $('#jpp-public-input').val(data.public_description || '');
                $('#jpp-public-warning').toggleClass('d-none', !!(data.public_description || '').trim());
                renderSyncState(data.ad_is_custom);
                $('#jpp-short-input').val(data.short_description || '');
                updateShortCount(JP_AD_PANEL);
                /* The name when we have one, with the number kept as the
                   tooltip so nothing is lost by making it readable. */
                $('#jpp-createdby')
                    .text(data.created_by_name || data.created_by || '—')
                    .attr('title', data.created_by || '');
                $('#jpp-postedat').text(data.posted_at ?? 'Not yet published');
                $('#jpp-closedat').text(data.closed_at ?? 'Not closed');

                renderFooter(data.status);
                photosEditable = data.status !== 'Closed';
                panelPhotos.load();
                jpLoadChannels(id);

                $('#jp-panel-loading').addClass('d-none');
                $('#jp-panel-content').removeClass('d-none');
            } catch (err) {
                showAlert('danger', 'Failed to load posting.');
                closePanel();
            }
        }

        $(document).on('click', '.jp-row-link', function() {
            panelTrigger = this;
            const id = $(this).data('posting-id');
            loadPosting(id);
        });

        $('#jp-panel-close-btn, #jp-panel-backdrop').on('click', closePanel);

        // Escape closes the slide-over, matching the Bootstrap modal on the
        // same page. Ignored while the create-posting modal is open so the
        // two do not both react to one keypress.
        $(document).on('keydown.jpPanel', function(e) {
            if (e.key !== 'Escape') {
                return;
            }
            if ($('#modal-create-posting').hasClass('show')) {
                return;
            }
            closePanel();
        });

        /*
        | Publishing, confirmed, with the links it will produce.
        |
        | Publish used to be the one status change with no confirmation at
        | all: Close asked, Publish just went. It is the more consequential
        | of the two -- the posting becomes visible on the Careers site and
        | starts accepting applications -- and HR had no sight of which
        | channels were about to be offered until afterwards.
        |
        | The dialog is built here rather than through hfConfirm because it
        | carries a list, not a sentence. Everything else about it follows the
        | same rules: Escape and the backdrop cancel, focus is trapped, focus
        | returns to the button, and nothing says "published" until the server
        | has said so.
        */
        function jpPublishDialog(posting, channels) {
            return new Promise(function (resolve) {
                const opener = document.activeElement;

                const rows = channels.length
                    ? channels.map(c =>
                        '<li class="jp-pub-channel">' +
                            '<span class="jp-pub-channel-name">' + esc(c.name) + '</span>' +
                            (c.link
                                ? '<code class="jp-pub-link">' + esc(c.link) + '</code>'
                                : '<span class="jp-desc-hint">No link — set CAREERS_URL</span>') +
                        '</li>').join('')
                    : '<li class="jp-desc-hint">No external channels are switched on for this posting. ' +
                      'It will still be published on the Careers site, and anyone arriving ' +
                      'untagged is counted as direct.</li>';

                const back = document.createElement('div');
                back.className = 'hf-dlg-back';
                back.style.zIndex = '2000';

                const box = document.createElement('div');
                box.className = 'hf-dlg jp-pub-dlg';
                box.style.zIndex = '2001';
                box.setAttribute('role', 'alertdialog');
                box.setAttribute('aria-modal', 'true');
                box.setAttribute('aria-labelledby', 'jp-pub-title');

                box.innerHTML =
                    '<h2 class="hf-dlg-title" id="jp-pub-title">Publish this job posting?</h2>' +
                    '<p class="hf-dlg-msg"><b>' + esc(posting.title) + '</b>' +
                        (posting.mr_no ? ' · ' + esc(posting.mr_no) : '') + '</p>' +
                    '<p class="hf-dlg-detail">It becomes visible on the Careers site and starts ' +
                        'accepting applications. You can close it again at any time.</p>' +
                    '<p class="jp-pub-label">Links you will be given to post by hand</p>' +
                    '<ul class="jp-pub-channels">' + rows + '</ul>' +
                    '<p class="hf-dlg-detail">HireFlow does not post to these sites itself. ' +
                        'It gives you a tagged link for each one so applications coming back ' +
                        'can be counted against it.</p>' +
                    '<div class="hf-dlg-row">' +
                        '<button type="button" class="hf-dlg-btn hf-dlg-cancel">Cancel</button>' +
                        '<button type="button" class="hf-dlg-btn hf-dlg-ok">Publish posting</button>' +
                    '</div>';

                function close(answer) {
                    document.removeEventListener('keydown', onKey, true);
                    back.remove();
                    box.remove();
                    if (opener && opener.focus && document.contains(opener)) { opener.focus(); }
                    resolve(answer);
                }

                function onKey(e) {
                    if (e.key === 'Escape') { e.preventDefault(); close(false); return; }
                    if (e.key !== 'Tab') { return; }

                    const f = [...box.querySelectorAll('button')].filter(b => !b.disabled);
                    if (!f.length) { return; }

                    const first = f[0];
                    const last = f[f.length - 1];

                    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
                }

                box.querySelector('.hf-dlg-cancel').addEventListener('click', () => close(false));
                box.querySelector('.hf-dlg-ok').addEventListener('click', () => close(true));
                back.addEventListener('click', () => close(false));
                document.addEventListener('keydown', onKey, true);

                document.body.appendChild(back);
                document.body.appendChild(box);
                box.querySelector('.hf-dlg-ok').focus();
            });
        }

        /*
        | Which channels this posting will actually offer a link for.
        |
        | Only the ones that exist on the posting -- a channel HR unticked has
        | no row and must not appear here -- and only the external ones, since
        | the direct bucket is where untagged traffic lands rather than
        | somewhere anybody posts.
        */
        /*
        | Every place this posting can be advertised by hand.
        |
        | This was called jpEnabledChannels, which was a lie: it filtered on
        | `external`, never on whether HR had ticked the channel as Posted. The
        | name said one thing and the code did another, and since seeding now
        | gives a posting all five external channels up front, "enabled" would
        | have meant "all of them" in every case anyway.
        |
        | Filtering on the tick would be worse, not better: at the moment of
        | publishing, nothing is ticked yet -- HR ticks a channel once they
        | have actually posted it, which is after they have the link. Doing
        | that would have emptied the copy panel exactly when it is needed.
        |
        | So: every external channel, and the ticks stay what they always
        | were, a record of where the ad has really gone up.
        */
        function jpExternalChannels() {
            return jpChannelsFromPayload({ channels: jpChannels, available: jpChannelsAvailable });
        }

        /*
        | The same reduction, from a payload that is not the open panel's.
        |
        | Publishing from the create form has no panel state to read: the
        | posting was made a moment ago and its channels are fetched for that
        | one purpose. Both paths reduce the answer the same way, so the copy
        | panel cannot show one thing in one flow and another in the other.
        */
        function jpChannelsFromPayload(payload) {
            const external = {};
            (payload.available || jpChannelsAvailable || []).forEach(a => {
                if (a.external) { external[a.key] = a; }
            });

            return (payload.channels || [])
                .filter(c => external[c.key])
                .map(c => ({ key: c.key, name: external[c.key].name, link: c.link || null }));
        }

        /*
        | After it really is published: the links, to copy.
        |
        | Shown only once the server has confirmed, and listing only the
        | channels that were switched on. The page reloads afterwards, so this
        | is the one moment HR has the whole set in front of them.
        */
        function jpShowCopyPanel(channels, onDismiss, posting) {
            const opener = document.activeElement;

            const back = document.createElement('div');
            back.className = 'hf-dlg-back';
            back.style.zIndex = '2000';

            const box = document.createElement('div');
            box.className = 'hf-dlg jp-pub-dlg jp-pub-done';
            box.style.zIndex = '2001';
            box.setAttribute('role', 'dialog');
            box.setAttribute('aria-modal', 'true');
            box.setAttribute('aria-labelledby', 'jp-done-title');

            const rows = channels.length
                ? '<p class="jp-pub-label">Links to post by hand</p>' +
                  '<ul class="jp-pub-channels">' +
                  channels.map(c =>
                    '<li class="jp-pub-channel">' +
                        '<span class="jp-pub-channel-name">' + esc(c.name) + '</span>' +
                        (c.link
                            ? '<code class="jp-pub-link">' + esc(c.link) + '</code>' +
                              '<button type="button" class="jp-channel-link js-channel-copy" ' +
                              'data-link="' + esc(c.link) + '"><i class="fa fa-copy"></i> Copy</button>'
                            : '<span class="jp-desc-hint">No link — set CAREERS_URL</span>') +
                    '</li>').join('') +
                  '</ul>' +
                  '<p class="hf-dlg-detail">They are switched on already. Post each one where you ' +
                      'said you would — HireFlow counts the visits and applications that come ' +
                      'back through it.</p>'
                : '<p class="hf-dlg-detail">It is advertised on the Careers page only. Anyone ' +
                      'arriving without a tagged link is counted as direct.</p>';

            box.innerHTML =
                '<h2 class="hf-dlg-title" id="jp-done-title">Published</h2>' +
                '<p class="hf-dlg-msg"><b>' + esc((posting && posting.title) || 'The posting') + '</b>' +
                    ' is live on the Careers site and accepting applications.</p>' +
                rows +
                '<div class="hf-dlg-row">' +
                    '<button type="button" class="hf-dlg-btn hf-dlg-ok">Done</button>' +
                '</div>';

            function close() {
                document.removeEventListener('keydown', onKey, true);
                back.remove();
                box.remove();
                if (opener && opener.focus && document.contains(opener)) { opener.focus(); }
                if (onDismiss) { onDismiss(); }
            }

            function onKey(e) {
                if (e.key === 'Escape') { e.preventDefault(); close(); return; }
                if (e.key !== 'Tab') { return; }

                const f = [...box.querySelectorAll('button, input')].filter(b => !b.disabled);
                if (!f.length) { return; }

                const first = f[0];
                const last = f[f.length - 1];

                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }

            box.querySelector('.hf-dlg-ok').addEventListener('click', close);
            back.addEventListener('click', close);
            document.addEventListener('keydown', onKey, true);

            document.body.appendChild(back);
            document.body.appendChild(box);

            /* The first link, not the Done button: copying is what this is
               for, and Done is one Tab away from wherever you stop. */
            (box.querySelector('.js-channel-copy') || box.querySelector('.hf-dlg-ok')).focus();
        }

        $(document).on('click', '.jp-panel-status-btn', async function() {
            const $btn = $(this);
            const status = $btn.data('status');
            const posting_description = $('#jpp-description-input').val();

            if (status === 'Closed' && !await hfConfirm({
                title: 'Close this job posting?',
                message: 'It will stop accepting applicants.',
                detail: 'Applications already made are unaffected. The panel stays open.',
                confirmLabel: 'Close posting',
                tone: 'warn'
            })) {
                return;
            }

            /*
            | Publishing asks too, and shows what it will produce.
            |
            | The channels are re-read first rather than trusted from whenever
            | the panel was last drawn: HR may have ticked something a minute
            | ago, and the list in the dialog has to be the list the posting
            | will actually offer.
            */
            let publishedChannels = [];

            if (status === 'Published') {
                try {
                    jpRenderChannels(await GET('recruitment/job-postings/' + currentPostingId + '/channels'));
                } catch (err) {
                    /* The dialog is still worth showing without them: it is
                       the confirmation that matters, and the links are
                       offered again afterwards. */
                    console.error('[posting-channels] could not refresh before publishing', err);
                }

                publishedChannels = jpExternalChannels();

                if (!await jpPublishDialog({
                    title: $('#jpp-title').text() || 'this posting',
                    mr_no: $('#jpp-mrno').text() || ''
                }, publishedChannels)) {
                    return;
                }
            }

            clearAlert();

            // Both footer buttons lock while the request is in flight, so a
            // slow publish cannot be double-submitted.
            const original = $btn.html();
            $('#jp-panel-footer .jp-panel-btn').prop('disabled', true);
            $btn.html('<i class="fa fa-spinner fa-spin"></i>Saving…');

            try {
                const res = await fetch(`${BASE}/recruitment/job-postings/${currentPostingId}/status`, {
                    method: 'PATCH',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        status,
                        posting_description
                    })
                });

                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(`Status ${res.status}: ${text}`);
                }

                /*
                | Published, and only now said so.
                |
                | The copy panel is appended to the body before the reload so
                | it survives it -- it does not, which is the point: the links
                | are read from the server-confirmed state on the way through,
                | and the panel is rebuilt from the reloaded page. Nothing
                | here claims success before this line.
                */
                if (status === 'Published') {
                    const body = await res.json().catch(() => ({}));

                    /*
                    | The server's word, not ours.
                    |
                    | "Published" is only said once the response says so.
                    | If it did not, the reload below still happens and the
                    | list repaints from whatever actually took effect.
                    */
                    if (body.status === 'Published') {
                        /* The reload WAITS for this dialog. Showing the links
                           and then reloading a moment later would take them
                           away before anybody could use them, which is the
                           one thing this dialog exists to prevent. */
                        jpShowCopyPanel(publishedChannels, () => location.reload(),
                            { title: $('#jpp-title').text() });

                        return;
                    }
                }

                // The full page reload repaints the list and the panel state,
                // so re-fetching the posting first would only be discarded.
                location.reload();
            } catch (err) {
                console.error(err);
                $('#jp-panel-footer .jp-panel-btn').prop('disabled', false);
                $btn.html(original);
                showAlert('danger', 'Failed to update status: ' + err.message);
            }
        });
    });
</script>
