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

if (isset($_SESSION['actionType'])) {
    unset($_SESSION['actionType']);
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

    <!-- Room container -->

    <div class="room-container">

        <div class="card " style="width: 80%;">
            <div class="addHotelContainer">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHotelModal"
                    id="addHotelBtn">Add Hotel Room</button>
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
                    FROM resortamenities rs 
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


    <!-- FORM MODAL ADDING Hotel-->
    <!-- Modal -->
    <form action="../../Function/Admin/Services/addServices.php" method="POST" enctype="multipart/form-data">
        <div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHotelModalLabel">Add Hotel Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-container">
                            <label for="roomName">Room No.</label>
                            <input type="text" class="form-control" id="roomName" name="roomName" placeholder="e.g. Room 1" required>
                        </div>
                        <div class="input-container">
                            <label for="roomStat">Room Status</label>
                            <select id="roomStat" name="roomStat" class="form-select"
                                required>
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
                        <button type="submit" class="btn btn-primary" id="saveHotelRoom" name="saveHotelRoom">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>



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