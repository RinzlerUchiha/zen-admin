<style>
    .jp-page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .jp-page-head h5 {
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.2px;
        margin: 0;
    }

    /* ===== Eligible positions — capsule layout ===== */
    .jp-eligible-card {
        border: 1px solid #E7E9EE;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(31, 36, 48, .04);
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
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        background: #FAFBFC;
        padding: 12px 14px;
    }

    .jp-mr-label {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-bottom: 8px;
    }

    .jp-mr-label .mr-no {
        color: #1B4FB0;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: none;
        font-size: 12px;
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
        border: 1px solid #E7E9EE;
        border-radius: 999px;
        background: #FAFBFC;
        padding: 4px 4px 4px 12px;
        width: 100%;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .jp-capsule:hover {
        border-color: #C7D8F7;
        box-shadow: 0 1px 6px rgba(31, 36, 48, .06);
    }

    .jp-capsule-text {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
        font-size: 11.5px;
        color: #1F2430;
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
        color: #C7CBD3;
        margin: 0 4px;
    }

    .jp-capsule-meta .jp-type-inline {
        font-weight: 700;
        width: 78px;
        display: inline-block;
    }

    .jp-capsule-meta .jp-type-additional {
        color: #1B4FB0;
    }

    .jp-capsule-meta .jp-type-replacement {
        color: #6A4FE0;
    }

    .jp-capsule-meta .headcount {
        color: #8A93A3;
    }

    .jp-btn-create-mini {
        border: none;
        border-radius: 999px;
        background: linear-gradient(135deg, #2F6FE4, #1B4FB0);
        color: #fff;
        font-weight: 700;
        font-size: 10.5px;
        padding: 5px 12px;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(27, 79, 176, .22);
        transition: filter .15s ease, transform .15s ease;
    }

    .jp-btn-create-mini:hover {
        filter: brightness(1.08);
        transform: translateY(-1px);
    }

    .jp-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #B0B6C0;
        background: #fff;
        border: 1px dashed #E7E9EE;
        border-radius: 14px;
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
        font-size: 11px;
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
        background: #F1F2F5;
        color: #5B6474;
    }

    .jp-status-published {
        background: #E7F6EC;
        color: #1E9E4C;
    }

    .jp-status-closed {
        background: #EAEBEF;
        color: #2B303B;
    }

    /* ===== Section divider ===== */
    .jp-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0 14px;
        font-weight: 700;
        font-size: 13px;
        color: #1F2430;
    }

    .jp-section-divider .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2F6FE4, #1B4FB0);
        box-shadow: 0 0 0 3px #E8F0FE;
    }

    .jp-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #F1F2F5;
    }

    .jp-eligible-card::-webkit-scrollbar {
        width: 8px;
    }

    .jp-eligible-card::-webkit-scrollbar-track {
        background: transparent;
    }

    .jp-eligible-card::-webkit-scrollbar-thumb {
        background: #E1E4EA;
        border-radius: 8px;
    }

    .jp-eligible-card::-webkit-scrollbar-thumb:hover {
        background: #C7CBD3;
    }

    /* ===== Existing postings table ===== */
    .jp-table-card {
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
        background: #fff;
    }

    table.jp-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        font-size: 13px;
    }

    table.jp-list-table thead th {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #8A93A3;
        padding: 12px 16px;
        background: #F5F6F9;
        border-bottom: 1px solid #E7E9EE;
        text-align: left;
        white-space: nowrap;
    }

    table.jp-list-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #F1F2F5;
        vertical-align: middle;
        color: #1F2430;
    }

    table.jp-list-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.jp-list-table tbody tr.jp-row-link {
        cursor: pointer;
        transition: background .15s ease;
    }

    table.jp-list-table tbody tr.jp-row-link:hover {
        background: #FAFBFF;
    }

    .jp-row-chevron {
        color: #C7CBD3;
        font-size: 11px;
        transition: color .15s ease, transform .15s ease;
    }

    table.jp-list-table tbody tr.jp-row-link:hover .jp-row-chevron {
        color: #1B4FB0;
        transform: translateX(3px);
    }

    /* ===== Create Posting modal polish ===== */
    #modal-create-posting .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 12px 32px rgba(31, 36, 48, .16);
        overflow: hidden;
    }

    #modal-create-posting .modal-header {
        border-bottom: none;
        padding: 22px 26px 4px;
    }

    #modal-create-posting .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.2px;
    }

    #modal-create-posting .modal-body {
        padding: 14px 26px 24px;
    }

    #modal-create-posting .modal-footer {
        border-top: 1px solid #F1F2F5;
        background: #FAFBFC;
        padding: 14px 26px;
    }

    #modal-create-posting .form-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
    }

    #modal-create-posting .form-control {
        border-radius: 9px;
        border: 1px solid #E7E9EE;
    }

    #modal-create-posting .form-control:focus {
        border-color: #9FB8ED;
        box-shadow: 0 0 0 3px #E8F0FE;
    }

    #alert-box .alert {
        border-radius: 10px;
        border: none;
        font-size: 13px;
    }

    /* ===== Slide-over panel ===== */
    .jp-panel-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(31, 36, 48, .35);
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
        background: #fff;
        box-shadow: -12px 0 32px rgba(31, 36, 48, .16);
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
        border-bottom: 1px solid #F1F2F5;
    }

    .jp-panel-head h6 {
        font-weight: 800;
        font-size: 16px;
        color: #1F2430;
        margin: 0 0 8px;
    }

    .jp-panel-close {
        border: none;
        background: #F1F2F5;
        color: #5B6474;
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
        background: #E8F0FE;
        color: #1B4FB0;
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
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-bottom: 5px;
    }

    .jp-panel-label i {
        font-size: 11px;
        color: #C7CBD3;
    }

    .jp-panel-value {
        font-size: 13.5px;
        color: #1F2430;
    }

    .jp-panel-value.mono {
        white-space: pre-wrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
        line-height: 1.6;
        background: #F5F6F9;
        border: 1px solid #E7E9EE;
        border-radius: 10px;
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
        background: #D7DBE3;
        border-radius: 6px;
    }

    .jp-panel-desc-textarea {
        width: 100%;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
        line-height: 1.6;
        color: #1F2430;
        background: #F5F6F9;
        border: 1px solid #E7E9EE;
        border-radius: 10px;
        padding: 14px 16px;
        resize: vertical;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }

    .jp-panel-desc-textarea:focus {
        outline: none;
        background: #fff;
        border-color: #9FB8ED;
        box-shadow: 0 0 0 3px #E8F0FE;
    }

    .jp-panel-desc-textarea::-webkit-scrollbar {
        width: 6px;
    }

    .jp-panel-desc-textarea::-webkit-scrollbar-track {
        background: transparent;
    }

    .jp-panel-desc-textarea::-webkit-scrollbar-thumb {
        background: #D7DBE3;
        border-radius: 6px;
    }

    .jp-desc-hint {
        font-size: 11px;
        color: #B0B6C0;
        margin-top: 6px;
    }

    .jp-gen-btn {
        margin-left: auto;
        border: 1px solid #C7D8F7;
        background: #EAF1FE;
        color: #14458F;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: none;
        padding: 3px 10px;
        transition: background .15s ease;
    }

    .jp-gen-btn:hover { background: #D8E6FD; }

    .jp-gen-btn:disabled { opacity: .6; }

    .jp-gen-btn i { margin-right: 4px; font-size: 9px; }

    .jp-gen-btn + .jp-field-tag { margin-left: 6px; }

    .jp-field-tag {
        margin-left: auto;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .04em;
        background: #F1F2F5;
        color: #8A93A3;
        border-radius: 20px;
        padding: 2px 8px;
    }

    .jp-field-tag-live {
        background: #E7F6EC;
        color: #1E9E4C;
    }

    .jp-sync-state {
        font-size: 11px;
        line-height: 1.5;
        border-radius: 8px;
        padding: 7px 11px;
        margin-bottom: 7px;
    }

    .jp-sync-auto {
        background: #EAF1FE;
        color: #14458F;
    }

    .jp-sync-custom {
        background: #F5F6F9;
        color: #5B6474;
    }

    .jp-sync-state i { margin-right: 5px; }

    .jp-public-warning {
        margin-top: 8px;
        font-size: 11.5px;
        line-height: 1.5;
        color: #9A5B12;
        background: #FFF6E6;
        border: 1px solid #FFE2B0;
        border-radius: 9px;
        padding: 9px 12px;
    }

    .jp-public-warning i {
        margin-right: 5px;
    }

    .jp-panel-footer {
        padding: 16px 24px;
        border-top: 1px solid #F1F2F5;
        background: #FAFBFC;
        display: flex;
        gap: 10px;
        box-shadow: 0 -4px 12px rgba(31, 36, 48, .05);
        flex-shrink: 0;
    }

    .jp-panel-btn {
        flex: 1;
        border: none;
        border-radius: 9px;
        font-weight: 700;
        font-size: 13px;
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
        outline: 2px solid #1B6BE0;
        outline-offset: -2px;
    }

    .jp-panel-btn-publish {
        background: linear-gradient(135deg, #34C471, #1E9E4C);
        color: #fff;
        box-shadow: 0 2px 8px rgba(30, 158, 76, .25);
    }

    /* "Close Posting" — ends the posting. NOT the same as the X that
    dismisses the panel (.jp-panel-close). Kept visually distinct. */
 .jp-panel-btn-close {
     background: linear-gradient(135deg, #E0693F, #C7472B);
     color: #fff;
     box-shadow: 0 2px 8px rgba(199, 71, 43, .25);
 }

 .jp-panel-btn-save {
     background: #fff;
     color: #1B4FB0;
     border: 1px solid #C3D3F2;
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
        color: #B0B6C0;
        padding: 40px 20px;
    }
</style>

<div class="container-fluid">
    <div class="jp-page-head">
        <h5>Job Postings</h5>
    </div>

    <div id="alert-box"></div>

    @forelse ($eligibleRequests as $request)
        @if ($loop->first)
            <div class="jp-eligible-card">
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
                    <th>Title</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Closed</th>
                    <th>Created</th>
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
                    <tr class="jp-row-link" tabindex="0" data-posting-id="{{ $posting->id }}">
                        <td>{{ $posting->posting_title }}</td>
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
                        <textarea id="cp-description" class="form-control" rows="8" style="font-family: monospace; font-size: 12.5px;"></textarea>
                        <div class="form-text">Pre-filled from this position's Job Specification. HR reference only —
                            never shown to applicants.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Public Ad <span class="jp-field-tag jp-field-tag-live">Careers
                                page</span></label>
                        <textarea id="cp-public" class="form-control" rows="14" style="font-size: 13px;"></textarea>
                        <div class="form-text">Auto-composed from the Job Specification. This is what applicants read —
                            edit the wording freely before saving.</div>
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
            <button type="button" class="jp-panel-close" id="jp-panel-expand-btn" title="Expand panel">
                <i class="fa fa-expand-alt"></i>
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
                <div class="jp-panel-value" id="jpp-position">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label"><i class="fa fa-file-alt"></i> Request</div>
                <div class="jp-panel-value" id="jpp-mrno">—</div>
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
                <div class="jp-public-warning d-none" id="jpp-public-warning">
                    <i class="fa fa-exclamation-triangle"></i> No public ad written. The careers page will fall back
                    to the internal jobspec, which exposes age, sex and headcount publicly.
                </div>
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
                public_description: publicAd
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
            $('#jpp-desc-hint').toggle(editable);
        }

        // ===== Expand / shrink panel (resets every time the panel closes) =====
        function setPanelWide(wide) {
            $('#jp-panel').toggleClass('jp-panel-wide', wide);
            $('#jp-panel-expand-btn')
                .attr('title', wide ? 'Shrink panel' : 'Expand panel')
                .find('i').attr('class', wide ? 'fa fa-compress-alt' : 'fa fa-expand-alt');
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
            const $btn = $(this);
            const current = $('#jpp-public-input').val().trim();

            if (current && !confirm('Replace the current public ad with a freshly composed one? Your edits will be lost.')) {
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Composing…');

            try {
                const data = await GET('recruitment/job-postings/' + currentPostingId + '/suggest-ad');
                $('#jpp-public-input').val(data.public_description || '');
                $('#jpp-public-warning').addClass('d-none');
                showAlert('info', 'Ad composed from the Job Specification. Review it, then click Save Description.');
            } catch (err) {
                console.error(err);
                showAlert('danger', 'Could not compose an ad for this posting.');
            } finally {
                $btn.prop('disabled', false).html('<i class="fa fa-wand-magic-sparkles"></i> Generate');
            }
        });

        // ===== Save description only (no status change) =====
        $(document).on('click', '#jp-save-desc-btn', async function() {
            clearAlert();
            const $btn = $(this);
            const posting_description = $('#jpp-description-input').val();
            const public_description = $('#jpp-public-input').val();

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
                    body: JSON.stringify({ posting_description, public_description })
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
                $('#jpp-status').text(data.status).attr('class', 'jp-status-chip ' + (STATUS_CLASS[data
                    .status] || 'jp-status-draft'));
                $('#jpp-position').text(data.position_title);
                $('#jpp-mrno').text(data.mr_no);
                $('#jpp-description-input').val(data.posting_description || '');
                $('#jpp-public-input').val(data.public_description || '');
                $('#jpp-public-warning').toggleClass('d-none', !!(data.public_description || '').trim());
                renderSyncState(data.ad_is_custom);
                $('#jpp-createdby').text(data.created_by ?? '—');
                $('#jpp-postedat').text(data.posted_at ?? 'Not yet published');
                $('#jpp-closedat').text(data.closed_at ?? 'Not closed');

                renderFooter(data.status);

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
                !confirm('Close this job posting? It will stop accepting applicants. This does not close the panel.')) {
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
