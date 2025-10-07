<?php
    include 'config/connection.php';
    if(!$_SESSION['student']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
      </script>";
    }


   if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // debug($_SESSION);
    // debug($_POST);

    $pid = $_POST['pid'];
    $uid = $_POST['uid'];
    $price = $_POST['price'];
    $orderqty = $_POST['orderqty'];

    $amt = $price * $orderqty;

    $sql = "INSERT INTO `order`(`pid`,`uid`,`qty`,`amt`) VALUES('$pid','$uid','$orderqty','$amt')";
    $query = mysqli_query($conn,$sql);
   if ($query) {
        echo "<script>
            alert('Data Received successfully!');
            window.location.href = 'orders.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_error($conn) . "');
            window.location.href = 'menu.php';
        </script>";
    }
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Food Menu</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      
      <!-- Logo & Profile -->
      <div class="d-flex align-items-center">
        <a class="navbar-brand fw-bold me-3" href="#"><?php echo $_SESSION['student']['firstname']; ?> <span style="font-size:12px;">(<?php echo ucfirst($_SESSION['student']['type']); ?>)</span></a>
        <i class="bi bi-person-circle text-white fs-3"></i>
      </div>

      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menu -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-white" href="menu.php">Menu</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">My Orders</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Profile</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Support</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <h2 class="text-center mb-4">🍽️ Our Food Menu</h2>
    <div class="row g-4">
    <?php 
        $sql = "SELECT * FROM items where status like '%Active%'";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            ?>
            <div class="col-md-4 col-sm-6">
                <div class="card menu-card shadow-sm">
                <img width="200px" src="<?php echo $row['image']; ?>" class="card-img-top" alt="Food Image">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['name']; ?></h5>
                    <p class="text-muted">Category:<?php echo $row['category']; ?></p>
                    <p class="mb-1"><strong>Price:</strong> ₹ <?php echo $row['price']; ?></p>
                    <p class="small text-muted"><?php echo $row['remarks']; ?></p>
                    <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                      <input type="hidden" name="pid" value="<?php echo $row['id']; ?>">
                      <input type="hidden" name="uid" value="<?php echo $_SESSION['student']['id']; ?>">
                      <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                      <label>Order Qty :</label>
                      <input style="width:50px" type="text" name="orderqty" value=''>
                      <button type="submit" class="btn btn-success w-100 mt-2">Order Now</button>
                    </form>
                </div>
                </div>
            </div>
            <?php
        }
    ?>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-primary text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 User Dashboard. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>