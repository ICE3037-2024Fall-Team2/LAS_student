<?php
session_start();
$toastr = isset($_SESSION['toastr']) ? $_SESSION['toastr'] : null;
unset($_SESSION['toastr']);

require '../backend/db_connect.php';

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $entered_password = $_POST['password'];

    // Determine if the user is an admin or a student
    if (str_starts_with($id, '1')) {
        // Admin login
        $sql = "SELECT * FROM admin WHERE admin_id = ?";
    } elseif (str_starts_with($id, '2')) {
        // Student login
        $sql = "SELECT * FROM users WHERE id = ?";
    } else {
        // Invalid ID format
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'Invalid ID format!'
        );
        header("Location: login.php");
        exit();
    }

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
            if (str_starts_with($id, '1')) {
                // Admin login successful
                $_SESSION['id'] = $row['admin_id'];
                $_SESSION['username'] = $row['admin_name'];
                $_SESSION['admin_logged_in'] = true;
                header("Location: ../admin_pages/ras_admin_dash.php"); //Redirect to admin dashboard
                exit();
            } else {
                // Student login successful
                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                header("Location: index.php"); // Redirect to lab reservation page
                exit();
            }
        } else {
            // Invalid password
            $_SESSION['toastr'] = array(
                'type' => 'error',
                'message' => 'Invalid password!'
            );
            header("Location: login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['toastr'] = array(
            'type' => 'error',
            'message' => 'User not found!'
        );
        header("Location: login.php");
        exit();
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
    <link rel="icon" href="../img/mini-logo-color.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <!-- Toastr -->
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <!-- importing styles for icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

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
        <h1 id="logoKo" class="header_logo"><img src="../img/logo.png?v=4"></h1>
        <h2 id="logoTitle">Lab Reservation system</h2>
        <!--h1 id="logoEn" style="display:none" class="header_logo"><img src="customs/resources/image/logo_en.png"></h1-->
    </div>
    <div id="login_wrapper">
        <form action="login.php" method="post" onsubmit="return validateLoginForm()">
            <div class="loginForm">
                <div class="loginBox">
                    <div class="inputText">
                        <label for="userid"></label>
                        <input id="userid" type="text" name="id" value="" autocomplete="off"
                            placeholder="Student ID" required>
                            
                    </div>
                    <div class="inputText">
                        <label for="password"></label>
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