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

require_once '../../Function/functions.php';
//Changing Status function to ah galing sa file na functions.php
changeToExpiredStatus($conn);
changeToDoneStatus($conn);

if ($userRole == 1) {
    $role = "Customer";
} elseif ($userRole == 2) {
    $role = "Business Partner";
} elseif ($userRole == 3) {
    $role = "Admin";
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
    <title>Booking History - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/bookingHistory.css" />
    <!-- DataTables Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css" />
    <!-- Font Awesome Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="wrapper d-flex">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header text-center">
                <div class="d-flex" id="toggle-container">
                    <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </button>
                </div>
                <div class="home">
                    <?php if ($role === 'Customer') { ?>
                        <a href="../Customer/dashboard.php">
                            <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                        </a>
                    <?php } elseif ($role === 'Admin') { ?>
                        <a href="../Admin/adminDashboard.php">
                            <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                        </a>
                    <?php } elseif ($role === 'Business Partner') { ?>
                        <a href="../Customer/dashboard.php">
                            <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                        </a>
                    <?php } ?>
                </div>

                <h5 class="sidebar-text">User Account</h5>

                <?php
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
                ?>
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>" alt=" <?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="../BusinessPartner/bpDashboard.php" class="list-group-item">
                            <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                            <span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="account.php" class="list-group-item">
                        <i class="fa-regular fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>


                <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                    <li>
                        <a href="bookingHistory.php" class="list-group-item active" id="paymentBookingHist">
                            <i class="fa-solid fa-table-list sidebar-icon"></i>
                            <span class="sidebar-text">Payment & Booking History</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li>
                        <a href="userManagement.php" class="list-group-item">
                            <i class="fa-solid fa-people-roof sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="../BusinessPartner/bpBookings.php" class="list-group-item">
                            <i class="fa-regular fa-calendar-days sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="../BusinessPartner/bpServices.php" class="list-group-item">
                            <i class="fa-solid fa-bell-concierge sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="list-group-item">
                            <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                            <span class="sidebar-text">Revenue</span>
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li>
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="fa-solid fa-user-slash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
                <li>
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn" style="margin: 3vw auto;">
                        <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside>
        <main class="main-content" id="main-content">
            <div class="bookingHistContainer">

                <div class="titleContainer">
                    <h2 class="title">Booking History</h2>
                </div>


                <div class="tableContainer">
                    <table class=" table table-striped" id="bookingHistory">
                        <thead>
                            <th scope="col">Check In</th>
                            <th scope="col">Total Cost</th>
                            <th scope="col">Balance</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Booking Type</th>
                            <th scope="col">Status</th>
                            <!-- <th scope="col">Review</th> -->
                            <th scope="col">Action</th>
                        </thead>

                        <tbody>

                            <?php

                            $getBooking = $conn->prepare("SELECT cb.*, b.*, s.statusName AS confirmedStatus, stat.statusName as bookingStatus FROM booking b
                                LEFT JOIN confirmedbooking cb ON cb.bookingID = b.bookingID
                                LEFT JOIN status s ON cb.paymentApprovalStatus = s.statusID
                                LEFT JOIN status stat ON b.bookingStatus = stat.statusID
                                WHERE userID = ?
                                ORDER BY b.createdAt");
                            $getBooking->bind_param("i", $userID);
                            $getBooking->execute();
                            $resultGetBooking = $getBooking->get_result();
                            if ($resultGetBooking->num_rows > 0) {
                                $bookings = $resultGetBooking->fetch_all(MYSQLI_ASSOC);


                                foreach ($bookings as $booking) {
                                    $confirmedBookingID = $booking['confirmedBookingID'];
                                    $bookingID = $booking['bookingID'];
                                    $startDate = strtotime($booking['startDate']);
                                    $checkIn = date("M j, Y", $startDate);  //  Pag gusto n`yo is Month day, Year pakipalitan ng F j, Y 
                                    $endDate = strtotime($booking['endDate']);
                                    $checkOut = date("M j, Y", $endDate); //  Pag gusto n`yo is Month day, Year pakipalitan ng F j, Y 
                                    $bookingType = $booking['bookingType'];
                                    $totalAmount = $booking['totalCost'];
                                    $balance = $booking['userBalance'] ?? $totalAmount;
                                    $paymentMethod = $booking['paymentMethod'];

                                    // echo '<pre>';
                                    // print_r("Data " . $booking['confirmedStatus'] . $booking['bookingStatus']);
                                    // echo '</pre>';
                            ?>
                                    <tr>
                                        <td><?= $checkIn ?></td>
                                        <!-- <td><?= $checkOut ?></td> -->
                                        <td>₱<?= number_format($totalAmount, 2) ?></td>
                                        <td>₱<?= number_format($balance, 2) ?></td>
                                        <td><?= htmlspecialchars($paymentMethod) ?></a></td>

                                        <td><?= htmlspecialchars($bookingType) ?></a></td>

                                        <!-- Papalitan na lang ng mas magandang term -->
                                        <?php if (!empty($booking['confirmedBookingID'])) {
                                            if ($booking['confirmedStatus'] === "Pending") {

                                                if ($paymentMethod === 'Cash') {
                                                    $status = "Onsite payment";
                                                    $class = 'btn btn-primary w-100';
                                                } else {
                                                    $status = "Downpayment";
                                                    $class = 'btn btn-primary w-100';
                                                }
                                            } elseif ($booking['confirmedStatus'] === "Approved") {
                                                $status = "Successful";
                                                $class = 'btn btn-success w-100';
                                            } elseif ($booking['confirmedStatus'] === "Rejected") {
                                                $status = "Rejected";
                                                $class = 'btn btn-red w-100';
                                            } elseif ($booking['confirmedStatus'] === 'Done') {
                                                $status = "Success";
                                                $class = 'btn btn-dark-green w-100';
                                            } elseif ($booking['confirmedStatus'] === "Cancelled") {

                                                $status = "Cancelled";
                                                $class = 'btn btn-orange w-100';
                                            }
                                        } else {
                                            $confirmedBookingID = NULL;
                                            if ($booking['bookingStatus'] === "Pending") {
                                                $status = "Pending";
                                                $class = 'btn btn-warning w-100';
                                                $bookingStatus = $booking['bookingStatus'];
                                            } else if ($booking['bookingStatus'] === "Approved") {
                                                $bookingStatus = $booking['bookingStatus'];
                                                if ($paymentMethod === 'Cash') {
                                                    $status = "Onsite payment";
                                                    $class = 'btn btn-primary w-100';
                                                } else {
                                                    $status = "Downpayment";
                                                    $class = 'btn btn-primary w-100';
                                                }
                                            } elseif ($booking['bookingStatus'] === "Rejected") {
                                                $bookingStatus = $booking['bookingStatus'];
                                                $status = "Rejected";
                                                $class = 'btn btn-red w-100';
                                            } elseif ($booking['bookingStatus'] === "Cancelled") {
                                                $bookingStatus = $booking['bookingStatus'];
                                                $status = "Cancelled";
                                                $class = 'btn btn-orange w-100';
                                            } elseif ($booking['bookingStatus'] === "Expired") {
                                                $bookingStatus = $booking['bookingStatus'];
                                                $status = "Expired";
                                                $class = 'btn btn-danger w-100';
                                            }
                                        }
                                        ?>

                                        <td> <span class="<?= $class ?> font-weight-bold bookingStatus" data-label="<?= $status ?>"><?= $status ?></span></td>
                                        <td>
                                            <div class="button-container gap-2 md-auto"
                                                style="display: flex;  width: 100%; justify-content: center;">
                                                <form action="reservationSummary.php" method="POST">
                                                    <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                                                    <input type="hidden" name="confirmedBookingID" value="<?= $confirmedBookingID ?>">
                                                    <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                                                    <input type="hidden" name="status" value="<?= $status ?>">
                                                    <button type="submit" name="viewBooking" class="btn btn-info w-100 viewBooking" data-label="View">View</button>
                                                </form>
                                                <?php if (
                                                    $booking['confirmedStatus'] === 'Done'
                                                    || $booking['bookingStatus'] === 'Cancelled'
                                                    || $booking['bookingStatus'] === 'Expired'
                                                    || $booking['confirmedStatus'] === 'Approved'
                                                    || $booking['bookingStatus'] === 'Rejected'
                                                    || $booking['confirmedStatus'] === 'Rejected'
                                                ) { ?>
                                                    <button class="btn btn-outline-primary w-100 rateBtn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rateModal"
                                                        data-bookingid="<?= $bookingID ?>"
                                                        data-label="Rate">
                                                    </button>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-danger w-100 cancelBooking"
                                                        data-bookingid="<?= $bookingID ?>"
                                                        data-confirmedbookingid="<?= $confirmedBookingID ?>"
                                                        data-status="<?= $status ?>"
                                                        data-bookingstatus="<?= $booking['bookingStatus'] ?>"
                                                        data-confirmedstatus="<?= $booking['confirmedStatus'] ?>"
                                                        data-bookingtype="<?= $bookingType ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmationModal" data-label="Cancel"></button>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    <?php } ?>
                                    </tr>
                                <?php
                            }
                                ?>

                        </tbody>
                    </table>


                    <!-- rate Modal -->
                    <div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header" id="rate-modal-header">
                                    <h4 class="modal-title" id="rateModalLabel">Please Rate Your Mamyr Experience</h4>

                                </div>
                                <div class="modal-body">
                                    <p class="rateSubtitle">We value your feedback! Share your thoughts to help us improve and
                                        offer better experiences.</p>
                                    <div class="d-flex">
                                        <span class="fa fa-star" id="star1" onclick="toggleStars(1)"></span>
                                        <span class="fa fa-star" id="star2" onclick="toggleStars(2)"></span>
                                        <span class="fa fa-star" id="star3" onclick="toggleStars(3)"></span>
                                        <span class="fa fa-star" id="star4" onclick="toggleStars(4)"></span>
                                        <span class="fa fa-star" id="star5" onclick="toggleStars(5)"></span>
                                    </div>


                                    <textarea class="form-control w-100 mt-3" id="purpose-additionalNotes"
                                        name="additionalRequest" rows="5" placeholder="Additional Feedback"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary w-25">Rate</button>

                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Confirmation Modal -->
                    <form action="../../Function/Booking/cancelBooking.php" method="POST">
                        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" id="cancel-content">

                                    <div class="image w-100 text-center">
                                        <img src="../../Assets/Images/Icon/warning.png" alt="warning icon"
                                            class="warning-image">
                                    </div>

                                    <div class="modal-body">

                                        <input type="hidden" name="bookingID" id="bookingIDModal" value="">
                                        <input type="hidden" name="confirmedBookingID" id="confirmedBookingIDModal" value="">
                                        <input type="hidden" name="bookingStatus" id="bookingStatusModal" value="">
                                        <input type="hidden" name="confirmedStatus" id="confirmedStatusModal" value="">
                                        <input type="hidden" name="bookingType" id="bookingTypeModal" value="">
                                        <input type="hidden" name="status" id="statusModal" value="">

                                        <p class="modal-title text-center mb-2 fw-bold fs-3">Are you sure?</p>
                                        <p class="modal-text text-center mb-2" id="cancelModalDesc">You are about to cancel this booking. This action cannot be undone.</p>

                                        <div class="button-container" id="cancelButtonModal">
                                            <button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">No</button>
                                            <button type="submit" class="btn btn-primary w-25" name="cancelBooking" id="yesDelete">Yes</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </main>
    </div>



    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#bookingHistory').DataTable({
                language: {
                    emptyTable: "No Bookings Made" //Pakipalitan na lang din ng magandang term
                },
                columnDefs: [{
                    width: '15%',
                    target: 0

                }]
            });
        });
    </script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const items = document.querySelectorAll('.list-group-item');
            const toggleCont = document.getElementById('toggle-container');
            const viewBtns = document.querySelectorAll('.viewBooking');
            const cancelBtns = document.querySelectorAll('.cancelBooking');
            const statuses = document.querySelectorAll('.bookingStatus');
            const rateBtns = document.querySelectorAll('.rateBtn')

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    });
                    toggleCont.style.justifyContent = "center"
                } else {
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    toggleCont.style.justifyContent = "flex-end"
                }
            });

            function handleResponsiveSidebar() {
                if (window.innerWidth <= 600) {
                    viewBtns.forEach(viewBtn => {
                        viewBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
                        viewBtn.style.width = '70%';
                    });
                    cancelBtns.forEach(cancelBtn => {
                        cancelBtn.innerHTML = '<i class="fa-solid fa-ban" style="color: #f4ebeb;"></i>';
                        cancelBtn.style.width = '70%';
                    });
                    rateBtns.forEach(rateBtns => {
                        rateBtns.innerHTML = '<i class="fa-solid fa-star" style="color: #FFD43B;padding:0;"></i>';
                        rateBtns.classList.remove('btn-outline-primary')
                    })
                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    })
                    sidebar.classList.remove('collapsed');

                    viewBtns.forEach(viewBtn => {
                        viewBtn.innerHTML = `${viewBtn.getAttribute('data-label')}`;
                    })
                    cancelBtns.forEach(cancelBtn => {
                        cancelBtn.innerHTML = `${cancelBtn.getAttribute('data-label')}`;
                    })
                    rateBtns.forEach(rateBtn => {
                        rateBtn.innerHTML = `${rateBtn.getAttribute('data-label')}`;
                        rateBtn.classList.add('btn-outline-primary')
                    })
                }
                //change the text into icons when the screen width shrinks to below 1024px
                if (window.innerWidth <= 1024) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })
                    statuses.forEach(status => {
                        if (status.innerHTML == "Pending") {
                            status.innerHTML = '<i class="fa-solid fa-hourglass-half" style="color: #ffc107;"></i>';
                            status.classList.remove('btn-warning');
                            status.classList.remove('w-100');
                        } else if (status.innerHTML == "Downpayment" || status.innerHTML == "Onsite Payment") {
                            status.innerHTML = '<i class="fa-solid fa-money-bill-1-wave" style="color: #0dcaf0;"></i>';
                            status.classList.remove('btn-info');
                            status.classList.remove('w-100');
                        } else if (status.innerHTML == "Cancelled") {
                            status.innerHTML = '<i class="fa-solid fa-xmark" style="color: #b02a37;"></i>';
                            status.classList.remove('btn-danger');
                            status.classList.remove('w-100');
                        }
                        status.style.width = "70%"
                    })
                } else {
                    //reverts the text for icons when screen is resized to larger sizes
                    statuses.forEach(status => {
                        status.innerHTML = `${status.getAttribute('data-label')}`;
                        if (status.innerHTML == "Pending") {
                            status.classList.add('btn-warning');
                            status.classList.add('w-100');
                        } else if (status.innerHTML == "Downpayment" || status.innerHTML == "Onsite Payment") {
                            status.classList.add('btn-info');
                            status.classList.add('w-100');
                        } else if (status.innerHTML == "Cancelled") {
                            status.classList.add('btn-danger');
                            status.classList.add('w-100');
                        }
                    })
                }

            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
        });
    </script>

    <!-- Show -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action')
        const confirmationModal = document.getElementById("confirmationModal");
        const logoutBtn = document.getElementById('logoutBtn');

        logoutBtn.addEventListener("click", function() {
            Swal.fire({
                title: "Are you sure you want to log out?",
                text: "You will need to log in again to access your account.",
                icon: "warning",
                showCancelButton: true,
                // confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, logout!",
                customClass: {
                    title: 'swal-custom-title',
                    htmlContainer: 'swal-custom-text'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../Function/logout.php";
                }
            });
        });

        document.querySelectorAll(".cancelBooking").forEach(button => {
            button.addEventListener("click", function() {

                const bookingID = this.getAttribute("data-bookingid");
                const confirmedBookingID = this.getAttribute("data-confirmedbookingid");
                const status = this.getAttribute("data-status");
                const bookingStatus = this.getAttribute("data-bookingstatus");
                const confirmedStatus = this.getAttribute("data-confirmedstatus");
                const bookingType = this.getAttribute("data-bookingtype");

                document.getElementById("bookingIDModal").value = bookingID;
                document.getElementById("confirmedBookingIDModal").value = confirmedBookingID;
                document.getElementById("statusModal").value = status;
                document.querySelector('input[name="bookingStatus"]').value = bookingStatus;
                document.querySelector('input[name="confirmedStatus"]').value = confirmedStatus;
                document.querySelector('input[name="bookingType"]').value = bookingType;
            });
        });

        if (paramValue === "Cancelled") {
            Swal.fire({
                title: "Successfully Cancelled!",
                text: "If you change your mind, feel free to book again anytime. Thank you.",
                icon: "success",
            });
        } else if (paramValue === "Error") {
            Swal.fire({
                title: "Cancellation Failed!",
                text: "An error occurred while cancelling.",
                icon: "error",
                confirmButtonText: "OK"
            });
        } else if (paramValue === 'paymentSuccess') {
            Swal.fire({
                title: "Payment Successful!",
                text: "Thank you! Your GCash payment receipt has been successfully sent. Please wait while the admin verifies your payment.",
                icon: "success",
                confirmButtonText: "OK"
            });
        };

        // if (paramValue) {
        //     const url = new URL(window.location);
        //     url.search = "";
        //     history.replaceState({}, document.title, url.toString());
        // };
    </script>

    <!-- rate JS -->
    <script>
        function toggleStars(starNumber) {
            for (let i = 1; i <= 5; i++) {
                document.getElementById('star' + i).classList.remove('orange');
            }

            for (let i = 1; i <= starNumber; i++) {
                document.getElementById('star' + i).classList.add('orange');
            }
        }
    </script>
</body>


</html>