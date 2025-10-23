<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);
require_once '../../Function/Helpers/userFunctions.php';
resetExpiredOTPs($conn);
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
        header("Location: .../../../../index.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';
require '../../Function/Partner/sales.php';
require '../../Function/Partner/getBookings.php';

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;
//SQL statement for retrieving data for website content from DB\
$folder = 'landingPage';
$sectionName = 'Landing';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
$defaultImage = "/Assets/Images/no-picture.jpg";

while ($row = $getWebContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
    $contentID = $row['contentID'];
    $contentMap[$cleanTitle] = $row['content'];

    // Fetch images with this contentID
    $getImages = $conn->prepare("SELECT WCImageID, imageData, altText FROM websitecontentimage WHERE contentID = ? ORDER BY imageOrder ASC");
    $getImages->bind_param("i", $contentID);
    $getImages->execute();
    $imageResult = $getImages->get_result();

    $images = [];
    while ($imageRow = $imageResult->fetch_assoc()) {
        $images[] = $imageRow;
    }

    $imageMap[$cleanTitle] = $images;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/BusinessPartner/bpDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Swiper's CSS Link  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <input type="hidden" id="userRole" value="<?= $userRole ?>">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
            <?php
            $getProfile = $conn->prepare("SELECT firstName, userProfile FROM user WHERE userID = ? AND userRole = ?");
            $getProfile->bind_param("ii", $userID, $userRole);
            $getProfile->execute();
            $getProfileResult = $getProfile->get_result();
            if ($getProfileResult->num_rows > 0) {
                $data = $getProfileResult->fetch_assoc();
                $firstName = $data['firstName'];
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

            if ($userRole === 1) {
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

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <?php if ($userRole !== 2): ?>
                        <a class="nav-link" href="dashboard.php"> Home</a>
                    <?php else: ?>
                        <a class="nav-link" href="../BusinessPartner/bpDashboard.php"> Home</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Amenities
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../Customer/amenities.php">Resort Amenities</a></li>
                        <li><a class="dropdown-item" href="../Customer/ratesAndHotelRooms.php">Rates and Hotel Rooms</a>
                        </li>
                        <li><a class="dropdown-item" href="../Customer/events.php">Events</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Customer/blog.php">Blog</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../Customer/about.php">About</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../Customer/bookNow.php">Book Now</a>
                </li>

                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">Log Out</a>
                </li>

            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <?php
    include '../notificationModal.php';
    $getPartnershipID = $conn->prepare('SELECT partnershipID FROM `partnership` WHERE userID = ?');
    $getPartnershipID->bind_param('i', $userID);
    $getPartnershipID->execute();
    $result = $getPartnershipID->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $partnershipID = $data['partnershipID'];
    }
    ?>
    <!-- Get Sales -->
    <?php $totalSales = getSales($conn, $userID); ?>

    <!-- Get number of booking — approved, pending -->
    <?php
    $row = getBookingsCount($conn, $userID);
    ?>

    <main class="main-content" id="main-content">
        <section class="topSec">
            <div class="container">
                <h3 class="welcomeText">Hello there, <?= ucfirst($firstName) ?>! Welcome to Mamyr Resort and Events
                    Place</h3>
                <section class="container topSection">
                    <div class="card statCard customer-card">
                        <div class="card-body">
                            <div class="header">
                                <i class="bi bi-calendar-week"></i>
                                <h6 class="header-text">All Bookings</h6>
                            </div>

                            <div class="data-container customer">
                                <h5 class="card-data bookingNumber"><?= $row['allBookingStatus'] ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="card statCard total-bookings">
                        <div class="card-body">
                            <div class="header">
                                <i class="bi bi-calendar-check"></i>
                                <h6 class="header-text">Approved</h6>
                            </div>

                            <div class="data-container ">
                                <h5 class="card-data approvedNumber"><?= $row['approvedBookings']  ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="card statCard total-sales">
                        <div class="card-body">
                            <div class="header">
                                <i class="bi bi-hourglass-top"></i>
                                <h6 class="header-text">Pending</h6>
                            </div>

                            <div class="data-container">
                                <h5 class="card-data pendingNumber"><?= $row['totalPendingBooking']  ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="card statCard mostUsedSrvice-card">
                        <div class="card-body">
                            <div class="header">
                                <i class="bi bi-tags"></i>
                                <h6 class="header-text">Total Monthly Sales</h6>
                            </div>

                            <div class="data-container">
                                <h5 class="card-data revenueNumber">
                                    <?= ($totalSales !== 0) ? number_format($totalSales, 2) : '₱0.00' ?></h5>
                            </div>
                        </div>
                    </div>
                </section>


                <section class="container secondRow-graph">

                    <div class="card graph-card" id="salesPerformance">
                        <div class="card-body graph-card-body">
                            <div class="graph-header">
                                <i class="bi bi-tags"></i>
                                <h6 class="graph-header-text">Monthly Sales</h6>

                                <div class="filter-btn-container">
                                    <div class="filter-select-wrapper">
                                        <select class="filter-select" name="sales-filter-select"
                                            id="sales-filter-select">
                                            <option selected disabled>Filters</option>
                                            <!-- <option value="month"><?= $monthToday ?></option> -->
                                            <option value="w1">Week 1</option>
                                            <option value="w2">Week 2</option>
                                            <option value="w3">Week 3</option>
                                            <option value="w4">Week 4</option>
                                            <option value="w5">Week 5</option>
                                        </select>
                                        <i class="bi bi-filter"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="sales-chart" id="pieGraph">
                                <!-- <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="salesBar"> -->
                                <canvas id="salesGraph" class="graph"></canvas>
                                <!-- <canvas class="graph" id="salesBar"></canvas> -->
                            </div>

                        </div>
                    </div>

                    <div class="card graph-card">
                        <div class="card-body graph-card-body services-card">
                            <div class="graph-header">
                                <i class="bi bi-bell"></i>
                                <h6 class="graph-header-text">Services</h6>

                                <!-- <div class="filter-btn-container">
                                <div class="filter-select-wrapper">
                                    <select class="filter-select" name="sales-filter-select" id="sales-filter-select">
                                        <option value="month"><?= $monthToday ?></option>
                                        <option value="w1">Week 1</option>
                                        <option value="w2">Week 2</option>
                                        <option value="w3">Week 3</option>
                                        <option value="w4">Week 4</option>
                                        <option value="w5">Week 5</option>
                                    </select>
                                    <i class="bi bi-filter"></i>
                                </div>
                            </div> -->
                            </div>

                            <div class="services-container">
                                <ul>
                                    <?php
                                    // Get Services
                                    $getServicesQuery = $conn->prepare('SELECT ps.`PBName`, ps.`PBPrice` FROM `partnershipservice` ps 
                                WHERE  partnershipID = ?');
                                    $getServicesQuery->bind_param('i', $partnershipID);
                                    if (!$getServicesQuery->execute()) {
                                        error_log('Failed executing services query: ' . $getServicesQuery->error());
                                    }

                                    $result = $getServicesQuery->get_result();


                                    if (!$result->num_rows === 0) {
                                    ?>
                                        <li>No Services</li>
                                    <?php
                                    }

                                    while ($service = $result->fetch_assoc()) {
                                        // echo '<pre>';
                                        // print_r("ID: " . $partnershipID);
                                        // echo '</pre>';
                                    ?>
                                        <li class="serviceNamePrice"><?= htmlspecialchars(ucfirst($service['PBName'])) ?>
                                            &mdash; ₱<?= number_format($service['PBPrice']) ?></li>
                                    <?php
                                    }

                                    ?>
                                </ul>

                            </div>
                            <div class="service-btn-container">
                                <a href="../Account/bpServices.php" class="btn btn-primary service-btn">View All
                                    Services</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <section class="middle-container">
            <div class="embed-responsive embed-responsive-16by9">
                <video id="mamyrVideo" autoplay muted controls class="embed-responsive-item"
                    poster="../../Assets/videos/thumbnail2.jpg">
                    <source src="../../Assets/videos/mamyrVideo3.mp4" type="video/mp4">

                </video>
            </div>
            <div class="videoText-container">
                <?php if ($editMode): ?>
                    <input type="text" class="editable-input videoTitle form-control" data-title="Heading2"
                        value="<?= htmlspecialchars($contentMap['Heading2'] ?? 'Title Not Found') ?>">
                    <textarea cols="20" rows="5" type="text" class="editable-input form-control subtext"
                        data-title="Subheading2"><?= htmlspecialchars($contentMap['Subheading2'] ?? 'Description Not Found') ?></textarea>
                <?php else: ?>
                    <h3 class="videoTitle"><?= htmlspecialchars($contentMap['Heading2'] ?? 'Name Not Found') ?> </h3>
                    <p class="videoDescription indent">
                        <?= htmlspecialchars($contentMap['Subheading2'] ?? 'Description Not Found') ?> </p>
                    <div class="middle-btn-container">
                        <a href="../Customer/bookNow.php" class="btn btn-primary bookNowBtn">Book Now</a>
                        <a href="../amenities.php" class="btn btn-primary viewBtn">View our Amenities</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="bottom-section">

            <div class="bottom-text-container">
                <?php if ($editMode): ?>
                    <input type="text" class="editable-input bottom-header form-control" data-title="BookNow"
                        value="<?= htmlspecialchars($contentMap['BookNow'] ?? 'Title Not Found') ?>">
                    <textarea cols="20" rows="5" type="text" class="editable-input form-control bottom-subtext"
                        data-title="BookNowDesc"><?= htmlspecialchars($contentMap['BookNowDesc'] ?? 'Description Not Found') ?></textarea>
                <?php else: ?>
                    <h3 class="bottom-header"><?= htmlspecialchars($contentMap['BookNow'] ?? 'Title Not Found') ?> </h3>
                    <p class="bottom-subtext indent">
                        <?= htmlspecialchars($contentMap['BookNowDesc'] ?? 'Description Not Found') ?> </p>
                <?php endif; ?>
            </div>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['BookNow'])): ?>
                        <?php foreach ($imageMap['BookNow'] as $index => $img):
                            $imagePath = "../../Assets/Images/landingPage/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img d-block w-100" style="cursor: pointer;" <?php if ($editMode): ?>
                                    data-bs-toggle="modal" data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?? '' ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText'] ?? '') ?>" <?php endif; ?>>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card-img">
                            <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                        </div>
                    <?php endif; ?>

                </div>
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <section class="rating-container">
            <div class="locationText-container">
                <?php if ($editMode): ?>
                    <input type="text" class="editable-input videoTitle form-control" data-title="Reviews"
                        value="<?= htmlspecialchars($contentMap['Reviews'] ?? 'Title Not Found') ?>">
                    <textarea cols="20" rows="5" type="text" class="editable-input form-control videoDescription"
                        data-title="ReviewsDesc"><?= htmlspecialchars($contentMap['ReviewsDesc'] ?? 'Description Not Found') ?></textarea>
                <?php else: ?>
                    <h3 class="videoTitle"><?= htmlspecialchars($contentMap['Reviews'] ?? 'Title Not Found') ?> </h3>
                    <p class="videoDescription indent">
                        <?= htmlspecialchars($contentMap['ReviewsDesc'] ?? 'Description Not Found') ?> </p>
                <?php endif; ?>
            </div>

            <div class="card ratings-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-star"></i>
                        <h6 class="graph-header-text">Ratings</h6>
                    </div>

                    <div class="rating-categories">
                        <!-- Resort -->
                        <div class="rating-row">
                            <div class="rating-label">Resort</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="resort-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="resort-rating-value"></div>
                        </div>

                        <!-- Hotel -->
                        <div class="rating-row">
                            <div class="rating-label">Hotel</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="hotel-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="hotel-rating-value"></div>
                        </div>

                        <!-- Event -->
                        <div class="rating-row">
                            <div class="rating-label">Event</div>
                            <div class="rating-bar">
                                <div class="progress">
                                    <div class="progress-bar" id="event-bar" role="progressbar" aria-valuenow=""
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="rating-value" id="event-rating-value"></div>
                        </div>

                        <!-- Overall Rating (Optional) -->
                        <div class="overall-rating">
                            <div class="overall-rating-label">
                                <h6 class="overall-rating-label">Overall Rating</h6>
                                <h4 class="overall-rating-value" id="overall-rating-value"></h4>
                            </div>
                            <div class="overall-rating-stars" id="star-container">
                                <!-- <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="location-container">
            <div class="locationText-container">
                <?php if ($editMode): ?>
                    <input type="text" class="editable-input videoTitle form-control" data-title="Map"
                        value="<?= htmlspecialchars($contentMap['Map'] ?? 'Title Not Found') ?>">
                    <textarea cols="20" rows="5" type="text" class="editable-input form-control videoDescription"
                        data-title="MapDesc"><?= htmlspecialchars($contentMap['MapDesc'] ?? 'Description Not Found') ?></textarea>
                <?php else: ?>
                    <h3 class="videoTitle"><?= htmlspecialchars($contentMap['Reviews'] ?? 'Title Not Found') ?> </h3>
                    <p class="videoDescription indent">
                        <?= htmlspecialchars($contentMap['MapDesc'] ?? 'Description Not Found') ?> </p>
                <?php endif; ?>
            </div>

            <div id="map"></div>
        </section>
        <?php if ($editMode) {
            include 'Pages/editImageModal.php';
        } else {
            include '../../Pages/Customer/footer.php';
            include '../../Pages/Customer/loader.php';
        }
        ?>
    </main>




    <!-- Monthly Sales Graph -->
    <?php
    $paymentStatusID = 3; //Fully Paid
    $paymentApprovalID = 5; //Done

    $getMonthlySalesQuery = $conn->prepare("SELECT MONTHNAME(b.startDate) AS month,
                    YEAR(b.startDate) AS year,
                    SUM(IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0)) AS monthlyRevenue,
                    ps.partnershipID, ps.partnershipServiceID
                    FROM booking b
                    LEFT JOIN  confirmedbooking cb ON b.bookingID = cb.bookingID
                    LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                    LEFT JOIN custompackageitem cpi ON b.customPackageID = cpi.customPackageID
                    LEFT JOIN service s ON (cpi.serviceID = s.serviceID  OR bs.serviceID = s.serviceID)
                    LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                    LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                    WHERE cb.paymentApprovalStatus = ?
                    AND cb.paymentStatus = ?
                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                    AND DATE(b.endDate) < CURDATE()
                    AND ps.partnershipID = ?
                    AND bpas.approvalStatus = 2
                    GROUP BY 
                        month
                    ORDER BY 
                        month");
    $getMonthlySalesQuery->bind_param("iii", $paymentApprovalID, $paymentStatusID, $partnershipID);
    if (!$getMonthlySalesQuery->execute()) {
        error_log("Failed executing monthly sales in a year. Error: " . $getMonthlySalesQuery->error);
    }
    $months = [];
    $sales = [];
    $year = '';
    $result = $getMonthlySalesQuery->get_result();
    if ($result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            $months[] = $data['month'];
            $sales[] = (float) $data['monthlyRevenue'];
            $year = $data['year'] ?? DATE('Y');
        }
    }
    ?>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Chart Js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            loopedSlides: 3,
            spaceBetween: 30,

            slidesPerView: 3,

            breakpoints: {
                0: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                600: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                }
            },


            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },

            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            }
        });
    </script>




    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const lat = 15.05073200154005;
        const lon = 121.0218658098424;

        const map = L.map('map').setView([lat, lon], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);


        const customIcon = L.icon({
            iconUrl: '../../Assets/Images/MamyrLogo.png',
            iconSize: [100, 25], // Size of the logo 
            iconAnchor: [25, 50], // Anchor point of the icon 
            popupAnchor: [0, -50] // Popup anchor point 
        });


        L.marker([lat, lon], {
                icon: customIcon
            }).addTo(map)
            .bindPopup('Mamyr Resort and Events Place is Located Here!')
            .openPopup();
    </script>

    <script>
        async function getRatings() {
            const response = await fetch('../../Function/Admin/Ajax/getRatings.php');
            const data = await response.json();

            const resortBar = document.getElementById('resort-bar');
            resortBar.style.width = data.resortPercent + '%';
            resortBar.setAttribute('ari-valuenow', data.resortPercent)
            document.getElementById('resort-rating-value').textContent = data.resortRating;

            const hotelBar = document.getElementById('hotel-bar');
            hotelBar.style.width = data.hotelPercent + '%';
            hotelBar.setAttribute('ari-valuenow', data.hotelPercent)
            document.getElementById('hotel-rating-value').textContent = data.hotelRating;

            const eventBar = document.getElementById('event-bar');
            eventBar.style.width = data.eventPercent + '%';
            eventBar.setAttribute('ari-valuenow', data.eventPercent)
            document.getElementById('event-rating-value').textContent = data.eventRating;

            document.getElementById('overall-rating-value').textContent = data.overAllRating;
            const starContainer = document.getElementById('star-container');
            starContainer.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= Math.floor(data.overAllRating)) {
                    starContainer.innerHTML += '<i class="bi bi-star-fill star text-warning"></i>';
                } else if (i - data.overAllRating <= .5 && i - data.overAllRating > 0) {
                    starContainer.innerHTML += '<i class="bi bi-star-half star text-warning"></i>';
                } else {
                    starContainer.innerHTML += '<i class="bi bi-star star text-warning"></i>';
                }
            }
        }
        getRatings();
        setInterval(getRatings, 300000);
    </script>

    <!-- This is shown if no data to display -->
    <script src="../../Assets/JS/ChartNoData.js"></script>

    <!-- Line Chart for sales  -->
    <script>
        const salesGraph = document.getElementById('salesGraph').getContext('2d');
        const labels = <?= json_encode($months) ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: "Monthly Sales Report — <?= !empty($year) ? json_encode($year) : DATE('Y') ?>",
                data: <?= json_encode($sales) ?>,
                fill: false,
                backgroundColor: 'rgb(33, 148, 209, .5)',
                borderColor: 'rgb(33, 148, 209, 1)',
                tension: 0.1
            }]
        };

        const lineSalesChart = new Chart(salesGraph, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            plugins: ['noDataPlugin']
        })
    </script>
</body>

</html>