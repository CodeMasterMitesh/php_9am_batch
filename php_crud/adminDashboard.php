<?php include 'includes/nav.php'; ?>

  <!-- Main Content -->
  <div class="container my-4 flex-grow-1">
    <h1 class="mb-4">Welcome to Admin Dashboard</h1>
    <div class="row g-4">

      <!-- Students Card -->
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-people fs-1 text-primary"></i>
            <h5 class="card-title mt-2">Students</h5>
            <p class="card-text">Manage all student records.</p>
            <a href="#" class="btn btn-primary btn-sm">View</a>
          </div>
        </div>
      </div>

      <!-- Menu Card -->
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-list fs-1 text-success"></i>
            <h5 class="card-title mt-2">Menu</h5>
            <p class="card-text">Manage application menus.</p>
            <a href="#" class="btn btn-success btn-sm">View</a>
          </div>
        </div>
      </div>

      <!-- Orders Card -->
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-basket fs-1 text-warning"></i>
            <h5 class="card-title mt-2">Orders</h5>
            <p class="card-text">Track and manage orders.</p>
            <a href="#" class="btn btn-warning btn-sm text-white">View</a>
          </div>
        </div>
      </div>

      <!-- Items Card -->
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-box-seam fs-1 text-danger"></i>
            <h5 class="card-title mt-2">Items</h5>
            <p class="card-text">Add, update, or remove items.</p>
            <a href="items.php" class="btn btn-danger btn-sm">View</a>
          </div>
        </div>
      </div>

    </div>
  </div>
<?php include 'includes/footer.php'; ?>