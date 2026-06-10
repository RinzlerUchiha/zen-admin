@extends('layouts.layout')

@section('content')

    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

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
    </style>

    <div class="container pb-3">

        <h1 class="mb-3 fw-bold" style="font-size:1.6rem;">Dashboard</h1>

        <!-- KPI Cards -->
        <div class="row mb-3 g-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-title">Employee Breakdown (Company / Department)</div>
                    <button id="backButton" class="btn btn-secondary btn-sm mb-1 d-none">&larr; Back to Company View</button>
                    {{-- <canvas id="breakdownChart" height="220"></canvas> --}}
                    <div style="height: 150px">
                        <canvas id="employeeChart" width="100%"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-title">Performance Rate (Company / Dept / Employee)</div>
                    <canvas id="performanceRateChart" width="220"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="d-flex align-items-center">
                        <div class="card-title mb-0">Manpower Request</div>
                        <div class="ms-auto fs-5 text-primary fw-semibold">3</div>
                    </div>
                </div>
                <div class="card">
                    <div class="d-flex align-items-center">
                        <div class="card-title mb-0">Total Employees</div>
                        <div class="ms-auto fs-5 text-success fw-semibold">520</div>
                    </div>
                </div>
                <div class="card">
                    <div class="d-flex align-items-center">
                        <div class="card-title mb-0">Total Resigning</div>
                        <div class="ms-auto fs-5 text-danger fw-semibold">12</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Clearance + Manpower -->
        <div class="row mb-3 g-3">
            <div class="col-md">
                <!-- Incident Reports -->
                <div class="card">
                    <div class="card-title">Incident Reports</div>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1001</td>
                                <td>John Doe</td>
                                <td>Safety</td>
                                <td>2025-06-10</td>
                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md">
                <div class="card">
                    <div class="card-title">Pending Clearance</div>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Dept</th>
                                <th>Last Day</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>IT</td>
                                <td>2025-07-01</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- <div class="col-md">
                <div class="card">
                    <div class="card-title">Manpower Requests</div>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Dept</th>
                                <th>Position</th>
                                <th>Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>IT</td>
                                <td>DevOps Engineer</td>
                                <td>2</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-3">
            <div class="col-md d-flex flex-column">
                <!-- Retention Rate Chart -->
                <div class="card flex-fill">
                    <div class="card-title">Retention Rate (Last 6 Months)</div>
                    <canvas id="retentionChart" height="120"></canvas>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-title">Training Programs & Modules</div>
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

                <div class="card">
                    <div class="card-title">New Hires This Month</div>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Dept</th>
                                <th>Start Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Mary Johnson</td>
                                <td>Marketing</td>
                                <td>2025-06-15</td>
                            </tr>
                            <tr>
                                <td>David Lee</td>
                                <td>Finance</td>
                                <td>2025-06-10</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // // Breakdown double doughnut
        // new Chart(document.getElementById('breakdownChart'), {
        //     type: 'doughnut',
        //     data: {
        //         labels: ['Company A', 'Company B', 'Company C'],
        //         datasets: [{
        //             label: 'Company',
        //             data: [300, 150, 70],
        //             backgroundColor: ['#4e79a7', '#f28e2b', '#e15759']
        //         }, {
        //             label: 'Department',
        //             data: [120, 100, 80, 70, 50],
        //             backgroundColor: ['#76b7b2', '#59a14f', '#edc949', '#af7aa1', '#ff9da7']
        //         }]
        //     },
        //     options: {
        //         cutout: '50%',
        //         plugins: {
        //             legend: {
        //                 position: 'bottom'
        //             }
        //         }
        //     }
        // });

        // Performance Rate bar
        new Chart(document.getElementById('performanceRateChart'), {
            type: 'bar',
            data: {
                labels: ['Dept A', 'Dept B', 'Dept C', 'Dept D', 'Dept E'],
                datasets: [{
                    label: 'Performance %',
                    data: [1, 3, 3.5, 3.1, 4],
                    backgroundColor: '#4e79a7',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Retention Rate
        new Chart(document.getElementById('retentionChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Retention %',
                    data: [95, 94, 96, 93, 95, 97],
                    fill: false,
                    borderColor: '#59a14f',
                    tension: 0.3
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });


        const ctx = document.getElementById('employeeChart').getContext('2d');

        // --- Data ---

        // Company headcounts
        const companyData = {
            labels: ['Company A', 'Company B', 'Company C'],
            // datasets: [{
            //     label: 'Headcount',
            //     data: [300, 200, 100],
            //     backgroundColor: ['#4e79a7','#f28e2b','#e15759']
            // }]
            data: [300, 200, 100],
            colors: ['#4e79a7','#f28e2b','#e15759']
        };

        // Departments per company
        const departmentData = {
            'Company A': {
                labels: ['Dev', 'HR', 'Sales'],
                data: [120, 100, 80],
                colors: ['#76b7b2','#59a14f','#edc949']
            },
            'Company B': {
                labels: ['IT', 'HR'],
                data: [120, 80],
                colors: ['#af7aa1','#ff9da7']
            },
            'Company C': {
                labels: ['Sales'],
                data: [100],
                colors: ['#ff9da7']
            }
        };

        // --- Chart Init ---

        let currentView = 'company';

        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: companyData.labels,
                datasets: [{
                    label: 'Headcount',
                    data: companyData.data,
                    backgroundColor: companyData.colors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '50%',
                borderWidth: 0,
                plugins: {
                    legend: { 
                        position: 'right',
                        labels: {
                            boxWidth: 10,
                            boxHeight: 10,
                            padding: 8,
                            font: {
                                size: 12
                            }
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
                    if (elements.length > 0 && currentView === 'company') {
                        const index = elements[0].index;
                        const company = companyData.labels[index];
                        showDepartmentView(company);
                    }
                }
            }
        });

        // --- Functions ---

        function showDepartmentView(company) {
            const dept = departmentData[company];
            if (!dept) return;
            console.log(dept.labels, dept.data);
            chart.data.labels = dept.labels;
            chart.data.datasets[0].data = dept.data;
            chart.data.datasets[0].backgroundColor = dept.colors;
            chart.update();

            currentView = 'department';
            document.getElementById('backButton').classList.remove('d-none');
        }

        function showCompanyView() {        
            chart.data.labels = companyData.labels;
            chart.data.datasets[0].data = companyData.data;
            chart.data.datasets[0].backgroundColor = companyData.colors;
            chart.update();

            currentView = 'company';
            document.getElementById('backButton').classList.add('d-none');
        }

        // Back button event
        document.getElementById('backButton').addEventListener('click', showCompanyView);
    </script>

@stop
