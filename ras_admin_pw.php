<?php
session_start();


require 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ras_admin_login.php');
    exit;
}

if (isset($_POST['password'])) {
    $id = $_SESSION['id'];
    $password = $_POST['password'];

    // Verify the admin's password again
    $stmt = $conn->prepare("SELECT password FROM admin WHERE admin_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Proceed to the action
        $_SESSION['pw_verified'] = true;
        header('Location: ' . $_SESSION['redirect_after_pw']);
        exit;
    } else {
        $error = "Password incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Password</title>
    <link rel="icon" href="img/admin-icon.png" type="image/x-icon">
    <style>
      body {
    font-family: Arial, sans-serif;
    text-align: center;
    background-color: #f2f2f2;
    margin: 0;
    padding: 0;
}

h2 {
    color: #333;
    font-size: 24px;
    margin-top: 50px;
    margin-bottom: 30px;
}

form {
    display: inline-block;
    text-align: center;   
}

.input_psw {
    display: flex;
    flex-direction: row; 
    gap: 10px; 
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: bold;
    font-size: 18px;
    padding: 10px;
}

input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 200px;
    font-size: 16px;
}

button[type="submit"] {
    background-color: #008000; /* Green color */
    color: #fff;
    padding: 10px 40px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    margin-top: 10px;

}

button[type="submit"]:hover {
    background-color: #006600; /* Darker green on hover */
}

a {
    color: #ffffff;
    text-decoration: underline;
    font-size: 0.9em;
    color: #0B3D0B;
    display: block;
    margin-top: 15px;
    text-align: center;
}
    </style>
</head>
<body>
    <h2>Confirm Password</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST" action="ras_admin_pw.php">
        <div class="input_psw">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
        </div>
        <button type="submit">Confirm</button>
    </form>
    <div><a href="ras_admin_dash.php">Go back to admin menu</a></div>
</body>
</html>
