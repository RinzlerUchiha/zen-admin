@extends('layouts.layout')

@section('content')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

    <style>
        main {
            font-size: 12px;
        }

        /* .dashboard-container>.col-3 {
            height: calc(calc(100vh - var(--main-top-margin)) - 1rem);
            border: .3px solid #dddddd;
        } */

        .cursor-pointer {
            cursor: pointer;
        }

        /* .shadow-sm-danger {
            --bs-box-shadow-sm: 0 .125rem .25rem rgba(var(--bs-danger-rgb), .075);
            box-shadow: var(--bs-box-shadow-sm) !important;
        } */

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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    {{-- <div class="row gx-3">
        <div class="col-3">
            <div class="row gx-3">
                <div id="girevance-13b-area" class="col-12 d-none">
                    <div class="card text-bg-body mb-3 shadow-sm border border-3 border-danger">
                        <div class="card-body p-2"></div>
                    </div>
                </div>
                <div id="girevance-13a-area" class="col-12 d-none">
                    <div class="card text-bg-body mb-3 shadow-sm border border-3 border-warning">
                        <div class="card-body p-2"></div>
                    </div>
                </div>
                <div id="girevance-ir-area" class="col-12 d-none">
                    <div class="card text-bg-body mb-3 shadow-sm border border-3 border-danger-subtle">
                        <div class="card-body p-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-5">
            <div class="row gx-3">
                <div class="col-7" id="clearance-area"></div>
                <div class="col" id="exit-interview-area"></div>

                <!-- Force next columns to break to new line at md breakpoint and up -->
                <div class="w-100 d-none d-md-block"></div>

                <div class="col">
                    <div class="card text-bg-body mb-3">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-0">Manpower Request</h6>
                            <table class="table table-sm table-hover table-striped border-top mt-1">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="cursor-pointer">
                                        <td>Department</td>
                                        <td>Position</td>
                                        <td class="text-center">1/3</td>
                                    </tr>
                                    <tr class="cursor-pointer">
                                        <td>Department</td>
                                        <td>Position</td>
                                        <td class="text-center">2/3</td>
                                    </tr>
                                    <tr class="cursor-pointer">
                                        <td>Department</td>
                                        <td>Position</td>
                                        <td class="text-center">3/3</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="row gx-3">
                <div class="col-12" id="timeoff-area"></div>
                
                <div class="col-12" id="memo-area"></div>

                <div class="col-12">
                    <div class="card text-bg-body mb-3">
                        <div class="card-body p-2">
                            <h6 class="card-title pb-1 border-bottom">Performance Rate</h6>
                            <br><br><br><br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="row gx-3">
        <div class="col-md-3">
            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <h6 class="card-title mb-0 pb-1 border-bottom">Academy Trainings</h6>
                    <br><br><br><br><br><br><br><br>
                </div>
            </div>

            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <div class="d-flex pb-1 border-bottom">
                    <h6 class="card-title mb-0">Manpower</h6>
                        <ul class="nav nav-pills ms-auto" id="mprTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="text-reset py-0 px-2 nav-link position-relative" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing-tab-pane" type="button" role="tab" aria-controls="ongoing-tab-pane" aria-selected="true">Ongoing 
                                    <span style="display: none" class="mpr-alert position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                        <span class="visually-hidden">New alerts</span>
                                    </span>
                                </button>
                            </li>
                            <li class="nav-item ms-1" role="presentation">
                                <button class="text-reset py-0 px-2 nav-link position-relative" id="update-tab" data-bs-toggle="tab" data-bs-target="#update-tab-pane" type="button" role="tab" aria-controls="update-tab-pane" aria-selected="false">Update
                                    <span style="display: none" class="mpr-alert position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                        <span class="visually-hidden">New alerts</span>
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="mprTabContent">
                        <div class="tab-pane fade" id="ongoing-tab-pane" role="tabpanel" aria-labelledby="ongoing-tab" tabindex="0">...</div>
                        <div class="tab-pane fade" id="update-tab-pane" role="tabpanel" aria-labelledby="update-tab" tabindex="0">...</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <div class="d-flex border-bottom">
                    <h6 class="card-title mb-0 pb-1 mb-1">Retention</h6> <input type="month" class="ms-auto">
                    </div>
                    {{-- <div class="container-fluid mt-3" id="miniChartsContainer"></div> --}}
                    <button id="showCompanyRetention" class="btn btn-sm btn-light" style="display: none;"><i class="fa fa-arrow-left"></i></button>
                    <div class="chart-container">
                        <canvas id="companyRetentionChart"></canvas>
                        <canvas id="deptRetentionChart" style="display: none"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <h6 class="card-title mb-0 pb-1 border-bottom">PA</h6>
                    {{-- <div class="overflow-y-auto" style="max-height: 300px;"> --}}
                        <div id="dept-pa" class="chart-container">
                            <canvas id="pa-area"></canvas>
                        </div>
                    {{-- </div> --}}
                </div>
            </div>

            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <h6 class="card-title mb-0 pb-1 border-bottom">EEI</h6>
                    <br><br><center>[DETAILS]</center><br><br><br><br><br>
                </div>
            </div>

            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <h6 class="card-title mb-0 pb-1 border-bottom">Face Time</h6>
                    <br><br><center>[DETAILS]</center><br><br><br><br><br>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-underline" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-reset pt-1 mb-0 pb-1 card-title active" id="girevance-ir-tab" data-bs-toggle="tab" data-bs-target="#girevance-ir-tab-pane" type="button" role="tab" aria-controls="girevance-ir-tab-pane" aria-selected="true">Unread IR</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-reset pt-1 mb-0 pb-1 card-title" id="girevance-13a-tab" data-bs-toggle="tab" data-bs-target="#girevance-13a-tab-pane" type="button" role="tab" aria-controls="girevance-13a-tab-pane" aria-selected="false">13A</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-reset pt-1 mb-0 pb-1 card-title" id="girevance-13b-tab" data-bs-toggle="tab" data-bs-target="#girevance-13b-tab-pane" type="button" role="tab" aria-controls="girevance-13b-tab-pane" aria-selected="false">13B</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="pt-1 tab-pane fade show active" id="girevance-ir-tab-pane" role="tabpanel" aria-labelledby="girevance-ir-tab" tabindex="0">...</div>
                        <div class="pt-1 tab-pane fade" id="girevance-13a-tab-pane" role="tabpanel" aria-labelledby="girevance-13a-tab" tabindex="0">...</div>
                        <div class="pt-1 tab-pane fade" id="girevance-13a-reply-tab-pane" role="tabpanel" aria-labelledby="girevance-13a-reply-tab" tabindex="0">...</div>
                        <div class="pt-1 tab-pane fade" id="girevance-13b-tab-pane" role="tabpanel" aria-labelledby="girevance-13b-tab" tabindex="0">...</div>
                    </div>
                </div>
            </div>

            <div class="card text-bg-body mb-3 shadow-sm">
                <div class="card-body p-2">
                    <h6 class="card-title mb-0 pb-1 border-bottom">Clearance</h6>
                    <div id="clearance-area"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function(){

            fetchData('/dashboard/manpower', true)
            .then(data => {
                if(data){
                    let html = ``;
                    for (const el of (data['ongoing'] || [])) {
                        html += `<tr>
                            <td>${el['empname']}</td>
                            <td class="text-center">${el['mp_progress'][1] + ' ' + el['mp_progress'][0]}</td>
                        </tr>`;
                    }
                    let tbl = `<table class="table table-sm table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th class="text-center">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${html}
                        </tbody>
                    </table>`;
                    $('#ongoing-tab-pane').html(tbl);
                    if(html){
                        $('#ongoing-tab-pane').append(`<div class="d-flex"><a href="${document.querySelector('meta[name="url-prefix"]')}/manpower" class="ms-auto fw-bold text-reset text-decoration-none ps-5">View List <i class="bi bi-chevron-double-right"></i></a></div>`);

                        $('#ongoing-tab .mpr-alert').show();
                        $('#ongoing-tab').click();
                    }

                    html = ``;
                    for (const el of (data['update'] || [])) {
                        html += `<tr>
                            <td>${el['empname']}</td>
                            <td>${el['mpu_req']}</td>
                            <td>${el['mpu_reason']}</td>
                        </tr>`;
                    }
                    tbl = `<table class="table table-sm table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Action</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${html}
                        </tbody>
                    </table>`;
                    $('#update-tab-pane').html(tbl);
                    if(html){
                        $('#update-tab-pane').append(`<div class="d-flex"><a href="${document.querySelector('meta[name="url-prefix"]')}/manpower" class="ms-auto fw-bold text-reset text-decoration-none ps-5">View List <i class="bi bi-chevron-double-right"></i></a></div>`);

                        $('#update-tab .mpr-alert').show();
                    }
                    if($('#ongoing-tab .mpr-alert:visible').length == 0){
                        $('#update-tab').click();
                    }

                    $('#mprTabContent table').DataTable({
                        scrollY: '150px',
                        scrollCollapse: true,
                        lengthMenu: [50, 100, { label: 'All', value: -1 }],
                        ordering: false,
                        paging: false,
                        searching: false,
                        info: false
                    });
                }
            });

            fetchData('/dashboard/ir', true)
            .then(data => {
                $('#girevance-ir-tab-pane').html('');

                const div = $('<div/>');
                div.addClass('list-group list-group-flush overflow-y-auto mb-1');
                div.css('height', '130px');
                div.css('max-height', '130px');
                for(i in data['recent']){
                    div.append(`<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/ir?ir=${data['recent'][i]['ir_id']}" class="p-1 list-group-item list-group-item-action text-reset">
                        <small class="fw-medium">${data['recent'][i]['empname']}</small>
                        <p class="m-0 small lh-sm">${data['recent'][i]['ir_subject']}</p>
                        </a>`);
                }

                if(Object.keys(data).length == 0){
                    div.append(`<li class="p-1 list-group-item text-center">
                        <small class="fw-small">- No Pending -</small>
                        </li>`);
                }else{
                    $('#girevance-ir-tab').addClass('position-relative');
                    $('#girevance-ir-tab').append(`<span class="position-absolute top-0 ms-1 mt-2 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">!</span></span>`);
                }

                $('#girevance-ir-tab-pane').append(div);

                if(data['unresolved_cnt'] > 0){
                    $('#girevance-ir-tab-pane').append(`<div class="d-flex">`+
                        (
                            data['unresolved_cnt'] > 0 ?
                            `<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/ir" class="ms-auto fw-normal text-reset text-decoration-none">
                                <span class="fw-bold text-danger">${data['unresolved_cnt']}</span> Unresolved
                                <i class="bi bi-chevron-double-right"></i>
                            </a>` : ``
                        )+
                        `</div>`);
                }
            });

            fetchData('/dashboard/13a', true)
            .then(data => {
                const div = $('<div/>');
                div.addClass('list-group list-group-flush overflow-y-auto');
                div.css('height', '150px');
                div.css('max-height', '150px');
                for(i in data){
                    div.append(`<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/13a?13a=${data[i]['13a_id']}" class="p-1 list-group-item list-group-item-action text-reset d-flex">
                        <small class="fw-medium">${data[i]['to_name']}</small>
                        <small class="ms-auto small lh-sm">${data[i]['status'].toUpperCase()}</small>
                        </a>`);
                }

                if(Object.keys(data).length == 0){
                    div.append(`<li class="p-1 list-group-item text-center">
                        <small class="fw-small">- No Pending -</small>
                        </li>`);
                }else{
                    $('#girevance-13a-tab').addClass('position-relative');
                    $('#girevance-13a-tab').append(`<span class="position-absolute top-0 ms-1 mt-2 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">!</span></span>`);
                }

                // $('#g-ir-tab-pane').html(div);
                $('#girevance-13a-tab-pane').html('');

                $('#girevance-13a-tab-pane').append(div);

                // if(Object.keys(data).length > 0){
                //     $('#girevance-13a-area').removeClass('d-none');
                // }
            });

            fetchData('/dashboard/13b', true)
            .then(data => {
                const div = $('<div/>');
                div.addClass('list-group list-group-flush overflow-y-auto');
                div.css('height', '150px');
                div.css('max-height', '150px');
                for(i in data){
                    div.append(`<a href="${document.querySelector('meta[name="url-prefix"]')}/grievance/13b?13b=${data[i]['13b_id']}" class="p-1 list-group-item list-group-item-action text-reset d-flex">
                        <small class="fw-medium">${data[i]['to_name']}</small>
                        <small class="ms-auto small lh-sm">${data[i]['13b_stat'].toUpperCase()}</small>
                        </a>`);
                }

                if(Object.keys(data).length == 0){
                    div.append(`<li class="p-1 list-group-item text-center">
                        <small class="fw-small">- No Pending -</small>
                        </li>`);
                }else{
                    $('#girevance-13b-tab').addClass('position-relative');
                    $('#girevance-13b-tab').append(`<span class="position-absolute top-0 ms-1 mt-2 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">!</span></span>`);
                }

                // $('#g-ir-tab-pane').html(div);
                $('#girevance-13b-tab-pane').html('');

                $('#girevance-13b-tab-pane').append(div);

                // if(Object.keys(data).length > 0){
                //     $('#girevance-13b-area').removeClass('d-none');
                // }
            });

            fetchData('/dashboard/clearance')
            .then(data => {
                if(data){
                    $('#clearance-area').html(data);
                    $('#clearance-area table').DataTable({
                        scrollY: '150px',
                        scrollCollapse: true,
                        lengthMenu: [50, 100, { label: 'All', value: -1 }],
                        ordering: false,
                        paging: false,
                        searching: false,
                        info: false
                    });
                }
            });

            /* fetchData('/dashboard/exit-interview')
            .then(data => {
                if(data){
                    $('#exit-interview-area').html(data);
                }
            });

            fetchData('/dashboard/timeoff')
            .then(data => {
                if(data){
                    $('#timeoff-area').html(data);
                    $('#timeoff-area table').DataTable({
                        scrollY: '150px',
                        scrollCollapse: true,
                        lengthMenu: [50, 100, { label: 'All', value: -1 }],
                        ordering: false,
                        paging: false,
                        searching: false,
                        info: false
                    });
                }
            });

            fetchData('/dashboard/memo')
            .then(data => {
                if(data){
                    $('#memo-area').html(data);
                }
            }); */


            // fetch('https://your-api.com/performance') // Replace with your API URL
            // .then(response => response.json())
            // .then(data => {
                const data = [
                    {"department": "HR", "performance": 3.4},
                    {"department": "Finance", "performance": 2.8},
                    {"department": "IT", "performance": 4.0},
                    {"department": "Marketing", "performance": 3.1},
                    {"department": "Dept1", "performance": 3.1},
                    {"department": "Dept2", "performance": 2},
                    {"department": "Dept3", "performance": 3.1},
                    {"department": "Dept4", "performance": 3.1},
                    {"department": "Dept5", "performance": 3.1},
                    {"department": "Dept6", "performance": 3.1},
                    {"department": "Dept7", "performance": 3.1},
                ];

                data.sort((a, b) => b.performance - a.performance);

                const labels = data.map(item => item.department);
                const values = data.map(item => item.performance);

                $('#pa-area').closest('.chart-container').height(`calc(30px * ${labels.length})`);

                const ctx = document.getElementById('pa-area');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            barPercentage: .7
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                min: 0,
                                max: 4,
                                ticks: {
                                    autoSkip: false,
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Performance Rate (1.0 - 4.0)'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    autoSkip: false,
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Departments'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            // });
            // .catch(error => {
            //     console.error('Error fetching performance data:', error);
            // });


            fetchData('/dashboard/retention', true)
            .then(data => {
                if(data){
                    const sortedCompany = Object.entries(data.company).sort((a, b) => b[1] - a[1]);

                    const companyLabels = sortedCompany.map(item => item[0]);
                    const companyValues = sortedCompany.map(item => item[1]);

                    const companyCtx = document.getElementById('companyRetentionChart').getContext('2d');
                    const deptCtx = document.getElementById('deptRetentionChart').getContext('2d');

                    const companyChart = new Chart(companyCtx, {
                        type: 'bar',
                        data: {
                            labels: companyLabels,
                            datasets: [{
                                data: companyValues,
                                backgroundColor: 'rgba(75, 192, 192, 0.5)'
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            onClick: function(evt, elements) {
                                if (elements.length > 0) {
                                    const element = elements[0];
                                    const company = this.data.labels[element.index];
                                    console.log('Clicked company:', company);
                                    getDeptRetention(company);
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    formatter: (value) => value + '%',
                                    color: '#333',
                                    font: { weight: 'bold' }
                                }
                            },
                            scales: {
                                y: {
                                    title: {
                                        display: false
                                    }
                                }
                            }
                        },
                        // plugins: [ChartDataLabels]
                    });

                    // Department chart — initially empty
                    let deptChart = new Chart(deptCtx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{
                                data: [],
                                backgroundColor: 'rgba(255, 159, 64, 0.5)'
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    formatter: (value) => value + '%',
                                    color: '#333',
                                    font: { weight: 'bold' }
                                }
                            },
                            scales: {
                                y: {
                                    title: {
                                        display: false
                                    }
                                }
                            }
                        },
                        // plugins: [ChartDataLabels]
                    });

                    function getDeptRetention(company) {
                        document.getElementById('showCompanyRetention').style.display = '';
                        document.getElementById('deptRetentionChart').style.display = '';
                        document.getElementById('companyRetentionChart').style.display = 'none';
                        const deptData = data.dept[company];

                        if (!deptData) return;

                        const sortedDept = Object.entries(deptData).sort((a, b) => b[1] - a[1]);

                        const deptLabels = sortedDept.map(item => item[0]);
                        const deptValues = sortedDept.map(item => item[1]);

                        // Update dept chart
                        deptChart.data.labels = deptLabels;
                        deptChart.data.datasets[0].data = deptValues;
                        deptChart.update();
                    }
                }
            });

            $('#showCompanyRetention').click(function(){
                $(this).hide();
                $('#deptRetentionChart').hide();
                $('#companyRetentionChart').show();
            });


            // fetchData('/dashboard/retention', true)
            // .then(data => {
            //     if(data){
            //         const retentionData = data['company'];
            //         const container = document.getElementById('miniChartsContainer');

            //         Object.keys(retentionData).forEach((company, index) => {
            //             // Create card column
            //             const grp = document.createElement('div');
            //             grp.className = 'd-flex my-1';
            //             grp.addEventListener('click', () => {
            //                 getDeptRetention(data['dept'], company);
            //             });
                        
            //             // Title
            //             const title = document.createElement('h5');
            //             title.className = 'my-auto';
            //             // title.style.width = '100px';
            //             title.innerText = company;

            //             // Title
            //             const curPercentage = document.createElement('h5');
            //             curPercentage.className = 'my-auto ms-auto';
            //             // title.style.width = '100px';
            //             curPercentage.innerText = `${retentionData[company].at(-1) || 0}%`;

            //             // Canvas
            //             const canvas = document.createElement('canvas');
            //             const canvasId = `miniChart${company}`;
            //             canvas.className = 'ms-3';
            //             canvas.id = canvasId;
            //             canvas.width = 100;
            //             canvas.height = 30;
            //             // canvas.title = retentionData[company].join(', ');
                        
            //             // Append elements
            //             grp.appendChild(title);
            //             grp.appendChild(curPercentage);
            //             grp.appendChild(canvas);
            //             container.appendChild(grp);

            //             const deptContainer = document.createElement('div');
            //             deptContainer.id = `miniChart${company}Dept`;
            //             deptContainer.className = 'my-3 container border-start border-bottom';
            //             deptContainer.style.display = 'none';
            //             container.appendChild(deptContainer);

            //             // const rawData = retentionData[company];
            //             const min = Math.min(...retentionData[company]);
            //             const max = Math.max(...retentionData[company]);
            //             const normalized = retentionData[company].map(v => (v - min) / (max - min));
                        
            //             // Create mini chart
            //             const ctx = document.getElementById(canvasId).getContext('2d');
            //             new Chart(ctx, {
            //                 type: 'line',
            //                 data: {
            //                     labels: retentionData[company].map((_, i) => i + 1),
            //                     datasets: [{
            //                         // data: retentionData[company],
            //                         data: normalized,
            //                         borderColor: 'rgba(75, 192, 192, 1)',
            //                         backgroundColor: 'rgba(75, 192, 192, 0.1)',
            //                         fill: true,
            //                         tension: 0,
            //                         pointRadius: 0,
            //                         borderWidth: 2
            //                     }]
            //                 },
            //                 options: {
            //                     responsive: false,
            //                     plugins: {
            //                         legend: { display: false },
            //                         tooltip: { enabled: false }
            //                     },
            //                     scales: {
            //                         x: { display: false },
            //                         // y: { display: false, suggestedMin: 70, suggestedMax: 100 }
            //                         y: { display: false, min: 0, max: 1 }
            //                         // y: { display: false, suggestedMin: 90, max: 100 }
            //                     }
            //                 }
            //             });
            //         });
            //     }
            // });
        });

        const fetchData = async (url, json = false) => {
            const response = await fetch(url);
            if(json == true){
                return await response.json();
            }else{
                return await response.text(); // if fetching raw HTML
            }
        };

        // function getDeptRetention(data, company) {
        //     const retentionData = data[company] || {};
        //     const container = document.getElementById(`miniChart${company}Dept`);
        //     if (container.style.display === 'none') {
        //         container.style.display = '';
        //     } else {
        //         container.style.display = 'none';
        //     }

        //     if(container.children.length > 0){
        //         return;
        //     }

        //     Object.keys(retentionData).forEach((dept, index) => {
        //         // Create card column
        //         const grp = document.createElement('div');
        //         grp.className = 'd-flex mb-2';
                
        //         // Title
        //         const title = document.createElement('h6');
        //         title.className = 'my-auto';
        //         // title.style.width = '100px';
        //         title.innerText = dept;

        //         // Title
        //         const curPercentage = document.createElement('h6');
        //         curPercentage.className = 'my-auto ms-auto';
        //         // title.style.width = '100px';
        //         curPercentage.innerText = `${retentionData[dept].at(-1) || 0}%`;
                
        //         // Canvas
        //         const canvas = document.createElement('canvas');
        //         const canvasId = `miniChart${company+dept}`;
        //         canvas.className = 'ms-3';
        //         canvas.id = canvasId;
        //         canvas.width = 100;
        //         canvas.height = 30;
        //         // canvas.title = retentionData[dept].join(', ');
                
        //         // Append elements
        //         grp.appendChild(title);
        //         grp.appendChild(curPercentage);
        //         grp.appendChild(canvas);
        //         container.appendChild(grp);
                
        //         // Create mini chart
        //         const ctx = document.getElementById(canvasId).getContext('2d');
        //         new Chart(ctx, {
        //             type: 'line',
        //             data: {
        //             labels: retentionData[dept].map((_, i) => i + 1),
        //             datasets: [{
        //                 data: retentionData[dept],
        //                 borderColor: 'rgba(75, 192, 192, 1)',
        //                 backgroundColor: 'rgba(75, 192, 192, 0.1)',
        //                 fill: true,
        //                 tension: 0,
        //                 pointRadius: 0,
        //                 borderWidth: 2
        //             }]
        //             },
        //             options: {
        //                 responsive: false,
        //                 plugins: {
        //                     legend: { display: false },
        //                     tooltip: { enabled: false }
        //                 },
        //                 scales: {
        //                     x: { display: false },
        //                     // y: { display: false, suggestedMin: 70, suggestedMax: 100 }
        //                     // y: { display: false, min: 0, max: 1.1 }
        //                     y: { display: false, suggestedMin: 90, max: 100 }
        //                 }
        //             }
        //         });
        //     });
        // }
    </script>
      
@stop
