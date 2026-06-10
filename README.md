UPDATE:
- government announcement
- view image in announcement


TO FIX:
- transcript not showing for other members listed for signature
- 13b penalty restriction & mitigation show/hide


ONGOING:
- Dashboard
- Settings
- Upload profile picture in 201
- Clearance
- Retention report
- Announcement: reactions
- Orgchart
- Compensation & Benefits


DASHBOARD:
- employee count per department (graph/chart)
- Incident report (list/table)
- 13a (list/table)
- 13b (list/table)
- clearance (list/table)
- manpower request (list/table)
- memo (list/table)
- on-leave/offset (list/table)
- performance rate (graph/chart)
- resigned/resigning employees (list/table) ???
- announcements (like facebook post)


<!-- Row 1: High Priority Reports -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-danger text-white">Incident Reports</div>
        <div class="card-body">
          <table class="table table-sm table-hover">
            <thead><tr><th>Date</th><th>Employee</th><th>Details</th></tr></thead>
            <tbody><!-- Data rows --></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-warning text-dark">Violations</div>
        <div class="card-body">
          <table class="table table-sm table-hover">
            <thead><tr><th>Type</th><th>Employee</th><th>Status</th></tr></thead>
            <tbody><!-- Data rows --></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-secondary text-white">Penalties / Punishments</div>
        <div class="card-body">
          <table class="table table-sm table-hover">
            <thead><tr><th>Employee</th><th>Reason</th><th>Action</th></tr></thead>
            <tbody><!-- Data rows --></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: Clearance & Manpower -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-success text-white">Clearance Status</div>
        <div class="card-body">
          <table class="table table-sm table-hover">
            <thead><tr><th>Employee</th><th>Status</th></tr></thead>
            <tbody><!-- Data rows --></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-info text-white">Manpower Requests</div>
        <div class="card-body">
          <table class="table table-sm table-hover">
            <thead><tr><th>Department</th><th>Position</th><th>Status</th></tr></thead>
            <tbody><!-- Data rows --></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 3: Pie Chart at Bottom -->
  <div class="row mb-4">
    <div class="col-auto">
      <div class="card">
        <div class="card-header">Employee Count by Department</div>
        <div class="card-body">
          <canvas id="departmentPieChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Pie Chart: Employee Count per Department
    const ctxPie = document.getElementById('departmentPieChart').getContext('2d');
    new Chart(ctxPie, {
      type: 'pie',
      data: {
        labels: ['HR', 'IT', 'Sales', 'Finance'],
        datasets: [{
          label: 'Employees',
          data: [10, 15, 8, 5],
          backgroundColor: [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)'
          ],
          borderWidth: 1
        }]
      }
    });
  </script>