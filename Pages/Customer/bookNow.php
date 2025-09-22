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

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

unset($_SESSION['hotelFormData']);
unset($_SESSION['resortFormData']);
unset($_SESSION['eventFormData']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/bookNow.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="body">

    <?php
    $emailQuery = $conn->prepare("SELECT email, phoneNumber,userProfile FROM user WHERE userID = ? and userRole = ?");
    $emailQuery->bind_param("ii", $userID, $userRole);
    $emailQuery->execute();
    $emailResult = $emailQuery->get_result();
    if ($emailResult->num_rows > 0) {
        $data =  $emailResult->fetch_assoc();
        $email = $data['email'];
        $phoneNumber = $data['phoneNumber'];

        $imageData = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

        if ($phoneNumber === NUll || $phoneNumber === "--") {
            $phoneNumber = NULL;
        } else {
            $phoneNumber;
        }
    } else {
        echo 'No Email Found';
    }
    ?>

    <input type="hidden" name="phoneNumber" id="phoneNumber" value="<?= $phoneNumber ?>">

    <nav class="navbar navbar-expand-lg fixed-top" id="navbar" style="background-color: rgba(255, 255, 255, 0.562);">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center gap-2" id="profileAndNotif">
            <li class="nav-item account-nav">
                <a href="../Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                </a>
            </li>

            <!-- Get notification -->
            <?php

            if ($userRole === 1 || $userRole === 4) {
                $receiver = 'Customer';
            } elseif ($userRole === 2) {
                $receiver = 'Partner';
            }

            $getNotifications = $conn->prepare("SELECT * FROM notification WHERE userID = ? AND receiver = ? AND is_read = 0");
            $getNotifications->bind_param("is", $userID, $receiver);
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
        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <?php if ($userRole !== 2): ?>
                        <a class="nav-link" href="dashboard.php"> Home</a>
                    <?php else: ?>
                        <a class="nav-link" href="../BusinessPartner/bpDashboard.php"> Home</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item " href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <?php if ($userRole !== 2): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="bookNow.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">LOG OUT</a>
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

    <!-- Made every section visible except for the selection section to see the errors -->
    <div class="categories-page" id="category-page">
        <div class="titleContainer" style="margin-top: 10vw !important;">
            <h4 class="title">What are you booking for?</h4>
        </div>
        <div class="categories">
            <a href="resortBooking.php" id="resort-link" class="categoryLink">
                <div class="card category-card resort-category"
                    style="width: 20rem; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../Assets/images/amenities/poolPics/poolPic3.jpg"
                        alt="Wedding Event">

                    <div class="category-body">
                        <h5 class="category-title">RESORT</h5>
                    </div>
                </div>
            </a>
            <a href="hotelBooking.php" id="hotel-link" class="categoryLink">
                <div class="card category-card hotel-category"
                    style="width: 20rem; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../Assets/images/amenities/hotelPics/hotel1.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">HOTEL</h5>
                    </div>
                </div>
            </a>
            <a href="eventBooking.php" id="event-link" class="categoryLink">
                <div class="card category-card event-category"
                    style="width: 20rem; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../Assets/images/amenities/pavilionPics/pav4.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">EVENT</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>


    <!-- Phone Number Modal -->
    <form action="../../Function/getPhoneNumber.php" method="POST">
        <div class="modal fade" id="phoneNumberModal" data-bs-backdrop="static" tabindex=" -1"
            aria-labelledby="phoneNumberModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="phoneNumberModalLabel">Required Phone Number</h5>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">Phone number is required before booking please enter your phone number
                        </p>
                        <input type="tel" name="phoneNumber" id="phoneNumber" class="form-control w-100 mt-2"
                            placeholder="+63 9XX XXX XXXX" pattern="^(?:\+63|0)9\d{9}$"
                            title="e.g., +639123456789 or 09123456789" required>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="submitPhoneNumber">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php
    include 'footer.php'
    ?>

    <!-- Full Calendar for Date display -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js"></script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sweetalert Popup -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        if (paramValue === 'success') {
            Swal.fire({
                title: "Successful Booking!",
                text: "Your request has been sent, please wait for the admin 's approval. Please check your account for more info. Thank You!",
                icon: "success",
                confirmButtonText: 'View',
                showCloseButton: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../Account/bookingHistory.php';
                }
            });
        }
        if (paramValue === 'bookNow') {
            Swal.fire({
                title: "Success!",
                text: "Your phone number has been submitted successfully. You may now proceed with booking.",
                icon: "success",
                confirmButtonText: "Okay"
            })
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>


    <!-- For checking the phone Number -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const phoneNumber = document.getElementById("phoneNumber").value;

            if (phoneNumber === '') {
                const phoneNumberModal = new bootstrap.Modal(document.getElementById('phoneNumberModal'));
                phoneNumberModal.show();
            }


        });
    </script>

</body>

</html>