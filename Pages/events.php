<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Events</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/events.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half">
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"> HOME</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="amenities.php" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item active" href="#">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="busPartnerRegister.php" id="bopNav">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
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
        <h4 class="title">EVENTS</h4>
        <p class="description">At Mamyr Resort and Events Place, we celebrate life’s most meaningful moments—weddings,
            birthdays, reunions, corporate events, and more—that can be celebrated in our Pavilion, which can occupy
            up to 350 guests, and our Mini Pavilion, perfect for more intimate gatherings of up to 50
            guests. Whether grand or small, each event is made memorable in a beautiful and comfortable setting
            designed to suit your occasion.
        </p>
    </div>

    <div class="categories">

        <!-- <div class="eventsTitleContainer">
            <h3 class="eventsTitle">Mamyr Events</h3>
            <p class="eventsDescription indent">
                Mamyr Resort and Events Place offers the perfect setting for unforgettable moments, blending elegance,
                comfort, and natural beauty—ideal for life’s most cherished celebrations and meaningful gatherings of
                all kinds.
        </div> -->

        <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">

                <div class="carousel-item active">
                    <div class="cardFlex">
                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/Images/amenities/pavilionPics/pav4.jpg"
                                alt="Wedding Event">
                            <div class="card-body">
                                <h5 class="card-title">Wedding</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Celebrating love and lifelong memories at Mamyr Resort—where
                                        every
                                        wedding
                                        is a
                                        dream come true!</p>
                                </div>

                                <button type="button" class="btn btn-primary" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/debut.jpg"
                                alt="Debut Event">
                            <div class="card-body">
                                <h5 class="card-title">Debut</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Celebrating a milestone at Mamyr Resort and Events
                                        Place—where
                                        every
                                        debut
                                        marks a new chapter of unforgettable memories!</p>
                                </div>
                                <button type="button" class="btn btn-primary" style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="carousel-item">
                    <div class="cardFlex">
                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/kidsParty.jpg"
                                alt="Kids Party">
                            <div class="card-body">
                                <h5 class="card-title">Kids Party</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Creating magical moments at Mamyr Resort and Events Place—where
                                        every
                                        kids'
                                        party is filled with joy, laughter, and unforgettable memories!</p>
                                </div>
                                <button type="button" class="btn btn-primary" style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/birthday.jpg"
                                alt="Birthday Event">
                            <div class="card-body">
                                <h5 class="card-title">Birthday</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Celebrating the joy of life at Mamyr Resort and Events
                                        Place—where
                                        every
                                        year
                                        brings new moments to cherish!</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="carousel-item">
                    <div class="cardFlex">
                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/ImagesEventsPhotos/christening.jpg"
                                alt="Christening Event">
                            <div class="card-body">
                                <h5 class="card-title">Christening/Dedication</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Make lasting memories at Mamyr Resort where every celebration,
                                        from
                                        christenings to dedications, is a moment to treasure.</p>
                                </div>

                                <button type="button" class="btn btn-primary" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/ImagesEventsPhotos/teamBuilding.jpg"
                                alt="Team Building Event">
                            <div class="card-body">
                                <h5 class="card-title">Team Building</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Creating great ideas and strong bonds at Mamyr Resort—where
                                        teamwork
                                        and
                                        leadership thrive in inspiring surroundings!
                                    </p>
                                </div>
                                <button type="button" class="btn btn-primary" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="carousel-item">
                    <div class="cardFlex">
                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/thanksgiving.jpg"
                                alt="Thanksgiving Event">
                            <div class="card-body">
                                <h5 class="card-title">Thanksgiving Party</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Celebrating gratitude and togetherness at Mamyr Resort—where
                                        good
                                        food
                                        and
                                        great company make every moment unforgettable!</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/xmas.jpg"
                                alt="Birthday Event">
                            <div class="card-body">
                                <h5 class="card-title">Christmas Party</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Embracing the magic of the holidays at Mamyr Resort—where grand
                                        feasts
                                        and
                                        unforgettable moments bring joy to all!</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>
                </div>



                <button class="carousel-control-prev " type="button" data-bs-target="#eventCarousel"
                    data-bs-slide="prev">
                    <span class="btn btn-primary carousel-control-prev-icon "></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel"
                    data-bs-slide="next">
                    <span class=" btn btn-primary carousel-control-next-icon"></span>
                </button>

            </div>
        </div>
    </div>

    <div class="venueTitleContainer">
        <h3 class="venueTitle">Our Venues</h3>
        <p class="venueDescription indent">
            Mamyr Resort and Events Place offers two exceptional venues: the spacious Main Function Hall for grand
            celebrations and the Mini Function Hall for intimate gatherings—both crafted to make every event
            truly memorable.
    </div>

    <div class="mainHall">
        <div id="carouselMainHall" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/pavilionPics/pav1.jpg"
                        alt="Pavilion1">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto " src=".../../../../Assets/Images/amenities/pavilionPics/pav2.jpg"
                        alt="Pavilion2">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/pavilionPics/pav3.jpg"
                        alt="Pavilion3">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/pavilionPics/pav4.jpg"
                        alt="Pavilion4">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/pavilionPics/pav5.jpg"
                        alt="Pavilion5">
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselMainHall" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselMainHall" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>



        <div class="mainHallDescContainer">
            <h3 class="mainHallDescTitle">Main Function Hall</h3>

            <ul class="mainHallDescription" id="mainHallDesc">
                <li>Maximum usage of 5 hours; ₱2,000 per hour extension fee.
                <li>Elegant, fully air-conditioned function room.</li>
                <li>Capacity of up to 350 guests.</li>
                <li>One (1) air-conditioned private room.</li>
                <li>Separate powder rooms/restrooms for males and females.</li>
            </ul>

            <h2 class="mainHallPrice text-center mt-5 fw-bold" style="color: #ffff;">₱ 30,000</h2>
        </div>


    </div>

    <div class="miniHall">
        <div class="miniHallDescContainer">
            <h3 class="miniHallDescTitle">Mini Function Hall</h3>

            <ul class="miniHallDescription" id="miniHallDesc">
                <li>Maximum usage of 5 hours; ₱2,000 per hour extension fee.
                <li>Elegant, fully air-conditioned function room.</li>
                <li>Capacity of up to 50 guests.</li>
            </ul>

            <h2 class="miniHallPrice text-center mt-5 fw-bold" style="color: black;">₱ 7,000</h2>
        </div>

        <div id="carouselMiniHall" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/miniPavPics/miniPav1.jpg"
                        alt="Mini Pavilion1">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/miniPavPics/miniPav2.jpg"
                        alt="Mini Pavilion2">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/miniPavPics/miniPav3.jpeg"
                        alt="Mini Pavilion3">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/miniPavPics/miniPav4.jpeg"
                        alt="Mini Pavilion4">
                </div>
                <div class="carousel-item">
                    <img class="d-block m-auto" src=".../../../../Assets/Images/amenities/miniPavPics/miniPav5.jpeg"
                        alt="Mini Pavilion5">
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselMiniHall" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselMiniHall" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>




    </div>


    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

    <footer class="py-1" style="margin-top: 10vw !important;">
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
    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Scroll Nav BG -->
    <script src="../Assets/JS/scrollNavbg.js"></script>

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

</body>

</html>