@extends('layouts.layout')

@section('content')
<style>
    .ais-wrap { max-width: 780px; }

    .ais-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 700;
        color: #5B6474;
        text-decoration: none;
    }

    .ais-back:hover { color: #1B4FB0; }

    .ais-head { margin: 14px 0 20px; }

    .ais-head h4 {
        font-weight: 800;
        color: #1F2430;
        letter-spacing: -.2px;
        margin: 0;
    }

    .ais-card {
        border: 1px solid #E7E9EE;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(31, 36, 48, .04);
        padding: 22px 26px;
    }

    .ais-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 4px 0 16px;
        font-weight: 700;
        font-size: 12px;
        color: #1F2430;
    }

    .ais-section-divider .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2F6FE4, #1B4FB0);
        box-shadow: 0 0 0 3px #E8F0FE;
    }

    .ais-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #F1F2F5;
    }

    .ais-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 24px;
    }

    .ais-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8A93A3;
        margin-bottom: 5px;
    }

    .ais-value {
        font-size: 14px;
        color: #1F2430;
    }

    .mpv-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .mpv-chip::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .mpv-chip-pending  { background: #E8F0FE; color: #1B4FB0; }
    .mpv-chip-approved { background: #E7F6EC; color: #1E9E4C; }
    .mpv-chip-rejected { background: #FCEBEB; color: #791F1F; }
</style>

<div class="container py-4 ais-wrap">
    <a href="{{ config('app.url') }}/recruitment/applicant-intake" class="ais-back">&larr; Back to Applicant Intake</a>

    <div class="ais-head">
        <h4>{{ $app->applicant_name }}</h4>
    </div>

    <div class="ais-card">
        <div class="ais-section-divider"><span class="dot"></span> Contact</div>
        <div class="ais-grid">
            <div>
                <div class="ais-label">Email</div>
                <div class="ais-value">{{ $app->app_email }}</div>
            </div>
            <div>
                <div class="ais-label">Contact</div>
                <div class="ais-value">{{ $app->app_mobile }}</div>
            </div>
        </div>

        <div class="ais-section-divider"><span class="dot"></span> Application</div>
        <div class="ais-grid">
            <div>
                <div class="ais-label">Position</div>
                <div class="ais-value">{{ $app->posting_title }}</div>
            </div>
            <div>
                <div class="ais-label">REQ / MR ID</div>
                <div class="ais-value">{{ $app->mr_no }}</div>
            </div>
            <div>
                <div class="ais-label">Date Applied</div>
                <div class="ais-value">{{ \Illuminate\Support\Carbon::parse($app->applied_at)->format('M d, Y g:i A') }}</div>
            </div>
            <div>
                <div class="ais-label">Status</div>
                <div>
                    @php
                        $statusClass = match ($app->status) {
                            'Pending' => 'mpv-chip-pending',
                            'Approved' => 'mpv-chip-approved',
                            'Rejected' => 'mpv-chip-rejected',
                            default => 'mpv-chip-pending',
                        };
                    @endphp
                    <span class="mpv-chip {{ $statusClass }}">{{ $app->status }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection