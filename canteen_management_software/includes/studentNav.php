<?php
// Student navigation include: protect student/customer pages
if (!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['user']['type'] ?? ''), ['student','customer'], true)) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Orders - The Hunger Bar Café</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="d-flex flex-column min-vh-100 student-page">
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
        <div class="user-name"><?php echo $_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']; ?></div>
        <div class="user-role"><?php echo ucfirst($_SESSION['user']['type']); ?></div>
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
        <a class="nav-link active" href="home.php">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Home</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="menu.php">
          <i class="bi bi-menu-button-wide-fill"></i>
          <span>Menu</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="orders.php">
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