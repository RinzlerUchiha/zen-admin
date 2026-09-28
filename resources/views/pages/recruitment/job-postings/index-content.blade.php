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

    .jp-eligible-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    @media (max-width: 900px) {
        .jp-eligible-grid {
            grid-template-columns: 1fr;
        }
    }

    .jp-mr-group {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        padding: 12px 14px;
    }

    .jp-mr-label {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        margin-bottom: 8px;
    }

    .jp-mr-label .mr-no {
        color: var(--zn-accent-dark);
        font-weight: 800;
        letter-spacing: 0;
        text-transform: none;
        font-size: var(--zn-fs-ui);
        margin-left: 4px;
    }

    .jp-capsule-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .jp-capsule {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        border: 1px solid var(--zn-line);
        border-radius: 999px;
        background: var(--zn-surface-2);
        padding: 4px 4px 4px 12px;
        width: 100%;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .jp-capsule:hover {
        border-color: var(--zn-line-2);
        box-shadow: 0 1px 6px rgba(32, 26, 22,.06);
    }

    .jp-capsule-text {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink);
    }

    .jp-capsule-title {
        white-space: nowrap;
        font-weight: 700;
    }

    .jp-capsule-meta {
        display: flex;
        align-items: center;
        white-space: nowrap;
        flex-shrink: 0;
        margin-left: auto;
        padding-left: 10px;
    }

    .jp-capsule-meta .sep {
        color: var(--zn-line-2);
        margin: 0 4px;
    }

    .jp-capsule-meta .jp-type-inline {
        font-weight: 700;
        width: 78px;
        display: inline-block;
    }

    .jp-capsule-meta .jp-type-additional {
        color: var(--zn-accent-dark);
    }

    .jp-capsule-meta .jp-type-replacement {
        color: var(--zn-accent-dark);
    }

    .jp-capsule-meta .headcount {
        color: var(--zn-ink-3);
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

    /* ===== Confirm dialog (replaces window.confirm on this page) ===== */
    .jp-confirm-back {
        position: fixed;
        inset: 0;
        background: rgba(32, 26, 22,.45);
        z-index: 1080;
    }

    .jp-confirm {
        position: fixed;
        z-index: 1081;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: min(420px, calc(100vw - 32px));
        background: var(--zn-surface);
        border-radius: var(--zn-radius-lg);
        box-shadow: 0 18px 48px rgba(32, 26, 22,.28);
        padding: 20px;
    }

    .jp-confirm p {
        margin: 0 0 16px;
        font-size: var(--zn-fs);
        color: var(--zn-ink);
    }

    .jp-confirm-row { display: flex; justify-content: flex-end; gap: 8px; }

    .jp-confirm-row button {
        border-radius: var(--zn-radius-lg);
        padding: 7px 16px;
        font-size: var(--zn-fs-ui);
        font-weight: 600;
        cursor: pointer;
    }

    .jp-confirm-cancel {
        border: 1px solid var(--zn-line);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
    }

    .jp-confirm-ok {
        border: 1px solid var(--zn-warn);
        background: var(--zn-warn);
        color: var(--zn-surface);
    }

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

    .jp-gen-btn i { margin-right: 4px; font-size: var(--zn-fs-xs); }

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
 .jp-panel-btn-close {
     background: linear-gradient(135deg, var(--zn-warn), var(--zn-warn));
     color: var(--zn-on-accent);
     box-shadow: 0 2px 8px rgba(198, 40, 40,.25);
 }

 .jp-panel-btn-save {
     background: var(--zn-surface);
     color: var(--zn-accent-dark);
     border: 1px solid var(--zn-line-2);
     box-shadow: none;
 }

 .jp-panel-btn-save:disabled {
     opacity: .65;
     transform: none;
 }

 .jp-panel-btn i {
     margin-right: 6px;
 }

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

    @forelse ($eligibleRequests as $request)
        @if ($loop->first)
            <div class="jp-eligible-card" id="jp-eligible">
                <div class="jp-eligible-grid">
        @endif

        <div class="jp-mr-group">
            <div class="jp-mr-label">Manpower Request<span class="mr-no">{{ $request->mr_no ?? '—' }}</span></div>
            <div class="jp-capsule-row">
                @forelse ($request->positions as $position)
                    @php $typeClass = strtolower($position->type) === 'additional' ? 'jp-type-additional' : 'jp-type-replacement'; @endphp
                    <div class="jp-capsule">
                        <span class="jp-capsule-text">
                            <span class="jp-capsule-title">{{ $position->positionTitle() }}</span>
                            <span class="jp-capsule-meta">
                                <span class="sep">·</span>
                                <span class="jp-type-inline {{ $typeClass }}">{{ ucfirst($position->type) }}</span>
                                <span class="sep">·</span>
                                <span class="headcount">{{ $position->headcount }} ({{ $position->filled }}
                                    filled)</span>
                            </span>
                        </span>
                        <button type="button" class="jp-btn-create-mini btn-open-create-posting"
                            data-position-id="{{ $position->id }}">
                            Create
                        </button>
                    </div>
                @empty
                    <span class="text-muted small">No eligible positions on this request.</span>
                @endforelse
            </div>
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
                    <div class="mb-2">
                        <label class="form-label">Public Ad <span class="jp-field-tag jp-field-tag-live">Careers
                                page</span></label>
                        <textarea id="cp-public" class="form-control" rows="14" style="font-size: var(--zn-fs);"></textarea>
                        <div class="form-text">Auto-composed from the Job Specification. This is what applicants read —
                            edit the wording freely before saving.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="cp-short">Short Description <span class="jp-field-tag jp-field-tag-live">Job
                                boards &amp; sharing</span></label>
                        <textarea id="cp-short" class="form-control" rows="3" maxlength="{{ \App\Services\Recruitment\Ad\JobShortDescription::MAX }}"></textarea>
                        <div class="form-text">Two or three plain sentences for anywhere the full ad does not fit — a job
                            board's summary, a social post, the careers list. No emoji needed.</div>
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
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-briefcase"></i> Position</div>
                {{-- Filled when HR composes an ad, so the choice of template
                     is never a black box. --}}
                <div class="jp-panel-value" id="jpp-position">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-file-alt"></i> Request</div>
                <div class="jp-panel-value" id="jpp-mrno">—</div>
                {{-- Straight through to step 3 for this posting. --}}
                <a class="jp-panel-jump" id="jpp-applicants-link"
                    href="{{ url('/recruitment/applicant-intake') }}">See applicants for this posting &rarr;</a>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-user"></i> Created By</div>
                <div class="jp-panel-value" id="jpp-createdby">—</div>
            </div>
            <div class="jp-panel-field jp-panel-field-inline">
                <div>
                    <div class="jp-panel-label"><i class="fa fa-calendar-check"></i> Posted At</div>
                    <div class="jp-panel-value" id="jpp-postedat">—</div>
                </div>
                <div>
                    <div class="jp-panel-label"><i class="fa fa-calendar-times"></i> Closed At</div>
                    <div class="jp-panel-value" id="jpp-closedat">—</div>
                </div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-lock"></i> Internal Jobspec <span
                        class="jp-field-tag">Not public</span></div>
                <textarea class="jp-panel-desc-textarea" id="jpp-description-input" rows="10"></textarea>
                <div class="jp-desc-hint">Auto-drafted from the jobspec. HR reference only — never shown on the
                    careers page.</div>
            </div>
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
                $('#create-posting-loading').addClass('d-none');
                $('#create-posting-form').removeClass('d-none');
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
                short_description: $('#cp-short').val()
            });
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

        $('#cp-publish-btn').on('click', async function() {
            try {
                const result = await createPosting();
                if (!result) return;

                console.log('createPosting() result:', result);

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

                location.reload();
            } catch (err) {
                console.error(err);
                showAlert('danger', 'Failed to publish posting: ' + err.message );
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

            if (editable) {
                html +=
                    '<button type="button" class="jp-panel-btn jp-panel-btn-save" id="jp-save-desc-btn">' +
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

        $(document).on('click', '.js-channel-copy', async function () {
            const link = $(this).data('link');
            if (!link) { return; }
            const $btn = $(this);
            try {
                await navigator.clipboard.writeText(link);
                const original = $btn.html();
                $btn.html('<i class="fa fa-check"></i> Copied');
                setTimeout(() => $btn.html(original), 1500);
            } catch (err) {
                // Clipboard is blocked outside HTTPS in some browsers; show the
                // link so it can still be copied by hand rather than failing.
                window.prompt('Copy this link:', link);
            }
        });

        /**
         * Names the template that composed the ad, and offers the others.
         *
         * Generating used to produce one ad with no alternative; the choice
         * was invisible and unchangeable.
         */
        function jpShowTemplate(template, templates) {
            const $box = $('#jpp-template');
            if (!template) { $box.hide(); return; }

            $box.find('.jp-template-name').text(template.name);
            $box.find('.jp-template-reason').text(template.reason || '');

            const $picker = $('#jpp-template-picker').empty();
            (templates || []).forEach(function (t) {
                $('<button type="button" class="jp-template-btn"></button>')
                    .text(t.name)
                    .attr('title', t.description)
                    .attr('aria-pressed', t.key === template.key ? 'true' : 'false')
                    .toggleClass('is-on', t.key === template.key)
                    .on('click', function () { jpGenerateAd(t.key); })
                    .appendTo($picker);
            });

            $box.show();
        }

        /**
         * Composes the public ad, optionally with a chosen template.
         * Only the draft in the box changes; nothing is stored until save.
         */
        async function jpGenerateAd(templateKey) {
            const $btn = $('#jpp-generate-btn');
            const original = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Composing…');
            $('.jp-template-btn').prop('disabled', true);

            try {
                const query = templateKey ? ('?template=' + encodeURIComponent(templateKey)) : '';
                const data = await GET('recruitment/job-postings/' + currentPostingId + '/suggest-ad' + query);
                $('#jpp-public-input').val(data.public_description || '');
                $('#jpp-public-warning').addClass('d-none');
                jpShowTemplate(data.template, data.templates);
                showAlert('info', data.template
                    ? (data.template.chosen_by_hr
                        ? 'Ad rewritten with the ' + data.template.name + ' template. Review it, then click Save Description.'
                        : 'Ad composed using the ' + data.template.name + ' template. Try another below, or click Save Description.')
                    : 'Ad composed from the Job Specification. Review it, then click Save Description.');
            } catch (err) {
                showAlert('danger', 'Ad not composed: ' + err.message);
            } finally {
                $btn.prop('disabled', false).html(original);
                $('.jp-template-btn').prop('disabled', false);
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

        /**
         * Replaces window.confirm for the destructive actions on this page.
         * Those are the most consequential things HR can do here — replacing a
         * hand-written ad, deleting a photo from every posting, closing a live
         * posting — and they were getting the browser's bare dialog.
         */
        function jpConfirm(message, okLabel) {
          return new Promise(function (resolve) {
            const $back = $('<div class="jp-confirm-back"></div>');
            const $box = $(
                '<div class="jp-confirm" role="alertdialog" aria-modal="true">' +
                '<p></p><div class="jp-confirm-row">' +
                '<button type="button" class="jp-confirm-cancel">Cancel</button>' +
                '<button type="button" class="jp-confirm-ok"></button>' +
                '</div></div>'
            );
            $box.find('p').text(message);
            $box.find('.jp-confirm-ok').text(okLabel);

            function close(answer) {
                $back.remove();
                $box.remove();
                $(document).off('keydown.jpConfirm');
                resolve(answer);
            }

            $box.find('.jp-confirm-cancel').on('click', function () { close(false); });
            $box.find('.jp-confirm-ok').on('click', function () { close(true); });
            $back.on('click', function () { close(false); });
            $(document).on('keydown.jpConfirm', function (e) { if (e.key === 'Escape') { close(false); } });

            $('body').append($back).append($box);
            $box.find('.jp-confirm-ok').trigger('focus');
          });
        }

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
        $(document).on('click', '#jpp-generate-btn', async function() {
            clearAlert();
            const current = $('#jpp-public-input').val().trim();

            if (current && !await jpConfirm(
                    'Replace the current public ad with a freshly composed one? Your edits will be lost.',
                    'Replace ad')) {
                return;
            }

            // null = use the recommended template; the picker passes a key.
            await jpGenerateAd(null);
        });

        // ===== Short description: counter, and a fresh draft on request =====
        function updateShortCount() {
            $('#jpp-short-count').text($('#jpp-short-input').val().length);
        }
        $(document).on('input', '#jpp-short-input', updateShortCount);

        // Fills the textarea only — nothing is stored until Save.
        $(document).on('click', '#jpp-short-generate-btn', async function() {
            clearAlert();
            const $btn = $(this);

            if ($('#jpp-short-input').val().trim() &&
                !await jpConfirm(
                    'Replace the current short description with a fresh draft? Your edits will be lost.',
                    'Replace description')) {
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Drafting…');

            try {
                const data = await GET('recruitment/job-postings/' + currentPostingId + '/suggest-ad');
                $('#jpp-short-input').val(data.short_description || '');
                updateShortCount();
                showAlert('info', 'Short description drafted from the Job Specification. Review it, then click Save Description.');
            } catch (err) {
                console.error(err);
                showAlert('danger', 'Could not draft a short description for this posting.');
            } finally {
                $btn.prop('disabled', false).html('<i class="fa fa-wand-magic-sparkles"></i> Draft');
            }
        });

        // ===== Photos (the Job Specification's; saved immediately) =====
        let photosEditable = true;

        function renderPhotos(data) {
            const photos = (data && data.photos) || [];
            let html = photos.map(p =>
                '<div class="jp-photo"><img src="' + p.url + '" alt="" loading="lazy">' +
                (photosEditable ? '<button type="button" class="jp-photo-remove" data-name="' + p.name +
                    '" title="Remove photo" aria-label="Remove photo"><i class="fa fa-times"></i></button>' : '') +
                '</div>').join('');
            if (photosEditable && photos.length < (data ? data.max : 0)) {
                html += '<button type="button" class="jp-photo-add" id="jpp-photo-add"><i class="fa fa-plus"></i><span>Add photo</span></button>';
            }
            $('#jpp-photos').html(html || '<span class="text-muted small">No photos.</span>');
        }

        async function loadPhotos() {
            $('#jpp-photos').html('<span class="text-muted small">Loading photos…</span>');
            try {
                renderPhotos(await GET('recruitment/job-postings/' + currentPostingId + '/photos'));
            } catch (err) {
                $('#jpp-photos').html('<span class="text-muted small">Photos could not be loaded.</span>');
            }
        }

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

        $(document).on('click', '#jpp-photo-add', function() {
            $('#jpp-photo-input').val('').trigger('click');
        });

        $('#jpp-photo-input').on('change', async function() {
            if (!this.files.length) return;
            clearAlert();
            const form = new FormData();
            form.append('photo', this.files[0]);
            $('#jpp-photo-add').prop('disabled', true).find('span').text('Uploading…');
            try {
                renderPhotos(await photoRequest('POST', 'recruitment/job-postings/' + currentPostingId + '/photos', form));
            } catch (err) {
                showAlert('danger', 'Photo not added: ' + err.message);
                loadPhotos();
            }
        });

        $(document).on('click', '.jp-photo-remove', async function() {
            if (!await jpConfirm('Remove this photo from every posting of this position?', 'Remove photo')) { return; }
            clearAlert();
            try {
                renderPhotos(await photoRequest('DELETE', 'recruitment/job-postings/' + currentPostingId + '/photos/' +
                    encodeURIComponent($(this).data('name'))));
            } catch (err) {
                showAlert('danger', 'Photo not removed: ' + err.message);
            }
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
                $('#jpp-status').text(data.status).attr('class', 'jp-status-chip ' + (STATUS_CLASS[data
                    .status] || 'jp-status-draft'));
                $('#jpp-position').text(data.position_title);
                $('#jpp-mrno').text(data.mr_no);
                $('#jpp-description-input').val(data.posting_description || '');
                $('#jpp-public-input').val(data.public_description || '');
                $('#jpp-public-warning').toggleClass('d-none', !!(data.public_description || '').trim());
                renderSyncState(data.ad_is_custom);
                $('#jpp-short-input').val(data.short_description || '');
                updateShortCount();
                $('#jpp-createdby').text(data.created_by ?? '—');
                $('#jpp-postedat').text(data.posted_at ?? 'Not yet published');
                $('#jpp-closedat').text(data.closed_at ?? 'Not closed');

                renderFooter(data.status);
                photosEditable = data.status !== 'Closed';
                loadPhotos();
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

        $(document).on('click', '.jp-panel-status-btn', async function() {
            const $btn = $(this);
            const status = $btn.data('status');
            const posting_description = $('#jpp-description-input').val();

            if (status === 'Closed' &&
                !await jpConfirm(
                    'Close this job posting? It will stop accepting applicants. This does not close the panel.',
                    'Close posting')) {
                return;
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
