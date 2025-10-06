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
    <link rel="stylesheet" href="../../Assets/CSS/Admin/navbar.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
    <!-- Font Awesome and Box Icon links  -->
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
            <ul class="navbar-nav w-100 me-10 d-flex justify-content-around" id="navUL">

                <li class="nav-item">
                    <a class="nav-link" href="adminDashboard.php">
                        <i class="fa-solid fa-grip navbar-icon"></i>
                        <h5>Dashboard</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="booking.php">
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


    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <!-- Booking-container -->
    <h1 class="title text-center my-3" style="display: none;" id="hiddenTitle">Bookings</h1>

    <div class="booking-container">
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>

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
        <?php if (!empty($message)): ?>
            Swal.fire({
                icon: '<?= $status ?>',
                title: '<?= ($status == 'error') ? 'Rejected' : 'Success' ?>',
                text: '<?= $message ?>'
            });
        <?php endif; ?>


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