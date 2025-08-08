<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<div class="sidebar">
    <div class="logo-details">
        <img src="../Untitled design.png" alt="APSIT_logo" style="height:45px; width:45px;">
        <span class="logo_name">Admin Panel</span>
    </div>
    <ul class="nav-links">
        <li>
            <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span class="link_name">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="admin_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span class="link_name">Users</span>
            </a>
        </li>
        <li>
            <a href="admin_event.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_event.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span class="link_name">Events</span>
            </a>
        </li>
        <li>
            <a href="admin_workshop.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_workshop.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i>
                <span class="link_name">Workshops</span>
            </a>
        </li>
        <li>
            <a href="admin_meetings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_meetings.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span class="link_name">Meeting Minutes</span>
            </a>
        </li>
        <li>
            <a href="admin_help_desk.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_help_desk.php' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i>
                <span class="link_name">Help Desk</span>
            </a>
        </li>
        <li>
            <a href="admin_login.php">
                <i class="fas fa-sign-out-alt"></i>
                <span class="link_name">Logout</span>
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 260px;
    background: #343a40;
    z-index: 100;
    transition: all 0.5s ease;
    padding-top: 20px;
}

.sidebar .logo-details {
    height: 60px;
    width: 100%;
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.sidebar .logo-details .logo_name {
    font-size: 20px;
    color: #fff;
    font-weight: 600;
    margin-left: 15px;
}

.sidebar .nav-links {
    padding: 0;
    margin-top: 20px;
}

.sidebar .nav-links li {
    position: relative;
    list-style: none;
}

.sidebar .nav-links li a {
    height: 50px;
    display: flex;
    align-items: center;
    text-decoration: none;
    padding: 0 15px;
    transition: all 0.4s ease;
}

.sidebar .nav-links li a:hover,
.sidebar .nav-links li a.active {
    background: #4b545c;
}

.sidebar .nav-links li i {
    min-width: 30px;
    text-align: center;
    font-size: 18px;
    color: #fff;
}

.sidebar .nav-links li a .link_name {
    color: #fff;
    font-size: 15px;
    font-weight: 400;
}

.main-content {
    margin-left: 260px;
    padding: 20px;
}
</style> 