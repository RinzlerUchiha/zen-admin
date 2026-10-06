@extends('pages.applicant.profile')

@push('styles')
@endpush

@section('profile_content')
@php
    /*
     | The candidate's application history.
     |
     | This existed nowhere before: the overview partial showed the first four
     | titles and their status, and everything else — which manpower request a
     | posting came from, when an application closed and why, when the person
     | may apply again — was only in the database. It is the hiring context HR
     | needs most, so it gets its own page.
     |
     | $profileApplications is already loaded for every profile tab, so this
     | page adds no query. Read-only; decisions are taken in Applicant Intake.
     */
    $applications = collect($profileApplications ?? [])->sortByDesc('applied_at')->values();
    $open = $applications->reject(fn ($a) => $a->is_closed);
    $pooled = $applications->filter(fn ($a) => $a->in_candidate_pool);
@endphp

<style>
    .apx-head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 14px;
    }

    .apx-head h5 { margin: 0; font-weight: 800; color: var(--zn-ink); }
    .apx-count { font-size: var(--zn-fs-ui); color: var(--zn-ink-3); }

    .apx-pool {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 10px 14px;
        margin-bottom: 16px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-ok-soft);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-ui);
    }

    .apx-pool i { color: var(--zn-ok); margin-top: 2px; }

    .apx-card {
        border: 1px solid var(--zn-line);
        border-left-width: 3px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 14px 16px;
        margin-bottom: 10px;
    }

    .apx-card.is-open { border-left-color: var(--hf-progress, var(--zn-ink-2)); }
    .apx-card.is-closed { border-left-color: var(--zn-line-2); }

    /* The recommendation link. Quieter than the status chips: it is context
       about where the applicant went, not the application's own outcome. */
    .apx-rec {
        margin-top: 6px;
        font-size: .9em;
        color: var(--zn-ink-2);
    }

    .apx-rec i { opacity: .7; }

    .apx-rec-reason,
    .apx-rec-count {
        color: var(--zn-ink-3, var(--zn-ink-2));
        font-style: italic;
    }

    .apx-rec-reason::before { content: '— '; }
    .apx-card.is-pooled { border-left-color: var(--hf-done, var(--zn-ok)); }

    .apx-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        margin-bottom: 8px;
    }

    .apx-title { font-weight: 700; color: var(--zn-ink); }

    .apx-mr {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-3);
    }

    .apx-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 18px;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
    }

    .apx-facts b { color: var(--zn-ink-3); font-weight: 600; }

    .apx-note {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid var(--zn-surface-2);
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .apx-empty {
        border: 1px dashed var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 28px 20px;
        text-align: center;
        color: var(--zn-ink-3);
    }

    @media (max-width: 575.98px) {
        .apx-facts { flex-direction: column; gap: 2px; }
    }
</style>

<div class="apx-wrap">
    <div class="apx-head">
        <span class="apx-count">
            {{ $open->count() }} open · {{ $applications->count() }} in total
        </span>
    </div>

    @if ($pooled->isNotEmpty())
        {{-- Candidate Pool is per application, never a property of the person
             — see config/applications.php. Said plainly so HR is not misled. --}}
        <div class="apx-pool">
            <i class="bi bi-people-fill" aria-hidden="true"></i>
            <div>
                <strong>In the Candidate Pool</strong>
                <div>
                    {{ $pooled->count() }}
                    {{ \Illuminate\Support\Str::plural('application', $pooled->count()) }}
                    closed without progressing and {{ $pooled->count() === 1 ? 'was' : 'were' }} retained.
                    This applies to those applications, not to the candidate as a whole.
                </div>
            </div>
        </div>
    @endif

    @forelse ($applications as $a)
        @php
            $cls = $a->in_candidate_pool ? 'is-pooled' : ($a->is_closed ? 'is-closed' : 'is-open');
            $reapply = \App\Models\Applicant\ApplicantApplication::cooldownEndsFor($a->status, $a->closed_at);
        @endphp
        <div class="apx-card {{ $cls }}">
            <div class="apx-top">
                <span class="apx-title">{{ $a->posting_title ?: 'Posting no longer available' }}</span>
                @if ($a->mr_no)
                    <span class="apx-mr">{{ $a->mr_no }}</span>
                @endif
                <span class="hf-chip {{ $a->is_closed ? 'hf-chip-lock' : 'hf-chip-ok' }}">{{ $a->status }}</span>
                @if ($a->in_candidate_pool)
                    <span class="hf-chip hf-chip-pool">Candidate Pool</span>
                @endif
            </div>

            <div class="apx-facts">
                <span><b>Applied</b>
                    {{ $a->applied_at ? \Illuminate\Support\Carbon::parse($a->applied_at)->format('M d, Y') : '—' }}
                </span>
                @if ($a->is_closed)
                    <span><b>Closed</b>
                        {{ $a->closed_at ? \Illuminate\Support\Carbon::parse($a->closed_at)->format('M d, Y') : '—' }}
                    </span>
                @endif
                @if ($reapply)
                    <span><b>May re-apply to this posting</b>
                        {{ $reapply->format('M d, Y') }}
                    </span>
                @endif
            </div>

            @if (trim((string) $a->closed_note) !== '')
                <div class="apx-note">{{ $a->closed_note }}</div>
            @endif

            {{-- Where this application came from, and what came of it.

                 Named by job post, because that is what HR recognises. The two
                 applications stay entirely separate: this says the applicant
                 was moved between posts, never that any interview or exam
                 carried across with them. --}}
            @if ($a->recommended_from)
                <div class="apx-rec">
                    <i class="bi bi-arrow-down-right" aria-hidden="true"></i>
                    Recommended from <b>{{ $a->recommended_from['posting_title'] }}</b>
                    @if ($a->recommended_from['recommended_at'])
                        · {{ \Illuminate\Support\Carbon::parse($a->recommended_from['recommended_at'])->format('M d, Y') }}
                    @endif
                    @if (trim((string) ($a->recommended_from['reason'] ?? '')) !== '')
                        <span class="apx-rec-reason">{{ $a->recommended_from['reason'] }}</span>
                    @endif
                </div>
            @endif

            @if (!empty($a->recommended_to))
                <div class="apx-rec">
                    <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    Recommended to
                    <b>{{ collect($a->recommended_to)->pluck('posting_title')->implode(', ') }}</b>
                    @if (count($a->recommended_to) > 1)
                        <span class="apx-rec-count">({{ count($a->recommended_to) }} posts)</span>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="apx-empty">
            <i class="bi bi-inbox d-block mb-2" style="font-size:26px;" aria-hidden="true"></i>
            This applicant registered but has not applied to any posting yet.
        </div>
    @endforelse
</div>
@endsection
