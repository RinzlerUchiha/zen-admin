@extends('layouts.layout')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

    <style>
        /* ============================================================
                           MPR module — scoped dark theme
                           Everything lives under #mpr-app so the rest of the app
                           (layout, sidebar, other pages) is untouched.
                           ============================================================ */
        #mpr-app {
            --mpr-page-bg: #f7f7f5;
            --mpr-bg-raised: #ffffff;
            --mpr-bg-input: #f4f4f2;
            --mpr-border: #ececea;
            --mpr-border-strong: #dcdbd6;
            --mpr-text: #18181b;
            --mpr-text-muted: #6b6b66;
            --mpr-accent: #0c7bd1;
            --mpr-accent-soft: #e6f1fb;
            --mpr-green: #1a7f37;
            --mpr-green-soft: #e6f6ea;
            --mpr-amber: #b5800b;
            --mpr-purple: #534ab7;
            --mpr-red: #dc2626;
            --mpr-red-soft: #fdecec;
            --mpr-radius: 12px;
            --mpr-radius-sm: 6px;
            color: var(--mpr-text);
            font-size: 14px;
        }

        #mpr-app,
        #mpr-app input,
        #mpr-app select,
        #mpr-app textarea,
        #mpr-app button {
            font-size: 14px;
        }

        #mpr-app .mpr-shell {
            background: var(--mpr-page-bg);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius);
            overflow: hidden;
        }

        /* ---------- Header ---------- */
        #mpr-app .mpr-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 20px;
            background: var(--mpr-bg-raised);
            border-bottom: 1px solid var(--mpr-border);
            flex-wrap: wrap;
        }

        #mpr-app .mpr-header-titlerow {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        #mpr-app .mpr-header-titlerow i {
            font-size: 16px;
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-header-title h1 {
            font-size: 16px;
            font-weight: 500;
            margin: 0;
            color: var(--mpr-text);
        }

        #mpr-app .mpr-header-title p {
            font-size: 12px;
            margin: 0;
            padding-left: 24px;
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-header-actions {
            display: flex;
            gap: 8px;
        }

        #mpr-app .btn-mpr-outline,
        #mpr-app .btn-mpr-solid {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #mpr-app .btn-mpr-outline {
            background: transparent;
            border: 1px solid var(--mpr-border-strong);
            color: var(--mpr-text);
            border-radius: var(--mpr-radius-sm);
            padding: 7px 14px;
            font-weight: 500;
        }

        #mpr-app .btn-mpr-outline:hover {
            background: var(--mpr-bg-input);
        }

        #mpr-app .btn-mpr-solid {
            background: var(--mpr-text);
            border: 1px solid var(--mpr-text);
            color: #ffffff;
            border-radius: var(--mpr-radius-sm);
            padding: 7px 14px;
            font-weight: 600;
        }

        #mpr-app .btn-mpr-solid:hover {
            background: #000000;
            border-color: #000000;
        }

        /* ---------- Tab strip ---------- */
        #mpr-app .mpr-tabstrip {
            display: flex;
            gap: 2px;
            padding: 0 12px;
            background: var(--mpr-bg-raised);
            border-bottom: 1px solid var(--mpr-border);
            overflow-x: auto;
            scrollbar-width: thin;
        }

        #mpr-app .mpr-tab {
            display: flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 10px 14px 9px;
            color: var(--mpr-text-muted);
            font-weight: 400;
            white-space: nowrap;
            cursor: pointer;
        }

        #mpr-app .mpr-tab:hover {
            color: var(--mpr-text);
        }

        #mpr-app .mpr-tab.active {
            color: var(--mpr-text);
            font-weight: 500;
            border-bottom-color: var(--mpr-text);
        }

        #mpr-app .mpr-tab-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        #mpr-app .mpr-tab[data-stat="draft"] .mpr-tab-dot {
            background: #888780;
        }

        #mpr-app .mpr-tab[data-stat="pending"] .mpr-tab-dot {
            background: var(--mpr-amber);
        }

        #mpr-app .mpr-tab[data-stat="pending_update"] .mpr-tab-dot {
            background: var(--mpr-amber);
        }

        #mpr-app .mpr-tab[data-stat="approved"] .mpr-tab-dot {
            background: var(--mpr-green);
        }

        #mpr-app .mpr-tab[data-stat="update"] .mpr-tab-dot {
            background: var(--mpr-purple);
        }

        #mpr-app .mpr-tab[data-stat="cancelled"] .mpr-tab-dot {
            background: #888780;
        }

        #mpr-app .mpr-tab[data-stat="declined"] .mpr-tab-dot {
            background: var(--mpr-red);
        }

        #mpr-app .mpr-tab[data-stat="jobspec"] .mpr-tab-dot {
            background: var(--mpr-accent);
        }

        #mpr-app .mpr-tab-count {
            background: var(--mpr-bg-input);
            border-radius: 999px;
            padding: 1px 7px;
            font-size: 11px;
            font-weight: 500;
            color: var(--mpr-text-muted);
        }

        /* ---------- Content area / card wrapper ---------- */
        #mpr-app .mpr-content-area {
            padding: 16px 20px;
        }

        #mpr-app .mpr-card {
            background: var(--mpr-bg-raised);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius);
            overflow: hidden;
        }

        /* ---------- Toolbar (search / per-page) ---------- */
        #mpr-app .mpr-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--mpr-border);
            flex-wrap: wrap;
        }

        #mpr-app .mpr-toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #mpr-app .mpr-toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #mpr-app .mpr-pp-label {
            font-size: 12px;
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-pp-select {
            font-size: 12px;
            padding: 4px 6px;
            width: auto;
            background: var(--mpr-bg-raised);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            color: var(--mpr-text);
        }

        #mpr-app .mpr-search {
            position: relative;
            flex: 1;
            min-width: 200px;
            max-width: 320px;
        }

        #mpr-app .mpr-search input {
            width: 100%;
            background: var(--mpr-bg-raised);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            color: var(--mpr-text);
            padding: 7px 12px 7px 30px;
        }

        #mpr-app .mpr-search input::placeholder {
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-search i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--mpr-text-muted);
            font-size: 12px;
        }

        #mpr-app .mpr-results-count {
            color: var(--mpr-text-muted);
            font-size: 12px;
            white-space: nowrap;
        }

        /* ---------- Table area ---------- */
        #mpr-app .mpr-table-wrap {
            padding: 0;
        }

        #mpr-app table.mpr-table {
            width: 100%;
            border-collapse: collapse;
        }

        #mpr-app table.mpr-table thead th {
            text-align: left;
            font-size: 11px;
            color: var(--mpr-text-muted);
            font-weight: 500;
            padding: 8px 12px;
            border-bottom: 1px solid var(--mpr-border);
            background: var(--mpr-bg-input);
        }

        #mpr-app table.mpr-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--mpr-border);
            vertical-align: middle;
            color: var(--mpr-text);
        }

        #mpr-app table.mpr-table tbody tr {
            cursor: pointer;
        }

        #mpr-app table.mpr-table tbody tr:last-child td {
            border-bottom: none;
        }

        #mpr-app table.mpr-table tbody tr:hover {
            background: var(--mpr-bg-input);
        }

        #mpr-app .mpr-ref {
            color: var(--mpr-accent);
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 500;
            font-variant-numeric: tabular-nums;
            cursor: pointer;
        }

        #mpr-app .mpr-requestor {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #mpr-app .mpr-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 500;
            flex-shrink: 0;
        }

        /* Cycle these per row, e.g. avatar class = 'av' + (employee_id % 5) */
        #mpr-app .mpr-avatar.av0 {
            background: #E6F1FB;
            color: #0C447C;
        }

        #mpr-app .mpr-avatar.av1 {
            background: #FAEEDA;
            color: #633806;
        }

        #mpr-app .mpr-avatar.av2 {
            background: #EAF3DE;
            color: #27500A;
        }

        #mpr-app .mpr-avatar.av3 {
            background: #EEEDFE;
            color: #3C3489;
        }

        #mpr-app .mpr-avatar.av4 {
            background: #FAECE7;
            color: #993C1D;
        }

        #mpr-app .mpr-requestor-name {
            font-weight: 500;
            font-size: 13px;
            line-height: 1.2;
        }

        #mpr-app .mpr-requestor-dept {
            color: var(--mpr-text-muted);
            font-size: 11px;
        }

        #mpr-app .mpr-pill {
            display: inline-block;
            background: var(--mpr-accent-soft);
            color: var(--mpr-accent);
            border-radius: var(--mpr-radius-sm);
            padding: 2px 7px;
            font-size: 11px;
            font-weight: 500;
            margin: 1px 2px 1px 0;
        }

        #mpr-app .mpr-pill-more {
            background: var(--mpr-bg-input);
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-row-actions {
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity .12s ease;
            justify-content: flex-end;
        }

        #mpr-app table.mpr-table tbody tr:hover .mpr-row-actions {
            opacity: 1;
        }

        #mpr-app .mpr-row-actions button {
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--mpr-bg-raised);
            border: 1px solid var(--mpr-border-strong);
            color: var(--mpr-text-muted);
            border-radius: var(--mpr-radius-sm);
            font-size: 13px;
        }

        #mpr-app .mpr-row-actions button:hover {
            background: var(--mpr-bg-input);
            color: var(--mpr-text);
        }

        #mpr-app .mpr-row-actions button.decline:hover {
            background: var(--mpr-red-soft);
            color: var(--mpr-red);
            border-color: var(--mpr-red);
        }

        #mpr-app .mpr-row-actions button.approve:hover {
            background: var(--mpr-green-soft);
            color: var(--mpr-green);
            border-color: var(--mpr-green);
        }

        /* ---------- Empty state ---------- */
        #mpr-app .mpr-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            color: var(--mpr-text-muted);
            text-align: center;
        }

        #mpr-app .mpr-empty i {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--mpr-bg-input);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 18px;
            color: var(--mpr-text-muted);
        }

        /* ---------- Pagination footer ---------- */
        #mpr-app .mpr-pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 14px;
            border-top: 1px solid var(--mpr-border);
            color: var(--mpr-text-muted);
            font-size: 12px;
        }

        #mpr-app .mpr-pager-buttons {
            display: flex;
            gap: 3px;
        }

        #mpr-app .mpr-pager-btn {
            width: 28px;
            height: 28px;
            padding: 0;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--mpr-bg-raised);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            color: var(--mpr-text);
        }

        #mpr-app .mpr-pager-btn:hover {
            background: var(--mpr-bg-input);
        }

        #mpr-app .mpr-pager-btn.active {
            background: var(--mpr-text);
            color: #fff;
            border-color: var(--mpr-text);
        }

        #mpr-app .mpr-pager-btn:disabled {
            opacity: .4;
            cursor: default;
        }

        /* ---------- Modal chrome ---------- */
        #mpr-app .modal-content {
            background: var(--mpr-bg-raised);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius);
        }

        #mpr-app .modal-header {
            align-items: flex-start;
            border-color: var(--mpr-border);
        }

        #mpr-app .modal-footer {
            border-color: var(--mpr-border);
        }

        #mpr-app .mpr-modal-subtitle {
            font-size: 12px;
            color: var(--mpr-text-muted);
            margin: 3px 0 0;
        }

        /* ---------- Modal two-column layout (form + interview side panel) ---------- */
        #mpr-app .mpr-modal-cols {
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }

        #mpr-app .mpr-modal-col-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        #mpr-app .mpr-modal-col-side {
            flex: 0 0 400px;
            position: sticky;
            top: 0;
        }

        @media (max-width: 991px) {
            #mpr-app .mpr-modal-cols {
                flex-direction: column;
            }

            #mpr-app .mpr-modal-col-side {
                flex: 1 1 auto;
                width: 100%;
                position: static;
            }
        }

        #mpr-app .form-control,
        #mpr-app .form-select,
        #mpr-app textarea.form-control {
            border-color: var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            font-size: 13px;
        }

        #mpr-app .form-control:focus,
        #mpr-app .form-select:focus {
            border-color: var(--mpr-accent);
            box-shadow: 0 0 0 2px var(--mpr-accent-soft);
        }

        #mpr-app .form-check-input:checked {
            background-color: var(--mpr-accent);
            border-color: var(--mpr-accent);
        }

        #mpr-app .input-group-text {
            background-color: var(--mpr-bg-input);
            border-color: var(--mpr-border-strong);
            color: var(--mpr-text-muted);
        }

        /* ---------- Modal form sections ---------- */
        #mpr-app .mpr-section-divider {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0 10px;
            font-weight: 500;
            font-size: 12px;
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-section-divider .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        #mpr-app .mpr-section-divider.replacement .dot {
            background: var(--mpr-amber);
        }

        #mpr-app .mpr-section-divider.additional .dot {
            background: var(--mpr-accent);
        }

        #mpr-app .mpr-section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--mpr-border);
            order: 1;
        }

        #mpr-app .mpr-section-hint {
            order: 2;
            font-size: 11px;
            font-weight: 400;
            color: var(--mpr-text-muted);
            white-space: nowrap;
        }

        #mpr-app .mpr-card-table {
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            overflow: hidden;
        }

        #mpr-app .mpr-card-table table {
            margin-bottom: 0;
        }

        #mpr-app .mpr-card-table thead th {
            font-size: 11px;
            font-weight: 500;
            color: var(--mpr-text-muted);
            padding: 6px 8px;
            border-bottom: 1px solid var(--mpr-border);
            background: var(--mpr-bg-input);
        }

        #mpr-app .mpr-card-table tbody td {
            padding: 5px 6px;
            border-bottom: 1px solid var(--mpr-border);
            vertical-align: top;
        }

        #mpr-app .mpr-card-table tbody tr:last-child td {
            border-bottom: none;
        }

        #mpr-app .mpr-card-table .form-control,
        #mpr-app .mpr-card-table .form-select {
            font-size: 12px;
            border-radius: var(--mpr-radius-sm);
        }

        #mpr-app .btn-add-row-full {
            width: 100%;
            border: none;
            border-top: 1px solid var(--mpr-border);
            background: transparent;
            color: var(--mpr-text-muted);
            border-radius: 0;
            padding: 8px 10px;
            font-weight: 400;
            font-size: 12px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #mpr-app .btn-add-row-full:hover {
            background: var(--mpr-bg-input);
            color: var(--mpr-text);
        }

        #mpr-app .modal-footer.mpr-footer-tone {
            background: var(--mpr-bg-input);
        }

        #mpr-app .modal-footer .btn-primary {
            background: var(--mpr-text);
            border-color: var(--mpr-text);
        }

        #mpr-app .modal-footer .btn-primary:hover {
            background: #000;
            border-color: #000;
        }

        #mpr-app .modal-footer .btn-secondary {
            background: transparent;
            border: 1px solid var(--mpr-border-strong);
            color: var(--mpr-text);
        }

        #mpr-app .modal-footer .btn-secondary:hover {
            background: var(--mpr-bg-raised);
        }

        #mprTabContent,
        #modal-mpr,
        #modal-view-mpr,
        .mpr-fill-td input {
            font-size: 12px;
        }

        .bootstrap-select {
            max-width: 100% !important;
        }

        #form-mpr-jobspec [type="checkbox"],
        #form-mpr-jobspec [type="radio"] {
            border: 1px solid var(--bs-dark);
        }

        /* ---------- Applicant picker + interview history (new) ---------- */
        #mpr-app .mpr-applicant-select {
            font-size: 12px;
            border-radius: var(--mpr-radius-sm);
        }

        /* ---------- Applicant slot chips ---------- */
        #mpr-app .mpr-applicant-slots {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        #mpr-app .mpr-applicant-chip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            background: var(--mpr-accent-soft);
            color: var(--mpr-accent);
            border: 1px solid var(--mpr-accent);
            border-radius: var(--mpr-radius-sm);
            padding: 4px 6px 4px 10px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
        }

        #mpr-app .mpr-applicant-chip:hover {
            background: #d8ebfa;
        }

        #mpr-app .mpr-applicant-chip.active {
            border-color: var(--mpr-text);
            background: var(--mpr-text);
            color: #fff;
        }

        #mpr-app .mpr-applicant-chip-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #mpr-app .mpr-applicant-chip-remove {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            border: none;
            background: rgba(255, 255, 255, .5);
            color: inherit;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            line-height: 1;
        }

        #mpr-app .mpr-applicant-chip.active .mpr-applicant-chip-remove {
            background: rgba(255, 255, 255, .2);
        }

        #mpr-app .mpr-applicant-chip-remove:hover {
            background: rgba(255, 255, 255, .85);
        }

        #mpr-app .mpr-iv-toggle-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        #mpr-app .mpr-iv-toggle {
            border-radius: 999px;
            font-size: 11px;
            padding: 4px 12px;
            font-weight: 500;
            border: 1px solid var(--mpr-border-strong);
            background: var(--mpr-bg-raised);
            color: var(--mpr-text-muted);
        }

        #mpr-app .mpr-iv-toggle.active {
            background: var(--mpr-accent);
            border-color: var(--mpr-accent);
            color: #fff;
        }

        #mpr-app .mpr-iv-toggle:hover:not(.active) {
            background: var(--mpr-bg-input);
            color: var(--mpr-text);
        }

        #mpr-app #mpr-applicant-interview-panel {
            margin-top: 4px;
            margin-bottom: 16px;
        }

        #mpr-app .mpr-iv-detail-card {
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            background: var(--mpr-bg-raised);
            padding: 12px;
        }

        #mpr-app .mpr-iv-detail-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: var(--mpr-text-muted);
            font-weight: 600;
            margin-bottom: 2px;
        }

        #mpr-app .mpr-iv-detail-value {
            font-size: 13px;
            color: var(--mpr-text);
            margin-bottom: 10px;
        }

        #mpr-app .mpr-iv-richbox {
            border: 1px solid var(--mpr-border);
            border-radius: var(--mpr-radius-sm);
            background: var(--mpr-bg-input);
            padding: 8px 10px;
            font-size: 12px;
            min-height: 48px;
            color: var(--mpr-text);
        }

        #mpr-app .mpr-iv-empty {
            color: var(--mpr-text-muted);
            font-size: 12px;
            padding: 16px;
            text-align: center;
        }

        #mpr-app .mpr-iv-verdict-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        #mpr-app .mpr-iv-verdict-pill.hired {
            background: var(--mpr-green-soft);
            color: var(--mpr-green);
        }

        #mpr-app .mpr-iv-verdict-pill.not-hired {
            background: var(--mpr-red-soft);
            color: var(--mpr-red);
        }

        #mpr-app .mpr-iv-verdict-pill.pending {
            background: var(--mpr-bg-input);
            color: var(--mpr-text-muted);
        }

        /* ---------- Interview panel applicant header ---------- */
        #mpr-app .mpr-iv-applicant-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: var(--mpr-bg-input);
            border: 1px solid var(--mpr-border-strong);
            border-radius: var(--mpr-radius-sm);
            min-height: 38px;
        }

        #mpr-app .mpr-iv-applicant-name-display {
            font-size: 13px;
            font-weight: 600;
            color: var(--mpr-text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #mpr-app .mpr-iv-applicant-name-display.empty {
            font-weight: 400;
            color: var(--mpr-text-muted);
            font-style: italic;
            font-size: 12px;
        }

        #mpr-app .mpr-iv-open-btn {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 500;
            color: var(--mpr-accent);
            background: var(--mpr-accent-soft);
            border: 1px solid var(--mpr-accent);
            border-radius: var(--mpr-radius-sm);
            padding: 3px 9px;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
        }

        #mpr-app .mpr-iv-open-btn:hover {
            background: #d8ebfa;
            color: var(--mpr-accent);
            text-decoration: none;
        }

        /* Applicant name pills in the list table */
        #mpr-app .mpr-applicant-pill {
            display: inline-block;
            background: var(--mpr-bg-input);
            color: var(--mpr-text-muted);
            border: 1px solid var(--mpr-border-strong);
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 11px;
            font-weight: 500;
            margin: 1px 2px 1px 0;
            white-space: nowrap;
        }
    </style>

    <div id="mpr-app">
        <div class="mpr-shell" id="mpr-content">

            <!-- Header -->
            <div class="mpr-header">
                <div class="mpr-header-title">
                    <div class="mpr-header-titlerow">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <h1>Manpower requests</h1>
                    </div>
                    <p>Submit and track staffing requests for your department</p>
                </div>
                <div class="mpr-header-actions">
                    <button class="btn-mpr-outline" data-bs-toggle="modal" data-bs-target="#modal-mpr-jobspec">
                        <i class="fa fa-file-text-o" aria-hidden="true"></i> Job specification
                    </button>
                    <button class="btn-mpr-solid" data-bs-toggle="modal" data-bs-target="#modal-mpr">
                        <i class="fa fa-plus" aria-hidden="true"></i> New request
                    </button>
                </div>
            </div>

            <!-- Tab strip -->
            <ul class="nav mpr-tabstrip" id="mprTab" role="tablist" style="list-style:none; margin:0;">
                <li class="mpr-tab" data-stat="draft" id="draft-tab" role="tab" aria-controls="draft-tab-pane"
                    aria-selected="false">
                    <span class="mpr-tab-dot"></span> Draft <span class="mpr-tab-count" data-count="draft">–</span>
                </li>
                <li class="mpr-tab active" data-stat="pending" id="pending-tab" role="tab"
                    aria-controls="pending-tab-pane" aria-selected="true">
                    <span class="mpr-tab-dot"></span> Pending <span class="mpr-tab-count" data-count="pending">–</span>
                </li>
                <li class="mpr-tab" data-stat="approved" id="approved-tab" role="tab" aria-controls="approved-tab-pane"
                    aria-selected="false">
                    <span class="mpr-tab-dot"></span> Approved <span class="mpr-tab-count" data-count="approved">–</span>
                </li>
                <li class="mpr-tab" data-stat="update" id="update-tab" role="tab" aria-controls="update-tab-pane"
                    aria-selected="false">
                    <span class="mpr-tab-dot"></span> Update <span class="mpr-tab-count" data-count="update">–</span>
                </li>
                <li class="mpr-tab" data-stat="cancelled" id="cancelled-tab" role="tab"
                    aria-controls="cancelled-tab-pane" aria-selected="false">
                    <span class="mpr-tab-dot"></span> Cancelled <span class="mpr-tab-count" data-count="cancelled">–</span>
                </li>
                <li class="mpr-tab" data-stat="declined" id="declined-tab" role="tab" aria-controls="declined-tab-pane"
                    aria-selected="false">
                    <span class="mpr-tab-dot"></span> Declined <span class="mpr-tab-count" data-count="declined">–</span>
                </li>
                <li class="mpr-tab" data-stat="jobspec" id="jobspec-tab" role="tab"
                    aria-controls="jobspec-tab-pane" aria-selected="false">
                    <span class="mpr-tab-dot"></span> Job specification <span class="mpr-tab-count"
                        data-count="jobspec">–</span>
                </li>
            </ul>

            <!-- Toolbar: search + count + page size (DataTables' own controls are hidden; these drive it) -->
            <div class="mpr-content-area">
                <div class="mpr-card">
                    <div class="mpr-toolbar">
                        <div class="mpr-toolbar-left">
                            <div class="mpr-search">
                                <i class="fa fa-search"></i>
                                <input type="text" id="mpr-global-search" placeholder="Search requests...">
                            </div>
                            <span class="mpr-results-count" id="mpr-results-count"></span>
                        </div>
                        <div class="mpr-toolbar-right">
                            <span class="mpr-pp-label">Show</span>
                            <select id="mpr-page-length" class="mpr-pp-select">
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                                <option value="-1">All</option>
                            </select>
                        </div>
                    </div>

                    <div class="mpr-table-wrap" id="mprTabContent">
                        <div class="tab-pane" id="draft-tab-pane" aria-labelledby="draft-tab" tabindex="0"
                            style="display:none;"></div>
                        <div class="tab-pane" id="pending-tab-pane" aria-labelledby="pending-tab" tabindex="0"></div>
                        <div class="tab-pane" id="approved-tab-pane" aria-labelledby="approved-tab" tabindex="0"
                            style="display:none;"></div>
                            <div class="tab-pane" id="update-tab-pane" aria-labelledby="update-tab" tabindex="0"
                            style="display:none;"></div>
                        <div class="tab-pane" id="cancelled-tab-pane" aria-labelledby="cancelled-tab" tabindex="0"
                            style="display:none;"></div>
                        <div class="tab-pane" id="declined-tab-pane" aria-labelledby="declined-tab" tabindex="0"
                            style="display:none;"></div>
                        <div class="tab-pane" id="jobspec-tab-pane" aria-labelledby="jobspec-tab" tabindex="0"
                            style="display:none;"></div>
                    </div>

                    <div class="mpr-pager" id="mpr-pager" style="display:none;">
                        <span id="mpr-pager-summary"></span>
                        <div class="mpr-pager-buttons" id="mpr-pager-buttons"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- #mpr-app itself closes after the modals below, not here -->

        <!--
                        NOTE ON THE TABLE PARTIAL
                        =========================
                        The actual <tr> markup for each MPR list comes from the server response
                        of GET /manpower/list/{stat} (a separate Blade partial not included in
                        the file you shared), so I can't restyle those rows from here directly.

                        To match the mockup, that partial's <table> should use:
                          <table class="mpr-table">
                            <thead><tr><th>Reference</th><th>Requested by</th><th>Positions</th><th>Date filed</th><th></th></tr></thead>
                            <tbody>
                              <tr>
                                <td class="mpr-ref" data-bs-toggle="modal" data-bs-target="#modal-view-mpr" ...>MPR-2025-007</td>
                                <td>
                                  <div class="mpr-requestor">
                                    <div class="mpr-avatar" style="background:#3b5bdb;">JD</div>
                                    <div>
                                      <div class="mpr-requestor-name">Juan Dela Cruz</div>
                                      <div class="mpr-requestor-dept">Finance</div>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <span class="mpr-pill">Accountant</span>
                                  <span class="mpr-pill">Bookkeeper</span>
                                </td>
                                <td>Jun 18, 2025</td>
                                <td>
                                  <div class="mpr-row-actions">
                                    <button class="approve" onclick="approve(7)"><i class="fa fa-check"></i></button>
                                    <button class="decline" onclick="decline(7)"><i class="fa fa-times"></i></button>
                                  </div>
                                </td>
                              </tr>
                            </tbody>
                          </table>

                        Avatar background colors can rotate through a small palette keyed off
                        employee id, e.g.: ['#3b5bdb','#c2410c','#15803d','#7e22ce','#0e7490'].

                        Empty state (when the list is empty), drop this in place of the table:
                          <div class="mpr-empty">
                            <i class="fa fa-inbox"></i>
                            <div>No requests in this tab yet.</div>
                          </div>
                    -->

        <div class="modal fade" id="modal-mpr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="modal-mpr-label" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h1 class="modal-title fs-5" id="modal-mpr-label">New manpower request</h1>
                            <p class="mpr-modal-subtitle">Specify positions needed for your department</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="form-mpr">
                        <div class="modal-body">
                            <div class="mpr-modal-cols">
                                <div class="mpr-modal-col-main">
                                    <div class="row" id="mpr-err"></div>
                                    <input type="hidden" id="mpr-id" value="">
                                    <input type="hidden" id="mpr-submit-mode" name="submit_mode" value="draft">

                                    <div class="alert alert-warning py-2 px-3 mb-3" id="mpr-edit-reason-box" style="display:none; font-size: 12px;">
                                        <strong>Reason for edit request:</strong>
                                        <div id="mpr-edit-reason-text"></div>
                                    </div>

                                    <!-- Master applicant option list — used as a clone source so each
                                                slot dropdown doesn't need its own Blade loop render. -->
                                    <select id="mpr-applicant-master-options" class="d-none">
                                        <option value="">-</option>
                                        @foreach ($applicants ?? [] as $a)
                                            <option value="{{ $a->app_id }}">{{ $a->app_name }}</option>
                                        @endforeach
                                    </select>

                                    <div class="mpr-section-divider replacement"><span class="dot"></span> Replacement
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="mpr-card-table">
                                                <table class="table table-sm table-borderless w-100 mb-0"
                                                    id="mpr-replacement-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Subject/Position</th>
                                                            <th>Applicant</th>
                                                            <th>Number Needed</th>
                                                            <th>Reason</th>
                                                            <th>Date Needed</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <select
                                                                    class="form-select form-select-sm mpr-replacement-position">
                                                                    <option value disabled selected>-</option>
                                                                    @foreach ($userJobSpec as $j)
                                                                        <option value="{{ $j->jspec_position }}">
                                                                            {{ $j->jd_title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <div class="mpr-applicant-slots"></div>
                                                            </td>
                                                            <td style="max-width: 100px;">
                                                                <input type="number"
                                                                    class="mpr-replacement-number form-control form-control-sm"
                                                                    min="1" value="1">
                                                            </td>
                                                            <td>
                                                                <select
                                                                    class="mpr-replacement-reason form-select form-select-sm">
                                                                    <option value="Resignation">Resignation</option>
                                                                    <option value="Terminated w/ cause">Terminated w/ cause
                                                                    </option>
                                                                    <option value="End of contract">End of contract
                                                                    </option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="date"
                                                                    class="mpr-replacement-dateneed form-control form-control-sm">
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger btn-del"><i
                                                                        class="fa fa-times"></i></button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn-add-row-full btn-add-row"><i
                                                        class="fa fa-plus" aria-hidden="true"></i> Add replacement
                                                    position</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mpr-section-divider additional"><span class="dot"></span> Additional
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <div class="mpr-card-table">
                                                <table class="table table-sm table-borderless w-100 mb-0"
                                                    id="mpr-additional-table">
                                                    <thead>
                                                        <tr>
                                                            <th width="26%">Subject/Position</th>
                                                            <th width="26%">Applicant</th>
                                                            <th width="10%">Number Needed</th>
                                                            <th>Reason</th>
                                                            <th width="13%">Date Needed</th>
                                                            <th width="30px"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <select
                                                                    class="form-select form-select-sm mpr-additional-position">
                                                                    <option value disabled selected>-</option>
                                                                    @foreach ($userJobSpec as $j)
                                                                        <option value="{{ $j->jspec_position }}">
                                                                            {{ $j->jd_title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <!-- Applicant slots — one chip/dropdown per Number Needed.
                                                                                 Rendered/managed entirely by rebuildApplicantSlots(). -->
                                                                <div class="mpr-applicant-slots"></div>
                                                            </td>
                                                            <td style="max-width: 100px;">
                                                                <input type="number"
                                                                    class="mpr-additional-number form-control form-control-sm"
                                                                    min="1" value="1">
                                                                <input type="hidden" class="mpr-additional-fill">
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                    class="mpr-additional-reason form-control form-control-sm">
                                                            </td>
                                                            <td>
                                                                <input type="date"
                                                                    class="mpr-additional-dateneed form-control form-control-sm">
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger btn-del"><i
                                                                        class="fa fa-times"></i></button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn-add-row-full btn-add-row"><i
                                                        class="fa fa-plus" aria-hidden="true"></i> Add additional
                                                    position</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-12 fs-6">NON-NEGOTIABLE</label>
                                        <div class="col-12">
                                            <textarea id="mpr-nonnegotiable" class="form-control form-control-sm"></textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-md-3">Requested by:</label>
                                        <div class="col-md-9">
                                            <label class="col-form-label"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mpr-modal-col-side">
                                    <!-- Always visible; shows an empty state until an applicant
                                                     is picked in either table on the left. -->
                                    <div id="mpr-applicant-interview-panel">
                                        <div class="mpr-section-divider additional">
                                            <span class="dot"></span> Applicant interview history
                                        </div>
                                        <div class="mpr-iv-applicant-header">
                                            <span class="mpr-iv-applicant-name-display empty"
                                                id="mpr-iv-applicant-name">No applicant selected</span>
                                            <a href="#" class="mpr-iv-open-btn d-none" id="mpr-iv-open-link"
                                                target="_blank" rel="noopener">
                                                <i class="fa fa-external-link"></i> Open
                                            </a>
                                        </div>

                                        <div class="mb-2 mpr-iv-toggle-group" id="mpr-interview-type-toggles">
                                            <!-- toggle buttons injected by JS, one per interview round the applicant has -->
                                        </div>

                                        <div class="mpr-iv-detail-card" id="mpr-iv-detail-card">
                                            <div class="mpr-iv-empty" id="mpr-iv-empty-state">
                                                Select an applicant to view interview history.
                                            </div>

                                            <div id="mpr-iv-detail-body" style="display:none;">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Interviewer</div>
                                                        <div class="mpr-iv-detail-value" id="mpr-iv-interviewer">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Date</div>
                                                        <div class="mpr-iv-detail-value" id="mpr-iv-date">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Department</div>
                                                        <div class="mpr-iv-detail-value" id="mpr-iv-department">-</div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Company</div>
                                                        <div class="mpr-iv-detail-value" id="mpr-iv-company">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Position</div>
                                                        <div class="mpr-iv-detail-value" id="mpr-iv-position">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Verdict</div>
                                                        <div class="mpr-iv-detail-value">
                                                            <span class="mpr-iv-verdict-pill pending"
                                                                id="mpr-iv-verdict">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mpr-iv-detail-label">Remarks</div>
                                                        <div class="mpr-iv-richbox" id="mpr-iv-remarks"></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mpr-iv-detail-label">Recommendation</div>
                                                        <div class="mpr-iv-richbox" id="mpr-iv-recommendation"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mpr-footer-tone">
                            <button type="button" class="btn btn-secondary" id="btn-close-mpr"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-outline-danger" id="btn-delete-mpr" style="display:none;"><i
                                    class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                            <button type="button" class="btn btn-outline-primary" id="btn-save-draft-mpr" style="display:none;"><i
                                    class="fa fa-save" aria-hidden="true"></i> Save draft</button>
                            <button type="button" class="btn btn-primary" id="btn-post-mpr"><i
                                    class="fa fa-paper-plane" aria-hidden="true"></i> Post request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-view-mpr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="modal-view-mpr-label" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modal-view-mpr-label">Manpower Request</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="form-view-mpr">
                        <div class="modal-body">
                            <div class="mpr-modal-cols">
                                <div class="mpr-modal-col-main">
                                    <div class="row" id="view-mpr-err"></div>
                                    <input type="hidden" id="view-mpr-id" value="">

                                    <div class="mpr-section-divider replacement"><span class="dot"></span> Replacement
                                        positions<span class="mpr-section-hint">Vacated roles</span></div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="mpr-card-table">
                                                <table class="table table-sm table-borderless w-100 mb-0"
                                                    id="view-mpr-replacement-table">
                                                    <thead>
                                                        <tr>
                                                            <th width="25%">Subject/Position</th>
                                                            <th width="70px">Number Needed</th>
                                                            <th>Reason</th>
                                                            <th width="100px">Date Needed</th>
                                                            <th>Applicant</th>
                                                            <th width="80px">Fill</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mpr-section-divider additional"><span class="dot"></span> Additional
                                        positions<span class="mpr-section-hint">New or expanded roles</span></div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="mpr-card-table">
                                                <table class="table table-sm table-borderless w-100 mb-0"
                                                    id="view-mpr-additional-table">
                                                    <thead>
                                                        <tr>
                                                            <th width="30%">Subject/Position</th>
                                                            <th width="70px">Number Needed</th>
                                                            <th>Reason</th>
                                                            <th width="100px">Date Needed</th>
                                                            <th>Applicant</th>
                                                            <th width="80px">Fill</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-12 fs-6">NON-NEGOTIABLE</label>
                                        <div class="col-12">
                                            <p id="view-mpr-nonnegotiable" class="col-form-label"></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-md-2">Requested by:</label>
                                        <label id="view-mpr-requestby" class="col-form-label col-md"></label>
                                    </div>
                                </div>

                                <div class="mpr-modal-col-side">
                                    <div id="view-mpr-applicant-interview-panel">
                                        <div class="mpr-section-divider additional">
                                            <span class="dot"></span> Applicant interview history
                                        </div>
                                        <div class="mpr-iv-applicant-header">
                                            <span class="mpr-iv-applicant-name-display empty"
                                                id="view-mpr-iv-applicant-name">No applicant selected</span>
                                            <a href="#" class="mpr-iv-open-btn d-none" id="view-mpr-iv-open-link"
                                                target="_blank" rel="noopener">
                                                <i class="fa fa-external-link"></i> Open
                                            </a>
                                        </div>

                                        <div class="mb-2 mpr-iv-toggle-group" id="view-mpr-interview-type-toggles"></div>

                                        <div class="mpr-iv-detail-card" id="view-mpr-iv-detail-card">
                                            <div class="mpr-iv-empty" id="view-mpr-iv-empty-state">
                                                Select an applicant to view interview history.
                                            </div>

                                            <div id="view-mpr-iv-detail-body" style="display:none;">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Interviewer</div>
                                                        <div class="mpr-iv-detail-value" id="view-mpr-iv-interviewer">-
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Date</div>
                                                        <div class="mpr-iv-detail-value" id="view-mpr-iv-date">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Department</div>
                                                        <div class="mpr-iv-detail-value" id="view-mpr-iv-department">-
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Company</div>
                                                        <div class="mpr-iv-detail-value" id="view-mpr-iv-company">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Position</div>
                                                        <div class="mpr-iv-detail-value" id="view-mpr-iv-position">-</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mpr-iv-detail-label">Verdict</div>
                                                        <div class="mpr-iv-detail-value">
                                                            <span class="mpr-iv-verdict-pill pending"
                                                                id="view-mpr-iv-verdict">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mpr-iv-detail-label">Remarks</div>
                                                        <div class="mpr-iv-richbox" id="view-mpr-iv-remarks"></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mpr-iv-detail-label">Recommendation</div>
                                                        <div class="mpr-iv-richbox" id="view-mpr-iv-recommendation"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mpr-footer-tone">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-outline-danger" id="btn-delete-view-mpr" style="display:none;"><i
                                    class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                            <button type="button" class="btn btn-outline-primary" id="btn-post-view-mpr" style="display:none;"><i
                                    class="fa fa-paper-plane" aria-hidden="true"></i> Post request</button>
                            <button type="submit" class="btn btn-primary" id="btn-save-view-mpr">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-decline-mpr" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-labelledby="modal-decline-mpr-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modal-decline-mpr-label">Manpower Request</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="form-decline-mpr">
                        <div class="modal-body">
                            <div class="row" id="decline-mpr-err"></div>
                            <input type="hidden" id="decline-mpr-id" value="">
                            <div class="row mb-3">
                                <label class="col-form-label col-12">Reason</label>
                                <div class="col-12">
                                    <textarea id="decline-mpr-reason" class="form-control form-control-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mpr-footer-tone">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-mpr-update" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="modal-mpr-update-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modal-mpr-update-label">Manpower Request Update/Cancel</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="form-mpr-update">
                        <div class="modal-body">
                            <div class="row" id="mpr-update-err"></div>
                            <input type="hidden" id="mpr-update-id" value="">
                            <input type="hidden" id="mpr-update-action" value="">
                            <div class="row mb-1">
                                <label class="col-form-label col-form-label-sm col-auto">Action:</label>
                                <label class="col-form-label col-form-label-sm col-auto"
                                    id="mpr-update-action-label"></label>
                            </div>
                            <div class="row mb-1">
                                <label class="col-form-label col-form-label-sm col-12">Reason:</label>
                                <div class="col-12">
                                    <textarea id="mpr-update-reason" class="form-control form-control-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mpr-footer-tone">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-mpr-jobspec" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="modal-mpr-jobspec-label" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modal-mpr-jobspec-label">Manpower Request Update</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="form-mpr-jobspec">
                        <div class="modal-body">
                            <div class="row" id="mpr-jobspec-err"></div>
                            <input type="hidden" id="mpr-jobspec-id" value="">
                            <fieldset>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-3">Department</label>
                                            <div class="col-9">
                                                <select class="form-control form-control-sm selectpicker"
                                                    data-width="auto" id="mpr-jobspec-dept" title="Select Department"
                                                    data-live-search="true" required>
                                                    @foreach ($department as $v)
                                                        @if (
                                                            $v->Dept_Stat == 'active' ||
                                                                strpos(check_assign($user_empno, 'PR', true), $v->Dept_Code) !== false ||
                                                                $userJobInfo->jd_code == $v->Dept_Code)
                                                            <option value="{{ $v->Dept_Code }}">{{ $v->Dept_Name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-3">Section</label>
                                            <div class="col-9">
                                                <select class="form-control form-control-sm selectpicker"
                                                    data-width="auto" id="mpr-jobspec-section" title="Select Section"
                                                    data-live-search="true" required>
                                                    @foreach ($section as $v)
                                                        {{-- @if ($v->sec_stat == 'active') --}}
                                                        <option value="{{ $v->sec_code }}">{{ $v->sec_name }}</option>
                                                        {{-- @endif --}}
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-3">Position</label>
                                            <div class="col-9">
                                                <select class="form-control form-control-sm selectpicker"
                                                    data-width="auto" id="mpr-jobspec-pos" title="Select Position"
                                                    data-live-search="true" required>
                                                    @foreach ($position as $v)
                                                        <option value="{{ $v->jd_code }}">{{ $v->jd_title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-3">Employment Status</label>
                                            <div class="col-9">
                                                <select class="form-select form-select-sm" id="mpr-jobspec-emplstat"
                                                    required>
                                                    @foreach ($emplstat as $v)
                                                        <option value="{{ $v->es_name }}">{{ $v->es_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-3">Gender</label>
                                            <div class="col-auto">
                                                <select class="form-select form-select-sm" id="mpr-jobspec-gender"
                                                    required>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Either">Either</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-3">Age</label>
                                            <div class="col-9">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Min</span>
                                                    <input type="text" class="form-control" aria-label="Min age"
                                                        min="0" max="0" id="mpr-jobspec-agemin" required>
                                                    <span class="input-group-text">-</span>
                                                    <input type="text" class="form-control" aria-label="Max age"
                                                        min="0" max="0" id="mpr-jobspec-agemax" required>
                                                    <span class="input-group-text">Max</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED: (Please check box of
                                    preferred option)</h6>

                                <div class="row mpr-jobspec-edu">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="High School Graduate"
                                                id="mpr-jobspec-edu1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-edu1">High School Graduate</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mpr-jobspec-edu">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="Vocational Course Graduate" id="mpr-jobspec-edu2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-edu2">Vocational Course Graduate</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-1 mpr-jobspec-edu">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="College Graduate (4 year course)" id="mpr-jobspec-edu3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-edu3">College Graduate (4 year course):</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm edu-detail"
                                            placeholder="Course/Degree">
                                    </div>
                                </div>

                                <div class="row mb-1 mpr-jobspec-edu">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="Five-year course Graduate" id="mpr-jobspec-edu4">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-edu4">Five-year course Graduate:</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm edu-detail">
                                    </div>
                                </div>

                                <div class="row mb-1 mpr-jobspec-edu">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="Masterate / Doctoral**Specify" id="mpr-jobspec-edu5">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-edu5">Masterate / Doctoral**Specify:</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm edu-detail">
                                    </div>
                                </div>

                                <div class="row mb-3 mpr-jobspec-edu">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Licensed"
                                                id="mpr-jobspec-edu6">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-edu6">Licensed:</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm edu-detail">
                                    </div>
                                </div>

                                <h6 class="mt-4">WORK EXPERIENCE(S) REQUIRED: (Please check box of preferred option)</h6>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-workexp" type="checkbox"
                                                value="Not Necessary (none)" id="mpr-jobspec-workexp1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-workexp1">Not Necessary (none)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-workexp" type="checkbox"
                                                value="6 months to 1 year" id="mpr-jobspec-workexp2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-workexp2">6 months to 1 year</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-workexp" type="checkbox"
                                                value="1 to 2 years" id="mpr-jobspec-workexp3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-workexp3">1 to 2 years</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-workexp" type="checkbox"
                                                value="2 years or more" id="mpr-jobspec-workexp4">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-workexp4">2 years or more</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-workexp" type="checkbox"
                                                value="5 years or more" id="mpr-jobspec-workexp5">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-workexp5">5 years or more</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED: (Please
                                    enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</h6>

                                <div class="row mb-3">
                                    <div class="col">
                                        <textarea class="form-control form-control-sm" id="mpr-jobspec-duties" rows="3"></textarea>
                                    </div>
                                </div>

                                <h6 class="mt-4">TECHNICAL COMPETENCIES</h6>

                                <div class="row mb-3">
                                    <div class="col">
                                        <textarea class="form-control form-control-sm" id="mpr-jobspec-technical" rows="3"></textarea>
                                    </div>
                                </div>

                                <h6 class="mt-4">Competencies Needed to Perform Responsibilities (Ex. Knows how to
                                    prepare financial statement, knows Computer Programming). Please enumerate.</h6>

                                <div class="row mb-3">
                                    <div class="col">
                                        <textarea class="form-control form-control-sm" id="mpr-jobspec-competenciesneeded" rows="3"></textarea>
                                    </div>
                                </div>

                                <h6 class="mt-4">Computer skills: (Please check box of preferred option/s)</h6>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-compskill" type="checkbox"
                                                value="Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )"
                                                id="mpr-jobspec-compskill1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-compskill1">Proficient in MS Office (Word, Excel, Power
                                                Point, Acces, Visio, etc. )</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-compskill" type="checkbox"
                                                value="Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)"
                                                id="mpr-jobspec-compskill2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-compskill2">Proficient in Accounting Software (Peach Tree,
                                                Quick Books, SAP, etc.)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-compskill" type="checkbox"
                                                value="Layout Designing Skills (using Publisher, Corel, PageMaker etc.)"
                                                id="mpr-jobspec-compskill3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-compskill3">Layout Designing Skills (using Publisher,
                                                Corel, PageMaker etc.)</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">Other Skills: (Ex. Driving; 4-wheel, 2-Wheel Vehicles)</h6>

                                <div class="row mb-3">
                                    <div class="col">
                                        <textarea class="form-control form-control-sm" id="mpr-jobspec-otherskill" rows="3"></textarea>
                                    </div>
                                </div>

                                <h6 class="mt-3 pt-1">META PROGRAM</h6>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">A.APPROACH TO
                                                PROBLEM</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-a" value="Towards"
                                                        id="mpr-jobspec-metaprog-a-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-a-opt1">Towards</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-a" value="Away from"
                                                        id="mpr-jobspec-metaprog-a-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-a-opt2">Away from</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-a" value="Both"
                                                        id="mpr-jobspec-metaprog-a-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-a-opt3">Both</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">B.TIME FRAME</label>
                                            <label class="col-form-label col-form-label-sm col-12">(Terms)</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-b1" value="Long- Term"
                                                        id="mpr-jobspec-metaprog-b1-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-b1-opt1">Long- Term</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-b1" value="Medium-Term"
                                                        id="mpr-jobspec-metaprog-b1-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-b1-opt2">Medium-Term</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-b1" value="Short-Term"
                                                        id="mpr-jobspec-metaprog-b1-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-b1-opt3">Short-Term</label>
                                                </div>
                                            </div>
                                            <label class="col-form-label col-form-label-sm col-12">(Time)</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-b2" value="Past"
                                                        id="mpr-jobspec-metaprog-b2-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-b2-opt1">Past</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-b2" value="Present"
                                                        id="mpr-jobspec-metaprog-b2-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-b2-opt2">Present</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-b2" value="Future"
                                                        id="mpr-jobspec-metaprog-b2-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-b2-opt3">Future</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">C. LOCUS OF
                                                CONTROL</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-c" value="Internal"
                                                        id="mpr-jobspec-metaprog-c-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-c-opt1">Internal</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-c" value="External"
                                                        id="mpr-jobspec-metaprog-c-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-c-opt2">External</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-c" value="Both"
                                                        id="mpr-jobspec-metaprog-c-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-c-opt3">Both</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">D. MODE OF
                                                COMPARISON</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-d" value="Match"
                                                        id="mpr-jobspec-metaprog-d-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-d-opt1">Match</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-d" value="Mismatch"
                                                        id="mpr-jobspec-metaprog-d-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-d-opt2">Mismatch</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-d" value="Both"
                                                        id="mpr-jobspec-metaprog-d-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-d-opt3">Both</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">E. Chunk Size</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-e" value="Generalities"
                                                        id="mpr-jobspec-metaprog-e-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-e-opt1">Generalities</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-e" value="Details"
                                                        id="mpr-jobspec-metaprog-e-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-e-opt2">Details</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-e" value="Both"
                                                        id="mpr-jobspec-metaprog-e-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-e-opt3">Both</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">F.APPROACH TO SOLVING
                                                PROBLEMS</label>
                                            <label class="col-form-label col-form-label-sm col-12">(Task)</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-f1" value="Choice"
                                                        id="mpr-jobspec-metaprog-f1-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-f1-opt1">Choice</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-f1" value="Procedure"
                                                        id="mpr-jobspec-metaprog-f1-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-f1-opt2">Procedure</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-f1" value="Both"
                                                        id="mpr-jobspec-metaprog-f1-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-f1-opt3">Both</label>
                                                </div>
                                            </div>
                                            <label class="col-form-label col-form-label-sm col-12">(Relationship)</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-f2" value="Self"
                                                        id="mpr-jobspec-metaprog-f2-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-f2-opt1">Self</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-f2" value="Others"
                                                        id="mpr-jobspec-metaprog-f2-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-f2-opt2">Others</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-f2" value="We, Both, Team"
                                                        id="mpr-jobspec-metaprog-f2-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-f2-opt3">We, Both, Team</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-form-label col-form-label-sm col-12">G. THINKING
                                                STYLE</label>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-g" value="Vision"
                                                        id="mpr-jobspec-metaprog-g-opt1">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-g-opt1">Vision</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-g" value="Action"
                                                        id="mpr-jobspec-metaprog-g-opt2">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-g-opt2">Action</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="mpr-jobspec-metaprog-g" value="Emotion"
                                                        id="mpr-jobspec-metaprog-g-opt3">
                                                    <label class="form-check-label col-form-label-sm py-0"
                                                        for="mpr-jobspec-metaprog-g-opt3">Emotion</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-3 pt-1">TAPT</h6>

                                <div class="row mb-3 gap-1">
                                    <label class="col-form-label col-form-label-sm col-12">Please check four preferred
                                        personality type combination:</label>
                                    <div class="col-md border rounded">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt1" value="Extrovert"
                                                id="mpr-jobspec-tapt1-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt1-opt1">Extrovert</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt1" value="Introvert"
                                                id="mpr-jobspec-tapt1-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt1-opt2">Introvert</label>
                                        </div>
                                    </div>
                                    <div class="col-md border rounded">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt2" value="Sensitive"
                                                id="mpr-jobspec-tapt2-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt2-opt1">Sensitive</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt2" value="Intuitive"
                                                id="mpr-jobspec-tapt2-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt2-opt2">Intuitive</label>
                                        </div>
                                    </div>
                                    <div class="col-md border rounded">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt3" value="Thinking" id="mpr-jobspec-tapt3-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt3-opt1">Thinking</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt3" value="Feeling" id="mpr-jobspec-tapt3-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt3-opt2">Feeling</label>
                                        </div>
                                    </div>
                                    <div class="col-md border rounded">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt4" value="Judging" id="mpr-jobspec-tapt4-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt4-opt1">Judging</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-tapt" type="radio"
                                                name="mpr-jobspec-tapt4" value="Perceiving"
                                                id="mpr-jobspec-tapt4-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-tapt4-opt2">Perceiving</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">ENNEAGRAM</h6>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">Please check box of preferred
                                        option:</label>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Perfectionist" id="mpr-jobspec-enneagram-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt1">Perfectionist</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Helper" id="mpr-jobspec-enneagram-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt2">Helper</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Achiever" id="mpr-jobspec-enneagram-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt3">Achiever</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Romantic" id="mpr-jobspec-enneagram-opt4">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt4">Romantic</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Observer" id="mpr-jobspec-enneagram-opt5">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt5">Observer</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Questioner" id="mpr-jobspec-enneagram-opt6">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt6">Questioner</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Adventurer" id="mpr-jobspec-enneagram-opt7">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt7">Adventurer</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Asserter" id="mpr-jobspec-enneagram-opt8">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt8">Asserter</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-enneagram" type="checkbox"
                                                value="Peacemaker" id="mpr-jobspec-enneagram-opt9">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-enneagram-opt9">Peacemaker</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-3 pt-1">LEARNING STYLE</h6>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">Please check box of preferred
                                        option:</label>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-learnstyle" type="checkbox"
                                                value="Visual" id="mpr-jobspec-learnstyle-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-learnstyle-opt1">Visual</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-learnstyle" type="checkbox"
                                                value="Auditory" id="mpr-jobspec-learnstyle-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-learnstyle-opt2">Auditory</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-learnstyle" type="checkbox"
                                                value="Kinesthetic" id="mpr-jobspec-learnstyle-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-learnstyle-opt3">Kinesthetic</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">CAREER ANCHOR</h6>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">Please check top 3 preferred
                                        choices:</label>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Technical/Functional Competence" id="mpr-jobspec-career-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt1">Technical/Functional Competence</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Autonomy/Independence" id="mpr-jobspec-career-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt2">Autonomy/Independence</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Entrepreneurial Creativity" id="mpr-jobspec-career-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt3">Entrepreneurial Creativity</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Pure Challenge" id="mpr-jobspec-career-opt4">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt4">Pure Challenge</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="General/Managerial Competence" id="mpr-jobspec-career-opt5">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt5">General/Managerial Competence</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Security/ Stability" id="mpr-jobspec-career-opt6">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt6">Security/ Stability</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Sense of Service/Dedication to A Cause"
                                                id="mpr-jobspec-career-opt7">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt7">Sense of Service/Dedication to A
                                                Cause</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-career" type="checkbox"
                                                value="Lifestyle" id="mpr-jobspec-career-opt8">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-career-opt8">Lifestyle</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">MOTIVATION TO WORK</h6>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">Please check top 3 preferred
                                        choices:</label>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Achievement" id="mpr-jobspec-motivation-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt1">Achievement</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Personal Growth" id="mpr-jobspec-motivation-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt2">Personal Growth</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Prestige" id="mpr-jobspec-motivation-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt3">Prestige</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Family" id="mpr-jobspec-motivation-opt4">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt4">Family</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Pleasure" id="mpr-jobspec-motivation-opt5">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt5">Pleasure</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Recognition" id="mpr-jobspec-motivation-opt6">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt6">Recognition</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Independence" id="mpr-jobspec-motivation-opt7">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt7">Independence</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Power" id="mpr-jobspec-motivation-opt8">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt8">Power</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Security" id="mpr-jobspec-motivation-opt9">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt9">Security</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Money" id="mpr-jobspec-motivation-opt10">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt10">Money</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Pressure" id="mpr-jobspec-motivation-opt11">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt11">Pressure</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-motivation" type="checkbox"
                                                value="Self-Esteem" id="mpr-jobspec-motivation-opt12">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-motivation-opt12">Self-Esteem</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-4">PERSONALITY TYPE</h6>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">Please check box preferred
                                        choices:</label>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-personality" type="checkbox"
                                                value="Controller" id="mpr-jobspec-personality-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-personality-opt1">Controller</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-personality" type="checkbox"
                                                value="Analyst" id="mpr-jobspec-personality-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-personality-opt2">Analyst</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-personality" type="checkbox"
                                                value="Promoter" id="mpr-jobspec-personality-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-personality-opt3">Promoter</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-personality" type="checkbox"
                                                value="Supporter" id="mpr-jobspec-personality-opt4">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-personality-opt4">Supporter</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-3 pt-1">RAVEN</h6>

                                <div class="row mb-3">
                                    <label class="col-form-label col-form-label-sm col-12">Please check box of preferred
                                        option:</label>
                                    <div class="col-md-4">
                                        <label class="col-form-label col-form-label-sm">LOW</label>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-low" type="checkbox"
                                                value="Low" id="mpr-jobspec-raven-low-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-low-opt1">Low</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-low" type="checkbox"
                                                value="Average" id="mpr-jobspec-raven-low-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-low-opt2">Average</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-low" type="checkbox"
                                                value="High" id="mpr-jobspec-raven-low-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-low-opt3">High</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label col-form-label-sm">AVERAGE</label>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-average" type="checkbox"
                                                value="Low" id="mpr-jobspec-raven-average-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-average-opt1">Low</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-average" type="checkbox"
                                                value="Average" id="mpr-jobspec-raven-average-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-average-opt2">Average</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-average" type="checkbox"
                                                value="High" id="mpr-jobspec-raven-average-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-average-opt3">High</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="col-form-label col-form-label-sm">HIGH</label>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-high" type="checkbox"
                                                value="Low" id="mpr-jobspec-raven-high-opt1">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-high-opt1">Low</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-high" type="checkbox"
                                                value="Average" id="mpr-jobspec-raven-high-opt2">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-high-opt2">Average</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mpr-jobspec-raven-high" type="checkbox"
                                                value="High" id="mpr-jobspec-raven-high-opt3">
                                            <label class="form-check-label col-form-label-sm py-0"
                                                for="mpr-jobspec-raven-high-opt3">High</label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-3 pt-1 d-none">LEADERSHIP STYLE (To be filled up by HR)</h6>

                                <div class="row mb-3 d-none">
                                    <div class="col">
                                        <textarea class="form-control form-control-sm" id="mpr-jobspec-leadership" rows="3"></textarea>
                                    </div>
                                </div>

                                <h6 class="mt-3 pt-1">REMARKS:</h6>

                                <div class="row mb-3">
                                    <div class="col">
                                        <textarea class="form-control form-control-sm" id="mpr-jobspec-remarks" rows="3"></textarea>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="modal-footer mpr-footer-tone">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-mpr-view-jobspec" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="modal-mpr-view-jobspec-label" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modal-mpr-view-jobspec-label">Manpower Request Update</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row" id="mpr-view-jobspec-err"></div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm col-3">Department</label>
                                        <label class="col-form-label col-form-label-sm col-9"
                                            id="mpr-view-jobspec-dept">(Department)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm col-3">Section</label>
                                        <label class="col-form-label col-form-label-sm col-9"
                                            id="mpr-view-jobspec-section">(Section)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm col-3">Position</label>
                                        <label class="col-form-label col-form-label-sm col-9"
                                            id="mpr-view-jobspec-pos">(Position)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm col-3">Employment Status</label>
                                        <label class="col-form-label col-form-label-sm col-9"
                                            id="mpr-view-jobspec-emplstat">(Employment Status)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm col-3">Gender</label>
                                        <label class="col-form-label col-form-label-sm col-9"
                                            id="mpr-view-jobspec-gender">(Gender)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm col-3">Age</label>
                                        <label class="col-form-label col-form-label-sm col-9"
                                            id="mpr-view-jobspec-age">(Min-Max)</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4">EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED: (Please check box of preferred
                                option)</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-edu"></div>

                            <h6 class="mt-4">WORK EXPERIENCE(S) REQUIRED: (Please check box of preferred option)</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-workexp"></div>

                            <h6 class="mt-4">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED: (Please
                                enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-duties"
                                        style="white-space: pre-line;"></p>
                                </div>
                            </div>

                            <h6 class="mt-4">TECHNICAL COMPETENCIES</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-technical"
                                        style="white-space: pre-line;"></p>
                                </div>
                            </div>

                            <h6 class="mt-4">Competencies Needed to Perform Responsibilities (Ex. Knows how to prepare
                                financial statement, knows Computer Programming). Please enumerate.</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-competenciesneeded"
                                        style="white-space: pre-line;"></p>
                                </div>
                            </div>

                            <h6 class="mt-4">Computer skills: (Please check box of preferred option/s)</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-compskill"></div>

                            <h6 class="mt-4">Other Skills: (Ex. Driving; 4-wheel, 2-Wheel Vehicles)</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-otherskill"
                                        style="white-space: pre-line;"></p>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">META PROGRAM</h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">A.APPROACH TO
                                            PROBLEM</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-a">(Answer)</label>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">B.TIME FRAME</label>
                                        <label class="col-form-label col-form-label-sm col-12">(Terms)</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-b1">(Answer)</label>
                                        <label class="col-form-label col-form-label-sm col-12">(Time)</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-b2">(Answer)</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">C. LOCUS OF CONTROL</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-c">(Answer)</label>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">D. MODE OF
                                            COMPARISON</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-d">(Answer)</label>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">E. Chunk Size</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-e">(Answer)</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">F.APPROACH TO SOLVING
                                            PROBLEMS</label>
                                        <label class="col-form-label col-form-label-sm col-12">(Task)</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-f1">(Answer)</label>
                                        <label class="col-form-label col-form-label-sm col-12">(Relationship)</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-f2">(Answer)</label>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-form-label col-form-label-sm col-12">G. THINKING STYLE</label>
                                        <label class="col-form-label col-form-label-sm col-12"
                                            id="mpr-view-jobspec-metaprog-g">(Answer)</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">TAPT</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-tapt"></div>

                            <h6 class="mt-4">ENNEAGRAM</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-enneagram"></div>

                            <h6 class="mt-3 pt-1">LEARNING STYLE</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-learnstyle"></div>

                            <h6 class="mt-4">CAREER ANCHOR</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-career"></div>

                            <h6 class="mt-4">MOTIVATION TO WORK</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-motivation"></div>

                            <h6 class="mt-4">PERSONALITY TYPE</h6>

                            <div class="row mb-3" id="mpr-view-jobspec-personality"></div>

                            <h6 class="mt-3 pt-1">RAVEN</h6>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm">LOW</label>
                                        <div class="col" id="mpr-view-jobspec-raven-low"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm">AVERAGE</label>
                                        <div class="col" id="mpr-view-jobspec-raven-average"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <label class="col-form-label col-form-label-sm">HIGH</label>
                                        <div class="col" id="mpr-view-jobspec-raven-high"></div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1 d-none">LEADERSHIP STYLE (To be filled up by HR)</h6>

                            <div class="row mb-3 d-none">
                                <div class="col">
                                    <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-leadership"
                                        style="white-space: pre-line;"></p>
                                </div>
                            </div>

                            <h6 class="mt-3 pt-1">REMARKS:</h6>

                            <div class="row mb-3">
                                <div class="col">
                                    <p class="col-form-label col-form-label-sm" id="mpr-view-jobspec-remarks"
                                        style="white-space: pre-line;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer mpr-footer-tone">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /#mpr-app -->

    <script>
        let curtab = 'pending';

        // Route template for an applicant's interview details page — the
        // placeholder gets swapped for the real app_id at runtime since
        // Laravel's route() helper can't be called from plain JS.
        const APPLICANT_INTERVIEW_URL_TEMPLATE = @json(route('applicant.show', ['id' => '__APP_ID__', 'tab' => 'interview-details']));

        function applicantInterviewUrl(appId) {
            return APPLICANT_INTERVIEW_URL_TEMPLATE.replace('__APP_ID__', appId);
        }

        // Cache of interview-round data for the currently-selected applicant,
        // keyed by interview type, e.g. { "Initial": {...}, "2nd Prelim": {...} }
        let currentApplicantInterviews = {};

        /** Selector map for the two interview-history side panels: the New
         *  Request modal ('mpr') and the View Request modal ('view-mpr').
         *  Lets the shared functions below target whichever panel applies. */
        const IV_SCOPES = {
            'mpr': {
                toggles: '#mpr-interview-type-toggles',
                name: '#mpr-iv-applicant-name',
                openLink: '#mpr-iv-open-link',
                emptyState: '#mpr-iv-empty-state',
                detailBody: '#mpr-iv-detail-body',
                interviewer: '#mpr-iv-interviewer',
                date: '#mpr-iv-date',
                department: '#mpr-iv-department',
                company: '#mpr-iv-company',
                position: '#mpr-iv-position',
                verdict: '#mpr-iv-verdict',
                remarks: '#mpr-iv-remarks',
                recommendation: '#mpr-iv-recommendation'
            },
            'view-mpr': {
                toggles: '#view-mpr-interview-type-toggles',
                name: '#view-mpr-iv-applicant-name',
                openLink: '#view-mpr-iv-open-link',
                emptyState: '#view-mpr-iv-empty-state',
                detailBody: '#view-mpr-iv-detail-body',
                interviewer: '#view-mpr-iv-interviewer',
                date: '#view-mpr-iv-date',
                department: '#view-mpr-iv-department',
                company: '#view-mpr-iv-company',
                position: '#view-mpr-iv-position',
                verdict: '#view-mpr-iv-verdict',
                remarks: '#view-mpr-iv-remarks',
                recommendation: '#view-mpr-iv-recommendation'
            }
        };

        /** Works out which panel a row's chips/dropdowns should talk to,
         *  based on which modal the row currently lives in. */
        function scopeForRow($row) {
            return $row.closest('#modal-view-mpr').length ? 'view-mpr' : 'mpr';
        }

        /**
         * Centralized POST helper used by every modal form on this page.
         * Handles CSRF header, error rendering, double-submit prevention,
         * and tab reload on success.
         */
        async function postForm({
            url,
            formData,
            errSelector,
            submitBtn,
            successMsg,
            onSuccess,
            method = 'POST',
            alertOnError = false
        }) {
            $(errSelector).html('');
            if (submitBtn) submitBtn.prop('disabled', true);

            try {
                const response = await fetch(url, {
                    method,
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = {};
                try {
                    result = await response.json();
                } catch (parseErr) {
                    result = {
                        success: false,
                        error: 'Unexpected server response.'
                    };
                }

                if (response.ok && result.success) {
                    if (typeof onSuccess === 'function') {
                        onSuccess(result);
                    } else {
                        $(submitBtn ? submitBtn.closest('.modal') : '.modal').modal('hide');
                    }
                    if (successMsg) alert(successMsg);
                    await reloadActiveTab();
                    return true;
                }

                $(errSelector).html(`<p style="color: red;">Error: ${result.error ?? 'Unknown error.'}</p>`);
                if (alertOnError) alert('Error: ' + (result.error ?? 'Unknown error.'));
                return false;

            } catch (error) {
                console.error('Request failed:', error);
                $(errSelector).html('<p style="color: red;">Request failed. Please try again.</p>');
                if (alertOnError) alert('Request failed. Please try again.');
                return false;
            } finally {
                if (submitBtn) submitBtn.prop('disabled', false);
            }
        }

        /** Escape text before injecting into HTML strings built via string concatenation. */
        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        async function reloadActiveTab() {
            const $active = $('#mprTab .mpr-tab.active');
            if ($active.length) await loadMPR($active.data('stat'));
            await loadCounts();
        }

        function activeDataTable() {
            const $table = $('#mprTabContent .tab-pane:visible > table').first();
            return ($table.length && $.fn.DataTable.isDataTable($table)) ? $table.DataTable() : null;
        }

        function renderPager(api) {
            const info = api.page.info();
            const $pager = $('#mpr-pager');

            if (info.pages <= 1) {
                $pager.hide();
                return;
            }
            $pager.show();

            const start = info.recordsDisplay === 0 ? 0 : info.start + 1;
            const end = Math.min(info.start + info.length, info.recordsDisplay);
            $('#mpr-pager-summary').text('Showing ' + start + '–' + end + ' of ' + info.recordsDisplay);

            const $btns = $('#mpr-pager-buttons').empty();
            const current = info.page;
            for (let p = 0; p < info.pages; p++) {
                $('<button>').addClass('mpr-pager-btn').toggleClass('active', p === current)
                    .text(p + 1)
                    .on('click', () => {
                        api.page(p).draw('page');
                    })
                    .appendTo($btns);
            }
            $('<button>').addClass('mpr-pager-btn').html('<i class="fa fa-chevron-right"></i>')
                .prop('disabled', current >= info.pages - 1)
                .on('click', () => {
                    api.page('next').draw('page');
                })
                .appendTo($btns);
        }

        /* ---------- Applicant slots (Number-Needed aware, chip UI) ---------- */

        /** Reads the current per-row applicant slot array (array of {id,name} | null). */
        function getRowApplicants($row) {
            return $row.data('applicants') || [];
        }

        function setRowApplicants($row, arr) {
            $row.data('applicants', arr);
        }

        /** Resizes the slot array to match Number Needed, then re-renders.
         *  Pass fixedCount to override reading from the number input
         *  (used by view-mpr rows which have no editable number cell). */
        function rebuildApplicantSlots($row, fixedCount) {
            const count = (fixedCount !== undefined && fixedCount > 0) ?
                fixedCount :
                Math.max(1, parseInt($row.find('.mpr-additional-number, .mpr-replacement-number').val(), 10) || 1);
            let applicants = getRowApplicants($row);
            applicants = Array.from({
                length: count
            }, (_, i) => applicants[i] || null);
            setRowApplicants($row, applicants);
            renderApplicantSlots($row);
        }

        /** Renders each slot as either a chip (filled) or a dropdown (empty). */
        function renderApplicantSlots($row) {
            const applicants = getRowApplicants($row);
            const $slots = $row.find('.mpr-applicant-slots').empty();

            const scope = scopeForRow($row);

            applicants.forEach((applicant, i) => {
                if (applicant) {
                    const $chip = $('<div class="mpr-applicant-chip">')
                        .attr('data-slot-index', i)
                        .append($('<span class="mpr-applicant-chip-name">').text(applicant.name))
                        .append(
                            $('<button type="button" class="mpr-applicant-chip-remove">&times;</button>')
                            .on('click', function(e) {
                                e.stopPropagation();
                                const arr = getRowApplicants($row);
                                arr[i] = null;
                                setRowApplicants($row, arr);
                                renderApplicantSlots($row);
                                resetApplicantInterviewPanel(scope);
                            })
                        );

                    $chip.on('click', function() {
                        $row.closest('.modal').find('.mpr-applicant-chip').removeClass('active');
                        $chip.addClass('active');
                        loadApplicantInterviewHistory(applicant.id, applicant.name, scope);
                    });

                    $slots.append($chip);
                } else {
                    const $sel = $('<select class="form-select form-select-sm mpr-additional-applicant">')
                        .append('<option value="">- Slot ' + (i + 1) + ' -</option>');

                    $('#mpr-applicant-master-options option').each(function() {
                        if (this.value) $sel.append($(this).clone());
                    });

                    $sel.on('change', function() {
                        const id = this.value;
                        const name = $(this).find('option:selected').text();
                        if (!id) return;

                        const arr = getRowApplicants($row);
                        arr[i] = {
                            id,
                            name
                        };
                        setRowApplicants($row, arr);
                        renderApplicantSlots($row);

                        $row.closest('.modal').find('.mpr-applicant-chip').removeClass('active');
                        $row.find('.mpr-applicant-chip[data-slot-index="' + i + '"]').addClass('active');

                        loadApplicantInterviewHistory(id, name, scope);
                    });

                    $slots.append($sel);
                }
            });
        }

        /**
         * Hydrates a row's applicant slots from a comma-separated CSV of saved
         * applicant ids (the format mp_replacement/mp_additional persist them
         * in). Names are resolved client-side from the master option list since
         * only ids are stored server-side. Shared by both the New Request modal
         * (4-element slot tuples) and the View Request modal (7-element tuples).
         */
        function hydrateSavedApplicants($row, savedIdsCsv) {
            const savedIds = (savedIdsCsv || '').split(',').filter(Boolean);
            if (!savedIds.length) return;

            const arr = getRowApplicants($row);
            savedIds.forEach((id, idx) => {
                const name = $('#mpr-applicant-master-options option[value="' + id + '"]').text();
                if (idx < arr.length) arr[idx] = {
                    id,
                    name
                };
            });
            setRowApplicants($row, arr);
            renderApplicantSlots($row);
        }

        /**
         * Clones the given empty-row template into the target table body,
         * filling in position/number/reason/date and rebuilding+hydrating its
         * applicant slots. Used for both replacement and additional rows in the
         * editable "New/Edit Request" modal. `item` is the parsed
         * [position, count, reason, date, applicants_csv] tuple.
         */
        function appendEditableSlotRow($template, $tbody, item, selectors) {
            const $row = $template.clone();
            $row.find(selectors.position).val(item[0] || '');
            $row.find(selectors.number).val(item[1] || 1);
            $row.find(selectors.reason).val(item[2] || '');
            $row.find(selectors.date).val(item[3] || '');
            $tbody.append($row);

            setRowApplicants($row, []);
            rebuildApplicantSlots($row);
            hydrateSavedApplicants($row, item[4]);

            return $row;
        }

        /**
         * Builds a read-only summary <tr> for the "View Request" modal (used for
         * both replacement and additional positions). `item` is the parsed
         * [title, code, count, reason, date, applicants_csv, fill] tuple coming
         * back from the server's parseSlots() output.
         */
        /**
         * Builds a read-only summary <tr> for the "View Request" modal and
         * appends it to $tbody BEFORE initialising applicant slots. The early
         * append is required so that scopeForRow() can walk up to
         * #modal-view-mpr via closest() when renderApplicantSlots() fires.
         * Without it, scopeForRow() finds no ancestor modal and falls back to
         * the 'mpr' scope, pointing chip clicks at the wrong interview panel.
         */
        function buildViewSlotRow(item, {
            rowClass,
            fillClass
        }, $tbody) {
            const $row = $('<tr/>').addClass(rowClass)
                .attr('position', item[1])
                .attr('number', item[2])
                .attr('reason', item[3])
                .attr('dateneed', item[4]);

            $row.append($('<td>').css('cursor', 'pointer').text(item[0]).on('click', () => view_jobspec(item[1])));
            $row.append($('<td>').text(item[2]));
            $row.append($('<td>').text(item[3]));
            $row.append($('<td>').text(item[4]));
            $row.append($('<td>').append($('<div class="mpr-applicant-slots">')));
            $row.append(
                $('<td>').addClass('mpr-fill-td').append(
                    $('<input type="number">')
                    .attr('min', 0)
                    .attr('max', item[2])
                    .addClass('form-control form-control-sm ' + fillClass)
                    .val(item[6] || 0)
                )
            );

            // Append to DOM first — scopeForRow() depends on DOM ancestry.
            $tbody.append($row);

            const slotCount = parseInt(item[2]) || 1;
            setRowApplicants($row, []);
            rebuildApplicantSlots($row, slotCount);
            hydrateSavedApplicants($row, item[5]);

            return $row;
        }

        /** Reads the editable position rows (New/Edit Request modal) for one
         *  section (replacement or additional) back into the payload shape
         *  expected by ManpowerRequestController::store(). */
        function collectEditableSlotPayload($form, selectors) {
            const rows = [];
            $form.find(selectors.position).each(function() {
                if (!this.value) return;
                const $row = $(this).closest('tr');
                rows.push({
                    position: this.value,
                    applicants: getRowApplicants($row).map(a => a ? a.id : null),
                    count: $row.find(selectors.number).val(),
                    reason: $row.find(selectors.reason).val(),
                    date: $row.find(selectors.date).val()
                });
            });
            return rows;
        }

        /** Reads the read-only summary rows (View Request modal) for one
         *  section back into the payload shape expected by
         *  ManpowerRequestController::fillRequest(). */
        function collectViewSlotPayload($form, rowClass, fillClass) {
            const rows = [];
            $form.find('.' + rowClass).each(function() {
                if (!$(this).attr('position')) return;
                const $row = $(this);
                rows.push({
                    position: $row.attr('position'),
                    count: $row.attr('number'),
                    reason: $row.attr('reason'),
                    date: $row.attr('dateneed'),
                    applicants_csv: getRowApplicants($row).map(a => a ? a.id : '').join(','),
                    fill: $row.find('.' + fillClass).val()
                });
            });
            return rows;
        }

        /* ---------- Applicant interview history (new) ---------- */

        /** Resets the interview panel for the given scope back to its empty state. */
        function resetApplicantInterviewPanel(scope) {
            const cfg = IV_SCOPES[scope];
            currentApplicantInterviews = {};
            $(cfg.toggles).empty();
            $(cfg.name).text('No applicant selected').addClass('empty');
            $(cfg.openLink).addClass('d-none').attr('href', '#');
            $(cfg.emptyState).show().text('Select an applicant to view interview history.');
            $(cfg.detailBody).hide();
        }

        /** Builds the verdict pill class from a verdict string. */
        function verdictPillClass(verdict) {
            if (verdict === 'Hired') return 'hired';
            if (verdict === 'Not Hired') return 'not-hired';
            return 'pending';
        }

        /** Renders one interview round's data into the detail card for the given scope. */
        function showInterviewDetail(type, scope) {
            const cfg = IV_SCOPES[scope];
            const data = currentApplicantInterviews[type] || {};

            $(cfg.toggles).find('.mpr-iv-toggle').removeClass('active');
            $(cfg.toggles).find('.mpr-iv-toggle[data-type="' + type + '"]').addClass('active');

            $(cfg.interviewer).text(data.interviewer_name || '—');
            $(cfg.date).text(data.interview_date || '—');
            $(cfg.department).text(data.department || '—');
            $(cfg.company).text(data.company || '—');
            $(cfg.position).text(data.position || '—');

            const verdict = data.verdict || 'Pending';
            $(cfg.verdict)
                .removeClass('hired not-hired pending')
                .addClass(verdictPillClass(data.verdict))
                .text(verdict);

            $(cfg.remarks).html(data.remarks || '<span class="text-muted">No remarks recorded.</span>');
            $(cfg.recommendation).html(data.recommendation ||
                '<span class="text-muted">No recommendation recorded.</span>');

            $(cfg.emptyState).hide();
            $(cfg.detailBody).show();
        }

        /** Fetches interview history for an applicant and rebuilds the toggle row
         *  in the side panel for the given scope ('mpr' or 'view-mpr'). */
        async function loadApplicantInterviewHistory(appId, applicantLabel, scope) {
            const cfg = IV_SCOPES[scope];
            const $toggles = $(cfg.toggles).empty();

            if (!appId) {
                resetApplicantInterviewPanel(scope);
                return;
            }

            $(cfg.name).text(applicantLabel || '—').removeClass('empty');
            $(cfg.openLink).attr('href', applicantInterviewUrl(appId)).removeClass('d-none');
            $(cfg.emptyState).show().text('Loading interview history…');
            $(cfg.detailBody).hide();

            try {
                const response = await fetch('/manpower/applicant/' + appId + '/interviews');
                if (!response.ok) throw new Error('Failed to fetch interview history');

                currentApplicantInterviews = await response.json();
                const types = Object.keys(currentApplicantInterviews);

                if (types.length === 0) {
                    $(cfg.emptyState).show().text('This applicant has no recorded interviews yet.');
                    $(cfg.detailBody).hide();
                    return;
                }

                types.forEach((type, idx) => {
                    $('<button type="button">')
                        .addClass('mpr-iv-toggle')
                        .toggleClass('active', idx === 0)
                        .attr('data-type', type)
                        .text(type)
                        .on('click', function() {
                            showInterviewDetail(type, scope);
                        })
                        .appendTo($toggles);
                });

                showInterviewDetail(types[0], scope);

            } catch (error) {
                console.error('Error fetching interview history:', error);
                $(cfg.emptyState).show().text('Failed to load interview history. Please try again.');
                $(cfg.detailBody).hide();
            }
        }

        /* ---------- Generic Blade/jobspec helpers (dedupe view_jobspec + modal-mpr-jobspec) ---------- */

        /** Renders a list of plain strings as stacked "- value" labels into a
         *  container, replacing its current contents. Used throughout
         *  view_jobspec() for every checkbox-array-derived field. */
        function renderLabelList(selector, items, colClass = 'col-12') {
            const $el = $(selector).empty();
            (items || []).forEach(i => {
                $el.append($('<label class="col-form-label col-form-label-sm ' + colClass + '">').text('- ' + i));
            });
        }

        /** Checks every checkbox/radio matching `selector` whose value is in
         *  `values`. Used to restore a jobspec's saved checkbox groups when the
         *  edit modal opens. */
        function checkValues(selector, values) {
            (values || []).forEach(v => {
                $(selector + '[value="' + v + '"]').prop('checked', true);
            });
        }

        $(function() {
            const tr_replacement = $('#mpr-replacement-table').find('tbody tr').first();
            const tr_additional = $('#mpr-additional-table').find('tbody tr').first();

            /* Tab switching — same lazy-load behavior as before, but driven
               by plain divs/clicks instead of Bootstrap's nav-link/tab-pane
               machinery (since the visual redesign replaces that markup). */
            $('#mprTab .mpr-tab').click(function() {
                let stat = $(this).data('stat');

                $('#mprTab .mpr-tab').removeClass('active').attr('aria-selected', 'false');
                $(this).addClass('active').attr('aria-selected', 'true');

                $('#mprTabContent .tab-pane').hide();
                $('#' + stat + '-tab-pane').show();

                if ((curtab == stat && !$('#' + stat + '-tab-pane').is(':empty')) || (curtab != stat && $(
                        '#' + stat + '-tab-pane').is(':empty'))) {
                    loadMPR(stat);
                }
                curtab = stat;
            });

            // FIX: button is a sibling of the table, not a child — use closest().find() instead
            $('#mpr-replacement-table').closest('.mpr-card-table').find('.btn-add-row').click(function() {
                const $newRow = tr_replacement.clone();
                $(this).closest('.mpr-card-table').find('table tbody').append($newRow);
                setRowApplicants($newRow, []);
                rebuildApplicantSlots($newRow);
            });

            $('#mpr-additional-table').closest('.mpr-card-table').find('.btn-add-row').click(function() {
                const $newRow = tr_additional.clone();
                $(this).closest('.mpr-card-table').find('table tbody').append($newRow);
                setRowApplicants($newRow, []);
                rebuildApplicantSlots($newRow);
            });

            $('#form-mpr').on('click', '.btn-del', function() {
                $(this).closest('tr').remove();
            });

            /* Rows no longer carry data-bs-toggle="modal" (that approach raced
               against confirm()/alert() dialogs and native click semantics —
               see history). Instead we open the view modal explicitly here,
               and simply skip rows where the click originated inside the
               action buttons, sidestepping the race entirely. */
            $('#mprTabContent').on('click', 'tr.mpr-clickable-row', function(e) {
                if ($(e.target).closest('.mpr-row-actions').length) return;

                const modalEl = document.getElementById('modal-view-mpr');
                bootstrap.Modal.getOrCreateInstance(modalEl).show(this);
            });

            /* Rebuild applicant slots whenever Number Needed changes. */
            $('#mpr-additional-table').on('input', '.mpr-additional-number', function() {
                rebuildApplicantSlots($(this).closest('tr'));
            });

            $('#mpr-replacement-table').on('input', '.mpr-replacement-number', function() {
                rebuildApplicantSlots($(this).closest('tr'));
            });

            /* Simple client-side filter wired to the new search box; relies on
               DataTables' search() if the active table is a DataTable. */
            $('#mpr-global-search').on('keyup', function() {
                const val = $(this).val();
                const $table = $('#mprTabContent .tab-pane:visible > table');
                if ($table.length && $.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().search(val).draw();
                }
            });

            $('#mpr-page-length').on('change', function() {
                const api = activeDataTable();
                if (api) api.page.len(parseInt(this.value, 10)).draw();
            });

            $('#modal-mpr').on('show.bs.modal', function(e) {
                let btn = $(e.relatedTarget);
                const submitMode = btn.data('submit-mode') || 'draft';
                $('#mpr-submit-mode').val(submitMode);
                const $replacementBody = $('#mpr-replacement-table').find('tbody').empty();
                const $additionalBody = $('#mpr-additional-table').find('tbody').empty();
                resetApplicantInterviewPanel('mpr');

                let replacement = (btn.data('replacement') || '').match(/\[([^\]]+)\]/g);
                replacement = (replacement || []).map(group =>
                    group.replace(/[\[\]]/g, '').split('|')
                );

                let additional = (btn.data('additional') || '').match(/\[([^\]]+)\]/g);
                additional = (additional || []).map(group =>
                    group.replace(/[\[\]]/g, '').split('|')
                );

                $('#mpr-id').val(btn.data('id') || '');
                $('#mpr-nonnegotiable').val(btn.data('nonnegotiable') || '');
                $('#mpr-submit-mode').val(submitMode);
                $('#btn-delete-mpr').toggle(!!btn.data('id') && submitMode === 'draft');
                $('#btn-save-draft-mpr').toggle(!btn.data('id') || submitMode === 'draft');

                const editReason = btn.data('reason');
                if (editReason) {
                    $('#mpr-edit-reason-text').text(editReason);
                    $('#mpr-edit-reason-box').show();
                } else {
                    $('#mpr-edit-reason-box').hide();
                }

                const replacementSelectors = {
                    position: '.mpr-replacement-position',
                    number: '.mpr-replacement-number',
                    reason: '.mpr-replacement-reason',
                    date: '.mpr-replacement-dateneed'
                };
                const additionalSelectors = {
                    position: '.mpr-additional-position',
                    number: '.mpr-additional-number',
                    reason: '.mpr-additional-reason',
                    date: '.mpr-additional-dateneed'
                };

                replacement.forEach(item => appendEditableSlotRow(tr_replacement, $replacementBody, item,
                    replacementSelectors));
                additional.forEach(item => appendEditableSlotRow(tr_additional, $additionalBody, item,
                    additionalSelectors));

                if ($replacementBody.find('tr').length === 0) {
                    appendEditableSlotRow(tr_replacement, $replacementBody, [null, 1],
                    replacementSelectors);
                }
                if ($additionalBody.find('tr').length === 0) {
                    appendEditableSlotRow(tr_additional, $additionalBody, [null, 1], additionalSelectors);
                }
            });

            $('#btn-delete-mpr').on('click', function() {
                const id = $('#mpr-id').val();
                if (!id) return;
                remove_mpr(id);
            });

            $('#btn-save-draft-mpr').on('click', function() {
                $('#mpr-submit-mode').val('draft');
                $('#form-mpr').trigger('submit');
            });

            $('#btn-post-mpr').on('click', function() {
                $('#mpr-submit-mode').val('pending');
                $('#form-mpr').trigger('submit');
            });

            $('#form-mpr').submit(async function(e) {
                e.preventDefault();

                const replacement = collectEditableSlotPayload($(this), {
                    position: '.mpr-replacement-position',
                    number: '.mpr-replacement-number',
                    reason: '.mpr-replacement-reason',
                    date: '.mpr-replacement-dateneed'
                });
                const additional = collectEditableSlotPayload($(this), {
                    position: '.mpr-additional-position',
                    number: '.mpr-additional-number',
                    reason: '.mpr-additional-reason',
                    date: '.mpr-additional-dateneed'
                });

                const submitMode = $('#mpr-submit-mode').val() || 'pending';
                const hasContent = replacement.some(row => row.position) || additional.some(row => row
                    .position);

                if (submitMode === 'pending' && !hasContent) {
                    $('#mpr-err').html(
                        '<p style="color: red;">Please add at least one position before posting the request.</p>'
                        );
                    return;
                }

                let formData = new FormData();
                formData.append('id', $('#mpr-id').val());
                formData.append('replacement', JSON.stringify(replacement));
                formData.append('additional', JSON.stringify(additional));
                formData.append('nonnegotiable', $('#mpr-nonnegotiable').val());
                formData.append('submit_mode', submitMode);

                await postForm({
                    url: '/manpower/save',
                    formData,
                    errSelector: '#mpr-err',
                    submitBtn: $('#btn-post-mpr'),
                    successMsg: 'Saved',
                    onSuccess: () => $('#modal-mpr').modal('hide')
                });
            });

            $('#form-decline-mpr').submit(async function(e) {
                e.preventDefault();

                let formData = new FormData();
                formData.append('id', $('#decline-mpr-id').val());
                formData.append('stat', 'declined');
                formData.append('reason', $('#decline-mpr-reason').val());

                await postForm({
                    url: '/manpower/stat',
                    formData,
                    errSelector: '#decline-mpr-err',
                    submitBtn: $(this).find('[type="submit"]'),
                    successMsg: 'Declined',
                    onSuccess: () => $('#modal-decline-mpr').modal('hide')
                });
            });

            $('#modal-view-mpr').on('show.bs.modal', function(e) {
                if ($(e.relatedTarget).is('button')) return;

                let main_tr = $(e.relatedTarget);
                const $replacementBody = $('#view-mpr-replacement-table').find('tbody').empty();
                const $additionalBody = $('#view-mpr-additional-table').find('tbody').empty();
                resetApplicantInterviewPanel('view-mpr');

                let replacement = (main_tr.data('replacement') || []);
                let additional = (main_tr.data('additional') || []);

                $('#view-mpr-id').val(main_tr.data('id') || '');
                $('#view-mpr-nonnegotiable').html((main_tr.data('nonnegotiable') || '').replace(/\r?\n/g,
                    '<br>'));

                replacement.forEach(item => {
                    buildViewSlotRow(item, {
                        rowClass: 'view-mpr-replacement-item',
                        fillClass: 'view-mpr-replacement-fill'
                    }, $replacementBody);
                });

                additional.forEach(item => {
                    buildViewSlotRow(item, {
                        rowClass: 'view-mpr-additional-item',
                        fillClass: 'view-mpr-additional-fill'
                    }, $additionalBody);
                });

                $('#view-mpr-requestby').text(main_tr.find('td').eq(1).text());

                const isDraft = curtab === 'draft';
                $('#btn-delete-view-mpr').toggle(isDraft);
                $('#btn-post-view-mpr').toggle(isDraft);
            });

            $('#btn-delete-view-mpr').on('click', function() {
                const id = $('#view-mpr-id').val();
                if (!id) return;
                $('#modal-view-mpr').modal('hide');
                remove_mpr(id);
            });

            $('#btn-post-view-mpr').on('click', function() {
                const id = $('#view-mpr-id').val();
                if (!id) return;
                post_mpr(id);
            });

            $('#form-view-mpr').submit(async function(e) {
                e.preventDefault();

                const replacement = collectViewSlotPayload($(this), 'view-mpr-replacement-item',
                    'view-mpr-replacement-fill');
                const additional = collectViewSlotPayload($(this), 'view-mpr-additional-item',
                    'view-mpr-additional-fill');

                let formData = new FormData();
                formData.append('id', $('#view-mpr-id').val());
                formData.append('replacement', JSON.stringify(replacement));
                formData.append('additional', JSON.stringify(additional));

                await postForm({
                    url: '/manpower/fill',
                    formData,
                    errSelector: '#view-mpr-err',
                    submitBtn: $('#btn-save-view-mpr'),
                    successMsg: 'Saved',
                    onSuccess: () => $('#modal-view-mpr').modal('hide')
                });
            });

            $('#modal-mpr-update').on('show.bs.modal', function(e) {
                let btn = $(e.relatedTarget);
                $('#mpr-update-id').val(btn.data('id') || '');
                $('#mpr-update-action').val(btn.data('action') || '');
                $('#mpr-update-action-label').text((btn.data('action') || '').toUpperCase());
            });

            $('#form-mpr-update').submit(async function(e) {
                e.preventDefault();

                let formData = new FormData();
                formData.append('id', $('#mpr-update-id').val());
                formData.append('action', $('#mpr-update-action').val());
                formData.append('reason', $('#mpr-update-reason').val());

                await postForm({
                    url: '/manpower/update',
                    formData,
                    errSelector: '#mpr-update-err',
                    submitBtn: $(this).find('[type="submit"]'),
                    successMsg: 'Saved',
                    onSuccess: () => $('#modal-mpr-update').modal('hide')
                });
            });

            $('#form-mpr-jobspec').submit(async function(e) {
                e.preventDefault();

                let edu = [];
                $('.mpr-jobspec-edu [type="checkbox"]:checked').each(function() {
                    const detail = $(this).closest('.mpr-jobspec-edu').find('.edu-detail')
                        .val();
                    edu.push({
                        value: $(this).val(),
                        detail: detail || null
                    });
                });

                let formData = new FormData();
                formData.append('id', $('#mpr-jobspec-id').val());
                formData.append('department', $('#mpr-jobspec-dept').val());
                formData.append('section', $('#mpr-jobspec-section').val());
                formData.append('position', $('#mpr-jobspec-pos').val());
                formData.append('emplstat', $('#mpr-jobspec-emplstat').val());
                formData.append('sex', $('#mpr-jobspec-gender').val());
                formData.append('agerange', JSON.stringify([$('#mpr-jobspec-agemin').val(), $(
                    '#mpr-jobspec-agemax').val()]));
                formData.append('education', JSON.stringify(edu));
                formData.append('workexp', JSON.stringify($('.mpr-jobspec-workexp:checked').map((_,
                    el) => el.value).get()));
                formData.append('duties', $('#mpr-jobspec-duties').val());
                formData.append('techcompetencies', $('#mpr-jobspec-technical').val());
                formData.append('competencies', $('#mpr-jobspec-competenciesneeded').val());
                formData.append('computerskill', JSON.stringify($('.mpr-jobspec-compskill:checked').map(
                    (_, el) => el.value).get()));
                formData.append('otherskill', $('#mpr-jobspec-otherskill').val());
                formData.append('mpa', $('[name="mpr-jobspec-metaprog-a"]:checked').val() || '');
                formData.append('mpb', JSON.stringify([
                    $('[name="mpr-jobspec-metaprog-b1"]:checked').val() || '',
                    $('[name="mpr-jobspec-metaprog-b2"]:checked').val() || ''
                ]));
                formData.append('mpc', $('[name="mpr-jobspec-metaprog-c"]:checked').val() || '');
                formData.append('mpd', $('[name="mpr-jobspec-metaprog-d"]:checked').val() || '');
                formData.append('mpe', $('[name="mpr-jobspec-metaprog-e"]:checked').val() || '');
                formData.append('mpf', JSON.stringify([
                    $('[name="mpr-jobspec-metaprog-f1"]:checked').val() || '',
                    $('[name="mpr-jobspec-metaprog-f2"]:checked').val() || ''
                ]));
                formData.append('mpg', $('[name="mpr-jobspec-metaprog-g"]:checked').val() || '');
                formData.append('tapt', JSON.stringify($('.mpr-jobspec-tapt:checked').map((_, el) => el
                    .value).get()));
                formData.append('enneagram', JSON.stringify($('.mpr-jobspec-enneagram:checked').map((_,
                    el) => el.value).get()));
                formData.append('learnstyle', JSON.stringify($('.mpr-jobspec-learnstyle:checked').map((
                    _, el) => el.value).get()));
                formData.append('career', JSON.stringify($('.mpr-jobspec-career:checked').map((_, el) =>
                    el.value).get()));
                formData.append('motivation', JSON.stringify($('.mpr-jobspec-motivation:checked').map((
                    _, el) => el.value).get()));
                formData.append('personality', JSON.stringify($('.mpr-jobspec-personality:checked').map(
                    (_, el) => el.value).get()));
                formData.append('ravenl', JSON.stringify($('.mpr-jobspec-raven-low:checked').map((_,
                    el) => el.value).get()));
                formData.append('ravena', JSON.stringify($('.mpr-jobspec-raven-average:checked').map((_,
                    el) => el.value).get()));
                formData.append('ravenh', JSON.stringify($('.mpr-jobspec-raven-high:checked').map((_,
                    el) => el.value).get()));
                formData.append('leadership', $('#mpr-jobspec-leadership').val());
                formData.append('remarks', $('#mpr-jobspec-remarks').val());

                const ok = await postForm({
                    url: '/manpower/jobspec/save',
                    formData,
                    errSelector: '#mpr-jobspec-err',
                    submitBtn: $(this).find('[type="submit"]'),
                    successMsg: 'Saved',
                    onSuccess: () => $('#modal-mpr-jobspec').modal('hide')
                });

                if (!ok) {
                    $('#modal-mpr-jobspec').animate({
                        scrollTop: $('#mpr-jobspec-err').offset().top
                    }, 500);
                }
            });

            $('#modal-mpr-jobspec').on('show.bs.modal', async function(e) {
                try {

                    const pos = $(e.relatedTarget).data('pos');

                    $('#form-mpr-jobspec textarea').each(function() {
                        this.style.height = 'auto';
                        this.style.height = this.scrollHeight + 'px';
                    }).on('input.autoresize', function() {
                        this.style.height = 'auto';
                        this.style.height = this.scrollHeight + 'px';
                    });

                    $('#form-mpr-jobspec').find(
                        'textarea, select, input:not([type="checkbox"], [type="radio"])').val('');
                    $('#form-mpr-jobspec').find('[type="checkbox"], [type="radio"]').prop('checked',
                        false);

                    $('.selectpicker').selectpicker('refresh');

                    $('#form-mpr-jobspec fieldset').prop('disabled', true);

                    if (!pos) {
                        $('#form-mpr-jobspec fieldset').prop('disabled', false);
                        return;
                    }

                    const data = await get_spec(pos);

                    if (!data || !data['id']) {
                        $('#form-mpr-jobspec fieldset').prop('disabled', false);
                        return;
                    }

                    $('#mpr-jobspec-id').val(data['id']);
                    $('#mpr-jobspec-dept').val(data['department']);
                    $('#mpr-jobspec-section').val(data['section']);
                    $('#mpr-jobspec-pos').val(data['position']);
                    $('#mpr-jobspec-emplstat').val(data['emplstat']);
                    $('#mpr-jobspec-gender').val(data['sex']);

                    const agerange = data['agerange'] || [];
                    $('#mpr-jobspec-agemin').val(agerange[0] || '');
                    $('#mpr-jobspec-agemax').val(agerange[1] || '');

                    for (const i of (data['education'] || [])) {
                        const val = i.value ?? i[0];
                        const detail = i.detail ?? i[1];
                        let chk = $('.mpr-jobspec-edu input[value="' + (val || '') + '"]');
                        chk.prop('checked', true);
                        chk.closest('.mpr-jobspec-edu').find('.edu-detail').val(detail || '');
                    }

                    checkValues('input.mpr-jobspec-workexp', data['workexp']);

                    $('#mpr-jobspec-duties').val(data['duties']);
                    $('#mpr-jobspec-technical').val(data['techcompetencies']);
                    $('#mpr-jobspec-competenciesneeded').val(data['competencies']);

                    checkValues('input.mpr-jobspec-compskill', data['computerskill']);

                    $('#mpr-jobspec-otherskill').val(data['otherskill']);

                    const mpb = data['mpb'] || [];
                    const mpf = data['mpf'] || [];

                    $('input[name="mpr-jobspec-metaprog-a"][value="' + data['mpa'] + '"]').prop(
                        'checked', true);
                    $('input[name="mpr-jobspec-metaprog-b1"][value="' + mpb[0] + '"]').prop('checked',
                        true);
                    $('input[name="mpr-jobspec-metaprog-b2"][value="' + mpb[1] + '"]').prop('checked',
                        true);
                    $('input[name="mpr-jobspec-metaprog-c"][value="' + data['mpc'] + '"]').prop(
                        'checked', true);
                    $('input[name="mpr-jobspec-metaprog-d"][value="' + data['mpd'] + '"]').prop(
                        'checked', true);
                    $('input[name="mpr-jobspec-metaprog-e"][value="' + data['mpe'] + '"]').prop(
                        'checked', true);
                    $('input[name="mpr-jobspec-metaprog-f1"][value="' + mpf[0] + '"]').prop('checked',
                        true);
                    $('input[name="mpr-jobspec-metaprog-f2"][value="' + mpf[1] + '"]').prop('checked',
                        true);
                    $('input[name="mpr-jobspec-metaprog-g"][value="' + data['mpg'] + '"]').prop(
                        'checked', true);

                    checkValues('input.mpr-jobspec-tapt', data['tapt']);
                    checkValues('input.mpr-jobspec-enneagram', data['enneagram']);
                    checkValues('input.mpr-jobspec-learnstyle', data['learnstyle']);
                    checkValues('input.mpr-jobspec-career', data['career']);
                    checkValues('input.mpr-jobspec-motivation', data['motivation']);
                    checkValues('input.mpr-jobspec-personality', data['personality']);
                    checkValues('input.mpr-jobspec-raven-low', data['ravenl']);
                    checkValues('input.mpr-jobspec-raven-average', data['ravena']);
                    checkValues('input.mpr-jobspec-raven-high', data['ravenh']);

                    $('#mpr-jobspec-leadership').val(data['leadership']);
                    $('#mpr-jobspec-remarks').val(data['remarks']);

                    $('#form-mpr-jobspec fieldset').prop('disabled', false);

                    $('.selectpicker').selectpicker('refresh');

                } catch (error) {
                    console.error('Error fetching the data:', error);
                    $('#form-mpr-jobspec fieldset').prop('disabled', false);
                }
            });

            loadMPR('pending');
            loadCounts();
        })

        async function view_jobspec(pos) {
            try {

                if (!pos) return;

                const data = await get_spec(pos);

                if (!data || !data['id']) return;

                $('#mpr-view-jobspec-dept').text(data['department_name']);
                $('#mpr-view-jobspec-section').text(data['section_name']);
                $('#mpr-view-jobspec-pos').text(data['position_name']);
                $('#mpr-view-jobspec-emplstat').text(data['emplstat']);
                $('#mpr-view-jobspec-gender').text(data['sex']);
                $('#mpr-view-jobspec-age').text(data['agerange'] ? data['agerange'].join('-') : '');

                $('#mpr-view-jobspec-edu').empty();
                for (const i of (data['education'] || [])) {
                    const val = i.value ?? i[0];
                    const detail = i.detail ?? i[1];
                    const label = detail ? `${val}: ${detail}` : val;
                    $('#mpr-view-jobspec-edu').append(
                        $('<label class="col-form-label col-form-label-sm col-12">').text('- ' + label)
                    );
                }

                renderLabelList('#mpr-view-jobspec-workexp', data['workexp']);

                $('#mpr-view-jobspec-duties').text(data['duties']);
                $('#mpr-view-jobspec-technical').text(data['techcompetencies']);
                $('#mpr-view-jobspec-competenciesneeded').text(data['competencies']);

                renderLabelList('#mpr-view-jobspec-compskill', data['computerskill']);

                $('#mpr-view-jobspec-otherskill').text(data['otherskill']);

                const mpb = data['mpb'] || [];
                const mpf = data['mpf'] || [];

                $('#mpr-view-jobspec-metaprog-a').text('-' + (data['mpa'] || ''));
                $('#mpr-view-jobspec-metaprog-b1').text('-' + (mpb[0] || ''));
                $('#mpr-view-jobspec-metaprog-b2').text('-' + (mpb[1] || ''));
                $('#mpr-view-jobspec-metaprog-c').text('-' + (data['mpc'] || ''));
                $('#mpr-view-jobspec-metaprog-d').text('-' + (data['mpd'] || ''));
                $('#mpr-view-jobspec-metaprog-e').text('-' + (data['mpe'] || ''));
                $('#mpr-view-jobspec-metaprog-f1').text('-' + (mpf[0] || ''));
                $('#mpr-view-jobspec-metaprog-f2').text('-' + (mpf[1] || ''));
                $('#mpr-view-jobspec-metaprog-g').text('-' + (data['mpg'] || ''));

                renderLabelList('#mpr-view-jobspec-tapt', data['tapt'], 'col-md');
                renderLabelList('#mpr-view-jobspec-enneagram', data['enneagram'], 'col-md');
                renderLabelList('#mpr-view-jobspec-learnstyle', data['learnstyle'], 'col-md');
                renderLabelList('#mpr-view-jobspec-career', data['career'], 'col-md');
                renderLabelList('#mpr-view-jobspec-motivation', data['motivation'], 'col-md');
                renderLabelList('#mpr-view-jobspec-personality', data['personality'], 'col-md');
                renderLabelList('#mpr-view-jobspec-raven-low', data['ravenl']);
                renderLabelList('#mpr-view-jobspec-raven-average', data['ravena']);
                renderLabelList('#mpr-view-jobspec-raven-high', data['ravenh']);

                $('#mpr-view-jobspec-leadership').text(data['leadership']);
                $('#mpr-view-jobspec-remarks').text(data['remarks']);

                $('#modal-mpr-view-jobspec').modal('show')

            } catch (error) {
                console.error('Error fetching the data:', error);
            }
        }

        async function loadMPR(stat) {
            const $pane = $('#' + stat + '-tab-pane');
            $pane.html('<div class="mpr-empty"><i class="fa fa-spinner fa-spin"></i><div>Loading...</div></div>');
            try {
                const response = await fetch('/manpower/list/' + stat);

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const html = await response.text();

                $pane.html(html);

                const $table = $pane.find('table').first();

                if ($table.length === 0) {
                    // No table at all means the partial already rendered its
                    // own empty state — leave it as-is. Otherwise, fall back
                    // to a generic empty state.
                } else {
                    if ($.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().destroy();
                    }

                    const api = $table.DataTable({
                        scrollY: '55vh',
                        scrollCollapse: true,
                        lengthMenu: [50, 100, {
                            label: 'All',
                            value: -1
                        }],
                        ordering: false,
                        dom: 'rt', // hide DataTables' built-in search/length/info UI; ours replaces it
                        drawCallback: function(settings) {
                            const api = this.api();
                            const info = api.page.info();
                            $('#mpr-results-count').text(info.recordsDisplay + ' result' + (info
                                .recordsDisplay === 1 ? '' : 's'));
                            renderPager(api);
                        }
                    });
                    $('#mpr-page-length').val(String(api.page.len()));
                }
            } catch (error) {
                console.error('Error fetching the list:', error);
                $pane.html(
                    '<div class="mpr-empty"><i class="fa fa-exclamation-triangle"></i><div>Failed to load. Please try again.</div></div>'
                );
            }
        }

        /**
         * Optional: populate the per-tab count badges in the header strip.
         * Expects an endpoint returning JSON like:
         *   { draft: 3, pending: 7, approved: 12, update: 2, cancelled: 0, declined: 1 }
         * If that endpoint doesn't exist yet, this fails silently and the
         * badges just stay blank — wire up /manpower/counts (or similar)
         * on the backend to light them up.
         */
        async function loadCounts() {
            try {
                const response = await fetch('/manpower/counts');
                if (!response.ok) return;
                const counts = await response.json();
                Object.keys(counts).forEach(stat => {
                    $('.mpr-tab-count[data-count="' + stat + '"]').text(counts[stat]);
                });
            } catch (error) {
                // Endpoint not available yet — badges remain blank.
            }
        }

        async function get_spec(pos) {
            try {
                const response = await fetch('/manpower/jobspec/' + pos);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Error fetching the data:', error);
                return null;
            }
        }

        async function remove_mpr(id) {
            if (!confirm("Are you sure?")) return;

            let formData = new FormData();
            // Some backends prefer DELETE with no body; keeping id in case a query/body param is needed.
            await postForm({
                url: '/manpower/delete/' + id,
                formData,
                errSelector: '#mpr-err',
                successMsg: 'Removed',
                onSuccess: () => {},
                method: 'DELETE',
                alertOnError: true
            });
        }

        async function post_mpr(id) {
            if (!confirm("Post this request?")) return;

            let formData = new FormData();
            formData.append('id', id);
            formData.append('stat', 'pending');

            await postForm({
                url: '/manpower/stat',
                formData,
                errSelector: '#view-mpr-err',
                successMsg: 'Posted',
                onSuccess: () => $('#modal-view-mpr').modal('hide'),
                alertOnError: true
            });
        }

        async function approve(id) {
            if (!confirm("Are you sure?")) return;

            let formData = new FormData();
            formData.append('id', id);
            formData.append('stat', 'approved');

            await postForm({
                url: '/manpower/stat',
                formData,
                errSelector: '#mpr-err',
                successMsg: 'Approved',
                onSuccess: () => {},
                alertOnError: true
            });
        }

        async function approve_update(id) {
            if (!confirm("Are you sure?")) return;

            let formData = new FormData();
            await postForm({
                url: '/manpower/update/approve/' + id,
                formData,
                errSelector: '#mpr-err',
                successMsg: 'Approved',
                onSuccess: () => {},
                alertOnError: true
            });
        }

        async function decline_update(id) {
            if (!confirm("Are you sure?")) return;

            let formData = new FormData();
            await postForm({
                url: '/manpower/update/decline/' + id,
                formData,
                errSelector: '#mpr-err',
                successMsg: 'Declined',
                onSuccess: () => {},
                alertOnError: true
            });
        }

        function decline(id) {
            $('#decline-mpr-id').val(id);
            $('#decline-mpr-reason').val('');
            $('#modal-decline-mpr').modal('show');
        }
    </script>
@stop
