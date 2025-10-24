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

// Get all bookingIDs with a submitted review
$getReviews = $conn->prepare("SELECT bookingID FROM userreview WHERE bookingID IN (SELECT bookingID FROM booking WHERE userID = ?)");
$getReviews->bind_param("i", $userID);
$getReviews->execute();
$reviewResult = $getReviews->get_result();

$reviewedBookingIDs = [];
while ($row = $reviewResult->fetch_assoc()) {
    $reviewedBookingIDs[] = $row['bookingID'];
}

require_once '../../Function/Helpers/statusFunctions.php';

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
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/bookingHistory.css" />
    <link rel="stylesheet" href="../../Assets/CSS/Account/account-sidebar.css" />
    <!-- DataTables Link -->
    <link rel=" stylesheet" href="../../Assets/CSS/datatables.min.css" />
    <!-- Font Awesome Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap Icon Link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="wrapper d-flex">
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-center" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home">
                <?php if ($role === 'Customer' || $role === 'Partnership Applicant') { ?>
                    <a href="../Customer/dashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } elseif ($role === 'Admin') { ?>
                    <a href="../Admin/adminDashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } elseif ($role === 'Business Partner') { ?>
                    <a href="../BusinessPartner/bpDashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } ?>
            </div>
            <div class="sidebar-header text-center">

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
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt=" <?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li>
                    <a href="account.php" class="list-group-item">
                        <i class="bi bi-person sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>
                <?php if ($role !== 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="BookingHist">
                            <i class="bi bi-calendar2-check sidebar-icon"></i>
                            <span class="sidebar-text">Booking History</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="paymentHistory.php" class="list-group-item active" id="paymentHist">
                            <i class="bi bi-credit-card-2-front sidebar-icon"></i>
                            <span class="sidebar-text">Payment</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li>
                        <a href="userManagement.php" class="list-group-item">
                            <i class="bi bi-person-gear sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="bi bi-calendar-week sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item">
                            <i class="bi bi-bell sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpSales.php" class="list-group-item">
                            <i class="bi bi-tags sidebar-icon"></i>
                            <span class="sidebar-text">Sales</span>
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="bi bi-shield-check sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li>
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="bi bi-person-dash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
                <li>
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                        style="margin: 3vw auto;">
                        <i class="bi bi-box-arrow-right logout-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside>
        <main class="main-content" id="main-content">
            <div class="bookingHistContainer">

                <div class="titleContainer">
                    <h2 class="title">Payments</h2>
                </div>
                <input type="hidden" name="userID" id="userID" value="<?= $userID ?>">
                <div class="tableContainer">
                    <table class=" table table-striped" id="paymentHistory">
                        <thead>
                            <th scope="col">Booking ID</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Balance</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Approval Status</th>
                            <th scope="col">Payment Status</th>
                            <th scope="col">Action</th>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View Payment Modal -->
            <div class="modal fade" id="vpModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">

                    <div class="modal-content">
                        <div class="modal-header" id="rate-modal-header">
                            <h4 class="modal-title fw-b"></h4>
                        </div>
                        <div class="modal-body">
                            <h5 class="payment-container-title" id="booking-type"></h5>
                            <div class="paymentListContainer" id="paymentListContainer">
                            </div>
                            <hr class="dp-hr">
                            <h6 class="payment-container-title text-start ms-3">Summary</h6>
                            <div class="payment-container">
                                <div class="dp-left-side">
                                    <h6 class="dp-title" id="booking-type-payment">Total Amount:</h6>
                                    <h6 class="dp-title" id="booking-type-paymenent">Remaining Balance:</h6>
                                    <h6 class="date">Due Date: </h6>
                                </div>
                                <div class="dp-right-side">
                                    <h6 class="dp-amount" id="total-amount"></h6>
                                    <h6 class="dp-amount" id="remaining-balance"></h6>
                                    <h6 class="date" id="dp-date"> </h6>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary w-25"
                                data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal fade" id="view-dp" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
                tabindex="-1">
                <div class="modal-dialog  modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <img src=""
                                alt="Downpayment Image" id="payment-preview" class="downpaymentPic mb-3">
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>



    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function getStatusBadge(colorClass, status) {
            return `<span class="badge bg-${colorClass} text-capitalize">${status}</span>`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            const userID = document.getElementById('userID');
            const userIDValue = userID.value;

            const bookingMap = {};
            // console.error(userIDValue);
            fetch(`../../Function/Admin/Ajax/getPaymentHistory.php?userID=${userIDValue}`)
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
                    const table = $('#paymentHistory').DataTable();
                    table.clear();

                    if (bookings && bookings.length > 0) {
                        bookings.forEach(booking => {
                            bookingMap[booking.bookingID] = booking;
                            table.row.add([
                                booking.bookingID.toString().padStart(4, '0'),
                                booking.totalBill,
                                booking.userBalance,
                                booking.paymentMethod,
                                getStatusBadge(booking.approvalClass, booking.approvalStatus),
                                getStatusBadge(booking.paymentClass, booking.paymentStatus),
                                `<div class="action-button-container">
                                            <button type="button" class="btn btn-info viewPaymentInfo" data-bookingid=${booking.bookingID}>
                                                View
                                            </button>
                                    </div> `
                            ]);
                        });

                        table.draw();
                    }

                }).catch(error => {
                    console.error("Error loading bookings:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'Failed to load data from the server.'
                    })
                });

            document.addEventListener("click", function(e) {
                if (e.target && e.target.classList.contains("viewPaymentInfo")) {
                    const bookingID = e.target.getAttribute("data-bookingid");
                    const booking = bookingMap[bookingID];

                    const viewPaymentModal = document.getElementById("vpModal");

                    const viewImage = document.getElementById('view-dp');

                    viewPaymentModal.querySelector('#rate-modal-header .modal-title').innerHTML = `Payment Details &mdash; <strong> ${booking.bookingCode} </strong>`;

                    viewPaymentModal.querySelector('#booking-type').innerHTML = `${booking.bookingType} Booking`;
                    viewPaymentModal.querySelector('#dp-date').textContent = booking.paymentDueDate;
                    viewPaymentModal.querySelector('#remaining-balance').textContent = booking.userBalance;
                    viewPaymentModal.querySelector('#total-amount').textContent = booking.totalBill;
                    const paymentListContainer = document.getElementById('paymentListContainer');
                    paymentListContainer.innerHTML = '';
                    const payments = booking.payments || [];

                    let terms = [];

                    if (payments.length > 0) {
                        const length = payments.length;
                        if (length < 3) {
                            terms = ['Inital', 'Final'];
                        } else {
                            terms = ['Initial', 'Second', 'Final'];
                        }

                        payments.forEach((payment, i) => {
                            const downpaymentContainer = document.createElement('downpayment-container');
                            downpaymentContainer.classList.add('downpayment-container');
                            const divChild1 = document.createElement('div');
                            divChild1.classList.add('dp-left-side');

                            const h61 = document.createElement('h6');
                            h61.classList.add('dp-title', 'fw-bold');
                            h61.textContent = `${terms[i]} Payment `;

                            const p1 = document.createElement('p');
                            p1.classList.add('dp-date');
                            p1.textContent = payment.date;

                            const divChild2 = document.createElement('div');
                            divChild2.classList.add('dp-right-side');

                            const h62 = document.createElement('h6');
                            h62.classList.add('dp-amount', 'fw-bold');
                            h62.textContent = `₱ ${payment.amount}`;

                            const p2 = document.createElement('p');
                            p2.classList.add('mode');
                            p2.textContent = `via ${payment.method}`;

                            paymentListContainer.appendChild(downpaymentContainer);
                            downpaymentContainer.appendChild(divChild1);
                            divChild1.appendChild(h61);
                            divChild1.appendChild(p1);
                            downpaymentContainer.appendChild(divChild2);
                            divChild2.appendChild(h62);
                            divChild2.appendChild(p2);

                            if (i === 0) {
                                const divChild3 = document.createElement('div');
                                divChild3.classList.add('view-dp');

                                const button = document.createElement('button');
                                button.classList.add('btn', 'btn-primary', 'view-dp-btn');
                                button.setAttribute('data-bs-target', '#view-dp');
                                button.setAttribute('data-bs-toggle', 'modal');
                                button.textContent = 'View';

                                downpaymentContainer.appendChild(divChild3);
                                divChild3.appendChild(button);
                            }

                            viewImage.querySelector('#payment-preview').src = '../../Assets/Images/PaymentProof/' + payment.image;
                        });
                    } else {
                        const downpaymentContainer = document.createElement('downpayment-container');
                        downpaymentContainer.classList.add('downpayment-container');
                        const divChild1 = document.createElement('div');
                        divChild1.classList.add('dp-left-side');

                        const h61 = document.createElement('h6');
                        h61.classList.add('dp-title');
                        h61.textContent = 'No Payment made';

                        paymentListContainer.appendChild(downpaymentContainer);
                        downpaymentContainer.appendChild(divChild1);
                        divChild1.appendChild(h61);
                    }



                    const modal = new bootstrap.Modal(viewPaymentModal);
                    modal.show();
                }
            });
        })
    </script>

    <script>
        function otherReason() {
            var selectBox = document.getElementById("select-reason");
            var otherInputGroup = document.getElementById("otherInputGroup");

            // Show or hide the text box when "Other (Please specify)" is selected
            if (selectBox.value === "other" || selectBox.value === '9') {
                otherInputGroup.style.display = "block"; // Show the text box
            } else {
                otherInputGroup.style.display = "none"; // Hide the text box
            }
        }
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
                        rateBtn.innerHTML =
                            '<i class="fa-solid fa-star" style="color: #FFD43B;padding:0;"></i>';
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
                            status.innerHTML =
                                '<i class="fa-solid fa-hourglass-half" style="color: #ffc107;"></i>';
                            status.classList.remove('btn-warning');
                            status.classList.remove('w-100');
                        } else if (status.innerHTML == "Downpayment" || status.innerHTML ==
                            "Onsite Payment") {
                            status.innerHTML =
                                '<i class="fa-solid fa-money-bill-1-wave" style="color: #0dcaf0;"></i>';
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
                        } else if (status.innerHTML == "Downpayment" || status.innerHTML ==
                            "Onsite Payment") {
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
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#paymentHistory').DataTable({
                language: {
                    emptyTable: "You have no approved bookings and no payment has been made yet."
                }
            });
        });
    </script>


</body>


</html>