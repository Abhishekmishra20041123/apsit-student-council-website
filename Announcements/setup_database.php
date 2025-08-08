<?php
include(__DIR__ . '/../config.php');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create announcements table
$announcements_table = "
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('latest', 'older') NOT NULL,
    date DATETIME NOT NULL
)";

if ($conn->query($announcements_table) === TRUE) {
    echo "Announcements table created successfully<br>";
} else {
    echo "Error creating announcements table: " . $conn->error . "<br>";
}

// Insert sample announcements if table is empty
$check_empty = "SELECT COUNT(*) as count FROM announcements";
$result = $conn->query($check_empty);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert sample announcements
    $sample_announcements = [
        [
            'title' => 'Meeting Scheduled',
            'content' => 'The next student council meeting is on January 20th at 4 PM.',
            'category' => 'latest',
            'date' => '2025-01-15 10:00:00'
        ],
        [
            'title' => 'Sports Fest',
            'content' => 'Join us for the annual Sports Fest starting February 5th!',
            'category' => 'latest',
            'date' => '2025-01-18 14:30:00'
        ],
        [
            'title' => 'Cultural Night',
            'content' => 'Don\'t miss the Cultural Night on February 15th at the main auditorium.',
            'category' => 'latest',
            'date' => '2025-01-20 09:15:00'
        ],
        [
            'title' => 'Environment Awareness Drive',
            'content' => 'Participate in the Environment Awareness Drive on March 1st. Let\'s make a difference!',
            'category' => 'latest',
            'date' => '2025-01-25 11:45:00'
        ],
        [
            'title' => 'Workshop on Leadership',
            'content' => 'A leadership workshop was held on January 10th. Check your emails for the recording link.',
            'category' => 'older',
            'date' => '2025-01-05 16:00:00'
        ],
        [
            'title' => 'Blood Donation Camp',
            'content' => 'Thanks to all who participated in the blood donation camp on December 15th.',
            'category' => 'older',
            'date' => '2024-12-16 13:20:00'
        ],
        [
            'title' => 'Alumni Meet',
            'content' => 'The Alumni Meet was successfully held on November 25th. Thank you for joining!',
            'category' => 'older',
            'date' => '2024-11-26 10:30:00'
        ],
        [
            'title' => 'Art Competition',
            'content' => 'The Art Competition held on October 10th showcased amazing talent. Winners have been notified.',
            'category' => 'older',
            'date' => '2024-10-12 15:45:00'
        ]
    ];

    // Prepare statement for inserting announcements
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, category, date) VALUES (?, ?, ?, ?)");

    // Check if statement was prepared successfully
    if ($stmt) {
        $stmt->bind_param("ssss", $title, $content, $category, $date);
        
        // Insert each announcement
        foreach ($sample_announcements as $announcement) {
            $title = $announcement['title'];
            $content = $announcement['content'];
            $category = $announcement['category'];
            $date = $announcement['date'];
            
            if ($stmt->execute()) {
                echo "Added announcement: " . $title . "<br>";
            } else {
                echo "Error adding announcement: " . $stmt->error . "<br>";
            }
        }
        
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error . "<br>";
    }
}

echo "<br>Database setup complete!<br>";
echo "<a href='announcements.php'>Go to Announcements Page</a>";

// Close connection
$conn->close();
?>