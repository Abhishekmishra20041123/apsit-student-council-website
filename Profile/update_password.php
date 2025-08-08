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
        echo json_encode(['error' => 'You must be logged in to update your password']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Check if form data is submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate required fields
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    // Validate password match
    if ($new_password !== $confirm_password) {
        echo json_encode(['error' => 'New passwords do not match']);
        exit;
    }

    // Validate password strength
    if (strlen($new_password) < 8) {
        echo json_encode(['error' => 'Password must be at least 8 characters long']);
        exit;
    }

    if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password)) {
        echo json_encode(['error' => 'Password must contain both uppercase and lowercase letters']);
        exit;
    }

    if (!preg_match('/[0-9]/', $new_password)) {
        echo json_encode(['error' => 'Password must contain at least one number']);
        exit;
    }

    if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        echo json_encode(['error' => 'Password must contain at least one special character']);
        exit;
    }

    // Get current user password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['error' => 'Current password is incorrect']);
        exit;
    }

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update password
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    if (!$update_stmt) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $update_stmt->bind_param("si", $hashed_password, $user_id);

    if ($update_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $update_stmt->error]);
    }

    $update_stmt->close();
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in update_password.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request']);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

