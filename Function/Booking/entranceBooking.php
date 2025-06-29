<?php

use Dom\ChildNode;

require '../../Config/dbcon.php';


session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);

if (isset($_POST['bookRates'])) {
    $scheduledDate = mysqli_real_escape_string($conn, $_POST['resortBookingDate']);
    $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);  //Day, Night, Overnight
    $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
    $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);
    $cottageChoice = mysqli_real_escape_string($conn, $_POST['cottageSelections']);
    $roomChoice = mysqli_real_escape_string($conn, $_POST['roomSelections']);
    $videokeChoice = mysqli_real_escape_string($conn, $_POST['videokeChoice']);
    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['PaymentMethod']);

    if ($adultCount !== "" && $childrenCount !== "") {
        $adultCount;
        $childrenCount;
    } elseif ($adultCount === "" && $childrenCount !== "") {
        $adultCount = 0;
        $childrenCount;
    } elseif ($adultCount !== "" && $childrenCount === "") {
        $adultCount;
        $childrenCount = 0;
    }

    if ($additionalRequest !== "") {
        $additionalRequest;
    } else {
        $additionalRequest = "None";
    }

    $adultRate = 0;
    $childRate = 0;

    //Get the rates
    $query = "SELECT er.*, s.serviceID 
    FROM entranceRates er
    JOIN services s ON s.entranceRateID = er.entranceRateID
    WHERE er.sessionType = '$tourSelections'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($rates = mysqli_fetch_assoc($result)) {
            if ($rates['ERcategory'] === 'Adult') {
                $adultRate = $rates['ERprice'];
                $adultServiceID = $rates['serviceID'];
            } elseif ($rates['ERcategory'] === 'Kids') {
                $childRate = $rates['ERprice'];
                $childServiceID = $rates['serviceID'];
            }
        }
    }

    //Set Date and Time
    if ($tourSelections === 'Overnight') {
        $startDateObj = new DateTime($scheduledDate . ' 20:00:00');
        $endDateObj = clone $startDateObj;
        $endDateObj->modify('+1 day')->setTime(5, 0, 0);
    } elseif ($tourSelections === 'Day') {
        $startDateObj = new DateTime($scheduledDate . ' 9:00:00');
        $endDateObj = clone $startDateObj;
        $endDateObj->setTime(16, 0, 0);
    } elseif ($tourSelections === 'Night') {
        $startDateObj = new DateTime($scheduledDate . ' 20:00:00');
        $endDateObj = clone $startDateObj;
        $endDateObj->setTime(5, 0, 0);
    } else {
        $startDateObj = new DateTime($scheduledDate);
        $endDateObj = clone $startDateObj;
    }

    $startDate = $startDateObj->format('Y-m-d H:i:s');
    $endDate = $endDateObj->format('Y-m-d H:i:s');

    //Get the number of hours
    $serviceQuery = "SELECT * FROM entrancetimerange
    WHERE session_type = '$tourSelections'";
    $serviceResult = mysqli_query($conn, $serviceQuery);
    if (mysqli_num_rows($serviceResult) > 0) {
        $data = mysqli_fetch_assoc($serviceResult);
        $tourType = $data['session_type'];
        if ($tourType === "Day") {
            $hours = 7;
        } elseif ($tourType === "Night") {
            $hours = 8;
        } elseif ($tourType === "Overnight") {
            $hours = 9;
        }
    }

    if (!empty($cottageChoice)) {
        $getServiceChoice = $cottageChoice;
    } elseif (!empty($roomChoice)) {
        $getServiceChoice = $roomChoice;
    } else {
        $getServiceChoice = "";
    }


    $getServiceChoiceQuery = "SELECT s.*, rs.* FROM services s
    INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
    WHERE RServiceName = '$getServiceChoice'";
    $getServiceChoiceResult = mysqli_query($conn, $getServiceChoiceQuery);
    if (mysqli_num_rows($getServiceChoiceResult) > 0) {
        $data = mysqli_fetch_assoc($getServiceChoiceResult);
        $serviceID = $data['serviceID'];  //Makukuha nito is cottage or hotel 
        $servicePrice = $data['RSprice'];
        $serviceCapacity = $data['RScapacity'];
    } else {
        echo "Service not found. MySQL error: " . mysqli_error($conn);
        echo "<br>Query: $getServiceChoiceQuery";
        exit();
    }


    if ($videokeChoice === "Yes") {
        $videokeFee = 800;
        $videoke = "Videoke";
    } elseif ($videokeChoice === "No") {
        $videokeFee = 0;
    }


    if ($adultCount !== "" && $childrenCount !== "" && $additionalRequest !== "") {
        $totalPax = (int)$adultCount + (int)$childrenCount;
        $totalAdultFee = ($adultRate * $adultCount);
        $totalChildFee = ($childRate * $childrenCount);
        $totalEntrance = $totalAdultFee + $totalChildFee;
        $totalCost = $totalEntrance + $servicePrice + $videokeFee;
        $downPayment = $totalCost * 0.3;
        $bookingStatus = 1;
        $addOns = $videoke ?: NULL;
        // $notes =  $videoke . " " . $tourSelections . " tour (" . $childrenCount . " Kids & " .  $adultCount . "Adults)";

        $insertBooking = $conn->prepare("INSERT INTO 
        bookings(userID, additionalRequest,  paxNum, hoursNum, 
        startDate, endDate, 
        totalCost, downpayment, 
        bookingStatus, addOns, paymentMethod) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?) ");
        $insertBooking->bind_param(
            "isiissddiss",
            $userID,
            // $serviceID,
            $additionalRequest,
            $totalPax,
            $hours,
            $startDate,
            $endDate,
            $totalCost,
            $downPayment,
            $bookingStatus,
            $addOns,
            $paymentMethod
        );
        if ($insertBooking->execute()) {
            $bookingID = $conn->insert_id;

            $insertBookingServices = $conn->prepare("INSERT INTO 
            bookingsservices(bookingID, serviceID, guests, Total)
            VALUES(?,?,?,?)");

            if ($adultCount > 0 && isset($adultServiceID)) {
                $insertBookingServices->bind_param("iiis", $bookingID, $adultServiceID, $adultCount, $totalAdultFee);
                $insertBookingServices->execute();
            }

            if ($childrenCount > 0 && isset($childServiceID)) {
                $insertBookingServices->bind_param("iiis", $bookingID, $childServiceID, $childCount, $totalChildFee);
                $insertBookingServices->execute();
            }

            if (!empty($serviceID)) {
                $insertBookingServices->bind_param("iiis", $bookingID, $serviceID, $serviceCapacity, $servicePrice);
                $insertBookingServices->execute();
            }

            header('Location: ../../Pages/Customer/bookNow.php?action=success');
            exit();
        } else {
            echo "Booking failed: " . $insertBooking->error;
        }
    } else {
        echo 'Empty Bai';
    }
} else {
    echo 'sa button error, di pumapasok pag pinindot bookRates';
}
