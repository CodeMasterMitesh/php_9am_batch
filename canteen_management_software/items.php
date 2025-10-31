<?php 
include 'config/connection.php';
include_once __DIR__ . '/includes/auth.php';
include 'includes/nav.php'; 
?>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h1><i class="bi bi-box-seam me-2"></i>Manage Items</h1>
        <p>Add, edit, and manage your product inventory</p>
      </div>
      <a href="add_items.php" class="btn btn-add-item text-white">
        <i class="bi bi-plus-circle"></i>
        Add New Item
      </a>
    </div>
    
    <!-- Items Table -->
    <div class="card items-card">
      <div class="card-header">
        <div>
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>All Items</h5>
        </div>
        <div class="text-muted">
          <i class="bi bi-info-circle me-1"></i>
          <span id="itemCount">
            <?php 
              $countQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM items");
              $countData = mysqli_fetch_assoc($countQuery);
              echo $countData['total'] . " items";
            ?>
          </span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Image</th>
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock Qty</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $sql = "SELECT * from items";
              $query = mysqli_query($conn,$sql);
              
              if(mysqli_num_rows($query) > 0) {
                while($row = mysqli_fetch_assoc($query)){
                  // Determine stock status class
                  $stockClass = '';
                  if ($row['stockqty'] <= 5) {
                    $stockClass = 'stock-low';
                  } elseif ($row['stockqty'] <= 15) {
                    $stockClass = 'stock-medium';
                  } else {
                    $stockClass = 'stock-high';
                  }
                  ?>
                    <tr>
                      <td>
                        <strong>#<?php echo $row['id'] ?></strong>
                      </td>
                      <td>
                        <img src="<?php echo $row['image']; ?>" class="item-image" alt="<?php echo $row['name']; ?>">
                      </td>
                      <td>
                        <div>
                          <strong><?php echo $row['name'] ?></strong>
                          <?php if ($row['stockqty'] <= 5): ?>
                            <span class="badge bg-danger ms-1">Low Stock</span>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td>
                        <span class="category-badge"><?php echo $row['category'] ?></span>
                      </td>
                      <td>
                        <span class="price">₹<?php echo $row['price'] ?></span>
                      </td>
                      <td>
                        <span class="<?php echo $stockClass; ?>"><?php echo $row['stockqty'] ?></span>
                      </td>
                      <td>
                        <span class="badge <?php if($row['status'] == 'Active'){echo 'badge-active';} else{echo 'badge-inactive';}?>">
                          <?php echo $row['status'] ?>
                        </span>
                      </td>
                      <td>
                        <div class="d-flex">
                          <a href="edit_items.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Edit Item">
                            <i class="bi bi-pencil-square"></i>
                          </a>
                          <form action="deletedb.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?')" class="d-inline">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="db" value="items">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn-action btn-delete" title="Delete Item">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php
                }
              } else {
                echo "<tr><td colspan='8' class='text-center py-4'>
                  <div class='empty-state'>
                    <i class='bi bi-inbox'></i>
                    <h4>No Items Found</h4>
                    <p>Get started by adding your first product</p>
                    <a href='add_items.php' class='btn btn-primary mt-2'>
                      <i class='bi bi-plus-circle me-1'></i>Add Item
                    </a>
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
        row.style.animationDelay = `${index * 0.05}s`;
      });
      
      // Update item count in header
      const itemCount = document.querySelectorAll('tbody tr').length;
      if (itemCount > 0) {
        document.getElementById('itemCount').textContent = `${itemCount} items`;
      }
    });
  </script>
<?php include 'includes/footer.php'; ?>