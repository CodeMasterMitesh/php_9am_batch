<?php
  include_once 'config/connection.php';
  if($_SERVER['REQUEST_METHOD'] == "POST"){

    // debug($_POST);
    // exit;
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    // debug($row);
    // exit;
    if($row) {
      // if(password_verify($pass, $row['password'])){
        // debug($row);
        // exit;
        if($row['type'] == "admin"){
           $_SESSION['user'] = $row;
            echo "<script>
              alert('Login Successfully');
              location.href = 'index.php';
          </script>";
        }else if($row['type'] == "student"){
           $_SESSION['user'] = $row;
            echo "<script>
              alert('Login Successfully');
              location.href = 'home.php';
          </script>";
        }else if($row['type'] == "employee"){
           $_SESSION['user'] = $row;
            echo "<script>
              alert('Login Successfully');
              location.href = 'index.php';
          </script>";
        }else if($row['type'] == "customer"){
           $_SESSION['user'] = $row;
            echo "<script>
              alert('Login Successfully');
              location.href = 'home.php';
          </script>";
        }else{
             echo "<script>
              alert('Unauthorized');
              location.href = '404.php';
          </script>";
        }
      // }
      // else{
      //   echo "<script>
      //       alert('Wrong Password');
      //       location.href = 'login.php';
      //   </script>";
      // }

    }else{
       echo "<script>
        alert('Email Id Not Found');
        location.href = 'login.php';
      </script>";
    }
    // debug($row);
    // exit;
  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary: #6F1D1B;       /* Rich Deep Brown (matches logo tone) */
      --secondary: #BB9457;     /* Golden Sand Accent */
      --highlight: #FFD60A;     /* Warm Yellow for highlights */
      --text-light: #FFF4E6;    /* Light creamy text */
      --text-muted: #DDB892;    /* Muted beige for placeholders */
      --gradient: linear-gradient(135deg, #7F2B1D 0%, #BB9457 100%);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #6F1D1B 0%, #BB9457 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    body::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(4px);
    }
    
    .login-container {
      max-width: 500px;
      width: 100%;
      margin: 20px;
    }
    
    .login-card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
      overflow: hidden;
    }
    
    .login-header {
      background: var(--gradient);
      padding: 1rem 1rem;
      text-align: center;
      color: white;
      position: relative;
    }
    
    .logo-container {
      margin-bottom: 10px;
    }
    
    .logo {
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      border: 3px solid rgba(255, 255, 255, 0.3);
    }
    
    .logo i {
      font-size: 2.5rem;
      color: white;
    }
    
    .login-header h3 {
      font-weight: 700;
      margin: 0;
      font-size: 1.75rem;
    }
    
    .login-header p {
      opacity: 0.9;
      margin: 0.5rem 0 0 0;
      font-size: 0.95rem;
    }
    
    .login-body {
      padding: 10px 10px;
    }
    
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }
    
    .form-control {
      border-radius: 12px;
      border: 2px solid #e9ecef;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: #f8f9fa;
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
      background: white;
    }
    
    .input-group-icon {
      position: relative;
    }
    
    .input-group-icon .form-control {
      padding-left: 3rem;
    }
    
    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      z-index: 2;
    }
    
    .form-check-input:checked {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .form-check-label {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .btn-login {
  background: var(--gradient);
  border: none;
  border-radius: 12px;
  padding: 0.75rem 2rem;
  font-weight: 600;
  font-size: 1.1rem;
  color: white;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(111, 29, 27, 0.4);
}
.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(187, 148, 87, 0.5);
}

    
    .login-footer {
      padding: 10px 10px;
      text-align: center;
      background: #f8f9fa;
      border-top: 1px solid #e9ecef;
    }
    
    .login-footer a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    
    .login-footer a:hover {
      color: var(--secondary);
    }
    
    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 1.5rem 0;
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #dee2e6;
    }
    
    .divider::before {
      margin-right: 5px;
    }
    
    .divider::after {
      margin-left: 5px;
    }
    
    /* Animation */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .login-card {
      animation: fadeInUp 0.6s ease-out;
    }
    
    /* Responsive */
    @media (max-width: 480px) {
      .login-header {
        padding: 2rem 1.5rem;
      }
      
      .login-body {
        padding: 2rem 1.5rem;
      }
      
      .login-footer {
        padding: 1.25rem 1.5rem;
      }
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="login-card">
      <!-- Header with Logo -->
      <div class="login-header">
        <div class="logo-container">
          <div class="logo">
            <!-- Replace with your logo - using icon as placeholder -->
            <div class="logo">
              <img src="assets/logo/the_hunger_bar_logo.png" alt="The Hunger Bar Logo" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
            </div>
            <!-- If you have an image logo, use this instead: -->
            <!-- <img src="your-logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"> -->
          </div>
        </div>
        <h3>Welcome Back</h3>
        <p>Sign in to your account</p>
      </div>

      <!-- Login Form -->
      <div class="login-body">
        <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
          <!-- Username -->
          <div class="mb-1">
            <label for="email" class="form-label">Email</label>
            <div class="input-group-icon">
              <i class="bi bi-person input-icon"></i>
              <input type="email" id="email" class="form-control" name="email" placeholder="mail@gmail.com" required>
            </div>
          </div>

          <!-- Password -->
          <div class="mb-1">
            <label for="password" class="form-label">Password</label>
            <div class="input-group-icon">
              <i class="bi bi-lock input-icon"></i>
              <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remember">
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="#" class="text-decoration-none small" style="color: var(--primary);">Forgot password?</a>
          </div>

          <!-- Submit Button -->
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-login text-white py-2">
              <i class="bi bi-box-arrow-in-right me-2"></i>
              Sign In
            </button>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <!-- <div class="login-footer">
        <small class="text-muted">
          By continuing, you agree to our 
          <a href="#">Terms of Service</a> 
          and 
          <a href="#">Privacy Policy</a>
        </small>
      </div> -->
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Add some interactive functionality
    document.addEventListener('DOMContentLoaded', function() {
      const inputs = document.querySelectorAll('.form-control');
      
      inputs.forEach(input => {
        // Add focus effect
        input.addEventListener('focus', function() {
          this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
          if (!this.value) {
            this.parentElement.classList.remove('focused');
          }
        });
        
        // Check if input has value on page load
        if (input.value) {
          input.parentElement.classList.add('focused');
        }
      });
    });
  </script>
</body>
</html>