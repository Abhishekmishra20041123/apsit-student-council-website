<?php
// Function to send email notification
function sendEmailNotification($to, $subject, $message) {
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
    
    if (!$mail_success) {
        error_log("Failed to send email to: $to");
        error_log("Error: " . error_get_last()['message']);
    }
    
    return $mail_success;
}
?> 