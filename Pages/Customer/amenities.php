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

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

//SQL statement for retrieving data for website content from DB
$sectionName = 'Amenities';
$getWebContent = $conn->prepare("SELECT * FROM websitecontents WHERE sectionName = ?");
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
    $getImages = $conn->prepare("SELECT WCImageID, imageData, altText FROM websitecontentimages WHERE contentID = ? ORDER BY imageOrder ASC");
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
    <title>Mamyr - Amenities</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/amenities.css">


    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">

        <!-- Account Icon on the Left -->
        <ul class="navbar-nav d-flex flex-row align-items-center gap-2" id="profileAndNotif">
            <?php
            $getProfile = $conn->prepare("SELECT userProfile FROM users WHERE userID = ? AND userRole = ?");
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

            if ($userRole === 1) {
                $receiver = 'Customer';
            } elseif ($userRole === 2) {
                $receiver = 'Partner';
            }

            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE userID = ? AND receiver = ? AND is_read = 0");
            $getNotifications->bind_param("is", $userID, $receiver);
            $getNotifications->execute();
            $getNotificationsResult = $getNotifications->get_result();
            if ($getNotificationsResult->num_rows > 0) {
                $counter = 0;
                $notificationsArray = [];
                $color = [];
                $notificationIDs = [];
                while ($notifications = $getNotificationsResult->fetch_assoc()) {
                    $is_readValue = $notifications['is_read'];
                    $notificationIDs[] = $notifications['notificationID'];
                    if ($is_readValue === 0) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "rgb(247, 213, 176, .5)";
                    } elseif ($is_readValue === 1) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "white";
                    }
                }
            }
            ?>

            <li class="nav-item notification-container" id="notifs">
                <button type="button" class="notifBtn" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= htmlspecialchars($counter) ?>
                    </span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item active" href="#">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookNow.php">BOOK NOW</a>
                </li>

                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">LOG OUT</a>
                </li>

            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <?php if (!empty($notificationsArray)): ?>
                    <ul class="list-group list-group-flush ">
                        <?php foreach ($notificationsArray as $index => $message):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                        <li class="list-group-item mb-2 notification-item"
                            data-id="<?= htmlspecialchars($notificationID) ?>"
                            style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                            <?= htmlspecialchars($message) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="p-3 text-muted">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="amenities">

        <h1 class="title">OUR AMENITIES</h1>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted loop controls class="embed-responsive-item">
                <source src="../../Assets/Videos/mamyrVideo1.mp4" type="video/mp4">
            </video>

        </div>

        <div class="pool">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <!-- <h4 class="amenityTitle">Swimming Pools</h4> -->
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity1"
                    value="<?= htmlspecialchars($contentMap['Amenity1'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity1'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity1Desc indent editable-input form-control"
                    data-title="Amenity1Desc"><?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/poolPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="cottage colored-bg" style="background-color:#f7d5b0;">
            <div class=" amenityTitleContainer">
                <hr class="amenityLine">
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity2"
                    value="<?= htmlspecialchars($contentMap['Amenity2'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity2'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity2Desc indent editable-input form-control"
                    data-title="Amenity2Desc"><?= htmlspecialchars($contentMap['Amenity2Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity2Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/poolPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="videoke">
            <div class=" amenityTitleContainer">
                <hr class="amenityLine">
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity3"
                    value="<?= htmlspecialchars($contentMap['Amenity3'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity3'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity3Desc indent editable-input form-control"
                    data-title="Amenity3Desc"><?= htmlspecialchars($contentMap['Amenity3Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity3Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/poolPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>

        <div class="pavilion colored-bg" style="background-color: #7dcbf2;">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity4"
                    value="<?= htmlspecialchars($contentMap['Amenity4'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity4'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity4Desc indent editable-input form-control"
                    data-title="Amenity4Desc"><?= htmlspecialchars($contentMap['Amenity4Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity4Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/poolPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>

        </div>

        <div class="minipavilion">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity5"
                    value="<?= htmlspecialchars($contentMap['Amenity5'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity5'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity5Desc indent editable-input form-control"
                    data-title="Amenity5Desc"><?= htmlspecialchars($contentMap['Amenity5Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity5Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/poolPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>

        </div>

        <div class="hotel colored-bg" style="background-color:#f7d5b0;">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity6"
                    value="<?= htmlspecialchars($contentMap['Amenity6'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity6'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity6Desc indent editable-input form-control"
                    data-title="Amenity6Desc"><?= htmlspecialchars($contentMap['Amenity6Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity6Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/poolPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/poolPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>

        </div>

        <div class="parking">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <?php if ($editMode): ?>
                <input type="text" class="amenityTitle editable-input form-control" data-title="Amenity7"
                    value="<?= htmlspecialchars($contentMap['Amenity7'] ?? 'No Title found') ?>">
                <?php else: ?>
                <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity7'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                <textarea type="text" rows="5"
                    class="amenityDescription Amenity7Desc indent editable-input form-control"
                    data-title="Amenity7Desc"><?= htmlspecialchars($contentMap['Amenity7Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                <p class="amenityDescription">
                    <?= htmlspecialchars($contentMap['Amenity7Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity7'])): ?>
                <?php foreach ($imageMap['Amenity7'] as $index => $img):
                        $imagePath = "../../Assets/Images/amenities/parkingPics/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                        class=" editable-img" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="amenities/parkingPics" data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="slide">
                    <img src="<? $defaultImage ?>" alt="None Found">
                </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>
    </div>

    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>
    <?php
        $sectionName = 'BusinessInformation';
        $getWebContent = $conn->prepare("SELECT * FROM websitecontents WHERE sectionName = ?");
        $getWebContent->bind_param("s", $sectionName);
        $getWebContent->execute();
        $getWebContentResult = $getWebContent->get_result();
        $businessInfo = [];
        while ($row = $getWebContentResult->fetch_assoc()) {
                $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
                $contentID = $row['contentID'];

                $businessInfo[$cleanTitle] = $row['content'];
        }
        ?>

    <footer class="py-1" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            </a>
            <h3 class="mb-0"><?= htmlspecialchars(strtoupper($businessInfo['FullName']) ?? 'Name Not Found') ?></h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter"><?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?></h4>
                <h4 class="emailAddressTextFooter"><?= htmlspecialchars($businessInfo['Email'] ?? 'None Provided') ?>
                </h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter"><?= htmlspecialchars($businessInfo['Address'] ?? 'None Provided') ?></h4>

            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="<?= htmlspecialchars($businessInfo['FBLink'] ?? 'None Provided') ?>"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="mailto: <?= htmlspecialchars($businessInfo['GmailAdd'] ?? 'None Provided') ?>"><i
                    class='bx bxl-gmail'></i></a>
            <a href="tel:<?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?>">
                <i class='bx bxs-phone'></i>
            </a>
        </div>
    </footer>



    <!-- Bootstrap Link -->
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



    <script>
    var video = document.getElementById("mamyrVideo");

    video.onplay = function() {
        video.muted = false;
    };
    </script>
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
    const navbar = document.getElementById("navbar");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 10) {
            navbar.classList.add("bg-white", "shadow");
        } else {
            navbar.classList.remove("bg-white", "shadow");
        }
    });
    </script>


    <script>
    // JS for slideshow
    function createSlideshow(container) {
        const slides = container.querySelectorAll('.slide');
        const prevBtn = container.querySelector('.prev-btn');
        const nextBtn = container.querySelector('.next-btn');
        let index = 0;

        slides.forEach(slide => {
            slide.style.transform = 'translateX(100%)'; // Start off-screen right
            slide.style.position = 'absolute';
            slide.style.top = '0';
            slide.style.left = '0';
            slide.style.width = '100%';
            slide.style.height = '100%';
        });


        slides[index].classList.add('active');
        slides[index].style.transform = 'translateX(0)';
        slides[index].style.zIndex = '1';

        function showSlide(newIndex, direction) {
            if (newIndex === index) return;

            const currentSlide = slides[index];
            const nextSlide = slides[newIndex];

            nextSlide.classList.add('active');
            nextSlide.style.transition = 'none';
            nextSlide.style.transform = direction === 'next' ? 'translateX(100%)' : 'translateX(-100%)';
            nextSlide.style.zIndex = '2';
            nextSlide.style.opacity = '1';

            void nextSlide.offsetWidth;

            nextSlide.style.transition = 'transform 0.6s ease';
            nextSlide.style.transform = 'translateX(0)';

            currentSlide.style.transition = 'transform 0.6s ease, opacity 0.6s ease';
            currentSlide.style.transform = direction === 'next' ? 'translateX(-100%)' : 'translateX(100%)';
            currentSlide.style.opacity = '0';
            currentSlide.style.zIndex = '1';

            setTimeout(() => {
                currentSlide.classList.remove('active');
                currentSlide.style.transition = '';
                currentSlide.style.transform = 'translateX(100%)';
                currentSlide.style.opacity = '1';
                currentSlide.style.zIndex = '0';

                nextSlide.style.transition = '';
                nextSlide.style.zIndex = '1';

                index = newIndex;
            }, 600);
        }

        nextBtn.addEventListener('click', () => {
            const newIndex = (index + 1) % slides.length;
            showSlide(newIndex, 'next');
        });

        prevBtn.addEventListener('click', () => {
            const newIndex = (index - 1 + slides.length) % slides.length;
            showSlide(newIndex, 'prev');
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        const allSlideshows = document.querySelectorAll('.slideshow-container');
        allSlideshows.forEach(container => {
            createSlideshow(container);
        });
    });
    </script>
</body>

</html>