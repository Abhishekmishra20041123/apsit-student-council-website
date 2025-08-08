<?php
session_start();
require_once '../db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Log function for debugging
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . "Delete Meeting Error: " . $message . "\n", 3, __DIR__ . "/logs/meeting_errors.log");
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Check if we have an ID
    if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
        throw new Exception('Meeting ID is required');
    }

    $meeting_id = intval($_REQUEST['id']);
    logError("Attempting to delete meeting with ID: " . $meeting_id);

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
        logError("Successfully deleted meeting with ID: " . $meeting_id);

        // Redirect back to admin_meetings.php
        header("Location: admin_meetings.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Log the error
    logError($e->getMessage());
    
    // Redirect back with error message
    header("Location: admin_meetings.php?error=" . urlencode($e->getMessage()));
    exit();
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?> 