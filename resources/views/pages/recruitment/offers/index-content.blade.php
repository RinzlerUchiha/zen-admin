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
                        <label class="form-label" for="ofExpires">Offer stands until</label>
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

        /* Lapsed is derived, not stored: an issued offer past its date that
           nobody has answered. It is still "issued" until somebody records
           what happened to it. */
        function tone(row) {
            if (row.lapsed) { return 'lapsed'; }
            if (row.status === 'draft') { return 'draft'; }
            if (row.status === 'issued') { return 'issued'; }
            if (row.status === 'accepted') { return 'accepted'; }
            return 'settled';
        }

        const VERB = {
            issued: 'Issue', accepted: 'Accepted', declined: 'Declined',
            withdrawn: 'Withdraw', expired: 'Mark expired'
        };

        function actions(row) {
            if (!CAN_DECIDE) { return ''; }

            /* Only what the server would allow from here. The list comes from
               OfferWorkflow's own table, so the buttons cannot offer a move
               the service would refuse. */
            return (row.allowed || []).map(to =>
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
                '<span class="of-chip of-chip-' + t + '">' + esc(row.lapsed ? 'Lapsed' : row.status) + '</span>' +
                '<span class="of-sub">' + esc(money(row)) + '</span>' +
                (row.employment_status ? '<span class="of-sub">' + esc(row.employment_status) + '</span>' : '') +
              '</div>' +
              '<div class="of-cell">' +
                (row.start_date ? '<span class="of-sub">Starts ' + esc(day(row.start_date)) + '</span>' : '') +
                (row.expires_at ? '<span class="of-sub">Stands until ' + esc(day(row.expires_at)) + '</span>' : '') +
                (row.issued_at ? '<span class="of-sub">Issued ' + esc(row.issued_at) + '</span>' : '') +
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
                rows = DATA.offers.filter(o => o.status === 'draft' || o.status === 'issued');
                html = rows.map(offerRow).join('');
            } else {
                rows = DATA.offers.filter(o => ['accepted', 'declined', 'withdrawn', 'expired'].includes(o.status));
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
                    $('#of-count-live').text((c.draft || 0) + (c.issued || 0));
                    $('#of-count-settled').text(c.settled || 0);

                    $('#of-count').text((c.lapsed || 0)
                        ? (c.lapsed + ' offer' + (c.lapsed === 1 ? '' : 's') + ' past the date they stood until')
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
                        expires_at: $('#ofExpires').val() || null,
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

        $('#of-list').on('click', '.js-of-move', async function () {
            const to = $(this).data('to');
            const offer = $(this).data('offer');

            /* Withdrawing and declining are kept with the offer, so the
               reason is asked for and the service refuses without one. */
            const needsReason = to === 'withdrawn' || to === 'declined';
            let reason = null;

            if (needsReason) {
                reason = await hfPrompt({
                    title: to === 'withdrawn' ? 'Withdraw this offer?' : 'Record a declined offer',
                    message: to === 'withdrawn'
                        ? 'It is taken back before the candidate answered.'
                        : 'The candidate turned it down.',
                    label: 'Why?',
                    required: true,
                    requiredMessage: 'A reason is needed, and it is kept with the offer.',
                    maxLength: 500,
                    confirmLabel: to === 'withdrawn' ? 'Withdraw offer' : 'Record decline',
                    tone: 'warn'
                });

                if (reason === null) { return; }
            } else if (!await hfConfirm({
                title: (VERB[to] || to) + ' this offer?',
                message: to === 'issued'
                    ? 'It is put to the candidate and waits on their answer.'
                    : (to === 'accepted'
                        ? 'The candidate accepted the terms.'
                        : 'It is recorded as having lapsed without an answer.'),
                detail: to === 'accepted'
                    ? 'This does NOT mark them Hired. Record the hire on the application when it happens.'
                    : null,
                confirmLabel: VERB[to] || to
            })) {
                return;
            }

            try {
                const res = await fetch(BASE_URL + '/recruitment/offers/' + encodeURIComponent(offer) + '/transition', {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ to: to, reason: reason })
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
