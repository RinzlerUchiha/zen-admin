@extends('pages.applicant.profile')

{{--
    Assessment access & status (HireFlow 2.5).

    HR opens the applicant's assessments by issuing a one-time code, read out
    or handed over after the initial interview. The same code resumes an
    assessment that was interrupted (the applicant was away longer than the
    grace period, e.g. a power cut): the clock stopped where it was, and a code
    entered after that lets them continue with the time that was left.

    Results and scores are unchanged — they stay on each assessment's own tab.
--}}
@section('profile_content')
<div class="w-100">

    @if (session('assessment_code'))
        <div class="alert alert-success d-flex align-items-center gap-3" role="alert">
            <div class="fs-2 fw-bold font-monospace" style="letter-spacing:.2em">{{ session('assessment_code') }}</div>
            <div>
                <div class="fw-semibold">Give this code to the applicant.</div>
                <div class="small">It works once, in the browser they enter it in, until {{ session('assessment_code_expires') }}.
                    It will not be shown again — issue a new one if it is lost.</div>
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <h6 class="mb-0">Assessment access</h6>
        @can('applicant-assessments.issue-access')
            <form method="POST" action="{{ route('applicant.assessment-access.issue', ['id' => $applicant?->app_id]) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary"
                        onclick="return confirm('Issue a new access code? Any earlier unused code stops working.')">
                    Issue access code
                </button>
            </form>
        @endcan
        <span class="small text-muted">
            @php $access = $assessmentAccess ?? null; @endphp
            @if (!$access)
                No code issued yet.
            @elseif ($access->redeemed_at)
                Last code used {{ \Illuminate\Support\Carbon::parse($access->redeemed_at)->format('M j, g:i A') }}
                @if ($access->unlocked_until && \Illuminate\Support\Carbon::parse($access->unlocked_until)->isFuture())
                    — assessments open until {{ \Illuminate\Support\Carbon::parse($access->unlocked_until)->format('g:i A') }}.
                @else
                    — access has since closed.
                @endif
            @elseif ($access->revoked_at)
                Last code no longer works{{ $access->failed_attempts >= config('applicant_assessments.max_failures') ? ' (too many wrong entries)' : ' (replaced)' }}.
            @elseif (\Illuminate\Support\Carbon::parse($access->expires_at)->isPast())
                Last code expired unused.
            @else
                A code is waiting to be used (until {{ \Illuminate\Support\Carbon::parse($access->expires_at)->format('g:i A') }}).
            @endif
        </span>
    </div>

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>Assessment</th>
                <th>Status</th>
                <th>Started</th>
                <th>Ended</th>
                <th class="text-end">Time used</th>
            </tr>
        </thead>
        <tbody>
            @foreach (config('applicant_assessments.list') as $key => $def)
                @php $label = $def['label']; @endphp
                @php $attempt = ($assessmentAttempts ?? collect())->get($key); @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>
                        @if (!$attempt)
                            <span class="text-muted">Not started</span>
                        @else
                            @php $status = $attempt->current_status; @endphp
                            {{ config('applicant_assessments.statuses.' . $status, $status) }}
                            @if ($status === 'interrupted')
                                <span class="small text-muted">· {{ $attempt->minutes_left }} min left</span>
                            @elseif ($status === 'timed_out' && $attempt->status === 'active')
                                <span class="small text-muted">· the autosaved answers are submitted when the applicant next opens it</span>
                            @elseif ($status === 'timed_out' && !$attempt->result_saved)
                                <span class="small text-muted">· incomplete, no result recorded</span>
                            @endif
                        @endif
                    </td>
                    <td class="small">{{ $attempt?->started_at?->format('M j, g:i A') }}</td>
                    <td class="small">{{ $attempt?->ended_at?->format('M j, g:i A') }}</td>
                    <td class="small text-end">
                        @if ($attempt)
                            {{ intdiv($attempt->time_used_seconds, 60) }}:{{ str_pad($attempt->time_used_seconds % 60, 2, '0', STR_PAD_LEFT) }}
                            / {{ intdiv($attempt->duration_seconds, 60) }} min
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="small text-muted">
        Assessments finished before this tracking existed show as “Not started” here; their results are still on their own tabs.
    </p>
</div>
@stop
