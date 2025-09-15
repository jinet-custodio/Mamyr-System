<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}


//Get the percent of payment methods

$payments = $conn->prepare("SELECT 
                    COUNT(CASE WHEN cb.paymentApprovalStatus = '2' AND b.paymentMethod = 'GCash' 
                        AND YEAR(b.startDate) = YEAR(CURDATE())  
                        AND DATE(b.endDate) < CURDATE()  
                        THEN 1 END) 
                        AS totalPaymentGCash,
                     COUNT(CASE WHEN cb.paymentApprovalStatus = '2' AND b.paymentMethod = 'Cash'
                        AND YEAR(b.startDate) = YEAR(CURDATE())  
                        AND DATE(b.endDate) < CURDATE()
                        THEN 1 END) AS totalPaymentCash  
                     FROM confirmedbooking cb
                     LEFT JOIN booking b ON cb.bookingID = b.bookingID
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
                                SUM(cb.confirmedFinalBill) AS monthlyRevenue 
                            FROM 
                                confirmedbooking cb 
                            JOIN 
                                booking b ON cb.bookingID = b.bookingID 
                            WHERE 
                                cb.paymentApprovalStatus = ?
                                AND YEAR(b.startDate) = YEAR(CURDATE())  
                                AND DATE(b.endDate) < CURDATE()        
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
    <link rel="stylesheet" href="../../Assets/CSS/Admin/navbar.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            $getNotifications = $conn->prepare("SELECT * FROM notification WHERE receiver = ? AND is_read = 0");
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
                $getProfile = $conn->prepare("SELECT firstName,userProfile FROM user WHERE userID = ? AND userRole = ?");
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

    <nav class="navbar navbar-expand-lg" id="navbar">
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav w-100 me-10 d-flex justify-content-around px-2" id="navUL">

                <li class="nav-item">
                    <a class="nav-link" href="adminDashboard.php">
                        <i class="fa-solid fa-grip navbar-icon"></i>
                        <h5>Dashboard</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="booking.php">
                        <i class="fa-solid fa-calendar-days navbar-icon"></i>
                        <h5>Bookings</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="roomList.php">
                        <i class="fa-solid fa-hotel navbar-icon"></i>
                        <h5>Rooms</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="services.php">
                        <i class="fa-solid fa-bell-concierge navbar-icon"></i>
                        <h5>Services</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="transaction.php">
                        <i class="fa-solid fa-credit-card navbar-icon"></i>
                        <h5>Payments</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="revenue.php">
                        <i class="fa-solid fa-money-bill-trend-up navbar-icon"></i>
                        <h5>Revenue</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="displayPartnership.php">
                        <i class="fa-solid fa-handshake navbar-icon"></i>
                        <h5>Partnerships</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="editWebsite/editWebsite.php">
                        <i class="fa-solid fa-pen-to-square navbar-icon"></i>
                        <h5>Edit Website</h5>
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <a href="../../Function/Admin/logout.php" class="btn btn-danger" id="logOutBtn">
                        Log Out
                    </a>
                </li>
            </ul>
        </div>
    </nav>


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

    <div class="wrapper mb-5">
        <div class="card">
            <h5 class="card-title">Revenue</h5>
            <div class="card-body">
                <div class="charts">
                    <?php if (!empty($revenues)): ?>
                        <div class="revenue-chart">
                            <canvas id="revenueBar"></canvas>
                        </div>
                    <?php else: ?>
                        <!-- <div class="revenueBar">No data available.</div> -->
                        <div class="revenue-chart">
                            <canvas id="revenueBar"></canvas>
                        </div>
                    <?php endif; ?>
                    <?php if (($GCashCount ?? 0) > 0 || ($CashCount ?? 0) > 0): ?>
                        <div class="revenue-chart">
                            <canvas id="revenuePie"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="revenue-chart">
                            <canvas id="revenuePie"></canvas>
                        </div>
                        <!-- <div class="revenuePie">No data available.</div> -->
                    <?php endif; ?>
                </div>
                <div class="salesReportBtn">
                    <a href="salesReport.php" class="btn btn-primary w-50">Sales Report</a>
                </div>
                <div class="cards">
                    <div class="display-revenue">

                        <?php
                        $getSales = $conn->prepare("SELECT 
                                        CURDATE() AS Today,
                                        SUM(CASE WHEN cb.paymentApprovalStatus = 2 
                                                    AND DATE(b.startDate) = CURDATE() 
                                                    AND DATE(b.endDate) < CURDATE()  
                                                    THEN cb.confirmedFinalBill ELSE 0 END) 
                                                    AS totalToday,
                                        SUM(CASE WHEN cb.paymentApprovalStatus = 2 
                                                    AND YEARWEEK(b.startDate, 1) = YEARWEEK(CURDATE(), 1)  
                                                    AND DATE(b.startDate) <= CURDATE() 
                                                    AND DATE(b.endDate) < CURDATE() 
                                                    THEN cb.confirmedFinalBill ELSE 0 END) 
                                                    AS totalThisWeek,
                                        SUM(CASE WHEN cb.paymentApprovalStatus = 2 
                                                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                                                    AND MONTH(b.startDate) = MONTH(CURDATE()) 
                                                    AND DATE(b.endDate) < CURDATE() 
                                                    THEN cb.confirmedFinalBill ELSE 0 END) 
                                                    AS totalThisMonth,
                                        SUM(CASE WHEN cb.paymentApprovalStatus = 2 
                                                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                                                    AND DATE(b.endDate) < CURDATE() 
                                                    THEN cb.confirmedFinalBill ELSE 0 END) AS totalThisYear                              
                                    FROM booking b 
                                    JOIN confirmedbooking cb ON b.bookingID = cb.bookingID");
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
                            <input type="text" class="form-control" id="todayRevenue"
                                value="₱ <?= number_format($totalToday, 2) ?>" readonly>
                            <label for="floatingInputValue">Today</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="weekRevenue"
                                value="₱ <?= number_format($totalThisWeek, 2) ?>" readonly>
                            <label for="floatingInputValue">This Week</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="monthRevenue"
                                value="₱ <?= number_format($totalThisMonth, 2) ?>" readonly>
                            <label for="floatingInputValue">This Month</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="yearRevenue"
                                value="₱ <?= number_format($totalThisYear, 2) ?>" readonly>
                            <label for="floatingInputValue">This Year</label>
                        </div>
                    </div>

                    <div class="booking-status">
                        <?php
                        $getBookings = $conn->prepare("SELECT
                                        COUNT(CASE WHEN paymentApprovalStatus = 2 THEN 1 END) AS totalApprovedBookings,
                                        COUNT(CASE WHEN paymentApprovalStatus = 3 THEN 1 END) AS totalRejectedBookings,
                                         COUNT(CASE WHEN bookingStatus = 4 THEN 1 END) AS totalCancelledBookings                                       
                                    FROM booking b 
                                    JOIN confirmedbooking cb ON b.bookingID = cb.bookingID");
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
                            <input type="text" class="form-control" id="bookingMade"
                                value="<?= htmlspecialchars($totalApprovedBookings) ?>" readonly>
                            <label for="floatingInputValue">Approved Bookings</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="bookingMade"
                                value="<?= htmlspecialchars($totalCancelledBookings) ?>" readonly>
                            <label for="floatingInputValue">Cancelled Bookings</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="bookingMade"
                                value="<?= htmlspecialchars($totalRejectedBookings) ?>" readonly>
                            <label for="floatingInputValue">Rejected Bookings</label>
                        </div>

                        <?php
                        $hotelCategoryID = 1;
                        $getOccupiedRates = $conn->prepare("SELECT 
                                        ROUND(
                                            COUNT(CASE WHEN RSAvailabilityID = '2' THEN 1 END) * 100 / COUNT(*), 
                                            2
                                        ) AS occupiedRates
                                    FROM resortamenity
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
                            <input type="text" class="form-control" id="occupied"
                                value="<?= htmlspecialchars($occupiedRates) ?>%" readonly>
                            <label for="floatingInputValue">Occupancy Rates</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

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


    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="path/to/chartjs/dist/chart.umd.js"></script> -->

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
            },
            plugins: ['noDataPlugin']
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
            },
            plugins: ['noDataPlugin']
        });
    </script>

    <!-- Responsive Navbar -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const icons = document.querySelectorAll('.navbar-icon');
            const navbarUL = document.getElementById('navUL');
            const nav = document.getElementById('navbar')

            function handleResponsiveNavbar() {
                if (window.innerWidth <= 991.98) {
                    navbarUL.classList.remove('w-100');
                    navbarUL.style.position = "fixed";
                    nav.style.margin = "0";
                    nav.style.maxWidth = "100%";
                    icons.forEach(icon => {
                        icon.style.display = "none";
                    })
                } else {
                    navbarUL.classList.add('w-100');
                    navbarUL.style.position = "relative";
                    nav.style.margin = "20px auto";
                    nav.style.maxWidth = "80vw";
                    icons.forEach(icon => {
                        icon.style.display = "block";
                    })
                }
            }

            handleResponsiveNavbar();
            window.addEventListener('resize', handleResponsiveNavbar);
        });
    </script>
</body>

</html>