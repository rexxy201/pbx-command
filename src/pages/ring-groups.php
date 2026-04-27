<?php
render_layout('Ring Groups', 'ring-groups', function() { ?>
<div class="page-header">
  <div>
    <h1>Ring Groups</h1>
    <p class="page-subtitle">Configure ring groups and hunt strategies.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add Ring Group
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Extension</th><th>Strategy</th><th>Ring Time</th><th>Notes</th><th></th></tr></thead>
      <tbody id="rg-tbody"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="rg-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rg-modal-title">Add Ring Group</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="rg-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rg-id">
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Group Name</label>
            <input type="text" class="form-control form-control-sm" id="rg-name" placeholder="Support Team">
          </div>
          <div class="col-6">
            <label class="form-label">Extension Number</label>
            <input type="text" class="form-control form-control-sm" id="rg-ext" placeholder="600">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Ring Strategy</label>
            <select class="form-select form-select-sm" id="rg-strategy">
              <option value="ringall">Ring All</option>
              <option value="hunt">Hunt</option>
              <option value="memoryhunt">Memory Hunt</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Ring Time (seconds)</label>
            <input type="number" class="form-control form-control-sm" id="rg-time" value="20" min="5">
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="rg-notes" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="rg-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="rg-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let rgs=[];
async function load(){
  rgs=await api('/ring-groups');
  const tbody=document.getElementById('rg-tbody');
  if(!rgs.length){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-4">No ring groups</td></tr>';return;}
  tbody.innerHTML=rgs.map(r=>`<tr>
    <td class="fw-medium">${r.name}</td><td class="font-mono">${r.extension_number||'-'}</td>
    <td>${r.strategy}</td><td>${r.ring_time}s</td><td class="td-muted">${r.notes||''}</td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${r.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${r.id},'${r.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('rg-id').value='';['rg-name','rg-ext','rg-notes'].forEach(i=>document.getElementById(i).value='');document.getElementById('rg-time').value=20;document.getElementById('rg-modal-title').textContent='Add Ring Group';openModal('rg-modal');}
function openEdit(id){const r=rgs.find(x=>x.id===id);document.getElementById('rg-id').value=r.id;document.getElementById('rg-name').value=r.name;document.getElementById('rg-ext').value=r.extension_number||'';document.getElementById('rg-strategy').value=r.strategy;document.getElementById('rg-time').value=r.ring_time;document.getElementById('rg-notes').value=r.notes||'';document.getElementById('rg-modal-title').textContent='Edit Ring Group';openModal('rg-modal');}
async function del(id,name){if(!await confirm(`Delete ring group "${name}"?`,'Delete')) return;await api('/ring-groups?id='+id,{method:'DELETE'});toast('Deleted');load();}
document.getElementById('rg-save').addEventListener('click',async()=>{
  const id=document.getElementById('rg-id').value;
  const body={name:document.getElementById('rg-name').value,extensionNumber:document.getElementById('rg-ext').value,strategy:document.getElementById('rg-strategy').value,ringTime:document.getElementById('rg-time').value,notes:document.getElementById('rg-notes').value};
  try{if(id) await api('/ring-groups?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/ring-groups',{method:'POST',body:JSON.stringify(body)});closeModal('rg-modal');toast('Saved');load();}catch(e){toast(e.message,'error');}
});
load();
</script>
<?php }, $user ?? []); ?>
