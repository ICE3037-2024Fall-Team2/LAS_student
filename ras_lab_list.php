<?php
session_start();
$toastr = isset($_SESSION['toastr']) ? $_SESSION['toastr'] : null;
unset($_SESSION['toastr']);

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ras_admin_login.php');
    exit;
}

// Check if password has been verified
if (!isset($_SESSION['pw_verified']) || $_SESSION['pw_verified'] !== true) {
    $_SESSION['redirect_after_pw'] = 'ras_change_admin_list.php';
    header('Location: ras_admin_pw.php');
    exit;
}

// Database connection
require 'db_connect.php'; 

// Handle delete request
if (isset($_GET['delete_lab_id'])) {
    $delete_lab_id = $_GET['delete_lab_id'];
    $delete_query = $conn->prepare("DELETE FROM labs WHERE lab_id = ?");
    $delete_query->bind_param("s", $delete_lab_id);
    $delete_query->execute();
    $delete_query->close();
    $_SESSION['toastr'] = array(
        'type' => 'success',
        'message' => 'Lab removed successfully.'
    );
    header("Location: ras_lab_list.php");
    exit;
}

$query = "SELECT lab_id, lab_name, address, img_path, capacity FROM labs";
$stmt = $conn->prepare($query);
$stmt->execute();
$stmt->bind_result($lab_id, $lab_name, $address, $img_path, $capacity);

$labs = [];
while ($stmt->fetch()) {
    $labs[] = [
        'lab_id' => $lab_id,
        'lab_name' => $lab_name,
        'address' => $address,
        'img_path' => $img_path,
        'capacity' => $capacity
    ];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Toastr -->
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>

    <title>Lab Management</title>
    <link rel="icon" href="img/admin-icon.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-top: 50px;
            margin-bottom: 10px;
        }

        h2 {
            color: #333;
            margin-top: 20px;
            margin-bottom: 0px;
        }
        form {
            display: inline-block;
            text-align: center;
            margin-bottom: 10px;
        }

        .fill {
            display: flex;
            flex-direction: row;
            align-items: center;
            text-align: center;
            justify-content: flex-start; 
            gap: 5px;
            margin: 10px 0px;
            margin-left: 20px;
        }

        input {
            margin-right: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button[type="submit"] {
            background-color: #008000; /* Green color */
            color: #fff;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #006600; /* Darker green on hover */
        }

        label {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }

        p{
            color: #008000;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th, td {
            border: 1px solid #ddd;
            text-align: center;

        }

        th {
            padding: 10px;
            background-color: #008000;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .delete-btn {
            margin: 5px;
            background-color: #ff4d4d;
            color: white;
            padding: 5px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px 10px;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        a {
            color: #0B3D0B;
            text-decoration: underline;
            font-size: 0.9em;
            display: block;
            text-align: center;
            margin-bottom: 10px;
        }

        @media (max-width: 800px) {
            .fill {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                justify-content: flex-start; 
                gap: 2px;
            }

            .fill input{
                margin-bottom: 5px;
            }


            button[type="submit"] {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <h1>Lab Management</h1>
    <div><a href="ras_admin_dash.php">Go back to dashboad</a></div>

    <h2>Update Lab</h2>
    <form method="POST" action="ras_lab_update.php" >
        <div class="fill">
            <label for="lab_id">ID:</label>
            <input type="text" id="add_id" name="add_id" value="" 
                        pattern="[0-9]{5}"
                        title="Lab ID must be exactly 5 digits." required>
            <label for="lab_name">Name:</label>
            <input type="text" id="lab_name" name="lab_name">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" >
        </div>
        <div class="fill">
        <label for="capacity">Capacity:</label>
        <input type="text" id="capacity" name="capacity"  
                        pattern="[0-9]+"
                        title="capacity must be number.">
            <label for="photo">Photo:</label>
            <input type="file" name="photo" id="photo" accept="image/*">
            <button type="submit">Update</button>
        </div>
        <p>If there no such lab yet, the new lab will be added to list</p>
    </form>

    <h2>Current Labs</h2>
    <table>
        <tr>
            <th>Lab ID</th>
            <th>Lab Name</th>
            <th>Address</th>
            <th>Image Available</th>
            <th>Capacity</th>
            <th>Delete</th>
        </tr>
        <?php foreach ($labs as $lab): ?>
            <tr>
                <td><?php echo htmlspecialchars($lab['lab_id']); ?></td>
                <td><?php echo htmlspecialchars($lab['lab_name']); ?></td>
                <td><?php echo htmlspecialchars($lab['address']); ?></td>
                <td><?php echo $lab['img_path'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo htmlspecialchars($lab['capacity']); ?></td>
                <td>
                <a href="ras_lab_list.php?delete_lab_id=<?php echo urlencode($lab['lab_id']); ?>" 
                       onclick="return confirm('Are you sure you want to remove this lab?');" 
                       class="delete-btn">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

    <?php if ($toastr): ?>
    <script type="text/javascript">
        $(document).ready(function() {
            toastr.<?php echo $toastr['type']; ?>('<?php echo $toastr['message']; ?>');
        });
    </script>
    <?php endif; ?>
</body>
</html>
