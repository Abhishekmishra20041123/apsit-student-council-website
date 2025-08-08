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
    'post' => $_POST,
    'get' => $_GET,
    'server' => $_SERVER,
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
    
    // First, test if we can query the notifications table
    $test_query = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($test_query->num_rows === 0) {
        throw new Exception("Notifications table does not exist");
    }
    
    // Test notifications to add
    $test_notifications = [
        [
            'title' => 'Test Notification 1',
            'message' => 'This is a test notification #1'
        ]
    ];
    
    $success_count = 0;
    $debug['queries'] = [];
    
    // Add test notification
    foreach ($test_notifications as $notification) {
        $query = "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $debug['queries'][] = [
            'query' => $query,
            'user_id' => $user_id,
            'title' => $notification['title'],
            'message' => $notification['message']
        ];
        
        $stmt->bind_param("iss", $user_id, $notification['title'], $notification['message']);
        
        if ($stmt->execute()) {
            $success_count++;
            $debug['last_insert_id'] = $conn->insert_id;
        } else {
            $debug['last_error'] = $stmt->error;
        }
    }
    
    if ($success_count > 0) {
        // Verify the insertion by selecting the data
        $verify_query = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $verify_query->bind_param("i", $user_id);
        $verify_query->execute();
        $result = $verify_query->get_result();
        $last_notification = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'message' => "Added $success_count test notifications successfully",
            'debug' => array_merge($debug, [
                'notifications_added' => $success_count,
                'last_notification' => $last_notification
            ])
        ]);
    } else {
        throw new Exception('Failed to add any test notifications');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error adding test notifications: ' . $e->getMessage(),
        'debug' => array_merge($debug, [
            'exception_message' => $e->getMessage(),
            'exception_trace' => $e->getTraceAsString()
        ])
    ]);
}
?> 