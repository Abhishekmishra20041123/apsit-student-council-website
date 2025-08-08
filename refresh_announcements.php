<?php
// Include database connection
include 'db_connect.php';

// Fetch latest announcements
$latest_sql = "SELECT * FROM announcements WHERE category='latest' ORDER BY date DESC LIMIT 5";
$latest_result = $conn->query($latest_sql);

$output = '';

if ($latest_result && $latest_result->num_rows > 0) {
    while($row = $latest_result->fetch_assoc()) {
        $output .= '<span class="logo-placeholder">NEW</span> ';
        $output .= htmlspecialchars($row['title']) . ' | ';
    }
} else {
    $output = '<span class="logo-placeholder">INFO</span> No announcements available at this time';
}

// Close connection
$conn->close();

// Return the HTML for the marquee content
echo $output;
?>

