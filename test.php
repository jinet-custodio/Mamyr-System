//* Unused Code

// Trigger an error
echo $undefined_var;
//* Display query
// Custom log entry
error_log("Manual error log test.");
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

fp.pricePerHead,

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
cb.downpaymentImage,
cb.downpaymentDueDate,
cb.additionalCharge
FROM booking b
LEFT JOIN confirmedbooking cb
ON b.bookingID = cb.bookingID
-- LEFT JOIN bookingpaymentstatus bps ON cb.paymentStatus = bps.paymentStatusID

LEFT JOIN custompackage cp
ON b.customPackageID = cp.customPackageID
LEFT JOIN foodpricing fp
ON cp.foodPricingPerHeadID = fp.pricingID
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
$totalCost = 0;
$downpayment = 0;
$discount = 0;
$totalPax = 0;
$kidsCount = 0;
$adultCount = 0;
$finalBill = 0;
$userBalance = 0;
$amountPaid = 0;
$additionalCharge = 0;
$serviceVenue = '';
$downpaymentNotes = [];
$paymentApprovalStatusName = '';
$foodList = [];
$partnerServiceList = [];


while ($row = $getBookingInfoResult->fetch_assoc()) {
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

if (!empty($rawStartDate) || $rawStartDate === $rawEndDate) {
$bookingDate = date('F d, Y', strtotime($rawStartDate));
} elseif (!empty($rawStartDate) && !empty($rawEndDate)) {
$bookingDate = $startDate . " to " . $endDate;
} else {
$bookingDate = 'Date not available';
}

$bookingCreationDate = !empty($row['createdAt']) ? date('F d, Y h:i A', strtotime($row['createdAt'])) : 'Not Stated';

$time = date("g:i A", strtotime($rawStartDate)) . " - " . date("g:i A", strtotime($rawEndDate));
$duration = $row['durationCount'] . " hours";

//IDs and Type
$customPackageID = $row['customPackageID'] ?? null;
$bookingType = $row['bookingType'] ?? null;
$confirmedBookingID = $row['confirmedBookingID'] ?? null;
$serviceType = $row['serviceType'] ?? null;
$serviceID = isset($row['serviceID']) ? $row['serviceID'] : '';

//Pax Details
$toddlerCount = (int) $row['toddlerCount'];
$kidCount = (int) $row['kidCount'];
$adultCount = (int) $row['adultCount'];
$guestCount = intval($row['guestCount']);


//Payment Details
$paymentMethod = $row['paymentMethod'];
$discount = (float) $row['discountAmount'] ?? 0;
$originalBill = (float) $row['originalBill'];
$downpayment = (float) $row['downpayment'];
$additionalCharge = (float) $row['additionalCharge'];


if (!empty($confirmedBookingID)) {
$paymentApprovalStatus = $row['paymentApprovalStatus'] ?? null;
$paymentStatus = $row['paymentStatus'] ?? null;
$finalBill = (float) $row['confirmedFinalBill'] ?? null;
$paymentDueDate = !empty($row['paymentDueDate']) ? date('F d, Y h:i A', strtotime($row['paymentDueDate'])) : 'Not
Stated';
$amountPaid = (float) $row['amountPaid'];
$userBalance = (float) $row['userBalance'];

if (!empty($paymentStatus) || !empty($paymentApprovalStatus)) {
$paymentStatuses = getPaymentStatus($conn, $paymentStatus);
$paymentStatusID = $paymentStatuses['paymentStatusID'];
$paymentStatusName = $paymentStatuses['paymentStatusName'];

$paymentApprovalStatuses = getStatuses($conn, $paymentApprovalStatus);
$paymentApprovalStatusID = $paymentApprovalStatuses['statusID'];
$paymentApprovalStatusName = $paymentApprovalStatuses['statusName'];
}
}

$bookingStatus = $row['bookingStatus'] ?? null;

$additionalServices = $row['addOns'] ?? 'None';
$downpayment = $row['downpayment'];
$bookingStatuses = getStatuses($conn, $bookingStatus);
$bookingStatusNameID = $bookingStatuses['statusID'];
$bookingStatusName = $bookingStatuses['statusName'];

$startDateObj = new DateTime($row['startDate']);
$startDateObj->modify('-1 day');

$raw_paymentDueDate = $row['paymentDueDate'] ?? $startDate;
$raw_downpaymentDueDate = $row['downpaymentDueDate'] ?? $startDateObj->format('F d, Y g:i A');
$paymentDueDate = date('F d, Y g:i A', strtotime($raw_paymentDueDate));
$downpaymentDueDate = date('F d, Y g:i A', strtotime($raw_downpaymentDueDate));

if ($amountPaid === $downpayment || $amountPaid > $downpayment) {
$dueDate = $paymentDueDate;
$buttonName = 'Check Payment';
} else {
$dueDate = $downpaymentDueDate;
$buttonName = 'Make a Down Payment';
}

$downpaymentImageData = $row['downpaymentImage'];
if (!empty($downpaymentImageData)) {
$downpaymentImage = "../../Assets/Images/PaymentProof/" . $downpaymentImageData;
} else {
$downpaymentImage = "../../Assets/Images/PaymentProof/defaultDownpayment.png";
}

$discount = $row['discountAmount'] ?? 0;
$userBalance = $row['userBalance'] ?? 0;
$amountPaid = $row['amountPaid'] ?? 0;
$finalBill = $row['confirmedFinalBill'] ?? 0;
$additionalCharge = $row['additionalCharge'] ?? 0;

$additionalReq = $row['additionalRequest'];
$addOns = $row['addOns'] ?? 'None';

if (!empty($customPackageID)) {
$foodItemID = isset($row['foodItemID']) ? $row['foodItemID'] : '';
$totalPax = intval($row['guestCount']) . ' people' ?? 1 . ' person';
$cardHeader = "Type of Event";
$eventType = $row['eventType'];

if (!empty($serviceID)) {
if ($serviceType === 'Resort') {
$serviceVenue = $row['RServiceName'] ?? 'none';
} elseif ($serviceType === 'Partnership') {
$category = $row['partnerTypeDescription'] ?? 'N/A';
$name = $row['PBName'] ?? '';

$partnerServiceList[$category][] = $name;
}
}
if (!empty($foodItemID)) {
$category = $row['foodCategory'];
$name = $row['foodName'];

$foodList[$category][] = $name;
}
} else {
$serviceID = $row['serviceID'];
$serviceType = $row['serviceType'];
$pax = $row['guestCount'];

$downpaymentNotes[] = 'Wait for the approval before paying the downpayment.';
$downpaymentNotes[] = 'Your booking is considered confirmed only after the downpayment is received and proof of payment
verified';
$downpaymentNotes[] = 'You can check the payment due date by clicking the "Make a Down Payment" button';

if ($bookingType === 'Resort') {
$downpaymentNotes[] = 'Required to pay for 1 cottage/room for reservation';
}

if ($bookingType === 'Hotel') {
$downpaymentNotes[] = 'Please pay for the down payment amount for the approval of your booking
withinseven (7) business days.';
}

if ($serviceType === 'Resort') {
$services[] = $row['RServiceName'];
$totalPax = ($adultCount > 0 ? "{$adultCount} Adults" : '') .
($kidsCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$kidsCount} Kids" : '') .
($toddlerCount > 0 ? (($adultCount > 0 || $kidsCount > 0) ? ' & ' : '') . "{$toddlerCount} toddlers" : '');
}

if ($serviceType === 'Entrance') {
$tourType = $row['tourType'];
$cardHeader = "Type of Tour";

if ($row['ERCategory'] === "Kids") {
$kidsCount = $row['guests'];
} elseif ($row['ERCategory'] === "Adult") {
$adultCount = $row['guests'];
}

$totalPax = ($adultCount > 0 ? "{$adultCount} Adults" : '') .
($kidsCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$kidsCount} Kids" : '') .
($toddlerCount > 0 ? (($adultCount > 0 || $kidsCount > 0) ? ' & ' : '') . "{$toddlerCount} toddlers" : '');
}
}

if ($bookingStatusName === 'Pending') {
$status = strtolower($bookingStatusName) ?? NUll;
$statusTitle = "Your reservation is pending for approval";
$statusSubtitle = 'Your request has been sent to the admin. Please wait for the approval of
your reservation.';
} elseif ($bookingStatusName === 'Rejected') {
$status = strtolower($bookingStatusName) ?? NUll;
$statusTitle = "Booking Rejected!";
$statusSubtitle = "We regret to inform you that your reservation has been rejected. Please contact us for more
details.";
} elseif ($bookingStatusName === 'Cancelled') {
$status = strtolower($bookingStatusName) ?? null;
$statusTitle = "Booking Cancelled";
$statusSubtitle = "You have cancelled your reservation. If this was a mistake or you wish to rebook, please contact
us.";
} elseif ($bookingStatusName === 'Expired') {
$status = strtolower($bookingStatusName) ?? null;
$statusTitle = "Expired Booking";
$statusSubtitle = "Sorry. The scheduled time for this booking has passed.";
} elseif ($bookingStatusName === 'Approved' && $paymentApprovalStatusName === 'Rejected') {
$status = strtolower($paymentApprovalStatusName) ?? null;
$statusTitle = "Payment Rejected";
$statusSubtitle = "Your reservation was approved, but the submitted payment was rejected. Please check the payment
details and try again, or contact the admin for assistance.";
} elseif ($bookingStatusName === 'Approved' && $paymentApprovalStatusName === 'Pending') {
$status = strtolower($bookingStatusName) ?? NUll;
$statusTitle = "Your reservation has been approved.";
if ($paymentMethod === 'GCash') {
$statusSubtitle = "Your reservation request has been approved by the admin. You may now proceed with the down payment
via GCash.";
} elseif ($paymentMethod === 'Cash') {
if ($bookingType === 'Resort') {
$statusSubtitle = "Your reservation has been approved by the admin. Please proceed on your scheduled swimming date and
complete the payment on that day.";
} else {
$statusSubtitle = "Your reservation request has been approved by the admin. You may now proceed to the resort to make
your downpayment.";
}
}
} elseif ($paymentApprovalStatusName === 'Approved' && $paymentStatusName === 'Partially Paid') {
$status = strtolower($bookingStatusName) ?? NUll;
$statusTitle = "Payment approved successfully.";
$statusSubtitle = "We have received and reviewed your payment. The service you booked is now reserved. Thank you!";
} elseif ($paymentApprovalStatusName === 'Approved' && $paymentStatusName === 'Fully Paid') {
$status = strtolower($bookingStatusName) ?? NUll;
$statusTitle = "Payment done successfully.";
$statusSubtitle = "Thank you! We have received your full payment. You may now enjoy your stay at the resort.";
} elseif ($bookingStatusName === 'Approved' && $paymentApprovalStatusName === 'Done' && $paymentStatusName === 'Fully
Paid') {
$statusTitle = "Booking Completed";
$status = strtolower($bookingStatusName) ?? NUll;
$statusSubtitle = "Thank you for staying with us! Your booking is fully paid and successfully completed. We hope you had
a wonderful time.";
}


if ($finalBill === 0 || !isset($finalBill)) {
$totalBill = $totalCost;
} else {
$totalBill = $finalBill;
}
$data[] = $row;
}

//Get the room or cottage
$cottageRoom = [];

foreach ($services as $service) {
if (stripos($service, 'cottage') !== false) {
$serviceVenue = "Cottage";
$cottageRoom[] = $service;
} elseif (stripos($service, 'room') !== false) {
$serviceVenue = "Room";
$cottageRoom[] = $service;
} elseif (stripos($service, 'umbrella') !== false) {
$serviceVenue = "Umbrella";
$cottageRoom[] = $service;
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
// echo '
<pre>';
        // print_r($partnerServiceList);
        // echo '</pre>';


//* FROM BOOKING APPROVAL
$conn->begin_transaction();
try {
$newTotalFoodPrice = 0.00;
error_log("Starting food price update loop. Count: " . count($foodPrices));

foreach ($foodIDs as $foodID => $name) {
error_log("Processing food item: $name with price $price");

$foodName = ucfirst($name);
$foodPrice = (float) $price;
$query = $conn->prepare("SELECT mi.foodPrice, mi.foodName, mi.foodItemID,
cpi.quantity, cpi.servicePrice,
cp.customPackageID, cp.customPackageTotalPrice
FROM `menuitem` mi
LEFT JOIN custompackageitem cpi ON mi.foodItemID = cpi.foodItemID
LEFT JOIN custompackage cp ON cpi.customPackageID = cp.customPackageID
WHERE `foodName` = ? AND cpi.customPackageID = ?");
$query->bind_param('si', $name, $customPackageID);

if (!$query->execute()) {
throw new Exception('Error in selecting a menu in the menuitem table for food = ' . $foodName);
}

$result = $query->get_result();
if ($result->num_rows === 0) {
throw new Exception('Error in fetching a menu in the menuitem table for food = ' . $foodName);
}

while ($data = $result->fetch_assoc()) {
$foodItemID = intval($data['foodItemID']);
$storedServicePrice = floatval($data['servicePrice']);
$storedQuantity = intval($data['quantity']);
$newTotalFoodPrice += $foodPrice;

if (abs($storedServicePrice - $foodPrice) > 0.01) {
$updateServicePrice = $conn->prepare("UPDATE `custompackageitem` SET `servicePrice`= ? WHERE foodItemID = ? AND
customPackageID = ?");
$updateServicePrice->bind_param("dii", $foodPrice, $foodItemID, $customPackageID);
if (!$updateServicePrice->execute()) {
throw new Exception("Error updating the service price for foodItemID " . $foodItemID);
}
}
}
} // end foreach

if (abs($newTotalFoodPrice - $foodPriceTotal) > 0.01) {
$bill = $newCustomPackageTotalPrice = $newTotalFoodPrice + $venuePrice;
$updateTotalPrice = $conn->prepare("UPDATE `custompackage` SET `customPackageTotalPrice`= ? WHERE `customPackageID`=
?");
$updateTotalPrice->bind_param('di', $newCustomPackageTotalPrice, $customPackageID);
if (!$updateTotalPrice->execute()) {
throw new Exception("Error updating the totalPrice for custom package " . $customPackageID);
}
}

error_log("Calculated total food price: $newTotalFoodPrice, Submitted: $foodPriceTotal");
$conn->commit();
} catch (Exception $e) {
$conn->rollback();
error_log("Error: " . $e->getMessage());
$_SESSION['bookingID'] = $bookingID;
header("Location: ../../Pages/Admin/viewBooking.php");
exit();
}

<!-- MODAL FROM viewBooking.php -->

<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectionModalLabel">Booking Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- // TODO -> Pakipalitan yung notes na they can change the final bill or the discount amount (pacheck grammary na lang) try nyo dark red -->
                <p>
                    Note: You can either change the total amount or apply a discount, not both.
                </p>
                <div class="input-container">
                    <label for="finalBill">Final Bill:</label>
                    <input type="text" class="form-control" placeholder="e.g. 100" name="finalBill" min="0"
                        value="₱<?= number_format($finalBill, 2) ?>" readonly>
                </div>
                <label>
                    <input type="radio" name="adjustOption" value="editBill" id="change-final-bill">
                    Edit Total Amount
                </label>

                <label>
                    <input type="radio" name="adjustOption" value="discount" id="offer-discount">
                    Enable discount
                </label>
                <!-- // TODO -> Palitan nyo label pag di madali intindihin -->
                <div class="input-container">
                    <label for="editedFinalBill">Enter Final Bill:</label>
                    <input type="number" class="form-control" placeholder="e.g. 100" id="editedFinalBill"
                        name="editedFinalBill" min="0" readonly>
                </div>
                <div class="input-container">
                    <label for="discountAmount">Enter discount amount:</label>
                    <input type="number" class="form-control" placeholder="e.g. 100" id="discountAmount"
                        name="discountAmount" min="0" readonly>
                </div>

                <label>
                    <input type="checkbox" name="applyAdditionalCharge" id="add-charge">
                    Enable Additional Charge
                </label>

                <div class="input-container">
                    <label for="additionalCharge">Additional Charge:</label>
                    <input type="number" class="form-control" placeholder="e.g. 100" id="additionalCharge"
                        name="additionalCharge" min="0" readonly>
                </div>

                <div class="input-container">
                    <label for="approvalNotes">Approval Notes</label>
                    <textarea rows="4" cols="50" class="form-control" name="approvalNotes" maxlength="50"
                        id="approvalNotes" placeholder=" Optional"></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- NOTIFICATION CODE FROM ROOMLIST.PHP -->
<div class="notification-container position-relative">
    <button type="button" class="btn position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
        <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
        <?php if (!empty($counter)): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= htmlspecialchars($counter) ?>
        </span>
        <?php endif; ?>
    </button>
</div>

<!-- Notification Modal -->
<?php include '../notificationModal.php' ?>
<!-- Room container -->


<!-- NOTIFICATION CODE FROM ROOMLIST.PHP -->

<!-- FROM VIEW PAYMENTS -->
<form action="reservationSummary.php" method="POST">
    <input type="hidden" name="bookingType" value="${booking.bookingType}">
    <input type="hidden" name="confirmedBookingID" value="${booking.confirmedBookingID}">
    <input type="hidden" name="bookingID" value="${booking.bookingID}">
    <input type="hidden" name="status" value="${booking.status}">
    <button type="submit" name="viewBooking" class="btn btn-info  viewBooking" data-label="View">View</button>
</form>

<!-- FROM VIEW PAYMENTS -->