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
    $verified = false;


    $check_sql = "SELECT * FROM reservations WHERE user_id = ? AND date = ? AND time = ?";
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
        $reservation_id = date('YmdHi') . rand(100, 999); 

        $sql = "INSERT INTO reservations (reservation_id, lab_id, user_id, date, time, verified) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $reservation_id, $lab_id, $user_id, $selected_date, $selected_time, $verified);

        if ($stmt->execute()) {
            $_SESSION['toastr'] = array(
                'type' => 'success',
                'message' => 'Reservation successfully created.'
            );
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
