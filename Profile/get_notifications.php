<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information array
$debug = [
    'session' => $_SESSION,
    'connection' => isset($conn) ? 'Connected' : 'Not connected'
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => 'User not logged in',
        'debug' => $debug
    ]);
    exit;
}

try {
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $user_id = $_SESSION['user_id'];
    $debug['user_id'] = $user_id;
    
    // Get notifications from database with error handling
    $query = "SELECT id, title, message, created_at, is_read 
             FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC";
             
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Get result failed: " . $stmt->error);
    }
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'is_read' => (bool)$row['is_read']
        ];
    }
    
    $debug['notifications_count'] = count($notifications);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'debug' => $debug
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error fetching notifications: ' . $e->getMessage(),
        'debug' => array_merge($debug, [
            'exception_message' => $e->getMessage(),
            'exception_trace' => $e->getTraceAsString(),
            'mysql_error' => isset($conn) ? $conn->error : null
        ])
    ]);
}
?> 