<?php
include_once __DIR__ . '/../config/connection.php';
include_once __DIR__ . '/../includes/auth.php';

require_login();
require_roles(['admin']);

// Accept POST from employee_form.php
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo "<script>alert('Invalid Request'); history.back();</script>";
    exit;
}

// CSRF validation
if (!verify_csrf_from_post()) {
    echo "<script>alert('Invalid CSRF token. Please try again.'); history.back();</script>";
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$username = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if($firstname === '' || $email === ''){
    echo "<script>alert('Name and Email are required'); history.back();</script>";
    exit;
}

// check email uniqueness
if($id){
    $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($chk, 'si', $email, $id);
} else {
    $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($chk, 's', $email);
}
mysqli_stmt_execute($chk);
$chkRes = mysqli_stmt_get_result($chk);
if(mysqli_fetch_assoc($chkRes)){
    echo "<script>alert('Email already exists'); history.back();</script>";
    exit;
}

if($id){
    // update
    if($password){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET firstname = ?, email = ?, password = ?, status = ? WHERE id = ? AND type = 'employee'");
        mysqli_stmt_bind_param($stmt, 'ssssi', $firstname, $email, $hash, $status, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET firstname = ?, email = ?, status = ? WHERE id = ? AND type = 'employee'");
        mysqli_stmt_bind_param($stmt, 'sssi', $firstname, $email, $status, $id);
    }
    $ok = mysqli_stmt_execute($stmt);
    if($ok){
        echo "<script>alert('Employee updated'); location.href='../employees.php';</script>";
        exit;
    } else {
        echo "<script>alert('Update failed'); history.back();</script>";
        exit;
    }
} else {
    // insert - password required
    if(!$password){ echo "<script>alert('Password is required'); history.back();</script>"; exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $type = 'employee';
    $stmt = mysqli_prepare($conn, "INSERT INTO users (firstname, username, email, password, type, status) VALUES (?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'ssssss', $firstname, $username, $email, $hash, $type, $status);
    $ok = mysqli_stmt_execute($stmt);
    if($ok){ echo "<script>alert('Employee added'); location.href='../employees.php';</script>"; exit; }
    else { echo "<script>alert('Insert failed'); history.back();</script>"; exit; }
}
