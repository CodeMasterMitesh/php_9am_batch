<?php
include 'config/connection.php';
// debug($_SESSION);
if(!isset($_SESSION['user']) || ($_SESSION['user']['type'] != 'student' && $_SESSION['user']['type'] != 'customer')){
    echo "<script>
    alert('Unauthorized');
    location.href = '404.php';
  </script>";
}

// Handle AJAX requests for cart operations
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    switch($_POST['action']) {
        case 'add_to_cart':
            $pid = $_POST['pid'];
            $uid = $_SESSION['user']['id'];
            
            // Check if user has an active cart
            $cart_sql = "SELECT id FROM cart WHERE user_id = '$uid'";
            $cart_query = mysqli_query($conn, $cart_sql);
            
            if(mysqli_num_rows($cart_query) > 0) {
                $cart = mysqli_fetch_assoc($cart_query);
                $cart_id = $cart['id'];
            } else {
                // Create new cart
                $cart_sql = "INSERT INTO cart (user_id) VALUES ('$uid')";
                if(mysqli_query($conn, $cart_sql)) {
                    $cart_id = mysqli_insert_id($conn);
                } else {
                    $response['message'] = 'Error creating cart: ' . mysqli_error($conn);
                    echo json_encode($response);
                    exit;
                }
            }
            
            // Check if item already exists in cart
            $item_sql = "SELECT id, qty FROM cart_items WHERE cart_id = '$cart_id' AND pid = '$pid'";
            $item_query = mysqli_query($conn, $item_sql);
            
            if(mysqli_num_rows($item_query) > 0) {
                // Update quantity
                $item = mysqli_fetch_assoc($item_query);
                $new_qty = $item['qty'] + 1;
                $update_sql = "UPDATE cart_items SET qty = '$new_qty' WHERE id = '{$item['id']}'";
                
                if(mysqli_query($conn, $update_sql)) {
                    $response['success'] = true;
                    $response['message'] = 'Item quantity updated in cart';
                } else {
                    $response['message'] = 'Error updating cart: ' . mysqli_error($conn);
                }
            } else {
                // Add new item to cart
                $insert_sql = "INSERT INTO cart_items (cart_id, pid, qty) VALUES ('$cart_id', '$pid', 1)";
                
                if(mysqli_query($conn, $insert_sql)) {
                    $response['success'] = true;
                    $response['message'] = 'Item added to cart';
                } else {
                    $response['message'] = 'Error adding to cart: ' . mysqli_error($conn);
                }
            }
            break;
            
        case 'update_cart_item':
            $cart_item_id = $_POST['cart_item_id'];
            $qty = $_POST['qty'];
            
            if($qty <= 0) {
                // Remove item if quantity is 0 or less
                $delete_sql = "DELETE FROM cart_items WHERE id = '$cart_item_id'";
                if(mysqli_query($conn, $delete_sql)) {
                    $response['success'] = true;
                    $response['message'] = 'Item removed from cart';
                } else {
                    $response['message'] = 'Error removing item: ' . mysqli_error($conn);
                }
            } else {
                // Update quantity
                $update_sql = "UPDATE cart_items SET qty = '$qty' WHERE id = '$cart_item_id'";
                if(mysqli_query($conn, $update_sql)) {
                    $response['success'] = true;
                    $response['message'] = 'Cart updated';
                } else {
                    $response['message'] = 'Error updating cart: ' . mysqli_error($conn);
                }
            }
            break;
            
        case 'remove_from_cart':
            $cart_item_id = $_POST['cart_item_id'];
            $delete_sql = "DELETE FROM cart_items WHERE id = '$cart_item_id'";
            
            if(mysqli_query($conn, $delete_sql)) {
                $response['success'] = true;
                $response['message'] = 'Item removed from cart';
            } else {
                $response['message'] = 'Error removing item: ' . mysqli_error($conn);
            }
            break;
            
        case 'get_cart_count':
            $uid = $_SESSION['user']['id'];
            $count_sql = "SELECT SUM(ci.qty) as total_items 
                         FROM cart_items ci 
                         JOIN cart c ON ci.cart_id = c.id 
                         WHERE c.user_id = '$uid'";
            $count_query = mysqli_query($conn, $count_sql);
            $count_result = mysqli_fetch_assoc($count_query);
            
            $response['success'] = true;
            $response['count'] = $count_result['total_items'] ? $count_result['total_items'] : 0;
            break;

        case 'add_order':
        $uid = $_SESSION['user']['id'];
        $response = ['success' => false, 'message' => ''];
        
        // Get active cart
        $cart_sql = "SELECT id FROM cart WHERE user_id = '$uid'";
        $cart_query = mysqli_query($conn, $cart_sql);
        
        if(mysqli_num_rows($cart_query) === 0) {
            $response['message'] = 'No active cart found!';
            echo json_encode($response);
            exit;
        }
        
        $cart = mysqli_fetch_assoc($cart_query);
        $cart_id = $cart['id'];
        
        // Get cart items
        $items_sql = "SELECT ci.pid, ci.qty, i.price 
                    FROM cart_items ci 
                    JOIN items i ON ci.pid = i.id 
                    WHERE ci.cart_id = '$cart_id'";
        $items_query = mysqli_query($conn, $items_sql);
        
        if(mysqli_num_rows($items_query) === 0) {
            $response['message'] = 'No items in cart!';
            echo json_encode($response);
            exit;
        }
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Calculate total amount
            $total_amount = 0;
            $order_items = [];
            
            while($item = mysqli_fetch_assoc($items_query)) {
                $item_total = $item['price'] * $item['qty'];
                $total_amount += $item_total;
                $order_items[] = $item;
            }
            
            // Insert into order table
            $order_sql = "INSERT INTO `order` (uid, amt, status) 
                        VALUES ('$uid', '$total_amount', 'Received')";
            
            if(!mysqli_query($conn, $order_sql)) {
                throw new Exception('Error creating order: ' . mysqli_error($conn));
            }
            
            $order_id = mysqli_insert_id($conn);
            
            // Insert into order_items table
            foreach($order_items as $item) {
                $pid = $item['pid'];
                $qty = $item['qty'];
                $price = $item['price'];
                $amt = $price * $qty;
                
                $order_item_sql = "INSERT INTO `order_items` (order_id, product_id, quantity, price, total) 
                                  VALUES ('$order_id', '$pid', '$qty', '$price', '$amt')";
                
                if(!mysqli_query($conn, $order_item_sql)) {
                    throw new Exception('Error creating order item: ' . mysqli_error($conn));
                }
            }
            
            // Update cart status to completed
            $deleteCart = "DELETE FROM cart WHERE id = '$cart_id'";
            if(!mysqli_query($conn, $deleteCart)) {
              throw new Exception('Error updating cart: ' . mysqli_error($conn));
            }
            $deleteCartItems = "DELETE FROM cart_items WHERE cart_id = '$cart_id'";
            if(!mysqli_query($conn, $deleteCartItems)) {
              throw new Exception('Error updating cart: ' . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            $response['success'] = true;
            $response['message'] = 'Order placed successfully!';
            $response['order_id'] = $order_id;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $response['message'] = $e->getMessage();
        }
        
        // echo json_encode($response);
        break;

    }
    
    echo json_encode($response);
    exit;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['place_order'])) {
    $uid = $_POST['uid'];
    
    // Get active cart
    $cart_sql = "SELECT id FROM cart WHERE user_id = '$uid";
    $cart_query = mysqli_query($conn, $cart_sql);
    
    if(mysqli_num_rows($cart_query) > 0) {
        $cart = mysqli_fetch_assoc($cart_query);
        $cart_id = $cart['id'];
        
        // Get cart items
        $items_sql = "SELECT ci.pid, ci.qty, i.price 
                     FROM cart_items ci 
                     JOIN items i ON ci.pid = i.id 
                     WHERE ci.cart_id = '$cart_id'";
        $items_query = mysqli_query($conn, $items_sql);
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            while($item = mysqli_fetch_assoc($items_query)) {
                $pid = $item['pid'];
                $qty = $item['qty'];
                $price = $item['price'];
                $amt = $price * $qty;
                
                // Insert into orders
                $order_sql = "INSERT INTO `order`(`pid`,`user_id`,`qty`,`amt`) VALUES('$pid','$uid','$qty','$amt')";
                if(!mysqli_query($conn, $order_sql)) {
                    throw new Exception('Error creating order: ' . mysqli_error($conn));
                }
            }
            
            // Update cart status to completed
            $update_cart_sql = "UPDATE cart SET status = 'completed' WHERE id = '$cart_id'";
            if(!mysqli_query($conn, $update_cart_sql)) {
                throw new Exception('Error updating cart: ' . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            echo "<script>
                alert('Order placed successfully!');
                window.location.href = 'orders.php';
            </script>";
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href = 'menu.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('No active cart found!');
            window.location.href = 'menu.php';
        </script>";
    }
}
?>
<?php include 'includes/studentNav.php'; ?>
<style>
  @media print {
    body * {
        visibility: hidden;
    }
    .invoice-container, .invoice-container * {
        visibility: visible;
    }
    .invoice-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .modal-footer, .modal-header .btn-close {
        display: none !important;
    }
}
</style>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-title">
        <h1><i class="bi bi-menu-button-wide me-2"></i>Our Food Menu</h1>
        <p>Discover our delicious selection of café favorites</p>
      </div>
    </div>

    <!-- User Info Bar -->
    <div class="user-info-bar">
      <div class="user-details">
        <div class="user-avatar">
          <i class="bi bi-person-circle"></i>
        </div>
        <div class="user-text">
          <h4><?php echo $_SESSION['user']['firstname']; ?></h4>
          <p><?php echo ucfirst($_SESSION['user']['type']); ?> Student</p>
        </div>
      </div>
      <div class="cart-indicator">
        <button class="cart-btn" data-bs-toggle="modal" data-bs-target="#cartModal">
          <i class="bi bi-cart4"></i>
          View Cart
          <span id="cartCount" class="cart-count">0</span>
        </button>
      </div>
    </div>

    <!-- Menu Section -->
    <div class="row g-4">
      <?php 
        $sql = "SELECT * FROM items WHERE status LIKE '%Active%'";
        $query = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($query)){
        ?>
          <div class="col-lg-4 col-md-6">
            <div class="card menu-card shadow-sm h-100">
              <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="Food Image">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo $row['name']; ?></h5>
                <p class="card-category"><strong>Category:</strong> <?php echo $row['category']; ?></p>
                <p class="card-price">₹ <?php echo $row['price']; ?></p>
                <p class="card-remarks"><?php echo $row['remarks']; ?></p>
                <button class="btn add-to-cart-btn addToCart"
                        data-pid="<?php echo $row['id']; ?>"
                        data-name="<?php echo $row['name']; ?>"
                        data-price="<?php echo $row['price']; ?>"
                        data-image="<?php echo $row['image']; ?>">
                  <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
        <?php
        }
        ?>
    </div>
  </div>

  <!-- Cart Modal -->
  <div class="modal fade cart-modal" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart4 me-2"></i>Your Cart</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="cartContent">
          <!-- Cart content will be loaded here -->
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
          <button class="btn btn-success" id="proceedToCheckout">Proceed to Checkout</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Checkout Modal -->
  <div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-header">
          <h5 class="modal-title" id="checkoutModalLabel">Complete Your Payment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="paymentForm" action="" method="POST">
          <input type="hidden" name="uid" value="<?php echo $_SESSION['user']['id']; ?>">
          <input type="hidden" name="place_order" value="1">
          <div class="modal-body">
            <div id="checkoutItems">
            </div>
            <div class="mb-3">
              <label class="form-label">Total Amount (₹)</label>
              <input type="number" class="form-control" id="totalAmount" name="totalAmount" readonly>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Pay Now</button>
          </div>
        </form>
      </div>
    </div>
  </div>
   <!-- Button to Open Modal -->
<!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal">
  View Invoice
</button> -->

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div class="invoice-container" id="invoiceContainer">
          
          <!-- Invoice Header -->
          <div class="invoice-header mb-4">
            <div class="row">
              <div class="col-md-6">
                <h2>INVOICE</h2>
                <p class="mb-1"><strong>Invoice #:</strong> <span id="invoiceNumber">INV-001</span></p>
                <p class="mb-1"><strong>Date:</strong> <span id="invoiceDate"></span></p>
              </div>
              <div class="col-md-6 text-end">
                <h4>College Canteen</h4>
                <p class="mb-1">ABC University Campus</p>
                <p class="mb-1">New Delhi, 110001</p>
                <p class="mb-1">Phone: +91 9876543210</p>
              </div>
            </div>
          </div>

          <!-- Student and Payment Info -->
          <div class="row mb-4">
            <div class="col-md-6">
              <h5>Bill To:</h5>
              <p class="mb-1"><strong id="studentName">Student Name</strong></p>
              <p class="mb-1" id="studentId">Student ID: S12345</p>
              <p class="mb-1" id="studentCourse">Course: B.Tech Computer Science</p>
            </div>
            <div class="col-md-6 text-end">
              <h5>Payment Details:</h5>
              <p class="mb-1"><strong>Payment Method:</strong> Cash</p>
              <p class="mb-1"><strong>Payment Date:</strong> <span id="paymentDate"></span></p>
              <p class="mb-1"><strong>Transaction ID:</strong> <span id="transactionId">TXN-001</span></p>
            </div>
          </div>

          <!-- Invoice Table -->
          <div class="table-responsive">
            <table class="table table-bordered invoice-table">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Item Description</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-end">Unit Price (₹)</th>
                  <th class="text-end">Amount (₹)</th>
                </tr>
              </thead>
              <tbody id="invoiceItems">
                <!-- Invoice items will be populated here -->
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                  <td class="text-end"><strong id="invoiceSubtotal">0.00</strong></td>
                </tr>
                <tr>
                  <td colspan="4" class="text-end"><strong>GST (5%):</strong></td>
                  <td class="text-end"><strong id="invoiceGst">0.00</strong></td>
                </tr>
                <tr class="invoice-total">
                  <td colspan="4" class="text-end"><strong>Total:</strong></td>
                  <td class="text-end"><strong id="invoiceTotal">0.00</strong></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Payment Terms -->
          <div class="row mt-4">
            <div class="col-md-12">
              <p class="mb-1"><strong>Payment Terms:</strong></p>
              <p class="mb-1">Payment is due within 15 days. Please make checks payable to College Canteen.</p>
            </div>
          </div>

          <!-- Thank You Message -->
          <div class="row mt-4">
            <div class="col-md-12 text-center">
              <p>Thank you for your business!</p>
            </div>
          </div>

        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer justify-content-center">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary me-2" id="printInvoice">
          <i class="fas fa-print me-1"></i> Print Invoice
        </button>
        <button class="btn btn-success" id="downloadInvoice">
          <i class="fas fa-download me-1"></i> Download PDF
        </button>
      </div>

    </div>
  </div>
</div>
  
  <?php include 'includes/footer.php'; ?>
               
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  
  <script>
    $(document).ready(function() {
        // Set current date for invoice
        const now = new Date();
        document.getElementById('invoiceDate').textContent = formatDate(now);
        document.getElementById('paymentDate').textContent = formatDate(now);
        
        // Print invoice functionality
        document.getElementById('printInvoice').addEventListener('click', function() {
            const invoiceContent = document.getElementById('invoiceContainer');
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = invoiceContent.innerHTML;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        });
        
        // Download PDF functionality
        document.getElementById('downloadInvoice').addEventListener('click', function() {
            downloadPDF();
        });
        
        // Format date function
        function formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
        
        // Generate invoice function - UPDATED
        // Updated generateInvoice function
function generateInvoice(orderData) {
    console.log('Generating invoice with data:', orderData);
    
    // Populate student information
    document.getElementById('studentName').textContent = '<?php echo $_SESSION["user"]["firstname"] . " " . $_SESSION["user"]["lastname"]; ?>';
    document.getElementById('studentId').textContent = 'Student ID: <?php echo $_SESSION["user"]["id"]; ?>';
    document.getElementById('studentCourse').textContent = 'Type: <?php echo ucfirst($_SESSION["user"]["type"]); ?> Student';
    
    // Populate invoice items
    const invoiceItems = document.getElementById('invoiceItems');
    invoiceItems.innerHTML = '';
    
    let subtotal = 0;
    
    // Try to get items from checkout modal as fallback
    const checkoutItems = document.querySelectorAll('.checkout-item');
    
    if (checkoutItems.length > 0) {
        checkoutItems.forEach((item, index) => {
            const itemName = item.querySelector('.item-name').textContent;
            const itemDetails = item.querySelector('.item-details').textContent;
            
            // Parse item details (assuming format: "₹price × quantity")
            const priceMatch = itemDetails.match(/₹(\d+\.?\d*)/);
            const quantityMatch = itemDetails.match(/×\s*(\d+)/);
            
            if (priceMatch && quantityMatch) {
                const price = parseFloat(priceMatch[1]);
                const quantity = parseInt(quantityMatch[1]);
                const amount = price * quantity;
                subtotal += amount;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${itemName}</td>
                    <td class="text-center">${quantity}</td>
                    <td class="text-end">₹${price.toFixed(2)}</td>
                    <td class="text-end">₹${amount.toFixed(2)}</td>
                `;
                invoiceItems.appendChild(row);
            }
        });
    } else {
        // If no checkout items found, show empty message
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="5" class="text-center">No items found in invoice</td>
        `;
        invoiceItems.appendChild(row);
    }
    
    // Calculate GST and total
    const gst = subtotal * 0.05;
    const total = subtotal + gst;
    
    // Update invoice totals
    document.getElementById('invoiceSubtotal').textContent = `₹${subtotal.toFixed(2)}`;
    document.getElementById('invoiceGst').textContent = `₹${gst.toFixed(2)}`;
    document.getElementById('invoiceTotal').textContent = `₹${total.toFixed(2)}`;
    
    // Generate random invoice and transaction numbers
    const invoiceNumber = 'INV-' + Math.floor(1000 + Math.random() * 9000);
    const transactionId = 'TXN-' + Math.floor(10000 + Math.random() * 90000);
    
    document.getElementById('invoiceNumber').textContent = invoiceNumber;
    document.getElementById('transactionId').textContent = transactionId;
    
    return {
        subtotal: subtotal,
        gst: gst,
        total: total,
        invoiceNumber: invoiceNumber,
        transactionId: transactionId
    };
}

// Updated payment form success handler
$('#paymentForm').submit(function(e) {
    e.preventDefault();
    
    // Generate invoice BEFORE placing order to capture current cart state
    const invoiceData = generateInvoice();
    
    $.ajax({
        url: '',
        type: 'POST',
        data: {
            action: 'add_order'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Order response:', response);
            if(response.success) {
                // Show success message
                alert('Order Placed Successfully! Order ID: ' + response.order_id);
                
                // Store order info for invoice
                localStorage.setItem('lastOrderId', response.order_id);
                localStorage.setItem('lastOrderTime', new Date().toISOString());
                
                // Clear modals
                $('#cartModal').modal('hide');
                $('#checkoutModal').modal('hide');
                
                // Show invoice modal
                $('#invoiceModal').modal('show');
                
                // Clear cart and update count
                loadCartContent();
                updateCartCount();
                
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error placing order: ' + error);
            console.error('Order error:', error);
        }
    });
});

// Function to fetch order details for invoice
function fetchOrderDetails(orderId) {
    $.ajax({
        url: 'get_order_details.php', // You'll need to create this file
        type: 'GET',
        data: { order_id: orderId },
        dataType: 'json',
        success: function(orderData) {
            generateInvoiceWithOrderData(orderData);
        },
        error: function() {
            // Fallback to current cart data
            generateInvoice();
        }
    });
}
        
        // Download PDF function
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Get invoice content
            const invoiceElement = document.getElementById('invoiceContainer');
            
            // Use html2canvas to capture the invoice as an image
            html2canvas(invoiceElement, {
                scale: 2,
                useCORS: true,
                logging: false
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 295; // A4 height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                let heightLeft = imgHeight;
                let position = 0;
                
                doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    doc.addPage();
                    doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }
                
                doc.save('invoice-' + document.getElementById('invoiceNumber').textContent + '.pdf');
            });
        }

        // Load cart count on page load
        updateCartCount();
        
        // Add to cart functionality
        $('.addToCart').click(function() {
            const pid = $(this).data('pid');
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'add_to_cart',
                    pid: pid
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Item added to cart!');
                        updateCartCount();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error adding item to cart');
                }
            });
        });

        $('#paymentForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'add_order'
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        // Show success message
                        alert('Order Placed Successfully! Order ID: ' + response.order_id);
                        
                        // Clear modals
                        $('#cartModal').modal('hide');
                        $('#checkoutModal').modal('hide');
                        
                        // Generate and show invoice
                        generateInvoice();
                        $('#invoiceModal').modal('show');
                        
                        // Clear cart and update count
                        loadCartContent();
                        updateCartCount();
                        
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error placing order: ' + error);
                }
            });
        });
        
        // Update cart count in navbar
        function updateCartCount() {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'get_cart_count'
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#cartCount').text(response.count);
                    }
                }
            });
        };
        
        // Load cart content when modal is shown
        $('#cartModal').on('show.bs.modal', function() {
            loadCartContent();
        });
        
        // Load cart content
        function loadCartContent() {
            $.ajax({
                url: 'get_cart_content.php',
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    $('#cartContent').html(response);
                    updateCartCount();
                },
                error: function() {
                    $('#cartContent').html(`
                        <div class="empty-cart text-center py-4">
                            <i class="bi bi-cart-x" style="font-size: 3rem; color: #8B7355;"></i>
                            <h5 class="mt-3" style="color: var(--primary);">Error loading cart</h5>
                            <p class="text-muted">Please try again</p>
                        </div>
                    `);
                }
            });
        }
        
        // Cart quantity controls
        $(document).on('click', '.cart-qty-plus', function() {
            const cartItemId = $(this).data('id');
            const currentQty = parseInt($(this).siblings('.cart-qty-input').val());
            const newQty = currentQty + 1;
            
            updateCartItem(cartItemId, newQty);
        });
        
        $(document).on('click', '.cart-qty-minus', function() {
            const cartItemId = $(this).data('id');
            const currentQty = parseInt($(this).siblings('.cart-qty-input').val());
            const newQty = currentQty - 1;
            
            if(newQty >= 0) {
                updateCartItem(cartItemId, newQty);
            }
        });
        
        // Cart quantity input change
        $(document).on('change', '.cart-qty-input', function() {
            const cartItemId = $(this).data('id');
            const newQty = parseInt($(this).val());
            
            if(newQty >= 0) {
                updateCartItem(cartItemId, newQty);
            } else {
                $(this).val(0);
            }
        });
        
        // Remove item from cart
        $(document).on('click', '.remove-from-cart', function() {
            const cartItemId = $(this).data('id');
            
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'remove_from_cart',
                    cart_item_id: cartItemId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        loadCartContent();
                        updateCartCount();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error removing item from cart');
                }
            });
        });
        
        // Update cart item quantity
        function updateCartItem(cartItemId, qty) {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'update_cart_item',
                    cart_item_id: cartItemId,
                    qty: qty
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        loadCartContent();
                        updateCartCount();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error updating cart');
                }
            });
        }
        
        // Proceed to checkout
        $('#proceedToCheckout').click(function() {
            // Hide cart modal
            $('#cartModal').modal('hide');
            
            // Prepare checkout items
            let checkoutItemsHtml = '';
            let totalAmount = 0;
            
            // Get cart items for checkout
            $.ajax({
                url: 'get_cart_content.php?checkout=1',
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    $('#checkoutItems').html(response);
                    
                    // Calculate total amount
                    $('.checkout-item').each(function() {
                        const itemTotal = parseFloat($(this).data('total'));
                        totalAmount += itemTotal;
                    });
                    
                    $('#totalAmount').val(totalAmount.toFixed(2));
                    
                    // Show checkout modal
                    $('#checkoutModal').modal('show');
                },
                error: function() {
                    alert('Error loading checkout information');
                }
            });
        });
    });
  </script>