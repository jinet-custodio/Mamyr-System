<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../Config/dbcon.php';
//for setting image paths in 'include' statements
session_start();
$baseURL = '..';

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;

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

$defaultImage = "../Assets/Images/no-picture.jpg";
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
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/events.css">
    <link rel="stylesheet" href="../Assets/CSS/navbar.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <?php if (!$editMode): ?>
        <nav class="navbar navbar-expand-lg fixed-top white-text" id="navbar-half">
            <a href="../index.php"><img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"></a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>


            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
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
                    <li class="nav-item">
                        <a class="nav-link" href="beOurPartnerNew.php" id="bopNav">Be Our Partner</a>
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
    <?php else: ?>
        <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
    <?php endif; ?>

    <div class="titleContainer">
        <?php if ($editMode): ?>
            <input type="text" class="title editable-input form-control text-center mx-auto text-white"
                data-title="EventTitle" value="<?= htmlspecialchars($contentMap['EventTitle'] ?? 'No Title found') ?>">
            <textarea type="text" rows="5"
                class="amenityDescription EventDesc indent editable-input form-control text-white text-center"
                data-title="EventDesc"><?= htmlspecialchars($contentMap['EventDesc'] ?? 'No description found') ?></textarea>
        <?php else: ?>
            <h4 class="title"><?= htmlspecialchars($contentMap['EventTitle'] ?? 'No Title found') ?></h4>
            <p class="description"><?= htmlspecialchars($contentMap['EventDesc'] ?? 'No description found') ?></p>
        <?php endif; ?>
    </div>

    <div class="categories nonEditable">
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php
                $defaultImage = "../Assets/Images/no-picture.jpg";

                foreach ($eventMap as $key => $event):
                    $eventName = $event['categoryName'] ?? 'Untitled Event';
                    $eventDesc = $event['eventDescription'] ?? 'No description available.';
                    $folderPath = '../Assets/Images/EventsPhotos/' .  $event['imagePath'];
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
                                <button type="button" class="btn btn-primary mt-auto bookBtn">BOOK NOW</button>
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
        <?php if ($editMode): ?>
            <input type="text" class="venueTitle OurEventsTitle editable-input form-control text-center mx-auto"
                data-title="OurEventsTitle" value="<?= htmlspecialchars($contentMap['OurEventsTitle'] ?? 'No Title found') ?>">
            <textarea type="text" rows="5"
                class="venueDescription OurEventsDesc indent editable-input form-control  text-center"
                data-title="OurEventsDesc"><?= htmlspecialchars($contentMap['OurEventsDesc'] ?? 'No description found') ?></textarea>
        <?php else: ?>
            <h3 class="venueTitle"><?= htmlspecialchars($contentMap['OurEventsTitle'] ?? 'No Title found') ?></h3>
            <p class="venueDescription indent"><?= htmlspecialchars($contentMap['OurEventsDesc'] ?? 'No description found') ?></p>
        <?php endif; ?>
    </div>

    <div class="mainHall nonEditable">
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
                <ul class="mainHallDescription" id="mainHallDesc">
                    <li>Maximum usage of ???; ₱2,000 per hour extension fee.
                    <li>Elegant, fully air-conditioned function room.</li>
                    <li>Capacity of up to ??? guests.</li>
                </ul>
            <?php } ?>
        </div>

    </div>

    <div class="miniHall mb-5 nonEditable">
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
                <ul class="miniHallDescription" id="miniHallDesc">
                    <li>Maximum usage of ???; ₱2,000 per hour extension fee.
                    <li>Elegant, fully air-conditioned function room.</li>
                    <li>Capacity of up to ??? guests.</li>
                </ul>
            <?php } ?>
        </div>

        <div id="carouselMiniHall" class="carousel slide" data-bs-ride="carousel">
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

    <?php if (!$editMode) {
        include 'footer.php';
        include '../Pages/Customer/loader.php';
    } else {
        include 'editImageModal.php';
        include '../Pages/Customer/loader.php';
    }
    ?>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Scroll Nav BG -->
    <script src="../Assets/JS/scrollNavbg.js"></script>

    <!-- Function for book now button -->
    <script>
        const bookButtons = document.querySelectorAll('.bookBtn');

        bookButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Login or Create an Account to Book',
                    text: 'To proceed with a booking, please log in or create an account.',
                    icon: 'info',
                    confirmButtonText: 'Okay',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'register.php';
                    }
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

            initWebsiteEditor('Events', '../Function/Admin/editWebsite/editWebsiteContent.php');
        </script>
        <script>
            const nonEditables = document.querySelectorAll(".nonEditable");
            nonEditables.forEach(nonEditable => {
                nonEditable.addEventListener("click", function() {
                    Swal.fire({
                        title: "Why can't I this?",
                        text: "Most of the contents of this page are already found at the services and amenities section. To edit, please head to the Services page to ensure consistency.",
                        icon: "info",
                        confirmButtonText: "Got it!"
                    });
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>