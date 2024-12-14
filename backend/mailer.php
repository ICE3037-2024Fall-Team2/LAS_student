<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Import the autoload file for PHPMailer library
require __DIR__ . '/../vendor/autoload.php'; 


function sendEmail($recipientEmail, $emailSubject, $emailBody) {
    $mail = new PHPMailer(true);

    try {
        // Use SMTP for sending emails
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email sender information
        $mail->setFrom('', 'LabReservSys_SKKU');

        // Add email of recipient
        $mail->addAddress($recipientEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        // Try to send email
        if ($mail->send()) {
            error_log("Email sent successfully to $recipientEmail");
        } else {
            error_log("Failed to send email to $recipientEmail");
        }
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
    }
}
?>