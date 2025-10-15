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
require '../../Function/notification.php';
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
    <main>
        <div id="sidebar" class="collapse show sidebar-custom">
            <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo"
                id="sbLogo">
            <ul class="nav flex-column">
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="adminDashboard.php">
                        <i class="bi bi-speedometer2"></i> <span id="linkText">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="booking.php">
                        <i class="bi bi-calendar-week"></i><span id="linkText"> Bookings</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="reviews.php">
                        <i class="bi bi-list-stars"></i> <span id="linkText">Reviews</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="roomList.php">
                        <i class="bi bi-door-open"></i> <span id="linkText">Rooms</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="services.php">
                        <i class="bi bi-bell"></i> <span id="linkText">Services</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="transaction.php">
                        <i class="bi bi-credit-card-2-front"></i> <span id="linkText">Payments</span>
                    </a>
                </li>
                <li class="nav-item active" id="navLI">
                    <a class="nav-link" href="displayPartnership.php">
                        <i class="bi bi-people"></i> <span id="linkText">Partnerships</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="editWebsite/editWebsite.php">
                        <i class="bi bi-pencil-square"></i> <span id="linkText">Edit Website</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="auditLogs.php">
                        <i class="bi bi-clock-history"></i> <span id="linkText">Audit Logs</span>
                    </a>
                </li>
            </ul>


            <section>
                <a href="../Account/account.php" class="profileContainer" id="pfpContainer">
                    <img src=" ../../Assets/Images/defaultProfile.png" alt="Admin Profile"
                        class="rounded-circle profilePic">
                    <h5 class="admin-name" id="adminName">Diane Dela Cruz</h5>
                </a>
            </section>

            <section class="btn btn-outline-danger logOutContainer">
                <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <h5>Log Out</h5>
                </a>
            </section>
        </div>
        <!-- Booking-container -->
        <section class="booking-container">
            <section class="notification-toggler-container">
                <div class="sbToggle-container">
                    <button class="toggle-button" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar"
                        aria-controls="sidebar">
                        <i class="bi bi-layout-sidebar"></i>
                    </button>
                </div>
                <div class="notification-container position-relative">
                    <button type="button" class="btn position-relative" data-bs-toggle="modal"
                        data-bs-target="#notificationModal">
                        <i class="bi bi-bell" id="notification-icon"></i>
                        <?php if (!empty($counter)): ?>
                        <?= htmlspecialchars($counter) ?>
                        </span>
                        <?php endif; ?>
                    </button>
                </div>
            </section>
            <section class="page-title-container">
                <nav aria-label="breadcrumb" id="choice-container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active page-title"><a href="#" id="partner-link">Partnerships</a>
                        </li>
                        <li class="breadcrumb-item page-title" aria-current="page"><a href="#" id="request-link">
                                Partnership Requests</li></a>
                    </ol>
                </nav>
            </section>


            <!-- Display when Partner is Click -->
            <div class="partner-container" id="partner-container">
                <!-- Partners Table  -->
                <div class=" partnership-table">

                    <div class="card" id="partner-card">
                        <table class="table table-striped display nowrap" id="partnersTable">
                            <thead>
                                <tr>
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

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Notification Ajax -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const badge = document.querySelector('.notification-container .badge');

        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationID = this.dataset.id;

                fetch('../../Function/notificationFunction.php', {
                        method: 'POST',
                        headers: {
                            'Content-type': 'application/x-www-form-urlencoded'
                        },
                        body: 'notificationID=' + encodeURIComponent(notificationID)
                    })
                    .then(response => response.text())
                    .then(data => {

                        this.style.transition = 'background-color 0.3s ease';
                        this.style.backgroundColor = 'white';


                        if (badge) {
                            let currentCount = parseInt(badge.textContent, 10);

                            if (currentCount > 1) {
                                badge.textContent = currentCount - 1;
                            } else {
                                badge.remove();
                            }
                        }
                    });
            });
        });
    });
    </script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $('#requestTable').DataTable({
        responsive: false,
        scrollX: true,
        columnDefs: [{
                width: '20%',
                targets: 0
            },
            {
                width: '20%',
                targets: 1
            },
            {
                width: '25%',
                targets: 2
            },
            {
                width: '20%',
                targets: 3
            },
            {
                width: '15%',
                targets: 4
            },

        ],
    });

    $('#partnersTable').DataTable({
        responsive: false,
        scrollX: true,
        columnDefs: [{
                width: '25%',
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
            }
        ],
    });
    </script>


    <!-- Ajax fort request adn partner -->
    <script>
    function loadPartners() {
        const table = $('#partnersTable');
        const tableBody = document.getElementById("partners-table-body");
        tableBody.innerHTML = "<tr><td colspan='4' class='text-center'>Loading...</td></tr>";

        fetch('../../Function/Admin/Partnership/getPartner.php')
            .then(res => res.text())
            .then(html => {

                tableBody.innerHTML = html;

                table.DataTable();
            }).catch(err => {
                console.log(err);
            });
    }


    function loadRequests() {
        const table = $('#requestTable');
        const tableBody = document.getElementById("requests-table-body");
        tableBody.innerHTML = "<tr><td colspan='5' class='text-center'>Loading...</td></tr>";

        fetch('../../Function/Admin/Partnership/getApplicant.php')
            .then(res => res.text())
            .then(html => {
                tableBody.innerHTML = html;

                table.DataTable();
            }).catch(err => {
                console.log(err);
            });
    }

    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("partner-link").addEventListener("click", function() {
            loadPartners();
        });

        document.getElementById("request-link").addEventListener("click", function() {
            loadRequests();
        });

        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('container');

        if (paramValue == 1) loadPartners();
        else if (paramValue == 2) loadRequests();
    });
    </script>

    <!-- Pages hide/show -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
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
    <script src="../../Assets/JS/adminNavbar.js"></script>

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

        loadPartners()
    } else if (paramValue == 2) {
        choices.style.display = "none";
        partnerContainer.style.display = "none";
        requestContainer.style.display = "block";
        partnerCard.style.display = "none";
        requestCard.style.display = "block";

        loadRequests();
    }

    if (action === "approved") {
        Swal.fire({
            icon: 'success',
            title: 'Partnership Approved',
            text: 'The partnership request has been approved successfully.'
        });
    } else if (action === 'rejected') {
        Swal.fire({
            icon: 'success',
            title: 'Partnership Rejected',
            text: 'The partnership request has been rejected successfully.'
        });
    }

    if (action) {
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