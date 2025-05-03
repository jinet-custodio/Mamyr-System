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
$bookingID = $_POST['bookingID'];
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
</head>

<body>
    <!-- Guest Information Container -->
    <div class="guest-container">
        <!-- Back Button -->
        <div>
            <a href="booking.php" class="btn btn-primary"><img src="../../Assets/Images/Icon/backbtn_black.png" alt="Back Button"></a>
            <h5>Guest Information</p>
            </h5>
        </div>
        <!-- Information -->
        <div class="card">
            <!-- Get the information to the database -->
            <?php
            $query = "SELECT 
                u.* , s.*, p.*, ec.categoryName, rs.*, ps.*, cp.*, b.*,
                p.capacity  as p_capacity
            FROM bookings b
                INNER JOIN users u ON b.userID = u.userID 
                LEFT JOIN packages p ON b.packageID = p.packageID
                LEFT JOIN eventcategories ec ON p.categoryID = ec.categoryID
                LEFT JOIN services s ON b.serviceID = s.serviceID
                LEFT JOIN customPackages cp ON b.customPackageID = cp.customPackageID
                LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID
                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
            WHERE bookingID = '$bookingID'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $name = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
                $email = $data['email'];
                $phoneNumber = $data['phoneNumber'];
                if ($phoneNumber === NULL || $phoneNumber === "") {
                    $phoneNumber = "--";
                } else {
                    $phoneNumber;
                }
                // $birthday = $data['birthDate'];
                // if ($birthday === NULL || $birthday === "") {
                //     $birthday = "--";
                // } else {
                //     $birthday;
                // }
                $address = $data['userAddress'];
                $profile = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $profile);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);

                $status = $data['status'];
                $service = $data['category'];
                $package = $data['categoryName'];
                $customPackage = $data['customPackageID'];

                if ($service != "") {
                    $booking = $service;
                    $pax = $data['capacity'];
                    $serviceName = $data['facilityName'];
                    $description = $data['description'];
                } elseif ($package != "") {
                    $booking = $package;
                    $pax = $data['p_capacity'];
                    $serviceName = $data['packageName'];
                    $description = $data['packageDescription'];
                } elseif ($customPackage != "") {
                    $booking = $customPackage;
                }
                $cost = $data['totalCost'];
                $startDate = $data['startDate'];
                $endDate = $data['endDate'];
                $AddRequest = $data['additionalRequest'];
            }
            ?>
            <!-- Display the information -->
            <div class="card mb-3" style="max-width: 540px;">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($applicantName) ?> ">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <!-- <h3 class="card-title">Partner</h3> -->
                            <div class="guest-info">
                                <h4 class="card-title">Name</h4>
                                <p class="card-text"><?= $name ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Email</h4>
                                <p class="card-text"><?= $email ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Phone Number</h4>
                                <p class="card-text"><?= $phoneNumber ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Address</h4>
                                <p class="card-text"><?= $address ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Display the information -->
            <div class="card mb-3" style="max-width: 540px;">
                <div class="row g-0">
                    <div class="col-md-8">
                        <div class="card-body">
                            <div class="guest-info">
                                <h4 class="card-title">Booking Type</h4>
                                <p class="card-text"><?= ucfirst($booking) ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Service Name</h4>
                                <p class="card-text"><?= ucwords($serviceName) ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Description</h4>
                                <pre class="card-text"><?= !empty($description) ? htmlspecialchars($description) : 'Not Available' ?></pre>
                            </div>

                            <div class="guest-info">
                                <h4 class="card-title">Number of People</h4>
                                <p><?= !empty($pax) ? $pax : 'Not Available' ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Total Price</h4>
                                <p><?= $cost ?></p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Schedule</h4>
                                <p><?= $startDate . " " ?> until <?= " " . $endDate ?> </p>
                            </div>
                            <div class="guest-info">
                                <h4 class="card-title">Request</h4>
                                <p><?= !empty($AddRequest) ? $AddRequest : 'Not Available' ?> </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-container">
                <form action="../../Function/Admin/bookingApproval.php" method="post">
                    <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                    <input type="hidden" name="bookingStatus" value="<?= $status ?>">
                    <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                    <button type="submit" class="btn btn-danger" name="declineBtn">Reject</button>
                </form>
            </div>
        </div>
    </div>



    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
</body>

</html>