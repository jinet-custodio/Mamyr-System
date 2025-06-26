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
    <title>Mamyr - Home </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/Customer/dashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">


        <!-- Account Icon on the Left -->
        <ul class="navbar-nav">
            <?php
            $query = "SELECT userProfile FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $imageData = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>
            <li class="nav-item account-nav">
                <a href="Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                </a>
            </li>
        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
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

    <div class="custom-container">
        <div class="topContainer">
            <div class="titleContainer">
                <div class="mamyrTitle">
                    <h1 class="welcome">Welcome to Mamyr,</h1>
                </div>

                <?php
                $query = "SELECT firstName FROM users WHERE userID = '$userID'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $firstName = $row['firstName'];
                } else {
                    $firstName = 'None';
                }
                ?>
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
                <h4 class="wsTitle">Welcome to Mamyr Resort and Events Place</h4>
                <p class="wsDescription">Welcome to Mamyr Resort and Events Place, where relaxation and unforgettable
                    moments await you. Whether you're here for a peaceful retreat or a special celebration, we're
                    dedicated to making your experience truly exceptional.</p>
            </div>

        </div>

        <div class="contact">
            <div class="contactText">
                <hr class="line">
                <h4 class="contactTitle">Contact Us </h4>

                <div class="location">
                    <img src="../../../Assets/Images/landingPage/icons/location.png" alt="locationPin"
                        class="locationIcon">
                    <h5 class="locationText">Sitio Colonia Gabihan, San Ildefonso, Bulacan</h5>
                </div>

                <div class="number">
                    <img src="../../../Assets/Images/landingPage/icons/phone.png" alt="phone" class="phoneIcon">
                    <h5 class="number">(0998) 962 4697</h5>
                </div>

                <div class="email">
                    <img src="../../../Assets/Images/landingPage/icons/email.png" alt="email" class="emailIcon">
                    <h5 class="emailAddressText">mamyresort128@gmail.com</h5>
                </div>


            </div>
            <div class="googleMap" id="googleMap"></div>
        </div>

        <div class="gallery">
            <hr class="line">
            <h4 class="galleryTitle">Gallery </h4>
            <div class="galleryPictures">
                <img src="../../Assets/Images/landingPage/gallery/img1.png" alt="resort View 1" class="img1 galleryImg">
                <img src="../../Assets/Images/landingPage/gallery/img2.png" alt="resort View 2" class="img2 galleryImg">
                <img src="../../Assets/Images/landingPage/gallery/img3.png" alt="resort View 3" class="img3 galleryImg">
                <img src="../../Assets/Images/landingPage/gallery/img4.png" alt="resort View 4" class="img4 galleryImg">
                <img src="../../Assets/Images/landingPage/gallery/img5.png" alt="resort View 5" class="img5 galleryImg">
                <img src="../../Assets/Images/landingPage/gallery/img6.png" alt="resort View 6" class="img6 galleryImg">
            </div>

            <div class="seeMore">
                <a href="../amenities.php" class="btn btn-primary w-100">See More</a>
            </div>
        </div>



        <footer class="py-1 my-2">
            <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">

                <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

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
    </div>


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
                events: '../../Function/fetchUserBookings.php', // the PHP file above
                eventClick: function(info) {
                    window.location.href = "/Pages/Customer/Account/bookingHistory.php";
                }
            });

            calendar.render();
        });
    </script>
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../../Assets/JS/scrollNavbg.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCalqMvV8mz7fIlyY51rxe8IerVxzUTQ2Q&callback=myMap">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>


</body>

</html>