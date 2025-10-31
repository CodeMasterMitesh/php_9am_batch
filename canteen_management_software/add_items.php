<?php 
include 'config/connection.php';
include_once __DIR__ . '/includes/auth.php';
// Admin guard is also in nav.php; this include gives us csrf_input()
include 'includes/nav.php'; 
?>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h1><i class="bi bi-plus-circle me-2"></i>Add New Item</h1>
        <p>Create a new product for your inventory</p>
      </div>
  <a href="items.php" class="btn-back-secondary">
        <i class="bi bi-arrow-left"></i>
        Back to Items
      </a>
    </div>

    <!-- Add Item Form -->
    <div class="card form-card">
      <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Item Details</h5>
      </div>
      <div class="card-body">
        <form action="insertdb.php" method="POST" enctype="multipart/form-data" id="itemForm">
          <?php echo csrf_input(); ?>
          <input type="hidden" name="db" value="items">
          
          <div class="row g-4">
            <!-- Basic Information -->
            <div class="col-12">
              <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Item Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Enter item name" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Category <span class="text-danger">*</span></label>
              <input type="text" name="category" class="form-control" placeholder="Enter category" required>
            </div>
            
            <!-- Pricing & Stock -->
            <div class="col-12 mt-4">
              <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-currency-rupee me-2"></i>Pricing & Stock</h6>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
              <div class="price-input">
                <input type="number" name="price" step="0.01" class="form-control" placeholder="0.00" min="0" required>
              </div>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
              <input type="number" name="stockqty" class="form-control" placeholder="0" min="0" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select" id="statusSelect" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
              <div id="statusPreview" class="status-preview status-active">
                <i class="bi bi-check-circle"></i>
                Active
              </div>
            </div>
            
            <!-- Image Upload -->
            <div class="col-12 mt-4">
              <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-image me-2"></i>Product Image</h6>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Upload Image</label>
              <input type="file" name="image" class="form-control" id="imageInput" accept="image/*">
              <small class="text-muted">Recommended: Square image, max 2MB</small>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Image Preview</label>
              <div class="file-upload-preview" id="imagePreview">
                <div class="file-upload-placeholder">
                  <i class="bi bi-cloud-arrow-up"></i>
                  <div>No image selected</div>
                </div>
                <img id="previewImage" src="" alt="Preview">
              </div>
            </div>
            
            <!-- Additional Information -->
            <div class="col-12 mt-4">
              <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-card-text me-2"></i>Additional Information</h6>
            </div>
            
            <div class="col-12">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="4" placeholder="Enter any additional notes or description..."></textarea>
            </div>
            
            <!-- Submit Button -->
            <div class="col-12 mt-4">
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-submit text-white">
                  <i class="bi bi-save me-2"></i>
                  Save Item
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Image preview functionality
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
        statusPreview.className = `status-preview status-${status.toLowerCase()}`;
        statusPreview.innerHTML = `
          <i class="bi bi-${status === 'Active' ? 'check-circle' : 'x-circle'}"></i>
          ${status}
        `;
      });
      
      // Form validation
      const form = document.getElementById('itemForm');
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
      });
    });
  </script>
<?php include 'includes/footer.php'; ?>