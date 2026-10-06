{{--
| The screening work queue.
|
| A list of open applications that need HR to do something, each showing the
| one reason it is here. It reads existing state only — nothing on this page
| changes an application's status. Every row links to where the work is done.
--}}
<style>
    .sq-head {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .sq-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }

    .sq-filter {
        border: 1px solid var(--zn-line-2);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: 20px;
        padding: 4px 13px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        cursor: pointer;
    }

    .sq-filter:hover:not(.is-on) { border-color: var(--zn-accent); color: var(--zn-accent-dark); }
    .sq-filter.is-on { background: var(--zn-accent); border-color: var(--zn-accent); color: var(--zn-on-accent); }
    .sq-filter-count { opacity: .75; font-weight: 500; }

    .sq-list { display: flex; flex-direction: column; gap: 8px; }

    .sq-row {
        display: grid;
        grid-template-columns: 4px minmax(0, 2fr) minmax(0, 1.4fr) minmax(0, 1.2fr) auto;
        align-items: center;
        gap: 14px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 12px 14px 12px 0;
        overflow: hidden;
    }

    .sq-row:hover { border-color: var(--zn-accent); }

    /* The urgency stripe: colour carries the same meaning as the chip. */
    .sq-stripe { align-self: stretch; }
    .sq-stripe-overdue   { background: var(--hf-overdue); }
    .sq-stripe-attention { background: var(--hf-attention); }
    .sq-stripe-progress  { background: var(--hf-progress); }
    .sq-stripe-done      { background: var(--hf-done); }

    .sq-who { display: flex; flex-direction: column; gap: 1px; min-width: 0; padding-left: 14px; }
    .sq-name { font-weight: 700; color: var(--zn-ink); }
    .sq-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); overflow-wrap: anywhere; }

    .sq-reason { display: flex; flex-direction: column; gap: 2px; }

    .sq-chip {
        align-self: flex-start;
        font-size: var(--zn-fs-xs);
        font-weight: 600;
        padding: 2px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .sq-chip-overdue   { background: var(--hf-overdue-soft);  color: var(--hf-overdue); }
    .sq-chip-attention { background: var(--hf-attention-soft); color: var(--hf-attention-ink); }
    .sq-chip-progress  { background: var(--hf-progress-soft); color: var(--hf-progress); }
    .sq-chip-done      { background: var(--hf-done-soft);     color: var(--hf-done); }

    .sq-empty {
        border: 1px dashed var(--zn-line);
        border-radius: var(--zn-radius-lg);
        padding: 32px 20px;
        text-align: center;
        color: var(--zn-ink-3);
    }

    .sq-loading { padding: 28px; text-align: center; color: var(--zn-ink-3); }

    @media (max-width: 991.98px) {
        .sq-row { grid-template-columns: 4px minmax(0, 1fr); row-gap: 10px; }
        .sq-reason, .sq-posting, .sq-go { grid-column: 2; padding-left: 14px; }
        .sq-go { justify-self: start; }
    }
</style>

<div class="sq-head">
    <div>
        <h5 class="mb-1">Screening</h5>
        <div class="sq-sub">
            Open applications that need attention. This is a work list — opening a row is where
            the work happens; nothing here changes an application on its own.
        </div>
    </div>
    <span class="sq-sub" id="sq-count"></span>
</div>

<div class="sq-filters" id="sq-filters" role="group" aria-label="Filter the queue">
    <button type="button" class="sq-filter is-on" data-reason="all" aria-pressed="true">
        All <span class="sq-filter-count" id="sq-count-all">0</span>
    </button>
    @foreach ($reasons as $key => $reason)
        <button type="button" class="sq-filter" data-reason="{{ $key }}" aria-pressed="false">
            {{ $reason['label'] }} <span class="sq-filter-count" id="sq-count-{{ $key }}">0</span>
        </button>
    @endforeach
</div>

<div id="sq-list" class="sq-list" aria-live="polite">
    <div class="sq-loading">Loading the queue…</div>
</div>

<div class="sq-empty" id="sq-empty" style="display:none;"></div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;

        let ROWS = [];
        let filter = 'all';

        /** Escapes a value for markup built here as a string. */
        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function shortDate(value) {
            if (!value) { return ''; }
            const d = new Date(value.replace(' ', 'T'));
            return isNaN(d) ? value : d.toLocaleDateString(undefined,
                { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function rowHtml(row) {
            const profile = BASE_URL + '/applicant/info/' + encodeURIComponent(row.app_id);

            // Send HR straight to the tab that matches the reason, so the
            // queue hands off to the work rather than to a profile.
            const target = (row.reason === 'access_requested')
                ? profile + '/assessment-access'
                : (row.documents ? profile + '/documents' : profile);

            const docs = row.documents
                ? esc(row.documents.accepted + '/' + row.documents.required + ' documents')
                : '';

            return '' +
            '<article class="sq-row">' +
              '<span class="sq-stripe sq-stripe-' + esc(row.reason_tone) + '" aria-hidden="true"></span>' +
              '<div class="sq-who">' +
                '<span class="sq-name">' + esc(row.applicant_name) + '</span>' +
                '<span class="sq-sub">' + esc(row.app_email || '') + '</span>' +
                '<span class="sq-sub">Applied ' + esc(shortDate(row.applied_at)) + '</span>' +
              '</div>' +
              '<div class="sq-reason">' +
                '<span class="sq-chip sq-chip-' + esc(row.reason_tone) + '">' + esc(row.reason_label) + '</span>' +
                '<span class="sq-sub">' + esc(row.detail) + '</span>' +
              '</div>' +
              '<div class="sq-posting">' +
                '<div class="sq-sub">' + esc(row.posting_title || 'Posting no longer available') + '</div>' +
                '<div class="sq-sub">' + esc(row.mr_no || '') + (docs ? ' · ' + docs : '') + '</div>' +
              '</div>' +
              '<div class="sq-go">' +
                '<a class="btn btn-sm btn-outline-primary" href="' + target + '">Open</a>' +
              '</div>' +
            '</article>';
        }

        function render() {
            const shown = filter === 'all' ? ROWS : ROWS.filter(r => r.reason === filter);

            $('#sq-list').html(shown.map(rowHtml).join(''));
            $('#sq-count').text(
                shown.length === ROWS.length
                    ? ROWS.length + ' open ' + (ROWS.length === 1 ? 'application' : 'applications')
                    : 'Showing ' + shown.length + ' of ' + ROWS.length
            );

            const $empty = $('#sq-empty');
            if (shown.length) {
                $empty.hide();
            } else {
                $empty.text(ROWS.length
                    ? 'Nothing in the queue under this filter.'
                    : 'Nothing needs attention. Every open application is up to date.').show();
            }
        }

        function load() {
            $.getJSON('{{ route('recruitment.screening.data') }}')
                .done(function (json) {
                    ROWS = json.data || [];
                    $('#sq-count-all').text(ROWS.length);
                    Object.entries(json.counts || {}).forEach(([key, count]) => {
                        $('#sq-count-' + key).text(count);
                    });
                    render();
                })
                .fail(function (xhr) {
                    console.error('[screening]', xhr.status, xhr.responseText);
                    $('#sq-list').empty();
                    $('#sq-empty').text('Could not load the queue. Please refresh — if it keeps '
                        + 'happening, your session may have expired.').show();
                });
        }

        $('#sq-filters').on('click', '.sq-filter', function () {
            $('.sq-filter').removeClass('is-on').attr('aria-pressed', 'false');
            $(this).addClass('is-on').attr('aria-pressed', 'true');
            filter = $(this).data('reason');
            render();
        });

        load();
    });
</script>
