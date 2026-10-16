{{--
| Assessment review for one application.
|
| Every assessment shows its status and, where the job specification defines
| one, the expected profile beside the result. There is no pass mark anywhere
| on this page, because there is none in the system — and where a profile was
| never filled in, the page says "unspecified" rather than leaving a blank that
| reads as agreement.
|
| Completing every assessment advances nobody. The decision is HR's, carries a
| reason, and is the only thing here that moves an application.
--}}
<style>
    .ar-head { margin-bottom: 6px; }
    .ar-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .ar-note {
        border-left: 3px solid var(--zn-accent);
        background: var(--zn-surface-2);
        padding: 10px 12px; border-radius: 0 6px 6px 0;
        font-size: var(--zn-fs-sm); margin: 10px 0 16px;
    }

    .ar-tally { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
    .ar-pill {
        font-size: var(--zn-fs-xs); font-weight: 700;
        border-radius: 20px; padding: 2px 10px;
        background: var(--zn-surface-2); color: var(--zn-ink-2);
    }
    .ar-pill-out { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .ar-pill-ok { background: var(--zn-ok-soft, #e8f5e9); color: var(--zn-ok); }

    .ar-list { display: flex; flex-direction: column; gap: 8px; }

    .ar-row {
        display: grid; grid-template-columns: minmax(0, 1.4fr) 110px minmax(0, 2fr);
        gap: 12px; align-items: baseline;
        border: 1px solid var(--zn-line); border-radius: var(--zn-radius-lg);
        background: var(--zn-surface); padding: 10px 14px;
    }
    .ar-name { font-weight: 600; }
    .ar-kind { font-size: var(--zn-fs-xs); color: var(--zn-ink-3); }

    .ar-state { font-size: var(--zn-fs-xs); font-weight: 700;
                border-radius: 20px; padding: 1px 9px; text-align: center; }
    .ar-state-taken { background: var(--zn-ok-soft, #e8f5e9); color: var(--zn-ok); }
    .ar-state-waiting { background: var(--zn-surface-2); color: var(--zn-ink-3); }

    .ar-exp { font-size: var(--zn-fs-sm); color: var(--zn-ink-2); }
    .ar-unspec { font-style: italic; color: var(--zn-ink-3); }

    .ar-decide { margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--zn-line); }
    .ar-acts { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }

    .ar-made { font-size: var(--zn-fs-sm); }
    .ar-empty {
        border: 1px dashed var(--zn-line); border-radius: var(--zn-radius-lg);
        padding: 32px 20px; text-align: center; color: var(--zn-ink-3);
    }

    @media (max-width: 767.98px) {
        .ar-row { grid-template-columns: 1fr; gap: 3px; }
    }
</style>

<div class="ar-head">
    <h5 class="mb-1">Assessment review</h5>
    <div class="ar-sub" id="ar-outstanding">Loading…</div>
</div>

<div class="ar-note" id="ar-disclaimer"></div>

<div class="ar-tally" id="ar-tally"></div>

<div id="ar-list" class="ar-list" aria-live="polite">
    <div class="ar-empty">Loading assessments…</div>
</div>

<div class="ar-decide" id="ar-decide" style="display:none">
    <h6 class="mb-1">What happens next</h6>
    <div class="ar-sub">
        Your decision, with a reason. Completing the assessments does not advance anybody —
        this is where it is recorded.
    </div>
    <div class="ar-acts" id="ar-acts"></div>
</div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;
        const APP = @json($applicationId);
        const CAN_DECIDE = @json(\Illuminate\Support\Facades\Gate::allows('applicant-applications.decide'));
        const CSRF = @json(csrf_token());

        function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

        const VERB = { proceed: 'Proceed', hold: 'Hold', stop: 'Do not proceed' };

        function row(a) {
            /* The expected profile, or why there is not one. Never a blank:
               a blank would read as agreement. */
            let expected;

            if (a.comparison === 'available') {
                expected = '<div class="ar-exp">Expected: ' + esc((a.expected || []).join(', ')) + '</div>'
                    + '<div class="ar-kind">' + esc(a.comparison_note) + '</div>';
            } else {
                expected = '<div class="ar-exp ar-unspec">'
                    + esc(a.comparison === 'unspecified' ? 'Expected profile unspecified'
                        : (a.comparison === 'not_taken' ? 'Not taken yet' : 'No expected profile'))
                    + '</div><div class="ar-kind">' + esc(a.comparison_note) + '</div>';
            }

            return '' +
            '<div class="ar-row">' +
              '<div>' +
                '<div class="ar-name">' + esc(a.label) + '</div>' +
                '<div class="ar-kind">' + esc(a.kind)
                  + (a.taken_on ? ' · taken ' + esc(a.taken_on) : '') + '</div>' +
              '</div>' +
              '<div><span class="ar-state ar-state-' + (a.taken ? 'taken' : 'waiting') + '">' +
                (a.taken ? 'Taken' : 'Outstanding') + '</span></div>' +
              '<div>' + expected + '</div>' +
            '</div>';
        }

        function render(json) {
            const c = json.counts || {};

            $('#ar-disclaimer').text(json.disclaimer || '');

            $('#ar-outstanding').text(json.all_taken
                ? 'Every assessment is in. That is not a verdict — record what you decided below.'
                : (json.outstanding || []).length + ' still outstanding: '
                  + (json.outstanding || []).join(', '));

            $('#ar-tally').html(
                '<span class="ar-pill ' + (json.all_taken ? 'ar-pill-ok' : '') + '">'
                    + (c.taken || 0) + ' of ' + (c.total || 0) + ' taken</span>'
                + ((c.outstanding || 0) ? '<span class="ar-pill ar-pill-out">'
                    + c.outstanding + ' outstanding</span>' : '')
                + '<span class="ar-pill">' + (c.comparable || 0) + ' comparable</span>'
                + ((c.unspecified || 0) ? '<span class="ar-pill">' + c.unspecified
                    + ' expected profile unspecified</span>' : '')
                + ((c.no_profile || 0) ? '<span class="ar-pill">' + c.no_profile
                    + ' with no profile defined</span>' : '')
            );

            $('#ar-list').html((json.assessments || []).map(row).join('')
                || '<div class="ar-empty">No assessments are configured.</div>');

            /* The decision, or the one already made. */
            if (json.decision) {
                $('#ar-acts').html('<span class="ar-made"><b>'
                    + esc(VERB[json.decision.decision] || json.decision.decision) + '</b>'
                    + ' — ' + esc(json.decision.reason) + '</span>'
                    + '<span class="ar-sub">by ' + esc(json.decision.decided_by || '—') + '</span>'
                    + (CAN_DECIDE ? '<button type="button" class="btn btn-sm btn-outline-secondary '
                        + 'js-ar-again">Record a different decision</button>' : ''));
                $('#ar-decide').show();
            } else if (CAN_DECIDE) {
                $('#ar-acts').html(
                    '<button type="button" class="btn btn-sm btn-primary js-ar-go" data-d="proceed">Proceed</button>'
                  + '<button type="button" class="btn btn-sm btn-outline-secondary js-ar-go" data-d="hold">Hold</button>'
                  + '<button type="button" class="btn btn-sm btn-outline-danger js-ar-go" data-d="stop">Do not proceed</button>');
                $('#ar-decide').show();
            }
        }

        function load() {
            $.getJSON(BASE_URL + '/recruitment/applications/' + APP + '/assessment-review/data')
                .done(render)
                .fail(function (xhr) {
                    console.error('[assessment-review]', xhr.status, xhr.responseText);
                    $('#ar-list').html('<div class="ar-empty">Could not load the assessments.</div>');
                });
        }

        async function decide(decision) {
            /* Always a reason. Each of these is a judgement about a person, and
               "the results were in" is not one. */
            const reason = await hfPrompt({
                title: VERB[decision] + '?',
                message: decision === 'proceed'
                    ? 'The application is unchanged by this — advance the stage when you are ready.'
                    : (decision === 'hold'
                        ? 'The application stays open and nothing moves until you decide.'
                        : 'This closes the application as Not Selected.'),
                label: 'Reason',
                required: true,
                requiredMessage: 'A reason is needed, and it is kept with the decision.',
                maxLength: 500,
                confirmLabel: VERB[decision],
                tone: decision === 'stop' ? 'warn' : undefined
            });

            if (reason === null) { return; }

            try {
                const res = await fetch(BASE_URL + '/recruitment/applications/' + APP
                    + '/assessment-review/decide', {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ decision: decision, reason: reason })
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
        }

        $('#ar-acts').on('click', '.js-ar-go', function () { decide($(this).data('d')); });

        $('#ar-acts').on('click', '.js-ar-again', function () {
            $('#ar-acts').html(
                '<button type="button" class="btn btn-sm btn-primary js-ar-go" data-d="proceed">Proceed</button>'
              + '<button type="button" class="btn btn-sm btn-outline-secondary js-ar-go" data-d="hold">Hold</button>'
              + '<button type="button" class="btn btn-sm btn-outline-danger js-ar-go" data-d="stop">Do not proceed</button>');
        });

        load();
    });
</script>
