<?php
session_start();
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
        /*$sql = "INSERT INTO users (id, username, password) VALUES ('$id', '$username', '$hashed_password')";

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
    }*/
        $sql = "INSERT INTO users (id, username, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Bind the parameters to the prepared statement
        $stmt->bind_param("sss", $id, $username, $hashed_password);

        // Add user to the user_info table (only id and username)
        $sql_user_info = "INSERT INTO user_info (id, username) VALUES (?, ?)";
        $stmt_user_info = $conn->prepare($sql_user_info);
        $stmt_user_info->bind_param("ss", $id, $username);

        // Execute the prepared statements
        if ($stmt_users->execute() && $stmt_user_info->execute()) {
            // Registration successful - set toastr success message
            $_SESSION['toastr'] = array(
                'type' => 'success',
                'message' => 'Sign Up successful!'
            );
            header("Location: login.php");
            exit();
            // Ensure script stops after header redirection
        } else {
            // Handle errors during the execution
            //echo "<script>alert('Error: " . $errorMessage . "');</script>";
            //$errorMessage = htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => $stmt->error
            );
        }
        header("Location: login.php");
        exit(); 

        // Close the prepared statement
        $stmt_user_info->close();
        $stmt->close();
    } else {
        // Show error message for invalid ID format
        //echo "<script>alert('Student ID must be exactly 10 digits!');</script>";
        echo "<script>toastr.warning('Student ID must be exactly 10 digits!');</script>";
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
    <!-- Toastr -->
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>

    
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
                        <input id="id" type="text" name="id" value="" 
                        pattern="[0-9]{10}"
                        title="Student ID must be exactly 10 digits." 
                        autocomplete="off" placeholder="Student ID" required>
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
