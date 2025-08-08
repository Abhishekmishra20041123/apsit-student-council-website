<?php
// This script should be set up as a cron job to run daily
// Example cron entry: 0 0 * * * php /path/to/cron_delete_expired_events.php

// Database connection
include 'db_connect.php';

// Get the current date
$currentDate = date("Y-m-d");

// First, get the file paths to delete them
$stmt = $conn->prepare("SELECT id, image, pdf FROM events WHERE end_date < ?");
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();

$deletedCount = 0;
while ($row = $result->fetch_assoc()) {
    // Delete image file if it exists
    if (!empty($row['image']) && file_exists($row['image'])) {
        unlink($row['image']);
    }
    
    // Delete PDF file if it exists
    if (!empty($row['pdf']) && file_exists($row['pdf'])) {
        unlink($row['pdf']);
    }
    
    $deletedCount++;
}

// Now delete the events from the database
$sql = "DELETE FROM events WHERE end_date < ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);

if ($stmt->execute()) {
    echo "Expired events deleted successfully. Total events deleted: " . $deletedCount;
} else {
    echo "Error deleting expired events: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>