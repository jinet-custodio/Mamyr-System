<?php
require '../../Config/dbcon.php';

// $session_timeout = 3600;

// ini_set('session.gc_maxlifetime', $session_timeout);
// session_set_cookie_params($session_timeout);
session_start();
// date_default_timezone_set('Asia/Manila');

// if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
//     header("Location: ../register.php");
//     exit();
// }

// if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
//     $_SESSION['error'] = 'Session Expired';

//     session_unset();
//     session_destroy();
//     header("Location: ../register.php?session=expired");
//     exit();
// }

// $_SESSION['last_activity'] = time();

$userID = $_SESSION['userID'];
$userType = $_SESSION['userType'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
</head>

<body>

    <!-- Booking-container -->
    <div class="booking-container">
        <table class="table" id="bookingTable">

            <thead>
                <th>Guest</th>
                <th>Service Type</th>
                <th>Check-in</th>
                <th>Status</th>
                <th>Action</th>
            </thead>
            <tbody>
                <!-- Select booking info -->
                <?php
                $selectQuery = "SELECT u.firstName, u.lastName, ps.PBName, rs.category , ec.categoryName, b.* 
                FROM bookings b
                INNER JOIN users u ON b.userID = u.userID
                LEFT JOIN packages p ON b.packageID = p.packageID
                LEFT JOIN eventcategories ec ON p.categoryID = ec.categoryID
                LEFT JOIN services s ON b.serviceID = s.serviceID
                LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID
                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
                ";
                $result = mysqli_query($conn, $selectQuery);
                if (mysqli_num_rows($result) > 0) {
                    foreach ($result as $bookings) {
                        $bookingID = $bookings['bookingID'];
                        $name = ucfirst($bookings['firstName']) . " " . ucfirst($bookings['lastName'])
                ?>
                        <tr>
                            <td><?= $name ?></td>
                            <?php
                            if ($bookings['serviceID'] != "") {
                            ?>
                                <td><?= $bookings['category'] ?></td>
                            <?php
                            } elseif ($bookings['packageID'] != "") {
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
                                if ($bookings['status'] == "Pending") {
                                ?>
                                    <button class="btn btn-warning">
                                        <?= $bookings['status'] ?>
                                    </button>
                                <?php
                                } elseif ($bookings['status'] == "Approved") {
                                ?>
                                    <button class="btn btn-success">
                                        <?= $bookings['status'] ?>
                                    </button>
                                <?php
                                } elseif ($bookings['status'] == "Cancelled") {
                                ?>
                                    <button class="btn btn-danger">
                                        <?= $bookings['status'] ?>
                                    </button>
                                <?php
                                }
                                ?>
                            </td>
                            <td>
                                <form action="viewBooking.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                                    <!-- <input type="hidden" name="userID" value="<?= $userID ?>"> -->
                                    <button type="submit" class="btn btn-info">View</button>
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

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#bookingTable').DataTable();
        });
    </script>
</body>

</html>