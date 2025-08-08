<?php
// Include database connection
include '../db_connect.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    
    // Validate input
    if (empty($title) || empty($content) || empty($category)) {
        $response['message'] = 'All fields are required';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, category, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            $response['message'] = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param("sss", $title, $content, $category);
            // Execute the statement
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Announcement added successfully';
            } else {
                $response['message'] = 'Error: ' . $stmt->error;
            }
            // Close statement
            $stmt->close();
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>