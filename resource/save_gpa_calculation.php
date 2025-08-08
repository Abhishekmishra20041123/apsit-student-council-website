<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to save GPA calculations']);
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['gpa']) || !isset($data['courses'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $gpa = floatval($data['gpa']);
    $courses = $data['courses'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Save GPA calculation
        $sql = "INSERT INTO gpa_calculations (user_id, gpa) VALUES ($userId, $gpa)";
        $conn->query($sql);
        $calculationId = $conn->insert_id;
        
        // Save individual courses
        foreach ($courses as $course) {
            $name = $conn->real_escape_string($course['name']);
            $credits = intval($course['credits']);
            $grade = floatval($course['grade']);
            
            $courseSql = "INSERT INTO gpa_courses (calculation_id, name, credits, grade) 
                           VALUES ($calculationId, '$name', $credits, $grade)";
            $conn->query($courseSql);
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

