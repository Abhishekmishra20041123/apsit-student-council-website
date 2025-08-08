<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session and connect to database
session_start();
require_once '../db_connect.php';

// Always set content type to JSON
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'You must be logged in to view letter details']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

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

    // Check if admin_reply column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM admin_letters LIKE 'admin_reply'");
    if ($columnCheck->num_rows == 0) {
        // If column doesn't exist, add it
        $conn->query("ALTER TABLE admin_letters ADD COLUMN admin_reply TEXT DEFAULT NULL");
    }

    // Get letter details
    $stmt = $conn->prepare("SELECT id, subject, message, attachment, status, created_at, 
                          IFNULL(admin_reply, '') as reply 
                          FROM admin_letters WHERE id = ? AND user_id = ?");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("ii", $letter_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Letter not found or you do not have permission to view it']);
        exit;
    }

    $letter = $result->fetch_assoc();

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
    // Log the error to a file instead of displaying it
    error_log('Error in get_letter_details.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request']);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

