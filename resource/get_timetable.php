<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to view your timetable']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get all events for the current user
$sql = "SELECT * FROM timetable_events WHERE user_id = $userId ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode(['success' => true, 'events' => $events]);
} else {
    echo json_encode(['success' => true, 'events' => []]);
}

$conn->close();
?>

