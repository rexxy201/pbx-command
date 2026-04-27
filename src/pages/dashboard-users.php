<?php
render_layout('User Management', 'dashboard-users', function() { ?>
<div class="page-header">
  <div>
    <h1>User Management</h1>
    <p class="page-subtitle">Manage dashboard users and their access roles.</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary btn-sm" onclick="openAddUser()">
      <i class="bi bi-plus-lg me-1"></i> Add User
    </button>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Total Users</div>
      <div class="stat-value" id="u-total">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Active</div>
      <div class="stat-value text-green" id="u-active">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Admins</div>
      <div class="stat-value text-blue" id="u-admins">—</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card h-100"><div class="card-body">
      <div class="stat-label">Managers</div>
      <div class="stat-value" id="u-mgrs">—</div>
    </div></div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th></th></tr></thead>
      <tbody id="u-tbody"><tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="user-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="user-modal-title">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-close-modal="user-modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="u-id">
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control form-control-sm" id="u-name" placeholder="Jane Smith">
          </div>
          <div class="col-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control form-control-sm" id="u-email" placeholder="jane@company.com">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password <span id="u-pw-hint" class="text-muted small">(leave blank to keep current)</span></label>
          <input type="password" class="form-control form-control-sm" id="u-password" placeholder="New password" autocomplete="new-password">
        </div>
        <div class="row g-3 mb-0">
          <div class="col-6">
            <label class="form-label">Role</label>
            <select class="form-select form-select-sm" id="u-role">
              <option value="employee">Employee</option>
              <option value="manager">Manager</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Status</label>
            <select class="form-select form-select-sm" id="u-status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-close-modal="user-modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="u-save">Save</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<script>
const roleBadge={admin:'bg-primary',manager:'bg-info text-dark',employee:'bg-success'};
let users=[];
async function loadUsers(){
  users=await api('/dashboard-users');
  document.getElementById('u-total').textContent=users.length;
  document.getElementById('u-active').textContent=users.filter(u=>u.status==='active').length;
  document.getElementById('u-admins').textContent=users.filter(u=>u.role==='admin').length;
  document.getElementById('u-mgrs').textContent=users.filter(u=>u.role==='manager').length;
  const tbody=document.getElementById('u-tbody');
  if(!users.length){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-4">No users. Click Add User to get started.</td></tr>';return;}
  tbody.innerHTML=users.map(u=>`<tr>
    <td class="fw-medium">${u.name}</td><td class="td-muted">${u.email}</td>
    <td><span class="badge ${roleBadge[u.role]||'bg-secondary'}">${u.role.charAt(0).toUpperCase()+u.role.slice(1)}</span></td>
    <td><span class="badge ${u.status==='active'?'bg-success':'bg-secondary'}">${u.status}</span></td>
    <td class="td-muted small">${u.created_at?new Date(u.created_at).toLocaleDateString():'-'}</td>
    <td class="text-end">
      <button class="btn-icon" onclick="openEditUser(${u.id})"><i class="bi bi-pencil"></i></button>
      <button class="btn-icon btn-danger-icon" onclick="delUser(${u.id},'${u.name}')"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`).join('');
}
function openAddUser(){document.getElementById('u-id').value='';document.getElementById('u-name').value='';document.getElementById('u-email').value='';document.getElementById('u-password').value='';document.getElementById('u-role').value='employee';document.getElementById('u-status').value='active';document.getElementById('user-modal-title').textContent='Add User';document.getElementById('u-pw-hint').style.display='none';openModal('user-modal');}
function openEditUser(id){const u=users.find(x=>x.id===id);document.getElementById('u-id').value=u.id;document.getElementById('u-name').value=u.name;document.getElementById('u-email').value=u.email;document.getElementById('u-password').value='';document.getElementById('u-role').value=u.role;document.getElementById('u-status').value=u.status;document.getElementById('user-modal-title').textContent='Edit User';document.getElementById('u-pw-hint').style.display='';openModal('user-modal');}
async function delUser(id,name){if(!await confirm(`Remove ${name} from dashboard?`,'Remove')) return;await api('/dashboard-users?id='+id,{method:'DELETE'});toast('Removed');loadUsers();}
document.getElementById('u-save').addEventListener('click',async()=>{
  const id=document.getElementById('u-id').value;
  const pw=document.getElementById('u-password').value;
  const body={name:document.getElementById('u-name').value,email:document.getElementById('u-email').value,role:document.getElementById('u-role').value,status:document.getElementById('u-status').value};
  if(pw) body.password=pw;
  try{
    if(id) await api('/dashboard-users?id='+id,{method:'PUT',body:JSON.stringify(body)});
    else await api('/dashboard-users',{method:'POST',body:JSON.stringify(body)});
    closeModal('user-modal');toast('Saved');loadUsers();
  }catch(e){toast(e.message,'error');}
});
loadUsers();
</script>
<?php }, $user ?? []); ?>
