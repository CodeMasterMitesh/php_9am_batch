<?php
include 'config/connection.php';
include 'includes/nav.php';
?>

<div class="main-content container py-4">
  <div class="page-header">
    <div>
      <h1><i class="bi bi-person-workspace me-2"></i>Employees</h1>
      <p class="text-muted">Manage employee accounts</p>
    </div>
    <div class="d-flex gap-2">
  <a href="employee_form.php" class="btn btn-add"><i class="bi bi-plus-circle me-1"></i> Add Employee</a>
    </div>
  </div>

  <div class="card card-list">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-3">
        <div class="d-flex gap-2">
          <input id="searchInput" class="form-control" placeholder="Search by name or email..." style="min-width:300px">
          <select id="perPage" class="form-select" style="width:110px">
            <option value="5">5 / page</option>
            <option value="10" selected>10 / page</option>
            <option value="25">25 / page</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0" id="employeesTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="employeesTbody">
            <tr><td colspan="5" class="text-center py-4">Loading...</td></tr>
          </tbody>
        </table>
      </div>

      <nav class="mt-3" aria-label="Page navigation">
        <ul class="pagination" id="pagination"></ul>
      </nav>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const tbody = document.getElementById('employeesTbody');
const pagination = document.getElementById('pagination');
const searchInput = document.getElementById('searchInput');
const perPageSelect = document.getElementById('perPage');
let currentPage = 1;

function fetchEmployees(page = 1){
  const q = encodeURIComponent(searchInput.value.trim());
  const per_page = perPageSelect.value;
  fetch(`api/get_employees.php?page=${page}&per_page=${per_page}&search=${q}`)
    .then(r => r.json())
    .then(data => {
      if(!data.success){
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">${data.message||'Error'}</td></tr>`;
        return;
      }
      const users = data.data;
      if(users.length === 0){
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">No employees found.</td></tr>`;
      } else {
        tbody.innerHTML = users.map(u => `
          <tr data-id="${u.id}">
            <td>${u.id}</td>
            <td>${escapeHtml(u.firstname)}</td>
            <td>${escapeHtml(u.email)}</td>
            <td><span class="badge ${u.status === 'active' ? 'bg-success' : 'bg-secondary'}">${u.status}</span></td>
            <td>
              <a href="employee_form.php?id=${u.id}" class="btn btn-sm btn-primary">Edit</a>
              <button class="btn btn-sm btn-outline-secondary btn-toggle-status ms-1">${u.status==='active'?'Deactivate':'Activate'}</button>
              <button class="btn btn-sm btn-danger btn-delete ms-1">Delete</button>
            </td>
          </tr>
        `).join('');
      }
      renderPagination(data.page, data.per_page, data.total);
    }).catch(err => { tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">Request error</td></tr>`; console.error(err); });
}

function renderPagination(page, per_page, total){
  const totalPages = Math.ceil(total / per_page) || 1;
  let html = '';
  const createPageItem = (p, label=null, disabled=false, active=false) => `<li class="page-item ${disabled? 'disabled':''} ${active? 'active':''}"><a class="page-link" href="#" data-page="${p}">${label||p}</a></li>`;
  html += createPageItem(1,'«', page===1, false);
  const start = Math.max(1, page-2);
  const end = Math.min(totalPages, page+2);
  for(let p=start;p<=end;p++) html += createPageItem(p,null,false,p===page);
  html += createPageItem(totalPages,'»', page===totalPages, false);
  pagination.innerHTML = html;
}

pagination.addEventListener('click', e=>{ e.preventDefault(); const a = e.target.closest('a.page-link'); if(!a) return; const p = parseInt(a.dataset.page); if(!isNaN(p)){ currentPage = p; fetchEmployees(p); } });

tbody.addEventListener('click', e=>{
  const tr = e.target.closest('tr'); if(!tr) return; const id = tr.dataset.id;
  if(e.target.classList.contains('btn-toggle-status')){
    if(!confirm('Change status for this employee?')) return;
    fetch('api/toggle_user_status.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ id }) })
      .then(r=>r.json()).then(res=>{ if(res.success) fetchEmployees(currentPage); else alert(res.message||'Failed'); }).catch(console.error);
  }
  if(e.target.classList.contains('btn-delete')){
    if(!confirm('Delete this employee?')) return;
    fetch('api/delete_user.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ id }) })
      .then(r=>r.json()).then(res=>{ if(res.success) fetchEmployees(currentPage); else alert(res.message||'Failed'); }).catch(console.error);
  }
});

let searchTimer;
searchInput.addEventListener('input', ()=>{ clearTimeout(searchTimer); searchTimer=setTimeout(()=>{ currentPage=1; fetchEmployees(1); }, 300); });
perPageSelect.addEventListener('change', ()=>{ currentPage=1; fetchEmployees(1); });
function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

fetchEmployees(1);
</script>
