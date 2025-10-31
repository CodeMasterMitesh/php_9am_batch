<?php
  include_once 'config/connection.php';
  // ini_set('display_errors', '1');
  // debug($_SESSION);
  $key = $_SESSION['user']['type'];
  // echo $key;
  // exit;
  if($key == 'admin'){
    if(!$_SESSION['user']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'adminDashboard.php';
  }else if($key == 'student'){
    if(!$_SESSION['user']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'studentDashboard.php';
  }else if($key == 'employee'){
    if(!$_SESSION['user']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
        </script>";
    }
    include 'employeeDashboard.php';
  }else{
    // Redirect to home.php
    header("Location: home.php");
    exit();
  }
?>