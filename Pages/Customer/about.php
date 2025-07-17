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

//SQL statement for retrieving data for website content from DB
$sectionName = 'About';
$getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
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
    <title>Mamyr - About</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/about.css">
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <!-- Online link for Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icon library from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>


<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav">
            <?php

            $getProfile = $conn->prepare("SELECT userProfile FROM users WHERE userID = ? AND userRole = ?");
            $getProfile->bind_param("ii", $userID, $userRole);
            $getProfile->execute();
            $getProfileResult = $getProfile->get_result();
            if ($getProfileResult->num_rows > 0) {
                $data = $getProfileResult->fetch_assoc();
                $imageData = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>
            <li class="nav-item account-nav">
                <a href="Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile">
                </a>
            </li>


            <!-- Get notification -->
            <?php

            if ($userRole === 1) {
                $receiver = 'Customer';
            } elseif ($userRole === 2) {
                $receiver = 'Partner';
            }

            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE userID = ? AND receiver = ? AND is_read = 0");
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

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item active" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/Customer/blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/Pages/Customer/about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookNow.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">LOG OUT</a>
                </li>
            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
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
                                <li class="list-group-item mb-2 notification-item" data-id="<?= htmlspecialchars($notificationID) ?>" style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
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

    <div class="titleContainer">
        <h1 class="title" id="title">ABOUT US</h1>
    </div>

    <div class="aboutTopContainer" id="aboutTopContainer">
        <div class="topPicContainer">
            <img src="../../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture" class="resortPic">
        </div>

        <div class="topTextContainer">
            <h3 class="hook"><?= htmlspecialchars($contentMap['Header'] ?? 'Header Not Found') ?> </h3>

            <p class="aboutDescription indent"><?= htmlspecialchars($contentMap['AboutMamyr'] ?? 'No description Not Found') ?></p>

            <a href="#backArrowContainer"><button class="btn btn-primary" onclick="readMore()">Read More</button></a>
        </div>
    </div>

    <div class="ourServicesContainer" id="ourServicesContainer">
        <div class="servicesTitleContainer">
            <h3 class="servicesTitle">Our Services</h3>
            <p class="servicesDescription indent"><?= htmlspecialchars($contentMap['ServicesDesc'] ?? 'No description Not Found') ?></p>
        </div>

        <div class="servicesIconContainer">

            <div class="resortContainer">
                <img src="../../Assets/Images/AboutImages/resort.png" alt="Resort Icon" class="resortIcon">
                <h4 class="resortIconTitle"><?= htmlspecialchars($contentMap['Service1'] ?? 'No description Not Found') ?></h4>
                <p class="resortIconDescription"><?= htmlspecialchars($contentMap['Service1Desc'] ?? 'No description Not Found') ?></p>
            </div>

            <div class="eventContainer">
                <img src="../../Assets/Images/AboutImages/events.png" alt="Event Icon" class="eventIcon">
                <h4 class="eventIconTitle"><?= htmlspecialchars($contentMap['Service2'] ?? 'No description Not Found') ?></h4>
                <p class="eventIconDescription"><?= htmlspecialchars($contentMap['Service2Desc'] ?? 'No description Not Found') ?></p>
            </div>

            <div class="hotelContainer">
                <img src="../../Assets/Images/AboutImages/hotel.png" alt="Hotel Icon" class="hotelIcon">
                <h4 class="hotelIconTitle"><?= htmlspecialchars($contentMap['Service3'] ?? 'No description Not Found') ?></h4>
                <p class="hotelIconDescription"><?= htmlspecialchars($contentMap['Service3Desc'] ?? 'No description Not Found') ?></p>
            </div>
        </div>
    </div>


    <div class="videoContainer" id="videoContainer">
        <div class="videoTextContainer">
            <?php
            $sectionName = 'BusinessInformation';
            $getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
            $getWebContent->bind_param("s", $sectionName);
            $getWebContent->execute();
            $getWebContentResult = $getWebContent->get_result();
            $businessInfo = [];
            while ($row = $getWebContentResult->fetch_assoc()) {
                $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
                $contentID = $row['contentID'];

                $businessInfo[$cleanTitle] = $row['content'];
            }
            ?>
            <h3 class="videoTitle">Explore <?= htmlspecialchars($businessInfo['FullName'] ?? 'No description Not Found') ?></h3>

            <p class="videoDescription indent"><?= htmlspecialchars($contentMap['Explore'] ?? 'No description Not Found') ?></p>
        </div>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted controls class="embed-responsive-item"
                poster="../../Assets/Videos/thumbnail2.jpg">
                <source src="../../Assets/Videos/mamyrVideo2.mp4" type="video/mp4">

            </video>
        </div>
    </div>


    <div class="backArrowContainer" id="backArrowContainer">
        <a href="about.php"><img src="../../Assets/Images/Icon/whiteArrow.png" alt="Back Button" class="backArrow"> </a>
    </div>

    <div class="mamyrHistoryContainer" id="mamyrHistoryContainer">
        <div class="firstParagraphContainer">
            <div class="firstParagraphtextContainer">
                <p class="firstParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph1'] ?? 'No description Not Found') ?></p>

                <p class="secondParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph2'] ?? 'No description Not Found') ?>
            </div>


            <div class="firstImageContainer">
                <img src="../../Assets/Images/AboutImages/aboutImage.jpg" alt="Mamyr Picture"
                    class="firstParagraphPhoto">
            </div>
        </div>

        <div class="thirdParagraphContainer">
            <div class="thirdImageContainer">
                <img src="../../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Mamyr Picture"
                    class="thirdParagraphPhoto">

            </div>

            <div class="thirdParagraphtextContainer">
                <p class="thirdParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph3'] ?? 'No description Not Found') ?>
                </p>
            </div>
        </div>

        <div class="fourthParagraphContainer">
            <p class="fourthParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph4'] ?? 'No description Not Found') ?>
            </p>
        </div>
    </div>

    <footer class="py-1" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            </a>
            <h3 class="mb-0"><?= htmlspecialchars(strtoupper($businessInfo['FullName']) ?? 'Name Not Found') ?></h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter"><?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?></h4>
                <h4 class="emailAddressTextFooter"><?= htmlspecialchars($businessInfo['Email'] ?? 'None Provided') ?></h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter"><?= htmlspecialchars($businessInfo['Address'] ?? 'None Provided') ?></h4>
            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="<?= htmlspecialchars($businessInfo['FBLink'] ?? 'None Provided') ?>"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="mailto: <?= htmlspecialchars($businessInfo['GmailAdd'] ?? 'None Provided') ?>"><i class='bx bxl-gmail'></i></a>
            <a href="tel:<?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?>">
                <i class='bx bxs-phone'></i>
            </a>
        </div>
    </footer>


    <script>
        const mamyrHistoryContainer = document.getElementById("mamyrHistoryContainer")
        const backArrowContainer = document.getElementById("backArrowContainer")
        const aboutTopContainer = document.getElementById("aboutTopContainer")
        const ourServicesContainer = document.getElementById("ourServicesContainer")
        const videoContainer = document.getElementById("videoContainer")

        mamyrHistoryContainer.style.display = "none"
        backArrowContainer.style.display = "none"


        function readMore() {
            if (mamyrHistoryContainer.style.display == "none" && backArrowContainer.style.display == "none") {

                mamyrHistoryContainer.style.display = "block";
                backArrowContainer.style.display = "block"
                aboutTopContainer.style.display = "none"
                ourServicesContainer.style.display = "none"
                videoContainer.style.display = "none"
                document.getElementById("title").innerHTML = "ABOUT US - HISTORY"

            } else {
                mamyrHistoryContainer.style.display = "block"
                backArrowContainer.style.display = "block"
            }
        }
    </script>


    <!-- Notification Ajax -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                            this.style.backgroundColor = 'white';
                        });
                });
            });
        });
    </script>





    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->

    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <script src="../../Assets/JS/scrollNavbg.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>