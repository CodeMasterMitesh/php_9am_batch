<?php
include 'config/connection.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    debug($_POST);

    $id = $_POST['id'];
    $db = $_POST['db'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stockqty = $_POST['stockqty'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status'];

    $sql = "UPDATE $db SET name = '$name',category = '$category',price = '$price',
    stockqty = '$stockqty',remarks = '$remarks',status = '$status' WHERE id = $id";
    $query = mysqli_query($conn,$sql);
    // echo $sql;

    if ($query) {
        echo "<script>
            alert('Data Update successfully!');
            window.location.href = '$db.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_error($conn) . "');
            window.location.href = '$db.php';
        </script>";
    }
    exit;
}

?>