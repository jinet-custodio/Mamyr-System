<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


if (isset($_POST['submitDownpaymentImage'])) {

    $bookingID = (int) $_POST['bookingID'];
    $confirmedBookingID = (int) $_POST['confirmedBookingID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $paymentAmount = (float) $_POST['payment-amount'];
    $downpayment = (float) $_POST['downpayment'];

    $imageMaxSize = 5 * 1024 * 1024; // 5 MB max
    $allowedExt = ['jpg', 'jpeg', 'png'];

    $storeProofPath = __DIR__ . '/../../../Assets/Images/PaymentProof/';
    $tempUploadPath = __DIR__ . '/../../../Assets/Images/TempUploads/';

    if (!is_dir($storeProofPath)) mkdir($storeProofPath, 0755, true);
    if (!is_dir($tempUploadPath)) mkdir($tempUploadPath, 0755, true);

    $_SESSION['bookingID'] = $bookingID;
    $_SESSION['payment-amount'] = $paymentAmount;
    $_SESSION['bookingType'] = $bookingType;

    if (isset($_FILES['downpaymentPic']) && is_uploaded_file($_FILES['downpaymentPic']['tmp_name'])) {

        $originalName = $_FILES['downpaymentPic']['name'];
        $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $imageSize = $_FILES['downpaymentPic']['size'];

        if (!in_array($imageExt, $allowedExt)) {
            unset($_SESSION['tempImage']);
            header("Location: ../../../Pages/Account/reservationSummary.php?action=extError");
            exit();
        }

        if ($imageSize > $imageMaxSize) {
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageSize");
            exit();
        }

        $tempFileName = 'temp_' . uniqid() . '_' . $bookingID . '.' . $imageExt;
        $tempFilePath = $tempUploadPath . $tempFileName;

        if (!move_uploaded_file($_FILES['downpaymentPic']['tmp_name'], $tempFilePath)) {
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
            exit();
        }

        $_SESSION['tempImage'] = $tempFileName;
    } else if (!empty($_SESSION['tempImage'])) {
        $tempFileName = $_SESSION['tempImage'];
    } else {
        header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
        exit();
    }

    if ($paymentAmount < $downpayment) {
        $_SESSION['uploadError'] = 'Amount is less than required downpayment.';
        header('Location: ../../../Pages/Account/reservationSummary.php?action=lessAmount');
        exit();
    }

    $finalFileName = str_pad($bookingID, 4, '0', STR_PAD_LEFT) . '_' . basename($_SESSION['tempImage']);
    $finalFilePath = $storeProofPath . $finalFileName;

    rename($tempUploadPath . $_SESSION['tempImage'], $finalFilePath);

    unset($_SESSION['tempImage']);


    $paymentSentID = 5;
    $userID = $_SESSION['userID'] ?? null;
    if (!$userID) {
        header("Location: ../../../Pages/Account/reservationSummary.php?action=noUser");
        exit();
    }

    $conn->begin_transaction();
    try {
        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedbooking
                                                    SET downpaymentImage = ?, paymentStatus = ?
                                                    WHERE bookingID = ?
                                                ");
        $downpaymentImageQuery->bind_param("sii", $finalFileName, $paymentSentID, $bookingID);
        $downpaymentImageQuery->execute();

        $today = date('Y-m-d H:i:s');
        $insertPaymentQuery = $conn->prepare("INSERT INTO payment (amount, downpaymentImage, paymentDate, confirmedBookingID) VALUES (?, ?, ?, ?)");
        $insertPaymentQuery->bind_param('dssi', $paymentAmount, $finalFileName, $today, $confirmedBookingID);
        $insertPaymentQuery->execute();

        $receiver = 'Admin';
        $message = 'A payment proof has been uploaded for Booking ID: ' . $bookingID . '. Please verify.';
        $insertNotificationQuery = $conn->prepare("INSERT INTO notification (receiver, senderID, bookingID, message) VALUES (?, ?, ?, ?) ");
        $insertNotificationQuery->bind_param('siis', $receiver, $userID, $bookingID, $message);
        $insertNotificationQuery->execute();

        $updateUnavailableService = $conn->prepare("UPDATE serviceunavailabledate SET expiresAt = NULL WHERE bookingID = ?");
        $updateUnavailableService->bind_param('i', $bookingID);
        $updateUnavailableService->execute();

        $conn->commit();

        unset($_SESSION['payment-amount'], $_SESSION['bookingType'], $_SESSION['bookingID']);
        header("Location: ../../../Pages/Account/bookingHistory.php?action=paymentSuccess");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        header("Location: ../../../Pages/Account/reservationSummary.php?action=error");
        exit();
    }
}
