<?php
include 'config/connection.php';

if(!$_SESSION['student']){
    die('Unauthorized');
}

$uid = $_SESSION['student']['id'];

// Get active cart
$cart_sql = "SELECT id FROM cart WHERE user_id = '$uid'";
$cart_query = mysqli_query($conn, $cart_sql);

if(mysqli_num_rows($cart_query) === 0) {
    echo '
    <div class="empty-cart">
        <i class="bi bi-cart-x"></i>
        <h5>Your cart is empty</h5>
        <p class="text-muted">Add some delicious food to your cart!</p>
    </div>
    ';
    exit;
}

$cart = mysqli_fetch_assoc($cart_query);
$cart_id = $cart['id'];

// Get cart items
$items_sql = "SELECT ci.id as cart_item_id, ci.pid, ci.qty, i.name, i.price, i.image 
             FROM cart_items ci 
             JOIN items i ON ci.pid = i.id 
             WHERE ci.cart_id = '$cart_id'";
$items_query = mysqli_query($conn, $items_sql);

if(mysqli_num_rows($items_query) === 0) {
    echo '
    <div class="empty-cart">
        <i class="bi bi-cart-x"></i>
        <h5>Your cart is empty</h5>
        <p class="text-muted">Add some delicious food to your cart!</p>
    </div>
    ';
    exit;
}

// Check if this is for checkout
$is_checkout = isset($_GET['checkout']) && $_GET['checkout'] == 1;

if($is_checkout) {
    // Display for checkout
    while($item = mysqli_fetch_assoc($items_query)) {
        $itemTotal = $item['price'] * $item['qty'];
        echo '
        <div class="checkout-item mb-2" data-total="'.$itemTotal.'">
            <input type="hidden" name="pid[]" value="'.$item['pid'].'">
            <input type="hidden" name="price[]" value="'.$item['price'].'">
            <input type="hidden" name="orderqty[]" value="'.$item['qty'].'">
            <div class="d-flex justify-content-between">
                <span>'.$item['name'].' ('.$item['qty'].'x)</span>
                <span>₹ '.number_format($itemTotal, 2).'</span>
            </div>
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
    
    while($item = mysqli_fetch_assoc($items_query)) {
        $itemTotal = $item['price'] * $item['qty'];
        $totalAmount += $itemTotal;
        
        echo '
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <img src="'.$item['image'].'" class="cart-item-img me-2" alt="'.$item['name'].'">
                    <span>'.$item['name'].'</span>
                </div>
            </td>
            <td>₹ '.number_format($item['price'], 2).'</td>
            <td>
                <div class="cart-qty-controls">
                    <button class="btn btn-sm btn-outline-secondary cart-qty-minus" data-id="'.$item['cart_item_id'].'">-</button>
                    <input type="number" class="form-control form-control-sm cart-qty-input" value="'.$item['qty'].'" min="0" data-id="'.$item['cart_item_id'].'">
                    <button class="btn btn-sm btn-outline-secondary cart-qty-plus" data-id="'.$item['cart_item_id'].'">+</button>
                </div>
            </td>
            <td>₹ '.number_format($itemTotal, 2).'</td>
            <td>
                <button class="btn btn-sm btn-danger remove-from-cart" data-id="'.$item['cart_item_id'].'">
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
}
?>