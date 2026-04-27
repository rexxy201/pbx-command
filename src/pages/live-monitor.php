<?php
render_layout('Live Monitor', 'live-monitor', function() { ?>
<div class="page-header">
  <div>
    <h1>Live Monitor</h1>
    <p class="page-subtitle">Real-time active calls and queue status. Auto-refreshes every 10 seconds.</p>
  </div>
  <div class="page-header-actions">
    <span id="last-refresh" class="text-muted small"></span>
    <button class="btn btn-outline-secondary btn-sm" onclick="refresh()">
      <i class="bi bi-arrow-clockwise me-1"></i>Refresh
    </button>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Active Calls</div>
      <div class="stat-value text-green" id="lm-active">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Queued Callers</div>
      <div class="stat-value text-blue" id="lm-queued">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Agents Available</div>
      <div class="stat-value" id="lm-agents">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Avg Wait Time</div>
      <div class="stat-value text-red" id="lm-wait">—</div>
    </div></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="card h-100">
      <div class="card-header">
        <div class="card-title">Active Calls</div>
        <div class="card-desc">Currently in-progress calls</div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Caller</th><th>Destination</th><th>Duration</th><th>Agent</th><th>Status</th></tr></thead>
          <tbody id="lm-calls"><tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card h-100">
      <div class="card-header">
        <div class="card-title">Queue Stats</div>
        <div class="card-desc">Per-queue breakdown</div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Queue</th><th>Waiting</th><th>Agents</th></tr></thead>
          <tbody id="lm-queues"><tr><td colspan="3" class="text-center text-muted py-4">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
async function refresh() {
  try {
    const d = await api('/active-calls');
    const calls = d.activeCalls || [];
    const qs    = d.queueStats  || [];

    document.getElementById('lm-active').textContent  = calls.length;
    document.getElementById('lm-queued').textContent  = qs.reduce((s,q) => s+(q.waiting||0), 0);
    document.getElementById('lm-agents').textContent  = qs.reduce((s,q) => s+(q.available||0), 0);
    const maxWait = qs.reduce((m,q) => Math.max(m, q.avgWait||0), 0);
    document.getElementById('lm-wait').textContent = maxWait ? formatDuration(maxWait) : '—';

    const callsEl = document.getElementById('lm-calls');
    if (!calls.length) {
      callsEl.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No active calls</td></tr>';
    } else {
      callsEl.innerHTML = calls.map(c=>`<tr>
        <td class="fw-medium">${c.callerNumber||c.caller||'Unknown'}</td>
        <td>${c.destination||'-'}</td>
        <td class="font-mono">${formatDuration(c.duration||0)}</td>
        <td class="td-muted">${c.agent||'-'}</td>
        <td><span class="badge bg-success">Active</span></td>
      </tr>`).join('');
    }

    const qEl = document.getElementById('lm-queues');
    if (!qs.length) {
      qEl.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No queue data</td></tr>';
    } else {
      qEl.innerHTML = qs.map(q=>`<tr>
        <td class="fw-medium">${q.name||q.queue||'-'}</td>
        <td>${q.waiting||0}</td>
        <td>${q.available||0}</td>
      </tr>`).join('');
    }

    document.getElementById('last-refresh').textContent = 'Updated ' + new Date().toLocaleTimeString();
  } catch(e) {
    console.error(e);
  }
}

startLivePolling(refresh, 10000);
</script>
<?php }, $user ?? []); ?>
