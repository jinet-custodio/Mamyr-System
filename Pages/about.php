<?php
require '../Config/dbcon.php';


//SQL statement for retrieving data for website content from DB
$sectionName = 'About';
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
    <title>Mamyr - About</title>
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/about.css">
    <!-- <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css"> -->
    <!-- Online link for Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icon library from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="../Pages/events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="./about.php">ABOUT</a>
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

    <div class="titleContainer">
        <h1 class="title" id="title">ABOUT US</h1>
    </div>

    <div class="aboutTopContainer" id="aboutTopContainer">
        <div class="topPicContainer">
            <img src="../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture" class="resortPic">
        </div>

        <div class="topTextContainer">
            <h3 class="hook"><?= htmlspecialchars($contentMap['Header'] ?? 'Header Not Found') ?> </h3>

            <p class="aboutDescription indent"><?= htmlspecialchars($contentMap['AboutMamyr'] ?? 'No description Not Found') ?></p>

            <a href="#backArrowContainer"><button class="btn btn-primary" onclick="readMore()">Read More</button></a>
        </div>
    </div>

    <div class="ourServicesContainer" id="ourServicesContainer">
        <div class="servicesTitleContainer">
            <h3 class="servicesTitle">Our Services</h3>
            <p class="servicesDescription indent"><?= htmlspecialchars($contentMap['ServicesDesc'] ?? 'No description Not Found') ?></p>
        </div>

        <div class="servicesIconContainer">

            <div class="resortContainer">
                <img src="../Assets/Images/AboutImages/resort.png" alt="Resort Icon" class="resortIcon">
                <h4 class="resortIconTitle"><?= htmlspecialchars($contentMap['Service1'] ?? 'No description Not Found') ?></h4>
                <p class="resortIconDescription"><?= htmlspecialchars($contentMap['Service1Desc'] ?? 'No description Not Found') ?></p>
            </div>

            <div class="eventContainer">
                <img src="../Assets/Images/AboutImages/events.png" alt="Event Icon" class="eventIcon">
                <h4 class="eventIconTitle"><?= htmlspecialchars($contentMap['Service2'] ?? 'No description Not Found') ?></h4>
                <p class="eventIconDescription"><?= htmlspecialchars($contentMap['Service2Desc'] ?? 'No description Not Found') ?></p>
            </div>

            <div class="hotelContainer">
                <img src="../Assets/Images/AboutImages/hotel.png" alt="Hotel Icon" class="hotelIcon">
                <h4 class="hotelIconTitle"><?= htmlspecialchars($contentMap['Service3'] ?? 'No description Not Found') ?></h4>
                <p class="hotelIconDescription"><?= htmlspecialchars($contentMap['Service3Desc'] ?? 'No description Not Found') ?></p>
            </div>
        </div>
    </div>


    <div class="videoContainer" id="videoContainer">
        <div class="videoTextContainer">
            <?php
            $sectionName = 'BusinessInformation';
            $getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
            $getWebContent->bind_param("s", $sectionName);
            $getWebContent->execute();
            $getWebContentResult = $getWebContent->get_result();
            $businessInfo = [];
            while ($row = $getWebContentResult->fetch_assoc()) {
                $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
                $contentID = $row['contentID'];

                $businessInfo[$cleanTitle] = $row['content'];
            }
            ?>
            <h3 class="videoTitle">Explore <?= htmlspecialchars($businessInfo['FullName'] ?? 'No description Not Found') ?></h3>

            <p class="videoDescription indent"><?= htmlspecialchars($contentMap['Explore'] ?? 'No description Not Found') ?></p>
        </div>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted controls class="embed-responsive-item"
                poster="../Assets/Videos/thumbnail2.jpg">
                <source src="../../Assets/Videos/mamyrVideo2.mp4" type="video/mp4">

            </video>
        </div>
    </div>


    <div class="backArrowContainer" id="backArrowContainer">
        <a href="about.php"><img src="../Assets/Images/Icon/whiteArrow.png" alt="Back Button" class="backArrow"> </a>
    </div>

    <div class="mamyrHistoryContainer" id="mamyrHistoryContainer">
        <div class="firstParagraphContainer">
            <div class="firstParagraphtextContainer">
                <p class="firstParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph1'] ?? 'No description Not Found') ?></p>

                <p class="secondParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph2'] ?? 'No description Not Found') ?>
                </p>
            </div>


            <div class="firstImageContainer">
                <img src="../Assets/Images/AboutImages/aboutImage.jpg" alt="Mamyr Picture" class="firstParagraphPhoto">
            </div>
        </div>

        <div class="thirdParagraphContainer">
            <div class="thirdImageContainer">
                <img src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Mamyr Picture"
                    class="thirdParagraphPhoto">

            </div>

            <div class="thirdParagraphtextContainer">
                <p class="thirdParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph3'] ?? 'No description Not Found') ?>
                </p>
            </div>
        </div>

        <div class="fourthParagraphContainer">
            <p class="fourthParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph4'] ?? 'No description Not Found') ?>
            </p>
        </div>
    </div>

    <footer class="py-1" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            </a>
            <h3 class="mb-0"><?= htmlspecialchars(strtoupper($businessInfo['FullName']) ?? 'Name Not Found') ?></h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter"><?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?></h4>
                <h4 class="emailAddressTextFooter"><?= htmlspecialchars($businessInfo['Email'] ?? 'None Provided') ?></h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter"><?= htmlspecialchars($businessInfo['Address'] ?? 'None Provided') ?></h4>

            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="<?= htmlspecialchars($businessInfo['FBLink'] ?? 'None Provided') ?>"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="mailto: <?= htmlspecialchars($businessInfo['GmailAdd'] ?? 'None Provided') ?>"><i class='bx bxl-gmail'></i></a>
            <a href="tel:<?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?>">
                <i class='bx bxs-phone'></i>
            </a>
        </div>
    </footer>


    <script>
        const mamyrHistoryContainer = document.getElementById("mamyrHistoryContainer")
        const backArrowContainer = document.getElementById("backArrowContainer")
        const aboutTopContainer = document.getElementById("aboutTopContainer")
        const ourServicesContainer = document.getElementById("ourServicesContainer")
        const videoContainer = document.getElementById("videoContainer")

        mamyrHistoryContainer.style.display = "none"
        backArrowContainer.style.display = "none"


        function readMore() {
            if (mamyrHistoryContainer.style.display == "none" && backArrowContainer.style.display == "none") {

                mamyrHistoryContainer.style.display = "block";
                backArrowContainer.style.display = "block"
                aboutTopContainer.style.display = "none"
                ourServicesContainer.style.display = "none"
                videoContainer.style.display = "none"
                document.getElementById("title").innerHTML = "ABOUT US - HISTORY"

            } else {
                mamyrHistoryContainer.style.display = "block"
                backArrowContainer.style.display = "block"
            }
        }
    </script>



    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>

    <!-- Sweetalert JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
</body>

</html>