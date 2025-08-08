<?php
include('../config.php');  // Include the database configuration

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Seeding database with sample data...<br>";

// Sample workshops
$workshops = [
    [
        'title' => 'Introduction to Machine Learning',
        'description' => 'Learn the basics of machine learning algorithms and their applications.',
        'date' => '2023-07-15',
        'time' => '14:00:00',
        'location' => 'Engineering Building, Room 302'
    ],
    [
        'title' => 'Research Paper Writing Workshop',
        'description' => 'Tips and techniques for writing effective research papers.',
        'date' => '2023-07-20',
        'time' => '10:00:00',
        'location' => 'Library, Conference Room'
    ],
    [
        'title' => 'Career Development Seminar',
        'description' => 'Prepare for your career with resume building and interview skills.',
        'date' => '2023-07-25',
        'time' => '15:30:00',
        'location' => 'Student Center, Main Hall'
    ],
    [
        'title' => 'Web Development Bootcamp',
        'description' => 'Intensive workshop on modern web development technologies.',
        'date' => '2023-07-30',
        'time' => '09:00:00',
        'location' => 'Computer Science Lab, Room 101'
    ]
];

// Insert workshop data
foreach ($workshops as $workshop) {
    $title = $conn->real_escape_string($workshop['title']);
    $description = $conn->real_escape_string($workshop['description']);
    $date = $workshop['date'];
    $time = $workshop['time'];
    $location = $conn->real_escape_string($workshop['location']);
    
    $sql = "INSERT INTO workshops (title, description, date, time, location) 
            VALUES ('$title', '$description', '$date', '$time', '$location')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Workshop '{$title}' added successfully<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    }
}

echo "Sample data seeded successfully!";

$conn->close();
?>

