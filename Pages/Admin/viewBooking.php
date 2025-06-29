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
$bookingID = $_POST['bookingID'];
$bookingType = $_POST['bookingType'];
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
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
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/viewBooking.css" />
</head>

<body>
    <!-- Guest Information Container -->
    <div class="guest-container">
        <!-- Back Button -->
        <div class="page-container">
            <a href="booking.php" class="btn btn-primary back"><img src="../../Assets/Images/Icon/whiteArrow.png" alt="Back Button"></a>
            <h5 class="page-title">Guest Booking Information</h5>
        </div>
        <!-- Information -->

        <!-- Get the user and booking information to the database -->
        <?php
        $user_query = "SELECT 
                u.* , b.*
                FROM bookings b
                LEFT JOIN users u ON b.userID = u.userID             
                LEFT JOIN bookingsservices bs ON b.bookingID = bs.bookingID
            WHERE b.bookingID = '$bookingID'";
        $user_result = mysqli_query($conn, $user_query);
        if (mysqli_num_rows($user_result) > 0) {
            $bookingInfo = mysqli_fetch_assoc($user_result);
            $name = ucfirst($bookingInfo['firstName']) . " " . ucfirst($bookingInfo['lastName']);
            $email = $bookingInfo['email'];
            $phoneNumber = $bookingInfo['phoneNumber'];
            if ($phoneNumber === NULL || $phoneNumber === "") {
                $phoneNumber = "--";
            } else {
                $phoneNumber;
            }
            $address = $bookingInfo['userAddress'];
            $profile = $bookingInfo['userProfile'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $profile);
            finfo_close($finfo);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
            $bookingID = $bookingInfo['bookingID'];
            $cost = $bookingInfo['totalCost'];
            $startDate = strtotime($bookingInfo['startDate']);
            $checkIn = date("M d, Y g:i A", $startDate);
            $startDay = date("l", $startDate);
            $endDate = strtotime($bookingInfo['endDate']);
            $checkOut = date("M d, Y g:i A", $endDate);
            $endDay = date("l", $endDate);


            if ($startDay === $endDay) {
                $Day = $startDay;
            } else {
                $Day = $startDay . " - " . $endDay;
            }


            $pax = $bookingInfo['paxNum'];
            $hoursNum = $bookingInfo['hoursNum'];

            $addOns = explode(",", $bookingInfo['addOns']);

            if (in_array("Videoke", $addOns)) {
                $videoke = "Videoke";
            } else {
                $addOns = NULL;
            }

            $paymentMethod = $bookingInfo['paymentMethod'];
        }



        $query = "SELECT 
                    b.*, st.*,
                    p.*, cp.*, cpi.*, 
                    bs.*, s.*, 
                    ra.*, rsc.categoryName AS serviceName, ec.categoryName AS eventName,
                    er.*, ps.*   
                    
                FROM bookings b

                LEFT JOIN packages p ON b.packageID = p.packageID
                LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID

                LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID
                LEFT JOIN custompackageitems cpi ON cp.customPackageID = cpi.customPackageID

                LEFT JOIN bookingsservices bs ON b.bookingID = bs.bookingID
                LEFT JOIN statuses st ON st.statusID = b.bookingStatus

                -- LEFT JOIN bookingsservices bs ON b.bookingID = bs.bookingID
                LEFT JOIN services s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)

                LEFT JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
                LEFT JOIN resortservicescategories rsc ON rsc.categoryID = ra.RScategoryID

                LEFT JOIN entranceRates er ON s.entranceRateID = er.entranceRateID

                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
            WHERE b.bookingID = '$bookingID'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $services = [];
            $adultCount = 0;
            $kidsCount = 0;
            $package = "";
            $customPackage = "";
            $status = "";
            $AddRequest = "";
            $allDescriptions = [];
            while ($data = mysqli_fetch_assoc($result)) {
                // echo "<pre>";
                // print_r($data);
                // echo "</pre>";
                if (!empty($data['serviceID'])) {
                    if (!empty($data['entranceRateID'])) {
                        $services[] = $data['sessionType'] . " Swimming";
                        if ($data['ERcategory'] === "Kids") {
                            $kidsCount = $data['guests'];
                        } elseif ($data['ERcategory'] === "Adult") {
                            $adultCount = $data['guests'];
                        }
                        $guest = "Adult: " . $adultCount . " | Kid:  " . $kidsCount;
                    }
                    if (!empty($data['partnershipServiceID'])) {
                        $services[] = $data['PBName'];
                    }
                    if (!empty($data['resortServiceID'])) {
                        $services[] = $data['RServiceName'];
                        $items = array_map('trim', explode(',', $data['RSdescription']));
                        $allDescriptions = array_merge($allDescriptions, $items);
                        $allDescriptions = array_unique($allDescriptions);
                    }
                }
                $status = $data['statusName'];
                $package = $data['eventName'];
                $customPackageID = $data['customPackageID'];
                $AddRequest = $data['additionalRequest'];

                if (!empty($package)) {
                    $pax = $data['paxNum'];
                    $serviceName = $data['packageName'];
                    $items = array_map('trim', explode(',', $data['packageDescription']));
                    $allDescriptions = array_merge($allDescriptions, $items);
                    $allDescriptions = array_unique($allDescriptions);
                }

                if (!empty($customPackageID)) {
                    $guest = $data['paxNum'];
                    if (!empty($data['serviceID'])) {
                        if (!empty($data['entranceRateID'])) {
                            $services[] = $data['sessionType'] . " Swimming";
                            if ($data['ERcategory'] === "Kids") {
                                $kidsCount = $data['guests'];
                            } elseif ($data['ERcategory'] === "Adult") {
                                $adultCount = $data['guests'];
                            }
                            $guest = "Adult: " . $adultCount . " | Kid:  " . $kidsCount;
                        }
                        if (!empty($data['partnershipServiceID'])) {
                            $services[] = $data['PBName'];
                        }
                        if (!empty($data['resortServiceID'])) {
                            $services[] = $data['RServiceName'];
                            $items = array_map('trim', explode(',', $data['RSdescription']));
                            $allDescriptions = array_merge($allDescriptions, $items);
                            $allDescriptions = array_unique($allDescriptions);
                        }
                    }
                    // $services[] = $
                }
            }
        }
        ?>

        <!-- Display the information -->
        <div class="card">
            <form action="../../Function/Admin/bookingApproval.php" method="post">
                <input type="hidden" id="status" value="<?= htmlspecialchars($status) ?>">
                <input type="hidden" id="videoke" value="<?= htmlspecialchars($videoke) ?>">
                <!-- <input type="hidden" id="serviceType" value="<?= $serviceType ?>" name="servicetype"> -->
                <div class="booking-info-name-pic-btn">
                    <div class="user-info">
                        <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start">
                        <div class="booking-info-contact">
                            <p class="card-text name"><?= $name ?></p>
                            <p class="card-text sub-name"><?= $email ?> | <?= $phoneNumber ?> </p>
                            <p class="card-text sub-name"><?= $address ?></p>
                        </div>
                    </div>

                    <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                    <input type="hidden" name="bookingStatus" value="<?= $status ?>">

                    <div class="button-container" id="button-container">
                        <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                        <button type="submit" class="btn btn-danger" name="rejectBtn">Reject</button>
                    </div>
                </div>

                <!-- Display the information -->
                <div class="card-body">
                    <div class="guest-info">
                        <h4 class="card-title important-title">Booking Type</h4>
                        <p class="card-text important-sub-title"><?= ucfirst($bookingType) ?></p>
                    </div>
                    <div class="guest-info fullWidth">
                        <h4 class="card-title important-title">Service/s</h4>
                        <p class="card-text important-sub-title">
                            <?= !empty($services) ? implode(" | ", array_unique($services)) : 'Not Available' ?>
                        </p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Guest</h4>
                        <p class="card-text"> <?= !empty($guest) ? $guest : $pax ?></p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Number of People</h4>
                        <p class="card-text"><?= !empty($pax) ? $pax : 'Not Available' ?></p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Total Price</h4>
                        <p class="card-text"><?= $cost ?></p>
                    </div>

                    <div class="guest-info fullWidth">
                        <h4 class="card-title">Schedule</h4>
                        <p class="card-text"> <?= $checkIn . " " ?> - <?= " " . $checkOut ?> </p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Day & Number of Hours</h4>
                        <p class="card-text"><?= $Day ?> <?= !empty($hoursNum) ? $hoursNum . ' hours' : 'Not Available' ?></p>
                    </div>



                    <div class="guest-info addOns">
                        <h4 class="card-title">Add Ons</h4>
                        <p class="card-text" id="addOns"><?= !empty($addOns) ? implode(", ", $addOns) : 'None' ?> </p>
                    </div>

                    <div class="guest-info" id="videokeSelectionContainer" style="display: none;">
                        <h4 class="card-title">Videoke</h4>
                        <select class="form-select" name="videokeChoice">
                            <option value="" selected disabled>Assign a videoke</option>
                            <?php
                            $selectHotel = "SELECT * FROM resortAmenities WHERE 
                            (RServiceName = 'Videoke 1' OR RServiceName = 'Videoke 2') AND RSAvailabilityID = 1";

                            $result = mysqli_query($conn, $selectHotel);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                    <option value="<?= $row['RServiceName'] ?>">
                                        <?= $row['RServiceName'] ?> — ₱<?= $row['RSprice'] ?>
                                    </option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Payment Method</h4>
                        <p class="card-text"><?= htmlspecialchars($paymentMethod) ?> </p>
                    </div>

                    <div class="two-item-row">
                        <div class="guest-info">
                            <h4 class="card-title">Service Description</h4>
                            <pre class="card-text description"><?= !empty($allDescriptions) ? implode("<br>", $allDescriptions) : 'None' ?></pre>
                        </div>
                        <div class="guest-info">
                            <h4 class="card-title">Additional Request</h4>
                            <p class="card-text"><?= !empty($AddRequest) ? $AddRequest : 'None' ?> </p>
                        </div>
                    </div>
                </div>
            </form>

        </div>

    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <script>
        const videoke = document.getElementById("videoke").value;
        const status = document.getElementById("status").value;
        const addOns = document.getElementById("addOns").textContent.trim();

        const buttonContainer = document.getElementById("button-container")
        const videokeSelectionContainer = document.getElementById("videokeSelectionContainer");

        if (videoke === 'Videoke') {
            videokeSelectionContainer.style.display = "block";
            videokeSelectionContainer.required = true;
        }

        if (status === "Approved") {
            buttonContainer.style.display = "none";
            videokeSelectionContainer.style.display = "none";
            document.querySelector(".guest-info.addOns").classList.add("fullWidth");
        } else if (addOns === "None") {
            document.querySelector(".guest-info.addOns").classList.add("fullWidth");
        }
    </script>


</body>

</html>