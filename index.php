<?php
require 'Config/dbcon.php';


//SQL statement for retrieving data for website content from DB
$sectionName = 'BusinessInformation';
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
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="Assets/CSS/landingPage.css">
    <!-- <link rel="stylesheet" href="Assets/CSS/bootstrap.min.css"> -->
    <!-- online stylesheet link for bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <button class=" navbar-toggler ms-auto" id="bg-nav-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Amenities
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="Pages/amenities.php">Resort Amenities</a></li>
                        <li><a class="dropdown-item" href="Pages/ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="Pages/events.php">Events</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Pages/blog.php">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">Be Our Partner</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Pages/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Pages/register.php">Book Now</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Pages/register.php">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="custom-container">
        <div class="titleContainer">
            <div class="mamyrTitle">
                <?php
                $businessName = str_split($contentMap['DisplayName']);
                $display = strtoupper(implode(" ", $businessName))
                ?>
                <h1 class="name"> <?= htmlspecialchars($display ?? 'Name Not Found') ?> </h1>
            </div>
            <div class="description">
                <p class="descriptionText"> <?= htmlspecialchars($contentMap['ShortDesc'] ?? 'Description Not Found') ?>
            </div>
            <a class="btn btn-outline-light me-2" href="Pages/about.php">Learn More</a>
        </div>

        <div class="containerBook">
            <div class="label">
                <h3 class="containerLabel">Check-In Date</h3>
                <h3 class="containerLabel">Check-Out Date</h3>
                <h3 class="containerLabel">No. of Adults</h3>
                <h3 class="containerLabel">No. of Children</h3>
            </div>
            <div class="formBoxes">
                <input type="date" class="form-control" id="start-date" name="start-date" placeholder="MM/DD/YYY">
                <input type="date" class="form-control" id="end-date" name="end-date" placeholder="MM/DD/YYY">
                <input type="number" class="form-control" id="adults" name="adults" placeholder="Adults">
                <input type="number" class="form-control" id="children" name="children" placeholder="Children">
            </div>
            <div class="availBtn">
                <a href="#"><button type="submit" class="btn custom-btn">CHECK FOR AVAILABILITY</button></a>
            </div>
        </div>

        <div class="welcomeSection">
            <div class="resortPic1">
                <img src="Assets/Images/landingPage/resortPic1.png" alt="Mamyr Resort" class="pic1">
            </div>
            <div class="wsText">
                <hr class="line">
                <h4 class="wsTitle">Welcome to <?= htmlspecialchars($contentMap['FullName'] ?? 'Name Not Found') ?></h4>
                <p class="wsDescription"> <?= htmlspecialchars($contentMap['ShortDesc2'] ?? 'Description Not Found') ?> </p>
            </div>

        </div>

        <div class="contact">
            <div class="contactText">
                <hr class="line">
                <h4 class="contactTitle">Contact Us </h4>

                <div class="location">
                    <img src="Assets/Images/landingPage/icons/location.png" alt="locationPin" class="locationIcon">
                    <h5 class="locationText"><?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?></h5>
                </div>

                <div class="number">
                    <img src="Assets/Images/landingPage/icons/phone.png" alt="phone" class="phoneIcon">
                    <h5 class="number"><?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?></h5>
                </div>

                <div class="email">
                    <img src="Assets/Images/landingPage/icons/email.png" alt="email" class="emailIcon">
                    <h5 class="emailAddressText"><?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?></h5>
                </div>


            </div>
            <div class="googleMap" id="googleMap"></div>
        </div>

        <div class="gallery">
            <hr class="line">
            <h4 class="galleryTitle">Gallery </h4>

            <div class="galleryPictures">

                <img src="Assets/Images/landingPage/gallery/img1.png" alt="resort View 1" class="img1 galleryImg">
                <img src="Assets/Images/landingPage/gallery/img2.png" alt="resort View 2" class="img2 galleryImg">
                <img src="Assets/Images/landingPage/gallery/img3.png" alt="resort View 3" class="img3 galleryImg">
                <img src="Assets/Images/landingPage/gallery/img4.png" alt="resort View 4" class="img4 galleryImg">
                <img src="Assets/Images/landingPage/gallery/img5.png" alt="resort View 5" class="img5 galleryImg">
                <img src="Assets/Images/landingPage/gallery/img6.png" alt="resort View 6" class="img6 galleryImg">
            </div>

            <div class="seeMore">
                <a href="Pages/amenities.php" class="btn custom-btn ">See More</a>
            </div>
        </div>



        <footer class="py-1 ">
            <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">

                <img src="Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

                <h3 class="mb-0"><?= htmlspecialchars(strtoupper($contentMap['FullName']) ?? 'Name Not Found') ?></h3>
            </div>

            <div class="info">
                <div class="reservation">
                    <h4 class="reservationTitle">Reservation</h4>
                    <h4 class="numberFooter"><?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?></h4>
                    <h4 class="emailAddressTextFooter"><?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?></h4>
                </div>
                <div class="locationFooter">
                    <h4 class="locationTitle">Location</h4>
                    <h4 class="addressTextFooter"><?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?></h4>

                </div>
            </div>
            <hr class="footerLine">
            <div class="socialIcons">
                <a href="<?= htmlspecialchars($contentMap['FBLink'] ?? 'None Provided') ?>"><i
                        class='bx bxl-facebook-circle'></i></a>
                <a href="mailto: <?= htmlspecialchars($contentMap['GmailAdd'] ?? 'None Provided') ?>"><i class='bx bxl-gmail'></i></a>
                <a href="tel:<?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>">
                    <i class='bx bxs-phone'></i>
                </a>

            </div>

        </footer>
    </div>

    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Bootstrap JS -->
    <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <script>
        function myMap() {
            var mapProp = {
                center: new google.maps.LatLng(15.050861525959231, 121.02183364955998),
                zoom: 5,
            };
            var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        }
    </script>

    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderOverlay = document.getElementById('loaderOverlay');
            const currentPath = window.location.pathname.replace(/\/+$/, '').toLowerCase(); // Normalize

            const navbarLinks = document.querySelectorAll('.navbar a');

            navbarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');

                    if (href && !href.startsWith('#')) {
                        // Create a temporary anchor to parse the href
                        const tempAnchor = document.createElement('a');
                        tempAnchor.href = href;
                        const targetPath = tempAnchor.pathname.replace(/\/+$/, '').toLowerCase();

                        // If the target is different from the current path, show loader
                        if (targetPath !== currentPath) {
                            loaderOverlay.style.display = 'flex';
                        }
                    }
                });
            });
        });

        function hideLoader() {
            const overlay = document.getElementById('loaderOverlay');
            if (overlay) overlay.style.display = 'none';
        }

        // Hide loader on normal load
        window.addEventListener('load', hideLoader);

        // Hide loader on back/forward navigation (from browser cache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });
    </script>


    <script>
        const navbar = document.querySelector('.navbar');
        const navCollapse = document.getElementById('navbarNav');

        // Add background when collapse is shown
        navCollapse.addEventListener('shown.bs.collapse', () => {
            navbar.classList.add('white-bg');
        });

        // Remove background when collapse is hidden
        navCollapse.addEventListener('hidden.bs.collapse', () => {
            navbar.classList.remove('white-bg');
        });
    </script>

    </script>

    <script src="Assets/JS/scrollNavbg.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCalqMvV8mz7fIlyY51rxe8IerVxzUTQ2Q&callback=myMap">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>