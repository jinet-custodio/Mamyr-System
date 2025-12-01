<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();
//for setting image paths in 'include' statements
$baseURL = '../..';

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../../register.php");
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
        header("Location: ../../../register.php");
        exit();
    }
}

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

$getUserData = $conn->prepare("SELECT firstName, lastName, userProfile FROM user WHERE userID = ?");
$getUserData->bind_param('i', $userID);
if (!$getUserData->execute()) {
    error_log('Failed getting user data: userID' . $userID);
}

$result = $getUserData->get_result();
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $adminName = ($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '');
    $profile = $data['userProfile'];
    if (!empty($profile)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
    }
} else {
    $_SESSION['error'] = "Unauthorized Access eh!";
    session_destroy();
    header("Location: ../register.php");
    exit();
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
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Box Icons and Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div id="sidebar" class=" sidebar show sidebar-custom">
        <div class="sbToggle-container d-flex justify-content-center" id="sidebar-toggle">
            <button class="toggle-button" type="button" id="toggle-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>
        </div>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo"
            id="sbLogo">
        <ul class="nav flex-column">
            <li class="nav-item" id="navLI" title="Dashboard">
                <a class="nav-link" href="adminDashboard.php">
                    <i class="bi bi-speedometer2"></i> <span class="linkText">Dashboard</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title=" Bookings">
                <a class="nav-link" href="booking.php">
                    <i class="bi bi-calendar-week"></i><span class="linkText"> Bookings</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Payments">
                <a class="nav-link" href="transaction.php">
                    <i class="bi bi-credit-card-2-front"></i> <span class="linkText">Payments</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Schedule">
                <a class="nav-link" href="schedule.php">
                    <i class="bi bi-calendar-date"></i><span class="linkText">Schedule</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Rooms">
                <a class="nav-link" href="roomList.php">
                    <i class="bi bi-door-open"></i> <span class="linkText">Rooms</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Services">
                <a class="nav-link" href="services.php">
                    <i class="bi bi-bell"></i> <span class="linkText">Services</span>
                </a>
            </li>
            <li class="nav-item active" id="navLI" title="Partnerships">
                <a class="nav-link" href="displayPartnership.php">
                    <i class="bi bi-people"></i> <span class="linkText">Partnerships</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Reviews">
                <a class="nav-link" href="reviews.php">
                    <i class="bi bi-list-stars"></i> <span class="linkText">Reviews</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Edit Website">
                <a class="nav-link" href="editWebsite/editWebsite.php">
                    <i class="bi bi-pencil-square"></i> <span class="linkText">Edit Website</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Audit Logs">
                <a class="nav-link" href="auditLogs.php">
                    <i class="bi bi-clock-history"></i> <span class="linkText">Audit Logs</span>
                </a>
            </li>
        </ul>

        <section>
            <a href="../Account/account.php" class="profileContainer" id="pfpContainer">
                <img src="<?= $image ?>" alt="Admin Profile"
                    class="rounded-circle profilePic">
                <h5 class="admin-name" id="adminName"><?= htmlspecialchars($adminName) ?></h5>
            </a>
        </section>

        <section class="btn btn-outline-danger logOutContainer">
            <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                <h5 class="logoutText">Log Out</h5>
            </a>
        </section>
    </div>
    <main>
        <!-- Booking-container -->
        <section class="booking-container">
            <section class="notification-toggler-container">
                <div class="notification-container position-relative">
                    <button type="button" class="btn position-relative" data-bs-toggle="modal"
                        data-bs-target="#notificationModal" id="notificationButton">
                        <i class="bi bi-bell" id="notification-icon"></i>
                    </button>
                </div>

                <div class="hidden-inputs" style="display: none;">
                    <input type="hidden" id="receiver" value="<?= $role ?>">
                    <input type="hidden" id="userID" value="<?= $userID ?>">
                </div>
            </section>
            <section class="page-title-container">
                <nav aria-label="breadcrumb" id="choice-container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active page-title" id="partner-link-li"><a href="#" id="partner-link">Partnerships</a>
                        </li>
                        <li class="breadcrumb-item page-title" aria-current="page" id="request-link-li"><a href="#" id="request-link">
                                Partnership Requests</li></a>
                    </ol>
                </nav>
            </section>


            <!-- Display when Partner is Click -->
            <div class="partner-container" id="partner-container">
                <!-- Partners Table  -->
                <div class="partnership-table">

                    <div class="card" id="partner-card">
                        <table class="table table-striped display nowrap" id="partnerTable">
                            <thead>
                                <tr>
                                    <th class="table-header" scope="col">ID</th>
                                    <th class="table-header wrap-date" scope="col">Name</th>
                                    <th class="table-header" scope="col">Partner Type</th>
                                    <th class="table-header wrap-date" scope="col">Date Applied</th>
                                    <th class="table-header" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-body" id="partners-table-body">
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            <!-- Display when Request is Click -->
            <div class="request-container" id="request-container" style="display: none;">

                <!-- Partnership Request Table  -->
                <div class="partnership-request-table">

                    <div class="card" id="request-card">
                        <table class="table table-striped display nowrap" id="requestTable">
                            <thead>
                                <tr>
                                    <th class="table-header" scope="col">ID</th>
                                    <th class="table-header" scope="col">Name</th>
                                    <th class="table-header" scope="col">Partner Type</th>
                                    <th class="table-header wrap-date" scope="col">Request Date</th>
                                    <th class="table-header" scope="col">Status</th>
                                    <th class="table-header" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-body" id="requests-table-body">
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../Notification/notification.php' ?>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Table JS -->
    <script>
        let partnersTable;
        let requestsTable;
        $(document).ready(function() {
            partnersTable = $('#partnerTable').DataTable({
                responsive: false,
                scrollX: true,
                columnDefs: [{
                        width: '5%',
                        targets: 0
                    },
                    {
                        width: '25%',
                        targets: 1
                    },
                    {
                        width: '25%',
                        targets: 2
                    },
                    {
                        width: '25%',
                        targets: 3
                    },
                    {
                        width: '20%',
                        targets: 4
                    },
                ],
                destroy: true
            });

            requestsTable = $('#requestTable').DataTable({
                responsive: false,
                scrollX: true,
                columnDefs: [{
                        width: '5%',
                        targets: 0
                    }, {
                        width: '20%',
                        targets: 1
                    },
                    {
                        width: '20%',
                        targets: 2
                    },
                    {
                        width: '20%',
                        targets: 3
                    },
                    {
                        width: '20%',
                        targets: 4
                    },
                    {
                        width: '15%',
                        targets: 5
                    }
                ],
                destroy: true
            });
            loadPartners();
            document.getElementById("partner-link").addEventListener("click", function() {
                loadPartners();
            });

            document.getElementById("request-link").addEventListener("click", function() {
                loadRequests();
            });
        });


        function loadPartners() {
            fetch('../../Function/Admin/Partnership/getPartner.php')
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Server Error',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        return;
                    }

                    partnersTable.clear();

                    Object.values(data.partners).forEach(p => {
                        partnersTable.row.add([
                            p.partnershipID,
                            p.name,
                            p.types.join(' & '),
                            p.startDate,
                            `<form action="partnership.php?container=3" method="POST">
                        <input type="hidden" name="partnerID" value="${p.partnershipID}">
                        <button type="submit" class="btn btn-info" name='view-btn'>View</button>
                    </form>`
                        ]);
                    });

                    partnersTable.draw();
                })
                .catch(err => console.log(err));
        }

        function loadRequests() {
            fetch('../../Function/Admin/Partnership/getApplicant.php')
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Server Error',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        return;
                    }

                    requestsTable.clear();

                    Object.values(data.applicants).forEach(a => {
                        requestsTable.row.add([
                            a.partnershipID,
                            a.name,
                            a.types.join(' & '),
                            a.requestDate,
                            `<span class="badge ${a.class}">${a.status}</span>`,
                            `<form action="partnership.php?container=4" method="POST">
                        <input type="hidden" name="partnerID" value="${a.partnershipID}">
                        <button type="submit" class="btn btn-info" name="view-btn">View</button>
                    </form>`
                        ]);
                    });

                    requestsTable.draw();
                })
                .catch(err => console.log(err));
        }



        // if (paramValue == 1) loadPartners();
        // else if (paramValue == 2) loadRequests();
    </script>

    <!-- Pages hide/show -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const requestLink = document.getElementById("request-link");
            const partnerLink = document.getElementById("partner-link");
            // const choices = document.getElementById("choice-container");
            // const choice1Link = document.getElementById("choice1-link");
            const partner_Container = document.getElementById("partner-container");
            const request_Container = document.getElementById("request-container");
            const partner_Card = document.getElementById("partner-card");
            const request_Card = document.getElementById("request-card");

            requestLink.addEventListener('click', function(event) {
                event.preventDefault();
                console.log("Request link clicked");
                partnerLink.style.color = "#6d6e72ff";
                requestLink.style.color = "#0d6dfc";
                partner_Container.style.display = "none";
                request_Container.style.display = "block";
                partner_Card.style.display = "none";
                request_Card.style.display = "block";
                loadRequests();
            });

            partnerLink.addEventListener('click', function(event) {
                event.preventDefault();
                console.log("Partner link clicked");
                requestLink.style.color = "#6d6e72ff";
                partnerLink.style.color = "#0d6dfc";
                partner_Container.style.display = "block";
                request_Container.style.display = "none";
                partner_Card.style.display = "block";
                request_Card.style.display = "none";

                loadPartners();
            });

        });

        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('container');
        const action = params.get("action");

        // const choices = document.getElementById("choice-container");
        const partnerContainer = document.getElementById("partner-container");
        const requestContainer = document.getElementById("request-container");
        const partnerCard = document.getElementById("partner-card");
        const requestCard = document.getElementById("request-card");
        const partnerLink = document.getElementById('partner-link-li');
        const requestLink = document.getElementById('request-link-li');
        switch (paramValue) {
            case '1': // partners
                partnerContainer.style.display = "block";
                requestContainer.style.display = "none";
                partnerCard.style.display = "block";
                requestCard.style.display = "none";
                partnerLink.classList.add('active');
                requestLink.classList.remove('active');
                loadPartners();
                break;

            case '2': // requests
                partnerContainer.style.display = "none";
                requestContainer.style.display = "block";
                partnerCard.style.display = "none";
                requestCard.style.display = "block";
                partnerLink.classList.remove('active');
                requestLink.classList.add('active');
                loadRequests();
                break;

            case '3': // view page
                partnerContainer.style.display = "none";
                requestContainer.style.display = "none";
                partnerCard.style.display = "none";
                requestCard.style.display = "none";
                // No table loading here
                break;

            default:
                // default to partners table
                partnerContainer.style.display = "block";
                requestContainer.style.display = "none";
                partnerCard.style.display = "block";
                requestCard.style.display = "none";
                partnerLink.classList.add('active');
                requestLink.classList.remove('active');
                loadPartners();
        }


        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        if (action === "approved") {
            Toast.fire({
                icon: "success",
                title: "Partnership Approved Successfully"
            });
            // Swal.fire({
            //     icon: 'success',
            //     title: 'Partnership Approved',
            //     text: 'The partnership request has been approved successfully.'
            // });
        } else if (action === 'rejected') {
            Toast.fire({
                icon: "success",
                title: "Partnership Rejected Successfully"
            });
            // Swal.fire({
            //     icon: 'success',
            //     title: 'Partnership Rejected',
            //     text: 'The partnership request has been rejected successfully.'
            // });
        }

        if (action || paramValue) {
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
    <?php include '../Customer/loader.php'; ?>
</body>

</html>