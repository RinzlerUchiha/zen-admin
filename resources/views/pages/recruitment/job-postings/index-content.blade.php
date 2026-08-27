<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Job Postings</h4>
    </div>

    <div id="alert-box"></div>

    @forelse ($eligibleRequests as $request)
        <div class="card mb-3">
            <div class="card-body p-0">
                @forelse ($request->positions as $position)
                    <div class="d-flex justify-content-between align-items-center border-bottom p-3">
                        <div>
                            <strong>{{ $position->positionTitle() }}</strong>
                            <div class="text-muted small">
                                {{ ucfirst($position->type) }} · Headcount {{ $position->headcount }}
                                ({{ $position->filled }} filled)
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary btn-open-create-posting"
                            data-position-id="{{ $position->id }}">
                            Create Posting
                        </button>
                    </div>
                @empty
                    <div class="p-3 text-muted">No eligible positions on this request.</div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="alert alert-secondary">No approved requests with eligible positions found.</div>
    @endforelse
</div>

@push('styles')
    <style>
        .posting-title-input {
            width: 220px;
        }
    </style>
@endpush

<div class="modal fade" id="modal-create-posting" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Job Posting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="create-posting-loading" class="text-center text-muted py-4">Loading draft…</div>
                <div id="create-posting-form" class="d-none">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Posting Title</label>
                        <input type="text" id="cp-title" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Description</label>
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

<script>
    $(function() {
        let currentPositionId = null;

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
    });
</script>
<hr class="my-4">

<h5>Existing Postings</h5>

@if ($postings->isEmpty())
    <p class="text-muted">No postings created yet.</p>
@else
    <table class="table table-sm table-hover">
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
                <tr>
                    <td>{{ $posting->posting_title }}</td>
                    <td>
                        <span
                            class="badge
                            @switch($posting->status)
                                @case('Draft') bg-secondary @break
                                @case('Published') bg-success @break
                                @case('Closed') bg-dark @break
                                @default bg-light text-dark
                            @endswitch">
                            {{ $posting->status }}
                        </span>
                    </td>
                    <td>{{ $posting->posted_at?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $posting->closed_at?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $posting->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('recruitment.job-postings.show', $posting->id) }}"
                            class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
