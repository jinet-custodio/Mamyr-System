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
    $bookingID = intval($_POST['bookingID']);
    $_SESSION['bookingID'] = $bookingID;
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = intval($_SESSION['bookingID']);
}


if ($userRole == 3) {
    $admin = "Admin";
} else {
    $_SESSION['error'] = "Unauthorized Access!";
    session_destroy();
    header("Location: ../register.php");
    exit();
}

if ($admin === "Admin") {
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
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/viewPayments.css" />


</head>


<body>

    <?php
    $bookingID = $bookingID;
    $bookingStatus = 2;

    $payments = $conn->prepare("SELECT 
                    LPAD(cb.bookingID, 4, '0') AS formattedID,
                    cb.confirmedBookingID, cb.downpaymentImage, cb.discountAmount, cb.additionalCharge, cb.confirmedFinalBill,
                    cb.amountPaid, cb.userBalance, cb.paymentApprovalStatus as paymentApprovalStatusID, cb.paymentStatus as paymentStatusID, cb.paymentDueDate, cb.downpaymentDueDate,
                    b.bookingID, b.bookingType, b.customPackageID, b.addOns, b.paymentMethod, b.totalCost as originalBill, b.downpayment, b.bookingStatus as bookingStatusID, mi.foodName,
                    u.firstName, u.lastName, u.phoneNumber, u.userID AS customerID, u.userRole,
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
                WHERE cb.bookingID = ? AND b.bookingStatus = ?
        ");
    $payments->bind_param("ii", $bookingID, $bookingStatus);
    $payments->execute();
    $resultPayments = $payments->get_result();
    if ($resultPayments->num_rows > 0) {
        $services = [];
        $venuePrice;
        $pricePerHead = 0;
        $foodPriceTotal = 0;
        $venuePrice = 0;
        $partnerServices = [];
        while ($row = $resultPayments->fetch_assoc()) {
            //user info
            $customerID = (int) $row['customerID'];
            $guestName = ucfirst($row['firstName']) . " " . ucfirst($row['lastName']);
            $phoneNumber = $row['phoneNumber'] ?? '--';
            $userRoleID = $row['userRole'];

            //Payment Details
            $originalBill = floatval($row['originalBill']);
            $bookingType = $row['bookingType'];
            $formattedBookingID = $row['formattedID'];
            $finalBill = floatval($row['confirmedFinalBill']);
            $userBalance = floatval($row['userBalance']);
            $amountPaid = floatval($row['amountPaid']);
            $downpayment = floatval($row['downpayment']);
            $paymentMethod = $row['paymentMethod'] ?? 'Not Stated';
            $paymentStatusID = intval($row['paymentStatusID']);
            $discount = floatval($row['discountAmount']);
            $additionalCharge =  floatval($row['additionalCharge']);

            if ($paymentMethod === 'Cash') {
                $paymentMethod = $paymentMethod . ' - Onsite Payment';
            } else {
                $paymentMethod = $paymentMethod;
            }

            $finalBill = !empty($finalBill) ? $finalBill : $originalBill;

            // $rawStartDate = $row['startDate'];
            // $rawEndDate = $row['endDate'];

            // $startDate = date("M d, Y", strtotime($rawStartDate));
            // $endDate = date("M d, Y", strtotime($rawEndDate));

            // if ($startDate === $endDate) {
            //     $date = date("F d, Y", strtotime($rawStartDate));
            // } else {
            //     $date = $startDate . " - " . $endDate;
            // }

            // $time = date("g:i A", strtotime($rawStartDate)) . " - " . date("g:i A", strtotime($rawEndDate));
            // $duration = $row['durationCount'] . " hours";


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
                if ($serviceType === 'Resort') {
                    $services[] = $row['RServiceName'];
                }
                if ($serviceType === 'Entrance') {
                    $tourType = $row['tourType'] . " Swimming";
                }
            }

            $paymentStatus = getPaymentStatus($conn, $paymentStatusID);
        }
    }
    // var_dump($mimeType);
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
                    <?php foreach ($services as $service): ?>
                        <input type="hidden" name="services[]" value="<?= $service ?>">
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
                                value="<?= $paymentStatus['paymentStatusName'] ?>" readonly>
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
                            <input type="text" class="form-control" name="discountAmount" id="payment-form"
                                value="₱<?= number_format($discount, 2) ?>" readonly>
                        </div>
                        <div class="payment-input-container">
                            <label id="paymentLabel">Final Bill</label>
                            <input type="text" class="form-control" id="payment-form"
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
                            data-bs-target="#approveModal">Approve</button>

                        <button type="button" name="rejectBtn" class="btn btn-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    </div>

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
            <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveModalLabel">Payment Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="approveModalBody">
                            <div class="input-container">
                                <label for="finalBill">Total Amount</label>
                                <input type="text" class="form-control" id="approveModalForm" name="finalBill"
                                    value="₱<?= number_format($finalBill, 2) ?>" readonly>
                            </div>
                            <div class="input-container">
                                <label for="balance">Customer Balance</label>
                                <input type="text" class="form-control approveModalForm"
                                    value="₱<?= number_format($userBalance, 2) ?>" readonly>
                            </div>
                            <div class="input-container">
                                <label for="paymentAmount">Payment Amount</label>
                                <input type="text" class="form-control" id="paymentAmount" name="paymentAmount"
                                    style="background-color: #ffff;">
                            </div>

                            <!-- // TODO -> Pakipalitan yung notes na they can change the final bill or the discount amount (pacheck grammary na lang) try nyo dark red -->
                            <p>
                                Note: You can either change the total amount or apply a discount, not both.
                            </p>
                            <div class="radio-container">
                                <div class="radio">
                                    <input type="radio" id="applyDiscount" value="discount" name="radioOptions">
                                    <label for="applyDiscount">Apply Discount</label>
                                </div>
                                <div class="radio">
                                    <input type="radio" id="changeTotalAmount" value="editedBill" name="radioOptions">
                                    <label for="changeTotalAmount">Change Total Amount</label>
                                </div>
                            </div>

                            <div class="changes-container" id="changes-container"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="approvePaymentBtn"
                                id="approvePaymentBtn">Approve Booking</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- //* Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="input-container">
                                <label for="rejectionReason">Please specify the reason for rejection</label>
                                <input type="text" class="form-control" name="rejectionReason" id="rejectionReason">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger" name="rejectPaymentBtn"
                                id="rejectPaymentBtn">Reject Booking</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- //* Add Payment Modal -->
            <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModal"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPaymentModalLabel">Customer Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body" id="addPaymentModalBody">
                            <div class="input-container">
                                <label for="finalBill">Total Amount</label>
                                <input type="text" class="form-control" id="paymentModalForm" name="finalBill"
                                    value="₱<?= number_format($finalBill, 2) ?>" readonly>
                            </div>
                            <div class="input-container">
                                <label for="balance">Customer Balance</label>
                                <input type="text" class="form-control" id="paymentModalForm"
                                    value="₱<?= number_format($userBalance, 2) ?>" readonly>
                            </div>
                            <div class="input-container">
                                <label for="customerPayment">Payment Amount</label>
                                <input type="text" class="form-control" name="customerPayment" id="customerPayment"
                                    style="background-color: #ffff;">
                            </div>

                            <div class="radio-container">
                                <div class="radio">
                                    <input type="radio" id="offered-discount" value="discount" name="radioOptions">
                                    <label for="offered-discount">Apply Discount</label>
                                </div>
                                <div class="radio">
                                    <input type="radio" id="edit-amount" value="editedBill" name="radioOptions">
                                    <label for="edit-amount">Change Total Amount</label>
                                </div>
                            </div>

                            <div id="addPaymentModal-changes"></div>

                            <div class="checkbox-container">
                                <input type="checkbox" id="add-charge" name="addCharge">
                                <label for="add-charge">Additional Charge</label>
                            </div>
                            <div class="input-container" id="charge-container"></div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="submitPaymentBtn"
                                id="submitPaymentBtn">Submit</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </form>


    <!-- Bootstrap JS -->
    <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>

    <script>
        const paymentMethod = document.getElementById("paymentMethod").value;
        const paymentStatus = document.getElementById("paymentStatus").value;
        const ImageContainer = document.getElementById("downpayment-image-container");
        const downpaymentContainer = document.getElementById('downpaymentContainer');
        if (paymentMethod === 'Cash - Onsite Payment') {
            ImageContainer.style.display = "none";
            downpaymentContainer.style.display = "none";
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

    <!-- Applying discount or changing total amount and adding payment -->
    <script>
        const changeFinalBillRadio = document.getElementById('changeTotalAmount');
        const applyDiscountRadio = document.getElementById('applyDiscount');
        const editAmountRadio = document.getElementById('edit-amount');
        const offeredDiscountRadio = document.getElementById('offered-discount');
        const container = document.getElementById('changes-container');
        const div = document.getElementById('addPaymentModal-changes');
        const addCharge = document.getElementById('add-charge');
        const paymentModalBody = document.getElementById('addPaymentModalBody');

        function updateInputContainer(container, radio, labelText, inputName) {
            if (radio.checked) {
                container.innerHTML = '';

                const div = document.createElement('div');
                div.classList.add('input-container');

                const label = document.createElement('label');
                label.textContent = labelText;

                const input = document.createElement('input');
                input.name = inputName;
                input.placeholder = 'e.g. 1500';
                input.className = 'form-control';

                div.appendChild(label);
                div.appendChild(input);
                container.appendChild(div);
            }
        }

        changeFinalBillRadio.addEventListener('change', function() {
            updateInputContainer(container, changeFinalBillRadio, 'Enter new total bill', 'newTotalAmount');
        });

        applyDiscountRadio.addEventListener('change', function() {
            updateInputContainer(container, applyDiscountRadio, 'Enter discount amount', 'discountAmount');
        });

        editAmountRadio.addEventListener('change', function() {
            console.log(1);
            updateInputContainer(div, editAmountRadio, 'Enter new total amount', 'newTotalAmount');
        });

        offeredDiscountRadio.addEventListener('change', function() {
            console.log(2);
            updateInputContainer(div, offeredDiscountRadio, 'Enter discount amount', 'discountAmount');
        });


        addCharge.addEventListener('change', function() {
            const existingChargeContainer = document.getElementById('chargeContainer');

            if (addCharge.checked) {
                console.log(addCharge.value);
                if (!existingChargeContainer) {
                    const chargeContainer = document.createElement('div');
                    chargeContainer.classList.add('input-container');
                    chargeContainer.id = 'chargeContainer';

                    const label = document.createElement('label');
                    label.textContent = 'Enter Additional Charge:';

                    const input = document.createElement('input');
                    input.name = 'additionalCharge';
                    input.placeholder = 'e.g. 1500';
                    input.className = 'form-control';

                    chargeContainer.appendChild(label);
                    chargeContainer.appendChild(input);
                    paymentModalBody.appendChild(chargeContainer);
                }
            } else {
                if (existingChargeContainer) {
                    existingChargeContainer.remove();
                }
            }
        })
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
                const paymentAmount = document.getElementById('paymentAmount');
                const approveModal = document.getElementById('approveModal');
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

</body>

</html>