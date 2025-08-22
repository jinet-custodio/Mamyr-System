<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


if (isset($_POST['submitDownpaymentImage'])) {
    $bookingID = (int) $_POST['bookingID'];
    $imageMaxSize = 64 * 1024 * 1024;
    $imageData = null;
    $storeProofPath = __DIR__ . '/../../../Assets/Images/PaymentProof/';

    if (!is_dir($storeProofPath)) {
        mkdir($storeProofPath, 0755, true);
    }

    if (isset($_FILES['downpaymentPic']) && is_uploaded_file($_FILES['downpaymentPic']['tmp_name'])) {
        if ($_FILES['downpaymentPic']['size'] <= $imageMaxSize) {
            $imagePath = $_FILES['downpaymentPic']['tmp_name'];
            $imageFileName = $imageData = $_FILES['downpaymentPic']['name'];
            $storeImage = $storeProofPath . $imageFileName;
            move_uploaded_file($imagePath,  $storeImage);
        } else {
            $_SESSION['bookingID'] = $bookingID;
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageSize");
            exit();
        }
    } else {
        $defaultImagePath = '../../../Assets/Images/ProofPayment/defaultDownpayment.png';
        if (file_exists($defaultImagePath)) {
            $imageData = file_get_contents($defaultImagePath);
        }
    }

    if ($imageData !== NULL) {
        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedBookings
            SET downpaymentImage = ?
            WHERE bookingID = ? ");
        $null = NULL;
        $downpaymentImageQuery->bind_param("si", $imageData, $bookingID);

        if ($downpaymentImageQuery->execute()) {

            $receiver = 'Admin';
            $message = 'A payment proof has been uploaded for Booking ID:' . $bookingID . '. Please review and verify the payment.';

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
