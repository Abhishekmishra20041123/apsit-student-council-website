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

// Check if search parameter is provided
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    // Search query
    $stmt = $conn->prepare("SELECT id, title, meeting_date, meeting_time, agenda FROM meeting_minutes 
                           WHERE user_id = ? AND (title LIKE ? OR agenda LIKE ? OR discussion LIKE ? OR action_items LIKE ?)
                           ORDER BY meeting_date DESC");
    
    $searchParam = "%$search%";
    $stmt->bind_param("issss", $user_id, $searchParam, $searchParam, $searchParam, $searchParam);
} else {
    // Get all minutes for the user
    $stmt = $conn->prepare("SELECT id, title, meeting_date, meeting_time, agenda FROM meeting_minutes 
                           WHERE user_id = ? ORDER BY meeting_date DESC");
    
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$minutes = [];
while ($row = $result->fetch_assoc()) {
    // Format the date
    $date = new DateTime($row['meeting_date']);
    $row['formatted_date'] = $date->format('Y-m-d');
    
    // Format the time if it exists
    if ($row['meeting_time']) {
        $time = new DateTime($row['meeting_time']);
        $row['formatted_time'] = $time->format('H:i:s');
    }
    
    // Add delete button HTML to each meeting
    $row['deleteButton'] = '<button class="delete-btn" data-id="' . $row['id'] . '">Delete</button>';
    $minutes[] = $row;
}

echo json_encode(['success' => true, 'data' => $minutes]);

$stmt->close();
$conn->close();
?>