<?php
require '../../Config/dbcon.php';
session_start();
$partnerID = $_POST['partnerID'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/partnership.css">
</head>

<body>
    <!-- View Individual Partner -->
    <div class="partner" id="partner-info" style="display: none;">
        <!-- Back Button -->
        <div class="page-container">
            <a href="displayPartnership.php?container=1" class="btn btn-primary back"><img src="../../Assets/Images/Icon/whiteArrow.png" alt="Back Button"></a>
            <h3 class="card-title page-title">Partner</h3>
        </div>
        <!-- Get the information to the database -->
        <?php
        $query = "SELECT u.firstName, u.lastName, u.userProfile, u.phoneNumber, p.*, s.statusName 
               FROM partnerships p
               INNER JOIN users u ON p.userID = u.userID 
               INNER JOIN statuses s ON s.statusID = p.partnerStatus
               WHERE partnershipID = '$partnerID'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
            $companyName = $data['companyName'];
            $partnerType = $data['partnerType'];
            $businessEmail = $data['businessEmail'];
            $phoneNumber = $data['phoneNumber'];
            if ($phoneNumber === NULL) {
                $phoneNumber = "--";
            } else {
                $phoneNumber;
            }
            $address = $data['partnerAddress'];
            $link = $data['documentLink'];
            $status = $data['statusName'];

            $profile = $data['userProfile'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $profile);
            finfo_close($finfo);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
        }
        ?>
        <!-- Display the information -->
        <div class="card mb-3">
            <div class="partner-info-name-pic">
                <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($applicantName) ?> ">
                <div class="partner-info-contact">
                    <!-- <h4 class="card-title name">Name</h4> -->
                    <p class="card-text name"><?= $applicantName ?></p>
                    <p class="card-text sub-name"><?= $businessEmail ?> | <?= $phoneNumber ?> </p>
                </div>
            </div>

            <div class="card-body">
                <div class="partner-info">
                    <h4 class="card-title">Company Name</h4>
                    <p class="card-text"><?= $companyName ?></p>
                </div>
                <div class="partner-info">
                    <h4 class="card-title">Business Address</h4>
                    <p class="card-text"><?= $address ?></p>
                </div>
                <div class="partner-info">
                    <h4 class="card-title">Document Link</h4>
                    <a class="stretched-link" href="<?= $link ?>"><?= $link ?></a>
                </div>
                <div class="partner-info">
                    <h4 class="card-title">Partner Type</h4>
                    <p class="card-text"><?= ucfirst($partnerType) ?></p>
                </div>
            </div>
        </div>
    </div>



    <!-- View Individual Applicant -->
    <div class="applicant" id="applicant-request" style="display: none;">
        <!-- Back Button -->
        <div class="page-container">
            <a href="displayPartnership.php?container=2" class="btn btn-primary back"><img src="../../Assets/Images/Icon/backbtn_black.png" alt="Back Button"></a>
            <h3 class="card-title page-title">Applicant</h3>
        </div>
        <!-- Get the information to the database -->
        <?php
        $query = "SELECT u.firstName, u.lastName, u.userProfile, u.phoneNumber, p.*, s.statusName
               FROM partnerships p
               INNER JOIN users u ON p.userID = u.userID 
               INNER JOIN statuses s ON s.statusID = p.partnerStatus
               WHERE partnershipID = '$partnerID'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
            $companyName = $data['companyName'];
            $partnerType = $data['partnerType'];
            $businessEmail = $data['businessEmail'];
            $phoneNumber = $data['phoneNumber'];
            if ($phoneNumber === NULL) {
                $phoneNumber = "--";
            } else {
                $phoneNumber;
            }
            $address = $data['partnerAddress'];
            $link = $data['documentLink'];
            $status = $data['statusName'];
            $profile = $data['userProfile'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $profile);
            finfo_close($finfo);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
        }
        ?>
        <!-- Display the information -->
        <div class="card mb-3">

            <div class="applicant-info-name-pic">
                <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($applicantName) ?> ">
                <div class="applicant-info-contact">
                    <!-- <h4 class="card-title name">Name</h4> -->
                    <p class="card-text name"><?= $applicantName ?></p>
                    <p class="card-text sub-name"><?= $businessEmail ?> | <?= $phoneNumber ?> </p>
                </div>
                <div class="button-container">
                    <form action="../../Function/Admin/partnerApproval.php" method="POST">
                        <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                        <input type="hidden" name="partnerStatus" value="<?= $data['partnerStatus'] ?>">
                        <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                        <button type="submit" class="btn btn-danger" name="declineBtn">Decline</button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="applicant-info">
                    <h4 class="card-title">Company Name</h4>
                    <p class="card-text"><?= $companyName ?></p>
                </div>
                <div class="applicant-info">
                    <h4 class="card-title">Partner Type</h4>
                    <p class="card-text"><?= ucfirst($partnerType) ?></p>
                </div>
                <div class="applicant-info">
                    <h4 class="card-title">Business Address</h4>
                    <p class="card-text"><?= $address ?></p>
                </div>
                <div class="applicant-info">
                    <h4 class="card-title">Document Link</h4>
                    <a href="<?= $link ?>"><?= $link ?></a>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Search URL -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('container');

        const partnerContainer = document.getElementById("partner-info");
        const requestContainer = document.getElementById("applicant-request");

        if (paramValue == 3) {
            partnerContainer.style.display = "block";
            requestContainer.style.display = "none";
        } else if (paramValue == 4) {
            partnerContainer.style.display = "none";
            requestContainer.style.display = "block";
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>
</body>

</html>