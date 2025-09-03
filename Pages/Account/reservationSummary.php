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
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_SESSION['bookingID']);
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
            <a href="bookingHistory.php"><img src="../../Assets/Images/Icon/arrow.png" alt="Back Button"
                    class="backButton"></a>
        </div>

        <div class="statusContainer">
            <!-- Get user data -->
            <?php
            $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
            $confirmedBookingID = (int) $_POST['confirmedBookingID'];
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
                $name = ucfirst($firstName) . " " . ucfirst($clientInfo['middleInitial']) . " "  . ucfirst($clientInfo['lastName']);
            }
            ?>

            <!-- Get booking info -->
            <?php
            $getBookingInfo = $conn->prepare("SELECT 
                                
                                b.*, 
                                cb.confirmedBookingID,
                                cb.amountPaid, 
                                cb.confirmedFinalBill, 
                                cb.userBalance, 
                                cb.paymentApprovalStatus, 
                                cb.paymentDueDate, cb.paymentStatus,
                                cb.discountAmount, cb.downpaymentImage,
                                cb.downpaymentDueDate, 
                                bs.*,
                                s.*, s.serviceType,
                                er.sessionType AS tourType, er.ERCategory, er.ERprice,
                                ra.RServiceName, ra.RSprice, rsc.categoryName AS serviceCategory   
                                    
                                FROM booking b
                                LEFT JOIN confirmedBooking cb ON b.bookingID = cb.bookingID 

                                LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                                LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID

                                LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                                LEFT JOIN service s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)

                                LEFT JOIN resortamenity ra ON s.resortServiceID = ra.resortServiceID
                                LEFT JOIN resortservicescategory rsc ON rsc.categoryID = ra.RScategoryID

                                LEFT JOIN entrancerate er ON s.entranceRateID = er.entranceRateID

                                LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
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
                $additionalCharge = 0;
                $serviceVenue = '';
                $downpaymentNotes = [];
                $paymentApprovalStatusName = '';

                while ($row = $getBookingInfoResult->fetch_assoc()) {

                    // echo '<pre>';
                    // print_r($row);
                    // echo '</pre>';

                    $customPackageID = $row['customPackageID'];
                    $bookingType = $row['bookingType'];
                    $arrivalTime = date('H:i A', strtotime($row['arrivalTime'])) ?? 'Not Stated';
                    $startDate = date('M. d, Y', strtotime($row['startDate']));
                    $endDate = date('M. d, Y', strtotime($row['endDate']));

                    if ($startDate === $endDate) {
                        $bookingDate = date('F d, Y', strtotime($row['startDate']));
                    } else {
                        $bookingDate = $startDate . "<br>" . $endDate;
                    }

                    $bookingCreationDate = date('F d, Y H:i A', strtotime($row['createdAt'])); //Booking

                    $time = date("g:i A", strtotime($row['startDate'])) . " - " . date("g:i A", strtotime($row['endDate'])); //Booking
                    $duration = $row['durationCount'] . " hours"; //Booking

                    $bookingStatus = $row['bookingStatus']; //Booking
                    $paymentApprovalStatus = $row['paymentApprovalStatus'] ?? ''; //Confirmed Booking
                    $paymentStatus = $row['paymentStatus'] ?? ''; //Confirmed Booking
                    $additionalServices = $row['addOns'] ?? 'None'; //Booking
                    $paymentMethod = $row['paymentMethod']; //Booking
                    $totalCost = $row['totalCost']; //Booking
                    $downpayment = $row['downpayment']; //Booking

                    if ($paymentStatus !== '' || $paymentApprovalStatus !== '') {
                        $paymentStatuses = getPaymentStatus($conn, $paymentStatus);
                        $paymentStatusID = $paymentStatuses['paymentStatusID'];
                        $paymentStatusName = $paymentStatuses['paymentStatusName'];

                        $paymentApprovalStatuses = getStatuses($conn, $paymentApprovalStatus);
                        $paymentApprovalStatusID = $paymentApprovalStatuses['statusID'];
                        $paymentApprovalStatusName = $paymentApprovalStatuses['statusName'];
                    }

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
                        $dueDate =  $paymentDueDate;
                        $buttonName = 'Check Payment';
                    } else {
                        $dueDate =  $downpaymentDueDate;
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
                    $additionalCharge = $row['additionalCharge'];

                    $toddlerCount = $row['toddlerCount'];
                    $adultCount = $row['adultCount'];
                    $kidsCount = $row['kidCount'];

                    $additionalReq = $row['additionalRequest'];
                    $addOns = $row['addOns'] ?? 'None';

                    if (!empty($customPackageID)) {
                        echo 'Wala pa';
                    } else {
                        $serviceID = $row['serviceID'];
                        $serviceType = $row['serviceType'];
                        $pax = $row['guestCount'];

                        $downpaymentNotes[] = 'Wait for the approval before paying the downpayment.';
                        $downpaymentNotes[] = 'Your booking is considered confirmed only after the downpayment is received and proof of payment verified';
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

                            $totalPax =  ($adultCount > 0 ? "{$adultCount} Adults" : '') .
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
                        $statusSubtitle = "We regret to inform you that your reservation has been rejected. Please contact us for more details.";
                    } elseif ($bookingStatusName === 'Cancelled') {
                        $status = strtolower($bookingStatusName) ?? null;
                        $statusTitle = "Booking Cancelled";
                        $statusSubtitle = "You have cancelled your reservation. If this was a mistake or you wish to rebook, please contact us.";
                    } elseif ($bookingStatusName === 'Expired') {
                        $status = strtolower($bookingStatusName) ?? null;
                        $statusTitle = "Expired Booking";
                        $statusSubtitle = "Sorry. The scheduled time for this booking has passed.";
                    } elseif ($bookingStatusName === 'Approved' && $paymentApprovalStatusName === 'Rejected') {
                        $status = strtolower($paymentApprovalStatusName) ?? null;
                        $statusTitle = "Payment Rejected";
                        $statusSubtitle = "Your reservation was approved, but the submitted payment was rejected. Please check the payment details and try again, or contact the admin for assistance.";
                    } elseif ($bookingStatusName === 'Approved' && $paymentApprovalStatusName === 'Pending') {
                        $status = strtolower($bookingStatusName) ?? NUll;
                        $statusTitle = "Your reservation has been approved.";
                        if ($paymentMethod === 'GCash') {
                            $statusSubtitle = "Your reservation request has been approved by the admin. You may now proceed with the down payment via GCash.";
                        } elseif ($paymentMethod === 'Cash') {
                            if ($bookingType === 'Resort') {
                                $statusSubtitle = "Your reservation has been approved by the admin. Please proceed on your scheduled swimming date and complete the payment on that day.";
                            } else {
                                $statusSubtitle = "Your reservation request has been approved by the admin. You may now proceed to the resort to make your downpayment.";
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
                    } elseif ($bookingStatusName === 'Approved' && $paymentApprovalStatusName === 'Done' && $paymentStatusName === 'Fully Paid') {
                        $statusTitle = "Booking Completed";
                        $status = strtolower($bookingStatusName) ?? NUll;
                        $statusSubtitle = "Thank you for staying with us! Your booking is fully paid and successfully completed. We hope you had a wonderful time.";
                    }


                    if ($finalBill === 0) {
                        $totalBill = $totalCost;
                    } elseif ($finalBill <= $totalCost || $finalBill >= $totalCost) {
                        $totalBill = $finalBill;
                    } else {
                        $totalBill = $totalCost;
                    }
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
            ?>

            <div class="leftStatusContainer">

                <input type="hidden" name="bookingStatus" id="bookingStatus"
                    value="<?= htmlspecialchars($bookingStatusName) ?>">
                <input type="hidden" name="paymentApprovalStatus" id="paymentApprovalStatus"
                    value="<?= htmlspecialchars($paymentApprovalStatusName) ?>">
                <input type="hidden" name="paymentStatus" id="paymentStatus"
                    value="<?= htmlspecialchars($paymentStatusName) ?>">
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
                        <input type="hidden" name="totalCost" value="<?= $totalBill ?>">
                        <input type="hidden" name="name" value="<?= $name ?>">
                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                        <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                        <input type="hidden" name="services" value="<?= implode(', ', array_unique($services)) ?>">
                        <button type="submit" class="btn btn-primary w-100 mt-3" name="downloadReceiptBtn" id="downloadReceiptBtn">Download Receipt </button>
                    </form>
                </div>
            </div>

            <div class="rightStatusContainer">
                <h3 class="rightContainerTitle">Reservation Summary</h3>

                <div class="firstRow">
                    <div class="clientContainer">
                        <h6 class="header">Client</h6>
                        <p class="content" id="clientName"><?= htmlspecialchars($name) ?></p>
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
                            <p class="cardContent" id="venue"><?= implode(' & ', array_unique($cottageRoom)) ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Duration</h6>
                            <p class="cardContent" id="eventDuration"><?= $duration ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Number of Guests</h6>
                            <p class="cardContent" id="guestNo"><?= $totalPax ?></p>
                        </li>

                        <li class="list-group-item" id="addOns">
                            <h6 class="cardHeader">Add Ons</h6>
                            <p class="cardContent"><?= !empty($addOns) ? htmlspecialchars($addOns) : "None" ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Request/Notes</h6>
                            <p class="cardContent" id="request">
                                <?= !empty($AddRequest) ? htmlspecialchars($AddRequest) : "None" ?>
                            </p>
                        </li>
                        <!-- <li class="list-group-item">
                            <h6 class="cardHeader">Package Type</h6>
                            <p class="cardContent" id="packageType">Wedding <img
                                    src="../../Assets/Images/Icon/information.png" alt="More Details"
                                    class="infoIcon">
        </p>
                        </li> -->
                        <li class="list-group-item" id="totalAmountSection">
                            <h6 class="cardHeader">Total Amount:</h6>
                            <h6 class="cardContentBill" id="totalAmount">₱ <?= number_format($totalCost, 2) ?></h6>
                        </li>

                        <li class="list-group-item" id="promoSection">
                            <h6 class="cardHeader">Promo/Discount:</h6>
                            <h6 class="cardContentBill" id="promoDiscount">₱ <?= number_format($discount, 2) ?></h6>
                        </li>

                        <li class="list-group-item" id="totalBillSection">
                            <h6 class="cardHeader">Grand Total:</h6>
                            <h6 class="cardContentBill" id="totalBill">₱ <?= number_format($totalCost, 2) ?></h6>
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

        console.log("Booking Stat: " + bookingStatus);
        console.log("payment App Stat" + paymentApprovalStatus);
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
        if (paramValue === "down$downpaymentImageError") {
            Swal.fire({
                title: "Oops!",
                text: "Failed to upload downpayment receipt down$downpaymentImage",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === "down$downpaymentImageFailed") {
            Swal.fire({
                title: "Oops!",
                text: "No downpayment down$downpaymentImage submitted.",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === "down$downpaymentImageSize") {
            Swal.fire({
                title: "Oops!",
                text: "File is too large. Maximum allowed size is 64MB.",
                icon: "warning",
                confirmButtonText: "Okay",
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