<?php
include 'config/connection.php';
// Optional: restrict to kitchen staff login
if (!$_SESSION['employee']) {
  echo "<script>alert('Unauthorized Access'); location.href='404.php';</script>";
  exit;
}

// Update order status via AJAX
if (isset($_POST['order_id']) && isset($_POST['status'])) {
  $order_id = $_POST['order_id'];
  $status = $_POST['status'];
  mysqli_query($conn, "UPDATE `order` SET status='$status' WHERE id='$order_id'");
  echo "success";
  exit;
}

// Fetch all active orders with item details
$sql = "SELECT o.*, i.name AS item_name, i.image, s.firstname AS student_name 
        FROM `order` o
        JOIN items i ON o.pid = i.id
        JOIN users s ON o.uid = s.id
        ORDER BY o.id DESC";
$query = mysqli_query($conn, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($query)) {
    // debug($row);
  $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kitchen Dashboard</title>
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
  h2 {
    font-weight: 600;
    color: #343a40;
  }
  .kanban-board {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
  }
  .kanban-column {
    flex: 1;
    min-width: 300px;
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  .kanban-column h4 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
    color: #0d6efd;
  }
  .order-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 12px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
  }
  .order-card:hover {
    transform: translateY(-3px);
  }
  .order-card img {
    width: 100%;
    height: 150px;
    border-radius: 10px;
    object-fit: cover;
  }
  .order-title {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 10px;
  }
  .status-btn {
    font-size: 0.85rem;
    border-radius: 10px;
    padding: 5px 10px;
  }
  .status-btn.received { background-color: #ffc107; color: #000; }
  .status-btn.preparing { background-color: #17a2b8; color: #fff; }
  .status-btn.delivered { background-color: #28a745; color: #fff; }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Kitchen Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Kanban Board -->
<div class="container py-5">
  <h2 class="text-center mb-5">👨‍🍳 Kitchen Order Status Board</h2>
  <div class="kanban-board">
    
    <!-- Received -->
    <div class="kanban-column">
      <h4>📥 Received</h4>
      <div id="received-column">
        <?php foreach ($orders as $order): if (strtolower($order['status']) == 'received') { ?>
          <div class="order-card" data-id="<?php echo $order['id']; ?>">
            <img src="<?php echo $order['image']; ?>" alt="Food">
            <div class="order-title"><?php echo $order['item_name']; ?></div>
            <p class="mb-1 text-muted small">Qty: <?php echo $order['qty']; ?> | ₹<?php echo $order['amt']; ?></p>
            <p class="small text-secondary mb-2">By: <?php echo $order['student_name']; ?></p>
            <button class="btn status-btn preparing w-100" onclick="updateStatus(<?php echo $order['id']; ?>, 'Preparing')">Move to Preparing</button>
          </div>
        <?php } endforeach; ?>
      </div>
    </div>

    <!-- Preparing -->
    <div class="kanban-column">
      <h4>🍳 Preparing</h4>
      <div id="preparing-column">
        <?php foreach ($orders as $order): if (strtolower($order['status']) == 'preparing') { ?>
          <div class="order-card" data-id="<?php echo $order['id']; ?>">
            <img src="<?php echo $order['image']; ?>" alt="Food">
            <div class="order-title"><?php echo $order['item_name']; ?></div>
            <p class="mb-1 text-muted small">Qty: <?php echo $order['qty']; ?> | ₹<?php echo $order['amt']; ?></p>
            <p class="small text-secondary mb-2">By: <?php echo $order['student_name']; ?></p>
            <button class="btn status-btn delivered w-100" onclick="updateStatus(<?php echo $order['id']; ?>, 'Delivered')">Move to Delivered</button>
          </div>
        <?php } endforeach; ?>
      </div>
    </div>

    <!-- Delivered -->
    <div class="kanban-column">
      <h4>✅ Delivered</h4>
      <div id="delivered-column">
        <?php foreach ($orders as $order): if (strtolower($order['status']) == 'delivered') { ?>
          <div class="order-card" data-id="<?php echo $order['id']; ?>">
            <img src="<?php echo $order['image']; ?>" alt="Food">
            <div class="order-title"><?php echo $order['item_name']; ?></div>
            <p class="mb-1 text-muted small">Qty: <?php echo $order['qty']; ?> | ₹<?php echo $order['amt']; ?></p>
            <p class="small text-secondary mb-2">By: <?php echo $order['student_name']; ?></p>
            <span class="badge bg-success w-100 py-2">Delivered</span>
          </div>
        <?php } endforeach; ?>
      </div>
    </div>

  </div>
</div>

<!-- Footer -->
<footer class="text-white text-center py-3 mt-auto bg-primary">
  <p class="mb-0">&copy; 2025 Kitchen Dashboard. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(orderId, newStatus) {
  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('status', newStatus);

  fetch('kitchen.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === 'success') {
      alert('Order status updated to ' + newStatus + '!');
      location.reload();
    }
  })
  .catch(err => console.error(err));
}
</script>
</body>
</html>
