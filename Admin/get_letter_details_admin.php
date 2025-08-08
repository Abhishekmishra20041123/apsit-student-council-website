<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once '../db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['error' => 'Letter ID is required']);
        exit;
    }

    $letter_id = intval($_GET['id']);

    // Check if the admin_letters table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_letters'");
    if ($tableCheck->num_rows == 0) {
        echo json_encode(['error' => 'Letters table does not exist']);
        exit;
    }

    // Update the letter details query to match the database structure
    $stmt = $conn->prepare("SELECT * FROM admin_letters WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $letter_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Letter not found']);
        exit;
    }

    $letter = $result->fetch_assoc();

    // Add user_name if it's missing
    if (!isset($letter['user_name'])) {
        // Try to get username from user_id if available
        if (isset($letter['user_id'])) {
            $user_query = $conn->prepare("SELECT username FROM users WHERE id = ?");
            if ($user_query) {
                $user_query->bind_param("i", $letter['user_id']);
                $user_query->execute();
                $user_result = $user_query->get_result();
                if ($user_result->num_rows > 0) {
                    $user = $user_result->fetch_assoc();
                    $letter['user_name'] = $user['username'];
                } else {
                    $letter['user_name'] = 'User #' . $letter['user_id'];
                }
                $user_query->close();
            } else {
                $letter['user_name'] = 'User #' . $letter['user_id'];
            }
        } else {
            $letter['user_name'] = 'Unknown User';
        }
    }

    // Update status to 'read' if it's 'pending'
    if ($letter['status'] === 'pending') {
        $update_stmt = $conn->prepare("UPDATE admin_letters SET status = 'read' WHERE id = ?");
        $update_stmt->bind_param("i", $letter_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $letter['status'] = 'read';
    }

    echo json_encode($letter);
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

