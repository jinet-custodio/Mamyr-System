<?php
error_reporting(0);
ini_set('display_errors', 0);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();
//for setting image paths in 'include' statements
$baseURL = '../..';

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
    <?php
    if ($role === "Admin") {
        $getProfile = $conn->prepare("SELECT firstName,lastName, userProfile FROM user WHERE userID = ? AND userRole = ?");
        $getProfile->bind_param("ii", $userID, $userRole);
        $getProfile->execute();
        $getProfileResult = $getProfile->get_result();
        if ($getProfileResult->num_rows > 0) {
            $data = $getProfileResult->fetch_assoc();
            $adminName = ($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '');

            $imageData = $data['userProfile'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            // finfo_close($finfo);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }
    } else {
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
    ?>


    <div id="sidebar" class="sidebar sidebar-custom">
        <div class="sbToggle-container d-flex justify-content-center" id="sidebar-toggle">
            <button class="toggle-button" type="button" id="toggle-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>
        </div>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo" id="sbLogo">
        <ul class="nav flex-column">
            <li class="nav-item" id="navLI" title="Dashboard">
                <a class="nav-link" href="adminDashboard.php">
                    <i class="bi bi-speedometer2"></i> <span class="linkText">Dashboard</span>
                </a>
            </li>
            <li class="nav-item active" id="navLI" title=" Bookings">
                <a class="nav-link" href="booking.php">
                    <i class="bi bi-calendar-week"></i><span class="linkText"> Bookings</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Payments">
                <a class="nav-link" href="transaction.php">
                    <i class="bi bi-credit-card-2-front"></i> <span class="linkText">Payments</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Schedule">
                <a class="nav-link" href="schedule.php">
                    <i class="bi bi-calendar-date"></i><span class="linkText">Schedule</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Rooms">
                <a class="nav-link" href="roomList.php">
                    <i class="bi bi-door-open"></i> <span class="linkText">Rooms</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Services">
                <a class="nav-link" href="services.php">
                    <i class="bi bi-bell"></i> <span class="linkText">Services</span>
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
                <img src="<?= $image ?>" alt="Admin Profile" class="rounded-circle profilePic">
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
    <main>
        <!-- Booking-container -->
        <section class="booking-container">
            <section class="notification-toggler-container">
                <div class="notification-container position-relative">
                    <button type="button" class="btn position-relative" data-bs-toggle="modal"
                        data-bs-target="#notificationModal" id="notificationButton">
                        <i class="bi bi-bell" id="notification-icon"></i>
                    </button>
                </div>

                <div class="hidden-inputs" style="display: none;">
                    <input type="hidden" id="receiver" value="<?= $role ?>">
                    <input type="hidden" id="userID" value="<?= $userID ?>">
                </div>
            </section>

            <section class="page-title-container">
                <h5 class="page-title">Bookings</h5>
            </section>

            <!-- <h1 class="title text-center my-3" style="display: none;" id="hiddenTitle">Bookings</h1> -->

            <div class="booking-table">
                <div class="card">
                    <div class="btnContainer">
                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select" name="booking-filter-select" id="booking-filter-select">
                                    <!-- <option selected disabled>Filters</option> -->
                                    <option value="all">All Bookings</option>
                                    <option value="pending">Awaiting Review</option>
                                    <option value="incoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="finished">Completed</option>
                                    <option value="expired">Cancelled / Rejected / Expired</option>
                                </select>
                                <i class="bi bi-filter"></i>
                            </div>
                        </div>

                        <a href="createBooking.php" class="btn btn-primary" id="addBookings">Add</a>
                    </div>

                    <table class="table table-striped display nowrap" id="bookingTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking Code</th>
                                <th>Guest</th>
                                <th>Type</th>
                                <th>Reserved On</th>
                                <th>Created On</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id='booking-display-body'></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <?php include '../Notification/notification.php' ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>

    <!-- Table JS -->
    <script>
    $('#bookingTable').DataTable({
        responsive: false,
        scrollX: true,
        "order": [],
        columnDefs: [{
                width: '5%',
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
        const filterSelected = document.getElementById('booking-filter-select');
        getBookings(filterSelected.value);
        filterSelected.addEventListener('change', () => {
            getBookings(filterSelected.value);
            // console.log(filterSelected.value);
        });
        // console.log(filterSelected.value);
    });



    function getBookings(filterSelectedValue) {
        fetch(`../../Function/Admin/Ajax/getBookingsJSON.php?filter=${encodeURIComponent(filterSelectedValue)}`)
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
                        booking.bookingID,
                        booking.bookingCode,
                        booking.name,
                        booking.bookingType + ` Booking`,
                        booking.bookingDate,
                        booking.createdOn,
                        // booking.checkOut,
                        getStatusBadge(booking.statusClass, booking.status),
                        `<form action="viewBooking.php" method="POST">
                                    <input type="hidden" name="button" value="booking">
                                    <input type="hidden" name="bookingType" value="${booking.bookingType}">
                                    <input type="hidden" name="bookingStatus" value="${booking.bookingStatus}">
                                    <input type="hidden" name="bookingID" value="${booking.bookingID}">
                                    <button type="submit" class="btn btn-primary viewBooking">View</button>
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
    }
    </script>

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
    <?php include '../Customer/loader.php'; ?>
</body>

</html>