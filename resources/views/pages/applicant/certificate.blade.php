@extends('pages.applicant.profile')

@push('styles')
<style>
    /* ── Table header (matches family) ── */
    #certificate-list-table thead th {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    #certificate-list-table tbody td {
        vertical-align: middle;
    }

    /* ── Truncated long cells ── */
    .cert-cell-truncate {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Empty state ── */
    .cert-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    .cert-empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.4;
    }

    /* ── Card wrapper ── */
    #certificate-list-card .card-header {
        background: transparent;
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #certificate-list-card .card-header .card-title {
        font-size: 15px;
        font-weight: 500;
        margin: 0;
    }

    /* ── Mobile card view ── */
    .cert-card-mobile {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .cert-card-mobile-header {
        margin-bottom: 10px;
    }
    .cert-card-mobile-header .cert-title {
        font-weight: 500;
        font-size: 14px;
    }
    .cert-card-mobile-body {
        font-size: 13px;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .cert-card-mobile-body i {
        font-size: 14px;
        vertical-align: -2px;
        margin-right: 6px;
        color: #6c757d;
    }
</style>
@endpush

@section('profile_content')

@php
    $certificates = $applicant?->certificate ?? collect();
    $count = $certificates->count();
@endphp

{{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     Card wrapper
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
<div class="card" id="certificate-list-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="ti ti-certificate me-2 text-muted" aria-hidden="true"></i>Certificates / Trainings
        </h2>
        @if($count > 0)
            <span class="badge bg-secondary fw-normal">{{ $count }} {{ Str::plural('record', $count) }}</span>
        @endif
    </div>

    {{-- ── Desktop table (hidden on xs/sm) ── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm table-hover mb-0" id="certificate-list-table">
            <caption class="visually-hidden">Certificates and trainings of the applicant</caption>
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Completion Date</th>
                    <th scope="col">Location of Event/Course</th>
                    <th scope="col">Speaker</th>
                    <th scope="col">Attachment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($certificates as $list)
                @php
                    $certDate = $list->cert_date ? \Carbon\Carbon::parse($list->cert_date) : null;
                @endphp
                <tr>
                    <td class="fw-medium">{{ $list->cert_title ?: '—' }}</td>
                    <td class="text-nowrap">{{ $certDate?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <span class="cert-cell-truncate d-inline-block" title="{{ $list->cert_address }}">
                            {{ $list->cert_address ?: '—' }}
                        </span>
                    </td>
                    <td class="text-nowrap">{{ $list->cert_speaker ?: '—' }}</td>
                    <td>
                        @if($list->cert_file)
                            <embed src="{{ '/file/get/certificate/'.$list->cert_file }}" style="max-width: 100%; height: 150px;">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="cert-empty-state">
                            <i class="ti ti-certificate-off" aria-hidden="true"></i>
                            No certificates or trainings on record.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Mobile cards (visible on xs/sm only) ── --}}
    <div class="d-md-none p-3">
        @forelse($certificates as $list)
        @php
            $certFmt = $list->cert_date ? \Carbon\Carbon::parse($list->cert_date)->format('M d, Y') : null;
        @endphp
        <div class="cert-card-mobile">
            <div class="cert-card-mobile-header">
                <span class="cert-title">{{ $list->cert_title ?: '—' }}</span>
            </div>
            <div class="cert-card-mobile-body">
                @if($certFmt)
                    <div><i class="ti ti-calendar" aria-hidden="true"></i>{{ $certFmt }}</div>
                @endif
                @if($list->cert_address)
                    <div><i class="ti ti-map-pin" aria-hidden="true"></i>{{ $list->cert_address }}</div>
                @endif
                @if($list->cert_speaker)
                    <div><i class="ti ti-microphone" aria-hidden="true"></i>{{ $list->cert_speaker }}</div>
                @endif
                @if($list->cert_file)
                    <div><embed src="{{ '/file/get/certificate/'.$list->cert_file }}" style="max-width: 100%; height: 120px;"></div>
                @endif
            </div>
        </div>
        @empty
        <div class="cert-empty-state">
            <i class="ti ti-certificate-off" aria-hidden="true"></i>
            No certificates or trainings on record.
        </div>
        @endforelse
    </div>
</div>

@stop