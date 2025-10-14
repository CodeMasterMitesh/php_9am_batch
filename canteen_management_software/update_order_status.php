<?php
include 'config/connection.php';

if (!$_SESSION['employee'] && !$_SESSION['admin']) {
    die('Unauthorized');
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