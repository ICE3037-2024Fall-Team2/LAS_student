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

if (isset($_POST['reservation_id'])) {
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
}

// Fetch all reservations
$sql = "SELECT reservation_id, lab_id, user_id, date, time, verified 
        FROM reservations 
        WHERE date >= CURDATE() 
        ORDER BY date ASC, time ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Reservation</title>
    <link rel="icon" href="img/admin-icon.png" type="image/x-icon">
    <style>
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

        button[type="submit"] {
            background-color: #008000; /* Green color */
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #006600; /* Darker green on hover */
        }

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

        .refuse-button {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .refused {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        a {
            color: #0B3D0B;
            text-decoration: underline;
            font-size: 0.9em;
            display: block;
            text-align: center;
            margin-bottom: 10px;
            text-align: center;
        }

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
        function openRefusePopup(reservationId, reason = '') {
            const modal = document.getElementById('refuseModal');
            const reasonInput = document.getElementById('refuseReasonInput');
            const modalSubmit = document.getElementById('modalSubmit');
            
            reasonInput.value = reason; // Set reason if provided
            modalSubmit.dataset.reservationId = reservationId;
            
            document.getElementById('modalOverlay').style.display = 'block';
            modal.style.display = 'block';
        }

        function closeRefusePopup() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('refuseModal').style.display = 'none';
        }
    </script>
</head>
<body>
    <h2>Cancel a Reservation</h2>
    <div><a href="ras_admin_dash.php">Go back to dashboard</a></div>

    <form method="POST" action="ras_cancel_app.php">
        <label for="reservation_id">Reservation ID:</label>
        <input type="text" id="reservation_id" name="reservation_id" required><br>
        <button type="submit">Cancel Reservation</button>
    </form>

    <h2>Existing Reservations</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Reservation ID</th>
                <th>Lab ID</th>
                <th>User ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Verified</th>
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
                    if ($row['verified']) {
                        echo "<td>Yes</td>";
                    } else {
                        echo "<td>";
                        echo "<form action='ras_verify_app.php' method='post' style='display:inline;'>";
                        echo "<input type='hidden' name='reservation_id' value='" . htmlspecialchars($row['reservation_id']) . "'>";
                        echo "<button type='submit' class='no-btn'>No</button>";
                        echo "</form>";
                        echo "</td>";
                    }

                    // Refused reservation check
                    if (!empty($row['refused_reason'])) {
                        echo "<td><span class='refused' onclick=\"openRefusePopup('" . htmlspecialchars($row['reservation_id']) . "', '" . htmlspecialchars($row['refused_reason']) . "')\">Refused</span></td>";
                    } else {
                        echo "<td><button class='refuse-button' onclick=\"openRefusePopup('" . htmlspecialchars($row['reservation_id']) . "')\">Refuse</button></td>";
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
    <div id="modalOverlay" onclick="closeRefusePopup()"></div>
    <div id="refuseModal">
        <h3>Refuse Reservation</h3>
        <form action="ras_refuse_app.php" method="post">
            <input type="hidden" id="modalSubmit" name="reservation_id">
            <label for="refuseReasonInput">Reason:</label>
            <input type="text" id="refuseReasonInput" name="reason" required>
            <button type="submit" class="refuse-button">Submit</button>
            <button type="button" onclick="closeRefusePopup()">Cancel</button>
        </form>
    </div>
</body>
</html>

<?php
    $conn->close();
?>