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
        .jp-eligible-grid { grid-template-columns: 1fr; }
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

    .jp-capsule-meta .sep { color: #C7CBD3; margin: 0 4px; }

    .jp-capsule-meta .jp-type-inline {
        font-weight: 700;
        width: 78px;
        display: inline-block;
    }

    .jp-capsule-meta .jp-type-additional { color: #1B4FB0; }
    .jp-capsule-meta .jp-type-replacement { color: #6A4FE0; }

    .jp-capsule-meta .headcount { color: #8A93A3; }

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

    .jp-btn-create-mini:hover { filter: brightness(1.08); transform: translateY(-1px); }

    .jp-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #B0B6C0;
        background: #fff;
        border: 1px dashed #E7E9EE;
        border-radius: 14px;
    }

    .jp-empty-state i { font-size: 26px; margin-bottom: 8px; display: block; }

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

    .jp-status-draft     { background: #F1F2F5; color: #5B6474; }
    .jp-status-published { background: #E7F6EC; color: #1E9E4C; }
    .jp-status-closed    { background: #EAEBEF; color: #2B303B; }

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

    table.jp-list-table tbody tr:last-child td { border-bottom: none; }

    table.jp-list-table tbody tr.jp-row-link {
        cursor: pointer;
        transition: background .15s ease;
    }

    table.jp-list-table tbody tr.jp-row-link:hover { background: #FAFBFF; }

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

    #modal-create-posting .modal-header { border-bottom: none; padding: 22px 26px 4px; }
    #modal-create-posting .modal-title { font-size: 18px; font-weight: 800; color: #1F2430; letter-spacing: -.2px; }
    #modal-create-posting .modal-body { padding: 14px 26px 24px; }
    #modal-create-posting .modal-footer { border-top: 1px solid #F1F2F5; background: #FAFBFC; padding: 14px 26px; }

    #modal-create-posting .form-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
    }

    #modal-create-posting .form-control { border-radius: 9px; border: 1px solid #E7E9EE; }
    #modal-create-posting .form-control:focus { border-color: #9FB8ED; box-shadow: 0 0 0 3px #E8F0FE; }

    #alert-box .alert { border-radius: 10px; border: none; font-size: 13px; }

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
        transition: transform .25s ease;
        display: flex;
        flex-direction: column;
    }

    .jp-panel.open { transform: translateX(0); }

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

    .jp-panel-close:hover { background: #E8F0FE; color: #1B4FB0; }

    .jp-panel-body {
        padding: 20px 24px;
        overflow-y: auto;
        flex: 1;
    }

    .jp-panel-field { margin-bottom: 18px; }
    .jp-panel-field:last-child { margin-bottom: 0; }

    .jp-panel-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-bottom: 5px;
    }

    .jp-panel-value { font-size: 13.5px; color: #1F2430; }

    .jp-panel-value.mono {
        white-space: pre-wrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
        background: #F5F6F9;
        border: 1px solid #E7E9EE;
        border-radius: 10px;
        padding: 12px 14px;
        max-height: 280px;
        overflow-y: auto;
    }

    .jp-panel-footer {
        padding: 16px 24px;
        border-top: 1px solid #F1F2F5;
        background: #FAFBFC;
        display: flex;
        gap: 10px;
    }

    .jp-panel-btn {
        border: none;
        border-radius: 9px;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 18px;
        transition: filter .15s ease, transform .15s ease;
    }

    .jp-panel-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }

    .jp-panel-btn-publish {
        background: linear-gradient(135deg, #34C471, #1E9E4C);
        color: #fff;
        box-shadow: 0 2px 8px rgba(30, 158, 76, .25);
    }

    .jp-panel-btn-close {
        background: linear-gradient(135deg, #3A4152, #1F2430);
        color: #fff;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .25);
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
                                <span class="headcount">{{ $position->headcount }} ({{ $position->filled }} filled)</span>
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
                        <tr class="jp-row-link" data-posting-id="{{ $posting->id }}">
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
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea id="cp-description" class="form-control" rows="14" style="font-family: monospace; font-size: 12.5px;"></textarea>
                        <div class="form-text">Pre-filled from this position's Job Specification. Review and edit before
                            saving.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cp-save-btn">Save as Draft</button>
            </div>
        </div>
    </div>
</div>

<!-- Slide-over panel: posting details -->
<div class="jp-panel-backdrop" id="jp-panel-backdrop"></div>
<div class="jp-panel" id="jp-panel">
    <div class="jp-panel-head">
        <div>
            <h6 id="jpp-title">—</h6>
            <span id="jpp-status" class="jp-status-chip jp-status-draft">—</span>
        </div>
        <button type="button" class="jp-panel-close" id="jp-panel-close-btn"><i class="fa fa-times"></i></button>
    </div>
    <div class="jp-panel-body">
        <div id="jp-panel-loading" class="jp-panel-loading">Loading…</div>
        <div id="jp-panel-content" class="d-none">
            <div class="jp-panel-field">
                <div class="jp-panel-label">Position</div>
                <div class="jp-panel-value" id="jpp-position">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label">Request</div>
                <div class="jp-panel-value" id="jpp-mrno">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label">Description</div>
                <div class="jp-panel-value mono" id="jpp-description">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label">Created By</div>
                <div class="jp-panel-value" id="jpp-createdby">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label">Posted At</div>
                <div class="jp-panel-value" id="jpp-postedat">—</div>
            </div>
            <div class="jp-panel-field">
                <div class="jp-panel-label">Closed At</div>
                <div class="jp-panel-value" id="jpp-closedat">—</div>
            </div>
        </div>
    </div>
    <div class="jp-panel-footer" id="jp-panel-footer"></div>
</div>

<script>
    $(function() {
        let currentPositionId = null;
        let currentPostingId = null;

        // ===== Create Posting modal (unchanged behavior) =====
        $('.btn-open-create-posting').on('click', async function() {
            currentPositionId = $(this).data('position-id');

            $('#create-posting-loading').removeClass('d-none');
            $('#create-posting-form').addClass('d-none');
            $('#modal-create-posting').modal('show');

            try {
                const draft = await GET('recruitment/job-postings/draft/' + currentPositionId);
                $('#cp-title').val(draft.title);
                $('#cp-description').val(draft.description);
                $('#create-posting-loading').addClass('d-none');
                $('#create-posting-form').removeClass('d-none');
            } catch (err) {
                $('#alert-box').html('<div class="alert alert-danger">Failed to load draft.</div>');
                $('#modal-create-posting').modal('hide');
            }
        });

        $('#cp-save-btn').on('click', async function() {
            const title = $('#cp-title').val();
            const description = $('#cp-description').val();

            if (!title) {
                $('#alert-box').html(
                    '<div class="alert alert-danger">Posting title is required.</div>');
                return;
            }

            try {
                await POST('recruitment/job-postings', {
                    request_position_id: currentPositionId,
                    posting_title: title,
                    posting_description: description
                });
                location.reload();
            } catch (err) {
                $('#alert-box').html(
                    '<div class="alert alert-danger">Failed to create posting.</div>');
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
        }

        function closePanel() {
            $('#jp-panel-backdrop').removeClass('open');
            $('#jp-panel').removeClass('open');
        }

        function renderFooter(status) {
            let html = '';
            if (status === 'Draft') {
                html = '<button type="button" class="jp-panel-btn jp-panel-btn-publish jp-panel-status-btn" data-status="Published">Publish</button>';
            } else if (status === 'Published') {
                html = '<button type="button" class="jp-panel-btn jp-panel-btn-close jp-panel-status-btn" data-status="Closed">Close</button>';
            }
            $('#jp-panel-footer').html(html);
        }

        async function loadPosting(id) {
            currentPostingId = id;
            $('#jp-panel-loading').removeClass('d-none');
            $('#jp-panel-content').addClass('d-none');
            $('#jp-panel-footer').html('');
            openPanel();

            try {
                const data = await GET('recruitment/job-postings/' + id + '/json');

                $('#jpp-title').text(data.posting_title);
                $('#jpp-status').text(data.status).attr('class', 'jp-status-chip ' + (STATUS_CLASS[data.status] || 'jp-status-draft'));
                $('#jpp-position').text(data.position_title);
                $('#jpp-mrno').text(data.mr_no);
                $('#jpp-description').text(data.posting_description || 'No description provided.');
                $('#jpp-createdby').text(data.created_by ?? '—');
                $('#jpp-postedat').text(data.posted_at ?? 'Not yet published');
                $('#jpp-closedat').text(data.closed_at ?? 'Not closed');

                renderFooter(data.status);

                $('#jp-panel-loading').addClass('d-none');
                $('#jp-panel-content').removeClass('d-none');
            } catch (err) {
                $('#alert-box').html('<div class="alert alert-danger">Failed to load posting.</div>');
                closePanel();
            }
        }

        $(document).on('click', '.jp-row-link', function() {
            const id = $(this).data('posting-id');
            loadPosting(id);
        });

        $('#jp-panel-close-btn, #jp-panel-backdrop').on('click', closePanel);

        $(document).on('click', '.jp-panel-status-btn', async function() {
            const status = $(this).data('status');
            try {
                await fetch(`${BASE}/recruitment/job-postings/${currentPostingId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ status })
                });
                loadPosting(currentPostingId);
                location.reload();
            } catch (err) {
                $('#alert-box').html('<div class="alert alert-danger">Failed to update status.</div>');
            }
        });
    });
</script>