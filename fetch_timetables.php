<?php
require 'db_connect.php';

$lab_id = $_POST['lab_id'];
$selected_date = $_POST['selected_date'];

// Function to get unavailable timetables for the selected date
function getUnavailableTimetables($conn, $lab_id, $selected_date) {
    $sql = "SELECT time FROM reservations WHERE lab_id = ? AND date = ? AND verified != 3";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $lab_id, $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $unavailable = [];
    while ($row = $result->fetch_assoc()) {
        $unavailable[] = $row['time'];  // Collect the reserved times for the selected date
    }

    return $unavailable;
}

$unavailableTimetables = getUnavailableTimetables($conn, $lab_id, $selected_date);

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
