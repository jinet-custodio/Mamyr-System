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
    $guestNo = intval($_POST['guestNo']);
    $pricingID = intval($_POST['pricingID']);
    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);
    $rawtotalFoodPrice = mysqli_real_escape_string($conn, $_POST['totalFoodPrice']);

    //Date and time
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventStartTime = mysqli_real_escape_string($conn, $_POST['eventStartTime']);

    $durationCount = '5 hours';

    //Service & Food & Venue
    $foodList =  $_POST['foodIDs'] ?? [];
    $venueID = intval($_POST['venueID']);
    $partnerIDs = $_POST['additionalServiceSelected'] ?? [];
    $serviceQuantity = 1;

    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']); //Cash or Gcash
    //with ₱ kaya raw nilagay ko guys
    $rawVenuePrice = mysqli_real_escape_string($conn, $_POST['eventVenuePrice']);
    $rawtotalFoodPrice = mysqli_real_escape_string($conn, $_POST['totalFoodPrice']);
    $rawDownpayment = mysqli_real_escape_string($conn, $_POST['downpayment']);
    $rawAdditionalServicePrice = mysqli_real_escape_string($conn, $_POST['additionalServicePrice']);
    $rawTotalCost = mysqli_real_escape_string($conn, $_POST['totalCost']);

    //Without naman 
    $additionalCharge = floatval(0);
    $venuePrice = floatval(str_replace(['₱', ','], '', $rawVenuePrice));
    $downpayment = floatval(str_replace(['₱', ','], '', $rawDownpayment));
    $totalFoodPrice = floatval(str_replace(['₱', ','], '', $rawtotalFoodPrice));
    $additionalServicePrice = floatval(str_replace(['₱', ','], '',  $rawAdditionalServicePrice));
    $totalCost = floatval(str_replace(['₱', ','], '',  $rawTotalCost));

    $serviceIDs = [];


    if (!empty($venueID)) {
        $getServiceID = $conn->prepare("SELECT * FROM `service` WHERE resortServiceID = ?");
        $getServiceID->bind_param('i', $venueID);
        if ($getServiceID->execute()) {
            $result = $getServiceID->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $serviceIDs[] = $row['serviceID'];
            } else {
                error_log("No matching service found for resortServiceID: $venueID");
            }
        } else {
            error_log("Query failed: " . $conn->error);
        }
        $getServiceID->close();
    }

    if (!empty($partnerIDs)) {
        $getServiceID = $conn->prepare("SELECT * FROM `service` WHERE partnershipServiceID = ?");
        foreach ($partnerIDs as $partershipServiceID) {
            $partershipServiceID = intval($partershipServiceID);
            $getServiceID->bind_param('i', $partershipServiceID);

            if ($getServiceID->execute()) {
                $result = $getServiceID->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $serviceIDs[] = $row['serviceID'];
                } else {
                    error_log("No matching service found for resortServiceID: $partershipServiceID");
                }
            } else {
                error_log("Query failed: " . $conn->error);
            }
        }
        $getServiceID->close();
    }




    //Get event category ID
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



    $conn->begin_transaction();
    try {
        //insert the total of all
        $insertCustomPackage = $conn->prepare("INSERT INTO `custompackage`(`userID`, `eventTypeID`, `customPackageTotalPrice`, `customPackageNotes`, `foodPricingPerHead`, `totalFoodPrice`, `venuePricing`, `additionalServicePrice`) VALUES (?,?,?,?,?,?,?,?)");
        $insertCustomPackage->bind_param('iidsiddd', $userID, $eventCategoryID,  $totalCost, $additionalRequest, $pricingID, $totalFoodPrice, $venuePrice, $additionalServicePrice);

        if (!$insertCustomPackage->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertCustomPackage->error);
        }
        $customPackageID =   $conn->insert_id;

        //insert each item
        $insertCustomPackageItem = $conn->prepare("INSERT INTO `custompackageitem`( `customPackageID`, `foodItemID`, `servicePrice`) VALUES (?,?,?)");


        foreach ($foodList as $foodItemID) {
            error_log('Food ID: ' . $foodItemID);
            $foodItemID = (int) $foodItemID;
            $foodItemPrice = 0.0;

            $servicePrice = $foodItemPrice;
            $insertCustomPackageItem->bind_param('iid', $customPackageID, $foodItemID, $servicePrice);

            if (!$insertCustomPackageItem->execute()) {
                $conn->rollback();
                error_log("Error inserting item $foodItemID: " . $insertCustomPackageItem->error);
            }
        }

        $insertServiceVenue = $conn->prepare("INSERT INTO `custompackageitem`( `customPackageID`, `serviceID`, `servicePrice`) VALUES (?,?,?)");
        foreach ($serviceIDs as $serviceID) {
            $serviceID = intval($serviceID);
            $insertServiceVenue->bind_param('iid', $customPackageID, $serviceID, $venuePrice);

            if (!$insertServiceVenue->execute()) {
                $conn->rollback();
                error_log("Error: " . $insertServiceVenue->error);
            }
        }

        //insert into booking
        $insertBooking = $conn->prepare("INSERT INTO `booking`(`userID`, `bookingType`, `customPackageID`, `additionalRequest`, `guestCount`, `durationCount`,  `startDate`, `endDate`, `paymentMethod`, `additionalCharge`, `totalCost`, `downpayment`, `arrivalTime`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertBooking->bind_param("isisissssddds", $userID, $bookingType, $customPackageID, $additionalRequest, $guestNo, $durationCount, $startDate, $endDate, $paymentMethod, $additionalCharge, $totalCost, $downpayment, $arrivalTime);
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
        header("Location: ../../../../Pages/Customer/eventBooking.php?action=errorBooking");
        exit;
    }
}
