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
    @php
        $apInitials = strtoupper(
            mb_substr($applicant?->app_fname ?? 'A', 0, 1) . mb_substr($applicant?->app_lname ?? '', 0, 1)
        );
    @endphp
    <div class="ap-overview-id">
        {{--
            The applicant's photo.

            The URL used to be built by hand as /file/app-img/{name}, which is
            the shape zen-applicants serves; zen-admin serves the same files at
            /file/get/applicant/{src}/{filename}, so the image 404'd and HR got
            a broken icon rather than the initials -- worse than no photo,
            because app_img was set so the fallback never ran.

            The initials sit underneath the image, not instead of it, so
            anything that stops the file loading (it lives on the shared S3
            disk, and the two apps keep separate storage outside production)
            shows a placeholder rather than a broken picture.

            That fallback did not actually run. The file route answers a
            missing file with a placeholder picture and HTTP 200, so onerror
            never fired and a "FILE NOT AVAILABLE" graphic sat in the circle
            where the initials should have been. `missing=skip` asks the route
            for a 404 instead, which is what makes the line below work.
        --}}
        <span class="ap-overview-photo">
            <span class="ap-overview-initials" aria-hidden="true">{{ $apInitials }}</span>
            @if ($applicant?->app_img)
                <img src="{{ route('applicant.file', ['src' => 'app-img', 'filename' => $applicant->app_img, 'missing' => 'skip']) }}"
                     alt="{{ $applicant?->first_last_name }}"
                     loading="lazy"
                     onerror="this.remove()">
            @endif
        </span>
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
                            @php
                                $progress = $application->progress ?? null;
                                $hasRail = $progress && ($progress['total'] ?? 0) > 0;

                                /* The step it is on, named -- the rail shows
                                   position, this says what the position IS. */
                                $railNow = null;
                                if ($hasRail) {
                                    foreach ($progress['steps'] as $s) {
                                        if (!empty($s['current'])) { $railNow = $s['label']; break; }
                                    }
                                }

                                /* One sentence of the same thing for a screen
                                   reader, since the rail itself is decoration. */
                                $railSays = [];
                                if ($hasRail) {
                                    foreach ($progress['steps'] as $s) {
                                        $railSays[] = $s['label'] . ': ' . (
                                            $s['state'] === 'completed' ? 'done'
                                                : ($s['state'] === 'not_applicable' ? 'not applicable'
                                                : (!empty($s['current']) ? 'current' : 'still to come'))
                                        );
                                    }
                                }
                            @endphp

                            {{--
                                What it is WAITING ON, which is a different
                                question from what was decided about it. Shown
                                only while it is open; a closed application is
                                not waiting on anything.

                                "Next: X" is only printed when there is no rail
                                to say it. With one, the rail names the current
                                step itself and the chip was the same sentence
                                twice, costing width the rail needed.
                            --}}
                            @if (!$application->is_closed)
                                @if ($application->stage_done)
                                    <span class="ap-stage is-done">All rounds done</span>
                                @elseif (!$hasRail && $application->stage_label)
                                    <span class="ap-stage">Next: {{ $application->stage_label }}</span>
                                @elseif (!$hasRail)
                                    <span class="ap-stage is-none">Not started</span>
                                @endif
                            @endif

                            {{--
                                How far along, and what it is on.

                                This was seven-pixel dots and a fraction. They
                                fitted, and they said almost nothing: four
                                identical specks, no way to tell which was the
                                Initial Interview and which the Final, and the
                                name of the step you were actually on only
                                appearing if you knew to hover. "0/3" told you
                                nothing had happened and nothing else.

                                It is now a bar of one segment per step, in the
                                application's own order, with the current step
                                NAMED beside it. Same height, same line, same
                                sticky header -- the segments are the width the
                                dots were wasting in gaps.

                                The bar is a fixed width whatever the number of
                                steps, so three applications stacked above each
                                other line up instead of ending raggedly.

                                A closed application keeps its bar: where it
                                stopped is part of what happened.

                                Informational only. Nothing here is a control
                                and nothing here can change a status -- the
                                workflow is driven from Interview Details.
                            --}}
                            @if ($hasRail)
                                <span class="ap-rail" role="img"
                                      aria-label="{{ $progress['completed'] }} of {{ $progress['total'] }} steps settled. {{ implode('; ', $railSays) }}."
                                      title="{{ implode("\n", $railSays) }}">
                                    @foreach ($progress['steps'] as $step)
                                        <span class="ap-rail-seg
                                            @if ($step['state'] === 'completed') is-done
                                            @elseif ($step['state'] === 'not_applicable') is-na
                                            @elseif ($step['current']) is-now
                                            @endif"></span>
                                    @endforeach
                                </span>

                                {{-- The step itself, in words. The rail shows
                                     where; this says what. --}}
                                @if ($railNow && !$application->is_closed)
                                    <span class="ap-rail-now">{{ $railNow }}</span>
                                @endif

                                <span class="ap-rail-count">{{ $progress['completed'] }} of {{ $progress['total'] }}</span>

                                {{-- Hired is a status, not a step, so it is said
                                     beside the marks and never drawn as one. --}}
                                @if ($progress['hired'])
                                    <span class="hf-chip hf-chip-ok">Hired</span>
                                @endif
                            @endif
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
