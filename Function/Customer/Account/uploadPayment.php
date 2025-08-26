<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


if (isset($_POST['submitDownpaymentImage'])) {
    $bookingID = (int) $_POST['bookingID'];
    $imageMaxSize = 64 * 1024 * 1024;
    // $imageData = null;
    $storeProofPath = __DIR__ . '/../../../Assets/Images/PaymentProof/';

    if (!is_dir($storeProofPath)) {
        mkdir($storeProofPath, 0755, true);
    }

    if (isset($_FILES['downpaymentPic']) && is_uploaded_file($_FILES['downpaymentPic']['tmp_name'])) {
        if ($_FILES['downpaymentPic']['size'] <= $imageMaxSize) {
            $imagePath = $_FILES['downpaymentPic']['tmp_name'];
            $randomNum = rand(1111, 9999);
            $imageFileName = $randomNum . $_FILES['downpaymentPic']['name'];
            $storeImage = $storeProofPath . $imageFileName;
            move_uploaded_file($imagePath,  $storeImage);
        } else {
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

    if ($imageFileName !== NULL) {
        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedbookings
            SET downpaymentImage = ?
            WHERE bookingID = ? ");
        $null = NULL;
        $downpaymentImageQuery->bind_param("si", $imageFileName, $bookingID);

        if ($downpaymentImageQuery->execute()) {
            $receiver = 'Admin';
            $message = 'A payment proof has been uploaded for Booking ID:' . $bookingID . '. Please review and verify the payment.';
            $insertNotificationQuery = $conn->prepare("INSERT INTO notifications(receiver, userID, bookingID, message) VALUES(?,?,?,?)");
            $insertNotificationQuery->bind_param('siis', $receiver, $userID, $bookingID, $message);

            header("Location: ../../../Pages/Account/bookingHistory.php?action=paymentSuccess");
            $downpaymentImageQuery->close();
        }
    } else {
        $_SESSION['bookingID'] = $bookingID;
        header("Location: ../../../Pages/Account/reservationSummary.php?action=imageError");
        $downpaymentImageQuery->close();
    }
} else {
    $_SESSION['bookingID'] = $bookingID;
    header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
}
