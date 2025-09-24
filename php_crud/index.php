<?php
    include 'config/connection.php';
    // debug($_SESSION);
    // exit;
    if(!$_SESSION['admin']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      
      <!-- Logo & User Profile -->
      <div class="d-flex align-items-center">
        <a class="navbar-brand fw-bold me-3" href="index.php"><?php echo $_SESSION['admin']['firstname']; ?> </a>
        <i class="bi bi-person-circle text-white fs-3"></i>
      </div>

      <!-- Toggler for mobile -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menu -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Students List</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Items</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

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
            <a href="#" class="btn btn-danger btn-sm">View</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 MyAdmin Dashboard. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
