<?php
session_start();
require 'db_connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['reservation_id'])) {
    
        $reservation_id = $_POST['reservation_id'];

        $update_sql = "UPDATE reservations SET verified = 1 WHERE reservation_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $reservation_id);


        if ($stmt->execute()) {

            $info_sql = "SELECT r.user_id, r.lab_id, r.date, r.time, u.email 
             FROM reservations r 
             JOIN user_info u ON r.user_id = u.id 
             WHERE r.reservation_id = ?";

            $info_stmt = $conn->prepare($info_sql);
            $info_stmt->bind_param("i", $reservation_id);
            $info_stmt->execute();
            $result = $info_stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Reservation ID not found.");
            }

 
            $row = $result->fetch_assoc();
            $user_email = $row['email'];
            $lab_id = $row['lab_id'];
            $reservation_time = $row['date'] . ' ' . $row['time'];

  
            $title = "[{$reservation_id}] Your Reservation For {$lab_id} Has Been Approved!";
            $body = "<p>Dear Student,</p><br>
                    <p>We are glad to inform you, your reservation has been approved.</p>
                    <hr>
                    <p><strong>Reservation ID:</strong> {$reservation_id}</p>
                    <p><strong>Lab ID:</strong> {$lab_id}</p>
                    <p><strong>Time:</strong> {$reservation_time}</p>
                    <br>
                    <p>You may enter the lab up to <strong>5 mins before</strong> or <strong>5 mins after</strong> the reserved time.</p>
                    <p><strong>Please avoid being late.</strong></p>
                    <hr>
                    <br>
                    <p>This email message was auto-generated. Please do not respond.</p>
                    <p>If you need additional information, please visit our website.</p>
                    <br>
                    <p>Sincerely,</p><br>
                    <p>Sungkyunkwan University</p>
                    <p>Lab Reservation System</p>
                    <p>Admin In Charge: {$_SESSION['id']}</p>
                    ";


            require_once 'mailer.php';
            try {
                sendEmail($user_email, $title, $body);
                error_log("Rejection email sent successfully to {$user_email}");
            } catch (Exception $e) {
                error_log("Failed to send rejection email: " . $e->getMessage());
            }

            $info_stmt->close();
            $stmt->close();
            $conn->close();


            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error: reservation_id is missing.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
