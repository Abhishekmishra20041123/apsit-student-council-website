<?php
include(__DIR__ . '/../config.php');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
    
    if (!$table_exists) {
        // Create the notifications table
        $create_table_sql = "
            CREATE TABLE notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_read TINYINT(1) DEFAULT 0,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        
        if ($conn->query($create_table_sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Notifications table created successfully'
            ]);
        } else {
            throw new Exception("Error creating table: " . $conn->error);
        }
    } else {
        // Table exists, verify structure
        $columns = $conn->query("SHOW COLUMNS FROM notifications");
        $column_names = [];
        while ($column = $columns->fetch_assoc()) {
            $column_names[] = $column['Field'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Notifications table already exists',
            'debug' => [
                'columns' => $column_names
            ]
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => [
            'mysql_error' => $conn->error ?? null
        ]
    ]);
}
?> 