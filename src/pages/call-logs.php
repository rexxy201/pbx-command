<?php
render_layout('Call Logs', 'call-logs', function() { ?>
<div class="page-header">
  <div>
    <h1>Call Logs</h1>
    <p class="page-subtitle">Historical record of all inbound and outbound calls.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-outline-secondary btn-sm" id="btn-export-csv">
      <i class="bi bi-download me-1"></i>Download CSV
    </button>
  </div>
</div>

<div class="card">
  <div class="card-body border-bottom border-dark pb-3">
    <div class="input-group input-group-sm">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" id="log-search" placeholder="Search by number, name or destination...">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Time</th><th>Caller</th><th>Destination</th><th>IVR Path</th><th>Status</th><th>Duration</th></tr></thead>
      <tbody id="log-tbody"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
  <div class="card-body border-top border-dark d-flex align-items-center gap-3 py-2">
    <span id="log-count" class="text-muted small"></span>
    <div class="flex-grow-1"></div>
    <button class="btn btn-outline-secondary btn-sm" id="btn-prev" disabled>
      <i class="bi bi-chevron-left"></i> Previous
    </button>
    <button class="btn btn-outline-secondary btn-sm" id="btn-next">
      Next <i class="bi bi-chevron-right"></i>
    </button>
  </div>
</div>

<script>
let logOffset = 0, logTotal = 0, logSearch = '';
const LIMIT = 50;

function statusBadge(s) {
  const map = {answered:'bg-success',missed:'bg-danger',voicemail:'bg-secondary',transferred:'bg-info text-dark',busy:'bg-warning text-dark'};
  return `<span class="badge ${map[s]||'bg-secondary'}">${s}</span>`;
}

async function loadLogs() {
  const params = new URLSearchParams({ search: logSearch, limit: LIMIT, offset: logOffset });
  try {
    const d = await api('/call-logs?' + params);
    logTotal = d.total;
    const tbody = document.getElementById('log-tbody');
    if (!d.logs.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No records found</td></tr>';
    } else {
      tbody.innerHTML = d.logs.map(r => `<tr>
        <td class="font-mono text-muted small">${fmtDate(r.created_at)}</td>
        <td>
          <div class="fw-medium">${r.caller_number || '-'}</div>
          ${r.caller_name ? `<div class="text-muted small">${r.caller_name}</div>` : ''}
        </td>
        <td>${r.destination || '-'}</td>
        <td>${r.ivr_path ? `<span class="badge bg-secondary">${r.ivr_path}</span>` : '-'}</td>
        <td>${statusBadge(r.status || 'unknown')}</td>
        <td class="text-muted">${formatDuration(r.duration)}</td>
      </tr>`).join('');
    }
    const shown = logTotal === 0 ? 0 : `${logOffset+1}–${Math.min(logOffset+LIMIT,logTotal)}`;
    document.getElementById('log-count').textContent = logTotal ? `Showing ${shown} of ${logTotal}` : '0 records';
    document.getElementById('btn-prev').disabled = logOffset === 0;
    document.getElementById('btn-next').disabled = logOffset + LIMIT >= logTotal;
  } catch(e) { console.error(e); }
}

let debounce;
document.getElementById('log-search').addEventListener('input', e => {
  clearTimeout(debounce);
  debounce = setTimeout(() => { logSearch = e.target.value; logOffset = 0; loadLogs(); }, 300);
});
document.getElementById('btn-prev').addEventListener('click', () => { logOffset = Math.max(0, logOffset - LIMIT); loadLogs(); });
document.getElementById('btn-next').addEventListener('click', () => { logOffset += LIMIT; loadLogs(); });

document.getElementById('btn-export-csv').addEventListener('click', () => {
  const params = new URLSearchParams({ export: 'csv', search: logSearch });
  window.location.href = BASE + '/api/call-logs?' + params;
});

loadLogs();
</script>
<?php }, $user ?? []); ?>
