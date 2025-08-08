<?php
// Start session
session_start();

// Include database connection
include('../config.php');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info if logged in
$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? $_SESSION['user_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Resources - APSIT</title>
    <link rel="stylesheet" href="style.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Custom Navbar -->
    <header class="custom-navbar">
        <div class="container navbar-container">
            <div class="navbar-brand">
                <img src="../Untitled design.png" alt="APSIT Logo" class="logo-img">
                <span class="institute-name">A. P. Shah Institute of Technology</span>
            </div>
            
            <button class="navbar-toggle" id="navbar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav class="navbar-menu" id="navbar-menu">
                <div class="nav-item">
                    <button class="nav-btn">Student Council</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="../Meet the president/president.html">Message from the President</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Meet the representatives/representatives.html">Meet the Representatives</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Meeting/meet.php">Minutes of Meetings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Letter/letters_to_admin.html">Letters to Administration</a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <button class="nav-btn">Announcements</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="../Announcements/announcements.php">Announcements</a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <button class="nav-btn">Events</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="../Events/events.html">Events</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Calendar/calendar.html">Calendar</a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <button class="nav-btn">Student's Life</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="../Student life/studentlife.html">APSIT Life</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Achivements/achivements.html">Achievements</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../resource/resource.php">Resources</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../faq.html">FAQs</a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <button class="nav-btn">Login</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="../Admin/admin_login.php">Admin</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Profile/verify.html">Member</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <header class="page-header">
            <div class="container page-header-content">
                <h1>College Resources</h1>
                <p class="lead">Your gateway to academic excellence and professional growth</p>
            </div>
        </header>

        <!-- Section Navigation -->
        <nav class="section-nav">
            <div class="container">
                <div class="section-nav-container">
                    <a href="#guidance" class="section-nav-item active">
                        <i class="fas fa-compass"></i> Using Guidance
                    </a>
                    <a href="#study-materials" class="section-nav-item">
                        <i class="fas fa-book"></i> Study Materials
                    </a>
                    <a href="#career" class="section-nav-item">
                        <i class="fas fa-graduation-cap"></i> Career Resources
                    </a>
                    <a href="#support" class="section-nav-item">
                        <i class="fas fa-life-ring"></i> Academic Support
                    </a>
                    <a href="#tools" class="section-nav-item">
                        <i class="fas fa-tools"></i> Tools
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- Using Guidance Section -->
            <section id="guidance" class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-compass"></i>
                    </div>
                    <h2>Using Guidance</h2>
                </div>
                <div class="section-content">
                    <p>Learn how to use college facilities effectively. Check out our detailed <a href="#" id="tutorials-link">tutorials</a> or reach out for assistance:</p>
                    <button class="btn primary-btn" id="help-desk-btn">
                        <i class="fas fa-headset"></i> Contact Help Desk
                    </button>
                    <div id="help-desk-form" class="form-container" style="display: none;">
                        <h3>Contact Help Desk</h3>
                        <form id="contact-form">
                            <div class="form-group">
                                <label for="contact-name">Name</label>
                                <input type="text" id="contact-name" required>
                            </div>
                            <div class="form-group">
                                <label for="contact-email">Email</label>
                                <input type="email" id="contact-email" required>
                            </div>
                            <div class="form-group">
                                <label for="contact-issue">Issue</label>
                                <textarea id="contact-issue" rows="4" required></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn primary-btn">
                                    <i class="fas fa-paper-plane"></i> Submit
                                </button>
                                <button type="button" class="btn secondary-btn" id="cancel-help-desk">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Study Materials Section -->
            <section id="study-materials" class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h2>Study Materials</h2>
                </div>
                <div class="section-content">
                    <p>Access and share study materials with your peers. Upload your notes, presentations, or download resources shared by others.</p>
                    
                    <?php if($loggedIn): ?>
                    <div class="upload-container">
                        <h3><i class="fas fa-cloud-upload-alt"></i> Upload Study Material</h3>
                        <form action="upload_material.php" method="post" enctype="multipart/form-data" id="upload-form">
                            <div class="form-group">
                                <label for="material-title">Title</label>
                                <input type="text" id="material-title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="material-subject">Subject</label>
                                <select id="material-subject" name="subject">
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Physics">Physics</option>
                                    <option value="Chemistry">Chemistry</option>
                                    <option value="Biology">Biology</option>
                                    <option value="Business">Business</option>
                                    <option value="Arts">Arts</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="material-description">Description</label>
                                <textarea id="material-description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="material-file">File (PDF, DOC, PPT, ZIP)</label>
                                <div class="file-input-wrapper">
                                    <input type="file" id="material-file" name="file" required>
                                    <span class="file-input-label">Choose a file</span>
                                </div>
                            </div>
                            <button type="submit" class="btn primary-btn">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Material
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="login-prompt">
                        <div class="card">
                            <div class="card-body">
                                <h4><i class="fas fa-lock"></i> Login Required</h4>
                                <p>Please <a href="../login.html">login</a> to upload study materials.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="materials-container">
                        <h3><i class="fas fa-book-open"></i> Available Study Materials</h3>
                        <div class="filter-container">
                            <label for="filter-subject">Filter by Subject:</label>
                            <select id="filter-subject">
                                <option value="All">All Subjects</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Business">Business</option>
                                <option value="Arts">Arts</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div id="materials-list" class="materials-grid">
                            <?php include('get_materials.php'); ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Career Resources Section -->
            <section id="career" class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Career and Academic Resources</h2>
                </div>
                <div class="section-content">
                    <div class="resource-links">
                        <a href="#" id="internships-link" class="resource-link">
                            <i class="fas fa-briefcase"></i>
                            <span>Internship Opportunities</span>
                            <p>Find internships that match your skills and career goals</p>
                        </a>
                        <a href="#" id="scholarships-link" class="resource-link">
                            <i class="fas fa-award"></i>
                            <span>Scholarships</span>
                            <p>Discover scholarships to help fund your education</p>
                        </a>
                        <a href="#workshops-section" id="workshops-link" class="resource-link">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Workshops</span>
                            <p>Enhance your skills with specialized workshops</p>
                        </a>
                    </div>

                    <div class="workshops-section">
                        <h3><i class="fas fa-calendar-alt"></i> Upcoming Workshops</h3>
                        <div id="workshops-list" class="workshops-grid">
                            <?php include('get_workshops.php'); ?>
                        </div>
                    </div>

                    <div id="workshop-registration" class="modal" style="display: none;">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h3><i class="fas fa-clipboard-list"></i> Register for <span id="workshop-title"></span></h3>
                            <form id="workshop-form">
                                <div class="form-group">
                                    <label for="workshop-name">Full Name</label>
                                    <input type="text" id="workshop-name" required>
                                </div>
                                <div class="form-group">
                                    <label for="workshop-email">Email</label>
                                    <input type="email" id="workshop-email" required>
                                </div>
                                <div class="form-group">
                                    <label for="workshop-id">Student ID</label>
                                    <input type="text" id="workshop-id" required>
                                </div>
                                <button type="submit" class="btn primary-btn">
                                    <i class="fas fa-check-circle"></i> Confirm Registration
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Academic Support Section -->
            <section id="support" class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-life-ring"></i>
                    </div>
                    <h2>Academic Support</h2>
                </div>
                <div class="section-content">
                    <p>Get academic guidance through our <a href="#" id="mentorship-link">mentorship programs</a> or find <a href="#" id="subject-resources-link">subject-specific resources</a>.</p>
                    
                    <div class="tabs">
                        <div class="tab-header">
                            <div class="tab-btn active" data-tab="mentorship">
                                <i class="fas fa-user-graduate"></i> Mentorship
                            </div>
                            <div class="tab-btn" data-tab="tutoring">
                                <i class="fas fa-chalkboard-teacher"></i> Tutoring
                            </div>
                            <div class="tab-btn" data-tab="study-groups">
                                <i class="fas fa-users"></i> Study Groups
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="mentorship-tab">
                                <h3><i class="fas fa-user-graduate"></i> Mentorship Programs</h3>
                                <p>Connect with experienced mentors in your field of study who can provide guidance, share their experiences, and help you navigate your academic and career path.</p>
                                <div class="resource-benefits">
                                    <h4>Benefits of Mentorship</h4>
                                    <ul>
                                        <li>Personalized guidance for your academic journey</li>
                                        <li>Insights into industry expectations and trends</li>
                                        <li>Networking opportunities with professionals</li>
                                        <li>Support for academic and career decisions</li>
                                    </ul>
                                </div>
                                <button class="btn primary-btn" id="find-mentor-btn">
                                    <i class="fas fa-search"></i> Find a Mentor
                                </button>
                            </div>
                            <div class="tab-pane" id="tutoring-tab">
                                <h3><i class="fas fa-chalkboard-teacher"></i> Tutoring Services</h3>
                                <p>Get one-on-one help with difficult subjects from qualified tutors. Our tutoring services are designed to help you master challenging concepts and improve your academic performance.</p>
                                <div class="resource-benefits">
                                    <h4>Available Tutoring Subjects</h4>
                                    <ul>
                                        <li>Mathematics (Calculus, Linear Algebra, Statistics)</li>
                                        <li>Computer Science (Programming, Data Structures)</li>
                                        <li>Physics (Mechanics, Electromagnetism)</li>
                                        <li>Chemistry (Organic, Inorganic)</li>
                                    </ul>
                                </div>
                                <button class="btn primary-btn" id="book-tutor-btn">
                                    <i class="fas fa-calendar-plus"></i> Book a Tutor
                                </button>
                            </div>
                            <div class="tab-pane" id="study-groups-tab">
                                <h3><i class="fas fa-users"></i> Study Groups</h3>
                                <p>Join or create study groups for collaborative learning. Study groups provide a supportive environment where you can discuss concepts, solve problems together, and learn from your peers.</p>
                                <div class="resource-benefits">
                                    <h4>Active Study Groups</h4>
                                    <ul>
                                        <li>Data Structures & Algorithms (Meets Tuesdays)</li>
                                        <li>Calculus Study Group (Meets Wednesdays)</li>
                                        <li>Physics Problem Solving (Meets Thursdays)</li>
                                        <li>Web Development Workshop (Meets Fridays)</li>
                                    </ul>
                                </div>
                                <button class="btn primary-btn" id="join-group-btn">
                                    <i class="fas fa-user-plus"></i> Join a Group
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tools Section -->
            <section id="tools" class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h2>Tools and Utilities</h2>
                </div>
                <div class="section-content">
                    <div class="tools-grid">
                        <div class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h3>GPA Calculator</h3>
                            <p>Calculate your GPA with ease</p>
                            <button class="btn primary-btn tool-btn" data-tool="gpa">Try Now</button>
                        </div>
                        <div class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3>Timetable Generator</h3>
                            <p>Create and customize your schedule</p>
                            <button class="btn primary-btn tool-btn" data-tool="timetable">Create</button>
                        </div>
                        <div class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h3>Task Planner</h3>
                            <p>Organize your assignments and tasks</p>
                            <button class="btn primary-btn tool-btn" data-tool="planner">Start Planning</button>
                        </div>
                    </div>
                    <div id="tool-container" class="tool-container" style="display: none;">
                        <!-- Tool interfaces will be loaded here -->
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="../Untitled design.png" alt="APSIT Logo" class="footer-logo-img">
                    <h3>A. P. Shah Institute of Technology</h3>
                    <p>Empowering students through knowledge and innovation</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Mumbai, Maharashtra</p>
                    <p><i class="fas fa-phone"></i> +91 1234567890</p>
                    <p><i class="fas fa-envelope"></i> info@apsit.edu.in</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> A. P. Shah Institute of Technology. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Notification Container -->
    <div id="notification-container"></div>
    
    <!-- Scripts -->
    <script src="scripts.js?v=4"></script>
</body>
</html>