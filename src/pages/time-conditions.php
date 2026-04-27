<?php
render_layout('Time Rules', 'time-conditions', function() { ?>
<div class="page-header">
  <div>
    <h1>Time Rules</h1>
    <p class="page-subtitle">Set business hours and time-based call routing conditions.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add Time Rule
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Timezone</th><th>Hours</th><th>Days</th><th>Status</th><th></th></tr></thead>
      <tbody id="tc-tbody"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="tc-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tc-modal-title">Add Time Condition</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="tc-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="tc-id">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" class="form-control form-control-sm" id="tc-name" placeholder="Business Hours">
        </div>
        <div class="mb-3">
          <label class="form-label">Timezone</label>
          <select class="form-select form-select-sm" id="tc-tz">
            <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
            <option value="Africa/Abuja">Africa/Abuja (WAT)</option>
            <option value="UTC">UTC</option>
            <option value="Europe/London">Europe/London (GMT)</option>
            <option value="America/New_York">America/New_York (EST)</option>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Open Time</label>
            <input type="time" class="form-control form-control-sm" id="tc-open" value="08:00">
          </div>
          <div class="col-6">
            <label class="form-label">Close Time</label>
            <input type="time" class="form-control form-control-sm" id="tc-close" value="18:00">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Open Days</label>
          <input type="text" class="form-control form-control-sm" id="tc-days" placeholder="Mon-Fri" value="Mon-Fri">
        </div>
        <div class="mb-0">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="tc-notes" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="tc-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="tc-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let tcs=[];
async function load(){
  tcs=await api('/time-conditions');
  const tbody=document.getElementById('tc-tbody');
  if(!tcs.length){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-4">No time conditions</td></tr>';return;}
  tbody.innerHTML=tcs.map(t=>`<tr>
    <td class="fw-medium">${t.name}</td><td class="td-muted">${t.timezone}</td>
    <td>${t.open_time} – ${t.close_time}</td><td>${t.open_days}</td>
    <td><span class="badge ${t.is_active?'bg-success':'bg-secondary'}">${t.is_active?'Active':'Inactive'}</span></td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${t.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${t.id},'${t.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('tc-id').value='';document.getElementById('tc-name').value='';document.getElementById('tc-open').value='08:00';document.getElementById('tc-close').value='18:00';document.getElementById('tc-days').value='Mon-Fri';document.getElementById('tc-notes').value='';document.getElementById('tc-modal-title').textContent='Add Time Condition';openModal('tc-modal');}
function openEdit(id){const t=tcs.find(x=>x.id===id);document.getElementById('tc-id').value=t.id;document.getElementById('tc-name').value=t.name;document.getElementById('tc-tz').value=t.timezone;document.getElementById('tc-open').value=t.open_time;document.getElementById('tc-close').value=t.close_time;document.getElementById('tc-days').value=t.open_days;document.getElementById('tc-notes').value=t.notes||'';document.getElementById('tc-modal-title').textContent='Edit Time Condition';openModal('tc-modal');}
async function del(id,name){if(!await confirm(`Delete time condition "${name}"?`,'Delete')) return;await api('/time-conditions?id='+id,{method:'DELETE'});toast('Deleted');load();}
document.getElementById('tc-save').addEventListener('click',async()=>{
  const id=document.getElementById('tc-id').value;
  const body={name:document.getElementById('tc-name').value,timezone:document.getElementById('tc-tz').value,openTime:document.getElementById('tc-open').value,closeTime:document.getElementById('tc-close').value,openDays:document.getElementById('tc-days').value,notes:document.getElementById('tc-notes').value,isActive:true};
  try{if(id) await api('/time-conditions?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/time-conditions',{method:'POST',body:JSON.stringify(body)});closeModal('tc-modal');toast('Saved');load();}catch(e){toast(e.message,'error');}
});
load();
</script>
<?php }, $user ?? []); ?>
