<?php
$file = "Academic Calendar Second Half 2024.pdf";

// Check if file exists
if (!file_exists($file)) {
    http_response_code(404);
    die("File not found: " . $file);
}

// Get file info
$fileSize = filesize($file);
$fileName = basename($file);

// Set proper headers for PDF display
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"" . $fileName . "\"");
header("Content-Length: " . $fileSize);
header("Cache-Control: public, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Output the file
readfile($file);
exit;
?>
