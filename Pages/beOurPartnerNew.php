<?php

error_reporting(E_ALL);
session_start();
ini_set('display_errors', 1);
require '../Config/dbcon.php';
//for setting image paths in 'include' statements
$baseURL = '..';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort And Events Place - Be Our Partner</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/beOurPartnerNew.css">
    <link rel="stylesheet" href="../Assets/CSS/navbar.css">
    <!-- Link to Bootstrap CSS -->
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>


<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <a href="../index.php"><img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"></a>
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Amenities
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="amenities.php">Resort Amenities</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">Rates and Hotel Rooms</a></li>
                        <li><a class="dropdown-item" href="events.php">Events</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#" id="bopNav">Be Our Partner</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Book Now</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="signUpBtn" href="register.php">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="topSec">
        <div class="topLeft">
            <h6 class="topText">Become Mamyr's Business Partner</h6>

            <h2 class="headerText">Unlock New Opportunities with Mamyr Resort & Events Place as Your Business Partner.
            </h2>
            <h5 class="subtext">Let's work together. Collaborate with us to grow your business and to better serve
                mutual customers.</h5>

            <a href="busPartnerRegister.php" class="btn btn-primary" id="applyasPartner">Apply as Partner</a>
        </div>

        <div class="topRight">
            <img src="../Assets/Images/beOurPartnerPhotos/bpIcon.png" alt="BP Icon" class="bpIcon">
        </div>
    </section>

    <section class="middleSec">

        <div class="partnershipContainer" id="partnershipContainer">
            <div class="partnershipTitleContainer">
                <h3 class="partnershipTitle">Partner Services</h3>

                <p class="partnershipDescription indent">Mamyr Resort and Events Place is open to collaborating with
                    trusted services and businesses, offering opportunities for partnership across various
                    event-related services that contribute to creating memorable and seamless celebrations.</p>
            </div>


            <div class="partnerIconContainer">

                <div class="partnerServiceContainer">
                    <img src="../Assets/Images/beOurPartnerPhotos/photog.png" alt="Photography Icon"
                        class="partnerIcon">
                    <h4 class="partnerTitle">Photography/Videography</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../Assets/Images/beOurPartnerPhotos/sound.png" alt="Sound/Light Icon" class="partnerIcon">
                    <h4 class="partnerTitle">Sound and Lighting</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../Assets/Images/beOurPartnerPhotos/host.png" alt="Host Icon" class="partnerIcon">
                    <h4 class="partnerTitle">Event Hosting</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../Assets/Images/beOurPartnerPhotos/photoBooth.png" alt="Photo Booth Icon"
                        class="partnerIcon">
                    <h4 class="partnerTitle">Photo Booth</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../Assets/Images/beOurPartnerPhotos/perf.png" alt="Performer Icon" class="partnerIcon">
                    <h4 class="partnerTitle">Performer</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../Assets/Images/beOurPartnerPhotos/foodCart.png" alt="Food Cart Icon"
                        class="partnerIcon">
                    <h4 class="partnerTitle">Food Cart</h4>
                </div>
            </div>
    </section>

    <section class="bottomSec">
        <div class="partnershipTitleContainer">
            <h3 class="partnershipTitle">Our Featured Partners </h3>

            <p class="partnershipDescription indent">Mamyr Resort and Events Place is proud to collaborate with a select
                group of trusted businesses and services that help us create unforgettable and seamless events. These
                valued partners
                provide a range of services that contribute to making every celebration special.</p>
        </div>

        <?php
        $approvedPartnerID = 2;
        $getPartnersQuery = $conn->prepare("SELECT p.companyName, pt.partnerTypeDescription, ppt.isApproved
                                            FROM partnership p 
                                            LEFT JOIN 
                                                partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID 
                                            LEFT JOIN
                                                partnershiptype pt ON ppt.partnerTypeID = pt.partnerTypeID
                                            WHERE 
                                                p.partnerStatusID = ?
                                                ");
        $getPartnersQuery->bind_param('i', $approvedPartnerID);
        if (!$getPartnersQuery->execute()) {
            error_log('Failed fetching the partners of mamyr. ' . $getPartnersQuery->error);
        }

        $result = $getPartnersQuery->get_result();

        $partners = [];

        if ($result->num_rows === 0) {
            $partners = [];
        }

        while ($row = $result->fetch_assoc()) {
            $partners[] = $row;
        }

        ?>


        <div class="BPContainer">
            <!-- <?php if (!empty($partners)):
                foreach ($partners as $partner): ?>
            <div class="card bp-card" id="bp1">

                <div class="card-body">
                    <h5 class="card-title"><?= ucwords($partner['companyName']) ?></h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="availability-container">
                        <span class="badge bg-danger text-capitalize">Not Available</span>

                    </div>
                    <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. In odit deleniti,
                        dolore quo officia mollitia minus modi sunt laborum labore distinctio nam asperiores optio
                        aperiam dolorum voluptate? Molestias, nihil optio!</p>


                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary moreInfo-btn" id="bp-moreInfo"
                        data-bs-target="bp-moreInfo-modal">More
                        Details</button>
                </div>
            </div>
            <?php endforeach;
            endif; ?> -->



            <div class="card bp-card" id="bp1">
                <img class="card-img-top" src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Singko Marias</h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="button-container">
                        <span class="badge bg-green text-capitalize">Available</span>
                        <!-- <span class="badge bg-${colorClass} text-capitalize">${status}</span> -->
                        <button type="button" class="badge btn bg-light-blue text-capitalize" data-bs-toggle="modal"
                            data-bs-target="#moreInfo-modal">More
                            Details</button>
                        <button type="button" class="badge btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#contact-modal">Contact
                            Us</button>
                    </div>

                    <div class="description-container">
                        <p class="card-description">Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                            Similique nam quo aspernatur corrupti nemo cumque dolore molestiae illo! Quod deleniti
                            reiciendis animi odio alias, et cum nobis voluptas porro illum.</p>
                    </div>
                </div>
            </div>

            <div class="card bp-card" id="bp1">
                <img class="card-img-top" src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Singko Marias</h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="button-container">
                        <span class="badge bg-warning text-capitalize">Maintenance</span>
                        <!-- <span class="badge bg-${colorClass} text-capitalize">${status}</span> -->
                        <button type="button" class="badge btn bg-light-blue text-capitalize" data-bs-toggle="modal"
                            data-bs-target="#moreInfo-modal">More
                            Details</button>
                        <button type="button" class="badge btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#contact-modal">Contact
                            Us</button>
                    </div>

                    <div class="description-container">
                        <p class="card-description">Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                            Similique nam quo aspernatur corrupti nemo cumque dolore molestiae illo! Quod deleniti
                            reiciendis animi odio alias, et cum nobis voluptas porro illum.</p>
                    </div>
                </div>
            </div>

            <div class="card bp-card" id="bp1">
                <img class="card-img-top" src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Singko Marias</h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="button-container">
                        <span class="badge bg-danger text-capitalize">Not Available</span>
                        <!-- <span class="badge bg-${colorClass} text-capitalize">${status}</span> -->
                        <button type="button" class="badge btn bg-light-blue text-capitalize" data-bs-toggle="modal"
                            data-bs-target="#moreInfo-modal">More
                            Details</button>
                        <button type="button" class="badge btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#contact-modal">Contact
                            Us</button>
                    </div>

                    <div class="description-container">
                        <p class="card-description">Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                            Similique nam quo aspernatur corrupti nemo cumque dolore molestiae illo! Quod deleniti
                            reiciendis animi odio alias, et cum nobis voluptas porro illum.</p>
                    </div>
                </div>
            </div>

            <div class="card bp-card" id="bp1">
                <img class="card-img-top" src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Singko Marias</h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="button-container">
                        <span class="badge bg-warning text-capitalize">Maintenance</span>
                        <!-- <span class="badge bg-${colorClass} text-capitalize">${status}</span> -->
                        <button type="button" class="badge btn bg-light-blue text-capitalize" data-bs-toggle="modal"
                            data-bs-target="#moreInfo-modal">More
                            Details</button>
                        <button type="button" class="badge btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#contact-modal">Contact
                            Us</button>
                    </div>

                    <div class="description-container">
                        <p class="card-description">Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                            Similique nam quo aspernatur corrupti nemo cumque dolore molestiae illo! Quod deleniti
                            reiciendis animi odio alias, et cum nobis voluptas porro illum.</p>
                    </div>
                </div>
            </div>

            <div class="card bp-card" id="bp1">
                <img class="card-img-top" src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Singko Marias</h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="button-container">
                        <span class="badge bg-green text-capitalize">Available</span>
                        <!-- <span class="badge bg-${colorClass} text-capitalize">${status}</span> -->
                        <button type="button" class="badge btn bg-light-blue text-capitalize" data-bs-toggle="modal"
                            data-bs-target="#moreInfo-modal">More
                            Details</button>
                        <button type="button" class="badge btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#contact-modal">Contact
                            Us</button>
                    </div>

                    <div class="description-container">
                        <p class="card-description">Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                            Similique nam quo aspernatur corrupti nemo cumque dolore molestiae illo! Quod deleniti
                            reiciendis animi odio alias, et cum nobis voluptas porro illum.</p>
                    </div>
                </div>
            </div>

            <div class="card bp-card" id="bp1">
                <img class="card-img-top" src="../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Singko Marias</h5>
                    <h6 class="card-subtitle">Photography</h6>
                    <div class="button-container">
                        <span class="badge bg-green text-capitalize">Available</span>
                        <!-- <span class="badge bg-${colorClass} text-capitalize">${status}</span> -->
                        <button type="button" class="badge btn bg-light-blue text-capitalize" data-bs-toggle="modal"
                            data-bs-target="#moreInfo-modal">More
                            Details</button>
                        <button type="button" class="badge btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#contact-modal">Contact
                            Us</button>
                    </div>

                    <div class="description-container">
                        <p class="card-description">Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                            Similique nam quo aspernatur corrupti nemo cumque dolore molestiae illo! Quod deleniti
                            reiciendis animi odio alias, et cum nobis voluptas porro illum.</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- modal for more info -->
        <div class="modal fade" id="moreInfo-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bpName">Singko Marias</h5>
                    </div>
                    <div class="modal-body">
                        <div class="md-container">
                            <label class="mdlabel">Partner Type</label>
                            <h5 class="partnerType">Photography</h5>
                        </div>
                        <div class="md-container">
                            <label class="mdlabel">Business Address:</label>
                            <h5 class="partnerAddress">Poblacion, San Ildefonso, Bulacan</h5>
                        </div>
                        <div class="md-container">
                            <label class="mdlabel">Duration</label>
                            <h5 class="partnerDuration">5 hours</h5>
                        </div>
                        <div class="md-container">
                            <label class="mdlabel">View Our Work</label>
                            <a class="partnerLink" href="https://www.example.com"
                                target="_blank">https://www.example.com</a>
                        </div>

                        <div class="md-container">
                            <label class="mdlabel">Price</label>
                            <h5 class="partnerPrice">₱ 2000</h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- modal for more info -->

        <!-- modal for contact -->
        <div class="modal fade" id="contact-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Contact Us</h5>
                    </div>
                    <div class="modal-body">
                        <div class="md-container">
                            <label class="mdlabel">Email Address</label>
                            <h5 class="partnerEmail"> <a href="example@example.com">singkomarias@gmail.com</a></h5>
                        </div>
                        <div class="md-container">
                            <label class="mdlabel">Business Address:</label>
                            <h5 class="partnerNumber"> <a href="tel:09237641541">
                                    09237641541
                                </a></h5>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- modal for contact -->

    </section>


    <?php include 'footer.php';
    include '../Pages/Customer/loader.php'; ?>
    <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>

    </script>
    <script src="../Assets/JS/scrollNavbg.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Redirects User to Book Now -->
    <script>
    const bookNowBtns = document.querySelectorAll('.bookNowBtn');

    bookNowBtns.forEach(bookNowBtn => {
        bookNowBtn.addEventListener("click", function(e) {
            window.location.href = "/Pages/register.php"
        });
    });
    </script>

</body>

</html>