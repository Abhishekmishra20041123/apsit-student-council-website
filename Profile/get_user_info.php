<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session
session_start();

// Always set content type to JSON
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
        echo json_encode([
            'loggedIn' => true,
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['user_name'],
            'verified' => isset($_SESSION['profile_verified']) && $_SESSION['profile_verified'] === true
        ]);
    } else {
        echo json_encode([
            'loggedIn' => false,
            'verified' => false
        ]);
    }
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in get_user_info.php: ' . $e->getMessage());
    echo json_encode([
        'loggedIn' => false,
        'verified' => false,
        'error' => 'An error occurred while checking login status'
    ]);
}
?>