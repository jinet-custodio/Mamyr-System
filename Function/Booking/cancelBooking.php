<?php

require '../../Config/dbcon.php';
require_once '../../Function/Helpers/statusFunctions.php';

session_start();
$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];

if (isset($_POST['cancelBooking'])) {
    $bookingID = (int) $_POST['bookingID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $cancellationReason = intval($_POST['cancellation-reason']);
    $otherReason = !empty($_POST['other-cancellation-reason']) ? mysqli_real_escape_string($conn, $_POST['other-cancellation-reason']) : 'N/A';
    try {

        $expiresAt = null;
        $searchBookingID = $conn->prepare("SELECT bookingID FROM serviceunavailabledate WHERE bookingID = ?");
        $searchBookingID->bind_param('i', $bookingID);
        if (!$searchBookingID->execute()) {
            $conn->rollback();
            throw new Exception("Failed executing (searchBookingID) booking ID: $bookingID");
        }

        $searchID = $searchBookingID->get_result();
        if ($searchID->num_rows > 0) {
            $deleteService = $conn->prepare("DELETE FROM `serviceunavailabledate` WHERE `bookingID`= ?");
            $deleteService->bind_param('i', $bookingID);
            if (!$deleteService->execute()) {
                $conn->rollback();
                throw new Exception("Failed to deleting unavailable date for booking ID: $bookingID");
            }
            $deleteService->close();
        }

        //Check if booking exist
        $checkBooking = $conn->prepare("SELECT *  FROM booking  WHERE bookingID = ? AND userID = ?");
        $checkBooking->bind_param("ii", $bookingID,  $userID);
        if (!$checkBooking->execute()) {
            throw new Exception('Error ' . $checkBooking->error);
        }

        $resultBooking = $checkBooking->get_result();
        if ($resultBooking->num_rows === 0) {
            error_log("Booking not found or doesn't match status/user. bookingID" . $bookingID . "userID:" . $userID);
            header("Location: ../../Pages/Account/bookingHistory.php?action=Error2");
            exit();
        }

        $cancelledStatus = getStatuses($conn, 4);
        $cancelledStatusID = $cancelledStatus['statusID'];

        $cancelBooking = $conn->prepare("UPDATE booking SET bookingStatus = ? WHERE bookingID = ?  ");
        $cancelBooking->bind_param("ii", $cancelledStatusID, $bookingID);


        $insertCancellationReason = $conn->prepare("INSERT INTO `booking_cancellation`(`bookingID`, `userID`, `reasonID`, `otherReason`) VALUES (?,?,?,?)");
        $insertCancellationReason->bind_param('iiis', $bookingID, $userID, $cancellationReason, $otherReason);
        if (!$insertCancellationReason->execute()) {
            error_log("Failed Adding Cancellation Reason" . $bookingID . "userID:" . $userID);
            header("Location: ../../Pages/Account/bookingHistory.php?action=Error3");
            exit();
        }

        $resultBooking->free();
        $checkBooking->close();
    } catch (Exception $e) {
        error_log('Error' . $e->getMessage());
        exit();
    }


    if ($cancelBooking->execute()) {

        $receiver = 'Admin';
        $message = 'A customer has cancelled a' . strtolower($bookingType) . ' booking.';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, senderID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        $insertBookingNotificationRequest->execute();
        header("Location: ../../Pages/Account/bookingHistory.php?action=Cancelled");
        $cancelBooking->close();
        exit();
    } else {
        header("Location: ../../Pages/Account/bookingHistory.php?action=Error3");
        exit();
    }
} else {
    header("Location: ../../Pages/Account/bookingHistory.php?action=Error1");
    exit();
}
