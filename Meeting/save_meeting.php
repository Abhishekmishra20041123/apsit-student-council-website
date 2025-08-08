<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['title', 'meeting_date', 'attendees', 'agenda', 'discussion'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

try {
    // Get current timestamp
    $current_time = date('Y-m-d H:i:s');
    
    // Get user ID from session
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if not set

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO meeting_minutes (
        user_id, title, meeting_date, meeting_time, attendees, 
        agenda, discussion, action_items, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Get and sanitize the input
    $title = trim($_POST['title']);
    $meeting_date = trim($_POST['meeting_date']);
    $meeting_time = isset($_POST['meeting_time']) ? trim($_POST['meeting_time']) : null;
    $attendees = trim($_POST['attendees']);
    $agenda = trim($_POST['agenda']);
    $discussion = trim($_POST['discussion']);
    $action_items = isset($_POST['action_items']) ? trim($_POST['action_items']) : '';

    // Bind parameters
    $stmt->bind_param("isssssssss", 
        $user_id,
        $title,
        $meeting_date,
        $meeting_time,
        $attendees,
        $agenda,
        $discussion,
        $action_items,
        $current_time,
        $current_time
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Meeting created successfully',
        'meeting_id' => $stmt->insert_id
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 