@extends('layouts.layout')

@section('content')

    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    {{-- <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script> --}}

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <style>
        body {
            background-color: #ffffff;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .card {
            border: 1px solid #eaeaea;
            border-radius: 0.5rem;
            background-color: #fff;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 0.5rem;
        }

        .table {
            border-color: #eaeaea;
        }

        .table th {
            color: #555;
            font-weight: 500;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.4rem 0.5rem;
        }

        main {
            font-size: 12px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .chart-container {
            position: relative;
            height: 200px;
        }

        #mprTab li .nav-link {
            border-radius: 3px;
            border: none;
        }

        #mprTab li .nav-link.active {
            color: white !important;
        }

        .card-max-h{
            max-height: 270px;
            min-height: 270px;
        }

        #pa-month {
            width: fit-content;
            font-size: 12px;
            height: fit-content;
        }
    </style>

    <div class="container pb-3">

        <div class="d-flex">
            <h1 class="mb-3 fw-bold" style="font-size:1.6rem;">Dashboard</h1>
            <h5 class="ms-auto text-muted align-self-center">{{ now()->format('F d, Y') }}</h5>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-title">Employee Count</div>
                    <div class="d-flex d-none">
                        <button id="backButton" class="btn btn-secondary btn-sm mb-1">&larr;</button>
                        <h5 class="ms-3 my-auto">Company</h5>
                    </div>
                    <div style="width: 100%;">
                        <canvas id="employeeCountChart" class=""></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-title mb-1">Performance Appraisal</div>
                    <div class="d-flex align-items-center">
                        <input type="month" class="border rounded mb-1 p-1" id="pa-month" value="{{ now()->subMonth()->format('Y-m') }}">
                        <div id="pa-chart-loading-spinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="d-flex d-none">
                        <button id="paBackButton" class="btn btn-secondary btn-sm mb-1">&larr;</button>
                        <h5 class="ms-3 my-auto">Dept</h5>
                    </div>
                    <div class="chart-container" style="min-height: 100px;">
                        <canvas id="performanceRateChart" width="220"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                {{-- <div class="row g-3">
                    <div class="col text-center"><h5 class="text-muted">As of {{ now()->format('F d, Y') }}</h5></div>
                </div> --}}
                <div class="row g-3">
                    <div class="col-md">
                        <div class="card">
                            <div class="d-flex align-items-center">
                                <div class="card-title mb-0">Manpower Request</div>
                                <div class="ms-auto fs-5 text-primary fw-semibold" id="manpower-cnt">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card">
                            <div class="d-flex align-items-center">
                                <div class="card-title mb-0">Exit Interview</div>
                                <div class="ms-auto fs-5 text-danger fw-semibold" id="exits-cnt">-</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md d-flex flex-column">
                        <div class="card flex-fill">
                            <div class="card-title">Retention Rate (Last 6 Months)</div>
                            <canvas id="retentionChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md">
                        <div class="card card-max-h">
                            <div class="card-title">Training Programs & Modules</div>
                            <div class="table-responsive" id="academy-area">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Training Title</th>
                                            <th>Modules</th>
                                            <th>Sessions</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Orientation</td>
                                            <td>5</td>
                                            <td>3</td>
                                            <td><span class="badge bg-success">Active</span></td>
                                        </tr>
                                        <tr>
                                            <td>Leadership</td>
                                            <td>8</td>
                                            <td>4</td>
                                            <td><span class="badge bg-success">Active</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-md">
                        <div class="card card-max-h">
                            <div class="card-title">Probationary</div>
                            <div class="table-responsive" id="probationary-area"></div>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card card-max-h">
                            <ul class="nav nav-underline shadow-sm" id="grievanceTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-reset pt-1 mb-0 pb-1 card-title active"
                                        id="girevance-ir-tab" data-bs-toggle="tab" data-bs-target="#girevance-ir-tab-pane"
                                        type="button" role="tab" aria-controls="girevance-ir-tab-pane"
                                        aria-selected="true">Unread
                                        IR</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-reset pt-1 mb-0 pb-1 card-title" id="girevance-13a-tab"
                                        data-bs-toggle="tab" data-bs-target="#girevance-13a-tab-pane" type="button"
                                        role="tab" aria-controls="girevance-13a-tab-pane"
                                        aria-selected="false">13A</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-reset pt-1 mb-0 pb-1 card-title" id="girevance-13b-tab"
                                        data-bs-toggle="tab" data-bs-target="#girevance-13b-tab-pane" type="button"
                                        role="tab" aria-controls="girevance-13b-tab-pane"
                                        aria-selected="false">13B</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="grievanceTabContent">
                                <div class="pt-1 tab-pane fade show active" id="girevance-ir-tab-pane" role="tabpanel"
                                    aria-labelledby="girevance-ir-tab" tabindex="0">...</div>
                                <div class="pt-1 tab-pane fade" id="girevance-13a-tab-pane" role="tabpanel"
                                    aria-labelledby="girevance-13a-tab" tabindex="0">...</div>
                                <div class="pt-1 tab-pane fade" id="girevance-13a-reply-tab-pane" role="tabpanel"
                                    aria-labelledby="girevance-13a-reply-tab" tabindex="0">...</div>
                                <div class="pt-1 tab-pane fade" id="girevance-13b-tab-pane" role="tabpanel"
                                    aria-labelledby="girevance-13b-tab" tabindex="0">...</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-max-h">
                            <div class="card-title">Pending Clearance</div>
                            <div class="table-responsive" id="clearance-area"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md">
                        <div class="card card-max-h">
                            <div class="card-title">Leave/Offset</div>
                            <div class="table-responsive" id="timeoff-area"></div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card card-max-h">
                            <div class="card-title">Travel</div>
                            <div class="table-responsive" id="travel-area"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let chartPA;
        $(function() {
            fetchData('/dashboard/counters', true)
                .then(data => {
                    $('#manpower-cnt').text(data['manpower'] ? (data['manpower']['fill'] + ' / ' + data['manpower']['require']) : '-');
                    $('#employees-cnt').text(Object.values(data['employees']).reduce((total, item) => total +
                        item.count, 0) || '-');

                    $('#exits-cnt').text((data['exits']['exitList'] && data['exits']['exitList'] != data['exits']['forInterview']) ? Object.values(data['exits']).join(' / ') : '-');

                    const ctx = document.getElementById('employeeCountChart').getContext('2d');
                    
                    const companyColors = {
                        TNGC: 'blue',
                        SJI:  '#ef57a3',
                        QST:  'orange'
                    };

                    const companyData = {
                        labels: Object.keys(data['employees']),
                        data: Object.values(data['employees']).map(c => c.count),
                        colors: Object.keys(data['employees']).map(company => companyColors[company] ?? generateColors(company.length) )
                    };

                    for (i in data['employees']) {
                        data['employees'][i] = {
                            labels: Object.keys(data['employees'][i]['breakdown']),
                            data: Object.values(data['employees'][i]['breakdown'])
                        };
                    }

                    const departmentData = data['employees'];

                    let currentView = 'company';
                    let isChartBusy = false;

                    const chart = new Chart(ctx, {
                        type: 'doughnut',
                        plugins: [{
                            beforeInit: function(chart, options) {
                                // Get a reference to the original fit function
                                const originalFit = chart.legend.fit;

                                // Override the fit function
                                chart.legend.fit = function fit() {
                                    // Call the original function and bind scope in order to use `this` correctly inside it
                                    originalFit.bind(chart.legend)();
                                    // Change the height as suggested in other answers
                                    this.width += 50;
                                }
                            }
                        },ChartDataLabels],
                        data: {
                            labels: companyData.labels,
                            datasets: [{
                                label: 'Headcount',
                                data: companyData.data,
                                backgroundColor: companyData.colors,
                                datalabels: {
                                    color: companyData.colors,
                                    anchor: 'end',
                                    font: {
                                        weight: 'bolder'
                                    },
                                    align: 'end'
                                    // offset: '-10'
                                    // formatter: function(value, context) {
                                    //     // return context.chart.data.labels[context.dataIndex];
                                    //     return context.dataIndex + ': ' + value;
                                    // }
                                }
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '50%',
                            borderWidth: 0,
                            layout: {
                                padding: {
                                    right: 20
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'left',
                                    align: 'start',
                                    labels: {
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        padding: 8
                                        // font: {
                                        //     size: 12
                                        // }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.label}: ${context.formattedValue}`;
                                        }
                                    }
                                },
                            },
                            onClick: (evt, elements) => {
                                if (isChartBusy) return;
                                if (elements.length > 0 && currentView === 'company') {
                                    isChartBusy = true;
                                    const index = elements[0].index;
                                    const company = companyData.labels[index];
                                    showDepartmentView(company);
                                }
                            }
                        }
                    });

                    function showDepartmentView(company) {
                        const dept = departmentData[company];
                        if (!dept) return;

                        const deptColors = generateColors(dept.labels.length);

                        chart.data.labels = dept.labels;
                        chart.data.datasets[0].data = dept.data;
                        chart.data.datasets[0].backgroundColor = deptColors;
                        chart.data.datasets[0].datalabels.color = deptColors;
                        chart.update();

                        currentView = 'department';
                        document.getElementById('backButton').parentElement.querySelector('h5').textContent =
                            company;
                        document.getElementById('backButton').parentElement.classList.remove('d-none');

                        isChartBusy = false;
                    }

                    function showCompanyView() {
                        isChartBusy = true;
                        chart.data.labels = companyData.labels;
                        chart.data.datasets[0].data = companyData.data;
                        chart.data.datasets[0].backgroundColor = companyData.colors;
                        chart.data.datasets[0].datalabels.color = companyData.colors;
                        chart.update();

                        currentView = 'company';
                        document.getElementById('backButton').parentElement.querySelector('h5').textContent =
                        '';
                        document.getElementById('backButton').parentElement.classList.add('d-none');
                        isChartBusy = false;
                    }

                    document.getElementById('backButton').addEventListener('click', showCompanyView);
                });

            fetchPA();

            fetchData('/dashboard/retention', true)
                .then(data => {
                    // const palette = [
                    //     '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
                    //     '#edc949', '#af7aa1', '#ff9da7', '#9c755f', '#bab0ab'
                    // ];

                    const sorted = Object.fromEntries(
                        Object.entries(data?.company ?? {}).sort((a, b) => (b[0]).localeCompare(a[0]))
                    );

                    const companyColors = {
                        TNGC: 'blue',
                        SJI:  '#ef57a3',
                        QST:  'orange'
                    };

                    const datasets = Object.entries(sorted).map(([companyName, values], i) => ({
                        label: companyName,
                        data: data.months.map(month => values[month] ?? 0),
                        // borderColor: palette[i % palette.length],
                        // backgroundColor: palette[i % palette.length],
                        borderColor: companyColors[companyName],
                        backgroundColor: companyColors[companyName],
                        tension: 0,
                        fill: false
                    }));

                    // Retention Rate
                    const chart = new Chart(document.getElementById('retentionChart'), {
                        type: 'bar',
                        data: {
                            labels: data.months,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            // maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    suggestedMin: 80,
                                    suggestedMax: 100,
                                    ticks: {
                                        callback: value => value + '%'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: context =>
                                            `${context.dataset.label}: ${context.formattedValue}%`
                                    }
                                }
                            }
                        }
                    });
                });

            fetchData('/dashboard/ir', true)
                .then(data => {
                    $('#girevance-ir-tab-pane').html('');

                    const div = $('<div/>');
                    div.addClass('list-group list-group-flush overflow-y-auto mb-1');
                    div.css('height', '175px');
                    div.css('max-height', '175px');
                    for (i in data['recent']) {
                        div.append(`<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/ir?ir=${data['recent'][i]['ir_id']}" class="p-1 list-group-item list-group-item-action text-reset">
                        <span class="fw-bold text-primary-emphasis">${data['recent'][i]['empname']}</span>
                        <p class="m-0 lh-sm">${data['recent'][i]['ir_subject']}</p>
                        </a>`);
                    }

                    if (Object.keys(data).length == 0) {
                        div.append(`<li class="p-1 list-group-item text-center">
                        <small>- No Pending -</small>
                        </li>`);
                    } else {
                        $('#girevance-ir-tab').addClass('position-relative');
                        $('#girevance-ir-tab').append(
                            `<span class="position-absolute top-0 ms-1 mt-2 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">!</span></span>`
                        );
                    }

                    $('#girevance-ir-tab-pane').append(div);

                    if (data['unresolved_cnt'] > 0) {
                        $('#girevance-ir-tab-pane').append(`<div class="d-flex">` +
                            (
                                data['unresolved_cnt'] > 0 ?
                                `<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/ir" class="ms-auto fw-normal text-reset text-decoration-none">
                                <span class="fw-bold text-danger">${data['unresolved_cnt']}</span> Unresolved
                                <i class="bi bi-chevron-double-right"></i>
                            </a>` : ``
                            ) +
                            `</div>`);
                    }
                });

            fetchData('/dashboard/13a', true)
                .then(data => {
                    const div = $('<div/>');
                    div.addClass('list-group list-group-flush overflow-y-auto');
                    div.css('height', '150px');
                    div.css('max-height', '150px');
                    for (i in data) {
                        div.append(`<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/13a?13a=${data[i]['13a_id']}" class="p-1 list-group-item list-group-item-action text-reset d-flex">
                        <span class="fw-bold text-primary-emphasis">${data[i]['to_name']}</span>
                        <span class="ms-auto lh-sm">${data[i]['status'].toUpperCase()}</span>
                        </a>`);
                    }

                    if (Object.keys(data).length == 0) {
                        div.append(`<li class="p-1 list-group-item text-center">
                        <small>- No Pending -</small>
                        </li>`);
                    } else {
                        $('#girevance-13a-tab').addClass('position-relative');
                        $('#girevance-13a-tab').append(
                            `<span class="position-absolute top-0 ms-1 mt-2 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">!</span></span>`
                        );
                    }

                    // $('#g-ir-tab-pane').html(div);
                    $('#girevance-13a-tab-pane').html('');

                    $('#girevance-13a-tab-pane').append(div);
                });

            fetchData('/dashboard/13b', true)
                .then(data => {
                    const div = $('<div/>');
                    div.addClass('list-group list-group-flush overflow-y-auto');
                    div.css('height', '150px');
                    div.css('max-height', '150px');
                    for (i in data) {
                        div.append(`<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/13b?13b=${data[i]['13b_id']}" class="p-1 list-group-item list-group-item-action text-reset d-flex">
                        <span class="fw-bold text-primary-emphasis">${data[i]['to_name']}</span>
                        <span class="ms-auto lh-sm">${data[i]['13b_stat'].toUpperCase()}</span>
                        </a>`);
                    }

                    if (Object.keys(data).length == 0) {
                        div.append(`<li class="p-1 list-group-item text-center">
                        <span>- No Pending -</span>
                        </li>`);
                    } else {
                        $('#girevance-13b-tab').addClass('position-relative');
                        $('#girevance-13b-tab').append(
                            `<span class="position-absolute top-0 ms-1 mt-2 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">!</span></span>`
                        );
                    }

                    // $('#g-ir-tab-pane').html(div);
                    $('#girevance-13b-tab-pane').html('');

                    $('#girevance-13b-tab-pane').append(div);
                });

            fetchData('/dashboard/clearance')
                .then(data => {
                    if (data) {
                        $('#clearance-area').html(data);
                        // $('#clearance-area table').DataTable({
                        //     scrollY: '150px',
                        //     scrollCollapse: true,
                        //     lengthMenu: [50, 100, {
                        //         label: 'All',
                        //         value: -1
                        //     }],
                        //     ordering: false,
                        //     paging: false,
                        //     searching: false,
                        //     info: false
                        // });
                    }
                });

            fetchData('/dashboard/probationary', true)
                .then(data => {
                    let html = '';
                    html += '<table class="table table-sm mb-0">';
                    html += '<thead class="table-light position-sticky top-0 shadow-sm">';
                    html += '<tr>';
                    html += '<th class="px-1">Employee</th>';
                    html += '<th class="px-1">Dept</th>';
                    html += '<th class="px-1">Start Date</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    for(i of data){
                        html += '<tr class="small">';
                        html += '<td class="px-1">' + i.empname + '</td>';
                        html += '<td class="px-1">' + (i.dept || '-') + '</td>';
                        html += '<td class="px-1">' + i.dt_hired + '</td>';
                        html += '</tr>';
                    }
                    html += '</tbody>';
                    html += '</table>';
                    $('#probationary-area').html(html);
                    console.log(data.length);
                    $('#probationary-area').parent().find('.card-title').append(data.length ? ' (' + data.length + ')' : '');
                });
            
            fetchData('/dashboard/academy', true)
                .then(data => {
                    let html = '';
                    html += '<table class="table table-sm mb-0">';
                    html += '<thead class="table-light position-sticky top-0 shadow-sm">';
                    html += '<tr>';
                    html += '<th class="px-1">Course</th>';
                    html += '<th class="px-1">Module</th>';
                    html += '<th class="px-1">Topic</th>';
                    // html += '<th class="px-1">Status</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    for(i in data){
                        html += '<tr class="small">';
                        html += '<td class="px-1">' + i + '</td>';
                        html += '<td class="px-1 text-center">' + Object.keys(data[i]).length + '</td>';
                        html += '<td class="px-1 text-center">' + Object.values(data[i]).reduce((total, item) => total + Object.keys(item).length, 0) + '</td>';
                        // html += '<td class="px-1"></td>';
                        html += '</tr>';
                    }
                    html += '</tbody>';
                    html += '</table>';
                    $('#academy-area').html(html);
                });

            fetchData('/dashboard/timeoff')
                .then(data => {
                    if(data){
                        $('#timeoff-area').html(data);
                        // $('#timeoff-area table').DataTable({
                        //     scrollY: '150px',
                        //     scrollCollapse: true,
                        //     lengthMenu: [50, 100, { label: 'All', value: -1 }],
                        //     ordering: false,
                        //     paging: false,
                        //     searching: false,
                        //     info: false
                        // });
                    }
                });

            fetchData('/dashboard/travel')
                .then(data => {
                    if(data){
                        $('#travel-area').html(data);
                    }
                });

            $('#pa-month').change(function(){
                fetchPA();
            });
        });

        function fetchPA(){
            $('#pa-chart-loading-spinner').show();
            if(chartPA) chartPA.destroy();
            fetchData('/dashboard/pa/' + $('#pa-month').val(), true)
                .then(data => {

                    const sortedDept = Object.entries(data).sort((a, b) => (b[1].rating - a[1].rating) || a[0]
                        .localeCompare(b[0]));
                    const deptLabels = sortedDept.map(item => item[0]);
                    const deptValues = sortedDept.map(item => item[1].rating);

                    $('#performanceRateChart').closest('.chart-container').height(
                        `calc(30px * ${deptLabels.length})`);

                    let currentView = 'dept';
                    let isChartBusy = false;

                    // Performance Rate bar
                    chartPA = new Chart(document.getElementById('performanceRateChart'), {
                        type: 'bar',
                        plugins: [ChartDataLabels],
                        data: {
                            labels: deptLabels,
                            datasets: [{
                                label: 'Performance %',
                                data: deptValues,
                                backgroundColor: '#4e79a7',
                                borderRadius: 6,
                                datalabels: {
                                    color: 'black',
                                    anchor: 'end',
                                    align: 'end'
                                }
                            }]
                        },
                        options: {
                            layout: {
                                padding: {
                                    right: 30
                                }
                            },
                            indexAxis: 'y',
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: 4,
                                    ticks: {
                                        autoSkip: false,
                                        stepSize: 1
                                    }
                                },
                                y: {
                                    ticks: {
                                        autoSkip: false,
                                        font: {
                                            size: 11 // reduce size
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            onClick: (evt, elements) => {
                                if (elements.length > 0 && currentView === 'dept') {
                                    const index = elements[0].index;
                                    showEmpView(deptLabels[index]);
                                }
                            }
                        }
                    });

                    function showEmpView(dept) {
                        const emp = data[dept]['emp'];

                        const empLabels = emp.map(item => item.empname);
                        const empValues = emp.map(item => item.weighted_rating_total);

                        if (!emp) return;

                        const empColors = generateColors(1);

                        $('#performanceRateChart').closest('.chart-container').height(
                            `calc(30px * ${empLabels.length})`);

                        chartPA.data.labels = empLabels;
                        chartPA.data.datasets[0].data = empValues;
                        chartPA.data.datasets[0].backgroundColor = empColors[0];
                        chartPA.update();

                        currentView = 'emp';
                        document.getElementById('paBackButton').parentElement.querySelector('h5').textContent =
                            dept;
                        document.getElementById('paBackButton').parentElement.classList.remove('d-none');
                    }

                    function showDeptView() {

                        $('#performanceRateChart').closest('.chart-container').height(
                            `calc(30px * ${deptLabels.length})`);

                        chartPA.data.labels = deptLabels;
                        chartPA.data.datasets[0].data = deptValues;
                        chartPA.data.datasets[0].backgroundColor = '#4e79a7';
                        chartPA.update();

                        currentView = 'dept';
                        document.getElementById('paBackButton').parentElement.querySelector('h5').textContent =
                            '';
                        document.getElementById('paBackButton').parentElement.classList.add('d-none');
                    }

                    document.getElementById('paBackButton').addEventListener('click', showDeptView);

                    $('#pa-chart-loading-spinner').hide();
                });
        }

        const fetchData = async (url, json = false) => {
            const response = await fetch(url);
            if (json == true) {
                return await response.json();
            } else {
                return await response.text(); // if fetching raw HTML
            }
        };

        // --- Helper to generate colors ---
        function generateColors(count) {
            return Array.from({
                length: count
            }, (_, i) => {
                const hue = Math.round((360 / count) * i);
                return `hsl(${hue}, 60%, 60%)`;
            });
        }
    </script>

@stop
