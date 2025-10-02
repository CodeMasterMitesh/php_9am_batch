<?php 
include 'config/connection.php';
include 'includes/nav.php';
$pid = $_GET['id'];

$sql = "SELECT * FROM items where id = $pid";
$query = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($query);

?>
  <!-- Main Content -->
  <div class="container my-4 flex-grow-1">
    <h1 class="mb-4">Manage Items</h1>
    <!-- Add Item Form -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-2">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Update Item</h5>
        <a href="items.php" class="btn btn-dark float-right">Back</a>
      </div>
      <div class="card-body">
        <form action="updatedb.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="db" value="items">
        <input type="hidden" name="id" value="<?php echo $pid; ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Item Name</label>
              <input type="text" name="name" value="<?php echo $row['name']; ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <input type="text" name="category" value="<?php echo $row['category']; ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Price</label>
              <input type="number" name="price" value="<?php echo $row['price']; ?>" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Stock Qty</label>
              <input type="number" value="<?php echo $row['stockqty']; ?>" name="stockqty" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Image</label>
              <input type="file" name="image" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3"><?php echo $row['remarks']; ?></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php include 'includes/footer.php'; ?>