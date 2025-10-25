<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once '../Config/dbcon.php';


$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;

//SQL statement for retrieving data for website content from DB
$sectionName = 'BusinessInformation';
$getContent = $conn->prepare("SELECT * FROM resortinfo WHERE resortInfoTitle = ?");
$getContent->bind_param("s", $sectionName);
$getContent->execute();
$getContentResult = $getContent->get_result();
$contentMap = [];
$logoInfo = [];
$defaultImage = "../Assets/Images/no-picture.jpg";
while ($row = $getContentResult->fetch_assoc()) {
    $cleanTitle = trim(preg_replace('/\s+/', '', $row['resortInfoName']));
    $contentID = $row['resortInfoID'];
    $contentMap[$cleanTitle] = $row['resortInfoDetail'];
}

//fetch Business Logo
$sectionName = 'Logo';
$getLogo = $conn->prepare("SELECT * FROM resortinfo WHERE resortInfoTitle = ?");
$getLogo->bind_param("s", $sectionName);
$getLogo->execute();
$getLogoResult = $getLogo->get_result();

while ($row = $getLogoResult->fetch_assoc()) {
    $id = $row['resortInfoID'];
    $title = trim($row['resortInfoName']);
    $detail = $row['resortInfoDetail'];

    $logoInfo[$id] = [$title => $detail];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Assets/CSS/footer.css">
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

</head>

<body>
    <?php if ($editMode): ?>
        <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
    <?php endif; ?>
    <footer class="py-1 px-4" id="footer">
        <div class="upperFooter d-flex pt-2">
            <div class=" py-1 d-flex justify-content-start flex-column w-25" id="nameAndLogo">
                <?php
                foreach ($logoInfo as $id => $logo) {
                    foreach ($logo as $fileName => $altText) {
                        $imagePath = "../Assets/Images/" . $fileName;
                        $finalImage = file_exists($imagePath) ? $imagePath : $defaultImage;
                ?>

                        <img src="<?= htmlspecialchars($finalImage) ?>"
                            alt="<?= htmlspecialchars($altText) ?>"
                            class="editable-img logo" style="cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#editImageModal"
                            data-wcimageid="<?= htmlspecialchars($id) ?>"
                            data-folder=""
                            data-imagepath="<?= htmlspecialchars($fileName) ?>"
                            data-alttext="<?= htmlspecialchars($altText) ?>">

                <?php
                    }
                }
                ?>
                <?php if ($editMode): ?>
                    <input type="text" class="mb-0 editable-input form-control" id="fullName"
                        data-title="FullName" value="<?= htmlspecialchars(strtoupper($contentMap['FullName']) ?? 'Name Not Found') ?>">
                <?php else: ?>
                    <h3 class="mb-0"><?= htmlspecialchars(strtoupper($contentMap['FullName']) ?? 'Name Not Found') ?></h3>
                <?php endif; ?>
                <div class="socialIcons">
                    <a href="<?= htmlspecialchars($contentMap['FBLink'] ?? 'None Provided') ?>"><i
                            class='bx bxl-facebook-circle'></i></a>
                    <a href="mailto: <?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?>"><i
                            class='bx bxl-gmail'></i></a>
                    <a href="tel:<?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>">
                        <i class='bx bxs-phone'></i>
                    </a>

                </div>
            </div>

            <div class="info d-flex align-items-center">
                <div class="reservation">
                    <h4 class="reservationTitle mb-1">Reservation</h4>
                    <?php if ($editMode): ?>
                        <input type="text" class="numberFooter editable-input form-control"
                            data-title="ContactNum" value="<?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>">
                        <input type="text" class="emailAddressTextFooter editable-input form-control"
                            data-title="Email" value="<?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?>">
                    <?php else: ?>
                        <h4 class="numberFooter"><?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?></h4>
                        <h4 class="emailAddressTextFooter">
                            <?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?></h4>
                    <?php endif; ?>


                </div>
                <div class="locationFooter">
                    <h4 class="locationTitle mb-1">Location</h4>
                    <?php if ($editMode): ?>
                        <input type="text" class="addressTextFooter editable-input form-control"
                            data-title="ContactNum" value="<?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?>">
                    <?php else: ?>
                        <h4 class="addressTextFooter"><?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?> </h4>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mapContainer d-flex align-items-center">
                <div id="map"></div>
            </div>
        </div>
        <hr class="footerLine">
        <div class="copyrightContainer">
            <h5 class="text-white text-center" id="copyrightText">© 2025 <?= htmlspecialchars($contentMap['FullName']) ?? 'Name Not Found' ?>. All Rights Reserved.</h5>
        </div>
    </footer>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const lat = 15.05073200154005;
        const lon = 121.0218658098424;

        const map = L.map('map').setView([lat, lon], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);


        const customIcon = L.icon({
            iconUrl: '../Assets/Images/MamyrLogo.png',
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
                document.getElementById("footer").style.marginTop = "8vw";

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

        <script>
            const editableImgs = document.querySelectorAll('.editable-img')
            document.addEventListener("DOMContentLoaded", function() {
                editableImgs.forEach(editable => {
                    editable.style.border = '2px solid red';
                })
            });
        </script>
        <!-- Sweetalert JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Bootstrap JS -->
        <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
        <script>
            const link = document.createElement('link');

            // Set its attributes
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = '../Assets/CSS/bootstrap.min.css';
            document.head.appendChild(link);
        </script>
        <!-- AJAX for editing website content -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const saveBtn = document.getElementById('saveChangesBtn');
                document.body.style.display = 'block';


                saveBtn?.addEventListener('click', () => {
                    saveTextContent();
                    saveEditableImages();
                });

                async function saveTextContent() {
                    const inputs = document.querySelectorAll('.editable-input');
                    const data = {
                        sectionName: 'BusinessInformation'
                    };

                    inputs.forEach(input => {
                        const title = input.getAttribute('data-title');
                        const value = input.value;
                        data[title] = value;
                    });

                    try {
                        const res = await fetch('../Function/Admin/editWebsite/editFooter.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const rawText = await res.text();
                        console.log("Text update server response:", rawText);

                        let response;
                        try {
                            response = JSON.parse(rawText);
                        } catch (e) {
                            throw new Error("Invalid JSON response: " + rawText);
                        }

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Content Updated!',
                                text: 'Text content has been successfully updated.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: response.message || 'Failed to update text content.',
                            });
                        }
                    } catch (err) {
                        console.error('❌ Error saving text content:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'An error occurred!',
                            text: err.message || 'Something went wrong while saving the content.',
                        });
                    }
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
                        formData.append('resortInfoID', wcImageID);
                        formData.append('altText', altText);
                        formData.append('folder', folder);

                        if (file) {
                            formData.append('image', file);
                        }

                        fetch('../Function/Admin/editWebsite/editFooter.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(async res => {
                                const rawText = await res.text();
                                console.log("Server response:", rawText);

                                let response;
                                try {
                                    response = JSON.parse(rawText);
                                } catch (e) {
                                    throw new Error("Invalid JSON: " + rawText);
                                }

                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Successfully Updated!',
                                        text: `Text and images have been updated.`,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Update Failed',
                                        text: response.message || 'Unknown error occurred.',
                                    });
                                }
                            })
                            .catch(error => {
                                console.error(`❌ Error updating image ${wcImageID}:`, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Upload Failed',
                                    text: `Could not update image ${wcImageID}. Check console for details.`
                                });
                            });
                    });
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>