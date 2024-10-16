<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "las_db";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['id'];
    $lab_name = $_POST['lab_name'];
    $reservation_time = str_replace('T', ' ', $_POST['reservation_time']); // Convert datetime-local to MySQL format

    // Check for conflicts (optional)
    $check_sql = "SELECT * FROM reservations WHERE lab_name = ? AND reservation_time = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $lab_name, $reservation_time);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: A reservation already exists for this lab and time.";
    } else {
        // Prepare and bind SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO reservations (student_id, lab_name, reservation_time) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $student_id, $lab_name, $reservation_time);

        if ($stmt->execute()) {
            echo "Reservation successful!";
        } else {
            echo "Error: " . $stmt->error;
            echo "<br>Debug info: student_id=$student_id, lab_name=$lab_name, reservation_time=$reservation_time";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve a Lab</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function validateReservationForm() {
            var labName = document.getElementById('lab_name').value;
            var reservationTime = document.getElementById('reservation_time').value;
            if (labName === "" || reservationTime === "") {
                alert("Please fill in all fields.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Reserve a Lab</h2>

    <form action="appoint.php" method="post" onsubmit="return validateReservationForm()">
        <label for="lab_name">Choose Lab:</label><br>
        <select id="lab_name" name="lab_name" required>
            <option value="">Select a Lab</option>
            <option value="Lab A">Lab A</option>
            <option value="Lab B">Lab B</option>
            <option value="Lab C">Lab C</option>
        </select><br><br>

        <label for="reservation_time">Choose Reservation Time:</label><br>
        <input type="datetime-local" id="reservation_time" name="reservation_time" required><br><br>

        <input type="submit" value="Reserve">
    </form>
</body>
</html>
