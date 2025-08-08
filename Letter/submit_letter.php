<?php
include(__DIR__ . '/../config.php');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
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
        echo json_encode(['error' => 'You must be logged in to submit a letter']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Check if form data is submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate required fields
    if (empty($subject) || empty($message)) {
        echo json_encode(['error' => 'Subject and message are required']);
        exit;
    }

    // Check if the admin_letters table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_letters'");
    if ($tableCheck->num_rows == 0) {
        // Create the table if it doesn't exist
        $createTableSql = "CREATE TABLE admin_letters (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            attachment VARCHAR(255) DEFAULT NULL,
            status ENUM('pending', 'read', 'replied') DEFAULT 'pending',
            admin_reply TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        if (!$conn->query($createTableSql)) {
            echo json_encode(['error' => 'Could not create letters table: ' . $conn->error]);
            exit;
        }
    }

    // Handle file upload if present
    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        
        // Validate file size (5MB max)
        if ($fileSize > 5 * 1024 * 1024) {
            echo json_encode(['error' => 'File size exceeds 5MB limit']);
            exit;
        }
        
        // Validate file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['error' => 'Invalid file type. Please upload PDF, DOC, DOCX, JPG, or PNG']);
            exit;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                echo json_encode(['error' => 'Failed to create uploads directory']);
                exit;
            }
        }
        
        // Generate unique filename
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueName = uniqid('letter_') . '.' . $fileExt;
        $uploadPath = $uploadDir . $uniqueName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            $attachment = $uniqueName;
        } else {
            echo json_encode(['error' => 'Failed to upload file']);
            exit;
        }
    }

    // Insert letter into database
    $stmt = $conn->prepare("INSERT INTO admin_letters (user_id, subject, message, attachment) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("isss", $user_id, $subject, $message, $attachment);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log('Error in submit_letter.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while processing your request']);
} finally {
    // Close the connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>

