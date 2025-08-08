<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Include email handler
require_once 'email_handler.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Auto-delete closed tickets older than 30 days
try {
    $delete_date = date('Y-m-d H:i:s', strtotime('-30 days'));
    $delete_query = "DELETE FROM help_desk WHERE status = 'closed' AND updated_at < ?";
    $delete_stmt = $conn->prepare($delete_query);
    
    if (!$delete_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $delete_stmt->bind_param("s", $delete_date);
    $delete_stmt->execute();
    $deleted_count = $delete_stmt->affected_rows;
    if ($deleted_count > 0) {
        error_log("Auto-deleted $deleted_count closed tickets older than 30 days");
    }
} catch (Exception $e) {
    error_log("Error in auto-deleting closed tickets: " . $e->getMessage());
}

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_response'])) {
    try {
        // Start transaction
        $conn->begin_transaction();

        $ticket_id = intval($_POST['ticket_id']);
        $response = $_POST['response'];
        $current_time = date('Y-m-d H:i:s');
        
        // Update help desk ticket with response
        $update_query = "UPDATE help_desk SET 
                        admin_response = ?,
                        response_date = ?,
                        status = 'Resolved',
                        updated_at = ?
                        WHERE id = ?";
                        
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssi", $response, $current_time, $current_time, $ticket_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update ticket: " . $stmt->error);
        }

        // Get ticket and user information
        $ticket_query = "SELECT h.*, u.username as user_name, u.email as user_email 
                        FROM help_desk h 
                        LEFT JOIN users u ON h.user_id = u.id 
                        WHERE h.id = ?";
        $ticket_stmt = $conn->prepare($ticket_query);
        if (!$ticket_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $ticket_stmt->bind_param("i", $ticket_id);
        
        if (!$ticket_stmt->execute()) {
            throw new Exception("Failed to get ticket info: " . $ticket_stmt->error);
        }
        
        $result = $ticket_stmt->get_result();
        $ticket_data = $result->fetch_assoc();

        if (!$ticket_data) {
            throw new Exception("Ticket not found");
        }

        // Create notification
        $notification_query = "INSERT INTO notifications (user_id, title, message, created_at) 
                             VALUES (?, ?, ?, ?)";
                             
        $notification_title = "Help Desk Response";
        $notification_message = "Your help desk ticket '" . $ticket_data['title'] . "' has received a response.";
        
        $notify_stmt = $conn->prepare($notification_query);
        if (!$notify_stmt) {
            throw new Exception("Prepare notification failed: " . $conn->error);
        }
        
        $notify_stmt->bind_param("isss", $ticket_data['user_id'], $notification_title, $notification_message, $current_time);
        
        if (!$notify_stmt->execute()) {
            throw new Exception("Failed to create notification: " . $notify_stmt->error);
        }

        // Send email notification
        $email_subject = "Response to Your Help Desk Ticket";
        $email_message = "
        <html>
        <head>
            <title>Help Desk Response</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { padding: 20px; }
                .header { background: #007bff; color: white; padding: 10px; }
                .content { padding: 20px 0; }
                .footer { color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Help Desk Response</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($ticket_data['user_name']) . ",</p>
                    <p>An admin has responded to your help desk ticket:</p>
                    <p><strong>Ticket ID:</strong> " . $ticket_id . "</p>
                    <p><strong>Response:</strong><br>" . nl2br(htmlspecialchars($response)) . "</p>
                    <p>Please log in to your account to view the full response.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>Note: Closed tickets will be automatically deleted after 30 days.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $email_sent = sendEmailNotification($ticket_data['user_email'], $email_subject, $email_message);
        
        if (!$email_sent) {
            error_log("Failed to send email notification for ticket #" . $ticket_id);
        }

        // Commit transaction
        $conn->commit();
        
        header("Location: view_ticket.php?id=$ticket_id&success=Response submitted successfully");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in admin_help_desk.php: " . $e->getMessage());
        header("Location: view_ticket.php?id=$ticket_id&error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $update_sql = "UPDATE help_desk SET status = '$new_status' WHERE id = $ticket_id";
    $conn->query($update_sql);
    
    // Redirect to prevent form resubmission
    header("Location: admin_help_desk.php");
    exit();
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Filtering
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query
$where_clause = "";
if (!empty($status_filter)) {
    $status_filter = $conn->real_escape_string($status_filter);
    $where_clause .= " WHERE h.status = '$status_filter'";
} else {
    $where_clause .= " WHERE 1=1";
}

if (!empty($search_term)) {
    $search_term = $conn->real_escape_string($search_term);
    $where_clause .= " AND (u.username LIKE '%$search_term%' OR h.email LIKE '%$search_term%' OR h.issue LIKE '%$search_term%')";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM help_desk h LEFT JOIN users u ON h.user_id = u.id" . $where_clause;
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get tickets
$sql = "SELECT h.*, u.username, u.email as user_email FROM help_desk h LEFT JOIN users u ON h.user_id = u.id" . $where_clause . " ORDER BY h.created_at DESC LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
$tickets = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

// Get status counts for filter badges
$status_counts = [
    'all' => 0,
    'open' => 0,
    'in_progress' => 0,
    'closed' => 0
];

$count_sql = "SELECT status, COUNT(*) as count FROM help_desk GROUP BY status";
$count_result = $conn->query($count_sql);

if ($count_result && $count_result->num_rows > 0) {
    while ($row = $count_result->fetch_assoc()) {
        $status = $row['status'] ?? 'open';
        $status_counts[$status] = $row['count'];
        $status_counts['all'] += $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Desk Management - Admin Dashboard</title>
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
            --info-color: #3498db;
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
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .filter-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            background-color: #7f8c8d;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-badge:hover {
            opacity: 0.9;
        }
        
        .filter-badge.active {
            background-color: var(--primary-color);
        }
        
        .filter-badge.open {
            background-color: var(--warning-color);
        }
        
        .filter-badge.in_progress {
            background-color: var(--info-color);
        }
        
        .filter-badge.closed {
            background-color: var(--success-color);
        }
        
        .filter-badge .count {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.3);
            padding: 2px 8px;
            border-radius: 50px;
            margin-left: 8px;
            font-size: 12px;
        }
        
        .search-box {
            display: flex;
            max-width: 300px;
        }
        
        .search-box input {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
        }
        
        .search-box button {
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
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
        
        .badge-open {
            background-color: var(--warning-color);
        }
        
        .badge-in_progress {
            background-color: var(--info-color);
        }
        
        .badge-closed {
            background-color: var(--success-color);
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
            background-color: #e67e22;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-color);
            background-color: white;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: #f1f1f1;
        }
        
        .pagination .active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination .disabled {
            color: #aaa;
            cursor: not-allowed;
        }
        
        .status-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .status-dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            z-index: 1;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .status-dropdown:hover .status-dropdown-content {
            display: block;
        }
        
        .status-option {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .status-option:hover {
            background-color: #f1f1f1;
        }
        
        .status-option.open {
            color: var(--warning-color);
        }
        
        .status-option.in_progress {
            color: var(--info-color);
        }
        
        .status-option.closed {
            color: var(--success-color);
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
            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                max-width: 100%;
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
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
                    <li><a href="admin_help_desk.php" class="active"><i class="fas fa-headset"></i> <span>Help Desk</span></a></li>
                    <li><a href="admin_task.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                    <li><a href="admin_letters.php"><i class="fas fa-envelope"></i> <span>Admin Letters</span></a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Help Desk Management</h1>
                <div>
                    <a href="admin_dashboard.php" class="btn btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>All Help Desk Tickets</h2>
                </div>
                <div class="card-body">
                    <div class="filter-bar">
                        <div class="filter-options">
                            <a href="admin_help_desk.php" class="filter-badge <?php echo empty($status_filter) ? 'active' : ''; ?>">
                                All <span class="count"><?php echo $status_counts['all']; ?></span>
                            </a>
                            <a href="admin_help_desk.php?status=open" class="filter-badge open <?php echo $status_filter === 'open' ? 'active' : ''; ?>">
                                Open <span class="count"><?php echo $status_counts['open'] ?? 0; ?></span>
                            </a>
                            <a href="admin_help_desk.php?status=in_progress" class="filter-badge in_progress <?php echo $status_filter === 'in_progress' ? 'active' : ''; ?>">
                                In Progress <span class="count"><?php echo $status_counts['in_progress'] ?? 0; ?></span>
                            </a>
                            <a href="admin_help_desk.php?status=closed" class="filter-badge closed <?php echo $status_filter === 'closed' ? 'active' : ''; ?>">
                                Closed <span class="count"><?php echo $status_counts['closed'] ?? 0; ?></span>
                            </a>
                        </div>
                        
                        <form class="search-box" method="GET" action="admin_help_desk.php">
                            <?php if (!empty($status_filter)): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <input type="text" name="search" placeholder="Search tickets..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Issue</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($tickets) > 0): ?>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['username']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                                            <td>
                                                <?php 
                                                    $issue = htmlspecialchars($ticket['issue']);
                                                    echo strlen($issue) > 50 ? substr($issue, 0, 50) . '...' : $issue;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status = $ticket['status'] ?? 'open';
                                                    $badgeClass = 'badge-open';
                                                    
                                                    if ($status === 'in_progress') {
                                                        $badgeClass = 'badge-in_progress';
                                                    } elseif ($status === 'closed') {
                                                        $badgeClass = 'badge-closed';
                                                    }
                                                ?>
                                                <div class="status-dropdown">
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                    </span>
                                                    <div class="status-dropdown-content">
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                            <input type="hidden" name="update_status" value="1">
                                                            
                                                            <button type="submit" name="status" value="open" class="status-option open" style="border:none; background:none; width:100%; text-align:left;">
                                                                <i class="fas fa-circle"></i> Open
                                                            </button>
                                                            
                                                            <button type="submit" name="status" value="in_progress" class="status-option in_progress" style="border:none; background:none; width:100%; text-align:left;">
                                                                <i class="fas fa-spinner"></i> In Progress
                                                            </button>
                                                            
                                                            <button type="submit" name="status" value="closed" class="status-option closed" style="border:none; background:none; width:100%; text-align:left;">
                                                                <i class="fas fa-check-circle"></i> Closed
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo isset($ticket['created_at']) ? date('M d, Y', strtotime($ticket['created_at'])) : 'N/A';
                                                ?>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>&respond=1" class="btn btn-sm btn-success">
                                                    <i class="fas fa-reply"></i> Respond
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center;">No help desk tickets found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-chevron-left"></i> Previous</span>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
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
</body>
</html>