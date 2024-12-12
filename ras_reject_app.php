<?php
session_start();

require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['reservation_id'])) {
    
        $reservation_id = $_POST['reservation_id'];
        $rejected_message = isset($_POST['reason']) ? trim($_POST['reason']) : null;

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update `verified` field to 2 in `reservations` table
            $update_sql = "UPDATE reservations SET verified = 2 WHERE reservation_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $reservation_id);

            if (!$stmt->execute()) {
                throw new Exception("Error updating reservation: " . $conn->error);
            }

            // If a rejection message is provided, insert or update it in the `rejected_messages` table
            if (!empty($rejected_message)) {
                $insert_sql = "INSERT INTO rejected_messages (reservation_id, rejected_message) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE rejected_message = VALUES(rejected_message)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("is", $reservation_id, $rejected_message);

                if (!$insert_stmt->execute()) {
                    throw new Exception("Error inserting rejection message: " . $conn->error);
                }

                $insert_stmt->close();
            }

            // Commit transaction
            $conn->commit();
            
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

  
            $title = "[{$reservation_id}] Your Reservation For {$lab_id} Has Been Rejected";
            $body = "<p>Dear Student,</p><br>
                    <p>We are sorry to inform you, your reservation has been rejected.</p>
                    <hr>
                    <p><strong>Reservation ID:</strong> {$reservation_id}</p>
                    <p><strong>Lab ID:</strong> {$lab_id}</p>
                    <p><strong>Time:</strong> {$reservation_time}</p><br>
                    <p><strong>Reason for rejection:</strong></p>
                    <blockquote>{$rejected_message}</blockquote>
                    <br>
                    <p>This email message was auto-generated. Please do not respond.</p><p>If you need additional information, please visit our website.</p>
                    <hr><br>
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
            
            // Redirect back with success
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
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
