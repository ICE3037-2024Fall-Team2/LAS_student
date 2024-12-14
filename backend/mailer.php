<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 加载 PHPMailer 的自动加载文件
require __DIR__ . '/../vendor/autoload.php'; 


function sendEmail($recipientEmail, $emailSubject, $emailBody) {
    $mail = new PHPMailer(true);

    try {
        // SMTP 配置
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // 发件人
        $mail->setFrom('', 'LabReservSys_SKKU');

        // 收件人
        $mail->addAddress($recipientEmail);

        // 邮件内容
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        // 发送邮件
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