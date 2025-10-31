<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');

$userID = intval($_SESSION['userID']);
$userRole = intval($_SESSION['userRole']);

require '../Helpers/userFunctions.php';

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
    $foodList =  $_POST['selectedFoods'] ?? [];
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

    $services = [];
    $serviceIDs = [];
    $customerChoice = isset($_POST['customer-choice']) ?  mysqli_real_escape_string($conn, $_POST['customer-choice']) : '';
    $bookingCode = 'EVT' . date('ymd') . generateCode(5);

    if (!empty($venueID)) {
        $getServiceID = $conn->prepare("SELECT * FROM `service` WHERE resortServiceID = ?");
        $getServiceID->bind_param('i', $venueID);
        if ($getServiceID->execute()) {
            $result = $getServiceID->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $serviceType = strtolower($row['serviceType']);
                $serviceIDs[$serviceType] = $row['resortServiceID'];
                $serviceID = $row['serviceID'];
                $services[$serviceID] = $venuePrice;
            } else {
                error_log("No matching service found for resortServiceID: $venueID");
            }
        } else {
            error_log("Query failed: " . $conn->error);
        }
        $getServiceID->close();
    }


    $partnershipIDs = [];
    $partnerService = [];
    if (!empty($partnerIDs)) {
        $getServiceID = $conn->prepare("SELECT s.*, ps.PBName, ps.PBPrice, p.partnershipID FROM `service` s 
        LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
        LEFT JOIN partnership p ON ps.partnershipID = p.partnershipID
        WHERE ps.partnershipServiceID = ?");
        foreach ($partnerIDs as $partershipServiceID) {
            error_log('PSID: ' . $partershipServiceID);
            $partershipServiceID = intval($partershipServiceID);
            $getServiceID->bind_param('i', $partershipServiceID);

            if ($getServiceID->execute()) {
                $result = $getServiceID->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $serviceType = strtolower($row['serviceType']);
                    $serviceIDs[$serviceType] = $row['partnershipServiceID'];
                    $serviceID = $row['serviceID'];
                    $price =  $row['PBPrice'];
                    $services[$serviceID] = $price;
                    $partnerService[$partershipServiceID] = $price;
                    $partnershipID = $row['partnershipID'];
                    $partnershipIDs[$partnershipID] = $row['PBName'];
                } else {
                    error_log("No matching service found for partnershipServiceID: $partershipServiceID");
                }
            } else {
                error_log("Query failed: " . $conn->error);
            }
        }
        $getServiceID->close();
    }

    // error_log(print_r($partnerService, true));



    //Get event category ID
    $getEventTypeID = $conn->prepare("SELECT * FROM `eventcategory` WHERE categoryName = ?");
    $getEventTypeID->bind_param('s', $eventType);
    if ($getEventTypeID->execute()) {
        $result = $getEventTypeID->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $eventCategoryID = intval($row['categoryID']);
        } {
            error_log("No matching event found for $eventType");
        }
    } else {
        error_log("Query failed: " . $conn->error);
    }



    $conn->begin_transaction();
    try {

        //insert the total of all
        $insertCustomPackage = $conn->prepare("INSERT INTO `custompackage`(`userID`, `eventTypeID`, `customPackageTotalPrice`, `customPackageNotes`, `foodPricingPerHeadID`, `totalFoodPrice`, `venuePricing`, `additionalServicePrice`) VALUES (?,?,?,?,?,?,?,?)");
        $insertCustomPackage->bind_param('iidsiddd', $userID, $eventCategoryID,  $totalCost, $additionalRequest, $pricingID, $totalFoodPrice, $venuePrice, $additionalServicePrice);

        if (!$insertCustomPackage->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertCustomPackage->error);
        }
        $customPackageID =  $conn->insert_id;

        //insert each item
        $insertCustomPackageItem = $conn->prepare("INSERT INTO `custompackageitem`( `customPackageID`, `foodItemID`, `servicePrice`) VALUES (?,?,?)");

        foreach ($foodList as $foodItemID => $name) {
            // error_log('Food ID: ' . $foodItemID . 'CustomID: ' .  $customPackageID);
            $foodItemID = (int) $foodItemID;
            $foodItemPrice = 0.0;

            $servicePrice = $foodItemPrice;
            $insertCustomPackageItem->bind_param('iid', $customPackageID, $foodItemID, $servicePrice);

            if (!$insertCustomPackageItem->execute()) {
                $conn->rollback();
                error_log("Error inserting itemID => $foodItemID: " . $insertCustomPackageItem->error);
            }
        }

        $insertServicePrices = $conn->prepare("INSERT INTO `custompackageitem`( `customPackageID`, `serviceID`, `servicePrice`) VALUES (?,?,?)");
        foreach ($services as $serviceID => $price) {
            $serviceID = intval($serviceID);
            $price = floatval($price);
            $insertServicePrices->bind_param('iid', $customPackageID, $serviceID, $price);

            if (!$insertServicePrices->execute()) {
                $conn->rollback();
                error_log("Error: " .  $insertServicePrices->error);
            }
        }

        //insert into booking
        $insertBooking = $conn->prepare("INSERT INTO `booking`(`userID`, `bookingCode`, `customerChoice`, `bookingType`, `customPackageID`, `additionalRequest`, `guestCount`, `durationCount`,  `startDate`, `endDate`, `paymentMethod`, `totalCost`, `downpayment`, `arrivalTime`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertBooking->bind_param("isssisissssdds", $userID, $bookingCode, $customerChoice, $bookingType, $customPackageID, $additionalRequest, $guestNo, $durationCount, $startDate, $endDate, $paymentMethod, $totalCost, $downpayment, $arrivalTime);
        if (!$insertBooking->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertBooking->error);
        }

        $bookingID = $insertBooking->insert_id;
        $pendingID = 1;

        //insert into bp availed service
        $insertBPavailedService = $conn->prepare("INSERT INTO `businesspartneravailedservice`(`partnershipServiceID`, `bookingID`, `approvalStatus`, `price`) VALUES (?,?,?,?)");
        foreach ($partnerService as $partershipServiceID => $price) {
            $insertBPavailedService->bind_param('iiid', $partershipServiceID, $bookingID, $pendingID, $price);
            if (!$insertBPavailedService->execute()) {
                $conn->rollback();
                error_log("Error: " . $insertBooking->error);
            }
        }
        $startDateTime = new DateTime($startDate);
        $unavailableStartDate = clone $startDateTime;
        $unavailableStartDateStr = $unavailableStartDate->setTime(0, 0)->format('Y-m-d H:i:s');

        $endDateTime = new DateTime($endDate);
        $unavailableEndDate = clone $endDateTime;
        $unavailableEndDateStr = $unavailableEndDate->setTime(23, 59)->format('Y-m-d H:i:s');


        //insert into unavailable table
        $insertIntoUnavailableService = $conn->prepare("INSERT INTO serviceunavailabledate(bookingID, resortServiceID, partnershipServiceID, unavailableStartDate, unavailableEndDate, expiresAt) values (?,?,?,?,?,?)");
        foreach ($serviceIDs as $type => $id) {
            $type = trim($type);
            $id = (int) $id;
            $null = NULL;
            if ($type === 'resort') {
                $insertIntoUnavailableService->bind_param('iiisss', $bookingID, $id, $null, $unavailableStartDateStr, $unavailableEndDateStr, $null);
            } elseif ($type === 'partnership') {
                $insertIntoUnavailableService->bind_param('iiisss', $bookingID, $null, $id, $unavailableStartDateStr, $unavailableEndDateStr, $null);
            }

            if (!$insertIntoUnavailableService->execute()) {
                $conn->rollback();
                error_log("Error: $type -> $id" . $insertIntoUnavailableService->error);
            }
        }

        if (!empty($partnershipIDs)) {
            foreach ($partnershipIDs as $id => $name):
                $receiver = 'Partner';
                $message = "You have received a new customer booking request for your <strong>" . strtolower($name) . "</strong> service.";
                $insertPartnerNotification = $conn->prepare("INSERT INTO `notification`(`bookingID`, `senderID`, `receiverID` , `message`, `receiver`) VALUES (?,?,?,?,?)");
                $insertPartnerNotification->bind_param("iiiss", $bookingID, $userID, $id, $message, $receiver);
                if (!$insertPartnerNotification->execute()) {
                    $conn->rollback();
                    throw new Exception("Failed inserting notification" . $insertPartnerNotification->error);
                }
            endforeach;
        }

        $receiver = 'Admin';
        $message = "A customer has submitted a new " . strtolower($bookingType) . " booking request. <a href='booking.php'>View here.</a>";
        $insertNotification = $conn->prepare("INSERT INTO `notification`(`bookingID`, `senderID`,  `message`, `receiver`) VALUES (?,?,?,?)");
        $insertNotification->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        if (!$insertNotification->execute()) {
            $conn->rollback();
            throw new Exception("Failed inserting notification" . $insertNotification->error);
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
