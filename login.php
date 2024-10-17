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

//
//IMPLEMENT BACKEND
//HANDLE LOGIN
//USING PHP
//


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKKU LAS - Login</title>
    <link rel="stylesheet" href="css/login.css">
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