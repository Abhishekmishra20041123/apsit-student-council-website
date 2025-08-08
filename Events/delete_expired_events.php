<?php
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
$deletedFiles = [];

while ($row = $result->fetch_assoc()) {
    // Delete image file if it exists
    if (!empty($row['image']) && file_exists($row['image'])) {
        if (unlink($row['image'])) {
            $deletedFiles[] = "Image: " . basename($row['image']);
        }
    }
    
    // Delete PDF file if it exists
    if (!empty($row['pdf']) && file_exists($row['pdf'])) {
        if (unlink($row['pdf'])) {
            $deletedFiles[] = "PDF: " . basename($row['pdf']);
        }
    }
    
    $deletedCount++;
}

// Now delete the events from the database
$sql = "DELETE FROM events WHERE end_date < ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => "Expired events deleted successfully",
        'total_deleted' => $deletedCount,
        'deleted_files' => $deletedFiles
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Error deleting expired events: " . $stmt->error
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>