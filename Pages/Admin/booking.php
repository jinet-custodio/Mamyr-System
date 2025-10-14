<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

require_once '../../Function/Helpers/statusFunctions.php';
changeToDoneStatus($conn);
changeToExpiredStatus($conn);

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

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';

$message = '';
$status = '';

if (isset($_SESSION['error'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['error']));
    $status = 'error';
    unset($_SESSION['error']);
} elseif (isset($_SESSION['success'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['success']));
    $status = 'success';
    unset($_SESSION['success']);
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
    <link rel="stylesheet" href="../../Assets/CSS/Admin/booking.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Icon Links -->
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
    <!-- Font Awesome and Box Icon links  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>


    <!-- Get notification -->
    <!-- <div class="menus">

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
    </div> -->


    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <main>
        <div id="sidebar">
            <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <i class="bi bi-speedometer2"></i>
                    <a class="nav-link" href="adminDashboard.php">Dashboard</a>
                </li>
                <li class="nav-item active">
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
                <img src="../../Assets/Images/defaultProfile.png" alt="Admin Profile" class="rounded-circle profilePic">
                <h5 class="admin-name">Diane Dela Cruz</h5>

            </section>

            <section class="btn btn-outline-danger logOutContainer">
                <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <h5>Log Out</h5>
                </a>
            </section>
        </div>

        <!-- Booking-container -->
        <section class="booking-container">
            <section class="notification-container">
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
            </section>

            <section class="page-title-container">
                <h5 class="page-title">Bookings</h5>
            </section>

            <!-- <h1 class="title text-center my-3" style="display: none;" id="hiddenTitle">Bookings</h1> -->

            <div class="booking-table">
                <div class="card">
                    <div class="btnContainer">
                        <a href="createBooking.php" class="btn btn-primary" id="addBookings">Add</a>
                    </div>

                    <table class="table table-striped display nowrap" id="bookingTable">
                        <thead>
                            <th scope="col">Booking ID</th>
                            <th scope="col">Guest</th>
                            <th scope="col">Booking Type</th>
                            <th scope="col">Check-in</th>
                            <th scope="col">Check-out</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </thead>
                        <tbody id='booking-display-body'></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>


    <!-- Table JS -->
    <script>
        $('#bookingTable').DataTable({
            responsive: false,
            scrollX: true,
            columnDefs: [{
                    width: '10%',
                    targets: 0
                },
                {
                    width: '15%',
                    targets: 1
                },
                {
                    width: '15%',
                    targets: 2
                },
                {
                    width: '15%',
                    targets: 3
                },
                {
                    width: '15%',
                    targets: 4
                },
                {
                    width: '10%',
                    targets: 5
                },
                {
                    width: '10%',
                    targets: 6
                },
            ],
        });
    </script>


    <!-- Booking Ajax -->
    <script>
        function getStatusBadge(colorClass, status) {
            return `<span class="badge bg-${colorClass} text-capitalize">${status}</span>`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            fetch("../../Function/Admin/Ajax/getBookingsJSON.php")
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // console.error("Failed to load bookings.");
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An unknown error occurred.',
                            showConfirmButton: false,
                            timer: 1500,
                        });
                        return;
                    }
                    const bookings = data.bookings;
                    const table = $('#bookingTable').DataTable();
                    table.clear();

                    bookings.forEach(booking => {
                        table.row.add([
                            booking.formattedBookingID,
                            booking.name,
                            booking.bookingType + ` Booking`,
                            booking.checkIn,
                            booking.checkOut,
                            getStatusBadge(booking.statusClass, booking.status),
                            `<form action="viewBooking.php" method="POST">
                                    <input type="hidden" name="button" value="booking">
                                    <input type="hidden" name="bookingType" value="${booking.bookingType}">
                                    <input type="hidden" name="bookingStatus" value="${booking.bookingStatus}">
                                    <input type="hidden" name="bookingID" value="${booking.bookingID}">
                                    <button type="submit" class="btn btn-primary">View</button>
                            </form>`
                        ]);
                    });

                    table.draw();

                }).catch(error => {
                    console.error("Error loading bookings:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An unknown error occurred.',
                        showConfirmButton: false,
                        timer: 1500,
                    })
                })
        })
    </script>

    <script src="../../Assets/JS/adminNavbar.js"></script>
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


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');

        if (paramValue === "approvedSuccess") {
            Swal.fire({
                position: "top-end",
                title: "Booking Approved!",
                text: "The booking has been successfully approved.",
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
            });
        } else if (paramValue === 'rejectedSuccess') {
            Swal.fire({
                position: "top-end",
                title: "Booking Rejected!",
                text: "The booking has been successfully rejected.",
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
            });
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>
</body>

</html>