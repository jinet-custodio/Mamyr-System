<?php

require '../../Config/dbcon.php';
session_start();

//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $bookingStatus = mysqli_real_escape_string($conn, $_POST['bookingStatus']);
    date_default_timezone_set('Asia/Manila');
    $startDate = date('Y-m-d');


    $query = "SELECT b.*, s.*, cp.*, rs.*, ps.* FROM bookings b
    LEFT JOIN services s ON b.serviceID = s.serviceID
    LEFT JOIN customPackages cp ON b.customPackageID = cp.customPackageID
    LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID
    LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
    WHERE bookingID = '$bookingID'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $totalCost = $data['totalCost'];
        $serviceID = $data['serviceID'];
        $packageID = $data['packageID'];
        $customPackageID = $data['customPackageID'];

        //Update Booking Table Status
        $updateStatus = "UPDATE bookings 
        SET status = 'Approved'
        WHERE bookingID ='$bookingID'";
        $statusResult = mysqli_query($conn, $updateStatus);

        //Update Service Availability
        if ($serviceID != "") {
            $updateAvailability = "UPDATE services SET availabilityID = '2' WHERE serviceID = '$serviceID'";
            $availabilityResult = mysqli_query($conn, $updateAvailability);
        } elseif ($packageID != "") {
            $updateAvailability = "UPDATE packages SET availabilityID = '2' WHERE packageID = '$packageID'";
            $availabilityResult = mysqli_query($conn, $updateAvailability);
        } elseif ($customPackageID != "") {
            $updateAvailability = "UPDATE custompackages SET availabilityID = '2' WHERE customPackageID = '$customPackageID'";
            $availabilityResult = mysqli_query($conn, $updateAvailability);
        }



        if ($statusResult && $availabilityResult) {
            $insertConfirmed = "INSERT INTO confirmedbookings(bookingID, totalCost)
            VALUES('$bookingID','$totalCost')";
            $insertConfirmedResult = mysqli_query($conn, $insertConfirmed);
            if ($insertConfirmedResult) {
                $_SESSION['success'] = 'Booking Approved Successfully';
                header('Location: ../../Pages/Admin/booking.php');
                exit();
            } else {
                $_SESSION['error'] = 'The booking request could not be approved. Please try again later.';
                header('Location: ../../Pages/Admin/viewBooking.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'The booking and availability can`t be updated';
            header('Location: ../../Pages/Admin/viewBooking.php');
            exit();
        }
    }
}


//Reject Button is Click
if (isset($_POST['rejectBtn'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $bookingStatus = mysqli_real_escape_string($conn, $_POST['bookingStatus']);
    date_default_timezone_set('Asia/Manila');
    $startDate = date('d-m-Y');;

    $query = "SELECT * FROM bookings 
    WHERE bookingID = '$bookingID' AND status ='$bookingStatus'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $updateStatus = "UPDATE bookings 
        SET status = 'Cancelled'
        WHERE bookingID ='$bookingID'";
        $result = mysqli_query($conn, $updateStatus);
        if ($result) {
            $_SESSION['success'] = 'The request has been cancelled successfully.';
            header('Location: ../../Pages/Admin/booking.php');
            exit();
        } else {
            $_SESSION['error'] = 'The request could not be cancelled. Please try again later.';
            header('Location: ../../Pages/Admin/booking.php');
            exit();
        }
    }
}
