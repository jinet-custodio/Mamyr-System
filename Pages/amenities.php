<?php

error_reporting(E_ALL);
session_start();
ini_set('display_errors', 1);
require '../Config/dbcon.php';


//for edit website, this will enable edit mode from the iframe
$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;

//SQL statement for retrieving data for website content from DB
$sectionName = 'Amenities';
$getWebContent = $conn->prepare("SELECT * FROM websitecontent WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
$defaultImage = "../Assets/Images/no-picture.jpg";
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
    <title>Mamyr - Amenities</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/amenities.css">
    <link rel="stylesheet" href="../Assets/CSS/navbar.css">
    <!-- Link to Bootsrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <?php if ($editMode): ?>
        <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
    <?php endif; ?>
    <?php if (!$editMode): ?>
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar">

            <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
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
                            data-bs-toggle="dropdown" aria-expanded="false" onclick="event.preventDefault()">
                            AMENITIES
                        </a>

                        <ul class="dropdown-menu  dropdown-menu-start" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item active" href="amenities.php">RESORT AMENITIES</a></li>
                            <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                            <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">BLOG</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="beOurPartnerNew.php" id="bopNav">BE OUR PARTNER</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">ABOUT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">BOOK NOW</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php" id="logOutBtn">Sign Up</a>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <div class="amenities" id="amenities">
        <h1 class="title">OUR AMENITIES</h1>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted loop controls class="embed-responsive-item">
                <source src="../Assets/videos/mamyrVideo1.mp4" type="video/mp4">
            </video>

        </div>

        <div class="pool">
            <div class="amenityTitleContainer">
                <hr class="amenityLine">
                <!-- <h4 class="amenityTitle">Swimming Pools</h4> -->
                <?php if ($editMode): ?>
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity1" value="<?= htmlspecialchars($contentMap['Amenity1'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity1'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity1Desc indent editable-input form-control text-center"
                        data-title="Amenity1Desc"><?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity1'])): ?>
                    <?php foreach ($imageMap['Amenity1'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/poolPics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
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
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity2" value="<?= htmlspecialchars($contentMap['Amenity2'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity2'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity2Desc indent editable-input form-control  text-center"
                        data-title="Amenity2Desc"><?= htmlspecialchars($contentMap['Amenity2Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity2Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity2'])): ?>
                    <?php foreach ($imageMap['Amenity2'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/cottagePics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
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
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity3" value="<?= htmlspecialchars($contentMap['Amenity3'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity3'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity3Desc indent editable-input form-control text-center"
                        data-title="Amenity3Desc"><?= htmlspecialchars($contentMap['Amenity3Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity3Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity3'])): ?>
                    <?php foreach ($imageMap['Amenity3'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/videokePics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
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
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity4" value="<?= htmlspecialchars($contentMap['Amenity4'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity4'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity4Desc indent editable-input form-control text-center"
                        data-title="Amenity4Desc"><?= htmlspecialchars($contentMap['Amenity4Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity4Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity4'])): ?>
                    <?php foreach ($imageMap['Amenity4'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/pavilionPics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
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
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity5" value="<?= htmlspecialchars($contentMap['Amenity5'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity5'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity5Desc indent editable-input form-control text-center"
                        data-title="Amenity5Desc"><?= htmlspecialchars($contentMap['Amenity5Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity5Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity5'])): ?>
                    <?php foreach ($imageMap['Amenity5'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/miniPavPics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
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
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity6" value="<?= htmlspecialchars($contentMap['Amenity6'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity6'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity6Desc indent editable-input form-control text-center"
                        data-title="Amenity6Desc"><?= htmlspecialchars($contentMap['Amenity6Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity6Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity6'])): ?>
                    <?php foreach ($imageMap['Amenity6'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/hotelPics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
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
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity7" value="<?= htmlspecialchars($contentMap['Amenity7'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity7'] ?? 'No title found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5"
                        class="amenityDescription Amenity7Desc indent editable-input form-control text-center"
                        data-title="Amenity7Desc"><?= htmlspecialchars($contentMap['Amenity7Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity7Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="slideshow-container">
                <?php if (isset($imageMap['Amenity7'])): ?>
                    <?php foreach ($imageMap['Amenity7'] as $index => $img):
                        $imagePath = "../Assets/Images/amenities/parkingPics/" . $img['imageData'];
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
                        <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                    </div>
                <?php endif; ?>
                <button class="btn slide-btn btn-primary prev-btn">&#10094;</button>
                <button class="btn slide-btn btn-primary next-btn">&#10095;</button>
            </div>
        </div>
    </div>
    <!-- Modal for editing images and alt texts in edit mode -->
    <?php if ($editMode): ?>
        <!-- Edit Image Modal -->
        <div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editImageModalLabel">Edit Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="modalImagePreview" src="" alt="" class="img-thumbnail mb-3">

                        <input type="file" id="modalImageUpload" class="form-control mb-2">

                        <input type="text" id="modalAltText" class="form-control mb-3" placeholder="Alt text">

                        <!-- Changed label to "Choose" -->
                        <button id="chooseImageBtn" class="btn btn-success me-2" data-bs-dismiss="modal">Choose This
                            Image</button>
                        <button id="deleteImageBtn" class="btn btn-danger">Delete Image</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let activeImageElement = null;
                let activeWCImageID = null;

                // On image click - open modal and load current image/alt
                document.querySelectorAll('.editable-img').forEach(img => {
                    img.addEventListener('click', function() {
                        activeImageElement = this;
                        activeWCImageID = this.dataset.wcimageid;

                        const currentSrc = this.src;
                        const currentAlt = this.alt;

                        document.getElementById('modalImagePreview').src = currentSrc;
                        document.getElementById('modalAltText').value = currentAlt;
                        activeImageElement.setAttribute('data-folder', this.dataset.folder || '');
                        document.getElementById('modalImageUpload').value = '';
                    });
                });

                // When user clicks "Choose"
                document.getElementById('chooseImageBtn').addEventListener('click', () => {
                    if (!activeImageElement) return;

                    const newAlt = document.getElementById('modalAltText').value;
                    const newFile = document.getElementById('modalImageUpload').files[0];

                    // Save alt text immediately to the image's alt and data attribute
                    activeImageElement.alt = newAlt;
                    activeImageElement.setAttribute('data-alttext', newAlt);

                    // Handle local image preview before uploading
                    if (newFile) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            activeImageElement.src = e.target.result;

                            activeImageElement.setAttribute('data-tempfile', newFile.name);
                            activeImageElement.fileObject =
                                newFile;
                        };
                        reader.readAsDataURL(newFile);
                    }
                });
            });
        </script>

    <?php endif; ?>

    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>
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

    <?php if (!$editMode): ?>
        <?php include 'footer.php'; ?>
    <?php endif; ?>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var video = document.getElementById("mamyrVideo");

        video.onplay = function() {
            video.muted = false;
        };
    </script>
    <!-- Bootstrap JS -->
    <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>


    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderOverlay = document.getElementById('loaderOverlay');
            const currentPath = window.location.pathname.replace(/\/+$/, '').toLowerCase(); // Normalize

            const navbarLinks = document.querySelectorAll('.navbar a');

            navbarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');

                    if (href && !href.startsWith('#')) {
                        // Create a temporary anchor to parse the href
                        const tempAnchor = document.createElement('a');
                        tempAnchor.href = href;
                        const targetPath = tempAnchor.pathname.replace(/\/+$/, '').toLowerCase();

                        // If the target is different from the current path, show loader
                        if (targetPath !== currentPath) {
                            loaderOverlay.style.display = 'flex';
                        }
                    }
                });
            });
        });

        function hideLoader() {
            const overlay = document.getElementById('loaderOverlay');
            if (overlay) overlay.style.display = 'none';
        }

        // Hide loader on normal load
        window.addEventListener('load', hideLoader);

        // Hide loader on back/forward navigation (from browser cache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });
    </script>

    <!-- AJAX for editing website content -->
    <?php if ($editMode): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const saveBtn = document.getElementById('saveChangesBtn');
                const amenities = document.getElementById('amenities');
                document.getElementById('mamyrVideo').style.height = 'auto';
                document.body.style.display = 'block';
                amenities.style.marginTop = '0';

                saveBtn?.addEventListener('click', () => {
                    saveTextContent();
                    saveEditableImages();
                });

                function saveTextContent() {
                    const inputs = document.querySelectorAll('.editable-input');
                    const data = {
                        sectionName: 'Amenities'
                    };

                    inputs.forEach(input => {
                        const title = input.getAttribute('data-title');
                        const value = input.value;
                        data[title] = value;
                    });

                    fetch('../Function/Admin/editWebsite/editWebsiteContent.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(res => res.text())
                        .then(text => {
                            if (!text) throw new Error('Empty response');
                            return JSON.parse(text);
                        })
                        .then(response => {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Content Updated!',
                                    text: 'Text content has been successfully updated.',
                                    timer: 2000, // Optional: auto-close after 2 seconds
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Update Failed',
                                    text: 'Failed to update text content: ' + response.message,
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Error saving content:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'An error occurred!',
                                text: 'Something went wrong while saving the content.',
                            });
                        });
                }

                function saveEditableImages() {
                    const editableImages = document.querySelectorAll('.editable-img');

                    editableImages.forEach(img => {
                        const wcImageID = img.dataset.wcimageid;
                        const altText = img.dataset.alttext;
                        const folder = img.dataset.folder || '';
                        const file = img.fileObject || null;

                        if (!wcImageID || (!file && !altText)) return;

                        const formData = new FormData();
                        formData.append('wcImageID', wcImageID);
                        formData.append('altText', altText);
                        formData.append('folder', folder);

                        if (file) {
                            formData.append('image', file);
                        }

                        fetch('../Function/Admin/editWebsite/editWebsiteContent.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(response => {
                                console.log("Full Response:", response);
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Image Updated!',
                                        text: `Image ${altText} has been updated`,
                                        timer: 2000, // Optional: auto-close after 2 seconds
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: `Update Failed for Image ${wcImageID}`,
                                        text: `Failed to update image ${wcImageID}: ` + response
                                            .message,
                                    });
                                }
                            })
                            .catch(err => {
                                console.error(`Image update failed for ${wcImageID}:`, err);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'An error occurred!',
                                    text: `Something went wrong while updating the image ${wcImageID}.`,
                                });
                            });
                    });
                }
            });
        </script>
    <?php endif; ?>

    <script>
        // JS for slideshow
        function createSlideshow(container) {
            const slides = container.querySelectorAll('.slide');
            const prevBtn = container.querySelector('.prev-btn');
            const nextBtn = container.querySelector('.next-btn');
            if (slides.length === 0) {
                console.warn('No slides found in container:', container);
                return;
            }
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

    <script src="../Assets/JS/scrollNavbg.js"></script>

</body>

</html>