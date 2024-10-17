<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$toastr = isset($_SESSION['toastr']) ? $_SESSION['toastr'] : null;
unset($_SESSION['toastr']);
//
//BACKEND
//implement info display 
//and info acces+cahnge
//using php
//

// Example values
/*$student_id = "2022123456";
$username = "john_doe";
$phone_number = "010-1234-5678";
$email = "john_doe@domain.com";
$photo_path = null; // photo is null for now
*/

require 'db_connect.php';
$id = $_SESSION['id'];


//$phonenumber = $email = $photo_path = null;
$phonenumber = $email = $photo_path = '';
$check_sql = "SELECT phonenumber, email, photo_path FROM user_info WHERE id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $id);
$stmt->execute();

$stmt->bind_result($phonenumber, $email, $photo_path);
/*if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $phonenumber = $row['phonenumber'];
    $email = $row['email'];
    //$photo_path = !empty($row['photo_path']) ? $row['photo_path'] : "Please complete your information.";
    $photo_path = $row['photo_path'];
}*/

if ($stmt->fetch()) {
} 

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- Toastr -->
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>

    <link rel="stylesheet" href="css/style.css"> 
    <!--css/index.css"> 
    <css/profile.css"--> 
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
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>
    </div>


    <!-- Profile Main Content -->
    <div id="profile-content">

        <!-- Account Info Section -->
        <div id="account-info">
            <h2>Account Info</h2>
            <p><strong>Student ID:</strong> <?php echo $_SESSION['id']; ?></p>
            <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>
            <p><strong>Phone Number:</strong> 
                <?php echo !empty($phonenumber) ? htmlspecialchars($phonenumber) : 'Please complete your information'; ?>
            </p>
            <p><strong>Email:</strong> 
                <?php echo !empty($email) ? htmlspecialchars($email) : 'Please complete your information'; ?>
            </p>
            <!-- Photo Upload / Display -->
            <?php if ($photo_path == null) { ?>
                <p><strong>Photo:</strong><img src="img/blank-profile.webp" alt="Profile Photo" class="profile-photo">
                <!--<form action="upload_photo.php" method="post" enctype="multipart/form-data">
                    <label for="photo">Upload your photo:</label>
                    <input type="file" name="photo" id="photo"> 
                    <input type="file" name="photo" id="photo" accept="image/*" (change)="getFile($event)" />

                    <button type="submit">Upload</button>
                </form>-->
            <?php } else { ?>
                <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Photo" class="profile-photo">
            <?php } ?>
            <button id="edit-inf-butt" class="edit">Edit</button>
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
            <form id="change-password-form" action="change_password.php" method="post">
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

    <!-- JavaScript for handling Edit and Save -->
    <script>
    document.getElementById('edit-inf-butt').addEventListener('click', function() {
    var accountInfoDiv = document.getElementById('account-info');

    // Replace content with an edit form
    accountInfoDiv.innerHTML = `
        <h2>Edit Account Info</h2>
        <form id="edit-form" action="update_account.php" method="POST" enctype="multipart/form-data">
            <p><strong>Student ID:</strong> <?php echo $_SESSION['id']; ?></p>
            <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>

            <p><strong><label for="phone">Phone Number:</label></strong>
                <input type="text" id="phone" name="phone" 
                pattern="010-[0-9]{4}-[0-9]{4}"
                title="Phone number must be in the format 010-xxxx-xxxx" 
                value="<?php echo htmlspecialchars($phonenumber); ?>" required><br>
            </p>
            <p><strong><label for="email">Email:</label></strong>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>
            </p>
            <p><strong><label for="photo">Upload your photo:</label></strong>
            <input type="file" name="photo" id="photo" accept="image/*"><br>
            <img id="img-preview" alt="Image Preview">
            </p>
            <button type="submit" class="edit">Save Changes</button>
        </form>
        <button id="cancel-edit" class="edit">Cancel</button>

    `;

    // Add event listener to "Cancel" button to revert back to original view
    document.getElementById('cancel-edit').addEventListener('click', function() {
        location.reload(); // Reload the page to cancel changes and revert back to original view
    });

    document.getElementById('phone').addEventListener('input', function() {
        this.value = this.value.trim().replace(/[^0-9\-]/g, '');  
        this.value = this.value.trim(); 
    });

    document.getElementById('photo').addEventListener('change', function(event) {
        var file = event.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('img-preview');
                img.src = e.target.result; 
                img.style.display = 'block'; 
            };
            reader.readAsDataURL(file);
        }
    });
    
    document.getElementById('change-password-form').addEventListener('submit', function(event) {
        var currentPasswordInput = document.getElementById('current-password');
        var newPasswordInput = document.getElementById('new-password');
        
        currentPasswordInput.value = currentPasswordInput.value.trim();
        newPasswordInput.value = newPasswordInput.value.trim();
    });
});
</script>

<?php if ($toastr): ?>
    <script type="text/javascript">
        $(document).ready(function() {
            toastr.<?php echo $toastr['type']; ?>('<?php echo $toastr['message']; ?>');
        });
    </script>
    <?php endif; ?>
    
</script>

</body>

</html>
