<?php
// Set headers for PDF display
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="mentoring.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Read and output the PDF file
$pdfFile = __DIR__ . '/mentoring.pdf';
if (file_exists($pdfFile)) {
    readfile($pdfFile);
} else {
    // If file doesn't exist, show error message
    echo "The mentoring PDF file is not available at the moment.";
}
?> 