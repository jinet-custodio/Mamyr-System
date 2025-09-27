<?php

require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');

$userID = (int) $_SESSION['userID'];
$userRole = (int) $_SESSION['userRole'];


function getMessageReceiver($userRoleID)
{
    switch ($userRoleID) {
        case 1:
            $receiver = 'Customer';
            break;
        case 2:
            $receiver = 'Partner';
            break;
        case 3:
            $receiver = 'Admin';
            break;
        default:
            $receiver = 'Customer';
    }
    return $receiver;
}


$getAdminName = $conn->prepare("SELECT fullName FROM admin WHERE userID = ?");
$getAdminName->bind_param('i', $userID);
if (!$getAdminName->execute()) {
    error_log("Failed Executing Admin Query. Error: " . $getAdminName->error);
}

$result = $getAdminName->get_result();

if ($result->num_rows > 0) {
    error_log('NO DATA  ' . $userID);
    $approvedBy = 'Unknown';
}

$data = $result->fetch_assoc();

$approvedBy = $data['fullName'];



//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $customPackageID = (int) $_POST['customPackageID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $serviceIDs = [];
    $serviceIDs = !empty($_POST['serviceIDs']) ? array_map('trim',   $_POST['serviceIDs']) : [];
    $notes = isset($_POST['approvalNotes']) ? mysqli_real_escape_string($conn, $_POST['approvalNotes']) : 'N/A';
    //*Date and Time
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    // $discountAmount = mysqli_real_escape_string($conn, $_POST['discountAmount']);
    // $rawOriginalBill = mysqli_real_escape_string($conn, $_POST['originalBill']);


    // $discount = (float) str_replace(['₱', ','], '', $discountAmount);
    // $originalBill = (float) str_replace(['₱', ','], '', $rawOriginalBill);
    $rawFinalBill = mysqli_real_escape_string($conn, $_POST['finalBill']);
    $finalBill = (float) str_replace(['₱', ','], '', $rawFinalBill);

    //* Options in approval
    $selectedOption = mysqli_real_escape_string($conn, $_POST['adjustOption']) ?? '';

    $discountAmount = 0.00;
    if ($selectedOption === 'editBill') {
        $editedFinalBill = floatval($_POST['editedFinalBill']);
        $finalBill =  ($editedFinalBill !== $finalBill && $editedFinalBill !== 0) ? $editedFinalBill : $finalBill;
    } elseif ($selectedOption === 'discount') {
        $discountAmount = floatval($_POST['discountAmount']);
        $finalBill = $finalBill - $discountAmount;
    }

    $applyAdditionalCharge = mysqli_real_escape_string($conn, $_POST['applyAdditionalCharge']) ?? '';
    $additionalCharge = !empty($applyAdditionalCharge) ? floatval($_POST['additionalCharge']) : 0.00;


    if ($bookingType === 'Event') {
        $rawVenuePrice = mysqli_real_escape_string($conn, $_POST['venuePrice']);
        $rawTotalFoodPrice = mysqli_real_escape_string($conn, $_POST['foodPriceTotal']);
        $venuePrice = (float) str_replace(['₱', ','], '', $rawVenuePrice) ?? 0;
        $foodPriceTotal = (float) str_replace(['₱', ','], '', $rawTotalFoodPrice) ?? 0;
        // $foodIDs = !empty($_POST['foodIDs']) ? array_map('trim',  $_POST['foodIDs']) : [];

        $venueName = mysqli_real_escape_string($conn, $_POST['venue']);

        if (stripos($venueName, 'Main') !== false) {
            $miniVenue = 'Mini Function Hall';
            $getMiniIDQuery = $conn->prepare("SELECT ra.resortServiceID, s.serviceID FROM resortamenity ra 
            LEFT JOIN service s ON ra.resortServiceID = s.resortServiceID
            WHERE ra.RServiceName = ?");
            $getMiniIDQuery->bind_param('s', $miniVenue);
            if (!$getMiniIDQuery->execute()) {
                error_log('Error getting mini function hall ID' . $getMiniIDQuery->error);
            }

            $result = $getMiniIDQuery->get_result();
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $serviceIDs[] = $data['serviceID'];
            }
        }
    }

    $availabilityID = 2;
    $conn->begin_transaction();
    try {
        $getServicesQuery = $conn->prepare("SELECT * FROM service WHERE serviceID = ?");


        //* Insert this to unavailable dates
        foreach ($serviceIDs as $serviceID) {
            $getServicesQuery->bind_param("i", $serviceID);
            if (!$getServicesQuery->execute()) {
                throw new Exception("Failed to fetch service for ID: $serviceID");
            }

            $getServicesQueryResult = $getServicesQuery->get_result();
            if ($getServicesQueryResult->num_rows === 0) {
                throw new Exception("No service found for ID: $serviceID");
            }

            $row = $getServicesQueryResult->fetch_assoc();
            $serviceType = $row['serviceType'];

            switch ($serviceType) {
                case 'Resort':
                    $resortServiceID = $row['resortServiceID'];
                    $insertToUnavailableDates = $conn->prepare("INSERT INTO serviceunavailabledate(resortServiceID, unavailableStartDate, unavailableEndDate) VALUES (?, ?, ?)");
                    $insertToUnavailableDates->bind_param('iss', $resortServiceID, $startDate, $endDate);
                    if (!$insertToUnavailableDates->execute()) {
                        throw new Exception("Failed to insert unavailable date for resort service ID: $resortServiceID");
                    }
                    $insertToUnavailableDates->close();
                    break;

                case 'Partner':
                    $partnershipServiceID = $row['partnershipServiceID'];
                    $insertToUnavailableDates = $conn->prepare("INSERT INTO serviceunavailabledate(partnershipServiceID, unavailableStartDate, unavailableEndDate) VALUES (?, ?, ?)");
                    $insertToUnavailableDates->bind_param('iss', $partnershipServiceID, $startDate, $endDate);
                    if (!$insertToUnavailableDates->execute()) {
                        throw new Exception("Failed to insert unavailable date for partner service ID: $partnershipServiceID");
                    }
                    $insertToUnavailableDates->close();
                    break;

                default:
                    throw new Exception("Unknown service type: $serviceType for service ID: $serviceID");
            }
        }

        //Update Booking Table Status
        $approvedStatus = 2;
        $approvedDate = date('Y-m-d h:i:s');
        $updateStatus = $conn->prepare("UPDATE booking SET bookingStatus = ?, approvedBy = ?, approvedDate =?  WHERE bookingID = ?");
        $updateStatus->bind_param("sssi", $approvedStatus, $approvedBy, $approvedDate, $bookingID);
        if (!$updateStatus->execute()) {
            throw new Exception("Failed updating the status for bookingID: $bookingID");
        }

        $startDateObj = new DateTime($startDate);
        $startDateObj->modify('-1 day');
        $downpaymentDueDate = $startDateObj->format('Y-m-d H:i:s');

        //Insert into Confirmed Booking
        $insertConfirmed = $conn->prepare("INSERT INTO confirmedbooking(bookingID, discountAmount, confirmedFinalBill, userBalance, downpaymentDueDate, paymentDueDate, notes)
            VALUES(?,?,?,?,?,?,?)");
        $insertConfirmed->bind_param(
            "idddsss",
            $bookingID,
            $discountAmount,
            $finalBill,
            $finalBill,
            $downpaymentDueDate,
            $startDate,
            $notes,
        );

        if (!$insertConfirmed->execute()) {
            throw new Exception("Failed to insert in confirmed booking table.");
        }

        $receiver = getMessageReceiver($userRoleID);
        $message = 'Your ' . $bookingType . ' booking has been approved successfully.';
        $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, userID, message, receiver) VALUES(?,?,?,?)");
        $insertNotification->bind_param('iiss', $bookingID, $userID, $message, $receiver);

        if (!$insertNotification->execute()) {
            throw new Exception("Failed to insert in notifcation table");
        }

        $conn->commit();
        $updateStatus->close();
        $insertConfirmed->close();
        $insertNotification->close();

        unset($_SESSION['bookingID']);
        header('Location: ../../Pages/Admin/booking.php?action=success');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error " . $e->getMessage());
        header("Location: ../../Pages/Admin/viewBooking.php?error=exception&message=" . urlencode($e->getMessage()));
        exit();
    }
}



//Reject Button is Click
if (isset($_POST['rejectBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $bookingStatusID = (int) $_POST['bookingStatus'];
    $message = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $userRoleID = (int) $_POST['userRoleID'];
    $conn->begin_transaction();
    try {
        $bookingQuery = $conn->prepare("SELECT * FROM booking WHERE bookingID = ? AND bookingStatus = ?");
        $bookingQuery->bind_param("is", $bookingID, $bookingStatusID);
        $bookingQuery->execute();
        $result = $bookingQuery->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Booking does not exist or has already been processed.");
        }

        $rejectedStatus = 3;
        $updateStatus = $conn->prepare("UPDATE booking SET bookingStatus = ? WHERE bookingID = ?");
        $updateStatus->bind_param("ii", $rejectedStatus, $bookingID);

        if (!$updateStatus->execute()) {
            throw new Exception("Failed to update booking status.");
        }

        $receiver = getMessageReceiver($userRoleID);

        $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, userID, message, receiver) VALUES(?,?,?,?)");
        $insertNotification->bind_param('iiss', $bookingID, $userID, $message, $receiver);

        if (!$insertNotification->execute()) {
            throw new Exception("Failed to insert notification.");
        }

        $conn->commit();
        $updateStatus->close();
        $insertNotification->close();
        unset($_SESSION['bookingID']);
        header('Location: ../../Pages/Admin/booking.php?action=rejectedSuccess');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Booking rejection error: " . $e->getMessage());
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewBooking.php?action=rejectionFailed');
        exit();
    }
} else {
    echo "<script>
            alert('Error');
            window.location.href = '../../Pages/Admin/booking.php';
        </script>";
    exit();
}
