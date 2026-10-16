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
        /* expanded: chevron sits at the rail's edge, pointing the way it closes */
        justify-content: flex-end;
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        color: var(--zn-ink-3);
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
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        /* CSS turns the chevron round; no script rewrites an icon class, so
           Font Awesome's <i> replacement cannot affect it. */
        transition: transform .18s ease;
    }

    #hf-nav-toggle .hf-toggle-ico svg {
        width: 18px;
        height: 18px;
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

    /*
    | The Job Postings step.
    |
    | Given slightly more presence than its neighbours and a count of what is
    | waiting, because it is the step with work queued behind it; the others
    | are places you go, this is a place with a number attached. Deliberately
    | the only change to the sidebar -- the pipeline's shape is unaltered.
    */
    .rc-count {
        margin-left: auto;
        min-width: 20px;
        padding: 1px 7px;
        border-radius: 999px;
        background: var(--zn-accent);
        color: var(--zn-on-accent);
        font-size: var(--zn-fs-xs);
        font-weight: 800;
        line-height: 1.5;
        text-align: center;
        flex: none;
    }

    .rc-link.active .rc-count {
        background: var(--zn-on-accent);
        color: var(--zn-accent-dark);
    }

    /* Collapsed rail: the number still shows, as a dot on the icon. */
    #hf-shell[data-collapsed="1"] .rc-count {
        position: absolute;
        top: 4px;
        right: 4px;
        min-width: 16px;
        padding: 0 4px;
        font-size: 10px;
    }
    #hf-shell[data-collapsed="1"] .rc-link { position: relative; }

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

    /*
    | The three places under Applicants.
    |
    | Indented to clear the pipeline rail rather than to decorate: the rail
    | runs at 18px and these sit to the right of it, so the group reads as
    | belonging to step 3 instead of as four separate steps.
    */
    .rc-sub {
        list-style: none;
        margin: 2px 0 6px;
        padding: 0 0 0 38px;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .rc-sublink {
        display: block;
        padding: 5px 10px;
        border-radius: var(--zn-radius);
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        color: var(--zn-ink-3);
        text-decoration: none;
        transition: background .15s ease, color .15s ease;
    }

    .rc-sublink:hover { background: var(--zn-surface-2); color: var(--zn-ink); }
    .rc-sublink.is-on { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .rc-sublink:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: 2px; }

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
        #hf-shell[data-collapsed="1"] .rc-soon {
            display: none;
        }

        /* Collapsed there is nothing but icons, and these have none: the
           parent icon stays and still opens the register. */
        #hf-shell[data-collapsed="1"] .rc-sub {
            display: none;
        }

        #hf-shell[data-collapsed="1"] #hf-nav-toggle .hf-toggle-ico {
            transform: rotate(180deg);
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

        /* the rail is narrow here, so the tooltip is the only label left */
        #hf-shell[data-collapsed="1"] #hf-nav-toggle .hf-toggle-ico {
            width: auto;
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

            // The toggle is icon-only in both states, so it always needs a name.
            const toggleLabel = collapsed ? 'Expand menu' : 'Collapse menu';
            toggle.setAttribute('title', toggleLabel);

            // The rail items only lose their label when the rail is collapsed.
            if (collapsed) {
                links.forEach(function (link) {
                    link.setAttribute('title', link.dataset.hfLabel);
                });
            }

            if (!hasBootstrapTips) { return; } // native title tooltips still work

            (collapsed ? tipTargets : [toggle]).forEach(function (el) {
                new bootstrap.Tooltip(el, { placement: 'right', container: 'body' });
            });
        }

        function render(collapsed) {
            shell.setAttribute('data-collapsed', collapsed ? '1' : '0');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('aria-label', collapsed ? 'Expand menu' : 'Collapse menu');
            // The chevron is turned by CSS off [data-collapsed]; nothing here
            // reaches into the icon, so Font Awesome swapping <i> for <svg>
            // cannot break this.
            applyTips(collapsed);
        }

        let collapsed = shell.getAttribute('data-collapsed') === '1';

        // Bound before the first render on purpose: if rendering ever throws,
        // the button must still work rather than being left inert.
        toggle.addEventListener('click', function () {
            collapsed = !collapsed;
            try { localStorage.setItem(STORE_KEY, collapsed ? '1' : '0'); } catch (e) { /* not fatal */ }
            render(collapsed);
        });

        // The inline script above already set the attribute; start from it so
        // the two never disagree.
        render(collapsed);
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
    <symbol id="ri-work-queue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
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
    {{-- An offer: a document with terms on it, and a signature line. --}}
    <symbol id="ri-offer" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7z" />
        <path d="M14 3v4h4" />
        <path d="M9 12h6" />
        <path d="M9 16.5c1.5-1 3-1 4.5 0s2 .5 2 .5" />
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
                <button type="button" id="hf-nav-toggle" aria-controls="page-tabs"
                    aria-expanded="true" aria-label="Collapse menu" title="Collapse menu">
                    <span class="hf-toggle-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m14 6-6 6 6 6" />
                            <path d="M19 6v12" />
                        </svg>
                    </span>
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
                    @if (($rcWaitingToPost ?? 0) > 0)
                        {{-- How many jobs are waiting to be posted: the thing
                             this step is for. Counted by job, so one position
                             wanted by two requests is one item here. --}}
                        <span class="rc-count"
                              title="{{ $rcWaitingToPost }} {{ \Illuminate\Support\Str::plural('job', $rcWaitingToPost) }} waiting to be posted">{{ $rcWaitingToPost }}</span>
                    @endif
                </a>
            </li>

            {{--
                Applicants: the people, in one place.

                Step 3 was "Applicant Intake", which named one of the three
                things HR does with applicants and hid the other two. The
                register was reachable only from outside the pipeline and the
                Candidate Pool only as a filter chip, so "who have we got?"
                and "who have we already met?" had no home.

                Intake is unchanged — same page, same filters, same acquisition
                form and entry points. It has simply stopped being the only
                door.
            --}}
            <li class="nav-item rc-item">
                <a href="{{ url('/applicant/list') }}"
                    class="rc-link {{ in_array($maincat ?? '', ['applicants', 'applicant-intake', 'candidate-pool'], true) ? 'active' : '' }}">
                    <span class="rc-step">3</span>
                    <span class="rc-ico"><svg><use href="#ri-applicant-intake" /></svg></span>
                    <span class="rc-text">Applicants</span>
                </a>
                <ul class="rc-sub">
                    <li>
                        <a class="rc-sublink {{ ($maincat ?? '') == 'applicants' ? 'is-on' : '' }}"
                            href="{{ url('/applicant/list') }}">Register</a>
                    </li>
                    <li>
                        <a class="rc-sublink {{ ($maincat ?? '') == 'applicant-intake' ? 'is-on' : '' }}"
                            href="{{ url('/recruitment/applicant-intake') }}">Intake</a>
                    </li>
                    {{--
                        Matching, which the process calls for between applying
                        and the Initial Interview.

                        It was reachable only by opening a posting and finding
                        a link inside its detail panel, so a named step of the
                        process was effectively hidden. Matching is always
                        ABOUT a posting, so this goes to the picker rather
                        than to a screen that would not know which one.
                    --}}
                    <li>
                        <a class="rc-sublink {{ ($maincat ?? '') == 'matching' ? 'is-on' : '' }}"
                            href="{{ url('/recruitment/postings/matching') }}">Matching</a>
                    </li>
                    <li>
                        <a class="rc-sublink {{ ($maincat ?? '') == 'candidate-pool' ? 'is-on' : '' }}"
                            href="{{ url('/recruitment/candidate-pool') }}">Candidate Pool</a>
                    </li>
                </ul>
            </li>

            {{--
                The work queue.

                It was called Screening, which made a work list look like the
                fourth stage of the pipeline. It never was one: it changes no
                application's status and moves nothing along. What it answers
                is "what should I do next", which is a question about HR's
                day, not about where an application has got to.

                The queue itself is unchanged by the rename.
            --}}
            <li class="nav-item rc-item">
                <a href="{{ url('/recruitment/work-queue') }}"
                    class="rc-link {{ $maincat == 'work-queue' ? 'active' : '' }}">
                    <span class="rc-step">4</span>
                    <span class="rc-ico"><svg><use href="#ri-work-queue" /></svg></span>
                    <span class="rc-text">Work Queue</span>
                </a>
            </li>

            {{--
                Interviews, now a real place to go.

                It was one disabled "Interview / Offer" item, which said two
                untrue things at once: that interviews were not built -- the
                engine is in daily use -- and that they are the same step as
                Offer, which genuinely is not built. Offer gets its own item
                when the navigation is restructured; this batch only stops
                hiding something that works.
            --}}
            <li class="nav-item rc-item">
                <a href="{{ url('/recruitment/interviews') }}"
                    class="rc-link {{ $maincat == 'interviews' ? 'active' : '' }}">
                    <span class="rc-step">5</span>
                    <span class="rc-ico"><svg><use href="#ri-interview" /></svg></span>
                    <span class="rc-text">Interviews</span>
                </a>
            </li>

            {{--
                Offer, on its own at last.

                It shared one disabled item with Interview, which said two
                untrue things at once: that interviews were not built, and
                that the two are one step. Interviews is live; Offer genuinely
                is not, so it keeps the Soon badge and gains nothing but its
                own line.
            --}}
            <li class="nav-item rc-item">
                <a href="{{ url('/recruitment/offers') }}"
                    class="rc-link {{ ($maincat ?? '') == 'offers' ? 'active' : '' }}">
                    <span class="rc-step">6</span>
                    <span class="rc-ico"><svg><use href="#ri-offer" /></svg></span>
                    <span class="rc-text">Offer</span>
                </a>
            </li>

            <li class="nav-item rc-item">
                <span class="rc-link rc-disabled">
                    <span class="rc-step">7</span>
                    <span class="rc-ico"><svg><use href="#ri-onboarding" /></svg></span>
                    <span class="rc-text">Onboarding</span>
                    <span class="rc-soon">Soon</span>
                </span>
            </li>

            {{--
                Probation sits outside the numbered pipeline.

                Not removed and not decided: once somebody is hired they are
                an employee, and whether their probation belongs to
                recruitment at all is an open question for the HRIS side. It
                keeps its place in the menu and loses only its step number,
                which was claiming it is part of a sequence that ends at
                Onboarding.
            --}}
            <li class="nav-item rc-item rc-item-plain">
                <span class="rc-link rc-disabled">
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