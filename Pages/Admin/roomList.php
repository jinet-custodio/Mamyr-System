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

if (isset($_SESSION['actionType'])) {
    unset($_SESSION['actionType']);
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
    <link rel="stylesheet" href="../../Assets/CSS/Admin/roomList.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
    <!-- Link to Box Icons and Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div id="sidebar" class=" sidebar sidebar-custom">
        <div class="sbToggle-container d-flex justify-content-center" id="sidebar-toggle">
            <button class="toggle-button" type="button" id="toggle-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>
        </div>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo"
            id="sbLogo">
        <ul class="nav flex-column">
            <li class="nav-item" id="navLI" title="Dashboard">
                <a class="nav-link" href="adminDashboard.php">
                    <i class="bi bi-speedometer2"></i> <span class="linkText">Dashboard</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title=" Bookings">
                <a class="nav-link" href="booking.php">
                    <i class="bi bi-calendar-week"></i><span class="linkText"> Bookings</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Schedule">
                <a class="nav-link" href="schedule.php">
                    <i class="bi bi-calendar-date"></i><span class="linkText">Schedule</span>
                </a>
            </li>
            <li class="nav-item active" id="navLI" title="Rooms">
                <a class="nav-link" href="roomList.php">
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
    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>
    <main>
        <section class="booking-container">
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

            <!-- Room container -->
            <div class="room-container">

                <section class="page-title-container">
                    <h5 class="page-title">Rooms</h5>
                </section>


                <div class="room-container">

                    <div class="card " style="width: 80%;">
                        <div class="addHotelContainer">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addHotelModal" id="addHotelBtn">Add Hotel Room</button>
                        </div>
                        <table class="table table-striped" id="roomsTable">

                            <thead>
                                <th scope="col">Room No.</th>
                                <th scope="col">Status</th>
                                <th scope="col">Rates</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Action</th>
                            </thead>
                            <tbody>
                                <!-- Select booking info -->
                                <?php
                                $hotelCategoryID = 1;
                                $getRoomInfo = $conn->prepare("SELECT rs.*, sa.availabilityName AS roomStatus
                    FROM resortamenity rs 
                    LEFT JOIN serviceavailability sa ON rs.RSAvailabilityID = sa.availabilityID
                    WHERE RScategoryID = ?
                    ORDER  BY resortServiceID");
                                $getRoomInfo->bind_param("i", $hotelCategoryID);
                                $getRoomInfo->execute();
                                $getRoomInfoResult = $getRoomInfo->get_result();
                                if ($getRoomInfoResult->num_rows > 0) {
                                    $rooms = $getRoomInfoResult->fetch_all(MYSQLI_ASSOC);
                                    foreach ($rooms as $roomInfo) {
                                        $roomID = $roomInfo['resortServiceID'];
                                        $roomStatus = $roomInfo['roomStatus'];

                                        switch ($roomStatus) {
                                            case 'Available';
                                                $statColor = 'success';
                                                break;
                                            case 'Maintenance';
                                                $statColor = 'info';
                                                break;
                                            case 'Occupied';
                                                $statColor = 'warning';
                                                break;
                                            case 'Private';
                                                $statColor = 'primary';
                                                break;
                                            case 'Unavailable';
                                                $statColor = 'secondary';
                                                break;
                                            default:
                                                $statColor = 'light';
                                        }
                                        // echo '<pre>';
                                        // print_r($statColor);
                                        // echo '<pre>';
                                ?>
                                        <tr>
                                            <td>
                                                <p style="display: none;"><?= $roomInfo['resortServiceID'] ?> </p>
                                                <?= $roomInfo['RServiceName'] ?>
                                            </td>
                                            <td>
                                                <span class="badge statusBtn bg-<?= $statColor ?>">
                                                    <?= $roomInfo['roomStatus'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= "₱ " . $roomInfo['RSprice'] ?>
                                            </td>
                                            <td>
                                                <?= $roomInfo['RSduration'] ?>
                                            </td>
                                            <td class="action-column">
                                                <form action="roomInfo.php" method="POST" class="w-50">
                                                    <input type="hidden" name="roomID" value="<?= htmlspecialchars($roomID) ?>">
                                                    <input type="hidden" name="actionType" value="edit">
                                                    <button type="submit" class="btn btn-primary actionBtn w-100">Edit</button>
                                                </form>
                                                <form action="roomInfo.php" method="POST" class="w-50">
                                                    <input type="hidden" name="roomID" value="<?= htmlspecialchars($roomID) ?>">
                                                    <input type="hidden" name="actionType" value="view">
                                                    <button type="submit" class="btn btn-info actionBtn w-100">View</button>
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
        </section>
    </main>

    <!-- FORM MODAL ADDING Hotel-->
    <!-- Modal -->
    <form action="../../Function/Admin/Services/addServices.php" method="POST" enctype="multipart/form-data">
        <div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHotelModalLabel">Add Hotel Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-container">
                            <label for="roomName">Room No.</label>
                            <input type="text" class="form-control" id="roomName" name="roomName"
                                placeholder="e.g. Room 1" required>
                        </div>
                        <div class="input-container">
                            <label for="roomStat">Room Status</label>
                            <select id="roomStat" name="roomStat" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <?php

                                $getAvailability = $conn->prepare('SELECT * FROM serviceavailability');
                                if ($getAvailability->execute()) {
                                    $result = $getAvailability->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                ?>
                                            <option value="<?= htmlspecialchars($row['availabilityID']) ?>">
                                                <?= htmlspecialchars($row['availabilityName']) ?></option>
                                <?php
                                        }
                                    }
                                    $result->free();
                                    $getAvailability->close();
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-container">
                            <label for="roomRate">RoomRate</label>
                            <input type="text" class="form-control" id="roomRate" name="roomRate">
                        </div>
                        <div class="input-container">
                            <label for="capacity">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity">
                        </div>
                        <div class="input-container">
                            <label for="maxCapacity">Max Capacity</label>
                            <input type="number" class="form-control" id="maxCapacity" name="maxCapacity">
                        </div>
                        <div class="input-container">
                            <label for="duration">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration">
                        </div>
                        <div class="input-container">
                            <label for="roomDescription">Description</label>
                            <textarea class="form-control" id="roomDescription" name="roomDescription"></textarea>
                        </div>
                        <div class="input-container">
                            <label for="roomImage">Room Image</label>
                            <input type="file" class="form-control" id="roomImage" name="roomImage">
                        </div>
                        <!-- <div class="input-container">
                            <label for="other">Other</label>
                            <input type="text" class="form-control" id="other" name="other">
                        </div> -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveHotelRoom"
                            name="saveHotelRoom">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>


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
            $('#roomsTable').DataTable({
                responsive: false,
                scrollX: true,
                language: {
                    emptyTable: "No Hotel Rooms"
                },
                columnDefs: [{
                    width: "30%",
                    target: 4
                }]

            })
        });
    </script>

    <?php include '../Customer/loader.php'; ?>

</body>

</html>