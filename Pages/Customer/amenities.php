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

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

//SQL statement for retrieving data for website content from DB
$sectionName = 'Amenities';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
$defaultImage = "../../Assets/Images/no-picture.jpg";
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
require '../../Function/notification.php';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Amenities</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/amenities.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar">

            <!-- Account Icon on the Left -->
            <ul class="navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
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
                        <a class="nav-link" href="beOurPartner.php">Be Our Partner</a>
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

        <main>

            <div class="amenities">

                <h1 class="title">OUR AMENITIES</h1>

                <div class="embed-responsive embed-responsive-16by9">
                    <video id="mamyrVideo" autoplay muted loop controls class="embed-responsive-item">
                        <source src="../../Assets/videos/mamyrVideo1.mp4" type="video/mp4">
                    </video>

                </div>
                <div class="pool">
                    <div class="amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity1'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></p>
                    </div>

                    <div class="swiper mySwiper swiper-amenity1">
                        <div class="swiper-wrapper">
                            <?php if (isset($imageMap['Amenity1'])): ?>
                            <?php foreach ($imageMap['Amenity1'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-1"></div>
                        <div class="swiper-button-prev swiper-button-prev-1"></div>
                    </div>
                </div>
                <div class="cottage colored-bg">
                    <div class=" amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity2'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity2Desc'] ?? 'No description found') ?></p>
                    </div>
                    <div class="swiper mySwiper swiper-amenity2">
                        <div class="swiper-wrapper">
                            <?php if (isset($imageMap['Amenity2'])): ?>
                            <?php foreach ($imageMap['Amenity2'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/cottagePics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-2"></div>
                        <div class="swiper-button-prev swiper-button-prev-2"></div>
                    </div>
                </div>

                <div class="videoke">
                    <div class=" amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity3'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity3Desc'] ?? 'No description found') ?></p>
                    </div>

                    <div class="swiper mySwiper swiper-amenity3">
                        <div class="swiper-wrapper" id="videokeSwiper">
                            <?php if (isset($imageMap['Amenity3'])): ?>
                            <?php foreach ($imageMap['Amenity3'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/videokePics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-3"></div>
                        <div class="swiper-button-prev swiper-button-prev-3"></div>
                    </div>
                </div>

                <div class="pavilion colored-bg" style="background-color: #7dcbf2;">
                    <div class="amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity4'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity4Desc'] ?? 'No description found') ?></p>
                    </div>

                    <div class="swiper mySwiper swiper-amenity4">
                        <div class="swiper-wrapper">
                            <?php if (isset($imageMap['Amenity4'])): ?>
                            <?php foreach ($imageMap['Amenity4'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/pavilionPics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-4"></div>
                        <div class="swiper-button-prev swiper-button-prev-4"></div>
                    </div>

                </div>

                <div class="minipavilion">
                    <div class="amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity5'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity5Desc'] ?? 'No description found') ?></p>
                    </div>
                    <div class="swiper mySwiper swiper-amenity5">
                        <div class="swiper-wrapper">
                            <?php if (isset($imageMap['Amenity5'])): ?>
                            <?php foreach ($imageMap['Amenity5'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/miniPavPics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-5"></div>
                        <div class="swiper-button-prev swiper-button-prev-5"></div>
                    </div>
                </div>

                <div class="hotel colored-bg">
                    <div class="amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity6'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity6Desc'] ?? 'No description found') ?></p>
                    </div>
                    <div class="swiper mySwiper swiper-amenity6">
                        <div class="swiper-wrapper">
                            <?php if (isset($imageMap['Amenity6'])): ?>
                            <?php foreach ($imageMap['Amenity6'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/hotelPics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-6"></div>
                        <div class="swiper-button-prev swiper-button-prev-6"></div>
                    </div>

                </div>

                <div class="parking">
                    <div class="amenityTitleContainer">
                        <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity7'] ?? 'No title found') ?>
                        </h4>
                        <p class="amenityDescription">
                            <?= htmlspecialchars($contentMap['Amenity7Desc'] ?? 'No description found') ?></p>
                    </div>

                    <div class="swiper mySwiper swiper-amenity7">
                        <div class="swiper-wrapper">
                            <?php if (isset($imageMap['Amenity7'])): ?>
                            <?php foreach ($imageMap['Amenity7'] as $index => $img):
                                    $imagePath = "../../Assets/Images/amenities/parkingPics/" . $img['imageData'];
                                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                                ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img"
                                    style="cursor: pointer;">
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next swiper-button-next-7"></div>
                        <div class="swiper-button-prev swiper-button-prev-7"></div>
                    </div>
                </div>
        </main>
        <?php include 'footer.php';
        include 'loader.php'; ?>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js">
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



    <script>
    var video = document.getElementById("mamyrVideo");

    video.onplay = function() {
        video.muted = false;
    };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script src="../../Assets/JS/scrollNavbg.js"></script>
    <script>
    const swiperConfigs = [{
            selector: '.swiper-amenity1',
            next: '.swiper-button-next-1',
            prev: '.swiper-button-prev-1'
        },
        {
            selector: '.swiper-amenity2',
            next: '.swiper-button-next-2',
            prev: '.swiper-button-prev-2'
        },
        {
            selector: '.swiper-amenity3',
            next: '.swiper-button-next-3',
            prev: '.swiper-button-prev-3'
        }, {
            selector: '.swiper-amenity4',
            next: '.swiper-button-next-4',
            prev: '.swiper-button-prev-4'
        },
        {
            selector: '.swiper-amenity5',
            next: '.swiper-button-next-5',
            prev: '.swiper-button-prev-5'
        },
        {
            selector: '.swiper-amenity6',
            next: '.swiper-button-next-6',
            prev: '.swiper-button-prev-6'
        },
        {
            selector: '.swiper-amenity7',
            next: '.swiper-button-next-7',
            prev: '.swiper-button-prev-7'
        }

    ];

    swiperConfigs.forEach(config => {
        const swiperElement = document.querySelector(config.selector);
        if (!swiperElement) {
            console.warn(`Swiper element not found: ${config.selector}`);
            return;
        }

        const slideCount = parseInt(swiperElement.dataset.slidesCount || "0", 10);
        const enableLoop = slideCount >= 3;

        new Swiper(config.selector, {
            slidesPerView: 1,
            spaceBetween: 10,
            loop: false,
            loopedSlides: 3,
            navigation: {
                nextEl: config.next,
                prevEl: config.prev,
            },
            grabCursor: true,
            keyboard: {
                enabled: true
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                    slidesPerGroup: 1
                },
                768: {
                    slidesPerView: 2,
                    slidesPerGroup: 2
                },
                992: {
                    slidesPerView: 3,
                    slidesPerGroup: 3
                }
            }
        });
    });
    </script>
</body>

</html>