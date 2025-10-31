<?php
include 'config/connection.php';
// Use centralized student/customer guard and main.css via studentNav include
include 'includes/studentNav.php';

// Fetch logged-in user orders with order_items
$uid = $_SESSION['user']['id'];

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
          <div class="col-12 mb-4">
            <div class="card order-card shadow-sm">
              <div class="card-header bg-primary text-white">
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
                        'pending' => 'bg-warning',
                        'received' => 'bg-info',
                        'preparing' => 'bg-primary',
                        'ready' => 'bg-success',
                        'delivered' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                  ?>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge <?php echo $badgeClass; ?>">
                      <?php echo ucfirst($status); ?>
                    </span>
                    <a class="btn btn-light btn-sm" href="invoice.php?id=<?php echo $order['order_id']; ?>&view=pdf" target="_blank">
                      <i class="bi bi-printer me-1"></i> Print Invoice
                    </a>
                  </div>
                </div>
              </div>
              
              <div class="card-body">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item d-flex align-items-center mb-3 pb-3 border-bottom">
        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" 
          class="me-3 order-item-img"
                         onerror="this.src='https://via.placeholder.com/70?text=No+Image'">
                    <div class="item-details flex-grow-1">
                      <div class="item-name fw-bold"><?php echo $item['name']; ?></div>
                      <div class="item-meta text-muted">
                        Quantity: <?php echo $item['quantity']; ?> | 
                        Price: ₹<?php echo $item['price']; ?> | 
                        Amount: ₹<?php echo $item['total']; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
                
                <div class="order-footer mt-3 pt-3 border-top">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <span class="total-amount fw-bold fs-5">Total: ₹<?php echo $order['total']; ?></span>
                    </div>
                    <div class="order-count text-muted">
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
      <div class="empty-state text-center py-5">
        <i class="bi bi-bag-x display-1 text-muted"></i>
        <h4 class="mt-3">No orders yet</h4>
        <p class="text-muted">You haven't placed any orders yet. Start exploring our menu!</p>
        <a href="menu.php" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-right me-2"></i>Browse Menu
        </a>
      </div>
    <?php endif; ?>
  </div>
<?php 
    include 'includes/footer.php';
?>