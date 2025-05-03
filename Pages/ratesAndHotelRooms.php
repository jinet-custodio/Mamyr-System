<?php
require '../Config/dbcon.php';

// $session_timeout = 3600;

// // ini_set('session.gc_maxlifetime', $session_timeout);
// session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

// if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
//     header("Location: ../register.php");
//     exit();
// }

// if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
//     $_SESSION['error'] = 'Session Expired';

//     session_unset();
//     session_destroy();
//     header("Location: ../register.php?session=expired");
//     exit();
// }

// $_SESSION['last_activity'] = time();

$userID = $_SESSION['userID'];
$userType = $_SESSION['userType'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Rates and Hotel Rooms</title>
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
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
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
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
                    <a class="nav-link" href="#">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">RATES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Pages/beOurPartner.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">BOOK NOW</a>
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
                    <img class="card-img-top" src="../assets/images/amenities/poolPics/poolPic3.jpg" alt="Wedding Event">

                    <div class="card-body">
                        <h5 class="card-title">Resort Rates</h5>
                    </div>
                </div>
            </a>

            <a class="categoryLink" onclick="showHotels(event)">
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
                $rateSql = "SELECT * FROM entrancerates ORDER BY 
                    FIELD(session_type, 'Day', 'Night', 'Overnight'), 
                    FIELD(category, 'Adult', 'Kids')";
                $rateResult = mysqli_query($conn, $rateSql);

                // Organize data into sessions
                $sessions = [];
                if (mysqli_num_rows($rateResult) > 0) {
                    while ($row = mysqli_fetch_assoc($rateResult)) {
                        $session = $row['session_type'];
                        if (!isset($sessions[$session])) {
                            $sessions[$session] = [
                                'time_range' => $row['time_range'],
                                'rates' => []
                            ];
                        }
                        $sessions[$session]['rates'][$row['category']] = $row['price'];
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
                                    <span class="age">ADULT - PHP<?= number_format($data['rates']['Adult'], 2) ?></span>
                                    <span class="age">KIDS - PHP<?= number_format($data['rates']['Kids'], 2) ?></span>
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
                $cottagesql = "SELECT * FROM resortServices WHERE category = 'Cottage'";
                $cottresult = mysqli_query($conn, $cottagesql);
                if (mysqli_num_rows($cottresult) > 0) {
                    foreach ($cottresult as $cottage) {
                ?>
                        <div class="cottage">
                            <div class="Description" style="width: 40%;">
                                <h2> Good for <?= $cottage['capacity'] ?> pax </h2>
                                <p>
                                    <?= $cottage['description'] ?>
                                </p>
                                <p class="font-weight-bold">
                                    Price: PHP <?= $cottage['price'] ?>
                                </p>
                            </div>
                            <div class="halfImg" style="width: 40%;">
                                <?php
                                $imgSrc = '../../Assets/Images/no-picture.jpg';
                                if (!empty($cottage['imageData'])) {
                                    $imgData = base64_encode($cottage['imageData']);
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
            $vidsql = "SELECT * FROM resortServices WHERE facilityName = 'Videoke 1'";
            $vidresult = mysqli_query($conn, $vidsql);
            if (mysqli_num_rows($vidresult) > 0) {
                foreach ($vidresult as $videoke) {
            ?>
                    <div class="section">
                        <div class="singleImg" style="width: 40%;">
                            <?php
                            $imgSrc = '../../Assets/Images/no-picture.jpg';
                            if (!empty($videoke['imageData'])) {
                                $imgData = base64_encode($videoke['imageData']);
                                $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                            }
                            ?>
                            <img src="<?= $imgSrc ?>" alt="Videoke Image" class="rounded" id="displayPhoto">

                        </div>
                        <div class="Description" id="videokeDesc" style="width: 40%;">
                            <h2 style="font-size: 3vw;"> PHP <?= $videoke['price'] ?> per Rent </h2>
                            <p>
                                <?= $videoke['description'] ?>
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
            $bilsql = "SELECT * FROM resortServices WHERE facilityName = 'Billiard'";
            $bilresult = mysqli_query($conn, $bilsql);
            if (mysqli_num_rows($bilresult) > 0) {
                foreach ($bilresult as $bill) {
            ?>
                    <div class="Description" style="width: 40%;">
                        <p>
                            <?= $bill['description'] ?>
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP<?= $bill['price'] ?> per Hour
                        </p>
                    </div>
                    <div class="singleImg" style="width: 50%;">
                        <?php
                        $imgSrc = '../../Assets/Images/no-picture.jpg';
                        if (!empty($bill['imageData'])) {
                            $imgData = base64_encode($bill['imageData']);
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
            $massagesql = "SELECT * FROM resortServices WHERE facilityName = 'Massage Chair'";
            $massageresult = mysqli_query($conn, $massagesql);
            if (mysqli_num_rows($massageresult) > 0) {
                foreach ($massageresult as $massage) {
            ?>
                    <div class="section" id="massage">
                        <div class="singleImg" style="width: 50%;">
                            <?php
                            $imgSrc = '../../Assets/Images/no-picture.jpg';
                            if (!empty($massage['imageData'])) {
                                $imgData = base64_encode($massage['imageData']);
                                $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                            }
                            ?>
                            <img src="<?= $imgSrc ?>" alt="Massage Chair Image" class="rounded" id="displayPhoto">

                        </div>
                        <div class="Description" id="massageDesc" style="width: 40%;">
                            <h2 style="font-size: 3vw;"> <?= $massage['price'] ?> pesos for <?= $massage['duration'] ?> </h2>
                            <p>
                                <?= $massage['description'] ?>
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
            <p class="hotelDescription">At Mamyr Resort and Events Place, we celebrate life’s most meaningful moments—weddings,
                birthdays, reunions, corporate events, and more—that can be celebrated in our Pavilion, which can occupy
                up to 350 guests, and our Mini Pavilion, perfect for more intimate gatherings of up to 50
                guests. Whether grand or small, each event is made memorable in a beautiful and comfortable setting
                designed to suit your occasion.
            </p>
        </div>
        <div class="container-fluid">
            <div class=" entranceTitleContainer">
                <hr class="entranceLine">
                <h4 class="entranceTitle" style="color: black;">Room Availability </h4>
            </div>

            <?php
            $availsql = "SELECT s.availabilityID, rs.facilityName 
            FROM services s
            JOIN resortServices rs ON s.resortServiceID = rs.resortServiceID
            WHERE rs.category = 'Room'";

            $result = mysqli_query($conn, $availsql);
            ?>
            <div class="hotelIconsContainer">
                <div class="availabilityIcons">
                    <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1"
                        class="avail">
                    <p>Available</p>
                    <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 1"
                        class="avail">
                    <p>Not Available</p>
                </div>

                <div class="hotelIconContainer">
                    <?php
                    if ($result->num_rows > 0) {
                        $i = 1;
                        while ($row = $result->fetch_assoc()) {
                            //ternary operator to check availability
                            $iconPath = ($row['availabilityID'] == 1)
                                ? "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png"
                                : "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                            $roomName = htmlspecialchars($row['facilityName']);

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
            </div>
        </div>
        <div class="ourRooms">
            <div class="titleContainer">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Our Rooms</h4>
            </div>
            <div class="hotelRoomList">
                <?php
                $roomsql = "SELECT * FROM resortServices WHERE category = 'Room'";
                $roomresult = mysqli_query($conn, $roomsql);
                if (mysqli_num_rows($roomresult) > 0) {
                    foreach ($roomresult as $hotel) {
                ?>
                        <div class="hotel">
                            <div class="halfImg">
                                <?php
                                $imgSrc = '../../Assets/Images/no-picture.jpg';
                                if (!empty($hotel['imageData'])) {
                                    $imgData = base64_encode($hotel['imageData']);
                                    $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                                }
                                ?>
                                <img src="<?= $imgSrc ?>" alt="User Image" class="rounded" id="displayPhoto">

                            </div>

                            <div class="Description">
                                <h2 class="text bold"> <?= $hotel['facilityName'] ?> </h2>
                                <p>
                                    <?= $hotel['description'] ?>
                                </p>
                                <p class="font-weight-bold">
                                    Price: PHP <?= $hotel['price'] ?>
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
    <footer class="py-1" id="footer" style="margin-top: 100vh;">
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
            event.preventDefault();
            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'block';
            document.getElementById('rates').style.display = 'none';
            document.getElementById("footer").style.marginTop = "3vw";
        }

        const navbar = document.getElementById("navbar");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 10) {
                navbar.classList.add("bg-white", "shadow");
            } else {
                navbar.classList.remove("bg-white", "shadow");
            }
        });
    </script>
</body>

</html>