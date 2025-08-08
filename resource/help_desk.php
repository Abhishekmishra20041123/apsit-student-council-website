<?php
session_start();
include('../db_connect.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $issue = $conn->real_escape_string($_POST['issue']);
    
    // Get user ID if logged in
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    
    // Insert query
    $sql = "INSERT INTO help_desk (user_id, name, email, issue) 
            VALUES ($userId, '$name', '$email', '$issue')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Your issue has been submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

