<?php
//ob_start();
session_start();
//var_dump($_SESSION['username']); 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

require 'db_connect.php';

// Query to get all labs from the database
//$sql = "SELECT lab_id, lab_name, address FROM labs";
//$result = $conn->query($sql);


//add logic: only show the lab related to the user
$sql = "SELECT lab_id FROM lab_stu WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$stmt->bind_result($lab_id);

$lab_ids = [];

while ($stmt->fetch()) {
    $lab_ids[] = $lab_id;
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Reservation</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/index.css">
    <!-- importing styles for icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!-- Header -->
    <?php include 'header.php'; ?>


    <!-- Labs Block -->
    <div id="book-lab-block">
        <?php
        /*if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { // Printing the blocks for labs
                echo '
            <div class="lab-block">
                <div class="lab-info">
                    <h3>' . htmlspecialchars($row["lab_name"]) . '</h3>
                    <p>' . htmlspecialchars($row["address"]) . '</p>
                </div>
                <button class="book-btn" onclick="window.location.href=\'reservation.php?lab_id=' . $row["lab_id"] . '\'">Go to Reserve</button>
            </div>';
            }
        } else {
            echo '<p>No labs available.</p>';
        }*/
        if (!empty($lab_ids)) {
            $in = str_repeat('?,', count($lab_ids) - 1) . '?';
            $sql = "SELECT lab_id, lab_name, address FROM labs WHERE lab_id IN ($in)";
            $stmt = $conn->prepare($sql);
        
            $types = str_repeat('s', count($lab_ids)); 
            $stmt->bind_param($types, ...$lab_ids); 
            $stmt->execute();
        
            $stmt->bind_result($lab_id, $lab_name, $address);
        
            $lab_found = false;
            while ($stmt->fetch()) {
                $lab_found = true;
                echo '
                    <div class="lab-block">
                        <div class="lab-info">
                            <h3>' . htmlspecialchars($lab_name) . '</h3>
                            <p>' . htmlspecialchars($address) . '</p>
                        </div>
                        <button class="book-btn" onclick="window.location.href=\'reservation.php?lab_id=' . $lab_id . '\'">Go to Reserve</button>
                    </div>';
            }
        
            if (!$lab_found) {
                echo '<p>No labs available.</p>';
            }
        
            $stmt->close();
        } else {
            echo '<p>No labs available.</p>';
        }
        
        $conn->close();
        ?>
    </div>
    <script>
        /*function toggleMenu() {
            var menu = document.getElementById('userMenu');
            menu.classList.toggle('active');
        }

        function closeMenu() {
            var menu = document.getElementById('userMenu');
            menu.classList.remove('active');
        }

        document.addEventListener('click', function(event) {
            var menu = document.getElementById('userMenu');
            var icon = document.querySelector('.fa-user');
        
            if (!menu.contains(event.target) && !icon.contains(event.target)) {
                menu.classList.remove('active');
            }
        });*/
    </script>

</body>

</html>