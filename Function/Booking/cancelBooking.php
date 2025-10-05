<?php

require '../../Config/dbcon.php';
require_once '../../Function/Helpers/statusFunctions.php';

session_start();
$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];

if (isset($_POST['cancelBooking'])) {
    $bookingID = (int) $_POST['bookingID'];
    // $confirmedBookingID = mysqli_real_escape_string($conn, $_POST['confirmedBookingID']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $bookingStatus = mysqli_real_escape_string($conn, $_POST['bookingStatus']);
    $confirmedStatus = mysqli_real_escape_string($conn, $_POST['confirmedStatus']);


    try {
        //GET STATUS ID 
        $IDtoUse = !empty($confirmedStatus) ? $confirmedStatus : $bookingStatus;
        if ($IDtoUse) {
            $getStatusID = $conn->prepare("SELECT * FROM status WHERE statusName = ?");
            $getStatusID->bind_param('s', $IDtoUse);
            if (!$getStatusID->execute()) {
                throw new Exception('Error ' . $getStatusID->error);
            }
            $getStatusIDResult = $getStatusID->get_result();
            if ($getStatusIDResult->num_rows > 0) {
                $row = $getStatusIDResult->fetch_assoc();

                $statusID = $row['statusID'];
            }
        }
        //Check if booking exist
        $checkBooking = $conn->prepare("SELECT *  FROM booking  WHERE bookingID = ? AND userID = ?");
        $checkBooking->bind_param("ii", $bookingID,  $userID);
        if (!$checkBooking->execute()) {
            throw new Exception('Error ' . $checkBooking->error);
        }

        $resultBooking = $checkBooking->get_result();
        if ($resultBooking->num_rows === 0) {
            error_log("Booking not found or doesn't match status/user.");
            header("Location: ../../Pages/Account/bookingHistory.php?action=Error2");
            exit();
        }

        $cancelledStatus = getStatuses($conn, 4);
        $cancelledStatusID = $cancelledStatus['statusID'];

        $cancelBooking = $conn->prepare("UPDATE booking SET bookingStatus = ? WHERE bookingID = ?  ");
        $cancelBooking->bind_param("ii", $cancelledStatusID, $bookingID);


        $resultBooking->free();
        $checkBooking->close();
    } catch (Exception $e) {
        error_log('Error' . $e->getMessage());
    }





    if ($cancelBooking->execute()) {

        $receiver = 'Admin';
        $message = 'A customer has cancelled a' . strtolower($bookingType) . ' booking.';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, senderID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        $insertBookingNotificationRequest->execute();
        // header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Cancelled&bookingID=$bookingID");
        header("Location: ../../Pages/Account/bookingHistory.php?action=Cancelled");
        $cancelBooking->close();
    } else {
        header("Location: ../../Pages/Account/bookingHistory.php?action=Error3");
        exit();
    }
} else {
    header("Location: ../../Pages/Account/bookingHistory.php?action=Error1");
    exit();
}
