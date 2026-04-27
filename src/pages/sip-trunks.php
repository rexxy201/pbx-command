<?php
render_layout('SIP Trunks', 'sip-trunks', function() { ?>
<div class="page-header">
  <div>
    <h1>SIP Trunks</h1>
    <p class="page-subtitle">Manage SIP trunk connections and carriers.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add Trunk
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Host</th><th>Port</th><th>Username</th><th>Codecs</th><th>Status</th><th>Max Ch.</th><th></th></tr></thead>
      <tbody id="trunk-tbody"><tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="trunk-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="trunk-modal-title">Add SIP Trunk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="trunk-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="t-id">
        <div class="row g-3 mb-3">
          <div class="col-8">
            <label class="form-label">Trunk Name</label>
            <input type="text" class="form-control form-control-sm" id="t-name" placeholder="MTN SIP">
          </div>
          <div class="col-4">
            <label class="form-label">Status</label>
            <select class="form-select form-select-sm" id="t-status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-8">
            <label class="form-label">Host / IP</label>
            <input type="text" class="form-control form-control-sm" id="t-host" placeholder="sip.provider.com">
          </div>
          <div class="col-4">
            <label class="form-label">Port</label>
            <input type="number" class="form-control form-control-sm" id="t-port" value="5060">
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Username</label>
            <input type="text" class="form-control form-control-sm" id="t-username" placeholder="sip_user">
          </div>
          <div class="col-6">
            <label class="form-label">Max Channels</label>
            <input type="number" class="form-control form-control-sm" id="t-maxch" value="30">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Codecs</label>
          <input type="text" class="form-control form-control-sm" id="t-codecs" placeholder="g729,ulaw,alaw">
        </div>
        <div class="mb-0">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="t-notes" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="trunk-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="t-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
let trunks=[];
async function loadTrunks(){
  trunks=await api('/sip-trunks');
  const tbody=document.getElementById('trunk-tbody');
  if(!trunks.length){tbody.innerHTML='<tr><td colspan="8" class="text-center text-muted py-4">No trunks configured</td></tr>';return;}
  tbody.innerHTML=trunks.map(t=>`<tr>
    <td class="fw-medium">${t.name}</td><td class="font-mono">${t.host}</td><td class="font-mono">${t.port}</td>
    <td class="td-muted">${t.username||'-'}</td><td class="td-muted small">${t.codecs||'-'}</td>
    <td><span class="badge ${t.status==='active'?'bg-success':'bg-secondary'}">${t.status}</span></td>
    <td>${t.max_channels}</td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${t.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${t.id},'${t.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('t-id').value='';['t-name','t-host','t-username','t-codecs','t-notes'].forEach(i=>document.getElementById(i).value='');document.getElementById('t-port').value=5060;document.getElementById('t-maxch').value=30;document.getElementById('t-status').value='active';document.getElementById('trunk-modal-title').textContent='Add SIP Trunk';openModal('trunk-modal');}
function openEdit(id){const t=trunks.find(x=>x.id===id);document.getElementById('t-id').value=t.id;document.getElementById('t-name').value=t.name;document.getElementById('t-host').value=t.host;document.getElementById('t-port').value=t.port;document.getElementById('t-username').value=t.username||'';document.getElementById('t-maxch').value=t.max_channels;document.getElementById('t-codecs').value=t.codecs||'';document.getElementById('t-status').value=t.status;document.getElementById('t-notes').value=t.notes||'';document.getElementById('trunk-modal-title').textContent='Edit SIP Trunk';openModal('trunk-modal');}
async function del(id,name){if(!await confirm(`Delete trunk "${name}"?`,'Delete')) return;await api('/sip-trunks?id='+id,{method:'DELETE'});toast('Deleted');loadTrunks();}
document.getElementById('t-save').addEventListener('click',async()=>{
  const id=document.getElementById('t-id').value;
  const body={name:document.getElementById('t-name').value,host:document.getElementById('t-host').value,port:document.getElementById('t-port').value,username:document.getElementById('t-username').value,status:document.getElementById('t-status').value,codecs:document.getElementById('t-codecs').value,maxChannels:document.getElementById('t-maxch').value,notes:document.getElementById('t-notes').value};
  try{if(id) await api('/sip-trunks?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/sip-trunks',{method:'POST',body:JSON.stringify(body)});closeModal('trunk-modal');toast('Saved');loadTrunks();}catch(e){toast(e.message,'error');}
});
loadTrunks();
</script>
<?php }, $user ?? []); ?>
