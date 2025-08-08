<?php
// Start session
session_start();

// Include database connection
require_once '../db_connect.php';

// Set content type to JSON for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['error' => 'Please enter both email and password.']);
            exit;
        } else {
            header("Location: verify.html?error=" . urlencode("Please enter both email and password."));
            exit;
        }
    }

    // Check if user exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if this is the same user as in the session
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) {
                // Set a verification flag in the session
                $_SESSION['profile_verified'] = true;
                
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    header("Location:profile.html");
                    exit;
                }
            } else {
                // Different user or not logged in
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode(['error' => 'Authentication failed. Please log in again.']);
                    exit;
                } else {
                    header("Location: ../login.html");
                    exit;
                }
            }
        } else {
            // Invalid password
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['error' => 'Invalid password.']);
                exit;
            } else {
                header("Location: verify.html?error=" . urlencode("Invalid password."));
                exit;
            }
        }
    } else {
        // No account found
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['error' => 'No account found with this email.']);
            exit;
        } else {
            header("Location: verify.html?error=" . urlencode("No account found with this email."));
            exit;
        }
    }

    $stmt->close();
} else {
    // Not a POST request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['error' => 'Invalid request method.']);
        exit;
    } else {
        header("Location: verify.html");
        exit;
    }
}

$conn->close();
?>