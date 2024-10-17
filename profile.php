<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

//
//BACKEND
//implement info display 
//and info acces+cahnge
//using php
//

// Example values
$student_id = "2022123456";
$username = "john_doe";
$phone_number = "010-1234-5678";
$email = "john_doe@domain.com";
$photo_path = null; // photo is null for now
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/index.css"> 
    <link rel="stylesheet" href="css/profile.css"> 
    <!-- Styles for profile page -->
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


    <!-- Profile Main Content -->
    <div id="profile-content">

        <!-- Account Info Section -->
        <div id="account-info">
            <h2>Account Info</h2>
            <p><strong>Student ID:</strong> <?php echo $student_id; ?></p>
            <p><strong>Username:</strong> <?php echo $username; ?></p>
            <p><strong>Phone Number:</strong> <?php echo $phone_number; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>

            <!-- Photo Upload or Display -->
            <?php if ($photo_path == null) { ?>
                <form action="upload_photo.php" method="post" enctype="multipart/form-data">
                    <label for="photo">Upload your photo:</label>
                    <input type="file" name="photo" id="photo">
                    <button type="submit">Upload</button>
                </form>
            <?php } else { ?>
                <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Photo" class="profile-photo">
            <?php } ?>
        </div>

        <!-- Reservations Info Section -->
        <div id="reservations-info">
            <h2>Upcoming Reservations</h2>
            <?php
            // Placeholder for upcoming reservations from db
            // get upcoming reservations
            // If found, print them
            // else print 'No  reservations found.'

            echo "<p>No upcoming reservations.</p>"; // sample text
            ?>
        </div>

        <!-- Past Reservations Section -->
        <div id="past-reservations">
            <h2>Past Reservations</h2>
            <?php
            // Placeholder for past reservations from db
            // get past reservations
            // if found, print them
            // else print 'No  reservations found.'

            echo "<p>No past reservations.</p>"; // sample text
            ?>
        </div>

        <!-- Change Password Section -->
        <div id="change-password">
            <h2>Change Password</h2>
            <form action="change_password.php" method="post">
                <div>
                    <label for="current-password">Current Password:</label>
                    <input type="password" name="current-password" id="current-password" required>
                </div>
                <div>
                    <label for="new-password">New Password:</label>
                    <input type="password" name="new-password" id="new-password" required>
                </div>
                <button type="submit">Change Password</button>
            </form>
        </div>

    </div>

</body>

</html>
