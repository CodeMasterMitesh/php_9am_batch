<?php
include 'config/connection.php';
if(!$_SESSION['student']){
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
            $uid = $_SESSION['student']['id'];
            
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
            $uid = $_SESSION['student']['id'];
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
        $uid = $_SESSION['student']['id'];
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
                window.location.href = 'studentsOrders.php';
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
          <h4><?php echo $_SESSION['student']['firstname']; ?></h4>
          <p><?php echo ucfirst($_SESSION['student']['type']); ?> Student</p>
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
          <input type="hidden" name="uid" value="<?php echo $_SESSION['student']['id']; ?>">
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

  <?php include 'includes/footer.php'; ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <script>
    $(document).ready(function() {
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
            // console.log(response);
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
              console.log(response);
                if(response.success) {
                    alert('Order Placed Successfully! Order ID: ' + response.order_id);
                    
                    // Clear the cart UI and update count
                    $('#cartModal').modal('hide');
                    $('#checkoutModal').modal('hide');
                    
                    // Reload cart content to show empty state
                    loadCartContent();
                    updateCartCount();
                    
                    // Optionally redirect to orders page
                    // window.location.href = 'studentsOrders.php';
                    
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
      }
      
      // Load cart content when modal is shown
      $('#cartModal').on('show.bs.modal', function() {
        loadCartContent();
      });
      
      // Load cart content
      function loadCartContent() {
        $.ajax({
          url: 'get_cart_content.php', // You'll need to create this file
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