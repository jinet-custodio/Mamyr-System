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
    <main>
        <div id="sidebar">
            <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo">
            <ul class="nav flex-column">
                <li class="nav-item">
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
                <li class="nav-item active">
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

        <section class="booking-container">

            <section class="notification-container">
                <i class="bi bi-bell" id="notification-icon"></i>
            </section>

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
                                    <button type="button" class="btn statusBtn btn-<?= $statColor ?>">
                                        <?= $roomInfo['roomStatus'] ?>
                                    </button>
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

    <!-- Responsive Navbar -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const icons = document.querySelectorAll('.navbar-icon');
        const navbarUL = document.getElementById('navUL');
        const nav = document.getElementById('navbar')

        function handleResponsiveNavbar() {
            if (window.innerWidth <= 991.98) {
                navbarUL.classList.remove('w-100');
                navbarUL.style.position = "fixed";
                nav.style.margin = "0";
                nav.style.maxWidth = "100%";
                icons.forEach(icon => {
                    icon.style.display = "none";
                })
            } else {
                navbarUL.classList.add('w-100');
                navbarUL.style.position = "relative";
                nav.style.margin = "20px auto";
                nav.style.maxWidth = "80vw";
                icons.forEach(icon => {
                    icon.style.display = "block";
                })
            }
        }

        handleResponsiveNavbar();
        window.addEventListener('resize', handleResponsiveNavbar);
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

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#roomsTable').DataTable({
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






</body>

</html>