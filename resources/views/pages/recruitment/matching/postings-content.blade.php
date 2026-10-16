{{--
| Which posting to match against.
|
| Matching is always about one posting, so the sidebar entry needs somewhere to
| land. This is only that: a list of open postings, each row going to the same
| matching screen. The number that makes it worth reading is how many
| applications nobody has screened yet.
--}}
<style>
    .mp-head { margin-bottom: 6px; }
    .mp-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .mp-note {
        border-left: 3px solid var(--zn-accent);
        background: var(--zn-surface-2);
        padding: 10px 12px; border-radius: 0 6px 6px 0;
        font-size: var(--zn-fs-sm); margin: 10px 0 16px;
    }

    .mp-list { display: flex; flex-direction: column; gap: 8px; }

    .mp-row {
        display: grid; grid-template-columns: minmax(0, 2fr) auto auto;
        gap: 14px; align-items: center;
        border: 1px solid var(--zn-line); border-radius: var(--zn-radius-lg);
        background: var(--zn-surface); padding: 12px 14px;
    }
    .mp-row:hover { border-color: var(--zn-accent); }

    .mp-title { font-weight: 700; color: var(--zn-ink); }

    .mp-pill {
        font-size: var(--zn-fs-xs); font-weight: 700;
        border-radius: 20px; padding: 2px 10px;
        background: var(--zn-surface-2); color: var(--zn-ink-2);
        white-space: nowrap;
    }
    .mp-pill-due { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .mp-pill-none { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }

    .mp-empty {
        border: 1px dashed var(--zn-line); border-radius: var(--zn-radius-lg);
        padding: 32px 20px; text-align: center; color: var(--zn-ink-3);
    }

    @media (max-width: 767.98px) {
        .mp-row { grid-template-columns: 1fr; gap: 6px; }
    }
</style>

<div class="mp-head">
    <h5 class="mb-1">Candidate matching</h5>
    <div class="mp-sub">Choose the posting you are screening for.</div>
</div>

<div class="mp-note">
    Matching compares each applicant against that posting's own job specification. It is a
    recommendation from the records held — unknown information is never counted against anybody,
    and HR decides who is shortlisted.
</div>

<div id="mp-list" class="mp-list" aria-live="polite">
    <div class="mp-empty">Loading postings…</div>
</div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;

        function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

        function row(p) {
            /* Without a specification there is nothing to match against. Said
               here rather than letting somebody open a screen that can only
               report "unknown" for everything. */
            const waiting = p.unscreened > 0
                ? '<span class="mp-pill mp-pill-due">' + p.unscreened + ' to screen</span>'
                : '<span class="mp-pill">' + p.applications + ' application'
                  + (p.applications === 1 ? '' : 's') + '</span>';

            const spec = p.has_specification
                ? ''
                : '<span class="mp-pill mp-pill-none">No job specification</span>';

            return '' +
            '<article class="mp-row">' +
              '<div>' +
                '<div class="mp-title">' + esc(p.posting_title || 'Untitled posting') + '</div>' +
                '<div class="mp-sub">' + esc(p.status) + '</div>' +
              '</div>' +
              '<div>' + waiting + ' ' + spec + '</div>' +
              '<div>' +
                '<a class="btn btn-sm btn-outline-primary" href="' + BASE_URL
                  + '/recruitment/postings/' + encodeURIComponent(p.id) + '/matching">Open</a>' +
              '</div>' +
            '</article>';
        }

        $.getJSON(BASE_URL + '/recruitment/postings/matching/data')
            .done(function (json) {
                const rows = json.postings || [];
                $('#mp-list').html(rows.length
                    ? rows.map(row).join('')
                    : '<div class="mp-empty">No open postings. Matching happens against a '
                      + 'posting, so publish one first.</div>');
            })
            .fail(function (xhr) {
                console.error('[matching-postings]', xhr.status, xhr.responseText);
                $('#mp-list').html('<div class="mp-empty">Could not load the postings.</div>');
            });
    });
</script>
