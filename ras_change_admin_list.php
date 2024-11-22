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

// Handle delete request from admin table
if (isset($_GET['delete_admin_id'])) {
    $delete_admin_id = $_GET['delete_admin_id'];
    $delete_query = $conn->prepare("DELETE FROM admin WHERE admin_id = ?");
    $delete_query->bind_param("s", $delete_admin_id);
    $delete_query->execute();
    $delete_query->close();
    $_SESSION['toastr'] = array(
        'type' => 'success',
        'message' => 'Delete Successful'
    );
    header("Location: ras_change_admin_list.php"); // Redirect to refresh the page
    exit;
}

// Fetch admin information
$query = "SELECT admin_id, admin_name FROM admin";
$stmt = $conn->prepare($query);
$stmt->execute();
$stmt->bind_result($admin_id, $admin_name);

$admins = [];
while ($stmt->fetch()) {
    $admins[] = [
        'admin_id' => $admin_id,
        'admin_name' => $admin_name,
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

    <title>Admin Management</title>
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

        h2 {
            color: #333;
            font-size: 24px;
            margin-top: 50px;
            margin-bottom: 10px;
        }

        form {
            display: inline-block;
            text-align: center;
            display: flex;
            flex-direction: row;
            align-items: center;
            text-align: center;
            justify-content: flex-end; 
            gap: 5px;
            margin: 10px 0px;
        }

        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button[type="submit"] {
            background-color: #008000; /* Green color */
            color: #fff;
            padding: 10px 20px;
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

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
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

    </style>
</head>
<body>
    <h2>Admin Management</h2>
    <div><a href="ras_admin_dash.php">Go back to dashboad</a></div>

    <div>
        <form action="ras_admin_register.php" method="post">
            <label for="admin_id">Admin ID:</label>
            <input type="text" name="admin_id" id="admin_id"  value="" 
                        pattern="[0-9]{10}"
                        title="ID must be exactly 10 digits." required><br>
            <label for="admin_name"> Name:</label>
            <input type="text" name="admin_name" id="admin_name" required><br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br>

            <button type="submit">Register</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Admin ID</th>
            <th>Name</th>
            <th>Delete</th>
        </tr>
        <?php foreach ($admins as $admin): ?>
            <tr>
                <td><?php echo htmlspecialchars($admin['admin_id']); ?></td>
                <td><?php echo htmlspecialchars($admin['admin_name']); ?></td>
                <td>
                    <a href="ras_change_admin_list.php?delete_admin_id=<?php echo urlencode($admin['admin_id']); ?>" 
                       onclick="return confirm('Are you sure you want to remove this admin?');" 
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
