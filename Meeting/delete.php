<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Check if meeting ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID is required']);
    exit;
}

$meeting_id = intval($_POST['id']);

try {
    // Delete the meeting
    $stmt = $conn->prepare("DELETE FROM meeting_minutes WHERE id = ?");
    $stmt->bind_param("i", $meeting_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Meeting deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Meeting not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete meeting']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?> 