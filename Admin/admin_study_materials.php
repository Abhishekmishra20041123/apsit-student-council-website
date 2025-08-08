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

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $material_id = intval($_GET['delete']);
    
    // First get the file path to delete the actual file
    $sql = "SELECT file_path FROM study_materials WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_path = 'uploads/materials/' . $row['file_path'];
        
        // Delete the file if it exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Now delete the database record
        $delete_sql = "DELETE FROM study_materials WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $material_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Study material deleted successfully.";
        } else {
            $error_message = "Error deleting study material: " . $conn->error;
        }
        
        $delete_stmt->close();
    }
    $stmt->close();
}

// Get all study materials with user information
$sql = "SELECT m.*, u.name as uploader_name, u.email as uploader_email 
        FROM study_materials m
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.upload_date DESC";
$result = $conn->query($sql);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Study Materials</title>
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
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            font-size: 24px;
            color: var(--secondary-color);
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
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
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
            font-size: 18px;
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
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
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
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 16px;
        }
        
        .file-info {
            display: flex;
            align-items: center;
        }
        
        .file-icon {
            margin-right: 10px;
            font-size: 24px;
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .file-meta {
            font-size: 12px;
            color: #6c757d;
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
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header .btn {
                margin-top: 10px;
            }
            
            table th:nth-child(3),
            table td:nth-child(3) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Study Materials Management</h1>
            <a href="admin_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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
        
        <div class="card">
            <div class="card-header">
                <h2>All Study Materials</h2>
            </div>
            <div class="card-body">
                <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Uploaded By</th>
                                <th>Upload Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="file-info">
                                        <?php
                                        $fileExt = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                                        $iconClass = 'fa-file';
                                        
                                        if (in_array($fileExt, ['pdf'])) {
                                            $iconClass = 'fa-file-pdf';
                                        } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                            $iconClass = 'fa-file-word';
                                        } elseif (in_array($fileExt, ['ppt', 'pptx'])) {
                                            $iconClass = 'fa-file-powerpoint';
                                        } elseif (in_array($fileExt, ['xls', 'xlsx'])) {
                                            $iconClass = 'fa-file-excel';
                                        } elseif (in_array($fileExt, ['zip', 'rar'])) {
                                            $iconClass = 'fa-file-archive';
                                        }
                                        ?>
                                        <div class="file-icon">
                                            <i class="fas <?php echo $iconClass; ?>"></i>
                                        </div>
                                        <div class="file-details">
                                            <div class="file-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                            <div class="file-meta"><?php echo strtoupper($fileExt); ?> file</div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['description'] ?? 'No description'); ?></td>
                                <td>
                                    <?php if ($row['uploader_name']): ?>
                                        <div><?php echo htmlspecialchars($row['uploader_name']); ?></div>
                                        <small><?php echo htmlspecialchars($row['uploader_email']); ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Unknown User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['upload_date'])); ?></td>
                                <td class="actions">
                                <a href="http://localhost/MY_PROJECT/resource/uploads/materials/<?php echo $row['file_path']; ?>" class="btn" target="_blank">
                                  <i class="fas fa-download"></i> Download
                                    </a>

                                    <a href="#" class="btn btn-danger delete-btn" data-id="<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No study materials found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete the study material "<span id="materialTitle"></span>"?</p>
            <p>This action cannot be undone.</p>
            <div style="margin-top: 20px; text-align: right;">
                <button id="cancelDelete" class="btn">Cancel</button>
                <a id="confirmDelete" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
    
    <script>
        // Delete confirmation modal
        const modal = document.getElementById('deleteModal');
        const materialTitle = document.getElementById('materialTitle');
        const confirmDelete = document.getElementById('confirmDelete');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelDelete');
        const deleteBtns = document.querySelectorAll('.delete-btn');
        
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                
                materialTitle.textContent = title;
                confirmDelete.href = `?delete=${id}`;
                modal.style.display = 'block';
            });
        });
        
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
            }
        });
        
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

