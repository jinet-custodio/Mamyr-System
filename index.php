<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'Config/dbcon.php';

require_once 'Function/Helpers/userFunctions.php';
resetExpiredOTPs($conn);
addToAdminTable($conn);

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
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="Assets/CSS/landingPage.css">
    <link rel="stylesheet" href="Assets/CSS/navbar.css">
    <!-- online stylesheet link for bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Swiper's CSS Link  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

</head>

<body>
    <?php if (!$editMode): ?>
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
            <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"> Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Amenities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="Pages/amenities.php">Resort Amenities</a></li>
                            <li><a class="dropdown-item" href="Pages/ratesAndHotelRooms.php">Rates and Hotel Rooms</a></li>
                            <li><a class="dropdown-item" href="Pages/events.php">Events</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/beOurPartnerNew.php" id="bopNav">Be Our Partner</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/register.php">Book Now</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="signUpBtn" href="Pages/register.php">Sign Up</a>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>
    <?php if ($editMode): ?>
        <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
    <?php endif; ?>
    <section class="topSec">
        <div class="topLeft">
            <?php if ($editMode): ?>
                <input type="text" class="editable-input topText form-control" data-title="Welcome"
                    value="<?= htmlspecialchars($contentMap['Welcome'] ?? 'Title Not Found') ?>">
                <textarea cols="20" rows="2" type="text" class="editable-input form-control headerText"
                    data-title="Heading"><?= htmlspecialchars($contentMap['Heading'] ?? 'Heading Not Found') ?></textarea>
                <textarea cols="20" rows="3" type="text" class="editable-input form-control subtext"
                    data-title="Subheading"><?= htmlspecialchars($contentMap['Subheading'] ?? 'Description Not Found') ?></textarea>
            <?php else: ?>
                <h6 class="topText"><?= htmlspecialchars($contentMap['Welcome'] ?? 'Name Not Found') ?> </h6>
                <h2 class="headerText"> <?= htmlspecialchars($contentMap['Heading'] ?? 'Heading Not Found') ?> </h2>
                <h5 class="subtext"><?= htmlspecialchars($contentMap['Subheading'] ?? 'Description Not Found') ?> </h5>
                <a href="pages/register.php" class="btn btn-primary" id="topBookNow-btn">Book Now</a>
            <?php endif; ?>

        </div>

        <div class="topRight">
            <div class="carousel-container">
                <div class="card-stack <?php if ($editMode): ?>editable-imgs<?php endif; ?>">
                    <?php if (isset($imageMap['Heading'])): ?>
                        <?php foreach ($imageMap['Heading'] as $index => $img):
                            $imagePath = "Assets/Images/landingPage/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                class="editable-img card-img" style="cursor: pointer;"
                                <?php if ($editMode): ?>
                                data-bs-toggle="modal"
                                data-bs-target="#editImageModal"
                                data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                data-folder="<?= $folder ?? '' ?>"
                                data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                data-alttext="<?= htmlspecialchars($img['altText'] ?? '') ?>"
                                <?php endif; ?>>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card-img">
                            <img src="<?= htmlspecialchars($defaultImage) ?>" class="default" alt="None Found">
                        </div>
                    <?php endif; ?>
                </div>

                <button id="prevBtn" class="carousel-btn prev">‹</button>
                <button id="nextBtn" class="carousel-btn next">›</button>
            </div>

        </div>
    </section>

    <section class="middle-container">
        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted controls class="embed-responsive-item"
                poster="Assets/Videos/thumbnail2.jpg">
                <source src="Assets/Videos/mamyrVideo3.mp4" type="video/mp4">

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
                <p class="videoDescription indent"> <?= htmlspecialchars($contentMap['Subheading2'] ?? 'Description Not Found') ?> </p>
                <div class="middle-btn-container">
                    <a href="Pages/amenities.php" class="btn btn-primary">View our Amenities</a>
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
                <p class="bottom-subtext indent"> <?= htmlspecialchars($contentMap['BookNowDesc'] ?? 'Description Not Found') ?> </p>
            <?php endif; ?>
        </div>

        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php if (isset($imageMap['BookNow'])): ?>
                    <?php foreach ($imageMap['BookNow'] as $index => $img):
                        $imagePath = "Assets/Images/landingPage/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                    ?>
                        <div class="swiper-slide">
                            <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                class="editable-img d-block w-100" style="cursor: pointer;"
                                <?php if ($editMode): ?>
                                data-bs-toggle="modal"
                                data-bs-target="#editImageModal"
                                data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                data-folder="<?= $folder ?? '' ?>"
                                data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                data-alttext="<?= htmlspecialchars($img['altText'] ?? '') ?>"
                                <?php endif; ?>>
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
                <p class="videoDescription indent"> <?= htmlspecialchars($contentMap['ReviewsDesc'] ?? 'Description Not Found') ?> </p>
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
                <p class="videoDescription indent"> <?= htmlspecialchars($contentMap['MapDesc'] ?? 'Description Not Found') ?> </p>
            <?php endif; ?>
        </div>

        <div id="map"></div>
    </section>
    <?php if ($editMode) {
        include 'Pages/editImageModal.php';
    } else {
        include 'Pages/Customer/footer.php';
        include './Pages/loader.php';
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>
    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            } from '/Assets/JS/EditWebsite/editWebsiteContent.js';

            initWebsiteEditor('Landing', 'Function/Admin/editWebsite/editWebsiteContent.php');
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const images = document.querySelectorAll(".card-img");
            const total = images.length;
            let current = 0;

            function updateStack() {
                images.forEach(img => img.className = "card-img"); // reset
                const prev = (current - 1 + total) % total;
                const next = (current + 1) % total;

                images[current].classList.add("active");
                images[prev].classList.add("behind-left");
                images[next].classList.add("behind-right");
            }

            document.getElementById("prevBtn").addEventListener("click", () => {
                current = (current - 1 + total) % total;
                updateStack();
            });

            document.getElementById("nextBtn").addEventListener("click", () => {
                current = (current + 1) % total;
                updateStack();
            });

            updateStack();
        });
    </script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 3,
            spaceBetween: 30,
            loop: true,
            loopedSlides: 3,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
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
            iconUrl: 'Assets/Images/MamyrLogo.png',
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
            const response = await fetch('Function/Admin/Ajax/getRatings.php');
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
            for (let i = 1; i <= 5; i++) {
                if (i <= Math.floor(data.overAllRating)) {
                    starContainer.innerHTML += '<i class="bi bi-star-fill text-warning"></i>';
                } else if (i - data.overAllRating <= .5 && i - data.overAllRating > 0) {
                    starContainer.innerHTML += '<i class="bi bi-star-half text-warning"></i>';
                } else {
                    starContainer.innerHTML += '<i class="bi bi-star text-warning"></i>';
                }
            }
        }



        getRatings();
        setInterval(getRatings, 300000);
    </script>

</body>

</html>