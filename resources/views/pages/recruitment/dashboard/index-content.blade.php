{{--
    HireFlow dashboard — the landing page for the module.

    Two halves: what needs attention now (server-rendered tiles, so the page is
    still useful if the chart library never loads), and where the work is
    sitting (charts). Every tile and every chart segment opens a page that
    already exists; nothing here is decorative, and nothing here writes.
--}}
<style>
    #hf-dash { font-size: var(--zn-fs); }

    #hf-dash .hf-dash-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    #hf-dash .hf-dash-head h5 {
        font-weight: 800;
        color: var(--zn-ink);
        letter-spacing: -.2px;
        margin: 0;
    }

    #hf-dash .hf-dash-sub {
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink-3);
    }

    /* ===== Attention tiles ===== */
    #hf-dash .hf-tiles {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    #hf-dash .hf-tile {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        box-shadow: 0 1px 3px rgba(32, 26, 22, .04);
        text-decoration: none;
        color: inherit;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    #hf-dash .hf-tile:hover {
        border-color: var(--zn-accent);
        box-shadow: 0 4px 12px rgba(32, 26, 22, .08);
        transform: translateY(-1px);
        text-decoration: none;
        color: inherit;
    }

    #hf-dash .hf-tile-ico {
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        color: var(--zn-ink-3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    /* a tile only looks urgent when it has something in it */
    #hf-dash .hf-tile.is-open .hf-tile-ico {
        background: var(--zn-accent-soft);
        color: var(--zn-accent-dark);
    }

    #hf-dash .hf-tile-value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        color: var(--zn-ink);
    }

    #hf-dash .hf-tile.is-zero .hf-tile-value { color: var(--zn-ink-3); }

    /*
    | Zero-value tiles.
    |
    | A dashboard of mostly noughts reads as noise and buries the few figures
    | that want doing something about. They are hidden by default and brought
    | back by the control in the header -- nothing is removed, and the toggle
    | says how many are hidden so their absence is never a surprise.
    |
    | Hiding is driven by a class this page's own script adds, not by the
    | stylesheet alone: if the script does not run, every tile shows, which is
    | how the dashboard behaved before. A toggle that cannot be reached must
    | never be able to hide anything.
    */
    #hf-dash.hf-zeros-hidden .hf-tile.is-zero { display: none; }
    #hf-dash.hf-zeros-hidden .hf-tiles:empty,
    #hf-dash.hf-zeros-hidden .hf-group-label.is-empty { display: none; }

    #hf-dash .hf-zero-toggle {
        display: none;                 /* shown by the script, once it knows the count */
        align-items: center;
        gap: 6px;
        margin-left: 10px;
        padding: 3px 11px;
        border: 1px solid var(--zn-line);
        border-radius: 20px;
        background: var(--zn-surface);
        color: var(--zn-ink-2);
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    #hf-dash .hf-zero-toggle:hover {
        background: var(--zn-surface-2);
        border-color: var(--zn-ink-3);
        color: var(--zn-ink);
    }
    #hf-dash .hf-zero-toggle:focus-visible {
        outline: 2px solid var(--zn-accent);
        outline-offset: 2px;
    }
    #hf-dash .hf-zero-toggle.is-shown {
        background: var(--zn-accent-soft);
        border-color: var(--zn-accent);
        color: var(--zn-accent-dark);
    }

    /* An all-clear line where a whole group is hidden, so the section does not
       simply vanish and leave HR wondering whether it failed to load. */
    #hf-dash .hf-group-allclear {
        display: none;
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-sm);
        padding: 2px 0 10px;
    }
    #hf-dash.hf-zeros-hidden .hf-group-allclear.is-on { display: block; }

    #hf-dash .hf-tile-label {
        font-size: var(--zn-fs-ui);
        font-weight: 700;
        color: var(--zn-ink-2);
    }

    #hf-dash .hf-tile-hint {
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-3);
        margin-top: 1px;
    }

    #hf-dash .hf-dash-asof {
        display: inline-block;
        margin-left: 10px;
        padding: 2px 9px;
        border-radius: 20px;
        background: var(--zn-surface-2);
        font-size: var(--zn-fs-xs);
        white-space: nowrap;
    }

    #hf-dash .hf-group-label {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--zn-ink-3);
        margin-bottom: 8px;
    }

    #hf-dash .hf-tiles-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    #hf-dash .hf-tiles-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    /* ===== Hiring progress ===== */
    #hf-dash .hf-hiring {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 14px 16px;
        margin-bottom: 22px;
    }

    #hf-dash .hf-hiring-head {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink-2);
        margin-bottom: 8px;
    }

    #hf-dash .hf-hiring-bar {
        height: 10px;
        border-radius: 20px;
        background: var(--hf-inactive-soft);
        overflow: hidden;
    }

    #hf-dash .hf-hiring-bar span {
        display: block;
        height: 100%;
        background: var(--hf-done);
    }

    /* progress, not a to-do: same shape, quieter */
    #hf-dash .hf-tile-quiet { background: var(--zn-surface-2); }
    #hf-dash .hf-tile-quiet .hf-tile-ico { background: var(--zn-surface); }

    /* Applicant Intake: navigation only — no figures are read for it */
    #hf-dash .hf-jumpcard {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        margin-bottom: 22px;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        color: inherit;
        text-decoration: none;
        font-size: var(--zn-fs-ui);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #hf-dash .hf-jumpcard:hover {
        border-color: var(--zn-accent);
        box-shadow: 0 4px 12px rgba(32, 26, 22, .08);
        color: inherit;
        text-decoration: none;
    }

    #hf-dash .hf-jumpcard span.d-block {
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-3);
    }

    #hf-dash .hf-jumpcard-ico {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface-2);
        color: var(--zn-ink-3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #hf-dash .hf-jumpcard-go {
        margin-left: auto;
        color: var(--zn-accent);
        font-size: 18px;
    }

    /*
       One colour, one meaning — the palette previously reused the same green
       for Approved, Served and Posted, and one grey for Draft and Cancelled.
       The value and label carry the meaning in words regardless.
    */
    #hf-dash .hf-tile.tone-overdue.is-open .hf-tile-ico { background: var(--hf-overdue-soft); color: var(--hf-overdue); }
    #hf-dash .hf-tile.tone-overdue.is-open { border-color: var(--hf-overdue); }
    #hf-dash .hf-tile.tone-attention.is-open .hf-tile-ico { background: var(--hf-attention-soft); color: var(--hf-attention-ink); }
    #hf-dash .hf-tile.tone-progress.is-open .hf-tile-ico { background: var(--hf-progress-soft); color: var(--hf-progress); }
    #hf-dash .hf-tile.tone-done.is-open .hf-tile-ico { background: var(--hf-done-soft); color: var(--hf-done); }
    #hf-dash .hf-tile.is-zero .hf-tile-ico { background: var(--hf-inactive-soft); color: var(--hf-inactive); }

    /* ===== Timing strip ===== */
    #hf-dash .hf-timings {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    #hf-dash .hf-timing {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 12px 16px;
    }

    #hf-dash .hf-timing-value {
        display: block;
        font-size: 20px;
        font-weight: 800;
        color: var(--zn-ink);
        line-height: 1.2;
    }

    #hf-dash .hf-timing-unit {
        font-size: var(--zn-fs-sm);
        font-weight: 600;
        color: var(--zn-ink-3);
        margin-left: 4px;
    }

    #hf-dash .hf-timing-label {
        display: block;
        font-size: var(--zn-fs-ui);
        color: var(--zn-ink-2);
        margin-top: 2px;
    }

    #hf-dash .hf-timing-none {
        display: block;
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-3);
    }

    /* ===== Chart cards ===== */
    #hf-dash .hf-charts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    #hf-dash .hf-card {
        display: flex;
        flex-direction: column;
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 16px 18px 12px;
        min-width: 0;
    }

    #hf-dash .hf-card-wide { grid-column: span 2; }

    #hf-dash .hf-card-title {
        font-size: var(--zn-fs-ui);
        font-weight: 700;
        color: var(--zn-ink);
        margin-bottom: 2px;
    }

    #hf-dash .hf-card-hint {
        font-size: var(--zn-fs-xs);
        color: var(--zn-ink-3);
        margin-bottom: 10px;
    }

    #hf-dash .hf-canvas-wrap {
        position: relative;
        flex: 1 1 auto;
        height: 240px;
    }

    #hf-dash .hf-card-foot {
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid var(--zn-surface-2);
        font-size: var(--zn-fs-sm);
    }

    #hf-dash .hf-card-foot a { font-weight: 600; }

    #hf-dash .hf-empty {
        display: flex;
        height: 100%;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--zn-ink-3);
        font-size: var(--zn-fs-ui);
        border: 1px dashed var(--zn-line);
        border-radius: var(--zn-radius-lg);
    }

    @media (max-width: 1200px) {
        #hf-dash .hf-tiles,
        #hf-dash .hf-tiles-3,
        #hf-dash .hf-tiles-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 991.98px) {
        #hf-dash .hf-timings { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        #hf-dash .hf-tiles,
        #hf-dash .hf-tiles-3,
        #hf-dash .hf-tiles-4 { grid-template-columns: 1fr; }
        #hf-dash .hf-dash-asof { margin-left: 0; }
        #hf-dash .hf-charts { grid-template-columns: 1fr; }
        #hf-dash .hf-card-wide { grid-column: span 1; }
    }
</style>

<div class="container-fluid" id="hf-dash">
    <div class="hf-dash-head">
        <h5>HireFlow Dashboard</h5>
        <span class="hf-dash-sub">
            Everything below opens the records behind it.
            <span class="hf-dash-asof" title="{{ $generatedAt->format('M d, Y h:i:s A') }}">
                Figures as of {{ $generatedAt->format('M d, Y h:i A') }}
            </span>
            {{-- Written by the script once it has counted them. --}}
            <button type="button" class="hf-zero-toggle" id="hf-zero-toggle"
                    aria-pressed="false" hidden></button>
        </span>
    </div>

    {{-- Rendered server-side: the actionable numbers survive JavaScript or
         the chart CDN failing. --}}
    <div class="hf-group-label">Needs attention</div>
    <div class="hf-tiles">
        @foreach ($actionTiles as $tile)
            <a class="hf-tile tone-{{ $tile['tone'] ?? 'attention' }} {{ $tile['value'] > 0 ? 'is-open' : 'is-zero' }}"
                href="{{ $tile['url'] }}">
                <span class="hf-tile-ico"><i class="fa {{ $tile['icon'] }}" aria-hidden="true"></i></span>
                <span>
                    <span class="hf-tile-value">{{ $tile['value'] }}</span>
                    <span class="hf-tile-label d-block">{{ $tile['label'] }}</span>
                    <span class="hf-tile-hint d-block">{{ $tile['hint'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="hf-group-label">Approved work</div>
    <div class="hf-tiles hf-tiles-3">
        @foreach ($progressTiles as $tile)
            <a class="hf-tile hf-tile-quiet tone-{{ $tile['tone'] ?? 'progress' }} is-open" href="{{ $tile['url'] }}">
                <span class="hf-tile-ico"><i class="fa {{ $tile['icon'] }}" aria-hidden="true"></i></span>
                <span>
                    <span class="hf-tile-value">{{ $tile['value'] }}</span>
                    <span class="hf-tile-label d-block">{{ $tile['label'] }}</span>
                    <span class="hf-tile-hint d-block">{{ $tile['hint'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Trends, not queues. A median with nothing behind it prints a dash
         rather than a misleading zero. --}}
    <div class="hf-timings">
        @foreach ($timings as $t)
            <div class="hf-timing">
                <span class="hf-timing-value">
                    {{ $t['value'] === null ? '—' : $t['value'] }}
                    @if ($t['value'] !== null)<span class="hf-timing-unit">{{ $t['unit'] }}</span>@endif
                </span>
                <span class="hf-timing-label">{{ $t['label'] }}</span>
                @if ($t['value'] === null)
                    <span class="hf-timing-none">not enough data yet</span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Steps 3 onward. Read over the existing applicant connection; nothing
         is copied into portal_db. --}}
    <div class="hf-group-label">Applicants &amp; Candidate Pool</div>
    <div class="hf-tiles hf-tiles-4">
        @foreach ($intakeTiles as $tile)
            <a class="hf-tile tone-{{ $tile['tone'] }} {{ $tile['value'] > 0 ? 'is-open' : 'is-zero' }}"
                href="{{ $tile['url'] }}">
                <span class="hf-tile-ico"><i class="fa {{ $tile['icon'] }}" aria-hidden="true"></i></span>
                <span>
                    <span class="hf-tile-value">{{ $tile['value'] }}</span>
                    <span class="hf-tile-label d-block">{{ $tile['label'] }}</span>
                    <span class="hf-tile-hint d-block">{{ $tile['hint'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Hiring progress, stated in words as well as a bar. --}}
    <div class="hf-hiring">
        <div class="hf-hiring-head">
            <strong>Hiring progress</strong>
            <span>{{ $hiring['filled'] }} filled of {{ $hiring['filled'] + $hiring['open'] }} approved headcount</span>
        </div>
        @php $total = max(1, $hiring['filled'] + $hiring['open']); @endphp
        <div class="hf-hiring-bar" role="img"
            aria-label="{{ $hiring['filled'] }} of {{ $hiring['filled'] + $hiring['open'] }} headcount filled">
            <span style="width: {{ round($hiring['filled'] / $total * 100) }}%"></span>
        </div>
    </div>

    <div class="hf-charts">
        <div class="hf-card">
            <div class="hf-card-title">Manpower requests by status</div>
            <div class="hf-card-hint">Click a slice to open that tab.</div>
            <div class="hf-canvas-wrap"><canvas id="hf-chart-status"></canvas></div>
            <div class="hf-card-foot"><a href="{{ url('/recruitment/manpower') }}">Open Manpower Requests</a></div>
        </div>

        <div class="hf-card">
            <div class="hf-card-title">Job postings by status</div>
            <div class="hf-card-hint">Click a bar to open Job Postings.</div>
            <div class="hf-canvas-wrap"><canvas id="hf-chart-postings"></canvas></div>
            <div class="hf-card-foot"><a href="{{ url('/recruitment/job-postings') }}">Open Job Postings</a></div>
        </div>

        <div class="hf-card hf-card-wide">
            <div class="hf-card-title">Approved headcount by department</div>
            <div class="hf-card-hint">Filled against still open, across all approved requests including served ones.</div>
            <div class="hf-canvas-wrap"><canvas id="hf-chart-headcount"></canvas></div>
            <div class="hf-card-foot"><a href="{{ url('/recruitment/manpower') }}?stat=approved">Open approved requests</a></div>
        </div>

        <div class="hf-card">
            <div class="hf-card-title">Requests raised, last 6 months</div>
            <div class="hf-card-hint">Click a month to list the requests raised in it.</div>
            <div class="hf-canvas-wrap"><canvas id="hf-chart-months"></canvas></div>
            <div class="hf-card-foot"><a href="{{ url('/recruitment/manpower') }}">Open Manpower Requests</a></div>
        </div>

        <div class="hf-card">
            <div class="hf-card-title">Approved positions by type</div>
            <div class="hf-card-hint">Replacement against additional headcount.</div>
            <div class="hf-canvas-wrap"><canvas id="hf-chart-types"></canvas></div>
            <div class="hf-card-foot"><a href="{{ url('/recruitment/manpower') }}?stat=approved">Open approved requests</a></div>
        </div>
    </div>
</div>

{{--
    Zero-value tiles.

    Runs before the charts and independently of them, because the tiles are
    server-rendered and must not depend on a CDN. The class that does the
    hiding is added here rather than sitting in the stylesheet, so a page
    where this does not run shows every tile exactly as it always did.
--}}
<script type="text/javascript">
(function () {
    var dash = document.getElementById('hf-dash');
    var toggle = document.getElementById('hf-zero-toggle');
    if (!dash || !toggle) { return; }

    var zeros = dash.querySelectorAll('.hf-tile.is-zero');
    if (!zeros.length) { return; }   // nothing to hide; leave the control away

    var KEY = 'hf-dash-show-zeros';

    function stored() {
        try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; }
    }
    function remember(on) {
        try { localStorage.setItem(KEY, on ? '1' : '0'); } catch (e) { /* private window */ }
    }

    /* A group whose tiles are now all hidden gets a line saying so, rather
       than collapsing to a bare heading with nothing under it. */
    var groups = [];
    dash.querySelectorAll('.hf-tiles').forEach(function (row) {
        var tiles = row.querySelectorAll('.hf-tile');
        if (!tiles.length) { return; }
        var allZero = row.querySelectorAll('.hf-tile.is-zero').length === tiles.length;
        if (!allZero) { return; }

        var label = row.previousElementSibling;
        var note = document.createElement('div');
        note.className = 'hf-group-allclear';
        note.textContent = (label && label.classList.contains('hf-group-label'))
            ? 'Nothing under ' + label.textContent.trim().toLowerCase() + ' right now.'
            : 'Nothing here right now.';
        row.parentNode.insertBefore(note, row.nextSibling);

        groups.push({ row: row, note: note });
    });

    function apply(show) {
        dash.classList.toggle('hf-zeros-hidden', !show);
        toggle.classList.toggle('is-shown', show);
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        toggle.textContent = show
            ? 'Hide ' + zeros.length + ' zero ' + (zeros.length === 1 ? 'figure' : 'figures')
            : 'Show ' + zeros.length + ' zero ' + (zeros.length === 1 ? 'figure' : 'figures');

        groups.forEach(function (g) {
            g.row.style.display = show ? '' : 'none';
            g.note.classList.toggle('is-on', !show);
        });
    }

    toggle.hidden = false;
    toggle.style.display = 'inline-flex';
    toggle.addEventListener('click', function () {
        /* Hidden now means the next state is shown. */
        var next = dash.classList.contains('hf-zeros-hidden');
        remember(next);
        apply(next);
    });

    apply(stored());
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    (function () {
        const DATA = @json($charts);
        const MANPOWER = @json(url('/recruitment/manpower'));
        const POSTINGS = @json(url('/recruitment/job-postings'));

        if (typeof Chart === 'undefined') {
            // The tiles above still carry the actionable numbers.
            document.querySelectorAll('#hf-dash .hf-canvas-wrap').forEach(function (wrap) {
                wrap.innerHTML = '<div class="hf-empty">Charts could not be loaded.</div>';
            });
            return;
        }

        // Pull the palette from the HireFlow theme so the charts follow it,
        // including in dark mode.
        const css = getComputedStyle(document.documentElement);
        function token(name, fallback) {
            const v = css.getPropertyValue(name).trim();
            return v === '' ? fallback : v;
        }

        const INK = token('--zn-ink-2', '#4a423c');
        const INK_3 = token('--zn-ink-3', '#8a7f77');
        const LINE = token('--zn-line', '#e5e0db');
        const ACCENT = token('--zn-accent', '#7b4a34');
        const ACCENT_DARK = token('--zn-accent-dark', '#5d2502');
        const OK = token('--zn-ok', '#1e7b45');
        const WARN = token('--zn-warn', '#c62828');
        const SURFACE_2 = token('--zn-surface-2', '#f2ede8');

        Chart.defaults.font.family = css.getPropertyValue('font-family') || 'inherit';
        Chart.defaults.color = INK;

        function go(url) { window.location.href = url; }

        /** Replaces a chart with a note when there is genuinely nothing yet. */
        function emptyIfBlank(canvasId, values, message) {
            if (values.some(function (v) { return v > 0; })) { return false; }
            const canvas = document.getElementById(canvasId);
            if (canvas) { canvas.parentElement.innerHTML = '<div class="hf-empty">' + message + '</div>'; }
            return true;
        }

        /** Click handler shared by every chart: index in => URL out. */
        function clickTo(urlFor) {
            return function (event, elements, chart) {
                const hit = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true);
                if (!hit.length) { return; }
                const url = urlFor(hit[0].index, hit[0].datasetIndex);
                if (url) { go(url); }
            };
        }

        function pointer(event, elements) {
            event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
        }

        // ---------------------------------------------------- requests by status
        // Slugs, labels and colours follow the Manpower tabs exactly, so a
        // slice always opens a tab holding that many rows. "Returned" is
        // labelled "Update" here for the same reason.
        const statusSlugs = ['draft', 'pending', 'approved', 'served', 'update', 'cancelled', 'declined'];
        const statusLabels = ['Draft', 'Pending', 'Approved', 'Served', 'Update', 'Cancelled', 'Declined'];
        const statusValues = statusSlugs.map(function (s) { return DATA.status[s] || 0; });

        if (!emptyIfBlank('hf-chart-status', statusValues, 'No manpower requests yet.')) {
            new Chart(document.getElementById('hf-chart-status'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: [SURFACE_2, ACCENT, token('--zn-ok-soft', '#e8f3ec'), OK, ACCENT_DARK, INK_3, WARN],
                        borderColor: token('--zn-surface', '#fff'),
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12 } } },
                    onHover: pointer,
                    onClick: clickTo(function (i) { return MANPOWER + '?stat=' + statusSlugs[i]; })
                }
            });
        }

        // ----------------------------------------------------- postings by status
        const postingLabels = ['Draft', 'Published', 'Closed'];
        const postingValues = postingLabels.map(function (s) { return DATA.postings[s] || 0; });

        if (!emptyIfBlank('hf-chart-postings', postingValues, 'No job postings yet.')) {
            new Chart(document.getElementById('hf-chart-postings'), {
                type: 'bar',
                data: {
                    labels: postingLabels,
                    datasets: [{
                        label: 'Postings',
                        data: postingValues,
                        backgroundColor: [SURFACE_2, OK, INK_3],
                        borderRadius: 6,
                        maxBarThickness: 64
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: INK } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: INK_3 }, grid: { color: LINE } }
                    },
                    onHover: pointer,
                    onClick: clickTo(function () { return POSTINGS; })
                }
            });
        }

        // ------------------------------------------------ headcount by department
        const head = DATA.headcount;
        const headTotals = (head.filled || []).concat(head.open || []);

        if (head.labels.length === 0 || !headTotals.some(function (v) { return v > 0; })) {
            emptyIfBlank('hf-chart-headcount', [0], 'No approved headcount yet.');
        } else {
            new Chart(document.getElementById('hf-chart-headcount'), {
                type: 'bar',
                data: {
                    labels: head.labels,
                    datasets: [
                        { label: 'Filled', data: head.filled, backgroundColor: OK, borderRadius: 4, maxBarThickness: 48 },
                        { label: 'Still open', data: head.open, backgroundColor: ACCENT, borderRadius: 4, maxBarThickness: 48 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: INK } },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0, color: INK_3 }, grid: { color: LINE } }
                    },
                    onHover: pointer,
                    onClick: clickTo(function () { return MANPOWER + '?stat=approved'; })
                }
            });
        }

        // --------------------------------------------------- requests over time
        const months = DATA.months;

        if (!emptyIfBlank('hf-chart-months', months.data, 'No requests raised in the last six months.')) {
            new Chart(document.getElementById('hf-chart-months'), {
                type: 'line',
                data: {
                    labels: months.labels,
                    datasets: [{
                        label: 'Requests raised',
                        data: months.data,
                        borderColor: ACCENT,
                        backgroundColor: token('--zn-accent-soft', '#f8f3ef'),
                        pointBackgroundColor: ACCENT,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: .3,
                        fill: true
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: INK } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: INK_3 }, grid: { color: LINE } }
                    },
                    onHover: pointer,
                    onClick: clickTo(function (i) {
                        // A month with nothing in it would open an empty list.
                        if (!months.data[i]) { return null; }
                        return MANPOWER + '?month=' + encodeURIComponent(months.keys[i]);
                    })
                }
            });
        }

        // ------------------------------------------------------ positions by type
        const typeValues = [DATA.types.replacement || 0, DATA.types.additional || 0];

        if (!emptyIfBlank('hf-chart-types', typeValues, 'No approved positions yet.')) {
            new Chart(document.getElementById('hf-chart-types'), {
                type: 'doughnut',
                data: {
                    labels: ['Replacement', 'Additional'],
                    datasets: [{
                        data: typeValues,
                        backgroundColor: [ACCENT, OK],
                        borderColor: token('--zn-surface', '#fff'),
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12 } } },
                    onHover: pointer,
                    onClick: clickTo(function () { return MANPOWER + '?stat=approved'; })
                }
            });
        }
    })();
</script>
