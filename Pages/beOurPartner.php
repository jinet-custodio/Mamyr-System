<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Be Our Partner</title>
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/beOurPartner.css">
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
                <li class="nav-item">
                    <a class="nav-link " href="amenities.php">AMENITIES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">RATES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">BOOK NOW</a>
                </li>
            </ul>
        </div>
    </nav>


    <div class="titleContainer">
        <!-- <hr class="line"> -->
        <h4 class="title">BE OUR PARTNER</h4>
        <p class="description">At Mamyr Resort and Events Place, we’re looking for talented event management
            professionals to
            help us create unforgettable experiences. If your business specializes in photography, catering, sound &
            lighting, or other event services, we’d love to collaborate with you. Partnering with us gives you access to
            a variety of events, from weddings to corporate gatherings, all while showcasing your expertise in a
            stunning resort setting. Reach out today to explore how we can work together to elevate every event we host!
        </p>
    </div>

    <form action="#" method="POST">
        <div class="container-fluid center-card d-flex justify-content-center align-items-center">
            <div class="card" style="width: 50rem;">

                <div class="card-header">
                    <div class="card-title">
                        <h5 class="titleCard">Partner Application Form</h5>
                        <h6 class="titleDesc">Tell us about you and your company</h6>
                    </div>
                </div>

                <div class="card-body">

                    <h5 class="repName">Representative Name</h5>
                    <div class="name">
                        <input type="text" class="form-control" id="FirstName" name="FirstName" placeholder="First Name"
                            required>
                        <input type="text" class="form-control" id="middleInitial" name="middleInitial"
                            placeholder="Middle Initial" required>
                        <input type="text" class="form-control" id="FirstName" name="FirstName" placeholder="Last Name"
                            required>
                    </div>

                    <h5 class="contactInfo">Contact Info</h5>
                    <div class="contact">
                        <input type="text" class="form-control" id="emailAddress" name="emailAddress"
                            placeholder="Email Address">
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                            placeholder="Phone Number">
                    </div>
                </div>

                <hr class="line">

                <div class="card-body">

                    <h5 class="companyName">Company Information</h5>
                    <div class="name">
                        <input type="text" class="form-control" id="comapanyName" name="companyName"
                            placeholder="Company Name" required>

                    </div>

                    <h5 class="busTypeName">Type of Business</h5>
                    <div class="d-flex">
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Business
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li id="catering-option" class="dropdown-item">Catering</li>
                                <li id="sound-lighting-option" class="dropdown-item">Sound and Lighting</li>
                                <li id="event-hosting-option" class="dropdown-item">Event Hosting</li>
                                <li id="photography-option" class="dropdown-item">Photography/Videography</li>
                                <li id="performer-option" class="dropdown-item">Perfomer</li>
                                <li id="other-option" class="dropdown-item">Other</li>
                            </ul>
                        </div>

                        <input type="text" id="other-input" class="form-control "
                            style="display: none; margin-left: 1vw;" placeholder="Please specify..." />
                    </div>



                    <div class="busAddForm">
                        <h5 class="busAddress">Business Address</h5>
                        <input type="text" class="form-control" id="streetAddress" name="streetAddress"
                            placeholder="Street Address" required>

                        <input type="text" class="form-control" id="address2" name="address2"
                            placeholder="Street Address Line 2 (optional)">

                        <input type="text" class="form-control" id="city" name="city" placeholder="Town/City" required>

                        <input type="text" class="form-control" id="province" name="province" placeholder="Province">

                        <input type="text" class="form-control" id="zip" name="zip" placeholder="ZIP/Postal Code"
                            required>
                    </div>

                    <h5 class="docuTitle">Documents for Business Verification</h5>
                    <input class="form-control" type="file" id="document"
                        accept=".pdf, .doc, .docx, .jpg, .jpeg, .png, ">
                </div>

                <button type="submit" class="btn btn-success btn-md">Submit Request</button>
            </div>




        </div>

    </form>


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
    const otherOption = document.getElementById('other-option');
    const otherInput = document.getElementById('other-input');
    const dropdownButton = document.getElementById('dropdownMenuButton');


    const cateringOption = document.getElementById('catering-option');
    const soundLightingOption = document.getElementById('sound-lighting-option');
    const eventHostingOption = document.getElementById('event-hosting-option');
    const photographyOption = document.getElementById('photography-option');
    const performerOption = document.getElementById('performer-option');


    otherInput.style.display = 'none';


    function updateButtonText(selectedText) {
        dropdownButton.textContent = selectedText;
    }


    otherOption.addEventListener('click', function(e) {
        e.preventDefault();
        otherInput.style.display = 'inline-block';
        updateButtonText('Other');
    });


    cateringOption.addEventListener('click', function(e) {
        e.preventDefault();
        otherInput.style.display = 'none';
        updateButtonText('Catering');
    });

    soundLightingOption.addEventListener('click', function(e) {
        e.preventDefault();
        otherInput.style.display = 'none';
        updateButtonText('Sound and Lighting');
    });

    eventHostingOption.addEventListener('click', function(e) {
        e.preventDefault();
        otherInput.style.display = 'none';
        updateButtonText('Event Hosting');
    });

    photographyOption.addEventListener('click', function(e) {
        e.preventDefault();
        otherInput.style.display = 'none';
        updateButtonText('Photography/Videography');
    });

    performerOption.addEventListener('click', function(e) {
        e.preventDefault();
        otherInput.style.display = 'none';
        updateButtonText('Perfomer');
    });
    </script>

    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
</body>

</html>