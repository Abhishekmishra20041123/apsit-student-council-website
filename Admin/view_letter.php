<?php
include '../db_connect.php';

$id = $_GET['id'];
$sql = "SELECT * FROM letters WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$letter = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Letter</title>
</head>
<body>
    <h2>Letter Details</h2>
    <p><strong>Subject:</strong> <?php echo $letter['subject']; ?></p>
    <p><strong>Message:</strong> <?php echo $letter['message']; ?></p>
    <p><strong>Created At:</strong> <?php echo $letter['created_at']; ?></p>
</body>
</html>
