<?php
include '../db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Prevent SQL injection

    // Delete user from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href = 'user_page.php';</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $stmt->error . "'); window.location.href = 'user_page.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
