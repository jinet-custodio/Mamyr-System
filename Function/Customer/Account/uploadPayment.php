<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


if (isset($_POST['submitDownpaymentImage'])) {

    $bookingID = (int) $_POST['bookingID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $imageMaxSize = 5 * 1024 * 1024;
    // $imageData = null;
    $storeProofPath = __DIR__ . '/../../../Assets/Images/PaymentProof/';
    $confirmedBookingID = (int) $_POST['confirmedBookingID'];

    $paymentAmount = (float) $_POST['payment-amount'];
    $downpayment = (float) $_POST['downpayment'];

    $imageFileName = mysqli_real_escape_string($conn, $_POST['imageFileName']) ?? '';

    if (empty($imageFileName)) {
        if (isset($_FILES['downpaymentPic']) && is_uploaded_file($_FILES['downpaymentPic']['tmp_name'])) {
            if ($_FILES['downpaymentPic']['size'] <= $imageMaxSize) {
                $imagePath = $_FILES['downpaymentPic']['tmp_name'];
                $randomNum =  str_pad($bookingID, 4, '0', STR_PAD_LEFT);
                $imageFileName = $randomNum . $_FILES['downpaymentPic']['name'];
                $storeImage = $storeProofPath . $imageFileName;
                move_uploaded_file($imagePath,  $storeImage);
            } else {
                $_SESSION['tempImage'] = $imageFileName;
                header("Location: ../../../Pages/Account/reservationSummary.php?action=imageSize");
                exit();
            }
        } else {
            $_SESSION['tempImage'] = $imageFileName;
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
            exit();
        }
    }
    $_SESSION['bookingID'] = $bookingID;
    $_SESSION['payment-amount'] = $paymentAmount;
    $_SESSION['bookingType'] = $bookingType;


    if ($paymentAmount < $downpayment) {
        $_SESSION['tempImage'] = $imageFileName;
        header('Location: ../../../../../Pages/Account/reservationSummary.php?action=lessAmount');
        exit();
    }

    if (!is_dir($storeProofPath)) {
        mkdir($storeProofPath, 0755, true);
    }


    $paymentSentID = 5;

    $conn->begin_transaction();
    try {
        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedbooking
            SET downpaymentImage = ?, paymentStatus = ?
            WHERE bookingID = ? ");
        $downpaymentImageQuery->bind_param("sii", $imageFileName, $paymentSentID,  $bookingID);

        if (!$downpaymentImageQuery->execute()) {
            $conn->rollback();
            $_SESSION['bookingID'] = $bookingID;
            throw new Exception('Error executing the downpayment query');
            error_log('Error: ' . $downpaymentImageQuery->error);
            header('Location: ../../../../../Pages/Account/reservationSummary.php?action=error');
            exit();
        }

        $today = Date('Y-m-d h:i:s');

        $insertPaymentQuery = $conn->prepare("INSERT INTO `payment`(`amount`, `downpaymentImage`, `paymentDate`,  confirmedBookingID) VALUES (?,?,?,?)");
        $insertPaymentQuery->bind_param('dssi', $paymentAmount, $imageFileName, $today, $confirmedBookingID);
        if (!$insertPaymentQuery->execute()) {
            $conn->rollback();
            throw new Exception('Failed Inserting payment');
            error_log('Payment Insertion: ' . $insertPaymentQuery->error);
            header('Location: ../../../../../Pages/Account/reservationSummary.php?action=error');
            exit();
        }

        $receiver = 'Admin';
        $message = 'A payment proof has been uploaded for Booking ID:' . $bookingID . '. Please review and verify the payment.';
        $insertNotificationQuery = $conn->prepare("INSERT INTO notification(receiver, senderID, bookingID, message) VALUES(?,?,?,?)");
        $insertNotificationQuery->bind_param('siis', $receiver, $userID, $bookingID, $message);

        if (!$insertNotificationQuery->execute()) {
            $conn->rollback();
            throw new Exception('Error executing the notification query');
            error_log('Error: ' . $insertNotificationQuery->error);
            header('Location: ../../../../../Pages/Account/reservationSummary.php?action=error');
            exit();
        }
        $conn->commit();
        unset($_SESSION['payment-amount']);
        unset($_SESSION['bookingType']);
        unset($_SESSION['bookingID']);
        $insertNotificationQuery->close();

        header("Location: ../../../Pages/Account/bookingHistory.php?action=paymentSuccess");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['bookingType'] = $bookingType;
        $_SESSION['bookingID'] = $bookingID;
        error_log("Error " . $e->getMessage());
        header("Location: ../../../Pages/Account/reservationSummary.php?action=error");
        exit();
    }
}
