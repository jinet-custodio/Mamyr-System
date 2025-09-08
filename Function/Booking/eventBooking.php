<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');

$userID = intval($_SESSION['userID']);
$userRole = intval($_SESSION['userRole']);

if (isset($_POST['eventBook'])) {
    $bookingType = 'Event';
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
    $rawPaxNumber = intval($_POST['paxNumber']);

    $guestNo = intval(filter_var($rawPaxNumber, FILTER_SANITIZE_NUMBER_INT));

    $venueID = intval($_POST['venueID']);
    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);
    $rawtotalFoodPrice = mysqli_real_escape_string($conn, $_POST['totalFoodPrice']);
    //Date and time
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventStartTime = mysqli_real_escape_string($conn, $_POST['eventStartTime']);
    $eventEndTime = mysqli_real_escape_string($conn, $_POST['eventEndTime']);

    $startDateTimeStr = $eventDate . ' ' . $eventStartTime;
    $endDateTimeStr = $eventDate . ' ' . $eventEndTime;


    $startDateTime = new DateTime($startDateTimeStr);
    $endDateTime = new DateTime($endDateTimeStr);
    $arrivalTime = $startDateTime->format('H:i:s');

    $interval = $startDateTime->diff($endDateTime);


    $hours = $interval->h;
    $minutes = $interval->i;
    $totalMinutes = ($hours * 60) + $minutes;
    $totalHours = $totalMinutes / 60;

    $durationCount = $totalHours . ' hours';

    //Service & Food
    $menuQuantities =  $_POST['quantities'];
    $serviceQuantity = 1;

    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $rawVenuePrice = mysqli_real_escape_string($conn, $_POST['eventVenuePrice']);
    $rawDownpayment = mysqli_real_escape_string($conn, $_POST['downpayment']);

    $additionalCharge = floatval(0);
    $venuePrice = floatval(str_replace(['₱', ','], '', $rawVenuePrice));
    $downpayment = floatval(str_replace(['₱', ','], '', $rawDownpayment));
    $totalFoodPrice = floatval(str_replace(['₱', ','], '', $rawtotalFoodPrice));

    $serviceID = null;

    $getServiceID = $conn->prepare("SELECT * FROM `service` WHERE resortServiceID = ?");
    $getServiceID->bind_param('i', $venueID);

    if ($getServiceID->execute()) {
        $result = $getServiceID->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $serviceID = $row['serviceID'];
        } else {
            error_log("No matching service found for resortServiceID: $venueID");
        }
    } else {
        error_log("Query failed: " . $conn->error);
    }



    $getEventTypeID = $conn->prepare("SELECT * FROM `eventcategory` WHERE categoryName = ?");
    $getEventTypeID->bind_param('s', $eventType);
    if ($getEventTypeID->execute()) {
        $result = $getEventTypeID->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $eventCategoryID = intval($row['categoryID']);
        } {
            error_log("No matching service found for $eventType");
        }
    } else {
        error_log("Query failed: " . $conn->error);
    }
    $totalCost =  $venuePrice +  $totalFoodPrice;

    $conn->begin_transaction();
    try {
        //insert the total of all
        $insertCustomPackage = $conn->prepare("INSERT INTO `custompackage`(`userID`, `eventTypeID`, `customPackageTotalPrice`, `customPackageNotes`) VALUES (?,?,?,?)");
        $insertCustomPackage->bind_param('iids', $userID, $eventCategoryID,  $totalCost, $additionalRequest);

        if (!$insertCustomPackage->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertCustomPackage->error);
        }
        $customPackageID =   $conn->insert_id;

        //insert each item
        $insertCustomPackageItem = $conn->prepare("INSERT INTO `custompackageitem`( `customPackageID`, `foodItemID`, `quantity`,`servicePrice`) VALUES (?,?,?,?)");


        foreach ($menuQuantities as $foodItemID => $itemData) {
            $foodItemID = (int) $foodItemID;
            $quantity = isset($itemData['quantity']) ? (int) $itemData['quantity'] : 0;
            $foodItemPrice = isset($itemData['price']) ? (float) $itemData['price'] : 0.0;

            $servicePrice = $quantity * $foodItemPrice;
            $insertCustomPackageItem->bind_param('iiid', $customPackageID, $foodItemID, $quantity, $servicePrice);

            if (!$insertCustomPackageItem->execute()) {
                $conn->rollback();
                error_log("Error inserting item $foodItemID: " . $insertCustomPackageItem->error);
            }
        }

        $insertServiceVenue = $conn->prepare("INSERT INTO `custompackageitem`( `customPackageID`, `serviceID`, `quantity`, `servicePrice`) VALUES (?,?,?,?)");
        $insertServiceVenue->bind_param('iiid', $customPackageID, $serviceID, $serviceQuantity, $venuePrice);

        if (!$insertServiceVenue->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertServiceVenue->error);
        }

        //insert into booking
        $insertBooking = $conn->prepare("INSERT INTO `booking`(`userID`, `bookingType`, `customPackageID`, `additionalRequest`, `guestCount`, `durationCount`,  `startDate`, `endDate`, `paymentMethod`, `additionalCharge`, `totalCost`, `downpayment`, `arrivalTime`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertBooking->bind_param("isisissssddds", $userID, $bookingType, $customPackageID, $additionalRequest, $guestNo, $durationCount, $startDateTimeStr, $endDateTimeStr, $paymentMethod, $additionalCharge, $totalCost, $downpayment, $arrivalTime);
        if (!$insertBooking->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertBooking->error);
        }

        $conn->commit();
        unset($_SESSION['eventFormData']);
        header("Location: ../../../../Pages/Customer/bookNow.php?action=success");
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error inserting: " . $e->getMessage());
        $_SESSION['eventFormData'] = $_POST;
        header("Location: ../../../../Pages/Customer/eventBookingConfirmation.php?action=errorBooking");
    }
}
