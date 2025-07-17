<?php

require '../../Config/dbcon.php';
session_start();


if (isset($_POST['approvePaymentBtn'])) {
    $paymentAmount = mysqli_real_escape_string($conn, $_POST['paymentAmount']);
    $bookingID  = mysqli_real_escape_string($conn, $_POST['bookingID']);
    // $balance = mysqli_real_escape_string($conn, $_POST['balance']);
    // $totalAmount = mysqli_real_escape_string($conn, $_POST['totalAmount']);
    $customerID = mysqli_real_escape_string($conn, $_POST['customerID']);
    $confirmBookingStatus = 2; //approve status


    if (empty($paymentAmount)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=paymentFieldEmpty');
        exit();
    } else {

        $customerPayment = "₱" . number_format($paymentAmount, 2);
        // $balance = (float) str_replace(['₱', ','], '', $balance);
        $paymentAmount = (float) str_replace(['₱', ','], '', $customerPayment);

        $bookingCheck = $conn->prepare("SELECT * FROM confirmedBookings WHERE bookingID = ? ");
        $bookingCheck->bind_param("i", $bookingID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        if ($bookingResult->num_rows > 0) {
            $row = $bookingResult->fetch_assoc();

            $userBalance = $row['userBalance'];
            $storedAmountPaid = $row['amountPaid'];
            $totalAmount = $row['CBtotalCost'];

            $totalBalance = $userBalance - $paymentAmount;
            $amountPaid = $storedAmountPaid + $paymentAmount;

            if ($totalBalance == $userBalance) {
                $paymentStatus = 1;
            } elseif ($paymentAmount < $userBalance && $totalBalance < $totalAmount) {
                $paymentStatus = 2;
            } elseif ($totalBalance == 0) {
                $paymentStatus = 3;
            }

            //Update booking and payment status
            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedBookings SET 
        amountPaid = ?,
        userBalance = ?,
        confirmedBookingStatus = ?,
        paymentStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ddiii", $paymentAmount, $totalBalance, $confirmBookingStatus, $paymentStatus, $bookingID);
            if ($updateBookingPaymentStatus->execute()) {
                $receiver = 'Customer';
                $message = 'Payment approved successfully. We have received and reviewed your payment. The service you booked is now reserved. Thank you';
                $insertNotification = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
                $insertNotification->bind_param("iiss", $bookingID, $customerID, $message, $receiver);
                $insertNotification->execute();

                header('Location: ../../Pages/Admin/transaction.php?action=approved');
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
} elseif (isset($_POST['rejectPaymentBtn'])) {
    $bookingID  = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $customerID = mysqli_real_escape_string($conn, $_POST['customerID']);
    $message = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $confirmBookingStatus = 3;  //Rejected Status

    if (empty($message)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=reasonFieldEmpty');
        exit();
    } else {
        $bookingCheck = $conn->prepare("SELECT * FROM confirmedBookings WHERE bookingID = ? ");
        $bookingCheck->bind_param("i", $bookingID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        if ($bookingResult->num_rows > 0) {
            $row = $bookingResult->fetch_assoc();

            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedBookings SET
        confirmedBookingStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ii", $confirmBookingStatus,  $bookingID);
            if ($updateBookingPaymentStatus->execute()) {

                $receiver = 'Customer';
                $insertNotification = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
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
    $bookingID  = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $balance = mysqli_real_escape_string($conn, $_POST['balance']);
    // $totalAmount = mysqli_real_escape_string($conn, $_POST['totalAmount']);
    $customerID = mysqli_real_escape_string($conn, $_POST['customerID']);

    if (empty($customerPayment)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewPayments.php?action=paymentFieldEmpty');
        exit();
    } else {

        $customerPayment = "₱" . number_format($customerPayment, 2);
        $balance = (float) str_replace(['₱', ','], '', $balance);
        $paymentAmount = (float) str_replace(['₱', ','], '', $customerPayment);

        // $totalBalance = $balance - $customerPayment;

        // if ($customerPayment < $totalAmount || $customerPayment === 0) {
        //     $paymentStatus = 2; //Partially Paid
        // } elseif ($customerPayment >= $totalAmount) {
        //     $paymentStatus = 3; //Fully Paid
        // }
        $bookingCheck = $conn->prepare("SELECT * FROM confirmedBookings WHERE bookingID = ? ");
        $bookingCheck->bind_param("i", $bookingID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        if ($bookingResult->num_rows > 0) {
            $row = $bookingResult->fetch_assoc();

            $userBalance = $row['userBalance'];
            $storedAmountPaid = $row['amountPaid'];
            $totalAmount = $row['CBtotalCost'];

            $totalBalance = $userBalance - $paymentAmount;
            $amountPaid = $storedAmountPaid + $paymentAmount;

            if ($totalBalance == $userBalance) {
                $paymentStatus = 1;
            } elseif ($paymentAmount < $userBalance && $totalBalance < $totalAmount) {
                $paymentStatus = 2;
            } elseif ($totalBalance == 0) {
                $paymentStatus = 3;
            }

            //Update payment amount and payment status
            $updateBookingPaymentStatus = $conn->prepare("UPDATE confirmedBookings SET 
                                amountPaid = ?,
                                userBalance = ?,
                                paymentStatus = ? WHERE bookingID = ?");
            $updateBookingPaymentStatus->bind_param("ddii", $amountPaid, $totalBalance,  $paymentStatus, $bookingID);
            if ($updateBookingPaymentStatus->execute()) {

                $receiver = 'Customer';
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
