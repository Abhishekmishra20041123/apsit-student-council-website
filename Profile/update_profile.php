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
        echo json_encode(['error' => 'You must be logged in to update your profile']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Check if form data is submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $username = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

    // Validate required fields
    if (empty($username) || empty($email)) {
        echo json_encode(['error' => 'Username and email are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    // Check if email already exists (for another user)
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['error' => 'Email already in use by another account']);
        exit;
    }
    
    $check_stmt->close();

    // Update user profile
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, department = ?, year = ?, bio = ? WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("sssssi", $username, $email, $department, $year, $bio, $user_id);

    if ($stmt->execute()) {
        // Update session data
        $_SESSION['user_name'] = $username;
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in update_profile.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request']);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

