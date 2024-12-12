<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$toastr = isset($_SESSION['toastr']) ? $_SESSION['toastr'] : null;
unset($_SESSION['toastr']);

require 'db_connect.php';
require 's3_connect.php';
$id = $_SESSION['id'];


//$phonenumber = $email = $photo_path = null;
$phonenumber = $email = $photo_path = '';
$check_sql = "SELECT phonenumber, email, photo_path FROM user_info WHERE id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $id);
$stmt->execute();

$stmt->bind_result($phonenumber, $email, $photo_path);


$stmt->fetch();
$stmt->close();

$presignedUrl = '';
$bucketName = "lrsys-bucket";

if ($photo_path){
    try {
        $presignedUrl = generatePresignedUrl($bucketName, $photo_path, '+60 minutes');
    
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}


//upcoming reservations
date_default_timezone_set('Asia/Seoul');

$now = new DateTime();
$now->sub(new DateInterval('PT5M'));
$now_str = $now->format('Y-m-d H:i:s');

$past_date = $now->modify('-30 days')->format('Y-m-d H:i:s');

$future_sql = "SELECT reservation_id, lab_id, date, time, verified, checked
               FROM reservations 
               WHERE user_id = ? 
               AND CONCAT(date, ' ', time) >= ?  AND verified != 3
               ORDER BY date ASC, time ASC";
$future_stmt = $conn->prepare($future_sql);
$future_stmt->bind_param("ss", $_SESSION['id'], $now_str);
$future_stmt->execute();
$future_result = $future_stmt->get_result();

//past reservations
$past_sql = "SELECT reservation_id, lab_id, date, time, verified, checked 
             FROM reservations 
             WHERE user_id = ? 
             AND ( verified = 3 OR(CONCAT(date, ' ', time) >= ? 
             AND CONCAT(date, ' ', time) < ? ))
             ORDER BY date DESC, time DESC";
$past_stmt = $conn->prepare($past_sql);
$past_stmt->bind_param("sss", $_SESSION['id'], $past_date, $now_str);
$past_stmt->execute();
$past_result = $past_stmt->get_result();

if (isset($_GET['action']) && $_GET['action'] === 'getRejectedMessage' && isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];

    // Fetch rejected message from the database
    $rejected_stmt = $conn->prepare("SELECT rejected_message FROM rejected_messages WHERE reservation_id = ?");
    $rejected_stmt->bind_param('s', $reservation_id);
    $rejected_stmt->execute();
    $rejected_result = $rejected_stmt->get_result();

    if ($rejected_result->num_rows > 0) {
        $row = $rejected_result->fetch_assoc();
        echo htmlspecialchars($row['rejected_message']);
    } else {
        echo "No rejected message found.";
    }

    $rejected_stmt->close();
    exit();
}
//$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="icon" href="img/mini-logo-color.png" type="image/x-icon">
    <!-- Toastr -->
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


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
            <!-- Photo Upload / Display -->
             <div class="personal-info">
                <?php if ($presignedUrl == null) { ?>
                    <p><img src="img/profile-user.jpg" alt="Profile Photo" class="profile-photo">
                    <!--<form action="upload_photo.php" method="post" enctype="multipart/form-data">
                        <label for="photo">Upload your photo:</label>
                        <input type="file" name="photo" id="photo"> 
                        <input type="file" name="photo" id="photo" accept="image/*" (change)="getFile($event)" />

                        <button type="submit">Upload</button>
                    </form>-->
                <?php } else { ?>
                    <p><img src="<?php echo htmlspecialchars($presignedUrl); ?>" alt="Profile Photo" class="profile-photo">
                <?php } ?>
                <div class="unchanged-info">
                    <p><strong>ID:</strong> <?php echo $_SESSION['id']; ?></p>
                    <p><strong>Name:</strong> <?php echo $_SESSION['username']; ?></p>
                    <p style="margin-top: 25px"> <strong>Contact info: </strong>
                          </p>
                    <p> 
                    <?php echo !empty($phonenumber) ? htmlspecialchars($phonenumber) : 'Please complete'; ?>
                    </p>
                    <p>
                        <?php echo !empty($email) ? htmlspecialchars($email) : 'Please complete'; ?>
                    </p>
                </div>
             </div>
            
            <button id="edit-inf-butt" class="edit">Edit</button>
        </div>
        <!-- Reservations Info Section -->
        <div id="reservations-info">
            <h2>Upcoming Reservations</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Lab</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($future_result->num_rows > 0) { ?>
                        <?php while ($row = $future_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['lab_id']); ?></td>
                                <td><?php $date = new DateTime($row['date']);
                                            echo htmlspecialchars($date->format('M d')); ?></td>
                                <td><?php echo htmlspecialchars($row['time']); ?></td>
                                <td>
                                    <?php 
                                    if ($row['verified'] == 0) {
                                        echo 'Pending';
                                    } elseif ($row['verified'] == 1) {
                                        ?>
                                        <button class="qr-button" onclick="openQrModal('<?php echo $row['reservation_id']; ?>')">QR</button>

                                        <form action="rsv_cancel.php" method="post" class="action-form">
                                            <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                                            <button type="submit" class="cancel-button">Cancel</button>
                                        </form>
                                        <?php
                                    } elseif ($row['verified'] == 2) {
                                        $reservation_id = htmlspecialchars($row['reservation_id']);
                                        echo "<a href='#' class='rejected-link' onclick=\"showRejectedMessage('$reservation_id', event)\">Rejected</a>";
                                    }
                                    ?>
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
                        <th>Lab</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($past_result->num_rows > 0) { ?>
                        <?php while ($row = $past_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['lab_id']); ?></td>
                                <td><?php $date = new DateTime($row['date']);
                                            echo htmlspecialchars($date->format('M d')); ?></td>
                               </td>
                                <td><?php echo htmlspecialchars($row['time']); ?></td>
                                <td>
                                    <?php 
                                        if ($row['verified'] == 2) {
                                            $reservation_id = htmlspecialchars($row['reservation_id']);
                                            echo "<a href='#' class='rejected-link' onclick=\"showRejectedMessage('$reservation_id', event)\">Rejected</a>";
                                        } elseif ($row['verified'] == 3) {
                                            echo 'Cancelled';
                                        }elseif ($row['checked'] == 0) {
                                            echo 'Absent';
                                        } elseif ($row['checked'] == 1) {
                                            echo 'Attended';
                                        }
                                   
                                    ?>
                                </td>
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

    <!-- Modal window-->
    <div id="rejectedMessageModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <p id="rejectedMessageText"></p>
        </div>
    </div>

    <!-- QR Code Modal window -->
    <div id="qrCodeModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeQrModal()">&times;</span>
            <h3>Your QR Code</h3>
            <div id="qr-code-container" style="display: flex; justify-content: center; margin-top: 20px;"></div>
            <button class="qr-cancel-button" onclick="closeQrModal()">Close</button>
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
                <button type="submit" class="edit">Save</button>
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

    //Rejected hyperlink logic
    function showRejectedMessage(reservationId, event) {
    event.preventDefault(); // Prevent default link behavior (page reload)

    fetch(`?action=getRejectedMessage&reservation_id=${reservationId}`)
        .then(response => response.text())
        .then(data => {
            // Ensure no HTML is included in the response
            if (data.trim().startsWith('<')) {
                console.error('Unexpected HTML in response:', data);
                alert('An error occurred while fetching the rejected message.');
                return;
            }
            
            // Display the message in the modal
            const modal = document.getElementById('rejectedMessageModal');
            document.getElementById('rejectedMessageText').innerHTML = `<br><strong>Reason for rejection:</strong><br><br> ${data.trim()}`;
            modal.style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching rejected message:', error);
            alert('Failed to fetch the rejected message.');
        });
    }

    function closeModal() {
        document.getElementById('rejectedMessageModal').style.display = 'none';
    }

    // Open QR Modal
    function openQrModal(reservationId) {
        const qrCodeContainer = document.getElementById("qr-code-container");
        qrCodeContainer.innerHTML = ""; // Clear previous QR code

        const qrCode = new QRCode(qrCodeContainer, {
            text: reservationId,
            width: 200,
            height: 200,
        });

        document.getElementById("qrCodeModal").style.display = "block";
    }

    // Close QR Modal
    function closeQrModal() {
        document.getElementById("qrCodeModal").style.display = "none";
        const qrCodeContainer = document.getElementById("qr-code-container");
        qrCodeContainer.innerHTML = ""; // Clear QR code for safety
    }
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