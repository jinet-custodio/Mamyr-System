<?php

require '../../Config/dbcon.php';
session_start();

//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $videokeChoice = mysqli_real_escape_string($conn, $_POST['videokeChoice']);
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $bookingStatus = mysqli_real_escape_string($conn, $_POST['bookingStatus']);
    $addOns = mysqli_real_escape_string($conn, $_POST['addOns']);
    $availabilityID = 2;
    $confirmedBookingStatus = 1;
    date_default_timezone_set('Asia/Manila');
    $startDate = date('Y-m-d');


    $query = "SELECT b.*, p.*, cp.*, cpi.*, bs.*, s.*, er.*, ra.*, ps.* FROM bookings b

    LEFT JOIN packages p ON b.packageID = p.packageID

    LEFT JOIN customPackages cp ON b.customPackageID = cp.customPackageID
    LEFT JOIN custompackageitems cpi ON cp.customPackageID = cp.customPackageID

    LEFT JOIN bookingServices bs ON b.bookingID = bs.bookingID
    LEFT JOIN services s ON bs.serviceID = s.serviceID

    LEFT JOIN entranceRates er ON s.entranceRateID = er.entranceRateID
    LEFT JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
    LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID

    WHERE bs.bookingID = '$bookingID'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($data = mysqli_fetch_assoc($result)) {
            $serviceID = $data['serviceID'];
            $customPackageID = $data['customPackageID'];
            $packageID = $data['packageID'];
            //Update Service Availability
            if (!empty($data['serviceID'])) {
                if (!empty($data['partnershipServiceID'])) {
                    $updateAvailability = $conn->prepare("UPDATE partnershipservices ps
                    JOIN services s ON ps.partnershipServiceID = s.partnershipServiceID
                    JOIN bookingsServices bs ON s.serviceID = bs.serviceID 
                    SET ps.PSAvailabilityID = ? 
                    WHERE serviceID = ?");
                    $updateAvailability->bind_param("ii", $availabilityID, $serviceID);
                    $updateAvailability->execute();
                }
                if (!empty($data['resortServiceID'])) {
                    $updateAvailability = $conn->prepare("UPDATE resortamenities ra
                    JOIN services s ON ra.resortServiceID = s.resortServiceID
                    JOIN bookingServices bs ON s.serviceID = bs.serviceID 
                    SET ra.RSAvailabilityID = ? 
                    WHERE s.serviceID = ?");
                    $updateAvailability->bind_param("ii", $availabilityID, $serviceID);
                    $updateAvailability->execute();
                }
            } elseif (!empty($packageID)) {

                $updateAvailability = $conn->prepare("UPDATE packages p
                    JOIN bookings b ON p.packageID = b.packageID 
                    SET p.packageAvailability = ? 
                    WHERE packageID = ?");
                $updateAvailability->bind_param("ii", $availabilityID, $packageID);
                $updateAvailability->execute();
            } elseif (!empty($customPackageID)) {

                $selectQuery = "SELECT cpi.serviceID, cpi.packageID 
                    FROM custompackages cp
                    JOIN bookings b ON cp.customPackageID = b.customPackageID
                    JOIN custompackageitems cpi ON cp.customPackageID = cpi.customPackageID
                    WHERE cp.customPackageID = '$customPackageID'";
                $result = mysqli_query($conn, $selectQuery);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $serviceID = $row['serviceID'];
                        $packageID = $row['packageID'];
                        if (!empty($serviceID)) {
                            $updateAvailability = $conn->prepare("UPDATE resortAmenities ra
                                JOIN services s ON ra.resortServiceID = s.resortServiceID
                                SET ra.RSAvailabilityID = ? WHERE s.serviceID = ?");
                            $updateAvailability->bind_param("ii", $availabilityID, $serviceID);
                            $updateAvailability->execute();
                        }
                        if (!empty($packageID)) {
                            $updateAvailability = $conn->prepare("UPDATE packages 
                            SET packageAvailability = ? WHERE packageID = ?");
                            $updateAvailability->bind_param("ii", $availabilityID, $packageID);
                            $updateAvailability->execute();
                        }
                    }
                }
            }
        }
    }


    $getVideoke = "SELECT ra.resortServiceID, s.serviceID, ra.RServiceName, ra.RSPrice FROM services s
    JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
    WHERE RServiceName = '$videokeChoice'";
    $getVideokeResult = mysqli_query($conn, $getVideoke);
    if (mysqli_num_rows($getVideokeResult) > 0) {
        $row = mysqli_fetch_assoc($getVideokeResult);
        $serviceID = $row['serviceID'];
        $resortServiceID = $row['resortServiceID'];
        $price = $row['RSPrice'];
    }

    if (!empty($videokeChoice) && $addOns !== "None") {
        $insertBooking = $conn->prepare("INSERT INTO bookingservices(bookingID, serviceID, bookingServicePrice)
        VALUES(?,?,?)");
        $insertBooking->bind_param("iid", $bookingID, $serviceID, $price);
        $insertBooking->execute();
        $insertBooking->close();


        $updateAvailability = $conn->prepare("UPDATE resortAmenities SET RSAvailabilityID = ? WHERE resortServiceID = ?");
        $updateAvailability->bind_param("ii", $availabilityID, $resortServiceID);
        $updateAvailability->execute();
        $updateAvailability->close();
    } else if ($addOns !== 'None') {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewBooking.php?action=videoke');
        exit();
    }


    //Update Booking Table Status
    $newStatus = 2;
    $updateStatus = $conn->prepare("UPDATE bookings SET bookingStatus = ? WHERE bookingID = ?");
    $updateStatus->bind_param("si", $newStatus, $bookingID);
    $updateStatus->execute();


    $getData = $conn->prepare("SELECT * FROM bookings WHERE bookingID = ?");
    $getData->bind_param("i", $bookingID);
    $getData->execute();
    $getDataResult = $getData->get_result();
    if ($getDataResult->num_rows > 0) {
        $data = $getDataResult->fetch_assoc();
        $totalCost = $data['totalCost'];
        $downpayment = $data['downpayment'];
        $downpaymentImage = NULL;
        $userBalance = $totalCost;
        $paymentMethod = $data['paymentMethod'];
    }

    $insertConfirmed = $conn->prepare("INSERT INTO confirmedbookings(bookingID, 
    downpayment, downpaymentImage, CBtotalCost, userBalance, CBpaymentMethod, confirmedBookingStatus)
            VALUES(?,?,?,?,?,?, ?)");
    $insertConfirmed->bind_param(
        "isbddsi",
        $bookingID,
        $downpayment,
        $downpaymentImage,
        $totalCost,
        $userBalance,
        $paymentMethod,
        $confirmedBookingStatus
    );
    if ($insertConfirmed->execute()) {
        unset($_SESSION['bookingID']);
        header('Location: ../../Pages/Admin/booking.php?action=success');
        exit();
    } else {
        $_SESSION['error'] = 'The booking request could not be approved. Please try again later.';
        header('Location: ../../Pages/Admin/booking.php?action=error');
        exit();
    }
}



//Reject Button is Click
if (isset($_POST['rejectBtn'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
    $bookingStatus = mysqli_real_escape_string($conn, $_POST['bookingStatus']);

    $bookingQuery = $conn->prepare("SELECT bookings.*, statuses.statusName FROM bookings 
    JOIN statuses ON bookings.bookingStatus = statuses.statusID
    WHERE bookingID = ? AND statusName = ?");
    $bookingQuery->bind_param("is", $bookingID, $bookingStatus);
    $bookingQuery->execute();
    $result = $bookingQuery->get_result();

    if ($result->num_rows > 0) {
        $newStatus = 3;

        $updateStatus = $conn->prepare("UPDATE bookings 
        SET bookingStatus = ?
        WHERE bookingID = ? ");
        $updateStatus->bind_param("ii", $newStatus, $bookingID);

        if ($updateStatus->execute()) {
            header('Location: ../../Pages/Admin/booking.php?action=rejected');
            $updateStatus->close();
        } else {
            header('Location: ../../Pages/Admin/booking.php?action=error');
            exit();
        }
    } else {
        echo "<script>
            alert('Not existing');
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
