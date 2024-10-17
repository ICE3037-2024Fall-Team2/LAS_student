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

    // Insert the reservation into the database
    $sql = "INSERT INTO reservations (lab_id, user_id, date, time) VALUES ('$lab_id', '$user_id', '$selected_date', '$selected_time')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to profile.php after successful reservation
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
