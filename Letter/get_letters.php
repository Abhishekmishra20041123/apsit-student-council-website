<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session and connect to database
session_start();
require_once '../db_connect.php';

// Always set content type to JSON
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get search and filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    // Check if the admin_letters table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_letters'");
    if ($tableCheck->num_rows == 0) {
        echo json_encode([]);
        exit;
    }

    // Build query
    $query = "SELECT id, subject, message, attachment, status, created_at FROM admin_letters WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    if (!empty($search)) {
        $query .= " AND (subject LIKE ? OR message LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }

    if (!empty($status) && $status !== 'all') {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY created_at DESC";

    // Prepare and execute query
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        echo json_encode([]);
        error_log("Database prepare error: " . $conn->error);
        exit;
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $letters = [];
    while ($row = $result->fetch_assoc()) {
        $letters[] = $row;
    }

    echo json_encode($letters);

    $stmt->close();
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in get_letters.php: ' . $e->getMessage());
    echo json_encode([]);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

