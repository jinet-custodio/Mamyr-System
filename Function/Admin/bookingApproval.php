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





// $totalCost = $data['totalCost'];
// $serviceID = $data['serviceID'];
// $packageID = $data['packageID'];
// $customPackageID = $data['customPackageID'];

// //Update Booking Table Status
// $updateStatus = "UPDATE bookings 
// SET status = 'Approved'
// WHERE bookingID ='$bookingID'";
// $statusResult = mysqli_query($conn, $updateStatus);

// //Update Service Availability
// if ($serviceID != "") {
//     $updateAvailability = "UPDATE services SET availabilityID = '2' WHERE serviceID = '$serviceID'";
//     $availabilityResult = mysqli_query($conn, $updateAvailability);
// } elseif ($packageID != "") {
//     $updateAvailability = "UPDATE packages SET availabilityID = '2' WHERE packageID = '$packageID'";
//     $availabilityResult = mysqli_query($conn, $updateAvailability);
// } elseif ($customPackageID != "") {
//     $updateAvailability = "UPDATE custompackages SET availabilityID = '2' WHERE customPackageID = '$customPackageID'";
//     $availabilityResult = mysqli_query($conn, $updateAvailability);
// }

// if ($updateStatus && $updateAvailability) {
//     $insertConfirmed = "INSERT INTO confirmedbookings(bookingID, totalCost)
//             VALUES('$bookingID','$totalCost')";
//     $insertConfirmedResult = mysqli_query($conn, $insertConfirmed);
//     if ($insertConfirmedResult) {
//         $_SESSION['success'] = 'Booking Approved Successfully';
//         header('Location: ../../Pages/Admin/booking.php');
//         exit();
//     } else {
//         $_SESSION['error'] = 'The booking request could not be approved. Please try again later.';
//         header('Location: ../../Pages/Admin/viewBooking.php');
//         exit();
//     }
// } else {
//     $_SESSION['error'] = 'The booking and availability can`t be updated';
//     header('Location: ../../Pages/Admin/viewBooking.php');
//     exit();
// }


// $getBookingInfoQuery = $conn->prepare("SELECT b.*, cp.*, cpi.*, bs.*, s.*, er.*, ra.*, ps.* FROM bookings b
    // LEFT JOIN customPackages cp ON b.customPackageID = cp.customPackageID
    // LEFT JOIN custompackageitems cpi ON cp.customPackageID = cp.customPackageID

    // LEFT JOIN bookingServices bs ON b.bookingID = bs.bookingID
    // LEFT JOIN services s ON bs.serviceID = s.serviceID

    // LEFT JOIN entranceRates er ON s.entranceRateID = er.entranceRateID
    // LEFT JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
    // LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID

    // WHERE bs.bookingID = ? ");
    // $getBookingInfoQuery->bind_param('i', $bookingID);
    // $getBookingInfoQuery->execute();
    // $result = $getBookingInfoQuery->get_result();
    // if ($result->num_rows > 0) {
    //     while ($data = $result->fetch_assoc()) {



    //         $serviceID = $data['serviceID'];
    //         $customPackageID = $data['customPackageID'];
    //         if (!empty($serviceID)) {

    //             $serviceType = $data['serviceType'];

    //             if ($serviceType === 'Resort') {
    //                 $updateAvailability = $conn->prepare("UPDATE resortamenities ra
    //                 JOIN services s ON ra.resortServiceID = s.resortServiceID
    //                 JOIN bookingServices bs ON s.serviceID = bs.serviceID 
    //                 SET ra.RSAvailabilityID = ? 
    //                 WHERE s.serviceID = ?");
    //                 $updateAvailability->bind_param("ii", $availabilityID, $serviceID);
    //                 $updateAvailability->execute();
    //             }

    //             if (!empty($data['partnershipServiceID'])) {
    //                 $updateAvailability = $conn->prepare("UPDATE partnershipservices ps
    //                 JOIN services s ON ps.partnershipServiceID = s.partnershipServiceID
    //                 JOIN bookingsServices bs ON s.serviceID = bs.serviceID 
    //                 SET ps.PSAvailabilityID = ? 
    //                 WHERE serviceID = ?");
    //                 $updateAvailability->bind_param("ii", $availabilityID, $serviceID);
    //                 $updateAvailability->execute();
    //             }
    //         }
    //         // elseif (!empty($packageID)) {
    //         //     $updateAvailability = $conn->prepare("UPDATE packages p
    //         //         JOIN bookings b ON p.packageID = b.packageID 
    //         //         SET p.packageAvailability = ? 
    //         //         WHERE packageID = ?");
    //         //     $updateAvailability->bind_param("ii", $availabilityID, $packageID);
    //         //     $updateAvailability->execute();
    //         // } 
    //         elseif (!empty($customPackageID)) {
    //             $selectQuery = "SELECT cpi.serviceID, cpi.packageID 
    //                 FROM custompackages cp
    //                 JOIN bookings b ON cp.customPackageID = b.customPackageID
    //                 JOIN custompackageitems cpi ON cp.customPackageID = cpi.customPackageID
    //                 WHERE cp.customPackageID = '$customPackageID'";
    //             $result = mysqli_query($conn, $selectQuery);
    //             if (mysqli_num_rows($result) > 0) {
    //                 while ($row = mysqli_fetch_assoc($result)) {
    //                     $serviceID = $row['serviceID'];
    //                     $packageID = $row['packageID'];
    //                     if (!empty($serviceID)) {
    //                         $updateAvailability = $conn->prepare("UPDATE resortAmenities ra
    //                             JOIN services s ON ra.resortServiceID = s.resortServiceID
    //                             SET ra.RSAvailabilityID = ? WHERE s.serviceID = ?");
    //                         $updateAvailability->bind_param("ii", $availabilityID, $serviceID);
    //                         $updateAvailability->execute();
    //                     }
    //                     if (!empty($packageID)) {
    //                         $updateAvailability = $conn->prepare("UPDATE packages 
    //                         SET packageAvailability = ? WHERE packageID = ?");
    //                         $updateAvailability->bind_param("ii", $availabilityID, $packageID);
    //                         $updateAvailability->execute();
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // }


    // $getVideoke = "SELECT ra.resortServiceID, s.serviceID, ra.RServiceName, ra.RSPrice FROM services s
    // JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
    // WHERE RServiceName = '$videokeChoice'";
    // $getVideokeResult = mysqli_query($conn, $getVideoke);
    // if (mysqli_num_rows($getVideokeResult) > 0) {
    //     $row = mysqli_fetch_assoc($getVideokeResult);
    //     $serviceID = $row['serviceID'];
    //     $resortServiceID = $row['resortServiceID'];
    //     $price = $row['RSPrice'];
    // }

    // if (!empty($videokeChoice) && $addOns !== "None") {
    //     $insertBooking = $conn->prepare("INSERT INTO bookingservices(bookingID, serviceID, bookingServicePrice)
    //     VALUES(?,?,?)");
    //     $insertBooking->bind_param("iid", $bookingID, $serviceID, $price);
    //     $insertBooking->execute();
    //     $insertBooking->close();


    //     $updateAvailability = $conn->prepare("UPDATE resortAmenities SET RSAvailabilityID = ? WHERE resortServiceID = ?");
    //     $updateAvailability->bind_param("ii", $availabilityID, $resortServiceID);
    //     $updateAvailability->execute();
    //     $updateAvailability->close();
    // } else if ($addOns !== 'None') {
    //     $_SESSION['bookingID'] = $bookingID;
    //     header('Location: ../../Pages/Admin/viewBooking.php?action=videoke');
    //     exit();
    // }

    //   $getData = $conn->prepare("SELECT * FROM bookings WHERE bookingID = ?");
    // $getData->bind_param("i", $bookingID);
    // $getData->execute();
    // $getDataResult = $getData->get_result();
    // if ($getDataResult->num_rows > 0) {
    //     $data = $getDataResult->fetch_assoc();
    //     $totalCost = $data['totalCost'];
    //     $downpayment = $data['downpayment'];
    //     $downpaymentImage = NULL;
    //     $userBalance = $totalCost;
    //     $paymentMethod = $data['paymentMethod'];
    // }
