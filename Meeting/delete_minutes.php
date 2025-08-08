<?php
session_start();
require_once '../db_connect.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . "Delete Minutes Error: " . $message . "\n", 3, "../logs/meeting_errors.log");
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Check if we received a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check if we have an ID
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Meeting ID is required');
    }

    $meeting_id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // First verify the meeting exists
        $check_stmt = $conn->prepare("SELECT id FROM meeting_minutes WHERE id = ?");
        if (!$check_stmt) {
            throw new Exception('Failed to prepare check statement: ' . $conn->error);
        }

        $check_stmt->bind_param("i", $meeting_id);
        if (!$check_stmt->execute()) {
            throw new Exception('Failed to execute check statement: ' . $check_stmt->error);
        }

        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows === 0) {
            throw new Exception('Meeting not found');
        }

        $check_stmt->close();

        // Delete the meeting
        $delete_stmt = $conn->prepare("DELETE FROM meeting_minutes WHERE id = ?");
        if (!$delete_stmt) {
            throw new Exception('Failed to prepare delete statement: ' . $conn->error);
        }

        $delete_stmt->bind_param("i", $meeting_id);
        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to delete meeting: ' . $delete_stmt->error);
        }

        if ($delete_stmt->affected_rows === 0) {
            throw new Exception('No meeting was deleted');
        }

        $delete_stmt->close();

        // Commit transaction
        $conn->commit();

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Meeting deleted successfully',
            'meeting_id' => $meeting_id
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Log the error
    logError($e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>
