<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

require '../../Function/Helpers/statusFunctions.php';

$userID = $_SESSION['userID'] ?? '';
$userRole = $_SESSION['userRole'] ?? '';


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

if (isset($_POST['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $_SESSION['bookingID'] = $bookingID;
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_SESSION['bookingID']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <!-- icon library from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/viewBooking.css" />
</head>

<body>
    <!-- Guest Information Container -->
    <div class="guest-container">
        <!-- Back Button -->
        <div class="page-container">
            <?php
            $button = !empty($_POST['button']) ? mysqli_real_escape_string($conn, $_POST['button']) : 'payment';
            if ($button === 'booking') { ?>
            <a href="booking.php" class="btn btn-primary back"><img src="../../Assets/Images/Icon/arrowBtnWhite.png"
                    alt="Back Button"></a>
            <?php   } elseif ($button === 'payment') {  ?>
            <form action="viewPayments.php" method="POST" style="display:inline;">
                <input type="hidden" name="bookingID" value="<?= htmlspecialchars($bookingID) ?>">
                <button type="submit" class="btn btn-primary back">
                    <img src="../../Assets/Images/Icon/arrowBtnWhite.png" alt="Back Button">
                </button>
            </form>
            <?php   } ?>
            <h5 class="page-title">Guest Booking Information</h5>
        </div>

        <!-- Information -->


        <?php
        $getUserInfo = $conn->prepare("SELECT u.*, b.*  FROM booking b
        INNER JOIN user u ON b.userID = u.userID
        WHERE b.bookingID = ?");
        $getUserInfo->bind_param("i", $bookingID);
        $getUserInfo->execute();
        $resultUserInfo = $getUserInfo->get_result();
        if ($resultUserInfo->num_rows > 0) {
            $data = $resultUserInfo->fetch_assoc();
            $customerID = (int) $data['userID'];
            $middleInitial = trim($data['middleInitial']);
            $name = ucfirst($data['firstName']) . " " . ucfirst($data['middleInitial']) . " "  . ucfirst($data['lastName']);
            $email = $data['email'];
            $phoneNumber = $data['phoneNumber'];
            $address = $data['userAddress'];
            $userRoleID = $data['userRole'];

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
                            <p class="card-text sub-name"><?= htmlspecialchars($email) ?> |
                                <?= htmlspecialchars($phoneNumber) ?> </p>
                            <p class="card-text sub-name"><?= htmlspecialchars($address) ?></p>
                        </div>
                    </div>

                    <div class="button-container" id="button-container">
                        <button type="button" class="btn btn-primary approveReject" data-bs-toggle="modal"
                            data-bs-target="#approvalModal">Approve</button>
                        <button type="button" class="btn btn-danger approveReject" data-bs-toggle="modal"
                            data-bs-target="#rejectionModal">Reject</button>
                    </div>
                </div>


                <!-- Get booking information to the database -->
                <?php

                $getBookingInfo = $conn->prepare("SELECT 
                                                    b.bookingID, 
                                                    b.bookingType, 
                                                    b.customPackageID, 
                                                    b.additionalRequest, 
                                                    b.toddlerCount, 
                                                    b.kidCount, 
                                                    b.adultCount, 
                                                    b.guestCount, 
                                                    b.durationCount, 
                                                    b.arrivalTime, 
                                                    b.startDate, 
                                                    b.endDate, 
                                                    b.paymentMethod, 
                                                    b.totalCost AS originalBill, 
                                                    b.downpayment, 
                                                    b.bookingStatus, 
                                                    b.createdAt,  

                                                    cp.eventTypeID, 
                                                    cp.customPackageTotalPrice, 
                                                    cp.customPackageNotes, 
                                                    cp.totalFoodPrice, 
                                                    cp.venuePricing, 
                                                    cp.additionalServicePrice, 

                                                    sp.price, 
                                                    sp.chargeType,
                                                    sp.pricingType,

                                                    mi.foodItemID,
                                                    mi.foodName,
                                                    mi.foodCategory,

                                                    s.serviceID, 
                                                    s.resortServiceID, 
                                                    s.partnershipServiceID, 
                                                    s.entranceRateID, 
                                                    s.serviceType, 

                                                    ra.RServiceName, 
                                                    ra.RSprice, 

                                                    ps.PBName, 
                                                    ps.PBPrice, 
                                                    ps.PBduration, 
                                                    ps.partnershipID, 

                                                    ppt.partnerTypeID,
                                                    er.sessionType as tourType,
                                                    ec.categoryName as eventType,

                                                    cb.confirmedBookingID,
                                                    cb.amountPaid, 
                                                    cb.confirmedFinalBill, 
                                                    cb.userBalance, 
                                                    cb.confirmedBookingID, 
                                                    cb.discountAmount, 
                                                    cb.paymentApprovalStatus, 
                                                    cb.paymentStatus,  
                                                    cb.paymentDueDate, 
                                                    cb.downpaymentDueDate,
                                                    cb.additionalCharge
                                                FROM booking b
                                                LEFT JOIN confirmedbooking cb 
                                                    ON b.bookingID = cb.bookingID
                                                -- LEFT JOIN bookingpaymentstatus bps ON cb.paymentStatus = bps.paymentStatusID 

                                                LEFT JOIN custompackage cp 
                                                    ON b.customPackageID = cp.customPackageID
                                                LEFT JOIN servicepricing sp 
                                                    ON cp.foodPricingPerHeadID = sp.pricingID
                                                LEFT JOIN custompackageitem cpi 
                                                    ON cp.customPackageID = cpi.customPackageID
                                                LEFT JOIN eventcategory ec 
                                                    ON cp.eventTypeID = ec.categoryID

                                                LEFT JOIN bookingservice bs 
                                                    ON b.bookingID = bs.bookingID
                                                LEFT JOIN service s 
                                                    ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)
                                                LEFT JOIN menuitem mi 
                                                    ON cpi.foodItemID = mi.foodItemID

                                                LEFT JOIN resortamenity ra 
                                                    ON s.resortServiceID = ra.resortServiceID
                                                -- LEFT JOIN resortservicescategory rsc ON rsc.categoryID = ra.RScategoryID

                                                LEFT JOIN entrancerate er 
                                                    ON s.entranceRateID = er.entranceRateID

                                                LEFT JOIN partnershipservice ps 
                                                    ON s.partnershipServiceID = ps.partnershipServiceID
                                                LEFT JOIN partnership_partnertype ppt 
                                                    ON ps.partnershipID = ppt.partnershipID
                                                LEFT JOIN partnershiptype pt 
                                                    ON ppt.partnerTypeID = pt.partnerTypeID
                                                WHERE b.bookingID = ?
                                                ");
                $getBookingInfo->bind_param("i", $bookingID);
                $getBookingInfo->execute();
                $getBookingInfoResult = $getBookingInfo->get_result();
                if ($getBookingInfoResult->num_rows > 0) {

                    $services = [];
                    $serviceIDs = [];
                    $originalBill = 0;
                    $downpayment = 0;
                    $discount = 0;
                    $totalPax = 0;
                    $kidsCount = 0;
                    $adultCount = 0;
                    $finalBill  = 0;
                    $userBalance = 0;
                    $amountPaid = 0;
                    $additionalCharge = 0;
                    $foodList = [];
                    $foodPriceTotal = 0;
                    $partnerServices = [];
                    while ($row = $getBookingInfoResult->fetch_assoc()) {

                        // echo '<pre>';
                        // print_r($row);
                        // echo '</pre>';

                        // Date and Time
                        $rawStartDate = $row['startDate'] ?? null;
                        $rawEndDate = $row['endDate'] ?? null;

                        $arrivalTime = !empty($row['arrivalTime'])
                            ? date('H:i A', strtotime($row['arrivalTime']))
                            : 'Not Stated';

                        $startDate = !empty($rawStartDate)
                            ? date('M. d, Y', strtotime($rawStartDate))
                            : 'Not Stated';

                        $endDate = !empty($rawEndDate)
                            ? date('M. d, Y', strtotime($rawEndDate))
                            : 'Not Stated';

                        $createdAt = $row['createdAt'] ?? null;

                        if (!empty($rawStartDate) || $rawStartDate ===  $rawEndDate) {
                            $bookingDate = date('F d, Y', strtotime($rawStartDate));
                        } elseif (!empty($rawStartDate) && !empty($rawEndDate)) {
                            $bookingDate = $startDate . " to " . $endDate;
                        } else {
                            $bookingDate = 'Date not available';
                        }

                        $bookingCreationDate = !empty($row['createdAt']) ? date('F d, Y h:i A', strtotime($row['createdAt'])) : 'Not Stated';

                        $time = date("g:i A", strtotime($rawStartDate)) . " - " . date("g:i A", strtotime($rawEndDate));
                        $duration = $row['durationCount'] . " hours";


                        //IDs
                        $customPackageID =  (int) $row['customPackageID'];
                        $confirmedBookingID = (int) $row['confirmedBookingID'] ?? null;
                        $serviceID = isset($row['serviceID']) ? $row['serviceID'] : '';

                        if (!empty($confirmedBookingID)) {
                            $paymentApprovalStatus = $row['paymentApprovalStatus'] ?? null;
                            $paymentStatus = $row['paymentStatus'] ?? null;
                            $finalBill = (float) $row['confirmedFinalBill'] ?? null;
                            $paymentDueDate = !empty($row['paymentDueDate']) ? date('F d, Y h:i A', strtotime($row['paymentDueDate'])) : 'Not Stated';
                            $amountPaid = (float) $row['amountPaid'];
                            $userBalance =  (float) $row['userBalance'];

                            if (!empty($paymentStatus) || !empty($paymentApprovalStatus)) {
                                $paymentStatuses = getPaymentStatus($conn, $paymentStatus);
                                $paymentStatusID = $paymentStatuses['paymentStatusID'];
                                $paymentStatusName = $paymentStatuses['paymentStatusName'];

                                $paymentApprovalStatuses = getStatuses($conn, $paymentApprovalStatus);
                                $paymentApprovalStatusID = $paymentApprovalStatuses['statusID'];
                                $paymentApprovalStatusName = $paymentApprovalStatuses['statusName'];
                            }
                        }

                        //Types
                        $bookingType = $row['bookingType'] ?? null;
                        $serviceType = $row['serviceType'] ?? null;

                        //Status of Booking
                        $bookingStatus = $row['bookingStatus'];
                        $bookingStatuses = getStatuses($conn, $bookingStatus);
                        $bookingStatusID = $bookingStatuses['statusID'];
                        $bookingStatusName = $bookingStatuses['statusName'];

                        //Payment Details
                        $paymentMethod =  $row['paymentMethod'];
                        $discount =  (float) $row['discountAmount'] ?? 0;
                        $originalBill =  (float) $row['originalBill'];
                        $downpayment =  (float) $row['downpayment'];
                        $additionalCharge =  (float) $row['additionalCharge'];

                        //Pax Details
                        $toddlerCount = (int) $row['toddlerCount'];
                        $kidCount = (int) $row['kidCount'];
                        $adultCount = (int) $row['adultCount'];
                        $guestCount = intval($row['guestCount']);

                        //Additionals
                        $additionalReq = $row['additionalRequest'];
                        $additionalServices = $row['addOns'] ?? 'None';

                        if (!empty($customPackageID)) {
                            $eventType = $row['eventType'] ?? null;
                            $foodItemID = isset($row['foodItemID']) ? $row['foodItemID'] : null;
                            $totalPax = $guestCount . ' people' ?? 1 . ' person';
                            $additionalServicePrice = floatval($row['additionalServicePrice']);

                            if (!empty($serviceID)) {
                                if ($serviceType === 'Resort') {
                                    $venue = $row['RServiceName'] ?? 'none';
                                    $venuePrice = $row['venuePricing'] ?? 0;
                                    $serviceIDs[] = $row['resortServiceID'];
                                } elseif ($serviceType === 'Partnership') {
                                    $partnerServicePrice = isset($row['PBPrice']) ? floatval($row['PBPrice']) : null;
                                    $serviceName = $row['PBName'] ?? 'N/A';
                                    $partnerServiceID = $row['partnershipServiceID'] ?? null;

                                    if ($partnerServiceID !== null) {
                                        $partnerServices[$partnerServiceID][$serviceName] = $partnerServicePrice;
                                    }
                                }
                            }
                            if (!empty($foodItemID)) {
                                $category = $row['foodCategory'];
                                $name = $row['foodName'];
                                $foodID = $row['foodItemID'];
                                $foodList[$category] = $name;
                                $foodPriceTotal = floatval($row['totalFoodPrice']);
                                $pricePerHead = (int) $row['price'];
                            }
                        } else {
                            if ($serviceType !== 'Event') {
                                $totalPax =  ($adultCount > 0 ? "{$adultCount}" . ($adultCount === 1 ? ' adult' : ' adults') : '') .
                                    ($kidCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$kidCount}" . ($kidCount === 1 ? ' child' : ' childs') : '') .
                                    ($toddlerCount > 0 ? (($adultCount > 0 || $kidCount > 0) ? ' & ' : '') . "{$toddlerCount}" . ($toddlerCount === 1 ? ' toddler' : 'toddlers') : '');
                            }
                            if ($serviceType === 'Resort') {
                                $services[] = $row['RServiceName'] . " - ₱"  . number_format($row['RSprice'], 2);
                                $serviceIDs[] = $row['resortServiceID'];
                            }
                            if ($serviceType === 'Entrance') {
                                $tourType = $row['tourType'];
                            }
                        }


                        $finalBill = ($finalBill === 0) ?  $originalBill : $finalBill;
                        // echo '<pre>';
                        // print_r($partnerServices);
                        // echo '</pre>';
                    }
                }
                ?>

                <!--Rejection Modal -->
                <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectionModalLabel">Rejection</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <h6 class="reject-label fw-bold">Select a Reason for Rejection</h6>
                                <div class="form-group mt-4">
                                    <select class="form-select" id="select-reject" aria-label="rejection-reason"
                                        onchange="otherReason()">
                                        <option value="" disabled selected>Select a reason</option>
                                        <option value="option1">Di ko bet customer</option>
                                        <option value="option2">Dami request</option>
                                        <option value="option3">Kuripot</option>
                                        <option value="other">Other (Please specify)</option>
                                    </select>
                                </div>

                                <div class="form-group mt-4" id="otherInputGroup" style="display: none;">
                                    <h6 class="otherReason-label fw-bold">Please Specify</h6>
                                    <input type="text" class="form-control" id="rejectReason-textBox"
                                        placeholder="Enter your option">
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger" name="rejectBtn">Reject</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Modal -->
                <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="rejectionModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectionModalLabel">Booking Approval</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="approvalModal-p">You are about to approve a booking. Please review the
                                    details carefully. Once you
                                    approve, the booking will be finalized and cannot be undone.</p>
                                <p class="approvalModal-p"><strong>Do you want to approve this booking?</strong>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display the information -->
                <input type="hidden" name="customPackageID" id="customPackageID" value="<?= $customPackageID ?>">
                <div class="card" id="info-card">
                    <div class="bookingInfoLeft" id="bookingInformation">
                        <div class="row1">
                            <div class="info-container" id="booking-info-container">
                                <label for="bookingType" class="info-label">Booking Type</label>
                                <input type="hidden" name="bookingType" id="bookingType" value="<?= $bookingType ?>">
                                <input type="text" class="form-control inputDetail" value="<?= $bookingType ?> Booking"
                                    readonly>
                            </div>
                            <?php if ($bookingType === 'Resort') { ?>
                            <div class="info-container" id="booking-info-container">
                                <label for="tourType" class="info-label">Tour Type</label>
                                <input type="hidden" name="tourType" id="tourType" value="<?= $tourType ?>">
                                <input type="text" class="form-control inputDetail" name="tourType"
                                    value="<?= $tourType ?> Swimming" readonly>
                            </div>
                            <?php } elseif ($bookingType === 'Event') { ?>
                            <div class="info-container" id="booking-info-container">
                                <label for="eventType" class="info-label">Event Type</label>
                                <input type="text" name="eventType" id="eventType" class="form-control inputDetail"
                                    readonly value="<?= $eventType ?>">
                            </div>
                            <?php } ?>
                        </div>


                        <div class="datesContainer mt-3">
                            <h1 class="card-title text-center">Date and Time</h1>
                            <div class="row2 mt-2">
                                <div class="info-container mt-2" id="booking-info-container">
                                    <label for="arrivalTime" class="info-label mb-2">Arrival Time</label>
                                    <input type="text" class="form-control inputDetail" name="arrivalTime"
                                        id="arrivalTime" value="<?= $arrivalTime ?>" readonly>
                                </div>

                                <div class="info-container mt-2" id="booking-info-container">
                                    <label for="timeDuration" class="info-label mb-2">Time Duration</label>
                                    <input type="text" class="form-control inputDetail" name="timeDuration"
                                        id="timeDuration" value="<?= $time ?> (<?= $duration ?>)" readonly>
                                </div>

                                <div class="info-container mt-2" id="booking-info-container">
                                    <label for="bookingDate" class="info-label mb-2">Booking Date</label>
                                    <input type="text" class="form-control inputDetail" name="bookingDate"
                                        id="bookingDate" value="<?= $bookingDate ?>" readonly>
                                </div>

                                <div class="info-container mt-2" id="booking-info-container">
                                    <label for="bookingCreationDate" class="info-label mb-2">Booking Creation
                                        Date</label>
                                    <input type="text" class="form-control inputDetail" name="bookingCreationDate"
                                        id="bookingCreationDate" value="<?= $bookingCreationDate ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="bookingDetails mt-3">
                            <?php if ($bookingType !== 'Event') { ?>
                            <div class="servicesDetails">
                                <h1 class="card-title text-center">Services</h1>
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

                            <?php } else { ?>

                            <div class="venueDetails">
                                <h1 class="card-title text-center">Venue</h1>
                                <input type="text" readonly class="form-control inputDetail" name="venue" id="venue"
                                    value="<?= $venue ?>">
                            </div>
                            <h1 class="card-title text-center">Selected Menu</h1>
                            <div class="foodDetails">
                                <?php if (!empty($foodList)) { ?>
                                <?php foreach ($foodList as $category => $name) { ?>
                                <div class="foodList">
                                    <p><?= htmlspecialchars($category) ?></p>
                                    <ul>
                                        <li>
                                            <input type="text" name="foodIDs[<?= htmlspecialchars($foodID) ?>]"
                                                class="form-control inputDetail" value="<?= htmlspecialchars($name) ?>">
                                        </li>
                                    </ul>
                                </div>
                                <?php } ?>
                                <?php } else { ?>
                                <h1 class="text-center defaultMess">No Food Selected!</h1>
                                <?php } ?>
                            </div>

                            <div class="partnerService">
                                <h1 class="card-title text-center">Additional Services</h1>
                                <?php if (!empty($partnerServices)) { ?>
                                <?php foreach ($partnerServices as $partnershipServiceID => $services) {
                                            foreach ($services as $name => $price) { ?>
                                <ul>
                                    <li class="servicesList">
                                        <input type="hidden" name="partnerServices[<?= $partnerServiceID ?>]"
                                            class="form-control inputDetail" value="<?= htmlspecialchars($name) ?>">
                                        <?= htmlspecialchars($name) ?> &mdash; ₱<?= number_format($price, 2) ?>
                                    </li>
                                </ul>
                                <?php } ?>
                                <?php } ?>
                                <?php } else { ?>
                                <h1 class="text-center defaultMess">No Additional Services Selected!</h1>
                                <?php } ?>
                            </div>
                            <?php  } ?>



                            <div class="row3 mt-4">
                                <?php if ($bookingType !== 'Event') { ?>
                                <div class="additionalServices" id="booking-info-container">
                                    <label for="addOns" class="info-label mb-2">Additional Services</label>
                                    <input type="text" class="form-control inputDetail" name="addOns" id="addOns"
                                        value="<?= $additionalServices ?>" readonly>
                                </div>
                                <?php  } ?>
                                <div class="peopleCountContainer" id="booking-info-container">
                                    <label for="paxNum" class="info-label mb-2">Number of People:</label>
                                    <input type="text" class="form-control inputDetail" name="paxNum" id="paxNum"
                                        value="<?= $totalPax ?>" readonly>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="paymentDetails">
                        <div class="payment-body">
                            <h1 class="card-title text-center mt-1 mb-3 me-3">
                                Payment Details
                            </h1>
                            <div class="info-container paymentInfo">
                                <label for="paymentMethod" class="mt-2">Payment Method</label>
                                <input type="text" class="form-control inputDetail w-50" name="paymentMethod"
                                    id="paymentMethod" value="<?= $paymentMethod ?>" readonly>
                            </div>
                            <?php if ($bookingStatusName === 'Approved') { ?>
                            <div class="info-container paymentInfo">
                                <label for="paymentStatus" class="mt-2">Payment Status</label>
                                <input type="text" class="form-control inputDetail w-50" name="paymentStatus"
                                    id="paymentStatus" value="<?= $paymentStatusName ?>" readonly>
                            </div>
                            <?php } ?>

                            <?php if ($bookingType === 'Event') { ?>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="venuePrice" class="mt-2">Venue Price</label>
                                <input type="text" class="form-control inputDetail w-50" name="venuePrice"
                                    id="venuePrice" value="₱<?= number_format($venuePrice, 2) ?>" readonly>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="pricePerHead" class="mt-2">Price Per Head</label>
                                <input type="text" class="form-control inputDetail w-50" name="pricePerHead"
                                    id="pricePerHead" value="₱<?= number_format($pricePerHead, 2) ?>" readonly>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="foodPriceTotal" class="mt-2">Total Food Price</label>
                                <input type="text" class="form-control inputDetail w-50" name="foodPriceTotal"
                                    id="foodPriceTotal" value="₱<?= number_format($foodPriceTotal, 2) ?>" readonly>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="additionalServicePrice" class="mt-2">Additional Services Price</label>
                                <input type="text" class="form-control inputDetail w-50" name="additionalServicePrice"
                                    id="additionalServicePrice"
                                    value="₱<?= number_format($additionalServicePrice, 2) ?>" readonly>
                            </div>

                            <?php } ?>

                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="additionalCharge" class="mt-2">Additional Charge</label>
                                <input type="text" class="form-control inputDetail w-50"
                                    value="₱<?= number_format($additionalCharge, 2) ?>" readonly>
                            </div>

                            <?php if ($bookingStatusName === 'Approved') { ?>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="paymentDue" class="mt-2">Payment Due Date</label>
                                <input type="text" class="form-control inputDetail w-50" value="<?= $paymentDueDate ?>"
                                    readonly>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="userBalance" class="mt-2">User Balance</label>
                                <input type="text" class="form-control inputDetail w-50"
                                    value="₱<?= number_format($userBalance, 2) ?>" readonly>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="amountPaid" class="mt-2">Amount Paid</label>
                                <input type="text" class="form-control inputDetail w-50"
                                    value="₱<?= number_format($amountPaid, 2) ?>" readonly>
                            </div>
                            <?php } ?>

                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="downpayment" class="mt-2">Downpayment</label>
                                <input type="text" class="form-control inputDetail w-50"
                                    value="₱<?= number_format($downpayment, 2) ?>" readonly>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="originalBill" class="mt-2">Original Bill</label>
                                <input type="text" class="form-control inputDetail w-50" id="originalBill"
                                    value="₱<?= number_format($originalBill, 2) ?>" readonly>
                            </div>

                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="discountAmount" class="mt-2">Discount </label>
                                <div class="discountform">
                                    <input type="text" class="form-control inputDetail w-100"
                                        value="₱<?= number_format($discount, 2) ?>" readonly>
                                    <!-- <i class="fa-solid fa-circle-info"
                                        id="discountTooltip"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        title="You can change the discount amount manually."
                                        style="color: #74C0FC;">
                                    </i> -->
                                </div>
                            </div>
                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="finalBill" class="mt-2">Final Bill</label>
                                <input type="text" class="form-control inputDetail w-50" name="finalBill" id="finalBill"
                                    value="₱<?= number_format($finalBill, 2) ?>" readonly>
                            </div>

                        </div>

                        <div class="notesContainer mt-3">
                            <!-- <h1 class="card-title text-center"> Notes</h1> -->
                            <div class="info-container notes">
                                <label for="req" class="info-label mt-2 mb-2">Additional Request(s)/Note(s)</label>
                                <textarea class="form-control inputDetail" rows="4" name="req" id="req"
                                    readonly><?= $additionalReq ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden-inputs">
                    <input type="hidden" name="bookingID" id="bookingID" value="<?= $bookingID ?>">
                    <input type="hidden" name="bookingStatusID" id="bookingStatusID" value="<?= $bookingStatusID ?>">
                    <input type="hidden" name="bookingStatusName" id="bookingStatusName"
                        value="<?= $bookingStatusName ?>">
                    <input type="hidden" name="paymentApprovalStatus" id="paymentApprovalStatus"
                        value="<?= $paymentApprovalStatusName ?? 'None' ?>">
                    <?php foreach ($serviceIDs as $serviceID): ?>
                    <input type="hidden" name="serviceIDs[]" value="<?= $serviceID ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="endDate" id="endDate" value="<?= $rawEndDate ?>">
                    <input type="hidden" name="startDate" id="startDate" value="<?= $rawStartDate ?>">
                    <input type="hidden" name="userRoleID" value="<?= $userRoleID ?>">
                    <input type="hidden" name="customerID" value="<?= $customerID ?>">
                </div>
            </form>
        </div>

    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>


    <!-- Allow adding discount and changing final bill -->
    <script>
    const changeFinalBillRadio = document.getElementById('change-final-bill');
    const offerDiscountRadio = document.getElementById('offer-discount');
    const finalBillInput = document.getElementById('editedFinalBill');
    const discountInput = document.getElementById('discountAmount');
    const addChargeCheckBox = document.getElementById('add-charge');
    const additionalChargeInput = document.getElementById('additionalCharge');

    function resetInputs() {
        finalBillInput.readOnly = true;
        finalBillInput.style.border = '';
        discountInput.readOnly = true;
        discountInput.style.border = '';
    }

    function updateInputs() {
        resetInputs();

        if (changeFinalBillRadio.checked) {
            // Enable and highlight final bill input
            finalBillInput.readOnly = false;
            finalBillInput.style.border = '1px solid red';

            // Clear discount input
            discountInput.value = '';
        } else if (offerDiscountRadio.checked) {
            // Enable and highlight discount input
            discountInput.readOnly = false;
            discountInput.style.border = '1px solid red';

            // Clear final bill input
            finalBillInput.value = '';
        }
    }

    addChargeCheckBox.addEventListener('change', function() {
        if (addChargeCheckBox.checked) {
            additionalChargeInput.readOnly = !this.checked;
            additionalChargeInput.style.border = '1px solid red';
        } else {
            additionalChargeInput.value = '';
            additionalChargeInput.style.border = '1px solid rgb(117, 117, 117)';
        }
    })



    // Listen for changes on both radios
    changeFinalBillRadio.addEventListener('change', updateInputs);
    offerDiscountRadio.addEventListener('change', updateInputs);
    </script>



    <!--//* Hiding buttons -->
    <script>
    const paymentApprovalStatus = document.getElementById('paymentApprovalStatus').value;
    const bookingStatus = document.getElementById('bookingStatusName').value;

    const buttonContainer = document.getElementById('button-container');

    if (paymentApprovalStatus === 'Done' ||
        bookingStatus === 'Expired' ||
        bookingStatus === 'Rejected' ||
        bookingStatus === 'Cancelled' ||
        paymentApprovalStatus === 'Rejected' ||
        paymentApprovalStatus === 'Cancelled' ||
        bookingStatus === 'Approved') {
        buttonContainer.style.display = "none";
    }
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
    const param = new URLSearchParams(window.location.search);
    const paramValue = param.get('action');

    if (paramValue === "approvalFailed") {
        Swal.fire({
            title: "Failed!",
            text: "The booking request could not be approved. Please try again later.",
            icon: 'error',
        });
    } else if (paramValue === 'rejectionEmpty') {
        Swal.fire({
            title: "Oops!",
            text: "Please provide the reason for your rejection",
            icon: 'warning',
            confirmButtonText: 'Okay',
        }).then((result) => {
            const rejectionModal = document.getElementById('rejectionModal');
            const modal = new bootstrap.modal(rejectionModal);
            modal.show();

            document.getElementById('rejectionReason').style.border = '1px solid red';
        });
    } else if (paramValue === 'rejectionFailed') {
        Swal.fire({
            title: "Failed!",
            text: "The booking request could not be rejected. Please try again later.",
            icon: 'error',
        });
    }

    if (paramValue) {
        const url = new URLSearchParams(window.location);
        url.search = '';
        history.replaceState({}, document.title.url.toString());
    }
    </script>

    <script>
    function otherReason() {
        var selectBox = document.getElementById("select-reject");
        var otherInputGroup = document.getElementById("otherInputGroup");

        // Show or hide the text box when "Other (Please specify)" is selected
        if (selectBox.value === "other") {
            otherInputGroup.style.display = "block"; // Show the text box
        } else {
            otherInputGroup.style.display = "none"; // Hide the text box
        }
    }
    </script>
</body>

</html>