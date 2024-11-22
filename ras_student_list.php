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
    $_SESSION['redirect_after_pw'] = 'ras_student_list.php';
    header('Location: ras_admin_pw.php');
    exit;
}

// Database connection
require 'db_connect.php'; 

// Fetch lab IDs from DB
$labOptions = '';
$sql_lab = "SELECT lab_id FROM labs";
$result = $conn->query($sql_lab);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labOptions .= '<option value="' . htmlspecialchars($row['lab_id']) . '">' . htmlspecialchars($row['lab_id']) . '</option>';
    }
} else {
    $labOptions = '<option value="">No labs available</option>';
}

// Fetch student IDs from DB
$studentOptions = '';
$sql_students = "SELECT id FROM users"; 
$result_students = $conn->query($sql_students);

if ($result_students->num_rows > 0) {
    while ($row = $result_students->fetch_assoc()) {
        $studentOptions .= '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['id']) . '</option>';
    }
} else {
    $studentOptions = '<option value="">No students available</option>';
}

// Handle delete request from lab_stu
if (isset($_GET['delete_lab_id']) && isset($_GET['delete_user_id'])) {
    $delete_lab_id = $_GET['delete_lab_id'];
    $delete_user_id = $_GET['delete_user_id'];
    $delete_query = $conn->prepare("DELETE FROM lab_stu WHERE lab_id = ? AND user_id = ?");
    $delete_query->bind_param("ss", $delete_lab_id, $delete_user_id);
    $delete_query->execute();
    $delete_query->close();
    $_SESSION['toastr'] = array(
        'type' => 'success',
        'message' => 'Delete Successful'
    );
    header("Location: ras_student_list.php"); // Redirect to refresh the page
    exit;
}

// Handle add student to lab
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_id']) && isset($_POST['lab_id'])) {
    $add_id = $_POST['add_id'];
    $lab_id = $_POST['lab_id'];

    // Check if student exists in users table
    $check_user_query = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user_query->bind_param("s", $add_id);
    $check_user_query->execute();
    $check_user_query->store_result();
    $user_exists = $check_user_query->num_rows > 0;
    $check_user_query->close();

    // Check if lab exists in labs table
    $check_lab_query = $conn->prepare("SELECT lab_id FROM labs WHERE lab_id = ?");
    $check_lab_query->bind_param("s", $lab_id);
    $check_lab_query->execute();
    $check_lab_query->store_result();
    $lab_exists = $check_lab_query->num_rows > 0;
    $check_lab_query->close();

    // If both user and lab exist, add to lab_stu table
    if ($user_exists && $lab_exists) {
        $add_query = $conn->prepare("INSERT INTO lab_stu (lab_id, user_id) VALUES (?, ?)");
        $add_query->bind_param("ss", $lab_id, $add_id);
        $add_query->execute();
        $add_query->close();
        $_SESSION['toastr'] = array(
            'type' => 'success',
            'message' => 'Successfully added!'
        );
        header("Location: ras_student_list.php"); // Redirect to refresh the page
        exit;
    } else {
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'There is no such a student or lab!'
        );
        header("Location: ras_student_list.php"); 
    }
}

// Fetch lab and student information with a join
$query = "SELECT lab_stu.lab_id, lab_stu.user_id, user_info.username, user_info.phonenumber, user_info.email 
          FROM lab_stu 
          JOIN user_info ON lab_stu.user_id = user_info.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$stmt->bind_result($lab_id, $user_id, $username, $phonenumber, $email);

$labs_students = [];
while ($stmt->fetch()) {
    $labs_students[] = [
        'lab_id' => $lab_id,
        'user_id' => $user_id,
        'username' => $username,
        'phonenumber' => $phonenumber,
        'email' => $email
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

    <title>Student Management</title>
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
            width: 30%;
        }

        .fill {
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

        /* Style for the select dropdown */
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            background-color: white;
            color: #333;
            width: 50%; 
        }

        select:focus {
            border-color: #008000; 
            outline: none;
            box-shadow: 0 0 5px rgba(0, 128, 0, 0.5);
        }

        .fill label {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h2>Student Management</h2>
    <div><a href="ras_admin_dash.php">Go back to dashboad</a></div>

    <form method="POST" action="">
        <div class="fill">
            <label for="add_id">Assign student:</label>
            <select id="add_id" name="add_id" required>
                <option value="" disabled selected>Select student</option>
                <?php echo $studentOptions; ?>
            </select>
        </div>
        <div class="fill">
            <label for="lab_id">To lab:</label>
            <select id="lab_id" name="lab_id" required>
                <option value="" disabled selected>Select lab</option>
                <?php echo $labOptions; ?>
            </select>
        </div>
        <button type="submit">Add Student</button>
    </form>

    <table>
        <tr>
            <th>Lab ID</th>
            <th>Student ID</th>
            <th>Username</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Delete</th>
        </tr>
        <?php foreach ($labs_students as $entry): ?>
            <tr>
                <td><?php echo htmlspecialchars($entry['lab_id']); ?></td>
                <td><?php echo htmlspecialchars($entry['user_id']); ?></td>
                <td><?php echo htmlspecialchars($entry['username']); ?></td>
                <td><?php echo htmlspecialchars($entry['phonenumber']); ?></td>
                <td><?php echo htmlspecialchars($entry['email']); ?></td>
                <td>
                    <a href="ras_student_list.php?delete_lab_id=<?php echo urlencode($entry['lab_id']); ?>&delete_user_id=<?php echo urlencode($entry['user_id']); ?>" 
                       onclick="return confirm('Are you sure you want to remove this user from this lab?');" 
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
