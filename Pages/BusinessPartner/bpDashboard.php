<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);
require_once '../../Function/Helpers/userFunctions.php';
resetExpiredOTPs($conn);
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];


if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: .../../../../index.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';
require '../../Function/Partner/sales.php';
require '../../Function/Partner/getBookings.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/BusinessPartner/bpDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <input type="hidden" id="userRole" value="<?= $userRole ?>">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
            <?php
            $getProfile = $conn->prepare("SELECT firstName, userProfile FROM user WHERE userID = ? AND userRole = ?");
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
            ?>
            <li class="nav-item account-nav">
                <a href="../Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                </a>
            </li>

            <!-- Get notification -->
            <?php

            if ($userRole === 1) {
                $receiver = 'Customer';
            } elseif ($userRole === 2) {
                $receiver = 'Partner';
            }

            $notifications = getNotification($conn, $userID, $receiver);
            $counter = $notifications['count'];
            $notificationsArray = $notifications['messages'];
            $color = $notifications['colors'];
            $notificationIDs = $notifications['ids'];
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>
        </ul>
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../Customer/amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="../Customer/ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="../Customer/events.php">EVENTS</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Customer/blog.php">BLOG</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../Customer/about.php">ABOUT</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../Customer/bookNow.php">BOOK NOW</a>
                </li>

                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">LOG OUT</a>
                </li>

            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <?php
    include '../notificationModal.php';
    $getPartnershipID = $conn->prepare('SELECT partnershipID FROM `partnership` WHERE userID = ?');
    $getPartnershipID->bind_param('i', $userID);
    $getPartnershipID->execute();
    $result = $getPartnershipID->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $partnershipID = $data['partnershipID'];
    }
    ?>
    <!-- Get Sales -->
    <?php $totalSales = getSales($conn, $userID); ?>

    <!-- Get number of booking — approved, pending -->
    <?php
    $row = getBookingsCount($conn, $userID);
    ?>
    <div class="wrapper d-flex">
        <main class="main-content" id="main-content">
            <div class="container">
                <h3 class="welcomeText">Hello there, <?= ucfirst($firstName) ?>!</h3>
                <section>
                    <div class="column1">
                        <div class="card">
                            <div class="card-header fw-bold fs-5">All Bookings</div>
                            <div class="card-body">
                                <h2 class="bookingNumber"><?= $row['allBookingStatus'] ?></h2>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header fw-bold fs-5">Approved</div>
                            <div class="card-body">
                                <h2 class="approvedNumber"><?= $row['approvedBookings']  ?></h2>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header fw-bold fs-5">Pending</div>
                            <div class="card-body">
                                <h2 class="pendingNumber"><?= $row['totalPendingBooking']  ?></h2>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header fw-bold fs-5">Total Monthly Sales</div>
                            <div class="card-body">
                                <h2 class="revenueNumber"><?= ($totalSales !== 0) ? number_format($totalSales, 2) : '₱0.00' ?></h2>
                            </div>
                        </div>

                    </div>

                    <div class="card" id="salesPerformance">
                        <div class="card-header fw-bold fs-5">Monthly Sales</div>
                        <div class="card-body" id="pieGraph">
                            <canvas id="salesGraph"></canvas>
                        </div>
                    </div>

                    <!-- <div class="card" id="revenue">
                        <div class="card-header fw-bold fs-5">Revenue Overview</div>
                        <div class="card-body" id="revenueGraphContainer">
                            <img src="../../Assets/Images/revenueGraph.png" alt="Pie" class="revenueGraph">
                        </div>
                    </div> -->

                    <div class="card">
                        <div class="card-header fw-bold fs-5">Services</div>
                        <div class="card-body">

                            <ul>
                                <?php
                                // Get Services
                                $getServicesQuery = $conn->prepare('SELECT ps.`PBName`, ps.`PBPrice` FROM `partnershipservice` ps 
                                WHERE  partnershipID = ?');
                                $getServicesQuery->bind_param('i', $partnershipID);
                                if (!$getServicesQuery->execute()) {
                                    error_log('Failed executing services query: ' . $getServicesQuery->error());
                                }

                                $result = $getServicesQuery->get_result();


                                if (!$result->num_rows === 0) {
                                ?>
                                    <li>No Services</li>
                                <?php
                                }

                                while ($service = $result->fetch_assoc()) {
                                    // echo '<pre>';
                                    // print_r("ID: " . $partnershipID);
                                    // echo '</pre>';
                                ?>
                                    <li class="serviceNamePrice"><?= htmlspecialchars(ucfirst($service['PBName'])) ?> &mdash; ₱<?= number_format($service['PBPrice']) ?></li>
                                <?php
                                }

                                ?>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <a href="../Account/bpServices.php" class="btn btn-primary w-100">View All Services</a>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Monthly Sales Graph -->
    <?php
    $paymentStatusID = 3; //Fully Paid
    $paymentApprovalID = 5; //Done

    $getMonthlySalesQuery = $conn->prepare("SELECT MONTHNAME(b.startDate) AS month,
                    YEAR(b.startDate) AS year,
                    SUM(IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0)) AS monthlyRevenue,
                    ps.partnershipID, ps.partnershipServiceID
                    FROM booking b
                    LEFT JOIN  confirmedbooking cb ON b.bookingID = cb.bookingID
                    LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                    LEFT JOIN custompackageitem cpi ON b.customPackageID = cpi.customPackageID
                    LEFT JOIN service s ON (cpi.serviceID = s.serviceID  OR bs.serviceID = s.serviceID)
                    LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                    LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                    WHERE cb.paymentApprovalStatus = ?
                    AND p.paymentStatus = ?
                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                    AND DATE(b.endDate) < CURDATE()
                    AND ps.partnershipID = ?
                    AND bpas.approvalStatus = 2
                    GROUP BY 
                        month
                    ORDER BY 
                        month");
    $getMonthlySalesQuery->bind_param("iii", $paymentApprovalID, $paymentStatusID, $partnershipID);
    if (!$getMonthlySalesQuery->execute()) {
        error_log("Failed executing monthly sales in a year. Error: " . $getMonthlySalesQuery->error);
    }
    $months = [];
    $sales = [];
    $year = '';
    $result = $getMonthlySalesQuery->get_result();
    if ($result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            $months[] = $data['month'];
            $sales[] = (float) $data['monthlyRevenue'];
            $year = $data['year'] ?? DATE('Y');
        }
    }
    ?>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Chart Js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const items = document.querySelectorAll('.list-group-item');
            const toggleCont = document.getElementById('toggle-container')

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    });
                    toggleCont.style.justifyContent = "center"
                } else {
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    toggleCont.style.justifyContent = "flex-end"
                }
            });

            function handleResponsiveSidebar() {
                if (window.innerWidth <= 600) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })

                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    })
                    sidebar.classList.remove('collapsed');
                }
            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
        });
    </script>

    <script>
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');

        logoutBtn.addEventListener("click", function() {
            Swal.fire({
                title: "Are you sure you want to log out?",
                text: "You will need to log in again to access your account.",
                icon: "warning",
                showCancelButton: true,
                // confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, logout!",
                customClass: {
                    title: 'swal-custom-title',
                    htmlContainer: 'swal-custom-text'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../../Function/logout.php";
                }
            });
        })
    </script>

    <!-- This is shown if no data to display -->
    <script src="../../Assets/JS/ChartNoData.js"></script>

    <!-- Line Chart for sales  -->
    <script>
        const salesGraph = document.getElementById('salesGraph').getContext('2d');
        const labels = <?= json_encode($months) ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: "Monthly Sales Report — <?= !empty($year) ? json_encode($year) : DATE('Y') ?>",
                data: <?= json_encode($sales) ?>,
                fill: false,
                backgroundColor: 'rgb(33, 148, 209, .5)',
                borderColor: 'rgb(33, 148, 209, 1)',
                tension: 0.1
            }]
        };

        const lineSalesChart = new Chart(salesGraph, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            plugins: ['noDataPlugin']
        })
    </script>
</body>

</html>