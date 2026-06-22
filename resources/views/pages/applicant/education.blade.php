@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Status badges ── */
    .educ-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    .educ-badge-graduated { background: #E1F5EE; color: #085041; }
    .educ-badge-ongoing   { background: #E6F1FB; color: #0C447C; }
    .educ-badge-dropped   { background: #FAECE7; color: #712B13; }
    .educ-badge-default   { background: #F1EFE8; color: #444441; }

    /* ── Table header ── */
    #education-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    #education-list-table tbody td {
        vertical-align: middle;
    }

    /* ── Truncated long cells ── */
    .educ-cell-truncate {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Empty state ── */
    .educ-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    .educ-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card wrapper ── */
    #education-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #education-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .educ-card-mobile {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .educ-card-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .educ-card-mobile-header .educ-school {
        font-weight: 500;
        font-size: 14px;
    }
    .educ-card-mobile-body {
        font-size: 13px;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .educ-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 6px;
        color: #6c757d;
    }
</style>
@endpush

@section('profile_content')

@php
    $education = $applicant?->education ?? collect();
    $count     = $education->count();

    $badgeMap = [
        'graduated' => 'educ-badge-graduated',
        'ongoing'   => 'educ-badge-ongoing',
        'dropped'   => 'educ-badge-dropped',
    ];
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="education-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-school me-2 text-muted" aria-hidden="true"></i>Educational background
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">{{ $count }} {{ Str::plural('record', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="education-list-table">
            <caption class="visually-hidden">Educational background of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Level</th>
                    <th scope="col">School</th>
                    <th scope="col">Address</th>
                    <th scope="col">Degree / Title</th>
                    <th scope="col">Major</th>
                    <th scope="col" class="text-center">Year Graduated</th>
                    <th scope="col" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($education as $item)
                @php
                    $statusKey = strtolower(trim($item->educ_currStatus ?? ''));
                    $badgeCss  = $badgeMap[$statusKey] ?? 'educ-badge-default';
                @endphp
                <tr>
                    <td class="text-nowrap">{{ $item->educ_level ?? '—' }}</td>
                    <td>
                        <span class="fw-medium">{{ $item->educ_school ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="educ-cell-truncate d-inline-block"
                              title="{{ $item->educ_schooladd }}">
                            {{ $item->educ_schooladd ?? '—' }}
                        </span>
                    </td>
                    <td>{{ $item->educ_degreetitle ?? '—' }}</td>
                    <td>{{ $item->educ_major ?? '—' }}</td>
                    <td class="text-nowrap text-center">{{ $item->educ_yeargrad ?? '—' }}</td>
                    <td class="text-center">
                        <span class="educ-badge {{ $badgeCss }}">
                            {{ $item->educ_currStatus ?? 'N/A' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="educ-empty-state">
                            <i class="ti ti-school-off" aria-hidden="true"></i>
                            No education records found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($education as $item)
        @php
            $statusKey = strtolower(trim($item->educ_currStatus ?? ''));
            $badgeCss  = $badgeMap[$statusKey] ?? 'educ-badge-default';
        @endphp
        <div class="educ-card-mobile">
            <div class="educ-card-mobile-header">
                <span class="educ-school">{{ $item->educ_school ?? '—' }}</span>
                <span class="educ-badge {{ $badgeCss }}">{{ $item->educ_currStatus ?? 'N/A' }}</span>
            </div>
            <div class="educ-card-mobile-body">
                @if($item->educ_level)
                    <div>
                        <i class="ti ti-ladder" aria-hidden="true"></i>
                        {{ $item->educ_level }}
                    </div>
                @endif
                @if($item->educ_degreetitle || $item->educ_major)
                    <div>
                        <i class="ti ti-certificate" aria-hidden="true"></i>
                        {{ $item->educ_degreetitle ?? '—' }}
                        @if($item->educ_major)
                            <span class="text-muted">· {{ $item->educ_major }}</span>
                        @endif
                    </div>
                @endif
                @if($item->educ_yeargrad)
                    <div>
                        <i class="ti ti-calendar" aria-hidden="true"></i>
                        Graduated {{ $item->educ_yeargrad }}
                    </div>
                @endif
                @if($item->educ_schooladd)
                    <div>
                        <i class="ti ti-map-pin" aria-hidden="true"></i>
                        {{ $item->educ_schooladd }}
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="educ-empty-state">
            <i class="ti ti-school-off" aria-hidden="true"></i>
            No education records found.
        </div>
        @endforelse
    </div>
</div>

@endsection