<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../Config/dbcon.php';

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;

//SQL statement for retrieving data for website content from DB
$sectionName = 'About';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
$defaultImage = "../Assets/Images/no-picture.png";
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
    <title>Mamyr - About</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/about.css">
    <link rel="stylesheet" href="../Assets/CSS/navbar.css">
    <!-- <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css"> -->
    <!-- Online link for Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icon library from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>


<body>
    <?php if ($editMode): ?>
        <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
    <?php endif; ?>
    <?php if (!$editMode): ?>
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
                        <a class="nav-link" href="/Pages/blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">Be Our Partner</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./about.php">About</a>
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
    <?php endif; ?>

    <div class="titleContainer">
        <h1 class="title" id="title">ABOUT US</h1>
    </div>

    <div class="aboutTopContainer" id="aboutTopContainer">
        <div class="topPicContainer">
            <?php if (isset($imageMap['AboutMamyr'])): ?>
                <?php foreach ($imageMap['AboutMamyr'] as $index => $img):
                    $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                    $defaultImage = "../Assets/Images/no-picture.png";
                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($img['altText']) ?>" class="editable-img resortPic"
                        style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editImageModal"
                        data-wcimageid="<?= $img['WCImageID'] ?>" data-folder="aboutImages"
                        data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- <img src="../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture" class="resortPic"> -->


        <div class="topTextContainer">
            <?php if ($editMode): ?>
                <textarea type="text" class="hook editable-input form-control" style="font-size: 2vw;font-weight:700;"
                    data-title="Header"><?= htmlspecialchars($contentMap['Header'] ?? '') ?></textarea>
            <?php else: ?>
                <h3 class="hook"><?= htmlspecialchars($contentMap['Header'] ?? 'Header Not Found') ?> </h3>
            <?php endif; ?>

            <?php if ($editMode): ?>
                <textarea type="text" cols="65" rows="8" class="aboutDescription indent editable-input form-control"
                    data-title="AboutMamyr"><?= htmlspecialchars($contentMap['AboutMamyr'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="aboutDescription indent">
                    <?= htmlspecialchars($contentMap['AboutMamyr'] ?? 'No description found') ?></p>
            <?php endif; ?>


            <a href="#backArrowContainer"><button class="btn btn-primary" id="readMoreBtn">Read More</button></a>
        </div>
    </div>

    <div class="ourServicesContainer" id="ourServicesContainer">
        <div class="servicesTitleContainer">
            <h3 class="servicesTitle">Our Services</h3>
            <?php if ($editMode): ?>
                <textarea type="text" cols="65" class="servicesDescription indent editable-input form-control"
                    data-title="AboutMamyr"><?= htmlspecialchars($contentMap['ServicesDesc'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="servicesDescription indent">
                    <?= htmlspecialchars($contentMap['ServicesDesc'] ?? 'No description found') ?></p>
            <?php endif; ?>

        </div>

        <div class="servicesIconContainer">

            <div class="resortContainer">
                <?php if (isset($imageMap['Service1Desc'])): ?>
                    <?php foreach ($imageMap['Service1Desc'] as $index => $img):
                        $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img resortIcon mx-auto" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>" data-folder="aboutImages"
                            data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">

                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<? $defaultImage ?>" alt="None Found">
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <input type="text" class="services resortIconTitle editable-input form-control" data-title="Service1"
                        value="<?= htmlspecialchars($contentMap['Service1'] ?? 'No description found') ?>">
                <?php else: ?>
                    <h4 class="resortIconTitle"><?= htmlspecialchars($contentMap['Service1'] ?? 'No description found') ?>
                    </h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="resortIconDescription Service1Desc indent editable-input form-control"
                        data-title="Service1Desc"><?= htmlspecialchars($contentMap['Service1Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="resortIconDescription">
                        <?= htmlspecialchars($contentMap['Service1Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="eventContainer">
                <?php if (isset($imageMap['Service2Desc'])): ?>
                    <?php foreach ($imageMap['Service2Desc'] as $index => $img):
                        $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                        $defaultImage = "../Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img eventIcon mx-auto" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>" data-folder="aboutImages"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<? $defaultImage ?>" alt="None Found">
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <input type="text" class="services eventIconTitle editable-input form-control" data-title="Service2"
                        value="<?= htmlspecialchars($contentMap['Service2'] ?? 'No description found') ?>">
                <?php else: ?>
                    <h4 class="eventIconTitle"><?= htmlspecialchars($contentMap['Service2'] ?? 'No description found') ?>
                    </h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="eventIconDescription Service2Desc indent editable-input form-control"
                        data-title="Service2Desc"><?= htmlspecialchars($contentMap['Service2Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="eventIconDescription">
                        <?= htmlspecialchars($contentMap['Service2Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="hotelContainer">
                <?php if (isset($imageMap['Service3Desc'])): ?>
                    <?php foreach ($imageMap['Service3Desc'] as $index => $img):
                        $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img hotelIcon mx-auto" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>" data-folder="aboutImages"
                            data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">

                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <input type="text" class="services hotelIconTitle editable-input form-control" data-title="Service3"
                        value="<?= htmlspecialchars($contentMap['Service3'] ?? 'No description found') ?>">
                <?php else: ?>
                    <h4 class="hotelIconTitle"><?= htmlspecialchars($contentMap['Service3'] ?? 'No description found') ?>
                    </h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="hotelIconDescription Service3Desc indent editable-input form-control"
                        data-title="Service3Desc"><?= htmlspecialchars($contentMap['Service3Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="hotelIconDescription">
                        <?= htmlspecialchars($contentMap['Service3Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="videoContainer" id="videoContainer" style="margin-bottom: 3vw;">
        <div class="videoTextContainer">
            <?php
            $sectionName = 'BusinessInformation';
            $getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
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
            <h3 class="videoTitle">Explore <?= htmlspecialchars($businessInfo['FullName'] ?? 'No description found') ?>
            </h3>
            <?php if ($editMode): ?>
                <textarea type="text" rows="13" cols="75"
                    class="videoDescription white-text indent editable-input form-control"
                    data-title="Explore"><?= htmlspecialchars($contentMap['Explore'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="videoDescription indent"><?= htmlspecialchars($contentMap['Explore'] ?? 'No description found') ?>
                </p>
            <?php endif; ?>

        </div>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted controls class="embed-responsive-item"
                poster="../Assets/videos/thumbnail2.jpg">
                <source src="../../Assets/videos/mamyrVideo2.mp4" type="video/mp4">

            </video>
        </div>
    </div>


    <div class="backArrowContainer" id="backArrowContainer" style="display: none;">
        <?php if ($editMode): ?>
            <a href="about.php?edit=true">
            <?php else: ?>
                <a href="about.php?">
                <?php endif; ?>
                <i class="fa-solid fa-arrow-left" style="color: #ededed;"></i>
                </a>
    </div>

    <div class="mamyrHistoryContainer" id="mamyrHistoryContainer">
        <div class="firstParagraphContainer">
            <div class="firstParagraphtextContainer">
                <?php if ($editMode): ?>
                    <textarea type="text" rows="7" cols="75" class="firstParagraph indent editable-input form-control"
                        data-title="HistoryParagraph1"><?= htmlspecialchars($contentMap['HistoryParagraph1'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="firstParagraph indent">
                        <?= htmlspecialchars($contentMap['HistoryParagraph1'] ?? 'No description found') ?></p>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="8" cols="75" class="secondParagraph indent editable-input form-control"
                        data-title="HistoryParagraph2"><?= htmlspecialchars($contentMap['HistoryParagraph2'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="secondParagraph indent">
                        <?= htmlspecialchars($contentMap['HistoryParagraph2'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>


            <div class="firstImageContainer">
                <?php if (isset($imageMap['HistoryParagraph2'])): ?>
                    <?php foreach ($imageMap['HistoryParagraph2'] as $index => $img):
                        $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                        $defaultImage = "../Assets/Images/no-picture.jpg";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img firstParagraphPhoto" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>" data-folder="aboutImages"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="thirdParagraphContainer">
            <div class="thirdImageContainer">
                <?php if (isset($imageMap['HistoryParagraph4'])): ?>
                    <?php foreach ($imageMap['HistoryParagraph4'] as $index => $img):
                        $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                        $defaultImage = "../Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img thirdParagraphPhoto" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#editImageModal" data-wcimageid="<?= $img['WCImageID'] ?>" data-folder="aboutImages"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($defaultImage) ?>">
                <?php endif; ?>
            </div>

            <div class="thirdParagraphtextContainer">
                <?php if ($editMode): ?>
                    <textarea type="text" rows="10" cols="75" class="thirdParagraph indent editable-input form-control"
                        data-title="HistoryParagraph3"> <?= htmlspecialchars($contentMap['HistoryParagraph3'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="thirdParagraph indent">
                        <?= htmlspecialchars($contentMap['HistoryParagraph3'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="fourthParagraphContainer">
            <?php if ($editMode): ?>
                <textarea type="text" rows="5" cols="75" class="fourthParagraph indent editable-input form-control"
                    data-title="HistoryParagraph4"><?= htmlspecialchars($contentMap['HistoryParagraph4'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="fourthParagraph indent">
                    <?= htmlspecialchars($contentMap['HistoryParagraph4'] ?? 'No description found') ?></p>
            <?php endif; ?>

        </div>
    </div>
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>
    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (!$editMode) {
        include 'footer.php';
        include 'loader.php';
    } else {
        include 'editImageModal.php';
    }
    ?>

    <!-- AJAX for editing website content -->
    <?php if ($editMode): ?>
        <script>
            const editableImgs = document.querySelectorAll('.editable-img')
            document.addEventListener("DOMContentLoaded", function() {
                editableImgs.forEach(editable => {
                    editable.style.border = '2px solid red';
                })
            });
        </script>
        <script type="module">
            import {
                initWebsiteEditor
            } from '../Assets/JS/EditWebsite/editWebsiteContent.js';

            initWebsiteEditor('About', '../Function/Admin/editWebsite/editWebsiteContent.php');
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mamyrHistoryContainer = document.getElementById("mamyrHistoryContainer")
            const backArrowContainer = document.getElementById("backArrowContainer")
            const aboutTopContainer = document.getElementById("aboutTopContainer")
            const readMoreBtn = document.getElementById("readMoreBtn")
            const ourServicesContainer = document.getElementById("ourServicesContainer")
            const videoContainer = document.getElementById("videoContainer")

            mamyrHistoryContainer.style.display = "none"

            readMoreBtn.addEventListener('click', function() {
                if (mamyrHistoryContainer.style.display == "none" && backArrowContainer.style.display == "none") {

                    mamyrHistoryContainer.style.display = "block";
                    backArrowContainer.style.display = "block"
                    aboutTopContainer.style.display = "none"
                    ourServicesContainer.style.display = "none"
                    videoContainer.style.display = "none"
                    document.getElementById("title").innerHTML = "ABOUT US - HISTORY"

                } else {
                    mamyrHistoryContainer.style.display = "block"
                    backArrowContainer.style.display = "block"
                }
            })
        })
    </script>

    <!-- Sweetalert JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
</body>

</html>