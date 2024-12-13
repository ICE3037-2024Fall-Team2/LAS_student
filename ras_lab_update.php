<?php
session_start();

// Database connection
require 'db_connect.php';
require 's3_update.php'; 

$bucketName = "lrsys-bucket"; 
$imgPathPrefix = "lab_img/";

// Collect input fields
$lab_id = $_POST['add_id'] ?? null;
$lab_name = $_POST['lab_name'] ?? null;
$address = $_POST['address'] ?? null;
$capacity = $_POST['capacity'] ?? null;
$photo = $_FILES['photo']['tmp_name'] ?? null;

// Check if lab_id is provided
if (empty($lab_id)) {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'Lab ID is required.'
    ];
    header("Location: ras_lab_list.php");
    exit();
}

// Check if the lab already exists
$query = "SELECT * FROM labs WHERE lab_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$lab_exists = $result->num_rows > 0;
$stmt->close();

if ($lab_exists) {
    // Update existing lab
    $update_fields = [];
    $update_params = [];
    $param_types = "";

    if (!empty($lab_name)) {
        $update_fields[] = "lab_name = ?";
        $update_params[] = $lab_name;
        $param_types .= "s";
    }

    if (!empty($address)) {
        $update_fields[] = "address = ?";
        $update_params[] = $address;
        $param_types .= "s";
    }

    if (!empty($capacity)) {
        $update_fields[] = "capacity = ?";
        $update_params[] = $capacity;
        $param_types .= "i";
    }

    if (!empty($photo) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = $_SESSION['id'] . "_" . time() . "." . $file_extension; 
        $s3Key = $imgPathPrefix . $photo_name;

        try {
            $uploadedKey = uploadToS3($photo, $bucketName, $s3Key,'lab');
            $update_fields[] = "img_path = ?";
            $update_params[] = $uploadedKey;
            $param_types .= "s";
        } catch (Exception $e) {
            $_SESSION['toastr'] = [
                'type' => 'error',
                'message' => 'Photo upload failed: ' . htmlspecialchars($e->getMessage())
            ];
            header("Location: ras_lab_list.php");
            exit();
        }
    }

    if (!empty($update_fields)) {
        $update_query = "UPDATE labs SET " . implode(", ", $update_fields) . " WHERE lab_id = ?";
        $param_types .= "s";
        $update_params[] = $lab_id;

        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($param_types, ...$update_params);
        $stmt->execute();
        $stmt->close();

        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Lab updated successfully.'
        ];
    } else {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'No fields provided for update.'
        ];
    }
} else {
    // Create new lab
    if (!empty($lab_name) && !empty($address) && !empty($capacity)) {
        $photo_path = null;

        if (!empty($photo) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_name = $_SESSION['id'] . "_" . time() . "." . $file_extension; 
            $s3Key = $imgPathPrefix . $photo_name;

            try {
                $photo_path = uploadToS3($photo, $bucketName, $s3Key);
            } catch (Exception $e) {
                $_SESSION['toastr'] = [
                    'type' => 'error',
                    'message' => 'Photo upload failed: ' . htmlspecialchars($e->getMessage())
                ];
                header("Location: ras_lab_list.php");
                exit();
            }
        }

        $insert_query = "INSERT INTO labs (lab_id, lab_name, address, img_path, capacity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssi", $lab_id, $lab_name, $address, $photo_path, $capacity);
        $stmt->execute();
        $stmt->close();

        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Lab added successfully.'
        ];
    } else {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Please fill in all required fields to add a new lab (except photo).'
        ];
    }
}

$conn->close();
header("Location: ras_lab_list.php");
exit();
