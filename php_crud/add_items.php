<?php 
include 'config/connection.php';
include 'includes/nav.php'; 

?>

  <!-- Main Content -->
  <div class="container my-4 flex-grow-1">
    <h1 class="mb-4">Manage Items</h1>

    <!-- Add Item Form -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-2">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Item</h5>
        <a href="items.php" class="btn btn-dark float-right">Back</a>
      </div>
      <div class="card-body">
        <form action="insertdb.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="db" value="items">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Item Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <input type="text" name="category" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Price</label>
              <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Stock Qty</label>
              <input type="number" name="stockqty" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Image</label>
              <input type="file" name="image" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3"></textarea>
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
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php include 'includes/footer.php'; ?>