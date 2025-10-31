<?php
include 'config/connection.php';

// Check if user is logged in or guest
$is_logged_in = isset($_SESSION['user']);
$uid = $is_logged_in ? $_SESSION['user']['id'] : null;

// Check if this is for checkout
$is_checkout = isset($_GET['checkout']) && $_GET['checkout'] == 1;

if($is_logged_in) {
    // Get active cart for logged-in user
    $cart_sql = "SELECT id FROM cart WHERE user_id = '$uid'";
    $cart_query = mysqli_query($conn, $cart_sql);

    if(mysqli_num_rows($cart_query) === 0) {
        displayEmptyCart();
        exit;
    }

    $cart = mysqli_fetch_assoc($cart_query);
    $cart_id = $cart['id'];

    // Get cart items for logged-in user
    $items_sql = "SELECT ci.id as cart_item_id, ci.pid, ci.qty, i.name, i.price, i.image 
                 FROM cart_items ci 
                 JOIN items i ON ci.pid = i.id 
                 WHERE ci.cart_id = '$cart_id'";
    $items_query = mysqli_query($conn, $items_sql);

    if(mysqli_num_rows($items_query) === 0) {
        displayEmptyCart();
        exit;
    }

    $cart_items = [];
    while($item = mysqli_fetch_assoc($items_query)) {
        $cart_items[] = $item;
    }
    
    displayCartContent($cart_items, $is_checkout, true);

} else {
    // Handle guest user from session
    if(!isset($_SESSION['guest_cart']) || empty($_SESSION['guest_cart'])) {
        displayEmptyCart();
        exit;
    }

    $cart_items = [];
    foreach($_SESSION['guest_cart'] as $pid => $item) {
        $cart_items[] = [
            'cart_item_id' => 'guest_' . $pid, // Create a unique ID for guest items
            'pid' => $pid,
            'qty' => $item['qty'],
            'name' => $item['name'],
            'price' => $item['price'],
            'image' => $item['image']
        ];
    }
    
    displayCartContent($cart_items, $is_checkout, false);
}

function displayEmptyCart() {
    echo '
    <div class="empty-cart text-center py-4">
        <i class="bi bi-cart-x" style="font-size: 3rem; color: #8B7355;"></i>
        <h5 class="mt-3" style="color: var(--primary);">Your cart is empty</h5>
        <p class="text-muted">Add some delicious food to your cart!</p>
    </div>
    ';
}

function displayCartContent($cart_items, $is_checkout, $is_logged_in) {
    if($is_checkout) {
        // Display for checkout
        foreach($cart_items as $item) {
            $itemTotal = $item['price'] * $item['qty'];
            $item_id = $is_logged_in ? $item['cart_item_id'] : $item['pid'];
            
            echo '
            <div class="checkout-item mb-3 p-2 border-bottom" data-total="'.$itemTotal.'">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 item-name">'.$item['name'].'</h6>
                        <small class="text-muted item-details">₹'.$item['price'].' × '.$item['qty'].'</small>
                    </div>
                    <span class="fw-bold">₹ '.number_format($itemTotal, 2).'</span>
                </div>
                <input type="hidden" name="pid[]" value="'.$item['pid'].'">
                <input type="hidden" name="price[]" value="'.$item['price'].'">
                <input type="hidden" name="orderqty[]" value="'.$item['qty'].'">
            </div>
            ';
        }
    } else {
        // Display for cart modal
        echo '
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        ';
        
        $totalAmount = 0;
        
        foreach($cart_items as $item) {
            $itemTotal = $item['price'] * $item['qty'];
            $totalAmount += $itemTotal;
            $item_id = $is_logged_in ? $item['cart_item_id'] : $item['pid'];
            
            echo '
            <tr class="cart-item">
                <td>
                    <div class="d-flex align-items-center">
                        <img src="'.$item['image'].'" class="cart-item-img me-2" alt="'.$item['name'].'" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        <span>'.$item['name'].'</span>
                    </div>
                </td>
                <td>₹ '.number_format($item['price'], 2).'</td>
                <td>
                    <div class="cart-qty-controls d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary cart-qty-minus" data-id="'.$item_id.'" data-pid="'.$item['pid'].'">-</button>
                        <input type="number" class="form-control form-control-sm cart-qty-input mx-1" value="'.$item['qty'].'" min="0" data-id="'.$item_id.'" data-pid="'.$item['pid'].'" style="width: 60px;">
                        <button class="btn btn-sm btn-outline-secondary cart-qty-plus" data-id="'.$item_id.'" data-pid="'.$item['pid'].'">+</button>
                    </div>
                </td>
                <td>₹ '.number_format($itemTotal, 2).'</td>
                <td>
                    <button class="btn btn-sm btn-danger remove-from-cart" data-id="'.$item_id.'" data-pid="'.$item['pid'].'">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            ';
        }
        
        echo '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>₹ '.number_format($totalAmount, 2).'</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        ';
        
        // Show guest user message
        if(!$is_logged_in) {
            echo '
            <div class="alert alert-info mt-3">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    You are browsing as a guest. <a href="#" data-bs-toggle="modal" data-bs-target="#authModal">Login or register</a> to save your cart and checkout.
                </small>
            </div>
            ';
        }
    }
}
?>