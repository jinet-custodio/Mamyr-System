<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


if (isset($_POST['submitDownpaymentImage'])) {
    $bookingID = (int) $_POST['bookingID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $imageMaxSize = 1 * 1024 * 1024;
    // $imageData = null;
    $storeProofPath = __DIR__ . '/../../../Assets/Images/PaymentProof/';

    if (!is_dir($storeProofPath)) {
        mkdir($storeProofPath, 0755, true);
    }

    if (isset($_FILES['downpaymentPic']) && is_uploaded_file($_FILES['downpaymentPic']['tmp_name'])) {
        if ($_FILES['downpaymentPic']['size'] <= $imageMaxSize) {
            $imagePath = $_FILES['downpaymentPic']['tmp_name'];
            $randomNum =  str_pad($bookingID, 4, '0', STR_PAD_LEFT);
            $imageFileName = $randomNum . $_FILES['downpaymentPic']['name'];
            $storeImage = $storeProofPath . $imageFileName;
            move_uploaded_file($imagePath,  $storeImage);
        } else {
            $_SESSION['bookingType'] = $bookingType;
            $_SESSION['bookingID'] = $bookingID;
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageSize");
            exit();
        }
    } else {
        $defaultImage = 'defaultDownpayment.png';
        if (file_exists($defaultImage)) {
            $imageFileName = file_get_contents($defaultImage);
        }
    }
    $paymentSentID = 5;
    try {
        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedbooking
            SET downpaymentImage = ?, paymentStatus = ?
            WHERE bookingID = ? ");
        $downpaymentImageQuery->bind_param("sii", $imageFileName, $paymentSentID,  $bookingID);

        if (!$downpaymentImageQuery->execute()) {
            $_SESSION['bookingID'] = $bookingID;
            throw new Exception('Error executing the downpayment query');
            error_log('Error: ' . $downpaymentImageQuery->error);
            header('Location: ../../../../../Pages/Account/reservationSummary.php?action=error');
            exit();
        }


        $receiver = 'Admin';
        $message = 'A payment proof has been uploaded for Booking ID:' . $bookingID . '. Please review and verify the payment.';
        $insertNotificationQuery = $conn->prepare("INSERT INTO notification(receiver, userID, bookingID, message) VALUES(?,?,?,?)");
        $insertNotificationQuery->bind_param('siis', $receiver, $userID, $bookingID, $message);

        if (!$insertNotificationQuery->execute()) {
            $_SESSION['bookingType'] = $bookingType;
            $_SESSION['bookingID'] = $bookingID;
            throw new Exception('Error executing the notification query');
            error_log('Error: ' . $downpaymentImageQuery->error);
            header('Location: ../../../../../Pages/Account/reservationSummary.php?action=error');
            exit();
        }
        $insertNotificationQuery->close();
        $downpaymentImageQuery->close();
        header("Location: ../../../Pages/Account/bookingHistory.php?action=paymentSuccess");
        exit();
    } catch (Exception $e) {
        $_SESSION['bookingType'] = $bookingType;
        $_SESSION['bookingID'] = $bookingID;
        error_log("Error " . $e->getMessage());
        header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
        exit();
    }
}
