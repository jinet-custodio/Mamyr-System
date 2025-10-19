<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

require_once '../../Function/Helpers/statusFunctions.php';
changeToDoneStatus($conn);
changeToExpiredStatus($conn);

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

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';

$message = '';
$status = '';

if (isset($_SESSION['error'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['error']));
    $status = 'error';
    unset($_SESSION['error']);
} elseif (isset($_SESSION['success'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['success']));
    $status = 'success';
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Links -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/auditLogs.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- CSS Links -->
    <!-- Bootstrap Links -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <!-- Bootstrap Links -->
    <!-- Icon Links -->
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Icon Links -->
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
            <li class="nav-item" id="navLI" title="Payments">
                <a class="nav-link" href="transaction.php">
                    <i class="bi bi-credit-card-2-front"></i> <span class="linkText">Payments</span>
                </a>
            </li>
            <li class="nav-item" id="navLI" title="Partnerships">
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
            <li class="nav-item active" id="navLI" title="Audit Logs">
                <a class="nav-link" href="auditLogs.php">
                    <i class="bi bi-clock-history"></i> <span class="linkText">Audit Logs</span>
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
                <h5 class="logoutText">Log Out</h5>
            </a>
        </section>
    </div>
    <main>
        <section class="auditLog-container">
            <section class="notification-toggler-container">
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
                <h5 class="page-title">Audit Logs</h5>
            </section>

            <div class="auditLog-table">
                <div class="card">

                    <table class="table table-striped display nowrap" id="auditLogTable">
                        <thead>
                            <th scope="col">Log ID</th>
                            <th scope="col">Admin Id</th>
                            <th scope="col">Action</th>
                            <th scope="col">Target</th>
                            <th scope="col">Details</th>
                            <th scope="col">Time Stamp</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-warning text-capitalize">Update</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-success text-capitalize">Create</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-danger text-capitalize">Delete</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-info text-capitalize">Approved</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-red text-capitalize">Rejected</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>



        </section>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>

    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#auditLogTable').DataTable({
                "ajax": "../../Function/Admin/Ajax/getAuditLogs.php",
                "columns": [{
                        "data": "logID",
                        "render": function(data, type, row) {
                            return data.toString().padStart(4, '0');
                        }
                    },
                    {
                        "data": "adminID"
                    },
                    {
                        "data": "action",
                        "render": function(data, type, row) {
                            let badgeClass = '';
                            switch (data.toLowerCase()) {
                                case 'create':
                                    badgeClass = 'bg-success';
                                    break;
                                case 'update':
                                    badgeClass = 'bg-warning';
                                    break;
                                case 'delete':
                                    badgeClass = 'bg-danger';
                                    break;
                                case 'approved':
                                    badgeClass = 'bg-info';
                                    break;
                                case 'rejected':
                                    badgeClass = 'bg-danger'; // or bg-red if custom class exists
                                    break;
                                default:
                                    badgeClass = 'bg-secondary';
                            }
                            return `<span class="badge ${badgeClass} text-capitalize">${data}</span>`;
                        }
                    },
                    {
                        "data": "target"
                    },
                    {
                        "data": "logDetails"
                    },
                    {
                        "data": "timestamp"
                    }
                ],
                responsive: true
            });
        });
    </script>


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


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->

</body>

</html>