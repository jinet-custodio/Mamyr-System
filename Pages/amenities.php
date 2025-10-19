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
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
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
            <button class=" navbar-toggler ms-auto collapsed" id="bg-nav-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse " id="navbarNav">
                <ul class="navbar-nav ms-auto me-10" id="navUL">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"> Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Amenities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item active" href="amenities.php">Resort Amenities</a></li>
                            <li><a class="dropdown-item" href="ratesAndHotelRooms.php">Rates and Hotel Rooms</a></li>
                            <li><a class="dropdown-item" href="events.php">Events</a></li>
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
    <?php endif; ?>

    <div class="amenities" id="amenities">
        <h1 class="title">OUR AMENITIES</h1>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" muted loop controls class="embed-responsive-item">
                <source src="../Assets/videos/mamyrVideo1.mp4" type="video/mp4">
            </video>

        </div>

        <div class="pool">
            <div class="amenityTitleContainer">
                <?php if ($editMode): ?>
                    <input type="text" class="amenityTitle editable-input form-control text-center mx-auto"
                        data-title="Amenity1"
                        value="<?= htmlspecialchars($contentMap['Amenity1'] ?? 'No Title found') ?>">
                <?php else: ?>
                    <h4 class="amenityTitle"><?= htmlspecialchars($contentMap['Amenity1'] ?? 'No title found') ?></h4>
                <?php endif; ?>

                <?php if ($editMode): ?>
                    <textarea rows="5"
                        class="amenityDescription Amenity1Desc indent editable-input form-control text-center"
                        data-title="Amenity1Desc"><?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="amenityDescription">
                        <?= htmlspecialchars($contentMap['Amenity1Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="swiper mySwiper swiper-amenity1">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['Amenity1'])): ?>
                        <?php foreach ($imageMap['Amenity1'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/poolPics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img" style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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
            <div class="swiper mySwiper swiper-amenity2">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['Amenity2'])): ?>
                        <?php foreach ($imageMap['Amenity2'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/cottagePics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img" style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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

            <div class="swiper mySwiper swiper-amenity3">
                <div class="swiper-wrapper" id="videokeSwiper">
                    <?php if (isset($imageMap['Amenity3'])): ?>
                        <?php foreach ($imageMap['Amenity3'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/videokePics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img
                                    src="<?= htmlspecialchars($finalImage) ?>"
                                    alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img"
                                    style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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

            <div class="swiper mySwiper swiper-amenity4">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['Amenity4'])): ?>
                        <?php foreach ($imageMap['Amenity4'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/pavilionPics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img" style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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

            <div class="swiper mySwiper swiper-amenity5">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['Amenity5'])): ?>
                        <?php foreach ($imageMap['Amenity5'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/miniPavPics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img" style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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
            <div class="swiper mySwiper swiper-amenity6">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['Amenity6'])): ?>
                        <?php foreach ($imageMap['Amenity6'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/hotelPics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img" style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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

            <div class="swiper mySwiper swiper-amenity7">
                <div class="swiper-wrapper">
                    <?php if (isset($imageMap['Amenity7'])): ?>
                        <?php foreach ($imageMap['Amenity7'] as $index => $img):
                            $imagePath = "../Assets/Images/amenities/parkingPics/" . $img['imageData'];
                            $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($finalImage) ?>" alt="<?= htmlspecialchars($img['altText']) ?>"
                                    class="editable-img" style="cursor: pointer;"
                                    <?php if ($editMode): ?>
                                    data-bs-toggle="modal"
                                    data-bs-target="#editImageModal"
                                    data-wcimageid="<?= htmlspecialchars($img['WCImageID'] ?? '') ?>"
                                    data-folder="<?= $folder ?>"
                                    data-imagepath="<?= htmlspecialchars($img['imageData'] ?? '') ?>"
                                    data-alttext="<?= htmlspecialchars($img['altText']) ?>"
                                    <?php endif; ?>>
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
    </div>



    <?php if (!$editMode) {
        include 'footer.php';
        include 'loader.php';
    } else {
        include 'editImageModal.php';
        include 'loader.php';
    }
    ?>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var video = document.getElementById("mamyrVideo");

        video.onplay = function() {
            video.muted = false;
        };
    </script>
    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
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

            initWebsiteEditor('Amenities', '../Function/Admin/editWebsite/editWebsiteContent.php');
        </script>

    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script src="../Assets/JS/scrollNavbg.js"></script>
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
                spaceBetween: 20,
                loop: false,
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