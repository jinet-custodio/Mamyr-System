<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');
//for setting image paths in 'include' statements
$baseURL = '../..';

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 3:
        $role = "Admin";
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

//SQL statement for retrieving data for website content from DB
$sectionName = 'Rates and Hotel Rooms';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$defaultImage = "../Assets/Images/no-picture.jpg";
while ($row = $getWebContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
    $contentID = $row['contentID'];
    $contentMap[$cleanTitle] = $row['content'];
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Rates and Hotel Rooms</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/ratesAndHotelRooms.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
</head>

<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">

            <!-- Account Icon on the Left -->
            <ul class="navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
                <?php
                $getProfile = $conn->prepare("SELECT userProfile FROM user WHERE userID = ? AND userRole = ?");
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

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                    <li class="nav-item">
                        <?php if ($userRole !== 2): ?>
                            <a class="nav-link" href="dashboard.php"> Home</a>
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
                            <li><a class="dropdown-item active" href="ratesAndHotelRooms.php">Rates and Hotel Rooms</a>
                            </li>
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
                        <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">Log Out</a>
                    </li>

                </ul>
            </div>
        </nav>


        <main>
            <div class="selection" id="selection" style="display: block;">
                <div class="categories mx-auto" id="categories">
                    <a class="categoryLink d-flex justify-content-center" onclick="showRates(event)">
                        <h5 class="card-title m-auto selected" id="ratesTitle">Resort Rates </h5>
                    </a>
                    <a class="categoryLink  d-flex justify-content-center" onclick="showHotels(event)">
                        <h5 class="card-title m-auto" id="hotelTitle">Hotel Rooms</h5>
                    </a>

                </div>
            </div>


            <div class="rates" id="rates" style="display: none;">
                <!-- <div class="titleContainer">
                    <h4 class="title">Our Rates</h4>
                </div> -->

                <div class="entrance mx-auto" style="padding: 0vw 0 1vw 0; ">
                    <div class=" entranceTitleContainer" style="padding-top: 2vw;">

                        <h4 class="entranceTitle" style="color: whitesmoke;">Resort Entrance Fee</h4>
                    </div>
                    <div class="entranceFee">
                        <?php
                        // DB query
                        $rateSql = "SELECT er.*, etr.time_range  FROM entrancerate  er
                            LEFT JOIN entrancetimerange etr ON er.timeRangeID = etr.timeRangeID
                            ORDER BY 
                                FIELD(sessionType, 'Day', 'Night', 'Overnight'), 
                                FIELD(ERcategory, 'Adult', 'Kids')";
                        $rateResult = mysqli_query($conn, $rateSql);

                        // Organize data into sessions
                        $sessions = [];
                        if (mysqli_num_rows($rateResult) > 0) {
                            while ($row = mysqli_fetch_assoc($rateResult)) {
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
                                <div class="entranceCard card mx-auto">
                                    <div class="entrace-card-body">
                                        <h5 class="entrance-card-title">
                                            <span class="dayNight"><?= strtoupper($session) ?> TOUR</span><br>
                                            <?= $data['time_range'] ?>
                                        </h5>
                                        <div class="entrance-card-content">
                                            <span class="age">ADULT -
                                                PHP<?= number_format($data['ERprice']['Adult'], 2) ?></span>
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

                <div class="titleContainer" style="margin-top: 2vw;">

                    <h4 class="entranceTitle">Cottages</h4>
                </div>

                <div class="cottages mx-auto">
                    <div class="swiper-container cottage-swiper-container">
                        <div class="swiper-wrapper">
                            <?php
                            $cottagesql = "SELECT * FROM resortamenity WHERE RSCategoryID = 2 AND RSAvailabilityID = 1";
                            $cottresult = mysqli_query($conn, $cottagesql);

                            if (mysqli_num_rows($cottresult) > 0) {
                                foreach ($cottresult as $cottage) {
                                    $imgSrc = '../../Assets/Images/Services/Cottage/';
                                    $img = !empty($cottage['RSimageData']) ? $imgSrc . $cottage['RSimageData'] : '';
                            ?>
                                    <div class="swiper-slide">
                                        <div class="card cottage">
                                            <img src="<?= $img ?>" alt="Cottage Image" class="card-img-top">
                                            <div class="card-body description">
                                                <h2 class="fw-bold"><?= $cottage['RServiceName'] ?></h2>
                                                <p><?= $cottage['RSdescription'] ?></p>
                                                <p class="font-weight-bold">Price: PHP <?= $cottage['RSprice'] ?></p>
                                                <a href="resortBooking.php" class="btn btn-primary mt-auto">Book Now</a>
                                            </div>
                                        </div>
                                    </div>
                            <?php
                                }
                            } else {
                                echo "<h5>No Record Found</h5>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="swiper-pagination cottage-pagination"></div>
                </div>



                <div class="videoke mx-auto"
                    style="background-color: oklch(0.64 0.65 220 / 0.1); padding: 0vw 0 3vw 0 ">
                    <div class=" videokeTitleContainer">
                        <h4 class="entranceTitle pt-3">Videoke for Rent</h4>
                    </div>
                    <div class="videokes d-flex">
                        <?php
                        $videoke = 'Videoke%';
                        $vidsql = $conn->prepare("SELECT * FROM resortamenity WHERE RServiceName LIKE ?");
                        $vidsql->bind_param('s', $videoke);
                        $vidsql->execute();
                        $vidresult = $vidsql->get_result();
                        if ($vidresult->num_rows > 0) {
                            while ($data = $vidresult->fetch_assoc()) {
                        ?>
                                <div class="section d-flex flex-column">
                                    <div class="singleImg">
                                        <?php
                                        $imgSrc = '../../Assets/Images/Services/Entertainment/';
                                        if (!empty($data['RSimageData'])) {
                                            $img = $imgSrc . $data['RSimageData'];
                                        }
                                        ?>
                                        <img src="<?= $img ?>" alt="Videoke Image" class="rounded mx-auto"
                                            id="videokeDisplayPhoto">

                                    </div>
                                    <div class="Description" id="videokeDescContainer">
                                        <h2 class="text-center" id="videokePriceDesc"> PHP <?= $data['RSprice'] ?> per Rent
                                        </h2>
                                        <p class="videokeDesc">
                                            <?= $data['RSdescription'] ?>
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

                <div class="d-flex mt-3 billiardMassage">
                    <div class="cottage section d-flex align-items-center" id="billiards">
                        <div class=" videokeTitleContainer mb-2" id="billiardCont">
                            <h4 class="entranceTitle">Blliards Table for Rent</h4>
                        </div>
                        <?php
                        $billiard = 'Billiard';
                        $bilsql = $conn->prepare("SELECT * FROM resortamenity WHERE RServiceName = ?");
                        $bilsql->bind_param("s", $billiard);
                        $bilsql->execute();
                        $bilresult = $bilsql->get_result();
                        if ($bilresult->num_rows > 0) {
                            while ($data = $bilresult->fetch_assoc()) {
                        ?>
                                <div class="d-flex justify-content-center align-items-center" id="billiard-flex">
                                    <div class="Description" id="videokeDescContainer">
                                        <h2 class="text-center" id="videokePriceDesc">
                                            Price: PHP<?= $data['RSprice'] ?> per Hour
                                        </h2>
                                        <p class="videokeDesc">
                                            <?= $data['RSdescription'] ?>
                                        </p>
                                    </div>
                                    <div class="singleImg">
                                        <?php
                                        $imgSrc = '../../Assets/Images/Services/Entertainment/';
                                        if (isset($data['RSimageData'])) {
                                            $img = $imgSrc . $data['RSimageData'];
                                        }
                                        ?>
                                        <img src=" <?= $img ?>" alt="Videoke Image" class="rounded mx-auto "
                                            id="billardsDisplayPhoto">

                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<h5> No Record Found </h5>";
                        }
                        ?>
                    </div>
                    <div class="massage section d-flex flex-column flex-grow-1 flex-shrink-1">
                        <div class=" videokeTitleContainer mb-2">
                            <h4 class="entranceTitle">Massage Chair</h4>
                        </div>
                        <?php
                        $Massage = 'Massage Chair';
                        $massagesql = $conn->prepare("SELECT * FROM resortamenity WHERE RServiceName = ?");
                        $massagesql->bind_param('s', $Massage);
                        $massagesql->execute();
                        $massageresult = $massagesql->get_result();
                        if ($massageresult->num_rows > 0) {
                            while ($data = $massageresult->fetch_assoc()) {
                        ?>
                                <div class="d-flex flex-column align-items-center justify-content-center" id="massage">
                                    <div class="singleImg">
                                        <?php
                                        $imgSrc = '../../Assets/Images/Services/Entertainment/';
                                        if (!empty($data['RSimageData'])) {
                                            $img = $imgSrc . $data['RSimageData'];
                                        }
                                        ?>
                                        <img src="<?= $img ?>" alt="Massage Chair Image" class="rounded"
                                            id="massageChairDisplayPhoto">

                                    </div>
                                    <div class="Description" id="massageDesc">
                                        <h2 class="text-center" id="videokePriceDesc"> <?= $data['RSprice'] ?> pesos for
                                            <?= $data['RSduration'] ?>
                                        </h2>
                                        <p class="text-center">
                                            <?= $data['RSdescription'] ?>
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

            <div class="hotelRooms" id="hotelRooms" style="display: none;">
                <div class="titleContainer" id="hotelTitle">
                    <h4 class="title">Hotel Rooms</h4>
                    <p class="hotelDescription">
                        <?= htmlspecialchars($contentMap['HotelDesc'] ?? 'No description found') ?></p>
                </div>

                <div class="container-fluid">
                    <div class=" entranceTitleContainer">
                        <h4 class="entranceTitle" style="color: black;">Room Availability </h4>
                    </div>
                    <div class="filterBtns d-flex justify-content-center">
                        <input type="text" id="hotelDatePicker" placeholder="Select your booking date">
                        <input type="hidden" id="hotelDate">
                    </div>
                    <?php
                    $availsql = "SELECT RServiceName, 
                        MIN(RSAvailabilityID) AS RSAvailabilityID, 
                        MIN(RSduration) AS RSduration
                    FROM resortamenity
                    WHERE RSCategoryID = 1
                    GROUP BY RServiceName
                    ORDER BY CAST(REGEXP_SUBSTR(RServiceName, '\\d+') AS UNSIGNED);";

                    $result = mysqli_query($conn, $availsql);
                    ?>
                    <div class="hotelIconsContainer">
                        <div class="availabilityIcons">
                            <div class="availabilityIcon" id="allRooms">
                                <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1"
                                    class="avail" id="allrooms">
                                <p>All Rooms</p>
                            </div>
                            <div class="availabilityIcon" id="availableRooms">
                                <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 2"
                                    class="avail">
                                <p>Available</p>
                            </div>
                            <div class="availabilityIcon" id="unavailableRooms">
                                <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 3"
                                    class="avail">
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

                                    echo '<div class="hotelIconWithCaption d-flex flex-column align-items-center" data-availability="' . $availabilityStatus . '">';


                                    echo '<a href="#' . trim($row['RServiceName']) . '" class="d-flex justify-content-center">  <img src="' . $iconPath . '" alt="' . $roomName . '" class="hotelIcon" id="hotelIcon' . $i . '"> </a>';
                                    echo '<p class="roomCaption">' . $roomName . '</p>';
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
                        <h4 class="entranceTitle">Our Rooms</h4>
                    </div>


                    <div class="hotelRoomList mx-auto">
                        <div class="swiper-container hotel-swiper-container">
                            <div class="swiper-wrapper">
                                <?php
                                $roomsql = "WITH Ranked AS (
                                        SELECT *,
                                            ROW_NUMBER() OVER (
                                            PARTITION BY RServiceName
                                            ORDER BY RSAvailabilityID
                                            ) AS rn
                                        FROM resortamenity
                                        WHERE RSCategoryID = 1
                                    )
                                    SELECT *
                                    FROM Ranked
                                    WHERE rn = 1
                                    ORDER BY CAST(SUBSTRING(RServiceName, 5) AS UNSIGNED);";

                                $roomresult = mysqli_query($conn, $roomsql);

                                if (mysqli_num_rows($roomresult) > 0) {
                                    foreach ($roomresult as $hotel) {
                                        $imgSrc = '../../Assets/Images/amenities/hotelPics/hotel1.jpg';
                                        if (!empty($hotel['imageData'])) {
                                            // You can use this if imageData is raw binary
                                            // $imgData = base64_encode($hotel['imageData']);
                                            // $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                                        }
                                ?>
                                        <div class="swiper-slide">
                                            <div class="card hotel" id="<?= trim($hotel['RServiceName']) ?>">
                                                <img src="<?= $imgSrc ?>" alt="Hotel Image" class="card-img-top rounded">
                                                <div class="card-body description">
                                                    <h2 class="text fw-bold bold roomNum"><?= $hotel['RServiceName'] ?></h2>
                                                    <?php
                                                    $descriptions = explode(',', $hotel['RSdescription']);
                                                    foreach ($descriptions as $description) {
                                                    ?>
                                                        <p><?= "- " . trim($description) ?><br></p>
                                                    <?php } ?>
                                                    <p class="font-weight-bold">
                                                        Price: PHP <?= $hotel['RSprice'] ?>
                                                    </p>
                                                    <a href="hotelBooking.php" class="btn btn-primary">Book Now</a>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    }
                                } else {
                                    echo "<h5>No Record Found</h5>";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="swiper-pagination hotel-pagination"></div>
                    </div>
                </div>
            </div>

            <div id="popup-container" class="popup-container">
                <img id="popup-image" class="popup-image" src="" alt="Popup Image" />
                <button id="close-popup" class="btn btn-danger close-popup">Close</button>
            </div>

        </main>
        <?php
        include 'footer.php';
        include 'loader.php';
        include '../Notification/notification.php';
        ?>
    </div>
    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>


    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="../../Assets/JS/scrollNavbg.js"></script>
    <script>
        const ratesTitle = document.getElementById("ratesTitle");
        const hotelTitle = document.getElementById("hotelTitle");

        function showRates(event) {
            event.preventDefault();
            document.getElementById('hotelRooms').style.display = 'none';
            document.getElementById('rates').style.display = 'block';
            if (!ratesTitle.classList.contains('selected')) {
                ratesTitle.classList.add('selected');
            };
            if (hotelTitle.classList.contains('selected')) {
                hotelTitle.classList.remove('selected')
            }
        }

        function showHotels(event) {
            event.preventDefault();
            document.getElementById('hotelRooms').style.display = 'block';
            document.getElementById('rates').style.display = 'none';
            if (!hotelTitle.classList.contains('selected')) {
                hotelTitle.classList.add('selected');
            };
            if (ratesTitle.classList.contains('selected')) {
                ratesTitle.classList.remove('selected')
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (ratesTitle.classList.contains('selected')) {
                document.getElementById('hotelRooms').style.display = 'none';
                document.getElementById('rates').style.display = 'block';
            } else if (hotelTitle.classList.contains('selected')) {
                document.getElementById('rates').style.display = 'none';
                document.getElementById('hotelRooms').style.display = 'block';
            }
        });
    </script>
    <!-- filters hotel rooms by the hour -->
    <script>
        let currentAvailabilityFilter = 'all';

        function applyFilters() {
            const allIcons = document.querySelectorAll('.hotelIconWithCaption');
            allIcons.forEach(icon => {
                const availability = icon.getAttribute('data-availability');
                const show = (currentAvailabilityFilter === 'all') || (currentAvailabilityFilter === availability);
                icon.classList.toggle('hidden', !show);
            });
        }

        function updateAvailability(filterType, button) {
            currentAvailabilityFilter = filterType;
            document.querySelectorAll('.availabilityIcon').forEach(icon => icon.classList.remove('selectedIcon'));
            button.classList.add('selectedIcon');
            applyFilters();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('allRooms').classList.add('selectedIcon');
            applyFilters();

            ['all', 'available', 'unavailable'].forEach(type => {
                document.getElementById(`${type}Rooms`).addEventListener('click', function() {
                    updateAvailability(type, this);
                });
            });
        });

        // Initialize flatpickr
        flatpickr("#hotelDatePicker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            disableMobile: true,
            onChange: function(selectedDates, dateStr) {
                document.getElementById('hotelDate').value = dateStr;
                fetchAvailability();
            }
        });

        // AJAX: Fetch availability from server
        function fetchAvailability() {
            const dateTime = document.getElementById('hotelDate').value;
            if (!dateTime) return;

            fetch('/Function/Customer/getAvailability.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        dateTime
                    })
                })
                .then(res => {
                    console.log("HTTP", res.status, res.statusText);
                    return res.text(); // read raw response
                })
                .then(text => {
                    console.log("RAW RESPONSE:", text);

                    let json;
                    try {
                        json = JSON.parse(text);
                    } catch (e) {
                        console.error("❌ JSON PARSE ERROR:", e);
                        return;
                    }

                    if (!json || !json.rooms) {
                        console.error("❌ Missing 'rooms' in JSON:", json);
                        return;
                    }

                    console.log("Parsed JSON:", json);

                    json.rooms.forEach(room => {
                        const icons = document.querySelectorAll('.hotelIconWithCaption');
                        icons.forEach(icon => {
                            const name = icon.querySelector('.roomCaption').textContent.trim();
                            if (name === room.service) {

                                icon.setAttribute('data-availability', room.available ? 'available' : 'unavailable');

                                const img = icon.querySelector('img');
                                if (img) {
                                    img.src = room.available ?
                                        "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" :
                                        "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                                }
                            }
                        });
                    });

                    applyFilters();
                })
                .catch(err => console.error("❌ FETCH ERROR:", err));
        }
    </script>

    <!-- SwiperJS JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script>
        const cottageSwiper = new Swiper('.cottage-swiper-container', {
            slidesPerView: 4,
            spaceBetween: 30,
            pagination: {
                el: '.cottage-pagination',
                clickable: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                    spaceBetween: 15,
                },
                600: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 25,
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                }
            }
        });


        const hotelSwiper = new Swiper('.hotel-swiper-container', {
            slidesPerView: 3, // show 3 cards per view
            spaceBetween: 30,
            pagination: {
                el: '.hotel-pagination',
                clickable: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 1, // Mobile
                    spaceBetween: 20,
                },
                600: {
                    slidesPerView: 2, // Tablet
                    spaceBetween: 25,
                },
                992: {
                    slidesPerView: 3, // Desktop
                    spaceBetween: 30,
                }
            }
        });
    </script>

    <script>
        const popupContainer = document.getElementById('popup-container');
        const popupImage = document.getElementById('popup-image');
        const closePopupBtn = document.getElementById('close-popup');


        document.querySelectorAll('.rounded').forEach((image) => {
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
</body>

</html>