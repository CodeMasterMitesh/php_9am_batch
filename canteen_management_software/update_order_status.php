<?php
include 'config/connection.php';
include_once __DIR__ . '/includes/auth.php';

require_login();
require_roles(['admin']);

// if (!$_SESSION['user'] && !$_SESSION['admin']) {
//     die('Unauthorized');
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'invalid request';
    exit;
}

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $sql = "UPDATE `order` SET status = '$status' WHERE id = '$order_id'";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
} else {
    echo "invalid request";
}
?>