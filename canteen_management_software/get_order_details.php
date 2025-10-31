<?php
include 'config/connection.php';

if(!isset($_SESSION['user']) || ($_SESSION['user']['type'] != 'student' && $_SESSION['user']['type'] != 'customer')){
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if(isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $uid = $_SESSION['user']['id'];
    
    // Get order details
    $order_sql = "SELECT o.*, oi.product_id, oi.quantity, oi.price, oi.total, i.name 
                 FROM `order` o 
                 JOIN order_items oi ON o.id = oi.order_id 
                 JOIN items i ON oi.product_id = i.id 
                 WHERE o.id = '$order_id' AND o.uid = '$uid'";
    
    $order_query = mysqli_query($conn, $order_sql);
    
    if(mysqli_num_rows($order_query) > 0) {
        $order_items = [];
        $order_total = 0;
        
        while($item = mysqli_fetch_assoc($order_query)) {
            $order_items[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['total']
            ];
            $order_total = $item['amt']; // Total amount from order table
        }
        
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'total_amount' => $order_total,
            'items' => $order_items,
            'order_date' => $item['created_at'] // Make sure your order table has this field
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
}
?>