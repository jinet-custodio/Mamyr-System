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
        header("Location: ../register.php");
        exit();
    }
}
if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';
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
    <!-- CSS Links -->
    <!-- Bootstrap Links -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <!-- Bootstrap Links -->
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
    <div id="sidebar">
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo">
        <ul class="nav flex-column">
            <li class="nav-item active">
                <i class="bi bi-speedometer2"></i>
                <a class="nav-link" href="adminDashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-calendar-week"></i>
                <a class="nav-link" href="booking.php">Bookings</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-list-stars"></i>
                <a class="nav-link" href="reviews.php">Reviews</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-door-open"></i>
                <a class="nav-link" href="roomList.php">Rooms</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-bell"></i>
                <a class="nav-link" href="services.php">Services</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-credit-card-2-front"></i>
                <a class="nav-link" href="transaction.php">Payments</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-people"></i>
                <a class="nav-link" href="displayPartnership.php">Partnerships</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-pencil-square"></i>
                <a class="nav-link" href="editWebsite/editWebsite.php">Edit Website</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-clock-history"></i>
                <a class="nav-link" href="auditLogs.php">Audit Logs</a>
            </li>
        </ul>

        <section class="profileContainer">
            <a href="../Account/account.php">
                <img src=" ../../Assets/Images/defaultProfile.png" alt="Admin Profile" class="rounded-circle profilePic">
                <h5 class="admin-name">Diane Dela Cruz</h5>
            </a>
        </section>

        <section class="btn btn-outline-danger logOutContainer">
            <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                <h5>Log Out</h5>
            </a>
        </section>
    </div>

    <main class="dashboard-container">
        <?php

        $receiver = 'Admin';
        $notifications = getNotification($conn, $userID, $receiver);
        $counter = $notifications['count'];
        $notificationsArray = $notifications['messages'];
        $color = $notifications['colors'];
        $notificationIDs = $notifications['ids'];
        ?>



        <section class="notification-container">
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
            <div class="card customer-card">
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
            </div>

            <div class="card total-bookings">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-calendar-check"></i>
                        <h6 class="header-text">Total Bookings</h6>
                    </div>

                    <div class="data-container ">
                        <h5 class="card-data" id="total-booking-data"></h5>
                    </div>
                </div>
            </div>

            <div class="card total-sales">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-tags"></i>
                        <h6 class="header-text">Sales</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="sales-data"></h5>
                    </div>
                </div>
            </div>

            <div class="card mostUsedSrvice-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-bell"></i>
                        <h6 class="header-text">Most Booked Service</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="most-booked-service"></h5>
                    </div>
                </div>
            </div>

            <div class="card occupancy-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-people"></i>
                        <h6 class="header-text">Current Occupancy</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="occupancy-data"></h5>
                    </div>
                </div>
            </div>
        </section>
        <?php $monthToday = date('F') ?>
        <section class="container bottomSection">
            <div class="card graph-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-calendar-check"></i>
                        <h6 class="graph-header-text">Bookings</h6>

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
                                <i class="bi bi-funnel"></i>
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


            <div class="card graph-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-tags"></i>
                        <h6 class="graph-header-text">Sales</h6>

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
                                <i class="bi bi-funnel"></i>
                            </div>
                        </div>
                    </div>

                    <div class="sales-chart">
                        <!-- <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="salesBar"> -->
                        <canvas id="salesBar" class="graph"></canvas>
                        <!-- <canvas class="graph" id="salesBar"></canvas> -->
                        <a href="salesReport.php" class="btn btn-primary gen-rep-btn" id="gen-rep">Generate Sales Report</a>
                    </div>

                </div>
            </div>


            <div class="card graph-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-receipt-cutoff"></i>
                        <h6 class="graph-header-text">Payments</h6>

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
                                <i class="bi bi-funnel"></i>
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

            <div class="calendar-rating-container">

                <div class="card calendar-card">
                    <div id="calendar"></div>
                </div>

                <div class="card ratings-card">
                    <div class="card-body graph-card-body">
                        <div class="graph-header">
                            <i class="bi bi-star"></i>
                            <h6 class="graph-header-text">Ratings</h6>
                        </div>

                        <div class="rating-categories">
                            <!-- Resort -->
                            <div class="rating-row">
                                <div class="rating-label">Resort</div>
                                <div class="rating-bar">
                                    <div class="progress">
                                        <div class="progress-bar" id="resort-bar" role="progressbar"
                                            aria-valuenow="" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="rating-value" id="resort-rating-value"></div>
                            </div>

                            <!-- Hotel -->
                            <div class="rating-row">
                                <div class="rating-label">Hotel</div>
                                <div class="rating-bar">
                                    <div class="progress">
                                        <div class="progress-bar" id="hotel-bar" role="progressbar"
                                            aria-valuenow="" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="rating-value" id="hotel-rating-value"></div>
                            </div>

                            <!-- Event -->
                            <div class="rating-row">
                                <div class="rating-label">Event</div>
                                <div class="rating-bar">
                                    <div class="progress">
                                        <div class="progress-bar" id="event-bar" role="progressbar"
                                            aria-valuenow="" aria-valuemin="0" aria-valuemax="100"></div>
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
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'

            });
            calendar.render();
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
                    starContainer.innerHTML += '<i class="bi bi-star-fill text-warning"></i>';
                } else if (i - data.overAllRating <= .5 && i - data.overAllRating > 0) {
                    starContainer.innerHTML += '<i class="bi bi-star-half text-warning"></i>';
                } else {
                    starContainer.innerHTML += '<i class="bi bi-star text-warning"></i>';
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
    <script>
        const colors = {
            unpaid: {
                bg: "rgba(219, 53, 69, .7)",
                border: "rgb(219, 53, 69)"
            },
            event: {
                bg: "rgb(79, 76, 207, .7)",
                border: "rgb(79, 76, 207)"
            },
            "partially paid": {
                bg: "rgba(255, 193, 8, .7)",
                border: "rgb(255, 193, 8)"
            },
            hotel: {
                bg: "rgb(211, 120, 250, .7)",
                border: "rgb(211, 120, 250)"
            },
            "fully paid": {
                bg: "rgba(26, 135, 84,.7)",
                border: "rgb(26, 135, 84)"
            },
            resort: {
                bg: "rgb(65, 138, 240, .7)",
                border: "rgb(65, 138, 240)"
            },
            "payment sent": {
                bg: "rgba(13, 109, 252, .7)",
                border: "rgb(13, 109, 252)"
            },
            default: {
                bg: "rgba(255, 205, 86, 0.7)",
                border: "rgb(255, 205, 86)"
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
    </script>

    <!-- SweetAlert Message -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        if (paramValue === 'successLogin') {
            Swal.fire({
                timer: 1000,
                showConfirmButton: false,
                title: "Login Successful!",
                text: "Welcome back! You have successfully logged in.",
                icon: "success",
            })
        };

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>
</body>

</html>