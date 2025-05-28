<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Now - Event Packages</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/packages.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="header">
        <div class="backIconContainer">
            <a href="bookNow.php#event-page">
                <img src="../../Assets/Images/Icon/whiteArrow.png" alt="Go back" class="backArrow">
            </a>
        </div>

        <h1 class="title">EVENT PACKAGES</h1>
    </div>

    <div class="pavilionPicContainer">
        <img src="../../Assets/Images/PackagesPhotos/pavilion.png" alt="Mamyr Pavilion" class="pavilion">
    </div>

    <form action="../../Function/Booking/addPackage.php" method="POST">
        <div class="package1">
            <div class="p1ImgContainer">
                <img src="../../Assets/Images/PackagesPhotos/weddingPicture.jpg" alt="Wedding Package Picture" class="p1Img">
            </div>

            <div class="p1Text">
                <h2 class="p1Title">Wedding Package</h2>
                <h5 class="p1Inclusion">Inclusions:</h5>
                <ul class="p1ListOfInclusions">
                    <li>Maximum of 5 hours (2,000 pesos extension per hour)</li>
                    <li>Good for 200 guests (maximum of 350 guest)</li>
                    <li>Elegant and fully air-condition function Hall</li>
                    <li>Catering - Inludes 4 dishes, decoration for the hall and presedential table, simple lights and
                        sounds</li>
                    <li>One (1) free Air-condition Room</li>
                    <li>Powder Room/Toilets for male and female</li>
                </ul>

                <h3 class="p1Price">&#8369;140,000</h3>
            </div>

            <div class="mt-auto">
                <button type="submit" class="btn custom-btn" name="weddingPackage" id="p1AddPckg">Add Package</button>
            </div>
        </div>

    </form>

</body>

</html>