<?php
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
