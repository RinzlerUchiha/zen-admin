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
        #hf-dash .hf-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
        #hf-dash .hf-tiles { grid-template-columns: 1fr; }
        #hf-dash .hf-charts { grid-template-columns: 1fr; }
        #hf-dash .hf-card-wide { grid-column: span 1; }
    }
</style>

<div class="container-fluid" id="hf-dash">
    <div class="hf-dash-head">
        <h5>HireFlow Dashboard</h5>
        <span class="hf-dash-sub">Everything below opens the records behind it.</span>
    </div>

    {{-- Needs attention. Rendered server-side: no JavaScript required. --}}
    <div class="hf-tiles">
        @foreach ($tiles as $tile)
            <a class="hf-tile {{ $tile['value'] > 0 ? 'is-open' : 'is-zero' }}" href="{{ $tile['url'] }}">
                <span class="hf-tile-ico"><i class="fa {{ $tile['icon'] }}" aria-hidden="true"></i></span>
                <span>
                    <span class="hf-tile-value">{{ $tile['value'] }}</span>
                    <span class="hf-tile-label d-block">{{ $tile['label'] }}</span>
                    <span class="hf-tile-hint d-block">{{ $tile['hint'] }}</span>
                </span>
            </a>
        @endforeach
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
            <div class="hf-card-hint">Filled against still open. Click a bar to open the approved requests.</div>
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
        const statusSlugs = ['draft', 'pending', 'approved', 'update', 'cancelled', 'declined'];
        const statusLabels = ['Draft', 'Pending', 'Approved', 'Returned', 'Cancelled', 'Declined'];
        const statusValues = statusSlugs.map(function (s) { return DATA.status[s] || 0; });

        if (!emptyIfBlank('hf-chart-status', statusValues, 'No manpower requests yet.')) {
            new Chart(document.getElementById('hf-chart-status'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: [SURFACE_2, ACCENT, OK, ACCENT_DARK, INK_3, WARN],
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
