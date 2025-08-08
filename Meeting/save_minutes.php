<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For demo, set a default user
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Demo User';
}

$user_id = $_SESSION['user_id'];

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$meeting_date = isset($_POST['meeting_date']) ? trim($_POST['meeting_date']) : '';
$meeting_time = isset($_POST['meeting_time']) ? trim($_POST['meeting_time']) : '';
$attendees = isset($_POST['attendees']) ? $_POST['attendees'] : '[]';
$agenda = isset($_POST['agenda']) ? trim($_POST['agenda']) : '';
$discussion = isset($_POST['discussion']) ? trim($_POST['discussion']) : '';
$action_items = isset($_POST['action_items']) ? $_POST['action_items'] : '[]';

// Validate required fields
if (empty($title) || empty($meeting_date) || empty($agenda) || empty($discussion)) {
    echo json_encode(['error' => 'Required fields are missing']);
    exit;
}

// Check if it's an update or new record
if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = intval($_POST['id']);
    
    // Verify ownership
    $check_stmt = $conn->prepare("SELECT id FROM meeting_minutes WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['error' => 'Meeting not found']);
        exit;
    }
    
    $check_stmt->close();
    
    // Update the record
    $stmt = $conn->prepare("UPDATE meeting_minutes SET 
                          title = ?, 
                          meeting_date = ?, 
                          meeting_time = ?, 
                          attendees = ?, 
                          agenda = ?, 
                          discussion = ?, 
                          action_items = ? 
                          WHERE id = ?");
    
    $stmt->bind_param("sssssssi", $title, $meeting_date, $meeting_time, $attendees, $agenda, $discussion, $action_items, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    // Insert new record
    $stmt = $conn->prepare("INSERT INTO meeting_minutes 
                          (user_id, title, meeting_date, meeting_time, attendees, agenda, discussion, action_items, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    $stmt->bind_param("isssssss", $user_id, $title, $meeting_date, $meeting_time, $attendees, $agenda, $discussion, $action_items);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>
