<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ras_admin_login.php');
    exit;
}

// Check if password has been verified
if (!isset($_SESSION['pw_verified']) || $_SESSION['pw_verified'] !== true) {
    $_SESSION['redirect_after_pw'] = 'ras_cancel_app.php';
    header('Location: ras_admin_pw.php');
    exit;
}

// Database connection
require 'db_connect.php';

/*if (isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];

    // Delete the reservation
    $stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_id = ?");
    $stmt->bind_param("s", $reservation_id);

    if ($stmt->execute()) {
        echo "Reservation cancelled successfully.";
    } else {
        echo "Failed to cancel the reservation.";
    }
    $stmt->close();
}*/

date_default_timezone_set('Asia/Seoul');

$now = new DateTime();
$now_str = $now->format('Y-m-d H:i:s');

// Fetch all reservations
$sql = "SELECT r.reservation_id, r.lab_id, r.user_id, r.date, r.time, r.verified, rm.rejected_message 
        FROM reservations r
        LEFT JOIN rejected_messages rm ON r.reservation_id = rm.reservation_id
        WHERE CONCAT(r.date, ' ', r.time) >= ?
        ORDER BY r.date DESC, r.time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $now_str);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
    <link rel="icon" href="img/admin-icon.png" type="image/x-icon">
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-top: 50px;
            margin-bottom: 10px;
        }

        /* Form Styles */
        form {
            display: inline-block;
            text-align: center;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            margin: 10px 0px;
            gap: 5px;
        }

        label {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 250px;
            font-size: 16px;
        }

        /* Table Styles */
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            padding: 10px;
            background-color: #008000;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Button Styles */
        .approve-button, .reject-button {
            padding: 5px 15px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .approve-button {
            background-color: #28a745; /* Green for Approve */
            color: white;
        }

        .reject-button {
            background-color: #dc3545; /* Red for Reject */
            color: white;
        }

        .approve-button:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .reject-button:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        /* Modal Styles */
        #modalOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        #refuseModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
        }

        #refuseModal h3 {
            margin-bottom: 10px;
        }

        #refuseModal form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        #refuseModal form div {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 800px) {
            form {
                display: inline-block;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                justify-content: flex-end;
                gap: 2px;
                margin: 2px 0px;
            }
        }

    </style>
    <script>
        let isEditMode = false; // Tracks whether modal is in Edit mode

        function openRefusePopup(reservationId, rejectedMessage = '') {
            const modal = document.getElementById('refuseModal');
            const modalOverlay = document.getElementById('modalOverlay');
            const modalSubmit = document.getElementById('modalSubmit');
            const modalReasonInput = document.getElementById('refuseReasonInput');
            const modalSubmitButton = document.getElementById('modalSubmitButton');

            modalSubmit.value = reservationId;
            modalReasonInput.value = rejectedMessage; // Load rejection message if in edit mode
            modalSubmitButton.textContent = isEditMode ? 'Save' : 'Submit';

            modalOverlay.style.display = 'block';
            modal.style.display = 'block';
        }

        function closeRefusePopup() {
            const modal = document.getElementById('refuseModal');
            const modalOverlay = document.getElementById('modalOverlay');
            const modalReasonInput = document.getElementById('refuseReasonInput');

            modal.style.display = 'none';
            modalOverlay.style.display = 'none';
            modalReasonInput.value = ''; // Clear input field
        }

        function setEditMode(edit) {
            isEditMode = edit;
        }

    </script>
</head>
<body>
    <h2>Manage Reservations</h2>
    <div><a href="ras_admin_dash.php">Go back to dashboard</a></div>

    <!--form method="POST" action="ras_cancel_app.php">
        <label for="reservation_id">Reservation ID:</label>
        <input type="text" id="reservation_id" name="reservation_id" required><br>
        <button type="submit">Cancel Reservation</button>
    </form-->

    <h2>Existing Reservations</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Lab ID</th>
                <th>User ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Verify</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are any reservations
            if ($result->num_rows > 0) {
                // Loop through and display each reservation
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['reservation_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['lab_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                    
                    if ($row['verified'] == 2) {
                        echo "<td>Rejected</td>";
                    } elseif ($row['verified'] == 1) {
                        echo "<td>Verified</td>";
                    } else {
                        echo "<td><form action='ras_verify_app.php' method='post' style='margin: 0;'>
                                <input type='hidden' name='reservation_id' value='" . htmlspecialchars($row['reservation_id']) . "'>
                                <button type='submit' class='approve-button'>Approve</button>
                              </form></td>";
                    }
                    
                    if ($row['verified'] == 2) {
                        echo "<td><button class='reject-button' onclick=\"setEditMode(true); openRefusePopup('" . htmlspecialchars($row['reservation_id']) . "', '" . htmlspecialchars($row['rejected_message']) . "')\">Edit Message</button></td>";
                    } else {
                        echo "<td><button class='reject-button' onclick=\"setEditMode(false); openRefusePopup('" . htmlspecialchars($row['reservation_id']) . "')\">Reject</button></td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No upcoming reservations found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Modal -->
    <div id="modalOverlay" style="display:none;" onclick="closeRefusePopup()"></div>
    <div id="refuseModal" style="display:none;">
        <h3 id="modalTitle">Reject Reservation</h3>
        <form action="ras_reject_app.php" method="post">
            <input type="hidden" id="modalSubmit" name="reservation_id">
            <label for="refuseReasonInput">Reason:</label>
            <input type="text" id="refuseReasonInput" name="reason" required>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
                <button type="submit" id="modalSubmitButton" class="approve-button">Submit</button>
                <button type="button" class="reject-button" onclick="closeRefusePopup()">Cancel</button>
            </div>
        </form>
    </div>



</body>
</html>

<?php
    $conn->close();
?>