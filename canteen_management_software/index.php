<?php
  include_once 'config/connection.php';
  // ini_set('display_errors', '1');
  // debug($_SESSION['admin']);
  $key = array_key_first($_SESSION);
  // echo $key;
  // exit;
  if($key == 'admin'){
    if(!$_SESSION['admin']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'adminDashboard.php';
  }else if($key == 'student'){
    if(!$_SESSION['student']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'studentDashboard.php';
  }else if($key == 'employee'){
    if(!$_SESSION['employee']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'employeeDashboard.php';
  }else{
    // Redirect to login.php
    header("Location: canteen_management_software/login.php");
    exit();
  }
?>