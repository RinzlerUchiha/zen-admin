@extends('layouts.layout')

@section('content')
<style>
    .jps-wrap { max-width: 780px; }

    .jps-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 700;
        color: #5B6474;
        text-decoration: none;
    }

    .jps-back:hover { color: #1B4FB0; }

    .jps-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin: 14px 0 20px;
    }

    .jps-head h4 {
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.2px;
        margin: 0;
    }

    .jps-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .jps-status-chip::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .jps-status-draft     { background: #F1F2F5; color: #5B6474; }
    .jps-status-published { background: #E7F6EC; color: #1E9E4C; }
    .jps-status-closed    { background: #EAEBEF; color: #2B303B; }

    .jps-card {
        border: 1px solid #E7E9EE;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(31, 36, 48, .04);
        padding: 22px 26px;
        margin-bottom: 18px;
    }

    .jps-field { margin-bottom: 18px; }
    .jps-field:last-child { margin-bottom: 0; }

    .jps-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-bottom: 5px;
    }

    .jps-value {
        font-size: 14px;
        color: #1F2430;
    }

    .jps-value.mono {
        white-space: pre-wrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12.5px;
        background: #F5F6F9;
        border: 1px solid #E7E9EE;
        border-radius: 10px;
        padding: 12px 14px;
        max-height: 320px;
        overflow-y: auto;
    }

    .jps-actions { display: flex; gap: 10px; }

    .jps-btn {
        border: none;
        border-radius: 9px;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 20px;
        transition: filter .15s ease, transform .15s ease;
    }

    .jps-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }

    .jps-btn-publish {
        background: linear-gradient(135deg, #34C471, #1E9E4C);
        color: #fff;
        box-shadow: 0 2px 8px rgba(30, 158, 76, .25);
    }

    .jps-btn-close {
        background: linear-gradient(135deg, #3A4152, #1F2430);
        color: #fff;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .25);
    }

    #alert-box .alert { border-radius: 10px; border: none; font-size: 13px; }
</style>

<div class="container-fluid mt-5 pt-3 jps-wrap">
    <a href="{{ route('recruitment.job-postings.index') }}" class="jps-back">&larr; Back to Job Postings</a>

    <div class="jps-head">
        <h4>{{ $jobPosting->posting_title }}</h4>
        @php
            $statusClass = match ($jobPosting->status) {
                'Draft' => 'jps-status-draft',
                'Published' => 'jps-status-published',
                'Closed' => 'jps-status-closed',
                default => 'jps-status-draft',
            };
        @endphp
        <span class="jps-status-chip {{ $statusClass }}">{{ $jobPosting->status }}</span>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="jps-card">
        <div class="jps-field">
            <div class="jps-label">Position</div>
            <div class="jps-value">{{ $jobPosting->hireflowPosition?->positionTitle() ?? '—' }}</div>
        </div>

        <div class="jps-field">
            <div class="jps-label">Request</div>
            <div class="jps-value">{{ $jobPosting->hireflowPosition?->request?->mr_no ?? '—' }}</div>
        </div>

        <div class="jps-field">
            <div class="jps-label">Description</div>
            <div class="jps-value mono">{{ $jobPosting->posting_description ?: 'No description provided.' }}</div>
        </div>

        <div class="jps-field">
            <div class="jps-label">Created By</div>
            <div class="jps-value">{{ $jobPosting->created_by ?? '—' }}</div>
        </div>

        <div class="jps-field">
            <div class="jps-label">Posted At</div>
            <div class="jps-value">{{ $jobPosting->posted_at?->format('M d, Y h:i A') ?? 'Not yet published' }}</div>
        </div>

        <div class="jps-field">
            <div class="jps-label">Closed At</div>
            <div class="jps-value">{{ $jobPosting->closed_at?->format('M d, Y h:i A') ?? 'Not closed' }}</div>
        </div>
    </div>

    <div id="alert-box"></div>

    <div class="jps-actions">
        @if ($jobPosting->status === 'Draft')
            <button type="button" class="jps-btn jps-btn-publish btn-status" data-status="Published">Publish</button>
        @endif
        @if ($jobPosting->status === 'Published')
            <button type="button" class="jps-btn jps-btn-close btn-status" data-status="Closed">Close</button>
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