<?php

require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');

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

$approvedBy = intval($data['adminID']);
$approvedDate = date('Y-m-d h:i:s');

if (isset($_POST['approvePaymentBtn'])) {

    //ID`s
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $paymentApprovalStatusID = 2; //Approved

    $rawFinalBill = mysqli_real_escape_string($conn, $_POST['finalBill']);
    $finalBill = (float) str_replace(['₱', ','], '',  $rawFinalBill);

    $paymentAmount = (float) $_POST['paymentAmount'];

    $radioOptions = isset($_POST['radioOptions']) ? mysqli_real_escape_string($conn, $_POST['radioOptions']) : null;

    $discount = 0;
    switch ($radioOptions) {
        case 'discount':
            $discount = floatval($_POST['discountAmount']);
            $finalBill = $finalBill - $discount;
            break;
        case 'newTotalAmount':
            $finalBill = ($_POST['newTotalAmount'] === 0) ? $finalBill : $_POST['newTotalAmount'];
            break;
        default:
            $finalBill;
            break;
    }

    if (empty($paymentAmount)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=paymentFieldEmpty');
        exit();
    } else {

        // $customerPayment = "₱" . number_format($paymentAmount, 2);
        // // $balance = (float) str_replace(['₱', ','], '', $balance);
        // $paymentAmount = (float) str_replace(['₱', ','], '', $customerPayment);
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
            //Stored data
            $storedUserBalance = floatval($row['userBalance']);
            $storedAmountPaid = floatval($row['amountPaid']);
            $storedFinalBill = floatval($row['confirmedFinalBill']);
            $storedDiscount = floatval($row['discountAmount']);
            //Computed data
            $totalDiscount = $storedDiscount + $discount;
            $totalBalance = $storedUserBalance - $paymentAmount;
            $totalAmountPaid = $storedAmountPaid + $paymentAmount;

            //AssigningStatus
            if ($totalBalance <= 0) {
                $totalBalance = 0;
                $paymentStatusID = 3; //Fully Paid
            } elseif ($paymentAmount > 0 && $totalBalance > 0 && $totalBalance < $storedFinalBill) {
                $paymentStatusID = 2; //
            } else {
                $paymentStatusID = 1;
            }

            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET 
                    confirmedFinalBill = ?,
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

            $receiver = getMessageReceiver($userRoleID);
            $message = 'Payment approved successfully. We have received ₱' . $paymentAmount . ' and reviewed your payment. The service you booked is now reserved. Thank you';
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
    }
} elseif (isset($_POST['rejectPaymentBtn'])) {
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $message = mysqli_real_escape_string($conn, $_POST['rejectionReason']);


    if (empty($message)) {
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

            // $paymenIssueID = 4;
            $paymentRejectedStatus = 3;  //Rejected Status
            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET
            paymentApprovalStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ii", $paymentRejectedStatus,  $bookingID);
            if (!$updateBookingPaymentStatus->execute()) {
                $conn->rollback();
                throw new Exception('Failed Updating Payment Approval Status' . $updateBookingPaymentStatus->error);
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

    //IDs
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    //Options
    $radioOptions = isset($_POST['radioOptions']) ? mysqli_real_escape_string($conn, $_POST['radioOptions']) : null;
    $hasAdditionalCharge = mysqli_real_escape_string($conn, $_POST['addCharge']) ?? null;

    //Payments
    $customerPayment = (float) $_POST['customerPayment'];
    $finalBill = (float) str_replace(['₱', ','], '', $_POST['finalBill']);
    $discount = 0;
    $additionalCharge = (float) 0.00;

    if ($hasAdditionalCharge) {
        $additionalCharge = (float) $_POST['additionalCharge'];
    }

    switch ($radioOptions) {
        case 'discount':
            $discount = floatval($_POST['discountAmount']);
            $finalBill = $finalBill - $discount;
            break;
        case 'newTotalAmount':
            $finalBill = ($_POST['newTotalAmount'] === 0) ? $finalBill : $_POST['newTotalAmount'];
            break;
        default:
            $finalBill;
            break;
    }

    if (empty($customerPayment)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=addPaymentFieldEmpty');
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
            //Stored data
            $storedUserBalance = floatval($row['userBalance']);
            $storedAmountPaid = floatval($row['amountPaid']);
            $storedFinalBill = floatval($row['confirmedFinalBill']);
            $storedDiscount = floatval($row['discountAmount']);
            //Computed data
            $totalDiscount = $storedDiscount + $discount;
            $totalBalance = $storedUserBalance - $customerPayment;
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


            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET 
                                amountPaid = ?,
                                userBalance = ?,
                                paymentStatus = ?,
                                additionalCharge = ?
                                WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ddidi",  $totalAmountPaid, $totalBalance,  $paymentStatusID, $additionalCharge, $bookingID);
            if (!$updateBookingPaymentStatus->execute()) {
                $conn->rollback();
                throw new Exception("Error: " . $updateBookingPaymentStatus->error);
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
