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
<title>The Hungar Bar Kitchen Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/employeeStyle.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-kitchen navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="#">
      <img src="assets/logo/the_hunger_bar_logo.png" width="40" height="40" class="rounded-circle me-2">
      <span>The Hunger Bar Kitchen</span>
    </a>
    <div class="navbar-nav ms-auto">
      <span class="navbar-text text-white me-3">
        <i class="bi bi-person-circle me-1"></i>
        <?php echo $_SESSION['employee']['firstname']; ?>
      </span>
      <span class="navbar-text text-white me-3">
        <i class="bi bi-clock me-1"></i>
        <?php echo date('h:i A'); ?>
      </span>
      <a class="nav-link text-white" href="logout.php">
        <i class="bi bi-box-arrow-right"></i>
        Logout
      </a>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container-fluid main-content">
  <!-- Page Header -->
  <div class="page-header">
    <div class="page-title">
      <h1><i class="bi bi-speedometer2 me-2"></i>Kitchen Order Management</h1>
      <p>Monitor and update order status in real-time</p>
    </div>
  </div>

  <!-- Kanban Board -->
  <div class="kanban-board">
    
    <!-- Received Column -->
    <div class="kanban-column received">
      <div class="column-header">
        <h4>
          <i class="bi bi-inbox"></i>
          Received
          <span class="order-count">
            <?php 
            $received_orders = array_filter($detailed_orders, function($order) {
              return strtolower($order['status']) == 'received' || strtolower($order['status']) == 'pending';
            });
            echo count($received_orders);
            ?>
          </span>
        </h4>
      </div>
      <div id="received-column">
        <?php if (empty($received_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5>No Orders</h5>
            <p>New orders will appear here</p>
          </div>
        <?php else: ?>
          <?php foreach ($received_orders as $order): ?>
            <div class="order-card received" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">#<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" onerror="this.src='https://via.placeholder.com/50?text=No+Image'">
                    <div class="item-details">
                      <div class="item-name"><?php echo $item['name']; ?></div>
                      <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">₹<?php echo $order['amt']; ?></div>
                  <div class="student-info"><?php echo $order['student_name']; ?></div>
                </div>
                <button class="status-btn preparing" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'preparing')">
                  <i class="bi bi-play-circle"></i>
                  Start Prep
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Preparing Column -->
    <div class="kanban-column preparing">
      <div class="column-header">
        <h4>
          <i class="bi bi-egg-fried"></i>
          Preparing
          <span class="order-count">
            <?php 
            $preparing_orders = array_filter($detailed_orders, function($order) {
              return strtolower($order['status']) == 'preparing';
            });
            echo count($preparing_orders);
            ?>
          </span>
        </h4>
      </div>
      <div id="preparing-column">
        <?php if (empty($preparing_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-egg-fried"></i>
            <h5>All Caught Up</h5>
            <p>No orders in preparation</p>
          </div>
        <?php else: ?>
          <?php foreach ($preparing_orders as $order): ?>
            <div class="order-card preparing" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">#<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" onerror="this.src='https://via.placeholder.com/50?text=No+Image'">
                    <div class="item-details">
                      <div class="item-name"><?php echo $item['name']; ?></div>
                      <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">₹<?php echo $order['amt']; ?></div>
                  <div class="student-info"><?php echo $order['student_name']; ?></div>
                </div>
                <button class="status-btn ready" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'ready')">
                  <i class="bi bi-check-circle"></i>
                  Mark Ready
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ready Column -->
    <div class="kanban-column ready">
      <div class="column-header">
        <h4>
          <i class="bi bi-check-circle"></i>
          Ready
          <span class="order-count">
            <?php 
            $ready_orders = array_filter($detailed_orders, function($order) {
              return strtolower($order['status']) == 'ready';
            });
            echo count($ready_orders);
            ?>
          </span>
        </h4>
      </div>
      <div id="ready-column">
        <?php if (empty($ready_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-check-circle"></i>
            <h5>Ready for Pickup</h5>
            <p>Completed orders will appear here</p>
          </div>
        <?php else: ?>
          <?php foreach ($ready_orders as $order): ?>
            <div class="order-card ready" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">#<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" onerror="this.src='https://via.placeholder.com/50?text=No+Image'">
                    <div class="item-details">
                      <div class="item-name"><?php echo $item['name']; ?></div>
                      <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">₹<?php echo $order['amt']; ?></div>
                  <div class="student-info"><?php echo $order['student_name']; ?></div>
                </div>
                <button class="status-btn delivered" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'delivered')">
                  <i class="bi bi-truck"></i>
                  Deliver
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Delivered Column -->
    <div class="kanban-column delivered">
      <div class="column-header">
        <h4>
          <i class="bi bi-truck"></i>
          Delivered
          <span class="order-count">
            <?php 
            $delivered_orders = array_filter($detailed_orders, function($order) {
              return strtolower($order['status']) == 'delivered';
            });
            echo count($delivered_orders);
            ?>
          </span>
        </h4>
      </div>
      <div id="delivered-column">
        <?php if (empty($delivered_orders)): ?>
          <div class="empty-state">
            <i class="bi bi-truck"></i>
            <h5>Delivery History</h5>
            <p>Delivered orders will appear here</p>
          </div>
        <?php else: ?>
          <?php foreach ($delivered_orders as $order): ?>
            <div class="order-card delivered" data-id="<?php echo $order['order_id']; ?>">
              <div class="order-header">
                <div class="order-id">#<?php echo $order['order_id']; ?></div>
                <div class="order-date"><?php echo date('M j, g:i A', strtotime($order['date'])); ?></div>
              </div>
              
              <div class="order-items">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" onerror="this.src='https://via.placeholder.com/50?text=No+Image'">
                    <div class="item-details">
                      <div class="item-name"><?php echo $item['name']; ?></div>
                      <div class="item-qty-price"><?php echo $item['quantity']; ?> x ₹<?php echo $item['price']; ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              
              <div class="order-footer">
                <div>
                  <div class="total-amount">₹<?php echo $order['amt']; ?></div>
                  <div class="student-info"><?php echo $order['student_name']; ?></div>
                </div>
                <span class="badge-delivered">
                  <i class="bi bi-check-lg"></i>
                  Delivered
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Footer -->
<footer class="footer-kitchen">
  <div class="container">
    <p class="mb-0">&copy; 2025 The Hungar Bar Kitchen Dashboard. All rights reserved. | Auto-refresh in <span id="refresh-timer">30</span>s</p>
  </div>
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

// Auto-refresh countdown
let refreshTime = 30;
const timerElement = document.getElementById('refresh-timer');

setInterval(() => {
  refreshTime--;
  timerElement.textContent = refreshTime;
  
  if (refreshTime <= 0) {
    location.reload();
  }
}, 1000);

// Reset timer on user interaction
document.addEventListener('click', () => {
  refreshTime = 30;
  timerElement.textContent = refreshTime;
});
</script>
</body>
</html>