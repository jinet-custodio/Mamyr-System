<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

require_once '../../Function/Helpers/statusFunctions.php';
require_once '../../Function/Helpers/userFunctions.php';
addToAdminTable($conn);
changeToDoneStatus($conn);

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
        header("Location: ../../../register.php");
        exit();
    }
}
if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../../register.php");
    exit();
}


$partiallyPaid = 2;
$fullyPaid = 3;
$approvedStatus = 2;
$doneStatus = 5;
$bookingTypes =  $conn->prepare("SELECT 
                                b.bookingType,
                                COUNT(*) AS totalBookings
                            FROM 
                                confirmedbooking cb 
                            JOIN 
                                booking b ON cb.bookingID = b.bookingID 
                            WHERE 
                                cb.paymentStatus = ? OR cb.paymentStatus = ?
                                AND YEARWEEK(b.startDate, 1) = YEARWEEK(CURDATE(), 1)
                            GROUP BY 
                                b.bookingType 
                    ");
$bookingTypes->bind_param("ii", $partiallyPaid, $fullyPaid);
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
$bookingTypesResult->free();
$bookingTypes->close();


$hotel = 1;
$availabilityCount = [];
$availabilityName = ['Available', 'Maintenance', 'Occupied', 'Private'];
$availabilityQuery = $conn->prepare("SELECT
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 1 THEN 1 END) AS availableCount,
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 2 THEN 1 END) AS occupiedCount,
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 3 THEN 1 END) AS maintenanceCount,
                                        COUNT(CASE WHEN uniqueRooms.RSAvailabilityID = 4 THEN 1 END) AS privateCount,
                                        sa.availabilityName
                                    FROM (
                                        SELECT RServiceName, RSAvailabilityID
                                        FROM resortamenity
                                        WHERE RScategoryID = ?
                                        GROUP BY RServiceName
                                    ) AS uniqueRooms
                                    JOIN serviceavailability sa ON uniqueRooms.RSAvailabilityID = sa.availabilityID
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
$availabilityResult->free();
$availabilityQuery->close();
require '../../Function/notification.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/adminDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/navbar.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>

    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/Images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <!-- Get notification -->
            <?php

            $receiver = 'Admin';
            $notifications = getNotification($conn, $userID, $receiver);
            $counter = $notifications['count'];
            $notificationsArray = $notifications['messages'];
            $color = $notifications['colors'];
            $notificationIDs = $notifications['ids'];
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            id="notifCounter">
                            <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>

            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" class="messageIcon" alt="Message icon">
            </a>
            <?php
            if ($userRole == 3) {
                $admin = "Admin";
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../../../register.php");
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
                header("Location: ../../../register.php");
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
                    <a class="nav-link active" href="adminDashboard.php">
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
                    <a class="nav-link" href="reviews.php">
                        <i class="fa-solid fa-star navbar-icon"></i>
                        <h5>Reviews</h5>
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
                    <a class="nav-link" href="revenue.php">
                        <i class="fa-solid fa-money-bill-trend-up navbar-icon"></i>
                        <h5>Sales</h5>
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
                    <a href="../../Function/Admin/logout.php" class="nav-link">
                        <i class="fa-solid fa-right-from-bracket navbar-icon" style="color: #db3545;"></i>
                        <h5 style="color: red;">Log Out</h5>
                    </a>
                </li>
            </ul>
        </div>
    </nav>


    <?php
    $weeklyReport = $conn->prepare("SELECT 
                                        -- Total guests 
                                        SUM(CASE 
                                            WHEN cb.paymentApprovalStatus = 5 
                                            THEN b.guestCount 
                                            ELSE 0 
                                            END) AS totalGuests,

                                        -- Total sales
                                        SUM(CASE 
                                                WHEN cb.paymentApprovalStatus = 5 THEN cb.confirmedFinalBill
                                                ELSE 0 
                                                END) AS totalsalesThisWeek,

                                        -- Check-outs this week
                                        COUNT(CASE 
                                                WHEN b.endDate >= weekStart AND b.endDate < weekEnd AND cb.paymentApprovalStatus = 5 
                                                THEN 1 
                                                ELSE NULL 
                                                END) AS checkOutsThisWeek,

                                        -- Check-ins this week
                                        COUNT(CASE 
                                                WHEN b.startDate >= weekStart AND b.startDate < weekEnd AND cb.paymentApprovalStatus = 5 
                                                THEN 1 
                                                ELSE NULL 
                                                END) AS checkInsThisWeek,
                                        -- Check-ins this week
                                        COUNT(CASE 
                                                WHEN b.bookingType = 'Event' AND
                                                cb.paymentApprovalStatus = 5 
                                                THEN 1 
                                                ELSE NULL 
                                                END) AS eventBooking,


                                        --  All bookings this week
                                        COUNT(DISTINCT b.bookingID) AS bookingsThisWeek

                                    FROM booking b
                                    LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
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
        $totalGuests = $data['totalGuests'] ?? '0';
        $totalsalesThisWeek = $data['totalsalesThisWeek'];
        $eventBooking = $data['eventBooking'];
    }
    ?>
    <!-- <h1 class="dashboardTitle">Weekly Status</h1> -->
    <div class="container-fluid" id="contentsCF">

        <div class="leftSection">
            <div class="trend-cards">
                <div class="card trendCardContent">
                    <div class="card-header">
                        All Bookings
                    </div>
                    <div class="card-body">
                        <h2 class="newBookingTotal"><?= $bookingsThisWeek ?></h2>
                    </div>
                </div>

                <div class="card trendCardContent">
                    <div class="card-header">
                        Event Bookings
                    </div>

                    <div class="card-body">
                        <h2 class="newBookingTotal"><?= $eventBooking ?></h2>
                    </div>
                </div>

                <div class="card trendCardContent">
                    <div class="card-header">
                        Total Guest
                    </div>

                    <div class="card-body">
                        <h2 class="totalGuest"><?= $totalGuests ?></h2>
                    </div>
                </div>

                <div class="card trendCardContent">
                    <div class="card-header">
                        Check In
                    </div>

                    <div class="card-body">
                        <h2 class="checkInTotal"><?= $checkInsThisWeek ?></h2>
                    </div>
                </div>

                <div class="card trendCardContent">
                    <div class="card-header">
                        Check Out
                    </div>

                    <div class="card-body">
                        <h2 class="checkOutTotal"><?= $checkOutsThisWeek ?></h2>
                    </div>
                </div>

                <div class="card trendCardContent">
                    <div class="card-header">
                        Sales
                    </div>

                    <div class="card-body">
                        <h2 class="salesTotal">₱<?= number_format($totalsalesThisWeek ?? 0, 2) ?></h2>
                    </div>
                </div>

            </div>
            <div class="card mt-2" id="roomAvailabilityCard">
                <div class="card-header">
                    Room Availability Today
                </div>
                <div class="card-body availabilityGraph">
                    <div class="roomAvailabilityGraph">
                        <canvas id="availabilityGraph"></canvas>
                    </div>
                </div>
                <!-- <div class="card-footer">
                    today
                </div> -->
            </div>
        </div>


        <div class="rightSection">

            <div class="filter-select" id="filter-select">
                <select name="sales-filter-select" class="form-select" id="sales-filter-select">
                    <option value="month">This Month</option>
                    <option value="w1">Week 1</option>
                    <option value="w2">Week 2</option>
                    <option value="w3">Week 3</option>
                    <option value="w4">Week 4</option>
                    <option value="w5">Week 5</option>
                </select>
            </div>

            <div class="card graph" id="salesGraphCard">
                <div class="salesGraphContainer mb-3">
                    <h5 class="revTitle">Sales</h5>
                    <div class="sales-chart">
                        <canvas id="salesBar"></canvas>
                    </div>
                </div>
            </div>




            <div class="card graph" id="reservationTrends">
                <div class="card-header ">

                    <h5>Reservation Trends</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($bookingTypeCount)): ?>
                        <div class="sales-chart">
                            <canvas id="reservationTrendsBar"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="sales-chart">
                            <canvas id="reservationTrendsBar"></canvas>
                        </div>
                        <!-- Change this div -->
                        <!-- <div class="ReservationTrendsGraph">No data available.</div> -->
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>


    <?php include '../notificationModal.php'; ?>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Responsive Navbar -->
    <script src="../../Assets/JS/adminNavbar.js"></script>

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
    <script src="../../Assets/JS/ChartNoData.js"> </script>

    <script>
        //* Reservation Trends Bar
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
        //* availabilityGraph
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
        //* For Sales
        let salesChart;
        const noDataPlugin = {
            id: 'noDataPlugin',
            afterDraw(chart) {
                const datasets = chart.data.datasets;
                const hasData = datasets.some(ds => ds.data.length > 0);

                if (!hasData) {
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '16px Arial';
                    ctx.fillStyle = 'gray';
                    ctx.restore();
                }
            }
        };

        function filteredSales(selectedFilterValue) {
            fetch(`../../Function/Admin/salesGraph.php?selectedFilter=${encodeURIComponent(selectedFilterValue)}`)
                .then(response => {
                    if (!response.ok) throw new Error("Network error");
                    return response.json();
                })
                .then(data => {
                    const ctx = document.getElementById("salesBar").getContext("2d");

                    if (!data.success || !data.sales || data.sales.length === 0) {

                        if (salesChart) {
                            salesChart.destroy();
                        }
                        salesChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [],
                                datasets: []
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: selectedFilterValue === 'month' ? 'Weeks of the Month' : 'Days of the Week'
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        enabled: false
                                    }
                                }
                            },
                            plugins: [noDataPlugin]
                        });
                        return;
                    }

                    const sales = data.sales;
                    let labels = [];
                    let dataset = [];
                    let title = '';

                    if (selectedFilterValue === 'month') {
                        labels = sales.map(item => item.weekOfMonth);
                        dataset = sales.map(item => parseFloat(item.totalSalesThisWeek));
                        title = sales[0]?.month || '';
                    } else {
                        const dayLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
                        labels = dayLabels;
                        title = sales[0]?.weekLabel || '';

                        const dayData = sales[0];
                        dataset = dayLabels.map(day => parseFloat(dayData[day]));
                    }

                    if (salesChart) {
                        salesChart.destroy();
                    }

                    salesChart = new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: labels,
                            datasets: [{
                                label: title,
                                data: dataset,
                                backgroundColor: 'rgb(128, 189, 255)',
                                borderColor: 'rgb(37, 144, 232)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: selectedFilterValue === 'month' ? 'Weeks of the Month' : 'Days of the Week'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `₱${context.parsed.y.toLocaleString()}`;
                                        }
                                    }
                                }
                            }
                        },
                        plugins: [noDataPlugin]
                    });
                })
                .catch(error => {
                    console.error("Error fetching sales data:", error);
                    if (salesChart) {
                        salesChart.destroy();
                    }
                    const ctx = document.getElementById("salesBar").getContext("2d");
                    salesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: []
                        },
                        options: {
                            responsive: true
                        },
                        plugins: [noDataPlugin]
                    });
                });
        }

        const selectedFilter = document.getElementById("sales-filter-select");
        if (selectedFilter) {
            selectedFilter.addEventListener("change", () => {
                filteredSales(selectedFilter.value);
            });
            filteredSales(selectedFilter.value);
        }
    </script>

</body>

</html>