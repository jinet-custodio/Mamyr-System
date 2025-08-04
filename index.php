<?php
require 'Config/dbcon.php';

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

//SQL statement for retrieving data for website content from DB
$sectionName = 'BusinessInformation';
$getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
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
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="Assets/CSS/landingPage.css">
    <!-- <link rel="stylesheet" href="Assets/CSS/bootstrap.min.css"> -->
    <!-- online stylesheet link for bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <?php if (!$editMode): ?>
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
            <button class=" navbar-toggler ms-auto" id="bg-nav-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse " id="navbarNav">
                <ul class="navbar-nav ms-auto me-10">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Amenities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="Pages/amenities.php">Resort Amenities</a></li>
                            <li><a class="dropdown-item" href="Pages/ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                            <li><a class="dropdown-item" href="Pages/events.php">Events</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">Be Our Partner</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/register.php">Book Now</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Pages/register.php">Sign Up</a>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <div class="custom-container">
        <!-- Save button, only visible if page is on edit mode -->
        <?php if ($editMode): ?>
            <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
        <?php endif; ?>
        <div class="titleContainer">
            <div class="mamyrTitle">
                <?php
                $businessName = str_split($contentMap['DisplayName']);
                $display = strtoupper(implode(" ", $businessName))
                ?>
                <h1 class="name">
                    <?php if ($editMode): ?>
                        <input type="text" class="editable-input form-control white-text" data-title="DisplayName" style="font-size:4.5vw !important;" value="<?= htmlspecialchars($contentMap['DisplayName'] ?? '') ?>">
                    <?php else: ?>
                        <?= htmlspecialchars($display ?? 'Name Not Found') ?>
                    <?php endif; ?>
                </h1>

            </div>

            <div class="description">
                <?php if ($editMode): ?>
                    <textarea cols="20" rows="5" type="text" class="editable-input form-control descriptionText white-text" data-title="ShortDesc" style="font-size:2vw !important;"><?= htmlspecialchars($contentMap['ShortDesc'] ?? 'Description Not Found') ?></textarea>
                <?php else: ?>
                    <p class="descriptionText">
                        <?= htmlspecialchars($contentMap['ShortDesc'] ?? 'Description Not Found') ?> </p>
                <?php endif; ?>
            </div>
            <a class=" btn btn-outline-light me-2" href="Pages/about.php">Learn More</a>
        </div>

        <div class="containerBook">
            <div class="label">
                <h3 class="containerLabel">Check-In Date</h3>
                <h3 class="containerLabel">Check-Out Date</h3>
                <h3 class="containerLabel">No. of Adults</h3>
                <h3 class="containerLabel">No. of Children</h3>
            </div>
            <div class="formBoxes">
                <input type="date" class="form-control" id="start-date" name="start-date" placeholder="MM/DD/YYY">
                <input type="date" class="form-control" id="end-date" name="end-date" placeholder="MM/DD/YYY">
                <input type="number" class="form-control" id="adults" name="adults" placeholder="Adults">
                <input type="number" class="form-control" id="children" name="children" placeholder="Children">
            </div>
            <div class="availBtn">
                <a href="#"><button type="submit" class="btn custom-btn">CHECK FOR AVAILABILITY</button></a>
            </div>
        </div>

        <div class="welcomeSection">
            <div class="resortPic1">
                <?php if (isset($imageMap['DisplayName'])): ?>
                    <?php foreach ($imageMap['DisplayName'] as $index => $img):
                        $imagePath = $img['imageData'];
                        $defaultImage = "Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <div class="image-wrapper mb-3 pic1">
                            <img src="<?= htmlspecialchars($img['imageData']) ?>"
                                alt="<?= htmlspecialchars($img['altText']) ?>"
                                class="img-fluid mb-2 editable-img"
                                style="cursor: pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#editImageModal"
                                data-wcimageid="<?= $img['WCImageID'] ?>"
                                data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                                data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                        </div>
                    <?php endforeach;
                    ?>
                <?php endif; ?>
                <!-- <img src="Assets/Images/landingPage/resortPic1.png" alt="Mamyr Resort" class="pic1"> -->
            </div>


            <div class="wsText">
                <hr class="line">
                <h4 class="wsTitle" style="display: flex;align-items:center;">
                    Welcome to
                    <?php if ($editMode): ?>
                        <input type="text" class="editable-input form-control" data-title="FullName" style="width: 28vw;" value="<?= htmlspecialchars($contentMap['FullName'] ?? 'Name Not Found') ?>">
                    <?php else: ?>
                        <?= htmlspecialchars($contentMap['FullName'] ?? 'Name Not Found') ?>
                    <?php endif; ?>

                </h4>
                <?php if ($editMode): ?>
                    <textarea cols="15" rows="5" type="text" class="editable-input form-control descriptionText" data-title="ShortDesc2"> <?= ltrim(htmlspecialchars($contentMap['ShortDesc2'] ?? 'Description Not Found')) ?> </textarea>
                <?php else: ?>
                    <p class="wsDescription">
                        <?= htmlspecialchars($contentMap['ShortDesc2'] ?? 'Description Not Found') ?> </p>
                <?php endif; ?>
            </div>

        </div>

        <div class="contact">
            <div class="contactText">
                <hr class="line">
                <h4 class="contactTitle">Contact Us </h4>

                <div class="location">
                    <img src="Assets/Images/landingPage/icons/location.png" alt="locationPin" class="locationIcon">
                    <h5 class="locationText">
                        <?php if ($editMode): ?>
                            <input type="text" class="editable-input form-control" data-title="Address" style="width: 37vw;margin-left:-2vw" value="<?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?>">
                        <?php else: ?>
                            <?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?>
                        <?php endif; ?>
                    </h5>
                </div>

                <div class="number">
                    <img src="Assets/Images/landingPage/icons/phone.png" alt="phone" class="phoneIcon">
                    <h5 class="number">
                        <?php if ($editMode): ?>
                            <input type="text" class="editable-input form-control" data-title="ContactNum" style="width: 37vw;margin-left:-2vw" value="<?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>">
                        <?php else: ?>
                            <?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>
                        <?php endif; ?>
                    </h5>
                </div>

                <div class="email">
                    <img src="Assets/Images/landingPage/icons/email.png" alt="email" class="emailIcon">
                    <h5 class="emailAddressText">
                        <?php if ($editMode): ?>
                            <input type="text" class="editable-input form-control" data-title="Email" style="width: 37vw;margin-left:-2vw" value="<?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?>">
                        <?php else: ?>
                            <?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?>
                        <?php endif; ?>
                    </h5>
                </div>


            </div>
            <div class="googleMap" id="googleMap"></div>
        </div>

        <div class="gallery">
            <hr class="line">
            <h4 class="galleryTitle">Gallery </h4>
            <?php if (isset($imageMap['FullName'])): ?>
                <div class="galleryPictures">
                    <?php foreach ($imageMap['FullName'] as $index => $img):
                        $imagePath = $img['imageData'];
                        $defaultImage = "Assets/Images/no-picture.png";
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage; ?>
                        <div class="image-wrapper mb-3 galleryImg">
                            <img src="<?= htmlspecialchars($img['imageData']) ?>"
                                alt="<?= htmlspecialchars($img['altText']) ?>"
                                class="img-fluid mb-2 editable-img"
                                style="cursor: pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#editImageModal"
                                data-wcimageid="<?= $img['WCImageID'] ?>"
                                data-imagepath="<?= htmlspecialchars($img['imageData']) ?>"
                                data-alttext="<?= htmlspecialchars($img['altText']) ?>">
                        </div>
                    <?php endforeach;
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="seeMore">
            <a href="Pages/amenities.php" class="btn custom-btn ">See More</a>
        </div>


        <?php if (!$editMode): ?>
            <footer class="py-1 ">
                <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">

                    <img src="Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

                    <h3 class="mb-0"><?= htmlspecialchars(strtoupper($contentMap['FullName']) ?? 'Name Not Found') ?></h3>
                </div>

                <div class="info">
                    <div class="reservation">
                        <h4 class="reservationTitle">Reservation</h4>
                        <h4 class="numberFooter"><?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?></h4>
                        <h4 class="emailAddressTextFooter"><?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?></h4>
                    </div>
                    <div class="locationFooter">
                        <h4 class="locationTitle">Location</h4>
                        <h4 class="addressTextFooter"><?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?></h4>
                    </div>
                </div>
                <hr class="footerLine">
                <div class="socialIcons">
                    <a href="<?= htmlspecialchars($contentMap['FBLink'] ?? 'None Provided') ?>"><i
                            class='bx bxl-facebook-circle'></i></a>
                    <a href="mailto: <?= htmlspecialchars($contentMap['GmailAdd'] ?? 'None Provided') ?>"><i class='bx bxl-gmail'></i></a>
                    <a href="tel:<?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>">
                        <i class='bx bxs-phone'></i>
                    </a>

                </div>
            </footer>
        <?php endif; ?>
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



    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Bootstrap JS -->
    <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <!-- AJAX for editing website content -->
    <?php if ($editMode): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const saveBtn = document.getElementById('saveChangesBtn');

                saveBtn?.addEventListener('click', () => {
                    // === 1. Save text-based website content ===
                    const inputs = document.querySelectorAll('.editable-input');
                    const data = {
                        sectionName: 'BusinessInformation'
                    };

                    inputs.forEach(input => {
                        const title = input.getAttribute('data-title');
                        const value = input.value;
                        data[title] = value;
                    });

                    fetch('Function/Admin/editWebsite/editWebsiteContent.php', {
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
                        const file = img.fileObject || null;
                        const imagePath = img.dataset.imagepath || ''; // Full path like 'Assets/Images/landingPage/resortPic1.png'

                        if (!wcImageID || (!file && !altText)) return;

                        const formData = new FormData();
                        formData.append('wcImageID', wcImageID);
                        formData.append('altText', altText);
                        formData.append('imagePath', imagePath); // ✅ Send full path

                        if (file) {
                            formData.append('image', file);
                        }

                        fetch('Function/Admin/editWebsite/editWebsiteContent.php', {
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
        function myMap() {
            var mapProp = {
                center: new google.maps.LatLng(15.050861525959231, 121.02183364955998),
                zoom: 5,
            };
            var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        }
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
        const navbar = document.querySelector('.navbar');
        const navCollapse = document.getElementById('navbarNav');

        // Add background when collapse is shown
        navCollapse.addEventListener('shown.bs.collapse', () => {
            navbar.classList.add('white-bg');
        });

        // Remove background when collapse is hidden
        navCollapse.addEventListener('hidden.bs.collapse', () => {
            navbar.classList.remove('white-bg');
        });
    </script>
    <script>
        const dropdownToggle = document.getElementById('navbarDropdown');
        const dropdownMenu = dropdownToggle?.nextElementSibling;

        dropdownToggle?.addEventListener('click', function(e) {
            const isShown = dropdownMenu?.classList.contains('show');

            // If dropdown is already shown, hide it manually
            if (isShown) {
                e.preventDefault(); // Prevent default behavior
                bootstrap.Dropdown.getInstance(dropdownToggle)?.hide();
            }
        });
    </script>


    <script src="Assets/JS/scrollNavbg.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCalqMvV8mz7fIlyY51rxe8IerVxzUTQ2Q&callback=myMap">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>