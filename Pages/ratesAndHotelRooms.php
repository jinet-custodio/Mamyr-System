<?php
require '../Config/dbcon.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Rates and Hotel Rooms</title>
    <link rel="icon" type="image/x-icon" href="/Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/ratesAndHotelRooms.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href=" ../Pages/amenities.php" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../Pages/amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item active" href="#">HOTEL ROOMS AND RATES</a></li>
                        <li><a class="dropdown-item" href="../Pages/events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">RATES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="selection" id="selection">
        <div class="titleContainer">
            <h4 class="title" id="mainTitle">RATES AND HOTEL ROOMS</h4>
        </div>

        <div class="categories" id="categories">

            <a class="categoryLink" onclick="showRates(event)">
                <div class="card" style="width: 25vw; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../assets/images/amenities/poolPics/poolPic3.jpg"
                        alt="Wedding Event">

                    <div class="card-body">
                        <h5 class="card-title">Resort Rates</h5>
                    </div>
                </div>
            </a>

            <a class="categoryLink" onclick="showHotels()">
                <div class="card" style="width: 25vw; display: flex; flex-direction: column;">
                    <img class="card-img-top" src="../assets/images/amenities/hotelPics/hotel1.jpg" alt="Wedding Event">
                    <div class="card-body">
                        <h5 class="card-title">Hotel Rooms</h5>
                    </div>
                </div>
            </a>

        </div>
    </div>
    <div class="rates" id="rates" style="display: none;">
        <div class="backToSelection" id="backToSelection">
            <img src="../Assets/Images/Icon/back-button.png" alt="back button" onclick="backToSelection()">
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
                $rateSql = "SELECT er.*, etr.time_range  FROM entrancerates  er
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
                $cottagesql = "SELECT * FROM resortAmenities WHERE RSCategoryID = 2";
                $cottresult = mysqli_query($conn, $cottagesql);
                if (mysqli_num_rows($cottresult) > 0) {
                    foreach ($cottresult as $cottage) {
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
            $vidsql = "SELECT * FROM resortAmenities WHERE RServiceName = 'Videoke 1'";
            $vidresult = mysqli_query($conn, $vidsql);
            if (mysqli_num_rows($vidresult) > 0) {
                foreach ($vidresult as $videoke) {
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
            $bilsql = "SELECT * FROM resortAmenities WHERE RServiceName = 'Billiard'";
            $bilresult = mysqli_query($conn, $bilsql);
            if (mysqli_num_rows($bilresult) > 0) {
                foreach ($bilresult as $bill) {
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
            $massagesql = "SELECT * FROM resortAmenities WHERE RServiceName = 'Massage Chair'";
            $massageresult = mysqli_query($conn, $massagesql);
            if (mysqli_num_rows($massageresult) > 0) {
                foreach ($massageresult as $massage) {
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
            <img src="../Assets/Images/Icon/back-button.png" alt="back button" onclick="backToSelection()">
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

            <?php
            $availsql = "SELECT RSAvailabilityID, RServiceName 
            FROM resortAmenities
            WHERE RSCategoryID = 1";

            $result = mysqli_query($conn, $availsql);
            ?>
            <div class="hotelIconsContainer">
                <div class="availabilityIcons">
                    <div class="availabilityIcon" id="allRooms" onclick="filterRooms('all')">
                        <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1" class="avail">
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
                            $availabilityStatus = $isAvailable ? 'available' : 'unavailable';

                            echo '<div class="hotelIconWithCaption" style="display: inline-block; text-align: center;" data-availability="' . $availabilityStatus . '">';
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
                        <div class="hotel">
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
                                <h2 class="text bold font-weight-bold"> <?= $hotel['RServiceName'] ?> </h2>
                                <?php $descriptions = explode(',', $hotel['RSdescription']);
                                foreach ($descriptions as $description) {
                                ?>
                                    <p>
                                        <?= "- " . trim($description) ?> <br>
                                    </p>
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
    <footer class="py-1" id="footer" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
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



    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>
    <script>
        const backbtn = document.getElementById("backToSelection");

        function backToSelection() {
            document.getElementById('selection').style.display = 'block';
            document.getElementById('hotelRooms').style.display = 'none';
            document.getElementById('rates').style.display = 'none';
            document.getElementById("footer").style.marginTop = "100vh";
        };

        function showRates(event) {

            event.preventDefault();
            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'none';
            document.getElementById('rates').style.display = 'block';
            document.getElementById("footer").style.marginTop = "3vw";


        }

        function showHotels(event) {

            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'block';
            document.getElementById('rates').style.display = 'none';
            document.getElementById("footer").style.marginTop = "3vw";
        }


        function filterRooms(filterType) {
            const allRooms = document.querySelectorAll('.hotelIconWithCaption');

            allRooms.forEach(room => {
                const availability = room.getAttribute('data-availability');

                if (filterType === 'all') {
                    room.style.display = 'inline-block'; // show all rooms
                } else if (filterType === availability) {
                    room.style.display = 'inline-block'; // show matching availability
                } else {
                    room.style.display = 'none'; // hide others
                }
            });
        }
        // const navbar = document.getElementById("navbar");

        // window.addEventListener("scroll", () => {
        //     if (window.scrollY > 10) {
        //         navbar.classList.add("bg-white", "shadow");
        //     } else {
        //         navbar.classList.remove("bg-white", "shadow");
        //     }
        // });
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sweet Alert -->
    <!-- <script>
        const bookButtons = document.querySelectorAll('#bopNav');

        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                Swal.fire({
                    title: 'Want to Become Our Business Partner?',
                    text: 'You must have an existing account before becoming a business partner.',
                    icon: 'info',
                    confirmButtonText: 'Sign Up'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'register.php';
                    }
                });
            });
        });
    </script> -->
</body>

</html>