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
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/bookNow.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="body">

    <?php
    $emailQuery = "SELECT email FROM users WHERE userID = '$userID' and userRole = '$userRole'";
    $emailResult = mysqli_query($conn, $emailQuery);
    if (mysqli_num_rows($emailResult) > 0) {
        $data = mysqli_fetch_assoc($emailResult);
        $email = $data['email'];
    } else {
        echo 'No Email Found';
    }
    ?>
    <nav class="navbar navbar-expand-lg fixed-top">
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
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile">
                </a>
            </li>
        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"> HOME</a>
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
                <li class="nav-item">
                    <a class="nav-link " href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
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

    <!-- Made every section visible except for the selection section to see the errors -->
    <div class="categories-page" id="category-page">
        <div class="titleContainer" style="margin-top: 10vw !important;">
            <h4 class="title">What are you booking for?</h4>
        </div>
        <div class="categories">
            <a href="#resort-page" id="resort-link" class="categoryLink">
                <div class="card category-card resort-category" style="width: 20rem; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../Assets/images/amenities/poolPics/poolPic3.jpg" alt="Wedding Event">

                    <div class="category-body">
                        <h5 class="category-title">RESORT</h5>
                    </div>
                </div>
            </a>
            <a href="#hotel-page" id="hotel-link" class="categoryLink">
                <div class="card category-card hotel-category" style="width: 20rem; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../Assets/images/amenities/hotelPics/hotel1.jpg" alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">HOTEL</h5>
                    </div>
                </div>
            </a>
            <a href="#event-page" id="event-link" class="categoryLink">
                <div class="card category-card event-category" style="width: 20rem; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../../Assets/images/amenities/pavilionPics/pav4.jpg" alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">EVENT</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Resort Booking -->
    <form action="confirmBooking.php" method="POST" id="resort-page" style="display: none;">
        <div class="resort" id="resort">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="resortTitle" id="resortTitle">RESORT BOOKING</h4>
            </div>

            <div class="container-fluid">
                <div class="card resort-card" id="resortBookingCard" style="flex-shrink: 0; ">

                    <h5 class="schedLabel">Schedule</h5>
                    <div class="scheduleForm">
                        <input type="text" class="form-control w-95" id="resortBookingDate" name="resortBookingDate" placeholder="Select booking date" required>
                        <i class="fa-solid fa-calendar" id="calendarIcon" style="margin-left: -5vw;font-size:1.2vw;"> </i>
                        <select id="tourSelections" name="tourSelections" class="form-select" required>
                            <option value="" disabled selected>Select Preferred Tour</option>
                            <option value="Day" id="dayTour">Day Tour</option>
                            <option value="Night" id="nightTour">Night Tour</option>
                            <option value="Overnight" id="overnightTour">Overnight Tour</option>
                        </select>
                    </div>

                    <h5 class="noOfPeopleLabel">Number of People</h5>
                    <div class="peopleForm">
                        <input type="number" class="form-control" placeholder="Adults" name="adultCount">
                        <input type="number" class="form-control" placeholder="Children" name="childrenCount">
                    </div>

                    <div class="cottageRoomForm">
                        <div class="cottageForm" id="cottage">
                            <h5 class="cottageLabel">Cottage</h5>
                            <select id="cottageSelections" name="cottageSelections" class="form-select">
                                <option value="" disabled selected>Please Select a Cottage</option>
                                <?php
                                $cottageQuery = "SELECT * FROM resortAmenities WHERE RScategoryID = 2 AND RSAvailabilityID = 1";
                                $result = mysqli_query($conn, $cottageQuery);
                                if (mysqli_num_rows($result) > 0) {
                                    $cottages = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                    foreach ($cottages as $cottage) {
                                        echo "<option value='" . $cottage['RServiceName'] . "'>₱" . $cottage['RSprice'] . " - Good for " . $cottage['RScapacity'] . " pax " . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No cottages available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="roomNumbers" style="display: none;" id="rooms">
                            <h5 class="roomLabel">Room</h5>
                            <select class="form-select" id="roomSelect" name="roomSelections">
                                <option value="" selected disabled>Choose a room</option>
                                <?php
                                $category = 'Hotel';
                                $selectHotel = "SELECT rs.*, rsc.categoryName FROM resortAmenities rs
                            JOIN resortservicescategories rsc ON rs.RScategoryID = rsc.categoryID  
                            WHERE rsc.categoryName = '$category' AND RSAvailabilityID = 1";
                                $result = mysqli_query($conn, $selectHotel);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                ?>
                                        <option value="<?= $row['RServiceName'] ?>">
                                            <?= $row['RServiceName'] ?> — <?= $row['RScapacity'] ?> guests for ₱<?= $row['RSprice'] ?>
                                        </option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="paymentVideokeForm">
                        <div class="paymentMethod">
                            <h5 class="paymentLabel">Payment Method</h5>
                            <div class="input-group">
                                <select class="form-select" name="PaymentMethod" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                            <!-- <input type="hidden" name="eventPax" id="hiddenGuestValue"> -->
                        </div>

                        <div class="videokeForm">
                            <h5 class="videokeRentalLabel">Videoke Rental</h5>
                            <div class="input-group">
                                <select id="booleanSelections" name="videokeChoice" class="form-select" required>
                                    <option value="" selected disabled>Choose...</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                    <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest" rows="5"
                        placeholder="Optional"></textarea>

                    <div class="mt-auto button-container">
                        <button type="submit" class="btn btn-primary btn-md w-100" name="bookRates">Book Now</button>
                    </div>
                </div>

                <div class="pics">
                    <img src="../../Assets/Images/BookNowPhotos/ResortRates/ratePic1.jpg" alt="Rate Picture 1"
                        class="ratePic">
                    <img src="../../Assets/Images/BookNowPhotos/ResortRates/ratePic2.png" alt="Rate Picture 2"
                        class="ratePic">
                </div>
            </div>


        </div>
    </form>
    <!--end ng resort div-->

    <!-- Hotel Booking -->
    <form action="confirmBooking.php" method="POST" id="hotel-page" style="display: none;">
        <div class="hotel" id="hotel">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="hotelTitle" id="hotelTitle">HOTEL BOOKING</h4>
            </div>
            <?php
            $availsql = "SELECT RSAvailabilityID, RServiceName, RSduration
            FROM resortAmenities 
            WHERE RScategoryID = '1'";

            $result = mysqli_query($conn, $availsql);
            ?>
            <div class="container-fluid" id="hotelContainerFluid">
                <div class="hotelIconsContainer">
                    <div class="availabilityIcons">
                        <div class="availabilityIcon" id="allRooms" onclick="filterRooms('all')">
                            <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1" class="avail" id="allrooms">
                            <p>All Rooms</p>
                        </div>
                        <div class="availabilityIcon" id="availableRooms" onclick="filterRooms('available')">
                            <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 2" class="avail">
                            <p>Available</p>
                        </div>
                        <div class="availabilityIcon" id="unavailableRooms" onclick="filterRooms('unavailable')">
                            <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 3" class="avail">
                            <p>Not Available</p>
                        </div>
                    </div>


                    <div class="hotelIconContainer mt-3">
                        <?php
                        if ($result->num_rows > 0) {
                            $i = 1;
                            while ($row = $result->fetch_assoc()) {
                                $isAvailable = ($row['RSAvailabilityID'] == 1);
                                $iconPath = $isAvailable
                                    ? "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png"
                                    : "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                                $roomName = htmlspecialchars($row['RServiceName']);
                                $availabilityStatus = $isAvailable ? 'available' : 'unavailable';
                        ?>
                                <div class="hotelIconWithCaption" style="display: inline-block; text-align: center;" data-availability="<?= $availabilityStatus ?>">
                                    <a href="#<?= trim($row['RServiceName']) ?>" data-duration="<?= htmlspecialchars($row['RSduration']) ?>">
                                        <img src="<?= $iconPath ?>" alt="<?= $roomName ?>" class="hotelIcon" id="hotelIcon<?= $i ?>">
                                    </a>
                                    <p class="roomCaption"> <?= $roomName ?></p>
                                </div>
                            <?php
                                // echo '<div class="hotelIconWithCaption" style="display: inline-block; text-align: center;" data-availability="' . $availabilityStatus . '">';
                                // echo '<a href="#' . trim($row['RServiceName']) . '" >  <img src="' . $iconPath . '" alt="' . $roomName . '" class="hotelIcon" id="hotelIcon' . $i . '"> </a>';
                                // echo '  <p class="roomCaption">' . $roomName . '</p>';
                                // echo '</div>';

                                $i++;
                            }
                        } else {
                            ?>
                            <p class="text-center m-auto">No room services found.</p>
                        <?php
                        }
                        ?>
                    </div>

                    <div>
                        <a href="ratesAndHotelRooms.php" class="btn btn-primary btn-md w-100" id="amenitiesHR"> Take me to Hotel Rooms and
                            Rates</a>
                    </div>
                </div>


                <div class="card hotel-card" id="hotelBookingCard" style="width: 40rem; flex-shrink: 0; ">

                    <div class="hoursRoom">
                        <div class="NumberOfHours">
                            <h5 class="numberOfHours">Number of Hours</h5>
                            <div class="input-group">
                                <select class="form-select" name="hoursSelected" id="hoursSelected" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="11 hours">11 Hours</option>
                                    <option value="22 hours">22 Hours</option>
                                </select>
                            </div>
                        </div>
                        <div class="roomNumbers">
                            <h5 class="roomNumber-title">Room Number</h5>
                            <div class="input-group">
                                <select class="form-select" name="selectedHotel" id="selectedHotel" required>
                                    <option value="" disabled selected>Choose a room</option>
                                    <?php
                                    $category = 'Hotel';
                                    $selectHotel = "SELECT rs.*, rsc.categoryName FROM resortAmenities rs
                            JOIN resortservicescategories rsc ON rs.RScategoryID = rsc.categoryID  
                            WHERE rsc.categoryName = '$category' AND RSAvailabilityID = 1";
                                    $result = mysqli_query($conn, $selectHotel);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                    ?>
                                            <option value="<?= $row['RServiceName'] ?>" data-duration="<?= $row['RSduration'] ?>"><?= $row['RServiceName'] ?> - Max. of <?= $row['RScapacity'] ?> pax - ₱<?= $row['RSprice'] ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="checkInOut">

                        <div class="checkIn-container">
                            <h5 class="containerLabel">Check-In Date</h5>
                            <div style="display: flex;align-items:center;width:100%">
                                <input type="text" class="form-control" name="checkInDate" id="checkInDate" required placeholder="Select Date and Time">
                                <i class="fa-solid fa-calendar" id="hotelCheckinIcon" style="margin-left: -2vw;font-size:1.2vw;"> </i>
                            </div>
                        </div>
                        <div class="checkOut-container">
                            <h5 class="containerLabel">Check-Out Date</h5>
                            <div style="display: flex;align-items:center;">
                                <input type="text" class="form-control" name="checkOutDate" id="checkOutDate" required placeholder="Select Date and Time">
                                <i class="fa-solid fa-calendar" id="hotelCheckoutIcon" style="margin-left: -2vw;font-size:1.2vw;"> </i>
                            </div>
                        </div>
                    </div>

                    <div class="hotelPax">
                        <h5 class="noOfPeopleHotelLabel">Number of People</h5>
                        <div class="hotelPeopleForm">
                            <input type="number" class="form-control" name="adultCount" placeholder="Adults" required>
                            <input type="number" class="form-control" name="childrenCount" placeholder="Children" required>
                        </div>
                    </div>


                    <div class="paymentMethod">
                        <h5 class="payment-title">Payment Method</h5>
                        <div class="input-group">
                            <select class="form-select" name="PaymentMethod" id="paymentMethod" required>
                                <option value="" disabled selected>Choose...</option>
                                <option value="GCash">GCash</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <!-- <input type="hidden" name="eventPax" id="hiddenGuestValue"> -->
                    </div>

                    <!-- <div class="notes">
                        <h5 class="purposeLabel">Request/s or Note/s</h5>
                        <textarea class="form-control w-100" id="purpose-additionalNotes" name="hotelNotes" rows="2"
                            placeholder="Optional"></textarea>
                    </div> -->

                    <div class="additional-info-container">
                        <ul>
                            <!-- <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon" class="info-icon">&nbsp;&nbsp;The ₱2,500(22hours)/₱2,000(11hours) room accommodates a maximum of 4 pax</li>
                            <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon" class="info-icon">&nbsp;&nbsp;The ₱3,500 room acommodates a maximum of 6 pax</li> -->
                            <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon" class="info-icon">&nbsp;&nbsp;If the maximum pax exceeded, extra guest is charged ₱250 per head</li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-success" name="hotelBooking" id="hotelBooking">Book Now</button>
                </div>
            </div>

        </div>
    </form>
    <!--end ng hotel div -->

    <form action="../../Function/Booking/eventBooking.php" method="POST" id="event-page" style="display: none;">
        <div class=" event" id="event">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="eventTitle" id="eventTitle">EVENT BOOKING</h4>
            </div>

            <div class="container-fluid event-container">
                <div class="card event-card" id="eventBookingCard" style="width: 40rem; flex-shrink: 0; ">

                    <div class="eventForm">
                        <h5 class="eventLabel">Type of Event</h5>
                    </div>

                    <div class="dateVenue">
                        <div class="dateForm">
                            <h5 class="dateLabel">Date</h5>
                            <div style="display: flex;align-items:center;">
                                <!-- <input type="date" class="form-control w-100" name="eventDate" id="eventtBookingDate" disabled required> -->
                                <input type="text" class="form-control w-100" name="eventDate" id="eventtBookingDate" placeholder="Select Date and Time for your Event">
                                <i class="fa-solid fa-calendar" id="eventDateIcon" style="margin-left: -2vw;font-size:1.2vw;"> </i>
                            </div>
                        </div>
                    </div>

                    <div class="noHrPpl">
                    </div>

                    <div class="package">
                    </div>

                    <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                    <textarea class="form-control w-100" id="additionalNotes" rows="5"
                        name="additionalNotes" placeholder="Optional" disabled></textarea>

                    <div class="button-container">
                        <a href="packages.php" class="btn btn-info btn-md">View Event Packages</a>
                        <button type="submit" class="btn btn-success btn-md" name="eventBook">Book Now</button>
                    </div>
                </div>

                <div class="secondrow">
                    <div id="calendar"></div>
                    <div class="packageDisplay" style="display: none;">
                        <div id="packageCardsContainer" class="container d-flex flex-wrap gap-3">
                            <!-- Cards will be inserted here -->
                        </div>
                    </div>

                </div>

            </div>
            <!--end ng container div-->

        </div>
        <!--end ng event div-->
    </form>


    <footer class="py-1 " id="footer" style="margin-top: 5vw !important;">
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
    <!-- Full Calendar for Date display -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js"></script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <!-- Page switch -->
    <script>
        const resortLink = document.getElementById("resort-link");
        const hotelLink = document.getElementById("hotel-link");
        const eventLink = document.getElementById("event-link");

        const categories = document.getElementById("category-page");
        const events = document.getElementById("event-page");
        const hotels = document.getElementById("hotel-page");
        const resorts = document.getElementById("resort-page");
        const backbtn = document.getElementById("backToSelection");

        function filterRooms(filterType) {
            const allRooms = document.querySelectorAll('.hotelIconWithCaption');
            const hotelCardContainer = document.querySelectorAll('#hotelContainerFluid');

            allRooms.forEach(room => {
                const availability = room.getAttribute('data-availability');

                if (filterType === 'all') {
                    room.style.display = 'inline-block'; // show all rooms
                    hotelContainerFluid.style.display = 'flex';
                    hotelContainerFluid.style.alignItems = 'start'
                } else if (filterType === availability) {
                    room.style.display = 'inline-block'; // show matching availability
                    hotelContainerFluid.style.display = 'flex';
                    hotelContainerFluid.style.alignItems = 'center'
                } else {
                    room.style.display = 'none'; // hide others
                }
            });
        }

        function backToSelection() {
            categories.style.display = 'block';
            events.style.display = 'none';
            resorts.style.display = 'none';
            hotels.style.display = 'none';
            document.getElementById("footer").style.marginTop = "5vw";
            document.body.style.setProperty('background', 'url(../../Assets/Images/BookNowPhotos/bookNowBg.jpg)');
        };

        function showPackageCards() {
            const container = document.getElementById('packageCardsContainer');
            container.style.display = 'block';
            // Optionally scroll to it
            container.scrollIntoView({
                behavior: 'smooth'
            });
        }

        //JS for calendar pickers
        const calIcon = document.getElementById("calendarIcon");
        const hotelCheckinIcon = document.getElementById("hotelCheckinIcon");
        const hotelCheckoutIcon = document.getElementById("hotelCheckoutIcon");
        const eventDateIcon = document.getElementById("eventDateIcon");
        //sets the minimum date in which the customer can book  (tentative)
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 3);

        //resort calendar
        flatpickr('#resortBookingDate', {
            minDate: minDate,
            dateFormat: "Y-m-d"
        });

        calIcon.addEventListener('click', function(event) {
            resortBookingDate.click()
        });

        //hotel calendar
        flatpickr('#checkInDate', {
            enableTime: true,
            minDate: minDate,
            dateFormat: "Y-m-d H:i"
        });

        hotelCheckinIcon.addEventListener('click', function(event) {
            checkInDate.click()
        });


        flatpickr('#checkOutDate', {
            enableTime: true,
            minDate: minDate,
            dateFormat: "Y-m-d H:i"
        });

        hotelCheckoutIcon.addEventListener('click', function(event) {
            checkOutDate.click()
        });

        flatpickr('#eventtBookingDate', {
            enableTime: true,
            minDate: minDate,
            dateFormat: "Y-m-d H:i"
        });

        eventDateIcon.addEventListener('click', function(event) {
            eventtBookingDate.click()
        });


        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOM fully loaded and parsed");
            var calendarEl = document.getElementById("calendar");
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
            });

            eventLink.addEventListener('click', function(event) {
                categories.style.display = "none";
                events.style.display = "block";
                resorts.style.display = "none";
                hotels.style.display = "none";
                document.body.style.setProperty('background', 'none');
                document.body.style.setProperty('background-color', 'rgb(164, 241, 255)');
                calendar.render();
                document.getElementById("footer").style.marginTop = "5vw";
            });

            resortLink.addEventListener('click', function(event) {
                categories.style.display = "none";
                events.style.display = "none";
                resorts.style.display = "block";
                hotels.style.display = "none";
                document.body.style.setProperty('background', 'url(../../Assets/Images/BookNowPhotos/bookNowBg.jpg)');
                document.body.style.setProperty('background-repeat', 'no-repeat');
                document.body.style.setProperty('background-size', 'cover');
                document.body.style.setProperty('background-position', 'center');
                document.getElementById("footer").style.marginTop = "5vw";
            });

            hotelLink.addEventListener('click', function(event) {
                categories.style.display = "none";
                events.style.display = "none";
                resorts.style.display = "none";
                hotels.style.display = "block";
                document.body.style.setProperty('background', 'none');
                document.body.style.setProperty('background-color', 'rgb(255, 220, 174)');
                document.getElementById("footer").style.marginTop = "5vw";
            });

        });
    </script>

    <!-- Select Option -->
    <!-- <script>
        // Events
        const eventSelect = document.getElementById('eventType');
        const otherContainer = document.getElementById('other-container');
        const other_input = document.getElementById('other-input');


        eventSelect.addEventListener('change', () => {
            if (eventSelect.value === 'other') {
                otherContainer.style.display = 'block';
                other_input.required = true;
            } else {
                otherContainer.style.display = 'none';
                other_input.required = false;
            }
        });
    </script> -->


    <!-- Hotel check-in check-out  -->
    <script>
        const hoursSelected = document.getElementById('hoursSelected');
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        const selectedHotel = document.getElementById('selectedHotel');
        const hotelDivs = document.querySelectorAll('.hotelIconWithCaption')

        checkInInput.addEventListener('change', () => {
            const selectedValue = hoursSelected.value;
            const checkInDate = new Date(checkInInput.value);
            const addHours = parseInt(selectedValue);
            if (!isNaN(checkInDate.getTime()) && !isNaN(addHours)) {
                const checkOutDate = new Date(checkInDate.getTime() + addHours * 60 * 60 * 1000);

                const year = checkOutDate.getFullYear();
                const month = String(checkOutDate.getMonth() + 1).padStart(2, '0');
                const day = String(checkOutDate.getDate()).padStart(2, '0');
                const hours = String(checkOutDate.getHours()).padStart(2, '0');
                const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;
                checkOutInput.value = formattedDate;

            }
        });



        hoursSelected.addEventListener('change', () => {
            const selectedValue = hoursSelected.value.trim().toLowerCase();


            if (checkInInput.value) {
                checkInInput.dispatchEvent(new Event('change'));
            }


            selectedHotel.setAttribute('data-duration', selectedValue);
            Array.from(selectedHotel.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }
                const roomDuration = option.getAttribute('data-duration')?.trim().toLowerCase() || '';
                option.hidden = roomDuration !== selectedValue;
            });
            selectedHotel.selectedIndex = 0;


            hotelDivs.forEach(div => {
                const aTag = div.querySelector('a[data-duration]');
                if (!aTag) return;

                const duration = aTag.getAttribute('data-duration')?.trim().toLowerCase() || '';
                div.style.display = duration === selectedValue ? 'inline-block' : 'none';
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
                confirmButtonText: 'Okay'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'Account/bookingHistory.php';
                }
            });
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <!-- Show the cottages if overnight -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tourSelect = document.getElementById("tourSelections");
            const rooms = document.getElementById("rooms");
            const roomSelect = document.getElementById("roomSelect");
            const cottages = document.getElementById("cottage");

            tourSelect.addEventListener("change", function() {
                if (tourSelect.value === "Overnight") {
                    rooms.style.display = "block";
                    // roomSelect.setAttribute("required", "required");
                    cottages.style.display = "none";
                } else if (tourSelect.value === "Day") {
                    rooms.style.display = "none";
                    // roomSelect.setAttribute("required", "required");
                    cottages.style.display = "block";
                } else {
                    cottages.style.display = "block";
                    rooms.style.display = "none";
                    roomSelect.removeAttribute("required");
                    roomSelect.value = "";
                }
            });
        });
    </script>


</body>

</html>