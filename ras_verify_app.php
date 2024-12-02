<?php
require 'db_connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['reservation_id'])) {
    
        $reservation_id = $_POST['reservation_id'];

        $update_sql = "UPDATE reservations SET verified = 1 WHERE reservation_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $reservation_id);


        if ($stmt->execute()) {
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
