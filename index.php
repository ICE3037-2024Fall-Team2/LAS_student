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
    <title>Lab Reservation</title>
    <link rel="stylesheet" href="css/login.css">
    
    
    <!-- importing styles for icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!-- Header -->
    <div id="header">
        <div class="left">
            <img src="img/mini-logo-color2.png" alt="Logo" class="logo-circle">
            <span>Lab Reservation</span>
        </div>
        <div class="right">
            <i class="fa-solid fa-user" onclick="toggleMenu()"></i>
            <div class="dropdown" id="userMenu">
                <i class="fa-solid fa-x close-dropdown" onclick="closeMenu()"></i>
                <a href="profile.php#account-info">Account Info</a>
                <a href="profile.php#reservations-info">Reservations</a>
                <a href="profile.php#past-reservations">Past Reservations</a>
                <a href="profile.php#change-password">Change Password</a>
                <a href="login.php" class="logout">Logout</a>
            </div>
        </div>
    </div>

    <div id="book-lab-block">
        <?php 
        //some backend implementation
        //check when implementing
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { // Printing the blocks for labs
                echo '
            <div class="lab-block">
                <div class="lab-info">
                    <h3>' . htmlspecialchars($row["lab_name"]) . '</h3>
                    <p>' . htmlspecialchars($row["address"]) . '</p>
                </div>
                <button class="book-btn" onclick="window.location.href=\'reservation.php?lab_id=' . $row["lab_id"] . '\'">Go to Reserve</button>
            </div>';
            }
        } else {
            echo '<p>No labs available.</p>';
        }
        ?>
    </div>
    <script>
        //implemented later:
        //function toggleMenu()
        //function closeMenu()
    </script>

</body>

</html>

<?php
$conn->close();
?>