<?php
require_once '../db_connect.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // First, check if the notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows === 0) {
        throw new Exception("Notifications table does not exist");
    }

    // Get all notifications with user information
    $query = "SELECT n.*, u.name as user_name 
              FROM notifications n 
              LEFT JOIN users u ON n.user_id = u.id 
              ORDER BY n.created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'],
            'title' => $row['title'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'is_read' => (bool)$row['is_read']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'notifications_count' => count($notifications),
        'notifications' => $notifications,
        'debug' => [
            'table_exists' => true,
            'connection_status' => 'Connected'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => [
            'mysql_error' => isset($conn) ? $conn->error : null
        ]
    ], JSON_PRETTY_PRINT);
}
?> 