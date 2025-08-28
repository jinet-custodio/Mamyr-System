<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../Config/dbcon.php';


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
$defaultImage = "../Assets/Images/no-picture.jpg";
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
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/amenities.css">
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
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item active" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <div class="amenities">
        <h1 class="title">OUR AMENITIES</h1>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted loop controls class="embed-responsive-item">
                <source src="../Assets/Videos/mamyrVideo1.mp4" type="video/mp4">
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
    <?php if (!$editMode): ?>
    <footer class="py-1" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
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
    <?php endif; ?>

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


    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>