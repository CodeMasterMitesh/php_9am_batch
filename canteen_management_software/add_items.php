<?php 
include 'config/connection.php';
include 'includes/nav.php'; 
?>
  <style>
   
    /* Back Button */
    .btn-back {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(108, 117, 125, 0.3);
      text-decoration: none;
      color: white;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(108, 117, 125, 0.4);
      color: white;
    }
    
    /* Form Card */
    .form-card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }
    
    .form-card .card-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border-bottom: none;
      padding: 1.25rem 1.5rem;
      color: white;
    }
    
    .form-card .card-body {
      padding: 2rem;
    }
    
    /* Form Styling */
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      padding: 0.75rem 1rem;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
    }
    
    /* File Upload Styling */
    .file-upload-container {
      position: relative;
    }
    
    .file-upload-preview {
      width: 120px;
      height: 120px;
      border: 2px dashed #dee2e6;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 0.5rem;
      overflow: hidden;
      background-color: #f8f9fa;
      transition: all 0.3s ease;
    }
    
    .file-upload-preview:hover {
      border-color: var(--primary);
    }
    
    .file-upload-preview img {
      max-width: 100%;
      max-height: 100%;
      object-fit: cover;
      display: none;
    }
    
    .file-upload-placeholder {
      text-align: center;
      color: #6c757d;
    }
    
    .file-upload-placeholder i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    /* Submit Button */
    .btn-submit {
      background: linear-gradient(135deg, #198754 0%, #157347 100%);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(25, 135, 84, 0.3);
    }
    
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(25, 135, 84, 0.4);
    }
    
    /* Form Row Spacing */
    .form-row {
      margin-bottom: 1.5rem;
    }
    
    /* Price Input */
    .price-input {
      position: relative;
    }
    
    .price-input:before {
      content: "₹";
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      font-weight: 600;
      z-index: 1;
    }
    
    .price-input .form-control {
      padding-left: 30px;
    }
    
    /* Status Badge Preview */
    .status-preview {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: 500;
      margin-top: 0.5rem;
    }
    
    .status-active {
      background-color: #d4edda;
      color: #155724;
    }
    
    .status-inactive {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
      }
      
      .form-card .card-body {
        padding: 1.5rem;
      }
    }
    
    /* Animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .form-card {
      animation: fadeIn 0.5s ease-out;
    }
  </style>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h1><i class="bi bi-plus-circle me-2"></i>Add New Item</h1>
        <p>Create a new product for your inventory</p>
      </div>
      <a href="items.php" class="btn-back">
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
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