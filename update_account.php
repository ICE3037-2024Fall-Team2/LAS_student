<?php
session_start();
require 'db_connect.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $id = $_SESSION['id'];
    $username = $_SESSION['username'];
    $photo_path = null;


    if (!preg_match('/^010-\d{4}-\d{4}$/', $phone)) {
        echo "<script>
                alert('Invalid phone number format. Please use 010-xxxx-xxxx.');
                window.history.back();
            </script>";
        return; 
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
                alert('Invalid email format.');
                window.history.back();
        </script>";
        return; 
    }


    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_tmp_name = $_FILES['photo']['tmp_name'];
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = $_SESSION['id'] . '.' . $file_extension;
        $photo_path = "user_photo/" . $photo_name;

        if (!move_uploaded_file($photo_tmp_name, $photo_path)) {
            echo "<script>
                    alert('Photo upload failed.');
                    window.history.back();
                </script>";
            $photo_path = null;
        }
    }


    $check_sql = "SELECT 1 FROM user_info WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {

        if ($photo_path) {
            $sql = "UPDATE user_info SET username = ?, phonenumber = ?, email = ?, photo_path = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $phone, $email, $photo_path, $id);

            $check_img_sql = "SELECT 1 FROM user_img WHERE id = ?";
            $check_img_stmt = $conn->prepare($check_img_sql);
            $check_img_stmt->bind_param("s", $id);
            $check_img_stmt->execute();
            $check_img_stmt->store_result();

            if ($check_img_stmt->num_rows > 0) {
                // If entry exists, update photo_path
                $update_img_sql = "UPDATE user_img SET photo_path = ? WHERE id = ?";
                $update_img_stmt = $conn->prepare($update_img_sql);
                $update_img_stmt->bind_param("ss", $photo_path, $id);
                $update_img_stmt->execute();
                $update_img_stmt->close();
            } else {
                // If entry does not exist, insert new record
                $insert_img_sql = "INSERT INTO user_img (id, photo_path) VALUES (?, ?)";
                $insert_img_stmt = $conn->prepare($insert_img_sql);
                $insert_img_stmt->bind_param("ss", $id, $photo_path);
                $insert_img_stmt->execute();
                $insert_img_stmt->close();
            }

            $check_img_stmt->close();

        } else {

            $sql = "UPDATE user_info SET username = ?, phonenumber = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $phone, $email, $id);
        }
    } else {

        $sql = "INSERT INTO user_info (id, username, phonenumber, email, photo_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $id, $username, $phone, $email, $photo_path);
    }


    if ($stmt->execute()) {
        header("Location: profile.php");
        exit;
    } else {
        echo "<script>alert('Error updating account info.');</script>";
    }

    $check_stmt->close();
    $stmt->close();
}

$conn->close();
?>
