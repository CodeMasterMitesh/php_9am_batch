<?php 
include 'includes/nav.php';

// -------------------------------
// 📊 Get Order Statistics
// -------------------------------
$todaySql = "SELECT COUNT(*) AS total FROM `order` WHERE DATE(date) = CURDATE()";
$weekSql = "SELECT COUNT(*) AS total FROM `order` WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
$monthSql = "SELECT COUNT(*) AS total FROM `order` WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())";
$yearSql = "SELECT COUNT(*) AS total FROM `order` WHERE YEAR(date) = YEAR(CURDATE())";

// Revenue statistics
$todayRevenueSql = "SELECT COALESCE(SUM(amt), 0) AS revenue FROM `order` WHERE DATE(date) = CURDATE()";
$monthRevenueSql = "SELECT COALESCE(SUM(amt), 0) AS revenue FROM `order` WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())";
$yearRevenueSql = "SELECT COALESCE(SUM(amt), 0) AS revenue FROM `order` WHERE YEAR(date) = YEAR(CURDATE())";

$todayOrders  = mysqli_fetch_assoc(mysqli_query($conn, $todaySql))['total'] ?? 0;
$weekOrders   = mysqli_fetch_assoc(mysqli_query($conn, $weekSql))['total'] ?? 0;
$monthOrders  = mysqli_fetch_assoc(mysqli_query($conn, $monthSql))['total'] ?? 0;
$yearOrders   = mysqli_fetch_assoc(mysqli_query($conn, $yearSql))['total'] ?? 0;

$todayRevenue  = mysqli_fetch_assoc(mysqli_query($conn, $todayRevenueSql))['revenue'] ?? 0;
$monthRevenue  = mysqli_fetch_assoc(mysqli_query($conn, $monthRevenueSql))['revenue'] ?? 0;
$yearRevenue   = mysqli_fetch_assoc(mysqli_query($conn, $yearRevenueSql))['revenue'] ?? 0;

// -------------------------------
// 📋 Fetch All Orders with Items
// -------------------------------
$orderQuery = "
    SELECT 
        o.id as order_id,
        o.amt,
        o.status,
        o.date,
        s.firstname AS student_name,
        COUNT(oi.id) as item_count,
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', i.name) SEPARATOR ' | ') as items_list
    FROM `order` o
    JOIN users s ON o.uid = s.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN items i ON oi.product_id = i.id
    GROUP BY o.id
    ORDER BY o.date DESC
";
$orderResult = mysqli_query($conn, $orderQuery);

// Alternative: Get detailed orders for display
$detailedQuery = "
    SELECT 
        o.id as order_id,
        o.amt,
        o.status,
        o.date,
        s.firstname AS student_name,
        i.name as item_name,
        oi.quantity,
        oi.price,
        oi.total
    FROM `order` o
    JOIN users s ON o.uid = s.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN items i ON oi.product_id = i.id
    ORDER BY o.date DESC, oi.id ASC
";
$detailedResult = mysqli_query($conn, $detailedQuery);

// Group orders by order_id for display
$orders = [];
while ($row = mysqli_fetch_assoc($detailedResult)) {
    $order_id = $row['order_id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $row['order_id'],
            'amt' => $row['amt'],
            'status' => $row['status'],
            'date' => $row['date'],
            'student_name' => $row['student_name'],
            'items' => []
        ];
    }
    $orders[$order_id]['items'][] = [
        'name' => $row['item_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'amt' => $row['amt']
    ];
}
?>

<div class="container my-4 flex-grow-1">
  <h2 class="mb-4 text-center fw-bold">📊 Admin Dashboard</h2>

  <!-- Statistics Cards -->
  <div class="row g-4 mb-4">
    <!-- Orders Statistics -->
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-primary text-white">
        <div class="card-body">
          <i class="bi bi-cart-check display-6 mb-2"></i>
          <h5 class="card-title">Today's Orders</h5>
          <p class="display-6 fw-bold"><?php echo $todayOrders; ?></p>
          <p class="mb-0">₹<?php echo number_format($todayRevenue, 2); ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-success text-white">
        <div class="card-body">
          <i class="bi bi-calendar-week display-6 mb-2"></i>
          <h5 class="card-title">This Week</h5>
          <p class="display-6 fw-bold"><?php echo $weekOrders; ?></p>
          <p class="mb-0">Total Orders</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-warning text-white">
        <div class="card-body">
          <i class="bi bi-calendar-month display-6 mb-2"></i>
          <h5 class="card-title">This Month</h5>
          <p class="display-6 fw-bold"><?php echo $monthOrders; ?></p>
          <p class="mb-0">₹<?php echo number_format($monthRevenue, 2); ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-danger text-white">
        <div class="card-body">
          <i class="bi bi-calendar display-6 mb-2"></i>
          <h5 class="card-title">This Year</h5>
          <p class="display-6 fw-bold"><?php echo $yearOrders; ?></p>
          <p class="mb-0">₹<?php echo number_format($yearRevenue, 2); ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders Table -->
  <div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
      <span><i class="bi bi-table me-2"></i> All Orders</span>
      <span class="badge bg-primary"><?php echo count($orders); ?> Orders</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Order ID</th>
              <th>Student</th>
              <th>Items</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Order Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            if(!empty($orders)){
              foreach($orders as $order){
                $status = strtolower($order['status']);
                $badgeClass = match($status) {
                    'pending' => 'bg-warning text-dark',
                    'received' => 'bg-info text-dark',
                    'preparing' => 'bg-primary',
                    'ready' => 'bg-success',
                    'delivered' => 'bg-secondary',
                    'cancelled' => 'bg-danger',
                    default => 'bg-warning text-dark'
                };
                ?>
                <tr>
                  <td>
                    <strong>#<?php echo $order['order_id']; ?></strong>
                  </td>
                  <td>
                    <div>
                      <strong><?php echo $order['student_name']; ?></strong>
                      <br>
                      <!-- <small class="text-muted">Room: <?//php echo $order['room_no']; ?></small> -->
                    </div>
                  </td>
                  <td>
                    <div class="items-list">
                      <?php foreach($order['items'] as $item): ?>
                        <div class="item-detail small mb-1">
                          <span class="fw-semibold"><?php echo $item['quantity']; ?>x</span>
                          <?php echo $item['name']; ?>
                          <br>
                          <small class="text-muted">
                            ₹<?php echo $item['price']; ?> each = ₹<?php echo $item['amt']; ?>
                          </small>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </td>
                  <td>
                    <strong class="text-success">₹<?php echo number_format($order['amt'], 2); ?></strong>
                  </td>
                  <td>
                    <span class='badge <?php echo $badgeClass; ?>'><?php echo ucfirst($order['status']); ?></span>
                  </td>
                  <td>
                    <?php echo date('d M Y', strtotime($order['date'])); ?>
                    <br>
                    <small class="text-muted"><?php echo date('h:i A', strtotime($order['date'])); ?></small>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['order_id']; ?>">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-outline-success" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'ready')">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button class="btn btn-outline-danger" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                
                <!-- Order Details Modal -->
                <div class="modal fade" id="orderModal<?php echo $order['order_id']; ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Order Details #<?php echo $order['order_id']; ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-6">
                            <h6>Student Information</h6>
                            <p><strong>Name:</strong> <?php echo $order['student_name']; ?></p>
                            <!-- <p><strong>Room No:</strong> <?//php echo $order['room_no']; ?></p> -->
                          </div>
                          <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['date'])); ?></p>
                            <p><strong>Status:</strong> <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                            <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['amt'], 2); ?></p>
                          </div>
                        </div>
                        <hr>
                        <h6>Order Items</h6>
                        <div class="table-responsive">
                          <table class="table table-sm">
                            <thead>
                              <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach($order['items'] as $item): ?>
                                <tr>
                                  <td><?php echo $item['name']; ?></td>
                                  <td><?php echo $item['quantity']; ?></td>
                                  <td>₹<?php echo $item['price']; ?></td>
                                  <td>₹<?php echo $item['amt']; ?></td>
                                </tr>
                              <?php endforeach; ?>
                              <tr class="table-success">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>₹<?php echo number_format($order['amt'], 2); ?></strong></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php
              }
            } else {
              echo "<tr><td colspan='7' class='text-center text-muted py-4'><i class='bi bi-inbox display-4 d-block mb-2'></i>No orders found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function updateOrderStatus(orderId, status) {
  if(confirm('Are you sure you want to update order #' + orderId + ' to "' + status + '"?')) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);
    
    fetch('update_order_status.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(result => {
      if(result === 'success') {
        alert('Order status updated successfully!');
        location.reload();
      } else {
        alert('Error updating order status');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error updating order status');
    });
  }
}
</script>

<?php include 'includes/footer.php'; ?>