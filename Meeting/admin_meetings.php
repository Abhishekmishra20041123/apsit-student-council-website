<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../Admin/admin_login.php");
    exit();
}

// Get all meetings
$sql = "SELECT * FROM meeting_minutes ORDER BY meeting_date DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
$meetings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Meeting Minutes Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            padding-top: 80px !important;
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            height: 80px;
            padding-bottom: 30px;
        }
        .container, main {
            margin-top: 20px;
        }
        .meeting-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .meeting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
        }
        .validation-message {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
            display: none;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .toast {
            padding: 12px 20px;
            margin-bottom: 10px;
            border-radius: 4px;
            color: white;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out forwards;
        }
        .toast.info {
            background-color: #17a2b8;
        }
        .toast.success {
            background-color: #28a745;
        }
        .toast.error {
            background-color: #dc3545;
        }
        .toast i {
            margin-right: 10px;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .marquee {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 15px 0;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .marquee span {
            display: inline-block;
            padding: 0 20px;
            animation: marquee 20s linear infinite;
        }

        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body>
    <div class="sticky-top">
        <nav class="navbar navbar-expand-xl bg-dark navbar-dark fixed-top">
            <div class="container">
                <a class="navbar-brand" href="../apsithomepage.php">
                    <img src="../Untitled design.png" alt="APSIT_logo" style="height:45px; width:45px;">
                </a>
                <span class="col-sm-6 navbar-text text-white">A. P. Shah Institute of Technology, Mumbai</span>
                <div class="collapse navbar-collapse" id="collapsenavbar">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a href="../Admin/admin_dashboard.php" class="btn btn-dark">Back to Dashboard</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="container">
        <header>
            <h1>Meeting Minutes Management</h1>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="meet.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View Public Page
                    </a>
                </div>
                <div>
                    <button id="new-meeting-btn" class="btn btn-success" data-toggle="modal" data-target="#newMeetingModal">
                        <i class="fas fa-plus"></i> Create New Meeting
                    </button>
                </div>
            </div>
        </header>

        <!-- New Meeting Modal -->
        <div class="modal fade" id="newMeetingModal" tabindex="-1" role="dialog" aria-labelledby="newMeetingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newMeetingModalLabel">Create New Meeting Minutes</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="new-meeting-form">
                            <div class="form-group">
                                <label for="title">Meeting Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                                <div class="validation-message" id="title-validation">This field is required</div>
                            </div>
                            <div class="form-group">
                                <label for="meeting_date">Meeting Date</label>
                                <input type="date" class="form-control" id="meeting_date" name="meeting_date" max="<?php echo date('Y-m-d'); ?>" required>
                                <div class="validation-message" id="meeting_date-validation">This field is required</div>
                            </div>
                            <div class="form-group">
                                <label for="meeting_time">Meeting Time</label>
                                <input type="time" class="form-control" id="meeting_time" name="meeting_time">
                                <div class="validation-message" id="meeting_time-validation"></div>
                            </div>
                            <div class="form-group">
                                <label for="attendees">Attendees</label>
                                <textarea class="form-control" id="attendees" name="attendees" rows="3" required></textarea>
                                <div class="validation-message" id="attendees-validation">This field is required</div>
                            </div>
                            <div class="form-group">
                                <label for="agenda">Agenda</label>
                                <textarea class="form-control" id="agenda" name="agenda" rows="3" required></textarea>
                                <div class="validation-message" id="agenda-validation">This field is required</div>
                            </div>
                            <div class="form-group">
                                <label for="discussion">Discussion</label>
                                <textarea class="form-control" id="discussion" name="discussion" rows="5" required></textarea>
                                <div class="validation-message" id="discussion-validation">This field is required</div>
                            </div>
                            <div class="form-group">
                                <label for="action_items">Action Items</label>
                                <textarea class="form-control" id="action_items" name="action_items" rows="3"></textarea>
                                <div class="validation-message" id="action_items-validation"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="save-meeting">Save Meeting</button>
                    </div>
                </div>
            </div>
        </div>

        <main>
            <div class="row">
                <?php foreach ($meetings as $meeting): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card meeting-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($meeting['title']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('F j, Y', strtotime($meeting['meeting_date'])); ?>
                                    </small>
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo $meeting['meeting_time'] ?: 'Time not specified'; ?>
                                    </small>
                                </p>
                                <div class="action-buttons">
                                    <a href="meet.php?id=<?php echo $meeting['id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <form method="post" action="delete_meeting.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this meeting?');">
                                        <input type="hidden" name="id" value="<?php echo $meeting['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- Toast Container -->
        <div id="toast-container" class="toast-container"></div>

        <!-- <div class="marquee">
            <span>New Meeting Added: <?php echo htmlspecialchars($new_meeting['title']); ?> - Date: <?php echo date('F j, Y', strtotime($new_meeting['date'])); ?></span>
        </div> -->
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Form validation
        function validateForm() {
            const form = document.getElementById('new-meeting-form');
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            // Reset all validation messages
            document.querySelectorAll('.validation-message').forEach(msg => {
                msg.style.display = 'none';
            });
            document.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });

            // Check each required field
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    const validationMsg = document.getElementById(`${field.id}-validation`);
                    if (validationMsg) {
                        validationMsg.style.display = 'block';
                        field.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });

            return isValid;
        }

        // Handle new meeting form submission
        document.getElementById('save-meeting').addEventListener('click', function() {
            if (!validateForm()) {
                showToast('Please fill in all required fields', 'error');
                return;
            }

            const form = document.getElementById('new-meeting-form');
            const formData = new FormData(form);

            fetch('save_meeting.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Meeting minutes saved successfully', 'success');
                    $('#newMeetingModal').modal('hide');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(data.error || 'Error saving meeting minutes', 'error');
                }
            })
            .catch(error => {
                showToast('Error saving meeting minutes', 'error');
            });
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                ${message}
            `;
            toastContainer.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>
</html>
