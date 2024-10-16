<?php
session_start();  

if (!isset($_SESSION['username'])) {
 
    header("Location: login.html");
    exit();
}


$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        .welcome {
            color: #007bff;
        }
        .button-container {
            margin-top: 20px;
        }
        .button-container a {
            display: block;
            margin: 10px 0;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            border-radius: 5px;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        .button-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Welcome, <span class="welcome"><?php echo htmlspecialchars($username); ?></span>!</h2>

    <div class="button-container">
        <!-- goto appoint.php -->
        <a href="appoint.php">Go to Appointment</a>

        <!-- goto profile.php -->
        <a href="profile.php">Go to Profile</a>

        <!--goto view.php -->
        <a href="view.php">Go to View</a>
    </div>
</body>
</html>