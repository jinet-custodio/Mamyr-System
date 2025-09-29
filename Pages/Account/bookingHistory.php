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

// Get all bookingIDs with a submitted review
$getReviews = $conn->prepare("SELECT bookingID FROM userreview WHERE bookingID IN (SELECT bookingID FROM booking WHERE userID = ?)");
$getReviews->bind_param("i", $userID);
$getReviews->execute();
$reviewResult = $getReviews->get_result();

$reviewedBookingIDs = [];
while ($row = $reviewResult->fetch_assoc()) {
    $reviewedBookingIDs[] = $row['bookingID'];
}

require_once '../../Function/functions.php';
//Changing Status function to ah galing sa file na functions.php
changeToExpiredStatus($conn);
changeToDoneStatus($conn);

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 3:
        $role = "Admin";
        break;
    case 4:
        $role = "Partnership Applicant";
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
                    <a href="../BusinessPartner/bpDashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } ?>
            </div>

            <div class="sidebar-header text-center">
                <div class="d-flex" id="toggle-container">
                    <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </button>
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
                <li>
                    <a href="account.php" class="list-group-item">
                        <i class="fa-solid fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>
                <?php if ($role !== 'Admin') { ?>
                    <li>
                        <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
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
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="fa-regular fa-calendar-days sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item">
                            <i class="fa-solid fa-bell-concierge sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpSales.php" class="list-group-item">
                            <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                            <span class="sidebar-text">Sales</span>
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
                <input type="hidden" name="userID" id="userID" value="<?= $userID ?>">
                <div class="tableContainer">
                    <table class=" table table-striped" id="bookingHistory">
                        <thead>
                            <th scope="col">Check In</th>
                            <th scope="col">Total Cost</th>
                            <th scope="col">Balance</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Booking Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </thead>

                        <tbody id="p-b-history-body">
                        </tbody>
                    </table>


                    <!-- rate Modal -->
                    <div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form id="reviewForm" method="POST">
                                <div class="modal-content">
                                    <div class="modal-header" id="rate-modal-header">
                                        <h4 class="modal-title" id="rateModalLabel">Please Rate Your Mamyr Experience</h4>
                                    </div>

                                    <div class="modal-body">
                                        <p class="rateSubtitle">We value your feedback! Share your thoughts to help us improve and offer better experiences.</p>

                                        <!-- Stars -->
                                        <div class="d-flex" id="starContainer"></div>

                                        <!-- Hidden rating value -->
                                        <input type="hidden" id="reviewRating" name="reviewRating" value="0">

                                        <!-- Feedback -->
                                        <textarea class="form-control w-100 mt-3" id="purpose-additionalNotes"
                                            name="reviewComment" rows="5" placeholder="Additional Feedback"></textarea>

                                        <!-- Booking Info -->
                                        <!-- Booking Info -->
                                        <input type="hidden" id="modalBookingID" name="bookingID" value="">
                                        <input type="hidden" id="modalBookingType" name="bookingType" value="">

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary w-25">Review</button>
                                    </div>
                                </div>
                            </form>
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

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const userID = document.getElementById('userID');
            const userIDValue = userID.value;
            // console.error(userIDValue);
            fetch(`../../Function/Admin/Ajax/getBookingHistoryJSON.php?userID=${userIDValue}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // console.error("Failed to load bookings.");
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An unknown error occurred.'
                        });
                        return;
                    }
                    const bookings = data.bookings;
                    const tbody = document.querySelector('#p-b-history-body');
                    tbody.innerHTML = "";


                    const reviewedBookingIDs = <?= json_encode($reviewedBookingIDs) ?>;
                    if (bookings && bookings.length > 0) {
                        bookings.forEach(booking => {
                            let isReviewed = reviewedBookingIDs.includes(booking.bookingID);
                            let canReview = (
                                booking.approvalStatus === 'Done' ||
                                booking.status === 'Cancelled' ||
                                booking.status === 'Expired' ||
                                booking.approvalStatus === 'Approved' ||
                                booking.status === 'Rejected' ||
                                booking.approvalStatus === 'Rejected'
                            );
                            // console.log(booking.bookingStatus);
                            // console.log(canReview);
                            const row = document.createElement("tr");
                            row.innerHTML = `
                                                <td>${booking.checkIn}</td>
                                                <td>${booking.totalBill}</td>
                                                <td>${booking.userBalance}</td>
                                                <td>${booking.paymentMethod}</td>
                                                <td>${booking.bookingType} Booking</td>
                                                <td>
                                                    <a class="btn btn-${booking.statusClass} w-100">
                                                        ${booking.status}
                                                    </a>
                                                </td>
                                                <td>
                                                <div class="button-container gap-2 md-auto"
                                                    style="display: flex;  width: 100%; justify-content: center;">
                                                    <form action="reservationSummary.php" method="POST">
                                                        <input type="hidden" name="bookingType" value="${booking.bookingType}">
                                                        <input type="hidden" name="confirmedBookingID" value="${booking.confirmedBookingID}">
                                                        <input type="hidden" name="bookingID" value="${booking.bookingID}">
                                                        <input type="hidden" name="status" value="${booking.status}">
                                                        <button type="submit" name="viewBooking" class="btn btn-info w-100 viewBooking" data-label="View">View</button>
                                                    </form>
                                                    ${
                                                        canReview
                                                            ? (
                                                                isReviewed
                                                                    ? `<button class="btn btn-outline-secondary px-0 w-100 rateBtn" title="You have already reviewed this booking/reservation" disabled data-label="Reviewed">Reviewed</button>`
                                                                    : `<button class="btn btn-outline-primary px-0 w-100 rateBtn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#rateModal"
                                                                        data-bookingid="${booking.bookingID}"
                                                                        data-bookingtype="${booking.bookingType}"
                                                                        data-label="Review">Review</button>`
                                                            )
                                                            : `<button type="button" class="btn btn-danger w-100 cancelBooking"
                                                                data-bookingid="${booking.bookingID}"
                                                                data-confirmedbookingid="${booking.confirmedBookingID}"
                                                                data-status="${booking.status}"
                                                                data-bookingstatus="${booking.bookingStatus}"
                                                                data-confirmedstatus="${booking.approvalStatus}"
                                                                data-bookingtype="${booking.bookingType}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmationModal" data-label="Cancel">Cancel</button>`
                                                    }
                                                </div> 
                                                </td>
                                                
                                            `;
                            tbody.appendChild(row);

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
                        })
                    } else {
                        const row = document.createElement("tr");
                        row.innerHTML = `<td colspan="7" class="text-center">No bookings to display</td>`;
                        tbody.appendChild(row);
                    }

                    $(document).ready(function() {
                        const starContainer = $("#starContainer");
                        const ratingInput = $("#reviewRating");
                        let currentRating = 0;

                        function renderStars(rating) {
                            starContainer.empty();
                            for (let i = 1; i <= 5; i++) {
                                const star = $('<i class="fa fa-star star"></i>');
                                star.attr("data-value", i);
                                if (i <= rating) {
                                    star.addClass("checked");
                                }
                                star.on("click", function() {
                                    currentRating = i;
                                    ratingInput.val(currentRating);
                                    renderStars(currentRating);
                                });
                                star.on("dblclick", function() {
                                    currentRating = i - 0.5;
                                    ratingInput.val(currentRating);
                                    renderStars(currentRating);
                                });
                                starContainer.append(star);
                            }
                        }

                        renderStars(currentRating);


                        $('.rateBtn').on('click', function() {
                            const bookingID = $(this).data('bookingid');
                            const bookingType = $(this).data('bookingtype');

                            $('#modalBookingID').val(bookingID);
                            $('#modalBookingType').val(bookingType);
                        });

                        // AJAX form submission
                        $("#reviewForm").on("submit", function(e) {
                            e.preventDefault();
                            // console.log("Submitting review:", {
                            //     bookingID: $('#modalBookingID').val(),
                            //     bookingType: $('#modalBookingType').val(),
                            //     rating: $('#reviewRating').val(),
                            //     comment: $('#purpose-additionalNotes').val()
                            // });

                            $.ajax({
                                url: "../../Function/Account/submitReview.php",
                                method: "POST",
                                data: $(this).serialize(),
                                success: function(response) {
                                    // alert("Review submitted successfully!");
                                    Swal.fire({
                                        position: "top-end",
                                        icon: "success",
                                        title: "Review submitted successfully!",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    $("#rateModal").modal("hide");
                                    $("#reviewForm")[0].reset();
                                    renderStars(0);
                                },
                                error: function(xhr, status, error) {
                                    alert("Error submitting review: " + error);
                                }
                            });
                        });


                    });
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
                    rateBtns.forEach(rateBtn => {
                        rateBtn.innerHTML = '<i class="fa-solid fa-star" style="color: #FFD43B;padding:0;"></i>';
                        if (rateBtn.classList == 'btn-outline-primary') {
                            rateBtn.classList.remove('btn-outline-primary')
                        } else {
                            rateBtn.classList.remove('btn-outline-secondary')
                        }
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
                        if (rateBtn.innerHTML == 'Review') {
                            rateBtn.classList.add('btn-outline-primary')
                        } else {
                            rateBtn.classList.add('btn-outline-secondary')
                        }
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
            const url = new URLSearchParams(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#bookingHistory').DataTable({
                language: {
                    emptyTable: "You have not made any bookings yet"
                },
                columnDefs: [{
                        width: '15%',
                        target: 0
                    },
                    {
                        width: '20%',
                        target: 6
                    },

                ]
            });
        });
    </script>


</body>


</html>