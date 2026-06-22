@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Expiry badges ── */
    .lic-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    .lic-badge-valid   { background: #E1F5EE; color: #085041; }
    .lic-badge-expired { background: #FAECE7; color: #712B13; }
    .lic-badge-unknown { background: #F1EFE8; color: #444441; }

    /* ── Table header (matches family) ── */
    #license-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    #license-list-table tbody td {
        vertical-align: middle;
    }

    /* ── Truncated long cells ── */
    .lic-cell-truncate {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Empty state ── */
    .lic-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    .lic-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card wrapper ── */
    #license-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #license-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .lic-card-mobile {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .lic-card-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .lic-card-mobile-header .lic-title {
        font-weight: 500;
        font-size: 14px;
    }
    .lic-card-mobile-body {
        font-size: 13px;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .lic-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 6px;
        color: #6c757d;
    }
</style>
@endpush

@section('profile_content')

@php
    $licenses = $applicant?->license ?? collect();
    $count = $licenses->count();
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="license-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-license me-2 text-muted" aria-hidden="true"></i>Licenses / Eligibility
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">{{ $count }} {{ Str::plural('record', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="license-list-table">
            <caption class="visually-hidden">Licenses and eligibility of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">License Type</th>
                    <th scope="col">Registration Date</th>
                    <th scope="col">Valid Until</th>
                    <th scope="col">Profession</th>
                    <th scope="col">Attachment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $list)
                @php
                    $regDate = $list->el_regdate ? \Carbon\Carbon::parse($list->el_regdate) : null;
                    $expDate = $list->el_expdate ? \Carbon\Carbon::parse($list->el_expdate) : null;

                    if (!$expDate) {
                        $expBadge = 'lic-badge-unknown';
                    } elseif ($expDate->isFuture()) {
                        $expBadge = 'lic-badge-valid';
                    } else {
                        $expBadge = 'lic-badge-expired';
                    }
                @endphp
                <tr>
                    <td class="fw-medium">{{ $list->el_type ?: '—' }}</td>
                    <td class="text-nowrap">{{ $regDate?->format('M d, Y') ?? '—' }}</td>
                    <td class="text-nowrap">
                        @if($expDate)
                            <span class="lic-badge {{ $expBadge }}">{{ $expDate->format('M d, Y') }}</span>
                        @else
                            <span class="lic-badge lic-badge-unknown">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="lic-cell-truncate d-inline-block" title="{{ $list->el_profession }}">
                            {{ $list->el_profession ?: '—' }}
                        </span>
                    </td>
                    <td>
                        @if($list->el_file)
                            <embed src="{{ '/file/get/license/'.$list->el_file }}" style="max-width: 100%; height: 150px;">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="lic-empty-state">
                            <i class="ti ti-license-off" aria-hidden="true"></i>
                            No licenses or eligibility on record.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($licenses as $list)
        @php
            $regFmt  = $list->el_regdate ? \Carbon\Carbon::parse($list->el_regdate)->format('M d, Y') : null;
            $expDate = $list->el_expdate ? \Carbon\Carbon::parse($list->el_expdate) : null;
            $expFmt  = $expDate?->format('M d, Y');

            if (!$expDate) {
                $expBadge = 'lic-badge-unknown';
            } elseif ($expDate->isFuture()) {
                $expBadge = 'lic-badge-valid';
            } else {
                $expBadge = 'lic-badge-expired';
            }
        @endphp
        <div class="lic-card-mobile">
            <div class="lic-card-mobile-header">
                <span class="lic-title">{{ $list->el_type ?: '—' }}</span>
                @if($expFmt)
                    <span class="lic-badge {{ $expBadge }}">{{ $expFmt }}</span>
                @endif
            </div>
            <div class="lic-card-mobile-body">
                @if($regFmt)
                    <div><i class="ti ti-calendar" aria-hidden="true"></i>Registered {{ $regFmt }}</div>
                @endif
                @if($list->el_profession)
                    <div><i class="ti ti-briefcase" aria-hidden="true"></i>{{ $list->el_profession }}</div>
                @endif
                @if($list->el_file)
                    <div><embed src="{{ '/file/get/license/'.$list->el_file }}" style="max-width: 100%; height: 120px;"></div>
                @endif
            </div>
        </div>
        @empty
        <div class="lic-empty-state">
            <i class="ti ti-license-off" aria-hidden="true"></i>
            No licenses or eligibility on record.
        </div>
        @endforelse
    </div>
</div>

@stop