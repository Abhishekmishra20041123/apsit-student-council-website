<?php
// Add debugging code at the top of the file to help identify issues
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include database connection
require_once '../db_connect.php';

// Debug function
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug: " . addslashes($output) . "');</script>";
}

// Function to get all letters
function getLetters($conn, $search = '', $status = '') {
    // Debug the connection
    if ($conn->connect_error) {
        debug_to_console("Connection failed: " . $conn->connect_error);
        return [];
    }

    // Check if the admin_letters table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_letters'");
    debug_to_console("Table check rows: " . $tableCheck->num_rows);
    
    if ($tableCheck->num_rows == 0) {
        debug_to_console("admin_letters table does not exist");
        return [];
    }

    // Get table structure for debugging
    $structure = $conn->query("DESCRIBE admin_letters");
    $columns = [];
    while ($col = $structure->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
    debug_to_console("Table columns: " . implode(", ", $columns));

    // Simple query to get all letters
    $result = $conn->query("SELECT * FROM admin_letters");
    if (!$result) {
        debug_to_console("Query error: " . $conn->error);
        return [];
    }
    
    debug_to_console("Found " . $result->num_rows . " letters");
    
    $letters = [];
    while ($row = $result->fetch_assoc()) {
        $letters[] = $row;
    }
    
    return $letters;
}

// Handle letter reply
$replySuccess = false;
$replyError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letter_id'], $_POST['reply'])) {
    $letter_id = intval($_POST['letter_id']);
    $reply = trim($_POST['reply']);
    
    if (empty($reply)) {
        $replyError = "Reply cannot be empty";
    } else {
        // Update the letter with admin reply
        $stmt = $conn->prepare("UPDATE admin_letters SET admin_reply = ?, status = 'replied', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reply, $letter_id);
        
        if ($stmt->execute()) {
            $replySuccess = true;
        } else {
            $replyError = "Failed to save reply: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Get all letters
$letters = getLetters($conn, $search, $status);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Letters - APSIT Admin</title>
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
        
        .user-info a {
            display: inline-block;
            transition: transform 0.2s ease;
        }
        
        .user-info a:hover {
            transform: scale(1.1);
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            cursor: pointer;
        }
        
        .user-info div {
            display: flex;
            flex-direction: column;
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
        
        .filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .search-container {
            display: flex;
            max-width: 400px;
            width: 100%;
        }
        
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        
        .search-container button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .filter-container select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
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
            cursor: pointer;
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
            margin: 5% auto;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
        }
        
        .letter-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .letter-date, .letter-status, .letter-user {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .letter-date i, .letter-status i, .letter-user i {
            margin-right: 5px;
        }
        
        .letter-content {
            margin-bottom: 20px;
        }
        
        .letter-content h4 {
            margin-bottom: 10px;
            color: var(--secondary-color);
        }
        
        .letter-content p {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        
        .letter-attachment {
            margin-bottom: 20px;
        }
        
        .letter-attachment h4 {
            margin-bottom: 10px;
            color: var(--secondary-color);
        }
        
        .letter-attachment a {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            padding: 5px 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .letter-attachment a i {
            margin-right: 5px;
        }
        
        .letter-attachment a:hover {
            background-color: #eee;
        }
        
        .reply-form {
            margin-top: 20px;
        }
        
        .reply-form h4 {
            margin-bottom: 10px;
            color: var(--secondary-color);
        }
        
        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }
        
        .reply-form button {
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .reply-form button:hover {
            background-color: #2980b9;
        }
        
        .admin-reply {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .admin-reply h4 {
            margin-bottom: 10px;
            color: var(--secondary-color);
        }
        
        .admin-reply p {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
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
            .filters {
                flex-direction: column;
            }
            
            .search-container, .filter-container {
                width: 100%;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
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
                    <li><a href="admin_announcements.php"><i class="fas fa-bullhorn"></i> <span>Announcement</span></a></li>
                </ul>
                
                <h3>Support</h3>
                <ul>
                    <li><a href="admin_help_desk.php"><i class="fas fa-headset"></i> <span>Help Desk</span></a></li>
                    <li><a href="admin_task.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
                    <li><a href="admin_letters.php" class="active"><i class="fas fa-envelope"></i> <span>Admin Letters</span></a></li>
                    <li><a href="admin_login.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Letters</h1>
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
            
            <!-- Letters List -->
            <div class="card">
                <div class="card-header">
                    <h2>Letters from Users</h2>
                </div>
                <div class="card-body">
                    <?php if ($replySuccess): ?>
                    <div class="alert alert-success">
                        Your reply has been sent successfully.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($replyError)): ?>
                    <div class="alert alert-danger">
                        <?php echo $replyError; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="filters">
                        <form action="" method="GET" class="search-container">
                            <input type="text" name="search" placeholder="Search by subject or content..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        
                        <div class="filter-container">
                            <select name="status" id="status-filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $status === '' || $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>From</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($letters) > 0): ?>
                                    <?php foreach ($letters as $letter): ?>
                                        <tr data-id="<?php echo $letter['id']; ?>">
                                            <td><?php echo $letter['id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($letter['subject'] ?? 'No Subject'); ?>
                                                <?php if (!empty($letter['attachment'])): ?>
                                                    <i class="fas fa-paperclip text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                // Handle user display
                                                if (isset($letter['user_name'])) {
                                                    echo htmlspecialchars($letter['user_name']);
                                                } elseif (isset($letter['user_id'])) {
                                                    echo 'User #' . $letter['user_id'];
                                                } else {
                                                    echo 'Unknown User';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo isset($letter['created_at']) ? date('M d, Y h:i A', strtotime($letter['created_at'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php 
                                                $status = $letter['status'] ?? 'pending';
                                                $badgeClass = 'badge-warning';
                                                if ($status === 'read') $badgeClass = 'badge-info';
                                                if ($status === 'replied') $badgeClass = 'badge-success';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm view-letter" data-id="<?php echo $letter['id']; ?>">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No letters found</td>
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
    
    <!-- Letter Detail Modal -->
    <div id="letter-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-subject"></h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="letter-meta">
                    <div class="letter-user">
                        <i class="fas fa-user"></i> From: <span id="modal-user"></span>
                    </div>
                    <div class="letter-date">
                        <i class="fas fa-calendar"></i> <span id="modal-date"></span>
                    </div>
                    <div class="letter-status">
                        <i class="fas fa-info-circle"></i> Status: <span id="modal-status"></span>
                    </div>
                </div>
                
                <div class="letter-content">
                    <h4>Message:</h4>
                    <p id="modal-message"></p>
                </div>
                
                <div id="attachment-container" class="letter-attachment" style="display: none;">
                    <h4>Attachment:</h4>
                    <a id="modal-attachment" href="#" target="_blank">
                        <i class="fas fa-paperclip"></i> <span id="attachment-name"></span>
                    </a>
                </div>
                
                <div id="reply-container" class="admin-reply" style="display: none;">
                    <h4>Your Reply:</h4>
                    <p id="modal-reply"></p>
                </div>
                
                <div id="reply-form-container" class="reply-form">
                    <h4>Reply to this Letter:</h4>
                    <form id="reply-form" method="POST" action="">
                        <input type="hidden" id="letter-id" name="letter_id" value="">
                        <textarea name="reply" id="reply-text" placeholder="Type your reply here..." required></textarea>
                        <button type="submit">Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal elements
            const modal = document.getElementById('letter-modal');
            const closeModal = document.querySelector('.close-modal');
            const viewButtons = document.querySelectorAll('.view-letter');
            const tableRows = document.querySelectorAll('tbody tr');
            
            // Status filter
            const statusFilter = document.getElementById('status-filter');
            statusFilter.addEventListener('change', function() {
                window.location.href = 'admin_letters.php?status=' + this.value + '<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>';
            });
            
            // Open modal when view button is clicked
            function openLetterModal(letterId) {
                // Fetch letter details via AJAX
                fetch('get_letter_details_admin.php?id=' + letterId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        
                        // Populate modal with letter details
                        document.getElementById('modal-subject').textContent = data.subject;
                        document.getElementById('modal-user').textContent = data.user_name || 'User #' + data.user_id;
                        document.getElementById('modal-date').textContent = formatDate(data.created_at);
                        document.getElementById('modal-status').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                        document.getElementById('modal-message').textContent = data.message;
                        document.getElementById('letter-id').value = data.id;
                        
                        // Handle attachment
                        const attachmentContainer = document.getElementById('attachment-container');
                        if (data.attachment) {
                            attachmentContainer.style.display = 'block';
                            const attachmentLink = document.getElementById('modal-attachment');
                            attachmentLink.href = '../Letter/uploads/' + data.attachment;
                            document.getElementById('attachment-name').textContent = data.attachment.split('/').pop();
                        } else {
                            attachmentContainer.style.display = 'none';
                        }
                        
                        // Handle reply
                        const replyContainer = document.getElementById('reply-container');
                        const replyFormContainer = document.getElementById('reply-form-container');
                        
                        if (data.admin_reply) {
                            replyContainer.style.display = 'block';
                            document.getElementById('modal-reply').textContent = data.admin_reply;
                            
                            // Hide reply form if already replied
                            if (data.status === 'replied') {
                                replyFormContainer.style.display = 'none';
                            } else {
                                replyFormContainer.style.display = 'block';
                                document.getElementById('reply-text').value = data.admin_reply;
                            }
                        } else {
                            replyContainer.style.display = 'none';
                            replyFormContainer.style.display = 'block';
                            document.getElementById('reply-text').value = '';
                        }
                        
                        // Show modal
                        modal.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching letter details:', error);
                        alert('Error loading letter details. Please try again.');
                    });
            }
            
            // Add click event to view buttons
            viewButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const letterId = this.getAttribute('data-id');
                    openLetterModal(letterId);
                });
            });
            
            // Add click event to table rows
            tableRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    if (!e.target.classList.contains('btn')) {
                        const letterId = this.getAttribute('data-id');
                        if (letterId) {
                            openLetterModal(letterId);
                        }
                    }
                });
            });
            
            // Close modal
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Format date
            function formatDate(dateString) {
                const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            }
        });
    </script>
</body>
</html>

