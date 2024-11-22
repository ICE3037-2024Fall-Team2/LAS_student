<?php
session_start();
$toastr = isset($_SESSION['toastr']) ? $_SESSION['toastr'] : null;
unset($_SESSION['toastr']);

require 'db_connect.php';

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $entered_password = $_POST['password'];

    // Fetch user info based on the provided student ID
    //$sql = "SELECT * FROM users WHERE id='$id'";
    //$result = $conn->query($sql);
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

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
            //echo "Invalid password!";
            //echo "<script>alert('Invalid password!');</script>";
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => 'Invalid password!'
            );
            header("Location: login.php");
        }
    } else {
        //echo "User not found!";
        //echo "<script>alert('User not found!');</script>";
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'User not found!'
        );
        header("Location: login.php");
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKKU LRS - Login</title>
    <link rel="icon" href="img/mini-logo-color.png" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <!-- Toastr -->
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>

    <script>
        function validateLoginForm() {
            var id = document.getElementById('userid').value;
            var password = document.getElementById('password').value;
            if (id === "" || password === "") {
                alert("Please fill in all fields.");
                return false;
            }
            return true;
        }
    </script>
</head>

<body>
    <div id="header">
        <h1 id="logoKo" class="header_logo"><img src="img/logo.png?v=4"></h1>
        <h2 id="logoTitle">Lab Reservation system</h2>
        <!--h1 id="logoEn" style="display:none" class="header_logo"><img src="customs/resources/image/logo_en.png"></h1-->
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

    <?php if ($toastr): ?>
    <script type="text/javascript">
        $(document).ready(function() {
            toastr.<?php echo $toastr['type']; ?>('<?php echo $toastr['message']; ?>');
        });
    </script>
    <?php endif; ?>

</body>

</html>