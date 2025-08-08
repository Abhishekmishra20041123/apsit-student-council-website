<?php
// This script creates the user_preferences table if it doesn't exist
include(__DIR__ . '/../config.php');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create user_preferences table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS user_preferences (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    email_notifications ENUM('0', '1') DEFAULT '1',
    event_reminders ENUM('0', '1') DEFAULT '1',
    news_updates ENUM('0', '1') DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table user_preferences created successfully or already exists";
} else {
    echo "Error creating table: " . $conn->error;
}

// Add department, year, and bio columns to users table if they don't exist
$columns = [
    "department" => "VARCHAR(100) DEFAULT NULL",
    "year" => "VARCHAR(50) DEFAULT NULL",
    "bio" => "TEXT DEFAULT NULL"
];

foreach ($columns as $column => $definition) {
    $check_column = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    
    if ($check_column->num_rows == 0) {
        $alter_sql = "ALTER TABLE users ADD COLUMN $column $definition";
        
        if ($conn->query($alter_sql) === TRUE) {
            echo "Column $column added to users table successfully<br>";
        } else {
            echo "Error adding column $column: " . $conn->error . "<br>";
        }
    } else {
        echo "Column $column already exists in users table<br>";
    }
}

$conn->close();
?>

