<?php
include('../db_connect.php');

// Get all upcoming workshops
$sql = "SELECT * FROM workshops WHERE date >= CURDATE() ORDER BY date ASC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<li class="workshop-item">';
        echo '<div class="workshop-info">';
        echo '<h4>' . htmlspecialchars($row['title']) . '</h4>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        echo '<p><strong>Date:</strong> ' . date('F j, Y', strtotime($row['date'])) . ' at ' . date('g:i A', strtotime($row['time'])) . '</p>';
        echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
        echo '</div>';
        echo '<button class="btn register-btn" data-workshop="' . htmlspecialchars($row['title']) . '" data-id="' . $row['id'] . '">Register</button>';
        echo '</li>';
    }
} else {
    echo '<li>No upcoming workshops scheduled.</li>';
}

$conn->close();
?>

