<?php 

include 'config/connection.php'; 
// echo "<pre>";
// print_r($_SERVER); // this is global variable of php
// echo "</pre>";
// exit;
if($_SERVER['REQUEST_METHOD'] == "POST"){
    // echo "<pre>";
    // print_r($_POST); // this is global variable of php
    // echo "</pre>";
    // exit;
    $firstName  = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $email  = $_POST['email'];
    $address  = $_POST['address'];
    $mobile  = $_POST['mobile'];
    $education  = mysqli_escape_string($conn,$_POST['education']);
    $hobby = implode(",",$_POST['hobby']);
    $type = "student";
    $username  = $_POST['username'];
    $password  = $_POST['password'];
    // exit;

    $sql = "INSERT INTO users(`firstname`,`lastname`,`email`,`address`,`mobile`,`education`,`hobby`,`type`,`username`,`password`) 
    VALUES('$firstName','$lastname','$email','$address','$mobile','$education','$hobby','$type','$username','$password')";

    // echo $sql;
    // exit;
    $query = mysqli_query($conn,$sql);
    if($query){
      echo "<script>
        alert('Data Store');
        location.href = 'index.php';
      </script>";
    }else{
      echo "<script>
        alert('Some Error');
        location.href = 'student_registration.php';
      </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-lg rounded-4">
          <div class="card-header bg-primary text-white text-center">
            <h3>Student Registration</h3>
          </div>
          <div class="card-body p-4">
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
              <!-- First & Last Name -->
              <div class="row mb-3">
                <div class="col">
                  <label for="firstname" class="form-label">First Name</label>
                  <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Enter first name" required>
                </div>
                <div class="col">
                  <label for="lastname" class="form-label">Last Name</label>
                  <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter last name" required>
                </div>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
              </div>

              <!-- Address -->
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" class="form-control" name="address" rows="2" placeholder="Enter address" required></textarea>
              </div>

              <!-- Mobile -->
              <div class="mb-3">
                <label for="mobile" class="form-label">Mobile</label>
                <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="Enter mobile number" required>
              </div>

              <!-- Education -->
              <div class="mb-3">
                <label for="education" class="form-label">Education</label>
                <select id="education" name="education" class="form-select" required>
                  <option value="">Select education</option>
                  <option value="High School">High School</option>
                  <option value="Bachelor's">Bachelor's</option>
                  <option value="Master's">Master's</option>
                  <option value="PhD">PhD</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <!-- Hobby -->
              <div class="mb-3">
                <label class="form-label">Hobby</label><br>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" name="hobby[]" type="checkbox" id="hobby1" value="Reading">
                  <label class="form-check-label" for="hobby1">Reading</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" name="hobby[]" type="checkbox" id="hobby2" value="Sports">
                  <label class="form-check-label" for="hobby2">Sports</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" name="hobby[]" type="checkbox" id="hobby3" value="Music">
                  <label class="form-check-label" for="hobby3">Music</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" name="hobby[]" type="checkbox" id="hobby4" value="Travel">
                  <label class="form-check-label" for="hobby4">Travel</label>
                </div>
              </div>

              <!-- Username -->
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
              </div>

              <!-- Submit -->
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Register</button>
              </div>
            </form>
          </div>
          <div class="card-footer text-center">
            <small class="text-muted">Already registered? <a href="#">Login here</a></small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
