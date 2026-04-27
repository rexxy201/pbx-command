<?php
render_layout('IVR Menus', 'ivr-menus', function() { ?>
<div class="page-header">
  <div>
    <h1>IVR Menus</h1>
    <p class="page-subtitle">Configure Interactive Voice Response menus and option routing.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add IVR Menu
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Timeout</th><th>Invalid Retries</th><th>Description</th><th></th></tr></thead>
      <tbody id="ivr-tbody"><tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="ivr-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ivr-modal-title">Add IVR Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="ivr-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ivr-id">
        <div class="mb-3">
          <label class="form-label">Menu Name</label>
          <input type="text" class="form-control form-control-sm" id="ivr-name" placeholder="Main Menu">
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Timeout (seconds)</label>
            <input type="number" class="form-control form-control-sm" id="ivr-timeout" value="5" min="1" max="30">
          </div>
          <div class="col-6">
            <label class="form-label">Invalid Retry Count</label>
            <input type="number" class="form-control form-control-sm" id="ivr-retries" value="3" min="1" max="10">
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label">Description</label>
          <textarea class="form-control form-control-sm" id="ivr-desc" rows="3" placeholder="Purpose of this IVR menu..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="ivr-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="ivr-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let ivrs=[];
async function load(){
  ivrs=await api('/ivr-menus');
  const tbody=document.getElementById('ivr-tbody');
  if(!ivrs.length){tbody.innerHTML='<tr><td colspan="5" class="text-center text-muted py-4">No IVR menus configured</td></tr>';return;}
  tbody.innerHTML=ivrs.map(m=>`<tr>
    <td class="fw-medium">${m.name}</td><td>${m.timeout}s</td><td>${m.invalid_retry_count}</td>
    <td class="td-muted">${m.description||''}</td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${m.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${m.id},'${m.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('ivr-id').value='';document.getElementById('ivr-name').value='';document.getElementById('ivr-timeout').value=5;document.getElementById('ivr-retries').value=3;document.getElementById('ivr-desc').value='';document.getElementById('ivr-modal-title').textContent='Add IVR Menu';openModal('ivr-modal');}
function openEdit(id){const m=ivrs.find(x=>x.id===id);document.getElementById('ivr-id').value=m.id;document.getElementById('ivr-name').value=m.name;document.getElementById('ivr-timeout').value=m.timeout;document.getElementById('ivr-retries').value=m.invalid_retry_count;document.getElementById('ivr-desc').value=m.description||'';document.getElementById('ivr-modal-title').textContent='Edit IVR Menu';openModal('ivr-modal');}
async function del(id,name){if(!await confirm(`Delete IVR menu "${name}"?`,'Delete')) return;await api('/ivr-menus?id='+id,{method:'DELETE'});toast('Deleted');load();}
document.getElementById('ivr-save').addEventListener('click',async()=>{
  const id=document.getElementById('ivr-id').value;
  const body={name:document.getElementById('ivr-name').value,timeout:document.getElementById('ivr-timeout').value,invalidRetryCount:document.getElementById('ivr-retries').value,description:document.getElementById('ivr-desc').value};
  try{if(id) await api('/ivr-menus?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/ivr-menus',{method:'POST',body:JSON.stringify(body)});closeModal('ivr-modal');toast('Saved');load();}catch(e){toast(e.message,'error');}
});
load();
</script>
<?php }, $user ?? []); ?>
