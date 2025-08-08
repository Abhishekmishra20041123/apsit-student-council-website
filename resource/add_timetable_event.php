<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add events']);
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $day = $conn->real_escape_string($_POST['day']);
    $startTime = $conn->real_escape_string($_POST['start_time']);
    $endTime = $conn->real_escape_string($_POST['end_time']);
    $location = $conn->real_escape_string($_POST['location']);
    
    // Add event to database
    $sql = "INSERT INTO timetable_events (user_id, name, day, start_time, end_time, location) 
            VALUES ($userId, '$name', '$day', '$startTime', '$endTime', '$location')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'event_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

