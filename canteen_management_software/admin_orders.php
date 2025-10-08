<?php 
include 'includes/nav.php';
include 'config/connection.php';

// 🟩 Handle search
$search = "";
if (isset($_GET['search'])) {
  $search = trim($_GET['search']);
}

// 🟩 Fetch all orders (with optional search)
$sql = "
  SELECT o.id, s.firstname AS student, i.name AS item, o.qty, o.amt, o.status, o.date
  FROM `order` o
  JOIN users s ON o.uid = s.id
  JOIN items i ON o.pid = i.id
  WHERE s.firstname LIKE '%$search%' 
     OR i.name LIKE '%$search%' 
     OR o.status LIKE '%$search%'
  ORDER BY o.date DESC
";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-4 flex-grow-1">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">📋 All Orders</h2>
    
    <!-- 🔍 Search Form -->
    <form class="d-flex" method="GET" action="">
      <input 
        class="form-control me-2" 
        type="search" 
        name="search" 
        placeholder="Search by student, item, or status"
        value="<?php echo htmlspecialchars($search); ?>"
      >
      <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <!-- Orders Table -->
  <div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white fw-bold">
      <i class="bi bi-basket me-2"></i> Orders List
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
          if(mysqli_num_rows($result) > 0){
            $i = 1;
            while($row = mysqli_fetch_assoc($result)){
              echo "<tr>
                <td>{$i}</td>
                <td>{$row['student']}</td>
                <td>{$row['item']}</td>
                <td>{$row['qty']}</td>
                <td>{$row['amt']}</td>
                <td>
                  <span class='badge 
                    ".($row['status']=='Delivered' ? 'bg-success' : 
                    ($row['status']=='Preparing' ? 'bg-warning text-dark' : 
                    'bg-secondary'))."'>
                    {$row['status']}
                  </span>
                </td>
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