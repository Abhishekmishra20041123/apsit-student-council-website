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
        echo json_encode(['error' => 'You must be logged in to update notification preferences']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Check if form data is submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $email_notifications = isset($_POST['email_notifications']) ? '1' : '0';
    $event_reminders = isset($_POST['event_reminders']) ? '1' : '0';
    $news_updates = isset($_POST['news_updates']) ? '1' : '0';

    // Check if user preferences already exist
    $check_stmt = $conn->prepare("SELECT user_id FROM user_preferences WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        // Update existing preferences
        $stmt = $conn->prepare("UPDATE user_preferences SET 
                              email_notifications = ?, 
                              event_reminders = ?, 
                              news_updates = ? 
                              WHERE user_id = ?");
        
        if (!$stmt) {
            echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("sssi", $email_notifications, $event_reminders, $news_updates, $user_id);
    } else {
        // Insert new preferences
        $stmt = $conn->prepare("INSERT INTO user_preferences 
                              (user_id, email_notifications, event_reminders, news_updates) 
                              VALUES (?, ?, ?, ?)");
        
        if (!$stmt) {
            echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("isss", $user_id, $email_notifications, $event_reminders, $news_updates);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in update_notifications.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request']);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

