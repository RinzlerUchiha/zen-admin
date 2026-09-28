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
    </style>
@endpush
