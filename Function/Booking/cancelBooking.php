<?php

require '../../Config/dbcon.php';


session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);

if (isset($_POST['cancelBooking'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    // $confirmedBookingID = mysqli_real_escape_string($conn, $_POST['confirmedBookingID']);
    $status = 1;
    $checkBooking = $conn->prepare("SELECT *  FROM bookings 
    WHERE bookingID = ?  AND bookingStatus = ? AND userID = ?");

    $checkBooking->bind_param("iii", $bookingID,  $status, $userID);
    $checkBooking->execute();
    $resultBooking = $checkBooking->get_result();
    if ($resultBooking->num_rows > 0) {
        $newBookingStatus = 4;
        $cancelBooking = $conn->prepare("UPDATE bookings
        SET bookingStatus = ?
        WHERE bookingID = ?  ");
        $cancelBooking->bind_param("ii", $newBookingStatus, $bookingID);
        if ($cancelBooking->execute()) {
            header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Cancelled&bookingID=$bookingID");
            // header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Cancelled");
            $cancelBooking->close();
        } else {
            header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Error");
            exit();
        }
    } else {
        header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Error");
        exit();
    }
} else {
    header("Location: ../../Pages/Customer/Account/bookingHistory.php?action=Error");
    exit();
}
