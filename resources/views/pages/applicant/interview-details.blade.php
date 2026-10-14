@extends('pages.applicant.profile')

@section('profile_content')
@php
    /*
     | The applicant's interviews, organised by when HR needs each thing.
     |
     | The previous version showed everything at once — every round, every
     | action, all eleven assessments, for every application — which is correct
     | but unreadable: a mid-process application put forty-odd buttons on screen
     | at the same visual weight, and nothing told a new HR user which one to
     | press. Nothing has been removed here; it has been ordered.
     |
     |   Level 1  one sentence saying what to do next, and a stepper showing
     |            where the applicant is
     |   Level 2  the current round opened: schedule, interviewers, evaluations
     |   Level 3  notes, earlier attempts, revisions, assessment detail — all
     |            one click away, none of it competing for attention
     |
     | Interviews belong to an application, so each application is its own
     | panel and only one opens at a time: two applications' rounds can never
     | sit next to each other where they might be read as one process.
     */
    $canDecide = Gate::allows('applicant-applications.decide');
    $applications = collect($interviewApplications ?? []);
    $assessments = collect($interviewAssessments ?? []);
    $assessmentsDone = $assessments->where('hasResult', true)->count();
@endphp

<style>
    /* ------------------------------------------------- the application bar
    |
    | Interviews belong to ONE application. This used to be an accordion per
    | application, which gave each one a heavy header and let two sets of rounds
    | sit open at once. It is now a selector: exactly one application's workflow
    | is on screen at a time, so nothing can be read as belonging to the other.
    */
    .iv-apps {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding-bottom: 10px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--zn-line);
    }

    .iv-apps-label {
        font-size: var(--zn-fs-xs);
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 700;
        color: var(--zn-ink-2);
        margin-right: 2px;
    }

    .iv-app-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--zn-surface, #fff);
        border: 1px solid var(--zn-line);
        border-radius: 999px;
        padding: 5px 13px;
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        color: var(--zn-ink);
        cursor: pointer;
    }

    .iv-app-tab:hover { border-color: var(--zn-ink-3); }

    .iv-app-tab[aria-selected="true"] {
        background: var(--zn-accent-soft, #f0e7df);
        border-color: var(--zn-accent, #5d2502);
        color: var(--zn-accent-dark, #3e1801);
    }

    .iv-app-tab[disabled] { opacity: 1; cursor: default; }
    .iv-app-tab.dropdown-toggle::after { margin-left: 2px; color: var(--zn-ink-3); }
    .iv-app-tab[disabled].dropdown-toggle::after { display: none; }

    /* The applications not on screen, named so the menu need not be opened. */
    .iv-apps-other { font-size: var(--zn-fs-sm); color: var(--zn-ink-2); }

    /* Stated outright: a stray `display` rule elsewhere would otherwise beat
       the hidden attribute and put two applications on screen at once. */
    .iv-panel[hidden] { display: none !important; }

    /* -------------------------------------------------------- progress rail
    |
    | The one element that answers "where is this candidate" at a glance, so it
    | leads rather than sitting under a banner that says the same thing in
    | words. Exams keeps its place in the sequence: it is part of the process
    | even though it is not an interview.
    */
    .iv-rail {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
    }

    .iv-step {
        flex: 1 1 108px;
        min-width: 96px;
        padding: 0;
        border: 0;
        background: none;
        text-align: left;
        cursor: pointer;
        font-size: var(--zn-fs-xs);
    }

    /*
    | Colours come from the theme, not from this file.
    |
    | The legacy page reached for --hf-done, --hf-progress and friends, which
    | are not defined anywhere: every one of them fell through to a hard-coded
    | hex, so the page ignored the theme and stayed light while the rest of the
    | application went dark. These are the real tokens.
    */
    .iv-step-bar {
        display: block;
        height: 5px;
        border-radius: 3px;
        background: var(--zn-line-2);
        margin-bottom: 7px;
    }

    .iv-step.is-done .iv-step-bar { background: var(--zn-ok); }
    .iv-step.is-current .iv-step-bar { background: var(--zn-accent-dark); }
    .iv-step.is-na { opacity: .55; }

    /*
    | Started but not finished — visibly under way, visibly not done.
    |
    | The dashes carry the meaning without relying on hue alone, so partial and
    | complete stay apart for anyone who cannot separate the two greens.
    */
    .iv-step.is-partial .iv-step-bar {
        background: repeating-linear-gradient(90deg,
            var(--zn-ok) 0 8px, var(--zn-line-2) 8px 14px);
    }

    .iv-step-name {
        display: block;
        font-weight: 600;
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .iv-step.is-current .iv-step-name { color: var(--zn-accent-dark); }
    .iv-step.is-na .iv-step-name { font-weight: 500; color: var(--zn-ink-2); }
    .iv-step-sub { display: block; color: var(--zn-ink-2); font-size: var(--zn-fs-sm); }

    /* ------------------------------------------------------- the next action
    |
    | The only loud thing on the page, and the ONLY place the next action is
    | offered: the round it belongs to no longer repeats the same button a few
    | lines below.
    */
    .iv-next {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        margin-bottom: 16px;
        border: 1px solid var(--zn-line);
        border-left: 4px solid var(--zn-accent-dark);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        box-shadow: var(--zn-shadow);
        font-size: var(--zn-fs);
    }

    .iv-next.is-clear { border-left-color: var(--zn-ok); }

    .iv-next-label {
        display: block;
        font-weight: 700;
        letter-spacing: .07em;
        font-size: var(--zn-fs-xs);
        text-transform: uppercase;
        color: var(--zn-ink-3);
        margin-bottom: 3px;
    }

    .iv-next-main { flex: 1 1 320px; min-width: 220px; }

    .iv-next-text {
        display: block;
        color: var(--zn-ink);
        font-weight: 700;
        font-size: var(--zn-fs-lg);
        line-height: 1.3;
    }

    .iv-next-sub {
        display: block;
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-ui);
        margin-top: 2px;
    }

    .iv-next-act { flex: 0 0 auto; display: flex; gap: 8px; align-items: center; }

    /* --------------------------------------------------- section headings
    |
    | "Progress" and "Rounds" as overlines, with the thing each section needs
    | on the right of its own heading: the position summary, and the control
    | that adds a round. Without them the page was one undifferentiated column
    | and the add button floated loose under the list.
    */
    .iv-section {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin: 18px 0 9px;
    }

    .iv-section-label {
        font-size: var(--zn-fs-xs);
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
        color: var(--zn-ink-3);
    }

    .iv-section-note { font-size: var(--zn-fs-sm); color: var(--zn-ink-2); margin-left: auto; }
    .iv-section-act { margin-left: auto; }

    /*
    | A marker per row, read down the left edge.
    |
    | Filled where the round has something live or settled, hollow where it is
    | still to come. It repeats what the chip says rather than replacing it, so
    | nothing depends on telling the fills apart.
    */
    .iv-dot {
        flex: 0 0 auto;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        box-sizing: border-box;
        border: 2px solid var(--zn-line-2);
    }

    .iv-dot.is-done { background: var(--zn-ok); border-color: var(--zn-ok); }
    .iv-dot.is-live { background: var(--zn-accent-dark); border-color: var(--zn-accent-dark); }
    .iv-dot.is-wait { border-color: var(--zn-caution); }

    /* ------------------------------------------------ the rounds, one list */
    .iv-rounds {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        box-shadow: var(--zn-shadow);
    }

    .iv-round {
        border-bottom: 1px solid var(--zn-line);
        /*
        | Deliberately NOT overflow:hidden.
        |
        | It was, to clip the summary's background into the rounded corners —
        | and it also clipped the row's "..." menu, which opens near the right
        | edge, so "Candidate did not attend" was cut in half and unreachable.
        */
    }

    .iv-round:last-child { border-bottom: 0; }

    .iv-round > summary {
        list-style: none;
        cursor: pointer;
        padding: 10px 14px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 11px;
        font-size: var(--zn-fs-ui);
    }

    .iv-round > summary:hover { background: var(--zn-surface-2); }
    .iv-round > summary::-webkit-details-marker { display: none; }

    .iv-round > summary::before {
        content: '▸';
        color: var(--zn-ink-3);
        flex: 0 0 auto;
        font-size: 10px;
    }

    .iv-round[open] > summary::before { content: '▾'; }

    /* The round being worked on is the only one marked. */
    .iv-round.is-current > summary {
        background: var(--zn-accent-soft);
        box-shadow: inset 3px 0 0 var(--zn-accent-dark);
    }

    .iv-round.is-current > summary:hover { background: var(--zn-accent-soft); }
    .iv-round.is-done > summary .iv-round-name { font-weight: 500; color: var(--zn-ink-2); }
    .iv-round.is-na > summary { opacity: .6; }

    .iv-round-name {
        flex: 1 1 150px;
        min-width: 120px;
        font-weight: 600;
        font-size: var(--zn-fs);
        color: var(--zn-ink);
    }

    .iv-round-when { flex: 0 1 160px; min-width: 105px; color: var(--zn-ink-2); }

    .iv-round-who {
        flex: 0 1 140px;
        min-width: 0;
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-sm);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /*
    | The action, on the row.
    |
    | The first cut of this made you open a round before you could do anything
    | to it, which is a step backwards from the page it replaced. The one thing
    | each round most likely needs is on the row itself.
    */
    .iv-round-act { flex: 0 0 auto; margin-left: auto; display: flex; gap: 6px; align-items: center; }
    .iv-round-body { padding: 2px 14px 14px 34px; font-size: var(--zn-fs-ui); }

    /* --------------------------------------------- the opened round, inside
    |
    | What was observed on the left, what was concluded on the right. They are
    | different kinds of record — a note can be added later without correcting
    | anything, an evaluation cannot — and the layout says so.
    */
    .iv-detail {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 26px;
        margin-top: 10px;
    }

    .iv-detail-main { flex: 1 1 380px; min-width: 260px; }
    .iv-detail-side { flex: 1 1 280px; min-width: 240px; }

    .iv-block { margin-bottom: 14px; }
    .iv-block:last-child { margin-bottom: 0; }

    .iv-block-head {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 7px;
    }

    .iv-block-label {
        font-size: var(--zn-fs-xs);
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
        color: var(--zn-ink-3);
    }

    .iv-block-note { margin-left: auto; font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    /* Who conducted it */
    .iv-people { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }

    .iv-person {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--zn-surface);
        border: 1px solid var(--zn-line);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: var(--zn-fs-ui);
    }

    /* Taking somebody off this interview. Quiet until reached for. */
    .iv-person-off {
        background: none;
        border: 0;
        padding: 0 0 0 2px;
        margin-left: 2px;
        line-height: 1;
        font-size: 15px;
        color: var(--zn-ink-3);
        cursor: pointer;
    }

    .iv-person-off:hover { color: var(--zn-warn); }

    .iv-person-add {
        background: none;
        border: 1px dashed var(--zn-line-2);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: var(--zn-fs-ui);
        font-weight: 600;
        color: var(--zn-accent-dark);
        cursor: pointer;
    }

    .iv-person-add:hover { border-color: var(--zn-accent); background: var(--zn-accent-soft); }

    /* What was concluded */
    .iv-eval {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius);
        background: var(--zn-surface);
        margin-bottom: 8px;
    }

    .iv-eval-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 11px;
        border-bottom: 1px solid var(--zn-divider);
    }

    .iv-eval-head .hf-chip { margin-left: auto; }
    .iv-eval-body { padding: 10px 11px; }

    .iv-outcomes { display: flex; flex-wrap: wrap; gap: 6px; }

    /*
    | The three conclusions, offered directly.
    |
    | Each opens the full form with that outcome already chosen — the reasoning
    | still has to be written. Outlined rather than filled: choosing one is the
    | start of recording an evaluation, not the end of it.
    */
    .iv-outcome-pick {
        background: var(--zn-surface);
        border: 1px solid var(--zn-line-2);
        border-radius: 999px;
        padding: 5px 13px;
        font-size: var(--zn-fs-ui);
        font-weight: 600;
        cursor: pointer;
        color: var(--zn-ink-2);
    }

    .iv-outcome-pick.is-ok { color: var(--zn-ok); border-color: var(--zn-ok); }
    .iv-outcome-pick.is-hold { color: var(--zn-caution); border-color: var(--zn-caution); }
    .iv-outcome-pick.is-no { color: var(--zn-warn); border-color: var(--zn-warn); }
    .iv-outcome-pick:hover { background: var(--zn-surface-2); }

    /* Notes, as cards rather than a run of lines. */
    .iv-note {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius);
        background: var(--zn-surface);
        padding: 9px 11px;
        margin-bottom: 7px;
    }

    .iv-primary { margin-left: auto; }

    /* ------------------------------------------------ level 3: disclosures */
    .iv-more {
        border-top: 1px dotted var(--zn-line);
        margin-top: 10px;
        padding-top: 8px;
    }

    .iv-more > summary {
        cursor: pointer;
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-sm);
        list-style: none;
    }

    .iv-more > summary::-webkit-details-marker { display: none; }
    .iv-more > summary::before { content: '▸ '; }
    .iv-more[open] > summary::before { content: '▾ '; }
    .iv-more > div { padding-top: 8px; }

    .iv-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: baseline;
        padding: 5px 0;
        border-bottom: 1px dotted var(--zn-line);
    }

    .iv-row:last-child { border-bottom: 0; }
    .iv-muted { color: var(--zn-ink-2); }
    .iv-xs { font-size: var(--zn-fs-xs); }

    .iv-note-body { white-space: pre-wrap; color: var(--zn-ink); }

    .iv-empty { color: var(--zn-ink-3); font-size: var(--zn-fs-sm); padding: 4px 0; }

    /* Theme tokens, so these follow light and dark like everything else. */
    .iv-outcome-proceed { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .iv-outcome-do_not_proceed { background: var(--zn-warn-soft); color: var(--zn-warn); }
    .iv-outcome-hold { background: var(--zn-caution-soft); color: var(--zn-caution); }

    .iv-att-scheduled { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .iv-att-held { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .iv-att-cancelled { background: var(--zn-lock-soft); color: var(--zn-lock); }
    .iv-att-no_show { background: var(--zn-warn-soft); color: var(--zn-warn); }

    /* Waiting on somebody, not wrong -- the same attention colour the rest of
       HireFlow uses for "this needs you", not the warning red. */
    .iv-att-due { background: var(--zn-caution-soft); color: var(--zn-caution); }

    /* Shown first, but not open yet: quieter than the rounds around it, so
       its position in the list does not read as its turn. */
    .iv-step.is-locked { opacity: .62; }

    .iv-note-locked {
        margin-top: 10px;
        padding: 9px 12px;
        border-radius: var(--zn-radius);
        background: var(--zn-lock-soft);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-sm);
    }

    /*
    | Narrow screens.
    |
    | The round row is a flex row of five things; below 720px it wraps instead
    | of squeezing, so the name keeps a full line and the rest follows under it
    | rather than each column shrinking to an unreadable sliver.
    */
    @media (max-width: 720px) {
        .iv-round-name { flex-basis: 100%; }
        .iv-round-when, .iv-round-who { flex: 0 1 auto; }
        .iv-round-act { margin-left: 0; flex-basis: 100%; margin-top: 4px; }
        .iv-round-body { padding-left: 13px; }
    }

    @media (max-width: 640px) {
        .iv-primary { margin-left: 0; flex-basis: 100%; margin-top: 6px; }
        .iv-step { flex-basis: calc(50% - 3px); }
        .iv-round-body { padding: 4px 10px 10px; }
        .iv-next { padding: 11px 12px; }
    }

    @media (max-width: 420px) {
        .iv-step { flex-basis: 100%; }
    }
</style>

<div id="iv-alert"></div>

@if ($applications->isEmpty())
    <div class="iv-empty">This applicant has not applied to any posting yet, so there are no interviews to arrange.</div>
@else
    {{--
      | Which application's interviews are on screen.
      |
      | A tablist rather than stacked accordions: exactly one application shows
      | at a time, so two applications' rounds can never sit next to each other
      | where they might be read as one process. Each panel keeps its own
      | data-application, and each loads its own workflow — nothing is shared.
    --}}
    @php($first = $applications->first())
    <div class="iv-apps">
        <span class="iv-apps-label">Interviews for</span>

        {{--
          | One control naming the application on screen, not a row of equal
          | tabs. With several applications they competed for attention and the
          | bar grew with every one; the current one is the headline and the
          | rest are a choice behind it.
        --}}
        <div class="dropdown">
            <button type="button"
                    class="iv-app-tab dropdown-toggle"
                    id="iv-app-current"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    {{ $applications->count() < 2 ? 'disabled' : '' }}>
                <span class="js-iv-current-title">{{ $first['posting_title'] }}</span>
                <span class="hf-chip {{ $first['is_closed'] ? 'hf-chip-lock' : 'hf-chip-ok' }} js-iv-current-status">{{ $first['status'] }}</span>
            </button>

            @if ($applications->count() > 1)
                <ul class="dropdown-menu">
                    @foreach ($applications as $index => $app)
                        <li>
                            <button type="button"
                                    class="dropdown-item js-iv-tab"
                                    data-application="{{ $app['id'] }}"
                                    data-title="{{ $app['posting_title'] }}"
                                    data-status="{{ $app['status'] }}"
                                    data-closed="{{ $app['is_closed'] ? '1' : '' }}"
                                    aria-current="{{ $index === 0 ? 'true' : 'false' }}">
                                {{ $app['posting_title'] }}
                                <span class="hf-chip {{ $app['is_closed'] ? 'hf-chip-lock' : 'hf-chip-ok' }}">{{ $app['status'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($applications->count() > 1)
            <span class="iv-apps-other js-iv-others"></span>
        @endif
    </div>

    @foreach ($applications as $index => $app)
        <div class="iv-panel js-iv-body"
             role="tabpanel"
             id="iv-panel-{{ $app['id'] }}"
             aria-labelledby="iv-tab-{{ $app['id'] }}"
             data-application="{{ $app['id'] }}"
             @if($index !== 0) hidden @endif>
            <div class="iv-empty">Loading…</div>
        </div>
    @endforeach

    @if ($applications->count() > 1)
        <p class="iv-muted iv-xs mt-3 mb-0">
            Each application has its own interviews. Nothing is shared between them.
        </p>
    @endif
@endif

@include('pages.applicant.partials.interview-modals', ['employees' => $employees ?? []])

<script>
(function () {
    const CAN_DECIDE = @json($canDecide);
    const ME = @json((string) (auth()->user()->Emp_No ?? ''));
    const ROOT = @json(rtrim(url('/recruitment/applications'), '/'));
    const CSRF = @json(csrf_token());
    const ASSESSMENTS = @json($assessments);

    /*
    | Can the applicant actually sit the examinations yet?
    |
    | Worked out from the rounds already on the page: the prerequisite is the
    | Initial Interview being COMPLETED, which is the same thing
    | AssessmentEligibility::PREREQUISITE_STAGE asks the database. Nothing new
    | is fetched and nothing about the rule is re-decided here -- the page
    | only needs to be able to say so.
    |
    | This matters because Examinations is now shown FIRST. Showing it first
    | without saying it is still waiting would imply the exams come before the
    | interview, which is the opposite of how the process runs and would have
    | HR handing out access codes that the server then refuses.
    */
    function examsUnlocked(appId) {
        const rounds = ((STATE[appId] || {}).rounds) || [];
        const initial = rounds.find(r => r.stage_key === 'initial');

        return !!initial && initial.state === 'completed';
    }
    const ASSESSMENTS_DONE = @json($assessmentsDone);

    const OUTCOME_LABEL = { proceed: 'Proceed', do_not_proceed: 'Do Not Proceed', hold: 'Hold' };
    const ATTENDANCE_LABEL = { scheduled: 'Scheduled', held: 'Held', cancelled: 'Cancelled', no_show: 'No show' };

    /*
    | Who may record what happened at an interview.
    |
    | Not the same question as "who may decide this application". The person
    | who knows whether an interview took place is the one who was in it, and
    | they are usually not an HR decision-maker -- which is why they used to
    | have to go to Zen-Hub to say so while this page showed them the round.
    |
    | HR keeps the right as well: somebody has to be able to tidy up a round
    | whose interviewer has left, and HR always could.
    |
    | This mirrors InterviewScheduling::guardAttendanceActor() exactly. It
    | hides nothing the server would allow and offers nothing the server
    | would refuse.
    */
    function canAttend(iv) {
        return CAN_DECIDE || (iv && (iv.interviewers || []).some(p => p.empno === ME));
    }

    /*
    | A scheduled interview whose time has passed.
    |
    | The server derives this; the page only repeats it. Deliberately shown
    | as a separate chip beside "Scheduled" rather than as a fifth status,
    | because the interview IS still scheduled -- it is the answer that is
    | overdue, not the booking that has changed.
    */
    function dueChip(iv) {
        return iv && iv.due
            ? ' <span class="hf-chip iv-att-due" title="Its time has passed. Confirm whether it took place.">Due</span>'
            : '';
    }

    // Keyed by application, so one application's rounds can never be drawn into
    // another's panel.
    const STATE = {};

    function esc(v) {
        const d = document.createElement('div');
        d.textContent = v == null ? '' : v;
        return d.innerHTML;
    }

    function when(v) {
        if (!v) return 'No date yet';
        return new Date(v.replace(' ', 'T')).toLocaleString('en-US', {
            year: 'numeric', month: 'short', day: '2-digit', hour: 'numeric', minute: '2-digit'
        });
    }

    function shortWhen(v) {
        if (!v) return 'not scheduled';
        return new Date(v.replace(' ', 'T')).toLocaleString('en-US', {
            month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit'
        });
    }

    /*
    | "Thu 1 Oct, 1:32 PM" — the day of the week first.
    |
    | What HR is deciding is whether an interview has happened yet, and the
    | weekday answers that faster than the year does. "Oct 01, 2026" made every
    | row the same length and the same shape.
    */
    function dayWhen(v) {
        if (!v) return '—';
        const d = new Date(v.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }) +
            ', ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    /** "Thursday 1 October, 1:32 PM" — spelled out, for the one line that leads. */
    function longWhen(v) {
        if (!v) return '';
        const d = new Date(v.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' }) +
            ', ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    function alertBox(message, kind) {
        document.getElementById('iv-alert').innerHTML =
            '<div class="alert alert-' + (kind || 'danger') + ' py-2">' + esc(message) + '</div>';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function api(applicationId, path, method, body) {
        const res = await fetch(ROOT + '/' + applicationId + '/interviews' + path, {
            method: method || 'GET',
            headers: {
                'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest'
            },
            body: body ? JSON.stringify(body) : undefined
        });

        const text = await res.text();
        let data = {};
        try { data = text ? JSON.parse(text) : {}; } catch (e) { /* not json */ }

        if (!res.ok) {
            const first = data.errors ? Object.values(data.errors)[0] : null;
            throw new Error((Array.isArray(first) ? first[0] : first) || data.message ||
                (res.status === 403 ? 'You do not have permission to do that.' : 'That could not be completed.'));
        }

        return data;
    }

    /* =================== LEVEL 1: where they are, what to do ============== */

    /*
    | How far along the assessments are.
    |
    | Done means ALL of them, not some. This once read
    | "ASSESSMENTS_DONE ? 'is-done' : ''", so one finished assessment painted
    | the bar complete while the line underneath still said "5 of 11 taken" —
    | the bar contradicting its own label.
    |
    | Read only. The assessment system owns these records; nothing here writes
    | to it.
    */
    function examsProgress() {
        const total = ASSESSMENTS.length;
        const done = ASSESSMENTS_DONE;

        return {
            total: total,
            done: done,
            all: total > 0 && done === total,
            partial: done > 0 && done < total,
            label: done + ' of ' + total + ' taken'
        };
    }

    function nextBlock(appId, next) {
        if (!next) {
            return '<div class="iv-next is-clear"><div class="iv-next-main">' +
                '<span class="iv-next-label">Next</span>' +
                '<span class="iv-next-text">Every interview round is settled. ' +
                'Move the application on when you are ready.</span></div>' +
                (CAN_DECIDE ? '<button type="button" class="btn btn-sm btn-outline-secondary js-iv-advance" data-app="' + appId + '">Move to next step</button>' : '') +
                '</div>';
        }

        /*
        | A question, then the particulars, then the answer as a button.
        |
        | The server's sentence states the situation and the instruction in one
        | run of prose ("… is scheduled for Thu 1 Oct, 1:32pm. Mark it held once
        | it has taken place."), which reads as something to work through. Asking
        | what HR has to decide, and putting when and with whom underneath, gets
        | to the same place in less reading.
        |
        | The action here is the only copy of itself: roundBlock and
        | liveInterview suppress whichever control this card is showing, so
        | "Mark held" never appears twice for the same interview.
        */
        const round = ((STATE[appId] || {}).rounds || [])
            .find(r => String(r.step_id) === String(next.step_id)) || {};
        const live = (round.interviews || []).find(i => i.attendance === 'scheduled');
        const held = (round.interviews || []).filter(i => i.attendance === 'held').slice(-1)[0];
        const subject = live || held;

        let question, detail, button = '', extra = '';

        if (next.action === 'held') {
            /* Overdue is worth saying out loud at the top of the page: it is
               the difference between "coming up" and "nobody has told us". */
            question = (live && live.due ? 'Did the ' + next.label + ' take place?' : 'Is the ' + next.label + ' still to come?');
            detail = longWhen(live && live.scheduled_at)
                + (live && live.due ? ' — this has passed. Confirm whether it took place.' : '');
            if (canAttend(live)) {
                button = '<button type="button" class="btn btn-sm btn-primary js-iv-held-next" data-app="' + appId + '" data-step="' + esc(next.step_id) + '">Mark held</button>';
            }
            if (CAN_DECIDE && live) {
                extra = '<button type="button" class="btn btn-sm btn-outline-secondary js-iv-reschedule" data-app="' + appId + '" data-interview="' + esc(live.id) + '">Reschedule</button>';
            }
        } else if (next.action === 'arrange') {
            question = 'Arrange the ' + next.label;
            detail = 'Nothing is scheduled for this round yet.';
            if (CAN_DECIDE) {
                button = '<button type="button" class="btn btn-sm btn-primary js-iv-arrange" data-app="' + appId + '" data-step="' + esc(next.step_id) + '">Schedule it</button>';
            }
        } else {
            question = 'What did the ' + next.label + ' conclude?';
            detail = longWhen(held && held.scheduled_at);
            if (CAN_DECIDE) {
                button = '<button type="button" class="btn btn-sm btn-primary js-iv-complete" data-app="' + appId + '" data-step="' + esc(next.step_id) + '" data-label="' + esc(next.label) + '">Record outcome</button>';
            }
        }

        const people = subject && subject.interviewers && subject.interviewers.length
            ? ' · with ' + subject.interviewers.map(p => p.name).join(', ') : '';

        // The rarely-wanted things, in one place, as on every other row.
        const menu = subject ? interviewMenu(subject, appId) : '';

        return '<div class="iv-next"><div class="iv-next-main">' +
            '<span class="iv-next-label">Next</span>' +
            '<span class="iv-next-text">' + esc(question) + '</span>' +
            '<span class="iv-next-sub">' + esc(detail + people) + '</span></div>' +
            '<span class="iv-next-act">' + button + extra + menu + '</span></div>';
    }

    function progressRail(appId, rounds) {
        // The sequence at a glance: completed, current, still to come. Exams is
        // shown in its place because it is part of the process, even though it
        // is not an interview and lives in a separate system.
        const parts = rounds.map(r => {
            const cls = r.state === 'completed' ? 'is-done'
                : (r.state === 'not_applicable' ? 'is-na'
                : (r.step_id === (STATE[appId] || {}).currentStepId ? 'is-current' : ''));

            const live = r.interviews.find(i => i.attendance === 'scheduled');
            const held = r.interviews.filter(i => i.attendance === 'held').length;

            const sub = r.state === 'completed' ? (r.result || 'completed')
                : (r.state === 'not_applicable' ? 'not applicable'
                : (live ? dayWhen(live.scheduled_at)
                : (held ? 'Held' : 'Not scheduled')));

            return '<button type="button" class="iv-step ' + cls + ' js-iv-focus" data-app="' + appId + '" data-step="' + esc(r.step_id) + '">' +
                '<span class="iv-step-bar"></span>' +
                '<span class="iv-step-name">' + esc(r.display_label) + '</span>' +
                '<span class="iv-step-sub">' + esc(sub) + '</span></button>';
        });

        if (ASSESSMENTS.length) {
            const ex = examsProgress();
            const unlocked = examsUnlocked(appId);
            const examsCls = ex.all ? 'is-done' : (ex.partial ? 'is-partial' : (unlocked ? '' : 'is-locked'));

            /*
            | Examinations is shown FIRST.
            |
            | A presentation change only, asked for so the row HR looks at
            | most sits at the top. The workflow underneath is untouched: the
            | stage catalogue still orders Initial (10), Exams (20), Final
            | (90), the steps still carry those positions, progression still
            | follows them, and nothing here reorders a single stored row.
            |
            | Because the display and the sequence now disagree, the row has
            | to say which one it is. Until the Initial Interview is
            | completed the exams are not open, and the sub-label says so
            | rather than leaving the position to imply otherwise.
            */
            parts.unshift(
                '<button type="button" class="iv-step ' + examsCls + ' js-iv-exams" data-app="' + appId + '">' +
                '<span class="iv-step-bar"></span>' +
                '<span class="iv-step-name">Examinations</span>' +
                '<span class="iv-step-sub">' +
                    esc(unlocked ? ex.label : 'After the Initial Interview') +
                '</span></button>');
        }

        return '<div class="iv-rail">' + parts.join('') + '</div>';
    }

    /* ================== LEVEL 2 + 3: one round, opened ================== */

    function evaluationRow(ev, iv, appId) {
        const mine = ev.evaluator_empno === ME;

        /*
        | Correcting and continuing need both: the evaluation has to be yours,
        | and you have to still be conducting the interview. Taking somebody off
        | it ends what they may do to it — their record stays, and stays theirs,
        | but it is closed to further revision.
        |
        | The same pair is checked in InterviewEvaluationService::correct();
        | this only keeps the page from offering what the server would refuse.
        */
        const stillOn = iv.interviewers.some(p => p.empno === ME);
        const outcome = ev.outcome
            ? '<span class="hf-chip iv-outcome-' + esc(ev.outcome) + '">' + esc(OUTCOME_LABEL[ev.outcome]) + '</span>'
            : '<span class="hf-chip">awaiting</span>';

        // Draft and submitted must never look alike: one can still be changed.
        const state = ev.submitted
            ? '<span class="iv-muted iv-xs">submitted</span>'
            : '<span class="hf-chip hf-chip-pool">draft</span>';

        let extra = '';
        if (ev.revision > 1) {
            extra += ' <button type="button" class="btn btn-sm btn-link p-0 iv-xs js-iv-history" data-app="' + appId +
                '" data-interview="' + esc(iv.id) + '" data-empno="' + esc(ev.evaluator_empno) + '">revision ' + esc(ev.revision) + ' · earlier versions</button>';
        }
        if (mine && stillOn && ev.submitted) {
            extra += ' <button type="button" class="btn btn-sm btn-link p-0 iv-xs js-iv-correct" data-app="' + appId + '" data-evaluation="' + esc(ev.id) + '">correct</button>';
        }
        if (mine && stillOn && !ev.submitted) {
            extra += ' <button type="button" class="btn btn-sm btn-link p-0 iv-xs js-iv-evaluate" data-app="' + appId + '" data-interview="' + esc(iv.id) + '">continue</button>';
        }

        const notes = [ev.recommendation, ev.remarks].filter(Boolean).join('\n\n');

        return '<div class="iv-row"><span>' + esc(ev.evaluator_name) + (mine ? ' <span class="iv-muted">(you)</span>' : '') + '</span>' +
            outcome + state + extra +
            (notes ? '<div class="iv-xs iv-muted" style="flex-basis:100%;white-space:pre-wrap">' + esc(notes) + '</div>' : '') +
            '</div>';
    }

    /*
    | The less-common things one can do to an interview.
    |
    | Built here rather than inline so the row and the opened round can both
    | show it: with one live interview it belongs on the row beside the action,
    | with several it belongs to each one.
    */
    function interviewMenu(iv, appId) {
        /*
        | Two audiences, different menus.
        |
        | An assigned interviewer gets the one thing they are entitled to say
        | -- that the candidate did not turn up. Rescheduling, cancelling and
        | changing the panel are HR's, and the server refuses them to anyone
        | else, so offering them here would only produce a 403.
        */
        if (!CAN_DECIDE) {
            if (iv.attendance !== 'scheduled' || !canAttend(iv)) return '';

            return '<div class="dropdown d-inline"><button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-label="More actions for this interview">⋯</button>' +
                '<ul class="dropdown-menu dropdown-menu-end">' +
                '<li><button type="button" class="dropdown-item js-iv-noshow" data-app="' + appId +
                '" data-interview="' + esc(iv.id) + '">Candidate did not attend</button></li>' +
                '</ul></div>';
        }

        let menu = [];
        if (iv.attendance === 'scheduled') {
            menu = [
                ['js-iv-reschedule', 'Reschedule'],
                ['js-iv-noshow', 'Candidate did not attend'],
                ['js-iv-cancel', 'Cancel interview']
            ];
        }
        if (iv.attendance === 'scheduled' || iv.attendance === 'held') {
            menu = menu.concat([['js-iv-assign', 'Add interviewer']]);
        }
        if (!menu.length) return '';

        return '<div class="dropdown d-inline"><button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-label="More actions for this interview">⋯</button>' +
            '<ul class="dropdown-menu dropdown-menu-end">' + menu.map(m =>
                '<li><button type="button" class="dropdown-item ' + m[0] + '" data-app="' + appId + '" data-interview="' + esc(iv.id) + '">' + esc(m[1]) + '</button></li>'
            ).join('') + '</ul></div>';
    }

    /*
    | One interview, opened.
    |
    | Two columns, as the design has it: what was observed on the left —
    | who conducted it, and the notes they wrote — and what was concluded on
    | the right. An observation is not a conclusion, and keeping them apart is
    | the point of having both.
    */
    function liveInterview(iv, appId, hideHeld, soleLive) {
        /*
        | Somebody above is already offering this interview's primary action —
        | either the row it sits under or the Next card at the top of the page.
        | Repeating it here is what put two "Mark held" buttons on the Final
        | Interview, one on the row and one loose underneath it.
        */
        let primary = '';
        if (canAttend(iv)) {
            if (iv.attendance === 'scheduled' && !hideHeld) {
                primary = '<button type="button" class="btn btn-sm btn-primary js-iv-held" data-app="' + appId + '" data-interview="' + esc(iv.id) + '">Mark held</button>';
            }
        }

        const menuHtml = soleLive ? '' : interviewMenu(iv, appId);

        /* Only what the row does not already say: where, the link, and
           anything still left to press. */
        const extras = [
            (soleLive ? '' : '<span><b>' + esc(dayWhen(iv.scheduled_at)) + '</b></span>' +
                '<span class="hf-chip iv-att-' + esc(iv.attendance) + '">' + esc(ATTENDANCE_LABEL[iv.attendance]) + '</span>' +
                dueChip(iv)),
            (iv.venue ? '<span class="iv-muted">' + esc(iv.venue) + '</span>' : ''),
            (iv.meeting_link ? '<a href="' + esc(iv.meeting_link) + '" target="_blank" rel="noopener">Meeting link</a>' : ''),
            (primary || menuHtml ? '<span class="iv-primary">' + primary + ' ' + menuHtml + '</span>' : '')
        ].filter(Boolean).join('');

        return (extras ? '<div class="iv-row" style="border-bottom:0">' + extras + '</div>' : '') +
            '<div class="iv-detail">' +
                '<div class="iv-detail-main">' +
                    interviewersBlock(iv, appId) +
                    notesBlock(iv, appId) +
                '</div>' +
                '<div class="iv-detail-side">' +
                    evaluationsBlock(iv, appId) +
                '</div>' +
            '</div>';
    }

    /*
    | Who is conducting it, and taking somebody off it.
    |
    | Names as given when they were assigned, so the record still reads
    | correctly after somebody changes department. Removing one ends their
    | access to THIS interview and nothing else: their notes and evaluations
    | stay where they are, the interview stays, and any other interview they
    | are on is untouched.
    */
    function interviewersBlock(iv, appId) {
        const removable = CAN_DECIDE && (iv.attendance === 'scheduled' || iv.attendance === 'held');

        const chips = iv.interviewers.length
            ? iv.interviewers.map(p =>
                '<span class="iv-person">' + esc(p.name) +
                (removable
                    ? '<button type="button" class="iv-person-off js-iv-unassign" data-app="' + appId +
                      '" data-interview="' + esc(iv.id) + '" data-empno="' + esc(p.empno) + '" data-name="' + esc(p.name) +
                      '" aria-label="Remove ' + esc(p.name) + ' from this interview">&times;</button>'
                    : '') +
                '</span>').join('')
            : '<span class="iv-empty">Nobody assigned yet.</span>';

        const add = (CAN_DECIDE && (iv.attendance === 'scheduled' || iv.attendance === 'held'))
            ? '<button type="button" class="iv-person-add js-iv-assign" data-app="' + appId + '" data-interview="' + esc(iv.id) + '">+ Assign</button>'
            : '';

        return '<div class="iv-block">' +
            '<div class="iv-block-head"><span class="iv-block-label">Interviewers</span></div>' +
            '<div class="iv-people">' + chips + add + '</div></div>';
    }

    /*
    | What was concluded, per interviewer.
    |
    | Scoped so one interviewer's card never reveals whether another has
    | submitted theirs beyond the fact of it — the outcome is shown because HR
    | needs it, the draft state because only its author can still change it.
    */
    function evaluationsBlock(iv, appId) {
        if (iv.attendance !== 'held') {
            return '<div class="iv-block"><div class="iv-block-head">' +
                '<span class="iv-block-label">Evaluations</span></div>' +
                '<div class="iv-empty">Once this interview is marked held, its evaluations are recorded here.</div></div>';
        }

        const mine = iv.evaluations.find(e => e.evaluator_empno === ME);

        /*
        | Who may record one: whoever is on THIS interview.
        |
        | Not "whoever may decide applications" — that was the old test, and it
        | offered the outcome buttons to every HR user looking at the page, then
        | let the server refuse them. The rule the server actually applies is
        | assignment, so that is the rule the page shows. Being HR does not
        | bypass it; being assigned is what counts, whoever you are.
        |
        | This hides nothing that is not also refused server-side by
        | InterviewEvaluationService::assignedNameOrFail().
        */
        const assigned = iv.interviewers.some(p => p.empno === ME);

        let own = '';
        if (assigned && !mine) {
            /* The three outcomes, offered directly. Each opens the full form
               with that outcome already chosen — the reasoning still has to be
               written, it is just one press closer. */
            const pill = (value, label, cls) =>
                '<button type="button" class="iv-outcome-pick ' + cls + ' js-iv-evaluate" data-app="' + appId +
                '" data-interview="' + esc(iv.id) + '" data-outcome="' + value + '">' + label + '</button>';

            own = '<div class="iv-eval">' +
                '<div class="iv-eval-head"><div><b>You</b><div class="iv-muted iv-xs">Not submitted yet</div></div>' +
                '<span class="hf-chip hf-chip-caution">Awaiting</span></div>' +
                '<div class="iv-eval-body">' +
                    '<div class="iv-muted iv-xs mb-1">Outcome</div>' +
                    '<div class="iv-outcomes">' +
                        pill('proceed', 'Proceed', 'is-ok') +
                        pill('hold', 'Hold', 'is-hold') +
                        pill('do_not_proceed', 'Do not proceed', 'is-no') +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2 js-iv-evaluate" data-app="' + appId +
                        '" data-interview="' + esc(iv.id) + '">Record my evaluation</button>' +
                '</div></div>';
        }

        const others = iv.evaluations.map(e => evaluationRow(e, iv, appId)).join('');

        return '<div class="iv-block">' +
            '<div class="iv-block-head"><span class="iv-block-label">Evaluations</span>' +
            (iv.evaluations.length ? '<span class="iv-block-note">' + iv.evaluations.length +
                (iv.evaluations.length > 1 ? ' recorded' : ' recorded') + '</span>' : '') +
            '</div>' + own + (others || (own ? '' : '<div class="iv-empty">None recorded yet.</div>')) +
            '</div>';
    }

    /*
    | What was observed. Separate from the evaluation: an observation is not a
    | conclusion, and adding one later should not need a correction.
    |
    | Loaded when the round opens rather than behind a second disclosure — one
    | fold to reach a note was enough.
    */
    function notesBlock(iv, appId) {
        const count = iv.note_count || 0;

        return '<div class="iv-block">' +
            '<div class="iv-block-head"><span class="iv-block-label">Notes</span>' +
            (count ? '<span class="iv-block-note">' + count + (count > 1 ? ' notes' : ' note') + '</span>' : '') +
            '</div>' +
            '<div class="js-iv-notes" data-app="' + appId + '" data-interview="' + esc(iv.id) + '">' +
            '<div class="iv-empty">Loading…</div></div></div>';
    }

    /*
    | One round as a single line, opening in place.
    |
    | It used to be a bordered card per round, so five rounds ran to roughly 650
    | pixels and only three fitted on screen. The same facts now sit in one row
    | — name, when, state, who, the action — and everything else is behind the
    | disclosure. A completed round stays readable history without taking the
    | space of an outstanding one.
    */
    function roundBlock(round, appId, isCurrent, next) {
        const cls = round.state === 'completed' ? 'is-done'
            : (round.state === 'not_applicable' ? 'is-na' : (isCurrent ? 'is-current' : ''));

        const live = round.interviews.filter(i => i.attendance === 'scheduled' || i.attendance === 'held');
        const past = round.interviews.filter(i => i.attendance === 'cancelled' || i.attendance === 'no_show');

        // Whether the Next card is already offering this round's action.
        const isNextStep = next && String(next.step_id) === String(round.step_id);
        const suppressHeld = !!(isNextStep && next.action === 'held');

        /* The row itself: the facts HR scans down the list, in fixed columns so
           the eye can follow one of them straight down. */
        const scheduled = round.interviews.find(i => i.attendance === 'scheduled');
        const lastHeld = round.interviews.filter(i => i.attendance === 'held').slice(-1)[0];
        const shown = scheduled || lastHeld;

        const whenCell = shown ? dayWhen(shown.scheduled_at)
            : (round.state === 'completed' ? (round.result || 'completed') : '—');

        const whoCell = shown && shown.interviewers.length ? shown.interviewers.map(p => p.name).join(', ')
            : (round.state === 'completed' || round.state === 'not_applicable' ? '' : 'No interviewer yet');

        /*
        | State the round is actually in, not just whether it is settled.
        |
        | Every outstanding round used to read "Outstanding", so the column
        | carried no information — four identical chips down the list. Scheduled,
        | held and not-scheduled are different situations calling for different
        | things, and the chip now says which.
        |
        | Four states, four appearances. The chip carries its own dot, so it is
        | the only marker the row needs.
        */
        let chip, dot;
        if (round.state === 'completed') {
            chip = '<span class="hf-chip hf-chip-ok">Completed</span>';
            dot = 'is-done';
        } else if (round.state === 'not_applicable') {
            chip = '<span class="hf-chip hf-chip-lock">Not applicable</span>';
            dot = '';
        } else if (scheduled) {
            chip = '<span class="hf-chip hf-chip-accent">Scheduled</span>';
            dot = 'is-live';
        } else if (lastHeld) {
            // Amber: it happened, and it is waiting on somebody here.
            chip = '<span class="hf-chip hf-chip-caution">Awaiting outcome</span>';
            dot = 'is-wait';
        } else {
            chip = '<span class="hf-chip hf-chip-lock">Not scheduled</span>';
            dot = '';
        }

        /*
        | The one thing this round most likely needs, on the row.
        |
        | Still never the action the Next card is already showing for this same
        | round — the suppression above decides that, and this follows it.
        */
        let rowAction = '';
        let rowKind = '';
        /* Marking held is open to the interviewer as well; everything else on
           this row stays an HR decision, so each branch asks its own
           question rather than the whole block sharing one. */
        if ((CAN_DECIDE || canAttend(scheduled)) && round.state !== 'completed' && round.state !== 'not_applicable') {
            if (scheduled && !suppressHeld && canAttend(scheduled)) {
                rowKind = 'held';
                rowAction = '<button type="button" class="btn btn-sm btn-primary js-iv-held" data-app="' + appId + '" data-interview="' + esc(scheduled.id) + '">Mark held</button>';
            } else if (CAN_DECIDE && lastHeld && !(isNextStep && next.action === 'complete')) {
                rowKind = 'complete';
                rowAction = '<button type="button" class="btn btn-sm btn-primary js-iv-complete" data-app="' + appId + '" data-step="' + esc(round.step_id) + '" data-label="' + esc(round.display_label) + '">Record outcome</button>';
            } else if (CAN_DECIDE && !scheduled && !lastHeld && !(isNextStep && next.action === 'arrange')) {
                /*
                | Nothing live on this round, so it needs arranging — whether it
                | has never been scheduled at all, or its only attempts were
                | cancelled or not attended. Keying this off "no interviews ever"
                | left a round with a no-show looking like it needed nothing;
                | keying it off nothing at all offered "Schedule" on a round that
                | was already scheduled, because the Mark held above it had been
                | suppressed in favour of the Next card.
                */
                rowKind = 'arrange';
                rowAction = '<button type="button" class="btn btn-sm btn-outline-secondary js-iv-arrange" data-app="' + appId + '" data-step="' + esc(round.step_id) + '">Schedule</button>';
            }
        }

        /*
        | The body is built only once the row is decided, because it has to know
        | which control the row is already showing and leave that one out.
        */
        let body = live.length
            ? live.map(iv => liveInterview(iv, appId, suppressHeld || rowKind === 'held', live.length === 1)).join('<hr class="my-2">')
            : '<div class="iv-empty">No interview arranged yet.</div>';

        // Cancelled and missed attempts are history: kept, but folded away.
        if (past.length) {
            body += '<details class="iv-more"><summary>Earlier attempts (' + past.length + ')</summary><div>' +
                past.map(iv =>
                    '<div class="iv-row"><span>' + esc(dayWhen(iv.scheduled_at)) + '</span>' +
                    '<span class="hf-chip iv-att-' + esc(iv.attendance) + '">' + esc(ATTENDANCE_LABEL[iv.attendance]) + '</span>' +
                    dueChip(iv) +
                    (iv.cancel_reason ? '<span class="iv-muted iv-xs">' + esc(iv.cancel_reason) + '</span>' : '') + '</div>'
                ).join('') + '</div></details>';
        }

        /*
        | Inside the round: only what the row and the Next card are NOT already
        | offering. Putting the primary on the row would otherwise have brought
        | back the duplication this redesign existed to remove — the same button
        | on the row and again a few lines below it.
        */
        let actions = '';
        if (CAN_DECIDE && round.state !== 'completed') {
            const hasScheduled = round.interviews.some(i => i.attendance === 'scheduled');

            if (!hasScheduled && rowKind !== 'arrange' && !(isNextStep && next.action === 'arrange')) {
                actions += '<button type="button" class="btn btn-sm btn-outline-primary js-iv-arrange" data-app="' + appId + '" data-step="' + esc(round.step_id) + '">Arrange interview</button> ';
            }
            if (round.interviews.some(i => i.attendance === 'held')
                && rowKind !== 'complete' && !(isNextStep && next.action === 'complete')) {
                actions += '<button type="button" class="btn btn-sm btn-primary js-iv-complete" data-app="' + appId + '" data-step="' + esc(round.step_id) + '" data-label="' + esc(round.display_label) + '">Record outcome</button> ';
            }
            if (!round.interviews.length && round.stage_key !== 'initial' && round.stage_key !== 'final') {
                actions += '<button type="button" class="btn btn-sm btn-link text-danger p-0 iv-xs js-iv-remove" data-app="' + appId + '" data-step="' + esc(round.step_id) + '">remove this round</button>';
            }
        }

        return '<details class="iv-round ' + cls + '" data-step="' + esc(round.step_id) + '"' + (isCurrent ? ' open' : '') + '>' +
            '<summary>' +
                '<span class="iv-dot ' + dot + '"></span>' +
                '<span class="iv-round-name">' + esc(round.display_label) + '</span>' +
                '<span class="iv-round-when">' + esc(whenCell) + '</span>' +
                chip +
                '<span class="iv-round-who">' + esc(whoCell) + '</span>' +
                '<span class="iv-round-act">' + rowAction +
                    (live.length === 1 ? interviewMenu(live[0], appId) : '') + '</span>' +
            '</summary>' +
            '<div class="iv-round-body">' + body +
            (actions ? '<div class="mt-2">' + actions + '</div>' : '') + '</div></details>';
    }

    /*
    | Exams, in the list and in the rail.
    |
    | Part of the candidate's workflow, so it is shown in sequence — but it is
    | a separate system, which the row says plainly. Read only: nothing here
    | records, scores or alters an assessment.
    */
    function examsBlock(appId) {
        if (!ASSESSMENTS.length) return '';

        const ex = examsProgress();

        // Partial must not look finished — the chip follows the rail's rule.
        const unlocked = examsUnlocked(appId);

        /*
        | "Not started" and "cannot start yet" are different facts, and only
        | one of them is anybody's fault. Before the Initial Interview is
        | completed the assessments are not merely untouched -- they are
        | closed, and AssessmentAccessCodes refuses to issue a code for them.
        */
        const chip = ex.all
            ? '<span class="hf-chip hf-chip-ok">All taken</span>'
            : (ex.partial
                ? '<span class="hf-chip hf-chip-pool">In progress</span>'
                : (unlocked
                    ? '<span class="hf-chip">Not started</span>'
                    : '<span class="hf-chip hf-chip-lock">Not open yet</span>'));

        const dot = ex.all ? 'is-done' : (ex.partial ? 'is-wait' : '');

        // Scoped to the application: a fixed id would repeat across panels.
        return '<details class="iv-round" id="iv-exams-' + esc(appId) + '"><summary>' +
            '<span class="iv-dot ' + dot + '"></span>' +
            '<span class="iv-round-name">Examinations</span>' +
            '<span class="iv-round-when">' +
                esc(unlocked ? ex.label : 'Opens after the Initial Interview') + '</span>' +
            chip +
            '<span class="iv-round-who">assessment system</span>' +
            '<span class="iv-round-act">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary js-iv-exams" data-app="' + esc(appId) + '">Open</button>' +
            '</span>' +
            '</summary>' +
            '<div class="iv-round-body">' +
            ASSESSMENTS.map(a => '<div class="iv-row"><span>' + esc(a.label) + '</span>' +
                '<span class="iv-primary ' + (a.hasResult ? '' : 'iv-muted') + '">' +
                esc(a.hasResult ? (a.takenOn || 'Completed') : 'Not taken') + '</span></div>').join('') +
            (unlocked ? '' :
                '<div class="iv-note-locked">Shown first for convenience, but the examinations only open ' +
                'once the Initial Interview is completed. An access code cannot be issued before then.</div>') +
            '<div class="iv-muted iv-xs mt-2">Results belong to the applicant and are shown for reference. ' +
            'They are recorded in the assessment system, not here.</div></div></details>';
    }

    function render(appId) {
        const host = document.querySelector('.js-iv-body[data-application="' + appId + '"]');
        if (!host) return;

        const data = STATE[appId] || {};
        const rounds = data.rounds || [];

        if (!rounds.length) {
            host.innerHTML = '<div class="iv-empty">This application has no interview rounds yet.</div>';
            return;
        }

        /*
        | What to do, then where they are, then the rounds themselves.
        |
        | The decision comes first because it is why HR opened the page; the
        | rail answers "how far along is this" for whoever is only looking.
        */
        const stages = rounds.length + (ASSESSMENTS.length ? 1 : 0);
        const done = rounds.filter(r => r.state === 'completed').length;
        const position = rounds.findIndex(r => r.step_id === data.currentStepId);

        const summary = done
            ? done + ' of ' + stages + ' completed'
            : 'nothing completed yet';

        const where = position >= 0
            ? 'Round ' + (position + 1) + ' of ' + stages + ' · ' + summary
            : summary;

        let html = nextBlock(appId, data.next) +
            '<div class="iv-section">' +
                '<span class="iv-section-label">Progress</span>' +
                '<span class="iv-section-note">' + esc(where) + '</span>' +
            '</div>' +
            progressRail(appId, rounds);

        html += '<div class="iv-section">' +
            '<span class="iv-section-label">Rounds</span>' +
            (CAN_DECIDE
                ? '<button type="button" class="btn btn-sm btn-outline-secondary js-iv-add iv-section-act" data-app="' + appId + '">+ Add interview</button>'
                : '') +
            '</div>';

        /*
        | Exams sits where the process puts it — after the first round — in the
        | list as well as in the rail, so the two read the same way down the
        | page. It is an assessment, not an interview, which its row says.
        */
        const rows = rounds.map(r => roundBlock(r, appId, r.step_id === data.currentStepId, data.next));
        const exams = examsBlock(appId);
        if (exams) {
            /* First in the list, as asked. See the note in progressRail():
               the order shown and the order run are now deliberately
               different, and the row says which. */
            rows.unshift(exams);
        }

        html += '<div class="iv-rounds">' + rows.join('') + '</div>';

        /*
        | Keep whatever was open, open.
        |
        | Every action re-renders the panel, so assigning somebody or taking
        | them off collapsed the round being worked in and threw the reader
        | back to the top of the list.
        */
        const wasOpen = new Set(
            [...host.querySelectorAll('.iv-round[open]')].map(d => d.dataset.step || d.id)
        );

        host.innerHTML = html;

        if (wasOpen.size) {
            host.querySelectorAll('.iv-round').forEach(d => {
                if (wasOpen.has(d.dataset.step || d.id)) d.open = true;
            });
        }


    }

    /*
    | Showing one application.
    |
    | Only the selected panel is in the page; the others stay hidden with their
    | own state, so no two applications' rounds are ever on screen together and
    | nothing can be carried from one to the other.
    */
    function selectApplication(appId) {
        const chosen = document.querySelector('.js-iv-tab[data-application="' + appId + '"]');

        document.querySelectorAll('.js-iv-tab').forEach(tab => {
            tab.setAttribute('aria-current', String(tab.dataset.application === String(appId)));
        });

        document.querySelectorAll('.js-iv-body').forEach(panel => {
            panel.hidden = panel.dataset.application !== String(appId);
        });

        // The control shows which application is on screen.
        if (chosen) {
            const title = document.querySelector('.js-iv-current-title');
            const status = document.querySelector('.js-iv-current-status');
            if (title) title.textContent = chosen.dataset.title;
            if (status) {
                status.textContent = chosen.dataset.status;
                status.className = 'hf-chip ' + (chosen.dataset.closed ? 'hf-chip-lock' : 'hf-chip-ok') + ' js-iv-current-status';
            }
        }

        /* The others, named rather than counted: "1 other application" alone
           makes you open the menu to find out what it is. */
        const others = [...document.querySelectorAll('.js-iv-tab')]
            .filter(t => t.dataset.application !== String(appId));
        const note = document.querySelector('.js-iv-others');

        if (note) {
            note.textContent = others.length
                ? others.length + ' other application' + (others.length > 1 ? 's' : '') + ' · ' +
                  others.map(t => t.dataset.title + ', ' + t.dataset.status.toLowerCase()).join(' · ')
                : '';
        }
    }

    async function load(appId) {
        try {
            const data = await api(appId, '/data');
            STATE[appId] = {
                rounds: data.rounds || [],
                next: data.next_action || null,
                currentStepId: data.next_action ? data.next_action.step_id : null
            };
            render(appId);
        } catch (e) {
            const host = document.querySelector('.js-iv-body[data-application="' + appId + '"]');
            if (host) host.innerHTML = '<div class="alert alert-danger py-2">' + esc(e.message) + '</div>';
        }
    }

    async function loadNotes(appId, interviewId, host) {
        try {
            const data = await api(appId, '/' + interviewId + '/notes');
            const notes = data.notes || [];

            host.innerHTML = (notes.length
                ? notes.map(n =>
                    '<div class="iv-note"><div class="iv-xs iv-muted mb-1">' +
                    '<b class="iv-note-who">' + esc(n.author_name) + '</b> ' +
                    '<span class="hf-chip ' + (n.author_role === 'interviewer' ? 'hf-chip-accent' : 'hf-chip-lock') + '">' +
                    (n.author_role === 'interviewer' ? 'interviewer' : 'HR') + '</span> · ' +
                    esc(dayWhen(n.created_at)) + (n.edited_at ? ' · edited' : '') +
                    (n.author_empno === ME ? ' <button type="button" class="btn btn-sm btn-link p-0 iv-xs js-iv-note-edit" data-app="' + appId + '" data-note="' + esc(n.id) + '">edit</button>' : '') +
                    '</div><div class="iv-note-body">' + esc(n.body) + '</div></div>'
                ).join('')
                : '<div class="iv-empty">Nothing noted yet.</div>') +
                '<button type="button" class="iv-person-add js-iv-note-add" data-app="' + appId + '" data-interview="' + interviewId + '">+ Add a note</button>';
        } catch (e) {
            host.innerHTML = '<div class="alert alert-danger py-2">' + esc(e.message) + '</div>';
        }
    }

    /* ----------------------------------------------------------- behaviour */

    let scheduleCtx = null, evalCtx = null, assignCtx = null,
        cancelCtx = null, completeCtx = null, noteCtx = null;

    const open = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
    const close = id => { const m = bootstrap.Modal.getInstance(document.getElementById(id)); if (m) m.hide(); };

    // Notes load only when their disclosure is opened: level 3 should not cost
    // a request until somebody asks for it.
    document.addEventListener('toggle', function (e) {
        const round = e.target.closest('.iv-round');
        if (!round || !round.open) return;

        round.querySelectorAll('.js-iv-notes').forEach(host => {
            if (host.dataset.loaded) return;
            host.dataset.loaded = '1';
            loadNotes(host.dataset.app, host.dataset.interview, host);
        });
    }, true);

    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const app = btn.dataset.app;

        /* A button inside a <summary> would otherwise toggle the round as well
           as doing its job: pressing "Mark held" on a row would collapse or
           expand it at the same time. The row's action acts; only the row
           itself opens it. */
        if (btn.closest('summary')) {
            e.preventDefault();
        }

        try {
            if (btn.classList.contains('js-iv-tab')) {
                selectApplication(btn.dataset.application);
                return;
            }

            /* Scoped to this application's panel: another application's rounds
               carry the same step markup and must never be the one found. */
            if (btn.classList.contains('js-iv-focus')) {
                const panel = document.querySelector('.js-iv-body[data-application="' + app + '"]');
                const round = panel && panel.querySelector('.iv-round[data-step="' + btn.dataset.step + '"]');
                if (round) { round.open = true; round.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            }

            if (btn.classList.contains('js-iv-exams')) {
                const ex = document.getElementById('iv-exams-' + app);
                if (ex) { ex.open = true; ex.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            }

            if (btn.classList.contains('js-iv-arrange')) {
                scheduleCtx = { mode: 'create', app: app, stepId: btn.dataset.step };
                document.getElementById('ivScheduleTitle').textContent = 'Arrange interview';
                document.getElementById('ivScheduleWho').textContent = '';
                document.getElementById('ivCopyWrap').hidden = true;
                document.getElementById('ivInterviewerWrap').hidden = false;
                document.getElementById('ivScheduleForm').reset();
                open('ivScheduleModal');
            }

            if (btn.classList.contains('js-iv-reschedule')) {
                scheduleCtx = { mode: 'reschedule', app: app, interviewId: btn.dataset.interview };
                document.getElementById('ivScheduleTitle').textContent = 'Reschedule interview';
                document.getElementById('ivScheduleWho').textContent = 'The interview being replaced is kept, marked cancelled.';
                document.getElementById('ivCopyWrap').hidden = false;
                document.getElementById('ivInterviewerWrap').hidden = true;
                document.getElementById('ivScheduleForm').reset();
                document.getElementById('ivCopyInterviewers').checked = true;
                open('ivScheduleModal');
            }

            if (btn.classList.contains('js-iv-held')) {
                await api(app, '/' + btn.dataset.interview + '/held', 'POST');
                await load(app);
            }

            // The NEXT button names a round, not an interview: find its live one.
            if (btn.classList.contains('js-iv-held-next')) {
                const round = (STATE[app].rounds || []).find(r => String(r.step_id) === String(btn.dataset.step));
                const live = round && round.interviews.find(i => i.attendance === 'scheduled');
                if (live) { await api(app, '/' + live.id + '/held', 'POST'); await load(app); }
            }

            if (btn.classList.contains('js-iv-noshow')) {
                if (!await hfConfirm({
                    title: 'Record a no-show?',
                    message: 'The candidate will be recorded as not having attended this interview.',
                    detail: 'This is kept permanently and cannot be undone. If they simply need a new time, reschedule instead.',
                    confirmLabel: 'Record no-show',
                    tone: 'warn'
                })) return;
                await api(app, '/' + btn.dataset.interview + '/no-show', 'POST');
                await load(app);
            }

            if (btn.classList.contains('js-iv-cancel')) {
                cancelCtx = { app: app, interviewId: btn.dataset.interview };
                document.getElementById('ivCancelForm').reset();
                open('ivCancelModal');
            }

            if (btn.classList.contains('js-iv-unassign')) {
                /*
                | Ends this employee's part in THIS interview.
                |
                | Spelled out because it is not obvious what survives: the
                | assignment goes, their access to this interview with it, and
                | everything they already wrote stays.
                */
                if (!await hfConfirm({
                    title: 'Remove ' + (btn.dataset.name || 'this interviewer') + ' from this interview?',
                    message: 'They lose access to it and cannot file a new evaluation for it.',
                    detail: 'Any notes and evaluations they have already recorded remain on the record, '
                        + 'and any other interview they are assigned to is unaffected.',
                    confirmLabel: 'Remove interviewer',
                    tone: 'warn'
                })) return;

                await api(app, '/' + btn.dataset.interview + '/interviewers/' +
                    encodeURIComponent(btn.dataset.empno), 'DELETE');
                await load(app);
                alertBox((btn.dataset.name || 'That interviewer') + ' is no longer on this interview. ' +
                    'Their earlier notes and evaluations are unchanged.', 'success');
            }

            if (btn.classList.contains('js-iv-assign')) {
                assignCtx = { app: app, interviewId: btn.dataset.interview };
                document.getElementById('ivAssignForm').reset();
                open('ivAssignModal');
            }

            if (btn.classList.contains('js-iv-evaluate')) {
                evalCtx = { app: app, interviewId: btn.dataset.interview, evaluationId: null };
                /* Pressed one of the three outcome buttons: carry that choice
                   into the form so it opens where they left off. */
                const picked = btn.dataset.outcome;
                document.getElementById('ivEvalTitle').textContent = 'Record evaluation';
                document.getElementById('ivEvalWho').textContent = 'Your view of this interview. Other interviewers record their own.';
                document.getElementById('ivEvalCorrectionNotice').hidden = true;
                document.getElementById('ivReasonWrap').hidden = true;
                document.getElementById('ivEvalDraft').hidden = false;
                document.getElementById('ivEvalSubmit').textContent = 'Submit';
                document.getElementById('ivEvalForm').reset();
                if (picked) {
                    const radio = document.querySelector('input[name="ivOutcome"][value="' + picked + '"]');
                    if (radio) radio.checked = true;
                }
                open('ivEvalModal');
            }

            if (btn.classList.contains('js-iv-correct')) {
                evalCtx = { app: app, interviewId: null, evaluationId: btn.dataset.evaluation };
                document.getElementById('ivEvalTitle').textContent = 'Correct your evaluation';
                document.getElementById('ivEvalWho').textContent = 'Your original stays on the record and remains readable.';
                document.getElementById('ivEvalCorrectionNotice').hidden = false;
                document.getElementById('ivReasonWrap').hidden = false;
                document.getElementById('ivEvalDraft').hidden = true;
                document.getElementById('ivEvalSubmit').textContent = 'Submit correction';
                document.getElementById('ivEvalForm').reset();
                open('ivEvalModal');
            }

            if (btn.classList.contains('js-iv-history')) {
                const data = await api(app, '/' + btn.dataset.interview + '/evaluations/' +
                    encodeURIComponent(btn.dataset.empno) + '/history');
                document.getElementById('ivHistoryBody').innerHTML = (data.history || []).map(h =>
                    '<div class="iv-note"><div><b>Revision ' + esc(h.revision) + '</b> ' +
                    (h.outcome ? '<span class="hf-chip iv-outcome-' + esc(h.outcome) + '">' + esc(OUTCOME_LABEL[h.outcome]) + '</span>' : '') +
                    (h.superseded_at ? ' <span class="iv-muted iv-xs">superseded</span>' : ' <span class="iv-muted iv-xs">current</span>') + '</div>' +
                    (h.correction_reason ? '<div class="iv-muted iv-xs">Reason for correcting: ' + esc(h.correction_reason) + '</div>' : '') +
                    (h.recommendation ? '<div class="iv-note-body">' + esc(h.recommendation) + '</div>' : '') +
                    (h.remarks ? '<div class="iv-note-body">' + esc(h.remarks) + '</div>' : '') + '</div>'
                ).join('') || '<div class="iv-empty">No earlier versions.</div>';
                open('ivHistoryModal');
            }

            if (btn.classList.contains('js-iv-complete')) {
                completeCtx = { app: app, stepId: btn.dataset.step };
                document.getElementById('ivCompleteTitle').textContent = 'Record outcome — ' + (btn.dataset.label || 'round');
                document.getElementById('ivCompleteForm').reset();
                open('ivCompleteModal');
            }

            if (btn.classList.contains('js-iv-remove')) {
                if (!await hfConfirm({
                    title: 'Remove this round?',
                    message: 'It is taken out of this application\'s sequence.',
                    detail: 'Only possible while nothing has happened on it. A round with an interview or an evaluation on it cannot be removed.',
                    confirmLabel: 'Remove round',
                    tone: 'warn'
                })) return;
                await api(app, '/steps/' + btn.dataset.step, 'DELETE');
                await load(app);
            }

            if (btn.classList.contains('js-iv-add')) {
                const created = await api(app, '/steps', 'POST', {});
                await load(app);

                /*
                | Name it the way the page names it.
                |
                | The stored label is fixed at creation; what every other part
                | of this screen shows is display_label, worked out from the
                | round's position so the sequence still reads in order after a
                | round is removed. Reporting the stored one meant the message
                | could say "Added 4th Interview" while the stepper beside it
                | showed a 3rd — the same round, two different names.
                */
                const round = (STATE[app].rounds || []).find(r => r.step_id === (created.step || {}).id);
                alertBox('Added ' + (round ? round.display_label : 'an interview round') + '.', 'success');
            }

            if (btn.classList.contains('js-iv-advance')) {
                if (!await hfConfirm({
                    title: 'Move to the next step?',
                    message: 'The application moves to its next outstanding step.',
                    detail: 'Its stage history records the move. Nothing about the current round is changed.',
                    confirmLabel: 'Move on'
                })) return;
                const res = await api(app, '/advance', 'POST', {});
                await load(app);
                alertBox('Moved to ' + (res.moved_to ? res.moved_to.label : 'the next step') + '.', 'success');
            }

            if (btn.classList.contains('js-iv-note-add')) {
                noteCtx = { app: app, interviewId: btn.dataset.interview, noteId: null };
                document.getElementById('ivNoteTitle').textContent = 'Add a note';
                document.getElementById('ivNoteBody').value = '';
                open('ivNoteModal');
            }

            if (btn.classList.contains('js-iv-note-edit')) {
                const body = btn.closest('.iv-note').querySelector('.iv-note-body').textContent;
                noteCtx = { app: app, interviewId: null, noteId: btn.dataset.note };
                document.getElementById('ivNoteTitle').textContent = 'Edit your note';
                document.getElementById('ivNoteBody').value = body;
                open('ivNoteModal');
            }
        } catch (err) {
            alertBox(err.message);
        }
    });

    document.getElementById('ivScheduleForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            scheduled_at: document.getElementById('ivScheduleAt').value.replace('T', ' '),
            duration_minutes: document.getElementById('ivDuration').value || null,
            venue: document.getElementById('ivVenue').value || null,
            meeting_link: document.getElementById('ivLink').value || null
        };

        try {
            if (scheduleCtx.mode === 'create') {
                const created = await api(scheduleCtx.app, '/steps/' + scheduleCtx.stepId, 'POST', payload);
                const who = document.getElementById('ivInterviewer').value;
                if (who && created.interview) {
                    await api(scheduleCtx.app, '/' + created.interview.id + '/interviewers', 'POST', { empno: who });
                }
            } else {
                payload.copy_interviewers = document.getElementById('ivCopyInterviewers').checked;
                await api(scheduleCtx.app, '/' + scheduleCtx.interviewId + '/reschedule', 'POST', payload);
            }
            close('ivScheduleModal');
            await load(scheduleCtx.app);
        } catch (err) { alertBox(err.message); }
    });

    document.getElementById('ivAssignForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        try {
            await api(assignCtx.app, '/' + assignCtx.interviewId + '/interviewers', 'POST',
                { empno: document.getElementById('ivAssignWho').value });
            close('ivAssignModal');
            await load(assignCtx.app);
        } catch (err) { alertBox(err.message); }
    });

    document.getElementById('ivCancelForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        try {
            await api(cancelCtx.app, '/' + cancelCtx.interviewId + '/cancel', 'POST',
                { reason: document.getElementById('ivCancelReason').value });
            close('ivCancelModal');
            await load(cancelCtx.app);
        } catch (err) { alertBox(err.message); }
    });

    document.getElementById('ivCompleteForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        try {
            await api(completeCtx.app, '/steps/' + completeCtx.stepId + '/complete', 'POST', {
                result: document.getElementById('ivCompleteResult').value || null,
                notes: document.getElementById('ivCompleteNotes').value || null
            });
            close('ivCompleteModal');
            await load(completeCtx.app);
            alertBox('Outcome recorded. The application has not moved yet.', 'success');
        } catch (err) { alertBox(err.message); }
    });

    document.getElementById('ivNoteForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const body = document.getElementById('ivNoteBody').value;
        try {
            if (noteCtx.noteId) {
                await api(noteCtx.app, '/notes/' + noteCtx.noteId, 'PATCH', { body: body });
            } else {
                await api(noteCtx.app, '/' + noteCtx.interviewId + '/notes', 'POST', { body: body });
            }
            close('ivNoteModal');
            await load(noteCtx.app);
        } catch (err) { alertBox(err.message); }
    });

    async function sendEvaluation(submit) {
        const outcome = (document.querySelector('input[name="ivOutcome"]:checked') || {}).value || null;
        const payload = {
            outcome: outcome,
            recommendation: document.getElementById('ivRecommendation').value || null,
            remarks: document.getElementById('ivRemarks').value || null
        };

        try {
            if (evalCtx.evaluationId) {
                payload.correction_reason = document.getElementById('ivCorrectionReason').value;
                if (!payload.correction_reason.trim()) { alertBox('Please say why you are correcting this evaluation.'); return; }
                if (!await hfConfirm({
                    title: 'Submit this correction?',
                    message: 'It replaces your previous evaluation as the one in force.',
                    detail: 'Your original stays on the record and remains readable. A submitted correction cannot be edited.',
                    confirmLabel: 'Submit correction',
                    tone: 'warn'
                })) return;
                await api(evalCtx.app, '/evaluations/' + evalCtx.evaluationId + '/correct', 'POST', payload);
            } else if (submit) {
                if (!await hfConfirm({
                    title: 'Submit this evaluation?',
                    message: 'It becomes part of the applicant\'s record.',
                    detail: 'It cannot be edited afterwards — you would have to file a correction, which is kept alongside it.',
                    confirmLabel: 'Submit evaluation',
                    tone: 'warn'
                })) return;
                await api(evalCtx.app, '/' + evalCtx.interviewId + '/evaluations', 'POST', payload);
            } else {
                await api(evalCtx.app, '/' + evalCtx.interviewId + '/evaluations/draft', 'POST', payload);
            }
            close('ivEvalModal');
            await load(evalCtx.app);
        } catch (err) { alertBox(err.message); }
    }

    document.getElementById('ivEvalForm').addEventListener('submit', function (e) { e.preventDefault(); sendEvaluation(true); });
    document.getElementById('ivEvalDraft').addEventListener('click', function () { sendEvaluation(false); });

    document.querySelectorAll('.js-iv-body').forEach(el => load(el.dataset.application));

    /* Name the application on screen, and the ones behind it, from the start. */
    const firstPanel = document.querySelector('.js-iv-body');
    if (firstPanel) selectApplication(firstPanel.dataset.application);
})();
</script>
@endsection
