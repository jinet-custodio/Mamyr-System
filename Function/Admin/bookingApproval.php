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
    $customPackageID = (int) $_POST['customPackageID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $serviceIDs = !empty($_POST['serviceIDs']) ? array_map('trim',   $_POST['serviceIDs']) : [];
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    $discountAmount = mysqli_real_escape_string($conn, $_POST['discountAmount']);
    $rawOriginalBill = mysqli_real_escape_string($conn, $_POST['originalBill']);
    $rawFinalBill = mysqli_real_escape_string($conn, $_POST['originalBill']);
    $rawVenuePrice = mysqli_real_escape_string($conn, $_POST['venuePrice']);
    $rawTotalFoodPrice = mysqli_real_escape_string($conn, $_POST['foodPriceTotal']);

    $discount = (float) str_replace(['₱', ','], '', $discountAmount);
    $originalBill = (float) str_replace(['₱', ','], '', $rawOriginalBill);
    $finalBill = (float) str_replace(['₱', ','], '', $rawFinalBill);
    $venuePrice = (float) str_replace(['₱', ','], '', $rawVenuePrice) ?? 0;
    $foodPriceTotal = (float) str_replace(['₱', ','], '', $rawTotalFoodPrice) ?? 0;
    $foodPrices = !empty($_POST['foodPrice']) ? array_map('trim',  $_POST['foodPrice']) : [];


    if ($bookingType === 'Event') {
        $conn->begin_transaction();
        try {
            $newTotalFoodPrice = 0.00;
            error_log("Starting food price update loop. Count: " . count($foodPrices));

            foreach ($foodPrices as $name => $price) {
                error_log("Processing food item: $name with price $price");

                $foodName = ucfirst($name);
                $foodPrice = (float) $price;
                $query = $conn->prepare("SELECT mi.foodPrice, mi.foodName, mi.foodItemID, 
                cpi.quantity, cpi.servicePrice,
                cp.customPackageID, cp.customPackageTotalPrice
                FROM `menuitem` mi
                LEFT JOIN custompackageitem cpi ON mi.foodItemID = cpi.foodItemID
                LEFT JOIN custompackage cp ON cpi.customPackageID = cp.customPackageID
                WHERE `foodName` = ? AND cpi.customPackageID = ?");
                $query->bind_param('si', $name, $customPackageID);

                if (!$query->execute()) {
                    throw new Exception('Error in selecting a menu in the menuitem table for food = ' . $foodName);
                }

                $result = $query->get_result();
                if ($result->num_rows === 0) {
                    throw new Exception('Error in fetching a menu in the menuitem table for food = ' . $foodName);
                }

                while ($data = $result->fetch_assoc()) {
                    $foodItemID = intval($data['foodItemID']);
                    $storedServicePrice = floatval($data['servicePrice']);
                    $storedQuantity = intval($data['quantity']);
                    $newTotalFoodPrice += $foodPrice;

                    if (abs($storedServicePrice - $foodPrice) > 0.01) {
                        $updateServicePrice = $conn->prepare("UPDATE `custompackageitem` SET `servicePrice`= ? WHERE foodItemID = ? AND customPackageID = ?");
                        $updateServicePrice->bind_param("dii", $foodPrice, $foodItemID, $customPackageID);
                        if (!$updateServicePrice->execute()) {
                            throw new Exception("Error updating the service price for foodItemID " . $foodItemID);
                        }
                    }
                }
            } // end foreach

            if (abs($newTotalFoodPrice - $foodPriceTotal) > 0.01) {
                $bill = $newCustomPackageTotalPrice =  $newTotalFoodPrice + $venuePrice;
                $updateTotalPrice = $conn->prepare("UPDATE `custompackage` SET `customPackageTotalPrice`= ? WHERE `customPackageID`= ?");
                $updateTotalPrice->bind_param('di', $newCustomPackageTotalPrice, $customPackageID);
                if (!$updateTotalPrice->execute()) {
                    throw new Exception("Error updating the totalPrice for custom package " .  $customPackageID);
                }
            }

            error_log("Calculated total food price: $newTotalFoodPrice, Submitted: $foodPriceTotal");
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error: " . $e->getMessage());
            $_SESSION['bookingID'] = $bookingID;
            header("Location: ../../Pages/Admin/viewBooking.php");
            exit();
        }
    }

    $discountedFinalBill = $originalBill - $discount;

    $confirmedFinalBill = ($discountedFinalBill === $finalBill) ? $finalBill : $discountedFinalBill;

    $availabilityID = 2;
    $conn->begin_transaction();

    try {

        $getServicesQuery = $conn->prepare("SELECT * FROM service WHERE serviceID = ?");

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
        $updateStatus = $conn->prepare("UPDATE booking SET bookingStatus = ?  WHERE bookingID = ?");
        $updateStatus->bind_param("si", $approvedStatus, $bookingID);
        if (!$updateStatus->execute()) {
            throw new Exception("Failed updating the status for bookingID: $bookingID");
        }

        $startDateObj = new DateTime($startDate);
        $startDateObj->modify('-1 day');
        $downpaymentDueDate = $startDateObj->format('Y-m-d H:i:s');

        //Insert into Confirmed Booking
        $insertConfirmed = $conn->prepare("INSERT INTO confirmedbooking(bookingID, discountAmount, confirmedFinalBill, userBalance, downpaymentDueDate, paymentDueDate)
            VALUES(?,?,?,?,?,?)");
        $insertConfirmed->bind_param(
            "idddss",
            $bookingID,
            $discount,
            $confirmedFinalBill,
            $confirmedFinalBill,
            $downpaymentDueDate,
            $startDate
        );

        if (!$insertConfirmed->execute()) {
            throw new Exception("Failed to insert in confirmed booking table.");
        }

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


        $message = 'The booking has been approved successfully.';
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
