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

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $calc_id = intval($_GET['delete']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete related courses (foreign key constraint)
        $deleteCoursesSql = "DELETE FROM gpa_courses WHERE calculation_id = ?";
        $stmt = $conn->prepare($deleteCoursesSql);
        $stmt->bind_param("i", $calc_id);
        $stmt->execute();
        
        // Then delete the calculation
        $deleteCalcSql = "DELETE FROM gpa_calculations WHERE id = ?";
        $stmt = $conn->prepare($deleteCalcSql);
        $stmt->bind_param("i", $calc_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        $successMessage = "GPA calculation #" . $calc_id . " has been deleted successfully.";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $errorMessage = "Error deleting calculation: " . $e->getMessage();
    }
}

// Pagination settings
$recordsPerPage = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Get total number of records for pagination
$countSql = "SELECT COUNT(*) as total FROM gpa_calculations";
$countResult = $conn->query($countSql);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get GPA calculations with user information
$sql = "SELECT gc.id, gc.user_id, gc.gpa, gc.calculation_date, u.email, u.username
        FROM gpa_calculations gc
        LEFT JOIN users u ON gc.user_id = u.id
        ORDER BY gc.calculation_date DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);  // This will show the actual MySQL error
}
$stmt->bind_param("ii", $offset, $recordsPerPage);
$stmt->execute();
$result = $stmt->get_result();

// Get course details for each calculation
$gpaData = [];
while ($row = $result->fetch_assoc()) {
    $calcId = $row['id'];
    
    // Get courses for this calculation
    $courseSql = "SELECT name, credits, grade FROM gpa_courses WHERE calculation_id = ?";
    $courseStmt = $conn->prepare($courseSql);
    $courseStmt->bind_param("i", $calcId);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    
    $courses = [];
    while ($course = $courseResult->fetch_assoc()) {
        $courses[] = $course;
    }
    
    $row['courses'] = $courses;
    $gpaData[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin GPA Calculations | APSIT Admin</title>
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
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
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .gpa-details {
            background-color: #f8f9fa;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }
        
        .course-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .course-item:last-child {
            border-bottom: none;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-color);
            background-color: white;
            border: 1px solid #ddd;
        }
        
        .pagination a:hover {
            background-color: #f8f9fa;
        }
        
        .pagination .active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
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
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header h1 {
                margin-bottom: 10px;
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
                    <li><a href="admin_pa.php" class="active"><i class="fas fa-calculator"></i> <span>GPA Calculations</span></a></li>
                    <li><a href="admin_timetable.php"><i class="fas fa-clock"></i> <span>Timetable</span></a></li>
                    <li><a href="admin_announcements.php"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
                </ul>
                
                <h3>Support</h3>
                <ul>
                    <li><a href="admin_help_desk.php"><i class="fas fa-headset"></i> <span>Help Desk</span></a></li>
                    <li><a href="admin_task.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                    <li><a href="admin_letters.php"><i class="fas fa-envelope"></i> <span>Admin Letters</span></a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>GPA Calculations Management</h1>
                <a href="admin_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            
            <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>All GPA Calculations</h2>
                    <div>
                        <span><i class="fas fa-calculator"></i> Total: <?php echo $totalRecords; ?> calculations</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>GPA</th>
                                    <th>Courses</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($gpaData) > 0): ?>
                                    <?php foreach ($gpaData as $index => $calculation): ?>
                                        <tr>
                                            <td><?php echo $calculation['id']; ?></td>
                                            <td>
                                                <?php if (isset($calculation['name']) && $calculation['name']): ?>
                                                    <?php echo htmlspecialchars($calculation['name']); ?>
                                                    <br>
                                                    <small><?php echo htmlspecialchars($calculation['email']); ?></small>
                                                <?php else: ?>
                                                    User ID: <?php echo $calculation['user_id']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo number_format($calculation['gpa'], 2); ?></strong></td>
                                            <td>
                                                <?php echo count($calculation['courses']); ?> courses
                                                <button class="btn btn-sm" onclick="toggleDetails(<?php echo $index; ?>)">
                                                    <i class="fas fa-chevron-down"></i> Details
                                                </button>
                                                
                                                <div id="details-<?php echo $index; ?>" class="gpa-details">
                                                    <?php foreach ($calculation['courses'] as $course): ?>
                                                        <div class="course-item">
                                                            <strong><?php echo htmlspecialchars($course['name']); ?></strong>
                                                            <br>
                                                            Credits: <?php echo $course['credits']; ?>,
                                                            Grade: <?php echo number_format($course['grade'], 1); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($calculation['calculation_date'])); ?></td>
                                            <td>
                                                <a href="?delete=<?php echo $calculation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this GPA calculation? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No GPA calculations found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=1">&laquo; First</a>
                                <a href="?page=<?php echo $page - 1; ?>">&lsaquo; Prev</a>
                            <?php endif; ?>
                            
                            <?php 
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>">Next &rsaquo;</a>
                                <a href="?page=<?php echo $totalPages; ?>">Last &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> APSIT Administration System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        function toggleDetails(index) {
            const detailsDiv = document.getElementById('details-' + index);
            if (detailsDiv.style.display === 'block') {
                detailsDiv.style.display = 'none';
            } else {
                detailsDiv.style.display = 'block';
            }
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        alert.style.opacity = '0';
                        alert.style.transition = 'opacity 0.5s';
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 500);
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>