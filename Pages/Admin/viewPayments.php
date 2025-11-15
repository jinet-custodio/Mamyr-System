<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();
require '../../Function/Helpers/statusFunctions.php';
//for setting image paths in 'include' statements
$baseURL = '../..';

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
    $bookingID = intval($_POST['bookingID']);
    $_SESSION['bookingID'] = $bookingID;
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = intval($_SESSION['bookingID']);
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

if ($role === "Admin") {
    $getAdminName = $conn->prepare("SELECT firstName,middleInitial, lastName FROM user WHERE userID = ? AND userRole = ?");
    $getAdminName->bind_param("ii", $userID, $userRole);
    $getAdminName->execute();
    $getAdminNameResult = $getAdminName->get_result();
    if ($getAdminNameResult->num_rows > 0) {
        $data = $getAdminNameResult->fetch_assoc();
        $middleInitial = $data['middleInitial'] ?? '';
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $adminName = $firstName . " " . $middleInitial . " " . $lastName;
    }
} else {
    $_SESSION['error'] = "Unauthorized Access!";
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
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/viewPayments.css" />

</head>


<body>

    <?php
    $bookingID = $bookingID;
    // $bookingStatus = 2;
    // $reservedStatus = 3;
    // $doneStatus = 6;

    $payments = $conn->prepare("SELECT 
                    LPAD(cb.bookingID, 4, '0') AS formattedID, b.bookingCode,
                    cb.confirmedBookingID, cb.downpaymentImage, cb.discountAmount, cb.additionalCharge, cb.finalBill,
                    cb.amountPaid, cb.userBalance, cb.paymentApprovalStatus as paymentApprovalStatusID, 
                    cb.paymentStatus as paymentStatusID, p.amount, p.paymentID,  cb.paymentDueDate, cb.downpaymentDueDate,
                    b.bookingID, b.bookingType, b.customPackageID, b.addOns, b.paymentMethod, b.totalCost as originalBill, b.downpayment, b.bookingStatus as bookingStatusID, mi.foodName,
                    u.firstName, u.lastName, u.phoneNumber, u.userID AS customerID, u.userRole, u.email,
                    cp.totalFoodPrice, cp.venuePricing, cp.additionalServicePrice, cpi.foodItemID, 
                    s.serviceID, s.resortServiceID, s.partnershipServiceID, s.entranceRateID, s.serviceType,
                    ra.RServiceName,sp.price,
                    er.sessionType as tourType,
                    ps.PBName
                FROM confirmedbooking cb
                LEFT JOIN booking b ON cb.bookingID = b.bookingID
                LEFT JOIN user u ON b.userID = u.userID
                LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID
                LEFT JOIN menuitem mi ON cpi.foodItemID = mi.foodItemID
                LEFT JOIN servicepricing sp ON cp.foodPricingPerHeadID = sp.price
                LEFT JOIN service s ON bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID
                LEFT JOIN resortamenity ra ON s.resortServiceID = ra.resortServiceID
                LEFT JOIN entrancerate er ON s.entranceRateID = er.entranceRateID
                LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                WHERE cb.bookingID = ? 
        ");
    $payments->bind_param("i", $bookingID);
    $payments->execute();
    $resultPayments = $payments->get_result();
    if ($resultPayments->num_rows > 0) {
        $services = [];
        $venuePrice;
        $pricePerHead = 0;
        $foodPriceTotal = 0;
        $venuePrice = 0;
        $partnerServices = [];
        $serviceIDs = [];
        while ($row = $resultPayments->fetch_assoc()) {
            //user info
            $userEmail = $row['email'];
            $customerID = (int) $row['customerID'];
            $firstName = ucfirst($row['firstName']);
            $guestName = $firstName . " " . ucfirst($row['lastName']);
            $phoneNumber = $row['phoneNumber'] ?? '--';
            $userRoleID = $row['userRole'];


            //Payment Details
            $originalBill = floatval($row['originalBill']);
            $bookingType = $row['bookingType'];
            $formattedBookingID = $row['formattedID'];
            $finalBill = floatval($row['finalBill']);
            $userBalance = floatval($row['userBalance']);
            $amountPaid = floatval($row['amountPaid']);
            $downpayment = floatval($row['downpayment']);
            $paymentMethod = $row['paymentMethod'] ?? 'Not Stated';
            $paymentStatusID = intval($row['paymentStatusID'] ?? 1);
            $discount = floatval($row['discountAmount']);
            $additionalCharge =  floatval($row['additionalCharge']);

            if ($paymentMethod === 'Cash') {
                $paymentMethod = $paymentMethod . ' - Onsite Payment';
            } else {
                $paymentMethod = $paymentMethod;
            }

            $finalBill = !empty($finalBill) ? $finalBill : $originalBill;
            //Downpayment Image
            $imageData = $row['downpaymentImage'] ?? null;

            if ($imageData) {
                $downpaymentImage = '../../Assets/Images/PaymentProof/' . $imageData;
            } else {
                $downpaymentImage = '../../Assets/Images/PaymentProof/defaultDownpayment.png';
            }


            //services IDs For Receipt
            $serviceID = (int) $row['serviceID'];
            $customPackageID = (int) $row['customPackageID'];
            $foodItemID = (int) $row['foodItemID'];

            $serviceType = $row['serviceType'];
            if (!empty($customPackageID)) {
                $eventType = $row['eventType'] ?? null;
                $foodItemID = isset($row['foodItemID']) ? $row['foodItemID'] : null;
                $additionalServicePrice = floatval($row['additionalServicePrice']);

                if (!empty($serviceID)) {
                    $serviceIDs[] = $row['serviceID'];
                    if ($serviceType === 'Resort') {
                        $services[] = $row['RServiceName'];
                        $venuePrice = $row['venuePricing'] ?? 0;
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
                    $services[]  = 'Catering with drinks & dessert';
                    $pricePerHead = (int) $row['price'];
                    $foodPriceTotal = floatval($row['totalFoodPrice']);
                }
            } else {
                $serviceIDs[] = $row['serviceID'];
                if ($serviceType === 'Resort') {
                    $services[] = $row['RServiceName'];
                }
                if ($serviceType === 'Entrance') {
                    $tourType = $row['tourType'] . " Swimming";
                }
            }

            $paymentID = $row['paymentID'];
            $customerAmountPaid = 0;
            if (!empty($paymentID)) {
                $customerAmountPaid = $row['amount'];
            }

            $paymentStatus = getPaymentStatus($conn, $paymentStatusID);
            $paymentStatusName = $paymentStatus['paymentStatusName'];
        }
    }
    // var_dump($paymentStatusID);
    ?>
    <form action="" method="POST" id="form">

        <div class="back-button">
            <a href="transaction.php" class="back-btn"><img src="../../Assets/Images/Icon/arrowBtnBlack.png"
                    alt="Go back"></a>
        </div>
        <main>
            <section style="background-color: #ffff;">
                <div class="bookingInfo">
                    <!-- <div class="card"> -->
                    <input type="hidden" name="customerID" value="<?= $customerID ?>">
                    <input type="hidden" name="userRoleID" value="<?= $userRoleID ?>">
                    <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                    <input type="hidden" name="firstName" value="<?= $firstName ?>">
                    <input type="hidden" name="email" value="<?= $userEmail ?>">
                    <?php foreach ($serviceIDs as $id): ?>
                        <input type="hidden" name="servicesIDs[]" value="<?= $id ?>">
                    <?php endforeach; ?>
                    <?php foreach ($services as $name): ?>
                        <input type="hidden" name="services[]" value="<?= $name ?>">
                    <?php endforeach; ?>
                    <div class="firstRow">
                        <div class="input-container">
                            <label for="formattedBookingID">Booking ID</label>
                            <input type="text" class="form-control" id="gridForm" name="formattedBookingID"
                                value="<?= $formattedBookingID ?>" readonly>
                        </div>

                        <div class="input-container">
                            <label for="Name">Guest Name:</label>
                            <input type="text" class="form-control" id="gridForm"
                                value="<?= htmlspecialchars($guestName) ?>" name="guestName" readonly>
                        </div>
                        <div class="input-container">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="text" class="form-control" id="gridForm" name="phoneNumber"
                                value="<?= htmlspecialchars($phoneNumber) ?>" readonly>
                        </div>

                        <div class="input-container">
                            <label for="bookingType">Booking Type</label>
                            <input type="text" class="form-control" id="gridForm" name="bookingType"
                                value="<?= $bookingType ?> Booking " readonly>
                        </div>
                    </div>
                </div>
                <div class="card mx-auto" style="width: 100%;">

                    <h4 class="cardTitle text-center mt-4">Payment Information</h4>

                    <div class=" card-body">
                        <div class="payment-input-container">
                            <label for="paymentMethod paymentLabel">Payment Method</label>
                            <input type="text" class="form-control" name="paymentMethod" id="paymentMethod"
                                value="<?= $paymentMethod ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label for="paymentStatus" id="paymentLabel">Payment Status</label>
                            <input type="text" class="form-control" name="paymentStatus" id="paymentStatus"
                                value="<?= $paymentStatusName ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label for="amountPaid" id="paymentLabel">Amount Paid </label>
                            <input type="text" class="form-control" id="payment-form" name="amountPaid"
                                value="₱<?= number_format($amountPaid, 2) ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label for="balance" id="paymentLabel">Balance</label>
                            <input type="text" class="form-control" id="payment-form"
                                value="₱<?= number_format($userBalance, 2) ?>" readonly>
                        </div>
                        <?php if ($bookingType === 'Event') { ?>
                            <div class="payment-input-container" id="payment-info">
                                <label for="venuePrice" id="paymentLabel" class="mt-2">Venue Price</label>
                                <input type="text" class="form-control inputDetail w-50" name="venuePrice" id="venuePrice"
                                    value="₱<?= number_format($venuePrice, 2) ?>" readonly>
                            </div>
                            <div class="payment-input-container" id="payment-info">
                                <label for="pricePerHead" id="paymentLabel" class="mt-2">Price Per Head</label>
                                <input type="text" class="form-control inputDetail w-50" name="pricePerHead"
                                    id="pricePerHead" value="₱<?= number_format($pricePerHead, 2) ?>" readonly>
                            </div>
                            <div class="payment-input-container" id="payment-info">
                                <label for="foodPriceTotal" id="paymentLabel" class="mt-2">Total Food Price</label>
                                <input type="text" class="form-control inputDetail w-50" name="foodPriceTotal"
                                    id="foodPriceTotal" value="₱<?= number_format($foodPriceTotal, 2) ?>" readonly>
                            </div>
                            <div class="payment-input-container" id="payment-info">
                                <label for="additionalServicePrice" id="paymentLabel" class="mt-2">Additional Services
                                    Price</label>
                                <input type="text" class="form-control  inputDetail w-50" name="additionalServicePrice"
                                    id="additionalServicePrice" value="₱<?= number_format($additionalServicePrice, 2) ?>"
                                    readonly>
                            </div>

                        <?php } ?>
                        <div class="payment-input-container" id="payment-info">
                            <label for="additionalCharge" id="paymentLabel" class="mt-2">Additional Charge</label>
                            <input type="text" class="form-control inputDetail w-50"
                                value="₱<?= number_format($additionalCharge, 2) ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label for="originalBill" id="paymentLabel">Original Bill</label>
                            <input type="text" class="form-control" id="payment-form" name="originalBill"
                                value="₱<?= number_format($originalBill, 2) ?>" readonly>
                        </div>
                        <div class="payment-input-container" id="downpaymentContainer">
                            <label for="downpayment" id="paymentLabel">Downpayment (30%)</label>
                            <input type="text" class="form-control" id="payment-form" name="downpayment"
                                id="downpayment" value="₱<?= number_format($downpayment, 2) ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label for="discountAmount" id="paymentLabel">Discount</label>
                            <input type="text" class="form-control" id="payment-form"
                                value="₱<?= number_format($discount, 2) ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label id="paymentLabel">Final Bill</label>
                            <input type="text" class="form-control" id="payment-form" name="finalBill"
                                value="₱<?= number_format($finalBill, 2) ?>" readonly>
                        </div>
                    </div>
                </div>


                <div class="button-container">
                    <button type="submit" name="viewBooking" class="btn btn-info w-100" id="viewBookingBtn">View Booking
                        Details</button>
                    <button type="button" name="addPayment" id="addPayment" class="btn btn-success w-100"
                        data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>

                    <div class="form-button" id="form-button">
                        <button type="button" name="approveBtn" class="btn btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#finalizedModal">Approve</button>

                        <button type="button" name="rejectBtn" class="btn btn-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    </div>
                    <input type="hidden" name="button" value="payment">
                    <input type="hidden" name="totalCost" value="<?= $finalBill ?>"> <!-- info for receipt -->
                    <input type="hidden" name="bookingType" value="<?= $bookingType ?>"> <!-- info for receipt -->
                    <input type="hidden" name="adminName" value="<?= $adminName ?>"> <!-- info for receipt -->
                    <button type="submit" name="downloadReceiptBtn" id="genReceipt"
                        class="btn btn-primary w-100 mt-4">Generate
                        Receipt</button>
                </div>

            </section>

            <section id="downpaymentImageSection">
                <div class="image-container" id="downpayment-image-container">
                    <?php if ($downpaymentImage !== 'None'): ?>
                        <img src="<?= $downpaymentImage ?>" alt="Receipt Image" class="preview-image">
                        <!-- <div class="zoom-overlay">
                            <img src="<?= $downpaymentImage ?>" alt="Zoomed Image">
                        </div> -->
                    <?php else: ?>
                        <img src="../../Assets/Images/defaultDownpayment.png" alt="Payment Icon"
                            class="defaultDownpaymentImage">
                        <p class="text-center">Customer has not uploaded the receipt yet</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <div class="modals-container">
            <!-- //* Approve Modal -->
            <div class="modal fade" id="finalizedModal" tabindex="-1" aria-labelledby="finalizedModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="finalizedModalLabel">Payment Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="approveModalBody">
                            <div class="amount-balance">
                                <div class="input-container">
                                    <label for="finalBill">Total Amount</label>
                                    <input type="text" class="form-control" id="approved-finalBill"
                                        value="<?= $finalBill ?>" readonly>
                                </div>
                                <div class="input-container">
                                    <label for="balance">Customer Balance</label>
                                    <input type="text" class="form-control approveModalForm"
                                        value="<?= $userBalance  ?>" readonly>
                                </div>
                            </div>

                            <input type="hidden" name="paymentID" value="<?= $paymentID ?? '' ?>">

                            <?php if (strtolower($paymentMethod) === 'gcash') { ?>
                                <div class="input-container">
                                    <label for="customerPaymentMade">Customer Entered Payment:</label>
                                    <input type="text" class="form-control" id="customerPaymentMade" name="customerPaymentMade" value="<?= $customerAmountPaid ?>" readonly>
                                </div>
                                <div class="note mt-3">
                                    <p class="note text-center mb-0">Is the entered amount the same as the amount on the receipt? </p>
                                    <div class="d-flex mb-2 mx-auto" style="width: 50%; height:10%;">
                                        <button type="button" class="btn btn-primary w-50 me-2" id="sameAmount">Yes</button>
                                        <button type="button" class="btn btn-secondary w-50" id="notSame">No</button>
                                    </div>
                                    <div class="input-container mt-2" style="display: none;" id="paymentAmountContainer">
                                        <label for="paymentAmount">Payment Amount</label>
                                        <input type="text" class="form-control" id="paymentAmount" name="paymentAmount" placeholder="5000"
                                            style="background-color: #ffff;">
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="input-container mt-2" id="paymentAmountContainer">
                                    <label for="paymentAmount">Payment Amount</label>
                                    <input type="text" class="form-control" id="cash-paymentAmount" name="paymentAmount" placeholder="5000"
                                        style="background-color: #ffff;">
                                </div>
                            <?php } ?>


                            <div class="discount-container mt-3">
                                <div class="d-flex mb-2 align-items-center">
                                    <p class="fw-bold mb-0">Would you like to give a discount?</p>
                                    <div class="discount-button-container d-inline-flex mx-3">
                                        <button type="button" class="btn btn-primary me-2 fs-6" id="addDiscount">Yes</button>
                                        <button type="button" class="btn btn-secondary w-50 fs-6" id="noDiscount" style="display:none;">No</button>
                                    </div>
                                </div>

                                <div id="add-discount-container" style="display:none;">
                                    <label for="discountAmount" class="form-label">Discount Amount (₱)</label>
                                    <input type="text" class="form-control" id="discountAmount" name="discountAmount" placeholder="Enter discount amount">
                                </div>
                            </div>

                            <!-- Summary Section -->
                            <div id="summaryContainer">
                                <h6 class="fw-bold">Summary</h6>
                                <p>Customer Payment: ₱<span id="summaryCustomerPayment">0.00</span></p>
                                <p>Discount: ₱<span id="summaryDiscount">0.00</span></p>
                                <hr>
                                <p><strong>Total Balance: ₱<span id="summaryBalance">0.00</span></strong></p>
                                <input type="hidden" id="balance" name="balance" value="">
                            </div>

                        </div>

                        <!-- Footer Section -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approvalModal">Next</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--//* Approval Modal -->
            <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p class="approvalModal-p">You are about to approve a payment. Please review the details carefully. Once you approve, the payment will be finalized and cannot be undone. After approval, the reservation will be secured.</p>
                            <p class="approvalModal-p text-center"><strong>Do you want to approve this payment?</strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Close</button>
                            <button type="submit" class="btn btn-primary loaderTrigger" name="approvePaymentBtn" id="approvePaymentBtn">Approve</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- //* Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h6 class="reject-label fw-bold">Select a Reason for Rejection</h6>
                            <div class="form-group mt-4">
                                <select class="form-select" id="select-reject" aria-label="rejection-reason" name="rejection-reason"
                                    onchange="otherReason()">
                                    <option value="" disabled selected>Select a reason</option>
                                    <?php
                                    $reason = 'PaymentRejection';
                                    $getPaymentReason = $conn->prepare("SELECT `reasonID`, `reasonDescription` FROM `reason` WHERE `category` = ?");
                                    $getPaymentReason->bind_param('s', $reason);
                                    $getPaymentReason->execute();
                                    $result = $getPaymentReason->get_result();
                                    if ($result->num_rows === 0) {
                                    ?>
                                        <option value="other">Other (Please specify)</option>
                                    <?php
                                    }

                                    while ($row = $result->fetch_assoc()) {
                                    ?>
                                        <option value="<?= $row['reasonID'] ?>"><?= htmlspecialchars($row['reasonDescription']) ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group mt-4" id="otherInputGroup" style="display: none;">
                                <h6 class="otherReason-label fw-bold">Please Specify</h6>
                                <input type="text" class="form-control" id="rejectReason-textBox" name="rejection-entered-reason"
                                    placeholder="Enter your reason here....">
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-danger" name="rejectPaymentBtn"
                                id="rejectPaymentBtn">Reject Booking</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- //* Add Payment Modal -->
            <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModal" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPaymentModalLabel">Customer Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body" id="addPaymentModalBody">
                            <div class="display-container d-flex gap-3 justify-content-center">
                                <div class="input-container">
                                    <label for="finalBill">Total Amount</label>
                                    <input type="text" class="form-control" id="add-payment-finalBill"
                                        value="<?= $finalBill ?>" readonly>
                                </div>
                                <div class="input-container">
                                    <label for="balance">Customer Balance</label>
                                    <input type="text" class="form-control" id="addModal-customer-balance"
                                        value="<?= $userBalance ?>" readonly>
                                </div>
                            </div>

                            <div class="input-container">
                                <label for="customerPayment">Payment Amount</label>
                                <input type="text" class="form-control" name="customerPayment" id="customerPayment"
                                    style="background-color: #ffff;">
                            </div>

                            <div class="additionalCharge-container">
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
                                            <input class="form-check-input" type="checkbox" id="massage"
                                                data-input="massage-input" onchange="toggleAdditionalInput()">
                                            <label for="massage">Massage Chair</label>
                                            <!-- Additional Input for Quality and Charge -->
                                            <div class="additional-input  gap-1" id="massage-input" style="display: none;">
                                                <div class="form-floating mb-1">
                                                    <input type="number" class="form-control" id="massage-quantity" name="additionalCharges[massage][quantity]" data-role="quantity">
                                                    <label for="massage-quantity">Quantity</label>
                                                </div>
                                                <div class="form-floating mb-1">
                                                    <input type="number" class="form-control" id="massage-amount" name="additionalCharges[massage][amount]" data-role="amount">
                                                    <label for="massage-amount">Amount</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="videoke"
                                                data-input="videoke-input" onchange="toggleAdditionalInput()">
                                            <label for="videoke">Videoke</label>
                                            <!-- Additional Input for Quality and Charge -->
                                            <div class="additional-input  gap-1" id="videoke-input" style="display: none;">
                                                <div class="form-floating mb-1">
                                                    <input type="number" class="form-control" id="videoke-quantity" name="additionalCharges[videoke][quantity]" data-role="quantity">
                                                    <label for="videoke-quantity">Quantity</label>
                                                </div>
                                                <div class="form-floating mb-1">
                                                    <input type="number" class="form-control" id="videoke-amount" name="additionalCharges[videoke][amount]" data-role="amount">
                                                    <label for="videoke-amount">Amount</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="billiard"
                                                data-input="billiard-input" onchange="toggleAdditionalInput()">
                                            <label for="billiard">Billiard</label>
                                            <!-- Additional Input for Quality and Charge -->
                                            <div class="additional-input  gap-1" id="billiard-input" style="display: none;">
                                                <div class="form-floating mb-1">
                                                    <input type="number" class="form-control" id="billiard-quantity" name="additionalCharges[billiard][quantity]" data-role="quantity">
                                                    <label for="billiard-quantity">Quantity</label>
                                                </div>
                                                <div class="form-floating mb-1">
                                                    <input type="number" class="form-control" id="billiard-amount" name="additionalCharges[billiard][amount]" data-role="amount">
                                                    <label for="billiard-amount">Amount</label>
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

                                <p>Customer Payment: ₱<span id="summary-add-payment">0.00</span></p>
                                <div id="additionalSummary" class="mt-2">
                                    <h6 class="fw-semibold">Additional Charges</h6>
                                    <ul class="list-group mb-1" id="additional-charges-list">
                                    </ul>
                                    <p>Total Additional Charges: ₱<span id="total-additional-charges">0.00</span></p>
                                </div>

                                <hr>
                                <p><strong>Total Balance: ₱<span id="summary-balance">0.00</span></strong></p>
                                <p><strong>Final Bill: ₱<span id="summary-total-amount">0.00</span></strong></p>
                                <input type="hidden" id="new-balance" name="new-balance" value="">
                                <input type="hidden" id="new-bill" name="new-bill" value="">
                                <input type="hidden" id="additional-charge" name="additional-charge" value="">
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary" name="submitPaymentBtn"
                                id="submitPaymentBtn">Submit</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </form>


    <!-- Bootstrap JS -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
        const paymentMethod = document.getElementById("paymentMethod").value;
        const paymentStatus = document.getElementById("paymentStatus").value;
        const ImageContainer = document.getElementById("downpayment-image-container");
        // const downpaymentContainer = document.getElementById('downpaymentContainer');
        if (paymentMethod === 'Cash - Onsite Payment') {
            ImageContainer.style.display = "none";
            // downpaymentContainer.style.display = "none";
        }
        if (paymentStatus === 'Fully Paid') {
            document.querySelector("#form-button").style.display = "none";
            document.getElementById("addPayment").style.display = "none";
            document.getElementById("downpaymentImageSection").style.display = "none";
        } else if (paymentStatus === 'Partially Paid') {
            document.getElementById("addPayment").style.display = "block";
            document.querySelector("#form-button").style.display = "none";
        } else if (paymentStatus === 'No Payment' || paymentStatus === 'Payment Sent') {
            document.getElementById("addPayment").style.display = "none";
        }
    </script>

    <!-- Form action  -->
    <script>
        const form = document.getElementById('form');

        document.getElementById('viewBookingBtn').addEventListener('click', () => {
            form.action = 'viewBooking.php';
        });

        document.getElementById('approvePaymentBtn').addEventListener('click', () => {
            form.action = '../../Function/Admin/paymentApproval.php';
        });

        document.getElementById('rejectPaymentBtn').addEventListener('click', () => {
            form.action = '../../Function/Admin/paymentApproval.php';
        });

        document.getElementById('submitPaymentBtn').addEventListener('click', () => {
            form.action = '../../Function/Admin/paymentApproval.php';
        })

        document.getElementById('genReceipt').addEventListener('click', () => {
            form.setAttribute('target', '_blank');
            form.action = '../../Function/receiptPDF.php';
        })
    </script>
    <!-- For Finalized Modal then approve -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const approveModalForm = document.querySelectorAll('#finalizedModal .form-control');
            const discountContainer = document.getElementById('add-discount-container');
            const discountInput = document.getElementById('discountAmount');
            const noDiscountBtn = document.getElementById('noDiscount');
            const paymentAmount = document.getElementById('paymentAmount');
            const cashPaymentAmount = document.getElementById('cash-paymentAmount');
            const enteredPayment = document.getElementById('customerPaymentMade');
            const finalBill = document.getElementById('approved-finalBill');

            const balanceInput = document.getElementById('balance');

            const paymentMethod = document.getElementById("paymentMethod");


            document.getElementById('addDiscount').addEventListener('click', () => {
                discountContainer.style.display = 'block';
                discountInput.style.border = '1px solid red';
                discountInput.style.backgroundColor = 'rgba(255, 255, 255, 1)'
                noDiscountBtn.style.display = 'block';

                discountInput.addEventListener('input', () => {
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
                const paymentAmountValue = parseFloat(paymentAmount?.value || 0);
                const enteredPaymentValue = parseFloat(enteredPayment?.value || 0);
                const discountValue = parseFloat(discountInput?.value || 0);
                const approvalTotalAmountValue = parseFloat(finalBill?.value || 0);
                const cashPaymentAmountValue = parseFloat(cashPaymentAmount?.value || 0);

                let totalBalance = 0;
                let customerPayment = 0;
                if (paymentMethod.value === 'GCash') {
                    if (paymentAmountValue === 0) {
                        totalBalance = approvalTotalAmountValue - enteredPaymentValue - discountValue;
                        customerPayment = enteredPaymentValue;
                    } else {
                        totalBalance = approvalTotalAmountValue - paymentAmountValue - discountValue;
                        customerPayment = paymentAmountValue;
                    }
                } else {
                    totalBalance = approvalTotalAmountValue - cashPaymentAmountValue - discountValue;
                    customerPayment = cashPaymentAmountValue;
                }

                balanceInput.value = totalBalance;
                document.getElementById('summaryBalance').textContent = totalBalance.toFixed(2);
                document.getElementById('summaryCustomerPayment').textContent = customerPayment.toFixed(2);
                document.getElementById('summaryDiscount').textContent = discountValue.toFixed(2);
            }


            const inputs = [
                paymentAmount,
                discountInput,
                cashPaymentAmount
            ].filter(Boolean);


            inputs.forEach(input => {
                input.addEventListener('input', updateSummary);
            });

            updateSummary();

            approveModalForm.forEach(formControl => {
                formControl.addEventListener('keypress', function(e) {
                    if (/[0-9]/.test(e.key)) return;

                    if (e.key === '.' && !formControl.value.includes('.')) return;

                    e.preventDefault();
                });
            });

            if (paymentMethod.value === 'GCash') {
                document.getElementById('notSame').addEventListener('click', () => {
                    document.getElementById('paymentAmountContainer').style.display = 'block';
                });

                document.getElementById('sameAmount').addEventListener('click', () => {
                    document.getElementById('paymentAmountContainer').style.display = 'none';
                    paymentAmount.value = '';
                    updateSummary();
                });
            }
        });
    </script>
    <!-- For Add Payment Modal -->
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


        const finalBill = document.getElementById('add-payment-finalBill');
        const customerBalance = document.getElementById('addModal-customer-balance');
        const customerPayment = document.getElementById('customerPayment');
        const finalBillInput = document.getElementById('new-bill');
        const balanceInput = document.getElementById('new-balance');
        const additionalChargeInput = document.getElementById('additional-charge');
        const chargesList = document.getElementById("additional-charges-list");
        const additionalChargeContainer = document.querySelectorAll('.additionalCharge-container .form-control');
        const data = [];

        additionalChargeContainer.forEach(form => {
            form.addEventListener('change', () => {
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
            const customerBalanceValue = parseFloat(customerBalance.value) || 0;
            const customerPaymentValue = parseFloat(customerPayment.value) || 0;
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


            const totalBill = finalBillValue + additionalChargesTotal;
            const totalBalance = (customerBalanceValue + additionalChargesTotal) - customerPaymentValue;
            //Summary display
            document.getElementById('summary-add-payment').textContent = customerPaymentValue.toFixed(2);
            document.getElementById('total-additional-charges').textContent = additionalChargesTotal.toFixed(2);
            document.getElementById('summary-balance').textContent = totalBalance.toFixed(2);
            document.getElementById('summary-total-amount').textContent = totalBill.toFixed(2);

            finalBillInput.value = totalBill;
            balanceInput.value = totalBalance;
            additionalChargeInput.value = additionalChargesTotal;
        };

        document.addEventListener("DOMContentLoaded", function() {
            customerPayment.addEventListener('input', updateAddPaymentSummary);

            const checkboxGroups = document.querySelectorAll('.checkbox-group .form-control');
            checkboxGroups.forEach((input) => {
                input.disabled = true;
            })


            updateAddPaymentSummary();
        });
    </script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');
        if (paramValue === "paymentFieldEmpty") {
            Swal.fire({
                title: "Oops!",
                text: "Please enter the payment amount.",
                icon: "warning",
                confirmButtonText: "Okay",
            }).then(() => {
                const paymentAmount = document.getElementById('cash-paymentAmount');
                const approveModal = document.getElementById('approvalModal');
                const modal = new bootstrap.Modal(approveModal);

                paymentAmount.style.border = '1px solid red';
                modal.show();
            })
        } else if (paramValue === 'addPaymentFieldEmpty') {
            Swal.fire({
                title: "Oops!",
                text: "Please enter the customer payment.",
                icon: "warning",
                confirmButtonText: "Okay",
            }).then(() => {
                const customerPayment = document.getElementById('customerPayment');
                const addPaymentModal = document.getElementById('addPaymentModal');
                const modal = new bootstrap.Modal(addPaymentModal);

                customerPayment.style.border = '1px solid red';
                modal.show();
            })
        } else if (paramValue === "reasonFieldEmpty") {
            Swal.fire({
                title: "Oops!",
                text: "Please provide a reason for rejection.",
                icon: "warning",
                confirmButtonText: "Okay",
            }).then(() => {
                const rejectModal = document.getElementById('rejectModal');
                const rejectionReason = document.getElementById('rejectionReason');
                const modal = new bootstrap.Modal(rejectModal);
                modal.show();
                rejectionReason.style.border = '1px solid red';
            });
        }


        if (paramValue) {
            const url = new URL(window.location.href);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <script>
        function otherReason() {
            var selectBox = document.getElementById("select-reject");
            var otherInputGroup = document.getElementById("otherInputGroup");
            // console.log(selectBox.textContent);
            // Show or hide the text box when "Other (Please specify)" is selected
            if (selectBox.value === "other" || selectBox.value == 23) {
                otherInputGroup.style.display = "block"; // Show the text box
            } else {
                otherInputGroup.style.display = "none"; // Hide the text box
            }
        }
    </script>
    <?php include '../Customer/loader.php'; ?>
</body>

</html>