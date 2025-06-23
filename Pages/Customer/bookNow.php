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

    <form action="../../Function/Booking/entranceBooking.php" method="POST" id="resort-page" style="display: none;">
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
                        <input type="date" class="form-control w-100" id="resortBookingDate" name="resortBookingDate" required>
                        <select id="tourSelections" name="tourSelections" class="form-select" required>
                            <option value="Day">Day Tour</option>
                            <option value="Night">Night Tour</option>
                            <option value="Overnight">Overnight Tour</option>
                        </select>
                    </div>

                    <h5 class="noOfPeopleLabel">Number of People</h5>
                    <div class="peopleForm">
                        <input type="number" class="form-control" placeholder="Adults" name="adultCount">
                        <input type="number" class="form-control" placeholder="Children" name="childrenCount">
                    </div>

                    <div class="cottageVideokeForm">
                        <div class="cottageForm">
                            <h5 class="cottageLabel">Cottage</h5>
                            <select id="cottageSelections" name="cottageSelections" class="form-select" required>
                                <option value="" disabled selected>Please Select a Cottage</option>
                                <?php
                                $cottageQuery = "SELECT * FROM resortservices WHERE RScategoryID = 2 AND RSAvailabilityID = 1";
                                $result = mysqli_query($conn, $cottageQuery);
                                if (mysqli_num_rows($result) > 0) {
                                    $cottages = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                    foreach ($cottages as $cottage) {
                                        echo "<option value='" . $cottage['RServiceName'] . "'>Php " . $cottage['RSprice'] . " - Good for " . $cottage['RScapacity'] . " pax " . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No cottages available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="videokeForm">
                            <h5 class="videokeRentalLabel">Videoke Rental</h5>
                            <select id="booleanSelections" name="videokeChoice" class="form-select" required>
                                <option value="yesChoice">Yes</option>
                                <option value="noChoice">No</option>
                            </select>
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


    <form action="../../Function/Booking/hotelBooking.php" method="POST" id="hotel-page" style="display: none;">
        <div class="hotel" id="hotel">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="hotelTitle" id="hotelTitle">HOTEL BOOKING</h4>
            </div>
            <?php
            $availsql = "SELECT RSAvailabilityID, RServiceName
            FROM resortservices 
            WHERE RScategoryID = '1'";

            $result = mysqli_query($conn, $availsql);
            ?>
            <div class="container-fluid">
                <div class="hotelIconsContainer">
                    <div class="availabilityIcons">
                        <div class="icons-container">
                            <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Available Icon"
                                class="avail">
                            <p class="avail-text">Available</p>
                        </div>
                        <div class="icons-container"> <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Not Available Icon"
                                class="avail">
                            <p class="avail-text">Not Available</p>
                        </div>
                    </div>


                    <div class="hotelIconContainer mt-3">
                        <?php
                        if ($result->num_rows > 0) {
                            $i = 1;
                            while ($row = $result->fetch_assoc()) {
                                //ternary operator to check availability
                                $iconPath = ($row['RSAvailabilityID'] == 1)
                                    ? "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png"
                                    : "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                                $roomName = htmlspecialchars($row['RServiceName']);

                                echo '<div class="hotelIconWithCaption" style="display: inline-block; text-align: center;">';
                                echo '  <img src="' . $iconPath . '" alt="' . $roomName . '" class="hotelIcon" id="hotelIcon' . $i . '">';
                                echo '  <p class="roomCaption">' . $roomName . '</p>';
                                echo '</div>';

                                $i++;
                            }
                        } else {
                            echo "<p>No room services found.</p>";
                        }
                        ?>
                    </div>

                    <div>
                        <a href="ratesAndHotelRooms.php" class="btn btn-primary btn-md w-100" id="amenitiesHR"> Take me to Hotel Rooms and
                            Rates</a>
                    </div>
                </div>


                <div class="card hotel-card" id="hotelBookingCard" style="width: 40rem; flex-shrink: 0; ">

                    <div class="checkInOut">
                        <div class="checkIn-container">
                            <h5 class="containerLabel">Check-In Date</h5>
                            <input type="datetime-local" class="form-control" id="checkInDate" required>
                        </div>
                        <div class="checkOut-container">
                            <h5 class="containerLabel">Check-Out Date</h5>
                            <input type="datetime-local" class="form-control" id="checkOutDate" required>
                        </div>
                    </div>

                    <div class="hotelPax">
                        <h5 class="noOfPeopleHotelLabel">Number of People</h5>
                        <div class="hotelPeopleForm">
                            <input type="number" class="form-control" placeholder="Adults" required>
                            <input type="number" class="form-control" placeholder="Children" required>
                        </div>
                    </div>


                    <div class="roomNumbers">
                        <h5 class="roomNumber-title">Room Number</h5>
                        <div class="input-group">
                            <select class="form-select" id="inputGroupSelect01">
                                <option selected>Choose...</option>
                                <?php
                                $category = 'Hotel';
                                $selectHotel = "SELECT rs.*, rsc.categoryName FROM resortServices rs
                            JOIN resortservicescategories rsc ON rs.RScategoryID = rsc.categoryID  
                            WHERE rsc.categoryName = '$category' AND RSAvailabilityID = 1";
                                $result = mysqli_query($conn, $selectHotel);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                ?>
                                        <option value="<?= $row['RServiceName'] ?>"><?= $row['RServiceName'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="eventPax" id="hiddenGuestValue">
                    </div>
                    <button type="submit" class="btn btn-primary" name="hotelBooking" id="hotelBooking">Book Now</button>

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
                        <div class="eventTypeForm">
                            <select id="eventType" name="eventType" class="form-select" required>
                                <option value="" disabled selected>Please Select an Event</option>

                                <?php
                                $sql = "SELECT categoryID, categoryName FROM eventCategories";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // categoryID will be submitted as eventType
                                        echo '<option value="' . $row["categoryID"] . '">' . htmlspecialchars($row["categoryName"]) . '</option>';
                                    }
                                } else {
                                    echo '<option disabled>No categories available</option>';
                                }

                                $conn->close();
                                ?>
                            </select>

                            <input type="hidden" name="selectedEventValue" id="selectedEventValue">
                            <div id="other-container" style="display: none; margin-left: 1vw;">
                                <input type="text" id="other-input" name="other_input" class="form-control "
                                    placeholder="Please specify..." />
                            </div>
                        </div>

                    </div>

                    <div class="dateVenue">
                        <div class="dateForm">
                            <h5 class="dateLabel">Date</h5>
                            <input type="date" class="form-control w-100" name="eventDate" id="eventtBookingDate" disabled required>
                        </div>

                        <div class="venueForm">
                            <h5 class="venueLabel">Venue</h5>
                            <!-- <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                id="venueDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Please Select a Venue
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="pavHall" class="dropdown-item">PAVILION HALL (max. 300pax)</li>
                                <li id="miniPavHall" class="dropdown-item">MINI PAVILION HALL (max. 50pax)</li>
                            </ul> -->
                            <select id="venue-hall" name="eventVenue" class="form-select" disabled required>
                                <option value="" disabled selected>Please Select a Venue</option>
                            </select>
                        </div>
                    </div>

                    <div class="noHrPpl">
                        <div class="hrForm">
                            <h5 class="hourLabel">Number of Hours</h5>
                            <input type="number" class="form-control w-100" id="numberOfHours" readonly required>
                            <input type="hidden" id="eventDuration" name="eventDuration">
                        </div>

                        <div class="peopleEventForm">
                            <h5 class="noOfGuestLabel">Number of Guests</h5>
                            <!-- <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                id="guestDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Estimated Number of Guests
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="guestNo1" class="dropdown-item">10-50 pax</li>
                                <li id="guestNo2" class="dropdown-item">51-100 pax</li>
                                <li id="guestNo3" class="dropdown-item">101-200 pax</li>
                                <li id="guestNo4" class="dropdown-item">201-350 pax</li>
                            </ul> -->
                            <select id="guest-number" class="form-select" disabled required>
                                <option value="" disabled selected>Estimated Number of Guests</option>
                                <option value="guestC1">10-50 pax</option>
                                <option value="guestC2">51-100 pax</option>
                                <option value="guestC3">101-200 pax</option>
                                <option value="guestC4">201-350 pax</option>
                            </select>
                            <input type="hidden" name="eventPax" id="hiddenGuestValue">

                        </div>
                    </div>

                    <div class="package">
                        <div class="packageForm w-100">
                            <h5 class="packageLabel">Package</h5>
                            <?php
                            $packageQuery = "SELECT * FROM packages";
                            ?>
                            <select id="package" name="eventPackage" class="form-select" disabled required>
                                <option value="" disabled selected>Please Select a Package</option>
                            </select>
                        </div>

                        <div class="customPackage w-100">
                            <h5 class="customPackageLabel">Can't find a package?</h5>
                            <a href="#" class=" btn btn-info" id="customPackageBtn">Customize my Package</a>
                        </div>

                    </div>

                    <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                    <textarea class="form-control w-100" id="additionalNotes" rows="5"
                        name="additionalNotes" placeholder="Optional" disabled></textarea>

                    <div class="mt-auto">
                        <a href="packages.php" class="btn btn-info btn-md w-100 mb-3">View Event Packages</a>
                        <button type="submit" class="btn btn-success btn-md w-100" name="eventBook">Book Now</button>
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


    <footer class="py-1 " id="footer">
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

    <!-- <script src="../../Assets/JS/BookNowJS/resortDropdown.js"></script>
    <script src="../../Assets/JS/BookNowJS/hotelDropdown.js"></script>
    <script src="../../Assets/JS/BookNowJS/eventDropdown.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js"></script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->

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

        function backToSelection() {
            categories.style.display = 'block';
            events.style.display = 'none';
            resorts.style.display = 'none';
            hotels.style.display = 'none';
            document.getElementById("footer").style.marginTop = "10vh";
            document.body.style.setProperty('background', 'url(../../Assets/Images/BookNowPhotos/bookNowBg.jpg)');
        };



        document.getElementById('eventType').addEventListener('change', function() {
            const categoryID = this.value; // categoryID is now defined inside the event listener
            const packagesDropdown = document.getElementById('package');
            const packageDisplay = document.querySelector('.packageDisplay');
            const packageCardsContainer = document.getElementById('packageCardsContainer');
            const venueDropdown = document.getElementById('venue-hall'); // Assuming venue select has this ID

            // Enable dropdown
            packagesDropdown.disabled = false;

            // Clear dropdown and card container
            packagesDropdown.innerHTML = '<option value="">Loading packages...</option>';
            packageCardsContainer.innerHTML = ''; // Clear any previous cards

            // Fetch packages
            fetch('../../Function/Booking/getPackages.php?categoryID=' + categoryID)
                .then(response => response.json())
                .then(data => {
                    packagesDropdown.innerHTML = ''; // Clear again
                    packageDisplay.style.display = 'block'; // Show the package display

                    if (data.length > 0) {
                        packagesDropdown.innerHTML = '<option value="" disabled selected>Select a package</option>';

                        data.forEach(pkg => {
                            // Populate dropdown
                            const option = document.createElement('option');
                            option.value = pkg.packageID;
                            option.text = pkg.packageName + ' - ₱' + parseFloat(pkg.Pprice).toFixed(2);
                            packagesDropdown.appendChild(option);

                            // Build card
                            const card = `
                        <div class="card h-100 shadow-sm" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title">${pkg.packageName}</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Duration:</strong> ${pkg.Pduration} hours</li>
                                    <li class="list-group-item"><strong>Capacity:</strong> Up to ${pkg.Pcapacity} guests</li>
                                    <li class="list-group-item"><strong>Price:</strong> ₱${parseFloat(pkg.Pprice).toFixed(2)}</li>
                                </ul>
                            </div>
                        </div>
                    `;
                            packageCardsContainer.insertAdjacentHTML('beforeend', card);
                        });
                    } else {
                        packagesDropdown.innerHTML = '<option value="" disabled>No packages available</option>';
                        packageCardsContainer.innerHTML = '<p>No packages found for this event type.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching packages:', error);
                    packagesDropdown.innerHTML = '<option value="" disabled>Error loading packages</option>';
                    packageCardsContainer.innerHTML = '<p>Error loading packages.</p>';
                });

            // Fetch venues for the selected event category
            fetch('../../Function/Booking/getVenues.php?categoryID=' + categoryID)
                .then(response => response.json())
                .then(data => {
                    venueDropdown.innerHTML = '<option value="" disabled selected>Please Select a Venue</option>';
                    if (data.length > 0) {
                        data.forEach(venue => {
                            const option = document.createElement('option');
                            option.value = venue.resortServiceID;
                            option.text = venue.RServiceName; // Facility name from the database
                            venueDropdown.appendChild(option);
                        });
                    } else {
                        venueDropdown.innerHTML = '<option value="" disabled>No venues available</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching venues:', error);
                    venueDropdown.innerHTML = '<option value="" disabled>Error loading venues</option>';
                });
        });


        document.getElementById('package').addEventListener('change', function() {
            const packageID = this.value;
            const eventtBookingDate = document.getElementById('eventtBookingDate');
            const additionalNotes = document.getElementById('additionalNotes');

            eventtBookingDate.disabled = false;
            additionalNotes.disabled = false;

            // Fetch the selected package's details
            fetch('../../Function/Booking/getPackageDetails.php?packageID=' + packageID)
                .then(response => response.json())
                .then(pkg => {
                    // Set duration
                    const numberOfHoursInput = document.getElementById('numberOfHours');
                    numberOfHoursInput.value = pkg.Pduration;
                    document.getElementById('eventDuration').value = pkg.Pduration;


                    // Set guest capacity
                    const guestSelect = document.getElementById('guest-number');
                    guestSelect.disabled = true;

                    const hiddenGuestInput = document.getElementById('hiddenGuestValue');
                    const capacity = parseInt(pkg.Pcapacity);
                    let selectedValue = '';

                    if (capacity <= 50) selectedValue = 'guestC1';
                    else if (capacity <= 100) selectedValue = 'guestC2';
                    else if (capacity <= 200) selectedValue = 'guestC3';
                    else selectedValue = 'guestC4';

                    guestSelect.value = selectedValue;
                    hiddenGuestInput.value = selectedValue;

                    const venueSelect = document.getElementById('venue-hall');
                    if (pkg.resortServiceID) {
                        venueSelect.value = pkg.resortServiceID;
                    }

                    venueSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching package details:', error);
                });
        });

        function showPackageCards() {
            const container = document.getElementById('packageCardsContainer');
            container.style.display = 'block';
            // Optionally scroll to it
            container.scrollIntoView({
                behavior: 'smooth'
            });
        }

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
                document.getElementById("footer").style.marginTop = "2vw";
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
                document.getElementById("footer").style.marginTop = "2vw";
            });

            hotelLink.addEventListener('click', function(event) {
                categories.style.display = "none";
                events.style.display = "none";
                resorts.style.display = "none";
                hotels.style.display = "block";
                document.body.style.setProperty('background', 'none');
                document.body.style.setProperty('background-color', 'rgb(255, 220, 174)');
                document.getElementById("footer").style.marginTop = "2vw";
            });

        });
    </script>


    <!-- Select Option -->
    <script>
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
                text: "Your request has been sent, please wait for the admin 's approval. Please check your account for more info.",
                icon: "success",
                confirmButtonText: 'Okay'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'Account/account.php';
                }
            });
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <!-- Get the value of event type -->
    <script>
        document.getElementById('eventType').addEventListener('change', function() {
            const selectedValue = this.value;
            document.getElementById('selectedEventValue').value = selectedValue;
        });
    </script>


</body>

</html>