<?php
include 'config/connection.php';
ini_set('display_errors', '1');
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $db = $_POST['db'];
    $exclude = ['db', 'submit']; // keys to exclude

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
            // No file uploaded for this field — just skip
            unset($_POST[$key]);
        }
    }


    // --- Remove unwanted keys from POST ---
    $data = array_diff_key($_POST, array_flip($exclude));

    // --- Separate keys and values ---
    $keys = array_keys($data);
    $values = array_values($data);

    // --- Escape values to prevent SQL injection ---
    foreach ($values as &$val) {
        $val = mysqli_real_escape_string($conn, $val);
    }

    // --- Build query ---
    $keyString = "`" . implode("`,`", $keys) . "`";
    $valueString = "'" . implode("','", $values) . "'";

    $sql = "INSERT INTO $db ($keyString) VALUES ($valueString)";
    // echo $sql; exit;

    $query = mysqli_query($conn, $sql);

    if ($query) {
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