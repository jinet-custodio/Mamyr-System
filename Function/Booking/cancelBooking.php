<?php

require '../../Config/dbcon.php';


session_start();
$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];

if (isset($_POST['cancelBooking'])) {
    $bookingID = (int) $_POST['bookingID'];
    // $confirmedBookingID = mysqli_real_escape_string($conn, $_POST['confirmedBookingID']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $bookingStatus = mysqli_real_escape_string($conn, $_POST['bookingStatus']);
    $confirmedStatus = mysqli_real_escape_string($conn, $_POST['confirmedStatus']);


    $getStatusID = $conn->prepare("SELECT * FROM statuses WHERE statusName = ?");

    //GET STATUS ID 
    if (!empty($bookingStatus) && empty($confirmedStatus)) {
        $getStatusID->bind_param('s', $bookingStatus);
        $getStatusID->execute();
        $getStatusIDResult = $getStatusID->get_result();
        if ($getStatusIDResult->num_rows > 0) {
            $row = $getStatusIDResult->fetch_assoc();

            $statusID = $row['statusID'];
        }
    } elseif (!empty($confirmedStatus) && !empty($bookingStatus)) {
        $getStatusID->bind_param('s', $confirmedStatus);
        $getStatusID->execute();
        $getStatusIDResult = $getStatusID->get_result();
        if ($getStatusIDResult->num_rows > 0) {
            $row = $getStatusIDResult->fetch_assoc();

            $statusID = $row['statusID'];
        }
    }

    $checkBooking = $conn->prepare("SELECT *  FROM bookings 
    WHERE bookingID = ?  AND bookingStatus = ? AND userID = ?");

    $checkBooking->bind_param("iii", $bookingID,  $statusID, $userID);
    $checkBooking->execute();
    $resultBooking = $checkBooking->get_result();
    if ($resultBooking->num_rows > 0) {
        $cancelledStatusID = 4;
        $cancelBooking = $conn->prepare("UPDATE bookings
        SET bookingStatus = ?
        WHERE bookingID = ?  ");
        $cancelBooking->bind_param("ii", $cancelledStatusID, $bookingID);
        if ($cancelBooking->execute()) {

            $receiver = 'Admin';
            $message = 'A customer has cancelled a' . strtolower($bookingType) . ' booking.';
            $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
            $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
            $insertBookingNotificationRequest->execute();
            // header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Cancelled&bookingID=$bookingID");
            header("Location: ../../Pages/Account/bookingHistory.php?action=Cancelled");
            $cancelBooking->close();
        } else {
            header("Location: ../../Pages/Account/bookingHistory.php?action=Error");
            exit();
        }
    } else {
        header("Location: ../../Pages/Account/bookingHistory.php?action=Error");
        exit();
    }
} else {
    header("Location: ../../Pages/Account/bookingHistory.php?action=Error");
    exit();
}
