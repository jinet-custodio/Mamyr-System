<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
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

switch ($userRole) {
    case 3:
        $role = "Admin";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

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
            <a href="displayPartnership.php?container=1" class="btn btn-primary back"><img
                    src="../../Assets/Images/Icon/arrowBtnWhite.png" alt="Back Button"></a>
            <h3 class="card-title page-title">Partner</h3>
        </div>
        <!-- Get the information to the database -->
        <?php
        $partnerStatusID = 2;
        $selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, u.phoneNumber, u.userProfile, 
                                p.validID, p.companyName, p.businessEmail, p.partnerAddress, p.documentLink, p.partnerStatusID, p.userID,
                                s.statusName, pt.partnerTypeDescription 
                                FROM partnership p
                                INNER JOIN user u ON p.userID = u.userID
                                INNER JOIN status s ON s.statusID = p.partnerStatusID
                                LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
                                LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
                                WHERE  p.partnershipID = ? AND p.partnerStatusID = ?
                                ");
        $selectQuery->bind_param("ii", $partnerID, $partnerStatusID);
        $selectQuery->execute();
        $result = $selectQuery->get_result();
        if ($result->num_rows > 0) {
            $partnerTypes = [];
            if ($data = $result->fetch_assoc()) {
                $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
                $companyName = $data['companyName'];
                $partnerTypes[] = $data['partnerTypeDescription'];
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

                $imageName = $data['validID'] ?? 'N/A';
            }
            // echo '<pre>';
            // print_r($data);
            // echo '</pre>';
        }
        ?>
        <!-- Display the information -->
        <div class="card mb-3">
            <div class="partner-info-name-pic">
                <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start"
                    alt="<?= htmlspecialchars($applicantName) ?> ">
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
                    <a href="<?= $link ?>" target="_blank" onclick="return warnExternalLink('<?= htmlspecialchars($link) ?>', event)"><?= $link ?></a>

                </div>
                <div class="applicant-info">
                    <h4 class="card-title">Partner Type</h4>
                    <?php foreach ($partnerTypes as  $partnerType): ?>
                        <p class="card-text"><?= ($partnerType) ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="applicant-info validID">
                    <h4 class="card-title">Valid ID</h4>
                    <input type="text" class="form-control validID" value="<?= $imageName ?? '' ?>" name="validID" readonly>
                    <button type="button" class="btn btn-primary viewID" data-bs-toggle="modal"
                        data-bs-target="#partnerModal">View ID</button>
                </div>
            </div>
        </div>
        <div class="modal fade" id="partnerModal" tabindex="-1" aria-labelledby="partnerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Valid ID</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <img src="../../Assets/Images/BusinessPartnerIDs/<?= $imageName ?? '' ?>" alt="Valid ID"
                            class="validIDImg">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- View Individual Applicant -->
    <div class="applicant" id="applicant-request" style="display: none;">
        <!-- Back Button -->
        <div class="page-container">
            <a href="displayPartnership.php?container=2" class="btn btn-primary back"><img
                    src="../../Assets/Images/Icon/arrowBtnWhite.png" alt="Back Button"></a>
            <h3 class="card-title page-title">Applicant</h3>
        </div>
        <!-- Get the information to the database -->
        <?php
        $pendingStatusID = 1;
        $rejectedStatusID = 3;
        $selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, u.phoneNumber, u.userProfile,
                                p.validID, p.companyName, p.businessEmail, p.partnerAddress, p.documentLink, p.partnerStatusID, p.userID,
                                s.statusName, pt.partnerTypeDescription, ppt.partnerTypeID 
                                FROM partnership p
                                INNER JOIN user u ON p.userID = u.userID
                                INNER JOIN status s ON s.statusID = p.partnerStatusID
                                LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
                                LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
                                WHERE  p.partnershipID = ? AND (p.partnerStatusID = ? OR  p.partnerStatusID = ?)
                                ");
        $selectQuery->bind_param("iii", $partnerID, $pendingStatusID, $rejectedStatusID);
        $selectQuery->execute();
        $result = $selectQuery->get_result();
        if ($result->num_rows === 0) {
            $nodata = 'Nodata';
        }
        $partnerTypes = [];
        while ($data = $result->fetch_assoc()) {
            $applicantID = (int) $data['userID'];
            $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
            $companyName = $data['companyName'];
            $partnerTypeID = (int) $data['partnerTypeID'];
            $partnerTypes[$partnerTypeID] = $data['partnerTypeDescription'];
            $businessEmail = $data['businessEmail'];
            $phoneNumber = $data['phoneNumber'];
            if ($phoneNumber === NULL) {
                $phoneNumber = "--";
            } else {
                $phoneNumber;
            }
            $address = $data['partnerAddress'] ?? null;
            $link = $data['documentLink'] ?? null;
            $status = $data['statusName'] ?? null;
            $profile = $data['userProfile'] ?? null;
            if ($profile) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $profile);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
            } else {
                $image = '../../Assets/Images/defaultProfile.png';
            }
            $partnerStatus = $data['partnerStatusID'] ?? null;
            $imageName = $data['validID'] ?? 'N/A';
        }

        ?>

        <div class="card mb-3">
            <form action="../../Function/Admin/partnerApproval.php" method="POST">
                <!-- Display the information -->
                <div class="applicant-info-name-pic">
                    <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start"
                        alt="<?= htmlspecialchars($applicantName) ?> ">
                    <div class="applicant-info-contact">
                        <!-- <h4 class="card-title name">Name</h4> -->
                        <p class="card-text name"><?= $applicantName ?> </p>
                        <p class="card-text sub-name"><?= $businessEmail ?> | <?= $phoneNumber ?> </p>
                    </div>
                    <?php if ($partnerStatus === 1) { ?>
                        <div class="button-container">
                            <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>

                            <button type="button" class="btn btn-danger" id="declineBtn" data-bs-toggle="modal"
                                data-bs-target="#rejectionModal">
                                Decline
                            </button>
                        </div>
                    <?php } else { ?>
                        <div class="rejected-image">
                            <img src="../../Assets/Images/Icon/rejected.png" alt="Rejected Image">
                        </div>
                    <?php } ?>
                </div>

                <div class="card-body">
                    <div class="firstRow">
                        <div class="applicant-info">
                            <h4 class="card-title">Company Name</h4>
                            <p class="card-text"><?= $companyName ?></p>
                        </div>
                        <div class="applicant-info" id="partnerType-container">
                            <h4 class="card-title">Partner Type</h4>
                            <?php foreach ($partnerTypes as  $id => $name): ?>
                                <div class="partnertype-container">
                                    <input type="checkbox" name="partnerTypes[]" value="<?= $id ?>">
                                    <label> <?= $name ?></label>
                                </div>

                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="secondRow">
                        <div class="applicant-info">
                            <h4 class="card-title">Business Address</h4>
                            <p class="card-text"><?= $address ?></p>
                        </div>
                        <div class="applicant-info documentLink">
                            <h4 class="card-title">Document Link</h4>
                            <a href="<?= $link ?>" target="_blank" onclick="return warnExternalLink('<?= htmlspecialchars($link) ?>', event)"><?= $link ?></a>
                        </div>
                        <div class="applicant-info validID">
                            <h4 class="card-title">Valid ID</h4>
                            <input type="text" class="form-control validID" value="<?= htmlspecialchars($imageName) ?>" name="validID" readonly>
                            <button type="button" class="btn btn-primary viewID" data-bs-toggle="modal"
                                data-bs-target="#applicantModal">View ID</button>
                        </div>
                    </div>
                </div>

                <div class="hidden-inputs">
                    <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                    <input type="hidden" name="partnerStatus" id="partnerStatusID" value="<?= $partnerStatus ?>">
                    <input type="hidden" name="partnerUserID" value="<?= $applicantID ?>">
                </div>

                <!-- Modal -->
                <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="rejectionModalLabel">Reason for Rejection</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label for="rejectionReason">Please provide the reason for rejecting this
                                    request</label>
                                <textarea name="rejectionReason" id="rejectionReason" cols="50" rows="5"> </textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger" name="declineBtn">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal fade" id="applicantModal" tabindex="-1" aria-labelledby="applicantModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Valid ID</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <img src="../../Assets/Images/BusinessPartnerIDs/<?= $imageName ?>" alt="Valid ID"
                            class="validIDImg">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        } else if (action === "emptyPartnerTypes") {
            Swal.fire({
                icon: 'warning',
                title: 'Oops',
                text: 'Please choose a partner type to approve!',
                confirmButtonText: 'Okay'
            }).then(() => {
                document.getElementById('partnerType-container').style.border = '1px solid red';
            })
        }




        if (action) {
            const url = new URL(window.location);
            url.searchParams.delete('action');
            history.replaceState({}, document.title, url.toString());
        }
    </script>


    <!-- To show warning when clicking the link -->
    <script>
        function warnExternalLink(url, e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '⚠️ External Link Warning',
                html: `
            <p class="fs-6 mb-2">You are about to visit an external site.</p>
            <p class="fs-6 mb-3"><b>This link may be unsafe, unverified, or contain spam.</b></p>
            <p class="fs-6 mb-2">Do you still want to continue to:</p>
            <code style="display:block; color: #1a73e8; margin-bottom: 15px;">${url}</code>
        `,
                showCancelButton: true,
                confirmButtonText: 'Yes, open link',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                width: '500px',
                padding: '2rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(url, '_blank');
                }
            });

            return false;
        }
    </script>

</body>

</html>