<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require '../../Function/sessionFunction.php';
checkSessionTimeout();

require_once '../../Function/Helpers/statusFunctions.php';
require_once '../../Function/Helpers/userFunctions.php';

resetExpiredOTPs($conn);
addToAdminTable($conn);
autoChangeStatus($conn);
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
//for setting image paths in 'include' statements
$baseURL = '../..';

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

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

if ($userRole === 2) {
    header("Location: ../BusinessPartner/bpDashboard.php");
    exit();
}


$folder = 'landingPage';
$sectionName = 'Landing';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
$defaultImage = "../../Assets/Images/no-picture.jpg";

while ($row = $getWebContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
    $contentID = $row['contentID'];
    $contentMap[$cleanTitle] = $row['content'];

    // Fetch images with this contentID
    $getImages = $conn->prepare("SELECT WCImageID, imageData, altText FROM websitecontentimage WHERE contentID = ? ORDER BY imageOrder ASC");
    $getImages->bind_param("i", $contentID);
    $getImages->execute();
    $imageResult = $getImages->get_result();

    $images = [];
    while ($imageRow = $imageResult->fetch_assoc()) {
        $images[] = $imageRow;
    }

    $imageMap[$cleanTitle] = $images;
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
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <!-- Swiper's CSS Link  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top white-text" id="navbar-half">

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


            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal" id="notificationButton">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                </button>
            </div>

            <div class="hidden-inputs" style="display: none;">
                <input type="hidden" id="receiver" value="<?= $role ?>">
                <input type="hidden" id="userID" value="<?= $userID ?>">
            </div>
        </ul>
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <?php if ($userRole !== 2): ?>
                        <a class="nav-link active" href="dashboard.php"> Home</a>
                    <?php else: ?>
                        <a class="nav-link" href="../BusinessPartner/bpDashboard.php"> Home</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Amenities
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="amenities.php">Resort Amenities</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">Rates and Hotel Rooms</a></li>
                        <li><a class="dropdown-item" href="events.php">Events</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">Blog</a>
                </li>
                <?php if ($userRole !== 2): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="beOurPartner.php">Be Our Partner</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookNow.php">Book Now</a>
                </li>

                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger nav-link" id="logOutBtn">Log
                        Out</a>
                </li>

            </ul>
        </div>
    </nav>

    <?php include '../Notification/notification.php' ?>

    <div class="custom-container">
        <section class="topSec">
            <div class="topLeft">
                <h1 class="welcome">Welcome to Mamyr Resort and Events Place, <?= ucfirst($firstName) ?>!</h1>
                <h5 class="subtext">We’re excited to welcome you to Mamyr Resort! Whether you're here to unwind,
                    explore, or enjoy some quality time, we’re ready to make your stay memorable. Book now and start
                    your relaxation journey with us today!</h5>
                <!-- <h5 class="subtext">Book now and start
                    your relaxation journey with us today!</h5> -->

                <div class="topBtn-container">
                    <a href="resortBooking.php" class="btn resort-booking-btn-base button-pool">Resort Booking</a>
                    <a href="hotelBooking.php" class="btn resort-booking-btn-base button-hotel">Hotel Booking</a>
                    <a href="eventBooking.php" class="btn resort-booking-btn-base button-event">Event Booking</a>
                    <!-- <a href="resortBooking.php" class="btn btn-outline-primary resortBooking">Hotel Booking</a>
                    <a href="resortBooking.php" class="btn btn-outline-primary resortBooking">Event Booking</a> -->
                </div>

                <!-- <div class="topBtn-container">
                    <a href="bookNow.php" class="btn btn-light">Book Now</a>
                    <a href="amenities.php" class="btn btn-light">Browse Amenities</a>
                </div> -->
            </div>

            <div class="topRight">
                <div class="card calendar-card">
                    <div class="legends">
                        <div class="legend ">
                            <i class="fa-solid fa-circle" style="color: #FF9999"></i> Event
                        </div>
                        <div class="legend ">
                            <i class="fa-solid fa-circle" style="color: #ffdb6d;"></i> Resort/Hotel
                        </div>
                        <div class="legend ">
                            <i class="fa-solid fa-circle" style="color: #b3e0f2 ;"></i> Resort Entrance
                        </div>
                    </div>
                    <div id="calendar"></div>
                    <!-- Event Info Modal -->
                    <div class="modal fade" id="userEventModal" tabindex="-1" aria-labelledby="userEventModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <!-- centers modal vertically -->
                            <div class="modal-content shadow-lg rounded-3">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="userEventModalLabel">Event Details</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="userEventModalBody">
                                    <!-- Filled dynamically by JavaScript -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="middle-container">
            <div class="embed-responsive embed-responsive-16by9">
                <video id="mamyrVideo" muted controls class="embed-responsive-item"
                    poster="../../Assets/videos/thumbnail2.jpg">
                    <source src="../../Assets/videos/Mamyrvideo3.mp4" type="video/mp4">

                </video>
            </div>
            <div class="videoText-container">
                <h3 class="videoTitle"><?= htmlspecialchars($contentMap['Heading2'] ?? 'Name Not Found') ?> </h3>
                <p class="videoDescription indent">
                    <?= htmlspecialchars($contentMap['Subheading2'] ?? 'Description Not Found') ?> </p>
                <div class="middle-btn-container">
                    <a href="amenities.php" class="btn btn-primary">View our Amenities</a>
                </div>
            </div>
        </section>


        <section class="bottom-section">

            <div class="bottom-text-container">
                <h3 class="bottom-header"><?= htmlspecialchars($contentMap['BookNow'] ?? 'Title Not Found') ?> </h3>
                <p class="bottom-subtext indent">
                    <?= htmlspecialchars($contentMap['BookNowDesc'] ?? 'Description Not Found') ?> </p>
            </div>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['BookNow'])): ?>
                        <?php foreach ($imageMap['BookNow'] as $index => $img):
                            $imagePath = "../../Assets/Images/landingPage/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img d-block" style="cursor: pointer;">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card-img">
                            <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                        </div>
                    <?php endif; ?>

                </div>
                <div class="swiper-pagination"></div>
            </div>

            <div id="popup-container" class="popup-container">
                <img id="popup-image" class="popup-image" src="" alt="Popup Image" />
                <button id="close-popup" class="btn btn-danger close-popup">Close</button>
            </div>
        </section>

        <section class="rating-container">
            <div class="locationText-container">
                <h3 class="videoTitle"><?= htmlspecialchars($contentMap['Reviews'] ?? 'Title Not Found') ?> </h3>
                <p class="videoDescription indent">
                    <?= htmlspecialchars($contentMap['ReviewsDesc'] ?? 'Description Not Found') ?> </p>
            </div>

            <div class="card ratings-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-star"></i>
                        <h6 class="graph-header-text">Ratings</h6>
                    </div>

                    <div class="rating-categories">
                        <!-- Resort -->
                        <div class="rating-row">
                            <div class="rating-label">Resort</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="resort-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="resort-rating-value"></div>
                        </div>

                        <!-- Hotel -->
                        <div class="rating-row">
                            <div class="rating-label">Hotel</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="hotel-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="hotel-rating-value"></div>
                        </div>

                        <!-- Event -->
                        <div class="rating-row">
                            <div class="rating-label">Event</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="event-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="event-rating-value"></div>
                        </div>

                        <!-- Overall Rating (Optional) -->
                        <div class="overall-rating">
                            <div class="overall-rating-label">
                                <h6 class="overall-rating-label">Overall Rating</h6>
                                <h4 class="overall-rating-value" id="overall-rating-value"></h4>
                            </div>
                            <div class="overall-rating-stars" id="star-container">
                                <!-- <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="ctaContainer" id="ourServicesContainer">

            <div class="bottom-text-container">
                <h3 class="bottom-header">How to Book at Mamyr Resorts and Events Place?</h3>
            </div>

            <div class="servicesIconContainer">
                <div class="eventContainer">
                    <h2 class="numbering">1</h2>
                    <img src="../../Assets/Images/landingPage/choose.png" alt="Choose Booking Type" class="eventIcon">
                    <h4 class="eventIconTitle">Select Booking Type</h4>
                    <p class="eventIconDescription">Select the booking type that suits your needs—resort stay, hotel
                        room,
                        or event reservation—and then complete the required details to complete your reservation.</p>
                </div>

                <div class="resortContainer">
                    <h2 class="numbering">2</h2>
                    <img src="../../Assets/Images/landingPage/check.png" alt="Check for Availability"
                        class="resortIcon">
                    <h4 class="resortIconTitle">Check Availability by Date and Time</h4>
                    <p class="resortIconDescription">Please enter your preferred date and time so the system can check
                        the
                        availability of services for your selected schedule.</p>
                </div>

                <div class="hotelContainer">
                    <h2 class="numbering">3</h2>
                    <img src="../../Assets/Images/landingPage/confirm.png" alt="Hotel Icon" class="hotelIcon">
                    <h4 class="hotelIconTitle">Confirm Your Booking Details</h4>
                    <p class="hotelIconDescription">Kindly review your booking details carefully and confirm all
                        information
                        is
                        correct before finalizing your reservation to ensure accuracy and avoid any issues.</p>
                </div>

                <div class="hotelContainer">
                    <h2 class="numbering">4</h2>
                    <img src="../../Assets/Images/landingPage/bookNow.png" alt="Book Now" class="hotelIcon">
                    <h4 class="hotelIconTitle">Finalize Your Reservation</h4>
                    <p class="hotelIconDescription">Once you click 'Book Now,' your reservation will be successfully
                        confirmed, and a confirmation email, including the next steps that you will need to do,
                        will be sent to you shortly.</p>
                </div>
            </div>
            <div class="bookNow-button-container">
                <a href="bookNow.php" class="btn btn-primary">Book With Us Today!</a>
            </div>
        </section>
        <?php include 'footer.php';
        include 'loader.php'; ?>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script src="../../Assets/JS/scrollNavbg.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

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

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        if (paramValue === 'successLogin') {
            Toast.fire({
                icon: "success",
                title: "Signed in successfully"
            });
        };

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <!-- Service Worker -->
    <!-- <script>
        async function registerPush() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

            try {
                const permission = await Notification.requestPermission();
                if (Notification.permission === "default") {
                    console.log('Requesting permission now...');
                    const permission = await Notification.requestPermission();
                    console.log('User response:', permission);
                    alert("Notification permission: " + permission);
                } else if (permission !== 'granted') {
                    console.error('Notification permission not granted');
                    return;
                }

                const sw = await navigator.serviceWorker.register('/serviceWorker.js');
                console.log('Service Worker registered:', sw);
                const response = await fetch('/Function/Notification/getVapidPublicKey.php');
                const vapidPublicKey = await response.text();
                const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                const subscription = await sw.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });

                await fetch('/Function/savePushSubscription.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(subscription)
                });

                console.log('Push subscription successful');

            } catch (err) {
                console.error('Error registering push', err);
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
            return outputArray;
        }
    </script> -->


    <!-- Initialize Swiper -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (document.querySelector(".mySwiper")) {

                var swiper = new Swiper(".mySwiper", {
                    loop: false,
                    loopedSlides: 3,
                    spaceBetween: 30,
                    slidesPerView: 3,
                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                            spaceBetween: 10
                        },
                        600: {
                            slidesPerView: 2,
                            spaceBetween: 20
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 30
                        }
                    },
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true
                    }
                });

                window.addEventListener("load", () => {
                    swiper.update();
                });
            }
        });
    </script>

    <script>
        const popupContainer = document.getElementById('popup-container');
        const popupImage = document.getElementById('popup-image');
        const closePopupBtn = document.getElementById('close-popup');


        document.querySelectorAll('.editable-img').forEach((image) => {
            image.addEventListener('click', function() {
                const imageSrc = image.src;
                popupImage.src = imageSrc;
                popupContainer.style.display = 'flex';
                requestAnimationFrame(() => {
                    popupContainer.classList.add('show');
                });
            });
        });


        closePopupBtn.addEventListener('click', function() {
            popupContainer.classList.remove('show');
            setTimeout(() => {
                popupContainer.style.display =
                    'none';
            }, 300);
        });

        popupContainer.addEventListener('click', (e) => {
            if (e.target === popupContainer) {
                popupContainer.classList.remove('show');
                setTimeout(() => {
                    popupContainer.style.display = 'none';
                }, 300);
            }
        });
    </script>

    <script>
        async function getRatings() {
            const response = await fetch('../../Function/Admin/Ajax/getRatings.php');
            const data = await response.json();

            const resortBar = document.getElementById('resort-bar');
            resortBar.style.width = data.resortPercent + '%';
            resortBar.setAttribute('ari-valuenow', data.resortPercent)
            document.getElementById('resort-rating-value').textContent = data.resortRating;

            const hotelBar = document.getElementById('hotel-bar');
            hotelBar.style.width = data.hotelPercent + '%';
            hotelBar.setAttribute('ari-valuenow', data.hotelPercent)
            document.getElementById('hotel-rating-value').textContent = data.hotelRating;

            const eventBar = document.getElementById('event-bar');
            eventBar.style.width = data.eventPercent + '%';
            eventBar.setAttribute('ari-valuenow', data.eventPercent)
            document.getElementById('event-rating-value').textContent = data.eventRating;

            document.getElementById('overall-rating-value').textContent = data.overAllRating;
            const starContainer = document.getElementById('star-container');
            starContainer.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= Math.floor(data.overAllRating)) {
                    starContainer.innerHTML += '<i class="bi bi-star-fill star text-warning"></i>';
                } else if (i - data.overAllRating <= .5 && i - data.overAllRating > 0) {
                    starContainer.innerHTML += '<i class="bi bi-star-half star text-warning"></i>';
                } else {
                    starContainer.innerHTML += '<i class="bi bi-star star text-warning"></i>';
                }
            }
        }
        getRatings();
        setInterval(getRatings, 300000);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const modal = new bootstrap.Modal(document.getElementById('userEventModal'));
            const modalBody = document.getElementById('userEventModalBody');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                displayEventTime: false,
                height: 'auto',
                aspectRatio: 1.35,
                events: '../../Function/Admin/fetchBookings.php',

                // Make event blocks colored but textless
                eventDidMount: function(info) {
                    info.el.style.backgroundColor = info.event.backgroundColor;
                    info.el.style.borderColor = info.event.backgroundColor;
                    info.el.style.color = 'transparent !important';
                },

                // Modal for clicking an event
                eventClick: function(info) {
                    const event = info.event;

                    const formattedStart = new Date(event.start).toLocaleString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    const formattedEnd = event.end ?
                        new Date(event.end).toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        }) :
                        null;

                    // Match header color with event
                    const header = document.querySelector('#userEventModal .modal-header');
                    header.style.backgroundColor = event.backgroundColor;

                    modalBody.innerHTML = `
                <div class="text-center">
                    <p class="fs-5 mb-2"><strong>Booking Type:</strong> ${event.title || 'N/A'}</p>
                    <p class="mb-1"><strong>Start:</strong> ${formattedStart}</p>
                    ${formattedEnd ? `<p><strong>End:</strong> ${formattedEnd}</p>` : ''}
                </div>
            `;
                    modal.show();
                },

                // Modal for clicking a date cell (list all events)
                dateClick: function(info) {
                    const clickedDate = new Date(info.dateStr);
                    clickedDate.setHours(0, 0, 0, 0);

                    const formattedClickedDate = clickedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    // Find all events that fall on that date
                    const eventsOnDate = calendar.getEvents().filter(event => {
                        const start = new Date(event.start);
                        const end = new Date(event.end || event.start);

                        start.setHours(0, 0, 0, 0);
                        end.setHours(0, 0, 0, 0);

                        return clickedDate >= start && clickedDate <= end && event.display !==
                            'background';
                    });

                    if (eventsOnDate.length === 0) {
                        modalBody.innerHTML =
                            `<p class="text-center mb-0">No events found on ${formattedClickedDate}.</p>`;
                    } else {
                        let content = `
                    <h5 class="text-center mb-3">Events on ${formattedClickedDate}</h5>
                    <div class="list-group">
                `;

                        eventsOnDate.forEach(event => {
                            const formattedStart = new Date(event.start).toLocaleString(
                                'en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                });

                            const formattedEnd = event.end ?
                                new Date(event.end).toLocaleString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                }) :
                                null;

                            content += `
                        <div class="list-group-item d-flex align-items-center justify-content-between" style="border-left: 8px solid ${event.backgroundColor}">
                            <div>
                                <strong>${event.title || 'Event'}</strong><br>
                                <small>${formattedStart}${formattedEnd ? ` - ${formattedEnd}` : ''}</small>
                            </div>
                        </div>
                    `;
                        });

                        content += `</div>`;
                        modalBody.innerHTML = content;
                    }

                    // Make header neutral color for date clicks
                    const header = document.querySelector('#userEventModal .modal-header');
                    header.style.backgroundColor = '#0d6efd';

                    modal.show();
                },

                // Create solid full-cell color if only 1 event that day
                eventsSet: function(events) {
                    const eventCountByDate = {};

                    // Count how many events per date
                    events.forEach(event => {
                        const startDate = event.startStr.split('T')[0];
                        if (!eventCountByDate[startDate]) eventCountByDate[startDate] = 0;
                        eventCountByDate[startDate]++;
                    });

                    // Add solid background event for single-event days
                    events.forEach(event => {
                        const startDate = event.startStr.split('T')[0];
                        if (eventCountByDate[startDate] === 1) {
                            calendar.addEvent({
                                start: event.startStr,
                                end: event.endStr || event.startStr,
                                display: 'background',
                                allDay: true,
                                backgroundColor: event.backgroundColor,
                                borderColor: event.backgroundColor
                            });
                        }
                    });
                }
            });

            calendar.render();
        });
    </script>

</body>

</html>