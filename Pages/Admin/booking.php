<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
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
            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE receiver = ? AND is_read = 0");
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
                $getProfile = $conn->prepare("SELECT firstName,userProfile FROM users WHERE userID = ? AND userRole = ?");
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

    <nav class="navbar">

        <a class="nav-link" href="adminDashboard.php">
            <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
            <h5>Dashboard</h5>
        </a>

        <a class="nav-link active" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>


        <a class="nav-link" href="roomList.php">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link" href="services.php">
            <img src="../../Assets/Images/Icon/servicesAdminNav.png" alt="Services">
            <h5>Services</h5>
        </a>

        <a class="nav-link" href="transaction.php">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <a class="nav-link" href="revenue.php">
            <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
            <h5>Revenue</h5>
        </a>


        <a class="nav-link" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="editWebsite/editWebsite.php">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

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
                            <?php foreach ($notificationsArray as $index => $notifMessage):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                                <li class="list-group-item mb-2 notification-item"
                                    data-id="<?= htmlspecialchars($notificationID) ?>"
                                    style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                                    <?= htmlspecialchars($notifMessage) ?>
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

    <!-- Booking-container -->

    <div class="booking-container">
        <div class="card" style="width: 80rem;">

            <div class="btnContainer">
                <a href="createBooking.php" class="btn btn-primary">Create</a>
            </div>

            <table class="table table-striped" id="bookingTable">
                <thead>
                    <th scope="col">Booking ID</th>
                    <th scope="col">Guest</th>
                    <th scope="col">Booking Type</th>
                    <th scope="col">Check-in</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </thead>
                <tbody>
                    <!-- Select booking info -->
                    <?php
                    $getBookingInfo = $conn->prepare("SELECT LPAD(b.bookingID, 4, 0) AS formattedBookingID, u.firstName,u.middleInitial, u.lastName, b.*,
                    cp.*,
                    cb.*, s.statusName AS confirmedStatus, stat.statusName as bookingStatus
                                    FROM bookings b
                                    INNER JOIN users u ON b.userID = u.userID   -- to get  the firstname, M.I and lastname 
                                    LEFT JOIN confirmedbookings cb ON b.bookingID = cb.bookingID 
                                    LEFT JOIN statuses s ON cb.paymentApprovalStatus = s.statusID -- to get the status name
                                    LEFT JOIN statuses stat ON b.bookingStatus = stat.statusID  -- to get the status name 
                                    LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID  -- info of the custom package
                                    ");
                    $getBookingInfo->execute();
                    $getBookingInfoResult = $getBookingInfo->get_result();
                    if ($getBookingInfoResult->num_rows > 0) {
                        while ($bookings = $getBookingInfoResult->fetch_assoc()) {
                            // echo "<pre>";
                            // print_r($bookings);
                            // echo "</pre>";
                            $bookingID = $bookings['formattedBookingID'];
                            $startDate = strtotime($bookings['startDate']);
                            $checkIn = date("F d, Y", $startDate);
                            $middleInitial = trim($bookings['middleInitial'] ?? '');
                            $name = ucfirst($bookings['firstName']) . " " . ucfirst($middleInitial) . " "  . ucfirst($bookings['lastName']);

                            $bookingType = $bookings['bookingType'];

                            if (!empty($bookings['confirmedBookingID'])) {
                                if ($bookings['confirmedStatus'] === "Pending") {
                                    if ($bookingType === 'Resort') {
                                        $status = "Onsite payment";
                                        $addClass = "btn btn-info w-100";
                                    } else {
                                        $status = "Downpayment";
                                        $addClass = "btn btn-primary w-100";
                                    }
                                } elseif ($bookings['confirmedStatus'] === "Approved") {
                                    $status = "Successful";
                                    $addClass = "btn btn-success w-100";
                                } elseif ($bookings['confirmedStatus'] === "Rejected") {
                                    $status = "Rejected";
                                    $addClass = "btn btn-danger w-100";
                                } elseif ($bookings['confirmedStatus'] === "Done") {
                                    $status = "Done";
                                    $addClass = "btn btn-success w-100";
                                }
                            } else {
                                $confirmedBookingID = NULL;
                                if ($bookings['bookingStatus'] === "Pending") {
                                    $status = "Pending";
                                    $addClass = "btn btn-warning w-100";
                                } else if ($bookings['bookingStatus'] === "Approved") {
                                    if ($bookingType === 'Resort') {
                                        $status = "Onsite payment";
                                        $addClass = "btn btn-info w-100";
                                    } else {
                                        $status = "Downpayment";
                                        $addClass = "btn btn-primary w-100";
                                    }
                                } elseif ($bookings['bookingStatus'] === "Cancelled") {
                                    $status = "Cancelled";
                                    $addClass = "btn btn-dark w-100";
                                } elseif ($bookings['bookingStatus'] === "Rejected") {
                                    $status = "Rejected";
                                    $addClass = "btn btn-danger w-100";
                                } elseif ($bookings['bookingStatus'] === "Expired") {
                                    $status = "Expired";
                                    $addClass = "btn btn-secondary w-100";
                                }
                            }
                    ?>
                            <tr>
                                <td><?= htmlspecialchars($bookingID) ?></td>
                                <td><?= htmlspecialchars($name) ?></td>
                                <td><?= htmlspecialchars($bookingType) ?>&nbsp;Booking</td>
                                <td><?= $checkIn ?></td>
                                <td>
                                    <a class=" <?= $addClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </a>
                                </td>
                                <td>
                                    <form action="viewBooking.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                                        <input type="hidden" name="bookingStatus"
                                            value="<?= !empty($bookings['bookingStatus']) ? !empty($bookings['bookingStatus']) : !empty($bookings['confirmedStatus'])  ?>">
                                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                                        <button type="submit" class="btn btn-primary w-75">View</button>
                                    </form>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
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

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#bookingTable').DataTable({
                columnDefs: [{
                        width: '10%',
                        targets: 0
                    },
                    {
                        width: '15%',
                        targets: 2
                    },
                    {
                        width: '15%',
                        targets: 4
                    },
                ],
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

        if (paramValue === "success") {
            Swal.fire({
                title: "Booking Approved!",
                text: "The booking has been successfully approved.",
                icon: 'success',
            });
        } else if (paramValue === "error") {
            Swal.fire({
                title: "Action Failed!",
                text: "The booking could not be approved or rejected. Please try again later.",
                icon: 'error',
            });
        } else if (paramValue === 'rejected') {
            Swal.fire({
                title: "Booking Rejected!",
                text: "The booking has been successfully rejected.",
                icon: 'success',
            });
        }

        if (paramValue) {
            const url = new URL(windows.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString)
        }
    </script>
</body>

</html>