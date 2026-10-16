{{--
| Mandatory vs preferred, against one reusable job specification.
|
| The legacy form asks for "REQUIRED/PREFERRED" in a single checkbox list, so
| the distinction was never captured. This is where it is captured — once per
| position, not once per posting, because a specification is reused.
|
| An unclassified requirement counts as preferred. The page says so, because
| somebody looking at a screen of "preferred" needs to know whether that was
| chosen or merely never answered.
--}}
<style>
    .rq-head { margin-bottom: 6px; }
    .rq-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .rq-note {
        border-left: 3px solid var(--zn-accent);
        background: var(--zn-surface-2);
        padding: 10px 12px; border-radius: 0 6px 6px 0;
        font-size: var(--zn-fs-sm); margin: 10px 0 16px;
    }

    .rq-list { display: flex; flex-direction: column; gap: 10px; }

    .rq-card {
        border: 1px solid var(--zn-line); border-radius: var(--zn-radius-lg);
        background: var(--zn-surface); padding: 14px 16px;
    }

    .rq-top { display: flex; flex-wrap: wrap; gap: 10px; align-items: baseline;
              justify-content: space-between; }
    .rq-label { font-weight: 700; }

    .rq-asks { margin-top: 8px; font-size: var(--zn-fs-sm); color: var(--zn-ink-2); }
    .rq-asks ul { margin: 4px 0 0 18px; padding: 0; }

    .rq-choice { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .rq-btn {
        border: 1px solid var(--zn-line-2); background: var(--zn-surface);
        color: var(--zn-ink-2); border-radius: 20px; padding: 4px 14px;
        font-size: var(--zn-fs-sm); font-weight: 600; cursor: pointer;
    }
    .rq-btn.is-on { background: var(--zn-accent); border-color: var(--zn-accent); color: var(--zn-on-accent); }
    .rq-btn[disabled] { opacity: .5; cursor: default; }

    .rq-flag {
        font-size: var(--zn-fs-xs); font-weight: 700;
        border-radius: 20px; padding: 1px 9px;
        background: var(--zn-surface-2); color: var(--zn-ink-3);
    }
    .rq-flag-unset { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .rq-noscore { font-style: italic; color: var(--zn-ink-3); font-size: var(--zn-fs-sm); margin-top: 6px; }

    .rq-empty {
        border: 1px dashed var(--zn-line); border-radius: var(--zn-radius-lg);
        padding: 32px 20px; text-align: center; color: var(--zn-ink-3);
    }
</style>

<div class="rq-head">
    <h5 class="mb-1">Mandatory and preferred requirements</h5>
    <div class="rq-sub" id="rq-position">Loading…</div>
</div>

<div class="rq-note" id="rq-note"></div>

<div id="rq-list" class="rq-list" aria-live="polite">
    <div class="rq-empty">Loading requirements…</div>
</div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;
        const JSPEC = @json($jspecId);
        const CAN_DECIDE = @json(\Illuminate\Support\Facades\Gate::allows('applicant-applications.decide'));
        const CSRF = @json(csrf_token());

        function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

        function card(c) {
            const asks = (c.required || []).length
                ? '<div class="rq-asks">This specification asks for:<ul>'
                    + c.required.map(r => '<li>' + esc(r) + '</li>').join('') + '</ul></div>'
                : '<div class="rq-asks">This specification asks for nothing here, so there is '
                    + 'nothing to classify.</div>';

            /* Classifying a criterion the matcher does not score changes
               nothing, and saying so is better than letting somebody set it
               and wonder why it had no effect. */
            const noScore = c.scored ? '' :
                '<div class="rq-noscore">Shown to HR as evidence but never scored'
                + (c.why_not_scored ? ' — ' + esc(c.why_not_scored) : '') + '</div>';

            const disabled = (!CAN_DECIDE || !(c.required || []).length || !c.scored)
                ? ' disabled' : '';

            return '' +
            '<article class="rq-card" data-key="' + esc(c.key) + '">' +
              '<div class="rq-top">' +
                '<span class="rq-label">' + esc(c.label) + '</span>' +
                '<span class="rq-flag' + (c.classified ? '' : ' rq-flag-unset') + '">' +
                  (c.classified ? esc(c.importance) : 'not classified — counts as preferred') +
                '</span>' +
              '</div>' +
              asks + noScore +
              '<div class="rq-choice">' +
                '<button type="button" class="rq-btn js-rq-set'
                  + (c.classified && c.importance === 'mandatory' ? ' is-on' : '') + '" '
                  + 'data-importance="mandatory"' + disabled + '>Mandatory</button>' +
                '<button type="button" class="rq-btn js-rq-set'
                  + (c.classified && c.importance === 'preferred' ? ' is-on' : '') + '" '
                  + 'data-importance="preferred"' + disabled + '>Preferred</button>' +
              '</div>' +
            '</article>';
        }

        function load() {
            $.getJSON(BASE_URL + '/recruitment/job-specifications/' + JSPEC + '/requirements/data')
                .done(function (json) {
                    $('#rq-position').text(json.position
                        ? 'Position reference ' + json.position
                        : 'Job specification ' + json.jspec_id);
                    $('#rq-note').text(json.note || '');
                    $('#rq-list').html((json.criteria || []).map(card).join('')
                        || '<div class="rq-empty">This specification has no requirements recorded.</div>');
                })
                .fail(function (xhr) {
                    console.error('[requirements]', xhr.status, xhr.responseText);
                    $('#rq-list').html('<div class="rq-empty">Could not load the requirements.</div>');
                });
        }

        $('#rq-list').on('click', '.js-rq-set', async function () {
            const $btn = $(this);
            const key = $btn.closest('.rq-card').data('key');
            const importance = $btn.data('importance');

            if (importance === 'mandatory' && !await hfConfirm({
                title: 'Make this mandatory?',
                message: 'An applicant who does not meet it will be flagged on the matching '
                       + 'screen. They are never rejected automatically, and an unknown is '
                       + 'never treated as unmet.',
                confirmLabel: 'Mandatory'
            })) {
                return;
            }

            try {
                const res = await fetch(BASE_URL + '/recruitment/job-specifications/'
                    + JSPEC + '/requirements', {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ criterion_key: key, importance: importance })
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(body.message
                        || (body.errors && Object.values(body.errors)[0][0])
                        || ('Status ' + res.status));
                }

                load();
            } catch (err) {
                await hfConfirm({
                    title: 'That could not be saved', message: err.message,
                    confirmLabel: 'Close', cancelLabel: ''
                });
            }
        });

        load();
    });
</script>
