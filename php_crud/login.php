<?php
  include 'config/connection.php';
  if($_SERVER['REQUEST_METHOD'] == "POST"){

    // debug($_POST);
    // exit;
    $useranme = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * from users where username like '%$useranme%'" ;
    $query = mysqli_query($conn,$sql);
    $row = mysqli_fetch_assoc($query);
    
    if($useranme === $row['username']){
      if($pass === $row['password']){
        if($row['type'] == "admin"){
           $_SESSION['admin'] = $row;
            echo "<script>
              alert('Login Successfully');
              location.href = 'index.php';
          </script>";
        }else{
           $_SESSION['user'] = $row;
            echo "<script>
              alert('Login Successfully');
              location.href = 'userdashboard.php';
          </script>";
        }
       
      }else{
        echo "<script>
            alert('Wrong Password');
            location.href = 'login.php';
        </script>";
      }

    }else{
       echo "<script>
        alert('User Name Not Found');
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
  <title>Student Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg rounded-4">
          <div class="card-header bg-success text-white text-center">
            <h3>Student Login</h3>
          </div>
          <div class="card-body p-4">
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
              <!-- Username -->
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" class="form-control" name="username" placeholder="Enter username" required>
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
              </div>

              <!-- Remember Me -->
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
              </div>

              <!-- Submit -->
              <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg">Login</button>
              </div>
            </form>
          </div>
          <div class="card-footer text-center">
            <small class="text-muted">Don’t have an account? <a href="student_registration.php">Register here</a></small><br>
            <a href="#" class="small">Forgot Password?</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>