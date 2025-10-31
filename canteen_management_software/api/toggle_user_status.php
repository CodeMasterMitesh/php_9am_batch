<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/connection.php';
include_once __DIR__ . '/../includes/auth.php';

require_login();
require_roles(['admin']);
deny_direct_browser_access('../404.php');

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
if(!$id){ echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

// fetch current status
$stmt = mysqli_prepare($conn, "SELECT status, type FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
if(!$row){ echo json_encode(['success'=>false,'message'=>'User not found']); exit; }

// prevent toggling admin status by accident
if(isset($row['type']) && $row['type'] === 'admin'){
    echo json_encode(['success'=>false,'message'=>'Cannot change status of admin']);
    exit;
}

$current = isset($row['status']) && $row['status'] === 'inactive' ? 'inactive' : 'active';
$new = $current === 'active' ? 'inactive' : 'active';

$uStmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($uStmt, 'si', $new, $id);
$ok = mysqli_stmt_execute($uStmt);
if($ok){
    echo json_encode(['success'=>true,'status'=>$new]);
} else {
    echo json_encode(['success'=>false,'message'=>'Update failed']);
}
