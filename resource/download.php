<?php
$file = __DIR__ . '/uploads/materials/' . basename($_GET['id']);
if (file_exists($file)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    readfile($file);
    exit;
}
http_response_code(404);
die("File not found");
?>