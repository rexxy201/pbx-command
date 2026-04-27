<?php
render_layout('Extensions', 'extensions', function() { ?>
<div class="page-header">
  <div>
    <h1>Extensions</h1>
    <p class="page-subtitle">Manage SIP extensions and their assignments.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAddExt()">
      <i class="bi bi-plus-lg me-1"></i> Add Extension
    </button>
  </div>
</div>

<div class="card">
  <div class="card-body border-bottom border-dark pb-3">
    <div class="input-group input-group-sm">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" id="ext-search" placeholder="Search extensions...">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Number</th><th>Name</th><th>Type</th><th>Status</th><th>Notes</th><th></th></tr></thead>
      <tbody id="ext-tbody"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="ext-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ext-modal-title">Add Extension</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="ext-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ext-id">
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Number</label>
            <input type="text" class="form-control form-control-sm" id="ext-number" placeholder="200">
          </div>
          <div class="col-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control form-control-sm" id="ext-name" placeholder="John Doe">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Type</label>
            <select class="form-select form-select-sm" id="ext-type">
              <option value="customer_support">Customer Support</option>
              <option value="tech_support">Tech Support</option>
              <option value="supervisor">Supervisor</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Status</label>
            <select class="form-select form-select-sm" id="ext-status">
              <option value="registered">Registered</option>
              <option value="unregistered">Unregistered</option>
            </select>
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="ext-notes" placeholder="Optional notes">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="ext-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="ext-save">Save</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let exts = [];
const typeLabels = {customer_support:'Customer Support',tech_support:'Tech Support',supervisor:'Supervisor'};

async function loadExts() {
  exts = await api('/extensions');
  render();
}
function render() {
  const q = document.getElementById('ext-search').value.toLowerCase();
  const rows = exts.filter(e => !q || (e.number+e.name).toLowerCase().includes(q));
  const tbody = document.getElementById('ext-tbody');
  if (!rows.length) { tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-4">No extensions</td></tr>'; return; }
  tbody.innerHTML = rows.map(e=>`<tr>
    <td class="font-mono fw-medium">${e.number}</td>
    <td>${e.name}</td>
    <td class="text-muted">${typeLabels[e.type]||e.type}</td>
    <td><span class="badge ${e.status==='registered'?'bg-success':'bg-danger'}">${e.status}</span></td>
    <td class="td-muted">${e.notes||''}</td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEditExt(${e.id})" title="Edit"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="delExt(${e.id},'${e.name}')" title="Delete"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAddExt(){document.getElementById('ext-id').value='';document.getElementById('ext-number').value='';document.getElementById('ext-name').value='';document.getElementById('ext-notes').value='';document.getElementById('ext-modal-title').textContent='Add Extension';openModal('ext-modal');}
function openEditExt(id){const e=exts.find(x=>x.id===id);document.getElementById('ext-id').value=e.id;document.getElementById('ext-number').value=e.number;document.getElementById('ext-name').value=e.name;document.getElementById('ext-type').value=e.type;document.getElementById('ext-status').value=e.status;document.getElementById('ext-notes').value=e.notes||'';document.getElementById('ext-modal-title').textContent='Edit Extension';openModal('ext-modal');}
async function delExt(id,name){if(!await confirm(`Delete extension "${name}"?`,'Delete')) return;await api('/extensions?id='+id,{method:'DELETE'});toast('Deleted');loadExts();}
document.getElementById('ext-save').addEventListener('click',async()=>{
  const id=document.getElementById('ext-id').value;
  const body={number:document.getElementById('ext-number').value,name:document.getElementById('ext-name').value,type:document.getElementById('ext-type').value,status:document.getElementById('ext-status').value,notes:document.getElementById('ext-notes').value};
  try{if(id) await api('/extensions?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/extensions',{method:'POST',body:JSON.stringify(body)});closeModal('ext-modal');toast('Saved');loadExts();}catch(e){toast(e.message,'error');}
});
document.getElementById('ext-search').addEventListener('input', render);
loadExts();
</script>
<?php }, $user ?? []); ?>
