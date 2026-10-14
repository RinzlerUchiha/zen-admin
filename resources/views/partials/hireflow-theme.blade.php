{{--
    The HireFlow visual language (public/hireflow-theme.css), for HireFlow pages
    only. Include it at the top of a page's content and wrap that content in
    <div class="hf-theme"> — the theme's Bootstrap rebinding applies only inside
    that wrapper, so the rest of zen-admin keeps its own look.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('hireflow-theme.css') }}?v={{ @filemtime(public_path('hireflow-theme.css')) ?: 1 }}">

    {{--
        Semantic state layer for HireFlow.

        hireflow-theme.css is generated and must not be hand-edited, so the
        meanings live here instead, built from its tokens. One colour = one
        meaning across Manpower, Job Postings, Applicant Intake and the
        dashboard, because the palette previously used --zn-ok for three
        unrelated things (Approved, Served, Posted) and one grey for both
        Draft and Cancelled.

        Every state also carries its own text, so nothing depends on hue.
    --}}
    <style>
        /*
            The canvas.

            zen-admin paints every page #e9ecf3 (public/main.css, a cool
            blue-grey) with !important, and that rule sits on <body>, outside
            the .hf-theme wrapper -- so a descendant cannot override it and
            HireFlow pages ended up as warm cards on a cool background while
            Applicants and ZenHub both sit on --zn-bg.

            It is repainted here rather than in main.css because that file is
            global and other zen-admin modules keep their own look, and rather
            than in hireflow-theme.css because that file is generated. This
            style is pushed only by this partial, which only HireFlow pages
            include, so the warm canvas reaches exactly those pages.

            Both html and body, because main.css paints both. Painting only
            body leaves a strip of the old colour under the fixed navbar:
            `body > main` carries a 70px top margin which, with body having no
            margin, border or padding of its own, collapses through body and
            pushes its painted box down -- and since html has a background of
            its own, body's colour no longer propagates to the canvas, so the
            uncovered band shows html's.
        */
        html, body { background-color: var(--zn-bg) !important; }

        .hf-theme {
            --hf-attention:      var(--zn-accent);
            --hf-attention-soft: var(--zn-accent-soft);
            --hf-attention-ink:  var(--zn-accent-dark);

            --hf-overdue:        var(--zn-warn);
            --hf-overdue-soft:   var(--zn-warn-soft);

            --hf-progress:       var(--zn-ink-2);
            --hf-progress-soft:  var(--zn-surface-2);

            --hf-done:           var(--zn-ok);
            --hf-done-soft:      var(--zn-ok-soft);

            --hf-inactive:       var(--zn-ink-3);
            --hf-inactive-soft:  var(--zn-lock-soft);
        }

        .hf-theme .hf-state {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 20px;
            padding: 3px 11px;
            font-size: var(--zn-fs-sm);
            font-weight: 700;
            white-space: nowrap;
        }

        .hf-theme .hf-state-attention { background: var(--hf-attention-soft); color: var(--hf-attention-ink); }
        .hf-theme .hf-state-overdue   { background: var(--hf-overdue-soft);   color: var(--hf-overdue); }
        .hf-theme .hf-state-progress  { background: var(--hf-progress-soft);  color: var(--hf-progress); }
        .hf-theme .hf-state-done      { background: var(--hf-done-soft);      color: var(--hf-done); }
        .hf-theme .hf-state-inactive  { background: var(--hf-inactive-soft);  color: var(--hf-inactive); }

        /* Served: hiring has started. Solid, so it reads as a milestone
           rather than another soft chip. */
        .hf-theme .mpv-chip-served { background: var(--hf-done); color: var(--zn-surface); }

        /*
        | The small dialogs (public/hf-dialog.js), and the line that follows
        | them.
        |
        | Deliberately NOT scoped to .hf-theme. A dialog is appended to
        | <body>, outside the wrapper, so a scoped rule would never reach it
        | and it would render as unstyled browser defaults -- which is what it
        | was replacing. It uses the theme's own tokens, which are defined on
        | :root by hireflow-theme.css, so it still looks like HireFlow.
        */
        .hf-dlg-back {
            position: fixed;
            inset: 0;
            background: rgba(32, 26, 22, .45);
        }

        .hf-dlg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            /* Fits a 390px phone with a gutter either side. */
            width: min(440px, calc(100vw - 32px));
            max-height: calc(100vh - 32px);
            overflow: auto;
            background: var(--zn-surface);
            border-radius: var(--zn-radius-lg);
            box-shadow: 0 18px 48px rgba(32, 26, 22, .28);
            padding: 20px;
            font-size: var(--zn-fs);
            color: var(--zn-ink);
        }

        .hf-dlg-title {
            margin: 0 0 8px;
            font-size: var(--zn-fs-lg, 16px);
            font-weight: 700;
            color: var(--zn-ink);
        }

        .hf-dlg-msg { margin: 0 0 10px; color: var(--zn-ink-2); }

        /* The consequence, said separately from the question so it cannot be
           skimmed past as part of it. */
        .hf-dlg-detail {
            margin: 0 0 12px;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-3);
        }

        .hf-dlg-body:empty { display: none; }

        .hf-dlg-label {
            display: block;
            margin: 4px 0 4px;
            font-size: var(--zn-fs-sm);
            font-weight: 600;
            color: var(--zn-ink-2);
        }

        .hf-dlg-field {
            width: 100%;
            border: 1px solid var(--zn-line);
            border-radius: var(--zn-radius);
            padding: 8px 10px;
            font: inherit;
            font-size: var(--zn-fs);
            color: var(--zn-ink);
            background: var(--zn-surface);
        }

        .hf-dlg-field:focus-visible {
            outline: none;
            border-color: var(--zn-accent);
            box-shadow: 0 0 0 3px var(--zn-accent-soft);
        }

        .hf-dlg-error {
            margin: 8px 0 0;
            font-size: var(--zn-fs-sm);
            font-weight: 600;
            color: var(--zn-warn);
        }

        .hf-dlg-row {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .hf-dlg-btn {
            border-radius: var(--zn-radius-lg);
            padding: 7px 16px;
            font-size: var(--zn-fs-ui);
            font-weight: 600;
            cursor: pointer;
        }

        .hf-dlg-cancel {
            border: 1px solid var(--zn-line);
            background: var(--zn-surface);
            color: var(--zn-ink-2);
        }

        .hf-dlg-ok {
            border: 1px solid var(--zn-accent);
            background: var(--zn-accent);
            color: var(--zn-surface);
        }

        /* A destructive confirmation is coloured as one, and the script does
           not put focus on it. */
        .hf-dlg.is-warn .hf-dlg-ok {
            border-color: var(--zn-warn);
            background: var(--zn-warn);
        }

        .hf-dlg-btn:focus-visible { outline: 2px solid var(--zn-accent); outline-offset: 2px; }

        .hf-toasts {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 2100;
            display: flex;
            flex-direction: column;
            gap: 8px;
            /* Never wider than a phone, and never in the way of a tap. */
            max-width: min(380px, calc(100vw - 32px));
            pointer-events: none;
        }

        .hf-toast {
            pointer-events: auto;
            border-radius: var(--zn-radius-lg);
            padding: 10px 14px;
            font-size: var(--zn-fs-ui);
            font-weight: 600;
            box-shadow: 0 10px 28px rgba(32, 26, 22, .18);
            background: var(--zn-ok-soft);
            color: var(--zn-ok);
            transition: opacity .25s ease, transform .25s ease;
        }

        .hf-toast.is-warn { background: var(--zn-warn-soft); color: var(--zn-warn); }
        .hf-toast.is-info { background: var(--zn-surface-2); color: var(--zn-ink-2); }
        .hf-toast.is-going { opacity: 0; transform: translateY(6px); }

        @media (prefers-reduced-motion: reduce) {
            .hf-toast { transition: none; }
        }
    </style>
@endpush

{{--
    The dialogs themselves.

    Pushed to the layout's script stack rather than written into the head, so
    jQuery, Bootstrap and the DOM are all there by the time it runs. Every
    HireFlow page already includes this partial -- recruitment.blade.php is
    the shell for Job Postings, Applicant Intake, Interviews and the
    dashboard, and applicant/profile.blade.php is the shell for Documents and
    Interview Details -- so including it here reaches all of them without
    touching each page.
--}}
@push('scripts')
    <script src="{{ asset('hf-dialog.js') }}?v={{ @filemtime(public_path('hf-dialog.js')) ?: 1 }}"></script>
@endpush
