<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$reservation_id = $_POST['reservation_id']; 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <title>QR Code for Reservation</title>
    <style>
        #qr-code {
            display: flex;
            justify-content: center;
            align-items: center; 
            margin-top: 20px;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
        .btn-container button {
            padding: 10px 20px;
            background-color: #508e0d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-container button:hover {
            background-color: #3d6b0b;
        }
    </style>
</head>
<body>

    <div id="qr-code"></div>

    <div class="btn-container">
        <form action="profile.php" method="get">
            <button type="submit">Back to Profile</button>
        </form>
    </div>
    
    <script>
        var qrcodeContainer = document.getElementById("qr-code");
        var reservationId = "<?php echo $reservation_id; ?>";
        var qrcode = new QRCode(qrcodeContainer, {
        text: reservationId, 
        width: 200, 
        height: 200, 
        });

        qrcodeContainer.style.backgroundColor = "#ffffff"; 
        qrcodeContainer.style.color = "#000000"; 
    </script>

</body>
</html>
