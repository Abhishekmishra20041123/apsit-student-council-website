<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include database connection
include '../db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../Events/styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .event-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .event-table th, .event-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .event-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .event-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .view-btn {
            background-color: #17a2b8;
            color: white;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        
        .status-active {
            background-color: #28a745;
        }
        
        .status-upcoming {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-expired {
            background-color: #6c757d;
        }
        
        .back-btn {
            margin-bottom: 20px;
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Events</h1>
            <div>
                <button class="back-btn" onclick="location.href='admin_dashboard.php'">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
                <button class="add-event-btn" onclick="openEventForm()">
                    <i class="fas fa-plus"></i> Add New Event
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <table class="event-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Event Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                        <!-- Events will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Event Form Modal -->
    <div id="eventFormModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEventForm()">&times;</span>
            <h2>Add New Event</h2>
            <form id="eventForm" onsubmit="saveEvent(event)">
                <input type="text" id="event_name" name="event_name" placeholder="Event Name" required>
                <label>Upload Event Image:</label>
                <input type="file" id="event_image" name="event_image" accept="image/*" required>
                <label>Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
                <label>End Date:</label>
                <input type="date" id="end_date" name="end_date" required>
                <textarea id="event_description" name="event_description" placeholder="Event Description" required></textarea>
                <label>Upload PDF (Optional):</label>
                <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf">
                <button type="submit" class="save-btn">Save Event</button>
            </form>
        </div>
    </div>
    
    <!-- Details Modal -->
    <div class="modal" id="eventModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalEventName"></h2>
            <p><b>Start Date:</b> <span id="modalStartDate"></span> | <b>End Date:</b> <span id="modalEndDate"></span></p>
            <p id="modalDescription"></p>
            <p id="modalPdf"></p>
            <button id="deleteEventBtn" class="btn btn-danger">Delete Event</button>
        </div>
    </div>
    
    <script>
        // Admin-specific JavaScript for managing events
        let currentEventId = null;
        
        function fetchEvents() {
            fetch("../Events/fetch_events.php")
                .then(response => response.json())
                .then(data => {
                    if (!Array.isArray(data)) {
                        console.error("Invalid response:", data);
                        return;
                    }

                    let tableBody = document.getElementById("eventsTableBody");
                    tableBody.innerHTML = ""; // Clear previous events
                    
                    const today = new Date().toISOString().split('T')[0];

                    data.forEach(event => {
                        let row = document.createElement("tr");
                        
                        // Determine event status
                        let status = 'active';
                        let statusClass = 'status-active';
                        
                        if (event.start_date > today) {
                            status = 'upcoming';
                            statusClass = 'status-upcoming';
                        } else if (event.end_date < today) {
                            status = 'expired';
                            statusClass = 'status-expired';
                        }
                        
                        row.innerHTML = `
                            <td>${event.id}</td>
                            <td><img src="${event.event_image}" alt="${event.event_title}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>${event.event_title}</td>
                            <td>${event.start_date || "N/A"}</td>
                            <td>${event.end_date || "N/A"}</td>
                            <td><span class="status-badge ${statusClass}">${status.toUpperCase()}</span></td>
                            <td class="action-buttons">
                                <button class="view-btn" onclick="viewEventDetails(${event.id}, '${event.event_title}', '${event.start_date}', '${event.end_date}', '${event.event_description}', '${event.event_pdf}')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="delete-btn" onclick="confirmDeleteEvent(${event.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                })
                .catch(error => console.error("Error fetching events:", error));
        }
        
        function viewEventDetails(id, title, startDate, endDate, description, pdfUrl) {
    document.getElementById("modalEventName").innerText = title;
    document.getElementById("modalStartDate").innerText = startDate;
    document.getElementById("modalEndDate").innerText = endDate;
    document.getElementById("modalDescription").innerText = description;

    let pdfElement = document.getElementById("modalPdf");

    if (pdfUrl && pdfUrl !== "null" && pdfUrl !== "") {
        pdfElement.innerHTML = `<a href="${pdfUrl}" target="_blank">View PDF</a>`;
    } else {
        pdfElement.innerHTML = "No PDF uploaded.";
    }

    document.getElementById("eventModal").style.display = "block";
}

        function confirmDeleteEvent(id) {
            if (confirm("Are you sure you want to delete this event? This action cannot be undone.")) {
                deleteEvent(id);
            }
        }
        
        function deleteEvent(id) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch("../Events/delete_event.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === "success") {
                    alert("Event deleted successfully!");
                    closeModal();
                    fetchEvents(); // Refresh the events list
                } else {
                    alert("Error deleting event: " + result.message);
                }
            })
            .catch(error => console.error("Error deleting event:", error));
        }
        document.getElementById("start_date").addEventListener("change", function () {
    let startDate = this.value;
    document.getElementById("end_date").setAttribute("min", startDate);
});

        function openEventForm() {
    document.getElementById("eventFormModal").style.display = "block";

    // Get today's date in YYYY-MM-DD format
    let today = new Date().toISOString().split('T')[0];

    // Set min date for start and end date fields
    document.getElementById("start_date").setAttribute("min", today);
    document.getElementById("end_date").setAttribute("min", today);

    // Clear form fields
    document.getElementById("event_name").value = "";
    document.getElementById("event_image").value = "";
    document.getElementById("start_date").value = "";
    document.getElementById("end_date").value = "";
    document.getElementById("event_description").value = "";
    document.getElementById("pdf_file").value = "";
}

        function closeEventForm() {
            document.getElementById("eventFormModal").style.display = "none";
        }
        
        function closeModal() {
            document.getElementById("eventModal").style.display = "none";
            currentEventId = null;
        }
        
        function saveEvent(event) {
            event.preventDefault();
            
            let formData = new FormData(document.getElementById("eventForm"));
            
            fetch("../Events/save_event.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === "success") {
                    alert("Event saved successfully!");
                    closeEventForm();
                    fetchEvents(); // Refresh the events list
                } else {
                    alert("Error saving event: " + result.message);
                }
            })
            .catch(error => console.error("Error saving event:", error));
        }
        
        // Initialize the page
        document.addEventListener("DOMContentLoaded", function() {
            fetchEvents();
            
            // Set up event listeners
            document.getElementById("deleteEventBtn").addEventListener("click", function() {
                if (currentEventId) {
                    confirmDeleteEvent(currentEventId);
                }
            });
        });
    </script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>