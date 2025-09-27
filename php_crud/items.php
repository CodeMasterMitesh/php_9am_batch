<?php include 'includes/nav.php'; ?>
 <!-- Main Content -->
  <div class="container my-4 flex-grow-1">
    <h1 class="mb-4">Manage Items</h1>

    <!-- Items Table -->
    <div class="card shadow-sm border-0">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center p-2">
        <h5 class="mb-0"><i class="bi bi-table"></i> All Items</h5>
        <a href="add_items.php" class="btn btn-primary float-right">Add Items</a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Image</th>
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock Qty</th>
              <th>Remarks</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><img src="https://via.placeholder.com/50" class="rounded" alt="item"></td>
              <td>Pizza</td>
              <td>Food</td>
              <td>₹199.00</td>
              <td>50</td>
              <td>Cheese Burst</td>
              <td><span class="badge bg-success">Active</span></td>
              <td>
                <button class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>2</td>
              <td><img src="https://via.placeholder.com/50" class="rounded" alt="item"></td>
              <td>Burger</td>
              <td>Food</td>
              <td>₹99.00</td>
              <td>120</td>
              <td>Veg Loaded</td>
              <td><span class="badge bg-secondary">Inactive</span></td>
              <td>
                <button class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>3</td>
              <td><img src="https://via.placeholder.com/50" class="rounded" alt="item"></td>
              <td>Pasta</td>
              <td>Food</td>
              <td>₹149.00</td>
              <td>75</td>
              <td>White Sauce</td>
              <td><span class="badge bg-success">Active</span></td>
              <td>
                <button class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php include 'includes/footer.php'; ?>