<?php

require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');


$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';

require '../emailSenderFunction.php';
require '../Helpers/statusFunctions.php';

$userID = (int) $_SESSION['userID'];
$userRole = (int) $_SESSION['userRole'];


function getMessageReceiver($userRoleID)
{
    switch ($userRoleID) {
        case 1:
            $receiver = 'Customer';
            break;
        case 2:
            $receiver = 'Partner';
            break;
        case 3:
            $receiver = 'Admin';
            break;
        default:
            $receiver = 'Customer';
    }
    return $receiver;
}


$getAdminName = $conn->prepare("SELECT adminID FROM admin WHERE userID = ?");
$getAdminName->bind_param('i', $userID);
if (!$getAdminName->execute()) {
    error_log("Failed Executing Admin Query. Error: " . $getAdminName->error);
}

$result = $getAdminName->get_result();

if ($result->num_rows === 0) {
    // error_log('NO DATA  ' . $userID);
    $approvedBy = 'Unknown';
}

$data = $result->fetch_assoc();

$adminID = $approvedBy = intval($data['adminID']);
$approvedDate = date('Y-m-d h:i:s');

if (isset($_POST['approvePaymentBtn'])) {

    // error_log(print_r($_POST, true));

    //ID`s
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $paymentID = (int) $_POST['paymentID'];
    $paymentApprovalStatusID = 2; //Approved

    $serviceIDs = $_POST['services'];

    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $totalBalance = (float) $_POST['balance'];
    $paymentAmount = (float) $_POST['paymentAmount'];
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $discount = (float) $_POST['discountAmount'];

    $finalBill = (float) str_replace(['₱', ','], '', $_POST['finalBill']) - $discount;

    $customerPaymentMade = (float) $_POST['customerPaymentMade'];

    if (strtolower($paymentMethod) !== 'gcash') {
        if (empty($paymentAmount)) {
            $_SESSION['bookingID'] = $bookingID;
            header('Location: ../../Pages/Admin/viewPayments.php?action=paymentFieldEmpty');
            exit();
        }
    }

    $amount = (strtolower($paymentMethod) !== 'gcash')  ? $paymentAmount : (($paymentAmount == 0) ? $customerPaymentMade : $paymentAmount);

    // error_log('amount: '  . $finalBill);
    // error_log('finalBill: ' . $discount);


    $today = date('M. d, Y g:i A');

    $conn->begin_transaction();
    try {


        $getServicesQuery = $conn->prepare("SELECT * FROM service WHERE serviceID = ?");

        foreach ($serviceIDs as $serviceID) {
            $getServicesQuery->bind_param("i", $serviceID);
            if (!$getServicesQuery->execute()) {
                $conn->rollback();
                throw new Exception("Failed to fetch service for ID: $serviceID");
            }

            $getServicesQueryResult = $getServicesQuery->get_result();
            if ($getServicesQueryResult->num_rows === 0) {
                $conn->rollback();
                throw new Exception("No service found for ID: $serviceID");
            }

            $row = $getServicesQueryResult->fetch_assoc();
            $serviceType = $row['serviceType'];
            $serviceStatus = 'confirmed';
            switch ($serviceType) {
                case 'Resort':
                    $resortServiceID = $row['resortServiceID'];
                    $updateUnavailableDates = $conn->prepare("UPDATE `serviceunavailabledate` SET `status`= ? ,`expiresAt`= NULL WHERE `resortServiceID`= ?");
                    $updateUnavailableDates->bind_param('si', $serviceStatus,  $resortServiceID);
                    if (!$updateUnavailableDates->execute()) {
                        $conn->rollback();
                        throw new Exception("Failed to insert unavailable date for resort service ID: $resortServiceID");
                    }
                    $updateUnavailableDates->close();
                    break;

                case 'Partner':
                    $partnershipServiceID = $row['partnershipServiceID'];
                    $updateUnavailableDates = $conn->prepare("UPDATE `serviceunavailabledate` SET `status`= ? ,`expiresAt`= NULL WHERE `partnershipServiceID`= ?");
                    $updateUnavailableDates->bind_param('si', $serviceStatus, $partnershipServiceID);
                    if (!$updateUnavailableDates->execute()) {
                        $conn->rollback();
                        throw new Exception("Failed to insert unavailable date for partner service ID: $partnershipServiceID");
                    }
                    $updateUnavailableDates->close();
                    break;

                default:
                    $conn->rollback();
                    throw new Exception("Unknown service type: $serviceType for service ID: $serviceID");
            }
        }

        $bookingCheck = $conn->prepare("SELECT cb.userBalance, cb.amountPaid, cb.finalBill, cb.discountAmount, b.bookingCode, b.bookingType, b.startDate, b.endDate, b.arrivalTime,  p.paymentDate FROM confirmedbooking cb
                                        LEFT JOIN booking b ON cb.bookingID = b.bookingID
                                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                                        WHERE b.bookingID = ?");
        $bookingCheck->bind_param("i", $bookingID);
        if (!$bookingCheck->execute()) {
            throw new Exception('Error executing booking check query!' . $bookingCheck->error);
        }
        $bookingResult = $bookingCheck->get_result();

        if ($bookingResult->num_rows === 0) {
            error_log('No bookings found: BookingID -> ' . $bookingID);
            throw new Exception('No found data!' . $bookingCheck->error);
        }
        $row = $bookingResult->fetch_assoc();
        //Stored data
        $storedUserBalance = floatval($row['userBalance']);
        $storedAmountPaid = floatval($row['amountPaid']);
        $storedFinalBill = floatval($row['finalBill']);
        $storedDiscount = floatval($row['discountAmount']);

        $paymentDate = date('F d, Y g:i A', strtotime($row['paymentDate']));
        $bookingType = htmlspecialchars($row['bookingType']);
        $arrivalTime = (!empty($row['arrivalTime']) || $row['arrivalTime'] !== "00:00:00") ? date('g:i A', strtotime($row['arrivalTime'])) : 'Not Stated';
        $startDate = date('M. d, Y g:i A', strtotime($row['startDate']));
        $endDate = date('M. d, Y g:i A', strtotime($row['endDate']));
        $bookingCode = $row['bookingCode'];

        //Computed data
        $totalDiscount = $storedDiscount + $discount;
        $totalAmountPaid = $storedAmountPaid + $amount;

        //AssigningStatus
        if ($totalBalance <= 0) {
            $totalBalance = 0;
            $paymentStatusID = 3; //Fully Paid
        } elseif ($amount > 0 && $totalBalance > 0 && $totalBalance < $storedFinalBill) {
            $paymentStatusID = 2; //
        } else {
            $paymentStatusID = 1;
        }

        $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET 
                    finalBill = ?,
                    discountAmount = ?,
                    amountPaid = ?,
                    userBalance = ?,
                    paymentApprovalStatus = ?,
                    paymentStatus = ?, approvedBy = ?, approvedDate = ? WHERE bookingID = ?");
        $updateBookingPaymentStatus->bind_param("ddddiiisi", $finalBill, $totalDiscount, $totalAmountPaid, $totalBalance, $paymentApprovalStatusID, $paymentStatusID, $approvedBy, $approvedDate, $bookingID);

        if (!$updateBookingPaymentStatus->execute()) {
            $conn->rollback();
            throw new Exception('Error executing update booking payment status query!' . $updateBookingPaymentStatus->error);
        }

        if (strtolower($paymentMethod) === 'gcash' && $paymentAmount != 0) {
            $updatePaymentAmount = $conn->prepare("UPDATE `payment` SET `amount`= ? WHERE paymentID = ?");
            $updatePaymentAmount->bind_param('di', $paymentAmount, $paymentID);

            if (!$updatePaymentAmount->execute()) {
                $conn->rollback();
                throw new Exception('Error updating payment amount!' . $updatePaymentAmount->error);
            }
        }


        $dateCreated = date('d F Y');
        $email_message = '
                        <body style="font-family: Poppins, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <!-- Header -->
                                <tr style="background-color: #365CCE;">
                                    <td style="text-align:center; padding: 30px;">
                                        <h4 style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 18px; color: #ffffff; margin: 0;">
                                            Payment Confirmation
                                        </h4>
                                    </td>
                                </tr>

                                <!-- Body -->
                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: -10px 0 20px; font-style: italic;">
                                            Booking Reference: <strong>' . $bookingCode . '</strong> &nbsp;|&nbsp; Created on ' . $dateCreated . '
                                        </p>

                                        <p style="font-size: 14px; margin: 20px 0 10px;">
                                            Hello <strong>' . $firstName . '</strong>,
                                        </p>

                                        <p style="font-size: 14px; margin: 10px 0;">
                                            Thank you for your payment! We’re pleased to confirm that we have received <strong>₱' . number_format($amount, 2) . '</strong> on <strong> ' . $paymentDate . '</strong> for your booking.
                                        </p>

                                        <p style="font-size: 14px; margin: 20px 0 10px;">Here are your booking details:</p>

                                        <p style="font-size: 12px; margin: 6px 0;">Booking Code: <strong>' . $bookingCode . ' </strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">Status: <strong>Reserved</strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">Start Date: <strong>' . $startDate . '</strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">End Date: <strong>' . $endDate . '</strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">Arrival Time: <strong>' . $arrivalTime . ' </strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">Booking Type: <strong>' . htmlspecialchars($bookingType) . ' Booking</strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">Grand Total: <strong>₱' . number_format($finalBill, 2) . '</strong></p>
                                        <p style="font-size: 12px; margin: 6px 0;">Balance: <strong>₱' . number_format($totalBalance, 2) . '</strong></p>

                                        <p style="font-size: 12px; margin: 15px 0;">
                                            For more information, you can visit your booking on our website and navigate to the <strong>Booking History</strong> section in account.
                                            Please note that while you can cancel your booking through the website, <strong>payments are non-refundable</strong> as per our policy.
                                        </p>

                                        <p style="font-size: 12px; margin: 8px 0;">
                                            If you have any questions or need assistance, please feel free to contact us via 
                                            <a href="https://www.facebook.com/messages/t/100888189251567" style="color: #365CCE; text-decoration: none;">Facebook Messenger</a>.
                                        </p>

                                        <p style="font-size: 12px; margin: 15px 0 0;">We look forward to welcoming you soon!</p>

                                        <p style="font-size: 14px; margin: 15px 0 0;">Thank you,</p>
                                        <p style="font-size: 14px; font-weight: bold; margin: 6px 0 0;">Mamyr Resort and Events Place</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
            ';

        $subject = 'Payment Confirmation';

        $isSend =  false;
        if (sendEmail($email, $firstName, $subject, $email_message, $env)) {
            $isSend = true;
        }

        if (!$isSend) {
            $conn->rollback();
            throw new Exception('Failed Sending Email');
        }

        $receiver = getMessageReceiver($userRoleID);
        $message = 'Payment approved successfully. We have received ₱' . $amount . ' and reviewed your payment. The service you booked is now reserved. Thank you';
        $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, receiverID, senderID, message, receiver) VALUES(?,?,?,?,?)");
        $insertNotification->bind_param("iiiss", $bookingID, $customerID, $userID, $message, $receiver);
        if (! $insertNotification->execute()) {
            $conn->rollback();
            throw new Exception('Error executing notification query!' . $insertNotification->error);
        }

        $conn->commit();
        header('Location: ../../Pages/Admin/transaction.php?action=approved');
        $bookingResult->free();
        $bookingCheck->close();
        $updateBookingPaymentStatus->close();
        $insertNotification->close();
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error Message: " . $e->getMessage());
        header("Location: ../../../../Pages/Admin/viewPayments.php");
    }
} elseif (isset($_POST['rejectPaymentBtn'])) {
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $reasonID = (int) $_POST['rejection-reason'];
    $otherReason = mysqli_real_escape_string($conn, $_POST['rejection-entered-reason']);

    if (empty($reasonID) && empty($otherReason)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=reasonFieldEmpty');
        exit();
    } else {
        $conn->begin_transaction();
        try {
            $bookingCheck = $conn->prepare("SELECT * FROM confirmedbooking WHERE bookingID = ? ");
            $bookingCheck->bind_param("i", $bookingID);
            if (!$bookingCheck->execute()) {
                throw new Exception('Error executing booking check query!' . $bookingCheck->error);
            }
            $bookingResult = $bookingCheck->get_result();

            if ($bookingResult->num_rows === 0) {
                error_log('No bookings found: BookingID -> ' . $bookingID);
                throw new Exception('No found data!' . $bookingCheck->error);
            }
            $row = $bookingResult->fetch_assoc();

            // $paymentIssueID = 4;
            $paymentRejectedStatus = 5;  //Rejected Status
            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET
            paymentApprovalStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ii", $paymentRejectedStatus,  $bookingID);
            if (!$updateBookingPaymentStatus->execute()) {
                $conn->rollback();
                throw new Exception('Failed Updating Payment Approval Status' . $updateBookingPaymentStatus->error);
            }

            if ($otherReason == '') {
                $getMessage = $conn->prepare("SELECT `reasonDescription` FROM `reason` WHERE reasonID = ?");
                $getMessage->bind_param('i', $reasonID);
                if (!$getMessage->execute()) {
                    $conn->rollback();
                    throw new Exception('Failed selecting the message for reasonID:' . $reasonID);
                }

                $result = $getMessage->get_result();

                if ($result->num_rows === 0) {
                    $message = 'We couldn’t verify your payment status at this time. Please contact the resort administrator for assistance.';
                }

                $row = $result->fetch_assoc();

                $message = $row['reasonDescription'];
            } else {
                $message = $otherReason;
            }


            $insertRejectionReason = $conn->prepare("INSERT INTO `booking_rejection`(`bookingID`, `adminID`, `reasonID`, `otherReason`) VALUES (?,?,?,?)");
            $insertRejectionReason->bind_param('iiis', $bookingID, $adminID, $reasonID, $otherReason);
            if (!$insertRejectionReason->execute()) {
                $conn->rollback();
                throw new Exception("Error inserting rejection reason for bookingID: " . $bookingID);
            }

            $receiver = getMessageReceiver($userRoleID);
            $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, receiverID, senderID, message, receiver) VALUES(?,?,?,?,?)");
            $insertNotification->bind_param("iiiss", $bookingID, $customerID, $userID, $message, $receiver);
            if (!$insertNotification->execute()) {
                $conn->rollback();
                throw new Exception('Insert Notification failed' . $insertNotification->error);
            }

            $conn->commit();
            header('Location: ../../Pages/Admin/transaction.php?action=rejected');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error: " . $e->getMessage());
            header('Location: ../../Pages/Admin/transaction.php?action=failed');
        }
    }
} elseif (isset($_POST['submitPaymentBtn'])) {
    error_log(print_r($_POST, true));

    //IDs
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];

    //Payments
    $customerPayment = (float) $_POST['customerPayment'];
    $additionalCharge = (float) $_POST['additional-charge'];
    $totalBalance = (float) $_POST['new-balance'];
    $finalBill = (float) $_POST['new-bill'];
    $originalBill = (float) str_replace(['₱', ','], '', $_POST['originalBill']);
    $downpayment = (float) str_replace(['₱', ','], '',  $_POST['downpayment']);


    //Info
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    //Service charge
    $additionalCharges = $_POST['additionalCharges'];

    if (empty($customerPayment)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=addPaymentFieldEmpty');
        exit();
    } else {
        $conn->begin_transaction();
        try {
            $bookingCheck = $conn->prepare("SELECT cb.confirmedBookingID, cb.userBalance, cb.amountPaid, cb.finalBill, cb.discountAmount, b.bookingCode, b.bookingType, b.startDate, b.endDate, b.arrivalTime,  p.paymentDate FROM confirmedbooking cb
                                        LEFT JOIN booking b ON cb.bookingID = b.bookingID
                                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                                        WHERE b.bookingID = ?");
            $bookingCheck->bind_param("i", $bookingID);
            if (!$bookingCheck->execute()) {
                throw new Exception('Error executing booking check query!' . $bookingCheck->error);
            }
            $bookingResult = $bookingCheck->get_result();

            if ($bookingResult->num_rows === 0) {
                error_log('No bookings found: BookingID -> ' . $bookingID);
                throw new Exception('No found data!' . $bookingCheck->error);
            }
            $row = $bookingResult->fetch_assoc();
            //Stored data
            $storedAmountPaid = floatval($row['amountPaid']);
            $bookingType = htmlspecialchars($row['bookingType']);
            $startDate = $row['startDate'];
            $endDate = $row['endDate'];
            $bookingCode = $row['bookingCode'];
            $storedFinalBill = floatval($row['finalBill']);
            $confirmedBookingID = $row['confirmedBookingID'];

            //Computed data
            $totalAmountPaid = $storedAmountPaid + $customerPayment;


            //AssigningStatus
            if ($totalBalance <= 0) {
                $totalBalance = 0;
                $paymentStatusID = 3; //Fully Paid
            } elseif ($customerPayment > 0 && $totalBalance > 0 && $totalBalance < $storedFinalBill) {
                $paymentStatusID = 2; //
            } else {
                $paymentStatusID = 1;
            }

            $paymentStatus = getPaymentStatus($conn, $paymentStatusID);


            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET 
                                amountPaid = ?,
                                userBalance = ?,
                                paymentStatus = ?,
                                additionalCharge = ?,
                                finalBill = ?
                                WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ddiddi",  $totalAmountPaid, $totalBalance,  $paymentStatusID, $additionalCharge, $finalBill, $bookingID);
            if (!$updateBookingPaymentStatus->execute()) {
                $conn->rollback();
                throw new Exception("Error: " . $updateBookingPaymentStatus->error);
            }

            $today = Date('Y-m-d H:i:s');
            $paymentMethod = 'Cash';
            $insertIntoPayment = $conn->prepare("INSERT INTO `payment`(`confirmedBookingID`, `amount`, `paymentDate`, `paymentMethod`) VALUES (?,?,?,?)");
            $insertIntoPayment->bind_param("idss", $confirmedBookingID, $customerPayment, $today, $paymentMethod);
            if (!$insertIntoPayment->execute()) {
                $conn->rollback();
                throw new Exception("Error: " . $insertIntoPayment->error);
            }

            if (strtotime($today) < strtotime($startDate)) {
                //upcoming
                $emailMessage = "We look forward to welcoming you soon!";
            } elseif (strtotime($today) >= strtotime($startDate) && strtotime($today) <= strtotime($endDate)) {
                //on-going
                $emailMessage = "We hope you're enjoying your time with us at Mamyr Resort and Events Place.";
            } else {
                //ended
                $emailMessage = "We hope you enjoyed your time with us at Mamyr Resort and Events Place.";
            }

            $insertCharges = $conn->prepare("INSERT INTO `additionalcharge`(`bookingID`, `chargeDescription`, `amount`) VALUES (?,?,?)");
            $additionalChargesRow = '';
            if (empty($additionalCharges)) {
                $additionalChargesRow = '
                <tr>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center;"> No Additional Charges </td>
                </tr>
                ';
            }
            foreach ($additionalCharges as $name => $items) {
                $name = strtolower($name);
                if ($name === 'others') {
                    $description = $items['quantity'] . ' — ' . $items['name'];
                } else {
                    $description = $items['quantity'] . ' — ' . ucfirst($name);
                }

                $amount = $items['amount'];
                $additionalChargesRow .= '
                <tr>
                    <td style="border: 1px solid #ccc; padding: 6px;">' . htmlspecialchars($description) . '</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: right;"> ₱' . number_format($amount, 2) . '</td>
                </tr>
                ';

                $insertCharges->bind_param('isd', $bookingID, $description, $amount);
                if (!$insertCharges->execute()) {
                    $conn->rollback();
                    throw new Exception('Failed Inserting charges' . $insertCharges->error);
                }
            }


            $dateCreated = date('d F Y');
            $email_message = '
                        <body style="font-family: Poppins, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <!-- Header -->
                                <tr style="background-color: #365CCE;">
                                    <td style="text-align:center; padding: 30px;">
                                        <h4 style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 18px; color: #ffffff; margin: 0;">
                                            Payment Confirmation
                                        </h4>
                                    </td>
                                </tr>

                                <!-- Body -->
                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: -10px 0 20px; font-style: italic;">
                                            Booking Reference: <strong>' . $bookingCode . '</strong> &nbsp;|&nbsp; Created on ' . $dateCreated . '
                                        </p>

                                        <p style="font-size: 14px; margin: 20px 0 10px;">
                                            Hello <strong>' . $firstName . '</strong>,
                                        </p>

                                        <p style="font-size: 14px; margin: 10px 0;">
                                            Thank you for your payment! We’re pleased to confirm that we have received <strong>₱' . number_format($customerPayment, 2) . '</strong> for your booking <strong> ' . $bookingCode . '</strong> for your booking.
                                        </p>

                                        <p style="font-size: 14px; margin: 20px 0 10px;">Here are your payment details:</p>

                                        <p style="font-size: 14px; margin: 8px 0;">Original Bill: <strong>₱' . number_format($originalBill, 2) . ' </strong></p>
                                        <p style="font-size: 14px; margin: 8px 0;">Downpayment: <strong>₱' . number_format($downpayment, 2) . '</strong></p>

                                        <p style="font-size: 14px; margin: 8px 0;">Additional Services/Charges</p>
                                        <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 10px;">
                                            <thead>
                                                <tr>
                                                    <th style="border: 1px solid #ccc; padding: 6px; text-align: center;">Description</th>
                                                    <th style="border: 1px solid #ccc; padding: 6px; text-align: center;">Amount (₱)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            ' . $additionalChargesRow . '
                                            </tbody>
                                        </table>
                                        <p style="font-size: 14px; margin: 8px 0;">Total Additional Charge: <strong>' . number_format($additionalCharge, 2) . ' </strong></p>
                                        <p style="font-size: 14px; margin: 8px 0;">Final Bill: <strong>' . number_format($finalBill, 2) . ' </strong></p>
                                        <p style="font-size: 14px; margin: 8px 0;">Total Amount Paid: <strong>₱' . number_format($totalAmountPaid, 2) . '</strong></p>

                                        <p style="font-size: 14px; margin: 8px 0;">Status: <strong>' . $paymentStatus['paymentStatusName'] . '</strong></p>

                                        <p style="font-size: 14px; margin: 20px 0;">' .  $emailMessage . ' </p>
                                        <p style="font-size: 14px; margin: 20px 0;">
                                            <strong> Rating our service is much appreciated! </strong> <br> You can share your feedback or experience with us to help us serve you even better.
                                        </p>
                                        <p style="font-size: 14px; margin: 20px 0;">
                                            You can <strong> view and download your receipt </strong> anytime on our website under <strong>Booking History</strong> section in account.
                                        </p>

                                        <p style="font-size: 14px; margin: 10px 0;">
                                            If you have any questions or need assistance, please feel free to contact us via 
                                            <a href="https://www.facebook.com/messages/t/100888189251567" style="color: #365CCE; text-decoration: none;">Facebook Messenger</a>.
                                        </p>

                                        <p style="font-size: 16px; margin: 30px 0 0;">Thank you,</p>
                                        <p style="font-size: 16px; font-weight: bold; margin: 8px 0 0;">Mamyr Resort and Events Place</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
            ';

            $subject = 'Payment Confirmation';

            $isSend =  false;
            if (sendEmail($email, $firstName, $subject, $email_message, $env)) {
                $isSend = true;
            }

            if (!$isSend) {
                $conn->rollback();
                throw new Exception('Failed Sending Email');
            }

            $receiver = getMessageReceiver($userRoleID);

            $message = "We have successfully deducted your payment of " . $customerPayment .
                " from your balance. Please check your payment history in your account for more details. " .
                "Your current balance is: " . ($totalBalance > 0 ? "₱" . number_format($totalBalance, 2) : "0.00") . ".";
            $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, receiverID, senderID, message, receiver) VALUES(?,?,?,?,?)");
            $insertNotification->bind_param("iiiss", $bookingID, $customerID, $userID, $message, $receiver);

            if (!$insertNotification->execute()) {
                $conn->rollback();
                throw new Exception("Error: " . $insertNotification->error);
            }

            $conn->commit();
            header('Location: ../../Pages/Admin/transaction.php?action=paymentSuccess');
            exit();
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            header('Location: ../../Pages/Admin/transaction.php?action=failed');
            exit();
        }
    }
} else {
    header('Location: ../../Pages/Admin/transaction.php?action=failed');
    exit();
}
