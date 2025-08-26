<?php
require '../Config/dbcon.php';

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

//SQL statement for retrieving data for website content from DB
$sectionName = 'About';
$getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
$getWebContent->bind_param("s", $sectionName);
$getWebContent->execute();
$getWebContentResult = $getWebContent->get_result();
$contentMap = [];
$imageMap = [];
$defaultImage = "Assets/Images/no-picture.png";
while ($row = $getWebContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
    $contentID = $row['contentID'];
    $contentMap[$cleanTitle] = $row['content'];

    // Fetch images with this contentID
    $getImages = $conn->prepare("SELECT WCImageID, imageData, altText FROM websiteContentImages WHERE contentID = ? ORDER BY imageOrder ASC");
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
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/about.css">
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
            <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-10">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"> Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            AMENITIES
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../Pages/amenities.php">RESORT AMENITIES</a></li>
                            <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                            <li><a class="dropdown-item" href="../Pages/events.php">EVENTS</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Pages/blog.php">BLOG</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">BE OUR PARTNER</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./about.php">ABOUT</a>
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

    <div class="titleContainer">
        <h1 class="title" id="title">ABOUT US</h1>
    </div>

    <div class="aboutTopContainer" id="aboutTopContainer">
        <div class="topPicContainer">
            <?php if (isset($imageMap['AboutMamyr'])): ?>
                <?php foreach ($imageMap['AboutMamyr'] as $index => $img):
                $imagePath = "../Assets/Images/aboutImages/" . $img['imageData'];
                    $defaultImage = "Assets/Images/no-picture.png";
                    $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                    <img src="<?= $imagePath ?>"
                        alt="<?= htmlspecialchars($img['altText']) ?>"
                        class="editable-img resortPic"
                        style="cursor: pointer;"
                        data-bs-toggle="modal"
                        data-bs-target="#editImageModal"
                        data-wcimageid="<?= $img['WCImageID'] ?>"
                        data-folder="aboutImages"
                        data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                        data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- <img src="../Assets/Images/amenities/poolPics/poolPic3.jpg" alt="Pool Picture" class="resortPic"> -->


        <div class="topTextContainer">
            <?php if ($editMode): ?>
                <textarea type="text" class="hook editable-input form-control" style="font-size: 2vw;font-weight:700;" data-title="Header"><?= htmlspecialchars($contentMap['Header'] ?? '') ?></textarea>
            <?php else: ?>
                <h3 class="hook"><?= htmlspecialchars($contentMap['Header'] ?? 'Header Not Found') ?> </h3>
            <?php endif; ?>

            <?php if ($editMode): ?>
                <textarea type="text" cols="65" rows="8" class="aboutDescription indent editable-input form-control" data-title="AboutMamyr"><?= htmlspecialchars($contentMap['AboutMamyr'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="aboutDescription indent"><?= htmlspecialchars($contentMap['AboutMamyr'] ?? 'No description found') ?></p>
            <?php endif; ?>


            <a href="#backArrowContainer"><button class="btn btn-primary" onclick="readMore()">Read More</button></a>
        </div>
    </div>

    <div class="ourServicesContainer" id="ourServicesContainer">
        <div class="servicesTitleContainer">
            <h3 class="servicesTitle">Our Services</h3>
            <?php if ($editMode): ?>
                <textarea type="text" cols="65" class="servicesDescription indent editable-input form-control" data-title="AboutMamyr"><?= htmlspecialchars($contentMap['ServicesDesc'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="servicesDescription indent"><?= htmlspecialchars($contentMap['ServicesDesc'] ?? 'No description found') ?></p>
            <?php endif; ?>

        </div>

        <div class="servicesIconContainer">

            <div class="resortContainer">
                <?php if (isset($imageMap['Service1Desc'])): ?>
                    <?php foreach ($imageMap['Service1Desc'] as $index => $img):
                        $imagePath = "Assets/Images/aboutImages/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>"
                            alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img resortIcon"
                            style="cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#editImageModal"
                            data-wcimageid="<?= $img['WCImageID'] ?>"
                            data-folder="aboutImages"
                            data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                      
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<?$defaultImage?>" alt="None Found">
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <input type="text" class="services resortIconTitle editable-input form-control" data-title="Service1" value="<?= htmlspecialchars($contentMap['Service1'] ?? 'No description found') ?>">
                <?php else: ?>
                    <h4 class="resortIconTitle"><?= htmlspecialchars($contentMap['Service1'] ?? 'No description found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5" class="resortIconDescription Service1Desc indent editable-input form-control" data-title="Service1Desc"><?= htmlspecialchars($contentMap['Service1Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="resortIconDescription"><?= htmlspecialchars($contentMap['Service1Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="eventContainer">
                <?php if (isset($imageMap['Service2Desc'])): ?>
                    <?php foreach ($imageMap['Service2Desc'] as $index => $img):
                         $imagePath = "Assets/Images/aboutImages/" . $img['imageData'];
                        $defaultImage = "Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($finalImage) ?>"
                            alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img eventIcon"
                            style="cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#editImageModal"
                            data-wcimageid="<?= $img['WCImageID'] ?>"
                            data-folder="aboutImages"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<?$defaultImage?>" alt="None Found">
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <input type="text" class="services eventIconTitle editable-input form-control" data-title="Service2" value="<?= htmlspecialchars($contentMap['Service2'] ?? 'No description found') ?>">
                <?php else: ?>
                    <h4 class="eventIconTitle"><?= htmlspecialchars($contentMap['Service2'] ?? 'No description found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5" class="eventIconDescription Service2Desc indent editable-input form-control" data-title="Service2Desc"><?= htmlspecialchars($contentMap['Service2Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="eventIconDescription"><?= htmlspecialchars($contentMap['Service2Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>

            <div class="hotelContainer">
                <?php if (isset($imageMap['Service3Desc'])): ?>
                    <?php foreach ($imageMap['Service3Desc'] as $index => $img):
                        $imagePath = "Assets/Images/aboutImages/" . $img['imageData'];
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars( $finalImage) ?>"
                            alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img hotelIcon"
                            style="cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#editImageModal"
                            data-wcimageid="<?= $img['WCImageID'] ?>"
                            data-folder="aboutImages"
                            data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">

                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <input type="text" class="services hotelIconTitle editable-input form-control" data-title="Service3" value="<?= htmlspecialchars($contentMap['Service3'] ?? 'No description found') ?>">
                <?php else: ?>
                    <h4 class="hotelIconTitle"><?= htmlspecialchars($contentMap['Service3'] ?? 'No description found') ?></h4>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="5" class="hotelIconDescription Service3Desc indent editable-input form-control" data-title="Service3Desc"><?= htmlspecialchars($contentMap['Service3Desc'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="hotelIconDescription"><?= htmlspecialchars($contentMap['Service3Desc'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="videoContainer" id="videoContainer">
        <div class="videoTextContainer">
            <?php
            $sectionName = 'BusinessInformation';
            $getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
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
            <h3 class="videoTitle">Explore <?= htmlspecialchars($businessInfo['FullName'] ?? 'No description found') ?></h3>
            <?php if ($editMode): ?>
                <textarea type="text" rows="13" cols="75" class="videoDescription white-text indent editable-input form-control" data-title="Explore"><?= htmlspecialchars($contentMap['Explore'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="videoDescription indent"><?= htmlspecialchars($contentMap['Explore'] ?? 'No description found') ?></p>
            <?php endif; ?>

        </div>

        <div class="embed-responsive embed-responsive-16by9">
            <video id="mamyrVideo" autoplay muted controls class="embed-responsive-item"
                poster="../Assets/Videos/thumbnail2.jpg">
                <source src="../../Assets/Videos/mamyrVideo2.mp4" type="video/mp4">

            </video>
        </div>
    </div>


    <div class="backArrowContainer" id="backArrowContainer">
        <a href="about.php?edit=true"><img src="../Assets/Images/Icon/whiteArrow.png" alt="Back Button" class="backArrow"> </a>
    </div>

    <div class="mamyrHistoryContainer" id="mamyrHistoryContainer">
        <div class="firstParagraphContainer">
            <div class="firstParagraphtextContainer">
                <?php if ($editMode): ?>
                    <textarea type="text" rows="7" cols="75" class="firstParagraph indent editable-input form-control" data-title="HistoryParagraph1"><?= htmlspecialchars($contentMap['HistoryParagraph1'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="firstParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph1'] ?? 'No description found') ?></p>
                <?php endif; ?>
                <?php if ($editMode): ?>
                    <textarea type="text" rows="8" cols="75" class="secondParagraph indent editable-input form-control" data-title="HistoryParagraph2"><?= htmlspecialchars($contentMap['HistoryParagraph2'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="secondParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph2'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>


            <div class="firstImageContainer">
                <?php if (isset($imageMap['HistoryParagraph1'])): ?>
                    <?php foreach ($imageMap['HistoryParagraph1'] as $index => $img):
                        $imagePath = "Assets/Images/aboutImages/" . $img['imageData'];
                        $defaultImage = "Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($img['imageData']) ?>"
                            alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img firstParagraphPhoto"
                            style="cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#editImageModal"
                            data-wcimageid="<?= $img['WCImageID'] ?>"
                            data-folder="aboutImages"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="thirdParagraphContainer">
            <div class="thirdImageContainer">
                <?php if (isset($imageMap['HistoryParagraph3'])): ?>
                    <?php foreach ($imageMap['HistoryParagraph3'] as $index => $img):
                        $imagePath = "Assets/Images/aboutImages/" . $img['imageData'];
                        $defaultImage = "Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <img src="<?= htmlspecialchars($img['imageData']) ?>"
                            alt="<?= htmlspecialchars($img['altText']) ?>"
                            class="editable-img thirdParagraphPhoto"
                            style="cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#editImageModal"
                            data-wcimageid="<?= $img['WCImageID'] ?>"
                            data-folder="aboutImages"
                            data-alttext="<?= htmlspecialchars($img['altText']) ?>">

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="thirdParagraphtextContainer">
                <?php if ($editMode): ?>
                    <textarea type="text" rows="10" cols="75" class="thirdParagraph indent editable-input form-control" data-title="HistoryParagraph3"> <?= htmlspecialchars($contentMap['HistoryParagraph3'] ?? 'No description found') ?></textarea>
                <?php else: ?>
                    <p class="thirdParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph3'] ?? 'No description found') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="fourthParagraphContainer">
            <?php if ($editMode): ?>
                <textarea type="text" rows="5" cols="75" class="fourthParagraph indent editable-input form-control" data-title="HistoryParagraph4"><?= htmlspecialchars($contentMap['HistoryParagraph4'] ?? 'No description found') ?></textarea>
            <?php else: ?>
                <p class="fourthParagraph indent"><?= htmlspecialchars($contentMap['HistoryParagraph4'] ?? 'No description found') ?></p>
            <?php endif; ?>

        </div>
    </div>

    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

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
                    <h4 class="emailAddressTextFooter"><?= htmlspecialchars($businessInfo['Email'] ?? 'None Provided') ?></h4>
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
                <a href="mailto: <?= htmlspecialchars($businessInfo['GmailAdd'] ?? 'None Provided') ?>"><i class='bx bxl-gmail'></i></a>
                <a href="tel:<?= htmlspecialchars($businessInfo['ContactNum'] ?? 'None Provided') ?>">
                    <i class='bx bxs-phone'></i>
                </a>
            </div>
        </footer>
    <?php endif; ?>

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
                        <img id="modalImagePreview" src="" alt="" class="img-thumbnail mb-3" style="max-width: 250px;">

                        <input type="file" id="modalImageUpload" class="form-control mb-2">

                        <input type="text" id="modalAltText" class="form-control mb-3" placeholder="Alt text">

                        <!-- Changed label to "Choose" -->
                        <button id="chooseImageBtn" class="btn btn-success me-2" data-bs-dismiss="modal">Choose This Image</button>
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

                            // Save temp image to dataset (so we can upload on final save)
                            activeImageElement.setAttribute('data-tempfile', newFile.name);
                            activeImageElement.fileObject = newFile; // temporarily attach file to element
                        };
                        reader.readAsDataURL(newFile);
                    }
                });
            });
        </script>

    <?php endif; ?>

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

            saveBtn?.addEventListener('click', () => {
                // === 1. Save text-based website content ===
                const inputs = document.querySelectorAll('.editable-input');
                const data = {
                    sectionName: 'About'
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
                    .then(response => {
                        console.log('Content saved:', response);
                        alert('Website content saved!');
                    })
                    .catch(err => {
                        console.error('Error saving content:', err);
                        alert('An error occurred while saving content.');
                    });

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
                        .then(data => {
                            if (data.success) {
                                console.log(`Image ${wcImageID} updated successfully.`);
                            } else {
                                alert(`Failed to update image ${wcImageID}: ` + data.message);
                            }
                        })
                        .catch(err => {
                            console.error('Image update failed:', err);
                            alert('An error occurred while updating an image.');
                        });
                });

            });
        });
        </script>
    <?php endif; ?>

    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('edit');
        const mamyrHistoryContainer = document.getElementById("mamyrHistoryContainer")
        const backArrowContainer = document.getElementById("backArrowContainer")
        const aboutTopContainer = document.getElementById("aboutTopContainer")
        const ourServicesContainer = document.getElementById("ourServicesContainer")
        const videoContainer = document.getElementById("videoContainer")

        mamyrHistoryContainer.style.display = "none"
        backArrowContainer.style.display = "none"


        function readMore() {
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
        }

        if (paramValue) {
            let editables = document.querySelectorAll('.editable-img');

            editables.forEach(editable => {
                editable.style.border = "2px solid red";
            })
        };
    </script>



    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>

    <!-- Sweetalert JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
</body>

</html>