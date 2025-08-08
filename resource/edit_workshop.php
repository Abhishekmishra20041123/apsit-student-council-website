<?php
include '../db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}

$workshop_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM workshops WHERE id = '$workshop_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("Workshop not found.");
}

$workshop = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $max_participants = mysqli_real_escape_string($conn, $_POST['max_participants']);
    
    $update_query = "UPDATE workshops SET title='$title', description='$description', date='$date', time='$time', location='$location', max_participants='$max_participants' WHERE id='$workshop_id'";
    
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Workshop updated successfully!'); window.location.href='http://localhost/MY_PROJECT/Admin/admin_workshop.php';</script>";
    } else {
        echo "<p>Error updating workshop: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Workshop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            margin-top: 15px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Edit Workshop</h2>
    <form method="post">
        <label>Title: <input type="text" name="title" value="<?php echo $workshop['title']; ?>" required></label>
        <label>Description: <textarea name="description" required><?php echo $workshop['description']; ?></textarea></label>
        <label>Date: <input type="date" name="date" value="<?php echo $workshop['date']; ?>" required></label>
        <label>Time: <input type="time" name="time" value="<?php echo $workshop['time']; ?>" required></label>
        <label>Location: <input type="text" name="location" value="<?php echo $workshop['location']; ?>" required></label>
        <label>Max Participants: <input type="number" name="max_participants" value="<?php echo $workshop['max_participants']; ?>" required></label>
        <button type="submit">Update Workshop</button>
    </form>
</body>
</html>
