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
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/booking.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
</head>

<body>
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <a href="#" class="notifs">
                <img src="../../Assets/Images/Icon/notification.png" alt="Notification Icon">
            </a>
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
            <a href="Account/account.php" class="admin">
                <img src="<?= htmlspecialchars($image) ?>" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar">

        <a class="nav-link" href="adminDashboard.php">
            <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
            <h5>Dashboard</h5>
        </a>

        <a class="nav-link active" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>


        <a class="nav-link" href="roomList.php">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link" href="transaction.php">
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

        <a class="nav-link" href="#">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

    </nav>

    <!-- Booking-container -->

    <div class="booking-container">
        <div class="card" style="width: 80rem;">

            <div class="btnContainer">
                <a href="createBooking.php" class="btn btn-primary">Create</a>
            </div>

            <table class="table table-striped" id="bookingTable">
                <thead>
                    <th scope="col">Booking ID</th>
                    <th scope="col">Guest</th>
                    <th scope="col">Booking Type</th>
                    <th scope="col">Check-in</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </thead>
                <tbody>
                    <!-- Select booking info -->
                    <?php
                    $getBookingInfo = $conn->prepare("SELECT LPAD(b.bookingID, 4, 0) AS formattedBookingID, u.firstName,u.middleInitial, u.lastName, b.*,
                    cp.*, ec.categoryName AS eventCategoryName,
                    cb.*, s.statusName AS confirmedStatus, stat.statusName as bookingStatus
                                    FROM bookings b
                                    INNER JOIN users u ON b.userID = u.userID   -- to get  the firstname, M.I and lastname 
                                    LEFT JOIN confirmedBookings cb ON b.bookingID = cb.bookingID 
                                    LEFT JOIN statuses s ON cb.confirmedBookingStatus = s.statusID -- to get the status name
                                    LEFT JOIN statuses stat ON b.bookingStatus = stat.statusID  -- to get the status name
                                    LEFT JOIN packages p ON b.packageID = p.packageID  -- to get the info of the package na binook 
                                    LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID    -- to get the event name of the package 
                                    -- LEFT JOIN bookingsservices bs ON b.bookingID = bs.bookingID
                                    -- LEFT JOIN services s ON bs.serviceID = s.serviceID   -- to get the info of the service na binook 
                                    -- LEFT JOIN resortamenities rs ON s.resortServiceID = rs.resortServiceID  -- info of service
                                    -- LEFT JOIN resortservicescategories rsc ON rsc.categoryID = rs.RScategoryID  -- status
                                    -- LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID -- info of service
                                    LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID  -- info of the custom package
                                    ");
                    $getBookingInfo->execute();
                    $getBookingInfoResult = $getBookingInfo->get_result();
                    if ($getBookingInfoResult->num_rows > 0) {
                        while ($bookings = $getBookingInfoResult->fetch_assoc()) {
                            // echo "<pre>";
                            // print_r($bookings);
                            // echo "</pre>";
                            $bookingID = $bookings['formattedBookingID'];
                            $startDate = strtotime($bookings['startDate']);
                            $checkIn = date("d F Y", $startDate);
                            $middleInitial = trim($bookings['middleInitial']);
                            $name = ucfirst($bookings['firstName']) . " " . ucfirst($bookings['middleInitial']) . " "  . ucfirst($bookings['lastName']);

                            $bookingType = $bookings['bookingType'];

                            if (!empty($bookings['confirmedBookingID'])) {
                                if ($bookings['confirmedStatus'] === "Pending") {
                                    if ($bookingType === 'Resort') {
                                        $status = "Onsite payment";
                                        $addClass = "btn btn-info w-100";
                                    } else {
                                        $status = "Downpayment";
                                        $addClass = "btn btn-primary w-100";
                                    }
                                } elseif ($bookings['confirmedStatus'] === "Approved") {
                                    $status = "Successful";
                                    $addClass = "btn btn-success w-100";
                                } elseif ($bookings['confirmedStatus'] === "Rejected") {
                                    $status = "Rejected";
                                    $addClass = "btn btn-danger w-100";
                                }
                            } else {
                                $confirmedBookingID = NULL;
                                if ($bookings['bookingStatus'] === "Pending") {
                                    $status = "Pending";
                                    $addClass = "btn btn-warning w-100";
                                } else if ($bookings['bookingStatus'] === "Approved") {
                                    if ($bookingType === 'Resort') {
                                        $status = "Onsite payment";
                                        $addClass = "btn btn-info w-100";
                                    } else {
                                        $status = "Downpayment";
                                        $addClass = "btn btn-primary w-100";
                                    }
                                } elseif ($bookings['bookingStatus'] === "Cancelled") {
                                    $status = "Cancelled";
                                    $addClass = "btn btn-dark w-100";
                                } elseif ($bookings['bookingStatus'] === "Rejected") {
                                    $status = "Rejected";
                                    $addClass = "btn btn-danger w-100";
                                }
                            }
                            // $status = $bookings['statusName'];
                            // if ($bookings['eventCategoryName'] != "") {
                            //     $bookingType = "Event Booking";
                            // } elseif ($bookings['customPackageID'] != "") {
                            //     $bookingType = "Customized Package";
                            // } else {
                            //     $bookingType = "Resort Booking";
                            // }

                    ?>
                            <tr>
                                <td><?= htmlspecialchars($bookingID) ?></td>
                                <td><?= htmlspecialchars($name) ?></td>
                                <td><?= htmlspecialchars($bookingType) ?>&nbsp;Booking</td>
                                <td><?= $checkIn ?></td>
                                <td>
                                    <a class=" <?= $addClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </a>
                                </td>
                                <td>
                                    <form action="viewBooking.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                                        <input type="hidden" name="bookingStatus"
                                            value="<?= !empty($bookings['bookingStatus']) ? !empty($bookings['bookingStatus']) : !empty($bookings['confirmedStatus'])  ?>">
                                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                                        <button type="submit" class="btn btn-primary w-75">View</button>
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
    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#bookingTable').DataTable({
                columnDefs: [{
                        width: '10%',
                        targets: 0
                    },
                    {
                        width: '15%',
                        targets: 2
                    },
                    {
                        width: '15%',
                        targets: 4
                    },
                ],
            });
        });
    </script>
    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        <?php if (!empty($message)): ?>
            Swal.fire({
                icon: '<?= $status ?>',
                title: '<?= ($status == 'error') ? 'Rejected' : 'Success' ?>',
                text: '<?= $message ?>'
            });
        <?php endif; ?>


        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');

        if (paramValue === "success") {
            Swal.fire({
                title: "Booking Approved!",
                text: "The booking has been successfully approved.",
                icon: 'success',
            });
        } else if (paramValue === "error") {
            Swal.fire({
                title: "Action Failed!",
                text: "The booking could not be approved or rejected. Please try again later.",
                icon: 'error',
            });
        } else if (paramValue === 'rejected') {
            Swal.fire({
                title: "Booking Rejected!",
                text: "The booking has been successfully rejected.",
                icon: 'success',
            });
        }

        if (paramValue) {
            const url = new URL(windows.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString)
        }
    </script>
</body>

</html>