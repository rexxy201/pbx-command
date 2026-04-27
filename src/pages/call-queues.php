<?php
render_layout('Call Queues', 'call-queues', function() { ?>
<div class="page-header">
  <div>
    <h1>Call Queues</h1>
    <p class="page-subtitle">Configure call distribution queues and strategies.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAdd()">
      <i class="bi bi-plus-lg me-1"></i> Add Queue
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Strategy</th><th>Max Wait</th><th>Notes</th><th></th></tr></thead>
      <tbody id="q-tbody"><tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="q-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="q-modal-title">Add Queue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="q-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="q-id">
        <div class="mb-3">
          <label class="form-label">Queue Name</label>
          <input type="text" class="form-control form-control-sm" id="q-name" placeholder="Support Queue">
        </div>
        <div class="row g-3 mb-0">
          <div class="col-6">
            <label class="form-label">Ring Strategy</label>
            <select class="form-select form-select-sm" id="q-strategy">
              <option value="ringall">Ring All</option>
              <option value="leastrecent">Least Recent</option>
              <option value="fewestcalls">Fewest Calls</option>
              <option value="random">Random</option>
              <option value="rrmemory">Round Robin</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Max Wait (seconds)</label>
            <input type="number" class="form-control form-control-sm" id="q-wait" value="60" min="10">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control form-control-sm" id="q-notes" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="q-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="q-save">Save</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
const strategyLabels={ringall:'Ring All',leastrecent:'Least Recent',fewestcalls:'Fewest Calls',random:'Random',rrmemory:'Round Robin'};
let queues=[];
async function load(){
  queues=await api('/call-queues');
  const tbody=document.getElementById('q-tbody');
  if(!queues.length){tbody.innerHTML='<tr><td colspan="5" class="text-center text-muted py-4">No queues configured</td></tr>';return;}
  tbody.innerHTML=queues.map(q=>`<tr>
    <td class="fw-medium">${q.name}</td><td>${strategyLabels[q.strategy]||q.strategy}</td>
    <td>${q.max_wait_time}s</td><td class="td-muted">${q.notes||''}</td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEdit(${q.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="del(${q.id},'${q.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAdd(){document.getElementById('q-id').value='';document.getElementById('q-name').value='';document.getElementById('q-wait').value=60;document.getElementById('q-notes').value='';document.getElementById('q-modal-title').textContent='Add Queue';openModal('q-modal');}
function openEdit(id){const q=queues.find(x=>x.id===id);document.getElementById('q-id').value=q.id;document.getElementById('q-name').value=q.name;document.getElementById('q-strategy').value=q.strategy;document.getElementById('q-wait').value=q.max_wait_time;document.getElementById('q-notes').value=q.notes||'';document.getElementById('q-modal-title').textContent='Edit Queue';openModal('q-modal');}
async function del(id,name){if(!await confirm(`Delete queue "${name}"?`,'Delete')) return;await api('/call-queues?id='+id,{method:'DELETE'});toast('Deleted');load();}
document.getElementById('q-save').addEventListener('click',async()=>{
  const id=document.getElementById('q-id').value;
  const body={name:document.getElementById('q-name').value,strategy:document.getElementById('q-strategy').value,maxWaitTime:document.getElementById('q-wait').value,notes:document.getElementById('q-notes').value};
  try{if(id) await api('/call-queues?id='+id,{method:'PUT',body:JSON.stringify(body)});else await api('/call-queues',{method:'POST',body:JSON.stringify(body)});closeModal('q-modal');toast('Saved');load();}catch(e){toast(e.message,'error');}
});
load();
</script>
<?php }, $user ?? []); ?>
