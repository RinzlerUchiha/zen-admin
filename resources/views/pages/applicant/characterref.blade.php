@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Relationship badges (same palette as family) ── */
    .ref-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    .ref-badge-spouse  { background: #EEEDFE; color: #3C3489; }
    .ref-badge-parent  { background: #FAECE7; color: #712B13; }
    .ref-badge-sibling { background: #E6F1FB; color: #0C447C; }
    .ref-badge-friend  { background: #E1F5EE; color: #085041; }
    .ref-badge-colleague { background: #FDF3DC; color: #7A5B05; }
    .ref-badge-default { background: #F1EFE8; color: #444441; }

    /* ── Table header (matches family) ── */
    #characterref-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    #characterref-list-table tbody td {
        vertical-align: middle;
    }

    /* ── Truncated long cells ── */
    .ref-cell-truncate {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Empty state ── */
    .ref-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    .ref-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card wrapper ── */
    #characterref-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #characterref-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .ref-card-mobile {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .ref-card-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .ref-card-mobile-header .ref-name {
        font-weight: 500;
        font-size: 14px;
    }
    .ref-card-mobile-body {
        font-size: 13px;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .ref-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 6px;
        color: #6c757d;
    }
</style>
@endpush

@section('profile_content')

@php
    $refs  = $applicant?->characterRef ?? collect();
    $count = $refs->count();

    /* Map relationship values to badge CSS classes */
    $refBadgeMap = [
        'spouse'    => 'ref-badge-spouse',
        'parent'    => 'ref-badge-parent',
        'father'    => 'ref-badge-parent',
        'mother'    => 'ref-badge-parent',
        'sibling'   => 'ref-badge-sibling',
        'brother'   => 'ref-badge-sibling',
        'sister'    => 'ref-badge-sibling',
        'friend'    => 'ref-badge-friend',
        'colleague' => 'ref-badge-colleague',
        'coworker'  => 'ref-badge-colleague',
    ];
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="characterref-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-user-check me-2 text-muted" aria-hidden="true"></i>Character References
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">{{ $count }} {{ Str::plural('reference', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="characterref-list-table">
            <caption class="visually-hidden">Character references of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Full Name</th>
                    <th scope="col">Company</th>
                    <th scope="col">Address</th>
                    <th scope="col">Position</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Relationship</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refs as $list)
                @php
                    $rel      = strtolower(trim($list->ref_relationship ?? ''));
                    $badgeCss = $refBadgeMap[$rel] ?? 'ref-badge-default';
                @endphp
                <tr>
                    <td class="fw-medium">{{ $list->ref_fullname ?: '—' }}</td>
                    <td class="text-nowrap">{{ $list->ref_company ?: '—' }}</td>
                    <td>
                        <span class="ref-cell-truncate d-inline-block" title="{{ $list->ref_address }}">
                            {{ $list->ref_address ?: '—' }}
                        </span>
                    </td>
                    <td class="text-nowrap">{{ $list->ref_position ?: '—' }}</td>
                    <td class="text-nowrap">{{ $list->ref_contact ?: '—' }}</td>
                    <td>
                        <span class="ref-badge {{ $badgeCss }}">
                            {{ $list->ref_relationship ?: '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="ref-empty-state">
                            <i class="ti ti-user-off" aria-hidden="true"></i>
                            No character references on record.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($refs as $list)
        @php
            $rel      = strtolower(trim($list->ref_relationship ?? ''));
            $badgeCss = $refBadgeMap[$rel] ?? 'ref-badge-default';
        @endphp
        <div class="ref-card-mobile">
            <div class="ref-card-mobile-header">
                <span class="ref-name">{{ $list->ref_fullname ?: '—' }}</span>
                <span class="ref-badge {{ $badgeCss }}">{{ $list->ref_relationship ?: '—' }}</span>
            </div>
            <div class="ref-card-mobile-body">
                @if($list->ref_position)
                    <div><i class="ti ti-briefcase" aria-hidden="true"></i>{{ $list->ref_position }}
                        @if($list->ref_company)
                            <span class="text-muted">· {{ $list->ref_company }}</span>
                        @endif
                    </div>
                @endif
                @if($list->ref_address)
                    <div><i class="ti ti-map-pin" aria-hidden="true"></i>{{ $list->ref_address }}</div>
                @endif
                @if($list->ref_contact)
                    <div><i class="ti ti-phone" aria-hidden="true"></i>{{ $list->ref_contact }}</div>
                @endif
            </div>
        </div>
        @empty
        <div class="ref-empty-state">
            <i class="ti ti-user-off" aria-hidden="true"></i>
            No character references on record.
        </div>
        @endforelse
    </div>
</div>

@stop