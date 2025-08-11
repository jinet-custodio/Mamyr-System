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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body>

    <div class="sidebar">

        <div class="home">
            <?php if ($role === 'Customer') { ?>
                <a href="../Customer/dashboard.php">
                    <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                </a>
            <?php } elseif ($role === 'Admin') { ?>
                <a href="../Admin/adminDashboard.php">
                    <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                </a>
            <?php } ?>
        </div>

        <div class="sidebar-header">
            <h5>User Account</h5>

            <?php
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
            ?>
            <div class="profileImage">
                <img src="<?= htmlspecialchars($image) ?>" alt=" <?= htmlspecialchars($data['firstName']) ?> Picture">
            </div>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item">
                    <img src="../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../Assets/Images/Icon/login_security.png" alt="Login Security" class="sidebar-icon">
                    Login & Security
                </a>
            </li>


            <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                <li>
                    <a href="bookingHistory.php" class="list-group-item active" id="paymentBookingHist">
                        <img src="../../Assets/Images/Icon/bookingHistory.png" alt="Booking History"
                            class="sidebar-icon">
                        Payment & Booking History
                    </a>
                </li>
            <?php } elseif ($role === 'Admin') { ?>
                <li>
                    <a href="userManagement.php" class="list-group-item">
                        <img src="../../Assets/Images/Icon/usermanagement.png" alt="" class="sidebar-icon">
                        Manage Users
                    </a>
                </li>
            <?php } ?>

            <li>
                <a href="deleteAccount.php" class="list-group-item">
                    <img src="../../Assets/Images/Icon/delete-user.png" alt="Delete Account" class="sidebar-icon">
                    Delete Account
                </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>

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
                    <th scope="col">Review</th>
                    <th scope="col">Action</th>
                </thead>

                <tbody>

                    <?php

                    $getBooking = $conn->prepare("SELECT cb.*, b.*, s.statusName AS confirmedStatus, stat.statusName as bookingStatus FROM bookings b
                    LEFT JOIN confirmedbookings cb ON cb.bookingID = b.bookingID
                    LEFT JOIN statuses s ON cb.confirmedBookingStatus = s.statusID
                    LEFT JOIN statuses stat ON b.bookingStatus = stat.statusID
                    WHERE userID = ?
                    ORDER BY createdAt");
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
                            $paymentMethod = $booking['paymentMethod']
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
                                            $class = 'btn btn-info w-100';
                                        } else {
                                            $status = "Downpayment";
                                            $class = 'btn btn-info w-100';
                                        }
                                    } elseif ($booking['confirmedStatus'] === "Approved") {
                                        $status = "Successful";
                                        $class = 'btn btn-success w-100';
                                    } elseif ($booking['confirmedStatus'] === "Rejected") {
                                        $status = "Rejected";
                                        $class = 'btn btn-danger w-100';
                                    }
                                } else {
                                    $confirmedBookingID = NULL;
                                    if ($booking['bookingStatus'] === "Pending") {
                                        $status = "Pending";
                                        $class = 'btn btn-warning w-100';
                                    } else if ($booking['bookingStatus'] === "Approved") {
                                        if ($paymentMethod === 'Cash') {
                                            $status = "Onsite payment";
                                            $class = 'btn btn-info w-100';
                                        } else {
                                            $status = "Downpayment";
                                            $class = 'btn btn-info w-100';
                                        }
                                    } elseif ($booking['bookingStatus'] === "Rejected") {
                                        $status = "Rejected";
                                        $class = 'btn btn-danger w-100';
                                    } elseif ($booking['bookingStatus'] === "Cancelled") {
                                        $status = "Cancelled";
                                        $class = 'btn btn-danger w-100';
                                    } elseif ($booking['bookingStatus'] === "Rejected") {
                                        $status = "Rejected";
                                        $class = 'btn btn-danger w-100';
                                    }
                                }
                                ?>

                                <td><span class="<?= $class ?>"><?= $status ?></span></td>
                                <td><a href="" class=" btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#rateModal">Rate</a></td>

                                <td>
                                    <div class="button-container gap-2 md-auto"
                                        style="display: flex;  width: 100%; justify-content: center;">
                                        <form action="reservationSummary.php" method="POST">
                                            <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                                            <input type="hidden" name="confirmedBookingID" value="<?= $confirmedBookingID ?>">
                                            <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                                            <input type="hidden" name="status" value="<?= $status ?>">
                                            <button type="submit" name="viewBooking" class="btn btn-info w-100">View</button>
                                        </form>

                                        <button type="button" class="btn btn-danger  w-100 cancelBooking"
                                            data-bookingid="<?= $bookingID ?>"
                                            data-confirmedbookingid="<?= $confirmedBookingID ?>"
                                            data-status="<?= $status ?>">Cancel</button>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>

                    <!-- <tr>
                        <td>January 26, 2025</td>
                        <td>January 26, 2025</td>
                        <td><a href=" #" class="fw-bold">Event Booking</a>
                                </td>
                                <td><a href="#" class="btn btn-success w-75">View</a></td>
                                <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                            </tr>

                            <tr>
                                <td>February 27, 2025</td>
                                <td>February 27, 2025</td>
                                <td><a href="#" class="fw-bold">Resort Booking</a></td>
                                <td><a href="#" class="btn btn-success w-75">View</a></td>
                                <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                            </tr>

                            <tr>
                                <td>March 26, 2025</td>
                                <td>March 26, 2025</td>
                                <td><a href="#" class="fw-bold">Event Booking</a></td>
                                <td><a href="#" class="btn btn-success w-75">View</a></td>
                                <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                            </tr>

                            <tr>
                                <td>April 26, 2025</td>
                                <td>April 26, 2025</td>
                                <td><a href="#" class="fw-bold">Resort Booking</a></td>
                                <td><a href="#" class="btn btn-success w-75">View</a></td>
                                <td><a href="" class="btn btn-outline-primary ">Rate</a></td>
                            </tr> -->
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

            <!-- <div class="modal modal-sm fade" id="rateModal" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Thank you for Ordering!</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5>Please rate your experience.</h5>
                            <span class="fa fa-star" id="star1" onclick="toggleStars(1)"></span>
                            <span class="fa fa-star" id="star2" onclick="toggleStars(2)"></span>
                            <span class="fa fa-star" id="star3" onclick="toggleStars(3)"></span>
                            <span class="fa fa-star" id="star4" onclick="toggleStars(4)"></span>
                            <span class="fa fa-star" id="star5" onclick="toggleStars(5)"></span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- rate Modal -->



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
                                <input type="hidden" name="bookingID" id="bookingIDModal" value="<?= $bookingID ?>">
                                <input type="hidden" name="confirmedBookingID" id="confirmedBookingIDModal"
                                    value="<?= $confirmedBookingID ?>">
                                <input type="hidden" name="status" id="statusModal" value="<?= $status ?>">
                                <p class="modal-title text-center mb-2 fw-bold fs-3">Are you sure?</p>
                                <p class="modal-text text-center mb-2" id="cancelModalDesc">You are about to cancel this
                                    booking. This
                                    action
                                    cannot be undone.</p>
                                <div class="button-container" id="cancelButtonModal">
                                    <button type="button" class="btn btn-secondary w-25"
                                        data-bs-dismiss="modal">No</button>
                                    <button type="submit" class="btn btn-primary w-25" name="cancelBooking"
                                        id="yesDelete">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>


        </div>
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

                document.getElementById("bookingIDModal").value = bookingID;
                document.getElementById("confirmedBookingIDModal").value = confirmedBookingID;
                document.getElementById("statusModal").value = status;


                const myCancelBookingModal = new bootstrap.Modal(confirmationModal);
                myCancelBookingModal.show();
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

        if (paramValue) {
            const url = new URL(window.location);
            url.search = "";
            history.replaceState({}, document.title, url.toString());
        };
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