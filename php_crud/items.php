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
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $sql = "SELECT * from items";
              $query = mysqli_query($conn,$sql);
              while($row = mysqli_fetch_assoc($query)){
                // debug($row);
                ?>
                  <tr>
                    <td><?php echo $row['id'] ?></td>
                    <td><img src="files/food.jpg" width="80px" class="rounded" alt="item"></td>
                    <td><?php echo $row['name'] ?></td>
                    <td><?php echo $row['category'] ?></td>
                    <td>₹<?php echo $row['price'] ?></td>
                    <td><?php echo $row['stockqty'] ?></td>
                    <td><span class="badge <?php if($row['status'] == 'Active'){echo 'bg-success';} else{echo 'bg-danger';}?>"><?php echo $row['status'] ?></span></td>
                    <td>
                      <button class="btn btn-sm btn-warning"><a href="edit_items.php?id=<?php echo $row['id']; ?>"><i class="bi bi-pencil-square"></i></a></button>
                      <button class="btn btn-sm btn-danger"><a href="deletedb.php?id=<?php echo $row['id']; ?>&db=items"><i class="bi bi-trash"></i></a></button>
                    </td>
                  </tr>
                <?php
              }
              // exit;
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php include 'includes/footer.php'; ?>