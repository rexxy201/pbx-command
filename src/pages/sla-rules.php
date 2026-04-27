<?php
render_layout('SLA Rules', 'sla-rules', function() { ?>
<div class="page-header">
  <div>
    <h1>SLA Rules</h1>
    <p class="page-subtitle">Define service level agreements and performance thresholds.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add SLA Rule
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Answer Time</th><th>Abandon Rate</th><th>Warning</th><th>Critical</th><th>Status</th><th></th></tr></thead>
      <tbody id="sla-tbody"><tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="sla-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sla-modal-title">Add SLA Rule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="sla-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="sla-id">
        <div class="mb-3">
          <label class="form-label">Rule Name</label>
          <input type="text" class="form-control form-control-sm" id="sla-name" placeholder="Standard SLA">
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Target Answer Time (s)</label>
            <input type="number" class="form-control form-control-sm" id="sla-ans" value="20" min="1">
          </div>
          <div class="col-6">
            <label class="form-label">Target Abandon Rate (%)</label>
            <input type="number" class="form-control form-control-sm" id="sla-abandon" value="5" min="0" max="100" step="0.1">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Warning Threshold (%)</label>
            <input type="number" class="form-control form-control-sm" id="sla-warn" value="80" min="0" max="100">
          </div>
          <div class="col-6">
            <label class="form-label">Critical Threshold (%)</label>
            <input type="number" class="form-control form-control-sm" id="sla-crit" value="70" min="0" max="100">
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="sla-notes" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="sla-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="sla-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let slas=[];
async function load(){
  slas=await api('/sla-rules');
  const tbody=document.getElementById('sla-tbody');
  if(!slas.length){tbody.innerHTML='<tr><td colspan="7" class="text-center text-muted py-4">No SLA rules</td></tr>';return;}
  tbody.innerHTML=slas.map(s=>`<tr>
    <td class="fw-medium">${s.name}</td><td>${s.target_answer_time}s</td><td>${s.target_abandon_rate}%</td>
    <td><span class="badge bg-warning text-dark">${s.threshold_warning}%</span></td>
    <td><span class="badge bg-danger">${s.threshold_critical}%</span></td>
    <td><span class="badge ${s.is_active?'bg-success':'bg-secondary'}">${s.is_active?'Active':'Inactive'}</span></td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${s.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${s.id},'${s.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('sla-id').value='';document.getElementById('sla-name').value='';document.getElementById('sla-ans').value=20;document.getElementById('sla-abandon').value=5;document.getElementById('sla-warn').value=80;document.getElementById('sla-crit').value=70;document.getElementById('sla-notes').value='';document.getElementById('sla-modal-title').textContent='Add SLA Rule';openModal('sla-modal');}
function openEdit(id){const s=slas.find(x=>x.id===id);document.getElementById('sla-id').value=s.id;document.getElementById('sla-name').value=s.name;document.getElementById('sla-ans').value=s.target_answer_time;document.getElementById('sla-abandon').value=s.target_abandon_rate;document.getElementById('sla-warn').value=s.threshold_warning;document.getElementById('sla-crit').value=s.threshold_critical;document.getElementById('sla-notes').value=s.notes||'';document.getElementById('sla-modal-title').textContent='Edit SLA Rule';openModal('sla-modal');}
async function del(id,name){if(!await confirm(`Delete SLA rule "${name}"?`,'Delete')) return;await api('/sla-rules?id='+id,{method:'DELETE'});toast('Deleted');load();}
document.getElementById('sla-save').addEventListener('click',async()=>{
  const id=document.getElementById('sla-id').value;
  const body={name:document.getElementById('sla-name').value,targetAnswerTime:document.getElementById('sla-ans').value,targetAbandonRate:document.getElementById('sla-abandon').value,thresholdWarning:document.getElementById('sla-warn').value,thresholdCritical:document.getElementById('sla-crit').value,notes:document.getElementById('sla-notes').value,isActive:true};
  try{if(id) await api('/sla-rules?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/sla-rules',{method:'POST',body:JSON.stringify(body)});closeModal('sla-modal');toast('Saved');load();}catch(e){toast(e.message,'error');}
});
load();
</script>
<?php }, $user ?? []); ?>
