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

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}


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

    <!-- Icons Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/transaction.css" />
    <link rel="stylesheet" href="../../Assets/CSS/Admin/navbar.css" />


</head>

<body>
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard">
                <img src="../../Assets/Images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <!-- Get notification -->
            <?php

            $receiver = 'Admin';
            $getNotifications = $conn->prepare("SELECT * FROM notification WHERE receiver = ? AND is_read = 0");
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
                $getProfile = $conn->prepare("SELECT firstName,userProfile FROM user WHERE userID = ? AND userRole = ?");
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

    <nav class="navbar navbar-expand-lg" id="navbar">
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav w-100 me-10 d-flex justify-content-around px-2" id="navUL">

                <li class="nav-item">
                    <a class="nav-link" href="adminDashboard.php">
                        <i class="fa-solid fa-grip navbar-icon"></i>
                        <h5>Dashboard</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="booking.php">
                        <i class="fa-solid fa-calendar-days navbar-icon"></i>
                        <h5>Bookings</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="reviews.php">
                        <i class="fa-solid fa-star navbar-icon"></i>
                        <h5>Reviews</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="services.php">
                        <i class="fa-solid fa-bell-concierge navbar-icon"></i>
                        <h5>Services</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="transaction.php">
                        <i class="fa-solid fa-credit-card navbar-icon"></i>
                        <h5>Payments</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="revenue.php">
                        <i class="fa-solid fa-money-bill-trend-up navbar-icon"></i>
                        <h5>Revenue</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="displayPartnership.php">
                        <i class="fa-solid fa-handshake navbar-icon"></i>
                        <h5>Partnerships</h5>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="editWebsite/editWebsite.php">
                        <i class="fa-solid fa-pen-to-square navbar-icon"></i>
                        <h5>Edit Website</h5>
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <a href="../../Function/Admin/logout.php" class="nav-link">
                        <i class="fa-solid fa-right-from-bracket navbar-icon" style="color: #db3545;"></i>
                        <h5 style="color: red;">Log Out</h5>
                    </a>
                </li>
            </ul>
        </div>
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
                                    <?php echo $message ?>
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
            <table class="table table-striped" id="transactionTable">
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
                    $payments = $conn->prepare("SELECT LPAD(cb.bookingID, 4, '0') AS formattedID, cb.*, b.userID, b.bookingID, u.firstName, u.lastName, b.paymentMethod, bps.statusName as PaymentStatus, stat.statusName AS paymentApprovalStatus
                    FROM confirmedbooking cb
                    LEFT JOIN booking b ON cb.bookingID = b.bookingID
                    LEFT JOIN user u ON b.userID = u.userID
                    LEFT JOIN bookingpaymentstatus bps ON cb.paymentStatus = bps.paymentStatusID
                    LEFT JOIN status stat ON cb.paymentApprovalStatus = stat.statusID
                    ");
                    $payments->execute();
                    $paymentsResult = $payments->get_result();
                    if ($paymentsResult->num_rows > 0) {
                        while ($data = $paymentsResult->fetch_assoc()) {
                            $guestName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
                            $bookingID = $data['bookingID'];
                            $formattedID = $data['formattedID'];
                            $totalAmount = $data['confirmedFinalBill'];
                            // $downpayment = $data['CBdownpayment'];
                            $amountPaid = $data['amountPaid'];
                            $balance = $data['userBalance'];
                            $paymentMethod = $data['paymentMethod'];
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
                                $addClass = "btn btn-primary w-100";
                            } elseif ($paymentApprovalStatus === "Rejected") {
                                $addClass = "btn btn-danger w-100";
                            } elseif ($paymentApprovalStatus === "Done") {
                                $addClass = "btn btn-success w-100";
                            }


                    ?>
                            <tr>
                                <td><?= htmlspecialchars($formattedID) ?></td>
                                <td><?= htmlspecialchars($guestName) ?></td>
                                <td>₱ <?= number_format($totalAmount, 2) ?></td>
                                <td>₱ <?= number_format($balance, 2) ?></td>
                                <td><?= htmlspecialchars($paymentMethod) ?></td>
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