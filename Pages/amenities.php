<?php
require '../Config/dbcon.php';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Amenities</title>
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/amenities.css">
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
                    <a class="nav-link  dropdown-toggle " href=" amenities.php" id="navbarDropdown" role="button"
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
                    <a class="nav-link" href="/Pages/blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="beOurPartner.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./about.php">ABOUT</a>
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

    <div class="amenities">

        <h1 class="title">OUR AMENITIES</h1>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted loop controls class="embed-responsive-item">
                <source src="../Assets/Videos/mamyrVideo1.mp4" type="video/mp4">
            </video>

        </div>

        <div class="pool">
            <div class="poolTitleContainer">
                <hr class="poolLine">
                <h4 class="poolTitle">Swimming Pools</h4>
                <p class="poolDescription">We offer three spacious pools designed for relaxation and fun. Whether you’re
                    looking to take a
                    refreshing dip or lounge by the water, each pool provides a perfect setting to unwind and enjoy your
                    stay. Dive in and make the most of your resort experience!</p>
            </div>

            <div class="carousel-container">
                <div class="carousel">
                    <img src="../Assets/Images/amenities/poolPics/poolPic1.png" alt="Pool Picture 1" class="poolPic1">
                    <img src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Pool Picture 2" class="poolPic2">
                    <img src="../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture 3" class="poolPic3">
                    <img src="../Assets/Images/amenities/poolPics/poolPic4.jpeg" alt="Pool Picture 4" class="poolPic4">
                    <img src="../Assets/Images/amenities/poolPics/poolPic5.jpg" alt="Pool Picture 5" class="poolPic5">
                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="cottage" style="background-color:#f7d5b0; height: 120vh;">
            <div class=" cottageTitleContainer" style="padding-top: 2vw;">
                <hr class="cottageLine">
                <h4 class="cottageTitle">Cottages</h4>
                <p class="cottageDescription">Our cozy cottages offer a relaxing retreat with spacious porches, secure
                    surroundings, and a refreshing ambiance. Enjoy a perfect blend of nature and modern facilities
                    designed for your comfort.</p>
            </div>


            <div class="carousel-container">
                <div class="carousel">

                    <?php
                    $serviceCategory = 'Cottage';
                    $query = "SELECT * FROM resortServices WHERE category = '$serviceCategory' ";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        $cottages = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        $counter = 1;
                        foreach ($cottages as $cottage) {
                            $imageData = $cottage['imageData'];
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mimeType = finfo_buffer($finfo, $imageData);
                            finfo_close($finfo);
                            $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Cottage Picture" class="poolPic<?= $counter ?>">
                    <?php
                            $counter++;
                        }
                    } else {
                        echo 'No Cottages';
                    }
                    ?>
                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>

        </div>

        <div class="videoke" style="height: 120vh;">
            <div class=" poolTitleContainer" style="padding-top: 2vw;">
                <hr class="videokeLine">
                <h4 class="videokeTitle">Videoke Area</h4>
                <p class="videokeDescription">Enjoy nonstop fun just steps away from your cottage! Our videoke area is
                    conveniently located beside the cottages, making it easy to sing, laugh, and bond without going far.
                    With a great sound system and cozy setup, it’s the perfect spot for music-filled memories in the
                    heart of the resort.</p>
            </div>

            <div class="poolPics">
                <img src="../Assets/Images/amenities/cottagePics/cottage3.jpg" alt="Hotel Picture 1" class="pic1">
                <img src="../Assets/Images/amenities/cottagePics/cottage5.jpg" alt="Hotel Picture 1" class="pic1">

            </div>

        </div>

        <div class="pavilion" style="background-color: #7dcbf2; height: 155vh;">
            <div class="pavilionTitleContainer" style="padding-top: 2vw ;">
                <hr class="pavilionLine">
                <h4 class="pavilionTitle">Pavilion Hall</h4>
                <p class="pavilionDescription">Our Pavilion Hall offers the perfect space for events, gatherings, and
                    special occasions. With its spacious and elegant design, it’s ideal for everything from weddings to
                    corporate events, comfortably accommodating up to 350 guests. Fully air-conditioned for your
                    comfort, the hall can be rented for a maximum of 5 hours. Included with your rental is exclusive
                    access to one private air-conditioned room and a dedicated powder room with separate comfort rooms
                    for both male and female guests. Let us help you create unforgettable memories in a setting of pure
                    sophistication and convenience.</p>
            </div>

            <div class="carousel-container">
                <div class="carousel">
                    <img src="../Assets/Images/amenities/pavilionPics/pav1.jpg" alt="Pavilion Picture 1"
                        class="poolPic1">
                    <img src="../Assets/Images/amenities/pavilionPics/pav2.jpg" alt="Pavilion Picture 2"
                        class="poolPic2">
                    <img src="../Assets/Images/amenities/pavilionPics/pav3.jpg" alt="Pavilion Picture 3"
                        class="poolPic3">
                    <img src="../Assets/Images/amenities/pavilionPics/pav4.jpg" alt="Pavilion Picture 4"
                        class="poolPic4">
                    <img src="../Assets/Images/amenities/pavilionPics/pav5.jpg" alt="Pavilion Picture 5"
                        class="poolPic5">

                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="minipavilion" style=" height: 125vh;">
            <div class="minipavilionTitleContainer">
                <hr class="minipavilionLine">
                <h4 class="minipavilionTitle">Mini Pavilion</h4>
                <p class="minipavilionDescription">Our mini pavilion offers an intimate and charming space perfect for
                    small
                    gatherings and special occasions. Designed to comfortably accommodate up to 50 guests, it’s ideal
                    for birthdays, reunions, meetings, or any cozy celebration. Surrounded by a refreshing resort
                    atmosphere, it provides both functionality and a relaxing vibe.</p>
            </div>

            <div class="carousel-container">
                <div class="carousel">
                    <img src="../Assets/Images/amenities/miniPavPics/miniPav1.jpg" alt="Mini Pavilion Picture 1"
                        class="poolPic1">
                    <img src="../Assets/Images/amenities/miniPavPics/miniPav2.jpg" alt="Mini Pavilion Picture 2"
                        class="poolPic2">
                    <img src="../Assets/Images/amenities/miniPavPics/miniPav3.jpeg" alt="Mini Pavilion Picture 3"
                        class="poolPic3">
                    <img src="../Assets/Images/amenities/miniPavPics/miniPav4.jpeg" alt="Mini Pavilion Picture 4"
                        class="poolPic4">
                    <img src="../Assets/Images/amenities/miniPavPics/miniPav5.jpeg" alt="Mini Pavilion Picture 5"
                        class="poolPic5">

                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="hotel" style="background-color:#f7d5b0; height: 140vh;">
            <div class="hotelTitleContainer" style="padding-top: 5vw;">
                <hr class="hotelLine">
                <h4 class="hotelTitle">Mamyr Hotel</h4>
                <p class="hotelDescription">We offer 11 thoughtfully designed hotel rooms, each providing a peaceful and
                    comfortable retreat. Perfect for guests looking for a relaxing space to unwind after a day of
                    exploration, our rooms offer all the essentials for a restful stay with a touch of convenience.</p>
            </div>

            <div class="carousel-container">
                <div class="carousel">
                    <img src="../Assets/Images/amenities/hotelPics/hotel1.jpg" alt="Hotel Picture 1" class="poolPic1">
                    <img src="../Assets/Images/amenities/hotelPics/hotel2.jpg" alt="Hotel Picture 2" class="poolPic2">
                    <img src="../Assets/Images/amenities/hotelPics/hotel3.jpg" alt="Hotel Picture 3" class="poolPic3">
                    <img src="../Assets/Images/amenities/hotelPics/hotel4.jpg" alt="Hotel Picture 4" class="poolPic4">
                    <img src="../Assets/Images/amenities/hotelPics/hotel5.jpeg" alt="Hotel Picture 5" class="poolPic5">

                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="parking">
            <div class="parkingTitleContainer">
                <hr class="parkingLine">
                <h4 class="parkingTitle">Parking Space</h4>
                <p class="parkingDescription">We provide ample parking spaces to ensure a hassle-free stay. Whether
                    you’re
                    arriving by car or with a group, our secure parking area is conveniently located, giving you peace
                    of mind throughout your visit.</p>
            </div>

            <div class="carousel-container">
                <div class="carousel">
                    <img src="../Assets/Images/amenities/parkingPics/parking1.jpg" alt="Parking Picture 1"
                        class="poolPic1">
                    <img src="../Assets/Images/amenities/parkingPics/parking2.jpg" alt="Parking Picture 2"
                        class="poolPic2">
                    <img src="../Assets/Images/amenities/parkingPics/parking3.jpg" alt="Parking Picture 3"
                        class="poolPic3">
                    <img src="../Assets/Images/amenities/parkingPics/parking4.jpg" alt="Parking Picture 4"
                        class="poolPic4">
                    <img src="../Assets/Images/amenities/parkingPics/parking5.jpg" alt="Parking Picture 5"
                        class="poolPic5">

                </div>
                <button class="btn btn-primary prev-btn">&#10094;</button>
                <button class="btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>
    </div>


    <footer class="py-1 my-2">
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

    <script>
    var video = document.getElementById("myVideo");

    video.onplay = function() {
        video.muted = false;
    };
    </script>
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
    const navbar = document.getElementById("navbar");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 10) {
            navbar.classList.add("bg-white", "shadow");
        } else {
            navbar.classList.remove("bg-white", "shadow");
        }
    });
    </script>


    <script>
    const carousels = document.querySelectorAll('.carousel');


    carousels.forEach(carousel => {
        let angle = 0;

        const prevButton = carousel.closest('.carousel-container').querySelector('.prev-btn');
        const nextButton = carousel.closest('.carousel-container').querySelector('.next-btn');


        nextButton.addEventListener('click', () => {
            angle -= 72;
            carousel.style.transform = `rotateY(${angle}deg)`;
        });


        prevButton.addEventListener('click', () => {
            angle += 72;
            carousel.style.transform = `rotateY(${angle}deg)`;
        });
    });
    </script>
</body>

</html>