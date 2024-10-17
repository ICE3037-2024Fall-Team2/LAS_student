<?php

// link to mysql
/*
$servername = "localhost";
$username = "root";  
$password = "wyq001102";     

$dbname = "las_db";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
*/
require 'db_connect.php';

//
//BACKEND
//IMPLEMENT REGISTER
//USING PHP
//

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/login.css">
    <script>
        //BACKEND
        //IMPLEMENT validateRegistrationForm()
        //USING JS
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
