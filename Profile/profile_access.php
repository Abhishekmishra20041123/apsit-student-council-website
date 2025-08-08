<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

// Check if profile is verified
if (!isset($_SESSION['profile_verified']) || $_SESSION['profile_verified'] !== true) {
    header("Location:verify.html");
    exit;
}

// If we get here, the user is logged in and verified
// Redirect to the profile page
header("Location: profile.html");
exit;
?>