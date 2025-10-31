<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/connection.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
if(!$id){ echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

// fetch user to prevent deleting admins
$stmt = mysqli_prepare($conn, "SELECT type FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
if(!$row){ echo json_encode(['success'=>false,'message'=>'User not found']); exit; }
if(isset($row['type']) && $row['type'] === 'admin'){
    echo json_encode(['success'=>false,'message'=>'Cannot delete admin']);
    exit;
}

$del = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($del, 'i', $id);
$ok = mysqli_stmt_execute($del);
if($ok){ echo json_encode(['success'=>true]); }
else { echo json_encode(['success'=>false,'message'=>'Delete failed']); }
