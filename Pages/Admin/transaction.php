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

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Icons Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/transaction.css" />
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css" />
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
            <li class="nav-item active" id="navLI" title="Payments">
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
                <h5>Log Out</h5>
            </a>
        </section>
    </div>
    <main>
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
                <h5 class="page-title">Payments</h5>
            </section>


            <div class="transactionContainer">
                <div class="card" id="tableContainer">

                    <table class="table table-striped display nowrap" id="transactionTable">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Code</th>
                                <th scope="col">Guest</th>
                                <th scope="col">Total</th>
                                <th scope="col">Payment</th>
                                <th scope="col">Balance</th>
                                <th scope="col">Payment Method</th>
                                <th scope="col">Status</th>
                                <!-- <th scope="col">Status</th> -->
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <!-- Get data and isplay Transaction -->
                        <tbody id="payment-display-body"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <!-- Notification Modal -->
    <?php include '../Notification/notification.php' ?>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Bootstrap Link -->
    <script src=" ../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#transactionTable').DataTable({
                scrollX: true,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                        width: '5%',
                        target: 0,
                    },
                    {
                        width: '15%',
                        target: 1,
                    },
                    {
                        width: '15%',
                        target: 2,
                    },
                    {
                        width: '10%',
                        target: 4,
                    },
                    {
                        width: '15%',
                        target: 5,
                    },
                    {
                        width: '15%',
                        target: 6,
                    },
                    {
                        width: '10%',
                        target: 7,
                    }
                ]
            });
        });
    </script>
    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>

    <script>
        function getStatusBadge(colorClass, status) {
            return `<span class="badge bg-${colorClass} text-capitalize">${status}</span>`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            fetch("../../Function/Admin/Ajax/getPaymentJSON.php")
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // console.error("Failed to load payments.");
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An unknown error occurred.'
                        });
                        return;
                    }
                    const payments = data.payments;
                    const table = $('#transactionTable').DataTable();
                    table.clear();
                    // console.log(payments);

                    payments.forEach(payment => {
                        // const row = document.createElement("tr");
                        table.row.add([
                            payment.bookingID,
                            payment.bookingCode,
                            payment.name,
                            payment.totalBill,
                            payment.paymentAmount,
                            payment.userBalance,
                            payment.paymentMethod,
                            getStatusBadge(payment.statusClass, payment.status),
                            // getStatusBadge(payment.paymentClass, payment.paymentStatusName),
                            ` <form action = "viewPayments.php"
                                        method = "POST" >
                                            <input type = "hidden"
                                        name = "button"
                                        value = "booking" >
                                            <input type = "hidden"
                                        name = "bookingType"
                                        value = "${payment.bookingType}" >
                                            <input type = "hidden"
                                        name="bookingStatus"
                                        value="${payment.bookingStatus}">
                                            <input type ="hidden"
                                        name="bookingID"
                                        value="${payment.bookingID}">
                                            <button type="submit" class="btn btn-primary viewBtn"> View </button> </form>`
                        ]);
                    });
                    table.draw();
                }).catch(error => {
                    console.error("Error loading bookings:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'Failed to load data from the server.'
                    })
                })
        })
    </script>

    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');
        if (paramValue === "approved") {
            Swal.fire({
                title: "Payment Approved",
                text: "You have successfully reviewed the payment. The booked service is now reserved for the customer.",
                icon: 'success',
            });
        } else if (paramValue === "rejected") {
            Swal.fire({
                title: "Payment Rejected",
                text: "You have reviewed and rejected the payment.",
                icon: 'success',
            });
        } else if (paramValue === "failed") {
            Swal.fire({
                title: "Payment Approval Failed",
                text: "Unable to approve or reject the payment. Please try again later.",
                icon: 'error',
            });
        } else if (paramValue === "paymentSuccess") {
            Swal.fire({
                title: "Payment Added",
                text: "Payment was successfully added and processed.",
                icon: 'success',
            });
        } else if (paramValue === "paymentFailed") {
            Swal.fire({
                title: "Payment Failed",
                text: "Failed to deduct the payment. Please try again later.",
                icon: 'error',
            });
        }

        if (paramValue) {
            const url = new URL(window.location.href);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <?php include '../Customer/loader.php'; ?>
</body>

</html>