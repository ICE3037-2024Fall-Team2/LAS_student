<?php
session_start();

// Database connection
require 'db_connect.php';
require 's3_upload.php'; 

$bucketName = "lrsys-bucket"; 
$imgPathPrefix = "img_path/"; 

// Initialize variables
$lab_id = $_POST['add_id'];
$lab_name = $_POST['lab_name'] ?? null;
$address = $_POST['address'] ?? null;
$capacity = $_POST['capacity'] ?? null;
$photo = $_FILES['photo']['tmp_name'] ?? null;

// Check if lab_id is provided
if (empty($lab_id)) {
    $_SESSION['toastr'] = array(
        'type' => 'error',
        'message' => 'Lab ID is required.'
    );
    header("Location: ras_lab_list.php");
    exit();
}

// Check if lab exists in database
$query = "SELECT * FROM labs WHERE lab_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$lab_exists = $result->num_rows > 0;
$stmt->close();

if ($lab_exists) {
    // Lab exists, update only the provided fields
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

    if (!empty($photo)) {
        $photoName = basename($_FILES['photo']['name']); 
        $photo_path = "uploads/" . $photoName;
    
        $currentTimestamp = date('Ymd_His'); 
        $newPhotoName = $currentTimestamp . "_" . $photoName; 
    
        if (move_uploaded_file($photo, $photo_path)) {
            try {
                $s3Key = $imgPathPrefix . $newPhotoName;

                $uploadedKey = uploadToS3($photo_path, $bucketName, $s3Key);
    
                $update_fields[] = "img_path = ?";
                $update_params[] = $uploadedKey; 
                $param_types .= "s";
    
                unlink($photo_path);
            } catch (Exception $e) {
                die("Error: " . $e->getMessage());
            }
        } else {
            die("Failed to move uploaded file.");
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

        $_SESSION['toastr'] = array(
            'type' => 'success',
            'message' => 'Lab updated successfully.'
        );
    } else {
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'No fields provided for update.'
        );
    }

} else {
    // Lab does not exist, check required fields
    if (!empty($lab_name) && !empty($address) && !empty($capacity)) {
        $photo_path = null;
        if (!empty($photo)) {
            $photoName = basename($_FILES['photo']['name']); 
            $photo_path = "uploads/" . $photoName;
        
            $currentTimestamp = date('Ymd_His'); 
            $newPhotoName = $currentTimestamp . "_" . $photoName; 
        
            if (move_uploaded_file($photo, $photo_path)) {
                try {
                    $s3Key = $imgPathPrefix . $newPhotoName;
    
                    $uploadedKey = uploadToS3($photo_path, $bucketName, $s3Key);
        
                    $update_fields[] = "img_path = ?";
                    $update_params[] = $uploadedKey; 
                    $param_types .= "s";
        
                    unlink($photo_path);
                } catch (Exception $e) {
                    die("Error: " . $e->getMessage());
                }
            } else {
                die("Failed to move uploaded file.");
            }
        }

        $insert_query = "INSERT INTO labs (lab_id, lab_name, address, img_path, capacity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssi", $lab_id, $lab_name, $address, $photo_path, $capacity);
        $stmt->execute();
        $stmt->close();

        $_SESSION['toastr'] = array(
            'type' => 'success',
            'message' => 'Lab added successfully.'
        );
    } else {
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'Please fill in all required fields to add a new lab (except photo).'
        );
    }
}
$conn->close();
header("Location: ras_lab_list.php");
exit();
?>
