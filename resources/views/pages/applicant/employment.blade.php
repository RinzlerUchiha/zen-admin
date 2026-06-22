@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Status badges ── */
    .empl-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    .empl-badge-current { background: #E1F5EE; color: #085041; }
    .empl-badge-past     { background: #F1EFE8; color: #444441; }

    /* ── Table header (matches family) ── */
    #employment-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    #employment-list-table tbody td {
        vertical-align: middle;
    }

    /* ── Truncated long cells ── */
    .empl-cell-truncate {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Empty state ── */
    .empl-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    .empl-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card wrapper ── */
    #employment-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #employment-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .empl-card-mobile {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .empl-card-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .empl-card-mobile-header .empl-name {
        font-weight: 500;
        font-size: 14px;
    }
    .empl-card-mobile-position {
        font-size: 12px;
        color: #adb5bd;
        margin-bottom: 8px;
    }
    .empl-card-mobile-body {
        font-size: 13px;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .empl-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 6px;
        color: #6c757d;
    }
</style>
@endpush

@section('profile_content')

@php
    $employmentRecs = $applicant?->employmentRec ?? collect();
    $count = $employmentRecs->count();
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="employment-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-briefcase me-2 text-muted" aria-hidden="true"></i>Employment Record
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">{{ $count }} {{ Str::plural('record', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="employment-list-table">
            <caption class="visually-hidden">Employment record of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Company</th>
                    <th scope="col">Address</th>
                    <th scope="col">Position</th>
                    <th scope="col">Supervisor</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Start Date</th>
                    <th scope="col">End Date</th>
                    <th scope="col">Reason for Leaving</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employmentRecs as $list)
                @php
                    $fromDate = $list->empl_from ? \Carbon\Carbon::parse($list->empl_from) : null;
                    $toDate   = $list->empl_to ? \Carbon\Carbon::parse($list->empl_to) : null;
                    $isCurrent = $fromDate && !$toDate;
                @endphp
                <tr>
                    <td class="fw-medium">{{ $list->empl_company ?: '—' }}</td>
                    <td>
                        <span class="empl-cell-truncate d-inline-block" title="{{ $list->empl_address }}">
                            {{ $list->empl_address ?: '—' }}
                        </span>
                    </td>
                    <td class="text-nowrap">{{ $list->empl_position ?: '—' }}</td>
                    <td class="text-nowrap">{{ $list->empl_supervisor ?: '—' }}</td>
                    <td class="text-nowrap">{{ $list->empl_contact ?: '—' }}</td>
                    <td class="text-nowrap">{{ $fromDate?->format('M d, Y') ?? '—' }}</td>
                    <td class="text-nowrap">
                        @if($isCurrent)
                            <span class="empl-badge empl-badge-current">Current</span>
                        @elseif($toDate)
                            {{ $toDate->format('M d, Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <span class="empl-cell-truncate d-inline-block" title="{{ $list->empl_reason }}">
                            {{ $list->empl_reason ?: '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empl-empty-state">
                            <i class="ti ti-briefcase-off" aria-hidden="true"></i>
                            No employment records on file.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($employmentRecs as $list)
        @php
            $fromFmt   = $list->empl_from ? \Carbon\Carbon::parse($list->empl_from)->format('M d, Y') : null;
            $toDate    = $list->empl_to ? \Carbon\Carbon::parse($list->empl_to) : null;
            $toFmt     = $toDate?->format('M d, Y');
            $isCurrent = $fromFmt && !$toDate;
        @endphp
        <div class="empl-card-mobile">
            <div class="empl-card-mobile-header">
                <span class="empl-name">{{ $list->empl_company ?: '—' }}</span>
                @if($isCurrent)
                    <span class="empl-badge empl-badge-current">Current</span>
                @else
                    <span class="empl-badge empl-badge-past">Past</span>
                @endif
            </div>
            @if($list->empl_position)
                <div class="empl-card-mobile-position">{{ $list->empl_position }}</div>
            @endif
            <div class="empl-card-mobile-body">
                @if($fromFmt)
                    <div>
                        <i class="ti ti-calendar" aria-hidden="true"></i>
                        {{ $fromFmt }} – {{ $toFmt ?? 'Present' }}
                    </div>
                @endif
                @if($list->empl_address)
                    <div><i class="ti ti-map-pin" aria-hidden="true"></i>{{ $list->empl_address }}</div>
                @endif
                @if($list->empl_supervisor)
                    <div><i class="ti ti-user" aria-hidden="true"></i>{{ $list->empl_supervisor }}</div>
                @endif
                @if($list->empl_contact)
                    <div><i class="ti ti-phone" aria-hidden="true"></i>{{ $list->empl_contact }}</div>
                @endif
                @if($list->empl_reason)
                    <div><i class="ti ti-door-exit" aria-hidden="true"></i>{{ $list->empl_reason }}</div>
                @endif
            </div>
        </div>
        @empty
        <div class="empl-empty-state">
            <i class="ti ti-briefcase-off" aria-hidden="true"></i>
            No employment records on file.
        </div>
        @endforelse
    </div>
</div>

@stop