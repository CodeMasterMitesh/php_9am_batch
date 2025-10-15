<?php
  include 'includes/nav.php';

  // -------------------------------
  // 📊 Get Order Statistics
  // -------------------------------
  $todaySql = 'SELECT COUNT(*) AS total FROM `order` WHERE DATE(date) = CURDATE()';
  $weekSql = 'SELECT COUNT(*) AS total FROM `order` WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)';
  $monthSql = 'SELECT COUNT(*) AS total FROM `order` WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())';
  $yearSql = 'SELECT COUNT(*) AS total FROM `order` WHERE YEAR(date) = YEAR(CURDATE())';

  // Revenue statistics
  $todayRevenueSql = 'SELECT COALESCE(SUM(amt), 0) AS revenue FROM `order` WHERE DATE(date) = CURDATE()';
  $monthRevenueSql = 'SELECT COALESCE(SUM(amt), 0) AS revenue FROM `order` WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())';
  $yearRevenueSql = 'SELECT COALESCE(SUM(amt), 0) AS revenue FROM `order` WHERE YEAR(date) = YEAR(CURDATE())';

  $todayOrders = mysqli_fetch_assoc(mysqli_query($conn, $todaySql))['total'] ?? 0;
  $weekOrders = mysqli_fetch_assoc(mysqli_query($conn, $weekSql))['total'] ?? 0;
  $monthOrders = mysqli_fetch_assoc(mysqli_query($conn, $monthSql))['total'] ?? 0;
  $yearOrders = mysqli_fetch_assoc(mysqli_query($conn, $yearSql))['total'] ?? 0;

  $todayRevenue = mysqli_fetch_assoc(mysqli_query($conn, $todayRevenueSql))['revenue'] ?? 0;
  $monthRevenue = mysqli_fetch_assoc(mysqli_query($conn, $monthRevenueSql))['revenue'] ?? 0;
  $yearRevenue = mysqli_fetch_assoc(mysqli_query($conn, $yearRevenueSql))['revenue'] ?? 0;

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
  $detailedQuery = '
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
';
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
      'amt' => $row['amt'],
      'total' => $row['total']
    ];
  }
  ?>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <div class="header-title">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?php echo $_SESSION['admin']['firstname']; ?>! Here's what's happening today.</p>
      </div>
      <div class="user-profile">
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-3 me-2"></i>
            <div>
              <strong><?php echo $_SESSION['admin']['firstname']; ?></strong>
              <div class="small text-muted">Administrator</div>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign out</a></li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-6 col-lg-3">
        <div class="card stats-card">
          <div class="card-body">
            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
              <i class="bi bi-cart-check"></i>
            </div>
            <div class="stats-content">
              <h5>TODAY'S ORDERS</h5>
              <h2><?php echo $todayOrders; ?></h2>
              <p class="text-success">₹<?php echo number_format($todayRevenue, 2); ?></p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card stats-card">
          <div class="card-body">
            <div class="stats-icon bg-success bg-opacity-10 text-success">
              <i class="bi bi-calendar-week"></i>
            </div>
            <div class="stats-content">
              <h5>THIS WEEK</h5>
              <h2><?php echo $weekOrders; ?></h2>
              <p class="text-muted">Total Orders</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card stats-card">
          <div class="card-body">
            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
              <i class="bi bi-calendar-month"></i>
            </div>
            <div class="stats-content">
              <h5>THIS MONTH</h5>
              <h2><?php echo $monthOrders; ?></h2>
              <p class="text-success">₹<?php echo number_format($monthRevenue, 2); ?></p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card stats-card">
          <div class="card-body">
            <div class="stats-icon bg-danger bg-opacity-10 text-danger">
              <i class="bi bi-calendar"></i>
            </div>
            <div class="stats-content">
              <h5>THIS YEAR</h5>
              <h2><?php echo $yearOrders; ?></h2>
              <p class="text-success">₹<?php echo number_format($yearRevenue, 2); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Orders Table -->
    <div class="card orders-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0"><i class="bi bi-table me-2"></i> Recent Orders</h5>
        </div>
        <div>
          <span class="badge" style="background-color:var(--secondary);"><?php echo count($orders); ?> Orders</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Student</th>
              <th class="order-items">Items</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($orders)) {
              foreach ($orders as $order) {
                // debug($order);
                $status = strtolower($order['status']);
                $badgeClass = "badge-{$status}";
                ?>
                <tr>
                  <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                  <td><strong><?php echo $order['student_name']; ?></strong></td>
                  <td class="order-items">
                    <div class="items-container">
                      <?php
                      $counter = 0;
                      $maxDisplay = 2;
                      foreach ($order['items'] as $item):
                        // debug($item);
                        if ($counter < $maxDisplay):
                          ?>
                          <span class="item-badge" title="<?php echo $item['quantity']; ?>x <?php echo $item['name']; ?>">
                            <?php echo $item['quantity']; ?>x <?php echo strlen($item['name']) > 15 ? substr($item['name'], 0, 15) . '...' : $item['name']; ?>
                          </span>
                        <?php
                        endif;
                        $counter++;
                      endforeach;
                      if (count($order['items']) > $maxDisplay):
                        ?>
                        <span class="item-badge more-items" title="View all items">
                          +<?php echo count($order['items']) - $maxDisplay; ?> more
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td><strong class="text-success">₹<?php echo number_format($order['amt'], 2); ?></strong></td>
                  <td><span class='badge <?php echo $badgeClass; ?>'><?php echo ucfirst($order['status']); ?></span></td>
                  <td>
                    <small><?php echo date('d M Y', strtotime($order['date'])); ?></small><br>
                    <small class="text-muted"><?php echo date('h:i A', strtotime($order['date'])); ?></small>
                  </td>
                  <td>
                    <div class="d-flex">
                      <button class="btn btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['order_id']; ?>" title="View Details">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-outline-success btn-action" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'ready')" title="Mark as Ready">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button class="btn btn-outline-danger btn-action" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')" title="Cancel Order">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                
                <?php
                // Store modal HTML separately
                $modals[] = "
                <div class='modal fade' id='orderModal{$order['order_id']}' tabindex='-1'>
                  <div class='modal-dialog modal-lg'>
                    <div class='modal-content'>
                      <div class='modal-header'>
                        <h5 class='modal-title'>Order Details #{$order['order_id']}</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <div class='modal-body'>
                        <div class='row mb-4'>
                          <div class='col-md-6'>
                            <h6>Student Information</h6>
                            <p><strong>Name:</strong> {$order['student_name']}</p>
                          </div>
                          <div class='col-md-6'>
                            <h6>Order Information</h6>
                            <p><strong>Order Date:</strong> " . date('d M Y, h:i A', strtotime($order['date'])) . "</p>
                            <p><strong>Status:</strong> <span class='badge {$badgeClass}'>" . ucfirst($order['status']) . '</span></p>
                            <p><strong>Total Amount:</strong> ₹' . number_format($order['amt'], 2) . "</p>
                          </div>
                        </div>
                        <hr>
                        <h6>Order Items</h6>
                        <div class='table-responsive'>
                          <table class='table table-sm'>
                            <thead>
                              <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>";

                foreach ($order['items'] as $item) {
                  $modals[] .= "
                  <tr>
                    <td>{$item['name']}</td>
                    <td>{$item['quantity']}</td>
                    <td>₹{$item['price']}</td>
                    <td>₹{$item['total']}</td>
                  </tr>";
                }

                $modals[] .= "
                              <tr class='table-success'>
                                <td colspan='3' class='text-end'><strong>Total:</strong></td>
                                <td><strong>₹" . number_format($order['amt'], 2) . '</strong></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>';
              }
            } else {
              echo '<tr><td colspan="7"><div class="empty-state">
                      <i class="bi bi-inbox"></i>
                      <h5>No orders found</h5>
                      <p>There are no orders to display at the moment.</p>
                    </div></td></tr>';
            }
            ?>
          </tbody>
        </table>
        <?php
          // Print all modals outside the table
          if (!empty($modals)) {
            echo implode('', $modals);
          }
          ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
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
</body>
</html>