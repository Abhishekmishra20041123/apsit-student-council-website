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

// Handle task deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $task_id = intval($_GET['delete']);
    
    // Delete the task
    $delete_sql = "DELETE FROM tasks WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $task_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Task deleted successfully.";
    } else {
        $error_message = "Error deleting task: " . $conn->error;
    }
    
    $delete_stmt->close();
}

// Get all tasks with user information
$sql = "SELECT t.*, u.name as user_name, u.email as user_email 
        FROM tasks t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.deadline ASC, t.priority ASC";
$result = $conn->query($sql);

// Get task statistics
$stats = [
    'total' => 0,
    'completed' => 0,
    'pending' => 0,
    'high_priority' => 0,
    'medium_priority' => 0,
    'low_priority' => 0
];

if ($result && $result->num_rows > 0) {
    $stats['total'] = $result->num_rows;
    
    // Reset result pointer
    $result->data_seek(0);
    
    // Count different task types
    while ($row = $result->fetch_assoc()) {
        if (isset($row['completed']) && $row['completed'] == 1) {
            $stats['completed']++;
        } else {
            $stats['pending']++;
        }
        
        if (strtolower($row['priority']) == 'high') {
            $stats['high_priority']++;
        } elseif (strtolower($row['priority']) == 'medium') {
            $stats['medium_priority']++;
        } elseif (strtolower($row['priority']) == 'low') {
            $stats['low_priority']++;
        }
    }
    
    // Reset result pointer again
    $result->data_seek(0);
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks Management - APSIT Admin</title>
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
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
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
        
        .badge-info {
            background-color: var(--primary-color);
        }
        
        .badge-secondary {
            background-color: #6c757d;
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
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
        }
        
        .btn-warning:hover {
            background-color: #f39c12;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .task-details {
            display: flex;
            flex-direction: column;
        }
        
        .task-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .task-course {
            font-size: 12px;
            color: #6c757d;
        }
        
        .task-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }
        
        .task-meta i {
            color: #6c757d;
        }
        
        .user-details {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #6c757d;
            font-weight: 600;
            font-size: 12px;
        }
        
        .user-info-small {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-email {
            font-size: 12px;
            color: #6c757d;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-container {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        
        .search-container i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 10px 10px 35px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 150px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--danger-color);
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
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-top: 10px;
            }
            
            .task-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table th:nth-child(3),
            table td:nth-child(3) {
                display: none;
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
                    <li><a href="admin_timetable.php"><i class="fas fa-clock"></i> <span>Timetable</span></a></li>
                    <li><a href="admin_announcements.php"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                </ul>
                
                <h3>Support</h3>
                <ul>
                    <li><a href="admin_help_desk.php"><i class="fas fa-headset"></i> <span>Help Desk</span></a></li>
                    <li><a href="admin_task.php" class="active"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                    <li><a href="admin_letters.php"><i class="fas fa-envelope"></i> <span>Admin Letters</span></a></li>
                    <li><a href="admin_login.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Tasks Management</h1>
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
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Task Statistics -->
            <div class="card">
                <div class="card-header">
                    <h2>Task Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #3498db;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p>Total Tasks</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #2ecc71;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['completed']; ?></h3>
                                <p>Completed Tasks</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #f39c12;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pending']; ?></h3>
                                <p>Pending Tasks</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: #e74c3c;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['high_priority']; ?></h3>
                                <p>High Priority Tasks</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Task List -->
            <div class="card">
                <div class="card-header">
                    <h2>All Tasks</h2>
                </div>
                <div class="card-body">
                    <div class="filter-container">
                        <div class="search-container">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" class="search-input" placeholder="Search tasks...">
                        </div>
                        <select id="priorityFilter" class="filter-select">
                            <option value="">All Priorities</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Priority</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Added By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr data-task-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                            data-priority="<?php echo htmlspecialchars($row['priority']); ?>"
                                            data-status="<?php echo isset($row['completed']) && $row['completed'] == 1 ? 'completed' : 'pending'; ?>">
                                            <td>
                                                <div class="task-details">
                                                    <div class="task-title"><?php echo htmlspecialchars($row['name']); ?></div>
                                                    <?php if (!empty($row['course'])): ?>
                                                        <div class="task-course">Course: <?php echo htmlspecialchars($row['course']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $priorityClass = 'badge-secondary';
                                                if (strtolower($row['priority']) == 'high') {
                                                    $priorityClass = 'badge-danger';
                                                } elseif (strtolower($row['priority']) == 'medium') {
                                                    $priorityClass = 'badge-warning';
                                                } elseif (strtolower($row['priority']) == 'low') {
                                                    $priorityClass = 'badge-info';
                                                }
                                                ?>
                                                <span class="badge <?php echo $priorityClass; ?>"><?php echo htmlspecialchars($row['priority']); ?></span>
                                            </td>
                                            <td>
                                                <div class="task-meta">
                                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($row['deadline'])); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (isset($row['completed']) && $row['completed'] == 1): ?>
                                                    <span class="badge badge-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="user-details">
                                                    <div class="user-avatar">
                                                        <?php echo strtoupper(substr($row['user_name'] ?? 'U', 0, 1)); ?>
                                                    </div>
                                                    <div class="user-info-small">
                                                        <div class="user-name"><?php echo htmlspecialchars($row['user_name'] ?? 'Unknown User'); ?></div>
                                                        <?php if (!empty($row['user_email'])): ?>
                                                            <div class="user-email"><?php echo htmlspecialchars($row['user_email']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="actions">
                                                <a href="#" class="btn btn-sm view-task" data-id="<?php echo $row['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger delete-task" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No tasks found</td>
                                    </tr>
                                <?php endif; ?>
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
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete the task "<span id="taskName"></span>"?</p>
            <p>This action cannot be undone.</p>
            <div style="margin-top: 20px; text-align: right;">
                <button id="cancelDelete" class="btn">Cancel</button>
                <a id="confirmDelete" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
    
    <!-- View Task Modal -->
    <div id="viewTaskModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Task Details</h2>
            <div id="taskDetails" style="margin-top: 20px;">
                <!-- Task details will be loaded here -->
            </div>
            <div style="margin-top: 20px; text-align: right;">
                <button class="btn close-modal">Close</button>
            </div>
        </div>
    </div>
    
    <script>
        // Delete confirmation modal
        const deleteModal = document.getElementById('deleteModal');
        const taskName = document.getElementById('taskName');
        const confirmDelete = document.getElementById('confirmDelete');
        const closeDeleteBtn = deleteModal.querySelector('.close');
        const cancelBtn = document.getElementById('cancelDelete');
        const deleteBtns = document.querySelectorAll('.delete-task');
        
        // View task modal
        const viewTaskModal = document.getElementById('viewTaskModal');
        const taskDetails = document.getElementById('taskDetails');
        const closeViewBtn = viewTaskModal.querySelector('.close');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        const viewBtns = document.querySelectorAll('.view-task');
        
        // Search and filter
        const searchInput = document.getElementById('searchInput');
        const priorityFilter = document.getElementById('priorityFilter');
        const statusFilter = document.getElementById('statusFilter');
        
        // Delete task functionality
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                taskName.textContent = name;
                confirmDelete.href = `?delete=${id}`;
                deleteModal.style.display = 'block';
            });
        });
        
        closeDeleteBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
        
        cancelBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
        
        // View task functionality
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                
                // In a real implementation, you would fetch task details via AJAX
                // For this example, we'll just use the data from the table row
                const row = this.closest('tr');
                const taskTitle = row.querySelector('.task-title').textContent;
                const taskCourse = row.querySelector('.task-course')?.textContent || 'No course specified';
                const priority = row.querySelector('.badge').textContent;
                const deadline = row.querySelector('.task-meta span').textContent;
                const status = row.querySelector('td:nth-child(4) .badge').textContent;
                const userName = row.querySelector('.user-name').textContent;
                const userEmail = row.querySelector('.user-email')?.textContent || 'No email available';
                
                // Build the task details HTML
                let detailsHTML = `
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin-bottom: 10px;">${taskTitle}</h3>
                        <p><strong>Course:</strong> ${taskCourse}</p>
                        <p><strong>Priority:</strong> ${priority}</p>
                        <p><strong>Deadline:</strong> ${deadline}</p>
                        <p><strong>Status:</strong> ${status}</p>
                    </div>
                    <div style="padding-top: 15px; border-top: 1px solid #eee;">
                        <h4 style="margin-bottom: 10px;">Added By</h4>
                        <p><strong>Name:</strong> ${userName}</p>
                        <p><strong>Email:</strong> ${userEmail}</p>
                    </div>
                `;
                
                taskDetails.innerHTML = detailsHTML;
                viewTaskModal.style.display = 'block';
            });
        });
        
        closeViewBtn.addEventListener('click', function() {
            viewTaskModal.style.display = 'none';
        });
        
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                viewTaskModal.style.display = 'none';
            });
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
            if (e.target == viewTaskModal) {
                viewTaskModal.style.display = 'none';
            }
        });
        
        // Search and filter functionality
        function filterTasks() {
            const searchTerm = searchInput.value.toLowerCase();
            const priorityValue = priorityFilter.value;
            const statusValue = statusFilter.value;
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const taskName = row.getAttribute('data-task-name').toLowerCase();
                const priority = row.getAttribute('data-priority');
                const status = row.getAttribute('data-status');
                
                const matchesSearch = taskName.includes(searchTerm);
                const matchesPriority = priorityValue === '' || priority === priorityValue;
                const matchesStatus = statusValue === '' || status === statusValue;
                
                if (matchesSearch && matchesPriority && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        searchInput.addEventListener('input', filterTasks);
        priorityFilter.addEventListener('change', filterTasks);
        statusFilter.addEventListener('change', filterTasks);
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            setTimeout(function() {
                alerts.forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 5000);
        }
    </script>
</body>
</html>