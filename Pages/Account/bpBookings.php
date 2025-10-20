<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);


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



$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

require_once '../../Function/Partner/getBookings.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner Services - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/account-sidebar.css" />
    <link rel="stylesheet" href="../../Assets/CSS/Account/bpBooking.css">
    <!-- DataTables Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css" />
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <!-- Get the information to the database -->
    <?php
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

    $getData = $conn->prepare("SELECT u.*, ut.typeName as roleName FROM user u
            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
            WHERE u.userID = ? AND userRole = ?");
    $getData->bind_param("ii", $userID, $userRole);
    $getData->execute();
    $getDataResult = $getData->get_result();
    if ($getDataResult->num_rows > 0) {
        $data =  $getDataResult->fetch_assoc();
        $middleInitial = trim($data['middleInitial'] ?? '');
        $name = ucfirst($data['firstName'] ?? '') . " " .
            ucfirst($data['middleInitial'] ?? '') . " " .
            ucfirst($data['lastName'] ?? '');
        $profile = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
    }

    ?>
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-center" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home">
                <?php if ($role === 'Customer') { ?>
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
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item ">
                        <i class="bi bi-person sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>

                <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                            <i class="bi bi-calendar2-check sidebar-icon"></i>
                            <span class="sidebar-text">Payment & Booking History</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="userManagement.php" class="list-group-item">
                            <i class="bi bi-person-gear sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item active">
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

                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="bi bi-person-dash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
            </ul>
            <div class="logout">
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                    style="margin: 3vw auto;">
                    <i class="bi bi-box-arrow-right logout-icon"></i>
                    <span class="sidebar-text ms-2">Logout</span>
            </div>
        </aside>
        <!-- End Side Bar -->
        <!-- Get number of booking — approved, pending -->
        <?php
        $row = getBookingsCount($conn, $userID);
        ?>

        <main class="main-content" id="main-content">
            <div class="container">
                <h3 class="welcomeText" id="title">Bookings</h3>

                <div class="cardContainer" id="bookingCountDisplayContainer">
                    <div class="card">
                        <div class="card-header fw-bold fs-5" style=" background-color:#cee4f2;">Bookings</div>
                        <div class="card-body">
                            <h2 class="bookingNumber"><?= $row['allBookingStatus'] ?></h2>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header fw-bold fs-5" style="background-color: #1a8754; color:#ffff">Approved
                        </div>
                        <div class="card-body">
                            <h2 class="approvedNumber"><?= $row['approvedBookings'] ?></h2>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header fw-bold fs-5" style="background-color: #ffc108;">Pending</div>
                        <div class="card-body">
                            <h2 class="pendingNumber"><?= $row['totalPendingBooking'] ?></h2>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header fw-bold fs-5" style="background-color: #db3545; color:#ffff">Cancelled
                        </div>
                        <div class="card-body">
                            <h2 class="cancelledNumber"><?= $row['cancelledBooking'] ?></h2>
                        </div>
                    </div>
                </div>

                <div class="hidden-input">
                    <input type="hidden" name="userID" id="userID" value="<?= $userID ?>">
                </div>
                <div class="tableContainer" id="bookingTable">
                    <table class=" table table-striped" id="booking">
                        <thead>
                            <tr>
                                <th scope="col">Booking ID</th>
                                <th scope="col">Guest</th>
                                <th scope="col">Booking Type</th>
                                <th scope="col">Service</th>
                                <th scope="col">Check-in</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>


            <form action="../../Function/Partner/approvalBooking.php" method="POST">
                <!-- bookingModal -->
                <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="booking"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="exampleModalLabel">Booking Info</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <section class="user-info-container">
                                    <div class="booking-info-name-pic-btn">
                                        <div class="user-info">
                                            <img src="../../Assets/Images/defaultProfile.png"
                                                class="img-fluid rounded-start" alt="Profile Image">
                                            <div class="booking-info-contact">
                                                <p class="card-text name"></p>
                                                <p class="card-text sub-name contact">
                                                </p>
                                                <p class="card-text sub-name address"></p>
                                            </div>
                                        </div>
                                </section>

                                <section class="booking-info-container">

                                    <div class="hidden-inputs" style="display: none;">
                                        <input type="hidden" name="bookingID" id="bookingID" value="">
                                        <input type="hidden" name="guestID" id="guestID" value="">
                                        <input type="hidden" name="guestRole" id="guestRole" value="">
                                    </div>
                                    <div class="booking-info">
                                        <label for="eventType">Event Type</label>
                                        <input type="text" class="form-control" name="eventType" id="eventType" value=""
                                            readonly>
                                    </div>

                                    <div class="booking-info">
                                        <label for="eventDate">Booking Date</label>
                                        <input type="text" class="form-control" name="eventDate" id="eventDate" value=""
                                            readonly>
                                    </div>

                                    <div class="booking-info">
                                        <label for="eventDuration">Time Duration</label>
                                        <input type="text" class="form-control" name="eventDuration" id="eventDuration"
                                            value="" readonly>
                                    </div>

                                    <div class="booking-info">
                                        <label for="eventVenue">Venue</label>
                                        <input type="text" class="form-control" name="eventVenue" id="eventVenue"
                                            value="" readonly>
                                    </div>
                                </section>

                                <section class="serviceContainer">
                                    <label for="service">Your Service</label>
                                    <div class="service-info">
                                        <p id="service"></p>
                                    </div>
                                </section>

                                <section class="additionalNotesContainer">
                                    <label for="eventVenue">Additional Notes</label>
                                    <textarea name="additionalNotes" class="form-control"
                                        id="additionalNotes"></textarea>
                                </section>
                            </div>
                            <div class="note-section-approval">
                                <p class="text-primary text-center" id="note-approval-time"></p>
                            </div>
                            <div class="modal-footer">
                                <div class="btnContainer">
                                    <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                                    <button type="submit" class="btn btn-danger" name="rejectBtn">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- bookingModal -->

        </main>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#booking').DataTable({
                language: {
                    emptyTable: "No data available",
                },
                responsive: false,
                scrollX: true,
                columnDefs: [{
                        width: '10%',
                        targets: 0
                    },
                    {
                        width: '20%',
                        targets: 1
                    },
                    {
                        width: '15%',
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
                    {
                        width: '20%',
                        targets: 5
                    },
                ],
            });
        });
    </script>

    <script>
        function getStatusBadge(colorClass, status) {
            return `<span class="badge bg-${colorClass} text-capitalize">${status}</span>`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            const userID = document.getElementById('userID').value;
            const bookingMap = {};

            fetch(`../../Function/Partner/getPartnerBookings.php?userID=${encodeURIComponent(userID)}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || "Failed to load user data.");
                    }

                    const bookings = data.bookings;
                    const table = $('#booking').DataTable();
                    table.clear();

                    bookings.forEach(booking => {
                        bookingMap[booking.bookingID] = booking;
                        table.row.add([
                            booking.formattedBookingID,
                            booking.guestName,
                            booking.bookingType,
                            booking.service,
                            booking.bookingDate,
                            getStatusBadge(booking.color, booking.statusName),
                            `
                            <button type="button" class="btn btn-info viewInfo" data-bookingid="${booking.bookingID}"> View </button>
                        `
                        ]);
                    });

                    table.draw();
                })
                .catch(error =>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'An unknown error occurred.',
                        showConfirmButton: false,
                        timer: 1500,
                    })
                );

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('viewInfo')) {
                    const bookingID = e.target.getAttribute("data-bookingid");
                    const booking = bookingMap[bookingID];
                    if (!booking) return;

                    const viewModal = document.getElementById('bookingModal');
                    if (!viewModal) return;

                    viewModal.querySelector('.user-info img').src = booking.profileImage ||
                        '../../Assets/Images/defaultProfile.png';
                    viewModal.querySelector('.user-info .name').textContent = booking.guestName;
                    viewModal.querySelector('.user-info .contact').textContent = booking.contact;
                    viewModal.querySelector('.user-info .address').textContent = booking.address;

                    viewModal.querySelector('#eventType').value = booking.eventType;
                    viewModal.querySelector('#eventDate').value = booking.bookingDate;
                    viewModal.querySelector('#eventDuration').value = booking.timeDuration;
                    viewModal.querySelector('#eventVenue').value = booking.venue;
                    viewModal.querySelector("#service").textContent = booking.serviceInfo;
                    viewModal.querySelector('#bookingID').value = booking.bookingID;
                    viewModal.querySelector('#guestID').value = booking.guestID;
                    viewModal.querySelector('#guestRole').value = booking.guestRole;
                    viewModal.querySelector('#note-approval-time').innerHTML = `Please note that this booking must be approved by <strong> ${booking.approvalTimeUntil} </strong>. After this time, it will be automatically rejected.`;

                    viewModal.querySelector('#additionalNotes').value = booking.notes || '';

                    const modal = new bootstrap.Modal(viewModal);
                    modal.show();
                }
            });
        });
    </script>


    <script>
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const items = document.querySelectorAll('.list-group-item');
            const toggleCont = document.getElementById('toggle-container')

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
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })

                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    })
                    sidebar.classList.remove('collapsed');
                }
            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
        });
    </script>

    <!-- Show Logout -->
    <script>
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');

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
                    window.location.href = "../../../Function/logout.php";
                }
            });
        })
    </script>
</body>

</html>