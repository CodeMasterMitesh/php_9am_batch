<?php
include 'config/connection.php';
include 'includes/nav.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;
if($id){
    $stmt = mysqli_prepare($conn, "SELECT id, firstname, email, status FROM users WHERE id = ? AND type = 'employee' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
}
?>

<style>
/* reuse add_items look */
.form-card { border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); }
.btn-submit { background: linear-gradient(135deg, #198754 0%, #157347 100%); border:none; color:white; }
</style>

<div class="main-content container py-4">
  <div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1><?php echo $id ? 'Edit Employee' : 'Add Employee'; ?></h1>
      <p class="text-muted"><?php echo $id ? 'Update employee information' : 'Create a new employee account'; ?></p>
    </div>
    <a href="employees.php" class="btn btn-secondary">Back to List</a>
  </div>

  <div class="card form-card">
    <div class="card-body">
      <form action="api/save_employee.php" method="POST" id="empForm">
        <input type="hidden" name="id" value="<?php echo $user ? $user['id'] : ''; ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="firstname" class="form-control" required value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label"><?php echo $id ? 'New Password (leave blank to keep)' : 'Password'; ?></label>
            <input type="password" name="password" class="form-control" <?php echo $id ? '' : 'required'; ?> >
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?php echo (isset($user['status']) && $user['status']=='active') ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo (isset($user['status']) && $user['status']=='inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>

          <div class="col-12 mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-submit">Save Employee</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const form = document.getElementById('empForm');
form.addEventListener('submit', ()=>{ /* Could add client validation */ });
</script>
