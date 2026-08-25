
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
                                {{ ucfirst($position->type) }} · Headcount {{ $position->headcount }} ({{ $position->filled }} filled)
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm posting-title-input" placeholder="Posting title">
                            <button type="button" class="btn btn-sm btn-primary btn-create-posting" data-position-id="{{ $position->id }}">
                                Create Posting
                            </button>
                        </div>
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
    .posting-title-input { width: 220px; }
</style>
@endpush

<script>
$(function () {
    $('.btn-create-posting').on('click', async function () {
        const $btn = $(this);
        const positionId = $btn.data('position-id');
        const title = $btn.closest('.d-flex.gap-2').find('.posting-title-input').val();

        if (!title) {
            $('#alert-box').html('<div class="alert alert-danger">Posting title is required.</div>');
            return;
        }

        try {
            await POST('recruitment/job-postings', {
                request_position_id: positionId,
                posting_title: title
            });
            location.reload();
        } catch (err) {
            $('#alert-box').html('<div class="alert alert-danger">Failed to create posting.</div>');
        }
    });
});
</script>
<hr class="my-4">

<h5>Existing Postings</h5>

@if($postings->isEmpty())
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
            @foreach($postings as $posting)
                <tr>
                    <td>{{ $posting->posting_title }}</td>
                    <td>
                        <span class="badge
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