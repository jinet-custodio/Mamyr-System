<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

require_once '../../Function/Helpers/statusFunctions.php';
require_once '../../Function/Helpers/userFunctions.php';
addToAdminTable($conn);
changeToDoneStatus($conn);
noPayment24hrs($conn);
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
        header("Location: ../register.php");
        exit();
    }
}
if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';


switch ($userRole) {
    case 3:
        $role = "Admin";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

$getUserData = $conn->prepare("SELECT firstName, lastName, userProfile FROM user WHERE userID = ?");
$getUserData->bind_param('i', $userID);
if (!$getUserData->execute()) {
    error_log('Failed getting user data: userID' . $userID);
}

$result = $getUserData->get_result();
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $adminName = ($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '');
    $profile = $data['userProfile'];
    if (!empty($profile)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
    }
} else {
    $_SESSION['error'] = "Unauthorized Access eh!";
    session_destroy();
    header("Location: ../register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Links -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/adminDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- Bootstrap Links -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <!-- Icon Links -->
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Icon Links -->
</head>

<body>
    <div id="sidebar" class=" sidebar show sidebar-custom">
        <div class="sbToggle-container d-flex justify-content-center" id="sidebar-toggle">
            <button class="toggle-button" type="button" id="toggle-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>
        </div>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo" id="sbLogo">
        <ul class="nav flex-column">
            <li class="nav-item active" id="navLI" title="Dashboard">
                <a class="nav-link" href="adminDashboard.php">
                    <i class="bi bi-speedometer2"></i> <span class="linkText">Dashboard</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Bookings">
                <a class="nav-link" href="booking.php">
                    <i class="bi bi-calendar-week"></i><span class="linkText"> Bookings</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Schedule">
                <a class="nav-link" href="schedule.php">
                    <i class="bi bi-calendar-date"></i><span class="linkText">Schedule</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="roomList.php" title="Rooms">
                    <i class="bi bi-door-open"></i> <span class="linkText">Rooms</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Services">
                <a class="nav-link" href="services.php">
                    <i class="bi bi-bell"></i> <span class="linkText">Services</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Payments">
                <a class="nav-link" href="transaction.php">
                    <i class="bi bi-credit-card-2-front"></i> <span class="linkText">Payments</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Partnerships">
                <a class="nav-link" href="displayPartnership.php">
                    <i class="bi bi-people"></i> <span class="linkText">Partnerships</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Reviews">
                <a class="nav-link" href="reviews.php">
                    <i class="bi bi-list-stars"></i> <span class="linkText">Reviews</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Edit Website">
                <a class="nav-link" href="editWebsite/editWebsite.php">
                    <i class="bi bi-pencil-square"></i> <span class="linkText">Edit Website</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Audit Logs">
                <a class="nav-link" href="auditLogs.php">
                    <i class="bi bi-clock-history"></i> <span class="linkText">Audit Logs</span>
                </a>
            </li>
        </ul>


        <section>
            <a href="../Account/account.php" class="profileContainer" id="pfpContainer">
                <img src="<?= $image ?>" alt="Admin Profile"
                    class="rounded-circle profilePic">
                <h5 class="admin-name" id="adminName"><?= htmlspecialchars($adminName) ?></h5>
            </a>
        </section>

        <section class="btn btn-outline-danger logOutContainer">
            <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                <h5 class="logoutText">Log Out</h5>
            </a>
        </section>
    </div>

    <main class="dashboard-container" id="main">
        <?php

        $receiver = 'Admin';
        $notifications = getNotification($conn, $userID, $receiver);
        $counter = $notifications['count'];
        $notificationsArray = $notifications['messages'];
        $color = $notifications['colors'];
        $notificationIDs = $notifications['ids'];
        ?>



        <section class="notification-toggler-container">
            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal">
                    <i class="bi bi-bell" id="notification-icon"></i>
                    <?php if (!empty($counter)): ?>
                        <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>
        </section>

        <section class="container topSection">
            <div class="card statCard customer-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-people"></i>
                        <h6 class="header-text">Customers</h6>
                    </div>

                    <div class="data-container customer">
                        <h5 class="card-data" id="customer-data"></h5>
                        <div class="spanContainer">
                            <span id="customer-span" class="status">
                                <i class="bi" id="customer-i"></i>
                                <h6 id="customer-status-percentage"></h6>
                            </span>
                            <h6 class="span-caption">vs last month</h6>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="span-caption other-card">This month</h6>
                </div>
            </div>

            <div class="card statCard total-bookings">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-calendar-check"></i>
                        <h6 class="header-text">Total Bookings</h6>
                    </div>

                    <div class="data-container ">
                        <h5 class="card-data" id="total-booking-data"></h5>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="span-caption other-card">This month</h6>
                </div>
            </div>

            <div class="card statCard total-sales">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-tags"></i>
                        <h6 class="header-text">Sales</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="sales-data"></h5>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="span-caption other-card">This month</h6>
                </div>
            </div>

            <div class="card statCard mostUsedSrvice-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-bell"></i>
                        <h6 class="header-text">Most Booked</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="most-booked-service"></h5>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="span-caption other-card">This month</h6>
                </div>
            </div>

            <div class="card statCard occupancy-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-people"></i>
                        <h6 class="header-text">Current Occupancy</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="occupancy-data"></h5>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="span-caption other-card">This month</h6>
                </div>
            </div>
        </section>
        <?php $monthToday = date('F') ?>
        <section class="container bottomSection">

            <div class="card graph-card" id="bookingSummary">
                <div class="card-body graph-card-body" id="bookingSummary-body">
                    <div class="graph-header">

                        <div class="headerText-container">
                            <i class="bi bi-calendar-check"></i>
                            <h6 class="graph-header-text">Booking Summary</h6>
                        </div>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select" name="booking-summary-filter-select"
                                    id="booking-summary-filter-select">
                                    <option value="month"><?= $monthToday ?></option>
                                    <option value="w1">Week 1</option>
                                    <option value="w2">Week 2</option>
                                    <option value="w3">Week 3</option>
                                    <option value="w4">Week 4</option>
                                    <option value="w5">Week 5</option>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>

                            <div class="filter-select-wrapper">
                                <select class="filter-select" id="booking-status-filter">
                                    <option value="all">All</option>
                                    <?php $statuses = getAllStatuses($conn);
                                    foreach ($statuses['status'] as $status):
                                    ?>
                                        <option value="<?= $status['statusID'] ?>"><?= $status['statusName'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>
                        </div>
                    </div>

                    <div class="booking-illustration">
                        <div class="booking-category resort" id="resort-category-div">
                            <div class="category-header">
                                <h6 class="category-type">Resort</h6>
                            </div>
                            <div class="categorySpan-container">
                                <span id="resort-span" class="category-span">
                                    <h6 id="resort-number" class="category-number">0</h6>
                                </span>
                            </div>
                        </div>

                        <div class="booking-category hotel" id="hotel-category-div">
                            <div class="category-header">
                                <h6 class="category-type">Hotel</h6>
                            </div>
                            <div class="categorySpan-container">
                                <span id="hotel-span" class="category-span">
                                    <h6 id="hotel-number" class=" category-number">0</h6>
                                </span>
                            </div>
                        </div>

                        <div class="booking-category event" id="event-category-div">
                            <div class="category-header">
                                <h6 class="category-type">Event</h6>
                            </div>
                            <div class="categorySpan-container">
                                <span id="event-span" class="category-span">
                                    <h6 id="event-number" class="category-number">0</h6>
                                </span>
                            </div>
                        </div>

                        <div class="booking-category total" id="total-category-div">
                            <div class="category-header">
                                <h6 class="category-type">Total</h6>
                            </div>
                            <div class="categorySpan-container">
                                <span id="resort-span" class="category-span">
                                    <h6 id="total-bookings" class="category-number">0</h6>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card graph-card" id="bookingsGraph">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <div class="headerText-container">
                            <i class="bi bi-calendar-week"></i>
                            <h6 class="graph-header-text">Bookings</h6>
                        </div>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select" name="bookings-filter-select" id="bookings-filter-select">
                                    <option value="month"><?= $monthToday ?></option>
                                    <option value="w1">Week 1</option>
                                    <option value="w2">Week 2</option>
                                    <option value="w3">Week 3</option>
                                    <option value="w4">Week 4</option>
                                    <option value="w5">Week 5</option>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bookings-chart">
                        <!-- <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="bookingsBar"> -->
                        <canvas class="graph" id="bookingsBar"></canvas>
                    </div>
                </div>
            </div>

            <div class="card graph-card" id="salesCard">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <div class="headerText-container">
                            <i class="bi bi-tags"></i>
                            <h6 class="graph-header-text">Sales</h6>
                        </div>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select" name="sales-filter-select" id="sales-filter-select">
                                    <!-- <option selected disabled>Filters</option> -->
                                    <option value="month"><?= $monthToday ?></option>
                                    <option value="w1">Week 1</option>
                                    <option value="w2">Week 2</option>
                                    <option value="w3">Week 3</option>
                                    <option value="w4">Week 4</option>
                                    <option value="w5">Week 5</option>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>
                        </div>
                    </div>

                    <div class="sales-chart">
                        <!-- <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="salesBar"> -->
                        <canvas id="salesBar" class="graph"></canvas>
                        <!-- <canvas class="graph" id="salesBar"></canvas> -->
                        <a href="salesReport.php" class="btn btn-primary gen-rep-btn" id="gen-rep">Generate Sales
                            Report</a>
                    </div>

                </div>
            </div>

            <div class="card graph-card" id="paymentsGraph">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <div class="headerText-container">
                            <i class="bi bi-receipt-cutoff"></i>
                            <h6 class="graph-header-text">Payments</h6>
                        </div>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select" id="payment-filter-select" name="payment-filter-select">
                                    <option value="month"><?= $monthToday ?></option>
                                    <option value="w1">Week 1</option>
                                    <option value="w2">Week 2</option>
                                    <option value="w3">Week 3</option>
                                    <option value="w4">Week 4</option>
                                    <option value="w5">Week 5</option>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>
                        </div>
                    </div>

                    <div class="payments-chart">
                        <!-- <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="paymentsBar"> -->
                        <canvas class="graph" id="paymentsBar"></canvas>

                    </div>

                </div>
            </div>

            <div class="card calendar-card">
                <div class="card-body calendar-card-body">
                    <div class="graph-header" id="calendarHeader">
                        <div class="headerText-container">
                            <i class="bi bi-calendar-check"></i>
                            <h6 class="calendar-header-text">Calendar</h6>
                        </div>
                        <div class="filter-btn-container mb-2" id="calendarFilterCont">
                            <div class="filter-select-wrapper" id="calendarFilter">
                                <select class="filter-select" name="calendar-filter-select" id="calendar-filter-select">
                                    <option selected value="events">Events</option>
                                    <option value="services">Available Services</option>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>
                        </div>
                    </div>

                    <div id="calendar"></div>
                </div>

                <div class="moreBtn">
                    <a href="schedule.php" class="btn btn-primary">View More</a>
                </div>
            </div>

            <div class="card graph-card" id="ratingsCard">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <div class="headerText-container">
                            <i class="bi bi-star"></i>
                            <h6 class="graph-header-text">Ratings</h6>
                        </div>
                    </div>

                    <div class="rating-categories">
                        <!-- Resort -->
                        <div class="rating-row">
                            <div class="rating-label">Resort</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="resort-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="resort-rating-value"></div>
                        </div>

                        <!-- Hotel -->
                        <div class="rating-row">
                            <div class="rating-label">Hotel</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="hotel-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="hotel-rating-value"></div>
                        </div>

                        <!-- Event -->
                        <div class="rating-row">
                            <div class="rating-label">Event</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="event-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="event-rating-value"></div>
                        </div>

                        <!-- Overall Rating (Optional) -->
                        <div class="overall-rating">
                            <div class="overall-rating-label">
                                <h6 class="overall-rating-label">Overall Rating</h6>
                                <h4 class="overall-rating-value" id="overall-rating-value"></h4>
                            </div>
                            <div class="overall-rating-stars" id="star-container">
                                <!-- <i class="bi bi-star-fill" id="overall-rating"></i>
                                <i class="bi bi-star-fill" id="overall-rating"></i>
                                <i class="bi bi-star-fill" id="overall-rating"></i>
                                <i class="bi bi-star-fill" id="overall-rating"></i>
                                <i class="bi bi-star-fill" id="overall-rating"></i> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- full calendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            const filterSelect = document.getElementById('calendar-filter-select');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                displayEventTime: false,
                events: '../../Function/Admin/fetchBookings.php',
                dateClick: function(info) {
                    window.location.href = `schedule.php`;
                },
                eventClick: function(info) {
                    window.location.href = `schedule.php`;
                },
                eventsSet: function(events) {
                    // console.log('Fetched events:', events);
                    events.forEach(event => {
                        console.log(`Title: ${event.title}, Start: ${event.startStr}`);
                    });
                },
            });
            calendar.render();

            function changeEventSource(newSourceUrl) {
                calendar.removeAllEventSources();
                calendar.addEventSource(newSourceUrl);
            }

            filterSelect.addEventListener('change', function() {
                const selectedValue = this.value;

                if (selectedValue === 'events') {
                    changeEventSource('../../Function/Admin/fetchBookings.php');
                } else if (selectedValue === 'services') {
                    changeEventSource('../../Function/Admin/fetchUnavailableServices.php');
                }
            });
        });
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

    <!-- Display if no available data -->
    <script src="../../Assets/JS/ChartNoData.js"> </script>
    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>

    <!-- For Cards -->
    <script>
        async function updateCards() {
            const response = await fetch('../../Function/Admin/Ajax/getCardsData.php');
            const data = await response.json();

            document.getElementById('customer-data').textContent = data.guestThisMonth;
            const span = document.getElementById('customer-span');
            span.classList.add(data.statusClass);
            span.style.backgroundColor = data.statusColor;
            document.getElementById('customer-i').classList.add(data.arrowClass);
            document.getElementById('customer-status-percentage').textContent = data.displayPercentage +
                '%';
            document.getElementById('total-booking-data').textContent = data.bookingCount;
            document.getElementById('sales-data').textContent = data.salesThisMonth;
            document.getElementById('most-booked-service').textContent = data.mostBookedService;
            document.getElementById('occupancy-data').textContent = data.guestCountToday;
        }

        async function getRatings() {
            const response = await fetch('../../Function/Admin/Ajax/getRatings.php');
            const data = await response.json();

            const resortBar = document.getElementById('resort-bar');
            resortBar.style.width = data.resortPercent + '%';
            resortBar.setAttribute('ari-valuenow', data.resortPercent)
            document.getElementById('resort-rating-value').textContent = data.resortRating;

            const hotelBar = document.getElementById('hotel-bar');
            hotelBar.style.width = data.hotelPercent + '%';
            hotelBar.setAttribute('ari-valuenow', data.hotelPercent)
            document.getElementById('hotel-rating-value').textContent = data.hotelRating;

            const eventBar = document.getElementById('event-bar');
            eventBar.style.width = data.eventPercent + '%';
            eventBar.setAttribute('ari-valuenow', data.eventPercent)
            document.getElementById('event-rating-value').textContent = data.eventRating;

            document.getElementById('overall-rating-value').textContent = data.overAllRating;
            const starContainer = document.getElementById('star-container');
            for (let i = 1; i <= 5; i++) {
                if (i <= Math.floor(data.overAllRating)) {
                    starContainer.innerHTML += '<i class="bi bi-star-fill star text-warning"></i>';
                } else if (i - data.overAllRating <= .5 && i - data.overAllRating > 0) {
                    starContainer.innerHTML += '<i class="bi bi-star-half star text-warning"></i>';
                } else {
                    starContainer.innerHTML += '<i class="bi bi-star star text-warning"></i>';
                }
            }
        }



        getRatings();
        updateCards();
        setInterval(updateCards, 300000);
    </script>

    <!-- //* For Sales -->
    <script src="../../Assets/JS/Dashboard/adminSales.js"></script>

    <!-- //* For Bookings -->
    <script src="../../Assets/JS/Dashboard/adminBookings.js"></script>

    <!-- //* For Payments -->
    <script src="../../Assets/JS/Dashboard/adminPayments.js"></script>

    <!-- //* For Booking Summary -->
    <script src="../../Assets/JS/Dashboard/adminBookingSummary.js"></script>

    <script>
        const colors = {
            unpaid: {
                bg: "rgba(219, 53, 69, .4)",
                border: "rgba(219, 53, 69, .7)"
            },
            event: {
                bg: "rgba(79, 76, 207, .4)",
                border: "rgba(79, 76, 207, .7)"
            },
            "partially paid": {
                bg: "rgba(255, 193, 8, .4)",
                border: "rgba(255, 193, 8, .7)"
            },
            hotel: {
                bg: "rgba(211, 120, 250, .4)",
                border: "rgba(211, 120, 250, .7)"
            },
            "fully paid": {
                bg: "rgba(26, 135, 84,.4)",
                border: "rgba(26, 135, 84, .7)"
            },
            resort: {
                bg: "rgba(65, 138, 240, .4)",
                border: "rgba(65, 138, 240, .7)"
            },
            "payment sent": {
                bg: "rgba(13, 109, 252, .4)",
                border: "rgba(13, 109, 252, .7)"
            },
            default: {
                bg: "rgba(255, 205, 86, 0.4)",
                border: "rgba(255, 205, 86, .7)"
            }
        };

        let salesChart = null;
        let bookingsChart = null;
        let paymentsChart = null;
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

        // For Sales
        const salesSelectedFilter = document.getElementById("sales-filter-select");
        if (salesSelectedFilter) {
            salesSelectedFilter.addEventListener("change", () => {
                filteredSales(salesSelectedFilter.value);
            });
            filteredSales(salesSelectedFilter.value);
        }

        // For Bookings
        const bookingsSelectedFilter = document.getElementById('bookings-filter-select');
        if (bookingsSelectedFilter) {
            bookingsSelectedFilter.addEventListener("change", () => {
                filteredBookings(bookingsSelectedFilter.value);
            });
            filteredBookings(bookingsSelectedFilter.value);
        }

        // For Payments
        const paymentsSelectedFilter = document.getElementById('payment-filter-select');
        if (paymentsSelectedFilter) {
            paymentsSelectedFilter.addEventListener('change', () => {
                filteredPayments(paymentsSelectedFilter.value);
            });
            filteredPayments(paymentsSelectedFilter.value);
        }


        //For Booking Summary
        const bookingSummaryFilter = document.getElementById('booking-summary-filter-select');
        const bookingStatusFilter = document.getElementById('booking-status-filter');
        if (bookingSummaryFilter || bookingStatusFilter) {
            bookingSummaryFilter.addEventListener('change', () => {
                const summaryFilterValue = bookingSummaryFilter.value;
                const statusFilterValue = bookingStatusFilter.value;
                filteredBookingSummary(summaryFilterValue, statusFilterValue);
            });
            bookingStatusFilter.addEventListener('change', () => {
                const summaryFilterValue = bookingSummaryFilter.value;
                const statusFilterValue = bookingStatusFilter.value;
                filteredBookingSummary(summaryFilterValue, statusFilterValue);
            });
            const summaryFilterValue = bookingSummaryFilter.value;
            const statusFilterValue = bookingStatusFilter.value;
            filteredBookingSummary(summaryFilterValue, statusFilterValue);
        }
    </script>

    <!-- SweetAlert Message -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        if (paramValue === 'successLogin') {
            Toast.fire({
                icon: "success",
                title: "Signed in successfully"
            });
        };

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <!-- <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const main = document.getElementById('main');
    const sbLogo = document.getElementById('sbLogo');
    const pfpContainer = document.getElementById('pfpContainer');
    const navLI = document.getElementById('navLI');
    const linkText = document.getElementById('linkText');

    let isOpen = true; 

    toggleBtn.addEventListener("click", function() {
        if (isOpen) {
            closeSB();
        } else {
            openSB();
        }
        isOpen = !isOpen; 
    });

    function openSB() {
        sidebar.style.width = "250px";
        main.style.width = "85%";
        sbLogo.style.display = "block";
        pfpContainer.style.padding = "1rem";
        navLI.style.padding = "1rem";
        linkText.style.display = "inline";
    }

    function closeSB() {
        sidebar.style.width = "5%";
        main.style.width = "95%";
        sbLogo.style.display = "none";
        pfpContainer.style.padding = "1rem";
        navLI.style.padding = "0";
        linkText.style.display = "none";
    }
    </script> -->
    <?php include '../Customer/loader.php'; ?>
</body>

</html>