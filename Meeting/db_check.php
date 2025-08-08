<?php
include(__DIR__ . '/../config.php');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the meeting_minutes table exists
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'meeting_minutes'");
if ($result->num_rows > 0) {
    $tableExists = true;
}

// If the table doesn't exist, create it
if (!$tableExists) {
    $sql = "CREATE TABLE meeting_minutes (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        title VARCHAR(255) NOT NULL,
        meeting_date DATE NOT NULL,
        meeting_time TIME,
        attendees TEXT,
        agenda TEXT NOT NULL,
        discussion TEXT NOT NULL,
        action_items TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table meeting_minutes created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
}

$conn->close();
?>

