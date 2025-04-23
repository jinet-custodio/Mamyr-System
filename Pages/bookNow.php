<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/bookNow.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="body">
    <nav class="navbar navbar-expand-lg fixed-top">
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
                    <a class="nav-link dropdown-toggle" href="../Pages/amenities.php" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item " href="../Pages/amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="#">HOTEL ROOMS AND RATES</a></li>
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
                    <a class="nav-link " href="../Pages/beOurPartner.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="register.php">BOOK NOW</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- <div class="titleContainer">
        <h4 class="title">What are you booking for?</h4>
    </div> -->


    <!-- <div class="categories">

        <a href="../pages/register.php" class="categoryLink">
            <div class="card" style="width: 20rem; display: flex; flex-direction: column; height: 100%;">
                <img class="card-img-top" src="../assets/images/amenities/poolPics/poolPic3.jpg" alt="Wedding Event">

                <div class="card-body">
                    <h5 class="card-title">RESORT</h5>
                </div>
            </div>
        </a>

        <a href="../pages/register.php" class="categoryLink">
            <div class="card" style="width: 20rem; display: flex; flex-direction: column; height: 100%;">
                <img class="card-img-top" src="../assets/images/amenities/hotelPics/hotel1.jpg" alt="Wedding Event">
                <div class="card-body">
                    <h5 class="card-title">HOTEL</h5>
                </div>
            </div>
        </a>

        <a href="../pages/register.php" class="categoryLink">
            <div class="card" style="width: 20rem; display: flex; flex-direction: column; height: 100%;">
                <img class="card-img-top" src="../assets/images/amenities/pavilionPics/pav4.jpg" alt="Wedding Event">
                <div class="card-body">
                    <h5 class="card-title">EVENT</h5>
                </div>
            </div>
        </a>

    </div> -->

    <!--<form action="#" method="POST">
        <div class="resort" id="resort">

            <div class="titleContainer">
                <h4 class="resortTitle">RESORT BOOKING</h4>
            </div>

            <div class="container-fluid">
                <div class="card" id="resortBookingCard"style="width: 40rem; flex-shrink: 0; ">

                    <h5 class="schedLabel">Schedule</h5>

                    <div class="scheduleForm">
                        <input type="date" class="form-control w-100" id="resortBookingDate" required>




                        <button class="btn btn-primary dropdown-toggle w-100" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            DAY/NIGHT TOUR
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li id="dayTour" class="dropdown-item">Day Tour</li>
                            <li id="nightTour" class="dropdown-item">Night Tour</li>

                        </ul>


                    </div>

                    <h5 class="noOfPeopleLabel">People</h5>

                    <div class="peopleForm">
                        <input type="number" class="form-control" placeholder="Adults" required>
                        <input type="number" class="form-control" placeholder="Children" required>
                    </div>


                    <div class="cottageVideokeForm">

                        <div class="cottageForm">
                            <h5 class="cottageLabel">Cottage</h5>



                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                id="cottageDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Please Select a Cottage
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="cottage1" class="dropdown-item">Php 500 - Good for 5 pax</li>
                                <li id="cottage2" class="dropdown-item">Php 800 - Good for 10 pax</li>
                                <li id="cottage3" class="dropdown-item">Php 900 - Good for 12 pax</li>
                                <li id="cottage4" class="dropdown-item">Php 1,000 - Good for 15 pax</li>
                                <li id="cottage5" class="dropdown-item">Php 2,000 - Good for 25 pax</li>


                            </ul>
                        </div>

                        <div class="videokeForm w-100">
                            <h5 class="videokeRentalLabel">Videoke Rental</h5>


                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                id="videokeDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                YES/NO
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="yes" class="dropdown-item">YES</li>
                                <li id="no" class="dropdown-item">NO</li>
                            </ul>


                        </div>
                    </div>


                    <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                    <textarea class="form-control w-100" id="purpose-additionalNotes" rows="5"
                        placeholder="Optional"></textarea>

                    <div class="mt-auto">
                        <button type="submit" class="btn btn-success btn-md w-100">Submit Request</button>
                    </div>
                </div>

                <div class="pics">
                    <img src="../Assets/Images/BookNowPhotos/ResortRates/ratePic1.jpg" alt="Rate Picture 1"
                        class="ratePic">
                    <img src="../Assets/Images/BookNowPhotos/ResortRates/ratePic2.png" alt="Rate Picture 2"
                        class="ratePic">
                </div>
            </div>
            

        </div>-->
    <!--end ng resort div-->


    <form action="#" method="POST">
        <div class="hotel" id="hotel">

            <div class="titleContainer">
                <h4 class="hotelTitle">HOTEL BOOKING</h4>
            </div>

            <div class="container-fluid">

                <div class="hotelIconsContainer">
                    <div class="availabilityIcons">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/availableIcon.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/notAvailableIcon.png" alt="Rate Picture 1"
                            class="hotelIcon">
                    </div>

                    <div class="hotelIconContainer">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon3.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon4.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon5.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon6.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon7.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon8.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon8.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon10.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon11.png" alt="Rate Picture 1"
                            class="hotelIcon">
                        <img src="../Assets/Images/BookNowPhotos/hotelIcons/icon12.png" alt="Rate Picture 1"
                            class="hotelIcon">
                    </div>
                    <div class="mt-5">
                        <a href="#" class="btn btn-primary btn-md w-100" id="amenitiesHR"> Take me to Hotel Rooms and
                            Rates</a>
                    </div>
                </div>


                <div class="card" id="hotelBookingCard" style="width: 40rem; flex-shrink: 0; ">

                    <div class="checkInOut">
                        <div class="checkInOutLabel">
                            <h3 class="containerLabel">Check-In Date</h3>
                            <h3 class="containerLabel">Check-Out Date</h3>
                        </div>
                        <div class="checkInOutForm">
                            <input type="date" class="form-control w-100" id="checkInDate" required>
                            <input type="date" class="form-control w-100" id="checkOutDate" required>
                        </div>
                    </div>

                    <!-- <h5 class="schedLabel">Schedule</h5>

                    <div class="scheduleForm">
                        <input type="date" class="form-control w-100" id="resortBookingDate" required>




                        <button class="btn btn-primary dropdown-toggle w-100" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            DAY/NIGHT TOUR
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li id="dayTour" class="dropdown-item">Day Tour</li>
                            <li id="nightTour" class="dropdown-item">Night Tour</li>

                        </ul>


                    </div>

                    <h5 class="noOfPeopleLabel">People</h5>

                    <div class="peopleForm">
                        <input type="number" class="form-control" placeholder="Adults" required>
                        <input type="number" class="form-control" placeholder="Children" required>
                    </div>


                    <div class="cottageVideokeForm">

                        <div class="cottageForm">
                            <h5 class="cottageLabel">Cottage</h5>



                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                id="cottageDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Please Select a Cottage
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="cottage1" class="dropdown-item">Php 500 - Good for 5 pax</li>
                                <li id="cottage2" class="dropdown-item">Php 800 - Good for 10 pax</li>
                                <li id="cottage3" class="dropdown-item">Php 900 - Good for 12 pax</li>
                                <li id="cottage4" class="dropdown-item">Php 1,000 - Good for 15 pax</li>
                                <li id="cottage5" class="dropdown-item">Php 2,000 - Good for 25 pax</li>


                            </ul>
                        </div>

                        <div class="videokeForm w-100">
                            <h5 class="videokeRentalLabel">Videoke Rental</h5>


                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                id="videokeDropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                YES/NO
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="yes" class="dropdown-item">YES</li>
                                <li id="no" class="dropdown-item">NO</li>
                            </ul>


                        </div>
                    </div>


                    <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                    <textarea class="form-control w-100" id="purpose-additionalNotes" rows="5"
                        placeholder="Optional"></textarea>

                    <div class="mt-auto">
                        <button type="submit" class="btn btn-success btn-md w-100">Submit Request</button>
                    </div>
                </div>
 -->

                </div>
                <!--end ng container div-->

            </div>
            <!--end ng resort div-->












































































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
            const dropdownButton = document.getElementById('dropdownMenuButton');
            const dayTour = document.getElementById('dayTour');
            const nightTour = document.getElementById('nightTour');

            function tourUpdateButtonText(selectedText) {
                dropdownButton.textContent = selectedText;
            }

            dayTour.addEventListener('click', function(e) {
                e.preventDefault();
                tourUpdateButtonText('DAY TOUR');
            });

            nightTour.addEventListener('click', function(e) {
                e.preventDefault();
                tourUpdateButtonText('NIGHT TOUR');
            });
            </script>

            <script>
            const cottageDropdownButton = document.getElementById('cottageDropdownMenuButton');
            const cottage1 = document.getElementById('cottage1');
            const cottage2 = document.getElementById('cottage2');
            const cottage3 = document.getElementById('cottage3');
            const cottage4 = document.getElementById('cottage4');
            const cottage5 = document.getElementById('cottage5');


            function cottageUpdateButtonText(selectedText) {
                cottageDropdownButton.textContent = selectedText;
            }

            cottage1.addEventListener('click', function(e) {
                e.preventDefault();
                cottageUpdateButtonText('Php 500 - Good for 5 pax');
            });

            cottage2.addEventListener('click', function(e) {
                e.preventDefault();
                cottageUpdateButtonText('Php 800 - Good for 10 pax');
            });

            cottage3.addEventListener('click', function(e) {
                e.preventDefault();
                updateButtonText('Php 900 - Good for 12 pax');
            });

            cottage4.addEventListener('click', function(e) {
                e.preventDefault();
                cottageUottageUpdateButtonText('Php 1,000 - Good for 15 pax');
            });

            cottage5.addEventListener('click', function(e) {
                e.preventDefault();
                cottageUpdateButtonText('Php 2,000 - Good for 25 pax');
            });
            </script>

            <script>
            const videokeDropdownButton = document.getElementById('videokeDropdownMenuButton');
            const yes = document.getElementById('yes');
            const no = document.getElementById('no');

            function videokeUpdateButtonText(selectedText) {
                videokeDropdownButton.textContent = selectedText;
            }

            yes.addEventListener('click', function(e) {
                e.preventDefault();
                videokeUpdateButtonText('YES');
            });

            no.addEventListener('click', function(e) {
                e.preventDefault();
                videokeUpdateButtonText('NO');
            });
            </script>



            <script src="../Assets/JS/bootstrap.bundle.min.js"></script>






</body>

</html>