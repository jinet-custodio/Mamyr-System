<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
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
$userType = $_SESSION['userType'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/adminDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <a href="#" class="navbar-brand" id="dashboard">DASHBOARD</a>
        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="../Pages/dashboard.php">
                        <img src="../../Assets/Images/Icon/notification.png" alt="home icon">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="navbar-brand" href="#">
                        Mamyr Admin
                        <img src="../../Assets/Images/Icon/profile.png" alt="home icon">
                    </a>
                </li>
                </li>
            </ul>
        </div>
    </nav>
    <main>
        <div class="selection">
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/Dashboard.png" alt="">
                <h5>Dashboard</h5>
            </div>
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/uim-schedule.png" alt="">
                <h5>Bookings</h5>
            </div>
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/Hotel.png" alt="">
                <h5>Rooms</h5>
            </div>
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/Credit card.png" alt="">
                <h5>Payments</h5>
            </div>
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/Profits.png" alt="">
                <h5>Revenue</h5>
            </div>
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/Friend request.png" alt="">
                <h5>Requests</h5>
            </div>
            <div class="selectionItem">
                <img src="../../Assets/Images/Icon/Edit Button.png" alt="">
                <h5>Edit Website</h5>
            </div>
        </div>
    </main>
</body>

</html>