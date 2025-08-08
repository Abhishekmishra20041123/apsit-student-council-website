<?php
include('../db_connect.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get subject filter if provided
$subject = isset($_GET['subject']) ? $conn->real_escape_string($_GET['subject']) : 'All';

try {
    // Build the query with exact column names
    $sql = "SELECT 
                m.id,
                m.user_id,
                m.title,
                m.subject,
                m.description,
                m.file_path,
                m.upload_date,
                u.username AS uploader_name
            FROM study_materials m
            JOIN users u ON m.user_id = u.id";
    
    // Add subject filter if needed
    if ($subject !== 'All') {
        $sql .= " WHERE m.subject = '$subject'";
    }
    
    // Add sorting by upload date (newest first)
    $sql .= " ORDER BY m.upload_date DESC";
    
    // Execute query
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Display results
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="material-item">';
            echo '<div class="material-info">';
            echo '<h4>' . htmlspecialchars($row['title']) . '</h4>';
            echo '<p><strong>Subject:</strong> ' . htmlspecialchars($row['subject']) . '</p>';
            echo '<p><strong>Uploaded by:</strong> ' . htmlspecialchars($row['uploader_name']) . '</p>';
            echo '<p><strong>Date:</strong> ' . date('F j, Y', strtotime($row['upload_date'])) . '</p>';
            
            if (!empty($row['description'])) {
                echo '<p><strong>Description:</strong> ' . htmlspecialchars($row['description']) . '</p>';
            }
            
            echo '</div>';
            echo '<div class="material-actions">';
            
            // Modified download link - using file_path instead of id
            // In your display loop, modify the download link to:
$file_path = 'http://localhost/MY_PROJECT/resource/uploads/materials/' . htmlspecialchars($row['file_path']);
echo '<a href="' . $file_path . '" 
     class="btn download-material" 
     download="' . htmlspecialchars($row['title']) . '">Download</a>';
            
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No study materials available' . ($subject !== 'All' ? ' for ' . htmlspecialchars($subject) : '') . '.</p>';
    }
    
} catch (Exception $e) {
    error_log("Materials Error: " . $e->getMessage());
    echo '<p>Error loading materials. Please try again later.</p>';
    // For debugging during development:
    echo '<!-- Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
}

$conn->close();
?>