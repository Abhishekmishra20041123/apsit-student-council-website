<?php
session_start(); // Start session for user authentication (if required)

header("Content-Type: application/json"); // Ensure the response is JSON

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "apsit_database";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    if (empty($_POST["event_name"]) || empty($_POST["start_date"]) || empty($_POST["end_date"]) || empty($_POST["event_description"])) {
        echo json_encode(["status" => "error", "message" => "All required fields must be filled."]);
        exit();
    }

    $eventName = $_POST["event_name"];
    $startDate = $_POST["start_date"];
    $endDate = $_POST["end_date"];
    $eventDescription = $_POST["event_description"];

    // Check for duplicate events (same name and overlapping dates)
    $checkDuplicateSQL = "SELECT * FROM events WHERE 
        name = ? AND (
            (start_date BETWEEN ? AND ?) OR
            (end_date BETWEEN ? AND ?) OR
            (start_date <= ? AND end_date >= ?)
        )";
    
    $checkStmt = $conn->prepare($checkDuplicateSQL);
    $checkStmt->bind_param("sssssss", 
        $eventName, 
        $startDate, $endDate,  // Check if start date is between existing event
        $startDate, $endDate,  // Check if end date is between existing event
        $startDate, $endDate   // Check if dates encompass existing event
    );
    
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            "status" => "error", 
            "message" => "An event with the same name already exists during these dates. Please choose different dates or a different name."
        ]);
        exit();
    }
    $checkStmt->close();

    // Check if user is logged in (optional)
    $userID = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;

    // Define upload directories
    $uploadDir = __DIR__ . "/uploads/"; // Adjust as per your server structure
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create folder if it doesn't exist
    }

    // Handle image upload securely
    $eventImage = "";
    if (!empty($_FILES["event_image"]["name"])) {
        $imageFileType = strtolower(pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION));
        $allowedImageTypes = ["jpg", "jpeg", "png", "gif"];
        
        if (in_array($imageFileType, $allowedImageTypes)) {
            $eventImage = $uploadDir . uniqid("img_") . "." . $imageFileType;
            if (!move_uploaded_file($_FILES["event_image"]["tmp_name"], $eventImage)) {
                echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
                exit();
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid image format (Allowed: JPG, JPEG, PNG, GIF)."]);
            exit();
        }
    }

    // Handle PDF upload securely
    $pdfFile = "";
    if (!empty($_FILES["pdf_file"]["name"])) {
        $pdfFileType = strtolower(pathinfo($_FILES["pdf_file"]["name"], PATHINFO_EXTENSION));

        if ($pdfFileType === "pdf") {
            $pdfFile = $uploadDir . uniqid("pdf_") . "." . $pdfFileType;
            if (!move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $pdfFile)) {
                echo json_encode(["status" => "error", "message" => "Failed to upload PDF."]);
                exit();
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid file format (Only PDF allowed)."]);
            exit();
        }
    }

    // Insert event into database
    $sql = "INSERT INTO events (user_id, name, image, start_date, end_date, description, pdf) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare SQL statement: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("issssss", $userID, $eventName, $eventImage, $startDate, $endDate, $eventDescription, $pdfFile);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to execute SQL statement: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>