@php
    /*
     | The interview workspace for ONE application.
     |
     | Organised around the application's workflow rounds rather than a fixed
     | set of interview types. A round holds however many interviews it took to
     | get through it: the one that happened, and the ones that were cancelled
     | or missed on the way, because those happened to the candidate too.
     |
     | Nothing here invents stage names. Fixed rounds carry the labels the
     | workflow gave them, and an intermediate round shows whatever HR called it
     | when they added it.
     */
    $canDecide = Gate::allows('applicant-applications.decide');
@endphp

<style>
    .iw-head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 4px;
    }

    .iw-name {
        font-size: var(--zn-fs-lg, 1.1rem);
        font-weight: 600;
        color: var(--zn-ink);
    }

    .iw-sub {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
    }

    .iw-back {
        font-size: var(--zn-fs-sm);
        color: var(--zn-ink-2);
        text-decoration: none;
    }

    .iw-back:hover { color: var(--zn-accent); }

    /* One card per round. The left edge carries its state, so the shape of the
       process is readable before any text is. */
    .iw-round {
        border: 1px solid var(--zn-line);
        border-left: 3px solid var(--zn-line-2);
        border-radius: var(--zn-radius-lg, 10px);
        padding: 14px 16px;
        margin-bottom: 12px;
        background: var(--zn-surface, #fff);
    }

    .iw-round.is-current { border-left-color: var(--hf-progress, var(--zn-accent)); }
    .iw-round.is-done { border-left-color: var(--hf-done, #2e7d32); }
    .iw-round.is-na { border-left-color: var(--zn-line-2); opacity: .75; }

    .iw-round-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .iw-round-title {
        font-weight: 600;
        color: var(--zn-ink);
    }

    .iw-round-actions {
        margin-left: auto;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    /* One line per interview attempt. */
    .iw-interview {
        border-top: 1px solid var(--zn-line);
        padding: 10px 0 2px;
        font-size: var(--zn-fs-sm);
    }

    .iw-interview:first-of-type { border-top: 0; }

    .iw-when { font-weight: 500; color: var(--zn-ink); }
    .iw-meta { color: var(--zn-ink-2); }

    .iw-superseded {
        opacity: .65;
        font-size: var(--zn-fs-xs);
    }

    .iw-evals { margin-top: 8px; }

    .iw-eval {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 8px;
        padding: 5px 0;
        font-size: var(--zn-fs-sm);
    }

    .iw-eval-notes {
        flex-basis: 100%;
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-xs);
        white-space: pre-wrap;
    }

    .iw-empty {
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-sm);
        padding: 6px 0;
    }

    /* Assessment results on the Exams round: read from the existing system,
       shown read-only, never edited here. */
    .iw-exam-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 6px 14px;
        font-size: var(--zn-fs-sm);
    }

    .iw-exam-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 3px 0;
        border-bottom: 1px dotted var(--zn-line);
    }

    .iw-outcome-proceed { background: var(--hf-done-soft, #e8f5e9); color: var(--hf-done, #2e7d32); }
    .iw-outcome-do_not_proceed { background: var(--hf-overdue-soft, #ffebee); color: var(--hf-overdue, #c62828); }
    .iw-outcome-hold { background: var(--hf-attention-soft, #fff8e1); color: var(--hf-attention-ink, #8d6e00); }

    .iw-att-scheduled { background: var(--hf-progress-soft, #e3f2fd); color: var(--hf-progress, #1565c0); }
    .iw-att-held { background: var(--hf-done-soft, #e8f5e9); color: var(--hf-done, #2e7d32); }
    .iw-att-cancelled { background: var(--zn-surface-2, #f3f3f3); color: var(--zn-ink-2); }
    .iw-att-no_show { background: var(--hf-overdue-soft, #ffebee); color: var(--hf-overdue, #c62828); }

    @media (max-width: 640px) {
        .iw-round-actions {
            margin-left: 0;
            flex-basis: 100%;
        }

        .iw-round { padding: 12px; }
    }
</style>

<div class="hf-theme">
    <a class="iw-back" href="{{ route('recruitment.applicant-intake.index') }}">
        <i class="bi bi-arrow-left"></i> Applicant Intake
    </a>

    <div class="iw-head mt-2">
        <span class="iw-name">{{ $applicantName ?: 'Applicant' }}</span>
        <span class="iw-sub">{{ $postingTitle }}</span>
        <span class="hf-chip {{ $application->is_closed ? 'hf-chip-lock' : 'hf-chip-ok' }}">{{ $application->status }}</span>
    </div>

    <p class="iw-sub mb-3">
        Interviews for this application only. {{ $applicantName ?: 'This applicant' }}'s other applications
        keep their own separate interview history.
    </p>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    <div id="iw-alert"></div>
    <div id="iw-rounds" aria-live="polite">
        <div class="iw-empty">Loading…</div>
    </div>

    @if ($canDecide)
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="iw-add-step">
            <i class="bi bi-plus-lg"></i> Add an interview round
        </button>
    @endif
</div>

{{-- Arranging or rearranging an interview. --}}
<div class="modal fade" id="iwScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="iwScheduleForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6" id="iwScheduleTitle">Arrange interview</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <p class="mb-2 fw-semibold" id="iwScheduleWho"></p>
                <p class="text-muted mb-3" id="iwScheduleHelp"></p>

                <label class="form-label" for="iwScheduleAt">Date and time</label>
                <input type="datetime-local" class="form-control form-control-sm" id="iwScheduleAt" required>

                <div class="row g-2 mt-1">
                    <div class="col-sm-5">
                        <label class="form-label" for="iwDuration">Minutes</label>
                        <input type="number" class="form-control form-control-sm" id="iwDuration" min="1" placeholder="45">
                    </div>
                    <div class="col-sm-7">
                        <label class="form-label" for="iwVenue">Venue</label>
                        <input type="text" class="form-control form-control-sm" id="iwVenue" maxlength="150" placeholder="HR Office">
                    </div>
                </div>

                <label class="form-label mt-2" for="iwLink">Meeting link <span class="text-muted">(optional)</span></label>
                <input type="url" class="form-control form-control-sm" id="iwLink" maxlength="500" placeholder="https://">

                <div class="form-check mt-3" id="iwCopyWrap" hidden>
                    <input class="form-check-input" type="checkbox" id="iwCopyInterviewers" checked>
                    <label class="form-check-label" for="iwCopyInterviewers">
                        Keep the same interviewers
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary" id="iwScheduleSubmit">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Recording what an interviewer concluded. --}}
<div class="modal fade" id="iwEvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="iwEvalForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6" id="iwEvalTitle">Record evaluation</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <p class="mb-2 fw-semibold" id="iwEvalWho"></p>

                {{-- A correction replaces a submitted evaluation and cannot
                     itself be edited afterwards, so it says so plainly. --}}
                <div class="alert alert-warning py-2" id="iwEvalCorrectionNotice" hidden>
                    This replaces your submitted evaluation. The original is kept and stays readable.
                    <b>A correction is submitted immediately and cannot be edited afterwards.</b>
                </div>

                <label class="form-label">Outcome for this round</label>
                <div class="btn-group d-flex mb-3" role="group">
                    <input type="radio" class="btn-check" name="iwOutcome" id="iwOutProceed" value="proceed">
                    <label class="btn btn-sm btn-outline-success" for="iwOutProceed">Proceed</label>

                    <input type="radio" class="btn-check" name="iwOutcome" id="iwOutHold" value="hold">
                    <label class="btn btn-sm btn-outline-warning" for="iwOutHold">Hold</label>

                    <input type="radio" class="btn-check" name="iwOutcome" id="iwOutNo" value="do_not_proceed">
                    <label class="btn btn-sm btn-outline-danger" for="iwOutNo">Do Not Proceed</label>
                </div>
                <div class="form-text mb-3">
                    This is about this round only — whether they go on to the next step. It is not a hiring decision.
                </div>

                <label class="form-label" for="iwRecommendation">Recommendation</label>
                <textarea class="form-control form-control-sm" id="iwRecommendation" rows="2" maxlength="2000"></textarea>

                <label class="form-label mt-2" for="iwRemarks">Remarks</label>
                <textarea class="form-control form-control-sm" id="iwRemarks" rows="3" maxlength="2000"></textarea>

                <div id="iwReasonWrap" hidden>
                    <label class="form-label mt-2" for="iwCorrectionReason">Why are you correcting this? <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="iwCorrectionReason" maxlength="300">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="iwEvalDraft">Save draft</button>
                <button type="submit" class="btn btn-sm btn-primary" id="iwEvalSubmit">Submit</button>
            </div>
        </form>
    </div>
</div>

{{-- Earlier revisions of one interviewer's evaluation, read-only. --}}
<div class="modal fade" id="iwHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-6">Evaluation history</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)" id="iwHistoryBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const APP_ID = @json((int) $application->id);
    const CAN_DECIDE = @json($canDecide);
    const ME = @json((string) (auth()->user()->Emp_No ?? ''));
    const BASE = @json(rtrim(url('/recruitment/applications'), '/')) + '/' + APP_ID + '/interviews';
    const CSRF = @json(csrf_token());

    // Only ever these three. No score, no total, no ranking.
    const OUTCOME_LABEL = {
        proceed: 'Proceed',
        do_not_proceed: 'Do Not Proceed',
        hold: 'Hold'
    };

    const ATTENDANCE_LABEL = {
        scheduled: 'Scheduled',
        held: 'Held',
        cancelled: 'Cancelled',
        no_show: 'No show'
    };

    let ROUNDS = [];
    let ASSESSMENTS = [];

    function esc(value) {
        const d = document.createElement('div');
        d.textContent = value == null ? '' : value;
        return d.innerHTML;
    }

    function when(value) {
        if (!value) return 'No date yet';
        const d = new Date(value.replace(' ', 'T'));
        return d.toLocaleString('en-US', {
            year: 'numeric', month: 'short', day: '2-digit',
            hour: 'numeric', minute: '2-digit'
        });
    }

    function alertBox(message, kind) {
        document.getElementById('iw-alert').innerHTML =
            '<div class="alert alert-' + (kind || 'danger') + ' py-2">' + esc(message) + '</div>';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function api(path, method, body) {
        const res = await fetch(BASE + path, {
            method: method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body ? JSON.stringify(body) : undefined
        });

        const text = await res.text();
        let data = {};
        try { data = text ? JSON.parse(text) : {}; } catch (e) { /* not json */ }

        if (!res.ok) {
            // Laravel returns the first validation message under errors.
            const first = data.errors ? Object.values(data.errors)[0] : null;
            throw new Error(
                (Array.isArray(first) ? first[0] : first) ||
                data.message ||
                (res.status === 403 ? 'You do not have permission to do that.' : 'That could not be completed.')
            );
        }

        return data;
    }

    /* ----------------------------------------------------------- rendering */

    function evaluationLine(ev, interview) {
        const mine = ev.evaluator_empno === ME;
        const outcome = ev.outcome
            ? '<span class="hf-chip iw-outcome-' + esc(ev.outcome) + '">' + esc(OUTCOME_LABEL[ev.outcome] || ev.outcome) + '</span>'
            : '<span class="hf-chip">No outcome yet</span>';

        // Draft and submitted must never look alike: one can still be changed.
        const state = ev.submitted
            ? '<span class="iw-sub">Submitted</span>'
            : '<span class="hf-chip hf-chip-pool">Draft</span>';

        const revised = ev.revision > 1
            ? ' <span class="iw-sub">· revision ' + esc(ev.revision) + '</span>'
            : '';

        let actions = '';
        if (ev.revision > 1) {
            actions += ' <button type="button" class="btn btn-sm btn-link p-0 js-iw-history"' +
                ' data-interview="' + esc(interview.id) + '" data-empno="' + esc(ev.evaluator_empno) + '">' +
                'Earlier versions</button>';
        }
        if (CAN_DECIDE && mine && ev.submitted) {
            actions += ' <button type="button" class="btn btn-sm btn-link p-0 js-iw-correct"' +
                ' data-evaluation="' + esc(ev.id) + '" data-interview="' + esc(interview.id) + '">' +
                'Correct</button>';
        }
        if (CAN_DECIDE && mine && !ev.submitted) {
            actions += ' <button type="button" class="btn btn-sm btn-link p-0 js-iw-evaluate"' +
                ' data-interview="' + esc(interview.id) + '">Continue draft</button>';
        }

        const notes = [ev.recommendation, ev.remarks].filter(Boolean).join('\n\n');

        return '<div class="iw-eval">' +
            '<span>' + esc(ev.evaluator_name) + (mine ? ' <span class="iw-sub">(you)</span>' : '') + '</span>' +
            outcome + state + revised + actions +
            (notes ? '<div class="iw-eval-notes">' + esc(notes) + '</div>' : '') +
            '</div>';
    }

    function interviewBlock(iv, round) {
        const att = '<span class="hf-chip iw-att-' + esc(iv.attendance) + '">' +
            esc(ATTENDANCE_LABEL[iv.attendance] || iv.attendance) + '</span>';

        const people = iv.interviewers.length
            ? iv.interviewers.map(esc).join(', ')
            : '<span class="iw-sub">No interviewer assigned yet</span>';

        let actions = '';
        if (CAN_DECIDE) {
            if (iv.attendance === 'scheduled') {
                actions =
                    '<button type="button" class="btn btn-sm btn-outline-success js-iw-held" data-interview="' + esc(iv.id) + '">Mark held</button> ' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary js-iw-reschedule" data-interview="' + esc(iv.id) + '">Reschedule</button> ' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary js-iw-noshow" data-interview="' + esc(iv.id) + '">No show</button> ' +
                    '<button type="button" class="btn btn-sm btn-outline-danger js-iw-cancel" data-interview="' + esc(iv.id) + '">Cancel</button>';
            }
            if (iv.attendance === 'held' || iv.attendance === 'scheduled') {
                actions += ' <button type="button" class="btn btn-sm btn-outline-primary js-iw-assign" data-interview="' + esc(iv.id) + '">Add interviewer</button>';
            }
            if (iv.attendance === 'held') {
                const alreadyMine = iv.evaluations.some(e => e.evaluator_empno === ME);
                if (!alreadyMine) {
                    actions += ' <button type="button" class="btn btn-sm btn-outline-primary js-iw-evaluate" data-interview="' + esc(iv.id) + '">Record my evaluation</button>';
                }
            }
        }

        const evals = iv.evaluations.length
            ? '<div class="iw-evals">' + iv.evaluations.map(e => evaluationLine(e, iv)).join('') + '</div>'
            : (iv.attendance === 'held' ? '<div class="iw-empty">No evaluations recorded yet.</div>' : '');

        return '<div class="iw-interview">' +
            '<div><span class="iw-when">' + esc(when(iv.scheduled_at)) + '</span> ' + att +
            (iv.venue ? ' <span class="iw-meta">· ' + esc(iv.venue) + '</span>' : '') + '</div>' +
            '<div class="iw-meta">' + people + '</div>' +
            (actions ? '<div class="mt-2">' + actions + '</div>' : '') +
            evals +
            '</div>';
    }

    function examBlock() {
        if (!ASSESSMENTS.length) {
            return '<div class="iw-empty">No assessments are configured.</div>';
        }

        // Read-only. The assessment system owns these; this only reports them.
        return '<div class="iw-exam-grid">' + ASSESSMENTS.map(a =>
            '<div class="iw-exam-row"><span>' + esc(a.label) + '</span>' +
            '<span class="' + (a.hasResult ? '' : 'iw-sub') + '">' +
            esc(a.hasResult ? (a.takenOn || 'Completed') : 'Not taken') + '</span></div>'
        ).join('') + '</div>' +
        '<div class="iw-sub mt-2">Assessment results belong to the applicant and are shown here for reference. They are recorded in the assessment system, not here.</div>';
    }

    function roundCard(round) {
        const cls = round.state === 'completed' ? 'is-done'
            : (round.state === 'not_applicable' ? 'is-na' : 'is-current');

        const stateChip = round.state === 'completed'
            ? '<span class="hf-chip hf-chip-ok">Completed</span>'
            : (round.state === 'not_applicable'
                ? '<span class="hf-chip">Not applicable</span>'
                : '<span class="hf-chip hf-chip-pool">Outstanding</span>');

        let actions = '';
        if (CAN_DECIDE && round.state !== 'completed') {
            const live = round.interviews.some(i => i.attendance === 'scheduled');
            if (!live) {
                actions += '<button type="button" class="btn btn-sm btn-outline-primary js-iw-arrange" data-step="' + esc(round.step_id) + '">Arrange interview</button>';
            }
            if (round.interviews.some(i => i.attendance === 'held')) {
                actions += ' <button type="button" class="btn btn-sm btn-primary js-iw-complete" data-step="' + esc(round.step_id) + '">Complete round</button>';
            }
            if (round.can_remove) {
                actions += ' <button type="button" class="btn btn-sm btn-outline-danger js-iw-remove-step" data-step="' + esc(round.step_id) + '">Remove</button>';
            }
        }

        const body = round.interviews.length
            ? round.interviews.map(iv => interviewBlock(iv, round)).join('')
            : '<div class="iw-empty">No interview arranged yet.</div>';

        return '<div class="iw-round ' + cls + '">' +
            '<div class="iw-round-top">' +
            '<span class="iw-round-title">' + esc(round.label) + '</span>' + stateChip +
            (round.result ? ' <span class="iw-sub">· ' + esc(round.result) + '</span>' : '') +
            (actions ? '<span class="iw-round-actions">' + actions + '</span>' : '') +
            '</div>' + body + '</div>';
    }

    function render() {
        const host = document.getElementById('iw-rounds');

        if (!ROUNDS.length) {
            host.innerHTML = '<div class="iw-empty">This application has no interview rounds yet.</div>';
            return;
        }

        let html = ROUNDS.map(roundCard).join('');

        // Exams sits between the interview rounds in the process, and is shown
        // here for context — but it is not an interview and gets no interview
        // controls.
        html += '<div class="iw-round"><div class="iw-round-top">' +
            '<span class="iw-round-title">Examinations</span>' +
            '<span class="hf-chip">Assessment system</span></div>' +
            examBlock() + '</div>';

        if (CAN_DECIDE) {
            html += '<button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="iw-advance">' +
                'Move to the next outstanding step</button>';
        }

        host.innerHTML = html;
    }

    async function load() {
        try {
            const data = await api('/data');
            ROUNDS = data.rounds || [];
            ASSESSMENTS = data.assessments || [];

            // Which rounds may be removed is a service decision, not a guess
            // from the page: it depends on interview history the page can see
            // but should not reason about.
            ROUNDS.forEach(r => {
                const untouched = r.interviews.length === 0;
                r.can_remove = untouched && r.state === 'pending' && !['initial', 'final'].includes(r.stage_key);
            });

            render();
        } catch (e) {
            document.getElementById('iw-rounds').innerHTML =
                '<div class="alert alert-danger py-2">' + esc(e.message) + '</div>';
        }
    }

    /* ------------------------------------------------------------ actions */

    let scheduleMode = null;   // {type:'create'|'reschedule', stepId|interviewId}
    let evalMode = null;       // {interviewId, evaluationId|null}

    function openSchedule(mode, title, help, showCopy) {
        scheduleMode = mode;
        document.getElementById('iwScheduleTitle').textContent = title;
        document.getElementById('iwScheduleWho').textContent = help;
        document.getElementById('iwScheduleHelp').textContent = showCopy
            ? 'The interview being replaced is kept as history, marked cancelled.'
            : 'The candidate is not notified automatically.';
        document.getElementById('iwCopyWrap').hidden = !showCopy;
        document.getElementById('iwScheduleForm').reset();
        document.getElementById('iwCopyInterviewers').checked = true;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('iwScheduleModal')).show();
    }

    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;

        try {
            if (btn.classList.contains('js-iw-arrange')) {
                openSchedule({ type: 'create', stepId: btn.dataset.step }, 'Arrange interview', '', false);
            }

            if (btn.classList.contains('js-iw-reschedule')) {
                openSchedule({ type: 'reschedule', interviewId: btn.dataset.interview },
                    'Reschedule interview', '', true);
            }

            if (btn.classList.contains('js-iw-held')) {
                await api('/' + btn.dataset.interview + '/held', 'POST');
                await load();
            }

            if (btn.classList.contains('js-iw-noshow')) {
                if (!await hfConfirm({
                    title: 'Record a no-show?',
                    message: 'The candidate will be recorded as not having attended this interview.',
                    detail: 'This is kept permanently and cannot be undone. If they simply need a new time, reschedule instead.',
                    confirmLabel: 'Record no-show',
                    tone: 'warn'
                })) return;
                await api('/' + btn.dataset.interview + '/no-show', 'POST');
                await load();
            }

            if (btn.classList.contains('js-iw-cancel')) {
                /* The reason is required, and the dialog says so and stays
                   open rather than cancelling the whole action and making HR
                   start again -- which is what the old prompt + alertBox pair
                   did. */
                const reason = await hfPrompt({
                    title: 'Cancel this interview?',
                    message: 'It will not go ahead, and the reason is kept with the application.',
                    label: 'Why is it being cancelled?',
                    placeholder: 'e.g. the panel is unavailable',
                    required: true,
                    requiredMessage: 'A cancellation needs a reason. It is kept on the record.',
                    maxLength: 300,
                    confirmLabel: 'Cancel interview',
                    tone: 'warn'
                });
                if (reason === null) return;
                await api('/' + btn.dataset.interview + '/cancel', 'POST', { reason: reason });
                await load();
            }

            if (btn.classList.contains('js-iw-assign')) {
                const empno = await hfPrompt({
                    title: 'Add an interviewer',
                    message: 'They get access to this interview, and to nothing else about the applicant.',
                    label: 'Employee number',
                    placeholder: '045-2025-001',
                    required: true,
                    requiredMessage: 'Give the interviewer\'s employee number.',
                    maxLength: 20,
                    confirmLabel: 'Add interviewer'
                });
                if (empno === null) return;
                await api('/' + btn.dataset.interview + '/interviewers', 'POST', { empno: empno });
                await load();
            }

            if (btn.classList.contains('js-iw-evaluate')) {
                evalMode = { interviewId: btn.dataset.interview, evaluationId: null };
                document.getElementById('iwEvalTitle').textContent = 'Record evaluation';
                document.getElementById('iwEvalWho').textContent = 'Your view of this interview. Other interviewers record their own.';
                document.getElementById('iwEvalCorrectionNotice').hidden = true;
                document.getElementById('iwReasonWrap').hidden = true;
                document.getElementById('iwEvalDraft').hidden = false;
                document.getElementById('iwEvalSubmit').textContent = 'Submit';
                document.getElementById('iwEvalForm').reset();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('iwEvalModal')).show();
            }

            if (btn.classList.contains('js-iw-correct')) {
                evalMode = { interviewId: btn.dataset.interview, evaluationId: btn.dataset.evaluation };
                document.getElementById('iwEvalTitle').textContent = 'Correct your evaluation';
                document.getElementById('iwEvalWho').textContent = 'Your original stays on the record and remains readable.';
                document.getElementById('iwEvalCorrectionNotice').hidden = false;
                document.getElementById('iwReasonWrap').hidden = false;
                // A correction has no draft stage.
                document.getElementById('iwEvalDraft').hidden = true;
                document.getElementById('iwEvalSubmit').textContent = 'Submit correction';
                document.getElementById('iwEvalForm').reset();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('iwEvalModal')).show();
            }

            if (btn.classList.contains('js-iw-history')) {
                const data = await api('/' + btn.dataset.interview + '/evaluations/' + encodeURIComponent(btn.dataset.empno) + '/history');
                document.getElementById('iwHistoryBody').innerHTML = (data.history || []).map(h =>
                    '<div class="iw-interview ' + (h.superseded_at ? 'iw-superseded' : '') + '">' +
                    '<div><b>Revision ' + esc(h.revision) + '</b> ' +
                    (h.outcome ? '<span class="hf-chip iw-outcome-' + esc(h.outcome) + '">' + esc(OUTCOME_LABEL[h.outcome]) + '</span>' : '') +
                    (h.superseded_at ? ' <span class="iw-sub">superseded</span>' : ' <span class="iw-sub">current</span>') +
                    '</div>' +
                    (h.correction_reason ? '<div class="iw-meta">Reason for correcting: ' + esc(h.correction_reason) + '</div>' : '') +
                    (h.recommendation ? '<div class="iw-eval-notes">' + esc(h.recommendation) + '</div>' : '') +
                    (h.remarks ? '<div class="iw-eval-notes">' + esc(h.remarks) + '</div>' : '') +
                    '</div>'
                ).join('') || '<div class="iw-empty">No earlier versions.</div>';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('iwHistoryModal')).show();
            }

            if (btn.classList.contains('js-iw-complete')) {
                const result = await hfPrompt({
                    title: 'Complete this round',
                    message: 'What the round concluded, in your words. It is kept with the application.',
                    label: 'Outcome',
                    placeholder: 'e.g. strong on the technical side, to meet the department head',
                    multiline: true,
                    maxLength: 100,
                    confirmLabel: 'Complete round'
                });
                if (result === null) return;
                await api('/steps/' + btn.dataset.step + '/complete', 'POST', { result: result || null });
                alertBox('Round completed. The application has not moved yet.', 'success');
                await load();
            }

            if (btn.classList.contains('js-iw-remove-step')) {
                if (!await hfConfirm({
                    title: 'Remove this round?',
                    message: 'It is taken out of this application\'s sequence.',
                    detail: 'Only possible while nothing has happened on it. A round with an interview or an evaluation on it cannot be removed.',
                    confirmLabel: 'Remove round',
                    tone: 'warn'
                })) return;
                await api('/steps/' + btn.dataset.step, 'DELETE');
                await load();
            }

            if (btn.id === 'iw-add-step') {
                const label = await hfPrompt({
                    title: 'Add an interview round',
                    message: 'It is added to this application only. Nobody else\'s sequence changes.',
                    label: 'What should it be called?',
                    placeholder: 'e.g. Technical Interview',
                    required: true,
                    requiredMessage: 'Give the round a name.',
                    maxLength: 100,
                    confirmLabel: 'Add round'
                });
                if (label === null) return;
                await api('/steps', 'POST', { label: label });
                await load();
            }

            if (btn.id === 'iw-advance') {
                /* One dialog, not two. The old pair asked for confirmation
                   and THEN asked for a reason, so cancelling the second left
                   HR unsure whether the first had already done something. */
                const reason = await hfPrompt({
                    title: 'Move to the next step?',
                    message: 'The application moves to its next outstanding step.',
                    detail: 'Its stage history records the move. Nothing about the current round is changed.',
                    label: 'Why is it moving on?',
                    placeholder: 'Optional',
                    required: false,
                    maxLength: 300,
                    confirmLabel: 'Move on'
                });
                if (reason === null) return;
                const res = await api('/advance', 'POST', { reason: reason || null });
                alertBox('Moved to ' + (res.moved_to ? res.moved_to.label : 'the next step') + '.', 'success');
                await load();
            }
        } catch (err) {
            alertBox(err.message);
        }
    });

    document.getElementById('iwScheduleForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const payload = {
            scheduled_at: document.getElementById('iwScheduleAt').value.replace('T', ' '),
            duration_minutes: document.getElementById('iwDuration').value || null,
            venue: document.getElementById('iwVenue').value || null,
            meeting_link: document.getElementById('iwLink').value || null
        };

        try {
            if (scheduleMode.type === 'create') {
                await api('/steps/' + scheduleMode.stepId, 'POST', payload);
            } else {
                payload.copy_interviewers = document.getElementById('iwCopyInterviewers').checked;
                await api('/' + scheduleMode.interviewId + '/reschedule', 'POST', payload);
            }
            bootstrap.Modal.getInstance(document.getElementById('iwScheduleModal')).hide();
            await load();
        } catch (err) {
            alertBox(err.message);
        }
    });

    async function sendEvaluation(submit) {
        const outcome = (document.querySelector('input[name="iwOutcome"]:checked') || {}).value || null;
        const payload = {
            outcome: outcome,
            recommendation: document.getElementById('iwRecommendation').value || null,
            remarks: document.getElementById('iwRemarks').value || null
        };

        try {
            if (evalMode.evaluationId) {
                payload.correction_reason = document.getElementById('iwCorrectionReason').value;
                if (!payload.correction_reason.trim()) {
                    alertBox('Please say why you are correcting this evaluation.');
                    return;
                }
                if (!await hfConfirm({
                    title: 'Submit this correction?',
                    message: 'It replaces your previous evaluation as the one in force.',
                    detail: 'Your original stays on the record and remains readable. A submitted correction cannot be edited.',
                    confirmLabel: 'Submit correction',
                    tone: 'warn'
                })) {
                    return;
                }
                await api('/evaluations/' + evalMode.evaluationId + '/correct', 'POST', payload);
            } else if (submit) {
                if (!await hfConfirm({
                    title: 'Submit this evaluation?',
                    message: 'It becomes part of the applicant\'s record.',
                    detail: 'It cannot be edited afterwards — you would have to file a correction, which is kept alongside it.',
                    confirmLabel: 'Submit evaluation',
                    tone: 'warn'
                })) {
                    return;
                }
                await api('/' + evalMode.interviewId + '/evaluations', 'POST', payload);
            } else {
                await api('/' + evalMode.interviewId + '/evaluations/draft', 'POST', payload);
            }

            bootstrap.Modal.getInstance(document.getElementById('iwEvalModal')).hide();
            await load();
        } catch (err) {
            alertBox(err.message);
        }
    }

    document.getElementById('iwEvalForm').addEventListener('submit', function (e) {
        e.preventDefault();
        sendEvaluation(true);
    });

    document.getElementById('iwEvalDraft').addEventListener('click', function () {
        sendEvaluation(false);
    });

    load();
})();
</script>
