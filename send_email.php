<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Your Gmail address and App Password
    $gmail_username = "abhishekmishra24112004@gmail.com"; // Replace with your Gmail
    $gmail_password = "ofqv fjnu lhtd qclt"; // Replace with your Gmail App Password
    
    // Email headers
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Email body
    $email_body = "
    <html>
    <head>
        <title>New Contact Form Submission</title>
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
                <h2>New Contact Form Submission</h2>
            </div>
            <div class='content'>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            </div>
            <div class='footer'>
                <p>This email was sent from the APSIT Student Council contact form.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // SMTP Configuration
    ini_set("SMTP", "smtp.gmail.com");
    ini_set("smtp_port", "587");
    ini_set("sendmail_from", $gmail_username);

    // Attempt to send email
    $mail_success = mail($gmail_username, "Contact Form: " . $subject, $email_body, $headers);

    // Send response back to ajax call
    $response = array(
        'success' => $mail_success,
        'message' => $mail_success ? 'Email sent successfully!' : 'Failed to send email.'
    );
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 