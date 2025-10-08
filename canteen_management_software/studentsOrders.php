<?php
include 'config/connection.php';
if (!$_SESSION['student']) {
    echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
    </script>";
}

// Fetch logged-in user orders
$uid = $_SESSION['student']['id'];
$sql = "SELECT o.*, i.name AS item_name, i.image 
        FROM `order` o 
        JOIN items i ON o.pid = i.id 
        WHERE o.uid = '$uid'
        ORDER BY o.id DESC";
$query = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .navbar {
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar-brand {
      font-size: 1.3rem;
      letter-spacing: 0.5px;
    }
    h2 {
      font-weight: 600;
      color: #343a40;
    }
    .order-card {
      border: none;
      border-radius: 16px;
      overflow: hidden;
      transition: all 0.3s ease;
      background: #fff;
    }
    .order-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }
    .order-card img {
      height: 140px;
      object-fit: cover;
      border-radius: 12px;
    }
    .badge-status {
      font-size: 0.8rem;
      padding: 6px 10px;
      border-radius: 12px;
    }
    .status-pending {
      background-color: #ffc107;
      color: #000;
    }
    .status-preparing {
      background-color: #17a2b8;
    }
    .status-delivered {
      background-color: #28a745;
    }
    .status-cancelled {
      background-color: #dc3545;
    }
    footer {
      background: #0d6efd;
      font-size: 0.9rem;
      letter-spacing: 0.4px;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container-fluid">
      <div class="d-flex align-items-center">
        <a class="navbar-brand fw-bold me-2" href="#">
          <?php echo $_SESSION['student']['firstname']; ?> 
          <span class="text-secondary" style="font-size:12px;">(<?php echo ucfirst($_SESSION['student']['type']); ?>)</span>
        </a>
        <i class="bi bi-person-circle text-white fs-3"></i>
      </div>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-white" href="menu.php">Menu</a></li>
          <li class="nav-item"><a class="nav-link active text-white fw-semibold" href="studentOrders.php">My Orders</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Profile</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Support</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Orders Section -->
  <div class="container py-5">
    <h2 class="text-center mb-5">🧾 My Orders</h2>

    <?php if (mysqli_num_rows($query) > 0): ?>
      <div class="row g-4">
        <?php while ($row = mysqli_fetch_assoc($query)): ?>
          <div class="col-lg-4 col-md-6">
            <div class="card order-card p-3 shadow-sm">
              <img src="<?php echo $row['image']; ?>" class="mb-3" alt="Food Image">
              <h5 class="fw-semibold mb-1"><?php echo $row['item_name']; ?></h5>
              <p class="text-muted small mb-1">Qty: <?php echo $row['qty']; ?></p>
              <p class="mb-1"><strong>Amount:</strong> ₹<?php echo $row['amt']; ?></p>
              <p class="text-muted small mb-2">
                <strong>Date:</strong> <?php echo isset($row['created_at']) ? date('d M Y, h:i A', strtotime($row['created_at'])) : 'N/A'; ?>
              </p>
              <?php
                $status = strtolower($row['status'] ?? 'received');
                $badgeClass = match($status) {
                    'received' => 'status-pending',
                    'preparing' => 'status-preparing',
                    'delivered' => 'status-delivered',
                    'cancelled' => 'status-cancelled',
                    default => 'status-pending'
                };
              ?>
              <span class="badge badge-status <?php echo $badgeClass; ?>">
                <?php echo ucfirst($status); ?>
              </span>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="text-center mt-5">
        <i class="bi bi-emoji-frown display-4 text-secondary"></i>
        <p class="mt-3 fs-5 text-muted">No orders yet. Go to the <a href="menu.php" class="text-primary text-decoration-none">menu</a> and order your favorite food!</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 User Dashboard. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>