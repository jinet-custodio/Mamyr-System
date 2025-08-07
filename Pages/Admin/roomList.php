<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];


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
    <link rel="stylesheet" href="../../Assets/CSS/Admin/roomList.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        <a class="nav-link" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>


        <a class="nav-link active" href="#">
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

        <a class="nav-link" href="editWebsite/landingPageEdit.php">
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
                        <?php foreach ($notificationsArray as $index => $message):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                        <li class="list-group-item mb-2 notification-item"
                            data-id="<?= htmlspecialchars($notificationID) ?>"
                            style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                            <?= htmlspecialchars($message) ?>
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

        <div class="card " style="width: 80%;">
            <table class="table table-striped" id="roomsTable">

                <thead>
                    <th scope="col">Room No.</th>
                    <th scope="col">Status</th>
                    <th scope="col">Rates</th>
                    <th scope="col">Action</th>
                </thead>
                <tbody>
                    <!-- Select booking info -->
                    <?php
                    $hotelCategoryID = 1;
                    $getRoomInfo = $conn->prepare("SELECT rs.*, sa.availabilityName AS roomStatus
                    FROM resortamenities rs 
                    LEFT JOIN serviceAvailability sa ON rs.RSAvailabilityID = sa.availabilityID
                    WHERE RScategoryID = ?
                    ORDER  BY resortServiceID");
                    $getRoomInfo->bind_param("i", $hotelCategoryID);
                    $getRoomInfo->execute();
                    $getRoomInfoResult = $getRoomInfo->get_result();
                    if ($getRoomInfoResult->num_rows > 0) {
                        $rooms = $getRoomInfoResult->fetch_all(MYSQLI_ASSOC);
                        foreach ($rooms as $roomInfo) {
                            $roomID = $roomInfo['resortServiceID'];
                            $statColor = $roomInfo['roomStatus'];
                            // echo '<pre>';
                            // print_r($statColor);
                            // echo '<pre>';
                    ?>
                    <tr>
                        <td>
                            <p style="display: none;"><?= $roomInfo['resortServiceID'] ?> </p>
                            <?= $roomInfo['RServiceName'] ?>
                        </td>
                        <td><button type="button" href="#"
                                class="btn <?= $statColor ?> status-btn"><?= $roomInfo['roomStatus'] ?> </button></td>
                        <td><?= "₱ " . $roomInfo['RSprice'] ?></td>
                        </td>
                        <td>
                            <form action="roomInfo.php" method="POST" style="display:inline;">
                                <input type="hidden" name="roomID" value="<?= $roomID ?>">
                                <input type="hidden" name="actionType" value="edit">
                                <!-- <input type="hidden" name="userID" value="<?= $userID ?>"> -->
                                <button type="submit" class="btn btn-secondary w-20">Edit</button>
                            </form>
                            <form action="roomInfo.php" method="POST" style="display:inline;">
                                <input type="hidden" name="roomID" value="<?= $roomID ?>">
                                <input type="hidden" name="actionType" value="view">
                                <!-- <input type="hidden" name="userID" value="<?= $userID ?>"> -->
                                <button type="submit" class="btn btn-secondary w-20">View</button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
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
        $('#bookingTable').DataTable();
    });
    </script>
</body>

</html>