<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include database connection
include(__DIR__ . '/../config.php');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ticket ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_help_desk.php");
    exit();
}

$ticket_id = intval($_GET['id']);

// Handle status updates
if (isset($_POST['update_status'])) {
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $update_sql = "UPDATE help_desk SET status = '$new_status' WHERE id = $ticket_id";
    $conn->query($update_sql);
    
    // Redirect to prevent form resubmission
    header("Location: view_ticket.php?id=$ticket_id&status_updated=1");
    exit();
}

// Handle response submission
if (isset($_POST['submit_response'])) {
    $response = $conn->real_escape_string($_POST['response']);
    $admin_id = $_SESSION['admin_id'] ?? 1;
    $admin_name = $_SESSION['admin_username'] ?? 'Administrator';
    
    // Check if responses table exists, if not create it
    $check_table = $conn->query("SHOW TABLES LIKE 'help_desk_responses'");
    if ($check_table->num_rows == 0) {
        $create_table = "CREATE TABLE help_desk_responses (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT(11) NOT NULL,
            admin_id INT(11) NOT NULL,
            admin_name VARCHAR(255) NOT NULL,
            response TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($create_table);
    }
    
    // Insert response
    $insert_sql = "INSERT INTO help_desk_responses (ticket_id, admin_id, admin_name, response) 
                  VALUES ($ticket_id, $admin_id, '$admin_name', '$response')";
    
    if ($conn->query($insert_sql)) {
        // Update ticket status to in_progress if it was open
        $check_status = $conn->query("SELECT status FROM help_desk WHERE id = $ticket_id");
        if ($check_status && $check_status->num_rows > 0) {
            $current_status = $check_status->fetch_assoc()['status'];
            if ($current_status == 'open') {
                $conn->query("UPDATE help_desk SET status = 'in_progress' WHERE id = $ticket_id");
            }
        }
        
        header("Location: view_ticket.php?id=$ticket_id&response_added=1");
        exit();
    } else {
        $response_error = "Failed to add response: " . $conn->error;
    }
}

// Get ticket details
$sql = "SELECT * FROM help_desk WHERE id = $ticket_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: admin_help_desk.php?error=ticket_not_found");
    exit();
}

$ticket = $result->fetch_assoc();

// Get responses if any
$responses = [];
$check_table = $conn->query("SHOW TABLES LIKE 'help_desk_responses'");
if ($check_table->num_rows > 0) {
    $responses_sql = "SELECT * FROM help_desk_responses WHERE ticket_id = $ticket_id ORDER BY created_at ASC";
    $responses_result = $conn->query($responses_sql);
    
    if ($responses_result && $responses_result->num_rows > 0) {
        while ($row = $responses_result->fetch_assoc()) {
            $responses[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket #<?php echo $ticket_id; ?> - Admin Dashboard</title>
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
        
        .ticket-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-item h3 {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .info-item p {
            font-size: 16px;
            font-weight: 600;
        }
        
        .ticket-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .ticket-content h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .ticket-content p {
            white-space: pre-line;
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
        
        .status-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .response-form {
            margin-top: 20px;
        }
        
        .response-form textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 150px;
            margin-bottom: 15px;
            font-family: inherit;
            font-size: 14px;
        }
        
        .response-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .responses {
            margin-top: 30px;
        }
        
        .response-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
        }
        
        .response-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .response-author {
            font-weight: 600;
        }
        
        .response-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .response-content {
            white-space: pre-line;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: white;
        }
        
        .alert-success {
            background-color: var(--success-color);
        }
        
        .alert-danger {
            background-color: var(--danger-color);
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
            .status-actions {
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header div {
                margin-top: 10px;
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
                </ul>
                
                <h3>Academic</h3>
                <ul>
                    <li><a href="admin_study_materials.php"><i class="fas fa-book"></i> <span>Study Materials</span></a></li>
                    <li><a href="admin_pa"><i class="fas fa-calculator"></i> <span>GPA Calculations</span></a></li>
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
                <h1>Ticket #<?php echo $ticket_id; ?></h1>
                <div>
                    <a href="admin_help_desk.php" class="btn btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Help Desk
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['status_updated'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Ticket status updated successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['response_added'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Response sent successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($response_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $response_error; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Ticket Details</h2>
                    <div>
                        <?php 
                            $status = $ticket['status'] ?? 'open';
                            $badgeClass = 'badge-open';
                            
                            if ($status === 'in_progress') {
                                $badgeClass = 'badge-in_progress';
                            } elseif ($status === 'closed') {
                                $badgeClass = 'badge-closed';
                            }
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="ticket-info">
                        <div class="info-item">
                            <h3>Submitted By</h3>
                            <p><?php echo htmlspecialchars($ticket['name']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <h3>Email</h3>
                            <p><?php echo htmlspecialchars($ticket['email']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <h3>Date Submitted</h3>
                            <p>
                                <?php 
                                    echo isset($ticket['created_at']) 
                                        ? date('F j, Y, g:i a', strtotime($ticket['created_at'])) 
                                        : 'N/A';
                                ?>
                            </p>
                        </div>
                        
                        <div class="info-item">
                            <h3>User ID</h3>
                            <p><?php echo $ticket['user_id'] ? $ticket['user_id'] : 'Guest'; ?></p>
                        </div>
                    </div>
                    
                    <div class="ticket-content">
                        <h3>Issue Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($ticket['issue'])); ?></p>
                    </div>
                    
                    <div class="status-actions">
                        <form method="POST" action="">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" name="status" value="open" class="btn <?php echo $status === 'open' ? 'btn-warning' : ''; ?>">
                                <i class="fas fa-circle"></i> Mark as Open
                            </button>
                        </form>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" name="status" value="in_progress" class="btn <?php echo $status === 'in_progress' ? 'btn-primary' : ''; ?>">
                                <i class="fas fa-spinner"></i> Mark as In Progress
                            </button>
                        </form>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" name="status" value="closed" class="btn <?php echo $status === 'closed' ? 'btn-success' : ''; ?>">
                                <i class="fas fa-check-circle"></i> Mark as Closed
                            </button>
                        </form>
                    </div>
                    
                    <!-- Responses Section -->
                    <?php if (count($responses) > 0): ?>
                        <div class="responses">
                            <h3>Responses</h3>
                            
                            <?php foreach ($responses as $response): ?>
                                <div class="response-item">
                                    <div class="response-header">
                                        <div class="response-author">
                                            <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($response['admin_name']); ?>
                                        </div>
                                        <div class="response-date">
                                            <?php echo date('F j, Y, g:i a', strtotime($response['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="response-content">
                                        <?php echo nl2br(htmlspecialchars($response['response'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Response Form -->
                    <div class="response-form">
                        <h3>Add Response</h3>
                        <form method="POST" action="">
                            <textarea name="response" placeholder="Type your response here..." required></textarea>
                            <button type="submit" name="submit_response" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Send Response
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> APSIT Administration System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>