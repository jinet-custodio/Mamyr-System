<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../Config/dbcon.php';


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Events</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/events.css">
    <link rel="stylesheet" href="../Assets/CSS/navbar.css">
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
        <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>


        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"> HOME</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="amenities.php" style="color: black;">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item active" href="#">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="beOurPartnerNew.php" id="bopNav">BE OUR PARTNER</a>
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
        <div id="eventCarousel" class="carousel" data-bs-ride="false">
            <div class="carousel-inner" id="eventsInner">

                <div class="carousel-item active">
                    <div class="cardFlex">
                        <div class="card event-card">
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

                                <button type="button" class="btn btn-primary bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card event-card">
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
                                <button type="button" class="btn btn-primary bookBtn" style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="carousel-item">
                    <div class="cardFlex">
                        <div class="card event-card">
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
                                <button type="button" class="btn btn-primary bookBtn" style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card event-card">
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
                                <button type="button" class="btn btn-primary bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="carousel-item">
                    <div class="cardFlex">
                        <div class="card event-card">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/christening.jpg"
                                alt="Christening Event">
                            <div class="card-body">
                                <h5 class="card-title">Christening/Dedication</h5>
                                <div class="eventDescription">
                                    <p class="eventDesc">Make lasting memories at Mamyr Resort where every celebration,
                                        from
                                        christenings to dedications, is a moment to treasure.</p>
                                </div>

                                <button type="button" class="btn btn-primary bookBtn" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card event-card">
                            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/teamBuilding.jpg"
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
                                <button type="button" class="btn btn-primary bookBtn" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="carousel-item">
                    <div class="cardFlex">
                        <div class="card event-card">
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
                                <button type="button" class="btn btn-primary bookBtn" id="bookBtn"
                                    style="margin-top: auto;">BOOK
                                    NOW</button>
                            </div>
                        </div>

                        <div class="card event-card">
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
                                <button type="button" class="btn btn-primary bookBtn" id="bookBtn"
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

        <?php
        $eventHallID = 4;
        $mainHall = '';
        $miniHall = '';
        $getEventHallQuery = $conn->prepare("SELECT * FROM `resortamenity` WHERE `RScategoryID` = ?");
        $getEventHallQuery->bind_param("i", $eventHallID,);
        $getEventHallQuery->execute();
        $result = $getEventHallQuery->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $serviceName = $row['RServiceName'];
                if (stripos($serviceName, 'Pavilion Hall') !== false) {
                    $mainHall = $row;
                } elseif (stripos($serviceName, 'Mini Pavilion Hall') !== false) {
                    $miniHall = $row;
                }
            }
        }
        ?>


        <div class="mainHallDescContainer">
            <?php if ($mainHall) { ?>
                <h3 class="mainHallDescTitle"><?= htmlspecialchars($mainHall['RServiceName']) ?></h3>

                <ul class="mainHallDescription" id="mainHallDesc">
                    <li>Maximum usage of <?= htmlspecialchars($mainHall['RSduration']) ?? '1 hour' ?>; ₱2,000 per hour extension fee.
                    <li>Elegant, fully air-conditioned function room.</li>
                    <li>Capacity of up to <?= htmlspecialchars($mainHall['RSmaxCapacity']) ?> guests.</li>
                    <li>One (1) air-conditioned private room.</li>
                    <li>Separate powder rooms/restrooms for males and females.</li>
                </ul>

                <h2 class="mainHallPrice text-center mt-5 fw-bold" style="color: #ffff;">₱ <?= htmlspecialchars(number_format($mainHall['RSprice'], 2)) ?></h2>
            <?php } else { ?>
                <h3 class="mainHallDescTitle">No Information to Display</h3>
                <ul class="mainHallDescription" id="mainHallDesc">
                    <li>Maximum usage of ???; ₱2,000 per hour extension fee.
                    <li>Elegant, fully air-conditioned function room.</li>
                    <li>Capacity of up to ??? guests.</li>
                </ul>
            <?php } ?>
        </div>

    </div>

    <div class="miniHall mb-5">
        <div class="miniHallDescContainer">
            <?php if ($miniHall) { ?>
                <h3 class="miniHallDescTitle">Mini Function Hall</h3>

                <ul class="miniHallDescription" id="miniHallDesc">
                    <li>Maximum usage of <?= htmlspecialchars($miniHall['RSduration']) ?? '1 hour' ?>; ₱2,000 per hour extension fee.
                    <li>Elegant, fully air-conditioned function room.</li>
                    <li>Capacity of up to <?= htmlspecialchars($miniHall['RSmaxCapacity']) ?> guests.</li>
                </ul>

                <h2 class="miniHallPrice text-center mt-5 fw-bold" style="color: black;">₱ <?= htmlspecialchars(number_format($miniHall['RSprice'], 2)) ?></h2>
            <?php } else { ?>
                <h3 class="miniHallDescTitle">No Information to Display</h3>
                <ul class="miniHallDescription" id="miniHallDesc">
                    <li>Maximum usage of ???; ₱2,000 per hour extension fee.
                    <li>Elegant, fully air-conditioned function room.</li>
                    <li>Capacity of up to ??? guests.</li>
                </ul>
            <?php } ?>
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

    <?php include 'footer.php'; ?>
    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

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

    <!-- Function for book now button -->
    <script>
        const bookButtons = document.querySelectorAll('.bookBtn');

        bookButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Login or Create an Account to Book',
                    text: 'To proceed with a booking, please log in or create an account.',
                    icon: 'info',
                    confirmButtonText: 'Okay',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'register.php';
                    }
                });
            });
        });
    </script>

</body>
<script>
    $(document).ready(function() {
        $('#eventCarousel').carousel({
            interval: 1000000 * 5
        });
    });
</script>

</html>