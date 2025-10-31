<?php $pageTitle = '404 Unauthorized'; include 'includes/header.php'; ?>
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }
    .error-container { text-align: center; }
    .error-code { font-size: 8rem; font-weight: 700; color: #dc3545; }
    .error-message { font-size: 1.5rem; color: #6c757d; }
  </style>
  <div class="container error-container">
    <div class="error-code">404</div>
    <h2 class="fw-bold">Unauthorized Access</h2>
    <p class="error-message">Sorry, you don’t have permission to view this page.</p>
    <a href="login.php" class="btn btn-primary mt-3">Go Back Home</a>
  </div>
<?php include 'includes/scripts.php'; ?>
