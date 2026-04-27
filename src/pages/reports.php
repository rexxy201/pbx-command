<?php
render_layout('Reports', 'reports', function() { ?>
<div class="page-header">
  <div>
    <h1>Analytics &amp; Reports</h1>
    <p class="page-subtitle">System-wide performance and call volume metrics.</p>
  </div>
  <div class="page-header-actions">
    <input type="date" id="rpt-from" class="form-control form-control-sm" style="width:140px">
    <input type="date" id="rpt-to"   class="form-control form-control-sm" style="width:140px">
    <button class="btn btn-outline-secondary btn-sm" id="rpt-load">
      <i class="bi bi-arrow-clockwise me-1"></i>Load
    </button>
    <button class="btn btn-outline-secondary btn-sm" id="btn-export-csv">
      <i class="bi bi-download me-1"></i>Download CSV
    </button>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Total Calls</div>
      <div class="stat-value" id="r-total">—</div>
      <div class="stat-sub">Selected period</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Answer Rate</div>
      <div class="stat-value text-green" id="r-rate">—</div>
      <div class="stat-sub" id="r-ans-sub">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Missed Calls</div>
      <div class="stat-value text-red" id="r-missed">—</div>
      <div class="stat-sub">Needs attention</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Avg Duration</div>
      <div class="stat-value text-blue" id="r-duration">—</div>
      <div class="stat-sub">Per answered call</div>
    </div></div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card h-100">
      <div class="card-header"><div class="card-title">Call Volume Trends</div><div class="card-desc">Daily total vs answered</div></div>
      <div class="card-body"><div class="chart-container"><canvas id="r-trend"></canvas></div></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header"><div class="card-title">IVR Selections</div><div class="card-desc">All-time menu choices</div></div>
      <div class="card-body"><div class="chart-container"><canvas id="r-ivr"></canvas></div></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Agent Performance</div></div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Agent</th><th>Total</th><th>Answered</th><th>Missed</th><th>Answer Rate</th><th>Avg Duration</th></tr></thead>
      <tbody id="r-agents"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<script>
let trendC=null, ivrC=null;
const COLORS=['#22c55e','#ef4444','#f59e0b','#38bdf8','#a78bfa','#fb923c'];

function initDates() {
  const today = new Date();
  const week  = new Date(today); week.setDate(today.getDate()-7);
  document.getElementById('rpt-from').value = week.toISOString().slice(0,10);
  document.getElementById('rpt-to').value   = today.toISOString().slice(0,10);
}

async function loadReports() {
  const from = document.getElementById('rpt-from').value;
  const to   = document.getElementById('rpt-to').value;
  const d = await api(`/reports?from=${from}&to=${to}`);
  const s = d.summary;
  const rate = s.total>0?Math.round(s.answered/s.total*100):0;
  document.getElementById('r-total').textContent = s.total;
  document.getElementById('r-rate').textContent  = rate+'%';
  document.getElementById('r-ans-sub').textContent = s.answered+' answered';
  document.getElementById('r-missed').textContent  = s.missed;
  document.getElementById('r-duration').textContent = formatDuration(s.avg_duration);

  if(trendC) trendC.destroy();
  trendC = new Chart(document.getElementById('r-trend'),{
    type:'line',
    data:{labels:d.trend.map(r=>r.day.slice(5)),datasets:[
      {label:'Total',   data:d.trend.map(r=>r.total),   borderColor:'#38bdf8',backgroundColor:'rgba(56,189,248,.1)',fill:true,tension:.4},
      {label:'Answered',data:d.trend.map(r=>r.answered),borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.1)', fill:true,tension:.4}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,grid:{color:'#1e2535'}},x:{grid:{color:'#1e2535'}}}}
  });

  if(ivrC) ivrC.destroy();
  if(d.ivr.length) {
    ivrC = new Chart(document.getElementById('r-ivr'),{
      type:'doughnut',
      data:{labels:d.ivr.map(r=>r.label),datasets:[{data:d.ivr.map(r=>r.count),backgroundColor:COLORS,borderWidth:0}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},cutout:'55%'}
    });
  }

  const tbody = document.getElementById('r-agents');
  if(!d.agents.length) { tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-4">No agent data</td></tr>'; return; }
  tbody.innerHTML = d.agents.map(a=>{
    const r=a.total>0?Math.round(a.answered/a.total*100):0;
    return `<tr>
      <td class="fw-medium">${a.name}</td><td>${a.total}</td>
      <td><span class="badge bg-success">${a.answered}</span></td>
      <td><span class="badge bg-danger">${a.missed}</span></td>
      <td>${r}%</td>
      <td class="text-muted">${formatDuration(a.avg_duration)}</td>
    </tr>`;
  }).join('');
}

document.getElementById('rpt-load').addEventListener('click', loadReports);

document.getElementById('btn-export-csv').addEventListener('click', () => {
  const from = document.getElementById('rpt-from').value;
  const to   = document.getElementById('rpt-to').value;
  const params = new URLSearchParams({ export: 'csv', from, to });
  window.location.href = BASE + '/api/reports?' + params;
});

initDates();
loadReports();
</script>
<?php }, $user ?? []); ?>
