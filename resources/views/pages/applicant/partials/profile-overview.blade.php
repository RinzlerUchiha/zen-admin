{{--
    The applicant at a glance, above the profile's tabs: who they are, what
    they applied for and where each application stands, how their documents
    look, and how far the assessments have got.

    Read-only. Every action stays where it already lives (the tabs, and Set
    Status for hiring), so this adds context, not new powers.
--}}
@php
    $applications = collect($profileApplications ?? []);
    $documents = $profileDocuments ?? null;
    $assessments = collect($assessmentResults ?? []);
    $assessmentsDone = $assessments->where('hasResult', true)->count();
    $open = $applications->reject(fn ($a) => $a->is_closed);
@endphp

<div class="ap-overview">
    <div class="ap-overview-id">
        @if ($applicant?->app_img)
            <img src="{{ url('/file/app-img/' . $applicant->app_img) }}" alt="">
        @else
            <span class="ap-overview-initials">{{ strtoupper(mb_substr($applicant?->app_fname ?? 'A', 0, 1) . mb_substr($applicant?->app_lname ?? '', 0, 1)) }}</span>
        @endif
        <div>
            <b>{{ $applicant?->first_last_name }}</b>
            <span>{{ $applicant?->app_email }}{{ $applicant?->app_mobile ? ' · ' . $applicant->app_mobile : '' }}</span>
            <span>Applicant since {{ $applicant?->app_date ? \Illuminate\Support\Carbon::parse($applicant->app_date)->format('M j, Y') : '—' }}</span>
        </div>
    </div>

    <div class="ap-overview-facts">
        <div class="ap-fact">
            <span class="ap-fact-label">Applications</span>
            @if ($applications->isEmpty())
                <span class="ap-fact-value text-muted">None yet</span>
            @else
                <span class="ap-fact-value">{{ $open->count() }} open · {{ $applications->count() }} total</span>
                <ul class="ap-applications">
                    @foreach ($applications->take(4) as $application)
                        <li>
                            <span>{{ $application->posting_title ?: '—' }}</span>
                            <span class="hf-chip {{ $application->is_closed ? 'hf-chip-lock' : 'hf-chip-ok' }}">{{ $application->status }}</span>
                        </li>
                    @endforeach
                    @if ($applications->count() > 4)
                        <li class="text-muted">…and {{ $applications->count() - 4 }} more</li>
                    @endif
                </ul>
            @endif
        </div>

        @can('applicant-documents.view')
            <div class="ap-fact">
                <span class="ap-fact-label">Documents</span>
                @if ($documents)
                    <span class="ap-fact-value">
                        <span class="hf-chip {{ $documents['complete'] ? 'hf-chip-ok' : 'hf-chip-caution' }}">
                            {{ $documents['required_accepted'] }}/{{ $documents['required_total'] }} accepted
                        </span>
                    </span>
                @else
                    <span class="ap-fact-value text-muted">—</span>
                @endif
                <a class="ap-fact-link" href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'documents']) }}">Open documents</a>
            </div>
        @endcan

        <div class="ap-fact">
            <span class="ap-fact-label">Assessments</span>
            <span class="ap-fact-value">
                <span class="hf-chip {{ $assessmentsDone === $assessments->count() && $assessments->count() ? 'hf-chip-ok' : 'hf-chip-lock' }}">
                    {{ $assessmentsDone }}/{{ $assessments->count() }} with results
                </span>
            </span>
            <a class="ap-fact-link" href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'assessment-access']) }}">Access &amp; status</a>
        </div>
    </div>
</div>
