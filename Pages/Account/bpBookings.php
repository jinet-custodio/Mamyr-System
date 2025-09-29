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

require '../../Function/Partner/getBookings.php';
require '../../Function/functions.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner Services - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
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
            <div class="d-flex" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home text-center">
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
                <h5 class="sidebar-text">User Account</h5>
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item">
                        <i class="fa-solid fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>


                <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                            <i class="fa-solid fa-table-list sidebar-icon"></i>
                            <span class="sidebar-text">Payment & Booking History</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="userManagement.php" class="list-group-item">
                            <i class="fa-solid fa-people-roof sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item active">
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

                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="fa-solid fa-user-slash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                        style="margin: 3vw auto;">
                        <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside> <!-- End Side Bar -->

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
                            <!-- Get availed service -->
                            <?php
                            $message = '';
                            try {
                                $getAvailedService = $conn->prepare("SELECT b.bookingID, LPAD(b.bookingID , 4, '0') AS formattedBookingID,
                                        u.firstName, u.lastName, b.bookingType, ps.PBName, b.startDate, b.endDate, bpas.approvalStatus
                                        FROM businesspartneravailedservice bpas
                                        LEFT JOIN booking b ON bpas.bookingID = b.bookingID
                                        LEFT JOIN user u ON u.userID = b.userID
                                        LEFT JOIN partnershipservice ps ON bpas.partnershipServiceID = ps.partnershipServiceID
                                        LEFT JOIN partnership p ON ps.partnershipID = p.partnershipID
                                        WHERE p.userID = ?");
                                $getAvailedService->bind_param('i', $userID);
                                if (!$getAvailedService->execute()) {
                                    throw new Exception("Error executing availed service: User ID: $userID. Error=>" . $getAvailedService->error);
                                }

                                $result = $getAvailedService->get_result();

                                if ($result->num_rows === 0) {
                                    $message =  'No booking to display';
                                }
                                $bookings = [];
                                while ($row = $result->fetch_assoc()) {
                                    $rawStartDate = $row['startDate'] ?? null;
                                    $startDate = DATE('m-d-y g:i A', strtotime($rawStartDate));
                                    $bookings[] = [
                                        'formattedBookingID' => $row['formattedBookingID'],
                                        'guestName' => $row['firstName'] . ' ' . $row['lastName'],
                                        'bookingType' => $row['bookingType'] . ' Booking',
                                        'service' => $row['PBName'],
                                        'bookingDate' => $startDate,
                                        'approvalStatusID' => $row['approvalStatus']
                                    ];
                                }
                            } catch (Exception $e) {
                                error_log("An error occured. Error-> " . $e->getMessage());
                            }


                            foreach ($bookings as $booking) {
                            ?>
                                <tr>
                                    <td><?= $booking['formattedBookingID'] ?></td>
                                    <td><?= $booking['guestName'] ?></td>
                                    <td><?= $booking['bookingType'] ?></td>
                                    <td><?= $booking['service'] ?></td>
                                    <td><?= $booking['bookingDate'] ?></td>
                                    <?php
                                    $status = getStatuses($conn, $booking['approvalStatusID']);
                                    $statusName = ucwords($status['statusName']);
                                    switch ($statusName) {
                                        case 'Pending':
                                            $className = 'warning';
                                            break;
                                        case 'Approved':
                                            $className = 'success';
                                            break;
                                        case 'Cancelled':
                                            $className = 'red';
                                            break;
                                        case 'Rejected':
                                            $className = 'danger';
                                            break;
                                        case 'Done':
                                            $className = 'light-green';
                                            break;
                                        case 'Expired':
                                            $className = 'secondary';
                                            break;
                                        default:
                                            $className = 'warning';
                                            break;
                                    }
                                    ?>
                                    <td>
                                        <span class="btn btn-<?= $className ?> w-75"><?= $statusName ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#bookingModal">View</button>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- bookingModal -->
            <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="booking"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel">Booking Info</h4>
                        </div>
                        <div class="modal-body">
                            <section class="user-info-container">
                                <div class="booking-info-name-pic-btn">
                                    <div class="user-info">
                                        <img src="../../Assets/Images/defaultProfile.png"
                                            class="img-fluid rounded-start" alt="Profile Image">
                                        <div class="booking-info-contact">
                                            <p class="card-text name">Mica Lee</p>
                                            <p class="card-text sub-name">micalee.bini@gmail.com |
                                                09235467831 </p>
                                            <p class="card-text sub-name">243, E. Viudez St., Poblacion, San Ildefonso,
                                                Bulacan</p>
                                        </div>
                                    </div>
                            </section>

                            <section class="booking-info-container">
                                <div class="booking-info">
                                    <label for="eventType">Event Type</label>
                                    <input type="text" class="form-control" name="eventType" id="eventType"
                                        value="Birthday" readonly>
                                </div>

                                <div class="booking-info">
                                    <label for="eventDate">Booking Date</label>
                                    <input type="text" class="form-control" name="eventDate" id="eventDate"
                                        value="October 08, 2025" readonly>
                                </div>

                                <div class="booking-info">
                                    <label for="eventDuration">Time Duration</label>
                                    <input type="text" class="form-control" name="eventDuration" id="eventDuration"
                                        value="2:00 PM - 7:00 PM (5 hours)" readonly>
                                </div>

                                <div class="booking-info">
                                    <label for="eventVenue">Venue</label>
                                    <input type="text" class="form-control" name="eventVenue" id="eventVenue"
                                        value="Mini Function Hall" readonly>
                                </div>
                            </section>

                            <section class="additionalServiceContainer">
                                <label for="additionalService">Addtional Service</label>
                                <div class="additionalService-info">
                                    <p class="additionalServiceName">Snapshot Photography - Photography</p>
                                    <p class="additionalServiceprice">₱2000</p>
                                </div>
                            </section>

                            <section class="additionalNotesContainer">
                                <label for="eventVenue">Addtional Notes</label>
                                <textarea name="additionalNotes" class="form-control" id="additionalNotes"></textarea>
                            </section>


                        </div>
                        <div class="modal-footer">
                            <div class="btnContainer">
                                <button type="submit" class="btn btn-primary">Approve</button>
                                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close">Reject</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- bookingModal -->
            <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModal"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel">Service Info</h4>
                        </div>
                        <div class="modal-body">
                            <section class="pic-info">
                                <div class="picContainer">
                                    <img src="../../Assets/Images/no-picture.jpg" alt="Service Picture"
                                        class="servicePic">
                                </div>

                                <div class="infoContainer">
                                    <div class="info-container">
                                        <label for="serviceName">Service Name</label>
                                        <input type="text" class="form-control" name="serviceName" id="serviceName"
                                            value="Snapshot Photography" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="servicePrice">Price</label>
                                        <input type="text" class="form-control" name="servicePrice" id="servicePrice"
                                            value="₱2000" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="serviceCapacity">Capacity</label>
                                        <input type="text" class="form-control" name="serviceCapacity"
                                            id="serviceCapacity" value="Unlimited Shots" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="serviceDuration">Service Duration</label>
                                        <input type="text" class="form-control" name="serviceDuration"
                                            id="serviceDuration" value="5 hours" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="serviceAvailable">Service Availability</label>
                                        <input type="text" class="form-control" name="serviceAvailability"
                                            id="serviceAvalability" value="Available" readonly>
                                    </div>
                                </div>
                            </section>

                            <section class="descContainer">
                                <div class="form-group">
                                    <label for="serviceDescription">Service Description</label>
                                    <textarea class="form-control" name="serviceDescription" id="serviceDescription"
                                        rows="60">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Neque expedita maxime quo obcaecati, corporis, sunt mollitia similique suscipit dolorem ipsam quia iure laborum, esse ducimus explicabo voluptatum autem temporibus quidem!</textarea>
                                </div>
                            </section>
                        </div>
                        <div class="modal-footer">
                            <div class="declineBtnContainer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">Close</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- bookingModal -->
        </main>
    </div>



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
                    emptyTable: <?= json_encode($message ?: "No data available") ?>
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

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
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

    <!-- Show -->
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