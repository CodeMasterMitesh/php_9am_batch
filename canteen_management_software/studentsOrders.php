<?php
include 'config/connection.php';
if (!$_SESSION['student']) {
    echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
    </script>";
}

// Fetch logged-in user orders with order_items
$uid = $_SESSION['student']['id'];
$sql = "SELECT 
          o.id as order_id,
          o.amt,
          o.status,
          o.date,
          COUNT(oi.id) as item_count,
          GROUP_CONCAT(CONCAT(oi.quantity, 'x ', i.name) SEPARATOR ' | ') as items_list
        FROM `order` o 
        JOIN order_items oi ON o.id = oi.order_id
        JOIN items i ON oi.product_id = i.id 
        WHERE o.uid = '$uid'
        GROUP BY o.id
        ORDER BY o.date DESC";
$query = mysqli_query($conn, $sql);

// Alternative: Get detailed order items for display
$detailed_sql = "SELECT 
                  o.id as order_id,
                  o.amt,
                  o.status,
                  o.date,
                  i.name as item_name,
                  i.image,
                  oi.quantity,
                  oi.price,
                  oi.total
                FROM `order` o 
                JOIN order_items oi ON o.id = oi.order_id
                JOIN items i ON oi.product_id = i.id 
                WHERE o.uid = '$uid'
                ORDER BY o.date DESC, oi.id ASC";
$detailed_query = mysqli_query($conn, $detailed_sql);

// Group items by order
$orders = [];
while ($row = mysqli_fetch_assoc($detailed_query)) {
    $order_id = $row['order_id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $row['order_id'],
            'total' => $row['total'],
            'status' => $row['status'],
            'date' => $row['date'],
            'items' => []
        ];
    }
    $orders[$order_id]['items'][] = [
        'name' => $row['item_name'],
        'image' => $row['image'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'total' => $row['total']
    ];
}
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
    .order-header {
      background: linear-gradient(135deg, #0d6efd, #0dcaf0);
      color: white;
      padding: 15px 20px;
      border-radius: 12px 12px 0 0;
    }
    .order-body {
      padding: 20px;
    }
    .order-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding: 12px;
      background: #f8f9fa;
      border-radius: 10px;
    }
    .order-item img {
      width: 70px;
      height: 70px;
      border-radius: 8px;
      object-fit: cover;
      margin-right: 15px;
    }
    .item-details {
      flex: 1;
    }
    .item-name {
      font-weight: 600;
      margin-bottom: 5px;
    }
    .item-meta {
      font-size: 0.9rem;
      color: #6c757d;
    }
    .order-footer {
      border-top: 1px solid #e9ecef;
      padding-top: 15px;
      margin-top: 15px;
    }
    .badge-status {
      font-size: 0.8rem;
      padding: 6px 12px;
      border-radius: 12px;
    }
    .status-pending {
      background-color: #ffc107;
      color: #000;
    }
    .status-received {
      background-color: #17a2b8;
      color: #fff;
    }
    .status-preparing {
      background-color: #fd7e14;
      color: #fff;
    }
    .status-ready {
      background-color: #20c997;
      color: #fff;
    }
    .status-delivered {
      background-color: #28a745;
      color: #fff;
    }
    .status-cancelled {
      background-color: #dc3545;
      color: #fff;
    }
    .total-amount {
      font-size: 1.1rem;
      font-weight: 600;
      color: #28a745;
    }
    .order-id {
      font-size: 0.9rem;
      opacity: 0.9;
    }
    .order-date {
      font-size: 0.85rem;
      opacity: 0.8;
    }
    footer {
      background: #0d6efd;
      font-size: 0.9rem;
      letter-spacing: 0.4px;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }
    .empty-state i {
      font-size: 4rem;
      color: #6c757d;
      margin-bottom: 20px;
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

    <?php if (!empty($orders)): ?>
      <div class="row g-4">
        <?php foreach ($orders as $order): ?>
          <div class="col-12">
            <div class="card order-card shadow-sm mb-4">
              <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h5 class="mb-1">Order #<?php echo $order['order_id']; ?></h5>
                    <div class="order-date">
                      <?php echo date('d M Y, h:i A', strtotime($order['date'])); ?>
                    </div>
                  </div>
                  <?php
                    $status = strtolower($order['status'] ?? 'pending');
                    $badgeClass = match($status) {
                        'pending' => 'status-pending',
                        'received' => 'status-received',
                        'preparing' => 'status-preparing',
                        'ready' => 'status-ready',
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
              
              <div class="order-body">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                    <div class="item-details">
                      <div class="item-name"><?php echo $item['name']; ?></div>
                      <div class="item-meta">
                        Quantity: <?php echo $item['quantity']; ?> | 
                        Price: ₹<?php echo $item['price']; ?> | 
                        Amount: ₹<?php echo $item['total']; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
                
                <div class="order-footer">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <span class="total-amount">Total: ₹<?php echo $order['total']; ?></span>
                    </div>
                    <div class="text-muted small">
                      <?php echo count($order['items']); ?> item(s) in this order
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-bag-x"></i>
        <h4 class="text-muted mb-3">No orders yet</h4>
        <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring our menu!</p>
        <a href="menu.php" class="btn btn-primary btn-lg">
          <i class="bi bi-arrow-right"></i> Browse Menu
        </a>
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