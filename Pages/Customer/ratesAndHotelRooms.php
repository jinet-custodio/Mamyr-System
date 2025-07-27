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
    <title>Mamyr - Rates and Hotel Rooms</title>
    <link rel="icon" type="image/x-icon" href="../../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/ratesAndHotelRooms.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">

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
            $receiver = 'Customer';
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
                    <a class="nav-link" href="dashboard.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item " href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item active" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
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

    <div class="selection" id="selection" style="display: block;">
        <div class="titleContainer">
            <h4 class="title" id="mainTitle">RATES AND HOTEL ROOMS</h4>
        </div>

        <div class="categories" id="categories">

            <a class="categoryLink" onclick="showRates(event)">
                <div class="card" style="width: 25vw; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../assets/images/amenities/poolPics/poolPic3.jpg" alt="Wedding Event">

                    <div class="card-body">
                        <h5 class="card-title">Resort Rates</h5>
                    </div>
                </div>
            </a>

            <a class="categoryLink" onclick="showHotels(event)">
                <div class="card" style="width: 25vw; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../assets/images/amenities/hotelPics/hotel1.jpg" alt="Wedding Event">
                    <div class="card-body">
                        <h5 class="card-title">Hotel Rooms</h5>
                    </div>
                </div>
            </a>

        </div>
    </div>
    <div class="rates" id="rates" style="display: none;">
        <div class="backToSelection" id="backToSelection">
            <img src="../../Assets/Images/Icon/back-button.png" alt="back button" onclick="backToSelection()">
        </div>
        <div class="titleContainer">
            <h4 class="title">Our Rates</h4>
        </div>


        <div class="entrance" style="background-color:rgba(16, 128, 125, 1); padding: 0vw 0 3vw 0; ">
            <div class=" entranceTitleContainer" style="padding-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle" style="color: whitesmoke;">Resort Entrance Fee</h4>
            </div>
            <div class="entranceFee">
                <?php
                // DB query
                $rateSql = $conn->prepare("SELECT er.*, etr.time_range  FROM entrancerates  er
                LEFT JOIN entrancetimeranges etr ON er.timeRangeID = etr.timeRangeID
                 ORDER BY 
                    FIELD(sessionType, 'Day', 'Night', 'Overnight'), 
                    FIELD(ERcategory, 'Adult', 'Kids')");
                $rateSql->execute();
                $rateResult = $rateSql->get_result();

                // Organize data into sessions
                $sessions = [];
                if ($rateResult->num_rows > 0) {
                    while ($row = $rateResult->fetch_assoc()) {
                        $session = $row['sessionType'];
                        if (!isset($sessions[$session])) {
                            $sessions[$session] = [
                                'time_range' => $row['time_range'],
                                'ERprice' => []
                            ];
                        }
                        $sessions[$session]['ERprice'][$row['ERcategory']] = $row['ERprice'];
                    }

                    // Display cards
                    foreach ($sessions as $session => $data) {
                ?>
                        <div class="entranceCard card">
                            <div class="entrace-card-body">
                                <h5 class="entrance-card-title">
                                    <span class="dayNight"><?= strtoupper($session) ?></span><br>
                                    <?= $data['time_range'] ?>
                                </h5>
                                <div class="entrance-card-content">
                                    <span class="age">ADULT - PHP<?= number_format($data['ERprice']['Adult'], 2) ?></span>
                                    <span class="age">KIDS - PHP<?= number_format($data['ERprice']['Kids'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p>No entrance rates found.</p>";
                }
                ?>
            </div>
        </div>


        <div class="cottages">
            <div class="titleContainer" style="margin-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Cottages</h4>
            </div>

            <div class="cottages">
                <?php
                $cottageCategoryID = 2;
                $availableID = 1;
                $cottagesql = $conn->prepare("SELECT * FROM resortAmenities WHERE RSCategoryID = ? AND RSAvailabilityID = ?");
                $cottagesql->bind_param("ii", $cottageCategoryID, $availableID);
                $cottagesql->execute();
                $cottresult = $cottagesql->get_result();
                if ($cottresult->num_rows > 0) {
                    $cottages = $cottresult->fetch_all(MYSQLI_ASSOC);
                    foreach ($cottages as $cottage) {
                ?>
                        <div class="cottage">
                            <div class="Description" style="width: 40%;">
                                <h2> Good for <?= $cottage['RScapacity'] ?> pax </h2>
                                <p>
                                    <?= $cottage['RSdescription'] ?>
                                </p>
                                <p class="font-weight-bold">
                                    Price: PHP <?= $cottage['RSprice'] ?>
                                </p>
                            </div>
                            <div class="halfImg" style="width: 40%;">
                                <?php
                                $imgSrc = '../../Assets/Images/no-picture.jpg';
                                if (!empty($cottage['imageData'])) {
                                    $imgData = base64_encode($cottage['RSimageData']);
                                    $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                                }
                                ?>
                                <img src="<?= $imgSrc ?>" alt="Cottage Image" class="rounded" id="displayPhoto">

                            </div>


                        </div>
                <?php
                    }
                } else {
                    echo "<h5> No Record Found </h5>";
                }
                ?>
            </div>
        </div>


        <div class="videoke" style="background-color:whitesmoke; padding: 0vw 0 3vw 0 ">

            <div class=" videokeTitleContainer" style="padding-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Videoke for Rent</h4>
            </div>
            <?php
            $videoke = 'Videoke 1';
            $vidsql = $conn->prepare("SELECT * FROM resortAmenities WHERE RServiceName = ?");
            $vidsql->bind_param("s", $videoke);
            $vidsql->execute();
            $vidresult = $vidsql->get_result();
            if ($vidresult->num_rows > 0) {
                $videokes = $vidresult->fetch_all(MYSQLI_ASSOC);
                foreach ($videokes as $videoke) {
            ?>
                    <div class="section">
                        <div class="singleImg" style="width: 40%;">
                            <?php
                            $imgSrc = '../../Assets/Images/no-picture.jpg';
                            if (!empty($videoke['RSimageData'])) {
                                $imgData = base64_encode($videoke['RSimageData']);
                                $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                            }
                            ?>
                            <img src="<?= $imgSrc ?>" alt="Videoke Image" class="rounded" id="displayPhoto">

                        </div>
                        <div class="Description" id="videokeDesc" style="width: 40%;">
                            <h2 style="font-size: 3vw;"> PHP <?= $videoke['RSprice'] ?> per Rent </h2>
                            <p>
                                <?= $videoke['RSdescription'] ?>
                            </p>
                        </div>


                    </div>
            <?php
                }
            } else {
                echo "<h5> No Record Found </h5>";
            }
            ?>
        </div>
        <div class=" videokeTitleContainer" id="billiardCont" style="padding-top: 2vw;">
            <hr class="entranceLine">
            <h4 class="entranceTitle">Blliards Table for Rent</h4>
        </div>
        <div class="cottage " id="billiards">
            <?php
            $billiard = 'Billiard';
            $bilsql = $conn->prepare("SELECT * FROM resortAmenities WHERE RServiceName = ?");
            $bilsql->bind_param("s", $billiard);
            $bilsql->execute();
            $bilresult = $bilsql->get_result();
            if ($bilresult->num_rows > 0) {
                $billiards = $bilresult->fetch_all(MYSQLI_ASSOC);
                foreach ($billiards as $bill) {
            ?>
                    <div class="Description" style="width: 40%;">
                        <p>
                            <?= $bill['RSdescription'] ?>
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP<?= $bill['RSprice'] ?> per Hour
                        </p>
                    </div>
                    <div class="singleImg" style="width: 50%;">
                        <?php
                        $imgSrc = '../../Assets/Images/no-picture.jpg';
                        if (!empty($bill['RSimageData'])) {
                            $imgData = base64_encode($bill['RSimageData']);
                            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                        }
                        ?>
                        <img src="<?= $imgSrc ?>" alt="Videoke Image" class="rounded" id="displayPhoto">

                    </div>
            <?php
                }
            } else {
                echo "<h5> No Record Found </h5>";
            }
            ?>

        </div>
        <div class="massage" style="background-color:rgba(125, 203, 242, 1); padding: 0vw 0 3vw 0; margin-bottom:3vw; ">
            <div class=" videokeTitleContainer" style="padding-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Massage Chair</h4>
            </div>
            <?php
            $massageChair = 'Massage Chair';
            $massagesql = $conn->prepare("SELECT * FROM resortAmenities WHERE RServiceName = ?");
            $massagesql->bind_param("s", $massageChair);
            $massagesql->execute();
            $massageresult = $massagesql->get_result();
            if ($massageresult->num_rows > 0) {
                $massages = $massageresult->fetch_all(MYSQLI_ASSOC);
                foreach ($massages as $massage) {
            ?>
                    <div class="section" id="massage">
                        <div class="singleImg" style="width: 50%;">
                            <?php
                            $imgSrc = '../../Assets/Images/no-picture.jpg';
                            if (!empty($massage['RSimageData'])) {
                                $imgData = base64_encode($massage['RSimageData']);
                                $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                            }
                            ?>
                            <img src="<?= $imgSrc ?>" alt="Massage Chair Image" class="rounded" id="displayPhoto">

                        </div>
                        <div class="Description" id="massageDesc" style="width: 40%;">
                            <h2 style="font-size: 3vw;"> <?= $massage['RSprice'] ?> pesos for <?= $massage['RSduration'] ?> </h2>
                            <p>
                                <?= $massage['RSdescription'] ?>
                            </p>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<h5> No Record Found </h5>";
            }
            ?>
        </div>
    </div>

    <div class="hotelRooms" id="hotelRooms" style="display: none;">
        <div class="backToSelection" id="backToSelection">
            <img src="../../Assets/Images/Icon/back-button.png" alt="back button" onclick="backToSelection()">
        </div>
        <div class="titleContainer" id="hotelTitle">
            <h4 class="title">Hotel Rooms</h4>
            <p class="hotelDescription">Mamyr Resort and Events Place is not only a venue for unforgettable celebrations
                but also a relaxing retreat, offering 11 air-conditioned hotel rooms for guests seeking comfort and convenience.
                Every booking at the hotel includes complimentary access to the resort's pool, allowing guests to unwind and
                enjoy their stay to the fullest. Whether you're here for a grand occasion or a quiet getaway, Mamyr Resort
                offers a beautiful and welcoming environment for all.

            </p>
        </div>
        <div class="container-fluid">
            <div class=" entranceTitleContainer">
                <hr class="entranceLine">
                <h4 class="entranceTitle" style="color: black;">Room Availability </h4>
            </div>
            <div class="filterBtns">
                <input type="text" placeholder="Select your booking date" id="hotelDate">
            </div>
            <?php
            $availsql = "SELECT RSAvailabilityID, RServiceName, RSduration 
            FROM resortAmenities
            WHERE RSCategoryID = 1";

            $result = mysqli_query($conn, $availsql);
            ?>
            <div class="hotelIconsContainer">
                <div class="availabilityIcons">
                    <div class="availabilityIcon" id="allRooms">
                        <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1" class="avail" id="allrooms">
                        <p>All Rooms</p>
                    </div>
                    <div class="availabilityIcon" id="availableRooms">
                        <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 2" class="avail">
                        <p>Available</p>
                    </div>
                    <div class="availabilityIcon" id="unavailableRooms">
                        <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 3" class="avail">
                        <p>Not Available</p>
                    </div>
                </div>

                <div class="hotelIconContainer">
                    <?php
                    if ($result->num_rows > 0) {
                        $i = 1;
                        while ($row = $result->fetch_assoc()) {
                            $isAvailable = ($row['RSAvailabilityID'] == 1);
                            $iconPath = $isAvailable
                                ? "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png"
                                : "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                            $roomName = htmlspecialchars($row['RServiceName']);
                            $duration = htmlspecialchars($row['RSduration']);
                            $availabilityStatus = $isAvailable ? 'available' : 'unavailable';

                            echo '<div class="hotelIconWithCaption" style="text-align: center;" 
                                data-availability="' . $availabilityStatus . '">';


                            echo '<a href="#' . trim($row['RServiceName']) . '">  <img src="' . $iconPath . '" alt="' . $roomName . '" class="hotelIcon" id="hotelIcon' . $i . '"> </a>';
                            echo '  <p class="roomCaption">' . $roomName . '</p>';
                            echo '</div>';

                            $i++;
                        }
                    } else {
                        echo "<p>No room services found.</p>";
                    }
                    ?>

                </div>
            </div>
        </div>
        <div class="ourRooms">
            <div class="titleContainer">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Our Rooms</h4>
            </div>


            <div class="hotelRoomList">
                <?php
                $roomsql = "SELECT * FROM resortAmenities WHERE RScategoryID = 1";
                $roomresult = mysqli_query($conn, $roomsql);
                if (mysqli_num_rows($roomresult) > 0) {
                    foreach ($roomresult as $hotel) {
                ?>
                        <div class="hotel" id="<?= trim($hotel['RServiceName']) ?>">
                            <div class="halfImg">
                                <?php
                                $imgSrc = '../../Assets/Images/no-picture.jpg';
                                if (!empty($hotel['imageData'])) {
                                    $imgData = base64_encode($hotel['RSimageData']);
                                    $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                                }
                                ?>
                                <img src="<?= $imgSrc ?>" alt="User Image" class="rounded" id="displayPhoto">
                            </div>

                            <div class="Description">
                                <h2 class="text bold font-weight-bold"> <?= $hotel['RServiceName']  ?> </h2>
                                <?php
                                $descriptions = explode(',', $hotel['RSdescription']);
                                foreach ($descriptions as $description) {
                                ?>
                                    <p><?= "- " . trim($description) ?><br></p>
                                <?php } ?>
                                <p class="font-weight-bold">
                                    Price: PHP <?= $hotel['RSprice'] ?>
                                </p>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<h5> No Record Found </h5>";
                }
                ?>
            </div>

        </div>
    </div>
    <!-- Back to Top Button -->
    <a href="#" id="backToTopBtn" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </a>

    <footer class="py-1" id="footer" style="margin-top: 5vw;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../../index.php">
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
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>


    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="../../Assets/JS/scrollNavbg.js"></script>
    <script>
        const backbtn = document.getElementById("backToSelection");

        function backToSelection() {
            document.getElementById('selection').style.display = 'block';
            document.getElementById('hotelRooms').style.display = 'none';
            document.getElementById('rates').style.display = 'none';
            document.getElementById("footer").style.marginTop = "5vw";
        };

        function showRates(event) {
            event.preventDefault();
            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'none';
            document.getElementById('rates').style.display = 'block';
            document.getElementById("footer").style.marginTop = "3vw";
        }

        function showHotels(event) {
            event.preventDefault();
            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'block';
            document.getElementById('rates').style.display = 'none';
            document.getElementById("footer").style.marginTop = "3vw";
        }

        flatpickr('#hotelDate', {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
        });

        window.onscroll = function() {
            const btn = document.getElementById("backToTopBtn");
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                btn.style.display = "block";
            } else {
                btn.style.display = "none";
            }
        };

        // Scroll to top
        document.getElementById("backToTopBtn").addEventListener("click", function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
    <!-- filters hotel rooms by the hour -->
    <script>
        // State variables
        let currentAvailabilityFilter = 'all';

        // Initialize filters when page loads
        document.addEventListener('DOMContentLoaded', () => {
            // Default 
            document.getElementById('allRooms').classList.add('selectedIcon');

            // Apply filters 
            applyFilters();

            // Click events
            ['all', 'available', 'unavailable'].forEach(type => {
                document.getElementById(`${type}Rooms`).addEventListener('click', function() {
                    updateAvailability(type, this);
                });
            });
        });

        function updateAvailability(filterType, button) {
            currentAvailabilityFilter = filterType;
            document.querySelectorAll('.availabilityIcon').forEach(icon => icon.classList.remove('selectedIcon'));
            button.classList.add('selectedIcon');

            applyFilters();
        }

        function filterRooms(filterType) {
            currentAvailabilityFilter = filterType;

            // Update selected icon
            document.querySelectorAll('.availabilityIcon').forEach(icon => {
                icon.classList.remove('selectedIcon');
            });

            const selectedIcon = document.getElementById(`${filterType}Rooms`);
            if (selectedIcon) {
                selectedIcon.classList.add('selectedIcon');
            }

            applyFilters(); // Call the filter logic
        }

        function applyFilters() {
            const allIcons = document.querySelectorAll('.hotelIconWithCaption');
            const allRooms = document.querySelectorAll('.hotel');

            allIcons.forEach(icon => {
                const availability = icon.getAttribute('data-availability');
                const matchesAvailability = (currentAvailabilityFilter === 'all') || (currentAvailabilityFilter === availability);

                icon.classList.toggle('hidden', !matchesAvailability);
            });

            allRooms.forEach(room => {
                // No duration logic; just show all rooms
                room.style.display = 'flex';
            });
        }
    </script>

    <!-- AJAX for fetching real time availability -->
    <!-- to be further tested after availability is resolved -->
    <script>
        function fetchAvailability() {
            fetch('/Function/Customer/getAvailability.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        dateTime: document.getElementById('hotelDate').value
                    })
                })
                .then(res => res.json())
                .then(json => {
                    json.rooms.forEach(room => {
                        const icons = document.querySelectorAll(`.hotelIconWithCaption[data-availability]`);
                        icons.forEach(icon => {
                            const name = icon.querySelector('.roomCaption').textContent.trim();
                            if (name === room.service) {
                                icon.setAttribute('data-availability', room.available ? 'available' : 'unavailable');
                            }
                        });
                    });
                    applyFilters(); // re-apply filtering based on updated availability
                })
                .catch(console.error);
        }

        // Re-fetch availability whenever the date changes
        document.getElementById('hotelDate').addEventListener('change', fetchAvailability);
        document.getElementById('hotelDate').addEventListener('keyup', fetchAvailability);
    </script>

</body>

</html>