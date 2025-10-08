<?php 
include 'includes/nav.php';

// -------------------------------
// 📊 Get Order Statistics
// -------------------------------
$todaySql = "SELECT COUNT(*) AS total FROM `order` WHERE DATE(date) = CURDATE()";
$weekSql = "SELECT COUNT(*) AS total FROM `order` WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
$monthSql = "SELECT COUNT(*) AS total FROM `order` WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())";
$yearSql = "SELECT COUNT(*) AS total FROM `order` WHERE YEAR(date) = YEAR(CURDATE())";

$todayOrders  = mysqli_fetch_assoc(mysqli_query($conn, $todaySql))['total'] ?? 0;
$weekOrders   = mysqli_fetch_assoc(mysqli_query($conn, $weekSql))['total'] ?? 0;
$monthOrders  = mysqli_fetch_assoc(mysqli_query($conn, $monthSql))['total'] ?? 0;
$yearOrders   = mysqli_fetch_assoc(mysqli_query($conn, $yearSql))['total'] ?? 0;

// -------------------------------
// 📋 Fetch All Orders
// -------------------------------
$orderQuery = "
    SELECT o.id, s.firstname AS student, i.name AS item, o.qty, o.amt, o.status, o.date
    FROM `order` o
    JOIN users s ON o.uid = s.id
    JOIN items i ON o.pid = i.id
    ORDER BY o.date DESC
";
$orderResult = mysqli_query($conn, $orderQuery);
?>

<div class="container my-4 flex-grow-1">
  <h2 class="mb-4 text-center fw-bold">📊 Order Overview Dashboard</h2>

  <!-- Statistics Cards -->
  <div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-primary text-white">
        <div class="card-body">
          <h5 class="card-title">Today</h5>
          <p class="display-6 fw-bold"><?php echo $todayOrders; ?></p>
          <p class="mb-0">Total Orders</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-success text-white">
        <div class="card-body">
          <h5 class="card-title">This Week</h5>
          <p class="display-6 fw-bold"><?php echo $weekOrders; ?></p>
          <p class="mb-0">Total Orders</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-warning text-white">
        <div class="card-body">
          <h5 class="card-title">This Month</h5>
          <p class="display-6 fw-bold"><?php echo $monthOrders; ?></p>
          <p class="mb-0">Total Orders</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow border-0 bg-danger text-white">
        <div class="card-body">
          <h5 class="card-title">This Year</h5>
          <p class="display-6 fw-bold"><?php echo $yearOrders; ?></p>
          <p class="mb-0">Total Orders</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders Table -->
  <div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white fw-bold">
      <i class="bi bi-table me-2"></i> All Orders
    </div>
    <div class="card-body table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Amount (₹)</th>
            <th>Status</th>
            <th>Order Date</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if(mysqli_num_rows($orderResult) > 0){
            $i = 1;
            while($row = mysqli_fetch_assoc($orderResult)){
              echo "<tr>
                <td>{$i}</td>
                <td>{$row['student']}</td>
                <td>{$row['item']}</td>
                <td>{$row['qty']}</td>
                <td>{$row['amt']}</td>
                <td><span class='badge bg-info text-dark'>{$row['status']}</span></td>
                <td>".date('d M Y, h:i A', strtotime($row['date']))."</td>
              </tr>";
              $i++;
            }
          } else {
            echo "<tr><td colspan='7' class='text-center text-muted'>No orders found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>