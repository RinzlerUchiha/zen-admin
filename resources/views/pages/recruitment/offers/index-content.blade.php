{{--
| Offers.
|
| Two lists: candidates whose recruitment steps are all finished and who a
| decision is now due on, and the offers that exist with where each has got
| to.
|
| An offer is terms put to somebody and their answer. It is not a stage of the
| application and it is NOT a hire — accepting one is the applicant saying
| yes, and the hire is recorded on the application when it actually happens.
| Nothing on this page changes an application's status.
--}}
<style>
    .of-head { display: flex; flex-wrap: wrap; gap: 10px; align-items: baseline; justify-content: space-between; margin-bottom: 14px; }
    .of-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .of-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
    .of-tab {
        border: 1px solid var(--zn-line-2);
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        border-radius: 20px;
        padding: 4px 13px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        cursor: pointer;
    }
    .of-tab:hover:not(.is-on) { border-color: var(--zn-accent); color: var(--zn-accent-dark); }
    .of-tab.is-on { background: var(--zn-accent); border-color: var(--zn-accent); color: var(--zn-on-accent); }
    .of-tab-count { opacity: .75; font-weight: 500; }

    .of-list { display: flex; flex-direction: column; gap: 8px; }

    .of-row {
        display: grid;
        grid-template-columns: 4px minmax(0, 1.6fr) minmax(0, 1.2fr) minmax(0, 1.2fr) auto;
        align-items: center;
        gap: 14px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 12px 14px 12px 0;
        overflow: hidden;
    }
    .of-row:hover { border-color: var(--zn-accent); }

    .of-stripe { align-self: stretch; }
    .of-stripe-draft    { background: var(--hf-progress, var(--zn-line-2)); }
    .of-stripe-waiting  { background: var(--hf-progress, var(--zn-line-2)); }
    .of-stripe-revision { background: var(--hf-overdue, var(--zn-warn)); }
    .of-stripe-issued   { background: var(--hf-attention, var(--zn-accent)); }
    .of-stripe-lapsed   { background: var(--hf-overdue, var(--zn-warn)); }
    .of-stripe-accepted { background: var(--hf-done, var(--zn-ok)); }
    .of-stripe-settled  { background: var(--zn-line-2); }
    .of-stripe-ready    { background: var(--hf-done, var(--zn-ok)); }

    .of-who { display: flex; flex-direction: column; gap: 1px; min-width: 0; padding-left: 14px; }
    .of-name { font-weight: 700; color: var(--zn-ink); }
    .of-cell { display: flex; flex-direction: column; gap: 2px; min-width: 0; }

    .of-chip {
        display: inline-block; align-self: flex-start;
        border-radius: 20px; padding: 1px 9px;
        font-size: var(--zn-fs-xs); font-weight: 700;
    }
    .of-chip-draft    { background: var(--zn-surface-2); color: var(--zn-ink-3); }
    .of-chip-waiting  { background: var(--zn-surface-2); color: var(--zn-ink-2); }
    .of-chip-revision { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .of-chip-issued   { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .of-chip-lapsed   { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .of-chip-accepted { background: var(--zn-ok-soft, #e8f5e9); color: var(--zn-ok); }
    .of-chip-settled  { background: var(--zn-surface-2); color: var(--zn-ink-3); }

    .of-acts { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }

    .of-empty {
        border: 1px dashed var(--zn-line-2);
        border-radius: var(--zn-radius-lg);
        padding: 28px 16px; text-align: center; color: var(--zn-ink-3);
    }

    .of-note {
        border: 1px solid var(--zn-line);
        border-left: 3px solid var(--zn-accent);
        background: var(--zn-surface-2);
        border-radius: var(--zn-radius);
        padding: 9px 12px;
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
        margin-bottom: 14px;
    }

    .of-trail { list-style: none; margin: 0; padding: 0; }
    .of-trail li { padding: 6px 0; border-bottom: 1px solid var(--zn-line); font-size: var(--zn-fs-sm); }
    .of-trail li:last-child { border-bottom: 0; }

    @media (max-width: 767.98px) {
        .of-row { grid-template-columns: 4px minmax(0, 1fr); row-gap: 8px; padding-bottom: 14px; }
        .of-row > :not(.of-stripe) { grid-column: 2; }
        .of-acts { justify-content: flex-start; }
    }
</style>

<div class="of-head">
    <div>
        <h5 class="mb-1">Offers</h5>
        <div class="of-sub">Terms put to a candidate, and what they said.</div>
    </div>
    <span class="of-sub" id="of-count"></span>
</div>

<div class="of-note">
    <strong>An accepted offer is not a hire.</strong> Accepting records that the candidate said yes.
    The hire is recorded on the application itself, when it actually happens — nothing on this page
    changes an application's status.
</div>

<div class="of-tabs" id="of-tabs" role="group" aria-label="Offer views">
    <button type="button" class="of-tab is-on" data-view="ready" aria-pressed="true">
        Ready for an offer <span class="of-tab-count" id="of-count-ready">0</span>
    </button>
    <button type="button" class="of-tab" data-view="live" aria-pressed="false">
        In play <span class="of-tab-count" id="of-count-live">0</span>
    </button>
    <button type="button" class="of-tab" data-view="settled" aria-pressed="false">
        Settled <span class="of-tab-count" id="of-count-settled">0</span>
    </button>
</div>

<div id="of-list" class="of-list" aria-live="polite"></div>
<div class="of-empty" id="of-empty" style="display:none;"></div>

{{-- Raising a draft. Terms only: putting it to the candidate is a separate
     act, so HR can write the numbers down while still deciding. --}}
<div class="modal fade" id="ofDraftModal" tabindex="-1" aria-labelledby="ofDraftTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="ofDraftTitle">Draft an offer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    This writes a <strong>draft</strong>. Nothing goes to the candidate until you issue it.
                </div>
                <div id="ofDraftError" class="alert alert-danger py-2 small d-none"></div>
                <div class="row g-2">
                    <div class="col-7">
                        <label class="form-label" for="ofSalary">Salary</label>
                        <input type="number" step="0.01" min="0" id="ofSalary" class="form-control">
                    </div>
                    <div class="col-5">
                        <label class="form-label" for="ofCurrency">Currency</label>
                        <input type="text" id="ofCurrency" class="form-control" value="PHP" maxlength="3">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="ofEmployment">Employment status</label>
                        <input type="text" id="ofEmployment" class="form-control" placeholder="e.g. Probationary">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="ofStart">Start date</label>
                        <input type="date" id="ofStart" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="ofExpires">Answer by</label>
                        <input type="date" id="ofExpires" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="ofNotes">Notes</label>
                        <textarea id="ofNotes" class="form-control" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="ofDraftSubmit">Save draft</button>
            </div>
        </div>
    </div>
</div>

{{-- One offer and everything that has happened to it. --}}
<div class="modal fade" id="ofDetailModal" tabindex="-1" aria-labelledby="ofDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="ofDetailTitle">Offer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ofDetailBody"></div>
        </div>
    </div>
</div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const CAN_DECIDE = @json(\Illuminate\Support\Facades\Gate::allows('applicant-applications.decide'));
        /* Hiring is the narrower right: fewer people hold it than hold the one
           that withdraws or declines an application. */
        const CAN_HIRE = @json(\Illuminate\Support\Facades\Gate::allows('applicant.hire'));

        let DATA = null;
        let view = 'ready';
        let drafting = null;

        function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

        function day(v) {
            if (!v) { return ''; }
            const d = new Date(String(v).replace(' ', 'T'));
            return isNaN(d) ? v : d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function money(row) {
            if (row.salary_amount == null) { return 'No salary recorded'; }
            return (row.salary_currency || '') + ' ' + Number(row.salary_amount).toLocaleString();
        }

        /* Overdue is derived, not stored: a SENT offer past its deadline that
           nobody has answered. It is still "sent" and still awaiting a reply
           until a person extends the deadline or ends it. */
        function tone(row) {
            if (row.overdue) { return 'lapsed'; }
            if (row.status === 'draft') { return 'draft'; }
            if (row.status === 'pending_approval') { return 'waiting'; }
            if (row.status === 'revision_requested') { return 'revision'; }
            if (row.status === 'approved' || row.status === 'sent') { return 'issued'; }
            if (row.status === 'accepted' || row.status === 'contract_received') { return 'accepted'; }
            return 'settled';
        }

        /* How each status reads to a person. */
        const STATUS_LABEL = {
            draft: 'Draft',
            pending_approval: 'Awaiting HR Head',
            revision_requested: 'Sent back for revision',
            approved: 'Approved',
            rejected: 'Rejected by HR Head',
            sent: 'With the applicant',
            accepted: 'Accepted',
            contract_received: 'Contract received',
            declined: 'Declined by applicant',
            withdrawn: 'Withdrawn',
            expired: 'Ended, unanswered'
        };

        const VERB = {
            pending_approval: 'Submit for approval',
            sent: 'Send to applicant',
            accepted: 'Record acceptance',
            declined: 'Record decline',
            contract_received: 'Record contract',
            withdrawn: 'Withdraw',
            expired: 'End offer'
        };

        /*
        | Which of HR's own endpoints each move belongs to.
        |
        | Approving, declining and requesting a revision are NOT here: they are
        | the HR Head's, through Zen-Hub. zen-admin has no route for them, so
        | HR cannot approve their own offer from their own workspace.
        */
        const ENDPOINT = {
            pending_approval: 'submit',
            sent: 'send',
            accepted: 'respond',
            declined: 'respond',
            contract_received: 'contract',
            withdrawn: 'transition',
            expired: 'transition'
        };

        function actions(row) {
            /*
            | Recording the hire.
            |
            | A milestone of its own: the contract being signed is not the
            | hire, and for headcount purposes somebody counts as hired once
            | they have been ORIENTED -- which nothing here observes, so HR
            | records it. It does NOT close the application and creates no
            | employee record.
            |
            | Offered on a signed offer because that is where HR is when
            | orientation happens, and the Work Queue sends them here.
            */
            if (CAN_HIRE && row.status === 'contract_received') {
                const hire = '<button type="button" class="btn btn-sm btn-success js-of-hire" '
                    + 'data-application="' + esc(row.application_id) + '">Mark as Hired</button>';

                if (!CAN_DECIDE) { return hire; }

                return hire + (row.allowed || []).filter(to => ENDPOINT[to]).map(to =>
                    '<button type="button" class="btn btn-sm btn-outline-primary js-of-move" '
                    + 'data-offer="' + esc(row.id) + '" data-to="' + esc(to) + '">'
                    + esc(VERB[to] || to) + '</button>').join('');
            }

            if (!CAN_DECIDE) { return ''; }

            /* Only what the server would allow from here, and only the moves
               that are HR's to make. The list comes from OfferWorkflow's own
               table, so the buttons cannot offer a move the service would
               refuse; ENDPOINT then drops the HR Head's three. */
            return (row.allowed || []).filter(to => ENDPOINT[to]).map(to =>
                '<button type="button" class="btn btn-sm btn-outline-primary js-of-move" ' +
                'data-offer="' + esc(row.id) + '" data-to="' + esc(to) + '">' +
                esc(VERB[to] || to) + '</button>').join('');
        }

        function offerRow(row) {
            const t = tone(row);

            return '' +
            '<article class="of-row">' +
              '<span class="of-stripe of-stripe-' + t + '" aria-hidden="true"></span>' +
              '<div class="of-who">' +
                '<span class="of-name">' + esc(row.applicant_name) + '</span>' +
                '<span class="of-sub">' + esc(row.posting_title || 'Posting no longer available') + '</span>' +
                '<span class="of-sub">' + esc(row.mr_no || '') + '</span>' +
              '</div>' +
              '<div class="of-cell">' +
                '<span class="of-chip of-chip-' + t + '">' +
                    esc(row.overdue ? 'Overdue' : (STATUS_LABEL[row.status] || row.status)) + '</span>' +
                '<span class="of-sub">' + esc(money(row)) + '</span>' +
                (row.employment_status ? '<span class="of-sub">' + esc(row.employment_status) + '</span>' : '') +
              '</div>' +
              '<div class="of-cell">' +
                (row.start_date ? '<span class="of-sub">Starts ' + esc(day(row.start_date)) + '</span>' : '') +
                (row.response_deadline ? '<span class="of-sub">Answer by ' + esc(day(row.response_deadline)) +
                    (row.deadline_extended_count ? ' (extended ' + esc(row.deadline_extended_count) + 'x)' : '') +
                    '</span>' : '') +
                (row.sent_at ? '<span class="of-sub">Sent ' + esc(row.sent_at) + '</span>' : '') +
                /* What the HR Head asked to be changed, where HR will see it. */
                (row.revision_reason && row.status === 'revision_requested'
                    ? '<span class="of-sub">HR Head asked: ' + esc(row.revision_reason) + '</span>' : '') +
                (row.rejected_reason && row.status === 'rejected'
                    ? '<span class="of-sub">Rejected: ' + esc(row.rejected_reason) + '</span>' : '') +
              '</div>' +
              '<div class="of-acts">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary js-of-detail" ' +
                'data-offer="' + esc(row.id) + '">History</button>' +
                actions(row) +
              '</div>' +
            '</article>';
        }

        function readyRow(row) {
            return '' +
            '<article class="of-row">' +
              '<span class="of-stripe of-stripe-ready" aria-hidden="true"></span>' +
              '<div class="of-who">' +
                '<span class="of-name">' + esc(row.applicant_name) + '</span>' +
                '<span class="of-sub">' + esc(row.app_email || '') + '</span>' +
              '</div>' +
              '<div class="of-cell">' +
                '<span class="of-chip of-chip-accepted">Steps all finished</span>' +
                '<span class="of-sub">' + esc(row.status) + '</span>' +
              '</div>' +
              '<div class="of-cell">' +
                '<span class="of-sub">' + esc(row.posting_title || 'Posting no longer available') + '</span>' +
                '<span class="of-sub">' + esc(row.mr_no || '') + '</span>' +
              '</div>' +
              '<div class="of-acts">' +
                '<a class="btn btn-sm btn-outline-secondary" href="' + BASE_URL +
                  '/recruitment/applications/' + encodeURIComponent(row.application_id) + '/interviews">Rounds</a>' +
                (CAN_DECIDE
                    ? '<button type="button" class="btn btn-sm btn-primary js-of-draft" ' +
                      'data-application="' + esc(row.application_id) + '" ' +
                      'data-name="' + esc(row.applicant_name) + '">Draft an offer</button>'
                    : '') +
              '</div>' +
            '</article>';
        }

        const EMPTY = {
            ready: 'Nobody has finished every recruitment step yet. Candidates appear here when their '
                 + 'interviews and assessments are all settled.',
            live: 'No offer is in play.',
            settled: 'No offer has been answered yet.'
        };

        function render() {
            if (!DATA) { return; }

            let rows = [];
            let html = '';

            if (view === 'ready') {
                rows = DATA.ready;
                html = rows.map(readyRow).join('');
            } else if (view === 'live') {
                rows = DATA.offers.filter(o => ['draft', 'pending_approval', 'revision_requested',
                    'approved', 'sent'].includes(o.status));
                html = rows.map(offerRow).join('');
            } else {
                rows = DATA.offers.filter(o => ['accepted', 'contract_received', 'rejected',
                    'declined', 'withdrawn', 'expired'].includes(o.status));
                html = rows.map(offerRow).join('');
            }

            $('#of-list').html(html);
            rows.length ? $('#of-empty').hide() : $('#of-empty').text(EMPTY[view]).show();
        }

        function load() {
            $.getJSON('{{ route('recruitment.offers.data') }}')
                .done(function (json) {
                    DATA = json;
                    const c = json.counts || {};

                    $('#of-count-ready').text(c.ready || 0);
                    $('#of-count-live').text((c.draft || 0) + (c.pending_approval || 0)
                        + (c.revision_requested || 0) + (c.approved || 0) + (c.sent || 0));
                    $('#of-count-settled').text((c.accepted || 0) + (c.contract_received || 0)
                        + (c.closed || 0));

                    /* Said as what it is: awaiting an answer, not declined. */
                    $('#of-count').text((c.overdue || 0)
                        ? (c.overdue + ' offer' + (c.overdue === 1 ? '' : 's')
                            + ' past the date to answer by, still awaiting a reply')
                        : '');

                    render();
                })
                .fail(function (xhr) {
                    console.error('[offers]', xhr.status, xhr.responseText);
                    $('#of-list').empty();
                    $('#of-empty').text('Could not load offers. Please refresh — if it keeps happening, '
                        + 'your session may have expired.').show();
                });
        }

        $('#of-tabs').on('click', '.of-tab', function () {
            $('.of-tab').removeClass('is-on').attr('aria-pressed', 'false');
            $(this).addClass('is-on').attr('aria-pressed', 'true');
            view = $(this).data('view');
            render();
        });

        /* ---- raising a draft ------------------------------------------ */

        $('#of-list').on('click', '.js-of-draft', function () {
            drafting = $(this).data('application');
            $('#ofDraftTitle').text('Draft an offer for ' + $(this).data('name'));
            $('#ofDraftError').addClass('d-none').text('');
            $('#ofSalary, #ofEmployment, #ofStart, #ofExpires, #ofNotes').val('');
            $('#ofCurrency').val('PHP');
            $('#ofDraftModal').modal('show');
        });

        $('#ofDraftSubmit').on('click', async function () {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Saving…');
            $('#ofDraftError').addClass('d-none').text('');

            try {
                const res = await fetch('{{ route('recruitment.offers.store') }}', {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        application_id: drafting,
                        salary_amount: $('#ofSalary').val() || null,
                        salary_currency: $('#ofCurrency').val() || 'PHP',
                        employment_status: $('#ofEmployment').val() || null,
                        start_date: $('#ofStart').val() || null,
                        response_deadline: $('#ofExpires').val() || null,
                        notes: $('#ofNotes').val() || null
                    })
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(body.message
                        || (body.errors && Object.values(body.errors)[0][0])
                        || ('Status ' + res.status));
                }

                $('#ofDraftModal').modal('hide');
                location.reload();
            } catch (err) {
                $('#ofDraftError').removeClass('d-none').text(err.message);
            } finally {
                $btn.prop('disabled', false).text('Save draft');
            }
        });

        /* ---- moving one along ----------------------------------------- */

        $('#of-list').on('click', '.js-of-hire', async function () {
            const application = $(this).data('application');

            /* The note is required: it is the only record that orientation
               happened, because nothing in the system observes one. */
            const note = await hfPrompt({
                title: 'Record this applicant as Hired',
                message: 'Only once they have actually been oriented. This does NOT create an '
                       + 'employee record and does not close the application — requirements and '
                       + 'the 201 handoff are still to come.',
                label: 'When were they oriented?',
                placeholder: 'e.g. oriented 15 Oct 2026, batch 4',
                required: true,
                requiredMessage: 'Say what happened — it is the only record of it.',
                maxLength: 1000,
                confirmLabel: 'Mark as Hired'
            });

            if (note === null) { return; }

            try {
                const res = await fetch(BASE_URL + '/recruitment/applicant-intake/applications/'
                    + encodeURIComponent(application) + '/hired', {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ note: note })
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(body.message
                        || (body.errors && Object.values(body.errors)[0][0])
                        || ('Status ' + res.status));
                }

                await hfConfirm({
                    title: 'Recorded', message: body.message,
                    confirmLabel: 'Close', cancelLabel: ''
                });

                load();
            } catch (err) {
                await hfConfirm({
                    title: 'That could not be done', message: err.message,
                    confirmLabel: 'Close', cancelLabel: ''
                });
            }
        });

        $('#of-list').on('click', '.js-of-move', async function () {
            const to = $(this).data('to');
            const offer = $(this).data('offer');
            const row = (DATA.offers || []).find(o => String(o.id) === String(offer)) || {};

            /*
            | Each move has its own endpoint, because each is a different act
            | with different requirements: sending needs a date to answer by,
            | a response needs whose answer it was, ending needs a reason.
            | One generic "set the status" call could not ask for any of them.
            */
            const endpoint = ENDPOINT[to];

            if (!endpoint) { return; }

            const payload = {};

            /* Ending an offer: the reason IS the record. */
            if (to === 'withdrawn' || to === 'expired') {
                const reason = await hfPrompt({
                    title: to === 'withdrawn' ? 'Withdraw this offer?' : 'End this offer?',
                    message: to === 'withdrawn'
                        ? 'It is taken back. If it is already with the applicant, they should be told.'
                        : 'The deadline passed with no answer and you are ending it. The applicant '
                          + 'is NOT recorded as having declined — only that it ended unanswered.',
                    label: 'Why?',
                    required: true,
                    requiredMessage: 'A reason is needed, and it is kept with the offer.',
                    maxLength: 500,
                    confirmLabel: VERB[to],
                    tone: 'warn'
                });

                if (reason === null) { return; }

                payload.to = to;
                payload.reason = reason;

            /* The applicant declining: their decision, recorded by HR. */
            } else if (to === 'declined') {
                const reason = await hfPrompt({
                    title: 'Record a declined offer',
                    message: 'The applicant turned it down. This closes the application as '
                           + 'Offer Declined — not Not Selected, and with no cooldown.',
                    label: 'What did they say?',
                    required: true,
                    requiredMessage: 'A reason is needed, and it is kept with the offer.',
                    maxLength: 500,
                    confirmLabel: 'Record decline',
                    tone: 'warn'
                });

                if (reason === null) { return; }

                payload.answer = 'declined';
                payload.reason = reason;

            /* Sending: a date to answer by is required, and there is no
               default — the policy for it has not been settled. */
            } else if (to === 'sent') {
                if (!row.response_deadline) {
                    const by = await hfPrompt({
                        title: 'Send this offer',
                        message: 'Give the applicant a date to answer by. There is no default: '
                               + 'the response period is set per offer.',
                        label: 'Answer by (YYYY-MM-DD)',
                        placeholder: 'e.g. 2026-11-30',
                        required: true,
                        requiredMessage: 'A date is needed before the offer can be sent.',
                        maxLength: 10,
                        confirmLabel: 'Send offer'
                    });

                    if (by === null) { return; }

                    payload.response_deadline = by;
                } else if (!await hfConfirm({
                    title: 'Send this offer?',
                    message: 'It goes to the applicant, who must answer by '
                           + day(row.response_deadline) + '.',
                    confirmLabel: 'Send offer'
                })) {
                    return;
                }

            /* Recording the contract. */
            } else if (to === 'contract_received') {
                const ref = await hfPrompt({
                    title: 'Record the signed contract',
                    message: 'The signed contract is in. This is still not the hire — record '
                           + 'that on the application when it happens.',
                    label: 'Contract reference (optional)',
                    maxLength: 100,
                    confirmLabel: 'Record contract'
                });

                if (ref === null) { return; }

                payload.contract_reference = ref || null;

            /* Submitting for approval, and recording an acceptance. */
            } else if (!await hfConfirm({
                title: VERB[to] + '?',
                message: to === 'pending_approval'
                    ? 'It goes to the HR Head. Every offer needs their approval before it can '
                      + 'be sent, whatever the salary or position.'
                    : 'The applicant accepted the terms.',
                detail: to === 'accepted'
                    ? 'This does NOT mark them Hired. The signed contract and the hire are '
                      + 'separate records.'
                    : null,
                confirmLabel: VERB[to]
            })) {
                return;
            }

            if (to === 'accepted') { payload.answer = 'accepted'; }

            try {
                const res = await fetch(BASE_URL + '/recruitment/offers/'
                    + encodeURIComponent(offer) + '/' + endpoint, {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify(payload)
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(body.message
                        || (body.errors && Object.values(body.errors)[0][0])
                        || ('Status ' + res.status));
                }

                await hfConfirm({
                    title: 'Done',
                    message: body.message,
                    confirmLabel: 'Close',
                    cancelLabel: ''
                });

                location.reload();
            } catch (err) {
                await hfConfirm({
                    title: 'That could not be done',
                    message: err.message,
                    confirmLabel: 'Close',
                    cancelLabel: ''
                });
            }
        });

        /* ---- the trail ------------------------------------------------- */

        $('#of-list').on('click', '.js-of-detail', async function () {
            const id = $(this).data('offer');
            $('#ofDetailBody').html('<div class="of-sub">Loading…</div>');
            $('#ofDetailModal').modal('show');

            try {
                const d = await $.getJSON(BASE_URL + '/recruitment/offers/' + encodeURIComponent(id));

                $('#ofDetailTitle').text('Offer — ' + (d.offer.applicant_name || ''));
                $('#ofDetailBody').html(
                    '<p class="of-sub">' + esc(d.offer.posting_title || '') + ' · ' + esc(d.offer.mr_no || '') + '</p>' +
                    '<p>' + esc(money(d.offer)) +
                        (d.offer.start_date ? ' · starts ' + esc(day(d.offer.start_date)) : '') + '</p>' +
                    (d.offer.notes ? '<p class="of-sub">' + esc(d.offer.notes) + '</p>' : '') +
                    '<h6 class="mt-3">Rounds before this offer</h6>' +
                    (d.rounds.length
                        ? '<ul class="of-trail">' + d.rounds.map(r =>
                            '<li>' + esc(r.label) + ' — ' + esc(r.state) +
                            (r.result ? ': ' + esc(r.result) : '') + '</li>').join('') + '</ul>'
                        : '<p class="of-sub">No interview rounds recorded.</p>') +
                    '<h6 class="mt-3">What has happened to this offer</h6>' +
                    '<ul class="of-trail">' + d.events.map(e =>
                        '<li><strong>' + esc(e.to) + '</strong>' +
                        (e.from ? ' <span class="of-sub">from ' + esc(e.from) + '</span>' : '') +
                        ' · ' + esc(e.at) + (e.actor ? ' · ' + esc(e.actor) : '') +
                        (e.reason ? '<div class="of-sub">' + esc(e.reason) + '</div>' : '') +
                        '</li>').join('') + '</ul>'
                );
            } catch (err) {
                $('#ofDetailBody').html('<div class="of-sub">Could not load this offer.</div>');
            }
        });

        load();
    });
</script>
