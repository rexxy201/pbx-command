<?php
render_layout('PBX Agents', 'pbx-agents', function() { ?>
<div class="page-header">
  <div>
    <h1>PBX Agents</h1>
    <p class="page-subtitle">Manage call center agents and their extensions.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add Agent
    </button>
  </div>
</div>

<div class="card">
  <div class="card-body border-bottom border-dark pb-3">
    <div class="input-group input-group-sm">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" id="ag-search" placeholder="Search agents...">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Extension</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
      <tbody id="ag-tbody"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="ag-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ag-modal-title">Add Agent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="ag-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ag-id">
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control form-control-sm" id="ag-name" placeholder="Full name">
          </div>
          <div class="col-6">
            <label class="form-label">Extension</label>
            <input type="text" class="form-control form-control-sm" id="ag-ext" placeholder="201">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control form-control-sm" id="ag-email" placeholder="agent@company.com">
          </div>
          <div class="col-6">
            <label class="form-label">Role</label>
            <select class="form-select form-select-sm" id="ag-role">
              <option value="agent">Agent</option>
              <option value="supervisor">Supervisor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="ag-notes" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="ag-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="ag-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let agents=[];
async function loadAgents(){
  agents=await api('/pbx-agents');
  renderAgents();
}
function renderAgents(){
  const q=document.getElementById('ag-search').value.toLowerCase();
  const rows=agents.filter(a=>!q||(a.name+(a.extension||'')).toLowerCase().includes(q));
  const tbody=document.getElementById('ag-tbody');
  if(!rows.length){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-4">No agents</td></tr>';return;}
  tbody.innerHTML=rows.map(a=>`<tr>
    <td class="fw-medium">${a.name}</td><td class="font-mono">${a.extension||'-'}</td>
    <td class="td-muted">${a.email||'-'}</td>
    <td><span class="badge bg-secondary">${a.role}</span></td>
    <td><span class="badge ${a.is_active?'bg-success':'bg-secondary'}">${a.is_active?'Active':'Inactive'}</span></td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${a.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${a.id},'${a.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('ag-id').value='';['ag-name','ag-ext','ag-email','ag-notes'].forEach(i=>document.getElementById(i).value='');document.getElementById('ag-role').value='agent';document.getElementById('ag-modal-title').textContent='Add Agent';openModal('ag-modal');}
function openEdit(id){const a=agents.find(x=>x.id===id);document.getElementById('ag-id').value=a.id;document.getElementById('ag-name').value=a.name;document.getElementById('ag-ext').value=a.extension||'';document.getElementById('ag-email').value=a.email||'';document.getElementById('ag-role').value=a.role;document.getElementById('ag-notes').value=a.notes||'';document.getElementById('ag-modal-title').textContent='Edit Agent';openModal('ag-modal');}
async function del(id,name){if(!await confirm(`Remove agent "${name}"?`,'Remove')) return;await api('/pbx-agents?id='+id,{method:'DELETE'});toast('Removed');loadAgents();}
document.getElementById('ag-save').addEventListener('click',async()=>{
  const id=document.getElementById('ag-id').value;
  const body={name:document.getElementById('ag-name').value,extension:document.getElementById('ag-ext').value,email:document.getElementById('ag-email').value,role:document.getElementById('ag-role').value,isActive:true,notes:document.getElementById('ag-notes').value};
  try{if(id) await api('/pbx-agents?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/pbx-agents',{method:'POST',body:JSON.stringify(body)});closeModal('ag-modal');toast('Saved');loadAgents();}catch(e){toast(e.message,'error');}
});
document.getElementById('ag-search').addEventListener('input', renderAgents);
loadAgents();
</script>
<?php }, $user ?? []); ?>
