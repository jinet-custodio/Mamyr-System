<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);


if (isset($_POST['submitDownpaymentImage'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $imageMaxSize = 64 * 1024 * 1024;
    if (isset($_FILES['downpaymentPic']) &&  $_FILES['downpaymentPic']['tmp_name']) {
        if ($_FILES['downpaymentPic']['size'] <= $imageMaxSize) {
            $imageData = file_get_contents($_FILES['downpaymentPic']['tmp_name']);
            $imageData = mysqli_real_escape_string($conn, $imageData);
        } else {
            $_SESSION['bookingID'] = $bookingID;
            header("Location: ../../../Pages/Customer/Account/reservationSummary.php?action=imageSize");
        }
    } else {
        $defaultDownpaymentImage = '../../../Assets/Images/defaultDownpayment.png';
        if (file_exists($defaultDownpaymentImage)) {
            $imageData = file_get_contents($defaultDownpaymentImage);
            $imageData = mysqli_real_escape_string($conn, $imageData);
        } else {
            $imageData = NULL;
        }
    }


    if ($imageData !== NULL) {
        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedBookings
            SET downpaymentImage = ?
            WHERE bookingID = ? ");
        $null = NULL;
        $downpaymentImageQuery->bind_param("bi", $null, $bookingID);
        $downpaymentImageQuery->send_long_data(0, $imageData);
        if ($downpaymentImageQuery->execute()) {
            header("Location: ../../../Pages/Customer/Account/bookingHistory.php?action=paymentSuccess");
            $downpaymentImageQuery->close();
        }
    } else {
        $_SESSION['bookingID'] = $bookingID;
        header("Location: ../../../Pages/Customer/Account/reservationSummary.php?action=imageError");
        $downpaymentImageQuery->close();
    }
} else {
    $_SESSION['bookingID'] = $bookingID;
    header("Location: ../../../Pages/Customer/Account/reservationSummary.php?action=imageFailed");
}
