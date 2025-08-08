<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to view your tasks']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get all tasks for the current user
$sql = "SELECT * FROM tasks WHERE user_id = $userId ORDER BY deadline ASC, priority ASC";
$result = $conn->query($sql);

$tasks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    echo json_encode(['success' => true, 'tasks' => $tasks]);
} else {
    echo json_encode(['success' => true, 'tasks' => []]);
}

$conn->close();
?>

