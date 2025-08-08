<?php
include '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Minutes</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../navbar.css">
    <style>
        body {
            padding-top: 80px !important;
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            height: 80px;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-left: -2rem;
        }
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }
        .navbar-brand img {
            height: 45px;
            width: 45px;
        }
        .navbar-text {
            font-size: 1.1rem;
            margin: 0;
            padding: 0;
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            margin-left: auto;
        }
        .navbar-nav .nav-item {
            margin-left: -0.5rem;
        }
        .navbar-nav .btn {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
        .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
        }
        .dropdown-item {
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
        }
        .dropdown-divider {
            margin: 0.5rem 0;
        }
        @media (max-width: 1200px) {
            .navbar-text {
                display: none;
            }
        }
        @media (max-width: 992px) {
            .navbar-collapse {
                background-color: #343a40;
                padding: 1rem;
                border-radius: 0.25rem;
                margin-top: 0.5rem;
            }
            .navbar-nav {
                flex-direction: column;
                width: 100%;
            }
            .navbar-nav .nav-item {
                width: 100%;
                margin: 0.25rem 0;
            }
            .navbar-nav .btn {
                width: 100%;
                text-align: left;
                margin: 0.25rem 0;
            }
            .dropdown-menu {
                position: static !important;
                transform: none !important;
                width: 100%;
                margin-top: 0.25rem;
                background-color: #2c3034;
            }
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        header h1 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .minutes-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        .minutes-item {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
        }
        .minutes-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #dee2e6;
        }
        .minutes-title {
            color: #2c3e50;
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }
        .minutes-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            color: #6c757d;
        }
        .minutes-meta i {
            color: #3498db;
            margin-right: 5px;
        }
        .minutes-content {
            color: #495057;
            line-height: 1.8;
            font-size: 1.05em;
        }
        .minutes-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .minutes-section h4 {
            color: #2c3e50;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .minutes-section h4 i {
            margin-right: 10px;
            color: #3498db;
        }
        .minutes-section-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .search-container {
            display: flex;
            margin-bottom: 30px;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .search-container input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px 0 0 8px;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .search-container input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .search-container button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .search-container button:hover {
            background: #2980b9;
        }
        .sort-container {
            margin-bottom: 30px;
            max-width: 300px;
            margin: 0 auto 30px;
        }
        .sort-select {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1em;
            background-color: white;
            transition: all 0.3s ease;
        }
        .sort-select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .filter-controls {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .filter-controls > div {
            flex: 1;
            min-width: 250px;
        }
        .no-results {
            text-align: center;
            padding: 50px;
            color: #6c757d;
            font-size: 1.2em;
        }
        .no-results i {
            font-size: 3em;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .minutes-item {
                padding: 20px;
            }
            .minutes-title {
                font-size: 1.5em;
            }
            .minutes-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        /* Toast notifications */
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
    </style>
</head>
<body>
    <div class="sticky-top">
        <nav class="navbar navbar-expand-xl bg-dark navbar-dark fixed-top navbar-custom">
            <div class="container">
                <a class="navbar-brand" href="../apsithomepage.php">
                    <img src="../Untitled design.png" alt="APSIT_logo" style="height:45px; width:45px;">
                </a>
                
                <span class="col-sm-6 navbar-text text-white">A. P. Shah Institute of Technology, Mumbai</span>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsenavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
    
                <div class="collapse navbar-collapse" id="collapsenavbar">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown"> Student Council</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="../Meet the president/president.html">Message from the President</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../Meet the representatives/representatives.html">Meet the Representatives</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="meet.php">Minutes of Meetings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../Letter/letters_to_admin.html">Letters to Administration</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown"> Announcements</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="../Announcements/announcements.php">Announcements</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown"> Events</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="../Events/events.html">Events</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../Calendar/calendar.html">Calendar</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown"> Student's Life</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="../Student life/studentlife.html">APSIT Life</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../Achivements/achivements.html">Achivements</a>
                                    <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../resource/resource.php">Resources</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../faq.html">FAQs</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown"> Login</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="../Admin/admin_login.php">Admin</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="../Profile/verify.html">Member</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="container">
        <header>
            <h1>Meeting Minutes</h1>
        </header>

        <main>
            <div class="controls">
                <div class="filter-controls">
                    <div class="search-container">
                        <input type="text" id="search-input" placeholder="Search minutes...">
                        <button id="search-btn" class="btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="sort-container">
                        <select id="sort-select" class="sort-select">
                            <option value="date-desc">Newest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="title-asc">Title A-Z</option>
                            <option value="title-desc">Title Z-A</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="minutes-container" id="minutes-container">
            <?php
                // Default query
                $query = "SELECT * FROM meeting_minutes ORDER BY meeting_date DESC";
                
                // Get search term if provided
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                if (!empty($search)) {
                    $search = $conn->real_escape_string($search);
                    $query = "SELECT * FROM meeting_minutes 
                             WHERE title LIKE '%$search%' 
                             OR attendees LIKE '%$search%' 
                             OR agenda LIKE '%$search%' 
                             OR discussion LIKE '%$search%' 
                             OR action_items LIKE '%$search%' 
                             ORDER BY meeting_date DESC";
                }
                
                $result = $conn->query($query);
                
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="minutes-item">';
                        echo '<h2 class="minutes-title">' . htmlspecialchars($row['title']) . '</h2>';
                        echo '<div class="minutes-meta">';
                        echo '<span><i class="fas fa-calendar"></i> ' . date('F j, Y', strtotime($row['meeting_date'])) . '</span>';
                        if (!empty($row['meeting_time'])) {
                            echo '<span><i class="fas fa-clock"></i> ' . htmlspecialchars($row['meeting_time']) . '</span>';
                        }
                        echo '</div>';
                        
                        echo '<div class="minutes-section">';
                        echo '<h4><i class="fas fa-users"></i> Attendees</h4>';
                        echo '<div class="minutes-section-content">' . nl2br(htmlspecialchars($row['attendees'])) . '</div>';
                        echo '</div>';
                        
                        echo '<div class="minutes-section">';
                        echo '<h4><i class="fas fa-list"></i> Agenda</h4>';
                        echo '<div class="minutes-section-content">' . nl2br(htmlspecialchars($row['agenda'])) . '</div>';
                        echo '</div>';
                        
                        echo '<div class="minutes-section">';
                        echo '<h4><i class="fas fa-comments"></i> Discussion</h4>';
                        echo '<div class="minutes-section-content">' . nl2br(htmlspecialchars($row['discussion'])) . '</div>';
                        echo '</div>';
                        
                        if (!empty($row['action_items'])) {
                            echo '<div class="minutes-section">';
                            echo '<h4><i class="fas fa-tasks"></i> Action Items</h4>';
                            echo '<div class="minutes-section-content">' . nl2br(htmlspecialchars($row['action_items'])) . '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-results">';
                    echo '<i class="fas fa-file-alt"></i>';
                    echo '<p>No meeting minutes found</p>';
                    echo '</div>';
                }
            ?>
            </div>
        </main>

        <div id="toast-container" class="toast-container"></div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('search-input');
            const searchBtn = document.getElementById('search-btn');
            
            // Sort functionality
            const sortSelect = document.getElementById('sort-select');
            
            // Function to update the page with search and sort parameters
            function updatePage() {
                const searchTerm = searchInput.value.trim();
                const sortValue = sortSelect.value;
                
                // Build the URL with parameters
                let url = 'meet.php';
                const params = [];
                
                if (searchTerm) {
                    params.push(`search=${encodeURIComponent(searchTerm)}`);
                }
                
                if (sortValue) {
                    params.push(`sort=${encodeURIComponent(sortValue)}`);
                }
                
                if (params.length > 0) {
                    url += '?' + params.join('&');
                }
                
                // Navigate to the new URL
                window.location.href = url;
            }
            
            // Event listeners
            searchBtn.addEventListener('click', updatePage);
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    updatePage();
                }
            });
            
            sortSelect.addEventListener('change', updatePage);
            
            // Set initial values from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            const sortParam = urlParams.get('sort');
            
            if (searchParam) {
                searchInput.value = searchParam;
            }
            
            if (sortParam) {
                sortSelect.value = sortParam;
            }
            
            // Toast notification function
            window.showToast = function(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                let icon = 'info-circle';
                if (type === 'success') icon = 'check-circle';
                if (type === 'error') icon = 'exclamation-circle';
                
                toast.innerHTML = `
                    <i class="fas fa-${icon}"></i>
                    <span>${message}</span>
                `;
                
                document.getElementById('toast-container').appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
