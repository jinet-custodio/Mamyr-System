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
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
</head>

<body>
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img src="../../Assets/images/MamyrLogo.png" alt=""
                    class="logo"></a>
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
                $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $firstName = $row['firstName'];
                } else {
                    $firstName = 'None';
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
                <img src="../../Assets/Images/Icon/profile.png" alt="home icon">
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


        <a class="nav-link" href="#">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link" href="#">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <!-- <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
                <h5>Revenue</h5>
            </a> -->


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

        <div class="card " style="width: 80rem;">
            <table class="table table-striped" id="bookingTable">

                <thead>
                    <th scope="col">Guest</th>
                    <th scope="col">Service Type</th>
                    <th scope="col">Check-in</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </thead>
                <tbody>
                    <!-- Select booking info -->
                    <?php
                    $selectQuery = "SELECT u.firstName, u.lastName, 
                    ps.*, rs.*, cp.*,
                    rsc.categoryName AS resortCategoryName , 
                    ec.categoryName AS eventCategoryName,
                    st.statusName,
                    b.*
                FROM bookings b
                INNER JOIN users u ON b.userID = u.userID   -- to get  the firstname and lastname 
                LEFT JOIN statuses st ON st.statusID = b.bookingStatus  -- to get the status name
                LEFT JOIN packages p ON b.packageID = p.packageID  -- to get the info of the package na binook 
                LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID    -- to get the event name of the package 
                LEFT JOIN services s ON b.serviceID = s.serviceID   -- to get the info of the service na binook 
                LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID  -- information of service
                LEFT JOIN resortservicescategories rsc ON rsc.categoryID = rs.RScategoryID  -- status
                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID -- info of service
                LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID  -- info of the custom package
                ";
                    $result = mysqli_query($conn, $selectQuery);
                    if (mysqli_num_rows($result) > 0) {
                        foreach ($result as $bookings) {
                            $bookingID = $bookings['bookingID'];
                            $name = ucfirst($bookings['firstName']) . " " . ucfirst($bookings['lastName']);
                            $status = $bookings['statusName'];
                    ?>
                            <tr>
                                <td><?= $name ?></td>
                                <?php
                                if ($bookings['serviceID'] != "") {
                                ?>
                                    <td><?= $bookings['resortCategoryName'] ?></td>
                                <?php
                                } elseif ($bookings['eventCategoryName'] != "") {
                                ?>
                                    <td><?= $bookings['categoryName'] ?></td>
                                <?php
                                } elseif ($bookings['customePackageID'] != "") {
                                ?>
                                    <td>Customized Package</td>
                                <?php
                                }
                                ?>
                                <td><?= $bookings['startDate'] ?></td>
                                <td>
                                    <?php
                                    if ($status == "Pending") {
                                    ?>
                                        <button class="btn btn-warning w-75">
                                            <?= $status ?>
                                        </button>
                                    <?php
                                    } elseif ($status == "Approved") {
                                    ?>
                                        <button class="btn btn-success w-75">
                                            <?= $status ?>
                                        </button>
                                    <?php
                                    } elseif ($status == "Cancelled") {
                                    ?>
                                        <button class="btn btn-danger w-75">
                                            <?= $status ?>
                                        </button>
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form action="viewBooking.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                                        <!-- <input type="hidden" name="userID" value="<?= $userID ?>"> -->
                                        <button type="submit" class="btn btn-info w-75">View</button>
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
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#bookingTable').DataTable();
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
    </script>
</body>

</html>