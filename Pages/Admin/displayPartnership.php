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

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

$message = '';
$status = '';

if (isset($_SESSION['error-partnership'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['error-partnership']));
    $status = 'error';
    unset($_SESSION['error-partnership']);
} elseif (isset($_SESSION['success-partnership'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['success-partnership']));
    $status = 'success';
    unset($_SESSION['success-partnership']);
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/displayPartnership.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/navbar.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Box Icons and Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
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
            $getNotifications = $conn->prepare("SELECT * FROM notification WHERE receiver = ? AND is_read = 0");
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
                $_SESSION['error'] = "Unauthorized Access eh!";
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
            <ul class="navbar-nav w-100 me-10 d-flex justify-content-around px-2" id="navUL">

                <li class="nav-item">
                    <a class="nav-link " href="adminDashboard.php">
                        <i class="fa-solid fa-grip navbar-icon"></i>
                        <h5>Dashboard</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="booking.php">
                        <i class="fa-solid fa-calendar-days navbar-icon"></i>
                        <h5>Bookings</h5>
                    </a>
                </li>

                <li class="nav-item ">
                    <a class="nav-link " href="roomList.php">
                        <i class="fa-solid fa-hotel navbar-icon"></i>
                        <h5>Rooms</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="services.php">
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
                        <h5>Revenue</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="displayPartnership.php">
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

    <div class="categories" id="choice-container">

        <a href="#" id="partner-link" class="categoryLink">
            <div class="card category-card d-flex" style="flex-direction: column;">
                <img class="card-img-top" src="../../Assets/Images/AdminImages/DisplayPartnershipImages/partners.jpg"
                    alt="Partners">

                <div class="category-body">
                    <h5 class="category-title m-auto">PARTNERS</h5>
                </div>
            </div>
        </a>

        <a href="#" id="request-link" class="categoryLink">
            <div class="card category-card d-flex" style="flex-direction: column;">
                <img class="card-img-top" src="../../Assets/Images/AdminImages/DisplayPartnershipImages/request.jpg"
                    alt="Requests">

                <div class="category-body">
                    <h5 class="category-title m-auto">REQUESTS</h5>
                </div>
            </div>
        </a>

    </div>


    <!-- Display when Partner is Click -->
    <div class="partner-container" id="partner-container" style="display: none;">
        <!-- Partners Table  -->
        <div class="partnership-table">

            <div class="card" id="partner-card" style="width: 80rem;">

                <!-- Back Button -->
                <div class="back-btn-container">
                    <a href="#" id="choice1-link" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left backArrow" style="color: #f6f6f6ff;"></i>
                    </a>

                </div>
                <h4 class="fw-bold page-title">Partners</h4>
                <table class="table table-striped display nowrap" id="partnersTable">
                    <thead>
                        <tr>
                            <th class="table-header wrap-date" scope="col">Name</th>
                            <th class="table-header" scope="col">Partner Type</th>
                            <th class="table-header wrap-date" scope="col">Date Applied</th>
                            <th class="table-header" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <!-- Select to display all the applicants  -->
                        <?php
                        $partner = 2;
                        // $rejectedStatus = 3;
                        $selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName,  GROUP_CONCAT(pt.partnerTypeDescription SEPARATOR ' & ') AS partnerTypeDescription
                                FROM partnership p
                                INNER JOIN user u ON p.userID = u.userID
                                INNER JOIN status s ON s.statusID = p.partnerStatusID
                                LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
                                LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
                                WHERE u.userRole = ?
                                GROUP BY 
                                p.partnershipID
                                ");
                        $selectQuery->bind_param("i", $partner);
                        $selectQuery->execute();
                        $result = $selectQuery->get_result();
                        if ($result->num_rows > 0) {
                            foreach ($result as $applicants) {
                                $name = ucwords($applicants['firstName'] ?? "") . " Secret" . ucwords($applicants['lastName'] ?? "");
                                $partnerID = $applicants['partnershipID'];
                                $status = $applicants['statusName'];
                                $date = $applicants['startDate'];
                                $startDate = !empty($date) ? date("F d, Y - g:i A", strtotime($date)) : "N/A";

                        ?>
                                <tr>
                                    <td scope="row" class="wrap-date" id="nameTD"><?= $name ?></td>

                                    <td scope="row"><?= ucfirst($applicants['partnerTypeDescription'] ?? "Photographer")  ?></td>

                                    <td scope="row" class="wrap-date">
                                        <?= $startDate ?>
                                    </td>

                                    <td scope="row">
                                        <?php
                                        $partner = 3;
                                        // $partnerContainer = base64_encode($partner);
                                        ?>
                                        <form action="partnership.php?container=<?= $partner ?>" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                                            <button type="submit" class="btn btn-info w-100" name="view-btn">View</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <h5>No Record Found!</h5>
                                </td>
                            </tr>

                        <?php
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Display when Request is Click -->
    <div class="request-container" id="request-container" style="display: none;">

        <!-- Partnership Request Table  -->
        <div class="partnership-request-table">

            <div class="card" id="request-card" style="width: 80rem;">
                <!-- Back Button -->
                <div class="back-btn-container">
                    <a href="#" id="choice2-link" class="btn btn-primary ">
                        <i class="fa-solid fa-arrow-left backArrow" style="color: #f7f7f7ff;" id="emailBackArrow"></i>
                    </a>

                </div>
                <h4 class="fw-bold page-title">Applicant Requests</h4>
                <table class="table table-striped display nowrap" id="requestTable">
                    <thead>
                        <tr>
                            <th class="table-header" scope="col">Name</th>
                            <th class="table-header" scope="col">Partner Type</th>
                            <th class="table-header wrap-date" scope="col">Request Date</th>
                            <th class="table-header" scope="col">Status</th>
                            <th class="table-header" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <!-- Select to display all the applicants  -->
                        <?php
                        $pendingStatus = 1;
                        $rejectedStatus = 3;
                        $applicant = 4;
                        $selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName,  GROUP_CONCAT(pt.partnerTypeDescription SEPARATOR ' & ') AS partnerTypeDescription
                                FROM partnership p
                                INNER JOIN user u ON p.userID = u.userID
                                INNER JOIN status s ON s.statusID = p.partnerStatusID
                                LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
                                LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
                                WHERE p.partnerStatusID = ? OR p.partnerStatusID = ? AND u.userRole = ?
                                GROUP BY 
                                p.partnershipID
                                ");
                        $selectQuery->bind_param("iii", $pendingStatus, $rejectedStatus, $applicant);
                        $selectQuery->execute();
                        $result = $selectQuery->get_result();
                        if ($result->num_rows > 0) {
                            foreach ($result as $applicants) {
                                $name = ucwords($applicants['firstName'] ?? "") . " " . ucwords($applicants['lastName'] ?? "");
                                $partnerID = $applicants['partnershipID'];
                                $status = $applicants['statusName'];
                                $date = $applicants['requestDate'];
                                $requestDate = date("F d, Y - g:i A", strtotime($date));
                        ?>
                                <tr>
                                    <td scope="row" class="wrap-date"><?= $name ?></td>

                                    <td scope="row"><?= ucfirst($applicants['partnerTypeDescription'] ?? "Photographer & Videographer")  ?></td>
                                    <td scope="row" class="wrap-date"><?= htmlspecialchars($requestDate) ?></td>
                                    <?php
                                    if ($status == "Pending") {
                                    ?>
                                        <td scope="row" class="btn btn-warning w-100 d-block m-auto mt-1"
                                            style="background-color:#ffc108 ;">
                                            <?= $status ?>
                                        </td>
                                    <?php
                                    } else if ($status == "Rejected") {
                                    ?>
                                        <td scope="row" class="btn btn-danger w-100 d-block m-auto mt-1"
                                            style="background-color:#FF0000; color:#ffff ;">
                                            <?= $status ?>
                                        </td>
                                    <?php
                                    }
                                    ?>
                                    <td scope="row">
                                        <?php
                                        $applicant = 4;
                                        // $applicantContainer = base64_encode($applicant);
                                        ?>
                                        <form action="partnership.php?container=<?= $applicant ?>" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                                            <button type="submit" class="btn btn-info w-100" name="view-partner">View</button>
                                        </form>

                                    </td>
                                    </td>
                                <?php
                            }
                        } else {
                                ?>
                                <td colspan="5">
                                    <h5 scope="row" class="text-center">No Record Found!</h5>
                                </td>
                            <?php
                        } ?>
                    </tbody>
                </table>
            </div>
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
    </script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $('#requestTable').DataTable({
            responsive: false,
            scrollX: true,
            columnDefs: [{
                    width: '20%',
                    targets: 0
                },
                {
                    width: '20%',
                    targets: 1
                },
                {
                    width: '25%',
                    targets: 2
                },
                {
                    width: '20%',
                    targets: 3
                },
                {
                    width: '15%',
                    targets: 4
                },

            ],
        });

        $('#partnersTable').DataTable({
            responsive: false,
            scrollX: true,
            columnDefs: [{
                    width: '25%',
                    targets: 0
                },
                {
                    width: '25%',
                    targets: 1
                },
                {
                    width: '25%',
                    targets: 2
                },
                {
                    width: '25%',
                    targets: 3
                }
            ],
        });
    </script>
    <!-- Pages hide/show -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Script loaded");
            const requestLink = document.getElementById("request-link");
            const partnerLink = document.getElementById("partner-link");

            const choices = document.getElementById("choice-container");
            const choice1Link = document.getElementById("choice1-link");
            const choice2Link = document.getElementById("choice2-link");
            const partner_Container = document.getElementById("partner-container");
            const request_Container = document.getElementById("request-container");
            const partner_Card = document.getElementById("partner-card");
            const request_Card = document.getElementById("request-card");

            requestLink.addEventListener('click', function(event) {
                event.preventDefault();
                console.log("Request link clicked");
                choices.style.display = "none";
                partner_Container.style.display = "none";
                request_Container.style.display = "block";
                partner_Card.style.display = "none";
                request_Card.style.display = "block";
            });

            partnerLink.addEventListener('click', function(event) {
                event.preventDefault();
                console.log("Request link clicked");
                choices.style.display = "none";
                partner_Container.style.display = "block";
                request_Container.style.display = "none";
                partner_Card.style.display = "block";
                request_Card.style.display = "none";
            });

            choice1Link.addEventListener('click', function(event) {
                event.preventDefault();
                choices.style.display = "flex";
                partner_Container.style.display = "none";
                request_Container.style.display = "none";
                partner_Card.style.display = "none";
                request_Card.style.display = "none";
            });

            choice2Link.addEventListener('click', function(event) {
                event.preventDefault();
                choices.style.display = "flex";
                partner_Container.style.display = "none";
                request_Container.style.display = "none";
                partner_Card.style.display = "none";
                request_Card.style.display = "none";
            });
        });
    </script>
    <script src="../../Assets/JS/adminNavbar.js"></script>
    <!-- Search URL -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('container');
        const action = params.get("action");

        const choices = document.getElementById("choice-container");
        const partnerContainer = document.getElementById("partner-container");
        const requestContainer = document.getElementById("request-container");
        const partnerCard = document.getElementById("partner-card");
        const requestCard = document.getElementById("request-card");

        if (paramValue == 1) {
            choices.style.display = "none";
            partnerContainer.style.display = "block";
            requestContainer.style.display = "none";
            partnerCard.style.display = "block";
            requestCard.style.display = "none";
        } else if (paramValue == 2) {
            choices.style.display = "none";
            partnerContainer.style.display = "none";
            requestContainer.style.display = "block";
            partnerCard.style.display = "none";
            requestCard.style.display = "block";
        }



        if (action === "approved") {
            Swal.fire({
                icon: 'success',
                title: 'Partnership Approved',
                text: 'The partnership request has been approved successfully.'
            });
        }

        if (paramValue || action) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <!-- Sweetalert Popup -->
    <script>
        <?php if (!empty($message)): ?>
            Swal.fire({
                icon: '<?= $status ?>',
                title: '<?= ($status == 'error') ? 'Rejected' : 'Success' ?>',
                text: '<?= $message ?>'
            });
        <?php endif; ?>
    </script>



</body>

</html>