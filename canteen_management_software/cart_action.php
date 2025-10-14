<?php
include 'config/connection.php';

$uid = $_SESSION['student']['id'];

$action = $_POST['action'];

if ($action == 'add') {

  $pid = $_POST['pid'];
  $price = $_POST['price'];

  // check if already exists
  $check = mysqli_query($conn, "SELECT * FROM cart_items WHERE uid='$uid' AND pid='$pid'");
  if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "UPDATE cart_items SET qty = qty + 1, total = price * qty WHERE uid='$uid' AND pid='$pid'");
  } else {
    mysqli_query($conn, "INSERT INTO cart_items(uid, pid, qty, price, total) VALUES('$uid','$pid',1,'$price','$price')");
  }
  echo "Item added to cart";
}

if ($action == 'fetch') {
  $result = mysqli_query($conn, "SELECT c.*, i.name, i.image FROM cart_items c JOIN items i ON c.pid = i.id WHERE c.uid='$uid'");
  $output = '';
  $grandTotal = 0;
  if (mysqli_num_rows($result) > 0) {
    $output .= '<table class="table table-bordered align-middle text-center">
      <thead class="table-light">
        <tr>
          <th>Image</th><th>Name</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th>
        </tr>
      </thead><tbody>';
    while ($row = mysqli_fetch_assoc($result)) {
      $grandTotal += $row['total'];
      $output .= '<tr>
        <td><img src="'.$row['image'].'" width="60"></td>
        <td>'.$row['name'].'</td>
        <td>₹'.$row['price'].'</td>
        <td>
          <button class="btn btn-sm btn-outline-secondary updateQty" data-action="minus" data-id="'.$row['id'].'">-</button>
          <span class="mx-2">'.$row['qty'].'</span>
          <button class="btn btn-sm btn-outline-secondary updateQty" data-action="plus" data-id="'.$row['id'].'">+</button>
        </td>
        <td>₹'.$row['total'].'</td>
        <td><button class="btn btn-sm btn-danger removeItem" data-id="'.$row['id'].'"><i class="bi bi-trash"></i></button></td>
      </tr>';
    }
    $output .= '<tr class="table-secondary"><td colspan="4" class="text-end fw-bold">Grand Total</td><td colspan="2" class="fw-bold">₹'.$grandTotal.'</td></tr></tbody></table>';
  } else {
    $output = '<p class="text-center text-muted">Your cart is empty!</p>';
  }
  echo $output;
}

if ($action == 'updateQty') {
  $id = $_POST['id'];
  $type = $_POST['type'];

  $res = mysqli_query($conn, "SELECT qty, price FROM cart_items WHERE id='$id'");
  $data = mysqli_fetch_assoc($res);
  $qty = $data['qty'];
  $price = $data['price'];

  if ($type == 'plus') $qty++;
  if ($type == 'minus' && $qty > 1) $qty--;

  $total = $price * $qty;
  mysqli_query($conn, "UPDATE cart_items SET qty='$qty', total='$total' WHERE id='$id'");
  echo "updated";
}

if ($action == 'remove') {
  $id = $_POST['id'];
  mysqli_query($conn, "DELETE FROM cart_items WHERE id='$id'");
  echo "removed";
}
?>
