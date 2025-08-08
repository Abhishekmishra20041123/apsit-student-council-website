<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $meeting_id = intval($_POST['id']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM meeting_minutes WHERE id = ?");
        $stmt->bind_param("i", $meeting_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Meeting deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Meeting not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete meeting']);
        }
        
        $stmt->close();
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Meeting Minutes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../Meeting/styles.css">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-title {
            font-size: 24px;
            color: #333;
        }
        
        #new-meeting-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        #new-meeting-btn:hover {
            background-color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }

        .modal-buttons {
            margin-top: 20px;
            text-align: right;
        }

        .modal-buttons button {
            margin-left: 10px;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Toast styles */
        #toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 4px;
            margin-bottom: 10px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            min-width: 200px;
        }

        .toast.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .toast.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .toast i {
            margin-right: 10px;
        }

        .toast.fade-out {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Meeting Minutes Management</h1>
            <button id="new-meeting-btn" class="admin-only">
                <i class="fas fa-plus"></i> New Meeting
            </button>
        </div>

        <!-- Search and Sort Controls -->
        <div class="controls">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search meetings...">
                <button id="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="sort-select">
                <option value="date-desc">Date (Newest First)</option>
                <option value="date-asc">Date (Oldest First)</option>
                <option value="title-asc">Title (A-Z)</option>
                <option value="title-desc">Title (Z-A)</option>
            </select>
            <div class="view-controls">
                <button id="grid-view-btn" class="active">
                    <i class="fas fa-th"></i>
                </button>
                <button id="list-view-btn">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Minutes List -->
        <div id="minutes-list">
            <div id="minutes-container" class="grid-view"></div>
        </div>

        <!-- Meeting Form -->
        <div id="meeting-form" class="hidden">
            <h2 id="form-title">Create Meeting Minutes</h2>
            <form id="minutes-form">
                <input type="hidden" id="meeting-id">
                
                <div class="form-group">
                    <label for="meeting-title">Title</label>
                    <input type="text" id="meeting-title" required>
                    <div id="meeting-title-validation" class="validation-message"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="meeting-date">Date</label>
                        <input type="date" id="meeting-date" required>
                        <div id="meeting-date-validation" class="validation-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="meeting-time">Time</label>
                        <input type="time" id="meeting-time">
                    </div>
                </div>

                <div class="form-group">
                    <label>Attendees</label>
                    <div class="attendees-container">
                        <input type="text" id="attendee-input" placeholder="Type name and press Enter">
                        <div id="attendees-tags"></div>
                    </div>
                    <input type="hidden" id="attendees" name="attendees">
                </div>

                <div class="form-group">
                    <label for="agenda">Agenda</label>
                    <textarea id="agenda" required></textarea>
                    <div id="agenda-validation" class="validation-message"></div>
                </div>

                <div class="form-group">
                    <label for="discussion">Discussion</label>
                    <textarea id="discussion" required></textarea>
                    <div id="discussion-validation" class="validation-message"></div>
                </div>

                <div class="form-group">
                    <label>Action Items</label>
                    <div id="action-items-container"></div>
                    <input type="hidden" id="action-items" name="action_items">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="button" id="cancel-btn" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Meeting Details -->
        <div id="meeting-details" class="hidden">
            <div class="details-header">
                <h2 id="detail-title"></h2>
                <div class="details-actions admin-only">
                    <button id="edit-btn" class="btn-secondary">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button id="delete-btn" class="btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
                <button id="back-btn" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </div>

            <div class="details-content">
                <div class="detail-section">
                    <div class="section-header" data-target="date-time-section">
                        <h3>Date & Time</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="date-time-section">
                        <p><strong>Date:</strong> <span id="detail-date"></span></p>
                        <p><strong>Time:</strong> <span id="detail-time"></span></p>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="section-header" data-target="attendees-section">
                        <h3>Attendees</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="attendees-section">
                        <div id="detail-attendees" class="attendees-list"></div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="section-header" data-target="agenda-section">
                        <h3>Agenda</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="agenda-section">
                        <p id="detail-agenda"></p>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="section-header" data-target="discussion-section">
                        <h3>Discussion</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="discussion-section">
                        <p id="detail-discussion"></p>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="section-header" data-target="action-items-section">
                        <h3>Action Items</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="action-items-section">
                        <div id="detail-action-items" class="action-items-list"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="confirmation-modal" class="modal">
            <div class="modal-content">
                <h3>Delete Meeting Minutes</h3>
                <p>Are you sure you want to delete these meeting minutes? This action cannot be undone.</p>
                <div class="modal-buttons">
                    <button id="confirm-delete" class="btn-danger">Delete</button>
                    <button id="cancel-delete" class="btn-secondary">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div id="toast-container"></div>
    </div>

    <script src="../Meeting/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize variables
        window.currentMeetingId = null; // Make it globally accessible
        const confirmationModal = document.getElementById('confirmation-modal');
        const confirmDelete = document.getElementById('confirm-delete');
        const cancelDelete = document.getElementById('cancel-delete');
        const deleteBtn = document.getElementById('delete-btn');
        const minutesContainer = document.getElementById('minutes-container');

        // Function to show toast message
        function showToast(message, type = 'info') {
            console.log('Showing toast:', message, type); // Debug log
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            document.getElementById('toast-container').appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Function to show delete confirmation modal
        function showDeleteModal(meetingId) {
            console.log('Showing delete modal for meeting:', meetingId); // Debug log
            window.currentMeetingId = meetingId;
            if (confirmationModal) {
                confirmationModal.style.display = 'block';
            }
        }

        // Function to hide delete confirmation modal
        function hideDeleteModal() {
            if (confirmationModal) {
                confirmationModal.style.display = 'none';
            }
        }

        // Handle delete button click
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Delete button clicked, currentMeetingId:', window.currentMeetingId); // Debug log
                if (!window.currentMeetingId) {
                    showToast('Please select a meeting first', 'error');
                    return;
                }
                showDeleteModal(window.currentMeetingId);
            });
        }

        // Add click event listener to the minutes container for delete buttons
        if (minutesContainer) {
            minutesContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const meetingId = e.target.closest('.minute-card').dataset.id;
                    console.log('Delete clicked for meeting:', meetingId); // Debug log
                    showDeleteModal(meetingId);
                }
            });
        }

        // Handle confirm delete
        if (confirmDelete) {
            confirmDelete.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Confirm delete clicked, currentMeetingId:', window.currentMeetingId); // Debug log
                if (!window.currentMeetingId) {
                    showToast('No meeting selected', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'delete'); // Add action parameter
                formData.append('id', window.currentMeetingId);

                fetch('admin_meetings.php', { // Use the current page as the endpoint
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Delete response received'); // Debug log
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Delete response data:', data); // Debug log
                    hideDeleteModal();
                    if (data.success) {
                        showToast('Meeting deleted successfully', 'success');
                        // Remove the deleted meeting from the list
                        const meetingElement = document.querySelector(`.minute-card[data-id="${window.currentMeetingId}"]`);
                        if (meetingElement) {
                            meetingElement.remove();
                        }
                        setTimeout(() => {
                            window.location.reload(); // Reload after showing success message
                        }, 1000);
                    } else {
                        showToast(data.error || 'Error deleting meeting', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideDeleteModal();
                    showToast('Error deleting meeting', 'error');
                });
            });
        }

        // Handle cancel delete
        if (cancelDelete) {
            cancelDelete.addEventListener('click', function(e) {
                e.preventDefault();
                hideDeleteModal();
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === confirmationModal) {
                hideDeleteModal();
            }
        });

        // Debug helper to show current meeting ID
        window.setInterval(() => {
            console.log('Current meeting ID:', window.currentMeetingId);
        }, 2000);
    });
    </script>
</body>
</html> 