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




//Get the percent of payment methods

$payments = $conn->prepare("SELECT 
                    COUNT(CASE WHEN confirmedBookingStatus = '2' AND CBpaymentMethod = 'GCash' THEN 1 END) AS totalPaymentGCash,
                     COUNT(CASE WHEN confirmedBookingStatus = '2' AND CBpaymentMethod = 'Cash' THEN 1 END) AS totalPaymentCash  
                     FROM confirmedBookings
                    ");
$payments->execute();
$paymentsResult = $payments->get_result();
if ($paymentsResult->num_rows > 0) {
    $row = $paymentsResult->fetch_assoc();
    $GCashCount = $row['totalPaymentGCash'];
    $CashCount = $row['totalPaymentCash'];
}

$approvedStatus = 2;
$revenue =  $conn->prepare("SELECT 
                                MONTHNAME(b.startDate) AS month,
                                SUM(cb.CBtotalCost) AS monthlyRevenue 
                            FROM 
                                confirmedBookings cb 
                            JOIN 
                                bookings b ON cb.bookingID = b.bookingID 
                            WHERE 
                                cb.confirmedBookingStatus = ?
                            GROUP BY 
                                month 
                            ORDER  BY 
                                month
                    ");
$revenue->bind_param("i", $approvedStatus);
$revenue->execute();
$revenueResult = $revenue->get_result();

$months = [];
$revenues = [];
if ($revenueResult->num_rows > 0) {
    while ($row = $revenueResult->fetch_assoc()) {
        $months[] = $row['month'];
        $revenues[] = (float) $row['monthlyRevenue'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/revenue.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
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
            <a href="Account/account.php" class="admin">
                <img src="<?= htmlspecialchars($image) ?>" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar">

        <a class="nav-link" href="adminDashboard.php">
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


        <a class="nav-link active" href="#">
            <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
            <h5>Revenue</h5>
        </a>


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


    <div class="wrapper">
        <div class="card">
            <h5 class="card-title">Revenue</h5>
            <div class="card-body">
                <div class="charts">
                    <?php if (!empty($revenues)): ?>
                        <div class="revenue-chart">
                            <canvas id="revenueBar"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="revenueBar">No data available.</div>
                    <?php endif; ?>
                    <?php if (($GCashCount ?? 0) > 0 || ($CashCount ?? 0) > 0): ?>
                        <div class="revenue-chart">
                            <canvas id="revenuePie"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="revenuePie">No data available.</div>
                    <?php endif; ?>
                </div>

                <div class="cards">
                    <div class="display-revenue">

                        <?php
                        $getSales = $conn->prepare("SELECT 
                                        CURDATE() AS Today,
                                        SUM(CASE WHEN cb.confirmedBookingStatus = 2 
                                                    AND DATE(b.startDate) = CURDATE() 
                                                    AND DATE(b.endDate) < CURDATE()  
                                                    THEN cb.CBtotalCost ELSE 0 END) 
                                                    AS totalToday,
                                        SUM(CASE WHEN cb.confirmedBookingStatus = 2 
                                                    AND YEARWEEK(b.startDate, 1) = YEARWEEK(CURDATE(), 1)  
                                                    AND DATE(b.startDate) <= CURDATE() 
                                                    AND DATE(b.endDate) < CURDATE() 
                                                    THEN cb.CBtotalCost ELSE 0 END) 
                                                    AS totalThisWeek,
                                        SUM(CASE WHEN cb.confirmedBookingStatus = 2 
                                                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                                                    AND MONTH(b.startDate) = MONTH(CURDATE()) 
                                                    AND DATE(b.endDate) < CURDATE() 
                                                    THEN cb.CBtotalCost ELSE 0 END) 
                                                    AS totalThisMonth,
                                        SUM(CASE WHEN cb.confirmedBookingStatus = 2 
                                                    THEN cb.CBtotalCost ELSE 0 END) AS totalThisYear                              
                                    FROM bookings b 
                                    JOIN confirmedbookings cb ON b.bookingID = cb.bookingID");
                        $getSales->execute();
                        $getSalesResult = $getSales->get_result();
                        if ($getSalesResult->num_rows > 0) {
                            $data = $getSalesResult->fetch_assoc();
                            // echo '<pre>';
                            // print_r($data);
                            // echo '</pre>';
                            $totalToday = $data['totalToday'];
                            $totalThisWeek = $data['totalThisWeek'];
                            $totalThisMonth = $data['totalThisMonth'];
                            $totalThisYear = $data['totalThisYear'];
                            // $allBookingsMade = $data['allBookingsMade'];
                            $today = $data['Today'];
                        }
                        ?>

                        <div class="form-floating">
                            <input type="text" class="form-control" id="todayRevenue" value="₱ <?= number_format($totalToday, 2) ?>" readonly>
                            <label for="floatingInputValue">Today</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="weekRevenue" value="₱ <?= number_format($totalThisWeek, 2) ?>" readonly>
                            <label for="floatingInputValue">This Week</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="monthRevenue" value="₱ <?= number_format($totalThisMonth, 2) ?>" readonly>
                            <label for="floatingInputValue">This Month</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="yearRevenue" value="₱ <?= number_format($totalThisYear, 2) ?>" readonly>
                            <label for="floatingInputValue">This Year</label>
                        </div>
                    </div>

                    <div class="booking-status">
                        <?php
                        $getBookings = $conn->prepare("SELECT
                                        COUNT(CASE WHEN confirmedBookingStatus = 2 THEN 1 END) AS totalApprovedBookings,
                                        COUNT(CASE WHEN confirmedBookingStatus = 3 THEN 1 END) AS totalRejectedBookings,
                                         COUNT(CASE WHEN bookingStatus = 4 THEN 1 END) AS totalCancelledBookings                                       
                                    FROM bookings b 
                                    JOIN confirmedbookings cb ON b.bookingID = cb.bookingID");
                        $getBookings->execute();
                        $getBookingsResult = $getBookings->get_result();
                        if ($getBookingsResult->num_rows > 0) {
                            $data = $getBookingsResult->fetch_assoc();
                            $totalApprovedBookings = $data['totalApprovedBookings'];
                            $totalRejectedBookings = $data['totalRejectedBookings'];
                            $totalCancelledBookings = $data['totalCancelledBookings'];
                            // $allBookingsMade = $data['allBookingsMade'];
                        }
                        ?>

                        <!-- <div class="form-floating">
                            <input type="text" class="form-control" id="bookingMade" value="<?= htmlspecialchars($allBookingsMade) ?>">
                            <label for="floatingInputValue">All Bookings</label>
                        </div> -->

                        <div class="form-floating">
                            <input type="text" class="form-control" id="bookingMade" value="<?= htmlspecialchars($totalApprovedBookings) ?>" readonly>
                            <label for="floatingInputValue">Approved Bookings</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="bookingMade" value="<?= htmlspecialchars($totalCancelledBookings) ?>" readonly>
                            <label for="floatingInputValue">Cancelled Bookings</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="bookingMade" value="<?= htmlspecialchars($totalRejectedBookings) ?>" readonly>
                            <label for="floatingInputValue">Rejected Bookings</label>
                        </div>

                        <?php
                        $hotelCategoryID = 1;
                        $getOccupiedRates = $conn->prepare("SELECT 
                                        ROUND(
                                            COUNT(CASE WHEN RSAvailabilityID = '2' THEN 1 END) * 100 / COUNT(*), 
                                            2
                                        ) AS occupiedRates
                                    FROM resortamenities
                                    WHERE RScategoryID = ?");
                        $getOccupiedRates->bind_param("i", $hotelCategoryID);
                        $getOccupiedRates->execute();
                        $getOccupiedRatesResult = $getOccupiedRates->get_result();
                        if ($getOccupiedRatesResult->num_rows > 0) {
                            $data = $getOccupiedRatesResult->fetch_assoc();
                            $occupiedRates = $data['occupiedRates'];
                        }
                        ?>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="occupied" value="<?= htmlspecialchars($occupiedRates) ?>%" readonly>
                            <label for="floatingInputValue">Occupancy Rates</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>


    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="path/to/chartjs/dist/chart.umd.js"></script> -->

    <script>
        const bar = document.getElementById("revenueBar").getContext('2d');

        const myBarChart = new Chart(bar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?= json_encode($revenues) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const pie = document.getElementById('revenuePie').getContext('2d');

        const myPieChart = new Chart(pie, {
            type: 'pie',
            data: {
                labels: ['Gcash', 'Cash'],
                datasets: [{
                    label: 'Payment Methods',
                    data: <?= json_encode([$GCashCount ?? 0, $CashCount ?? 0]) ?>,
                    backgroundColor: [
                        'rgba(30, 134, 232, 0.6)',
                        'rgba(129, 204, 196, 0.6)'
                        // 'rgba(99, 99, 99, 0.6)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Payment Methods'
                    }
                }
            }
        });
    </script>
</body>

</html>