<?php
session_start();

require 'db_connect.php'; 

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current_password = $_POST['current-password'];
    $new_password = $_POST['new-password'];

    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    $stmt->close();
   
    if (password_verify($current_password, $hashed_password)) {
        if (!password_verify($new_password, $hashed_password)) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_hashed_password, $id);
            if ($update_stmt->execute()) {
                $_SESSION['toastr'] = array(
                    'type' => 'success',
                    'message' => 'Password updated successfully.'
                );
            } else {
                $_SESSION['toastr'] = array(
                    'type' => 'error',
                    'message' => 'Error updating password. Please try again.'
                );
            }
            $update_stmt->close();
        } else {
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => 'New password cannot be the same as the current password.'
            );
        }
    } else {
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'Current password is incorrect.'
        );
    }

    header('Location: profile.php');
    exit;
}

$conn->close();
?>
