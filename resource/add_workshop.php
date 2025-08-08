<?php
include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $max_participants = $_POST['max_participants'];
    
    $query = "INSERT INTO workshops (title, description, date, time, location, max_participants) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $date, $time, $location, $max_participants);
    
    if (mysqli_stmt_execute($stmt)) {
        // Workshop added successfully
        header("Location: http://localhost/MY_PROJECT/Admin/admin_workshop.php?success=1");
        exit();
    } else {
        // Error handling
        header("Location: http://localhost/MY_PROJECT/Admin/admin_workshop.php?error=" . urlencode(mysqli_error($conn)));
        exit();
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
