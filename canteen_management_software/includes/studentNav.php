<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Orders - The Hunger Bar Café</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/studentStyle.css">
</head>
<body class="d-flex flex-column min-vh-100">
     <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-brand">
      <img src="assets/logo/the_hunger_bar_logo.png" alt="The Hunger Bar Logo">
      <span>The Hunger Bar Café</span>
    </div>
       <!-- User Info Section -->
    <div class="sidebar-user-info">
      <div class="user-avatar-sidebar">
        <i class="bi bi-person-circle"></i>
      </div>
      <div class="user-info-text">
        <div class="user-name"><?php echo $_SESSION['student']['firstname'] . ' ' . $_SESSION['student']['lastname']; ?></div>
        <div class="user-role"><?php echo ucfirst($_SESSION['student']['type']); ?> Student</div>
      </div>
    </div>

    <ul class="nav flex-column sidebar-nav">
      <li class="nav-item">
        <a class="nav-link" href="index.php">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="menu.php">
          <i class="bi bi-menu-button-wide-fill"></i>
          <span>Menu</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="studentsOrders.php">
          <i class="bi bi-cart-check"></i>
          <span>My Orders</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <i class="bi bi-box-arrow-right"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>