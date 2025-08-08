<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db_connect.php';

// Check if ticket ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user_help_desk.php");
    exit();
}

$ticket_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get ticket details
$sql = "SELECT * FROM help_desk WHERE id = $ticket_id AND user_id = $user_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: user_help_desk.php?error=ticket_not_found");
    exit();
}

$ticket = $result->fetch_assoc();

// Get responses
$responses = [];
$responses_sql = "SELECT * FROM help_desk_responses WHERE ticket_id = $ticket_id ORDER BY created_at ASC";
$responses_result = $conn->query($responses_sql);

if ($responses_result && $responses_result->num_rows > 0) {
    while ($row = $responses_result->fetch_assoc()) {
        $responses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket_id; ?> - Help Desk</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-item h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-item p {
            font-size: 16px;
        }
        
        .ticket-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .ticket-content h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .ticket-content p {
            white-space: pre-line;
        }
        
        .responses {
            margin-top: 30px;
        }
        
        .responses h2 {
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .response-item {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .response-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .response-author {
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .response-author i {
            color: var(--primary-color);
            margin-right: 5px;
        }
        
        .response-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .response-content {
            white-space: pre-line;
        }
        
        .no-responses {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-responses i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }
        
        .status-open {
            background-color: var(--warning-color);
        }
        
        .status-in_progress {
            background-color: var(--primary-color);
        }
        
        .status-closed {
            background-color: var(--success-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ticket #<?php echo $ticket_id; ?></h1>
            <a href="user_help_desk.php" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Ticket Details</h2>
                <?php 
                    $status = $ticket['status'] ?? 'open';
                    $statusClass = 'status-' . $status;
                ?>
                <span class="status-badge <?php echo $statusClass; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                </span>
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
                        <p><?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="ticket-content">
                    <h3>Issue Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($ticket['issue'])); ?></p>
                </div>
                
                <div class="responses">
                    <h2>Responses</h2>
                    
                    <?php if (count($responses) > 0): ?>
                        <?php foreach ($responses as $response): ?>
                            <div class="response-item">
                                <div class="response-header">
                                    <div class="response-author">
                                        <i class="fas fa-user-shield"></i>
                                        <?php echo htmlspecialchars($response['admin_name']); ?>
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
                    <?php else: ?>
                        <div class="no-responses">
                            <i class="fas fa-comments"></i>
                            <h3>No Responses Yet</h3>
                            <p>Your ticket is being reviewed by our support team.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 