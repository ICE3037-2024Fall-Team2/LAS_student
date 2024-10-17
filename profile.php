<?php
session_start();


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$toastr = isset($_SESSION['toastr']) ? $_SESSION['toastr'] : null;
unset($_SESSION['toastr']);
// Placeholder values for demonstration purposes
//$student_id = "2022123456";
//$username = "john_doe";
//$phone_number = "010-1234-5678";
//$email = "john_doe@domain.com";
//$photo_path = null; // Assume photo is null for now, which means no photo is uploaded

require 'db_connect.php';
$id = $_SESSION['id'];

//$sql = "SELECT * FROM user_info WHERE id = '$id' ";
//$result = $conn->query($sql);
//$check_sql = "SELECT * FROM user_info WHERE id = ?";
//$result = $conn->prepare($check_sql);
//$result->bind_param("s", $id);
//$result->execute();
//$result->store_result();

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

//upcoming reservation
date_default_timezone_set('Asia/Seoul');
$today = new DateTime();
$today_str = $today->format('Y-m-d');
$future_sql = "SELECT reservation_id, lab_id, date, time FROM reservations WHERE user_id = ? AND date >= ? ORDER BY date, time";
$future_stmt = $conn->prepare($future_sql);
$future_stmt->bind_param("ss", $_SESSION['id'], $today_str);
$future_stmt->execute();
$future_result = $future_stmt->get_result();

$past_date = $today->modify('-30 days')->format('Y-m-d');
$past_sql = "SELECT lab_id, date, time, verified FROM reservations WHERE user_id = ? AND date < ? ORDER BY date DESC, time";
$past_stmt = $conn->prepare($past_sql);
$past_stmt->bind_param("ss", $_SESSION['id'], $today_str);
$past_stmt->execute();
$past_result = $past_stmt->get_result();

//$conn->close();
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
    <link rel="stylesheet" href="css/index.css"> 
    <link rel="stylesheet" href="css/profile.css"> 
    <!-- Styles for profile page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!-- Header -->
    <?php include 'header.php'; ?>


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
                <p><strong>Photo:</strong><img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Photo" class="profile-photo">
            <?php } ?>
            <button id="edit-inf-butt" class="edit">Edit</button>
        </div>
        <!-- Reservations Info Section -->
        <div id="reservations-info">
            <h2>Upcoming Reservations</h2>
            <table border="1">
        <thead>
            <tr>
                <th>Lab ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>generate QR Code</th>
                <th>Cancel</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($future_result->num_rows > 0) { ?>
                <?php while ($row = $future_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['lab_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['time']); ?></td>
                        <td>
                            <form action="generate_qr.php" method="post">
                                <!--<input type="hidden" name="lab_id" value="<?php echo $row['lab_id']; ?>">
                                <input type="hidden" name="date" value="<?php echo $row['date']; ?>">
                                <input type="hidden" name="time" value="<?php echo $row['time']; ?>">-->
                                <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>" >
                                <button type="submit" class="edit" id="qr-butt">View QR Code</button>
                            </form>
                        </td>
                        <td>
                            <form action="rsv_cancel.php" method="post">
                                <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>" >
                                <button type="submit" class="edit" id="cancel-butt">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4">No upcoming reservations.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
        </div>

        <!-- Past Reservations Section -->
        <div id="past-reservations">
            <h2>Past Reservations (Past 30days)</h2>
            <table border="1">
        <thead>
            <tr>
                <th>Lab ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Attended</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($past_result->num_rows > 0) { ?>
                <?php while ($row = $past_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['lab_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['time']); ?></td>
                        <td><?php echo $row['verified'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4">No past reservations in the last 30 days.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
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

</body>

</html>
<?php
$future_stmt->close();
$past_stmt->close();
$conn->close();
?>