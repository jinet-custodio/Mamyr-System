<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

require '../../Function/Helpers/statusFunctions.php';

$userID = $_SESSION['userID'] ?? '';
$userRole = $_SESSION['userRole'] ?? '';


if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT u.userID, u.userRole, a.adminID
                            FROM user u
                            LEFT JOIN admin a ON u.userID = a.userID
                            WHERE u.userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
        $_SESSION['adminID'] = (int) $user['adminID'];
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

switch ($userRole) {
    case 3:
        $role = "Admin";
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
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
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
            $button = !empty($_POST['button']) ? mysqli_real_escape_string($conn, $_POST['button']) : 'booking';
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
        $getUserInfo = $conn->prepare("SELECT u.*, b.* FROM booking b
        INNER JOIN user u ON b.userID = u.userID
        WHERE b.bookingID = ?");
        $getUserInfo->bind_param("i", $bookingID);
        $getUserInfo->execute();
        $resultUserInfo = $getUserInfo->get_result();
        if ($resultUserInfo->num_rows > 0) {
            $data = $resultUserInfo->fetch_assoc();
            $customerID = (int) $data['userID'];
            $middleInitial = trim($data['middleInitial'] ?? '');
            $name = ucfirst($data['firstName'] ?? '') . " " . $middleInitial . " "  . ucfirst($data['lastName'] ?? '');
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
                            <input type="hidden" name="firstName" value="<?= $data['firstName'] ?>">
                            <input type="hidden" name="email" value="<?= $email ?>">
                            <p class="card-text sub-name"><?= htmlspecialchars($email) ?> |
                                <?= htmlspecialchars($phoneNumber) ?> </p>
                            <p class="card-text sub-name"><?= htmlspecialchars($address) ?></p>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="button" class="btn btn-success approveReject" data-bs-toggle="modal"
                            data-bs-target="#chargesModal">Add Charges</button>
                        <div id="button-approval-container">
                            <button type="button" class="btn btn-primary approveReject" data-bs-toggle="modal"
                                data-bs-target="#finalizedModal">Approve</button>
                            <button type="button" class="btn btn-danger approveReject" data-bs-toggle="modal"
                                data-bs-target="#rejectionModal">Reject</button>
                        </div>

                    </div>
                </div>


                <!-- Get booking information to the database -->
                <?php

                $getBookingInfo = $conn->prepare("SELECT 
                                                    b.bookingCode,
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
                                                    b.customerChoice,  
                                                    b.additionalCharge as charges,

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
                                                    cb.finalBill, 
                                                    cb.userBalance, 
                                                    cb.confirmedBookingID, 
                                                    cb.discountAmount, 
                                                    cb.paymentApprovalStatus, 
                                                    cb.paymentStatus,  
                                                    cb.paymentDueDate, 
                                                    cb.downpaymentDueDate,
                                                    cb.additionalCharge,

                                                    bpas.approvalStatus,
                                                    bpas.availedDate,

                                                    ac.chargeDescription,
                                                    ac.amount,
                                                    ac.additionalChargeID
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

                                                LEFT JOIN additionalcharge ac 
                                                    ON b.bookingID = ac.bookingID
                                                -- LEFT JOIN payment p 
                                                --     ON cb.confirmedBookingID = p.confirmedBookingID
                                                LEFT JOIN businesspartneravailedservice bpas 
                                                    ON b.bookingID = bpas.bookingID
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
                    $pricePerHead = 0;
                    $additionalChargesInfo = [];
                    // $businessApproval = '';
                    while ($row = $getBookingInfoResult->fetch_assoc()) {

                        // echo '<pre>';
                        // print_r($row);
                        // echo '</pre>';

                        // Date and Time
                        $bookingCode = $row['bookingCode'];
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
                            $paymentStatus = $row['paymentStatus'] ?? 1;
                            $finalBill = (float) $row['finalBill'] ?? null;
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
                        $additionalCharge = !empty($confirmedBookingID) ? (float) $row['additionalCharge'] : (float) $row['charges'];
                        error_log('Additional Charge: ' .  $row['charges']);
                        //Pax Details
                        $toddlerCount = (int) $row['toddlerCount'];
                        $kidCount = (int) $row['kidCount'];
                        $adultCount = (int) $row['adultCount'];
                        $guestCount = intval($row['guestCount']);

                        //Additionals
                        $additionalReq = $row['additionalRequest'];
                        $additionalServices = $row['addOns'] ?? 'None';

                        $additionalChargeID = $row['additionalChargeID'] ?? null;

                        if (!empty($additionalChargeID)) {
                            $alreadyAdded = array_column($additionalChargesInfo ?? [], 'id');
                            if (!in_array($additionalChargeID, $alreadyAdded)) {
                                $additionalChargesInfo[] = [
                                    'id' => $additionalChargeID,
                                    'desc' => $row['chargeDescription'],
                                    'amount' => $row['amount']
                                ];
                            }
                        }

                        if (!empty($customPackageID)) {
                            $eventType = $row['eventType'] ?? null;
                            $foodItemID = isset($row['foodItemID']) ? $row['foodItemID'] : null;
                            $totalPax = $guestCount . ' people' ?? 1 . ' person';
                            $additionalServicePrice = floatval($row['additionalServicePrice']);
                            $customerChoice = strtolower($row['customerChoice'] ?? 'N/A');

                            switch ($customerChoice) {
                                case 'proceed':
                                    $customerDecisionMessage = 'The customer chose to proceed regardless of the partner service’s decision.';
                                    break;

                                case 'cancel':
                                    $customerDecisionMessage = 'The customer chose to cancel the reservation if the partner service declines their booking.';
                                    break;

                                default:
                                    $customerDecisionMessage = 'Please call the customer to confirm their decision regarding the partner’s event service.';
                                    break;
                            };

                            $approvalStatusID = $row['approvalStatus'] ?? null;
                            if (!empty($approvalStatusID)) {
                                $approvalStatus = getStatuses($conn, $approvalStatusID);
                                $businessApprovalStatus = $approvalStatus['statusName'] ?? null;

                                switch ($approvalStatusID) {
                                    case 1:
                                        $businessApprovalColor = 'warning';
                                        break;
                                    case 2:
                                        $businessApprovalColor = 'green';
                                        break;
                                    case 3:
                                        $businessApprovalColor = 'success';
                                        break;
                                    case 5:
                                        $businessApprovalColor = 'red';
                                        break;
                                    case 7:
                                        $businessApprovalColor = 'muted';
                                        break;
                                    default:
                                        $businessApprovalColor = 'muted';
                                        break;
                                }
                            }

                            $availedDate = new DateTime($row['availedDate'] ?? $createdAt);
                            $approvalTimeRange = (clone $availedDate)->modify('+24 hours');
                            $approvalTimeUntil = $approvalTimeRange->format('M. d,Y g:i A');

                            if (!empty($serviceID)) {
                                if ($serviceType === 'Resort') {
                                    $venue = $row['RServiceName'] ?? 'none';
                                    $venuePrice = $row['venuePricing'] ?? 0;
                                    $serviceIDs[] = $row['resortServiceID'];
                                } elseif ($serviceType === 'Partnership') {
                                    $partnerServicePrice = isset($row['PBPrice']) ? floatval($row['PBPrice']) : null;
                                    $serviceName = $row['PBName'] ?? 'N/A';
                                    $partnerServiceID = $row['partnershipServiceID'] ?? null;
                                    $partnerID = $row['partnershipID'] ?? null;


                                    if ($partnerServiceID !== null) {
                                        $partnerServices[$partnerID][] = [
                                            'name' => $serviceName,
                                            'price' => $partnerServicePrice,
                                            'partnershipServiceID' => $partnerServiceID,
                                            'approvalStatusID' => $approvalStatusID,
                                            'approvalStatus' => $businessApprovalStatus ?? 'N/A',
                                            'approvalColor' => $businessApprovalColor ?? 'muted',
                                            'approvalTimeUntil' => $approvalTimeUntil,
                                        ];
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
                            <div class="modal-body">

                                <h6 class="reject-label fw-bold">Select a Reason for Rejection</h6>
                                <div class="form-group mt-4">
                                    <select class="form-select" id="select-reason" name="rejection-reason"
                                        aria-label="rejection-reason" onchange="otherReason()">
                                        <option value="" disabled selected>Select a reason</option>
                                        <?php
                                        $category = 'BookingRejection';
                                        $getRejectionReason = $conn->prepare("SELECT `reasonID`, `reasonDescription` FROM `reason` WHERE `category` = ?");
                                        $getRejectionReason->bind_param('s', $category);
                                        if (!$getRejectionReason->execute()) {
                                            error_log('Failed getting rejection reason');
                                        ?>
                                            <option value="other">Other (Please specify)</option>
                                        <?php
                                        }

                                        $result = $getRejectionReason->get_result();

                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                            <option value="<?= $row['reasonID'] ?>">
                                                <?= htmlspecialchars($row['reasonDescription']) ?></option>
                                        <?php
                                        endwhile;
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group mt-4" id="otherInputGroup" style="display: none;">
                                    <h6 class="otherReason-label fw-bold">Please Specify</h6>
                                    <input type="text" class="form-control" id="rejectReason-textBox"
                                        placeholder="Enter your option">
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">Close</button>
                                <button type="submit" class="btn btn-danger" name="rejectBtn">Reject</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Finalization Modal -->
                <div class="modal fade" id="finalizedModal" tabindex="-1" aria-labelledby="finalizedModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="finalizedModalLabel">Finalize Booking</h5>
                            </div>
                            <div class="modal-body finalized-booking-modal-body">
                                <div class="original-price-container">
                                    <?php if (!empty($foodList)) { ?>
                                        <div class="mb-3">
                                            <label class="form-label">Original Food Price (₱)</label>
                                            <input type="text" class="form-control" id="foodPrice" name="foodPrice"
                                                value="<?= $foodPriceTotal ?>" readonly>
                                        </div>
                                    <?php } ?>

                                    <div class="mb-3">
                                        <label class="form-label">Original Bill (₱)</label>
                                        <input type="text" class="form-control" id="originalBill"
                                            value="<?= $finalBill ?>" readonly>
                                    </div>
                                </div>
                                <hr>
                                <div class="updated-price-container">
                                    <?php if (!empty($foodList)) { ?>
                                        <div class="mb-3">
                                            <label for="newFoodPrice" class="form-label">Enter Updated Food Price
                                                (₱)</label>
                                            <input type="text" class="form-control" id="newFoodPrice" name="newFoodPrice"
                                                placeholder="10000">
                                        </div>
                                    <?php } else { ?>
                                        <div class="mb-3">
                                            <label for="newBaseAmount" class="form-label">Enter Updated Total Amount
                                                (₱)</label>
                                            <input type="text" class="form-control" id="newBaseAmount" name="newFinalBill"
                                                placeholder="10000">
                                        </div>
                                    <?php  } ?>
                                </div>

                                <div class="discount-container mt-3">
                                    <p>Would you like to give a discount?</p>
                                    <div class="d-flex mb-2 discount-choice">
                                        <button type="button" class="btn btn-primary w-50 me-2"
                                            id="addDiscount">Yes</button>
                                        <button type="button" class="btn btn-secondary w-50" id="noDiscount"
                                            style="display:none;">No</button>
                                    </div>

                                    <div id="add-discount-container" style="display:none;">
                                        <label for="discountAmount" class="form-label">Discount Amount (₱)</label>
                                        <input type="text" class="form-control" id="discountAmount"
                                            name="discountAmount" placeholder="Enter discount amount">
                                    </div>
                                </div>

                                <hr>

                                <!-- Summary Section -->
                                <div id="summaryContainer">
                                    <h6 class="fw-bold">Summary</h6>
                                    <p>Food Price: ₱<?= $foodPriceTotal ?> -> <strong> ₱<span id="summaryUpdatedFoodPrice">0.00</span></p> </strong>
                                    <?php if (empty($foodList)) { ?>
                                        <p>Total Amount: ₱<?= $finalBill ?> -> <strong> ₱<span id="summaryUpdatedTotalAmount">0.00</span></p> </strong>
                                    <?php } ?>
                                    <p>Discount: ₱<span id="summaryDiscount">0.00</span></p>
                                    <hr>
                                    <p><strong>Final Bill: ₱<span id="summaryFinalBill">0.00</span></strong></p>
                                    <input type="hidden" id="finalBill" name="finalBill" value="">
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#approvalModal">Next</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Approval Modal -->
                <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <p class="approvalModal-p">You are about to approve a booking. Please review the
                                    details carefully. Once you
                                    approve, the booking will be finalized and cannot be undone.</p>
                                <p class="approvalModal-p"><strong>Do you want to approve this booking?</strong>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">Close</button>
                                <button type="submit" class="btn btn-primary loaderTrigger" name="approveBtn">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!--  Charges Modal -->
                <div class="modal fade" id="chargesModal" tabindex="-1" aria-labelledby="chargesModal" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="chargesModalLabel">Additional Charges</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body" id="chargesModalBody">
                                <div class="original-info-container">
                                    <div class="input-container">
                                        <label for="finalBillValue">Total Amount:</label>
                                        <input type="text" id="finalBillValue" value="<?= $finalBill ?>" readonly class="form-control">
                                    </div>
                                    <div class="input-container">
                                        <label for="finalBillValue">Original:</label>
                                        <input type="text" id="additionalChargeValue" value="<?= $additionalCharge ?>" readonly class="form-control">
                                    </div>
                                </div>
                                <div class="additionalCharge-container mt-3">
                                    <label for="additional-charge">Additionals</label>
                                    <div class="form-group mt-3">
                                        <div class="checkbox-group">

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="additional-bed"
                                                    data-input="additional-bed-input"
                                                    onchange="toggleAdditionalInput()">
                                                <label for="additional-bed">Additional Bed</label>
                                                <!-- Additional Input for Quality and Charge -->
                                                <div class="additional-input gap-1" id="additional-bed-input"
                                                    style="display: none;">
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="bed-quantity" name="additionalCharges[bed][quantity]" value="" data-role="quantity">
                                                        <label for="bed-quantity">Quantity</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="bed-amount" data-role="amount" name="additionalCharges[bed][amount]">
                                                        <label for="bed-amount">Amount</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="additional-kitchenware"
                                                    data-input="additional-kitchenware-input"
                                                    onchange="toggleAdditionalInput()">
                                                <label for="additional-kitchenware">Kitchenware</label>
                                                <!-- Additional Input for Quality and Charge -->
                                                <div class="additional-input  gap-1" id="additional-kitchenware-input"
                                                    style="display: none;">
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="kitchenware-quantity" name="additionalCharges[kitchenware][quantity]" data-role="quantity">
                                                        <label for="kitchenware-quantity">Quantity</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="kitchenware-amount" name="additionalCharges[kitchenware][amount]" data-role="amount">
                                                        <label for="kitchenware-amount">Amount</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="electric-fan"
                                                    data-input="electric-fan-input" onchange="toggleAdditionalInput()">
                                                <label for="electric-fan">Electric Fan</label>
                                                <!-- Additional Input for Quantity and Charge -->

                                                <div class="additional-input efan  gap-1" id="electric-fan-input"
                                                    style="display: none;">
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="electricFan-quantity" name="additionalCharges[fan][quantity]" data-role="quantity">
                                                        <label for="electric-fan-quantity">Quantity</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="electricFan-amount" name="additionalCharges[fan][amount]" data-role="amount">
                                                        <label for="electric-fan-amount">Amount</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="table"
                                                    data-input="table-input" onchange="toggleAdditionalInput()">
                                                <label for="table">Table</label>
                                                <!-- Additional Input for Quality and Charge -->
                                                <div class="additional-input  gap-1" id="table-input" style="display: none;">
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="table-quantity" name="additionalCharges[table][quantity]" data-role="quantity">
                                                        <label for="table-quantity">Quantity</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="table-amount" name="additionalCharges[table][amount]" data-role="amount">
                                                        <label for="table-amount">Amount</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="additional-person"
                                                    data-input="additional-person-input"
                                                    onchange="toggleAdditionalInput()">
                                                <label for="additional-person">Additional Person</label>
                                                <!-- Additional Input for Quantity and Charge -->
                                                <div class="additional-input  gap-1" id="additional-person-input"
                                                    style="display: none;">
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="person-quantity" name="additionalCharges[person][quantity]" data-role="quantity">
                                                        <label for="kitchenware-quantity">Quantity</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number"" class=" form-control" id="person-amount" name="additionalCharges[person][amount]" data-role="amount">
                                                        <label for="kitchenware-amount">Amount</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="others"
                                                    data-input="others-input" onchange="toggleAdditionalInput()">
                                                <label for="others">Others</label>
                                                <!-- Additional Input for Description and Charge -->
                                                <div class="additional-input" id="others-input" style="display: none;">
                                                    <div class="form-floating mb-1">
                                                        <input type="text" class="form-control" id="other-desc" name="additionalCharges[others][name]" data-role="name">
                                                        <label for="other-name">Description</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="other-quantity" name="additionalCharges[others][quantity]" data-role="quantity">
                                                        <label for="other-quantity">Quantity</label>
                                                    </div>
                                                    <div class="form-floating mb-1">
                                                        <input type="number" class="form-control" id="other-amount" name="additionalCharges[others][amount]" data-role="amount">
                                                        <label for="other-amount">Amount</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary Section -->
                                <div id="summaryContainer" class="border-top pt-3">
                                    <h6 class="fw-bold mb-1">Summary</h6>
                                    <div id="additionalSummary" class="mt-2">
                                        <h6 class="fw-semibold">Additional Charges</h6>
                                        <ul class="list-group mb-1" id="additional-charges-list">
                                        </ul>
                                        <p>Total Additional Charges: ₱<span id="total-additional-charges">0.00</span></p>
                                    </div>

                                    <hr>
                                    <p><strong>Final Bill: ₱<span id="summary-total-amount">0.00</span></strong></p>
                                    <input type="hidden" id="new-bill" name="new-bill" value="">
                                    <input type="hidden" id="additional-charge" name="additional-charge" value="">
                                </div>
                            </div>
                            <div class="modal-footer">

                                <button type="submit" class="btn btn-primary" name="submitCharges"
                                    id="submitCharges">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display the information -->
                <input type="hidden" name="customPackageID" id="customPackageID" value="<?= $customPackageID ?>">
                <input type="hidden" name="confirmedBookingID" value="<?= $confirmedBookingID ?>">
                <div class="card" id="info-card">
                    <div class="bookingInfoLeft" id="bookingInformation">
                        <div class="row1">
                            <input type="hidden" name="bookingCode" id="bookingCode" value="<?= $bookingCode ?>">
                            <input type="hidden" name="startDate" value="<?= $startDate ?>">
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
                            <div class="info-container" id="booking-info-container">
                                <label for="paxNum" class="info-label mb-2">Number of People:</label>
                                <input type="text" class="form-control inputDetail" name="paxNum" id="paxNum"
                                    value="<?= $totalPax ?>" readonly>
                            </div>
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
                                    <h1 class="card-title text-center">Additional Service/s</h1>
                                    <?php if (!empty($partnerServices)) { ?>
                                        <?php foreach ($partnerServices as $partnerID => $services) { ?>
                                            <ul>
                                                <?php foreach ($services as $i => $service) { ?>
                                                    <li class="servicesList">
                                                        <?= htmlspecialchars($service['name']) ?> —
                                                        ₱<?= number_format($service['price'], 2) ?>
                                                        <span class="badge bg-<?= htmlspecialchars($service['approvalColor']) ?>">
                                                            <?= htmlspecialchars($service['approvalStatus']) ?>
                                                        </span>
                                                        <small class="text-muted">
                                                            (wait until <?= htmlspecialchars($service['approvalTimeUntil']) ?>)
                                                        </small>

                                                        <!-- Hidden fields -->
                                                        <input type="hidden" name="partnerServices[<?= $partnerID ?>][<?= $i ?>][id]"
                                                            value="<?= $service['partnershipServiceID'] ?>">

                                                        <input type="hidden"
                                                            name="partnerServices[<?= $partnerID ?>][<?= $i ?>][status]"
                                                            value="<?= htmlspecialchars($service['approvalStatus']) ?>">

                                                        <input type="hidden" name="partnerServices[<?= $partnerID ?>][<?= $i ?>][price]"
                                                            value="<?= htmlspecialchars($service['price']) ?>">
                                                    </li>
                                                <?php } ?>

                                            </ul>
                                        <?php } ?>
                                        <input type="hidden" name="customerChoice" value="<?= $customerChoice ?>">
                                        <p class="note text-primary text-center">
                                            <?= htmlspecialchars($customerDecisionMessage) ?></p>
                                    <?php } else { ?>
                                        <h1 class="text-center defaultMess">None</h1>
                                    <?php } ?>

                                </div>
                            <?php  } ?>



                            <div class="row3 mt-4">
                                <?php if ($bookingType !== 'Event') { ?>
                                    <div class="additionalServices" id="booking-info-container">
                                        <label for="addOns" class="info-label mb-2">Additional Service/s</label>
                                        <input type="text" class="form-control inputDetail" name="addOns" id="addOns"
                                            value="<?= $additionalServices ?>" readonly>
                                    </div>
                                <?php  } ?>
                            </div>

                            <div class="additional-charges-container">
                                <h6 class="fw-bold">Additional Charge/s Info</h6>
                                <?php if (!empty($additionalChargesInfo)) { ?>
                                    <ul>
                                        <?php foreach ($additionalChargesInfo as $charge): ?>
                                            <li>
                                                <p><?= ucfirst($charge['desc']) ?> &mdash; ₱<?= number_format($charge['amount'], 2) ?></p>
                                            </li>

                                        <?php endforeach; ?>
                                    </ul>
                                <?php     } else { ?>
                                    <p class="text-center defaultMess">No Additional Charge/s!</p>
                                <?php  } ?>

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
                                    <input type="text" class="form-control inputDetail w-50"
                                        value="₱<?= number_format($foodPriceTotal, 2) ?>" readonly>
                                </div>

                                <div class="info-container paymentInfo" id="payment-info">
                                    <label for="additionalServicePrice" class="mt-2">Additional Service/s Price</label>
                                    <input type="text" class="form-control inputDetail w-50" name="additionalServicePrice"
                                        id="additionalServicePrice"
                                        value="₱<?= number_format($additionalServicePrice, 2) ?>" readonly>
                                </div>
                            <?php } ?>

                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="additionalCharge" class="mt-2">Additional Charge/s</label>
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
                                <input type="text" class="form-control inputDetail w-50"
                                    value="₱<?= number_format($originalBill, 2) ?>" readonly>
                            </div>

                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="discountAmount" class="mt-2">Discount</label>
                                <div class="discountform">
                                    <input type="text" class="form-control inputDetail w-100"
                                        value="₱<?= number_format($discount, 2) ?>" readonly>
                                </div>
                            </div>

                            <div class="info-container paymentInfo" id="payment-info">
                                <label for="finalBill" class="mt-2">Final Bill</label>
                                <input type="text" class="form-control inputDetail w-50"
                                    value="₱<?= number_format($finalBill, 2) ?>" readonly>
                            </div>
                        </div>

                        <div class="notesContainer mt-3">
                            <div class="info-container notes">
                                <label for="req" class="info-label mt-2 mb-2">Additional Request(s)/Note(s)</label>
                                <textarea class="form-control inputDetail" rows="4" name="req" id="req"
                                    readonly><?= $additionalReq ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="hidden-inputs" style="display: none;">
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
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>


    <!-- Allow adding discount and changing final bill -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const formControls = document.querySelectorAll('#finalizedModal .form-control');

            const discountContainer = document.getElementById('add-discount-container');
            const discountInput = document.getElementById('discountAmount');
            const noDiscountBtn = document.getElementById('noDiscount');

            const foodPrice = document.getElementById('foodPrice');
            const originalBill = document.getElementById('originalBill');

            const newFoodPrice = document.getElementById('newFoodPrice');
            const newBaseAmount = document.getElementById('newBaseAmount');

            const updatedTotalAmount = document.getElementById('summaryUpdatedTotalAmount');
            const updatedFoodPrice = document.getElementById('summaryUpdatedFoodPrice');
            const discountSummary = document.getElementById('summaryDiscount');

            const finalBill = document.getElementById('finalBill');
            const summaryFinalBill = document.getElementById('summaryFinalBill');

            document.getElementById('addDiscount').addEventListener('click', () => {
                discountContainer.style.display = 'block';
                discountInput.style.border = '1px solid red';
                noDiscountBtn.style.display = 'block';

                discountInput.addEventListener('change', () => {
                    discountInput.style.border = '1px solid rgb(223, 226, 230)';
                    updateSummary();
                });
                updateSummary();
            });

            noDiscountBtn.addEventListener('click', () => {
                discountContainer.style.display = 'none';
                noDiscountBtn.style.display = 'none';
                discountInput.value = '';
                updateSummary();
            });

            function updateSummary() {
                const foodPriceValue = foodPrice ? parseFloat(foodPrice.value) || 0 : 0;
                const originalBillValue = parseFloat(originalBill.value) || 0;
                const discountValue = discountInput ? parseFloat(discountInput.value) || 0 : 0;
                const baseAmountValue = newBaseAmount ? parseFloat(newBaseAmount.value) || 0 : originalBillValue;
                const newFoodPriceValue = newFoodPrice ? parseFloat(newFoodPrice.value) || 0 : 0;
                let totalOriginalBill = 0;
                if (newFoodPriceValue != 0) {
                    const originalBillWithoutFood = originalBillValue - foodPriceValue;
                    totalOriginalBill = (originalBillWithoutFood + newFoodPriceValue) - discountValue;
                } else {
                    totalOriginalBill = ((baseAmountValue === 0) ? originalBillValue : baseAmountValue) - discountValue;
                }

                // const

                finalBill.value = totalOriginalBill.toFixed(2);
                if (summaryFinalBill) summaryFinalBill.textContent = totalOriginalBill.toFixed(2);
                if (updatedFoodPrice) updatedFoodPrice.textContent = newFoodPriceValue.toFixed(2);
                if (updatedTotalAmount) updatedTotalAmount.textContent = baseAmountValue.toFixed(2);
                if (discountSummary) discountSummary.textContent = discountValue.toFixed(2);
            };

            const inputs = [
                newFoodPrice,
                newBaseAmount,
                discountInput
            ].filter(Boolean);


            inputs.forEach(input => {
                input.addEventListener('input', updateSummary);
            });

            // console.log(inputs);

            updateSummary();

            //Disable any letter but allowed the peiod
            formControls.forEach(formControl => {
                formControl.addEventListener('keypress', function(e) {
                    if (/[0-9]/.test(e.key)) return;

                    if (e.key === '.' && !formControl.value.includes('.')) return;

                    e.preventDefault();
                });
            });

        });
    </script>

    <script>
        function toggleAdditionalInput() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            var additionalInputs = document.querySelectorAll('.additional-input');

            additionalInputs.forEach(function(input) {
                input.style.display = 'none';
                input.querySelectorAll('input').forEach(i => i.disabled = true);
            });

            checkboxes.forEach(function(checkbox) {
                const inputId = checkbox.getAttribute('data-input');
                const container = document.getElementById(inputId);
                const inputs = container.querySelectorAll('input');

                if (checkbox.checked) {
                    if (inputId === 'others-input') {
                        document.getElementById(inputId).style.display = 'block';
                    } else {
                        document.getElementById(inputId).style.display = 'flex';
                    }
                    inputs.forEach(input => input.disabled = false);
                } else {
                    container.style.display = 'none';
                    inputs.forEach(input => {
                        input.value = '';
                        input.disabled = true;
                    });

                    const name = inputId.split('-')[1] || inputId.replace('-input', '');

                    const index = data.findIndex(item => Object.keys(item)[0] === name);
                    if (index !== -1) {
                        data.splice(index, 1);
                    }
                }
            });

            updateAddPaymentSummary();
        }


        const finalBill = document.getElementById('finalBillValue');
        const originalCharge = document.getElementById('additionalChargeValue');
        const finalBillInput = document.getElementById('new-bill');
        const additionalChargeInput = document.getElementById('additional-charge');
        const chargesList = document.getElementById("additional-charges-list");
        const additionalChargeContainer = document.querySelectorAll('.additionalCharge-container .form-control');
        const data = [];

        additionalChargeContainer.forEach(form => {
            form.addEventListener('input', () => {
                const parts = form.id.split('-');
                const name = parts[0];
                const property = parts[1];

                let entry = data.find(item => item[name]);
                if (!entry) {
                    entry = {
                        [name]: {}
                    };
                    data.push(entry);
                }
                entry[name][property] = isNaN(form.value) ? form.value : Number(form.value);
                updateAddPaymentSummary();
                // console.log(data);
            });
        });


        function updateAddPaymentSummary() {
            const finalBillValue = parseFloat(finalBill.value) || 0;
            const originalChargeValue = parseFloat(originalCharge.value) || 0;
            let additionalChargesTotal = 0;
            chargesList.innerHTML = '';

            data.forEach(item => {
                const name = Object.keys(item)[0];
                const properties = item[name];

                if (name === 'other') {
                    displayName = properties.desc;
                } else if (name === 'electricFan') {
                    displayName = 'Electric Fan';
                } else {
                    displayName = name;
                }

                const li = document.createElement('li');

                li.textContent = `${displayName.charAt(0).toUpperCase() + displayName.slice(1)} — Quantity: ${parseInt(properties.quantity) || 0}, Amount: ${parseFloat(properties.amount) || 0}`;
                additionalChargesTotal += parseFloat(properties.amount) || 0;

                chargesList.appendChild(li);
            });

            const chargesTotal = originalChargeValue + additionalChargesTotal;
            const totalBill = finalBillValue + chargesTotal;
            //Summary display
            document.getElementById('total-additional-charges').textContent = chargesTotal.toFixed(2);
            document.getElementById('summary-total-amount').textContent = totalBill.toFixed(2);

            finalBillInput.value = totalBill;
            additionalChargeInput.value = chargesTotal;
        };

        document.addEventListener("DOMContentLoaded", function() {
            const checkboxGroups = document.querySelectorAll('.checkbox-group .form-control');
            checkboxGroups.forEach((input) => {
                input.disabled = true;
            })
            updateAddPaymentSummary();
        });
    </script>

    <!--//* Hiding buttons -->
    <script>
        const paymentApprovalStatus = document.getElementById('paymentApprovalStatus').value;
        const bookingStatus = document.getElementById('bookingStatusName').value;

        const buttonContainer = document.getElementById('button-approval-container');

        if (paymentApprovalStatus === 'Done' ||
            bookingStatus === 'Expired' ||
            bookingStatus === 'Rejected' ||
            bookingStatus === 'Cancelled' ||
            paymentApprovalStatus === 'Rejected' ||
            paymentApprovalStatus === 'Cancelled' ||
            bookingStatus === 'Approved' ||
            bookingStatus === 'Reserved') {
            buttonContainer.style.display = "none";
        }
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const action = param.get('action');
        // const urlParams = new URLSearchParams(window.location.search);
        // const action = urlParams.get('action');


        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        if (action === 'approvalFailed') {
            const errorMessage = window.approvalErrorMessage ||
                "The booking request could not be approved. Please try again later.";

            Swal.fire({
                title: "Failed!",
                text: errorMessage,
                icon: 'error',
            });
        } else if (action === 'chargesAdded') {
            Toast.fire({
                title: "Applied Charges Successfully",
                icon: 'success',
            });
        } else if (action === 'chargesError') {
            Swal.fire({
                title: "Failed!",
                text: 'Server Error: An error occured in database. Please try again later!',
                icon: 'error',
            });
        }


        // if (paramValue === "approvalFailed") {
        //     Swal.fire({
        //         title: "Failed!",
        //         text: "The booking request could not be approved. Please try again later.",
        //         icon: 'error',
        //     });
        // } else if (paramValue === 'rejectionEmpty') {
        //     Swal.fire({
        //         title: "Oops!",
        //         text: "Please provide the reason for your rejection",
        //         icon: 'warning',
        //         confirmButtonText: 'Okay',
        //     }).then((result) => {
        //         const rejectionModal = document.getElementById('rejectionModal');
        //         const modal = new bootstrap.modal(rejectionModal);
        //         modal.show();

        //         // document.getElementById('rejectionReason').style.border = '1px solid red';
        //     });
        // } else if (paramValue === 'rejectionFailed') {
        //     Swal.fire({
        //         title: "Failed!",
        //         text: "The booking request could not be rejected. Please try again later.",
        //         icon: 'error',
        //     });
        // } else if (paramValue === 'addOnsService-rejected') {
        //     Swal.fire({
        //         title: "Oops! You can’t approve this booking",
        //         text: "The customer’s decision is to cancel this booking if any availed partnership service is declined.",
        //         icon: 'info',
        //     });

        // }

        if (action) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url);
        }
    </script>

    <script>
        function otherReason() {
            var selectBox = document.getElementById("select-reason");
            var otherInputGroup = document.getElementById("otherInputGroup");

            // Show or hide the text box when "Other (Please specify)" is selected
            if (selectBox.value === "other" || selectBox.value === '17') {
                otherInputGroup.style.display = "block"; // Show the text box
            } else {
                otherInputGroup.style.display = "none"; // Hide the text box
            }
        }
    </script>

    <?php include '../Customer/loader.php'; ?>
</body>

</html>