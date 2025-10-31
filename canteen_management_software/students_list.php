<?php
include 'config/connection.php';
include 'includes/nav.php';
?>

<div class="main-content container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Students / Users</h3>
    <div class="d-flex gap-2">
      <input id="searchInput" class="form-control" placeholder="Search by name or email..." style="min-width:300px">
      <select id="perPage" class="form-select" style="width:110px">
        <option value="5">5 / page</option>
        <option value="10" selected>10 / page</option>
        <option value="25">25 / page</option>
      </select>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0" id="usersTable">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="usersTbody">
            <tr><td colspan="6" class="text-center py-4">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <nav class="mt-3" aria-label="Page navigation">
    <ul class="pagination" id="pagination"></ul>
  </nav>
</div>

<?php include 'includes/footer.php'; ?>

<script>
  const usersTbody = document.getElementById('usersTbody');
  const pagination = document.getElementById('pagination');
  const searchInput = document.getElementById('searchInput');
  const perPageSelect = document.getElementById('perPage');

  let currentPage = 1;

  function fetchUsers(page = 1){
    const q = encodeURIComponent(searchInput.value.trim());
    const per_page = perPageSelect.value;
    fetch(`api/get_students.php?page=${page}&per_page=${per_page}&search=${q}`)
      .then(r => r.json())
      .then(data => {
        if(!data.success){
          usersTbody.innerHTML = `<tr><td colspan="6" class="text-center py-4">${data.message || 'Error'}</td></tr>`;
          return;
        }

        // render rows
        const users = data.data;
        if(users.length === 0){
          usersTbody.innerHTML = `<tr><td colspan="6" class="text-center py-4">No users found.</td></tr>`;
        } else {
          usersTbody.innerHTML = users.map(u => `
            <tr data-id="${u.id}">
              <td>${u.id}</td>
              <td>${escapeHtml(u.firstname || '')}</td>
              <td>${escapeHtml(u.email || '')}</td>
              <td>${escapeHtml(u.type || '')}</td>
              <td><span class="badge ${u.status === 'active' ? 'bg-success' : 'bg-secondary'}">${u.status}</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary btn-toggle-status">${u.status === 'active' ? 'Deactivate' : 'Activate'}</button>
                <button class="btn btn-sm btn-outline-danger btn-delete ms-2">Delete</button>
              </td>
            </tr>
          `).join('');
        }

        // render pagination
        renderPagination(data.page, data.per_page, data.total);
      })
      .catch(err => {
        usersTbody.innerHTML = `<tr><td colspan="6" class="text-center py-4">Request error</td></tr>`;
        console.error(err);
      });
  }

  function renderPagination(page, per_page, total){
    const totalPages = Math.ceil(total / per_page) || 1;
    let html = '';
    const createPageItem = (p, label = null, disabled = false, active = false) => {
      return `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${label || p}</a></li>`;
    };

    html += createPageItem(1, '«', page === 1, false);
    const start = Math.max(1, page - 2);
    const end = Math.min(totalPages, page + 2);
    for(let p = start; p <= end; p++){
      html += createPageItem(p, null, false, p === page);
    }
    html += createPageItem(totalPages, '»', page === totalPages, false);
    pagination.innerHTML = html;
  }

  // event delegation for pagination and actions
  pagination.addEventListener('click', (e) => {
    e.preventDefault();
    const a = e.target.closest('a.page-link');
    if(!a) return;
    const page = parseInt(a.dataset.page);
    if(!isNaN(page)){
      currentPage = page;
      fetchUsers(page);
    }
  });

  usersTbody.addEventListener('click', (e) => {
    const tr = e.target.closest('tr');
    if(!tr) return;
    const id = tr.dataset.id;
    if(e.target.classList.contains('btn-toggle-status')){
      // toggle status
      if(!confirm('Change status for this user?')) return;
      fetch('api/toggle_user_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      }).then(r => r.json()).then(res => {
        if(res.success){
          fetchUsers(currentPage);
        } else alert(res.message || 'Failed');
      }).catch(err => console.error(err));
    }

    if(e.target.classList.contains('btn-delete')){
      if(!confirm('Delete this user? This action cannot be undone.')) return;
      fetch('api/delete_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      }).then(r => r.json()).then(res => {
        if(res.success){
          fetchUsers(currentPage);
        } else alert(res.message || 'Failed to delete');
      }).catch(err => console.error(err));
    }
  });

  // search debounce
  let searchTimer;
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentPage = 1; fetchUsers(1); }, 350);
  });
  perPageSelect.addEventListener('change', () => { currentPage = 1; fetchUsers(1); });

  // helper to escape html
  function escapeHtml(str){
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // initial load
  fetchUsers(1);
</script>
