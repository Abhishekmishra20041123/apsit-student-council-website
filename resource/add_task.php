<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add tasks']);
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $course = $conn->real_escape_string($_POST['course']);
    $deadline = $conn->real_escape_string($_POST['deadline']);
    $priority = $conn->real_escape_string($_POST['priority']);
    
    // Add task to database
    $sql = "INSERT INTO tasks (user_id, name, course, deadline, priority) 
            VALUES ($userId, '$name', '$course', '$deadline', '$priority')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'task_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

