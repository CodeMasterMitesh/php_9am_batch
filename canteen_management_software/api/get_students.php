<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/connection.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $per_page;

try {
    if($search !== ''){
        $like = "%" . $search . "%";
        // Count
        $countSql = "SELECT COUNT(*) as cnt FROM users WHERE firstname LIKE ? OR email LIKE ?";
        $stmt = mysqli_prepare($conn, $countSql);
        mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $total = (int) mysqli_fetch_assoc($res)['cnt'];

        $sql = "SELECT id, firstname, email, type, IFNULL(status, 'active') as status FROM users WHERE (type like '%student%' OR type like '%customer%') AND (firstname LIKE ? OR email LIKE ?) ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $per_page, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $countSql = "SELECT COUNT(*) as cnt FROM users";
        $totalRes = mysqli_query($conn, $countSql);
        $total = (int) mysqli_fetch_assoc($totalRes)['cnt'];

        $sql = "SELECT id, firstname, email, type, IFNULL(status, 'active') as status FROM users WHERE (type like '%student%' OR type like '%customer%') ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    $users = [];
    while($row = mysqli_fetch_assoc($result)){
        $users[] = $row;
    }

    echo json_encode(["success" => true, "data" => $users, "page" => $page, "per_page" => $per_page, "total" => $total]);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
