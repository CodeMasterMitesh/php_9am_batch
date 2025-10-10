<?php
    include 'config/connection.php';
    if(!$_SESSION['student']){
        echo "<script>
        alert('Unauthorized');
        location.href = '404.php';
      </script>";
    }

   if ($_SERVER['REQUEST_METHOD'] == "POST") {
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
            window.location.href = 'studentsOrders.php';
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
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .navbar {
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar-brand {
      font-size: 1.3rem;
      letter-spacing: 0.5px;
    }
    h2 {
      font-weight: 600;
      color: #343a40;
    }
    .menu-card {
      border: none;
      border-radius: 16px;
      overflow: hidden;
      transition: all 0.3s ease;
      background: #fff;
    }
    .menu-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }
    .menu-card img {
      height: 220px;
      object-fit: cover;
    }
    .card-body {
      padding: 1.2rem;
    }
    .card-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #212529;
    }
    .btn-success {
      border-radius: 12px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .btn-success:hover {
      background-color: #218838;
      transform: scale(1.03);
    }
    footer {
      background: #0d6efd;
      font-size: 0.9rem;
      letter-spacing: 0.4px;
    }
    label {
      font-size: 0.9rem;
      font-weight: 500;
    }
    input[name="orderqty"] {
      border-radius: 6px;
      border: 1px solid #ced4da;
      padding: 4px 6px;
      text-align: center;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container-fluid">
      <!-- Logo & Profile -->
      <div class="d-flex align-items-center">
        <a class="navbar-brand fw-bold me-2" href="#">
          <?php echo $_SESSION['student']['firstname']; ?> 
          <span class="text-secondary" style="font-size:12px;">(<?php echo ucfirst($_SESSION['student']['type']); ?>)</span>
        </a>
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
          <li class="nav-item"><a class="nav-link active text-white fw-semibold" href="menu.php">Menu</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="studentsOrders.php">My Orders</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Profile</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Support</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Menu Section -->
  <div class="container py-5">
    <h2 class="text-center mb-5">🍽️ Our Food Menu</h2>
    <div class="row g-4">
    <?php 
        $sql = "SELECT * FROM items WHERE status LIKE '%Active%'";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card menu-card shadow-sm h-100">
                  <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="Food Image">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo $row['name']; ?></h5>
                    <p class="text-muted mb-1"><strong>Category:</strong> <?php echo $row['category']; ?></p>
                    <p class="mb-2"><strong>Price:</strong> ₹ <?php echo $row['price']; ?></p>
                    <p class="small text-muted mb-3"><?php echo $row['remarks']; ?></p>
                    <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" class="mt-auto">
                      <input type="hidden" name="pid" value="<?php echo $row['id']; ?>">
                      <input type="hidden" name="uid" value="<?php echo $_SESSION['student']['id']; ?>">
                      <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                      <div class="d-flex align-items-center mb-2">
                        <label class="me-2">Qty:</label>
                        <input type="text" name="orderqty" style="width:60px" required>
                      </div>
                      <button type="submit" class="btn btn-success w-100 mt-1">Order Now</button>
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
  <footer class="text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 User Dashboard. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>