<?php
include 'config/connection.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $db = $_POST['db'];
    // Remove unwanted keys from $_POST
    $exclude = ['db', 'submit']; // add more if needed
    $data = array_diff_key($_POST, array_flip($exclude));

    // Separate keys and values
    $keys = array_keys($data);
    $values = array_values($data);

    // Escape values to prevent SQL injection
    foreach ($values as &$val) {
        $val = mysqli_real_escape_string($conn, $val);
    }

    // Build query
    $keyString = "`" . implode("`,`", $keys) . "`";
    $valueString = "'" . implode("','", $values) . "'";

    $sql = "INSERT INTO $db ($keyString) VALUES ($valueString)";
    // Debug
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