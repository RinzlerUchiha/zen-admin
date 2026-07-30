@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ══════════════════════════════════════
       Card shell
    ══════════════════════════════════════ */
    #employment-list-card {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
    }
    #employment-list-card .card-header {
        background: linear-gradient(180deg, #fafbfc 0%, #f5f6f8 100%);
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    #employment-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        color: #1c1f24;
    }
    #employment-list-card .card-header .card-title i {
        font-size: 18px;
        margin-right: 0.6rem;
        color: #6c757d;
    }
    #employment-list-card .card-header .badge {
        font-size: 11.5px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eef1f4;
        color: #495057;
    }

    /* ══════════════════════════════════════
       Status badges
    ══════════════════════════════════════ */
    .empl-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 11px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
        letter-spacing: 0.01em;
    }
    .empl-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .empl-badge-current { background: #E1F5EE; color: #085041; }
    .empl-badge-current::before { background: #16a072; }
    .empl-badge-past { background: #F1EFE8; color: #57544c; }
    .empl-badge-past::before { background: #a8a397; }

    /* ══════════════════════════════════════
       Desktop table
    ══════════════════════════════════════ */
    #employment-list-table thead th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #868e96;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
        padding: 0.85rem 1rem;
        background: #fcfcfd;
    }
    #employment-list-table tbody td {
        vertical-align: middle;
        padding: 0.9rem 1rem;
        font-size: 13.5px;
        color: #343a40;
        border-bottom: 1px solid #f1f3f5;
    }
    #employment-list-table tbody tr:last-child td {
        border-bottom: none;
    }
    #employment-list-table tbody tr {
        transition: background-color 0.12s ease;
    }
    #employment-list-table tbody tr:hover {
        background-color: #f8f9fb;
    }
    .empl-company-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .empl-company-avatar {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #eef1f4;
        color: #495057;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
    }
    .empl-company-name {
        font-weight: 600;
        color: #1c1f24;
        line-height: 1.35;
    }
    .empl-position-sub {
        font-size: 12px;
        color: #868e96;
        margin-top: 1px;
    }

    /* Truncated long cells, with full text available via title attr */
    .empl-cell-truncate {
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ══════════════════════════════════════
       Empty state
    ══════════════════════════════════════ */
    .empl-empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #868e96;
    }
    .empl-empty-state i {
        font-size: 2.1rem;
        display: block;
        margin-bottom: 0.65rem;
        opacity: 0.35;
    }
    .empl-empty-state p {
        font-size: 13.5px;
        margin: 0;
    }

    /* ══════════════════════════════════════
       Mobile cards
    ══════════════════════════════════════ */
    .empl-card-mobile {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.03);
    }
    .empl-card-mobile:last-child { margin-bottom: 0; }
    .empl-card-mobile-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }
    .empl-card-mobile-header .empl-name {
        font-weight: 600;
        font-size: 14.5px;
        color: #1c1f24;
        line-height: 1.3;
    }
    .empl-card-mobile-position {
        font-size: 12.5px;
        color: #868e96;
        margin-top: -6px;
        margin-bottom: 10px;
    }
    .empl-card-mobile-body {
        font-size: 13px;
        color: #495057;
        border-top: 1px dashed #e9ecef;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .empl-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 7px;
        color: #adb5bd;
        width: 14px;
        display: inline-block;
    }
</style>
@endpush

@section('profile_content')

@php
    /**
     * Compute derived date fields exactly once, up front, so the desktop
     * table and mobile card list can never drift out of sync with each
     * other (previously each block parsed/derived "is current" independently).
     */
    $employmentRecs = ($applicant?->employmentRec ?? collect())->map(function ($record) {
        $record->from_date  = $record->empl_from ? \Carbon\Carbon::parse($record->empl_from) : null;
        $record->to_date    = $record->empl_to ? \Carbon\Carbon::parse($record->empl_to) : null;
        $record->is_current = (bool) ($record->from_date && !$record->to_date);
        return $record;
    });
    $count = $employmentRecs->count();
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="employment-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-briefcase" aria-hidden="true"></i>Employment Record
        </h2>
        @if($count > 0)
            <span class="badge">{{ $count }} {{ Str::plural('record', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm mb-0" id="employment-list-table">
            <caption class="visually-hidden">Employment record of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Company / Position</th>
                    <th scope="col">Address</th>
                    <th scope="col">Supervisor</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Reason for Leaving</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employmentRecs as $record)
                <tr>
                    <td>
                        <div class="empl-company-cell">
                            <span class="empl-company-avatar">{{ Str::substr($record->empl_company ?: '?', 0, 2) }}</span>
                            <div>
                                <div class="empl-company-name">{{ $record->empl_company ?: '—' }}</div>
                                @if($record->empl_position)
                                    <div class="empl-position-sub">{{ $record->empl_position }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="empl-cell-truncate d-inline-block" title="{{ $record->empl_address }}">
                            {{ $record->empl_address ?: '—' }}
                        </span>
                    </td>
                    <td class="text-nowrap">{{ $record->empl_supervisor ?: '—' }}</td>
                    <td class="text-nowrap">{{ $record->empl_contact ?: '—' }}</td>
                    <td class="text-nowrap">
                        {{ $record->from_date?->format('M Y') ?? '—' }}
                        &ndash;
                        @if($record->is_current)
                            <span class="empl-badge empl-badge-current">Current</span>
                        @else
                            {{ $record->to_date?->format('M Y') ?? '—' }}
                        @endif
                    </td>
                    <td>
                        <span class="empl-cell-truncate d-inline-block" title="{{ $record->empl_reason }}">
                            {{ $record->empl_reason ?: '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empl-empty-state">
                            <i class="ti ti-briefcase-off" aria-hidden="true"></i>
                            <p>No employment records on file.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($employmentRecs as $record)
        <div class="empl-card-mobile">
            <div class="empl-card-mobile-header">
                <span class="empl-name">{{ $record->empl_company ?: '—' }}</span>
                @if($record->is_current)
                    <span class="empl-badge empl-badge-current">Current</span>
                @else
                    <span class="empl-badge empl-badge-past">Past</span>
                @endif
            </div>
            @if($record->empl_position)
                <div class="empl-card-mobile-position">{{ $record->empl_position }}</div>
            @endif
            <div class="empl-card-mobile-body">
                @if($record->from_date)
                    <div>
                        <i class="ti ti-calendar" aria-hidden="true"></i>
                        {{ $record->from_date->format('M d, Y') }} – {{ $record->to_date?->format('M d, Y') ?? 'Present' }}
                    </div>
                @endif
                @if($record->empl_address)
                    <div><i class="ti ti-map-pin" aria-hidden="true"></i>{{ $record->empl_address }}</div>
                @endif
                @if($record->empl_supervisor)
                    <div><i class="ti ti-user" aria-hidden="true"></i>{{ $record->empl_supervisor }}</div>
                @endif
                @if($record->empl_contact)
                    <div><i class="ti ti-phone" aria-hidden="true"></i>{{ $record->empl_contact }}</div>
                @endif
                @if($record->empl_reason)
                    <div><i class="ti ti-door-exit" aria-hidden="true"></i>{{ $record->empl_reason }}</div>
                @endif
            </div>
        </div>
        @empty
        <div class="empl-empty-state">
            <i class="ti ti-briefcase-off" aria-hidden="true"></i>
            <p>No employment records on file.</p>
        </div>
        @endforelse
    </div>
</div>

@stop