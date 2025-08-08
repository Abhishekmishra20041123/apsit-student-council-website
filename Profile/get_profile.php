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
        echo json_encode(['error' => 'You must be logged in to view your profile']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get user profile data
    $stmt = $conn->prepare("SELECT name, email, department, year, bio, email_notifications, event_reminders, news_updates 
                           FROM users 
                           LEFT JOIN user_preferences ON users.id = user_preferences.user_id 
                           WHERE users.id = ?");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $user = $result->fetch_assoc();
    
    // If user_preferences doesn't exist yet, set default values
    if ($user['email_notifications'] === null) {
        $user['email_notifications'] = '1';
        $user['event_reminders'] = '1';
        $user['news_updates'] = '1';
    }

    echo json_encode($user);
    
    $stmt->close();
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in get_profile.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request']);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

