<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

/*
$servername = "localhost";

$username = "root";  
$password = "wyq001102";     
$dbname = "las_db";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
*/
require 'db_connect.php';

date_default_timezone_set('Asia/Seoul');
$today = new DateTime();
$dayOfWeek = $today->format('w'); // 0 = Sun, 6 = Sat

$weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Fetch lab details
$lab_id = isset($_GET['lab_id']) ? $_GET['lab_id'] : null;
if ($lab_id === null) {
    echo "Invalid lab selection.";
    exit();
}

//Select chosen lab info
$sql = "SELECT * FROM labs WHERE lab_id='$lab_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $lab = $result->fetch_assoc();
} else {
    echo "Lab not found!";
    exit();
}

// Selects unavailable timetables from DB (for the chosen date)
function getUnavailableTimetables($conn, $lab_id, $date)
{
    $sql = "SELECT time FROM reservations WHERE lab_id='$lab_id' AND date='$date'";
    $result = $conn->query($sql);

    $unavailable = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $unavailable[] = $row['time'];
        }
    }
    return $unavailable;
}

// Get unavailable times for today
$unavailableTimetables = getUnavailableTimetables($conn, $lab_id, $today->format('Y-m-d'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation - <?php echo htmlspecialchars($lab['lab_name']); ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/index.css"> 
    <link rel="stylesheet" href="css/reservation.css"> 

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- jquery? -->
</head>

<body>
    
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


    <!-- Main Content -->
    <div id="reservation-lab-block">
        
        <div class="left-half">
            <h3><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
            <div class="lab-image">
                <img src="<?php echo htmlspecialchars($lab['img_path']); ?>"
                    alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" />
            </div>
        </div>

        <!-- Right half: week calendar, timetables, and buttons -->
        <div class="right-half">
            
            <div class="calendar-block">
                <table>
                    <tr>
                        <?php foreach ($weekDays as $day) { ?>
                            <th><?php echo $day; ?></th>
                        <?php } ?>
                    </tr>
                    <tr>
                        <!-- 
                        BACKEND 
                        Implement calendar
                        using php                
                        -->

                    </tr>
                </table>
            </div>

            
            <div class="timetable-block">
                <table id="timetable">
                    <?php
                    //BACKEND
                    //print timetables
                    //if exists in DB -< unavailable
                    //if not, make available
                    ?>
                </table>
            </div>

            <form action="reserve.php" method="post" id="reservationForm"
                style="display: flex; justify-content: center; gap: 20px;">
                
                <button type="button" id="back-button" onclick="window.location.href='index.php';">Back</button>

                
                <input type="hidden" name="lab_id" value="<?php echo $lab_id; ?>">
                <input type="hidden" name="selected_date" id="selected_date"
                    value="<?php echo $today->format('Y-m-d'); ?>">
                <input type="hidden" name="selected_time" id="selected_time">
                <button type="submit" id="reserve-button" disabled>Reserve</button>
            </form>
        </div>
    </div>

    <script>
        //implemented later:
        //function toggleMenu()
        //function closeMenu()
        
        $(document).ready(function () {
            // Handling date click
            $('.available-date').on('click', function () {
                //BACKEND
                //IMPLEMENT date click
                //using js, ajax?
            });

            // Handling time click
            $(document).on('click', '.available-time', function () {
                //BACKEND
                //IMPLEMENT time click
                //using js, ajax?
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
