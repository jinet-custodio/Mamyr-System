<?php

require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');

$userID = (int) $_SESSION['userID'];
$userRole = (int) $_SESSION['userRole'];
//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];

    $serviceIDs = $_POST['serviceIDs'];
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    $discountAmount = mysqli_real_escape_string($conn, $_POST['discountAmount']);
    $totalCost = mysqli_real_escape_string($conn, $_POST['totalCost']);

    $discount = (float) str_replace(['₱', ','], '', $discountAmount);
    $bill = (float) str_replace(['₱', ','], '', $totalCost);



    if ($discount == '0.00') {
        $finalBill = $bill;
    } else {
        $finalBill = $bill - $discount;
    }


    $availabilityID = 2;

    $getServicesQuery = $conn->prepare("SELECT * FROM services WHERE serviceID = ?");
    foreach ($serviceIDs as $serviceID) {
        $getServicesQuery->bind_param("i", $serviceID);
        $getServicesQuery->execute();
        $getServicesQueryResult = $getServicesQuery->get_result();
        if ($getServicesQueryResult->num_rows > 0) {
            $row = $getServicesQueryResult->fetch_assoc();
            $serviceType = $row['serviceType'];
            if ($serviceType === 'Resort') {
                $resortServiceID = $row['resortServiceID'];
                $insertToUnavailableDates = $conn->prepare("INSERT INTO serviceunavailabledates(resortServiceID, unavailableStartDate, unavailableEndDate)
                VALUES(?,?,?)");
                $insertToUnavailableDates->bind_param('iss', $resortServiceID, $startDate, $endDate);
                $insertToUnavailableDates->execute();
                $insertToUnavailableDates->close();
            } elseif ($serviceType === 'Partner') {
                $partnershipServiceID = $row['partnershipServiceID'];
                $insertToUnavailableDates = $conn->prepare("INSERT INTO serviceunavailabledates(partnershipServiceID, unavailableStartDate, unavailableEndDate)
                VALUES(?,?,?)");
                $insertToUnavailableDates->bind_param('iss', $resortServiceID, $startDate, $endDate);
                $insertToUnavailableDates->execute();
                $insertToUnavailableDates->close();
            }
        }
    }


    //Update Booking Table Status
    $approvedStatus = 2;
    $updateStatus = $conn->prepare("UPDATE bookings SET bookingStatus = ? WHERE bookingID = ?");
    $updateStatus->bind_param("si", $approvedStatus, $bookingID);
    $updateStatus->execute();
    $updateStatus->close();


    $startDateObj = new DateTime($startDate);
    $startDateObj->modify('-1 day');
    $downpaymentDueDate = $startDateObj->format('Y-m-d H:i:s');


    //Insert into Confirmed Booking
    $insertConfirmed = $conn->prepare("INSERT INTO confirmedbookings(bookingID, discountAmount, confirmedFinalBill, userBalance, downpaymentDueDate, paymentDueDate)
            VALUES(?,?,?,?,?,?)");
    $insertConfirmed->bind_param(
        "idddss",
        $bookingID,
        $discount,
        $finalBill,
        $finalBill,
        $downpaymentDueDate,
        $startDate
    );
    if ($insertConfirmed->execute()) {

        if ($userRoleID === 1) {
            $receiver = 'Customer';
        } elseif ($userRoleID === 2) {
            $receiver = 'Partner';
        } elseif ($userRoleID === 3) {
            $receiver = 'Admin';
        }

        $message = 'Your booking has been approved.';
        $insertNotification = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver) VALUES(?,?,?,?)");
        $insertNotification->bind_param('iiss', $bookingID, $userID, $message, $receiver);
        $insertNotification->execute();
        unset($_SESSION['bookingID']);
        header('Location: ../../Pages/Admin/booking.php?action=success');
        exit();
        $insertConfirmed->close();
        $insertNotification->close();
    } else {
        $_SESSION['bookingID'];
        header('Location: ../../Pages/Admin/booking.php?action=error');
        exit();
    }
}



//Reject Button is Click
if (isset($_POST['rejectBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $bookingStatusID = (int) $_POST['bookingStatus'];
    $message = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $userRoleID = (int) $_POST['userRoleID'];


    $bookingQuery = $conn->prepare("SELECT * FROM bookings WHERE bookingID = ? AND bookingStatus = ?");
    $bookingQuery->bind_param("is", $bookingID,  $bookingStatusID);
    $bookingQuery->execute();
    $result = $bookingQuery->get_result();

    if ($result->num_rows > 0) {
        $rejectedStatus = 3;
        $updateStatus = $conn->prepare("UPDATE bookings 
        SET bookingStatus = ?
        WHERE bookingID = ? ");
        $updateStatus->bind_param("ii", $approvedStatus, $bookingID);

        if ($updateStatus->execute()) {

            if ($userRoleID === 1) {
                $receiver = 'Customer';
            } elseif ($userRoleID === 2) {
                $receiver = 'Partner';
            } elseif ($userRoleID === 3) {
                $receiver = 'Admin';
            }

            $insertNotification = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver) VALUES(?,?,?,?)");
            $insertNotification->bind_param('iiss', $bookingID, $userID, $message, $receiver);
            $insertNotification->execute();
            header('Location: ../../Pages/Admin/booking.php?action=rejected');
            $updateStatus->close();
            $insertNotification->close();
        } else {
            header('Location: ../../Pages/Admin/booking.php?action=error');
            exit();
        }
    } else {
        echo "<script>
            alert('Booking Not existing');
            window.location.href = '../../Pages/Admin/booking.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
            alert('Error');
            window.location.href = '../../Pages/Admin/booking.php';
        </script>";
    exit();
}
