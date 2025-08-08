<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test email configuration
$to = "abhishekmishra24112004@gmail.com"; // Replace with your test email
$subject = "Test Email from APSIT Help Desk";
$message = "
<html>
<head>
    <title>Test Email</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { padding: 20px; }
        .header { background: #007bff; color: white; padding: 10px; }
        .content { padding: 20px 0; }
        .footer { color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Test Email</h2>
        </div>
        <div class='content'>
            <p>This is a test email from the APSIT Help Desk system.</p>
            <p>If you're receiving this email, the email functionality is working correctly.</p>
        </div>
        <div class='footer'>
            <p>This is an automated test message.</p>
        </div>
    </div>
</body>
</html>
";

// Email headers
$headers = "From: APSIT Help Desk <abhishekmishra24112004@gmail.com>\r\n";
$headers .= "Reply-To: abhishekmishra24112004@gmail.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// SMTP Configuration
ini_set("SMTP", "smtp.gmail.com");
ini_set("smtp_port", "587");
ini_set("sendmail_from", "abhishekmishra24112004@gmail.com");

// Send email
$mail_success = mail($to, $subject, $message, $headers);

if ($mail_success) {
    echo "Test email sent successfully! Please check your inbox.";
} else {
    echo "Failed to send test email. Error: " . error_get_last()['message'];
}
?> 