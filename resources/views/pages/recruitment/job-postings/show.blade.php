@extends('layouts.layout')

@section('content')
<div class="container-fluid mt-5 pt-3">
    <a href="{{ route('recruitment.job-postings.index') }}" class="text-decoration-none small">&larr; Back to Job Postings</a>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h4 class="mb-0">{{ $jobPosting->posting_title }}</h4>
        <span class="badge
            @if($jobPosting->status === 'Draft') bg-secondary
            @elseif($jobPosting->status === 'Published') bg-success
            @else bg-dark
            @endif">
            {{ $jobPosting->status }}
        </span>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="text-muted small">Position</h6>
            <p>{{ $jobPosting->hireflowPosition?->positionTitle() ?? '—' }}</p>

            <h6 class="text-muted small">Request</h6>
            <p>{{ $jobPosting->hireflowPosition?->request?->mr_no ?? '—' }}</p>

            <h6 class="text-muted small">Description</h6>
            <p>{{ $jobPosting->posting_description ?: 'No description provided.' }}</p>

            <h6 class="text-muted small">Created By</h6>
            <p>{{ $jobPosting->created_by ?? '—' }}</p>

            <h6 class="text-muted small">Posted At</h6>
            <p>{{ $jobPosting->posted_at?->format('M d, Y h:i A') ?? 'Not yet published' }}</p>

            <h6 class="text-muted small">Closed At</h6>
            <p>{{ $jobPosting->closed_at?->format('M d, Y h:i A') ?? 'Not closed' }}</p>
        </div>
    </div>

    <div id="alert-box"></div>

    <div class="d-flex gap-2">
        @if ($jobPosting->status === 'Draft')
            <button type="button" class="btn btn-success btn-status" data-status="Published">Publish</button>
        @endif
        @if ($jobPosting->status === 'Published')
            <button type="button" class="btn btn-dark btn-status" data-status="Closed">Close</button>
        @endif
    </div>
</div>

<script>
$(function () {
    $('.btn-status').on('click', async function () {
        const status = $(this).data('status');
        try {
            await fetch(`${BASE}/recruitment/job-postings/{{ $jobPosting->id }}/status`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ status })
            });
            location.reload();
        } catch (err) {
            $('#alert-box').html('<div class="alert alert-danger">Failed to update status.</div>');
        }
    });
});
</script>
@endsection