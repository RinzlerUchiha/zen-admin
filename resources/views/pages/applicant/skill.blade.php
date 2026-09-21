@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Category badges ── */
    .skill-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 500;
        white-space: nowrap;
    }
    .skill-badge-technical  { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .skill-badge-soft       { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .skill-badge-language   { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .skill-badge-others     { background: var(--zn-surface-2); color: var(--zn-ink-2); }
    .skill-badge-default    { background: var(--zn-surface-2); color: var(--zn-ink-2); }

    /* ── Table header ── */
    #skills-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--zn-ink-3);
        border-bottom: 1px solid var(--zn-line);
        white-space: nowrap;
    }

    #skills-list-table tbody td {
        vertical-align: middle;
        font-size: 15px;
    }

    /* ── Empty state ── */
    .skill-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--zn-ink-3);
    }
    .skill-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card header ── */
    #skills-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--zn-line);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #skills-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .skill-card-mobile {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 14px 16px;
        margin-bottom: 10px;
        background: var(--zn-surface);
    }
    .skill-card-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }
    .skill-card-mobile-name {
        font-weight: 500;
        font-size: 14px;
    }
</style>
@endpush

@section('profile_content')

@php
    $skills = $applicant?->skill ?? collect();
    $count  = $skills->count();

    $badgeMap = [
        'technical'  => 'skill-badge-technical',
        'soft'       => 'skill-badge-soft',
        'soft skill' => 'skill-badge-soft',
        'language'   => 'skill-badge-language',
        'others'     => 'skill-badge-others',
    ];
@endphp

<div class="card" id="skills-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-tool me-2 text-muted" aria-hidden="true"></i>Skills
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">
                {{ $count }} {{ Str::plural('skill', $count) }}
            </span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="skills-list-table">
            <caption class="visually-hidden">Skills of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Category</th>
                    <th scope="col">Skill</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skills as $list)
                @php
                    $cat      = strtolower(trim($list->sc_title ?? ''));
                    $badgeCss = $badgeMap[$cat] ?? 'skill-badge-default';
                @endphp
                <tr>
                    <td>
                        <span class="skill-badge {{ $badgeCss }}">
                            {{ $list->sc_title ?? '—' }}
                        </span>
                    </td>
                    <td>{{ $list->display_name ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2">
                        <div class="skill-empty-state">
                            <i class="ti ti-pencil-off" aria-hidden="true"></i>
                            No skills on record.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($skills as $list)
        @php
            $cat      = strtolower(trim($list->sc_title ?? ''));
            $badgeCss = $badgeMap[$cat] ?? 'skill-badge-default';
        @endphp
        <div class="skill-card-mobile">
            <div class="skill-card-mobile-header">
                <span class="skill-card-mobile-name">{{ $list->display_name ?: '—' }}</span>
                <span class="skill-badge {{ $badgeCss }}">{{ $list->sc_title ?? '—' }}</span>
            </div>
        </div>
        @empty
        <div class="skill-empty-state">
            <i class="ti ti-pencil-off" aria-hidden="true"></i>
            No skills on record.
        </div>
        @endforelse
    </div>
</div>

@endsection