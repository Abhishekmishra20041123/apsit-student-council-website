<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include the database connection file
include '../db_connect.php';

// Function to get all timetable events
function getAllTimetableEvents($conn) {
    $sql = "SELECT te.*, u.name as user_name, u.email as user_email 
            FROM timetable_events te 
            JOIN users u ON te.user_id = u.id 
            ORDER BY te.day, te.start_time";
    $result = $conn->query($sql);
    
    $events = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    return $events;
}

// Get all timetable events
$timetableEvents = getAllTimetableEvents($conn);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APSIT Admin - Timetable Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --text-color: #333;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #f5f7fa;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu h3 {
            padding: 0 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .sidebar-menu ul {
            list-style: none;
            margin-bottom: 20px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .header {
            background-color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.2rem;
            color: var(--secondary-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .filter-controls {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-controls label {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .filter-controls select,
        .filter-controls input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-width: 150px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table th,
        table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 20px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header h3,
            .sidebar-header p,
            .sidebar-menu h3,
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a i {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-top: 10px;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls > * {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>APSIT Admin</h3>
                <p>Dashboard</p>
            </div>
            
            <div class="sidebar-menu">
                <h3>Main</h3>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="user_page.php"><i class="fas fa-users"></i> <span>Users</span></a></li>
                    <li><a href="admin_event.php"><i class="fas fa-calendar-alt"></i> <span>Events</span></a></li>
                    <li><a href="admin_workshop.php"><i class="fas fa-chalkboard-teacher"></i> <span>Workshops</span></a></li>
                    <li><a href="../Meeting/admin_meetings.php"><i class="fas fa-clipboard-list"></i> <span>Meeting Minutes</span></a></li>
                </ul>
                
                <h3>Academic</h3>
                <ul>
                    <li><a href="admin_study_materials.php"><i class="fas fa-book"></i> <span>Study Materials</span></a></li>
                    <li><a href="admin_pa.php"><i class="fas fa-calculator"></i> <span>GPA Calculations</span></a></li>
                    <li><a href="admin_timetable.php" class="active"><i class="fas fa-clock"></i> <span>Timetable</span></a></li>
                    <li><a href="admin_announcements.php"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                </ul>
                
                <h3>Support</h3>
                <ul>
                    <li><a href="admin_help_desk.php"><i class="fas fa-question-circle"></i> <span>Help Desk</span></a></li>
                    <li><a href="admin_task.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                    <li><a href="admin_letters.php"><i class="fas fa-envelope"></i> <span>Admin Letters</span></a></li>
                    <li><a href="admin_login.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Timetable Management</h1>
                <div class="user-info">
                    <a href="../apsithomepage.php">
                        <img src="../Untitled design.png" alt="Admin">
                    </a>
                    <div>
                        <p>Welcome, <strong>Administrator</strong></p>
                        <small>Last login: Today, <?php echo date('h:i A'); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Timetable Management Content -->
            <div class="card">
                <div class="card-header">
                    <h2>All Timetable Events</h2>
                </div>
                <div class="card-body">
                    <div class="filter-controls">
                        <label for="filter-day">Filter by Day:</label>
                        <select id="filter-day">
                            <option value="">All Days</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                        
                        <label for="filter-user">Filter by User:</label>
                        <input type="text" id="filter-user" placeholder="Enter user name">
                        
                        <button class="btn" onclick="filterTimetable()">Apply Filters</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="timetable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Event Name</th>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timetableEvents as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['user_name']); ?> (<?php echo htmlspecialchars($event['user_email']); ?>)</td>
                                        <td><?php echo htmlspecialchars($event['name']); ?></td>
                                        <td><?php echo htmlspecialchars($event['day']); ?></td>
                                        <td><?php echo htmlspecialchars($event['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($event['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                                        <td>
                                            <button class="btn btn-sm" onclick="editEvent(<?php echo $event['id']; ?>)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> APSIT Administration System. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script>
        function filterTimetable() {
            const day = document.getElementById('filter-day').value.toLowerCase();
            const user = document.getElementById('filter-user').value.toLowerCase();
            const rows = document.querySelectorAll('#timetable tbody tr');
            
            rows.forEach(row => {
                const rowDay = row.children[2].textContent.toLowerCase();
                const rowUser = row.children[0].textContent.toLowerCase();
                const showRow = (day === '' || rowDay === day) && (user === '' || rowUser.includes(user));
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        function editEvent(eventId) {
            // Implement edit functionality (e.g., redirect to edit page or show modal)
            alert('Edit event with ID: ' + eventId);
        }
        
        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                // Implement delete functionality (e.g., AJAX call to delete_timetable_event.php)
                alert('Delete event with ID: ' + eventId);
            }
        }
    </script>
</body>
</html>