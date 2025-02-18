<?php
// Database connection details
$servername = "localhost"; // XAMPP runs on localhost
$username = "root";        // Default username for XAMPP
$password = "";            // Default password is empty
$dbname = "propease";      // Your database name

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Set charset to avoid character encoding issues
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Catch any connection or SQL errors
    die("Database connection failed: " . $e->getMessage());
}
?>
