<?php
  include_once 'config/connection.php';
  // debug($_SESSION['admin']);
  $key = array_key_first($_SESSION);
  // echo $key;

  if($key == 'admin'){
    if(!$_SESSION['admin']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'adminDashboard.php';
  }else if($key == 'user'){
    if(!$_SESSION['user']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'userDashboard.php';
  }else if($key == 'employee'){
    if(!$_SESSION['employee']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'employeeDashboard.php';
  }
?>