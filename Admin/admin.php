<?php
// Include the database connection file
include '../db_connect.php';

// Function to count records from a table
function countRecords($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

// Array of all tables in the database
$tables = [
    'users' => ['icon' => 'fa-users', 'color' => '#3498db', 'title' => 'Users'],
    'events' => ['icon' => 'fa-calendar-alt', 'color' => '#2ecc71', 'title' => 'Events'],
    'workshop_registrations' => ['icon' => 'fa-user-graduate', 'color' => '#e74c3c', 'title' => 'Workshop Registrations'],
    'workshops' => ['icon' => 'fa-chalkboard-teacher', 'color' => '#9b59b6', 'title' => 'Workshops'],
    'timetable_events' => ['icon' => 'fa-clock', 'color' => '#f39c12', 'title' => 'Timetable Events'],
    'study_materials' => ['icon' => 'fa-book', 'color' => '#1abc9c', 'title' => 'Study Materials'],
    'tasks' => ['icon' => 'fa-tasks', 'color' => '#34495e', 'title' => 'Tasks'],
    'help_desk' => ['icon' => 'fa-headset', 'color' => '#d35400', 'title' => 'Help Desk Tickets'],
    'meeting_minutes' => ['icon' => 'fa-file-alt', 'color' => '#27ae60', 'title' => 'Meeting Minutes'],
    'admin_letters' => ['icon' => 'fa-envelope', 'color' => '#8e44ad', 'title' => 'Admin Letters'],
    'gpa_calculations' => ['icon' => 'fa-calculator', 'color' => '#c0392b', 'title' => 'GPA Calculations'],
    'gpa_courses' => ['icon' => 'fa-graduation-cap', 'color' => '#16a085', 'title' => 'GPA Courses'],
    'user_preferences' => ['icon' => 'fa-sliders-h', 'color' => '#7f8c8d', 'title' => 'User Preferences'],
    'profile' => ['icon' => 'fa-id-card', 'color' => '#2980b9', 'title' => 'Profiles'],
    'achievements' => ['icon' => 'fa-trophy', 'color' => '#f1c40f', 'title' => 'Achievements'],
];

// Get counts for each table
$counts = [];
foreach ($tables as $table => $info) {
    $counts[$table] = countRecords($conn, $table);
}

// Get recent events (last 5)
$recentEvents = [];
$sql = "SELECT * FROM events ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentEvents[] = $row;
    }
}

// Get recent help desk tickets (last 5)
$recentTickets = [];
$sql = "SELECT * FROM help_desk ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentTickets[] = $row;
    }
}

// Get total users count
$totalUsers = $counts['users'];

// Get total workshops count
$totalWorkshops = $counts['workshops'];

// Get total workshop registrations
$totalRegistrations = $counts['workshop_registrations'];

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APSIT Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
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
            padding-top: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 15px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            margin-bottom: 5px;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu h3 {
            padding: 0 15px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            margin: 15px 0 5px;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin: 2px 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 10px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
            object-fit: cover;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }
        
        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            margin: 0;
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
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        table tbody tr {
            border-bottom: 1px solid #eee;
        }
        
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: var(--danger-color);
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
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
        }
        
        .action-card i {
            font-size: 32px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .action-card h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .action-card p {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 20px;
        }
        
        /* Responsive adjustments */
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
                font-size: 18px;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-top: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                    <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="#"><i class="fas fa-users"></i> <span>Users</span></a></li>
                    <li><a href="../Events/events.html"><i class="fas fa-calendar-alt"></i> <span>Events</span></a></li>
                    <li><a href="../resource/resource.php"><i class="fas fa-chalkboard-teacher"></i> <span>Workshops</span></a></li>
                </ul>
                
                <h3>Academic</h3>
                <ul>
                    <li><a href="../resource/resource.php"><i class="fas fa-book"></i> <span>Study Materials</span></a></li>
                    <li><a href="../resource/resource.php"><i class="fas fa-calculator"></i> <span>GPA Calculations</span></a></li>
                    <li><a href="../resource/resource.php"><i class="fas fa-clock"></i> <span>Timetable</span></a></li>
                    <li><a href="../Achivements/achivements.html"><i class="fas fa-trophy"></i> <span>Achievements</span></a></li>
                </ul>
                
                <h3>Support</h3>
                <ul>
                    <li><a href="../resource/resource.php"><i class="fas fa-headset"></i> <span>Help Desk</span></a></li>
                    <li><a href="../resource/resource.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                    <li><a href="../Letter/letters_to_admin.html"><i class="fas fa-envelope"></i> <span>Admin Letters</span></a></li>
                    <li><a href="../Profile/profile.html"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <img src="../Untitled design.png" alt="Admin">
                    <div>
                        <p>Welcome, <strong>Administrator</strong></p>
                        <small>Last login: Today, <?php echo date('h:i A'); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Main Stats -->
            <div class="card">
                <div class="card-header">
                    <h2>Key Metrics</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #3498db;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $totalUsers; ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #2ecc71;">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $totalWorkshops; ?></h3>
                                <p>Total Workshops</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #9b59b6;">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $totalRegistrations; ?></h3>
                                <p>Workshop Registrations</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #e74c3c;">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $counts['help_desk']; ?></h3>
                                <p>Help Desk Tickets</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- All Database Tables -->
            <div class="card">
                <div class="card-header">
                    <h2>Database Overview</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <?php foreach ($tables as $table => $info): ?>
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: <?php echo $info['color']; ?>">
                                <i class="fas <?php echo $info['icon']; ?>"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $counts[$table]; ?></h3>
                                <p><?php echo $info['title']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Events -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Events</h2>
                        <a href="#" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recentEvents) > 0): ?>
                                        <?php foreach ($recentEvents as $event): ?>
                                            <tr>
                                                <td><?php echo $event['id']; ?></td>
                                                <td><?php echo $event['event_name'] ?? 'Event #' . $event['id']; ?></td>
                                                <td><?php echo $event['event_date'] ?? 'N/A'; ?></td>
                                                <td>
                                                    <?php 
                                                    $status = $event['status'] ?? 'active';
                                                    $badgeClass = 'badge-success';
                                                    if ($status == 'pending') $badgeClass = 'badge-warning';
                                                    if ($status == 'cancelled') $badgeClass = 'badge-danger';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                                </td>
                                                <td>
                                                    <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center;">No events found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Help Desk Tickets -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Help Desk Tickets</h2>
                        <a href="#" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recentTickets) > 0): ?>
                                        <?php foreach ($recentTickets as $ticket): ?>
                                            <tr>
                                                <td><?php echo $ticket['id']; ?></td>
                                                <td><?php echo $ticket['subject'] ?? 'Ticket #' . $ticket['id']; ?></td>
                                                <td>
                                                    <?php 
                                                    $status = $ticket['status'] ?? 'open';
                                                    $badgeClass = 'badge-warning';
                                                    if ($status == 'closed') $badgeClass = 'badge-success';
                                                    if ($status == 'urgent') $badgeClass = 'badge-danger';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                                </td>
                                                <td><?php echo $ticket['created_at'] ?? 'N/A'; ?></td>
                                                <td>
                                                    <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center;">No help desk tickets found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Visualization -->
            <div class="card">
                <div class="card-header">
                    <h2>System Overview</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="overviewChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <div class="action-card">
                            <i class="fas fa-user-plus"></i>
                            <h3>Add User</h3>
                            <p>Create a new user account</p>
                            <a href="../registeration.html" class="btn">Add User</a>
                        </div>
                        
                        <div class="action-card">
                            <i class="fas fa-calendar-plus"></i>
                            <h3>Create Event</h3>
                            <p>Schedule a new event</p>
                            <a href="../Events/events.html" class="btn">Create Event</a>
                        </div>
                        
                        <div class="action-card">
                            <i class="fas fa-chalkboard"></i>
                            <h3>New Workshop</h3>
                            <p>Create a new workshop</p>
                            <a href="../resource/resource.php" class="btn">Create Workshop</a>
                        </div>
                        
                        <div class="action-card">
                            <i class="fas fa-chart-line"></i>
                            <h3>Generate Report</h3>
                            <p>Create detailed reports</p>
                            <a href="#" class="btn">Generate</a>
                        </div>

                        <div class="action-card">
                            <i class="fas fa-bullhorn"></i>
                            <h3>Add Announcement</h3>
                            <p>Create a new announcement</p>
                            <a href="admin_announcements.php" class="btn">Add Announcement</a>
                        </div>

                        <div class="action-card">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>Meeting Minutes</h3>
                            <p>Manage meeting minutes</p>
                            <a href="../Meeting/admin_meetings.php" class="btn">Manage Meetings</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> APSIT Administration System. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Chart.js implementation
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('overviewChart').getContext('2d');
            
            // Data from PHP
            var tableData = {
                labels: [
                    <?php 
                    $selectedTables = ['users', 'events', 'workshops', 'workshop_registrations', 'study_materials', 'help_desk', 'tasks'];
                    foreach ($selectedTables as $table) {
                        echo "'" . $tables[$table]['title'] . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Record Count',
                    data: [
                        <?php 
                        foreach ($selectedTables as $table) {
                            echo $counts[$table] . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        <?php 
                        foreach ($selectedTables as $table) {
                            echo "'" . $tables[$table]['color'] . "', ";
                        }
                        ?>
                    ],
                    borderWidth: 1
                }]
            };
            
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: tableData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>