<?php 
include 'config/connection.php';
include 'includes/nav.php';
$pid = $_GET['id'];

$sql = "SELECT * FROM items where id = $pid";
$query = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($query);

?>
  <style>
    a{
      text-decoration:none;
    }
    /* Status Preview for Edit Page */
    .status-preview-edit {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: 500;
      margin-top: 0.5rem;
      transition: all 0.3s ease;
    }
    
    /* Current Image Preview */
    .current-image-container {
      margin-top: 1rem;
      text-align: center;
    }
    
    .current-image-label {
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .current-image {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
    }
    
    .current-image:hover {
      transform: scale(1.8);
      z-index: 10;
      position: relative;
      border-color: var(--primary);
    }
    
    /* Update Button Specific */
    .btn-update {
      background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(253, 126, 20, 0.3);
    }
    
    .btn-update:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(253, 126, 20, 0.4);
      color: white;
    }
    
    /* Form Section Enhancements */
    .form-section-edit {
      border-left: 4px solid var(--primary);
      padding-left: 1rem;
      margin: 1.5rem 0;
    }
    
    .form-section-edit h6 {
      color: var(--primary);
      font-weight: 600;
    }
    
    /* Read-only Fields Styling */
    .form-control[readonly] {
      background-color: #f8f9fa;
      border-color: #e9ecef;
      color: #6c757d;
    }
    
    /* Image Comparison */
    .image-comparison {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
      margin-top: 1rem;
    }
    
    .image-box {
      text-align: center;
      flex: 1;
    }
    
    .image-box h6 {
      font-size: 0.875rem;
      color: #495057;
      margin-bottom: 0.5rem;
    }
    
    /* Success State for Form */
    .form-control.success {
      border-color: #198754;
      box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.1);
    }
    
    /* Warning State for Low Stock */
    .stock-warning {
      color: #dc3545;
      font-weight: 600;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }
    
    /* Responsive Image Layout */
    @media (max-width: 768px) {
      .image-comparison {
        flex-direction: column;
      }
      
      .current-image:hover {
        transform: scale(1.2);
      }
    }
  </style>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h1><i class="bi bi-pencil-square me-2"></i>Edit Item</h1>
        <p>Update product information and inventory details</p>
      </div>
      <a href="items.php" class="btn-back text-white">
        <i class="bi bi-arrow-left"></i>
        Back to Items
      </a>
    </div>

    <!-- Edit Item Form -->
    <div class="card form-card">
      <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Edit Item Details</h5>
      </div>
      <div class="card-body">
        <form action="updatedb.php" method="POST" enctype="multipart/form-data" id="editItemForm">
          <input type="hidden" name="db" value="items">
          <input type="hidden" name="id" value="<?php echo $pid; ?>">
          
          <div class="row g-4">
            <!-- Basic Information -->
            <div class="col-12">
              <div class="form-section-edit">
                <h6><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
              </div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Item Name <span class="text-danger">*</span></label>
              <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control" placeholder="Enter item name" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Category <span class="text-danger">*</span></label>
              <input type="text" name="category" value="<?php echo htmlspecialchars($row['category']); ?>" class="form-control" placeholder="Enter category" required>
            </div>
            
            <!-- Pricing & Stock -->
            <div class="col-12">
              <div class="form-section-edit">
                <h6><i class="bi bi-currency-rupee me-2"></i>Pricing & Stock</h6>
              </div>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
              <div class="price-input">
                <input type="number" name="price" value="<?php echo $row['price']; ?>" step="0.01" class="form-control" placeholder="0.00" min="0" required>
              </div>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
              <input type="number" name="stockqty" value="<?php echo $row['stockqty']; ?>" class="form-control <?php echo $row['stockqty'] <= 5 ? 'stock-warning' : ''; ?>" placeholder="0" min="0" required>
              <?php if($row['stockqty'] <= 5): ?>
                <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Low stock alert</small>
              <?php endif; ?>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select" id="statusSelect" required>
                <option value="Active" <?php echo $row['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $row['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
              </select>
              <div id="statusPreview" class="status-preview-edit <?php echo $row['status'] == 'Active' ? 'status-active' : 'status-inactive'; ?>">
                <i class="bi bi-<?php echo $row['status'] == 'Active' ? 'check-circle' : 'x-circle'; ?>"></i>
                <?php echo $row['status']; ?>
              </div>
            </div>
            
            <!-- Image Management -->
            <div class="col-12">
              <div class="form-section-edit">
                <h6><i class="bi bi-image me-2"></i>Image Management</h6>
              </div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Update Image</label>
              <input type="file" name="image" class="form-control" id="imageInput" accept="image/*">
              <small class="text-muted">Leave empty to keep current image</small>
              
              <div class="file-upload-preview mt-3" id="imagePreview">
                <div class="file-upload-placeholder">
                  <i class="bi bi-cloud-arrow-up"></i>
                  <div>New image preview</div>
                </div>
                <img id="previewImage" src="" alt="New preview">
              </div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Current Image</label>
              <div class="current-image-container">
                <?php if(!empty($row['image'])): ?>
                  <img src="<?php echo $row['image']; ?>" alt="Current item image" class="current-image">
                  <div class="current-image-label">Click to enlarge</div>
                <?php else: ?>
                  <div class="file-upload-preview">
                    <div class="file-upload-placeholder">
                      <i class="bi bi-image"></i>
                      <div>No image</div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Additional Information -->
            <div class="col-12">
              <div class="form-section-edit">
                <h6><i class="bi bi-card-text me-2"></i>Additional Information</h6>
              </div>
            </div>
            
            <div class="col-12">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="4" placeholder="Enter any additional notes or description..."><?php echo htmlspecialchars($row['remarks']); ?></textarea>
            </div>
            
            <!-- System Information (Read-only) -->
            <div class="col-12">
              <div class="form-section-edit">
                <h6><i class="bi bi-gear me-2"></i>System Information</h6>
              </div>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Item ID</label>
              <input type="text" class="form-control" value="#<?php echo $pid; ?>" readonly>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Last Updated</label>
              <input type="text" class="form-control" value="<?php echo date('M j, Y g:i A'); ?>" readonly>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Database Table</label>
              <input type="text" class="form-control" value="items" readonly>
            </div>
            
            <!-- Submit Button -->
            <div class="col-12 mt-4">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <a href="items.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancel
                  </a>
                </div>
                <div class="d-flex gap-2">
                  <a href="deletedb.php?id=<?php echo $pid; ?>&db=items" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                    <i class="bi bi-trash me-2"></i>
                    Delete
                  </a>
                  <button type="submit" class="btn btn-update text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    Update Item
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Image preview functionality for new image
      const imageInput = document.getElementById('imageInput');
      const previewImage = document.getElementById('previewImage');
      const imagePreview = document.getElementById('imagePreview');
      const placeholder = imagePreview.querySelector('.file-upload-placeholder');
      
      imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
            placeholder.style.display = 'none';
          }
          reader.readAsDataURL(file);
        } else {
          previewImage.style.display = 'none';
          placeholder.style.display = 'block';
        }
      });
      
      // Status preview update
      const statusSelect = document.getElementById('statusSelect');
      const statusPreview = document.getElementById('statusPreview');
      
      statusSelect.addEventListener('change', function() {
        const status = this.value;
        statusPreview.className = `status-preview-edit status-${status.toLowerCase()}`;
        statusPreview.innerHTML = `
          <i class="bi bi-${status === 'Active' ? 'check-circle' : 'x-circle'}"></i>
          ${status}
        `;
      });
      
      // Form validation
      const form = document.getElementById('editItemForm');
      form.addEventListener('submit', function(e) {
        const price = form.querySelector('input[name="price"]');
        const stock = form.querySelector('input[name="stockqty"]');
        
        if (parseFloat(price.value) < 0) {
          e.preventDefault();
          alert('Price cannot be negative');
          price.focus();
          return false;
        }
        
        if (parseInt(stock.value) < 0) {
          e.preventDefault();
          alert('Stock quantity cannot be negative');
          stock.focus();
          return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Updating...';
        submitBtn.disabled = true;
      });
      
      // Add success class to fields with existing values
      const formControls = form.querySelectorAll('.form-control');
      formControls.forEach(control => {
        if (control.value && control.type !== 'file') {
          control.classList.add('success');
        }
      });
    });
  </script>
<?php include 'includes/footer.php'; ?>