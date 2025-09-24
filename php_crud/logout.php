<?php
include 'config/connection.php';

session_destroy();

echo "<script>
        alert('Logout Successfully');
        location.href = 'login.php';
      </script>";

?>