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
    <title>Mamyr - About</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/about.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
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
                <a href="account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile">
                </a>
            </li>
        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item active" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/Customer/blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/Pages/Customer/about.php">ABOUT</a>
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

    <div class="titleContainer">
        <h1 class="title" id="title">ABOUT US</h1>
    </div>

    <div class="aboutTopContainer" id="aboutTopContainer">
        <div class="topPicContainer">
            <img src="../../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture" class="resortPic">
        </div>

        <div class="topTextContainer">
            <h3 class="hook">Compassionate Service, Unforgettable Family Moments</h3>

            <p class="aboutDescription">Mamyr Resort and Events Place is a peaceful getaway located in Gabihan, San
                Ildefonso, Bulacan, built on a story of resilience, love, and family. Before it became a resort, the
                land was used for pig farming. When the business faced financial challenges, owners Mamerto Dela Cruz
                and Myrna Dela Cruz looked for a new opportunity—something that would not only support their family but
                also bring joy to others.</p>

            <a href="#"><button class="btn btn-primary" onclick="readMore()">Read More</button></a>

        </div>
    </div>

    <div class="ourServicesContainer" id="ourServicesContainer">
        <div class="servicesTitleContainer">
            <h3 class="servicesTitle">Our Services</h3>
            <p class="servicesDescription">Mamyr isn’t just a resort; it’s a family-oriented getaway with comfortable
                rooms and a versatile event venue for gatherings and celebrations. It offers a relaxed, fun environment
                for all ages to enjoy.</p>
        </div>

        <div class="servicesIconContainer">

            <div class="resortContainer">
                <img src="../../Assets/Images/AboutImages/resort.png" alt="Resort Icon" class="resortIcon">
                <h4 class="resortIconTitle">Resort</h4>
                <p class="resortIconDescription">Mamyr features three refreshing pools, providing the perfect spots for
                    family fun, relaxation, and leisurely swims.</p>
            </div>

            <div class="eventContainer">
                <img src="../../Assets/Images/AboutImages/events.png" alt="Event Icon" class="eventIcon">
                <h4 class="eventIconTitle">Events Place</h4>
                <p class="eventIconDescription">Mamyr’s versatile event venue offers a spacious and welcoming setting,
                    ideal for family gatherings, reunions, and celebrations of all kinds.</p>
            </div>

            <div class="hotelContainer">
                <img src="../../Assets/Images/AboutImages/hotel.png" alt="Hotel Icon" class="hotelIcon">
                <h4 class="hotelIconTitle">Hotel</h4>
                <p class="hotelIconDescription">Mamyr’s cozy hotel features 11 comfortable rooms, perfect for a relaxing
                    stay with family and friends.</p>
            </div>
        </div>
    </div>


    <div class="videoContainer" id="videoContainer">
        <div class="videoTextContainer">
            <h3 class="videoTitle">Explore Mamyr Resort and Events Place</h3>

            <p class="videoDescription">At Mamyr Resort, we treat every guest like family, offering an experience that
                goes beyond just comfort. From our humble beginnings to the thriving retreat we are today, we've poured
                our heart and soul into creating a sanctuary where nature and relaxation meet. Our story is built on
                passion, growth, and a deep commitment to providing an unforgettable experience. When you visit, you’ll
                discover not just stunning surroundings and luxurious comfort, but the warm, welcoming spirit that
                defines us. Come join us and see firsthand what makes Mamyr Resort a place where memories are made, and
                guests feel right at home.</p>
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
                <p class="firstParagraph">Mamyr Resort and Events Place is a peaceful getaway located in Gabihan, San
                    Ildefonso, Bulacan, built on a story of resilience, love, and family. Before it became a resort, the
                    land was used for pig farming. When the business faced financial challenges, owners Mamerto Dela
                    Cruz
                    and Myrna Dela Cruz looked for a new opportunity—something that would not only support their family
                    but
                    also bring joy to others.</p>

                <p class="secondParagraph">With faith and hard work, they transformed the land into a relaxing resort
                    that people could enjoy. Their vision and dedication shaped the
                    landscape into a serene retreat where visitors could unwind and create lasting memories.
                    The name "Mamyr" came from their own names—Mamerto and Myrna—a symbol of the spirit of unity that
                    brought
                    the resort to life, making it not just a place to stay, but a reflection of their dreams and the
                    love they poured into every corner of the property.
                </p>
            </div>


            <div class="firstImageContainer">
                <img src="../../Assets/Images/AboutImages/aboutImage.jpg" alt="Mamyr Picture"
                    class="firstParagraphPhoto">
            </div>
        </div>

        <div class="thirdParagraphContainer">
            <div class="thirdImageContainer">
                <img src="../../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Mamyr Picture"
                    class="thirdParagraphPhoto">

            </div>

            <div class="thirdParagraphtextContainer">
                <p class="thirdParagraph">Opened in 2022, Mamyr Resort has become a popular and welcoming place
                    for people looking to relax and enjoy nature. The resort is known for its clean swimming pools,
                    spacious function areas, beautiful surroundings, and warm hospitality. Guests
                    can enjoy the resort's three refreshing swimming pools, two elegant pavilions, cozy cottages to stay
                    in, as well as 11 comfortable
                    hotel rooms for those who prefer a more private stay, and a spacious parking lot to accommodate all
                    guests conveniently.
                </p>
            </div>
        </div>

        <div class="fourthParagraphContainer">
            <p class="fourthParagraph">
                At Mamyr Resort, we treat every guest like family, making sure your stay is special and enjoyable.
                Whether you're celebrating an important event, spending time with loved ones, or just looking for a
                peaceful break, we have everything you need to feel comfortable and relaxed. Our team works hard to
                create a warm and welcoming atmosphere where you can make lasting memories. Visit us and see for
                yourself why we're so proud of how much we've grown.
            </p>
        </div>
    </div>

    <footer class="py-1 my-2" id="footer">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>