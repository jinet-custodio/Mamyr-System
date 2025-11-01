<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/footer.css">
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
    <footer class="py-1 px-3" id="footer">
        <div class="upperFooter d-flex pt-2">
            <div class=" py-1 d-flex justify-content-start flex-column" id="nameAndLogo">
                <?php
                foreach ($logoInfo as $id => $logo) {
                    foreach ($logo as $fileName => $altText) {
                        $imagePath = "../../Assets/Images/" . $fileName;
                        $indexPath = "Assets/Images/" .  $fileName;
                        $finalImage = file_exists($imagePath)
                            ? $imagePath
                            : (file_exists($indexPath)
                                ? $indexPath
                                : $defaultPath);
                ?>
                <img src="<?= $baseURL ?>/Assets/Images/<?= htmlspecialchars($fileName) ?>"
                    alt="<?= htmlspecialchars($altText) ?>" class="logo mx-auto mb-0">
                <?php
                    }
                }
                ?>

                <h3 class="mb-0 text-center">
                    <?= htmlspecialchars(strtoupper($contentMap['FullName']) ?? 'Name Not Found') ?>
                </h3>

                <div class="socialIcons">
                    <a href="<?= htmlspecialchars($contentMap['FBLink'] ?? 'None Provided') ?>" target="_blank"><i
                            class='bx bxl-facebook-circle'></i></a>
                    <a href="mailto: <?= htmlspecialchars($contentMap['GmailAdd'] ?? 'None Provided') ?>" target="_blank"><i
                            class='bx bxl-gmail'></i></a>
                    <a href="tel:<?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>" target="_blank">
                        <i class='bx bxs-phone'></i>
                    </a>
                </div>
            </div>

            <div class="info d-flex align-items-center">
                <div class="reservation">
                    <h4 class="reservationTitle mb-1"><i class="bi bi-telephone-fill"></i></h4>
                    <h4 class="numberFooter"><?= htmlspecialchars($contentMap['ContactNum'] ?? 'None Provided') ?>
                    </h4>
                    <h4 class="emailAddressTextFooter">
                        <?= htmlspecialchars($contentMap['Email'] ?? 'None Provided') ?>
                    </h4>
                </div>
                <div class="locationFooter">
                    <h4 class="locationTitle mb-1"><i class="bi bi-geo-alt-fill"></i></i></h4>
                    <h4 class="addressTextFooter"><?= htmlspecialchars($contentMap['Address'] ?? 'None Provided') ?>
                    </h4>
                </div>
            </div>
            <div class="mapContainer d-flex align-items-center">
                <div id="map"></div>
            </div>
        </div>
        <hr class="footerLine w-100">
        <div class="copyrightContainer w-100">
            <h5 class="text-white text-center" id="copyrightText">© 2025
                <?= htmlspecialchars($contentMap['FullName']) ?? 'Name Not Found' ?>. All Rights Reserved.</h5>
        </div>
    </footer>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    const baseURL = "<?= $baseURL ?>";
    const logoFile = "<?= htmlspecialchars($fileName) ?>";
    const lat = 15.05073200154005;
    const lon = 121.0218658098424;

    const map = L.map('map').setView([lat, lon], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);


    const customIcon = L.icon({
        iconUrl: `${baseURL}/Assets/Images/${logoFile}`,
        iconSize: [100, 25],
        iconAnchor: [25, 50],
        popupAnchor: [0, -50]
    });


    L.marker([lat, lon], {
            icon: customIcon
        }).addTo(map)
        .bindPopup('Mamyr Resort and Events Place is Located Here!')
        .openPopup();
    console.log(`${baseURL}/Assets/Images/${logoFile}`)
    </script>

</body>

</html>