<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User is not logged in.'
    ]);
    exit();
}

// Retrieve the logged-in user's email from the session
$email = $_SESSION['email'];

try {
    // Include the database connection
    include 'db_connection.php';

    // Prepare the SQL query to fetch user details
    $stmt = $conn->prepare("SELECT email, password FROM registration WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare the SQL statement: " . $conn->error);
    }

    // Bind the parameters and execute the query
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists in the database
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Mask the password with asterisks for security
        $maskedPassword = str_repeat('*', strlen($row['password']));

        // Send the user details as JSON
        echo json_encode([
            'status' => 'success',
            'email' => $row['email'],
            'password' => $maskedPassword
        ]);
    } else {
        // User not found in the database
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found.'
        ]);
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Handle exceptions and provide feedback
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit();
}
?>
