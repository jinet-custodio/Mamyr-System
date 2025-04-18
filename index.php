<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="Assets/CSS/landingPage.css">
    <link rel="stylesheet" href="Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="Pages/amenities.php">AMENITIES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">RATES</a>
                </li>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Pages/beOurPartner.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Pages/register.php">BOOK NOW</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="custom-container">
        <div class="titleContainer">
            <div class="mamyrTitle">
                <h1 class="name">M A M Y R</h1>
            </div>
            <div class="description">
                <p class="descriptionText">Welcome to Mamyr Resort and Event Place!
                    We're more than just a resort, we're a place where memories are made. Whether you're here for a
                    relaxing
                    getaway, a family gathering, or a special event, we’re dedicated to making your stay unforgettable.
                </p>
            </div>
            <button class="btn btn-outline-light me-2">Learn More</button>
        </div>

        <div class="containerBook">
            <div class="label">
                <h3 class="containerLabel">Check-In Date</h3>
                <h3 class="containerLabel">Check-Out Date</h3>
                <h3 class="containerLabel">No. of Adults</h3>
                <h3 class="containerLabel">No. of Children</h3>
            </div>
            <div class="formBoxes">
                <input type="date" class="form-control" placeholder="MM/DD/YYY">
                <input type="date" class="form-control" placeholder="MM/DD/YYY">
                <input type="number" class="form-control" placeholder="Adults">
                <input type="number" class="form-control" placeholder="Children">
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
                <h4 class="wsTitle">Welcome to Mamyr Resort and Events Place</h4>
                <p class="wsDescription">Welcome to Mamyr Resort and Events Place, where relaxation and unforgettable
                    moments await you. Whether you're here for a peaceful retreat or a special celebration, we're
                    dedicated to making your experience truly exceptional.</p>
            </div>

        </div>

        <div class="contact">
            <div class="contactText">
                <hr class="line">
                <h4 class="contactTitle">Contact Us </h4>

                <div class="location">
                    <img src="Assets/Images/landingPage/icons/location.png" alt="locationPin" class="locationIcon">
                    <h5 class="locationText">Sitio Colonia Gabihan, San Ildefonso, Bulacan</h5>
                </div>

                <div class="number">
                    <img src="Assets/Images/landingPage/icons/phone.png" alt="phone" class="phoneIcon">
                    <h5 class="number">(0998) 962 4697</h5>
                </div>

                <div class="email">
                    <img src="Assets/Images/landingPage/icons/email.png" alt="email" class="emailIcon">
                    <h5 class="emailAddressText">mamyr@gmail.com</h5>
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

        <div class="announcements">
            <div class="announcementTitleContainer">
                <hr class="announcementLine">
                <h4 class="announcementTitle">Announcements</h4>
            </div>


            <div class="post">
                <h4 class="sorryText">Sorry We Are</h4>
                <hr class="closeLine">
                <h1 class="closedText">CLOSED</h1>
                <hr class="closeLine">
                <h2 class="tomorrowText">TOMORROW</h2>
                <div class="dateTimeConatiner">
                    <h3 class="date">March 25, 2025</h3>
                    <div class="vr"></div>
                    <h3 class="time">9:00am - 6:00pm</h3>
                </div>


                <p class="closedAdditionalText">Thank you for your understanding and continued support.
                    We apologize for any delayed in response and seek your kind understanding during this period for the
                    inconvenience caused.</p>
            </div>


        </div>

        <footer class="py-1 my-2">
            <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">

                <img src="Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

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
    </div>


    <script>
    function myMap() {
        var mapProp = {
            center: new google.maps.LatLng(15.050861525959231, 121.02183364955998),
            zoom: 5,
        };
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
    }
    </script>
    <script src="Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCalqMvV8mz7fIlyY51rxe8IerVxzUTQ2Q&callback=myMap">
    </script>

</body>

</html>