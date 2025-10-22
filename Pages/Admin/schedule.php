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
    <title>Schedule </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Links -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/schedule.css">
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
    <div id="sidebar" class="sidebar show sidebar-custom">
        <div class="sbToggle-container d-flex justify-content-center" id="sidebar-toggle">
            <button class="toggle-button" type="button" id="toggle-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>
        </div>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo" id="sbLogo">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="adminDashboard.php" title="Dashboard">
                    <i class="bi bi-speedometer2"></i> <span class="linkText">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="booking.php" title="Bookings">
                    <i class="bi bi-calendar-week"></i><span class="linkText"> Bookings</span>
                </a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="schedule.php" title="Schedule">
                    <i class="bi bi-calendar-date"></i><span class="linkText">Schedule</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="roomList.php" title="Rooms">
                    <i class="bi bi-door-open"></i> <span class="linkText">Rooms</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="services.php" title="Services">
                    <i class="bi bi-bell"></i> <span class="linkText">Services</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="transaction.php" title="Payments">
                    <i class="bi bi-credit-card-2-front"></i> <span class="linkText">Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="displayPartnership.php" title="Partnerships">
                    <i class="bi bi-people"></i> <span class="linkText">Partnerships</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reviews.php" title="Reviews">
                    <i class="bi bi-list-stars"></i> <span class="linkText">Reviews</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="editWebsite/editWebsite.php" title="Edit Website">
                    <i class="bi bi-pencil-square"></i> <span class="linkText">Edit Website</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="auditLogs.php" title="Audit Logs">
                    <i class="bi bi-clock-history"></i> <span class="linkText">Audit Logs</span>
                </a>
            </li>
        </ul>

        <section>
            <a href="../Account/account.php" class="profileContainer" id="pfpContainer">
                <img src=" ../../Assets/Images/defaultProfile.png" alt="Admin Profile"
                    class="rounded-circle profilePic">
                <h5 class="admin-name" id="adminName">Diane Dela Cruz</h5>
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

        <section class="page-title-container">
            <h5 class="page-title">Schedule</h5>
        </section>
        <div class="card calendar-card">
            <div class="filter-btn-container m-2 p-2 d-flex justify-content-end">
                <div class="filter-select-wrapper">
                    <select class="filter-select" name="calendar-filter-select" id="calendar-filter-select">
                        <option selected value="events">Events</option>
                        <option value="services">Available Services</option>
                    </select>
                    <i class="bi bi-funnel"></i>
                </div>
            </div>
            <div id="calendar"></div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="calendarInfoModal" tabindex="-1" aria-labelledby="calendarInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="calendarInfoModalLabel">Date Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="calendarModalBody">
                        <!-- Info goes here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>

    <!-- full calendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const filterSelect = document.getElementById('calendar-filter-select');
            const modal = new bootstrap.Modal(document.getElementById('calendarInfoModal'));
            const modalBody = document.getElementById('calendarModalBody');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto', // make it responsive
                contentHeight: 'auto',
                aspectRatio: 1.35,
                events: '../../Function/Admin/fetchBookings.php',

                // Show modal on date click
                dateClick: function(info) {
                    const clickedDate = new Date(info.dateStr);
                    clickedDate.setHours(0, 0, 0, 0); // Normalize

                    const formattedClickedDate = clickedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    const eventsOnDate = calendar.getEvents().filter(event => {
                        const start = new Date(event.start);
                        const end = new Date(event.end ?? event.start);

                        start.setHours(0, 0, 0, 0);
                        end.setHours(0, 0, 0, 0);

                        return clickedDate >= start && clickedDate <= end;
                    });

                    if (eventsOnDate.length === 0) {
                        modalBody.innerHTML = `<p>No events found on ${formattedClickedDate}.</p>`;
                    } else {
                        let content = `<h5>Events on ${formattedClickedDate}</h5>`;
                        content += `<div class="list-group">`;

                        eventsOnDate.forEach(event => {
                            const formattedStart = new Date(event.start).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                            const formattedEnd = event.end ?
                                new Date(event.end).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                }) :
                                null;

                            content += `
                            <div class="list-group-item">
                                <h6 class="mb-1">${event.title}</h6>
                                <p class="mb-1">
                                    <strong>Start:</strong> ${formattedStart}<br>
                                    ${formattedEnd ? `<strong>End:</strong> ${formattedEnd}<br>` : ''}
                                </p>
                            </div>
                        `;
                        });

                        content += `</div>`;
                        modalBody.innerHTML = content;
                    }

                    modal.show();
                },

                // Show modal on event click
                eventClick: function(info) {
                    const event = info.event;

                    const formattedStart = new Date(event.start).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    const formattedEnd = event.end ?
                        new Date(event.end).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }) :
                        null;

                    let content = `
                    <h5>${event.title}</h5>
                    <p>
                        <strong>Start:</strong> ${formattedStart}<br>
                        ${formattedEnd ? `<strong>End:</strong> ${formattedEnd}<br>` : ''}
                    </p>
                `;

                    modalBody.innerHTML = content;
                    modal.show();
                },

                eventsSet: function(events) {
                    console.log('Fetched events:', events);
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
    <?php include '../Customer/loader.php'; ?>
</body>

</html>