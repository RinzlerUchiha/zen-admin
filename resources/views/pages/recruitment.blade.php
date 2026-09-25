@extends('layouts.layout')

@section('content')
    {{-- HireFlow page: the shared HireFlow look, scoped to this wrapper. --}}
    @include('partials.hireflow-theme')
    <div class="hf-theme">
<style>
    :root {
        --my-top-space: calc(var(--main-top-margin) + .25rem);
    }

    /* ===== Collapsible rail =====
       The rail keeps Bootstrap's row/col gutters; only its width is taken
       over, and only from md up, where the two columns sit side by side.
       Below that breakpoint the columns already stack and the rail becomes a
       horizontal strip, so the collapse has nothing to do. */
    #hf-shell {
        --hf-rail-w: 16.66666667%;
    }

    @media (min-width: 992px) {
        #hf-shell {
            --hf-rail-w: 232px;
            flex-wrap: nowrap;
        }

        #hf-shell[data-collapsed="1"] {
            --hf-rail-w: 68px;
        }

        #hf-shell > #hf-rail {
            flex: 0 0 var(--hf-rail-w);
            width: var(--hf-rail-w);
            max-width: var(--hf-rail-w);
            transition: flex-basis .18s ease, width .18s ease, max-width .18s ease;
        }

        #hf-shell > #hf-main {
            flex: 1 1 auto;
            width: auto;
            max-width: none;
            min-width: 0;
        }
    }

    #hf-nav-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-sm);
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: 6px 10px;
        margin-bottom: 4px;
        border-radius: var(--zn-radius-lg);
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }

    #hf-nav-toggle:hover {
        background: var(--zn-surface-2);
        color: var(--zn-ink);
    }

    #hf-nav-toggle:focus-visible {
        outline: none;
        border-color: var(--zn-accent);
    }

    #hf-nav-toggle .hf-toggle-ico {
        flex-shrink: 0;
        width: 30px;
        display: inline-flex;
        justify-content: center;
        font-size: var(--zn-fs);
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

    /* an item that stands outside the numbered pipeline */
    .rc-item-plain::before {
        display: none;
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

    /* ===== Collapsed: icons only, still clickable, named on hover ===== */
    @media (min-width: 992px) {
        #hf-shell[data-collapsed="1"] #page-tabs {
            padding-left: 8px;
            padding-right: 8px;
            align-items: stretch;
        }

        #hf-shell[data-collapsed="1"] .rc-group {
            /* the group heading has no icon to fall back to */
            height: 0;
            padding: 0;
            overflow: hidden;
            visibility: hidden;
        }

        #hf-shell[data-collapsed="1"] .rc-text,
        #hf-shell[data-collapsed="1"] .rc-soon,
        #hf-shell[data-collapsed="1"] #hf-nav-toggle .hf-toggle-text {
            display: none;
        }

        #hf-shell[data-collapsed="1"] .rc-link {
            justify-content: center;
            gap: 0;
            padding: 8px 6px;
        }

        /* the step number rides the icon instead of taking its own column */
        #hf-shell[data-collapsed="1"] .rc-step {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 15px;
            height: 15px;
            font-size: 10px;
            background: var(--zn-surface);
            border-width: 1.5px;
        }

        /* the pipeline rail follows the icons to the middle */
        #hf-shell[data-collapsed="1"] .rc-item::before {
            left: 50%;
            margin-left: -1px;
        }

        #hf-shell[data-collapsed="1"] .rc-link.active::after {
            left: -4px;
        }

        #hf-shell[data-collapsed="1"] #hf-nav-toggle {
            justify-content: center;
            padding-left: 6px;
            padding-right: 6px;
        }
    }

    @media (max-width: 991.98px) {
        #page-tabs {
            height: auto;
            max-height: none;
            position: static;
            border-right: none;
            border-bottom: 1px solid var(--zn-line);
        }

        /* stacked layout: there is no narrow rail to collapse */
        #hf-nav-toggle {
            display: none;
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

<script type="text/javascript">
    /**
     * Collapse/expand for the HireFlow rail.
     *
     * Collapsed, every item keeps its icon and stays clickable; the label it
     * loses comes back as a tooltip, so nothing becomes unidentifiable. The
     * choice is remembered per browser. Everything here is scoped to
     * #hf-shell, which exists only on HireFlow pages.
     */
    $(function () {
        const STORE_KEY = 'hf.nav.collapsed';
        const shell = document.getElementById('hf-shell');
        const toggle = document.getElementById('hf-nav-toggle');
        if (!shell || !toggle) { return; }

        const links = Array.from(shell.querySelectorAll('#page-tabs .rc-link'));
        const tipTargets = links.concat([toggle]);
        const hasBootstrapTips = typeof bootstrap !== 'undefined' && bootstrap.Tooltip;

        // The label each item shows when there is room for it.
        links.forEach(function (link) {
            const text = link.querySelector('.rc-text');
            link.dataset.hfLabel = text ? text.textContent.trim() : '';
            if (link.classList.contains('rc-disabled')) {
                link.dataset.hfLabel += ' — coming soon';
            }
        });

        function clearTip(el) {
            if (hasBootstrapTips) {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) { tip.dispose(); }
            }
            el.removeAttribute('title');
        }

        function applyTips(collapsed) {
            tipTargets.forEach(clearTip);
            if (!collapsed) { return; }

            links.forEach(function (link) {
                link.setAttribute('title', link.dataset.hfLabel);
            });
            toggle.setAttribute('title', 'Expand menu');

            if (!hasBootstrapTips) { return; } // native title tooltips still work

            tipTargets.forEach(function (el) {
                new bootstrap.Tooltip(el, { placement: 'right', container: 'body' });
            });
        }

        function render(collapsed) {
            shell.setAttribute('data-collapsed', collapsed ? '1' : '0');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('aria-label', collapsed ? 'Expand menu' : 'Collapse menu');
            toggle.querySelector('.hf-toggle-ico i').className =
                collapsed ? 'fa fa-angles-right' : 'fa fa-angles-left';
            applyTips(collapsed);
        }

        // The inline script above already set the attribute; start from it so
        // the two never disagree.
        let collapsed = shell.getAttribute('data-collapsed') === '1';
        render(collapsed);

        toggle.addEventListener('click', function () {
            collapsed = !collapsed;
            render(collapsed);
            try { localStorage.setItem(STORE_KEY, collapsed ? '1' : '0'); } catch (e) { /* not fatal */ }
        });
    });
</script>

{{-- Recruitment pipeline icon set (inline sprite; inherits currentColor) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <g id="ri-base" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
            stroke-linejoin="round"></g>
    </defs>
    <symbol id="ri-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <rect x="4" y="4" width="7" height="6" rx="1.2" />
        <rect x="4" y="13" width="7" height="7" rx="1.2" />
        <rect x="14" y="4" width="6" height="10" rx="1.2" />
        <rect x="14" y="17" width="6" height="3" rx="1.2" />
    </symbol>
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

<div class="row pt-1" id="hf-shell" data-collapsed="0">
    {{-- Restore the rail's width before first paint, so a collapsed rail does
         not flash wide and snap shut on every page load. --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('hf.nav.collapsed') === '1') {
                    document.getElementById('hf-shell').setAttribute('data-collapsed', '1');
                }
            } catch (e) { /* storage blocked: stay expanded */ }
        })();
    </script>

    <div class="col-md-2" id="hf-rail">
        <ul class="nav flex-column" id="page-tabs">
            <li class="nav-item">
                <button type="button" id="hf-nav-toggle" aria-controls="page-tabs" aria-expanded="true">
                    <span class="hf-toggle-ico"><i class="fa fa-angles-left" aria-hidden="true"></i></span>
                    <span class="hf-toggle-text">Collapse</span>
                </button>
            </li>

            <li class="nav-item rc-item rc-item-plain">
                <a href="{{ url('/recruitment/dashboard') }}"
                    class="rc-link {{ $maincat == 'dashboard' ? 'active' : '' }}">
                    <span class="rc-ico"><svg><use href="#ri-dashboard" /></svg></span>
                    <span class="rc-text">Dashboard</span>
                </a>
            </li>

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

    <div class="col-md-10" id="hf-main">
        <div class="row">
            <div class="col-12">
                @includeIf($page ?? '')
            </div>
        </div>
    </div>
</div>
    </div>{{-- /.hf-theme --}}
@stop