<?php
/*
$servername = "localhost";
$username = "root";  
$password = "";     

$dbname = "las_db";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
*/
require 'db_connect.php';

// Handle register
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate student ID format
    if (preg_match("/^\d{10}$/", $id)) {
        // Hash the password using bcrypt (for protection)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Add user to the users table
        $sql = "INSERT INTO users (id, username, password) VALUES ('$id', '$username', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            // Registration successful - show an alert
            echo "<script>alert('Registration successful!');</script>";
            header("Location: login.php");
        } else {
            // Show error message in an alert
            $errorMessage = $conn->error;
            echo "<script>alert('Error: " . addslashes($errorMessage) . "');</script>";
        }
    } else {
        // Show error message for invalid ID in an alert
        echo "<script>alert('Student ID must be exactly 10 digits!');</script>";
    }
    
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css"> 
    
    <script>
        function validateRegistrationForm() {
            var id = document.getElementById('id').value;
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            if (id === "" || username === "" || password === "") {
                alert("Please fill in all fields.");
                return false;
            }
            if (!/^\d{10}$/.test(id)) {
                alert("Student ID must be exactly 10 digits.");
                return false;
            }
            return true;
        }
    </script>
</head>

<body>
    <div id="header">
        <h1 id="logoKo" class="header_logo"><img src="img/logo.png?v=4"></h1>
        <h2 id="logoTitle"> Registration </h2>
    </div>

    <div id="login_wrapper">
        <form action="register.php" method="post" onsubmit="return validateRegistrationForm()">
            <div class="loginForm">
                <div class="loginBox">
                    <div class="inputText">
                        <label for="id">Student ID</label>
                        <input id="id" type="text" name="id" value="" autocomplete="off" placeholder="Student ID" required>
                    </div>
                    <div class="inputText">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="" autocomplete="off" placeholder="Username" required>
                    </div>
                    <div class="inputText">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" placeholder="Password" required>
                    </div>
                </div>
                <button type="submit" class="loginBtn" id="btnLoginBtn"><span id="btnLogin">REGISTER</span></button>
            </div>
        </form>
    </div>

    <div class="footerBtn">
        <div class="btnLink">
            <ul>
                <div>Already have an account? <a href="login.php">Go to login</a></div>
            </ul>
        </div>
    </div>
</body>

</html>
