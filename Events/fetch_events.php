<?php
header("Content-Type: application/json"); // Ensure proper JSON output
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once '../config.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database Connection Failed: " . $conn->connect_error]);
    exit;
}

// Auto-delete expired events
$currentDate = date("Y-m-d");

// First, get the file paths to delete them
$stmt = $conn->prepare("SELECT id, image, pdf FROM events WHERE end_date < ?");
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Delete image file if it exists
    if (!empty($row['image']) && file_exists($row['image'])) {
        unlink($row['image']);
    }
    
    // Delete PDF file if it exists
    if (!empty($row['pdf']) && file_exists($row['pdf'])) {
        unlink($row['pdf']);
    }
}

// Now delete the events from the database
$deleteSql = "DELETE FROM events WHERE end_date < ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("s", $currentDate);
$deleteStmt->execute();

// Include deployment configuration
require_once '../deployment_config.php';

// Define base URL dynamically
$baseURL = BASE_URL . "/Events/uploads/";

$query = "SELECT id, name AS event_title, image AS event_image, description AS event_description, pdf AS event_pdf, start_date, end_date FROM events ORDER BY id DESC";
$result = $conn->query($query);

if (!$result) {
    echo json_encode(["error" => "SQL Query Failed: " . $conn->error]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    // Convert database file paths to absolute URLs
    $row['event_image'] = !empty($row['event_image']) ? $baseURL . basename($row['event_image']) : $baseURL . "default.jpg";
    $row['event_pdf'] = !empty($row['event_pdf']) ? $baseURL . basename($row['event_pdf']) : null; 

    $events[] = $row;
}

// Return events as JSON
echo json_encode($events, JSON_PRETTY_PRINT);

$conn->close();
?>
