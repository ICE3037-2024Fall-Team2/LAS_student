<?php
session_start();

$servername = "localhost";
$username = "root"; 
$password = "your_mysql_password"; 
$dbname = "las_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id']; 
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM users WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
      
        if (password_verify($password, $row['password'])) {
            
            $_SESSION['id'] = $row['id'];  
            $_SESSION['username'] = $row['username'];  

            echo "Login successful!<br>";
            echo "Welcome, " . $_SESSION['username'] . "!";

            header("Location: index.php");  
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
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login.php" method="post">
        <label for="id">Student ID:</label><br>
        <input type="text" id="id" name="id" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="register.php">Go to register</a></p>
</body>
</html>