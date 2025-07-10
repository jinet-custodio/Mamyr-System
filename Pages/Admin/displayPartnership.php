<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];


$message = '';
$status = '';

if (isset($_SESSION['error-partnership'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['error-partnership']));
    $status = 'error';
    unset($_SESSION['error-partnership']);
} elseif (isset($_SESSION['success-partnership'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['success-partnership']));
    $status = 'success';
    unset($_SESSION['success-partnership']);
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/displayPartnership.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>



    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <a href="#" class="notifs">
                <img src="../../Assets/Images/Icon/notification.png" alt="Notification Icon">
            </a>
            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
            </a>

            <?php
            if ($userRole == 3) {
                $admin = "Admin";
            } else {
                $_SESSION['error'] = "Unauthorized Access eh!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }

            if ($admin === "Admin") {
                $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $firstName = $row['firstName'];
                } else {
                    $firstName = 'None';
                }
            } else {
                $_SESSION['error'] = "Unauthorized Access ah!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }
            ?>
            <h5 class="adminTitle"><?= ucfirst($firstName) ?></h5>
            <a href="Account/account.php" class="admin">
                <img src="../../Assets/Images/Icon/profile.png" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar">
        <a class="nav-link" href="adminDashboard.php">
            <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
            <h5>Dashboard</h5>
        </a>

        <a class="nav-link" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>

        <a class="nav-link" href="roomList.php">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link" href="transaction.php">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>

        <a class="nav-link" href="revenue.php">
            <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
            <h5>Revenue</h5>
        </a>

        <a class="nav-link active" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="#">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>
        <div class="nav-link">
            <a href="../../Function/Admin/logout.php" class="btn btn-danger">
                Log Out
            </a>
        </div>
    </nav>

    <div class="categories" id="choice-container">

        <a href="#" id="partner-link" class="categoryLink">
            <div class="card category-card " style="width: 20rem; display: flex; flex-direction: column;">
                <img class="card-img-top" src="../../Assets/images/AdminImages/DisplayPartnershipImages/partners.jpg"
                    alt="Partners">

                <div class="category-body m-auto">
                    <h5 class="category-title m-auto">PARTNERS</h5>
                </div>
            </div>
        </a>

        <a href="#" id="request-link" class="categoryLink">
            <div class="card category-card " style="width: 20rem; display: flex; flex-direction: column;">
                <img class="card-img-top" src="../../Assets/images/AdminImages/DisplayPartnershipImages/request.jpg"
                    alt="Requests">

                <div class="category-body m-auto">
                    <h5 class="category-title m-auto">REQUESTS</h5>
                </div>
            </div>
        </a>

    </div>


    <!-- Display when Partner is Click -->
    <div class="partner-container" id="partner-container" style="display: none;">
        <!-- Partners Table  -->
        <div class="partnership-table">

            <div class="card" id="partner-card" style="width: 80rem;">

                <!-- Back Button -->
                <div>
                    <a href="#" id="choice1-link" class="btn btn-primary"><img
                            src="../../Assets/Images/Icon/whiteArrow.png" alt="Back Button"></a>

                </div>
                <h4 class="fw-bold page-title">Partners</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="table-header" scope="col">Name</th>
                            <th class="table-header" scope="col">Partner Type</th>
                            <th class="table-header" scope="col">Date Applied</th>
                            <th class="table-header" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <!-- Select to display all the applicants  -->
                        <?php
                        $pendingStatus = 1;
                        $rejectedStatus = 3;
                        $selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName, pt.partnerTypeDescription
                                FROM partnerships p
                                INNER JOIN users u ON p.userID = u.userID
                                INNER JOIN statuses s ON s.statusID = p.partnerStatus
                                LEFT JOIN partnershipTypes pt ON p.partnerTypeID = pt.partnerTypeID
                                WHERE partnerStatus != ? AND partnerStatus != ?
                                ");
                        $selectQuery->bind_param("ii", $pendingStatus, $rejectedStatus);
                        $selectQuery->execute();
                        $result = $selectQuery->get_result();
                        if ($result->num_rows > 0) {
                            foreach ($result as $applicants) {
                                $name = ucwords($applicants['firstName']) . " " . ucwords($applicants['lastName']);
                                $partnerID = $applicants['partnershipID'];
                                $status = $applicants['statusName'];
                                $date = $applicants['startDate'];
                                $startDate = date("F d, Y — g:i A", strtotime($date));
                        ?>
                                <tr>
                                    <td scope="row"><?= $name ?></td>

                                    <td scope="row"><?= ucfirst($applicants['partnerTypeDescription'])  ?></td>

                                    <td scope="row">
                                        <?= $startDate ?>
                                    </td>

                                    <td scope="row">
                                        <?php
                                        $partner = 3;
                                        // $partnerContainer = base64_encode($partner);
                                        ?>
                                        <form action="partnership.php?container=<?= $partner ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                                            <button type="submit" class="btn btn-info" name="view-btn">View</button>
                                        </form>
                                    </td>
                                    </td>
                                <?php
                            }
                        } else {
                                ?>
                                <td colspan="5">
                                    <h5 scope="row" class="text-center">No Record Found!</h5>
                                </td>
                            <?php
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Display when Request is Click -->
    <div class="request-container" id="request-container" style="display: none;">

        <!-- Partnership Request Table  -->
        <div class="partnership-request-table">

            <div class="card" id="request-card" style="width: 80rem;">
                <!-- Back Button -->
                <div class="buttonContainer">
                    <a href="#" id="choice2-link" class="btn btn-primary "><img
                            src="../../Assets/Images/Icon/whiteArrow.png" alt="Back Button"></a>

                </div>
                <h4 class="fw-bold page-title">Applicant Request</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="table-header" scope="col">Name</th>
                            <th class="table-header" scope="col">Partner Type</th>
                            <th class="table-header" scope="col">Request Date</th>
                            <th class="table-header" scope="col">Status</th>
                            <th class="table-header" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <!-- Select to display all the applicants  -->
                        <?php
                        $pendingStatus = 1;
                        $rejectedStatus = 3;
                        $selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName, pt.partnerTypeDescription
                                FROM partnerships p
                                INNER JOIN users u ON p.userID = u.userID
                                INNER JOIN statuses s ON s.statusID = p.partnerStatus
                                LEFT JOIN partnershipTypes pt ON p.partnerTypeID = pt.partnerTypeID
                                WHERE partnerStatus = ? OR partnerStatus = ?
                                ");
                        $selectQuery->bind_param("ii", $pendingStatus, $rejectedStatus);
                        $selectQuery->execute();
                        $result = $selectQuery->get_result();
                        if ($result->num_rows > 0) {
                            foreach ($result as $applicants) {
                                $name = ucwords($applicants['firstName']) . " " . ucwords($applicants['lastName']);
                                $partnerID = $applicants['partnershipID'];
                                $status = $applicants['statusName'];
                                $date = $applicants['requestDate'];
                                $requestDate = date("F d, Y — g:i A", strtotime($date));
                        ?>
                                <tr>
                                    <td scope="row"><?= $name ?></td>

                                    <td scope="row"><?= ucfirst($applicants['partnerTypeDescription'])  ?></td>
                                    <td scope="row"><?= htmlspecialchars($requestDate) ?></td>
                                    <?php
                                    if ($status == "Pending") {
                                    ?>
                                        <td scope="row" class="btn btn-warning w-75 d-block m-auto mt-1"
                                            style="background-color:#ffc108 ;">
                                            <?= $status ?>
                                        </td>
                                    <?php
                                    } else if ($status == "Rejected") {
                                    ?>
                                        <td scope="row" class="btn btn-danger w-75 d-block m-auto mt-1"
                                            style="background-color:#FF0000; color:#ffff ;">
                                            <?= $status ?>
                                        </td>
                                    <?php
                                    }
                                    ?>
                                    <td scope="row">
                                        <?php
                                        $applicant = 4;
                                        // $applicantContainer = base64_encode($applicant);
                                        ?>
                                        <form action="partnership.php?container=<?= $applicant ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                                            <button type="submit" class="btn btn-info w-75" name="view-partner">View</button>
                                        </form>

                                    </td>
                                    </td>
                                <?php
                            }
                        } else {
                                ?>
                                <td colspan="5">
                                    <h5 scope="row" class="text-center">No Record Found!</h5>
                                </td>
                            <?php
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Pages hide/show -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Script loaded");
            const requestLink = document.getElementById("request-link");
            const partnerLink = document.getElementById("partner-link");

            const choices = document.getElementById("choice-container");
            const choice1Link = document.getElementById("choice1-link");
            const choice2Link = document.getElementById("choice2-link");
            const partner_Container = document.getElementById("partner-container");
            const request_Container = document.getElementById("request-container");
            const partner_Card = document.getElementById("partner-card");
            const request_Card = document.getElementById("request-card");

            requestLink.addEventListener('click', function(event) {
                event.preventDefault();
                console.log("Request link clicked");
                choices.style.display = "none";
                partner_Container.style.display = "none";
                request_Container.style.display = "block";
                partner_Card.style.display = "none";
                request_Card.style.display = "block";
            });

            partnerLink.addEventListener('click', function(event) {
                event.preventDefault();
                console.log("Request link clicked");
                choices.style.display = "none";
                partner_Container.style.display = "block";
                request_Container.style.display = "none";
                partner_Card.style.display = "block";
                request_Card.style.display = "none";
            });

            choice1Link.addEventListener('click', function(event) {
                event.preventDefault();
                choices.style.display = "flex";
                partner_Container.style.display = "none";
                request_Container.style.display = "none";
                partner_Card.style.display = "none";
                request_Card.style.display = "none";
            });

            choice2Link.addEventListener('click', function(event) {
                event.preventDefault();
                choices.style.display = "flex";
                partner_Container.style.display = "none";
                request_Container.style.display = "none";
                partner_Card.style.display = "none";
                request_Card.style.display = "none";
            });
        });
    </script>

    <!-- Search URL -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('container');
        const action = params.get("action");

        const choices = document.getElementById("choice-container");
        const partnerContainer = document.getElementById("partner-container");
        const requestContainer = document.getElementById("request-container");
        const partnerCard = document.getElementById("partner-card");
        const requestCard = document.getElementById("request-card");

        if (paramValue == 1) {
            choices.style.display = "none";
            partnerContainer.style.display = "block";
            requestContainer.style.display = "none";
            partnerCard.style.display = "block";
            requestCard.style.display = "none";
        } else if (paramValue == 2) {
            choices.style.display = "none";
            partnerContainer.style.display = "none";
            requestContainer.style.display = "block";
            partnerCard.style.display = "none";
            requestCard.style.display = "block";
        }



        if (action === "approved") {
            Swal.fire({
                icon: 'success',
                title: 'Partnership Approved',
                text: 'The partnership request has been approved successfully.'
            });
        }

        if (paramValue || action) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <!-- Sweetalert Popup -->
    <script>
        <?php if (!empty($message)): ?>
            Swal.fire({
                icon: '<?= $status ?>',
                title: '<?= ($status == 'error') ? 'Rejected' : 'Success' ?>',
                text: '<?= $message ?>'
            });
        <?php endif; ?>
    </script>



</body>

</html>