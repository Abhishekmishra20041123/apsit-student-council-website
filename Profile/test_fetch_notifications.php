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

try {
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("No user logged in");
    }

    $user_id = $_SESSION['user_id'];
    $debug['user_id'] = $user_id;

    // Get user info
    $user_query = "SELECT id, name, email FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    $debug['user'] = $user;

    // Get notifications for the user
    $notifications_query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($notifications_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
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
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'notifications_count' => count($notifications),
        'notifications' => $notifications,
        'debug' => $debug
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => array_merge($debug, [
            'exception_message' => $e->getMessage(),
            'exception_trace' => $e->getTraceAsString(),
            'mysql_error' => isset($conn) ? $conn->error : null
        ])
    ], JSON_PRETTY_PRINT);
}
?> 