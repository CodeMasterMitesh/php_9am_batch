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
  <style>
    /* Page Header */
    .page-header {
      background-color: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      margin-bottom: 1.5rem;
    }
    
    .page-title h1 {
      font-weight: 700;
      color: var(--dark);
      margin: 0;
    }
    
    .page-title p {
      color: #6c757d;
      margin: 0;
    }
    
    /* Search Form */
    .search-form {
      max-width: 400px;
    }
    
    .search-form .form-control {
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      padding: 0.75rem 1rem;
      transition: all 0.3s ease;
    }
    
    .search-form .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
    }
    
    .search-btn {
      border-radius: 8px;
      padding: 0.75rem 1.25rem;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border: none;
      transition: all 0.3s ease;
    }
    
    .search-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
    }
    
    /* Orders Card */
    .orders-card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }
    
    .orders-card .card-header {
      background-color: white;
      border-bottom: 1px solid #e9ecef;
      padding: 1.25rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .table-responsive {
      border-radius: 0 0 12px 12px;
    }
    
    .table th {
      border-top: none;
      font-weight: 600;
      color: #495057;
      padding: 1rem 0.75rem;
      background-color: #f8f9fa;
    }
    
    .table td {
      padding: 1rem 0.75rem;
      vertical-align: middle;
    }
    
    /* Status Badges */
    .badge-delivered {
      background-color: #d4edda;
      color: #155724;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }
    
    .badge-preparing {
      background-color: #fff3cd;
      color: #856404;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }
    
    .badge-pending {
      background-color: #cce7ff;
      color: #004085;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }
    
    .badge-ready {
      background-color: #d1ecf1;
      color: #0c5460;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }
    
    .badge-received {
      background-color: #e2e3e5;
      color: #383d41;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }
    
    .badge-cancelled {
      background-color: #f8d7da;
      color: #721c24;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }
    
    /* Table Row Hover Effect */
    .table-hover tbody tr {
      transition: all 0.2s ease;
    }
    
    .table-hover tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    /* Amount Styling */
    .amount {
      font-weight: 700;
      color: #198754;
    }
    
    /* Items List Styling */
    .items-list {
      max-width: 300px;
    }
    
    .item-detail {
      padding: 0.5rem;
      background: #f8f9fa;
      border-radius: 6px;
      margin-bottom: 0.5rem;
    }
    
    .item-detail:last-child {
      margin-bottom: 0;
    }
    
    .item-name {
      font-weight: 600;
      color: #495057;
    }
    
    .item-meta {
      font-size: 0.85rem;
      color: #6c757d;
    }
    
    /* Student Name */
    .student-name {
      font-weight: 600;
      color: var(--dark);
    }
    
    .room-no {
      font-size: 0.85rem;
      color: #6c757d;
    }
    
    /* Date Styling */
    .order-date {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .order-time {
      color: #adb5bd;
      font-size: 0.8rem;
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    
    /* Stats Overview */
    .stats-overview {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }
    
    .stat-card {
      background: white;
      padding: 1rem 1.5rem;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      flex: 1;
      min-width: 200px;
    }
    
    .stat-card .stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 0.25rem;
    }
    
    .stat-card .stat-label {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    /* Order ID Styling */
    .order-id {
      font-weight: 700;
      color: var(--primary);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
      }
      
      .search-form {
        max-width: 100%;
        width: 100%;
      }
      
      .stats-overview {
        flex-direction: column;
      }
      
      .stat-card {
        min-width: 100%;
      }
      
      .items-list {
        max-width: 200px;
      }
    }
  </style>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
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