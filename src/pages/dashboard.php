<?php
render_layout('Overview', 'dashboard', function() { ?>

<div class="page-header">
  <div>
    <h1>Overview</h1>
    <p class="page-subtitle">Real-time call center performance summary.</p>
  </div>
  <div class="page-header-actions">
    <select id="range-select" class="form-select form-select-sm" style="width:140px">
      <option value="1">Last 24 h</option>
      <option value="7" selected>Last 7 days</option>
      <option value="30">Last 30 days</option>
    </select>
  </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4" id="kpi-grid">
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="stat-label">Total Calls</div>
        <div class="stat-value" id="kpi-total">—</div>
        <div class="stat-sub">For selected period</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="stat-label">Answer Rate</div>
        <div class="stat-value text-green" id="kpi-rate">—</div>
        <div class="stat-sub" id="kpi-answered-sub">— answered</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="stat-label">Missed Calls</div>
        <div class="stat-value text-red" id="kpi-missed">—</div>
        <div class="stat-sub">Needs attention</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="stat-label">Avg Duration</div>
        <div class="stat-value text-blue" id="kpi-duration">—</div>
        <div class="stat-sub">Per answered call</div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card h-100">
      <div class="card-header">
        <div class="card-title">Call Volume Trend</div>
        <div class="card-desc">Daily totals for selected period</div>
      </div>
      <div class="card-body">
        <div class="chart-container"><canvas id="trend-chart"></canvas></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header">
        <div class="card-title">Extensions</div>
        <div class="card-desc">Registration status</div>
      </div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-6">
            <div class="stat-label">Registered</div>
            <div class="stat-value text-green" id="ext-active">—</div>
          </div>
          <div class="col-6">
            <div class="stat-label">Total</div>
            <div class="stat-value" id="ext-total">—</div>
          </div>
        </div>
        <div class="chart-container" style="height:160px"><canvas id="ext-chart"></canvas></div>
      </div>
    </div>
  </div>
</div>

<!-- Agent Performance Table -->
<div class="card">
  <div class="card-header">
    <div class="card-title">Agent Performance</div>
    <div class="card-desc">Individual call handling statistics</div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="agent-table">
      <thead>
        <tr><th>Agent</th><th>Total</th><th>Answered</th><th>Missed</th><th>Answer Rate</th><th>Avg Duration</th></tr>
      </thead>
      <tbody id="agent-tbody">
        <tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script>
let trendChart = null, extChart = null;

async function loadDashboard() {
  const range = document.getElementById('range-select').value;
  try {
    const d = await api('/dashboard?range=' + range);
    const t = d.totals;
    const rate = t.total > 0 ? Math.round(t.answered / t.total * 100) : 0;

    document.getElementById('kpi-total').textContent = t.total;
    document.getElementById('kpi-rate').textContent = rate + '%';
    document.getElementById('kpi-answered-sub').textContent = t.answered + ' answered';
    document.getElementById('kpi-missed').textContent = t.missed;
    document.getElementById('kpi-duration').textContent = formatDuration(t.avg_duration);
    document.getElementById('ext-active').textContent = d.activeExtensions;
    document.getElementById('ext-total').textContent = d.totalExtensions;

    const labels   = d.trend.map(r => r.day.slice(5));
    const totals   = d.trend.map(r => parseInt(r.total));
    const answered = d.trend.map(r => parseInt(r.answered));

    if (trendChart) trendChart.destroy();
    trendChart = new Chart(document.getElementById('trend-chart'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          {label:'Total',   data:totals,   borderColor:'#38bdf8', backgroundColor:'rgba(56,189,248,.1)', fill:true, tension:.4},
          {label:'Answered',data:answered, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.1)',  fill:true, tension:.4}
        ]
      },
      options: {responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},
                scales:{y:{beginAtZero:true,grid:{color:'#1e2535'}},x:{grid:{color:'#1e2535'}}}}
    });

    if (extChart) extChart.destroy();
    const inactive = d.totalExtensions - d.activeExtensions;
    extChart = new Chart(document.getElementById('ext-chart'), {
      type: 'doughnut',
      data: {
        labels: ['Registered','Unregistered'],
        datasets: [{data:[d.activeExtensions, inactive], backgroundColor:['#22c55e','#334155'], borderWidth:0}]
      },
      options: {responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},cutout:'65%'}
    });

    const tbody = document.getElementById('agent-tbody');
    if (!d.agentStats.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No agent data for this period</td></tr>';
    } else {
      tbody.innerHTML = d.agentStats.map(a => {
        const r = a.total > 0 ? Math.round(a.answered / a.total * 100) : 0;
        return `<tr>
          <td class="fw-medium">${a.name}</td>
          <td>${a.total}</td>
          <td><span class="badge bg-success">${a.answered}</span></td>
          <td><span class="badge bg-danger">${a.missed}</span></td>
          <td><div class="progress" style="height:6px;width:80px;background:#1e2535">
                <div class="progress-bar bg-success" style="width:${r}%"></div>
              </div>
              <small class="text-muted ms-1">${r}%</small></td>
          <td class="text-muted">${formatDuration(a.avg_duration)}</td>
        </tr>`;
      }).join('');
    }
  } catch(e) { console.error(e); }
}

document.getElementById('range-select').addEventListener('change', loadDashboard);
loadDashboard();
</script>
<?php }, $user ?? []); ?>
