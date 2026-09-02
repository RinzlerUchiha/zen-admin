@extends('layouts.layout')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <a href="{{ config('app.url') }}/recruitment/applicant-intake" class="text-decoration-none small">&larr; Back to Applicant Intake</a>

    <div class="card mt-3">
        <div class="card-body">
            <h4 class="mb-3">{{ $app->applicant_name }}</h4>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="text-muted small">Email</div>
                    <div>{{ $app->app_email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Contact</div>
                    <div>{{ $app->app_mobile }}</div>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="text-muted small">Position</div>
                    <div>{{ $app->posting_title }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">REQ / MR ID</div>
                    <div>{{ $app->mr_no }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="text-muted small">Date Applied</div>
                    <div>{{ \Illuminate\Support\Carbon::parse($app->applied_at)->format('M d, Y g:i A') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Status</div>
                    <div><span class="mpv-chip mpv-chip-pending">{{ $app->status }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection