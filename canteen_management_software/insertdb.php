<?php
include 'config/connection.php';
include_once __DIR__ . '/includes/auth.php';
ini_set('display_errors', '1');

// Restrict to logged-in admins only
require_login();
require_roles(['admin']);

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // CSRF check
    if (!verify_csrf_from_post()) {
        echo "<script>alert('Invalid CSRF token. Please try again.'); window.history.back();</script>";
        exit;
    }

    $db = $_POST['db'];
    // allow only safe table names (alphanumeric + underscore)
    if (!preg_match('/^[A-Za-z0-9_]+$/', $db)) {
        echo "<script>alert('Invalid target table'); window.history.back();</script>";
        exit;
    }
    // exclude framework/internal fields
    $exclude = ['db', 'submit', '_csrf', 'csrf_token']; // keys to exclude

    // --- Handle file upload dynamically ---
    foreach ($_FILES as $key => $file) {
        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            // Check for PHP upload errors
            // debug($_FILES);
            // debug($file);
            // debug($file['error']);
            // debug(UPLOAD_ERR_INI_SIZE);
            // exit;
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    // No problem, continue to upload
                    $targetDir = "uploads/$db/";
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    // basic file type validation (images only)
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowedExt = ['jpg','jpeg','png','gif','webp'];
                    if (!in_array($ext, $allowedExt, true)) {
                        echo "<script>alert('Invalid file type. Allowed: jpg, jpeg, png, gif, webp'); window.history.back();</script>";
                        exit;
                    }

                    $fileName = time() . "_" . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
                    $targetFile = $targetDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        $_POST[$key] = $targetFile;
                    } else {
                        echo "<script>alert('Failed to move uploaded file.'); window.history.back();</script>";
                        exit;
                    }
                    break;

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $maxSize = ini_get('upload_max_filesize');
                    echo "<script>alert('File too large. Max allowed size is $maxSize.'); window.history.back();</script>";
                    exit;

                case UPLOAD_ERR_PARTIAL:
                    echo "<script>alert('File was only partially uploaded. Please try again.'); window.history.back();</script>";
                    exit;

                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "<script>alert('Missing a temporary folder on the server.'); window.history.back();</script>";
                    exit;

                case UPLOAD_ERR_CANT_WRITE:
                    echo "<script>alert('Failed to write file to disk. Check permissions.'); window.history.back();</script>";
                    exit;

                case UPLOAD_ERR_EXTENSION:
                    echo "<script>alert('File upload stopped by a PHP extension.'); window.history.back();</script>";
                    exit;

                default:
                    echo "<script>alert('Unknown upload error occurred.'); window.history.back();</script>";
                    exit;
            }
        } else {
            // No file uploaded for this field — just skip
            unset($_POST[$key]);
        }
    }


    // --- Remove unwanted keys from POST ---
    $data = array_diff_key($_POST, array_flip($exclude));

    // Validate column names (only alphanumeric and underscore)
    foreach (array_keys($data) as $k) {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $k)) {
            echo "<script>alert('Invalid column name detected'); window.history.back();</script>";
            exit;
        }
    }

    // --- Separate keys and values ---
    $keys = array_keys($data);
    $values = array_values($data);

    // --- Build prepared statement dynamically ---
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $keyString = "`" . implode("`,`", $keys) . "`";
    $sql = "INSERT INTO `{$db}` ({$keyString}) VALUES ({$placeholders})";

    // determine types string (treat everything as string 's' for simplicity)
    $types = str_repeat('s', count($values));
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo "<script>alert('Prepare failed'); window.history.back();</script>";
        exit;
    }
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    $ok = mysqli_stmt_execute($stmt);

    if ($ok) {
        echo "<script>
            alert('Data inserted successfully!');
            window.location.href = '$db.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_error($conn) . "');
            window.location.href = '$db.php';
        </script>";
    }
}
?>