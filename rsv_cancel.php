<?php
session_start();
require 'db_connect.php'; 

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reservation_id = $_POST['reservation_id'];

    // Prepare the SQL DELETE statement to delete the reservation by the primary key (reservation_id)
    $sql = "DELETE FROM reservations WHERE reservation_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $reservation_id);

        // Execute the prepared statement
        if ($stmt->execute()) {
            // Reservation successfully deleted
            $_SESSION['toastr'] = array(
                'type' => 'success',
                'message' => 'Reservation cancelled.'
            );
        } else {
            // If an error occurred during execution
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => 'Could not cancel the reservation.'
            );
        }

        // Close the statement
        $stmt->close();
    } else {
        // If an error occurred while preparing the SQL statement
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'Unexpected error.'
        );
    }

    // Redirect back to the profile or reservations page
    header("Location: profile.php");
    exit();
}

// Close the database connection
$conn->close();
?>
