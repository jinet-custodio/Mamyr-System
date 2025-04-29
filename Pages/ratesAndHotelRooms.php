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
            <h4 class="title">RATES AND HOTEL ROOMS</h4>
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
        <div class="titleContainer">
            <h4 class="title">Our Rates</h4>
        </div>


        <div class="entrance" style="background-color:rgba(16, 128, 125, 1); padding: 0vw 0 3vw 0; ">
            <div class=" entranceTitleContainer" style="padding-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle" style="color: whitesmoke;">Resort Entrance Fee</h4>
            </div>
            <div class="entranceFee">
                <div class="entranceCard card">
                    <div class="entrace-card-body">
                        <h5 class="entrance-card-title"><span class="dayNight">DAY</span> <br> 9:00 am - 4:00 pm</h5>
                        <div class="entrance-card-content">
                            <span class="age">
                                ADULT - PHP150
                            </span>
                            <span class="age">
                                KIDS - PHP100
                            </span>
                        </div>
                    </div>
                </div>
                <div class="entranceCard card">
                    <div class="entrace-card-body">
                        <h5 class="entrance-card-title"><span class="dayNight">NIGHT</span> <br> 12:00 pm - 8:00 pm</h5>
                        <div class="entrance-card-content">
                            <span class="age">
                                ADULT - PHP180
                            </span>
                            <span class="age">
                                KIDS - PHP130
                            </span>
                        </div>
                    </div>
                </div>
                <div class="entranceCard card">
                    <div class="entrace-card-body">
                        <h5 class="entrance-card-title"><span class="dayNight">OVERNIGHT</span> <br> 8:00 pm - 5:00 am</h5>
                        <div class=" entrance-card-content">
                            <span class="age">
                                ADULT - PHP250
                            </span>
                            <span class="age">
                                KIDS - PHP200
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="cottages">
            <div class="titleContainer">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Cottages</h4>
            </div>

            <div class="cottages">
                <div class="cottage">
                    <div class="Description" style="width: 40%;">
                        <h2> Good for 5 pax </h2>
                        <p>
                            A cozy haven perfect for small families or tight-knit friend groups. Enjoy a comfortable stay surrounded by nature's peace.
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP500
                        </p>
                    </div>
                    <div class="halfImg" style="width: 40%;">
                        <img src="../Assets/Images/amenities/cottagePics/cottage1.jpg" alt="" class="rounded">
                    </div>
                </div>
                <div class="cottage">
                    <div class="Description" style="width: 40%;">
                        <h2> Good for 10 pax </h2>
                        <p>
                            Spacious and breezy, this cottage is ideal for medium-sized groups looking to relax and bond in style.
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP800
                        </p>
                    </div>
                    <div class="halfImg" style="width: 40%;">
                        <img src="../Assets/Images/amenities/cottagePics/cottage1.jpg" alt="" class="rounded">
                    </div>
                </div>
                <div class="cottage">
                    <div class="Description" style="width: 40%;">
                        <h2> Good for 12 pax </h2>
                        <p>
                            Tailored for slightly bigger groups, this cottage blends comfort and space — perfect for reunions or team outings.
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP900
                        </p>
                    </div>
                    <div class="halfImg" style="width: 40%;">
                        <img src="../Assets/Images/amenities/cottagePics/cottage1.jpg" alt="" class="rounded">
                    </div>
                </div>
                <div class="cottage">
                    <div class="Description" style="width: 40%;">
                        <h2> Good for 15 pax </h2>
                        <p>
                            Our group-friendly cottage offers generous room for celebration or rest — great for large families or barkadas.
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP1000
                        </p>
                    </div>
                    <div class="halfImg" style="width: 40%;">
                        <img src="../Assets/Images/amenities/cottagePics/cottage1.jpg" alt="" class="rounded">
                    </div>
                </div>
                <div class="cottage">
                    <div class="Description" style="width: 40%;">
                        <h2> Good for 20 pax </h2>
                        <p>
                            The ultimate group retreat! Big, breezy, and built for bonding — ideal for events, company outings, or big reunions.
                        </p>
                        <p class="font-weight-bold">
                            Price: PHP2000
                        </p>
                    </div>
                    <div class="halfImg" style="width: 40%;">
                        <img src="../Assets/Images/amenities/cottagePics/cottage1.jpg" alt="" class="rounded">
                    </div>
                </div>
            </div>
        </div>


        <div class="videoke" style="background-color:whitesmoke; padding: 0vw 0 3vw 0 ">
            <div class=" videokeTitleContainer" style="padding-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Videoke for Rent</h4>
            </div>
            <div class="section">
                <div class="singleImg" style="width: 50%;">
                    <img src="../Assets/Images/amenities/cottagePics/cottage1.jpg" alt="" class="rounded">
                </div>
                <div class="Description" id="videokeDesc" style="width: 40%;">
                    <h2 style="font-size: 3vw;"> PHP800 per Rent </h2>
                    <p>
                        Enjoy nonstop fun just steps away from your cottage! Our videoke area is
                        conveniently located beside the cottages, making it easy to sing, laugh, and bond without going far.
                        With a great sound system and cozy setup, it’s the perfect spot for music-filled memories in the
                        heart of the resort.
                    </p>
                </div>
            </div>
        </div>

        <div class=" videokeTitleContainer" style="padding-top: 2vw;">
            <hr class="entranceLine">
            <h4 class="entranceTitle">Blliards Table for Rent</h4>
        </div>

        <div class="cottage" id="billiards">
            <div class="Description" style="width: 40%;">
                <p>
                    Add some friendly competition to your getaway with our billiards table, available for rent by the hour. It's perfect for guests who want to kick back, line up a shot, and enjoy a classic game. Great for both casual players and pool sharks alike!
                </p>
                <p class="font-weight-bold">
                    Price: PHP200 per Hour
                </p>
            </div>
            <div class="singleImg" style="width: 50%;">
                <img src="../Assets/Images/amenities/billiardPics/billiardPic3.png" alt="" class="rounded">
            </div>

        </div>

        <div class="massage" style="background-color:rgba(125, 203, 242, 1); padding: 0vw 0 3vw 0; margin-bottom:3vw; ">
            <div class=" videokeTitleContainer" style="padding-top: 2vw;">
                <hr class="entranceLine">
                <h4 class="entranceTitle">Massage Chair</h4>
            </div>
            <div class="section" id="massage">
                <div class="singleImg" style="width: 50%;">
                    <img src="../Assets/Images/amenities/massageChairPics/massageChair.png" alt="" class="rounded">
                </div>
                <div class="Description" id="massageDesc" style="width: 40%;">
                    <h2 style="font-size: 3vw;"> 100 pesos for 40 minutes </h2>
                    <p>
                        Relax and unwind with our simple yet effective massage chair,
                        designed to provide soothing relief after a long day. With its easy-to-use settings
                        and comfortable design, this chair targets key areas to help you relax and de-stress.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="hotelRooms" id="hotelRooms">

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
        function showRates(event) {
            event.preventDefault();
            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'none';
            document.getElementById('rates').style.display = 'block';

        }

        function showHotels(event) {
            event.preventDefault();
            document.getElementById('selection').style.display = 'none';
            document.getElementById('hotelRooms').style.display = 'block';
            document.getElementById('rates').style.display = 'none';
            document.getElementById("footer").style.marginTop = "1vw";
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