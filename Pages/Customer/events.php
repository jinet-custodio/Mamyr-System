<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);
require '../../Function/notification.php';


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

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

//for setting image paths in 'include' statements
$baseURL = '../..';

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

//SQL statement for retrieving data for website content from DB
$sectionName = 'Events';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
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

$getEvents = $conn->prepare("SELECT * FROM eventcategory");
$getEvents->execute();

$getEventsResult = $getEvents->get_result();

$defaultImage = "../../Assets/Images/no-picture.jpg";
$eventMap = [];

while ($row = $getEventsResult->fetch_assoc()) {
    $cleanTitle = strtolower(trim($row['categoryName']));
    $imagePath = !empty($row['imagePath']) ? $row['imagePath'] : $defaultImage;

    $eventMap[$cleanTitle] = [
        'categoryID' => $row['categoryID'],
        'categoryName' => $row['categoryName'],
        'eventDescription' => $row['eventDescription'],
        'imagePath' => $imagePath
    ];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Events</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/events.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- Bootstrap Link -->
    <link href="../../Assets/CSS/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top white-text" id="navbar-half">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center gap-2" id="profileAndNotif">
            <?php
            $query = "SELECT userProfile FROM user WHERE userID = '$userID' AND userRole = '$userRole'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
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

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
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
                        <li><a class="dropdown-item active" href="events.php">Events</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">Blog</a>
                </li>
                <?php if ($userRole !== 2): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="partnerApplication.php">Be Our Partner</a>
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

    <div class="titleContainer">
        <h4 class="title"><?= htmlspecialchars($contentMap['EventTitle'] ?? 'No Title found') ?></h4>
        <p class="description"><?= htmlspecialchars($contentMap['EventDesc'] ?? 'No description found') ?></p>
    </div>

    <div class="categories">
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php
                $defaultImage = "../../Assets/Images/no-picture.jpg";

                foreach ($eventMap as $key => $event):
                    $eventName = $event['categoryName'] ?? 'Untitled Event';
                    $eventDesc = $event['eventDescription'] ?? 'No description available.';
                    $folderPath = '../../Assets/Images/EventsPhotos/' .  $event['imagePath'];
                    $imagePath = !empty($event['imagePath']) ? $folderPath : $defaultImage;

                    // Sanitize alt text
                    $altText = htmlspecialchars($eventName);
                ?>
                    <div class="swiper-slide">
                        <div class="card event-card">
                            <img class="card-img-top" src="<?= htmlspecialchars($imagePath) ?>" alt="<?= $altText ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($eventName) ?></h5>
                                <div class="eventDescription">
                                    <p class="eventDesc"><?= htmlspecialchars($eventDesc) ?></p>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    style="margin-top: auto;"
                                    onclick="window.location.href='eventbooking.php?event=<?= $eventName ?>'">
                                    BOOK NOW
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Optional navigation -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>

            <!-- Optional pagination -->
            <div class="swiper-pagination"></div>
        </div>

    </div>

    <div class="venueTitleContainer">
        <h3 class="venueTitle"><?= htmlspecialchars($contentMap['OurEventsTitle'] ?? 'No Title found') ?></h3>
        <p class="venueDescription indent"><?= htmlspecialchars($contentMap['OurEventsDesc'] ?? 'No description found') ?></p>
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
                if (stripos($serviceName, 'Main Function Hall') !== false) {
                    $mainHall = $row;
                } elseif (stripos($serviceName, 'Mini Function Hall') !== false) {
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
            <?php } ?>
        </div>


    </div>

    <div class="miniHall">
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
            <?php } ?>
        </div>


        <div id="carouselMiniHall" class="carousel slide mb-5" data-bs-ride="carousel">
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

    <?php include 'footer.php';
    include 'loader.php'; ?>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

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
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2
                }
            }
        });
    </script>
    <!-- Scroll Nav BG -->
    <script src="../../Assets/JS/scrollNavbg.js"></script>
</body>

</html>