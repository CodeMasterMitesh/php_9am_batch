<?php
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

// Fetch all orders with their items
$sql = "SELECT 
          o.id as order_id,
          o.uid,
          o.amt,
          o.status,
          o.date,
          s.firstname AS student_name,
          GROUP_CONCAT(CONCAT(oi.quantity, 'x ', i.name) SEPARATOR ' | ') as items,
          COUNT(oi.id) as item_count
        FROM `order` o
        JOIN users s ON o.uid = s.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN items i ON oi.product_id = i.id
        GROUP BY o.id
        ORDER BY o.date DESC";
$query = mysqli_query($conn, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($query)) {
    $orders[] = $row;
}

// Alternative query if you want individual item details for display
$detailed_sql = "SELECT 
                  o.id as order_id,
                  o.uid,
                  o.amt,
                  o.status,
                  o.date,
                  s.firstname AS student_name,
                  i.name as item_name,
                  i.image,
                  oi.quantity,
                  oi.price,
                  oi.total
                FROM `order` o
                JOIN users s ON o.uid = s.id
                JOIN order_items oi ON o.id = oi.order_id
                JOIN items i ON oi.product_id = i.id
                ORDER BY o.date DESC, oi.id ASC";
$detailed_query = mysqli_query($conn, $detailed_sql);
$detailed_orders = [];
while ($row = mysqli_fetch_assoc($detailed_query)) {
    $order_id = $row['order_id'];
    if (!isset($detailed_orders[$order_id])) {
        $detailed_orders[$order_id] = [
            'order_id' => $row['order_id'],
            'uid' => $row['uid'],
            'amt' => $row['amt'],
            'status' => $row['status'],
            'date' => $row['date'],
            'student_name' => $row['student_name'],
            'items' => []
        ];
    }
    $detailed_orders[$order_id]['items'][] = [
        'name' => $row['item_name'],
        'image' => $row['image'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'amt' => $row['amt']
    ];
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
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
  }
  .order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  .order-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 10px;
    margin-bottom: 10px;
  }
  .order-id {
    font-weight: 600;
    color: #0d6efd;
    font-size: 0.9rem;
  }
  .order-date {
    font-size: 0.8rem;
    color: #6c757d;
  }
  .order-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 8px;
  }
  .order-item img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 10px;
  }
  .item-details {
    flex: 1;
  }
  .item-name {
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 2px;
  }
  .item-qty-price {
    font-size: 0.8rem;
    color: #6c757d;
  }
  .order-footer {
    border-top: 1px solid #e9ecef;
    padding-top: 10px;
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .total-amount {
    font-weight: 600;
    color: #28a745;
  }
  .student-info {
    font-size: 0.8rem;
    color: #6c757d;
  }
  .status-btn {
    font-size: 0.85rem;
    border-radius: 10px;
    padding: 6px 12px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  .status-btn:hover {
    transform: scale(1.05);
  }
  .status-btn.received { background-color: #ffc107; color: #000; }
  .status-btn.preparing { background-color: #17a2b8; color: #fff; }
  .status-btn.ready { background-color: #fd7e14; color: #fff; }
  .status-btn.delivered { background-color: #28a745; color: #fff; }
  .badge-delivered {
    background-color: #28a745;
    color: white;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 0.85rem;
  }
  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
  }
  .empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
  }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">
      🍳 Kitchen Dashboard (<?php echo $_SESSION['employee']['firstname']; ?>)
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link text-white">
            <i class="bi bi-clock"></i> <?php echo date('h:i A'); ?>
          </span>
        </li>
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
        <?php 
        $received_orders = array_filter($detailed_orders, function($order) {
          return strtolower($order['status']) == 'received' || strtolower($order['status']) == 'pending';
        });
        
        if (empty($received_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>No orders received</p>
          </div>
        <?php else: ?>
          <?php foreach ($received_orders as $order): ?>
            <div class="order-card" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <?php foreach ($order['items'] as $item): ?>
                <div class="order-item">
                  <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                  <div class="item-details">
                    <div class="item-name"><?php echo $item['name']; ?></div>
                    <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?> = ₹<?php echo $item['amt']; ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">Total: ₹<?php echo $order['amt']; ?></div>
                  <div class="student-info">By: <?php echo $order['student_name']; ?> </div>
                </div>
                <button class="btn status-btn preparing" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'preparing')">
                  Start Preparing
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Preparing -->
    <div class="kanban-column">
      <h4>🍳 Preparing</h4>
      <div id="preparing-column">
        <?php 
        $preparing_orders = array_filter($detailed_orders, function($order) {
          return strtolower($order['status']) == 'preparing';
        });
        
        if (empty($preparing_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-egg-fried"></i>
            <p>No orders being prepared</p>
          </div>
        <?php else: ?>
          <?php foreach ($preparing_orders as $order): ?>
            <div class="order-card" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <?php foreach ($order['items'] as $item): ?>
                <div class="order-item">
                  <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                  <div class="item-details">
                    <div class="item-name"><?php echo $item['name']; ?></div>
                    <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?> = ₹<?php echo $item['amt']; ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">Total: ₹<?php echo $order['amt']; ?></div>
                  <div class="student-info">By: <?php echo $order['student_name']; ?> </div>
                </div>
                <button class="btn status-btn ready" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'ready')">
                  Mark as Ready
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ready -->
    <div class="kanban-column">
      <h4>✅ Ready</h4>
      <div id="ready-column">
        <?php 
        $ready_orders = array_filter($detailed_orders, function($order) {
          return strtolower($order['status']) == 'ready';
        });
        
        if (empty($ready_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-check-circle"></i>
            <p>No orders ready</p>
          </div>
        <?php else: ?>
          <?php foreach ($ready_orders as $order): ?>
            <div class="order-card" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <?php foreach ($order['items'] as $item): ?>
                <div class="order-item">
                  <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                  <div class="item-details">
                    <div class="item-name"><?php echo $item['name']; ?></div>
                    <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?> = ₹<?php echo $item['amt']; ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">Total: ₹<?php echo $order['amt']; ?></div>
                  <div class="student-info">By: <?php echo $order['student_name']; ?> </div>
                </div>
                <button class="btn status-btn delivered" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'delivered')">
                  Mark as Delivered
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Delivered -->
    <div class="kanban-column">
      <h4>📦 Delivered</h4>
      <div id="delivered-column">
        <?php 
        $delivered_orders = array_filter($detailed_orders, function($order) {
          return strtolower($order['status']) == 'delivered';
        });
        
        if (empty($delivered_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-truck"></i>
            <p>No delivered orders</p>
          </div>
        <?php else: ?>
          <?php foreach ($delivered_orders as $order): ?>
            <div class="order-card" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <?php foreach ($order['items'] as $item): ?>
                <div class="order-item">
                  <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                  <div class="item-details">
                    <div class="item-name"><?php echo $item['name']; ?></div>
                    <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?> = ₹<?php echo $item['amt']; ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">Total: ₹<?php echo $order['amt']; ?></div>
                  <div class="student-info">By: <?php echo $order['student_name']; ?> </div>
                </div>
                <span class="badge-delivered">Delivered</span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
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
  if (confirm('Are you sure you want to update order #' + orderId + ' to "' + newStatus + '"?')) {
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
      } else {
        alert('Error updating status');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error updating status');
    });
  }
}

// Auto-refresh every 30 seconds
setInterval(() => {
  location.reload();
}, 30000);
</script>
</body>
</html>