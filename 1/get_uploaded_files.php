<?php
session_start();
include 'db_connection.php';

// Set response header
header('Content-Type: application/json');

// Validate user session
if (!isset($_SESSION['email'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in. Please log in to view uploaded files."
    ]);
    exit();
}

$user_email = $_SESSION['email'];

try {
    // Prepare the SQL query to fetch files uploaded by the user
    $stmt = $conn->prepare("SELECT id, file_name, file_path, upload_date FROM files WHERE user_email = ? ORDER BY upload_date DESC");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    // Bind the email parameter and execute the query
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are any uploaded files
    if ($result->num_rows > 0) {
        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = [
                "id" => $row['id'],
                "file_name" => $row['file_name'],
                "file_path" => $row['file_path'],
                "upload_date" => $row['upload_date']
            ];
        }
        echo json_encode([
            "status" => "success",
            "files" => $files
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "files" => [],
            "message" => "No uploaded files found for this user."
        ]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Return an error message in case of exception
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred while fetching files: " . $e->getMessage()
    ]);
    exit();
}
?>
