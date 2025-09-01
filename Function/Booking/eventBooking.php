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

    //Date and time
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventStartTime = mysqli_real_escape_string($conn, $_POST['eventStartTime']);
    $eventEndTime = mysqli_real_escape_string($conn, $_POST['eventEndTime']);

    $startDateTimeStr = $eventDate . ' ' . $eventStartTime;
    $endDateTimeStr = $eventDate . ' ' . $eventEndTime;


    $startDateTime = new DateTime($startDateTimeStr);
    $endDateTime = new DateTime($endDateTimeStr);


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

    $serviceID = null;

    $getServiceID = $conn->prepare("SELECT * FROM `services` WHERE resortServiceID = ?");
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


    $conn->begin_transaction();
    try {
        //insert the total of all
        $insertCustomPackage = $conn->prepare("INSERT INTO `custompackages`(`userID`, `customPackageTotalPrice`, `customPackageNotes`) VALUES (?,?,?)");
        $insertCustomPackage->bind_param('ids', $userID, $venuePrice, $additionalRequest);

        if (!$insertCustomPackage->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertCustomPackage->error);
        }
        $customPackageID =   $conn->insert_id;

        //insert each item
        $insertCustomPackageItem = $conn->prepare("INSERT INTO `custompackageitems`( `customPackageID`, `foodItemID`, `quantity`) VALUES (?,?,?)");

        foreach ($menuQuantities as $foodItemID => $quantity) {
            $foodItemID = (int) $foodItemID;
            $quantity =  (int) $quantity;
            $insertCustomPackageItem->bind_param('iii', $customPackageID, $foodItemID, $quantity);
            if (!$insertCustomPackageItem->execute()) {
                $conn->rollback();
                error_log("Error: " . $insertCustomPackageItem->error);
            }
        }

        $insertServiceVenue = $conn->prepare("INSERT INTO `custompackageitems`( `customPackageID`, `serviceID`, `quantity`, `servicePrice`) VALUES (?,?,?,?)");
        $insertServiceVenue->bind_param('iiid', $customPackageID, $serviceID, $serviceQuantity, $venuePrice);

        if (!$insertServiceVenue->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertServiceVenue->error);
        }

        //insert into booking
        $insertBooking = $conn->prepare("INSERT INTO `bookings`(`userID`, `bookingType`, `customPackageID`, `additionalRequest`, `guestCount`, `durationCount`,  `startDate`, `endDate`, `paymentMethod`, `additionalCharge`, `totalCost`, `downpayment`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertBooking->bind_param("isisissssddd", $userID, $bookingType, $customPackageID, $additionalRequest, $guestNo, $durationCount, $startDateTimeStr, $endDateTimeStr, $paymentMethod, $additionalCharge, $venuePrice, $downpayment);
        if (!$insertBooking->execute()) {
            $conn->rollback();
            error_log("Error: " . $insertBooking->error);
        }

        $conn->commit();
        header("Location: ../../../../Pages/Customer/bookNow.php?action=success");
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error inserting:" . $e->getMessage());
        // $_SESSION['eventFormData'] = $_POST;
        // header("Location: ../../../../Pages/Customer/eventBookingConfirmation.php");
    }
}
