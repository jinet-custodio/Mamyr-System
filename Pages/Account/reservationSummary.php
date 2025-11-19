<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

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

if (isset($_POST['bookingID'])) {
    $bookingID = intval($_POST['bookingID']);
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = intval($_SESSION['bookingID']);
}

require_once '../../Function/Helpers/statusFunctions.php';

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
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
    <title>Reservation Summary - Mamyr Resort and Events Place</title>
    <link rel="icon" type="down$downpaymentImage/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/reservationSummary.css" />
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="container">
        <div class="backButtonContainer">
            <a href="bookingHistory.php"><img src="../../Assets/Images/Icon/arrowBtnBlack.png" alt="Back Button"
                    class="backButton"></a>
        </div>

        <div class="statusContainer">
            <!-- Get user data -->
            <?php
            // $confirmedBookingID = (int) $_POST['confirmedBookingID'] ?? $_SESSION['confirmedBookingID'];
            $bookingID = (int) $bookingID;
            // $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
            // $status = mysqli_real_escape_string($conn, $_POST['status']);
            $getData = $conn->prepare("SELECT booking.*, user.firstName, user.middleInitial, user.lastName, user.phoneNumber, user.userAddress FROM booking 
            JOIN user ON booking.userID = user.userID
            WHERE booking.userID = ? AND booking.bookingID =?");
            $getData->bind_param("ii", $userID, $bookingID);
            $getData->execute();
            $resultData = $getData->get_result();
            if ($resultData->num_rows > 0) {
                $clientInfo = $resultData->fetch_assoc();
                $middleInitial = trim($clientInfo['middleInitial'] ?? '');
                $firstName = $clientInfo['firstName'] ?? '';
                $clientName = (ucfirst($firstName) ?? '') . " " . ucfirst($clientInfo['middleInitial'] ?? '') . " "  . ucfirst($clientInfo['lastName'] ?? '');
            }
            ?>

            <!-- Get booking info -->
            <?php
            $getBookingInfo = $conn->prepare("SELECT 
                                                    b.bookingID, 
                                                    b.bookingType, 
                                                    b.customPackageID, 
                                                    b.additionalRequest,
                                                    b.addOns, 
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
                                                    b.bookingCode,

                                                    cp.eventTypeID, 
                                                    cp.customPackageTotalPrice, 
                                                    cp.customPackageNotes, 
                                                    cp.totalFoodPrice, 
                                                    cp.venuePricing, 
                                                    cp.additionalServicePrice, 

                                                    sp.price, 

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
                                                    pt.partnerTypeDescription as category,
                                                    er.sessionType as tourType,
                                                    ec.categoryName as eventType,

                                                    cb.confirmedBookingID,
                                                    cb.amountPaid, 
                                                    cb.finalBill, 
                                                    cb.userBalance, 
                                                    cb.discountAmount, 
                                                    cb.paymentApprovalStatus,  
                                                    cb.paymentDueDate, 
                                                    cb.downpaymentDueDate,
                                                    cb.downpaymentImage,
                                                    cb.additionalCharge,
                                                    cb.paymentStatus,

                                                    ac.additionalChargeID,
                                                    ac.chargeDescription,
                                                    ac.amount as chargeAmount 
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

                                                LEFT JOIN payment p ON p.confirmedBookingID = cb.confirmedBookingID
                                                LEFT JOIN additionalcharge ac ON b.bookingID = ac.bookingID
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
                $additionalChargesAmount = 0;
                $foodList = [];
                $foodPriceTotal = 0;
                $partnerServiceList = [];
                $downpaymentNotes = [];
                $additionalChargesInfo = [];
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

                    $newCreatedAt = new DateTime($createdAt);
                    $until24hrs = $newCreatedAt->modify('+1');
                    $within24hrs = $until24hrs->format('M. d, Y g:i A');

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
                    $bookingCode = $row['bookingCode'];

                    //Types
                    $bookingType = $row['bookingType'] ?? null;
                    $serviceType = $row['serviceType'] ?? null;

                    //Payment Details
                    $paymentMethod =  $row['paymentMethod'];
                    $discount =  (float) $row['discountAmount'] ?? 0;
                    $originalBill =  (float) $row['originalBill'];
                    $downpayment =  (float) $row['downpayment'];
                    $additionalChargesAmount =  (float) $row['additionalCharge'];
                    $downpaymentImageData = $row['downpaymentImage'];
                    $bookingStatusID = $row['bookingStatus'] ?? null;
                    $bookingStatus = getStatuses($conn, $bookingStatusID);

                    if (!empty($confirmedBookingID)) {
                        $paymentApprovalStatusID = $row['paymentApprovalStatus'] ?? null;
                        $paymentStatusID = $row['paymentStatus'] ?? 1;
                        $finalBill = (float) $row['finalBill'] ?? null;
                        $downpaymentDueDate = !empty($row['downpaymentDueDate']) ? date('M. d, Y h:i A', strtotime($row['downpaymentDueDate'])) : 'Not Stated';
                        $paymentDueDate = !empty($row['paymentDueDate']) ? date('M. d, Y h:i A', strtotime($row['paymentDueDate'])) : 'Not Stated';
                        $amountPaid = (float) $row['amountPaid'];
                        $userBalance =  (float) $row['userBalance'];

                        if (!empty($paymentStatusID) || !empty($paymentApprovalStatusID)) {
                            $paymentStatus = getPaymentStatus($conn, $paymentStatusID);
                            $paymentApprovalStatus = getStatuses($conn, $paymentApprovalStatusID) ?? null;
                        }
                    } else {
                        $finalBill = $originalBill;
                        $dueDate = 'Wait for approval before paying';
                    }

                    //Pax Details
                    $toddlerCount = (int) $row['toddlerCount'];
                    $kidCount = (int) $row['kidCount'];
                    $adultCount = (int) $row['adultCount'];
                    $guestCount = intval($row['guestCount']);

                    //Additionals
                    $additionalReq = $row['additionalRequest'];
                    $additionalServices = !empty($row['addOns']) ?  $row['addOns'] : 'None';

                    $status = strtolower($bookingStatus['statusName']);
                    switch ($bookingStatus['statusID']) {
                        case 1: //Pending
                            $statusTitle = 'Reservation Pending Approval';
                            $statusIcon = '<i class="bi bi-hourglass-split"></i>';
                            $statusSubtitle = 'Your request has been sent to the admin. You will be notified once it is approved.';
                            break;
                        case 2: //Approved
                            $status = strtolower($paymentApprovalStatus['statusName']);
                            switch ($paymentApprovalStatus['statusID']) {
                                case 1: // Payment Pending Review
                                    $statusTitle = 'Booking Approved – Not Yet Reserved';
                                    $statusIcon = '<i class="bi bi-check-circle"></i>';
                                    if ($paymentMethod === 'GCash') {
                                        $statusSubtitle = 'Your booking is approved. Please proceed with the down payment via GCash. The service will be reserved once your payment is reviewed.';
                                    } elseif ($paymentMethod === 'Cash' && $bookingType === 'Resort') {
                                        $statusSubtitle = 'Your booking is approved. Please pay on your scheduled swimming date. The service will be reserved once your payment is received.';
                                    } else {
                                        $statusSubtitle = 'Your booking is approved. Please proceed to make your down payment. The service will be reserved once your payment is reviewed.';
                                    }
                                    break;

                                case 3: // Payment Rejected
                                    $statusTitle = 'Payment Declined';
                                    $statusIcon = '<i class="bi bi-x-circle"></i>';
                                    $statusSubtitle = 'Please check the payment details and try again, or contact the admin for assistance.';
                                    break;
                            }
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: //Partially Paid
                                    $statusTitle = "Payment Reviewed – Service Reserved";
                                    $statusIcon = '<i class="bi bi-card-checklist"></i>';
                                    $statusSubtitle = "We have received and reviewed your payment. Your service is now confirmed and reserved. Thank you!";
                                    break;
                                case 3: //Fully Paid
                                    $statusTitle = "Payment Completed – Service Confirmed";
                                    $statusIcon = '<i class="bi bi-check-circle-fill"></i>';
                                    $statusSubtitle = 'Thank you! Your full payment has been received and reviewed. Your service is now confirmed.';
                                    break;
                                case 5: // Payment Sent
                                    $statusTitle = 'Payment submitted!';
                                    $statusIcon = '<i class="bi bi-cash-stack"></i>';
                                    $statusSubtitle = 'Thank you for your payment. Please wait for the admin’s review and approval.';
                                    break;
                            }
                            break;
                        case 3: //Reserved
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: //Partially Paid
                                    $statusTitle = "Payment Reviewed – Service Reserved";
                                    $statusIcon = '<i class="bi bi-card-checklist"></i>';
                                    $statusSubtitle = "We have received and reviewed your payment. Your service is now confirmed and reserved. Thank you!";
                                    break;
                                case 3: //Fully Paid
                                    $statusTitle = "Payment Completed – Service Confirmed";
                                    $statusIcon = '<i class="bi bi-check-circle-fill"></i>';
                                    $statusSubtitle = 'Thank you! Your full payment has been received and reviewed. Your service is now confirmed.';
                                    break;
                            }
                            break;
                        case 5: //Rejected
                            $statusTitle = 'Booking Rejected';
                            $statusIcon = '<i class="bi bi-x-circle-fill"></i>';
                            $statusSubtitle = 'We regret to inform you that your reservation has been rejected. Please contact us for more details.';
                            break;
                        case 4: //Cancelled
                            $statusTitle = 'Booking Cancelled';
                            $statusIcon = '<i class="bi bi-slash-circle"></i>';
                            $statusSubtitle = 'You have cancelled your reservation. If this was a mistake or you wish to rebook, please contact us.';
                            break;
                        case 6: //Done
                            $status = strtolower($bookingStatus['statusName']);
                            switch ($paymentStatus['paymentStatusID']) {
                                case 3: //Fully Paid
                                    $statusTitle = 'Booking Completed';
                                    $statusIcon = '<i class="bi bi-flag-checkered"></i>';
                                    $statusSubtitle = 'Thank you for staying with us! Your booking is fully paid and successfully completed. We hope you had a wonderful time.';
                                    break;
                            }
                            break;
                        case 7: //Expired
                            $statusTitle = "Expired Booking";
                            $statusIcon = '<i class="bi bi-clock-history"></i>';
                            $statusSubtitle = "Sorry. The scheduled time for this booking has passed.";
                            break;
                    }


                    if (!empty($customPackageID)) {
                        $eventType = $row['eventType'] ?? null;
                        $foodItemID = isset($row['foodItemID']) ? $row['foodItemID'] : null;
                        $totalPax = $guestCount . ' people' ?? 1 . ' person';
                        $cardHeader = "Type of Event";
                        $eventType = $row['eventType'];
                        $additionalServicePrice = floatval($row['additionalServicePrice']);
                        // $partnerServiceList['Hakdog']['Haha'] = 3000;
                        // $partnerServiceList['HEhe']['Haha'] = 5000;
                        $downpaymentNotes[] = 'Any additional services offered by our business partners require separate approval and are not included in the reservation unless specifically requested and confirmed.';
                        $downpaymentNotes[] = 'The displayed price on the summary is only rough estimate. The price can change depending on the customer\'s discussions with the admin.';
                        if (!empty($serviceID)) {
                            if ($serviceType === 'Resort') {
                                $services[] = $venue = $row['RServiceName'] ?? 'none';
                                $venuePrice = $row['venuePricing'] ?? 0;
                                $serviceIDs['resort'][] = $row['resortServiceID'];
                            } elseif ($serviceType === 'Partnership') {
                                $partnerServicePrice = isset($row['PBPrice']) ? floatval($row['PBPrice']) : null;
                                $services[]  = $serviceName = $row['PBName'] ?? 'N/A';
                                $partnerServiceID = $row['partnershipServiceID'] ?? null;
                                $category = $row['category'] ?? null;
                                $serviceIDs['partner'][] = $row['partnershipServiceID'] ?? null;

                                if ($partnerServiceID !== null) {
                                    $partnerServiceList[$category][$serviceName] = $partnerServicePrice;
                                }
                            }
                        }
                        if (!empty($foodItemID)) {
                            $services[]  = 'Catering with drinks & dessert';
                            $category = $row['foodCategory'];
                            $name = $row['foodName'];
                            $foodID = $row['foodItemID'];
                            $foodList[$category][] = $name;
                            $foodPriceTotal = floatval($row['totalFoodPrice']);
                            $pricePerHead = (int) $row['price'];
                        }
                    } else {
                        $downpaymentNotes[] = 'Wait for the approval before paying the downpayment.';
                        $downpaymentNotes[] = 'Your booking is considered confirmed only after the downpayment is received and proof of payment verified';
                        $downpaymentNotes[] = 'You can check the payment due date by clicking the "Make a Down Payment" button';

                        if ($serviceType !== 'Event') {
                            $totalPax =  ($adultCount > 0 ? "{$adultCount}" . ($adultCount === 1 ? ' adult' : ' adults') : '') .
                                ($kidCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$kidCount}" . ($kidCount === 1 ? ' child' : 'childs') : '') .
                                ($toddlerCount > 0 ? (($adultCount > 0 || $kidCount > 0) ? ' & ' : '') . "{$toddlerCount}" . ($toddlerCount === 1 ? ' toddler' : 'toddlers') : '');
                        }
                        if ($serviceType === 'Resort') {
                            $services[] = $row['RServiceName'];
                            $serviceIDs['resort'][] = $row['resortServiceID'];
                        }
                        if ($serviceType === 'Entrance') {
                            $cardHeader = "Type of Tour";
                            $tourType = $row['tourType'] . ' Tour';
                        }
                    }

                    $chargeID = $row['additionalChargeID'];
                    if (!empty($chargeID)) {
                        $chargeDescription = $row['chargeDescription'];
                        $additionalChargesInfo[$chargeID] = [
                            'desc' => $chargeDescription,
                            'amount' => $row['chargeAmount']
                        ];
                    }
                }

                // echo '<pre>';
                // print_r($paymentStatus);
                // echo '</pre>';

                $serviceVenue = [];

                foreach ($services as $service) {
                    if (
                        stripos($service, 'cottage') !== false ||
                        stripos($service, 'room') !== false ||
                        stripos($service, 'umbrella') !== false
                    ) {
                        $serviceVenue[] = trim($service);
                    }
                    // if (stripos($service, 'Day') !== false) {
                    //     $tourType = "Day Tour";
                    // } elseif (stripos($service, 'Night') !== false) {
                    //     $tourType = "Night Tour";
                    // } elseif (stripos($service, 'Overnight') !== false) {
                    //     $tourType = "Overnight Tour";
                    // }
                }
                $serviceVenue = array_unique($serviceVenue);
            }
            ?>



            <div class="leftStatusContainer">

                <input type="hidden" name="bookingStatus" id="bookingStatus"
                    value="<?= !empty($bookingStatus['statusName']) ? htmlspecialchars($bookingStatus['statusName']) : '' ?>">
                <input type="hidden" name="paymentApprovalStatus" id="paymentApprovalStatus"
                    value="<?= !empty($paymentApprovalStatus['statusName']) ? htmlspecialchars($paymentApprovalStatus['statusName']) : '' ?>">
                <input type="hidden" name="paymentStatus" id="paymentStatus"
                    value="<?= !empty($paymentStatus['paymentStatusName']) ?  htmlspecialchars($paymentStatus['paymentStatusName']) : '' ?>">
                <input type="hidden" name="paymentMethod" id="paymentMethod"
                    value="<?= htmlspecialchars($paymentMethod) ?>">
                <div class="status-image-container m-2">
                    <img src="../../Assets/Images/Icon/StatusIcon/<?= htmlspecialchars(ucfirst($status)) ?>.png"
                        alt="<?= ucfirst(htmlspecialchars($status)) ?> Icon" class="statusIcon">
                </div>


                <h4 class="statusTitle"><?= htmlspecialchars($statusTitle) ?></h4>
                <h6 class="statusSubtitle"><?= htmlspecialchars($statusSubtitle) ?></h6>

                <div class="button-container">
                    <button type="button" class="btn btn-success w-100 mt-3" id="makeDownpaymentBtn"
                        style="display: none;" data-bs-toggle="modal" data-bs-target="#gcashPayment1stModal">Make a
                        downpayment</button>

                    <a href="paymentHistory.php" class="btn btn-info w-100 mt-3" id="viewTransaction">View Your
                        Transaction</a>

                    <form action="../../Function/receiptPDF.php" method="POST" target="_blank">
                        <input type="hidden" name="totalCost" value="<?= $finalBill ?>">
                        <input type="hidden" name="name" value="<?= $clientName ?>">
                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                        <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                        <?php foreach ($services as $service): ?>
                            <input type="hidden" name="services[]" value="<?= $service ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary w-100 mt-3" name="downloadReceiptBtn"
                            id="downloadReceiptBtn">Download Receipt </button>
                    </form>
                </div>
            </div>

            <div class="rightStatusContainer">
                <h3 class="rightContainerTitle">Reservation Summary <small class="fst-italic fw-lighter"> &mdash; <?= $bookingCode ?></small></h3>

                <div class="firstRow">
                    <div class="clientContainer">
                        <h6 class="header">Customer Name</h6>
                        <p class="content" id="clientName"><?= htmlspecialchars($clientName) ?></p>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Contact Number</h6>
                        <p class="content" id="contactNumber">
                            <?= $clientInfo['phoneNumber'] ? $clientInfo['phoneNumber'] : 'Not Available' ?></p>
                    </div>

                    <input type="hidden" name="bookingType" id="bookingType" value="<?= $bookingType ?>">
                    <div class="reservationTypeContainer">
                        <h6 class="header">Reservation Type</h6>
                        <p class="content" id="reservation"><?= $bookingType ?> Booking</p>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Address</h6>
                        <p class="content" id="address">
                            <?= $clientInfo['userAddress'] ? $clientInfo['userAddress'] : 'Not Available' ?></p>
                    </div>
                </div>

                <div class="card" id="summaryDetails">
                    <ul class="list-group list-group-flush">
                        <?php if ($bookingType === 'Resort') { ?>
                            <li class="list-group-item" id="tourType">
                                <h6 class="cardHeader"><?= $cardHeader ?></h6>
                                <p class="cardContent"><?= $tourType ?></p>
                            </li>
                        <?php } elseif ($bookingType === 'Event') { ?>
                            <li class="list-group-item" id="tourType">
                                <h6 class="cardHeader"><?= $cardHeader ?></h6>
                                <p class="cardContent"><?= $eventType ?></p>
                            </li>
                        <?php } ?>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Date</h6>
                            <p class="cardContent" id="eventDate"><?= $bookingDate ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Time</h6>
                            <p class="cardContent" id="eventTime"><?= $time ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader"> Venue </h6>
                            <div class="venues">
                                <?php if ($bookingType === 'Resort' || $bookingType === 'Hotel') {
                                ?>
                                    <p class="cardContent"><?= implode(', ', $serviceVenue) ?></p>
                                <?php
                                } else { ?>
                                    <p class="cardContent"><?= htmlspecialchars($venue) ?></p>
                                <?php } ?>
                            </div>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Duration</h6>
                            <p class="cardContent" id="eventDuration"><?= $duration ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Number of Guests</h6>
                            <p class="cardContent" id="guestNo"><?= $totalPax ?></p>
                        </li>

                        <?php if ($bookingType === 'Event') {  ?>
                            <li class="list-group-item">
                                <h6 class="cardHeader">Menu</h6>
                                <?php if ($foodList) { ?>
                                    <p class="cardContent">Food List
                                        <button id="food-info-button" data-bs-target="#foodListModal" class="iModalBtn"
                                            data-bs-toggle="modal">
                                            <i class="bi bi-info-circle  text-primary"></i></button>
                                    </p>
                                <?php } else {  ?>
                                    <p class="cardContent">None</p>
                                <?php
                                }
                                ?>
                            </li>

                            <li class="list-group-item">
                                <h6 class="cardHeader">Additional Service</h6>
                                <p class="cardContent">Service List
                                    <button id="service-info-button" data-bs-target="#partnerServiceModal" class="iModalBtn"
                                        data-bs-toggle="modal"> <i class="bi bi-info-circle  text-primary"></i></button>
                                </p>
                            </li>
                        <?php } else { ?>
                            <li class="list-group-item" id="addOns">
                                <h6 class="cardHeader">Add Ons</h6>
                                <p class="cardContent"><?= $additionalServices ?></p>
                            </li>
                        <?php } ?>

                        <li class="list-group-item" id="addOns">
                            <h6 class="cardHeader">Additional Charges</h6>
                            <p class="cardContent">Charges <button id="charges-info-button" data-bs-target="#chargesModal" class="iModalBtn"
                                    data-bs-toggle="modal">
                                    <i class="bi bi-info-circle  text-primary"></i></button></p>
                        </li>

                        <li class="list-group-item" id="promoSection">
                            <h6 class="cardHeader">Additional Charges:</h6>
                            <h6 class="cardContentBill" id="additional-charges">₱ <?= number_format($additionalChargesAmount, 2) ?></h6>
                        </li>

                        <li class="list-group-item" id="totalAmountSection">
                            <h6 class="cardHeader">Total Amount:</h6>
                            <h6 class="cardContentBill" id="originalBill">₱ <?= number_format($originalBill, 2) ?></h6>
                        </li>

                        <li class="list-group-item" id="promoSection">
                            <h6 class="cardHeader">Promo/Discount:</h6>
                            <h6 class="cardContentBill" id="promoDiscount">₱ <?= number_format($discount, 2) ?></h6>
                        </li>


                        <li class="list-group-item" id="totalBillSection">
                            <h6 class="cardHeader">Grand Total:</h6>
                            <h6 class="cardContentBill" id="grandTotal">₱ <?= number_format($finalBill, 2) ?></h6>
                        </li>
                    </ul>
                </div>

                <div class="downpaymentNoteContainer" id="downpaymentNoteContainer">
                    <div class="downpayment">
                        <h6 class="header">Down Payment Amount:</h6>
                        <p class="content" id="downPaymentAmount">₱ <?= number_format($downpayment, 2) ?></p>
                    </div>
                    <div class="note">
                        <ul>
                            <?php foreach (array_unique($downpaymentNotes) as $notes) {  ?>
                                <li><?= $notes ?></li>
                            <?php  }  ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for menu list -->
    <div class="modal fade" tabindex="-1" id="foodListModal" aria-labelledby="foodListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="food-table table table-bordered table-sm">
                        <thead>
                            <tr class="text-center fw-bold">
                                <td>Category</td>
                                <td>Name</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($foodList as $category => $items): ?>
                                <tr>
                                    <td class="category-cell fw-bold">
                                        <?= htmlspecialchars(ucfirst($category)) ?>
                                    </td>
                                    <td class="items-cell">
                                        <?= htmlspecialchars(implode(', ', $items)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for partner service list -->
    <div class="modal fade" tabindex="-1" id="partnerServiceModal" aria-labelledby="partnerServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Additional Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($partnerServiceList)) : ?>
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Service Name</th>
                                    <th class="text-end">Price (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalAdditionalServiceFee = 0;
                                foreach ($partnerServiceList as $category => $items) :
                                    foreach ($items as $name => $price) :
                                        $totalAdditionalServiceFee += $price;
                                ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category) ?></td>
                                            <td><?= htmlspecialchars($name) ?></td>
                                            <td class="text-end"><?= number_format($price, 2) ?></td>
                                        </tr>
                                <?php endforeach;
                                endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th colspan="2" class="text-end">₱<?= number_format($totalAdditionalServiceFee, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    <?php else : ?>
                        <p class="text-muted mb-0">No additional services found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!--Modal for Charges List -->
    <div class="modal fade" id="chargesModal" tabindex="-1" aria-labelledby="chargesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="chargesModalLabel">Additional Charges Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <?php if (!empty($additionalChargesInfo)) : ?>
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalAdditional = 0;
                                foreach ($additionalChargesInfo as $charge) :
                                    $totalAdditional += $charge['amount'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($charge['desc']) ?></td>
                                        <td class="text-end"><?= number_format($charge['amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">₱<?= number_format($totalAdditional, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    <?php else : ?>
                        <p class="text-muted mb-0">No additional charges found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <!-- Form for payment -->
    <form action="../../Function/Customer/Account/uploadPayment.php" method="POST" enctype="multipart/form-data">

        <?php foreach ($serviceIDs as $type => $ids):
            foreach ($ids as $id): ?>
                <input type="hidden" name="serviceIDs[<?= $type ?>][]" value="<?= $id ?>">
        <?php endforeach;
        endforeach; ?>

        <input type="hidden" name="startDate" value="<?= $rawStartDate ?>">
        <input type="hidden" name="endDate" value="<?= $rawEndDate ?>">
        <input type="hidden" name="bookingID" id="bookingID" value="<?= htmlspecialchars($bookingID) ?>">
        <input type="hidden" name="bookingType" id="bookingType" value="<?= htmlspecialchars($bookingType) ?>">
        <input type="hidden" name="confirmedBookingID" value="<?= htmlspecialchars($confirmedBookingID) ?>">
        <input type="hidden" name="fullName" value="<?= $clientName ?>">
        <input type="hidden" name="bookingCode" value="<?= $bookingCode ?>">
        <input type="hidden" name="phoneNumber" value="<?= $clientInfo['phoneNumber'] ?? '' ?>">
        <input type="hidden" name="finalBill" value="<?= $finalBill ?>">

        <div class="modal fade" id="gcashPayment1stModal" aria-hidden="true" aria-labelledby="gcashPayment1stModalLabel"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Upload Your Screenshot</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="gcashModalBody">
                        <p>
                            Please upload a screenshot of your <strong>GCash downpayment</strong> below.
                        </p>

                        <div class="alert alert-info mt-3">
                            <ul class="mb-0">
                                <li>Downpayment must be made within <strong>24 hours</strong> after booking creation for
                                    <strong>events and hotels</strong>.
                                </li>
                                <li>Downpayment for <strong>resort cottages</strong> is <strong>not required</strong>,
                                    but failure to pay may make your reserved cottage available to others.</li>
                                <li>Make sure the receipt is <strong>readable</strong>.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success w-100 mt-3" data-bs-toggle="modal"
                            data-bs-target="#gcashPaymentModal">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="gcashPaymentModal" aria-hidden="true" aria-labelledby="gcashPaymentModalLabel"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Upload Your Screenshot</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="gcashModalBody">
                        <p>
                            Please upload a screenshot of your <strong>GCash downpayment</strong> below.
                        </p>

                        <div class="mt-3 time-container">
                            <div class="due-date-container">
                                <label class="form-label fw-bold">Downpayment Due Date:</label>
                                <p><?= htmlspecialchars($within24hrs) ?> <br>
                                    <?= htmlspecialchars($downpaymentDueDate) ?> </p>
                            </div>

                            <div class="due-date-container">
                                <label class="form-label fw-bold">Full Payment Due Date:</label>
                                <p><?= htmlspecialchars($paymentDueDate) ?></p>
                            </div>
                        </div>

                        <div class="mt-2 text-center upload-photo-container">
                            <?php
                            if (isset($_SESSION['tempImage']) && file_exists(__DIR__ . '/../../Assets/Images/TempUploads/' . $_SESSION['tempImage'])) {
                                $imageSrc = '../../Assets/Images/TempUploads/' . $_SESSION['tempImage'];
                            } else {
                                $imageSrc = '../../Assets/Images/PaymentProof/' . ($_SESSION['savedDownpaymentImage'] ?? 'defaultDownpayment.png');
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>"
                                alt="Downpayment Image"
                                id="preview"
                                class="downpaymentPic mb-3">

                            <!-- You can drop imageFileName from the form now — it’s managed by PHP -->
                            <input type="file" name="downpaymentPic" id="downpaymentPic" hidden>
                            <label for="downpaymentPic" class="custom-file-button btn btn-primary mt-2">
                                Upload Payment Receipt
                            </label>
                        </div>


                        <div class="payment-details-container d-flex gap-3">
                            <div class="input-container mt-3">
                                <label for="downpayment" class="fw-bold">Downpayment Amount:</label>
                                <input type="text" id="downpayment" name="downpayment" value="<?= $downpayment ?>"
                                    class="form-control mt-1" readonly>
                            </div>
                            <div class="input-container mt-3">
                                <label for="payment" class="fw-bold">Enter payment amount:</label>
                                <input type="text" id="payment-amount" name="payment-amount" placeholder="1000"
                                    class="form-control mt-1"
                                    value="<?= !empty($_SESSION['payment-amount']) ? $_SESSION['payment-amount'] : '' ?>"
                                    required>
                                <div id="tooltip" class="custom-tooltip">Please input number</div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#gcashPayment1stModal">Back</button>
                        <button type="submit" class="btn btn-success" name="submitDownpaymentImage">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
        //Hide the make a downpayment button
        const paymentStatus = document.getElementById("paymentStatus").value;
        const bookingStatus = document.getElementById("bookingStatus").value;
        const paymentApprovalStatus = document.getElementById("paymentApprovalStatus").value;
        const paymentMethod = document.getElementById("paymentMethod").value;
        const downloadReceiptBtn = document.getElementById('downloadReceiptBtn');
        const viewTransactionBtn = document.getElementById('viewTransaction');
        // console.log("Booking Stat: " + bookingStatus);
        // console.log("payment App Stat" + paymentApprovalStatus);
        if ((bookingStatus === "Pending" && (paymentApprovalStatus === 'Pending' ||
                paymentApprovalStatus === '')) || (bookingStatus === 'Cancelled') || (
                bookingStatus === 'Rejected')) {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
            downloadReceiptBtn.style.display = 'none';
            viewTransactionBtn.style.display = 'none';
        } else if (bookingStatus === "Approved" && paymentApprovalStatus === "Pending" && (paymentStatus === "Unpaid" || paymentStatus === "Payment Sent")) {
            document.getElementById("makeDownpaymentBtn").style.display = "block";
            downloadReceiptBtn.style.display = 'none';
            viewTransactionBtn.style.display = 'block';
        } else if (paymentApprovalStatus === "Approved" && bookingStatus === 'Reserved' && (paymentStatus === "Partially Paid" || paymentStatus === "Fully Paid")) {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
            viewTransactionBtn.style.display = 'block';
        } else if ((bookingStatus === "Done" && paymentStatus === "Fully Paid") || bookingStatus === 'Expired') {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
            viewTransactionBtn.style.display = 'block';
        } else if (paymentMethod === 'Cash') {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (paymentMethod === 'GCash') {
            document.getElementById("makeDownpaymentBtn").style.display = "block";
        } else {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        };


        const input = document.getElementById('payment-amount');
        const tooltip = document.getElementById('tooltip');
        input.addEventListener('keypress', function(e) {
            if (!/[0-9.]/.test(e.key) || (e.key === '.' && input.value.includes('.'))) {
                tooltip.classList.add('show');
                e.preventDefault();
            }

            clearTimeout(tooltip.hideTimeout);
            tooltip.hideTimeout = setTimeout(() => {
                tooltip.classList.remove('show');
            }, 1000);
        });
    </script>

    <script>
        //Show the preview of image
        document.querySelector("input[type='file']").addEventListener("change", function(event) {
            let reader = new FileReader();
            reader.onload = function() {
                let preview = document.getElementById("preview");
                preview.src = reader.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>

    <!-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            const bookingType = document.getElementById("bookingType").value;

            // const downpaymentNoteContainer = document.getElementById("downpaymentNoteContainer");
            const addOnsContainer = document.getElementById("addOns");
            // const tourTypeContainer = document.getElementById("tourType");

            if (bookingType === "Resort") {
                addOnsContainer.style.display = "flex";
                // tourTypeContainer.style.display = "flex";
            } else if (bookingType === "Hotel") {
                addOnsContainer.style.display = "none";
                // tourTypeContainer.style.display = "none";
            } else {
                addOnsContainer.style.display = "none";
            }
        });
    </script> -->

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');

        const downpaymentValue = parseFloat(document.getElementById('downpayment').value);
        const paymentAmount = parseFloat(document.getElementById('payment-amount').value);

        if (paramValue === "imageSize") {
            Swal.fire({
                title: "Oops!",
                text: "File is too large. Maximum allowed size is 5MB.",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === 'error') {
            Swal.fire({
                title: 'Oops! Database problem!',
                text: 'There was an error while processing your request. Please try again later.',
                icon: 'warning',
                confirmButtonText: 'Okay'
            })
        } else if (paramValue === 'lessAmount') {
            Swal.fire({
                title: 'Oops',
                text: `Your payment is only ₱${paymentAmount.toFixed(2)}. Please complete the required downpayment of ₱${downpaymentValue.toFixed(2)}.`,
                icon: 'warning',
                confirmButtonText: 'Okay'
            }).then((result) => {
                const paymentModal = document.getElementById('gcashPaymentModal');
                const modal = new bootstrap.Modal(paymentModal);
                modal.show();

                document.getElementById('payment-amount').style.border = '1px solid red';
            })
        } else if (paramValue === 'imageFailed') {
            Swal.fire({
                title: 'Oops',
                text: `Make sure you uploaded an image`,
                icon: 'warning',
                confirmButtonText: 'Okay'
            }).then((result) => {
                const paymentModal = document.getElementById('gcashPaymentModal');
                const modal = new bootstrap.Modal(paymentModal);
                modal.show();

                document.querySelector('.custom-file-button').style.border = '2px solid red';
            });
        } else if (paramValue === 'extError') {
            Swal.fire({
                title: 'Oops',
                text: `Invalid file type. Please upload JPG, JPEG, WEBP, or PNG.`,
                icon: 'warning',
                confirmButtonText: 'Okay'
            }).then((result) => {
                const paymentModal = document.getElementById('gcashPaymentModal');
                const modal = new bootstrap.Modal(paymentModal);
                modal.show();
            })
        } else if (paramValue === 'serviceUnavailable') {
            Swal.fire({
                title: 'Booking Unavailable',
                text: `We regret to inform you that the service you booked is no longer available, as payment was not completed within 24 hours.`,
                icon: 'warning',
                confirmButtonText: 'Okay'
            })
        };

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }

        document.getElementById('payment-amount').addEventListener('input', () => {
            document.getElementById('payment-amount').style.border = '1px solid rgb(222, 222, 227)';
        });

        document.querySelector('.custom-file-button').addEventListener('click', () => {
            document.querySelector('.custom-file-button').style.border = '1px solid rgb(64, 136, 245)';
        })
    </script>


</body>

</html>