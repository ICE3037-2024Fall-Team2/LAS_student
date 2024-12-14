<?php
session_start();
require 'db_connect.php';
require 's3_update.php'; // Include the S3 upload script

$bucketName = "lrsys-bucket";
$imgPathPrefix = "user_photo/"; 

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

    if (isset($_FILES['photo-upload'])) {
        error_log("File upload detected");
        if ($_FILES['photo-upload']['error'] === UPLOAD_ERR_OK) {
            error_log("File upload is valid");
        } else {
            error_log("File upload error code: " . $_FILES['photo-upload']['error']);
        }
    } else {
        error_log("No file upload detected");
    }
    

    if (isset($_FILES['photo-upload']) && $_FILES['photo-upload']['error'] === UPLOAD_ERR_OK) {
        $photo_tmp_name = $_FILES['photo-upload']['tmp_name'];
        $file_extension = pathinfo($_FILES['photo-upload']['name'], PATHINFO_EXTENSION);
    
        // Use the current timestamp and user ID to generate a unique name
        //$photo_name = $_SESSION['id'] . "_" . time() . "." . $file_extension;
        $photo_name = $_SESSION['id'] . "_" . time() . "." . $file_extension; 
        $s3Key = $imgPathPrefix . $photo_name;
        error_log("$photo_tmp_name, $bucketName, $s3Key");
    
        try {

            // Upload the file to S3
            $uploadedKey = uploadToS3($photo_tmp_name, $bucketName, $s3Key,'user');
    
            // Update the photo path with the S3 key
            $photo_path = $uploadedKey;
        } catch (Exception $e) {
            echo "<script>
                    alert('Photo upload failed: " . $e->getMessage() . "');
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
        error_log("$photo_path");
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
