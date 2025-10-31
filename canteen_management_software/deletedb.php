<?php
include 'config/connection.php';
include_once __DIR__ . '/includes/auth.php';
ini_set('display_errors', '1');

// Restrict to logged-in admins only
require_login();
require_roles(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method'); window.history.back();</script>";
    exit;
}

if (!verify_csrf_from_post()) {
    echo "<script>alert('Invalid CSRF token.'); window.history.back();</script>";
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$db = $_POST['db'] ?? '';

if ($id <= 0 || !preg_match('/^[A-Za-z0-9_]+$/', $db)) {
    echo "<script>alert('Invalid request parameters'); window.history.back();</script>";
    exit;
}

$sql = "DELETE FROM `{$db}` WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo "<script>alert('Prepare failed'); window.history.back();</script>";
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    echo "<script>alert('Data deleted successfully!'); window.location.href = '$db.php';</script>";
} else {
    echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href = '$db.php';</script>";
}
exit;
?>