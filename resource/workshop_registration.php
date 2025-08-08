<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to register for workshops']);
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $studentId = $conn->real_escape_string($_POST['student_id']);
    $workshop = $conn->real_escape_string($_POST['workshop']);
    
    // Get workshop ID from title
    $workshopQuery = "SELECT id FROM workshops WHERE title = '$workshop'";
    $workshopResult = $conn->query($workshopQuery);
    
    if ($workshopResult->num_rows > 0) {
        $workshopRow = $workshopResult->fetch_assoc();
        $workshopId = $workshopRow['id'];
        
        // Check if user is already registered for this workshop
        $checkQuery = "SELECT * FROM workshop_registrations WHERE workshop_id = $workshopId AND user_id = $userId";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'You are already registered for this workshop']);
            exit;
        }
        
        // Register for workshop
        $sql = "INSERT INTO workshop_registrations (workshop_id, user_id, name, email, student_id) 
                VALUES ($workshopId, $userId, '$name', '$email', '$studentId')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Workshop not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

