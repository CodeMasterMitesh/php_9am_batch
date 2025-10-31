<?php
include 'config/connection.php';
ini_set('display_errors', '1');

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $db = $_POST['db'];
    $id = $_POST['id'];
    $exclude = ['db', 'id', 'submit']; // keys to exclude

    // --- Handle file upload dynamically ---
    foreach ($_FILES as $key => $file) {
        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            // Check for PHP upload errors
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    // No problem, continue to upload
                    $targetDir = "uploads/$db/";
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $fileName = time() . "_" . basename($file['name']);
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
            // No file uploaded for this field — keep existing value (don't unset)
            // We'll handle this in the update query by not including unchanged file fields
            unset($_POST[$key]);
        }
    }

    // --- Remove unwanted keys from POST ---
    $data = array_diff_key($_POST, array_flip($exclude));

    // --- Build SET clause dynamically ---
    $setParts = [];
    foreach ($data as $key => $value) {
        $escapedValue = mysqli_real_escape_string($conn, $value);
        $setParts[] = "`$key` = '$escapedValue'";
    }

    // If no fields to update (only file fields that weren't changed)
    if (empty($setParts)) {
        echo "<script>
            alert('No changes to update!');
            window.location.href = '$db.php';
        </script>";
        exit;
    }

    $setClause = implode(", ", $setParts);

    // --- Build and execute UPDATE query ---
    $sql = "UPDATE $db SET $setClause WHERE id = '$id'";
    // echo $sql; exit; // Uncomment for debugging

    $query = mysqli_query($conn, $sql);

    if ($query) {
        echo "<script>
            alert('Data updated successfully!');
            window.location.href = '$db.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_error($conn) . "');
            window.location.back();
        </script>";
    }
    exit;
}
?>