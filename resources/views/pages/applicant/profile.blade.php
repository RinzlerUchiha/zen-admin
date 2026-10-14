@extends('layouts.layout')

@section('content')
    {{-- HireFlow page: the shared HireFlow look, scoped to this wrapper. --}}
    @include('partials.hireflow-theme')
    <div class="hf-theme">

    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script> --}}

    <style>
        /* The applicant at a glance, above the tabs. */
        .ap-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            align-items: flex-start;
            justify-content: space-between;
            background: var(--zn-surface);
            border: 1px solid var(--zn-line);
            border-radius: var(--zn-radius-lg);
            padding: 14px 16px;
            margin-bottom: 14px;
        }

        .ap-overview-id {
            display: flex;
            gap: 12px;
            align-items: center;
            min-width: 240px;
        }

        /* The photo sits on top of the initials, so a file that cannot be
           loaded leaves a placeholder rather than a broken image. */
        .ap-overview-photo {
            position: relative;
            width: 46px;
            height: 46px;
            flex: none;
        }

        .ap-overview-photo img,
        .ap-overview-initials {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .ap-overview-initials {
            display: grid;
            place-items: center;
            background: var(--zn-accent-soft);
            color: var(--zn-accent);
            font-weight: 700;
        }

        /*
        | The status header follows HR down the page.
        |
        | The profile is long -- documents, assessments, interviews, history --
        | and who the applicant is and where they stand is the context all of
        | it is read against. Scrolling it away means scrolling back up to
        | check which of two applicants is on screen.
        |
        | It sticks below the app's fixed navbar, not at 0, or it would sit
        | underneath it. Only from a height where there is room to spare: on a
        | short window a sticky header eats the content it is meant to give
        | context to.
        */
        @media (min-height: 620px) {
            .ap-overview {
                position: sticky;
                top: var(--main-top-margin, 70px);
                z-index: 3;
            }
        }

        /* Where an application has got to, beside what was decided about it. */
        .ap-stage {
            font-size: var(--zn-fs-xs);
            font-weight: 600;
            color: var(--zn-accent-dark);
            background: var(--zn-accent-soft);
            border-radius: 20px;
            padding: 1px 8px;
            white-space: nowrap;
        }
        .ap-stage.is-done { color: var(--zn-ok); background: var(--zn-ok-soft); }
        .ap-stage.is-none { color: var(--zn-ink-3); background: var(--zn-surface-2); }

        /*
        | How far along, as a bar of named steps.
        |
        | Still small enough to sit inside the sticky header without making it
        | taller: the segments take the width the old dots were spending on
        | the gaps between them, and the step name replaces the "Next:" chip
        | that used to sit alongside saying the same thing.
        |
        | A FIXED width whatever the number of steps, with the segments
        | sharing it. An applicant with three applications of four, three and
        | five steps would otherwise get three bars of different lengths
        | stacked on top of each other, and the eye reads that as progress.
        */
        .ap-rail {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            width: 96px;
            vertical-align: middle;
        }

        .ap-rail-seg {
            flex: 1 1 0;
            min-width: 0;
            height: 6px;
            border-radius: 3px;
            background: var(--zn-line-2);
        }

        /* Settled: done, or deliberately skipped. Both are finished with --
           but they are not the same thing, so they are not the same colour. */
        .ap-rail-seg.is-done { background: var(--hf-done, var(--zn-ok)); }
        .ap-rail-seg.is-na   { background: var(--zn-line); }

        /* The one it is waiting on. Haloed rather than merely coloured: at six
           pixels a colour difference alone is not enough to find at a glance,
           and this is the segment the whole bar exists to point at. */
        .ap-rail-seg.is-now {
            background: var(--zn-accent);
            box-shadow: 0 0 0 2px var(--zn-accent-soft);
        }

        .ap-rail-now {
            font-size: var(--zn-fs-xs);
            font-weight: 600;
            color: var(--zn-accent-dark);
            white-space: nowrap;
        }

        .ap-rail-count {
            font-size: var(--zn-fs-xs);
            color: var(--zn-ink-3);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* Narrow windows: the bar is the first thing worth dropping, since
           the step name and the count carry the same information in words. */
        @media (max-width: 575.98px) {
            .ap-rail { display: none; }
        }

        .ap-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            font-size: var(--zn-fs-sm);
            font-weight: 600;
            color: var(--zn-ink-2);
            text-decoration: none;
        }
        .ap-back:hover { color: var(--zn-accent-dark); }
        .ap-back:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: 2px; }

        /* A blacklisted applicant is said so above everything else. */
        .ap-blacklist {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid var(--zn-warn);
            border-left-width: 4px;
            border-radius: var(--zn-radius-lg);
            background: var(--zn-warn-soft);
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .ap-blacklist-ico { color: var(--zn-warn); flex: none; line-height: 1.4; }
        .ap-blacklist-main { min-width: 0; flex: 1; }
        .ap-blacklist-title {
            margin: 0;
            font-weight: 700;
            color: var(--zn-warn);
            font-size: var(--zn-fs);
        }
        .ap-blacklist-reason {
            margin: 2px 0 0;
            color: var(--zn-ink);
            overflow-wrap: anywhere;
        }
        .ap-blacklist-meta {
            margin: 4px 0 0;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-2);
        }
        .ap-blacklist-note {
            margin: 6px 0 0;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-2);
        }

        .ap-bl-history { margin: 10px 0 14px; }
        .ap-bl-history > summary {
            cursor: pointer;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-2);
        }
        .ap-bl-history ol {
            margin: 8px 0 0;
            padding-left: 18px;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-2);
        }
        .ap-bl-history li { margin-bottom: 6px; overflow-wrap: anywhere; }

        .ap-overview-id b {
            display: block;
            font-size: var(--zn-fs-lg);
        }

        .ap-overview-id span {
            display: block;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-3);
        }

        .ap-overview-facts {
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
        }

        .ap-fact {
            min-width: 150px;
        }

        .ap-fact-label {
            display: block;
            font-size: var(--zn-fs-xs);
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--zn-ink-3);
            margin-bottom: 3px;
        }

        .ap-fact-value {
            display: block;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink);
        }

        .ap-fact-link {
            display: inline-block;
            margin-top: 4px;
            font-size: var(--zn-fs-sm);
            font-weight: 600;
        }

        .ap-applications {
            list-style: none;
            padding: 0;
            margin: 6px 0 0;
            font-size: var(--zn-fs-sm);
        }

        .ap-applications li {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            padding: 2px 0;
        }

        :root {
            --my-top-space: calc(var(--main-top-margin) + .25rem);
        }

        #sidebar {
            max-height: calc(100vh - var(--ap-navbar-h, 57px));
            overflow: auto;
        }

        /* #sidebar is the only scroll container; Bootstrap's offcanvas-body
           would otherwise add a second scrollbar inside it. */
        #sidebar .offcanvas-body {
            overflow: visible;
        }

        /*
        | The scrollbar, which was the gap.
        |
        | A always-visible 8px scrollbar sat between the menu and the content
        | and read as a grey channel down the page rather than as part of
        | anything. Thin and in the page's own colours, it stops being a seam;
        | it still appears, and still scrolls, when the menu is longer than the
        | window.
        */

        @media (min-width: 768px) {
            #sidebar.offcanvas {
                width: var(--ap-side-w, 300px);
                position: sticky;
                transform: none;
                visibility: visible !important;
                /*
                | Meets the bar above it.
                |
                | The page offsets its content by --main-top-margin to clear
                | the fixed navbar, but that offset is taller than the navbar
                | actually is -- so a strip of page background showed between
                | the two, and against a white menu panel it read as a seam
                | rather than as padding. The menu is pulled up by exactly the
                | difference and sticks at the navbar's own height, so the
                | panel and the bar meet. The content column keeps the offset.
                |
                | --ap-navbar-h is corrected from the real navbar on load; the
                | value here is the fallback if that does not run.
                */
                --ap-navbar-h: 57px;
                /* --my-top-space is the content offset plus the row's own
                   top padding, which is exactly how far down the menu
                   would otherwise start. */
                margin-top: calc(var(--ap-navbar-h) - var(--my-top-space));
                top: var(--ap-navbar-h);
                /*
                | A border, not a gutter.
                |
                | The two columns were held apart by Bootstrap's 24px of
                | gutter with nothing drawn in it, so the menu and the content
                | floated with a channel between them. One line separates them
                | properly and gives the width back.
                */
                border-right: 1px solid var(--zn-line);
                padding-right: 0;
                /*
                | Full height in both states.
                |
                | The column was only ever as tall as what was inside it, so
                | folded -- when all that is left is the handle -- it stopped a
                | few centimetres down the page and the rest of the edge was
                | bare. Fixing the height to the window is what the recruitment
                | rail does, and it makes the fold a change of width only.
                */
                height: calc(100vh - var(--ap-navbar-h));
                /*
                | Width only -- no slide on a sidebar that does not move.
                |
                | Bootstrap animates every .offcanvas, so this one slid in from
                | off-screen on every single page load before settling where it
                | was always going to be. Off the drawer behaviour it is just a
                | column, and a column should be there when the page is.
                |
                | Naming the one property rather than switching transitions off
                | altogether keeps transform untransitioned -- so the slide
                | stays gone -- while letting the column fold at the speed the
                | recruitment rail folds at, instead of jumping between two
                | widths.
                */
                transition: width .18s ease;
            }

            /*
            | Collapsed: the column is taken out of the layout entirely, so the
            | content grows into the whole width.
            |
            | By display rather than by width. As a flex item in a Bootstrap
            | row this one would not shrink below its content: setting its
            | width to zero -- even inline, even with min-width cleared -- still
            | measured 300px, so the menu stayed exactly where it was and only
            | the button changed. Removing it from the flow is the thing that
            | actually gives the room back.
            |
            | Only above the breakpoint. Below it the menu is Bootstrap's
            | drawer, and a drawer that has been displayed none cannot open.
            */
            /*
            | Folded: a narrow rail holding just the handle.
            |
            | This used to be display:none, which worked but took the handle
            | away with the menu -- the way back lived in the content column.
            | Now that the handle is inside the menu, the menu has to leave
            | something behind, so it keeps a rail wide enough for the button
            | and hides everything else.
            |
            | A fixed width rather than a shrink: as a flex item this column
            | will not go below its content, so asking it to be narrow does
            | nothing unless the content is taken out of the measurement too.
            */
            /*
            | 68px and the panel's own colour, both taken from the recruitment
            | rail rather than chosen here.
            |
            | It was 52px and tinted, which made folding the menu change the
            | colour of the column as well as its width -- next to the main
            | rail, which only ever narrows, the two read as different
            | controls. The border and the full height are what say the column
            | is still there; it does not need a shade of its own.
            */
            #ap-shell[data-nav-collapsed="1"] #sidebar.offcanvas {
                --ap-side-w: 68px;
                flex: 0 0 68px;
                padding-left: 0;
                padding-right: 0;
                overflow-x: hidden;
            }

            #ap-shell[data-nav-collapsed="1"] #sidebar .nav.flex-column {
                padding: 10px 7px !important;
            }

            /*
            | Everything except the handle goes.
            |
            | !important because the section headings carry Bootstrap's d-flex
            | utility, which is itself !important -- without matching it the
            | headings stayed on screen in a 68px rail, stacked on top of each
            | other.
            */
            #ap-shell[data-nav-collapsed="1"] #sidebar .nav > *:not(#ap-nav-toggle-item) {
                display: none !important;
            }

            /*
            | Folded, the handle only moves to the middle.
            |
            | It used to change colour, grow its chevron and pick up a
            | different hover tint as well, so the same button looked like two
            | buttons depending on which state you caught it in. Centring it
            | and dropping the label is the whole difference, as on the
            | recruitment rail.
            */
            #ap-shell[data-nav-collapsed="1"] #ap-nav-toggle {
                justify-content: center;
                padding-left: 6px;
                padding-right: 6px;
            }

            /* Nothing to align against in a rail this narrow, so the icon box
               gives its fixed width back. */
            #ap-shell[data-nav-collapsed="1"] #ap-nav-toggle .ap-toggle-ico {
                width: auto;
            }

            #ap-shell[data-nav-collapsed="1"] #ap-nav-toggle-text {
                display: none;
            }
        }

        /*
        | The handle.
        |
        | Sits at the top of the content, so it is in the same place whether
        | the menu is open or shut -- a control that moves when you use it is
        | a control you have to hunt for twice.
        */
        /*
        | The handle, styled as a menu item rather than a floating pill,
        | because that is what it now is.
        */
        #ap-nav-toggle-item { display: none; }

        #ap-nav-toggle {
            display: flex;
            align-items: center;
            gap: 6px;
            /* Expanded: the chevron sits at the menu's edge, pointing the way
               it will close. */
            justify-content: flex-end;
            width: 100%;
            margin: 0 0 4px;
            padding: 6px 10px;
            border: 1px solid transparent;
            border-radius: var(--zn-radius-lg);
            background: transparent;
            color: var(--zn-ink-3);
            font-size: var(--zn-fs-sm);
            font-weight: 600;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        /* Only above the breakpoint: below it the menu is a drawer with its
           own close button, and a second control would just be confusing. */
        @media (min-width: 768px) { #ap-nav-toggle-item { display: block; } }

        #ap-nav-toggle .ap-toggle-ico {
            flex: none;
            /* 30, as on the recruitment rail; the chevron inside stays 18. */
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .18s ease;
        }

        #ap-nav-toggle:hover {
            background: var(--zn-surface-2);
            color: var(--zn-ink);
        }

        #ap-nav-toggle:focus-visible { outline: none; border-color: var(--zn-accent); }

        #ap-nav-toggle svg { width: 18px; height: 18px; }

        /* The chevron points the way the menu will go. Turned by CSS, so no
           script has to rewrite an icon. */
        #ap-shell[data-nav-collapsed="1"] #ap-nav-toggle .ap-toggle-ico { transform: rotate(180deg); }

        /* Result tables scroll sideways on a phone instead of breaking the page. */
        @media (max-width: 767.98px) {
            .applicant-profile-content table { display: block; max-width: 100%; overflow-x: auto; }
        }

        #sidebar h6 {
            font-size: .9rem;
        }

        #sidebar li a {
            font-size: var(--zn-fs-ui);
            color: black;
        }

        #sidebar li a.active {
            font-weight: bold;
            color: var(--bs-primary);
        }

        #sidebar li:hover {
            background-color: var(--zn-line-2);
        }

        /*
        | The scrollbar, which was most of the gap.
        |
        | The thumb was already slimmed to 7px, but the TRACK was left at the
        | browser default -- a solid light channel running the height of the
        | page between the menu and the content, which read as a seam rather
        | than as a scrollbar. Clearing the track and quietening the thumb
        | leaves the scrolling intact and the channel gone.
        |
        | Deliberately NOT scrollbar-width: thin. Chrome honours that in
        | preference to these rules and renders ~11px, which is wider than what
        | is here.
        */
        #sidebar::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: var(--zn-line-2);
            border-radius: var(--zn-radius-lg);
        }

        /*
        | No darkening on hover.
        |
        | Resting on the menu turned the thumb to near-ink, which next to a
        | 7px line in --zn-line-2 read as a solid bar appearing down the edge
        | of the page. The recruitment rail leaves its thumb one colour; so
        | does this one now.
        */

        /* Which section of the profile is open, and the applicant it belongs to. */
        .ap-section-head {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .ap-section-head h5 { margin: 0 0 2px; }

        .ap-section-sub {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            color: var(--zn-ink-3);
            font-size: var(--zn-fs-sm);
        }

        /*
         | Status chips shared by the section header and the Applications tab.
         | Small, quiet, and readable at a glance — the same shape the intake
         | list uses, so a status looks the same wherever HR meets it.
         */
        .hf-chip {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: var(--zn-fs-xs);
            font-weight: 600;
            line-height: 1.6;
            white-space: nowrap;
        }

        .hf-chip-ok   { background: var(--hf-done-soft);     color: var(--hf-done); }
        .hf-chip-lock { background: var(--hf-inactive-soft); color: var(--hf-inactive); }
        .hf-chip-pool { background: var(--hf-attention-soft); color: var(--hf-attention-ink); }
    </style>

    <script>
        $(function() {
            $('#hireModal').on('show.bs.modal', async function(){
                // if($('#form-hire-content').text()) return;
                try {
                    const url = @json(route('applicant.form.hire', ['id' => $applicant?->app_id]));
                    const response = await fetch(url);
                    const html = await response.text();
                    $('#form-hire-content').html(html);
                } catch (error) {
                    console.error('Error:', error);
                    hfToast('That could not be loaded. Please try again.', 'warn');
                }
            });

            $('#form-hire-content').on('change', '#hire-dt, #hire-outlet', async function(){
                if(!$('#hire-dt').val() || !$('#hire-outlet').val()) return;
                $('#hire-empno').prop('disabled', true);
                $('#hire-empno').val('generating...');
                try {
                    const url = @json(route('generateEmpNo'));
                    const params = new URLSearchParams({
                        dateHired: $('#hire-dt').val(),
                        area: $('#hire-outlet option:selected').data('area'),
                        outlet: $('#hire-outlet').val()
                    });

                    const response = await fetch(`${url}?${params.toString()}`);
                    const text = await response.text();
                    $('#hire-empno').val(text);
                } catch (error) {
                    $('#hire-empno').val('');
                    console.error('Error:', error);
                    hfToast('That could not be loaded. Please try again.', 'warn');
                } finally {
                    $('#hire-empno').prop('disabled', false);
                }
            });

            $('#form-hire-content').on('change', '#hire-outlet', async function(){
                $('#hire-area').val($('#hire-outlet option:selected').data('area') || '');
            });

            // $(document).on('submit', '#myForm', function(e) {
            //     e.preventDefault();
            // });
        });

        function toggleHirePw() {
            const input = document.getElementById("hire-pw");
            const btn = document.getElementById("btn-hire-toggle-pw");
            input.type = input.type === "password" ? "text" : "password";
            btn.textContent = input.type === "password" ? "Hide" : "Show";
        }
    </script>

    {{-- Restore the menu's state before first paint, so a collapsed menu does
         not flash open and fold shut on every page load. --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('hf.profileNav.collapsed') === '1') {
                    document.documentElement.setAttribute('data-ap-nav-collapsed', '1');
                }
            } catch (e) { /* storage blocked: stay open */ }
        })();
    </script>

    <div class="row pt-1 justify-content-center" id="ap-shell">
        <div class="col-md-auto offcanvas offcanvas-start" tabindex="-1" id="sidebar">
            <div class="offcanvas-header d-md-none">
                <h5 class="offcanvas-title">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <ul class="nav flex-column p-3">

                    {{--
                        The handle, inside the menu it operates.

                        It used to sit at the top of the content column, which
                        put the control for the menu outside the menu. Here it
                        is the first thing in the list, the way the recruitment
                        shell does it, and it stays reachable when the menu is
                        folded because folding leaves a rail rather than
                        removing the column.

                        Desktop only: below the breakpoint this is Bootstrap's
                        drawer, which has its own close button.
                    --}}
                    <li class="nav-item" id="ap-nav-toggle-item">
                        <button type="button" id="ap-nav-toggle" aria-controls="sidebar"
                                aria-expanded="true" aria-label="Collapse menu" title="Collapse menu">
                            <span class="ap-toggle-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m14 6-6 6 6 6" />
                                    <path d="M19 6v12" />
                                </svg>
                            </span>
                            <span id="ap-nav-toggle-text">Hide menu</span>
                        </button>
                    </li>

                    {{-- Hiring first: what this person applied for, how far
                         each application got, and the documents behind it. --}}
                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Hiring</span>
                        @if (isset($profileApplications))
                            <span class="badge rounded-pill text-bg-light border fw-normal">{{ count($profileApplications) }}</span>
                        @endif
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'applications']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'applications' ? 'active' : '' }}">Applications</a></li>
                    @can('applicant-documents.view')
                        <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'documents']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'documents' ? 'active' : '' }}">Documents</a></li>
                    @endcan
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'interview-details']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'interview-details' ? 'active' : '' }}">Interview Details</a></li>

                    <hr class="my-3">

                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Candidate Profile</span>
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', [ 'id' => $applicant?->app_id, 'tab' => 'personal']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'personal' ? 'active' : '' }}">Personal</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'family']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'family' ? 'active' : '' }}">Family Background</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'skill']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'skill' ? 'active' : '' }}">Special Skills</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'education']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'education' ? 'active' : '' }}">Education</a></li>

                    <hr class="my-3">

                    {{-- Credentials and work history were two groups of two
                         items; they answer the same question. --}}
                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Experience &amp; Credentials</span>
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'employment']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'employment' ? 'active' : '' }}">Employment Record</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'license']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'license' ? 'active' : '' }}">Eligibility/Licenses</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'certificate']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'certificate' ? 'active' : '' }}">Certificate</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'characterref']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'characterref' ? 'active' : '' }}">Character Reference</a></li>

                    <hr class="my-3">

                    {{-- Assessments (HireFlow 2.5): read-only results, each marked with
                         where it stands. The same view for HR and approvers. --}}
                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Assessments &amp; Interviews</span>
                        @if (isset($assessmentResults))
                            <span class="badge rounded-pill text-bg-light border fw-normal">{{ $assessmentResults->where('hasResult', true)->count() }}/{{ $assessmentResults->count() }}</span>
                        @endif
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'assessment-access']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'assessment-access' ? 'active' : '' }}">Access &amp; Status</a></li>
                    @foreach (collect($assessmentResults ?? [])->groupBy('kind') as $kind => $group)
                        <li class="nav-item px-3 pt-2 small text-body-secondary">{{ $kind === 'aptitude' ? 'Aptitude tests' : 'Questionnaires' }}</li>
                        @foreach ($group as $item)
                            <li class="nav-item">
                                <a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => $item->tab]) }}"
                                   class="nav-link d-flex align-items-center gap-2 {{ ($sub_link ?? '') == $item->tab ? 'active' : '' }}">
                                    <i class="bi {{ match ($item->status) { 'submitted' => 'bi-check-circle-fill text-success', 'timed_out' => 'bi-hourglass-bottom text-secondary', 'interrupted' => 'bi-pause-circle text-warning', 'active' => 'bi-play-circle text-info', default => 'bi-circle text-body-tertiary' } }}"
                                       title="{{ $item->badge[0] ?? '' }}" aria-hidden="true"></i>
                                    <span class="flex-grow-1">{{ $item->label }}</span>
                                    <span class="visually-hidden">— {{ $item->badge[0] ?? '' }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md col-12">
            <button class="btn btn-primary d-md-none my-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">☰ Menu</button>

            <div class="container-fluid">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {{--
                    Back to where HR came from.

                    The profile is reached from the Applicant Intake list, and
                    there was no way back to it except the browser button or
                    the sidebar -- which returns to an unfiltered list, losing
                    whatever search or filter found this applicant. The return
                    carries that context back when it was passed in.
                --}}
                <a class="ap-back" href="{{ $intakeReturn ?? route('recruitment.applicant-intake.index') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    <span>Applicant Intake</span>
                </a>
                @include('pages.applicant.partials.applicant-blacklist')
                @include('pages.applicant.partials.profile-overview')

@php
    // The section being viewed, so the header says where you are rather than
    // repeating the applicant's name that is already in the card above.
    $apSectionTitles = [
        'applications' => 'Applications',
        'documents' => 'Documents',
        'interview-details' => 'Interview Details',
        'personal' => 'Personal Information',
        'family' => 'Family Background',
        'skill' => 'Special Skills',
        'education' => 'Education',
        'employment' => 'Employment Record',
        'license' => 'Eligibility & Licenses',
        'certificate' => 'Certificates',
        'characterref' => 'Character References',
        'assessment-access' => 'Assessment Access & Status',
    ];
    $apSection = $apSectionTitles[$sub_link ?? ''] ?? 'Assessment Result';
    $apOpen = collect($profileApplications ?? [])->reject(fn ($a) => $a->is_closed);
@endphp

                <div class="ap-section-head">
                    <div>
                        <h5>{{ $apSection }}</h5>
                        <span class="ap-section-sub">
                            {{ $applicant?->first_last_name }}
                            @if ($apOpen->isNotEmpty())
                                · <span class="hf-chip hf-chip-ok">{{ $apOpen->count() }} open {{ \Illuminate\Support\Str::plural('application', $apOpen->count()) }}</span>
                            @elseif (!empty($profileApplications))
                                · <span class="hf-chip hf-chip-lock">No open application</span>
                            @endif
                        </span>
                    </div>
                    @if(($sub_link ?? '') !== 'interview-details')
                    <div class="btn-group dropstart ms-auto">
                        <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Set Status</button>
                        <ul class="dropdown-menu">
                            @can('applicant.hire')
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#hireModal">Hired</button></li>
                            @endcan
                            <li><a class="dropdown-item" href="#">Inactive</a></li>
                            @can('applicant-applications.decide')
                                <li><hr class="dropdown-divider"></li>
                                @if (empty($blacklist))
                                    <li>
                                        <button type="button" class="dropdown-item text-danger"
                                                data-bs-toggle="modal" data-bs-target="#apBlacklistModal">
                                            Blacklist applicant…
                                        </button>
                                    </li>
                                @else
                                    <li>
                                        <button type="button" class="dropdown-item"
                                                data-bs-toggle="modal" data-bs-target="#apBlacklistLiftModal">
                                            Lift blacklist…
                                        </button>
                                    </li>
                                @endif
                            @endcan
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="applicant-profile-content">
                    @yield('profile_content')
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @can('applicant.hire')
    <div class="modal fade" id="hireModal" tabindex="-1" aria-labelledby="hireModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="hireModalLabel">Setup Employee Account</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-hire" action="{{ route('applicant.hire', ['id' => $applicant?->app_id]) }}" method="POST">
                    @csrf
                    <div class="modal-body" id="form-hire-content"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan


    {{--
        Folding the profile menu away.

        The menu is a column at desktop widths and a drawer on a phone. This
        only touches the column: on a phone Bootstrap's own offcanvas still
        does the work, and the toggle is not shown there.

        The choice is remembered per browser, and applied before first paint by
        the small script above -- read from the <html> element, because at that
        point the row itself has not been parsed yet.
    --}}
    <script>
        (function () {
            const STORE_KEY = 'hf.profileNav.collapsed';
            const shell = document.getElementById('ap-shell');
            const toggle = document.getElementById('ap-nav-toggle');
            const label = document.getElementById('ap-nav-toggle-text');
            const sidebar = document.getElementById('sidebar');
            if (!shell || !toggle || !sidebar) { return; }

            /*
            | The menu meets the bar above it.
            |
            | The CSS pulls the menu up by the difference between the page's
            | content offset and the navbar's height, but only the browser
            | knows how tall the navbar really is -- it depends on the font and
            | on what is in it. Measuring once and writing the answer back
            | keeps the two touching even if the bar changes; the value in the
            | stylesheet is only the fallback for when this does not run.
            */
            (function matchNavbar() {
                const bar = document.querySelector('nav.navbar');
                if (!bar) { return; }

                const set = function () {
                    const h = Math.round(bar.getBoundingClientRect().height);
                    if (h > 0) { sidebar.style.setProperty('--ap-navbar-h', h + 'px'); }
                };

                set();
                // It can change with the width (a wrapped bar is taller).
                if (typeof ResizeObserver === 'function') {
                    new ResizeObserver(set).observe(bar);
                } else {
                    window.addEventListener('resize', set);
                }
            })();

            /* The pre-paint script could only reach <html>; move its answer on
               to the row now that there is one. */
            if (document.documentElement.getAttribute('data-ap-nav-collapsed') === '1') {
                shell.setAttribute('data-nav-collapsed', '1');
            }
            document.documentElement.removeAttribute('data-ap-nav-collapsed');

            function apply(collapsed, remember) {
                if (collapsed) {
                    shell.setAttribute('data-nav-collapsed', '1');
                } else {
                    shell.removeAttribute('data-nav-collapsed');
                }

                toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                label.textContent = collapsed ? 'Show menu' : 'Hide menu';

                /* Folded, the label is hidden and the button is a chevron on
                   its own, so what it does has to be said some other way. */
                const says = collapsed ? 'Expand menu' : 'Collapse menu';
                toggle.setAttribute('aria-label', says);
                toggle.setAttribute('title', says);

                /*
                | Nothing here touches `inert`.
                |
                | Folded, the column stays -- it becomes a rail holding the
                | handle -- but every item inside it except that handle is
                | display:none, which already takes them out of the tab order
                | and the accessibility tree. Marking the column inert as well
                | would add nothing above the breakpoint and was actively wrong
                | below it: the attribute outlived the width it was set at, so
                | the phone drawer opened with every link in it dead. The CSS
                | is the single place that decides what is reachable.
                */

                if (remember) {
                    try { localStorage.setItem(STORE_KEY, collapsed ? '1' : '0'); }
                    catch (e) { /* storage blocked: the choice lasts this page */ }
                }
            }

            toggle.addEventListener('click', function () {
                apply(!shell.hasAttribute('data-nav-collapsed'), true);
            });

            /*
            | Nothing to do on a resize.
            |
            | The collapse lives entirely in a media query, so narrowing the
            | window to phone width lifts it by itself and the drawer behaves
            | as Bootstrap intends -- collapsed or not. The remembered choice
            | is still there when the window is widened again.
            */
            apply(shell.hasAttribute('data-nav-collapsed'), false);
        })();
    </script>

    </div>{{-- /.hf-theme --}}
@stop
