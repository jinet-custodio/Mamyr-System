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


if (isset($_POST['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_SESSION['bookingID']);
}

$_SESSION['last_activity'] = time();
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


        <?php
        $getUserInfo = $conn->prepare("SELECT u.*, b.*  FROM bookings b
        INNER JOIN users u ON b.userID = u.userID
        WHERE b.bookingID = ?");
        $getUserInfo->bind_param("i", $bookingID);
        $getUserInfo->execute();
        $resultUserInfo = $getUserInfo->get_result();
        if ($resultUserInfo->num_rows > 0) {
            $data = $resultUserInfo->fetch_assoc();
            $middleInitial = trim($data['middleInitial']);
            $name = ucfirst($data['firstName']) . " " . ucfirst($data['middleInitial']) . " "  . ucfirst($data['lastName']);
            $email = $data['email'];
            $phoneNumber = $data['phoneNumber'];
            $address = $data['userAddress'];

            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $imageData = $data['userProfile'];
            $mime_type = finfo_buffer($file_info, $imageData);
            finfo_close($file_info);

            $userProfile = 'data:' . $mime_type . ';base64,' . base64_encode($imageData);

            if (!empty($phoneNumber)) {
                $phoneNumber;
            } else {
                $phoneNumber = "--";
            }
        }
        ?>

        <!-- Display the information -->
        <div class="card">
            <form action="../../Function/Admin/bookingApproval.php" method="POST">
                <div class="booking-info-name-pic-btn">
                    <div class="user-info">
                        <img src="<?= htmlspecialchars($userProfile) ?>" class="img-fluid rounded-start">
                        <div class="booking-info-contact">
                            <p class="card-text name"><?= htmlspecialchars($name) ?></p>
                            <p class="card-text sub-name"><?= htmlspecialchars($email) ?> | <?= htmlspecialchars($phoneNumber) ?> </p>
                            <p class="card-text sub-name"><?= htmlspecialchars($address) ?></p>
                        </div>
                    </div>

                    <div class="button-container" id="button-container">
                        <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                        <button type="submit" class="btn btn-danger" name="rejectBtn">Reject</button>
                    </div>
                </div>

                <!-- Get booking information to the database -->
                <?php

                $getBookingInfo = $conn->prepare("SELECT 
                                
                                b.*, 
                                cb.amountPaid, 
                                cb.CBtotalCost AS finalBill, 
                                cb.userBalance, 
                                cb.confirmedBookingStatus AS cbStatus, 
                                cb.paymentDueDate, bps.statusName AS paymentStatus,
                                cb.discountAmount,
                                stat1.statusName AS bookingStatusName,
                                stat2.statusName AS confirmedBookingStatusName,
                                bs.*,
                                s.*, s.serviceType,
                                er.sessionType AS tourType, er.ERCategory, er.ERprice,
                                ra.RServiceName, ra.RSprice, rsc.categoryName AS serviceCategory   
                                    
                                FROM bookings b
                                LEFT JOIN confirmedBookings cb ON b.bookingID = cb.bookingID
                                LEFT JOIN bookingpaymentstatus bps ON cb.paymentStatus = bps.paymentStatusID 

                                LEFT JOIN statuses stat1 ON stat1.statusID = b.bookingStatus
                                LEFT JOIN statuses stat2 ON stat2.statusID = cb.confirmedBookingStatus

                                LEFT JOIN packages p ON b.packageID = p.packageID
                                LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID

                                LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID
                                LEFT JOIN custompackageitems cpi ON cp.customPackageID = cpi.customPackageID

                                LEFT JOIN bookingservices bs ON b.bookingID = bs.bookingID
                                LEFT JOIN services s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)

                                LEFT JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
                                LEFT JOIN resortservicescategories rsc ON rsc.categoryID = ra.RScategoryID

                                LEFT JOIN entranceRates er ON s.entranceRateID = er.entranceRateID

                                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
                            WHERE b.bookingID = ?");
                $getBookingInfo->bind_param("i", $bookingID);
                $getBookingInfo->execute();
                $getBookingInfoResult = $getBookingInfo->get_result();
                if ($getBookingInfoResult->num_rows > 0) {

                    $services = [];
                    $totalCost = 0;
                    $downpayment = 0;
                    $discount = 0;
                    $totalPax = 0;
                    $kidsCount = 0;
                    $adultCount = 0;
                    $finalBill  = 0;
                    $userBalance = 0;
                    $amountPaid = 0;
                    while ($row = $getBookingInfoResult->fetch_assoc()) {

                        // echo '<pre>';
                        // print_r($row);
                        // echo '</pre>';

                        $packageID = $data['packageID'];
                        $customPackageID = $data['customPackageID'];


                        $bookingType = $row['bookingType'];
                        $arrivalTime = $row['arrivalTime'] ?? 'Not Stated';
                        $startDate = date('M. d, Y', strtotime($row['startDate']));
                        $endDate = date('M. d, Y', strtotime($row['endDate']));

                        if ($startDate === $endDate) {
                            $bookingDate = date('F d, Y', strtotime($row['startDate']));
                        } else {
                            $bookingDate = $startDate . " - " . $endDate;
                        }

                        $bookingCreationDate = date('F d,Y g:i A', strtotime($row['createdAt']));

                        $time = date("g:i A", strtotime($data['startDate'])) . " - " . date("g:i A", strtotime($data['endDate']));
                        $duration = $data['hoursNum'] . " hours";

                        $bookingStatusName = $row['bookingStatusName'];
                        $confirmedBookingStatusName = $row['confirmedBookingStatusName'];

                        $additionalServices = $row['addOns'];
                        $paymentMethod = $row['paymentMethod'];
                        $paymentStatus = $row['paymentStatus'];
                        $totalCost = $row['totalCost'];
                        $downpayment = $row['downpayment'];
                        $discount = $row['discountAmount'];
                        $userBalance = $row['userBalance'];
                        $amountPaid = $row['amountPaid'];
                        $finalBill = $row['finalBill'];
                        $paymentDueDate = date('F d, Y g:i A', strtotime($row['paymentDueDate']));

                        $toddlerCount = $row['toddlerCount'];
                        $additionalReq = $row['additionalRequest'];

                        if (!empty($packageID)) {
                            echo 'Wala pa';
                        } elseif (!empty($customPackageID)) {
                        } else {
                            $serviceID = $row['serviceID'];
                            $serviceType = $row['serviceType'];


                            if ($serviceType === 'Resort') {
                                $services[] = $row['RServiceName'] . " - ₱"  . number_format($row['RSprice'], 2);
                            }

                            if ($serviceType === 'Entrance') {
                                $tourType = $row['tourType'];
                                if ($row['ERCategory'] === "Kids") {
                                    $kidsCount = $row['guests'];
                                } elseif ($row['ERCategory'] === "Adult") {
                                    $adultCount = $row['guests'];
                                }

                                $totalPax =  ($adultCount > 0 ? "{$adultCount} Adults" : '') .
                                    ($kidsCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$kidsCount} Kids" : '') .
                                    ($toddlerCount > 0 ? (($adultCount > 0 || $kidsCount > 0) ? ' & ' : '') . "{$toddlerCount} toddlers" : '');
                            }
                        }

                        if ($finalBill === 0.00) {
                            $totalBill = $totalCost;
                        } elseif ($finalBill >= $totalCost || $finalBill <= $totalCost) {
                            $totalBill = $finalBill;
                        }
                    }
                }

                ?>

                <!-- Display the information -->
                <div class="card" style="border: 1px solid red;">
                    <div class="info-container">
                        <label for="bookingType">Booking Type</label>
                        <input type="hidden" name="bookingType" id="bookingType" value="<?= $bookingType ?>">
                        <input type="text" name="bookingType" value="<?= $bookingType ?> Booking">
                    </div>
                    <div class="info-container">
                        <label for="tourType">Tour Type</label>
                        <input type="hidden" name="tourType" id="tourType" value="<?= $tourType ?>">
                        <input type="text" name="tourType" value="<?= $tourType ?> Swimming">
                    </div>
                    <div class="datesContainer" style="border: 1px solid blue;">
                        <h1 class="card-title">Date and Time</h1>
                        <div class="info-container">
                            <label for="arrivalTime">Time Arrival</label>
                            <input type="text" name="arrivalTime" id="arrivalTime" value="<?= $arrivalTime ?>">
                        </div>

                        <div class="info-container">
                            <label for="timeDuration">Time Duration</label>
                            <input type="text" name="timeDuration" id="timeDuration" value="<?= $time ?> (<?= $duration ?>)">
                        </div>

                        <div class="info-container">
                            <label for="bookingDate">Booking Date</label>
                            <input type="text" name="bookingDate" id="bookingDate" value="<?= $bookingDate ?>">
                        </div>
                        <div class="info-container">
                            <label for="bookingCreationDate">Booking Creation Date</label>
                            <input type="text" name="bookingCreationDate" id="bookingCreationDate" value="<?= $bookingCreationDate ?>">
                        </div>
                    </div>

                    <div class="bookingDetails" style="border: 1px solid pink;">
                        <div class="servicesDetails" style="border: 1px solid yellow;">
                            <h1 class="card-title">Services</h1>
                            <div class="servicesInfo">
                                <ul>
                                    <?php
                                    foreach ($services as $service) {
                                    ?>
                                        <li><?= $service ?></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <div class="additionalServices" style="border: 1px solid green;">
                            <label for="addOns">Additional Services</label>
                            <input type="text" name="addOns" id="addOns" value="<?= $additionalServices ?>">
                        </div>
                        <div class="peopleCountContainer" style="border: 1px solid orange;">
                            <label for="paxNum">Number of People:</label>
                            <input type="text" name="paxNum" id="paxNum" value="<?= $totalPax ?>">
                        </div>
                    </div>


                    <div class="paymentDetails" style="border: 1px solid purple;">
                        <h1 class="card-title">
                            Payment Details
                        </h1>
                        <div class="info-container">
                            <label for="paymentMethod">Payment Method</label>
                            <input type="text" name="paymentMethod" id="paymentMethod" value="<?= $paymentMethod ?>">
                        </div>
                        <div class="info-container">
                            <label for="paymentStatus">Payment Status</label>
                            <input type="text" name="paymentStatus" id="paymentStatus" value="<?= $paymentStatus ?>">
                        </div>
                        <?php if ($bookingStatusName === 'Approved') { ?>
                            <div class="info-container">
                                <label for="paymentDue">Payment Due Date</label>
                                <input type="text" name="paymentDue" id="paymentDue" value="<?= $paymentDueDate ?>">
                            </div>
                            <div class="info-container">
                                <label for="userBalance">User Balance</label>
                                <input type="text" name="userBalance" id="userBalance" value="₱<?= number_format($userBalance, 2) ?>">
                            </div>
                            <div class="info-container">
                                <label for="amountPaid">Amount Paid</label>
                                <input type="text" name="amountPaid" id="amountPaid" value="₱<?= number_format($amountPaid, 2) ?>">
                            </div>
                        <?php } ?>
                        <div class="info-container">
                            <label for="discountAmount">Discount</label>
                            <input type="text" name="discountAmount" id="discountAmount" value="₱<?= number_format($discount, 2) ?>">
                        </div>
                        <div class="info-container">
                            <label for="downpayment">Downpayment</label>
                            <input type="text" name="downpayment" id="downpayment" value="₱<?= number_format($downpayment, 2) ?>">
                        </div>
                        <div class="info-container">
                            <label for="totalCost">Total Cost</label>
                            <input type="text" name="totalCost" id="totalCost" value="₱<?= number_format($totalBill, 2) ?>">
                        </div>
                    </div>

                    <div class="notesContainer" style="border: 1px solid skyblue;">
                        <h1 class="card-title"> Notes</h1>
                        <div class="info-container">
                            <label for="req">Additional Request(s)/Note(s)</label>
                            <input type="text" name="req" id="req" value="<?= $additionalReq ?>">
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
        const bookingType = document.getElementById("bookingType").value;
        const status = document.getElementById("status").value;
        const addOns = document.getElementById("addOns").textContent.trim();

        const buttonContainer = document.getElementById("button-container")
        const videokeSelectionContainer = document.getElementById("videokeSelectionContainer");
        const downpaymentContainer = document.getElementById("downpayment");
        const paymentStatusContainer = document.getElementById("paymentStatus");

        if (videoke === 'Videoke') {
            videokeSelectionContainer.style.display = "block";
            videokeSelectionContainer.required = true;
        }

        if (bookingType === "Resort") {
            downpaymentContainer.style.display = "none";
            document.querySelector(".guest-info.payment").classList.add("fullWidth");
        }

        if (status === "Approved") {
            buttonContainer.style.display = "none";
            videokeSelectionContainer.style.display = "none";
            document.querySelector(".guest-info.addOns").classList.add("fullWidth");
            document.querySelector(".guest-info.payment").classList.remove("fullWidth");
            paymentStatusContainer.style.display = "block";
        }

        if (addOns === "None") {
            document.querySelector(".guest-info.addOns").classList.add("fullWidth");
        }

        if (status === "Cancelled" || status === "Rejected") {
            buttonContainer.style.display = "none";
        }
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');

        if (paramValue === "videoke") {
            Swal.fire({
                title: "Oops!",
                text: "Please assign a videoke.",
                icon: 'warning',
            });
        } else if (paramValue === "error") {
            Swal.fire({
                title: "Failed!",
                text: "The booking request could not be approved. Please try again later.",
                icon: 'error',
            });
        }
    </script>


</body>

</html>