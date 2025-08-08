<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For demo, set a default user
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Demo User';
}

$user_id = $_SESSION['user_id'];

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Meeting ID is required']);
    exit;
}

$meeting_id = intval($_GET['id']);

// Get meeting details
$stmt = $conn->prepare("SELECT id, title, meeting_date, meeting_time, attendees, agenda, discussion, action_items 
                       FROM meeting_minutes WHERE id = ?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Meeting not found']);
    exit;
}

$meeting = $result->fetch_assoc();
echo json_encode($meeting);

$stmt->close();
$conn->close();
?>
