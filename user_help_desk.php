<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db_connect.php';

// Get user's tickets
$user_id = $_SESSION['user_id'];
$sql = "SELECT h.*, 
        (SELECT COUNT(*) FROM help_desk_responses WHERE ticket_id = h.id) as response_count
        FROM help_desk h 
        WHERE h.user_id = $user_id 
        ORDER BY h.created_at DESC";
$result = $conn->query($sql);
$tickets = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Help Desk Tickets</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--secondary-color);
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            background-color: var(--light-color);
            border-bottom: 1px solid #ddd;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .ticket-list {
            list-style: none;
        }
        
        .ticket-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s ease;
        }
        
        .ticket-item:hover {
            background-color: #f8f9fa;
        }
        
        .ticket-item:last-child {
            border-bottom: none;
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .ticket-id {
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .ticket-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .ticket-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .status-open {
            background-color: var(--warning-color);
            color: white;
        }
        
        .status-in_progress {
            background-color: var(--primary-color);
            color: white;
        }
        
        .status-closed {
            background-color: var(--success-color);
            color: white;
        }
        
        .ticket-content {
            margin-bottom: 10px;
        }
        
        .ticket-issue {
            color: #555;
            margin-bottom: 10px;
        }
        
        .response-count {
            color: var(--primary-color);
            font-size: 14px;
        }
        
        .no-tickets {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-tickets i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Help Desk Tickets</h1>
            <a href="submit_ticket.php" class="btn">
                <i class="fas fa-plus"></i> Submit New Ticket
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (count($tickets) > 0): ?>
                    <ul class="ticket-list">
                        <?php foreach ($tickets as $ticket): ?>
                            <li class="ticket-item">
                                <div class="ticket-header">
                                    <div>
                                        <span class="ticket-id">Ticket #<?php echo $ticket['id']; ?></span>
                                        <?php 
                                            $status = $ticket['status'] ?? 'open';
                                            $statusClass = 'status-' . $status;
                                        ?>
                                        <span class="ticket-status <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </div>
                                    <div class="ticket-date">
                                        <?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="ticket-content">
                                    <div class="ticket-issue">
                                        <?php echo nl2br(htmlspecialchars($ticket['issue'])); ?>
                                    </div>
                                    
                                    <div class="response-count">
                                        <?php if ($ticket['response_count'] > 0): ?>
                                            <i class="fas fa-comments"></i> 
                                            <?php echo $ticket['response_count']; ?> 
                                            <?php echo $ticket['response_count'] == 1 ? 'response' : 'responses'; ?>
                                        <?php else: ?>
                                            <i class="fas fa-clock"></i> Waiting for response
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 14px;">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-tickets">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>No Tickets Found</h3>
                        <p>You haven't submitted any help desk tickets yet.</p>
                        <a href="submit_ticket.php" class="btn" style="margin-top: 20px;">
                            Submit Your First Ticket
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 