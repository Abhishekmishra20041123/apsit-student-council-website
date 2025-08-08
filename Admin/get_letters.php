<?php
include '../db_connect.php';

$sql = "SELECT * FROM letters ORDER BY created_at DESC";
$result = $conn->query($sql);

$letters = [];
while ($row = $result->fetch_assoc()) {
    $letters[] = $row;
}

echo json_encode($letters);
?>
