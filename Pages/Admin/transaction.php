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


// $customerPayment = $_SESSION['huh']
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/transaction.css" />

</head>

<body>
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard">
                <img src="../../Assets/images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <!-- Get notification -->
            <?php

            $receiver = 'Admin';
            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE receiver = ? AND is_read = 0");
            $getNotifications->bind_param("s", $receiver);
            $getNotifications->execute();
            $getNotificationsResult = $getNotifications->get_result();
            if ($getNotificationsResult->num_rows > 0) {
                $counter = 0;
                $notificationsArray = [];
                $color = [];
                $notificationIDs = [];
                while ($notifications = $getNotificationsResult->fetch_assoc()) {
                    $is_readValue = $notifications['is_read'];
                    $notificationIDs[] = $notifications['notificationID'];
                    if ($is_readValue === 0) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "rgb(247, 213, 176, .5)";
                    } elseif ($is_readValue === 1) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "white";
                    }
                }
            }
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= htmlspecialchars($counter) ?>
                    </span>
                    <?php endif; ?>
                </button>
            </div>

            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
            </a>
            <?php
            if ($userRole == 3) {
                $admin = "Admin";
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }

            if ($admin === "Admin") {
                $getProfile = $conn->prepare("SELECT firstName,userProfile FROM users WHERE userID = ? AND userRole = ?");
                $getProfile->bind_param("ii", $userID, $userRole);
                $getProfile->execute();
                $getProfileResult = $getProfile->get_result();
                if ($getProfileResult->num_rows > 0) {
                    $data = $getProfileResult->fetch_assoc();
                    $firstName = $data['firstName'];
                    $imageData = $data['userProfile'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $imageData);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }
            ?>
            <h5 class="adminTitle"><?= ucfirst($firstName) ?></h5>
            <a href="../Account/account.php" class="admin">
                <img src="<?= htmlspecialchars($image) ?>" alt="home icon">
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

        <a class="nav-link" href="services.php">
            <img src="../../Assets/Images/Icon/servicesAdminNav.png" alt="Services">
            <h5>Services</h5>
        </a>

        <a class="nav-link active" href="#">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <a class="nav-link" href="revenue.php">
            <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
            <h5>Revenue</h5>
        </a>

        <a class="nav-link" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="editWebsite/landingPageEdit.php">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

    </nav>


    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <?php if (!empty($notificationsArray)): ?>
                    <ul class="list-group list-group-flush ">
                        <?php foreach ($notificationsArray as $index => $message):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                        <li class="list-group-item mb-2 notification-item"
                            data-id="<?= htmlspecialchars($notificationID) ?>"
                            style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                            <?= htmlspecialchars($message) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="p-3 text-muted">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="transactionContainer">
        <div class="card" style="width: 80rem;">
            <div class="titleContainer">
                <h3 class="title">Transaction</h3>
            </div>
            <table class=" table table-striped" id="transactionTable">
                <thead>
                    <th scope="col">Booking ID</th>
                    <th scope="col">Guest</th>
                    <th scope="col">Total Payment</th>
                    <!-- <th scope="col">Downpayment (30%)</th> -->
                    <!-- <th scope="col">Amount Paid</th> -->
                    <th scope="col">Balance</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Payment Approval</th>
                    <th scope="col">Payment Status</th>
                    <th scope="col">Action</th>
                </thead>
                <!-- Get data and isplay Transaction -->
                <tbody>
                    <?php
                    $payments = $conn->prepare("SELECT LPAD(cb.bookingID, 4, '0') AS formattedID, cb.*, b.userID, b.bookingID, u.firstName, u.lastName, bps.statusName as PaymentStatus, stat.statusName AS paymentApprovalStatus
                    FROM confirmedBookings cb
                    LEFT JOIN bookings b ON cb.bookingID = b.bookingID
                    LEFT JOIN users u ON b.userID = u.userID
                    LEFT JOIN bookingPaymentStatus bps ON cb.paymentStatus = bps.paymentStatusID
                    LEFT JOIN statuses stat ON cb.confirmedBookingStatus = stat.statusID
                    ");
                    $payments->execute();
                    $paymentsResult = $payments->get_result();
                    if ($paymentsResult->num_rows > 0) {
                        while ($data = $paymentsResult->fetch_assoc()) {
                            $guestName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
                            $bookingID = $data['bookingID'];
                            $formattedID = $data['formattedID'];
                            $totalAmount = $data['CBtotalCost'];
                            $downpayment = $data['CBdownpayment'];
                            $amountPaid = $data['amountPaid'];
                            $balance = $data['userBalance'];
                            $CBPaymentMethod = $data['CBpaymentMethod'];
                            $paymentStatus = $data['PaymentStatus'];
                            $paymentApprovalStatus = $data['paymentApprovalStatus'];

                            if ($paymentStatus === 'Fully Paid') {
                                $classColor = 'success';
                            } elseif ($paymentStatus === 'No Payment') {
                                $classColor = 'danger';
                            } elseif ($paymentStatus === 'Partially Paid') {
                                $classColor = 'primary';
                            }



                            if ($paymentApprovalStatus === "Pending") {
                                $addClass = "btn btn-warning w-100";
                            } elseif ($paymentApprovalStatus === "Approved") {

                                $addClass = "btn btn-success w-100";
                            } elseif ($paymentApprovalStatus === "Rejected") {
                                $addClass = "btn btn-danger w-100";
                            }


                    ?>
                    <tr>
                        <td><?= htmlspecialchars($formattedID) ?></td>
                        <td><?= htmlspecialchars($guestName) ?></td>
                        <td>₱ <?= number_format($totalAmount, 2) ?></td>
                        <!-- <td>₱ <?= number_format($downpayment, 2) ?></td> -->
                        <!-- <td>₱ <?= number_format($amountPaid, 2) ?></td> -->
                        <td>₱ <?= number_format($balance, 2) ?></td>
                        <td><?= htmlspecialchars($CBPaymentMethod) ?></td>
                        <td><span class="<?= $addClass ?>"><?= htmlspecialchars($paymentApprovalStatus) ?></span></td>
                        <td><span
                                class="btn btn-<?= $classColor ?> w-100"><?= htmlspecialchars($paymentStatus) ?></span>
                        </td>

                        <td>
                            <form action="viewPayments.php" method="POST">
                                <input type="hidden" name="bookingID" id="bookingID" value="<?= $bookingID ?>">
                                <button type="submit" name="viewIndividualPayment"
                                    class="btn btn-info w-100">View</button>
                            </form>
                        </td>
                    </tr>

                    <?php
                        }
                    }

                    ?>

                    <!-- <tr>
                        <td>Alliah Reyes</td>
                        <td>3,500 Php</td>
                        <td>3,500 Php</td>
                        <td><span class="btn btn-danger w-75" id="unpaidStatus">No Payment</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Shan Ignacio</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-primary w-75" id="partiallyPaidStatus">Partially Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Jeanette Custodio</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-success w-75" id="fullyPaidStatus">Fully Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Jannine Correa</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-success w-75" id="fullyPaidStatus">Fully Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr> -->
                </tbody>
            </table>
        </div>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
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
    });
    </script>


    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#transactionTable').DataTable({
            columnDefs: [{
                    width: '9%',
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

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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


</body>

</html>