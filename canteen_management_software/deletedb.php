<?php
  include 'config/connection.php';
  $id = $_GET['id'];
  $db = $_GET['db'];

  $sql = "DELETE from $db where id = $id";
  $query = mysqli_query($conn,$sql);

  if($query) {
        echo "<script>
            alert('Data Deleted successfully!');
            window.location.href = '$db.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_error($conn) . "');
            window.location.href = '$db.php';
        </script>";
    }
  exit;

?>