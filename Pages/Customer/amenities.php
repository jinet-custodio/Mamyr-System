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
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Amenities</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/amenities.css">


    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">

        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center gap-2">
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
                <a href="../Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
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

            <li class="nav-item" id="notifs">
                <button type="button" class="notifBtn" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item active" href="#">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">ABOUT</a>
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
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <div class="amenities">

        <h1 class="title">OUR AMENITIES</h1>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted loop controls class="embed-responsive-item">
                <source src="../../Assets/Videos/mamyrVideo1.mp4" type="video/mp4">
            </video>

        </div>

        <div class="pool">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <h4 class="amenityTitle">Swimming Pools</h4>
                <p class="amenityDescription">We offer three spacious pools designed for relaxation and fun. Whether you’re
                    looking to take a
                    refreshing dip or lounge by the water, each pool provides a perfect setting to unwind and enjoy your
                    stay. Dive in and make the most of your resort experience!</p>
            </div>

            <div class="carousel-container">
                <div class="carousel">
                    <img src="../../Assets/Images/amenities/poolPics/poolPic1.png" alt="Pool Picture 1" class="poolPic1">
                    <img src="../../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Pool Picture 2" class="poolPic2">
                    <img src="../../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture 3" class="poolPic3">
                    <img src="../../Assets/Images/amenities/poolPics/poolPic4.jpeg" alt="Pool Picture 4" class="poolPic4">
                    <img src="../../Assets/Images/amenities/poolPics/poolPic5.jpg" alt="Pool Picture 5" class="poolPic5">
                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="cottage colored-bg" style="background-color:#f7d5b0;">
            <div class=" amenityTitleContainer">
                <hr class="amenityLine">
                <h4 class="amenityTitle">Cottages</h4>
                <p class="amenityDescription">Our cozy cottages offer a relaxing retreat with spacious porches, secure
                    surroundings, and a refreshing ambiance. Enjoy a perfect blend of nature and modern facilities
                    designed for your comfort.</p>
            </div>


            <div class="carousel-container">
                <div class="carousel">
                    <?php
                    $serviceCategory = 2;
                    $query = "SELECT * FROM resortAmenities WHERE RScategoryID = $serviceCategory ";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        $cottages = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        $counter = 1;
                        foreach ($cottages as $cottage) {
                            $imageData = $cottage['RSimageData'];
                            if ($imageData) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mimeType = finfo_buffer($finfo, $imageData);
                                finfo_close($finfo);
                                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                            } else {
                                $image = '../../Assets/Images/no-picture.jpg';
                            }
                    ?>

                            <!-- <img src="<?= htmlspecialchars($image) ?>" alt="Cottage Picture" class="poolPic<?= $counter ?>"> -->
                    <?php
                            $counter++;
                        }
                    } else {
                        echo 'No Cottages';
                    }
                    ?>

                     <div class="carousel">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav1.jpg" alt="Pavilion Picture 1"
                        class="poolPic1">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav2.jpg" alt="Pavilion Picture 2"
                        class="poolPic2">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav3.jpg" alt="Pavilion Picture 3"
                        class="poolPic3">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav4.jpg" alt="Pavilion Picture 4"
                        class="poolPic4">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav5.jpg" alt="Pavilion Picture 5"
                        class="poolPic5">

                </div>
                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

    </div>

    <div class="videoke">
        <div class=" amenityTitleContainer">
            <hr class="amenityLine">
            <h4 class="amenityTitle">Videoke Area</h4>
            <p class="amenityDescription">Enjoy nonstop fun just steps away from your cottage! Our videoke area is
                conveniently located beside the cottages, making it easy to sing, laugh, and bond without going far.
                With a great sound system and cozy setup, it’s the perfect spot for music-filled memories in the
                heart of the resort.</p>
        </div>

        <div class="poolPics">
            <?php
            $videokeCategoryID = 3;
            $getVideoke = $conn->prepare("SELECT * FROM resortAmenities WHERE RScategoryID = ? ");
            $getVideoke->bind_param("i", $videokeCategoryID);
            $getVideoke->execute();
            $getVideokeResult =  $getVideoke->get_result();
            if ($getVideokeResult->num_rows > 0) {
                // $counter = 1;
                while ($videoke = $getVideokeResult->fetch_assoc()) {
                    $imageData = $videoke['RSimageData'];
                    if ($imageData) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_buffer($finfo, $imageData);
                        finfo_close($finfo);
                        $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    } else {
                        $image = '../../Assets/Images/no-picture.jpg';
                    }
            ?>

                    <img src="<?= htmlspecialchars($image) ?>" alt="Cottage Picture" class="pic1">
            <?php
                    // $counter++;
                }
            } else {
                echo 'No Videoke';
            }
            ?>
            <!-- <img src="../../Assets/Images/amenities/cottagePics/cottage3.jpg" alt="Hotel Picture 1" class="pic1">
                <img src="../../Assets/Images/amenities/cottagePics/cottage5.jpg" alt="Hotel Picture 1" class="pic1"> -->
        </div>

    </div>

    <div class="pavilion colored-bg" style="background-color: #7dcbf2;">
        <div class="amenityTitleContainer">
            <hr class="amenityLine">
            <h4 class="amenityTitle">Pavilion Hall</h4>
            <p class="amenityDescription">Our Pavilion Hall offers the perfect space for events, gatherings, and
                special occasions. With its spacious and elegant design, it’s ideal for everything from weddings to
                corporate events, comfortably accommodating up to 350 guests. Included with your rental is exclusive
                access to one private air-conditioned room and a dedicated powder room with separate comfort rooms
                for both male and female guests.</p>
        </div>

        <div class="carousel-container">
            <div class="carousel">

                <?php
                $eventHallCategoryID = 6;
                $getEventHall = $conn->prepare("SELECT * FROM resortAmenities WHERE RScategoryID = ? ");
                $getEventHall->bind_param("i",  $eventHallCategoryID);
                $getEventHall->execute();
                $getEventHallResult =  $getEventHall->get_result();
                if ($getEventHallResult->num_rows > 0) {
                    $counter = 1;
                    while ($eventHall = $getEventHallResult->fetch_assoc()) {
                        $imageData = $eventHall['RSimageData'];
                        if ($imageData) {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mimeType = finfo_buffer($finfo, $imageData);
                            finfo_close($finfo);
                            $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        } else {
                            $image = '../../Assets/Images/no-picture.jpg';
                        }
                ?>

                        <img src="<?= htmlspecialchars($image) ?>" alt="Cottage Picture" class="poolPic<?= $counter ?>">
                <?php
                        $counter++;
                    }
                } else {
                    echo 'No Event Hall';
                }
                ?>
                <!-- <img src="../../Assets/Images/amenities/pavilionPics/pav1.jpg" alt="Pavilion Picture 1"
                        class="poolPic1">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav2.jpg" alt="Pavilion Picture 2"
                        class="poolPic2">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav3.jpg" alt="Pavilion Picture 3"
                        class="poolPic3">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav4.jpg" alt="Pavilion Picture 4"
                        class="poolPic4">
                    <img src="../../Assets/Images/amenities/pavilionPics/pav5.jpg" alt="Pavilion Picture 5"
                        class="poolPic5"> -->

            </div>
            <button class="btn btn-primary prev-btn">&#10094;</button>
            <button class="btn btn-primary next-btn">&#10095;</button>
        </div>
    </div>

    <div class="minipavilion">
        <div class="amenityTitleContainer">
            <hr class="amenityLine">
            <h4 class="amenityTitle">Mini Pavilion</h4>
            <p class="amenityDescription">Our mini pavilion offers an intimate and charming space perfect for
                small
                gatherings and special occasions. Designed to comfortably accommodate up to 50 guests, it’s ideal
                for birthdays, reunions, meetings, or any cozy celebration. Surrounded by a refreshing resort
                atmosphere, it provides both functionality and a relaxing vibe.</p>
        </div>

        <div class="carousel-container">
            <div class="carousel">
                <?php
                $miniPavCategoryID = 7;
                $getMiniPav = $conn->prepare("SELECT * FROM resortAmenities WHERE RScategoryID = ? ");
                $getMiniPav->bind_param("i", $miniPavCategoryID);
                $getMiniPav->execute();
                $getMiniPavResult =  $getMiniPav->get_result();
                if ($getMiniPavResult->num_rows > 0) {
                    // $counter = 1;
                    while ($videoke =  $getMiniPav->fetch_assoc()) {
                        $imageData = $videoke['RSimageData'];
                        if ($imageData) {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mimeType = finfo_buffer($finfo, $imageData);
                            finfo_close($finfo);
                            $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        } else {
                            $image = '../../Assets/Images/no-picture.jpg';
                        }
                ?>

                        <img src="<?= htmlspecialchars($image) ?>" alt="Cottage Picture" class="pic1">
                <?php
                        // $counter++;
                    }
                } else {
                    echo 'No Cottages';
                }
                ?>
                <img src="../../Assets/Images/amenities/miniPavPics/miniPav1.jpg" alt="Mini Pavilion Picture 1"
                    class="poolPic1">
                <img src="../../Assets/Images/amenities/miniPavPics/miniPav2.jpg" alt="Mini Pavilion Picture 2"
                    class="poolPic2">
                <img src="../../Assets/Images/amenities/miniPavPics/miniPav3.jpeg" alt="Mini Pavilion Picture 3"
                    class="poolPic3">
                <img src="../../Assets/Images/amenities/miniPavPics/miniPav4.jpeg" alt="Mini Pavilion Picture 4"
                    class="poolPic4">
                <img src="../../Assets/Images/amenities/miniPavPics/miniPav5.jpeg" alt="Mini Pavilion Picture 5"
                    class="poolPic5">

            </div>
            <button class="btn btn-primary prev-btn">&#10094;</button>
            <button class="btn btn-primary next-btn">&#10095;</button>
        </div>
    </div>

    <div class="hotel colored-bg" style="background-color:#f7d5b0;">
        <div class="amenityTitleContainer">
            <hr class="amenityLine">
            <h4 class="amenityTitle">Mamyr Hotel</h4>
            <p class="amenityDescription">We offer 11 thoughtfully designed hotel rooms, each providing a peaceful and
                comfortable retreat. Perfect for guests looking for a relaxing space to unwind after a day of
                exploration, our rooms offer all the essentials for a restful stay with a touch of convenience.</p>
        </div>

        <div class="carousel-container">
            <div class="carousel">
                <img src="../../Assets/Images/amenities/hotelPics/hotel1.jpg" alt="Hotel Picture 1" class="poolPic1">
                <img src="../../Assets/Images/amenities/hotelPics/hotel2.jpg" alt="Hotel Picture 2" class="poolPic2">
                <img src="../../Assets/Images/amenities/hotelPics/hotel3.jpg" alt="Hotel Picture 3" class="poolPic3">
                <img src="../../Assets/Images/amenities/hotelPics/hotel4.jpg" alt="Hotel Picture 4" class="poolPic4">
                <img src="../../Assets/Images/amenities/hotelPics/hotel5.jpeg" alt="Hotel Picture 5" class="poolPic5">

            </div>
            <button class="btn btn-primary prev-btn">&#10094;</button>
            <button class="btn btn-primary next-btn">&#10095;</button>
        </div>
    </div>

    <div class="parking">
        <div class="amenityTitleContainer">
            <hr class="amenityLine">
            <h4 class="amenityTitle">Parking Space</h4>
            <p class="amenityDescription">We provide ample parking spaces to ensure a hassle-free stay. Whether
                you’re arriving by car or with a group, our secure parking area is conveniently located, giving you peace
                of mind throughout your visit.</p>
        </div>

        <div class="carousel-container">
            <div class="carousel">
                <img src="../../Assets/Images/amenities/parkingPics/parking1.jpg" alt="Parking Picture 1"
                    class="poolPic1">
                <img src="../../Assets/Images/amenities/parkingPics/parking2.jpg" alt="Parking Picture 2"
                    class="poolPic2">
                <img src="../../Assets/Images/amenities/parkingPics/parking3.jpg" alt="Parking Picture 3"
                    class="poolPic3">
                <img src="../../Assets/Images/amenities/parkingPics/parking4.jpg" alt="Parking Picture 4"
                    class="poolPic4">
                <img src="../../Assets/Images/amenities/parkingPics/parking5.jpg" alt="Parking Picture 5"
                    class="poolPic5">

            </div>
            <button class="btn btn-primary prev-btn">&#10094;</button>
            <button class="btn btn-primary next-btn">&#10095;</button>
        </div>
    </div>
    </div>


    <footer class="py-1" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            </a>
            <h3 class="mb-0">MAMYR RESORT AND EVENTS PLACE</h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter">(0998) 962 4697 </h4>
                <h4 class="emailAddressTextFooter">mamyr@gmail.com</h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter">Sitio Colonia, Gabihan, San Ildefonso, Bulacan</h4>
            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="https://www.facebook.com/p/Mamyr-Resort-Restaurant-Events-Place-100083298304476/"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="https://workspace.google.com/intl/en-US/gmail/"><i class='bx bxl-gmail'></i></a>
            <a href="tel:+09989624697">
                <i class='bx bxs-phone'></i>
            </a>

        </div>

    </footer>




    <!-- Bootstrap Link -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
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



    <script>
        var video = document.getElementById("mamyrVideo");

        video.onplay = function() {
            video.muted = false;
        };
    </script>
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
        const navbar = document.getElementById("navbar");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 10) {
                navbar.classList.add("bg-white", "shadow");
            } else {
                navbar.classList.remove("bg-white", "shadow");
            }
        });
    </script>


    <script>
        const carousels = document.querySelectorAll('.carousel');


        carousels.forEach(carousel => {
            let angle = 0;

            const prevButton = carousel.closest('.carousel-container').querySelector('.prev-btn');
            const nextButton = carousel.closest('.carousel-container').querySelector('.next-btn');


            nextButton.addEventListener('click', () => {
                angle -= 72;
                carousel.style.transform = `rotateY(${angle}deg)`;
            });


            prevButton.addEventListener('click', () => {
                angle += 72;
                carousel.style.transform = `rotateY(${angle}deg)`;
            });
        });
    </script>
</body>

</html>