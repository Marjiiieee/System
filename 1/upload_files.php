<?php
session_start();
include 'db_connection.php';

// Set response header for JSON output
header('Content-Type: application/json');

// Validate session (ensure user is logged in)
if (!isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_email = $_SESSION['email'];
$upload_dir = __DIR__ . '/uploads/'; // Absolute path to uploads directory
$responses = [];

// Create upload directory if it doesn't exist
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
    echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
    exit();
}

// Check if files are uploaded
if (empty($_FILES['files']['tmp_name'][0])) {
    echo json_encode(["status" => "error", "message" => "No files uploaded."]);
    exit();
}

// Allowed file extensions
$allowed_extensions = ['pdf', 'doc', 'docx', 'wps'];

// Validate and process uploaded files
foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
    $original_file_name = $_FILES['files']['name'][$key];
    $file_tmp = $_FILES['files']['tmp_name'][$key];
    $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));

    // Sanitize file name to prevent path traversal vulnerabilities
    $sanitized_file_name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $original_file_name);
    $relative_path = 'uploads/' . $sanitized_file_name; // Path relative to the web server
    $absolute_path = $upload_dir . $sanitized_file_name; // Absolute path on the file system

    // Validate file type
    if (!in_array($file_extension, $allowed_extensions)) {
        $responses[] = [
            "status" => "error",
            "message" => "$original_file_name is not a supported file type. Only PDF, DOC, DOCX, and WPS are allowed."
        ];
        continue;
    }

    // Check for duplicate file names and rename them instead of rejecting
    $counter = 1;
    while (file_exists($absolute_path)) {
        $sanitized_file_name = pathinfo($original_file_name, PATHINFO_FILENAME) . "_$counter." . $file_extension;
        $relative_path = 'uploads/' . $sanitized_file_name;
        $absolute_path = $upload_dir . $sanitized_file_name;
        $counter++;
    }

    // Move file to upload directory
    if (move_uploaded_file($file_tmp, $absolute_path)) {
        // Insert file details into database
        $stmt = $conn->prepare("INSERT INTO files (user_email, file_name, file_path, upload_date) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("sss", $user_email, $sanitized_file_name, $relative_path);
            if ($stmt->execute()) {
                $responses[] = ["status" => "success", "file" => $sanitized_file_name];
            } else {
                $responses[] = [
                    "status" => "error",
                    "message" => "Database error while saving $original_file_name: " . $stmt->error
                ];
            }
            $stmt->close();
        } else {
            $responses[] = [
                "status" => "error",
                "message" => "Database error: " . $conn->error
            ];
        }
    } else {
        $responses[] = [
            "status" => "error",
            "message" => "Failed to upload $original_file_name."
        ];
    }
}

// Return response
echo json_encode($responses);
$conn->close();
?>