<?php
session_start();
include('../db_connect.php');

// Check if an ID was provided
if (isset($_GET['id'])) {
    $materialId = $conn->real_escape_string($_GET['id']);
    
    // Get the file information from the database
    $sql = "SELECT * FROM study_materials WHERE id = '$materialId'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $material = $result->fetch_assoc();
        $filePath = 'uploads/materials/' . $material['file_path'];
        
        // Check if the file exists
        if (file_exists($filePath)) {
            // Get file extension to determine content type
            $fileExt = strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION));
            
            // Set appropriate content type based on file extension
            switch ($fileExt) {
                case 'pdf':
                    $contentType = 'application/pdf';
                    break;
                case 'doc':
                case 'docx':
                    $contentType = 'application/msword';
                    break;
                case 'ppt':
                case 'pptx':
                    $contentType = 'application/vnd.ms-powerpoint';
                    break;
                case 'zip':
                    $contentType = 'application/zip';
                    break;
                default:
                    $contentType = 'application/octet-stream';
            }
            
            // Set headers for file download
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $material['title'] . '.' . $fileExt . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            
            // Clear output buffer
            ob_clean();
            flush();
            
            // Read and output file
            readfile($filePath);
            exit;
        } else {
            // File not found
            header('Location:resources.php?error=file-not-found');
            exit;
        }
    } else {
        // Material not found in database
        header('Location:resources.php?error=material-not-found');
        exit;
    }
} else {
    // No ID provided
    header('Location:resources.php?error=invalid-request');
    exit;
}

$conn->close();
?>

