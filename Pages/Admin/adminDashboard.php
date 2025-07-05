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

    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <a href="#" class="notifs">
                <img src="../../Assets/Images/Icon/notification.png" alt="Notification Icon">
            </a>
            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
            </a>
            <?php
            if ($userRole == 3) {
                $admin = "Admin";
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }

            if ($admin === "Admin") {
                $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $firstName = $row['firstName'];
                    $profile = $row['userProfile'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $profile);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
                } else {
                    $firstName = 'None';
                }
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }
            ?>
            <h5 class="adminTitle"><?= ucfirst($firstName) ?></h5>
            <a href="Account/account.php" class="admin">
                <img src="<?= htmlspecialchars($image) ?>" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar">

        <a class="nav-link active" href="adminDashboard.php">
            <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
            <h5>Dashboard</h5>
        </a>

        <a class="nav-link" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>


        <a class="nav-link" href="roomList.php">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link" href="transaction.php">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <!-- <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
                <h5>Revenue</h5>
            </a> -->


        <a class="nav-link" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="#">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

    </nav>

    <div class="container-fluid" id="contentsCF">
        <div class="leftSection">

            <div class="bookingSummary">

                <div class="card trend-cards" id="newBookings">
                    <div class="card-header ">
                        New Bookings
                    </div>

                    <div class="card-body">
                        <h2 class="newBookingTotal">15</h2>
                    </div>

                    <h6 class="newBookingDate">This Week</h6>
                </div>

                <div class="card trend-cards" id="checkIn">
                    <div class="card-header ">
                        Check In
                    </div>

                    <div class="card-body">
                        <h2 class="checkInTotal">15</h2>
                    </div>

                    <h6 class="checkInDate">This Week</h6>
                </div>

                <div class="card trend-cards" id="checkOut">
                    <div class="card-header ">
                        Check Out
                    </div>

                    <div class="card-body">
                        <h2 class="checkOutTotal">15</h2>
                    </div>

                    <h6 class="checkOutDate">This Week</h6>
                </div>

                <div class="card trend-cards " id="revenue">
                    <div class="card-header ">
                        Revenue
                    </div>

                    <div class="card-body">
                        <h2 class="revenueTotal">&#8369;200,000</h2>
                    </div>

                    <h6 class="checkOutDate">This Week</h6>
                </div>

                <div class="ReservationTrendsContainer">
                    <div class="card" id="sched">
                        <div class="card-header ">
                            Reservation Trends
                        </div>
                        <div class="card-body">
                            <img src="../../Assets/Images/AdminImages/DashboardImages/graph.png" alt=""
                                class="ReservationTrendsGraph">
                        </div>
                    </div>
                    <div class="salesReportBtn">
                        <a href="salesReport.php" class="btn btn-light">Sales Report</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="rightSection">


            <div class="card" id="revenueGraphCard">
                <div class="revenueGraphContainer">
                    <h5 class=" revTitle">REVENUE</h5>
                    <img src="../../Assets/Images/AdminImages/DashboardImages/graph.png" alt="" class="revenueGraph">
                </div>
            </div>

            <div class="rightSectionBottom">
                <div class="card" id="schedule">
                    <div class="card-header ">
                        Room Availability
                    </div>
                    <div class="card-body">
                        <div class="roomAvailabilityGraph">
                            <img src="../../Assets/Images/AdminImages/DashboardImages/roomAvailabilityGraph.png" alt=""
                                class="graphRA">
                        </div>

                        <div class="roomAvailabilityLegend">

                            <span class="occupied bg-danger">Occupied</span>
                            <span class="available bg-success">Available</span>
                            <span class="maintenance bg-warning">Maintenance</span>
                        </div>
                    </div>
                </div>

                <div class="card" id="sched">
                    <div class="card-header ">
                        Overall Rating
                    </div>

                    <div class="card-body">

                        <div class="totalRating">
                            <span class="totalRatingSpan bg-primary">4.3/5</span>
                            <h5 class="card-title">Reviews</h5>
                        </div>


                        <div class="ratingLabelContainer">

                            <div class="facilitiesRating">
                                <h5 class="ratingLabel">Facilities</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-info" id="facilitiesRating" role="progressbar"
                                        style="width: 70%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <h5 class="facilityRatingNumber">4.6</h5>
                            </div>

                            <div class="cleanlinessRating">
                                <h5 class="ratingLabel">Cleanliness</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-info" id="cleanlinessRating" role="progressbar"
                                        style="width: 50%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <h5 class="cleanlinessRatingNumber">4.5</h5>
                            </div>

                            <div class="servicesRating">
                                <h5 class="ratingLabel">Services</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-info" id="servicesRating" role="progressbar"
                                        style="width: 95%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <h5 class="servicesRatingNumber">4.8</h5>
                            </div>

                            <div class="comfortRating">
                                <h5 class="ratingLabel">Comfort</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-info" id="comfortRating" role="progressbar"
                                        style="width: 45%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <h5 class="comfortRatingNumber">4.2</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>