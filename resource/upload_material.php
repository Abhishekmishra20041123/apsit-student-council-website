<?php
session_start();
include('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to upload materials']);
    exit;
}

// Check if form was submitted with a file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $userId = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // File upload handling
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip'];
    
    // Check if file type is allowed
    if (in_array($fileExt, $allowed)) {
        // Check for upload errors
        if ($fileError === 0) {
            // Check file size - 20MB max
            if ($fileSize < 20000000) {
                // Create unique filename
                $fileNameNew = uniqid('', true) . "." . $fileExt;
                $uploadDir = 'uploads/materials/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileDestination = $uploadDir . $fileNameNew;
                
                // Move uploaded file to destination
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Save file info to database
                    $sql = "INSERT INTO study_materials (user_id, title, subject, description, file_path) 
                            VALUES ('$userId', '$title', '$subject', '$description', '$fileNameNew')";
                    
                    if ($conn->query($sql) === TRUE) {
                        // Redirect back to resources page
                        header("Location:resource.php?upload=success");
                        exit;
                    } else {
                        // Delete uploaded file if database insert fails
                        unlink($fileDestination);
                        header("Location:resource.php?error=database");
                        exit;
                    }
                } else {
                    header("Location:resource.php?error=upload-failed");
                    exit;
                }
            } else {
                header("Location:resource.php?error=filesize");
                exit;
            }
        } else {
            header("Location:resource.php?error=upload-error");
            exit;
        }
    } else {
        header("Location:resource.php?error=filetype");
        exit;
    }
} else {
    header("Location:resource.php");
    exit;
}

$conn->close();
?>

