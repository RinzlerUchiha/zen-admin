@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #form-personal {
        font-size: 16px;
    }

    /* ── Section cards with left border ───────────────────────── */
    .info-section {
        border-left: 3px solid #0d6efd;
        padding-left: 1rem;
        margin-bottom: 1.75rem;
    }

    .info-section > h6 {
        color: #0d6efd;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .65rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    /* ── Field label / value ───────────────────────────────────── */
    .field-label {
        color: #6c757d;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 1px;
    }

    .field-value {
        font-weight: 600;
        min-height: 1.1em;
        word-break: break-word;
    }

    /* ── Copy button ────────────────────────────────────────────── */
    .btn-copy {
        padding: 0 3px;
        border: none;
        background: transparent;
        color: #adb5bd;
        cursor: pointer;
        font-size: 11px;
        vertical-align: middle;
        transition: color .15s;
    }

    .btn-copy:hover { color: #0d6efd; }

    /* ── Mask toggle ────────────────────────────────────────────── */
    .btn-mask-toggle {
        padding: 0 4px;
        border: none;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
        font-size: 10px;
        vertical-align: middle;
        transition: color .15s;
    }

    .btn-mask-toggle:hover { color: #0d6efd; }

    .masked { letter-spacing: 1px; color: #adb5bd; }

    /* ── Status badge ───────────────────────────────────────────── */
    .status-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 2px 8px;
        border-radius: 20px;
    }

    /* ── Accordion tweaks ───────────────────────────────────────── */
    .info-accordion {
        --bs-accordion-bg: #e9ecf3;
    }

    .info-accordion .accordion-button {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        background: transparent;
        box-shadow: none;
        padding: 6px 0;
    }

    .info-accordion .accordion-button:not(.collapsed) {
        color: #0d6efd;
        background: transparent;
    }

    .info-accordion .accordion-button::after {
        width: 12px;
        height: 12px;
        background-size: 12px;
    }

    .info-accordion .accordion-item {
        border: none;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-accordion .accordion-body {
        padding: 6px 0 12px 0;
    }

    /* ── Copy toast ─────────────────────────────────────────────── */
    #copy-toast {
        z-index: 9999;
        display: none;
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
    }

    /* ── Print ──────────────────────────────────────────────────── */
    @media print {
        #sidebar,
        .btn-copy,
        .btn-mask-toggle,
        #btn-print,
        nav,
        .offcanvas,
        .dropdown,
        button:not(.accordion-button),
        #hireModal { display: none !important; }

        .info-section { border-left-color: #000 !important; page-break-inside: avoid; }
        .info-accordion .accordion-collapse { display: block !important; }
        body, #form-personal { font-size: 10px; }
        .masked { color: #000 !important; letter-spacing: normal; }
    }
</style>

<div class="container-fluid">
    <div id="form-personal" class="mb-3">

        {{-- ── Header row: photo · name · status · print ──────────── --}}
        <div class="d-flex align-items-start gap-3 mb-4">

            <img id="personal-img-preview"
                 src="{{ $applicant?->app_img ? config('app.url').'/file/app-img/'.$applicant?->app_img : asset('no-file.png') }}"
                 class="img-thumbnail object-fit-contain flex-shrink-0"
                 alt="Applicant photo"
                 style="height:70px; width:70px;">

            <div class="flex-grow-1 min-w-0">
                {{-- Name --}}
                <div class="fw-bold fs-6 mb-1">
                    {{ trim(collect([
                        $applicant?->app_fname,
                        $applicant?->app_mname,
                        $applicant?->app_lname,
                    ])->filter()->implode(' ')) ?: '—' }}
                    @if($applicant?->app_suffix)
                        <span class="text-muted fw-normal" style="font-size:16px;">{{ $applicant->app_suffix }}</span>
                    @endif
                </div>

                {{-- Position applied --}}
                <div class="text-muted mb-1" style="font-size:14px;">
                    Applied for: <strong>{{ $applicant?->app_posapplied ?: '—' }}</strong>
                </div>

                {{-- Status badge --}}
                @php
                    $status = strtolower($applicant?->app_status ?? '');
                    $badgeCss = match($status) {
                        'active'   => 'background:#d1fae5; color:#065f46;',
                        'hired'    => 'background:#dbeafe; color:#1e40af;',
                        'inactive' => 'background:#f3f4f6; color:#6b7280;',
                        default    => 'background:#fef9c3; color:#92400e;',
                    };
                @endphp
                <span class="status-badge" style="{{ $badgeCss }}">
                    {{ $status ? ucfirst($status) : 'Pending' }}
                </span>

                {{-- Record date --}}
                @if($applicant?->app_date)
                    <div class="text-muted mt-1" style="font-size:12px;">
                        Record created: {{ \Carbon\Carbon::parse($applicant->app_date)->format('M d, Y') }}
                    </div>
                @endif
            </div>

            <button id="btn-print"
                    type="button"
                    class="btn btn-sm btn-outline-secondary flex-shrink-0"
                    onclick="window.print()"
                    title="Print profile">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>

        {{-- ── Contact Info ─────────────────────────────────────────── --}}
        <div class="info-section">
            <h6><i class="bi bi-person-lines-fill"></i> Contact Info</h6>
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="field-label">Email</div>
                    <div class="field-value">
                        {{ $applicant?->app_email ?: '—' }}
                        @if($applicant?->app_email)
                            <button class="btn-copy"
                                    onclick="copyText('{{ e($applicant->app_email) }}')"
                                    title="Copy email">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="field-label">Personal Contact</div>
                    <div class="field-value">
                        {{ $applicant?->app_mobile ?: '—' }}
                        @if($applicant?->app_mobile)
                            <button class="btn-copy"
                                    onclick="copyText('{{ e($applicant->app_mobile) }}')"
                                    title="Copy number">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-lg">
                    <div class="field-label">Telephone</div>
                    <div class="field-value">
                        {{ $applicant?->app_telephone ?: '—' }}
                        @if($applicant?->app_telephone)
                            <button class="btn-copy"
                                    onclick="copyText('{{ e($applicant->app_telephone) }}')"
                                    title="Copy number">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Address (accordion) ─────────────────────────────────── --}}
        <div class="info-section">
            <h6><i class="bi bi-geo-alt-fill"></i> Address</h6>
            <div class="accordion info-accordion" id="accordionAddress">

                {{-- Permanent --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#addrPerm">
                            Permanent Address
                        </button>
                    </h2>
                    <div id="addrPerm" class="accordion-collapse collapse show"
                         data-bs-parent="#accordionAddress">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <div class="field-label">Province</div>
                                    <div class="field-value">{{ $applicant?->address?->add_perm_prov ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">City / Municipality</div>
                                    <div class="field-value">{{ $applicant?->address?->add_perm_city ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">Barangay</div>
                                    <div class="field-value">{{ $applicant?->address?->add_perm_brngy ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">Street / House #</div>
                                    <div class="field-value">{{ $applicant?->address?->add_perm_location ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Current --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#addrCur">
                            Current Address
                        </button>
                    </h2>
                    <div id="addrCur" class="accordion-collapse collapse"
                         data-bs-parent="#accordionAddress">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <div class="field-label">Province</div>
                                    <div class="field-value">{{ $applicant?->address?->add_cur_prov ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">City / Municipality</div>
                                    <div class="field-value">{{ $applicant?->address?->add_cur_city ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">Barangay</div>
                                    <div class="field-value">{{ $applicant?->address?->add_cur_brngy ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">Street / House #</div>
                                    <div class="field-value">{{ $applicant?->address?->add_cur_location ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Birth --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#addrBirth">
                            Place of Birth
                        </button>
                    </h2>
                    <div id="addrBirth" class="accordion-collapse collapse"
                         data-bs-parent="#accordionAddress">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <div class="field-label">Province</div>
                                    <div class="field-value">{{ $applicant?->address?->add_birth_prov ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">City / Municipality</div>
                                    <div class="field-value">{{ $applicant?->address?->add_birth_city ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">Barangay</div>
                                    <div class="field-value">{{ $applicant?->address?->add_birth_brngy ?: '—' }}</div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="field-label">Street / House #</div>
                                    <div class="field-value">{{ $applicant?->address?->add_birth_location ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Basic Info ───────────────────────────────────────────── --}}
        <div class="info-section">
            <h6><i class="bi bi-info-circle-fill"></i> Basic Info</h6>
            <div class="row g-3">
                <div class="col-lg-auto">
                    <div class="field-label">Birth Date</div>
                    <div class="field-value">{{ $applicant?->app_bdate ?: '—' }}</div>
                </div>
                <div class="col-lg-1">
                    <div class="field-label">Age</div>
                    <div class="field-value" id="computed-age">
                        {{-- Server-side fallback; JS overwrites this below --}}
                        @if($applicant?->app_bdate)
                            {{ \Carbon\Carbon::parse($applicant->app_bdate)->age }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="field-label">Civil Status</div>
                    <div class="field-value">{{ $applicant?->app_cstatus ?: '—' }}</div>
                </div>
                <div class="col-lg-auto">
                    <div class="field-label">Sex</div>
                    <div class="field-value">{{ $applicant?->app_sex ?: '—' }}</div>
                </div>
                <div class="col-lg-auto">
                    <div class="field-label">Blood Type</div>
                    <div class="field-value">{{ $applicant?->app_btype ?: '—' }}</div>
                </div>
                <div class="col-lg-2">
                    <div class="field-label">Height (cm)</div>
                    <div class="field-value">{{ $applicant?->app_height ?: '—' }}</div>
                </div>
                <div class="col-lg-2">
                    <div class="field-label">Weight (kg)</div>
                    <div class="field-value">{{ $applicant?->app_weight ?: '—' }}</div>
                </div>
                <div class="col-lg-auto">
                    <div class="field-label">Nationality</div>
                    <div class="field-value">{{ $applicant?->app_nationality ?: '—' }}</div>
                </div>
                <div class="col-lg-auto">
                    <div class="field-label">Religion</div>
                    <div class="field-value">{{ $applicant?->app_religion ?: '—' }}</div>
                </div>
                <div class="col-lg-auto">
                    <div class="field-label">Dialect</div>
                    <div class="field-value">{{ $applicant?->app_dialect ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- ── Government IDs ───────────────────────────────────────── --}}
        <div class="info-section">
            <h6>
                <i class="bi bi-shield-lock-fill"></i> Government IDs
                <button class="btn-mask-toggle"
                        id="gov-toggle-btn"
                        onclick="toggleGovIds()"
                        title="Show / hide all IDs">
                    <i class="bi bi-eye-slash" id="gov-toggle-icon"></i>
                    <span id="gov-toggle-label">Show</span>
                </button>
            </h6>
            <div class="row g-3">
                @foreach([
                    ['SSS #',        $applicant?->app_sss,        'gov-sss'],
                    ['Pagibig #',     $applicant?->app_pagibig,    'gov-pagibig'],
                    ['Philhealth #',  $applicant?->app_philhealth, 'gov-philhealth'],
                    ['TIN #',         $applicant?->app_tin,        'gov-tin'],
                ] as [$label, $raw, $govId])
                    <div class="col-lg-3">
                        <div class="field-label">{{ $label }}</div>
                        <div class="field-value">
                            @if($raw)
                                {{-- Show last 3 chars, mask the rest --}}
                                <span id="{{ $govId }}"
                                      class="masked"
                                      data-real="{{ $raw }}"
                                      data-masked="{{ str_repeat('●', max(0, strlen($raw) - 3)) . substr($raw, -3) }}">
                                    {{ str_repeat('●', max(0, strlen($raw) - 3)) . substr($raw, -3) }}
                                </span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /form-personal --}}
</div>

{{-- ── Copy toast ──────────────────────────────────────────────────── --}}
<div id="copy-toast">
    <div class="toast show align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body py-2">
                <i class="bi bi-check-circle me-1"></i> Copied to clipboard
            </div>
        </div>
    </div>
</div>

<script>
// ── Copy to clipboard ──────────────────────────────────────────────────
function copyText(text) {
    const done = () => {
        const t = document.getElementById('copy-toast');
        t.style.display = 'block';
        setTimeout(() => t.style.display = 'none', 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done);
    } else {
        const el = document.createElement('textarea');
        el.value = text;
        el.style.position = 'fixed';
        el.style.opacity  = '0';
        document.body.appendChild(el);
        el.focus(); el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        done();
    }
}

// ── Government ID masking ──────────────────────────────────────────────
let govVisible = false;
const govIds   = ['gov-sss', 'gov-pagibig', 'gov-philhealth', 'gov-tin'];

function toggleGovIds() {
    govVisible = !govVisible;
    govIds.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = govVisible ? el.dataset.real : el.dataset.masked;
        el.classList.toggle('masked', !govVisible);
    });
    document.getElementById('gov-toggle-icon').className =
        govVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
    document.getElementById('gov-toggle-label').textContent =
        govVisible ? 'Hide' : 'Show';
}

// ── Live age calculation ───────────────────────────────────────────────
@if($applicant?->app_bdate)
(function () {
    const bdate = new Date('{{ $applicant->app_bdate }}');
    const now   = new Date();
    let age = now.getFullYear() - bdate.getFullYear();
    const m = now.getMonth() - bdate.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < bdate.getDate())) age--;
    document.getElementById('computed-age').textContent = age;
}());
@endif
</script>

@stop