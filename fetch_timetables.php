<?php
session_start();
require 'db_connect.php';

$lab_id = $_POST['lab_id'];
$selected_date = $_POST['selected_date'];


//$sql = "SELECT * FROM labs WHERE lab_id='$lab_id'";
//$result = $conn->query($sql);
$sql = "SELECT * FROM labs WHERE lab_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$lab_capacity = 0;
if ($result->num_rows > 0) {
    $lab = $result->fetch_assoc();
    $lab_capacity = $lab['capacity'];
} else {
    echo "Lab not found!";
    exit();
}
$stmt->close();

// Function to get unavailable timetables for the selected date
function getUnavailableTimetables($conn, $lab_id, $selected_date, $lab_capacity) {
    $user_id =  $_SESSION['id'];
    // Query to count the number of reservations for each time slot
    $sql = " SELECT time, COUNT(*) as reservation_count, 
            SUM(CASE WHEN user_id = ? THEN 1 ELSE 0 END) as user_reservation
            FROM reservations
            WHERE lab_id = ? AND date = ? AND verified != 3
            GROUP BY time
    ";
    $search_stmt = $conn->prepare($sql);
    $search_stmt->bind_param("sss", $user_id, $lab_id, $selected_date);
    $search_stmt->execute();
    $search_stmt->bind_result($time, $reservation_count, $user_reserved);
    
    $unavailable = [];
    while ($search_stmt->fetch()) {
        // If the number of reservations has reached the lab's capacity, or the current user has already reserved this time slot
        if ($user_reserved != 0 || $reservation_count >= $lab_capacity) {
            $unavailable[] = $time;  // Mark the time as unavailable
        }
    }
    $search_stmt->close();
    return $unavailable;
}


$unavailableTimetables = getUnavailableTimetables($conn, $lab_id, $selected_date, $lab_capacity);

// Get the current time if the selected date is today
date_default_timezone_set('Asia/Seoul'); // Set to your timezone
$currentDate = date('Y-m-d');
$currentTime = ($selected_date === $currentDate) ? date('H:i') : null;

// Generate timetable block
$times = ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
for ($i = 0; $i < 4; $i++) {
    echo '<tr>';
    for ($j = 0; $j < 3; $j++) {
        $time = $times[$i * 3 + $j];

        // Check if the time is unavailable or in the past for today
        if (in_array($time, $unavailableTimetables) || ($currentTime && $time <= $currentTime)) {
            echo '<td class="unavailable-time">' . $time . '</td>';
        } else {
            echo '<td class="available-time" data-time="' . $time . '">' . $time . '</td>';
        }
    }
    echo '</tr>';
}

$conn->close();
?>
