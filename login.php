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

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $entered_password = $_POST['password'];

    // Fetch user info based on the provided student ID
    $sql = "SELECT * FROM users WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password_hash = $row['password'];

        // Verify the entered password with the stored hashed password
        if (password_verify($entered_password, $stored_password_hash)) {
            // Password is correct, set session variables
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            header("Location: index.php"); // redirect to welcome page
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKKU LAS - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        //BACKEND
        //IMPLEMENT validateLoginForm()
        //USING JS
    </script>
</head>

<body>
    <div id="header">
        <h1 id="logoKo" class="header_logo"><img src="img/logo.png?v=4"></h1>
        <h2 id="logoTitle">Lab Appointment system</h2>
    </div>
    <div id="login_wrapper">
        <form action="login.php" method="post" onsubmit="return validateLoginForm()">
            <div class="loginForm">
                <div class="loginBox">
                    <div class="inputText">
                        <label for="userid"> Student ID</label>
                        <input id="userid" type="text" name="id" value="" autocomplete="off"
                            placeholder="Student ID" required>
                            
                    </div>
                    <div class="inputText">
                        <label for="password"> Password </label>
                        <input id="password" type="password" name="password" autocomplete="new-password"
                            placeholder="Password" required>
                    </div>
                </div>
                <button type="submit" class="loginBtn" id="btnLoginBtn"><span id="btnLogin">LOGIN</span></button>

            </div>
        </form>
    </div>
    <div class="footerBtn">
        <div class="btnLink">
            <ul>
                <div>Dont have an account? <a href="register.php">Go to register</a></div>
            </ul>
        </div>
    </div>

</body>

</html>
