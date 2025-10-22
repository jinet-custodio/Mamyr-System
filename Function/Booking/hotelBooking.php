<?php

require '../../Config/dbcon.php';
require '../Helpers/userFunctions.php';

session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);


function arrayAddition($array)
{
    return array_sum($array);
}


function addition($a, $b, $c)
{
    return $a + $b + $c;
}

function subtraction($a, $b, $c)
{
    return $a - $b - $c;
}

function multiplication($a, $b)
{
    return $a * $b;
}

unset($_SESSION['hotelFormData']);

if (isset($_POST['hotelBooking'])) {
    $selectedHotels = [];

    $hoursSelected = mysqli_real_escape_string($conn, $_POST['hoursSelected']);
    $checkInDate = mysqli_real_escape_string($conn, $_POST['scheduledStartDate']);
    $checkOutDate = mysqli_real_escape_string($conn, $_POST['scheduledEndDate']);
    $arrivalTime = mysqli_real_escape_string($conn, $_POST['arrivalTime']);

    $adultCount = (int)$_POST['adultCount'] ?? 0;
    $childrenCount = (int) $_POST['childrenCount'] ?? 0;
    $toddlerCount = (int) $_POST['toddlerCount'] ?? 0;
    $totalPax = (int) $_POST['totalPax'];
    $totalCapacity = (int) $_POST['capacity'];
    $additionalGuest = (int) $_POST['additionalGuest'];

    $selectedHotels = !empty($_POST['hotelSelections']) ? $_POST['hotelSelections'] : [];

    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);

    $downpayment = (float) $_POST['downPayment'];
    $totalCost = (float) $_POST['totalCost'];
    // $additionalCharge = (int) $_POST['additionalFee'];

    $bookingStatus = 1;
    $serviceIDs = [];
    $hotelPrices = [];
    $hotelCapacity = [];
    $resortServiceIDs = [];

    $arrivalTimeObj = new DateTime($arrivalTime);
    $arrivalTime = $arrivalTimeObj->format('H:i:s');

    if (empty($selectedHotels)) {
        header("Location: ../../Pages/Customer/hotelBooking.php");
    }

    $selectedHotelQuery = $conn->prepare("SELECT * FROM service s
            JOIN resortamenity ra ON s.resortServiceID = ra.resortServiceID
            WHERE ra.RServiceName = ? AND ra.RSduration = ?");

    foreach ($selectedHotels as $selectedHotel) {
        $selectedHotel = trim($selectedHotel);
        $selectedHotelQuery->bind_param("ss", $selectedHotel, $hoursSelected);
        $selectedHotelQuery->execute();
        $resultHotelQuery = $selectedHotelQuery->get_result();
        if ($resultHotelQuery->num_rows > 0) {
            $data = $resultHotelQuery->fetch_assoc();
            $serviceIDs[] = $data['serviceID'];
            $hotelPrices[] = $data['RSprice'];
            $hotelCapacity[] = $data['RScapacity'];
            $resortServiceIDs[] = $data['resortServiceID'];
            $resortServiceIDs[] = $data['resortServiceID'];
        }
    }

    $getSameServiceName = $conn->prepare("SELECT s.serviceID, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
            WHERE rs.RServiceName = ? AND rs.RSduration = '11 hours'");
    foreach ($selectedHotels as $selectedRoom) {
        $selectedRoom = trim($selectedRoom);
        $getSameServiceName->bind_param('s', $selectedRoom);
        $getSameServiceName->execute();
        $getSameServiceResult = $getSameServiceName->get_result();

        if ($getSameServiceResult->num_rows > 0) {
            while ($data = $getSameServiceResult->fetch_assoc()) {
                $resortServiceIDs[] = $data['resortServiceID'];
            }
        } else {
            echo "Service not found for: " . htmlspecialchars($selectedRoom);
            exit();
        }
    }




    $getSameServiceName = $conn->prepare("SELECT s.serviceID, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
            WHERE rs.RServiceName = ? AND rs.RSduration = '11 hours'");
    foreach ($selectedHotels as $selectedRoom) {
        $selectedRoom = trim($selectedRoom);
        $getSameServiceName->bind_param('s', $selectedRoom);
        $getSameServiceName->execute();
        $getSameServiceResult = $getSameServiceName->get_result();

        if ($getSameServiceResult->num_rows > 0) {
            while ($data = $getSameServiceResult->fetch_assoc()) {
                $resortServiceIDs[] = $data['resortServiceID'];
            }
        } else {
            echo "Service not found for: " . htmlspecialchars($selectedRoom);
            exit();
        }
    }



    $hoursNum = str_replace(" hours", "", $hoursSelected);

    $bookingCode = 'HTL' . date('ymd') . generateCode(5);

    $conn->begin_transaction();
    try {

        //Insert Booking
        $insertBooking = $conn->prepare("INSERT INTO booking(userID, toddlerCount, adultCount, kidCount, guestCount, durationCount, startDate, endDate, 
                    paymentMethod,  totalCost, downpayment, bookingStatus, bookingType, arrivalTime, bookingCode) 
                    VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertBooking->bind_param(
            "iiiiiisssddisss",
            $userID,
            $toddlerCount,
            $adultCount,
            $childrenCount,
            $totalPax,
            $hoursNum,
            $checkInDate,
            $checkOutDate,
            $paymentMethod,
            // $additionalCharge,
            $totalCost,
            $downpayment,
            $bookingStatus,
            $bookingType,
            $arrivalTime,
            $bookingCode
        );
        if (!$insertBooking->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' . $insertBooking->error);
        }

        $bookingID = $conn->insert_id;

        $insertBookingServices = $conn->prepare("INSERT INTO bookingservice(bookingID, serviceID, guests, bookingServicePrice)
        VALUES(?,?,?,?)");
        if (!empty($serviceIDs)) {
            for ($i = 0; $i < count($serviceIDs); $i++) {
                $serviceID = $serviceIDs[$i];
                $servicePrice = $hotelPrices[$i];
                $serviceCapacity = $hotelCapacity[$i];

                $insertBookingServices->bind_param("iiid", $bookingID, $serviceID, $serviceCapacity, $servicePrice);
                if (!$insertBookingServices->execute()) {
                    $conn->rollback();
                    throw new Exception('Error: ' . $insertBookingServices->error);
                }
            }
        }
        $insertBookingServices->close();

        $receiver = 'Admin';
        $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, senderID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        if (! $insertBookingNotificationRequest->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' .  $insertBookingNotificationRequest->error);
        }
        $insertBookingNotificationRequest->close();

        $expiresAt = 'NULL';
        $insertUnavailableService = $conn->prepare("INSERT INTO serviceunavailabledate(bookingID,resortServiceID, unavailableStartDate, unavailableEndDate, expiresAt) VALUES (?,?,?,?,?)");
        if (!empty($resortServiceIDs)) {
            for ($i = 0; $i < count($resortServiceIDs); $i++) {
                $resortServiceID = $resortServiceIDs[$i];
                $insertUnavailableService->bind_param("iisss", $bookingID, $resortServiceID, $checkInDate, $checkOutDate, $expiresAt);
                if (!$insertUnavailableService->execute()) {
                    $conn->rollback();
                    throw new Exception('Error :' . $insertUnavailableService->error);
                }
            }
        }
        $insertUnavailableService->close();


        $conn->commit();
        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Error: ' . $e->getMessage());
        $_SESSION['hotelFormData'] = $_POST;
        header('Location: ../../../../Pages/Customer/hotelBooking.php?action=errorBooking');
    }
}
