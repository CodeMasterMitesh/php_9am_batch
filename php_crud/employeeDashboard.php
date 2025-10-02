<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      
      <!-- Logo & Profile -->
      <div class="d-flex align-items-center">
        <a class="navbar-brand fw-bold me-3" href="#"><?php echo $_SESSION['employee']['firstname']; ?> <span style="font-size:12px;">(<?php echo ucfirst($_SESSION['employee']['type']); ?>)</span></a>
        <i class="bi bi-person-circle text-white fs-3"></i>
      </div>

      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menu -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-white" href="menu.php">Menu</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">My Orders</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Profile</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Support</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container my-4 flex-grow-1">
    <h2 class="mb-4">My Orders</h2>

    <!-- Orders Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Order ID</th>
            <th>Item</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <!-- Example static data -->
          <tr>
            <td>#1001</td>
            <td>Pizza</td>
            <td>2</td>
            <td><span class="badge bg-success">Delivered</span></td>
            <td>2025-09-23</td>
          </tr>
          <tr>
            <td>#1002</td>
            <td>Burger</td>
            <td>1</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>2025-09-24</td>
          </tr>
          <tr>
            <td>#1003</td>
            <td>Pasta</td>
            <td>3</td>
            <td><span class="badge bg-danger">Cancelled</span></td>
            <td>2025-09-21</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-primary text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 User Dashboard. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>