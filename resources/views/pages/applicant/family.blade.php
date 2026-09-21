@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Relationship badges ── */
    .fam-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    .fam-badge-spouse   { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .fam-badge-parent   { background: var(--zn-warn-soft); color: var(--zn-warn); }
    .fam-badge-child    { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .fam-badge-sibling  { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .fam-badge-default  { background: var(--zn-surface-2); color: var(--zn-ink-2); }

    /* ── Table header ── */
    #family-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--zn-ink-3);
        border-bottom: 1px solid var(--zn-line);
        white-space: nowrap;
    }

    #family-list-table tbody td {
        vertical-align: middle;
    }

    /* ── Truncated long cells ── */
    .fam-cell-truncate {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Muted dash for empty values ── */
    .fam-empty {
        color: var(--zn-line-2);
    }

    /* ── Empty state ── */
    .fam-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--zn-ink-3);
    }
    .fam-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card wrapper ── */
    #family-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--zn-line);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #family-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .fam-card-mobile {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 14px 16px;
        margin-bottom: 10px;
        background: var(--zn-surface);
    }
    .fam-card-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .fam-card-mobile-header .fam-name {
        font-weight: 500;
        font-size: 14px;
    }
    .fam-card-mobile-maiden {
        font-size: 12px;
        color: var(--zn-line-2);
        margin-bottom: 8px;
    }
    .fam-card-mobile-body {
        font-size: 13px;
        border-top: 1px solid var(--zn-line);
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .fam-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 6px;
        color: var(--zn-ink-3);
    }
</style>
@endpush

@section('profile_content')

@php
    $family = $applicant?->family ?? collect();
    $count  = $family->count();

    /* Map relationship values to badge CSS classes */
    $badgeMap = [
        'spouse'  => 'fam-badge-spouse',
        'wife'    => 'fam-badge-spouse',
        'husband' => 'fam-badge-spouse',
        'parent'  => 'fam-badge-parent',
        'father'  => 'fam-badge-parent',
        'mother'  => 'fam-badge-parent',
        'child'   => 'fam-badge-child',
        'son'     => 'fam-badge-child',
        'daughter'=> 'fam-badge-child',
        'sibling' => 'fam-badge-sibling',
        'brother' => 'fam-badge-sibling',
        'sister'  => 'fam-badge-sibling',
    ];
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="family-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-users me-2 text-muted" aria-hidden="true"></i>Family background
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">{{ $count }} {{ Str::plural('member', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="family-list-table">
            <caption class="visually-hidden">Family members of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Relationship</th>
                    <th scope="col">Name</th>
                    <th scope="col">Birth date</th>
                    <th scope="col">Age</th>
                    <th scope="col">Sex</th>
                    <th scope="col">Contact #</th>
                    <th scope="col">Address</th>
                    <th scope="col">Occupation</th>
                    <th scope="col">Work address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($family as $list)
                @php
                    $rel       = strtolower(trim($list->fam_relationship ?? ''));
                    $badgeCss  = $badgeMap[$rel] ?? 'fam-badge-default';
                    $fullName  = trim(implode(' ', array_filter([
                        $list->fam_firstname,
                        $list->fam_midname,
                        $list->fam_lastname,
                        $list->fam_suffix ? ', ' . $list->fam_suffix : null,
                    ])));
                    $birthDate = $list->fam_birthdate ? \Carbon\Carbon::parse($list->fam_birthdate) : null;
                    $birthFmt  = $birthDate?->format('M d, Y');
                    $age       = $birthDate?->age;
                @endphp
                <tr>
                    <td>
                        <span class="fam-badge {{ $badgeCss }}">
                            {{ $list->fam_relationship ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <span class="fw-medium">{{ $fullName ?: '—' }}</span>
                        @if($list->fam_maidenname)
                            <br>
                            <small class="text-muted">
                                <span style="font-size:10px; text-transform:uppercase; letter-spacing:0.04em;">Maiden name:</span>
                                {{ $list->fam_maidenname }}
                            </small>
                        @endif
                    </td>
                    <td class="text-nowrap">{{ $birthFmt ?? '—' }}</td>
                    <td class="text-nowrap">{{ $age ?? '—' }}</td>
                    <td>{{ $list->fam_sex ?? '—' }}</td>
                    <td class="text-nowrap">{{ $list->fam_contact ?? '—' }}</td>
                    <td>
                        <span class="fam-cell-truncate d-inline-block"
                              title="{{ $list->fam_add }}">
                            {{ $list->fam_add ?? '—' }}
                        </span>
                    </td>
                    <td class="text-nowrap">{{ $list->fam_occupation ?? '—' }}</td>
                    <td>
                        <span class="fam-cell-truncate d-inline-block"
                              title="{{ $list->fam_workplace }}">
                            {{ $list->fam_workplace ?? '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="fam-empty-state">
                            <i class="ti ti-users-off" aria-hidden="true"></i>
                            No family members on record.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($family as $list)
        @php
            $rel      = strtolower(trim($list->fam_relationship ?? ''));
            $badgeCss = $badgeMap[$rel] ?? 'fam-badge-default';
            $fullName = trim(implode(' ', array_filter([
                $list->fam_firstname,
                $list->fam_midname,
                $list->fam_lastname,
                $list->fam_suffix ? ', ' . $list->fam_suffix : null,
            ])));
            $birthFmt = $list->fam_birthdate
                            ? \Carbon\Carbon::parse($list->fam_birthdate)->format('M d, Y')
                            : null;
        @endphp
        <div class="fam-card-mobile">
            <div class="fam-card-mobile-header">
                <span class="fam-name">{{ $fullName ?: '—' }}</span>
                <span class="fam-badge {{ $badgeCss }}">{{ $list->fam_relationship ?? '—' }}</span>
            </div>
            @if($list->fam_maidenname)
                <div class="fam-card-mobile-maiden">née {{ $list->fam_maidenname }}</div>
            @endif
            <div class="fam-card-mobile-body">
                @if($birthFmt)
                    <div>
                        <i class="ti ti-calendar" aria-hidden="true"></i>
                        {{ $birthFmt }}{{ $list->age ? ' · ' . $list->age : '' }}
                    </div>
                @endif
                @if($list->fam_contact)
                    <div>
                        <i class="ti ti-phone" aria-hidden="true"></i>
                        {{ $list->fam_contact }}
                    </div>
                @endif
                @if($list->fam_add)
                    <div>
                        <i class="ti ti-map-pin" aria-hidden="true"></i>
                        {{ $list->fam_add }}
                    </div>
                @endif
                @if($list->fam_occupation)
                    <div>
                        <i class="ti ti-briefcase" aria-hidden="true"></i>
                        {{ $list->fam_occupation }}
                        @if($list->fam_workplace)
                            <span class="text-muted">· {{ $list->fam_workplace }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="fam-empty-state">
            <i class="ti ti-users-off" aria-hidden="true"></i>
            No family members on record.
        </div>
        @endforelse
    </div>
</div>

@stop