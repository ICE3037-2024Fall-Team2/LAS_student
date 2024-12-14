<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lab_id = $_POST['lab_id'];
    $selected_date = $_POST['selected_date'];
    $user_id = $_SESSION['id'];
    $selected_time = $_POST['selected_time']; 
    $verified = 0;


    $check_sql = "SELECT * FROM reservations WHERE user_id = ? AND date = ? AND time = ? AND verified != 3 ";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $user_id, $selected_date, $selected_time);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['toastr'] = array(
            'type' => 'warning',
            'message' => 'You have already reserved for this time.'
        );
        //header("Location: profile.php");
        //exit;
    } else {
        $check_cancel = $sql = "DELETE FROM reservations 
        WHERE user_id = ? 
          AND lab_id = ? 
          AND date = ? 
          AND time = ? 
          AND verified = 3";

        $delete_stmt = $conn->prepare($sql);
        $delete_stmt->bind_param("ssss", $user_id, $lab_id, $selected_date, $selected_time);

        $delete_stmt->execute();

        $delete_stmt->close();

        $reservation_id = date('YmdHi') . rand(100, 999); 

        $sql = "INSERT INTO reservations (reservation_id, lab_id, user_id, date, time, verified) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $reservation_id, $lab_id, $user_id, $selected_date, $selected_time, $verified);

        if ($stmt->execute()) {
            $_SESSION['toastr'] = array(
                'type' => 'success',
                'message' => 'Reservation successfully created.'
            );

            $admin_emails = [];
            $sql_admin = "SELECT email FROM admin";
            $result_admin = $conn->query($sql_admin);
            if ($result_admin->num_rows > 0) {
                while ($row = $result_admin->fetch_assoc()) {
                    if (!empty($row['email'])) 
                        $admin_emails[] = $row['email'];
                    
                }
            }

            $subject = "[{$reservation_id}] There Is A New Reservation";
            $body = "
                <p>Dear Admin,</p><br>
                <p>There is a new reservation from our system, here are the details:</p>
                <hr>
                <p><strong>Reservation ID:</strong> {$reservation_id}</p>
                <p><strong>Student ID:</strong> {$user_id}</p>
                <p><strong>Lab ID:</strong> {$lab_id}</p>
                <p><strong>Time:</strong> {$selected_date} {$selected_time}</p>
                <hr><br>
                <p>Please go to our website and check the reservation at your most available time.</p><br>
                <p>Sincerely,</p>
                <br>
                <p>Sungkyunkwan University</p>
                <p>Lab Reservation System</p>
            ";

            if (!empty($admin_emails)) {
                try {
                    require_once 'mailer.php'; 

                    foreach ($admin_emails as $admin_email) {
                        if (is_string($admin_email) && !empty($admin_email)) {
                            $email_sent = sendEmail($admin_email, $subject, $body); 
                            if ($email_sent) {
                                error_log("Email sent successfully to $admin_email");
                            } else {
                                error_log("Failed to send email to $admin_email");
                            }
                        } else {
                            error_log("Invalid email address: " . json_encode($admin_email));
                        }
                    }
                } catch (Exception $e) {
                    error_log("Mailer exception: " . $e->getMessage());
                }
            }
            
            
        } else {
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => 'Error: Failed to create reservation.'
            );
        }
        $stmt->close();
    }

    $check_stmt->close();

    //header("Location: reservation.php");
    header("Location: reservation.php?lab_id=" . urlencode($lab_id));
    exit;
}


$conn->close();
?>
