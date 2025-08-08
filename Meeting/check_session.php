<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

// Get user information including admin status
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'loggedIn' => true,
        'user_name' => $user['username'],
        'is_admin' => (bool)$user['is_admin']
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}

$stmt->close();
$conn->close();
?>