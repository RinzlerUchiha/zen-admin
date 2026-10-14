{{--
| The Candidate Pool.
|
| People HR has already met who did not go forward on one application, kept
| so the next posting does not start from nothing. The pool is per
| APPLICATION: the same person can sit here against one posting and be live
| on another.
|
| Everything on this page is read from rules that already exist — which
| statuses pool, when somebody may reapply, whether a bar sits on them — and
| putting a candidate forward goes through the recommendation endpoints, which
| refuse a blacklisted applicant inside their own lock whatever this page
| shows.
--}}
<style>
    .cp-head {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .cp-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .cp-tools { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; align-items: center; }

    .cp-search-wrap { position: relative; flex: 1 1 260px; min-width: 0; }

    .cp-search-wrap input {
        width: 100%;
        border: 1px solid var(--zn-line-2);
        border-radius: var(--zn-radius-lg);
        padding: 7px 12px 7px 32px;
        font-size: var(--zn-fs);
    }

    .cp-search-ico {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--zn-ink-3);
        pointer-events: none;
    }

    .cp-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }

    .cp-filter {
        border: 1px solid var(--zn-line-2);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: 20px;
        padding: 4px 13px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        cursor: pointer;
    }

    .cp-filter:hover:not(.is-on) { border-color: var(--zn-accent); color: var(--zn-accent-dark); }
    .cp-filter.is-on { background: var(--zn-accent); border-color: var(--zn-accent); color: var(--zn-on-accent); }
    .cp-filter-count { opacity: .75; font-weight: 500; }

    .cp-list { display: flex; flex-direction: column; gap: 8px; }

    .cp-row {
        display: grid;
        grid-template-columns: 4px minmax(0, 1.7fr) minmax(0, 1.3fr) minmax(0, 1.1fr) auto;
        align-items: center;
        gap: 14px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 12px 14px 12px 0;
        overflow: hidden;
    }

    .cp-row:hover { border-color: var(--zn-accent); }

    .cp-stripe { align-self: stretch; }
    .cp-stripe-ready   { background: var(--hf-done, var(--zn-ok)); }
    .cp-stripe-waiting { background: var(--hf-progress, var(--zn-line-2)); }
    .cp-stripe-barred  { background: var(--hf-overdue, var(--zn-warn)); }

    .cp-who { display: flex; flex-direction: column; gap: 1px; min-width: 0; padding-left: 14px; }
    .cp-name { font-weight: 700; color: var(--zn-ink); }

    .cp-cell { display: flex; flex-direction: column; gap: 2px; min-width: 0; }

    .cp-chip {
        display: inline-block;
        align-self: flex-start;
        border-radius: 20px;
        padding: 1px 9px;
        font-size: var(--zn-fs-xs);
        font-weight: 700;
    }
    .cp-chip-ready   { background: var(--zn-ok-soft, #e8f5e9); color: var(--zn-ok); }
    .cp-chip-waiting { background: var(--zn-surface-2); color: var(--zn-ink-3); }
    .cp-chip-barred  { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .cp-chip-status  { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }

    .cp-empty {
        border: 1px dashed var(--zn-line-2);
        border-radius: var(--zn-radius-lg);
        padding: 28px 16px;
        text-align: center;
        color: var(--zn-ink-3);
    }

    @media (max-width: 767.98px) {
        .cp-row { grid-template-columns: 4px minmax(0, 1fr); row-gap: 8px; padding-bottom: 14px; }
        .cp-row > :not(.cp-stripe) { grid-column: 2; }
    }
</style>

<div class="cp-head">
    <div>
        <h5 class="mb-1">Candidate Pool</h5>
        <div class="cp-sub">
            People already met who did not go forward on one application. Kept per application —
            the same person may still be live on another posting. Nothing here reopens anything;
            putting somebody forward creates a fresh application against the posting you choose.
        </div>
    </div>
    <span class="cp-sub" id="cp-count"></span>
</div>

<div class="cp-tools">
    <span class="cp-search-wrap">
        <i class="bi bi-search cp-search-ico" aria-hidden="true"></i>
        <input type="search" id="cp-search" aria-label="Search the candidate pool"
            placeholder="Search name, email, position, MR no…">
    </span>
    <select id="cp-posting" class="form-select form-select-sm" style="width:auto;"
        aria-label="Filter by the posting they applied to">
        <option value="">Every posting</option>
    </select>
</div>

<div class="cp-filters" id="cp-filters" role="group" aria-label="Filter the pool">
    <button type="button" class="cp-filter is-on" data-filter="all" aria-pressed="true">
        Everyone <span class="cp-filter-count" id="cp-count-all">0</span>
    </button>
    <button type="button" class="cp-filter" data-filter="ready" aria-pressed="false">
        Can be considered now <span class="cp-filter-count" id="cp-count-ready">0</span>
    </button>
    <button type="button" class="cp-filter" data-filter="waiting" aria-pressed="false">
        Still in cooldown <span class="cp-filter-count" id="cp-count-waiting">0</span>
    </button>
    @foreach ($poolStatuses as $status)
        <button type="button" class="cp-filter" data-filter="status:{{ $status }}" aria-pressed="false">
            {{ $status }} <span class="cp-filter-count" id="cp-count-status-{{ \Illuminate\Support\Str::slug($status) }}">0</span>
        </button>
    @endforeach
</div>

<div id="cp-list" class="cp-list" aria-live="polite"></div>
<div class="cp-empty" id="cp-empty" style="display:none;"></div>

{{-- Putting somebody forward. The same two endpoints the intake list uses:
     the server decides what may be offered and refuses a barred applicant. --}}
<div class="modal fade" id="cpRecModal" tabindex="-1" aria-labelledby="cpRecTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="cpRecTitle">Consider for another posting</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    This creates a <strong>new application</strong> for them against the posting you pick.
                    The closed one stays exactly as it is, and nothing is sent to the applicant.
                </div>
                <div id="cpRecBlocked" class="alert alert-danger py-2 small d-none"></div>
                <div id="cpRecError" class="alert alert-danger py-2 small d-none"></div>
                <div class="mb-2">
                    <label class="form-label" for="cpRecTarget">Posting</label>
                    <select id="cpRecTarget" class="form-select"></select>
                </div>
                <label class="form-label mt-2" for="cpRecReason">Reason
                    <span class="text-muted">(optional, kept with the recommendation)</span></label>
                <textarea id="cpRecReason" class="form-control" rows="3"
                    placeholder="e.g. strong on the technical side, better suited to this post"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cpRecSubmit">Create application</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        /* Built from the route, so the application id is substituted rather
           than the URL assembled by hand. */
        const REC = {
            targets: @json(route('recruitment.applicant-intake.recommendation.targets', ['application' => '__ID__'])),
            store: @json(route('recruitment.applicant-intake.recommendation.store', ['application' => '__ID__']))
        };

        let ROWS = [];
        let filter = 'all';
        let current = null;

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function day(value) {
            if (!value) { return ''; }
            const d = new Date(String(value).replace(' ', 'T'));
            return isNaN(d) ? value : d.toLocaleDateString(undefined,
                { year: 'numeric', month: 'short', day: 'numeric' });
        }

        /*
        | Who can be put forward right now.
        |
        | Read from reapply_on, which the SERVER works out from the closure
        | reason and the cooldown table. This page never computes a date or
        | decides eligibility; it only says what it was told.
        */
        function readiness(row) {
            if (row.blacklisted) { return 'barred'; }

            return row.reapply_on ? 'waiting' : 'ready';
        }

        function matches(row) {
            if (filter === 'all') { return true; }
            if (filter === 'ready') { return readiness(row) === 'ready'; }
            if (filter === 'waiting') { return readiness(row) === 'waiting'; }
            if (filter.startsWith('status:')) { return row.status === filter.slice(7); }

            return true;
        }

        function searched(row) {
            const q = ($('#cp-search').val() || '').trim().toLowerCase();
            const posting = $('#cp-posting').val();

            if (posting && (row.posting_title || '') !== posting) { return false; }
            if (!q) { return true; }

            return [row.applicant_name, row.app_email, row.app_mobile, row.posting_title, row.mr_no]
                .some(v => (v || '').toString().toLowerCase().includes(q));
        }

        function rowHtml(row) {
            const state = readiness(row);
            const profile = BASE_URL + '/applicant/info/' + encodeURIComponent(row.app_id);

            const when = state === 'barred'
                ? '<span class="cp-chip cp-chip-barred">Blacklisted</span>'
                : (state === 'waiting'
                    ? '<span class="cp-chip cp-chip-waiting">Can reapply ' + esc(day(row.reapply_on)) + '</span>'
                    : '<span class="cp-chip cp-chip-ready">Can be considered now</span>');

            /* Barred and in-cooldown candidates keep their row and lose the
               button: the reason is on the row, so nobody has to find out by
               being refused. */
            const action = state === 'ready'
                ? '<button type="button" class="btn btn-sm btn-outline-primary js-cp-recommend" ' +
                  'data-application="' + esc(row.id) + '" data-name="' + esc(row.applicant_name) + '">' +
                  'Consider</button>'
                : '<a class="btn btn-sm btn-outline-secondary" href="' + profile + '">Open</a>';

            return '' +
            '<article class="cp-row">' +
              '<span class="cp-stripe cp-stripe-' + state + '" aria-hidden="true"></span>' +
              '<div class="cp-who">' +
                '<span class="cp-name">' + esc(row.applicant_name) + '</span>' +
                '<span class="cp-sub">' + esc(row.app_email || '') + '</span>' +
                '<span class="cp-sub">' + esc(row.app_mobile || '') + '</span>' +
              '</div>' +
              '<div class="cp-cell">' +
                '<span class="cp-chip cp-chip-status">' + esc(row.status) + '</span>' +
                '<span class="cp-sub">Closed ' + esc(day(row.closed_at)) + '</span>' +
                (row.closed_note ? '<span class="cp-sub">' + esc(row.closed_note) + '</span>' : '') +
              '</div>' +
              '<div class="cp-cell">' +
                '<span class="cp-sub">' + esc(row.posting_title || 'Posting no longer available') + '</span>' +
                '<span class="cp-sub">' + esc(row.mr_no || '') + '</span>' +
                when +
              '</div>' +
              '<div>' + action + '</div>' +
            '</article>';
        }

        function render() {
            const shown = ROWS.filter(r => matches(r) && searched(r));

            $('#cp-list').html(shown.map(rowHtml).join(''));
            $('#cp-count').text(shown.length === ROWS.length
                ? ROWS.length + ' in the pool'
                : 'Showing ' + shown.length + ' of ' + ROWS.length);

            const $empty = $('#cp-empty');
            if (shown.length) {
                $empty.hide();
            } else {
                $empty.text(ROWS.length
                    ? 'Nobody in the pool matches this.'
                    : 'The pool is empty. Applications that close as withdrawn, non-responsive '
                      + 'or not selected are kept here.').show();
            }
        }

        function counts() {
            $('#cp-count-all').text(ROWS.length);
            $('#cp-count-ready').text(ROWS.filter(r => readiness(r) === 'ready').length);
            $('#cp-count-waiting').text(ROWS.filter(r => readiness(r) === 'waiting').length);

            @foreach ($poolStatuses as $status)
                $('#cp-count-status-{{ \Illuminate\Support\Str::slug($status) }}')
                    .text(ROWS.filter(r => r.status === @json($status)).length);
            @endforeach
        }

        function postings() {
            const seen = [...new Set(ROWS.map(r => r.posting_title).filter(Boolean))].sort();
            const $sel = $('#cp-posting');

            seen.forEach(t => $sel.append($('<option>').attr('value', t).text(t)));
        }

        function load() {
            $.getJSON('{{ route('recruitment.candidate-pool.data') }}')
                .done(function (json) {
                    ROWS = json.data || [];
                    counts();
                    postings();
                    render();
                })
                .fail(function (xhr) {
                    console.error('[candidate-pool]', xhr.status, xhr.responseText);
                    $('#cp-list').empty();
                    $('#cp-empty').text('Could not load the pool. Please refresh — if it keeps '
                        + 'happening, your session may have expired.').show();
                });
        }

        $('#cp-filters').on('click', '.cp-filter', function () {
            $('.cp-filter').removeClass('is-on').attr('aria-pressed', 'false');
            $(this).addClass('is-on').attr('aria-pressed', 'true');
            filter = $(this).data('filter');
            render();
        });

        $('#cp-search').on('input', render);
        $('#cp-posting').on('change', render);

        /* ---- putting one forward -------------------------------------- */

        $('#cp-list').on('click', '.js-cp-recommend', async function () {
            current = $(this).data('application');

            $('#cpRecTitle').text('Consider ' + $(this).data('name') + ' for another posting');
            $('#cpRecBlocked').addClass('d-none').text('');
            $('#cpRecError').addClass('d-none').text('');
            $('#cpRecReason').val('');
            $('#cpRecTarget').html('<option>Loading…</option>').prop('disabled', true);
            $('#cpRecSubmit').prop('disabled', true);
            $('#cpRecModal').modal('show');

            try {
                const data = await $.getJSON(REC.targets.replace('__ID__', current));
                const targets = data.targets || [];

                /*
                | Rendered exactly as the intake list renders it, because it is
                | the same answer from the same endpoint.
                |
                | Grouped by department, since position titles repeat across
                | them. The MR number is shown because two postings can carry
                | the same title — two openings for the same role on different
                | requests — and without it HR is choosing between identical
                | lines. Ineligible postings are shown DISABLED with the
                | reason rather than dropped: a list that silently omits
                | things reads as a broken list.
                */
                let html = '<option value="">Choose a job post…</option>';
                let lastDept = null;
                let open = false;

                targets.forEach(function (t) {
                    const dept = t.department || 'Other';

                    if (dept !== lastDept) {
                        if (open) { html += '</optgroup>'; }
                        html += '<optgroup label="' + esc(dept) + '">';
                        lastDept = dept;
                        open = true;
                    }

                    html += '<option value="' + esc(t.id) + '"' + (t.eligible ? '' : ' disabled') + '>' +
                        esc(t.title) +
                        (t.mr_no ? ' — ' + esc(t.mr_no) : '') +
                        (t.eligible ? '' : ' · ' + esc(t.reason)) +
                        '</option>';
                });

                if (open) { html += '</optgroup>'; }

                const $sel = $('#cpRecTarget').html(html);

                /* The server says when the applicant themselves is the
                   obstacle; it is one fact about the person, not forty about
                   the postings. */
                if (data.blocked) {
                    $('#cpRecBlocked').removeClass('d-none').text(data.blocked);
                    $sel.prop('disabled', true);
                } else if (!targets.length) {
                    $('#cpRecBlocked').removeClass('d-none')
                        .text('There are no published job posts to put them forward to.');
                } else if (!targets.some(t => t.eligible)) {
                    /* Postings exist but every one is ruled out for this
                       applicant — already recommended, already applied, or
                       the post they came from. Saying so beats an enabled
                       button over a list of disabled lines. */
                    $('#cpRecBlocked').removeClass('d-none')
                        .text('Every published post is already ruled out for this applicant. '
                            + 'The reason is shown against each one.');
                    $sel.prop('disabled', false);
                } else {
                    $sel.prop('disabled', false);
                    $('#cpRecSubmit').prop('disabled', false);
                }
            } catch (err) {
                $('#cpRecError').removeClass('d-none').text('Could not load the postings to choose from.');
            }
        });

        $('#cpRecSubmit').on('click', async function () {
            const $btn = $(this);
            const original = $btn.text();

            $btn.prop('disabled', true).text('Creating…');
            $('#cpRecError').addClass('d-none').text('');

            try {
                const res = await fetch(REC.store.replace('__ID__', current), {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        target_job_posting_id: $('#cpRecTarget').val(),
                        reason: $('#cpRecReason').val() || null
                    })
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(body.message
                        || (body.errors && Object.values(body.errors)[0][0])
                        || ('Status ' + res.status));
                }

                $('#cpRecModal').modal('hide');
                location.reload();
            } catch (err) {
                $('#cpRecError').removeClass('d-none').text(err.message);
                $btn.prop('disabled', false).text(original);
            }
        });

        load();
    });
</script>
