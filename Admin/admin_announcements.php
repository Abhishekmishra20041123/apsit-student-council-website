<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include database connection
include '../db_connect.php';

// Fetch latest announcements
$latest_sql = "SELECT * FROM announcements WHERE category='latest' ORDER BY created_at DESC";
$latest_result = $conn->query($latest_sql);

// Fetch older announcements
$older_sql = "SELECT * FROM announcements WHERE category='older' ORDER BY created_at DESC";
$older_result = $conn->query($older_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        header {
            background: linear-gradient(90deg, #4CAF50, #2E8B57);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 5px;
        }
        header h1 {
            font-size: 2.5em;
            margin: 0;
        }
        .an-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .announcements {
            flex: 1;
            min-width: 300px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .announcements h2 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .announcement-item {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            background-color: #fafafa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .announcement-item h3 {
            font-size: 1.3em;
            margin: 0 0 8px;
            color: #2E8B57;
        }
        .announcement-item p {
            margin: 0;
            font-size: 1em;
            color: #555;
            line-height: 1.5;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #ff5252;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
        }
        .add-announcement-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .add-announcement-btn:hover {
            background-color: #2E8B57;
        }
        .controls-container {
            text-align: center;
            margin: 20px 0;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 100px;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .date-info {
            font-size: 0.8em;
            color: #777;
            margin-top: 5px;
            font-style: italic;
        }
        .back-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            font-weight: bold;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #2980b9;
            text-decoration: none;
            color: white;
        }
        @media (max-width: 768px) {
            .an-container {
                flex-direction: column;
            }
            .modal-content {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Announcements</h1>
    </header>

    <div class="controls-container">
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <button id="addAnnouncementBtn" class="add-announcement-btn">
            <i class="fas fa-plus"></i> Add New Announcement
        </button>
    </div>

    <div class="an-container">
        <div class="announcements">
            <h2>Latest Announcements</h2>
            
            <?php
            if ($latest_result && $latest_result->num_rows > 0) {
                while($row = $latest_result->fetch_assoc()) {
                    echo '<div class="announcement-item" data-id="' . $row["id"] . '">';
                    echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                    echo '<p>' . htmlspecialchars($row["content"]) . '</p>';
                    echo '<p class="date-info">Posted on: ' . date('M d, Y', strtotime($row["created_at"])) . '</p>';
                    echo '<button class="delete-btn" onclick="deleteAnnouncement(' . $row["id"] . ')"><i class="fas fa-trash"></i></button>';
                    echo '</div>';
                }
            } else {
                echo '<p>No latest announcements available.</p>';
            }
            ?>
        </div>
        
        <div class="announcements">
            <h2>Older Announcements</h2>
            
            <?php
            if ($older_result && $older_result->num_rows > 0) {
                while($row = $older_result->fetch_assoc()) {
                    echo '<div class="announcement-item" data-id="' . $row["id"] . '">';
                    echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                    echo '<p>' . htmlspecialchars($row["content"]) . '</p>';
                    echo '<p class="date-info">Posted on: ' . date('M d, Y', strtotime($row["created_at"])) . '</p>';
                    echo '<button class="delete-btn" onclick="deleteAnnouncement(' . $row["id"] . ')"><i class="fas fa-trash"></i></button>';
                    echo '</div>';
                }
            } else {
                echo '<p>No older announcements available.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Add Announcement Modal -->
    <div id="addAnnouncementModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Announcement</h2>
            <form id="announcementForm">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="content">Content:</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="latest">Latest Announcement</option>
                        <option value="older">Older Announcement</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Add Announcement</button>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Get the modal
        var modal = document.getElementById("addAnnouncementModal");
        
        // Get the button that opens the modal
        var btn = document.getElementById("addAnnouncementBtn");
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        
        // When the user clicks the button, open the modal 
        btn.onclick = function() {
            modal.style.display = "block";
        }
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        // Form submission
        document.getElementById("announcementForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const title = document.getElementById("title").value;
            const content = document.getElementById("content").value;
            const category = document.getElementById("category").value;
            
            // Send data to server
            fetch('../Announcements/add_announcement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}&category=${encodeURIComponent(category)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Announcement added successfully!');
                    // Reset form and close modal
                    document.getElementById("announcementForm").reset();
                    modal.style.display = "none";
                    // Reload page to show new announcement
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the announcement.');
            });
        });
        
        // Delete announcement function
        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                fetch('../Announcements/delete_announcement.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the announcement from the DOM
                        const element = document.querySelector(`.announcement-item[data-id="${id}"]`);
                        if (element) {
                            element.remove();
                        }
                        alert('Announcement deleted successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the announcement.');
                });
            }
        }
    </script>
</body>
</html>

