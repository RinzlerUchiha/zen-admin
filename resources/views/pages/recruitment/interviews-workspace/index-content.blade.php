{{--
| The interview workspace.
|
| Every interview HR has, across applications, in four views: what is due,
| what still has no date, who owes an evaluation, and how the load falls
| across interviewers.
|
| It reads existing state only. Nothing on this page schedules, assigns,
| marks held or evaluates — every row links to the per-application interview
| page, which remains the only place any of that happens.
--}}
<style>
    .iw-head {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .iw-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }

    .iw-tab {
        border: 1px solid var(--zn-line-2);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: 20px;
        padding: 4px 13px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        cursor: pointer;
    }

    .iw-tab:hover:not(.is-on) { border-color: var(--zn-accent); color: var(--zn-accent-dark); }
    .iw-tab.is-on { background: var(--zn-accent); border-color: var(--zn-accent); color: var(--zn-on-accent); }
    .iw-tab-count { opacity: .75; font-weight: 500; }

    /* The one number worth seeing before anything is clicked: an interview
       whose time has passed and that nobody has said happened yet. */
    .iw-tab .iw-overdue {
        background: var(--hf-overdue, var(--zn-warn));
        color: var(--zn-on-accent);
        border-radius: 10px;
        padding: 0 6px;
        margin-left: 4px;
        font-size: var(--zn-fs-xs);
    }

    .iw-list { display: flex; flex-direction: column; gap: 8px; }

    .iw-row {
        display: grid;
        grid-template-columns: 4px minmax(0, 1.8fr) minmax(0, 1.3fr) minmax(0, 1.2fr) auto;
        align-items: center;
        gap: 14px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 12px 14px 12px 0;
        overflow: hidden;
    }

    .iw-row:hover { border-color: var(--zn-accent); }

    .iw-stripe { align-self: stretch; }
    .iw-stripe-overdue   { background: var(--hf-overdue, var(--zn-warn)); }
    .iw-stripe-attention { background: var(--hf-attention, var(--zn-accent)); }
    .iw-stripe-progress  { background: var(--hf-progress, var(--zn-line-2)); }
    .iw-stripe-done      { background: var(--hf-done, var(--zn-ok)); }

    .iw-who { display: flex; flex-direction: column; gap: 1px; min-width: 0; padding-left: 14px; }
    .iw-name { font-weight: 700; color: var(--zn-ink); }
    .iw-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); overflow-wrap: anywhere; }

    .iw-when { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .iw-chip {
        display: inline-block;
        align-self: flex-start;
        border-radius: 20px;
        padding: 1px 9px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
    }
    .iw-chip-overdue   { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .iw-chip-attention { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .iw-chip-done      { background: var(--zn-ok-soft, #e8f5e9); color: var(--zn-ok); }

    .iw-empty {
        border: 1px dashed var(--zn-line-2);
        border-radius: var(--zn-radius-lg);
        padding: 28px 16px;
        text-align: center;
        color: var(--zn-ink-3);
    }

    /* Interviewer load reads as a table: the names line up and the numbers
       are meant to be compared down the column. */
    .iw-load { width: 100%; border-collapse: collapse; }
    .iw-load th, .iw-load td {
        padding: 9px 12px;
        border-bottom: 1px solid var(--zn-line);
        text-align: left;
        font-size: var(--zn-fs);
    }
    .iw-load th {
        font-size: var(--zn-fs-xs);
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--zn-ink-3);
    }
    .iw-load td.iw-num, .iw-load th.iw-num { text-align: right; font-variant-numeric: tabular-nums; }
    .iw-load tr:last-child td { border-bottom: 0; }
    .iw-owed { font-weight: 800; color: var(--zn-warn); }

    @media (max-width: 767.98px) {
        .iw-row { grid-template-columns: 4px minmax(0, 1fr); row-gap: 8px; padding-bottom: 14px; }
        .iw-row > :not(.iw-stripe) { grid-column: 2; }
    }
</style>

<div class="iw-head">
    <div>
        <h5 class="mb-1">Interviews</h5>
        <div class="iw-sub" id="iw-count">Loading…</div>
    </div>
</div>

<div class="iw-tabs" id="iw-tabs" role="group" aria-label="Interview views">
    <button type="button" class="iw-tab is-on" data-view="due" aria-pressed="true">
        Due &amp; upcoming <span class="iw-tab-count" id="iw-count-due">0</span><span class="iw-overdue" id="iw-count-overdue" style="display:none;">0</span>
    </button>
    <button type="button" class="iw-tab" data-view="needs_scheduling" aria-pressed="false">
        Needs scheduling <span class="iw-tab-count" id="iw-count-needs_scheduling">0</span>
    </button>
    <button type="button" class="iw-tab" data-view="evaluations_outstanding" aria-pressed="false">
        Evaluations outstanding <span class="iw-tab-count" id="iw-count-evaluations_outstanding">0</span>
    </button>
    <button type="button" class="iw-tab" data-view="interviewer_load" aria-pressed="false">
        Interviewer load <span class="iw-tab-count" id="iw-count-interviewer_load">0</span>
    </button>
</div>

<div id="iw-list" class="iw-list" aria-live="polite"></div>
<div class="iw-empty" id="iw-empty" style="display:none;"></div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;

        let DATA = null;
        let view = 'due';

        /** Escapes a value for markup built here as a string. */
        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function when(value) {
            if (!value) { return ''; }
            const d = new Date(String(value).replace(' ', 'T'));
            return isNaN(d) ? value : d.toLocaleString(undefined,
                { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        }

        function dayOnly(value) {
            if (!value) { return ''; }
            const d = new Date(String(value).replace(' ', 'T'));
            return isNaN(d) ? value : d.toLocaleDateString(undefined,
                { year: 'numeric', month: 'short', day: 'numeric' });
        }

        /* Every row goes to the same place: the application's own interview
           page. This workspace finds the work; that page does it. */
        function interviewsUrl(row) {
            return BASE_URL + '/recruitment/applications/' + encodeURIComponent(row.application_id) + '/interviews';
        }

        function shell(row, stripe, middle, right) {
            return '' +
            '<article class="iw-row">' +
              '<span class="iw-stripe iw-stripe-' + stripe + '" aria-hidden="true"></span>' +
              '<div class="iw-who">' +
                '<span class="iw-name">' + esc(row.applicant_name) + '</span>' +
                '<span class="iw-sub">' + esc(row.posting_title || 'Posting no longer available') + '</span>' +
                '<span class="iw-sub">' + esc(row.mr_no || '') + '</span>' +
              '</div>' +
              middle +
              right +
              '<div><a class="btn btn-sm btn-outline-primary" href="' + interviewsUrl(row) + '">Open</a></div>' +
            '</article>';
        }

        function dueRow(row) {
            const overdue = row.overdue;
            const where = row.has_link ? 'Online' : (row.venue || 'No venue set');

            return shell(row, overdue ? 'overdue' : 'attention',
                '<div class="iw-when">' +
                    '<span class="iw-chip iw-chip-' + (overdue ? 'overdue' : 'attention') + '">' +
                        (overdue ? 'Needs an outcome' : 'Scheduled') + '</span>' +
                    '<span class="iw-sub">' + esc(when(row.scheduled_at)) + '</span>' +
                '</div>',
                '<div class="iw-when">' +
                    '<span class="iw-sub">' + esc(row.round) + '</span>' +
                    '<span class="iw-sub">' + esc(where) + '</span>' +
                    '<span class="iw-sub">' + esc(row.interviewers.join(', ') || 'No interviewer assigned') + '</span>' +
                '</div>');
        }

        function schedulingRow(row) {
            return shell(row, 'progress',
                '<div class="iw-when">' +
                    '<span class="iw-chip iw-chip-attention">No date yet</span>' +
                    '<span class="iw-sub">' + esc(row.round) + '</span>' +
                '</div>',
                '<div class="iw-when">' +
                    '<span class="iw-sub">' +
                        (row.attempts
                            ? esc(row.attempts + ' earlier ' + (row.attempts === 1 ? 'attempt' : 'attempts')
                                + ' cancelled or missed')
                            : 'Not scheduled yet') +
                    '</span>' +
                '</div>');
        }

        function evaluationRow(row) {
            const decision = row.kind === 'decision';

            return shell(row, decision ? 'done' : 'attention',
                '<div class="iw-when">' +
                    '<span class="iw-chip iw-chip-' + (decision ? 'done' : 'attention') + '">' +
                        (decision ? 'Ready to conclude' : 'Evaluation outstanding') + '</span>' +
                    '<span class="iw-sub">' + esc(row.round) + ' · held ' + esc(dayOnly(row.held_at)) + '</span>' +
                '</div>',
                '<div class="iw-when">' +
                    '<span class="iw-sub">' + esc(row.received + ' of ' + row.assigned + ' received') + '</span>' +
                    '<span class="iw-sub">' +
                        (decision
                            ? 'All evaluations in — record what the round concluded'
                            : 'Waiting on ' + esc(row.awaiting.join(', '))) +
                    '</span>' +
                '</div>');
        }

        function loadTable(rows) {
            return '' +
            '<table class="iw-load">' +
              '<thead><tr>' +
                '<th>Interviewer</th>' +
                '<th class="iw-num">Scheduled</th>' +
                '<th class="iw-num">Held</th>' +
                '<th class="iw-num">Evaluations owed</th>' +
              '</tr></thead><tbody>' +
              rows.map(r =>
                '<tr>' +
                  '<td>' + esc(r.name) + '<div class="iw-sub">' + esc(r.empno) + '</div></td>' +
                  '<td class="iw-num">' + r.scheduled + '</td>' +
                  '<td class="iw-num">' + r.held + '</td>' +
                  '<td class="iw-num' + (r.evaluations_owed ? ' iw-owed' : '') + '">' + r.evaluations_owed + '</td>' +
                '</tr>').join('') +
              '</tbody>' +
            '</table>';
        }

        const EMPTY = {
            due: 'No interview is due or coming up in the next week.',
            needs_scheduling: 'Every open interview round has a date.',
            evaluations_outstanding: 'Every interview that has taken place has been written up and concluded.',
            interviewer_load: 'Nobody is carrying an interview at the moment.'
        };

        function render() {
            if (!DATA) { return; }

            const $list = $('#iw-list');
            const $empty = $('#iw-empty');
            let html = '';
            let n = 0;

            if (view === 'due') {
                const rows = DATA.due.concat(DATA.upcoming);
                n = rows.length;
                html = rows.map(dueRow).join('');
            } else if (view === 'needs_scheduling') {
                n = DATA.needs_scheduling.length;
                html = DATA.needs_scheduling.map(schedulingRow).join('');
            } else if (view === 'evaluations_outstanding') {
                n = DATA.evaluations_outstanding.length;
                html = DATA.evaluations_outstanding.map(evaluationRow).join('');
            } else {
                n = DATA.interviewer_load.length;
                html = n ? loadTable(DATA.interviewer_load) : '';
            }

            $list.html(html);
            n ? $empty.hide() : $empty.text(EMPTY[view]).show();
        }

        function load() {
            $.getJSON('{{ route('recruitment.interviews-workspace.data') }}')
                .done(function (json) {
                    DATA = json;

                    const c = json.counts || {};
                    $('#iw-count-due').text((c.due || 0) + (c.upcoming || 0));
                    $('#iw-count-needs_scheduling').text(c.needs_scheduling || 0);
                    $('#iw-count-evaluations_outstanding').text(c.evaluations_outstanding || 0);
                    $('#iw-count-interviewer_load').text(c.interviewer_load || 0);

                    $('#iw-count-overdue').toggle(!!c.overdue).text(c.overdue || 0);

                    const total = (c.due || 0) + (c.upcoming || 0) + (c.needs_scheduling || 0)
                        + (c.evaluations_outstanding || 0);
                    $('#iw-count').text(total
                        ? total + ' ' + (total === 1 ? 'item needs' : 'items need') + ' attention across all applications'
                        : 'Nothing outstanding across any application.');

                    render();
                })
                .fail(function (xhr) {
                    console.error('[interviews]', xhr.status, xhr.responseText);
                    $('#iw-list').empty();
                    $('#iw-empty').text('Could not load interviews. Please refresh — if it keeps '
                        + 'happening, your session may have expired.').show();
                });
        }

        $('#iw-tabs').on('click', '.iw-tab', function () {
            $('.iw-tab').removeClass('is-on').attr('aria-pressed', 'false');
            $(this).addClass('is-on').attr('aria-pressed', 'true');
            view = $(this).data('view');
            render();
        });

        load();
    });
</script>
