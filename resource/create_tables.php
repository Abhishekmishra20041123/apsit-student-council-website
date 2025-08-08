<?php
include(__DIR__ . '/../config.php');  // Include the database configuration

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Creating tables...<br>";

// Study Materials Table
$study_materials = "CREATE TABLE IF NOT EXISTS study_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($study_materials) === TRUE) {
    echo "Table 'study_materials' created successfully!<br>";
} else {
    echo "Error creating table 'study_materials': " . $conn->error . "<br>";
}

// Help Desk Queries Table
$help_desk = "CREATE TABLE IF NOT EXISTS help_desk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    issue TEXT NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($help_desk) === TRUE) {
    echo "Table 'help_desk' created successfully!<br>";
} else {
    echo "Error creating table 'help_desk': " . $conn->error . "<br>";
}

// Workshops Table
$workshops = "CREATE TABLE IF NOT EXISTS workshops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(255),
    max_participants INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($workshops) === TRUE) {
    echo "Table 'workshops' created successfully!<br>";
} else {
    echo "Error creating table 'workshops': " . $conn->error . "<br>";
}

// Workshop Registrations Table
$workshop_registrations = "CREATE TABLE IF NOT EXISTS workshop_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($workshop_registrations) === TRUE) {
    echo "Table 'workshop_registrations' created successfully!<br>";
} else {
    echo "Error creating table 'workshop_registrations': " . $conn->error . "<br>";
}

// Timetable Events Table
$timetable_events = "CREATE TABLE IF NOT EXISTS timetable_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($timetable_events) === TRUE) {
    echo "Table 'timetable_events' created successfully!<br>";
} else {
    echo "Error creating table 'timetable_events': " . $conn->error . "<br>";
}

// Tasks Table
$tasks = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    course VARCHAR(100),
    deadline DATE NOT NULL,
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($tasks) === TRUE) {
    echo "Table 'tasks' created successfully!<br>";
} else {
    echo "Error creating table 'tasks': " . $conn->error . "<br>";
}

// GPA Calculations Table
$gpa_calculations = "CREATE TABLE IF NOT EXISTS gpa_calculations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gpa DECIMAL(3,2) NOT NULL,
    calculation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($gpa_calculations) === TRUE) {
    echo "Table 'gpa_calculations' created successfully!<br>";
} else {
    echo "Error creating table 'gpa_calculations': " . $conn->error . "<br>";
}

// GPA Courses Table
$gpa_courses = "CREATE TABLE IF NOT EXISTS gpa_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calculation_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    credits INT NOT NULL,
    grade DECIMAL(3,1) NOT NULL,
    FOREIGN KEY (calculation_id) REFERENCES gpa_calculations(id)
)";

if ($conn->query($gpa_courses) === TRUE) {
    echo "Table 'gpa_courses' created successfully!<br>";
} else {
    echo "Error creating table 'gpa_courses': " . $conn->error . "<br>";
}

echo "All tables created successfully!";

$conn->close();
?>

