<?php
session_start();
require 'db_connect.php';

// Check if form is submitted with required fields
if (isset($_POST['admin_id']) && isset($_POST['password'])) {
    $admin_id = $_POST['admin_id'];
    $admin_name = $_POST['admin_name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_query = $conn->prepare("SELECT admin_id FROM admin WHERE admin_id = ?");
    $check_query->bind_param("s", $admin_id);
    $check_query->execute();
    $check_query->store_result();

    if ($check_query->num_rows > 0) {
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'Admin ID already exists!'
        );
    } else {

        $insert_query = $conn->prepare("INSERT INTO admin (admin_id, admin_name, password) VALUES (?, ?, ?)");
        $insert_query->bind_param("sss", $admin_id, $admin_name, $password);

        if ($insert_query->execute()) {
            $_SESSION['toastr'] = array(
                'type' => 'success',
                'message' => 'Admin registered successfully!'
            );
        } else {
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => 'Failed to register admin. Please try again.'
            );
        }

        $insert_query->close();
    }

    $check_query->close();
    $conn->close();
    header("Location: ras_change_admin_list.php");
    exit;
} else {
    $_SESSION['toastr'] = array(
        'type' => 'error',
        'message' => 'Please fill in all required fields.'
    );
    header("Location: ras_change_admin_list.php");
    exit;
}
?>
