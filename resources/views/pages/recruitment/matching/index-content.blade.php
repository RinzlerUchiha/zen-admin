{{--
| Candidate matching for one posting.
|
| Every requirement is shown with its result AND the evidence behind it. There
| is no score and no percentage: the ranking rules are printed on the page, in
| order, because a ranking whose rules are not visible is exactly the opaque
| scoring this design exists to avoid.
|
| Unknown is shown in its own colour and never counted against anybody. The
| page says so out loud, because a reader who assumes otherwise would use this
| screen to reject people for gaps in our own records.
--}}
<style>
    .mt-head { display: flex; flex-wrap: wrap; gap: 10px; align-items: baseline;
               justify-content: space-between; margin-bottom: 6px; }
    .mt-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .mt-note {
        border-left: 3px solid var(--zn-accent);
        background: var(--zn-surface-2);
        padding: 10px 12px; border-radius: 0 6px 6px 0;
        font-size: var(--zn-fs-sm); margin: 10px 0 16px;
    }

    .mt-rules { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 16px; }
    .mt-rule {
        font-size: var(--zn-fs-xs); font-weight: 600;
        background: var(--zn-surface-2); color: var(--zn-ink-2);
        border-radius: 20px; padding: 2px 10px;
    }
    .mt-rule-n { opacity: .6; margin-right: 3px; }

    .mt-list { display: flex; flex-direction: column; gap: 10px; }

    .mt-card {
        border: 1px solid var(--zn-line); border-radius: var(--zn-radius-lg);
        background: var(--zn-surface); padding: 14px 16px;
    }
    .mt-card:hover { border-color: var(--zn-accent); }

    .mt-top { display: flex; flex-wrap: wrap; gap: 10px; align-items: baseline; justify-content: space-between; }
    .mt-who { display: flex; align-items: baseline; gap: 10px; min-width: 0; }
    .mt-rank { font-weight: 700; color: var(--zn-ink-3); font-size: var(--zn-fs-sm); }
    .mt-name { font-weight: 700; }

    .mt-tally { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .mt-pill {
        font-size: var(--zn-fs-xs); font-weight: 700;
        border-radius: 20px; padding: 2px 10px;
    }
    .mt-pill-mand { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .mt-pill-pref { background: var(--zn-surface-2); color: var(--zn-ink-2); }
    .mt-pill-unmet { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .mt-pill-unknown { background: var(--zn-surface-2); color: var(--zn-ink-3); }

    .mt-crit { margin-top: 12px; display: flex; flex-direction: column; gap: 6px; }
    .mt-row {
        display: grid; grid-template-columns: 150px 92px minmax(0, 1fr);
        gap: 10px; align-items: baseline;
        font-size: var(--zn-fs-sm);
        padding: 5px 0; border-top: 1px dashed var(--zn-line);
    }
    .mt-row-label { font-weight: 600; }
    .mt-res { font-weight: 700; font-size: var(--zn-fs-xs); border-radius: 20px;
              padding: 1px 8px; text-align: center; }
    .mt-res-met { background: var(--zn-ok-soft, #e8f5e9); color: var(--zn-ok); }
    .mt-res-not_met { background: var(--zn-warn-soft, #fdecea); color: var(--zn-warn); }
    .mt-res-unknown { background: var(--zn-surface-2); color: var(--zn-ink-3); }
    .mt-ev { color: var(--zn-ink-2); overflow-wrap: anywhere; }
    .mt-req { color: var(--zn-ink-3); font-size: var(--zn-fs-xs); }
    .mt-mand { color: var(--zn-accent-dark); font-weight: 700; }
    .mt-noscore { color: var(--zn-ink-3); font-style: italic; }

    .mt-acts { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .mt-decided { font-size: var(--zn-fs-sm); font-weight: 600; }

    .mt-empty {
        border: 1px dashed var(--zn-line); border-radius: var(--zn-radius-lg);
        padding: 32px 20px; text-align: center; color: var(--zn-ink-3);
    }

    @media (max-width: 767.98px) {
        .mt-row { grid-template-columns: 1fr; gap: 2px; }
    }
</style>

<div class="mt-head">
    <div>
        <h5 class="mb-1">Candidate matching</h5>
        <div class="mt-sub" id="mt-posting">Loading…</div>
    </div>
    <span class="mt-sub" id="mt-count"></span>
</div>

<div class="mt-note" id="mt-disclaimer"></div>

<div class="mt-rules" id="mt-rules" aria-label="How candidates are ordered"></div>

<div id="mt-list" class="mt-list" aria-live="polite">
    <div class="mt-empty">Loading candidates…</div>
</div>

<script>
    $(function () {
        const BASE_URL = document.querySelector('meta[name="base-url"]').content;
        const POSTING = @json($postingId);
        const CAN_DECIDE = @json(\Illuminate\Support\Facades\Gate::allows('applicant-applications.decide'));
        const CSRF = @json(csrf_token());

        let ROWS = [];

        function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

        const RESULT_LABEL = { met: 'Met', not_met: 'Not met', unknown: 'Unknown' };

        function criterionRow(c) {
            /* The requirement, the verdict, and what the verdict was based on.
               Never a verdict on its own. */
            const asked = (c.required || []).map(r => r.value || r).join(' / ');

            return '' +
            '<div class="mt-row">' +
              '<div class="mt-row-label">' + esc(c.label) +
                (c.importance === 'mandatory' ? ' <span class="mt-mand">(mandatory)</span>' : '') +
                (c.scored ? '' : ' <span class="mt-noscore">not scored</span>') +
              '</div>' +
              '<div><span class="mt-res mt-res-' + esc(c.result) + '">' +
                esc(RESULT_LABEL[c.result] || c.result) + '</span></div>' +
              '<div>' +
                '<div class="mt-ev">' + esc(c.evidence) + '</div>' +
                (asked ? '<div class="mt-req">Asks for: ' + esc(asked) + '</div>' : '') +
                (c.missing ? '<div class="mt-req">Missing: ' + esc(c.missing) + '</div>' : '') +
                (!c.scored && c.why_not_scored
                    ? '<div class="mt-req">' + esc(c.why_not_scored) + '</div>' : '') +
              '</div>' +
            '</div>';
        }

        function tally(counts) {
            const pills = [];

            if (counts.mandatory_total > 0) {
                pills.push('<span class="mt-pill mt-pill-mand">' + counts.mandatory_met + ' of '
                    + counts.mandatory_total + ' mandatory met</span>');
            }
            if (counts.mandatory_unmet > 0) {
                pills.push('<span class="mt-pill mt-pill-unmet">' + counts.mandatory_unmet
                    + ' mandatory unmet</span>');
            }
            if (counts.preferred_total > 0) {
                pills.push('<span class="mt-pill mt-pill-pref">' + counts.preferred_met + ' of '
                    + counts.preferred_total + ' preferred met</span>');
            }
            if (counts.unknown > 0) {
                pills.push('<span class="mt-pill mt-pill-unknown">' + counts.unknown
                    + ' unknown — not counted against them</span>');
            }

            return pills.join('');
        }

        function card(row) {
            const m = row.match || {};
            const counts = m.counts || {};
            const unavailable = m.unavailable;

            let acts = '';

            if (row.decision) {
                acts = '<span class="mt-decided">'
                    + (row.decision.decision === 'shortlisted' ? 'Shortlisted' : 'Not shortlisted')
                    + '</span> <span class="mt-sub">by ' + esc(row.decision.decided_by || '—')
                    + ' · ' + esc(row.decision.decided_at || '') + '</span>'
                    + (row.decision.overrode
                        ? ' <span class="mt-pill mt-pill-unmet">override</span>' : '')
                    + (row.decision.reason
                        ? '<div class="mt-sub">' + esc(row.decision.reason) + '</div>' : '');
            } else if (CAN_DECIDE) {
                acts = '<button type="button" class="btn btn-sm btn-primary js-mt-yes" '
                     + 'data-app="' + esc(row.application_id) + '">Shortlist</button>'
                     + '<button type="button" class="btn btn-sm btn-outline-danger js-mt-no" '
                     + 'data-app="' + esc(row.application_id) + '">Not shortlisted</button>';
            }

            const findings = (row.eligibility && row.eligibility.findings || [])
                .map(f => '<div class="mt-req">' + esc(f.detail) + '</div>').join('');

            return '' +
            '<article class="mt-card">' +
              '<div class="mt-top">' +
                '<div class="mt-who">' +
                  '<span class="mt-rank">#' + esc(row.rank) + '</span>' +
                  '<span class="mt-name">' + esc(row.applicant_name) + '</span>' +
                  '<span class="mt-sub">' + esc(row.app_email || '') + '</span>' +
                '</div>' +
                '<a class="btn btn-sm btn-outline-secondary" href="' + BASE_URL
                  + '/applicant/info/' + encodeURIComponent(row.app_id) + '">Profile</a>' +
              '</div>' +
              (unavailable
                ? '<div class="mt-note">' + esc(unavailable) + '</div>'
                : '<div class="mt-tally">' + tally(counts) + '</div>'
                  + '<div class="mt-crit">' + (m.criteria || []).map(criterionRow).join('') + '</div>') +
              (findings ? '<div class="mt-crit">' + findings + '</div>' : '') +
              '<div class="mt-acts">' + acts + '</div>' +
            '</article>';
        }

        function render() {
            $('#mt-list').html(ROWS.length
                ? ROWS.map(card).join('')
                : '<div class="mt-empty">No open applications to this posting yet.</div>');
            $('#mt-count').text(ROWS.length + (ROWS.length === 1 ? ' candidate' : ' candidates'));
        }

        function load() {
            $.getJSON(BASE_URL + '/recruitment/postings/' + POSTING + '/matching/data')
                .done(function (json) {
                    ROWS = json.candidates || [];
                    $('#mt-posting').text((json.posting && json.posting.posting_title)
                        || 'Posting no longer available');
                    $('#mt-disclaimer').text(json.disclaimer || '');
                    $('#mt-rules').html('<span class="mt-sub">Ordered by:</span> '
                        + (json.ranking_rules || []).map((r, i) =>
                            '<span class="mt-rule"><span class="mt-rule-n">' + (i + 1)
                            + '</span>' + esc(r) + '</span>').join(''));
                    render();
                })
                .fail(function (xhr) {
                    console.error('[matching]', xhr.status, xhr.responseText);
                    $('#mt-list').html('<div class="mt-empty">Could not load the candidates. '
                        + 'Please refresh — if it keeps happening, your session may have expired.</div>');
                });
        }

        async function decide(applicationId, decision) {
            const row = ROWS.find(r => String(r.application_id) === String(applicationId)) || {};
            const unmet = ((row.match || {}).counts || {}).mandatory_unmet || 0;

            let reason = null;

            /*
            | A reason is asked for when shortlisting somebody with an unmet
            | mandatory requirement -- the service requires one there too. Not
            | shortlisting is an ordinary decision and does not demand one,
            | though HR may give one.
            */
            if (decision === 'shortlisted' && unmet > 0) {
                reason = await hfPrompt({
                    title: 'Shortlist anyway?',
                    message: unmet + ' mandatory requirement' + (unmet === 1 ? '' : 's')
                           + ' not met. You can still shortlist them — the recommendation is '
                           + 'advice, not a rule — but the reason is kept with the decision.',
                    label: 'Why?',
                    required: true,
                    requiredMessage: 'This goes against the recommendation, so it needs a reason.',
                    maxLength: 500,
                    confirmLabel: 'Shortlist'
                });

                if (reason === null) { return; }
            } else if (decision === 'not_shortlisted') {
                reason = await hfPrompt({
                    title: 'Not shortlisted?',
                    message: 'This closes the application. The candidate stays in the Candidate '
                           + 'Pool with no cooldown, so they can be considered for other postings.',
                    label: 'Reason (optional)',
                    maxLength: 500,
                    confirmLabel: 'Record',
                    tone: 'warn'
                });

                if (reason === null) { return; }
            } else if (!await hfConfirm({
                title: 'Shortlist this candidate?',
                message: 'Their workflow is unchanged — arrange an interview when you are ready.',
                confirmLabel: 'Shortlist'
            })) {
                return;
            }

            try {
                const res = await fetch(BASE_URL + '/recruitment/postings/' + POSTING
                    + '/matching/' + encodeURIComponent(applicationId) + '/decide', {
                    method: 'POST',
                    redirect: 'error',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({ decision: decision, reason: reason || null })
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

        $('#mt-list').on('click', '.js-mt-yes', function () {
            decide($(this).data('app'), 'shortlisted');
        });

        $('#mt-list').on('click', '.js-mt-no', function () {
            decide($(this).data('app'), 'not_shortlisted');
        });

        load();
    });
</script>
