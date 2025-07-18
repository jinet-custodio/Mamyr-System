<?php
require '../../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../../register.php?session=expired");
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
$payments->execute();
$paymentsResult = $payments->get_result();
if ($paymentsResult->num_rows > 0) {
    $row = $paymentsResult->fetch_assoc();
    $GCashCount = $row['totalPaymentGCash'];
    $CashCount = $row['totalPaymentCash'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/Account/revenue.css" />

</head>



<body>

    <!-- Side Bar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h5>Account Settings</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/user.png" alt="" class="sidebar-icon">
                    Profile Information
                </a>
            </li>
            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="" class="sidebar-icon">
                    Login & Security
                </a>
            </li>
            <li>
                <a href="userManagement.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/usermanagement.png" alt="" class="sidebar-icon">
                    Manage Users
                </a>
            </li>
            <!-- <li>
                <a href="" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/systempreferences.png" alt="" class="sidebar-icon">
                    System Preferences
                </a>
            </li> -->
            <li>
                <a href="revenue.php" class="list-group-item  active">
                    <img src="../../../Assets/Images/Icon/revenue.png" alt="" class="sidebar-icon">
                    Revenue
                </a>
            </li>
            <li>
                <a href="deleteAccount.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/delete-user.png" alt="" class="sidebar-icon">
                    Delete Account
                </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img src="../../../Assets/Images/Icon/logout.png" alt="" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>
    <!-- End Side Bar -->
    <a href="../adminDashboard.php" class="home-button btn btn-primary"><img src="../../../Assets/Images/Icon/home2.png" alt=""></a>


    <div class="wrapper">
        <div class="card">
            <h5 class="card-title">Revenue</h5>
            <div class="card-body">
                <div class="charts">
                    <div class="revenue-chart">
                        <canvas id="revenueBar"></canvas>
                    </div>
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
                        $revenue = "SELECT 
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
                                                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                                                    AND DATE(b.endDate) < CURDATE() 
                                                    THEN cb.CBtotalCost ELSE 0 END) AS totalThisYear                              
                                    FROM bookings b 
                                    JOIN confirmedbookings cb ON b.bookingID = cb.bookingID";
                        $result = mysqli_query($conn, $revenue);

                        if (mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
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
                        $bookings = "SELECT
                                        COUNT(CASE WHEN confirmedBookingStatus = '2' THEN 1 END) AS totalApprovedBookings,
                                        COUNT(CASE WHEN confirmedBookingStatus = '3' THEN 1 END) AS totalRejectedBookings,
                                         COUNT(CASE WHEN bookingStatus = '4' THEN 1 END) AS totalCancelledBookings                                       
                                    FROM bookings b 
                                    JOIN confirmedbookings cb ON b.bookingID = cb.bookingID";
                        $result = mysqli_query($conn, $bookings);

                        if (mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
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
                        $occupied = "SELECT 
                                        ROUND(
                                            COUNT(CASE WHEN RSAvailabilityID = '2' THEN 1 END) * 100 / COUNT(*), 
                                            2
                                        ) AS occupiedRates
                                    FROM resortamenities
                                    WHERE RScategoryID = '1'";
                        $result = mysqli_query($conn, $occupied);

                        if (mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
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
    <script src="path/to/chartjs/dist/chart.umd.js"></script>

    <script>
        const bar = document.getElementById("revenueBar").getContext('2d');

        const myBarChart = new Chart(bar, {
            type: 'bar',
            data: {
                labels: ['2024', '2025'],
                datasets: [{
                    label: 'Sales',
                    data: [12, 19, 3, 5, 9],
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