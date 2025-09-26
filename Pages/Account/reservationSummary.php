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

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

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

if (isset($_POST['bookingID'])) {
    $bookingID = intval($_POST['bookingID']);
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = intval($_SESSION['bookingID']);
}

require_once '../../Function/functions.php';


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
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">


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
                $middleInitial = trim($clientInfo['middleInitial']);
                $firstName = $clientInfo['firstName'];
                $clientName = ucfirst($firstName) . " " . ucfirst($clientInfo['middleInitial']) . " "  . ucfirst($clientInfo['lastName']);
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
                                                    cb.confirmedFinalBill, 
                                                    cb.userBalance, 
                                                    cb.confirmedBookingID, 
                                                    cb.discountAmount, 
                                                    cb.paymentApprovalStatus, 
                                                    cb.paymentStatus,  
                                                    cb.paymentDueDate, 
                                                    cb.downpaymentDueDate,
                                                    cb.downpaymentImage,
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
                $partnerServiceList = [];
                $downpaymentNotes = [];
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

                    //Types
                    $bookingType = $row['bookingType'] ?? null;
                    $serviceType = $row['serviceType'] ?? null;

                    //Payment Details
                    $paymentMethod =  $row['paymentMethod'];
                    $discount =  (float) $row['discountAmount'] ?? 0;
                    $originalBill =  (float) $row['originalBill'];
                    $downpayment =  (float) $row['downpayment'];
                    $additionalCharge =  (float) $row['additionalCharge'];
                    $downpaymentImageData = $row['downpaymentImage'];
                    if (!empty($downpaymentImageData)) {
                        $downpaymentImage = "../../Assets/Images/PaymentProof/" . $downpaymentImageData;
                    } else {
                        $downpaymentImage = "../../Assets/Images/PaymentProof/defaultDownpayment.png";
                    }
                    $bookingStatusID = $row['bookingStatus'] ?? null;
                    $bookingStatus = getStatuses($conn, $bookingStatusID);

                    if (!empty($confirmedBookingID)) {
                        $paymentApprovalStatusID = $row['paymentApprovalStatus'] ?? null;
                        $paymentStatusID = $row['paymentStatus'] ?? null;
                        $finalBill = (float) $row['confirmedFinalBill'] ?? null;
                        $downpaymentDueDate = !empty($row['downpaymentDueDate']) ? date('F d, Y h:i A', strtotime($row['downpaymentDueDate'])) : 'Not Stated';
                        $paymentDueDate = !empty($row['paymentDueDate']) ? date('F d, Y h:i A', strtotime($row['paymentDueDate'])) : 'Not Stated';
                        $dueDate = ($amountPaid === $downpayment || $amountPaid > $downpayment) ? $paymentDueDate : $downpaymentDueDate;
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

                    $buttonName = ($amountPaid === $downpayment || $amountPaid > $downpayment) ? 'Check Payment' : 'Make a downpayment';


                    //Pax Details
                    $toddlerCount = (int) $row['toddlerCount'];
                    $kidCount = (int) $row['kidCount'];
                    $adultCount = (int) $row['adultCount'];
                    $guestCount = intval($row['guestCount']);

                    //Additionals
                    $additionalReq = $row['additionalRequest'];
                    $additionalServices = $row['addOns'] ?? 'None';

                    $status = strtolower($bookingStatus['statusName']);
                    switch ($bookingStatus['statusID']) {
                        case 1: //Pending
                            $statusTitle = 'Reservation Pending Approval';
                            $statusSubtitle = 'Your request has been sent to the admin. You will be notified once it is approved.';
                            break;
                        case 2: //Approved
                            $status = strtolower($paymentApprovalStatus['statusName']);
                            switch ($paymentApprovalStatus['statusID']) {
                                case 1: //Pending
                                    $statusTitle = 'Your reservation has been approved.';
                                    if ($paymentMethod === 'GCash') {
                                        $statusSubtitle = 'You may now proceed with the down payment via GCash.';
                                    } elseif ($paymentMethod === 'Cash' && $bookingType === 'Resort') {
                                        $statusSubtitle = 'Please proceed on your scheduled swimming date and complete the payment on that day.';
                                    } else {
                                        $statusSubtitle = "You may now proceed to the resort to make your downpayment.";
                                    }


                                    break;
                                case 3: //Rejected
                                    $statusTitle = 'Payment was declined';
                                    $statusSubtitle = 'Please check the payment details and try again, or contact the admin for assistance.';
                                    break;
                            }
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: //Partially Paid
                                    $statusTitle = "Payment approved successfully.";
                                    $statusSubtitle = "We have received and reviewed your payment. The service you booked is now reserved. Thank you!";
                                    break;
                                case 3: //Fully Paid
                                    $statusTitle = "Payment done successfully.";
                                    $statusSubtitle = "Thank you! We have received your full payment. You may now enjoy your stay at the resort.";
                                    break;
                            }
                            break;
                        case 3: //Rejected
                            $statusTitle = 'Booking Rejected';
                            $statusSubtitle = 'We regret to inform you that your reservation has been rejected. Please contact us for more details.';
                            break;
                        case 4: //Cancelled
                            $statusTitle = 'Booking Cancelled';
                            $statusSubtitle = 'You have cancelled your reservation. If this was a mistake or you wish to rebook, please contact us.';
                            break;
                        case 5: //Done
                            $status = strtolower($paymentStatus['statusName']);
                            switch ($paymentStatus['statusID']) {
                                case 3: //Fully Paid
                                    $statusTitle = 'Booking Completed';
                                    $statusSubtitle = 'Thank you for staying with us! Your booking is fully paid and successfully completed. We hope you had a wonderful time.';
                                    break;
                            }
                            break;
                        case 6: //Expired
                            $statusTitle = "Expired Booking";
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

                        $downpaymentNotes[] = 'Any additional services offered by our business partners require separate approval and are not included in the reservation unless specifically requested and confirmed.';
                        $downpaymentNotes[] = 'The displayed price on the summary is only rough estimate. The price can change depending on the customer\'s discussions with the admin.';
                        if (!empty($serviceID)) {
                            if ($serviceType === 'Resort') {
                                $services[] = $venue = $row['RServiceName'] ?? 'none';
                                $venuePrice = $row['venuePricing'] ?? 0;
                                $serviceIDs[] = $row['resortServiceID'];
                            } elseif ($serviceType === 'Partnership') {
                                $partnerServicePrice = isset($row['PBPrice']) ? floatval($row['PBPrice']) : null;
                                $services[]  = $serviceName = $row['PBName'] ?? 'N/A';
                                $partnerServiceID = $row['partnershipServiceID'] ?? null;
                                $category = $row['category'] ?? null;

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
                            $serviceIDs[] = $row['resortServiceID'];
                        }
                        if ($serviceType === 'Entrance') {
                            $cardHeader = "Type of Tour";
                            $tourType = $row['tourType'];
                        }
                    }
                }

                // echo '<pre>';
                // print_r($services);
                // echo '</pre>';

                $serviceVenue = [];

                foreach ($services as $service) {
                    if (stripos($service, 'cottage') !== false) {
                        $serviceVenue[] = $service;
                    } elseif (stripos($service, 'room') !== false) {
                        $serviceVenue[] = $service;
                    } elseif (stripos($service, 'umbrella') !== false) {
                        $serviceVenue[] = $service;
                    }
                    if (stripos($service, 'Day') !== false) {
                        $tourType = "Day Tour";
                    } elseif (stripos($service, 'Night') !== false) {
                        $tourType = "Night Tour";
                    } elseif (stripos($service, 'Overnight') !== false) {
                        $tourType = "Overnight Tour";
                    }
                }
            }
            ?>



            <div class="leftStatusContainer">

                <input type="hidden" name="bookingStatus" id="bookingStatus"
                    value="<?= !empty($bookingStatus['statusName']) ? htmlspecialchars($bookingStatus['statusName']) : '' ?>">
                <input type="hidden" name="paymentApprovalStatus" id="paymentApprovalStatus"
                    value="<?= !empty($paymentApprovalStatus['statusName']) ? htmlspecialchars($paymentApprovalStatus['statusName']) : '' ?>">
                <input type="hidden" name="paymentStatus" id="paymentStatus"
                    value="<?= !empty($paymentStatus['statusName']) ?  htmlspecialchars($paymentStatus['statusName']) : '' ?>">
                <input type="hidden" name="paymentMethod" id="paymentMethod"
                    value="<?= htmlspecialchars($paymentMethod) ?>">

                <img src="../../Assets/Images/Icon/StatusIcon/<?= htmlspecialchars(ucfirst($status)) ?>.png"
                    alt="<?= ucfirst(htmlspecialchars($status)) ?> Icon" class="statusIcon">

                <h4 class="statusTitle"><?= htmlspecialchars($statusTitle) ?></h4>
                <h6 class="statusSubtitle"><?= htmlspecialchars($statusSubtitle) ?></h6>

                <div class="button-container">
                    <button type="button" class="btn btn-success w-100 mt-3" id="makeDownpaymentBtn" style="display: none;"
                        data-bs-toggle="modal" data-bs-target="#gcashPaymentModal"><?= $buttonName ?></button>
                    <!-- <a href="../bookNow.php" class="btn btn-primary w-100 mt-3" id="newReservationBtn">Make Another
                        Reservation</a> -->
                    <form action="../../Function/receiptPDF.php" method="POST" target="_blank">
                        <input type="hidden" name="totalCost" value="<?= $finalBill ?>">
                        <input type="hidden" name="name" value="<?= $clientName ?>">
                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                        <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                        <?php foreach ($services as $service): ?>
                            <input type="hidden" name="services[]" value="<?= $service ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary w-100 mt-3" name="downloadReceiptBtn" id="downloadReceiptBtn">Download Receipt </button>
                    </form>
                </div>
            </div>

            <div class="rightStatusContainer">
                <h3 class="rightContainerTitle">Reservation Summary</h3>

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
                                <p class="cardContent" id="eventDate"><?= $tourType ?></p>
                            </li>
                        <?php } elseif ($bookingType === 'Event') { ?>
                            <li class="list-group-item" id="tourType">
                                <h6 class="cardHeader"><?= $cardHeader ?></h6>
                                <p class="cardContent" id="eventDate"><?= $eventType ?></p>
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
                                    foreach ($serviceVenue as $venue): ?>
                                        <p class="cardContent" id="venue"><?= $venue ?></p>
                                    <?php endforeach;
                                } else { ?>
                                    <p class="cardContent" id="venue"><?= htmlspecialchars($venue) ?></p>
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
                                        <button data-bs-target="#foodListModal" data-bs-toggle="modal"> <img src="../../Assets/Images/Icon/information.png" alt="More Details" class="infoIcon"></button>
                                    </p>
                                <?php } else {  ?>
                                    <p class="cardContent">None</p>
                                <?php
                                }
                                ?>
                            </li>

                            <li class="list-group-item">
                                <h6 class="cardHeader">Additional Service</h6>
                                <?php if ($partnerServiceList) { ?>
                                    <p class="cardContent">Service List
                                        <button data-bs-target="#partnerServiceModal" data-bs-toggle="modal"> <img src="../../Assets/Images/Icon/information.png" alt="More Details" class="infoIcon"></button>
                                    </p>
                                <?php } else {  ?>
                                    <p class="cardContent">None</p>
                                <?php
                                }
                                ?>
                            </li>
                        <?php } else { ?>
                            <li class="list-group-item" id="addOns">
                                <h6 class="cardHeader">Add Ons</h6>
                                <p class="cardContent"><?= $additionalServices ?></p>
                            </li>
                        <?php } ?>

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

        <!-- Modal for menu list -->
        <div class="modal" tabindex="-1" id="foodListModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Event Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php foreach ($foodList as $category => $items) { ?>
                            <p class="foodNameLabel"><?= htmlspecialchars(strtoupper($category)) ?></p>
                            <?php foreach ($items as $name) { ?>
                                <ul>
                                    <li> <?= htmlspecialchars($name) ?></li>
                                </ul>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for partner service list -->
        <div class="modal" tabindex="-1" id="partnerServiceModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Additional Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <?php foreach ($partnerServiceList as $category => $items) { ?>
                            <p class="foodNameLabel"><?= htmlspecialchars(strtoupper($category)) ?></p>
                            <?php foreach ($items as $name => $price) { ?>
                                <ul>
                                    <li> <?= htmlspecialchars($name) ?> — ₱<?= number_format($price, 2) ?> </li>
                                </ul>
                            <?php } ?>
                        <?php } ?>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form for payment -->
        <form action="../../Function/Customer/Account/uploadPayment.php" method="POST" enctype="multipart/form-data">
            <div class="modal fade" id="gcashPaymentModal" aria-hidden="true" aria-labelledby="gcashPaymentModal"
                tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h1 class="modal-title">Upload Your Screenshot</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body" id="gcashModalBody">
                            Please upload a screenshot of your GCash down payment below.
                            <img src="<?= $downpaymentImage ?>" alt="Downpayment Image" id="preview"
                                class="downpaymentPic">
                            <input type="hidden" name="bookingID" id="bookingID" value="<?= $bookingID ?>">
                            <input type="hidden" name="bookingType" id="bookingType" value="<?= $bookingType ?>">
                            <input type="text" name="paymentDueDate" value="<?= $dueDate ?>">
                            <input type="file" name="downpaymentPic" id="downpaymentPic" hidden>
                            <label for="downpaymentPic" class="custom-file-button btn btn-outline-primary mt-2">
                                Upload Payment Receipt
                            </label>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="submitDownpaymentImage">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <script>
        //Hide the make a downpayment button
        const paymentStatus = document.getElementById("paymentStatus").value;
        const bookingStatus = document.getElementById("bookingStatus").value;
        const paymentApprovalStatus = document.getElementById("paymentApprovalStatus").value;
        const paymentMethod = document.getElementById("paymentMethod").value;

        // console.log("Booking Stat: " + bookingStatus);
        // console.log("payment App Stat" + paymentApprovalStatus);
        if (bookingStatus === "Pending" && paymentApprovalStatus === '') {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (bookingStatus === "Approved" && paymentApprovalStatus === "Pending" && paymentStatus === "Unpaid") {
            document.getElementById("makeDownpaymentBtn").style.display = "show";
        } else if (paymentApprovalStatus === "Approved" && paymentStatus === "Partially Paid") {
            document.getElementById("makeDownpaymentBtn").style.display = "show";
        } else if (paymentApprovalStatus === "Done" && paymentStatus === "Fully Paid") {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (paymentMethod === 'Cash') {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (paymentMethod === 'GCash') {
            document.getElementById("makeDownpaymentBtn").style.display = "block";
        } else {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        }
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

    <script>
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
    </script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');
        if (paramValue === "imageSize") {
            Swal.fire({
                title: "Oops!",
                text: "File is too large. Maximum allowed size is 64MB.",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === 'error') {
            Swal.fire({
                title: 'Oops',
                text: 'There was an error while processing your request. Please try again later.',
                icon: 'warning',
                confirmButtonText: 'Okay'
            })
        }


        if (paramValue) {
            const url = new URL(window.location.href);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>


</body>

</html>