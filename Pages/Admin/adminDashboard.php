<?php
require '../../Config/dbcon.php';

session_start();
require_once '../../Function/sessionFunction.php';

checkSessionTimeout($timeout = 3600);


$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}


$approvedStatus = 2;
$bookingTypes =  $conn->prepare("SELECT 
                                b.bookingType,
                                COUNT(*) AS totalBookings
                            FROM 
                                confirmedBookings cb 
                            JOIN 
                                bookings b ON cb.bookingID = b.bookingID 
                            WHERE 
                                cb.paymentApprovalStatus = ?
                                AND YEARWEEK(b.startDate, 1) = YEARWEEK(CURDATE(), 1)
                            GROUP BY 
                                b.bookingType 
                    ");
$bookingTypes->bind_param("i", $approvedStatus);
$bookingTypes->execute();
$bookingTypesResult = $bookingTypes->get_result();

$bookingTypeName = [];
$bookingTypeCount = [];
if ($bookingTypesResult->num_rows > 0) {
    while ($row = $bookingTypesResult->fetch_assoc()) {
        $bookingTypeName[] = $row['bookingType'];
        $bookingTypeCount[] = (float) $row['totalBookings'];
    }
}

$revenue =  $conn->prepare("SELECT 
                                CONCAT(
                                    'Week ', WEEK(b.startDate, 1),
                                    ' (',
                                    DATE_FORMAT(DATE_SUB(b.startDate, INTERVAL WEEKDAY(b.startDate) DAY), '%b %e'),
                                    ' - ',
                                    DATE_FORMAT(DATE_ADD(b.startDate, INTERVAL (6 - WEEKDAY(b.startDate)) DAY), '%b %e'),
                                    ')'
                                ) AS weekName,

                                SUM(CASE WHEN WEEKDAY(b.startDate) = 0 THEN cb.confirmedFinalBill ELSE 0 END) AS Mon,
                                SUM(CASE WHEN WEEKDAY(b.startDate) = 1 THEN cb.confirmedFinalBill ELSE 0 END) AS Tue,
                                SUM(CASE WHEN WEEKDAY(b.startDate) = 2 THEN cb.confirmedFinalBill ELSE 0 END) AS Wed,
                                SUM(CASE WHEN WEEKDAY(b.startDate) = 3 THEN cb.confirmedFinalBill ELSE 0 END) AS Thu,
                                SUM(CASE WHEN WEEKDAY(b.startDate) = 4 THEN cb.confirmedFinalBill ELSE 0 END) AS Fri,
                                SUM(CASE WHEN WEEKDAY(b.startDate) = 5 THEN cb.confirmedFinalBill ELSE 0 END) AS Sat,
                                SUM(CASE WHEN WEEKDAY(b.startDate) = 6 THEN cb.confirmedFinalBill ELSE 0 END) AS Sun

                            FROM 
                                confirmedBookings cb
                            JOIN 
                                bookings b ON cb.bookingID = b.bookingID
                            WHERE 
                                cb.paymentApprovalStatus = ?
                                AND YEARWEEK(b.startDate, 1) = YEARWEEK(CURDATE(), 1)
                            GROUP BY 
                                weekName
                            ORDER BY 
                                MIN(b.startDate)
                    ");
$revenue->bind_param("i", $approvedStatus);
$revenue->execute();
$revenueResult = $revenue->get_result();

$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$revenues = [];
$weekName = "";
if ($revenueResult->num_rows > 0) {
    $row = $revenueResult->fetch_assoc();
    $weekName = $row['weekName'];
    $revenues[] = (float) $row['Mon'];
    $revenues[] = (float) $row['Tue'];
    $revenues[] = (float) $row['Wed'];
    $revenues[] = (float) $row['Thu'];
    $revenues[] = (float) $row['Fri'];
    $revenues[] = (float) $row['Sat'];
    $revenues[] = (float) $row['Sun'];
}

$hotel = 1;
// $availabilityCount = [];
// $availabilityName = ['Available', 'Maintenance', 'Occupied', 'Private'];
$availabilityQuery = $conn->prepare("SELECT
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 1 THEN 1 END) AS availableCount,
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 2 THEN 1 END) AS occupiedCount,
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 3 THEN 1 END) AS maintenanceCount,
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 4 THEN 1 END) AS privateCount,
                                        sa.availabilityName
                                    FROM (
                                        SELECT RServiceName, RSAvailabilityID
                                        FROM resortAmenities
                                        WHERE RScategoryID = ?
                                        GROUP BY RServiceName
                                    ) AS uniqueRooms
                                    JOIN serviceAvailability sa ON uniqueRooms.RSAvailabilityID = sa.availabilityID
                                    GROUP BY sa.availabilityName
                                    ");
$availabilityQuery->bind_param("i", $hotel);
$availabilityQuery->execute();
$availabilityResult = $availabilityQuery->get_result();
if ($availabilityResult->num_rows > 0) {
    $availabilityCount = [];
    $availabilityName = ['Available', 'Maintenance', 'Occupied', 'Private'];
    $availabilityCount = array_fill_keys($availabilityName, 0);

    while ($data = $availabilityResult->fetch_assoc()) {
        $name = $data['availabilityName'];
        if ($name === 'Available') {
            $availabilityCount['Available'] = (int)$data['availableCount'];
        } elseif ($name === 'Maintenance') {
            $availabilityCount['Maintenance'] = (int)$data['maintenanceCount'];
        } elseif ($name === 'Occupied') {
            $availabilityCount['Occupied'] = (int)$data['occupiedCount'];
        } elseif ($name === 'Private') {
            $availabilityCount['Private'] = (int)$data['privateCount'];
        }
    }

    $availabilityCount = array_values($availabilityCount);

    // print_r($availabilityName);
    // print_r($availabilityCount);
}
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
            <!-- Get notification -->
            <?php

            $receiver = 'Admin';
            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE receiver = ? AND is_read = 0");
            $getNotifications->bind_param("s", $receiver);
            $getNotifications->execute();
            $getNotificationsResult = $getNotifications->get_result();
            if ($getNotificationsResult->num_rows > 0) {
                $counter = 0;
                $notificationsArray = [];
                $color = [];
                $notificationIDs = [];
                while ($notifications = $getNotificationsResult->fetch_assoc()) {
                    $is_readValue = $notifications['is_read'];
                    $notificationIDs[] = $notifications['notificationID'];
                    if ($is_readValue === 0) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "rgb(247, 213, 176, .5)";
                    } elseif ($is_readValue === 1) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "white";
                    }
                }
            }
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>

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
                $getProfile = $conn->prepare("SELECT firstName,userProfile FROM users WHERE userID = ? AND userRole = ?");
                $getProfile->bind_param("ii", $userID, $userRole);
                $getProfile->execute();
                $getProfileResult = $getProfile->get_result();
                if ($getProfileResult->num_rows > 0) {
                    $data = $getProfileResult->fetch_assoc();
                    $firstName = $data['firstName'];
                    $imageData = $data['userProfile'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $imageData);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }
            ?>
            <h5 class="adminTitle"><?= ucfirst($firstName) ?></h5>
            <a href="../Account/account.php" class="admin">
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

        <a class="nav-link" href="services.php">
            <img src="../../Assets/Images/Icon/servicesAdminNav.png" alt="Services">
            <h5>Services</h5>
        </a>


        <!-- <a href="revenue.php" class="nav-link">
            <img src="../../../Assets/Images/Icon/revenue.png" alt="" class="sidebar-icon">
            <h5>Revenue</h5>
        </a> -->


        <a class="nav-link" href="transaction.php">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <a class="nav-link" href="revenue.php">
            <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
            <h5>Revenue</h5>
        </a>


        <a class="nav-link" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="editWebsite/editWebsite.php">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

    </nav>


    <?php
    $weeklyReport = $conn->prepare("SELECT 
                                        -- Total guests 
                                        SUM(CASE 
                                            WHEN cb.paymentApprovalStatus = 2 
                                            THEN b.guestCount 
                                            ELSE 0 
                                            END) AS totalGuests,

                                        -- Total revenue
                                        SUM(CASE 
                                                WHEN cb.paymentApprovalStatus = 2 THEN b.totalCost 
                                                ELSE 0 
                                                END) AS totalRevenueThisWeek,

                                        -- Check-outs this week
                                        COUNT(CASE 
                                                WHEN b.endDate >= weekStart AND b.endDate < weekEnd AND cb.paymentApprovalStatus = 2 
                                                THEN 1 
                                                ELSE NULL 
                                                END) AS checkOutsThisWeek,

                                        -- Check-ins this week
                                        COUNT(CASE 
                                                WHEN b.startDate >= weekStart AND b.startDate < weekEnd AND cb.paymentApprovalStatus = 2 
                                                THEN 1 
                                                ELSE NULL 
                                                END) AS checkInsThisWeek,
                                        -- Check-ins this week
                                        COUNT(CASE 
                                                WHEN b.bookingType = 'Event' AND
                                                cb.paymentApprovalStatus = 2 
                                                THEN 1 
                                                ELSE NULL 
                                                END) AS eventBooking,


                                        --  All bookings this week
                                        COUNT(DISTINCT b.bookingID) AS bookingsThisWeek

                                    FROM bookings b
                                    LEFT JOIN confirmedBookings cb ON b.bookingID = cb.bookingID
                                    CROSS JOIN (
                                        SELECT
                                            DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AS weekStart,
                                            DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 7 DAY) AS weekEnd
                                            ) AS weekRange
                                        WHERE 
                                            (
                                            b.startDate >= weekStart AND b.startDate < weekEnd
                                        OR
                                            b.endDate >= weekStart AND b.endDate < weekEnd)
                                            ");
    $weeklyReport->execute();
    $weeklyReportResult = $weeklyReport->get_result();
    if ($weeklyReportResult->num_rows > 0) {
        $data = $weeklyReportResult->fetch_assoc();

        $bookingsThisWeek = $data['bookingsThisWeek'];
        $checkOutsThisWeek = $data['checkOutsThisWeek'];
        $checkInsThisWeek = $data['checkInsThisWeek'];
        $totalGuests = $data['totalGuests'];
        $totalRevenueThisWeek = $data['totalRevenueThisWeek'];
        $eventBooking = $data['eventBooking'];
    }
    ?>
    <h1 class="dashboardTitle">Weekly Status</h1>
    <div class="container-fluid" id="contentsCF">

        <div class="leftSection">
            <div class="trend-cards">
                <div class="card">
                    <div class="card-header ">
                        All Bookings
                    </div>

                    <div class="card-body">
                        <h2 class="newBookingTotal"><?= $bookingsThisWeek ?></h2>
                    </div>

                    <!-- <h6 class="card-footer">This Week</h6> -->
                </div>

                <div class="card">
                    <div class="card-header ">
                        Event Bookings
                    </div>

                    <div class="card-body">
                        <h2 class="newBookingTotal"><?= $eventBooking ?></h2>
                    </div>

                    <!-- <h6 class="card-footer">This Week</h6> -->
                </div>

                <div class="card">
                    <div class="card-header ">
                        Total Guest
                    </div>

                    <div class="card-body">
                        <h2 class="totalGuest"><?= $totalGuests ?></h2>
                    </div>

                    <!-- <h6 class="card-footer">This Week</h6> -->
                </div>


                <div class="card">
                    <div class="card-header ">
                        Check In
                    </div>

                    <div class="card-body">
                        <h2 class="checkInTotal"><?= $checkInsThisWeek ?></h2>
                    </div>

                    <!-- <h6 class="card-footer">This Week</h6> -->
                </div>

                <div class="card">
                    <div class="card-header ">
                        Check Out
                    </div>

                    <div class="card-body">
                        <h2 class="checkOutTotal"><?= $checkOutsThisWeek ?></h2>
                    </div>

                    <!-- <h6 class="card-footer">This Week</h6> -->
                </div>

                <div class="card">
                    <div class="card-header ">
                        Revenue
                    </div>

                    <div class="card-body">
                        <h2 class="revenueTotal">₱<?= number_format($totalRevenueThisWeek, 2) ?></h2>
                    </div>

                    <!-- <h6 class="card-footer">This Week</h6> -->
                </div>

            </div>
            <div class="card">
                <div class="card-header ">
                    Room Availability
                </div>
                <div class="card-body availabilityGraph">
                    <div class="roomAvailabilityGraph">
                        <canvas id="availabilityGraph"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="rightSection">



            <div class="card graph" id="revenueGraphCard">


                <div class="revenueGraphContainer">
                    <h5 class=" revTitle">REVENUE</h5>
                    <?php if (!empty($revenues)): ?>
                        <div class="revenue-chart">
                            <canvas id="revenueBar"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="revenue-chart">
                            <canvas id="revenueBar"></canvas>
                        </div>
                        <!-- Change this div -->
                        <!-- <div class="revenueImage"><img src="../../Assets/Images/revenueGraph.png" alt=""></div> -->
                    <?php endif; ?>
                </div>
            </div>

            <div class="card graph" id="reservationTrends">
                <div class="card-header ">

                    <h5>Reservation Trends</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($bookingTypeCount)): ?>
                        <div class="revenue-chart">
                            <canvas id="reservationTrendsBar"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="revenue-chart">
                            <canvas id="reservationTrendsBar"></canvas>
                        </div>
                        <!-- Change this div -->
                        <!-- <div class="ReservationTrendsGraph">No data available.</div> -->
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>


    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <?php if (!empty($notificationsArray)): ?>
                        <ul class="list-group list-group-flush ">
                            <?php foreach ($notificationsArray as $index => $message):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                                <li class="list-group-item mb-2 notification-item"
                                    data-id="<?= htmlspecialchars($notificationID) ?>"
                                    style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                                    <?= htmlspecialchars($message) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-3 text-muted">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>




    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <!-- Notification Ajax -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.querySelector('.notification-container .badge');

            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationID = this.dataset.id;

                    fetch('../../Function/notificationFunction.php', {
                            method: 'POST',
                            headers: {
                                'Content-type': 'application/x-www-form-urlencoded'
                            },
                            body: 'notificationID=' + encodeURIComponent(notificationID)
                        })
                        .then(response => response.text())
                        .then(data => {

                            this.style.transition = 'background-color 0.3s ease';
                            this.style.backgroundColor = 'white';


                            if (badge) {
                                let currentCount = parseInt(badge.textContent, 10);

                                if (currentCount > 1) {
                                    badge.textContent = currentCount - 1;
                                } else {
                                    badge.remove();
                                }
                            }
                        });
                });
            });
        });
    </script>

    <!-- Display if no available data -->
    <script>
        Chart.register({
            id: 'noDataPlugin',
            beforeDraw(chart) {
                const dataset = chart.data.datasets[0];
                const hasData = dataset && dataset.data && dataset.data.some(value => value > 0);

                if (!hasData) {
                    const ctx = chart.ctx;
                    const {
                        width,
                        height
                    } = chart;

                    chart.clear();

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '20px Times New Roman';
                    ctx.fillStyle = 'gray';
                    ctx.fillText('No available data', width / 2, height / 2);
                    ctx.restore();
                }
            }
        });
    </script>


    <script>
        //Reservation Trends Bar
        const reservationTrendsBar = document.getElementById("reservationTrendsBar").getContext('2d');

        const reservationTrendsChart = new Chart(reservationTrendsBar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bookingTypeName) ?>,
                datasets: [{
                    data: <?= json_encode($bookingTypeCount) ?>,
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.5)',
                        'rgba(255, 193, 7, 0.5)',
                        'rgba(40, 167, 69, 0.5)',
                        'rgba(220, 53, 69, 0.5)'
                    ],
                    borderColor: [
                        'rgba(0, 123, 255, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],

                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>


    <script>
        // availabilityGraph
        const availabilityGraph = document.getElementById("availabilityGraph").getContext('2d');

        const availabilityChart = new Chart(availabilityGraph, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($availabilityName) ?>,
                datasets: [{
                    data: <?= json_encode($availabilityCount) ?>,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.5)', // Available
                        'rgba(255, 193, 7, 0.5)', // Maintenance
                        'rgba(220, 53, 69, 0.5)', // Occupied
                        'rgba(0, 123, 255, 0.5)' // Private
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(0, 123, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '60%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>


    <script>
        //Revenue Bar
        const revenueBar = document.getElementById("revenueBar").getContext('2d');

        const revenueChart = new Chart(revenueBar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($days) ?>,
                datasets: [{
                    label: <?= json_encode($weekName) ?>,
                    data: <?= json_encode($revenues) ?>,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.5)', // Green
                        'rgba(255, 193, 7, 0.5)', // Yellow
                        'rgba(220, 53, 69, 0.5)', // Red
                        'rgba(0, 123, 255, 0.5)', // Blue
                        'rgba(23, 162, 184, 0.5)', // Cyan
                        'rgba(108, 117, 125, 0.5)', // Gray
                        'rgba(255, 99, 132, 0.5)', // Pink
                        'rgba(153, 102, 255, 0.5)', // Purple
                        'rgba(255, 159, 64, 0.5)', // Orange
                        'rgba(75, 192, 192, 0.5)', // Teal
                        'rgba(201, 203, 207, 0.5)', // Light Gray
                        'rgba(54, 162, 235, 0.5)' // Light Blue
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(108, 117, 125, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(201, 203, 207, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'line',
                            boxWidth: 0,
                            font: {
                                size: 16
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }

        });
    </script>
</body>

</html>