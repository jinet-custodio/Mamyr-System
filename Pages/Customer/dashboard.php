<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

require '../../Function/functions.php';
require '../../Function/notification.php';
resetExpiredOTPs($conn);
addToAdminTable($conn);
autoChangeStatus($conn);
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

if ($userRole === 2) {
    header("Location: ../BusinessPartner/bpDashboard.php");
    exit();
}


//SQL statement for retrieving data for website content from DB
$sectionName = 'BusinessInformation';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
while ($row = $getWebContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
    $contentID = $row['contentID'];

    $contentMap[$cleanTitle] = $row['content'];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Home </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/Customer/dashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <!-- online stylesheet link for bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half">

        <input type="hidden" id="userRole" value="<?= $userRole ?>">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
            <?php
            $getProfile = $conn->prepare("SELECT firstName, userProfile FROM user WHERE userID = ? AND userRole = ?");
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
            ?>
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

            $notifications = getNotification($conn, $userID, $receiver);
            $counter = $notifications['count'];
            $notificationsArray = $notifications['messages'];
            $color = $notifications['colors'];
            $notificationIDs = $notifications['ids'];
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
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

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a>
                        </li>
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
                    <a class="nav-link" href="bookNow.php">BOOK NOW</a>
                </li>

                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger nav-link" id="logOutBtn">LOG OUT</a>
                </li>

            </ul>
        </div>
    </nav>

    <?php
    include '../notificationModal.php';
    ?>

    <div class="custom-container">
        <div class="topContainer">
            <div class="titleContainer">
                <div class="mamyrTitle">
                    <h1 class="welcome">Welcome to Mamyr,</h1>
                </div>
                <div class="nameOfUserContainer">
                    <h1 class="nameOfUser"><?= ucfirst($firstName) ?></h1>
                </div>
            </div>
            <div class="calendar-container">
                <div class="legends">
                    <div class="legend btn btn-outline-dark">
                        <i class="fa-solid fa-circle" style="color: #dc3545;"></i> Event
                    </div>
                    <div class="legend btn btn-outline-dark">
                        <i class="fa-solid fa-circle" style="color: #ffc107;"></i> Resort/Hotel
                    </div>
                    <div class="legend btn btn-outline-dark">
                        <i class="fa-solid fa-circle" style="color: #007bff;"></i> Resort Entrance
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>


        <div class="welcomeSection">
            <div class="resortPic1">
                <img src="../../../Assets/Images/landingPage/resortPic1.png" alt="Mamyr Resort" class="pic1">
            </div>
            <div class="wsText">
                <hr class="line">
                <h4 class="wsTitle">Welcome to <?= htmlspecialchars($contentMap['FullName'] ?? 'Name Not Found') ?></h4>
                <p class="wsDescription">Welcome to Mamyr Resort and Events Place, where relaxation and unforgettable
                    moments await you. Whether you're here for a peaceful retreat or a special celebration, we're
                    dedicated to making your experience truly exceptional.</p>
            </div>

        </div>

        <div class="contact">
            <div class="contactText">
                <hr class="line">
                <h4 class="contactTitle">Contact Us </h4>

                <div class="location contacts">
                    <img src="../../Assets/Images/landingPage/icons/location.png" alt="locationPin" class="locationIcon">
                    <h5 class="locationText"><?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?></h5>
                </div>

                <div class="number contacts">
                    <img src="../../Assets/Images/landingPage/icons/phone.png" alt="phone" class="phoneIcon">
                    <h5 class="number"><?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?></h5>
                </div>

                <div class="email contacts">
                    <img src="../../Assets/Images/landingPage/icons/email.png" alt="email" class="emailIcon">
                    <h5 class="emailAddressText"><?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?></h5>
                </div>

            </div>
            <div class="googleMap" id="googleMap"></div>
        </div>

        <div class="gallery">
            <hr class="line">
            <h4 class="galleryTitle">Gallery </h4>
            <div class="galleryPictures">
                <img src="../../Assets/Images/landingPage/img1.png" alt="resort View 1" class="img1 galleryImg">
                <img src="../../Assets/Images/landingPage/img2.png" alt="resort View 2" class="img2 galleryImg">
                <img src="../../Assets/Images/landingPage/img3.png" alt="resort View 3" class="img3 galleryImg">
                <img src="../../Assets/Images/landingPage/img4.png" alt="resort View 4" class="img4 galleryImg">
                <img src="../../Assets/Images/landingPage/img5.png" alt="resort View 5" class="img5 galleryImg">
                <img src="../../Assets/Images/landingPage/img6.png" alt="resort View 6" class="img6 galleryImg">
            </div>

            <div class="seeMore">
                <a href="../amenities.php" class="btn btn-primary w-100">See More</a>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->

    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <script>
        function myMap() {
            var mapProp = {
                center: new google.maps.LatLng(15.050861525959231, 121.02183364955998),
                zoom: 5,
            };
            var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: '../../Function/fetchUserBookings.php',
                eventsSet: function(events) {
                    console.log('Fetched events:', events);
                    events.forEach(event => {
                        console.log(`Title: ${event.title}, Start: ${event.startStr}`);
                    });
                },
                eventClick: function(info) {
                    window.location.href = "/Pages/Customer/Account/bookingHistory.php";
                },
                eventDidMount: function(info) {
                    if (info.event.allDay) {
                        const dateStr = info.event.startStr;
                        const dayCell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
                        if (dayCell) {
                            let baseColor = info.event.backgroundColor || info.event.extendedProps.color || '#dc3545';
                            dayCell.style.backgroundColor = baseColor;
                            dayCell.style.color = '#000';
                        }
                        if (info.el) {
                            info.el.style.display = 'none';
                        }
                    }
                }
            });

            calendar.render();
        });
    </script>
    <script src="../../Assets/JS/scrollNavbg.js">
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCalqMvV8mz7fIlyY51rxe8IerVxzUTQ2Q&callback=myMap">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>


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


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const businessPartnerNavLink = document.getElementById("businessPartnerNav");
            const userRoleValue = document.getElementById("userRole").value;

            if (userRoleValue === "2") {
                businessPartnerNavLink.style.display = "none";
            }
        });
    </script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        if (paramValue === 'successLogin') {
            Swal.fire({
                title: "Login Successful!",
                text: "Welcome back! You have successfully logged in.",
                icon: "success",
            })
        };

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>


</body>

</html>