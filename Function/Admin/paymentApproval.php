<?php

require '../../Config/dbcon.php';
session_start();


if (isset($_POST['approvePaymentBtn'])) {
    $paymentAmount = (float) $_POST['paymentAmount'];
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $approvedPaymentStatus = 2;

    $totalAmount = mysqli_real_escape_string($conn, $_POST['totalAmount']);
    $totalCost = (float) str_replace(['₱', ','], '', $totalAmount);
    $discount = floatval($_POST['discountAmount']);


    if ($discount != 0.00) {
        $bill = $totalCost - $discount;
    } else {
        $bill = $totalCost;
    }

    if (empty($paymentAmount)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=paymentFieldEmpty');
        exit();
    } else {

        $customerPayment = "₱" . number_format($paymentAmount, 2);
        // $balance = (float) str_replace(['₱', ','], '', $balance);
        $paymentAmount = (float) str_replace(['₱', ','], '', $customerPayment);

        $bookingCheck = $conn->prepare("SELECT * FROM confirmedbooking WHERE bookingID = ? ");
        $bookingCheck->bind_param("i", $bookingID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        if ($bookingResult->num_rows > 0) {
            $row = $bookingResult->fetch_assoc();

            $storedUserBalance = floatval($row['userBalance']);
            $storedAmountPaid = floatval($row['amountPaid']);
            $storedBill = floatval($row['confirmedFinalBill']);
            $storedDiscount = floatval($row['discountAmount']);

            $discount = $storedDiscount + $discount;

            $totalBalance = $storedUserBalance - $paymentAmount;
            $amountPaid = $storedAmountPaid + $paymentAmount;

            if ($totalBalance <= 0) {
                $totalBalance;
                $paymentStatus = 3;
            } elseif ($paymentAmount > 0 && $totalBalance > 0 && $totalBalance < $storedBill) {
                $paymentStatus = 2;
            } else {
                $paymentStatus = 1;
            }


            //Update booking and payment status
            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET 
                    confirmedFinalBill = ?,
                    discountAmount = ?,
                    amountPaid = ?,
                    userBalance = ?,
                    paymentApprovalStatus = ?,
                    paymentStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ddddiii", $bill, $discount, $amountPaid, $totalBalance, $approvedPaymentStatus, $paymentStatus, $bookingID);
            if ($updateBookingPaymentStatus->execute()) {

                if ($userRoleID === 1) {
                    $receiver = 'Customer';
                } elseif ($userRoleID === 2) {
                    $receiver = 'Partner';
                } elseif ($userRoleID === 3) {
                    $receiver = 'Admin';
                }
                $message = 'Payment approved successfully. We have received and reviewed your payment. The service you booked is now reserved. Thank you';
                $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, userID, message, receiver)
                 VALUES(?,?,?,?)");
                $insertNotification->bind_param("iiss", $bookingID, $customerID, $message, $receiver);
                $insertNotification->execute();

                header('Location: ../../Pages/Admin/transaction.php?action=approved');
                exit();
                $updateBookingPaymentStatus->close();
                $insertNotification->close();
            } else {
                header('Location: ../../Pages/Admin/transaction.php?action=failed');
                exit();
            }
        } else {
            header('Location: ../../Pages/Admin/transaction.php?action=failed');
            exit();
        }
    }
} elseif (isset($_POST['rejectPaymentBtn'])) {
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $message = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $paymentRejectedStatus = 3;  //Rejected Status

    if (empty($message)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=reasonFieldEmpty');
        exit();
    } else {
        $bookingCheck = $conn->prepare("SELECT * FROM confirmedbooking WHERE bookingID = ? ");
        $bookingCheck->bind_param("i", $bookingID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        if ($bookingResult->num_rows > 0) {
            $row = $bookingResult->fetch_assoc();

            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET
            paymentApprovalStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ii", $paymentRejectedStatus,  $bookingID);
            if ($updateBookingPaymentStatus->execute()) {

                if ($userRoleID === 1) {
                    $receiver = 'Customer';
                } elseif ($userRoleID === 2) {
                    $receiver = 'Partner';
                } elseif ($userRoleID === 3) {
                    $receiver = 'Admin';
                }

                $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, userID, message, receiver)
                    VALUES(?,?,?,?)");
                $insertNotification->bind_param("iiss", $bookingID, $customerID, $message, $receiver);
                $insertNotification->execute();

                header('Location: ../../Pages/Admin/transaction.php?action=rejected');
                exit();
            } else {
                header('Location: ../../Pages/Admin/transaction.php?action=failed');
                exit();
            }
        } else {
            header('Location: ../../Pages/Admin/transaction.php?action=failed');
            exit();
        }
    }
} elseif (isset($_POST['submitPaymentBtn'])) {

    $customerPayment = mysqli_real_escape_string($conn, $_POST['customerPayment']);
    $bookingID  = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $balance = mysqli_real_escape_string($conn, $_POST['balance']);
    // $totalAmount = mysqli_real_escape_string($conn, $_POST['totalAmount']);

    if (empty($customerPayment)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=paymentFieldEmpty');
        exit();
    } else {
        $customerPayment = "₱" . number_format($customerPayment, 2);
        $balance = (float) str_replace(['₱', ','], '', $balance);
        $paymentAmount = (float) str_replace(['₱', ','], '', $customerPayment);


        $bookingCheck = $conn->prepare("SELECT * FROM confirmedbooking WHERE bookingID = ? ");
        $bookingCheck->bind_param("i", $bookingID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        if ($bookingResult->num_rows > 0) {
            $row = $bookingResult->fetch_assoc();

            $storedUserBalance = floatval($row['userBalance']);
            $storedAmountPaid = floatval($row['amountPaid']);
            $storedBill = floatval($row['confirmedFinalBill']);

            $totalBalance = $storeUserBalance - $paymentAmount;
            $amountPaid = $storedAmountPaid + $paymentAmount;


            if ($totalBalance = 0) {
                $totalBalance = 0;
                $paymentStatus = 3;
            } elseif ($paymentAmount > 0 && $totalBalance > 0 && $totalBalance < $storedBill) {
                $paymentStatus = 2;
            } else {
                $paymentStatus = 1;
            }


            //Update payment amount and payment status
            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedbooking SET 
                                amountPaid = ?,
                                userBalance = ?,
                                paymentStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ddii", $amountPaid, $totalBalance,  $paymentStatus, $bookingID);
            if ($updateBookingPaymentStatus->execute()) {

                if ($userRoleID === 1) {
                    $receiver = 'Customer';
                } elseif ($userRoleID === 2) {
                    $receiver = 'Partner';
                } elseif ($userRoleID === 3) {
                    $receiver = 'Admin';
                }

                $message = "We have successfully deducted your payment of " . $customerPayment .
                    " from your balance. Please check your payment history in your account for more details. " .
                    "Your current balance is: " . ($totalBalance > 0 ? "₱" . number_format($totalBalance, 2) : "0.00") . ".";
                $insertNotification = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
                $insertNotification->bind_param("iiss", $bookingID, $customerID, $message, $receiver);
                $insertNotification->execute();

                header('Location: ../../Pages/Admin/transaction.php?action=paymentSuccess');
                exit();
            } else {
                header('Location: ../../Pages/Admin/transaction.php?action=paymentFailed');
                exit();
            }
        } else {
            header('Location: ../../Pages/Admin/transaction.php?action=paymentFailed');
            exit();
        }
    }
} else {
    header('Location: ../../Pages/Admin/transaction.php?action=failed');
    exit();
}
