<?php
// Include database connection
include '../db_connect.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get announcement ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Validate input
    if ($id <= 0) {
        $response['message'] = 'Invalid announcement ID';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Announcement deleted successfully';
            } else {
                $response['message'] = 'Announcement not found';
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