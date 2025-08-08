<?php
include(__DIR__ . '/../config.php');
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../db_connect.php';

    // Check if the table already exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'admin_letters'")->num_rows > 0;

    if (!$tableExists) {
        // Create admin_letters table if it doesn't exist
        $sql = "CREATE TABLE admin_letters (
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

        if ($conn->query($sql) === TRUE) {
            echo "Table admin_letters created successfully";
        } else {
            echo "Error creating table: " . $conn->error;
        }
    } else {
        // Check if admin_reply column exists
        $columnExists = $conn->query("SHOW COLUMNS FROM admin_letters LIKE 'admin_reply'")->num_rows > 0;
        
        if (!$columnExists) {
            // Add the admin_reply column if it doesn't exist
            $alterSql = "ALTER TABLE admin_letters ADD COLUMN admin_reply TEXT DEFAULT NULL";
            if ($conn->query($alterSql) === TRUE) {
                echo "Column admin_reply added successfully";
            } else {
                echo "Error adding column: " . $conn->error;
            }
        } else {
            echo "Table admin_letters already exists with all required columns";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

