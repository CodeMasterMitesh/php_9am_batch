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
            'total' => $row['amt'],
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
<?php include 'includes/studentNav.php'; ?>
  <!-- Main Content -->
  <div class="orders-container">
    <!-- Page Header -->
    <div class="orders-header">
      <div class="orders-title">
        <h1><i class="bi bi-receipt me-2"></i>My Orders</h1>
        <p>Track your order history and status</p>
      </div>
    </div>

    <!-- Orders Section -->
    <?php if (!empty($orders)): ?>
      <div class="row">
        <?php foreach ($orders as $order): ?>
          <div class="col-12">
            <div class="card order-card">
              <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h5 class="mb-1">Order #<?php echo $order['order_id']; ?></h5>
                    <div class="order-date text-white-50">
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
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" 
                         onerror="this.src='https://via.placeholder.com/70?text=No+Image'">
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
                    <div class="order-count">
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
        <h4>No orders yet</h4>
        <p>You haven't placed any orders yet. Start exploring our menu!</p>
        <a href="menu.php" class="btn browse-menu-btn">
          <i class="bi bi-arrow-right me-2"></i>Browse Menu
        </a>
      </div>
    <?php endif; ?>
  </div>
<?php include 'includes/footer.php'; ?>