<?php 
include 'config/connection.php';
include 'includes/nav.php';

// 🟩 Handle search
$search = "";
if (isset($_GET['search'])) {
  $search = trim($_GET['search']);
}

// 🟩 Fetch all orders with order_items (with optional search)
$sql = "
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
  WHERE s.firstname LIKE '%$search%' 
     OR i.name LIKE '%$search%' 
     OR o.status LIKE '%$search%'
  GROUP BY o.id
  ORDER BY o.date DESC
";
$result = mysqli_query($conn, $sql);

// Alternative: Get detailed orders for display
$detailed_sql = "
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
  WHERE s.firstname LIKE '%$search%' 
     OR i.name LIKE '%$search%' 
     OR o.status LIKE '%$search%'
  ORDER BY o.date DESC, oi.id ASC
";
$detailed_result = mysqli_query($conn, $detailed_sql);

// Group orders by order_id for display
$orders = [];
$totalOrders = 0;
while ($row = mysqli_fetch_assoc($detailed_result)) {
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
        $totalOrders++;
    }
    $orders[$order_id]['items'][] = [
        'name' => $row['item_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'amt' => $row['amt']
    ];
}

// Get total orders count from grouped data
$totalOrders = count($orders);
?>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
      <div class="page-title">
        <h1><i class="bi bi-basket me-2"></i>Manage Orders</h1>
        <p>View and manage all customer orders</p>
      </div>
      <form class="d-flex search-form" method="GET" action="">
        <input 
          class="form-control me-2" 
          type="search" 
          name="search" 
          placeholder="Search by student, item, or status..."
          value="<?php echo htmlspecialchars($search); ?>"
        >
        <button class="btn search-btn text-white" type="submit">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>

    <!-- Stats Overview -->
    <div class="stats-overview">
      <div class="stat-card">
        <div class="stat-value"><?php echo $totalOrders; ?></div>
        <div class="stat-label">Total Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">
          <?php 
            $deliveredQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT id) as count FROM `order` WHERE status = 'delivered'");
            $deliveredCount = mysqli_fetch_assoc($deliveredQuery)['count'];
            echo $deliveredCount;
          ?>
        </div>
        <div class="stat-label">Delivered</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">
          <?php 
            $preparingQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT id) as count FROM `order` WHERE status = 'preparing'");
            $preparingCount = mysqli_fetch_assoc($preparingQuery)['count'];
            echo $preparingCount;
          ?>
        </div>
        <div class="stat-label">Preparing</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">
          <?php 
            $revenueQuery = mysqli_query($conn, "SELECT COALESCE(SUM(amt), 0) as revenue FROM `order` WHERE status = 'delivered'");
            $revenue = mysqli_fetch_assoc($revenueQuery)['revenue'];
            echo '₹' . number_format($revenue, 2);
          ?>
        </div>
        <div class="stat-label">Total Revenue</div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card orders-card">
      <div class="card-header">
        <div>
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>Orders List</h5>
        </div>
        <div class="text-muted">
          <i class="bi bi-info-circle me-1"></i>
          <span><?php echo $totalOrders; ?> orders found</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Student</th>
              <th>Items</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Order Date</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            if(!empty($orders)){
              foreach($orders as $order){
                // Determine badge class based on status
                $status = strtolower($order['status']);
                $badgeClass = match($status) {
                    'pending' => 'badge-pending',
                    'received' => 'badge-received',
                    'preparing' => 'badge-preparing',
                    'ready' => 'badge-ready',
                    'delivered' => 'badge-delivered',
                    'cancelled' => 'badge-cancelled',
                    default => 'badge-pending'
                };
                
                // Format date and time
                $orderDate = date('d M Y', strtotime($order['date']));
                $orderTime = date('h:i A', strtotime($order['date']));
                ?>
                <tr>
                  <td>
                    <span class="order-id">#<?php echo $order['order_id']; ?></span>
                  </td>
                  <td>
                    <div>
                      <span class="student-name"><?php echo $order['student_name']; ?></span>
                      <br>
                    </div>
                  </td>
                  <td>
                    <div class="items-list">
                      <?php foreach($order['items'] as $item): ?>
                        <div class="item-detail">
                          <div class="item-name">
                            <?php echo $item['quantity']; ?>x <?php echo $item['name']; ?>
                          </div>
                          <div class="item-meta">
                            ₹<?php echo $item['price']; ?> each = ₹<?php echo $item['amt']; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </td>
                  <td>
                    <span class="amount">₹<?php echo number_format($order['amt'], 2); ?></span>
                  </td>
                  <td>
                    <span class="badge <?php echo $badgeClass; ?>">
                      <?php echo ucfirst($order['status']); ?>
                    </span>
                  </td>
                  <td>
                    <div>
                      <span class="order-date"><?php echo $orderDate; ?></span>
                      <br>
                      <small class="order-time"><?php echo $orderTime; ?></small>
                    </div>
                  </td>
                </tr>
                <?php
              }
            } else {
              echo "<tr><td colspan='6' class='text-center py-4'>
                <div class='empty-state'>
                  <i class='bi bi-inbox'></i>
                  <h4>No Orders Found</h4>
                  <p>" . ($search ? "No orders match your search criteria" : "No orders have been placed yet") . "</p>
                  " . ($search ? "<a href='?' class='btn btn-primary mt-2'><i class='bi bi-arrow-left me-1'></i>Clear Search</a>" : "") . "
                </div>
              </td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Add some interactive functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Add animation to table rows
      const tableRows = document.querySelectorAll('tbody tr');
      tableRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.03}s`;
      });
      
      // Add real-time search functionality
      const searchInput = document.querySelector('input[name="search"]');
      if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            if (this.value.length >= 3 || this.value.length === 0) {
              this.form.submit();
            }
          }, 500);
        });
      }
    });
  </script>
<?php include 'includes/footer.php'; ?>