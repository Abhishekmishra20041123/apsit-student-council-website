<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notification_id'])) {
    echo json_encode(['error' => 'Notification ID is required']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $notification_id = $data['notification_id'];
    
    // Verify notification belongs to user and delete it
    $stmt = $conn->prepare("
        DELETE FROM notifications 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Notification not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error deleting notification: ' . $e->getMessage()]);
}
?> 