<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
include '../db_connect.php';

// Initialize response array
$response = array('status' => 'error', 'message' => '');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get event ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Validate input
    if ($id <= 0) {
        $response['message'] = 'Invalid event ID';
    } else {
        // First, get the file paths to delete them
        $stmt = $conn->prepare("SELECT image, pdf FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Delete image file if it exists
            if (!empty($row['image']) && file_exists($row['image'])) {
                unlink($row['image']);
            }
            
            // Delete PDF file if it exists
            if (!empty($row['pdf']) && file_exists($row['pdf'])) {
                unlink($row['pdf']);
            }
        }
        
        // Now delete the event from the database
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['status'] = 'success';
                $response['message'] = 'Event deleted successfully';
            } else {
                $response['message'] = 'Event not found';
            }
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        
        // Close statement
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>

