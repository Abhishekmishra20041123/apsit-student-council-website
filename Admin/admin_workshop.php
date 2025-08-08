<?php
include '../db_connect.php';

// Handle workshop deletion with confirmation
if (isset($_POST['delete_workshop'])) {
    $id = mysqli_real_escape_string($conn, $_POST['workshop_id']);
    $delete_query = "DELETE FROM workshops WHERE id = '$id'";
    if (mysqli_query($conn, $delete_query)) {
        $success_message = "Workshop deleted successfully!";
    } else {
        $error_message = "Error deleting workshop: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1E90FF;
            --primary-dark: #0066cc;
            --secondary: #0066cc;
            --success: #4cc9f0;
            --danger: #dc3545;
            --danger-dark: #c82333;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-light);
            margin: 0;
            padding: 0;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--primary);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .navbar h1 {
            font-size: 1.5rem;
            margin: 0;
        }
        
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 62px);
        }
        
        .sidebar {
            width: 250px;
            background-color: white;
            padding: 2rem 1rem;
            border-right: 1px solid var(--gray-light);
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 1rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            padding: 1rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            border-left-color: var(--success);
            color: var(--dark);
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            border-left-color: var(--danger);
            color: var(--dark);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        table, th, td {
            border: 1px solid var(--gray-light);
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        tr:nth-child(even) {
            background-color: var(--gray-light);
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            margin-right: 0.5rem;
            text-decoration: none;
        }
        
        .btn:last-child {
            margin-right: 0;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-dark);
            border-color: var(--danger-dark);
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--dark);
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -10px;
            margin-left: -10px;
        }
        
        .form-col {
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 10px;
            padding-left: 10px;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--gray-light);
                padding: 1rem;
            }
            
            .content {
                padding: 1rem;
            }
            
            .form-col {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-header .btn {
                margin-top: 1rem;
                width: auto;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .close {
            font-size: 1.5rem;
            font-weight: 700;
            cursor: pointer;
        }

        .modal-footer {
            padding-top: 1rem;
            border-top: 1px solid var(--gray-light);
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }

        .action-cell {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-chalkboard-teacher"></i> Workshop Management System</h1>
        <div>
            <span>Welcome, Admin</span>
            <a href="admin_dashboard.php" class="btn btn-sm" style="background: white; color: var(--primary);">
                <i class="fas fa-sign-out-alt"></i> Back to dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">
        

        <div class="content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div>Registered Workshop Participants</div>
                    <button class="btn btn-primary" onclick="exportToCSV('users')">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Workshop</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT users.id, users.username, users.email, workshops.title, 
                                          workshop_registrations.registration_date 
                                          FROM users 
                                          JOIN workshop_registrations ON users.id = workshop_registrations.user_id
                                          JOIN workshops ON workshop_registrations.workshop_id = workshops.id
                                          ORDER BY workshop_registrations.registration_date DESC";
                                $result = mysqli_query($conn, $query);
                                if (!$result) {
                                    die("Query Failed: " . mysqli_error($conn));
                                }
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $registration_date = isset($row['registration_date']) ? 
                                                            date('M d, Y', strtotime($row['registration_date'])) : 'N/A';
                                        
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td>{$row['username']}</td>
                                                <td>{$row['email']}</td>
                                                <td>{$row['title']}</td>
                                                <td>{$registration_date}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' style='text-align: center;'>No registrations found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div>Manage Workshops</div>
                    <button class="btn btn-primary" onclick="showAddWorkshopForm()">
                    <i class="fas fa-plus"></i> Add New Workshop
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
$query = "SELECT * FROM workshops ORDER BY date DESC";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query Failed: " . mysqli_error($conn));  // ❗ Shows exact SQL error
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $workshop_date = date('M d, Y', strtotime($row['date']));
        $workshop_time = date('h:i A', strtotime($row['time'])); // Format time
        
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['description']}</td> <!-- Add description -->
                <td>{$workshop_date}</td>
                <td>{$workshop_time}</td> <!-- Add time -->
                <td>{$row['location']}</td>
                <td>{$row['max_participants']}</td> <!-- Use max_participants -->
                <td class='action-cell'>
                    <a href='../resource/edit_workshop.php?id={$row['id']}' class='btn btn-primary btn-sm'>
                        <i class='fas fa-edit'></i> Edit
                    </a>
                    <button onclick='confirmDelete({$row['id']})' class='btn btn-danger btn-sm'>
                        <i class='fas fa-trash'></i> Delete
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8' style='text-align: center;'>No workshops found</td></tr>";
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <p>Are you sure you want to delete this workshop? This action cannot be undone.</p>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" id="workshop_id" name="workshop_id">
                    <button type="button" class="btn btn-primary" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="delete_workshop" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Workshop Modal -->
    <div id="addWorkshopModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Workshop</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form action="..\resource\add_workshop.php" method="POST">
    <div class="form-group">
        <label class="form-label" for="title">Workshop Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="form-row">
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="date">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
        </div>
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="time">Time</label>  <!-- ✅ Added missing time field -->
                <input type="time" class="form-control" id="time" name="time" required>
            </div>
        </div>
        <div class="form-col">
            <div class="form-group">
                <label class="form-label" for="max_participants">Max Participants</label>  <!-- ✅ Changed name from capacity -->
                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" required>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="location">Location</label>
        <input type="text" class="form-control" id="location" name="location" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" required></textarea>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="closeAddModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Workshop</button>
    </div>
</form>

    <script>
        // Modal functions
        function confirmDelete(id) {
            document.getElementById('workshop_id').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        function showAddWorkshopForm() {
            document.getElementById('addWorkshopModal').style.display = 'block';
        }
        
        function closeAddModal() {
            document.getElementById('addWorkshopModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const deleteModal = document.getElementById('deleteModal');
            const addWorkshopModal = document.getElementById('addWorkshopModal');
            
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
            
            if (event.target == addWorkshopModal) {
                addWorkshopModal.style.display = 'none';
            }
        }
        
        // Export table data to CSV
        function exportToCSV(type) {
            // Get table data
            const table = document.querySelector('table');
            let csv = [];
            let rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    // Replace any commas in the cell text to avoid CSV issues
                    let text = cols[j].innerText.replace(/,/g, ' ');
                    // Add quotes around the field
                    row.push('"' + text + '"');
                }
                
                csv.push(row.join(','));
            }
            
            // Create CSV file
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            
            // Create download link and trigger download
            const a = document.createElement('a');
            const filename = type === 'users' ? 'workshop_participants.csv' : 'workshops.csv';
            a.setAttribute('href', url);
            a.setAttribute('download', filename);
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
</body>
</html>