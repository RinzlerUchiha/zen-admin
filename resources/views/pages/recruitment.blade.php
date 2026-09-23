@extends('layouts.layout')

@section('content')
    {{-- HireFlow page: the shared HireFlow look, scoped to this wrapper. --}}
    @include('partials.hireflow-theme')
    <div class="hf-theme">
<style>
    :root {
        --my-top-space: calc(var(--main-top-margin) + .25rem);
    }

    #page-tabs {
        width: 100%;
        height: calc(100vh - var(--my-top-space));
        max-height: calc(100vh - var(--my-top-space));
        flex-wrap: nowrap;
        position: sticky;
        top: var(--my-top-space);
        border-right: 1px solid var(--zn-line);
        overflow: auto;
        padding: 10px 8px 20px;
        gap: 2px;
    }

    #page-tabs::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    #page-tabs::-webkit-scrollbar-thumb {
        background: var(--zn-line-2);
        border-radius: var(--zn-radius-lg);
    }

    .rc-group {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        padding: 6px 10px 8px;
    }

    .rc-item {
        position: relative;
    }

    /* vertical rail that ties the steps into one pipeline */
    .rc-item::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--zn-line);
    }

    .rc-item:first-of-type::before {
        top: 50%;
    }

    .rc-item:last-of-type::before {
        bottom: 50%;
    }

    .rc-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: var(--zn-radius-lg);
        font-size: var(--zn-fs-ui);
        font-weight: 600;
        color: var(--zn-ink-2);
        text-decoration: none;
        transition: background .15s ease, color .15s ease;
    }

    .rc-link:hover {
        background: var(--zn-surface-2);
        color: var(--zn-ink);
    }

    .rc-step {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--zn-surface);
        border: 2px solid var(--zn-line-2);
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .rc-ico {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        color: var(--zn-ink-2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .15s ease, color .15s ease;
    }

    .rc-ico svg {
        width: 17px;
        height: 17px;
    }

    .rc-text {
        flex: 1;
        line-height: 1.25;
    }

    .rc-link.active {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    .rc-link.active::after {
        content: '';
        position: absolute;
        left: -8px;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: var(--bs-primary, var(--zn-accent));
    }

    .rc-link.active .rc-ico {
        background: var(--bs-primary, var(--zn-accent));
        color: var(--zn-on-accent);
    }

    .rc-link.active .rc-step {
        border-color: var(--bs-primary, var(--zn-accent));
        color: var(--bs-primary, var(--zn-accent));
    }

    .rc-link.rc-disabled {
        color: var(--zn-ink-3);
        cursor: default;
        pointer-events: none;
    }

    .rc-link.rc-disabled .rc-ico {
        background: var(--zn-surface-2);
        color: var(--zn-line-2);
    }

    .rc-link.rc-disabled .rc-step {
        border-color: var(--zn-line);
        color: var(--zn-line-2);
    }

    .rc-soon {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        background: var(--zn-surface-2);
        border-radius: 20px;
        padding: 2px 7px;
    }

    @media (max-width: 991.98px) {
        #page-tabs {
            height: auto;
            max-height: none;
            position: static;
            border-right: none;
            border-bottom: 1px solid var(--zn-line);
        }
    }
</style>

<script type="text/javascript">
    $(function(){
        const link_item = $("#page-tabs a[href='{{ url('/recruitment/'.$maincat) }}']").parent()[0];
        if(link_item){
            link_item.scrollIntoView({
                block: 'center'
            });
        }
    });
</script>

{{-- Recruitment pipeline icon set (inline sprite; inherits currentColor) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <g id="ri-base" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
            stroke-linejoin="round"></g>
    </defs>
    <symbol id="ri-manpower" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1Z" />
        <path d="M16 5h2a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h2" />
        <circle cx="12" cy="11" r="2" />
        <path d="M8.5 17c0-1.7 1.6-3 3.5-3s3.5 1.3 3.5 3" />
    </symbol>
    <symbol id="ri-job-postings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 10.5v3a1 1 0 0 0 1 1h2.5l6.5 3.5v-15L7.5 9.5H5a1 1 0 0 0-1 1Z" />
        <path d="M17.5 9.5a4 4 0 0 1 0 5" />
        <path d="M7.5 14.5V18a1.5 1.5 0 0 0 3 0v-1.6" />
    </symbol>
    <symbol id="ri-applicant-intake" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 14h4l1.2 2h5.6l1.2-2h4" />
        <path d="M4 14 6.2 6.6A1 1 0 0 1 7.2 6h9.6a1 1 0 0 1 .95.6L20 14v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4Z" />
    </symbol>
    <symbol id="ri-screening" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="10" cy="8" r="3" />
        <path d="M4.5 19c0-3 2.5-5 5.5-5 .9 0 1.8.2 2.5.5" />
        <circle cx="16.5" cy="16.5" r="3" />
        <path d="m19 19 2 2" />
    </symbol>
    <symbol id="ri-interview" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 6a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H6l-3 2.5V6Z" />
        <path d="M11 13h9a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4l-3 2.5V19h-2a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1Z" />
    </symbol>
    <symbol id="ri-onboarding" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 3h5a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-5" />
        <path d="M4 12h10" />
        <path d="m10.5 8.5 3.5 3.5-3.5 3.5" />
    </symbol>
    <symbol id="ri-probation" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <rect x="4" y="5" width="16" height="15" rx="1.5" />
        <path d="M4 10h16M9 3v4M15 3v4" />
        <path d="m9.5 14.5 2 2 3.5-3.5" />
    </symbol>
</svg>

<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="page-tabs">
            <li class="rc-group">Hiring Pipeline</li>

            <li class="nav-item rc-item">
                <a href="{{ url('/recruitment/manpower') }}"
                    class="rc-link {{ $maincat == 'manpower' ? 'active' : '' }}">
                    <span class="rc-step">1</span>
                    <span class="rc-ico"><svg><use href="#ri-manpower" /></svg></span>
                    <span class="rc-text">Manpower Request</span>
                </a>
            </li>

            <li class="nav-item rc-item">
                <a href="{{ url('/recruitment/job-postings') }}"
                    class="rc-link {{ $maincat == 'job-postings' ? 'active' : '' }}">
                    <span class="rc-step">2</span>
                    <span class="rc-ico"><svg><use href="#ri-job-postings" /></svg></span>
                    <span class="rc-text">Job Postings</span>
                </a>
            </li>

            <li class="nav-item rc-item">
                <a href="{{ url('/recruitment/applicant-intake') }}"
                    class="rc-link {{ $maincat == 'applicant-intake' ? 'active' : '' }}">
                    <span class="rc-step">3</span>
                    <span class="rc-ico"><svg><use href="#ri-applicant-intake" /></svg></span>
                    <span class="rc-text">Applicant Intake</span>
                </a>
            </li>

            <li class="nav-item rc-item">
                <span class="rc-link rc-disabled">
                    <span class="rc-step">4</span>
                    <span class="rc-ico"><svg><use href="#ri-screening" /></svg></span>
                    <span class="rc-text">Screening</span>
                    <span class="rc-soon">Soon</span>
                </span>
            </li>

            <li class="nav-item rc-item">
                <span class="rc-link rc-disabled">
                    <span class="rc-step">5</span>
                    <span class="rc-ico"><svg><use href="#ri-interview" /></svg></span>
                    <span class="rc-text">Interview / Offer</span>
                    <span class="rc-soon">Soon</span>
                </span>
            </li>

            <li class="nav-item rc-item">
                <span class="rc-link rc-disabled">
                    <span class="rc-step">6</span>
                    <span class="rc-ico"><svg><use href="#ri-onboarding" /></svg></span>
                    <span class="rc-text">Onboarding</span>
                    <span class="rc-soon">Soon</span>
                </span>
            </li>

            <li class="nav-item rc-item">
                <span class="rc-link rc-disabled">
                    <span class="rc-step">7</span>
                    <span class="rc-ico"><svg><use href="#ri-probation" /></svg></span>
                    <span class="rc-text">Probation</span>
                    <span class="rc-soon">Soon</span>
                </span>
            </li>
        </ul>
    </div>

    <div class="col-md-10">
        <div class="row">
            <div class="col-12">
                @includeIf($page ?? '')
            </div>
        </div>
    </div>
</div>
    </div>{{-- /.hf-theme --}}
@stop