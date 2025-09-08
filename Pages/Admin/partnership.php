<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (isset($_POST['view-btn'])) {
    $_SESSION['partnerID'] = mysqli_real_escape_string($conn, $_POST['partnerID']);
}

if (isset($_POST['view-partner'])) {
    $_SESSION['partnerID'] = mysqli_real_escape_string($conn, $_POST['partnerID']);
}

$partnerID = $_SESSION['partnerID']  ?? null;

if (!$partnerID) {
    echo "<script>console.log('PHP says: " . addslashes($partnerID) . "'); </script>";
}
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
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
        $selectQuery = $conn->prepare("SELECT u.*, p.*, s.statusName, pt.partnerTypeDescription
                                FROM partnership p
                                INNER JOIN user u ON p.userID = u.userID
                                INNER JOIN status s ON s.statusID = p.partnerStatusID
                                LEFT JOIN partnershiptype pt ON p.partnerTypeID = pt.partnerTypeID
                                WHERE partnershipID = ?
                                ");
        $selectQuery->bind_param("i", $partnerID);
        $selectQuery->execute();
        $result = $selectQuery->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
            $companyName = $data['companyName'];
            $partnerType = $data['partnerTypeDescription'];
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
            if ($profile) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $profile);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
            } else {
                $image = '../../Assets/Images/defaultProfile.png';
            }

            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
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
        $selectQuery = $conn->prepare("SELECT u.*, p.*, s.statusName, pt.partnerTypeDescription
                                FROM partnership p
                                INNER JOIN user u ON p.userID = u.userID
                                INNER JOIN status s ON s.statusID = p.partnerStatusID
                                LEFT JOIN partnershiptype pt ON p.partnerTypeID = pt.partnerTypeID
                                WHERE partnershipID = ?
                                ");
        $selectQuery->bind_param("i", $partnerID);
        $selectQuery->execute();
        $result = $selectQuery->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
            $companyName = $data['companyName'];
            $partnerType = $data['partnerTypeDescription'];
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
            if ($profile) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $profile);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
            } else {
                $image = '../../Assets/Images/defaultProfile.png';
            }
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
                        <input type="hidden" name="partnerStatus" value="<?= $data['partnerStatusID'] ?>">
                        <input type="hidden" name="partnerUserID" value="<?= $data['userID'] ?>">
                        <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectionModal">
                            Decline
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="rejectionModalLabel">Reason for Rejection</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label for="rejectionReason">Please provide the reason for rejecting this request</label>
                                        <input type="text" name="rejectionReason" id="rejectionReason">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger" name="declineBtn">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    <a href="<?= $link ?>" target="_blank"><?= $link ?></a>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <!-- Search URL -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('container');
        const action = params.get("action");
        // const paramValue = atob(encodedParamValue);

        const partnerContainer = document.getElementById("partner-info");
        const requestContainer = document.getElementById("applicant-request");

        if (paramValue == 3) {
            partnerContainer.style.display = "block";
            requestContainer.style.display = "none";
        } else if (paramValue == 4) {
            partnerContainer.style.display = "none";
            requestContainer.style.display = "block";
        }

        if (action === "failed1") {
            Swal.fire({
                icon: 'error',
                title: 'Partnership Approval Failed',
                text: 'There was an issue approving the partnership request. Please try again.'
            });
        } else if (action === "failed2") {
            Swal.fire({
                icon: 'error',
                title: 'Partnership Rejection Failed',
                text: 'There was an issue declining the partnership request. Please try again.'
            });
        } else if (action === "failed") {
            Swal.fire({
                icon: 'error',
                title: 'Partnership Approval Failed',
                text: 'There was an issue approving/rejecting the partnership request. Please try again.'
            });
        }




        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>
</body>

</html>