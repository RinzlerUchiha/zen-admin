@extends('layouts.layout')

@section('content')
<div class="container-fluid mt-5 pt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Job Postings</h4>
    </div>

    <div id="alert-box"></div>

    @forelse ($eligibleRequests as $request)
        <div class="card mb-3">
            <div class="card-header">
                Request #{{ $request->id }}
            </div>
            <div class="card-body p-0">
                @forelse ($request->positions as $position)
                    <div class="d-flex justify-content-between align-items-center border-bottom p-3">
                        <div>
                            <strong>Position #{{ $position->id }}</strong>
                            <div class="text-muted small">Not yet posted</div>
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
@endsection