<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

//SQL statement for retrieving data for website content from DB
$sectionName = 'About';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
while ($row = $getWebContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
    $contentID = $row['contentID'];

    $contentMap[$cleanTitle] = $row['content'];
}
require '../../Function/notification.php';

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort And Events Place - Be Our Partner</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/beOurPartnerNew.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- Link to Bootstrap CSS -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar" style="background-color: white;">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center gap-2" id="profileAndNotif">
            <?php

            $getProfile = $conn->prepare("SELECT userProfile FROM user WHERE userID = ? AND userRole = ?");
            $getProfile->bind_param("ii", $userID, $userRole);
            $getProfile->execute();
            $getProfileResult = $getProfile->get_result();
            if ($getProfileResult->num_rows > 0) {
                $data = $getProfileResult->fetch_assoc();
                $imageData = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>

            <li class="nav-item account-nav">
                <a href="../Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                </a>
            </li>


            <!-- Get notification -->
            <?php

            if ($userRole === 1 || $userRole === 4) {
                $receiver = 'Customer';
            } elseif ($userRole === 2) {
                $receiver = 'Partner';
            }

            $notifications = getNotification($conn, $userID, $receiver);
            $counter = $notifications['count'];
            $notificationsArray = $notifications['messages'];
            $color = $notifications['colors'];
            $notificationIDs = $notifications['ids'];
            ?>


            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= htmlspecialchars($counter) ?>
                    </span>
                    <?php endif; ?>
                </button>
            </div>

        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <?php if ($userRole !== 2): ?>
                    <a class="nav-link" href="dashboard.php"> Home</a>
                    <?php else: ?>
                    <a class="nav-link" href="../BusinessPartner/bpDashboard.php"> Home</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
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
                <?php if ($userRole !== 2): ?>
                <li class="nav-item">
                    <a class="nav-link active" href="beOurPartner.php">Be Our Partner</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookNow.php">Book Now</a>
                </li>
                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">Log Out</a>
                </li>
            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <section class="topSec">
        <div class="topLeft">
            <h6 class="topText">Become Mamyr's Business Partner</h6>

            <h2 class="headerText">Unlock New Opportunities with Mamyr Resort & Events Place as Your Business Partner.
            </h2>
            <h5 class="subtext">Let's work together. Collaborate with us to grow your business and to better serve
                mutual customers.</h5>

            <a href="partnerApplication.php" class="btn btn-primary" id="applyasPartner">Apply as Partner</a>
        </div>

        <div class="topRight">
            <img src="../../Assets/Images/beOurPartnerPhotos/bpIcon.png" alt="BP Icon" class="bpIcon">
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
                    <img src="../../Assets/Images/beOurPartnerPhotos/photog.png" alt="Photography Icon"
                        class="partnerIcon">
                    <h4 class="partnerTitle">Photography/Videography</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../../Assets/Images/beOurPartnerPhotos/sound.png" alt="Sound/Light Icon"
                        class="partnerIcon">
                    <h4 class="partnerTitle">Sound and Lighting</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../../Assets/Images/beOurPartnerPhotos/host.png" alt="Host Icon" class="partnerIcon">
                    <h4 class="partnerTitle">Event Hosting</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../../Assets/Images/beOurPartnerPhotos/photoBooth.png" alt="Photo Booth Icon"
                        class="partnerIcon">
                    <h4 class="partnerTitle">Photo Booth</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../../Assets/Images/beOurPartnerPhotos/perf.png" alt="Performer Icon" class="partnerIcon">
                    <h4 class="partnerTitle">Performer</h4>
                </div>

                <div class="partnerServiceContainer">
                    <img src="../../Assets/Images/beOurPartnerPhotos/foodCart.png" alt="Food Cart Icon"
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
                <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                    alt="Card image cap">
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
                <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                    alt="Card image cap">
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
                <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                    alt="Card image cap">
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
                <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                    alt="Card image cap">
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
                <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                    alt="Card image cap">
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
                <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                    alt="Card image cap">
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
    include 'loader.php'; ?>

    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Notification Ajax -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const badge = document.querySelector('.notification-container .badge');

        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationID = this.dataset.id;

                fetch('../../Function/notificationFunction.php', {
                        method: 'POST',
                        headers: {
                            'Content-type': 'application/x-www-form-urlencoded'
                        },
                        body: 'notificationID=' + encodeURIComponent(notificationID)
                    })
                    .then(response => response.text())
                    .then(data => {

                        this.style.transition = 'background-color 0.3s ease';
                        this.style.backgroundColor = 'white';


                        if (badge) {
                            let currentCount = parseInt(badge.textContent, 10);

                            if (currentCount > 1) {
                                badge.textContent = currentCount - 1;
                            } else {
                                badge.remove();
                            }
                        }
                    });
            });
        });
    });
    </script>






    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->


    <script src="../../Assets/JS/scrollNavbg.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>