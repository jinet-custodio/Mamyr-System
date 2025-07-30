<?php

require '../../Config/dbcon.php';


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



if (isset($_POST['hotelBooking'])) {
    $selectedHotels = [];

    $hoursSelected = mysqli_real_escape_string($conn, $_POST['hoursSelected']);
    $checkInDate = mysqli_real_escape_string($conn, $_POST['scheduledStartDate']);
    $checkOutDate = mysqli_real_escape_string($conn, $_POST['scheduledEndDate']);
    $arrivalTime = mysqli_real_escape_string($conn, $_POST['arrivalTime']);

    $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
    $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);
    $totalPax = mysqli_real_escape_string($conn, $_POST['totalPax']);
    $totalCapacity = mysqli_real_escape_string($conn, $_POST['capacity']);

    $selectedHotels = !empty($_POST['hotelSelections']) ? array_map('trim', explode(', ', $_POST['hotelSelections'])) : [];

    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);

    $downpayment = mysqli_real_escape_string($conn, $_POST['downPayment']);
    $totalCost = mysqli_real_escape_string($conn, $_POST['totalCost']);

    $excessChargePerPerson = 250;
    $additionalCharge = 0;
    $additionalGuest = 0;
    $bookingStatus = 1;
    $serviceIDs = [];
    $hotelPrices = [];
    $hotelCapacity = [];
    if ($totalPax > $totalCapacity) {
        $additionalGuest = subtraction($totalPax, $totalCapacity, 0);
        $additionalCharge = multiplication($additionalGuest, $excessChargePerPerson);
    }

    if (empty($selectedHotels)) {
        header("Location: ../../Pages/Customer/hotelBooking.php");
    }

    $selectedHotelQuery = $conn->prepare("SELECT * FROM services s
            JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
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
        }
    }

    $hoursNum = str_replace(" hours", "", $hoursSelected);

    //Insert Booking
    $insertBooking = $conn->prepare("INSERT INTO bookings(userID, paxNum, hoursNum, startDate, endDate, 
    paymentMethod, additionalCharge, totalCost, downpayment, bookingStatus, bookingType, arrivalTime) 
    VALUES(?,?,?,?,?,?,?,?,?,?,?, ?)");
    $insertBooking->bind_param(
        "iiisssdddiss",
        $userID,
        $totalPax,
        $hoursNum,
        $checkInDate,
        $checkOutDate,
        $paymentMethod,
        $additionalCharge,
        $totalCost,
        $downpayment,
        $bookingStatus,
        $bookingType,
        $arrivalTime
    );
    if ($insertBooking->execute()) {
        $bookingID = $conn->insert_id;

        $insertBookingServices = $conn->prepare("INSERT INTO bookingservices(bookingID, serviceID, guests, bookingServicePrice)
        VALUES(?,?,?,?)");
        if (!empty($serviceIDs)) {
            for ($i = 0; $i < count($serviceIDs); $i++) {
                $serviceID = $serviceIDs[$i];
                $servicePrice = $hotelPrices[$i];
                $serviceCapacity = $hotelCapacity[$i];

                $insertBookingServices->bind_param("iiid", $bookingID, $serviceID, $serviceCapacity, $servicePrice);
                $insertBookingServices->execute();
            }
        }
        $insertBookingServices->close();

        $receiver = 'Admin';
        $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        $insertBookingNotificationRequest->execute();
        $insertBookingNotificationRequest->close();
        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        exit();
    } else {
        echo "Booking failed: " . $insertBooking->error;
    }
}
