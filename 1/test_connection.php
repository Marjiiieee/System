<?php
include 'db_connection.php';
$result = $conn->query("SELECT 1 FROM files LIMIT 1");
if ($result) {
    echo "Connection and query successful!";
} else {
    echo "Database connection or query failed: " . $conn->error;
}
?>
