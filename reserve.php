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

    $reservation_id = date('YmdHi') . rand(100, 999); 
    // Insert the reservation into the database
    //$sql = "INSERT INTO reservations (lab_id, user_id, date, time) VALUES ('$lab_id', '$user_id', '$selected_date', '$selected_time')";
    $sql = "INSERT INTO reservations (reservation_id, lab_id, user_id, date, time, verified) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $reservation_id, $lab_id, $user_id, $selected_date, $selected_time, $verified);
    if ($stmt->execute()) {
        // Redirect to profile.php after successful reservation
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
