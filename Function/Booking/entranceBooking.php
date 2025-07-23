<?php

// use Dom\ChildNode;

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
    $roomChoice = isset($_POST['roomSelections']) ? mysqli_real_escape_string($conn, $_POST['roomSelections']) : "";
    $videokeChoice = mysqli_real_escape_string($conn, $_POST['videokeChoice']);
    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $totalPax = mysqli_real_escape_string($conn, $_POST['totalPax']);

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
    $query = $conn->prepare("SELECT er.*, s.serviceID 
            FROM entranceRates er
            JOIN services s ON s.entranceRateID = er.entranceRateID
            WHERE er.sessionType = ?");
    $query->bind_param("s", $tourSelections);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        while ($rates = $result->fetch_assoc()) {
            if ($rates['ERcategory'] === 'Adult') {
                $adultRate = $rates['ERprice'];
                $adultServiceID = $rates['serviceID'];
            } elseif ($rates['ERcategory'] === 'Kids') {
                $childRate = $rates['ERprice'];
                $childrenServiceID = $rates['serviceID'];
            }
        }
    }

    //Get the time range
    $getTimeRange = $conn->prepare("SELECT * FROM entrancetimeranges");
    $getTimeRange->execute();
    $resultTimeRange = $getTimeRange->get_result();
    if ($resultTimeRange->num_rows > 0) {
        while ($row = $resultTimeRange->fetch_assoc()) {
            $sessionType = $row['session_type'];
            $timeRange = $row['time_range'];
            if ($sessionType === "Overnight") {
                list($startTime, $endTime) =  explode('-', $timeRange);
                $overnightStartTime = trim($startTime);
                $overnightEndTime = trim($endTime);
            } elseif ($sessionType === "Night") {
                list($startTime, $endTime) =  explode('-', $timeRange);
                $nightStartTime = trim($startTime);
                $nightEndTime = trim($endTime);
            } elseif ($sessionType === "Day") {
                list($startTime, $endTime) =  explode('-', $timeRange);
                $dayStartTime = trim($startTime);
                $dayEndTime = trim($endTime);
            }
        }
    }

    //Set Date and Time
    if ($tourSelections === 'Overnight') {
        $startDateObj = new DateTime($scheduledDate . ' ' . $overnightStartTime);
        $endDateObj = new DateTime($scheduledDate . ' ' . $overnightEndTime);
        //Add one day
        if ($endDateObj <= $startDateObj) {
            $endDateObj->modify('+1 day');
        }

        //Get number of hours

        $interval = $startDateObj->diff($endDateObj);
        $numHours = $interval->h + ($interval->days * 24);
    } elseif ($tourSelections === 'Day') {
        $startDateObj = new DateTime($scheduledDate . ' ' . $dayStartTime);
        $endDateObj = new DateTime($scheduledDate . ' ' . $dayEndTime);

        $interval = $startDateObj->diff($endDateObj);
        $numHours = $interval->h + ($interval->days * 24);
    } elseif ($tourSelections === 'Night') {
        $startDateObj = new DateTime($scheduledDate . ' ' . $nightStartTime);
        $endDateObj = new DateTime($scheduledDate . ' ' . $nightEndTime);

        $interval = $startDateObj->diff($endDateObj);
        $numHours = $interval->h + ($interval->days * 24);
    } else {
        $startDateObj = new DateTime($scheduledDate);
        $endDateObj = clone $startDateObj;

        $interval = $startDateObj->diff($endDateObj);
        $numHours = $interval->h + ($interval->days * 24);
    }
    $startDate = $startDateObj->format('Y-m-d H:i:s');
    $endDate = $endDateObj->format('Y-m-d H:i:s');


    $getServiceChoice = '';
    if (!empty($cottageChoice)) {
        $getServiceChoice = $cottageChoice;
    } elseif (!empty($roomChoice)) {
        $getServiceChoice = $roomChoice;
    } else {
        $getServiceChoice = "";
    }


    $getServiceChoiceQuery = $conn->prepare("SELECT s.*, rs.* FROM services s
            INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
            WHERE RServiceName = ?");
    $getServiceChoiceQuery->bind_param("s", $getServiceChoice);
    $getServiceChoiceQuery->execute();
    $getServiceChoiceResult = $getServiceChoiceQuery->get_result();
    if ($getServiceChoiceResult->num_rows > 0) {
        $data =  $getServiceChoiceResult->fetch_assoc();
        $serviceID = $data['serviceID'];  //Makukuha nito is cottage or hotel 
        $servicePrice = $data['RSprice'];
        $serviceCapacity = $data['RScapacity'];
    } else {
        echo "Service not found. MySQL error: " . mysqli_error($conn);
        error_log("Running query to fetch service with name: " . $getServiceChoice);
        exit();
    }

    //Get Videoke Price
    $videokeName = "Videoke 1";
    $getVideokePrice = $conn->prepare("SELECT RServiceName, RSPrice FROM resortAmenities WHERE RServiceName = ?");
    $getVideokePrice->bind_param("s", $videokeName);
    $getVideokePrice->execute();
    $resultVideokePrice = $getVideokePrice->get_result();
    if ($resultVideokePrice->num_rows > 0) {
        $row = $resultVideokePrice->fetch_assoc();
        $VideokePrice = $row['RSPrice'];
    }

    $videokeFee = 0;
    $videoke = NULL;
    if ($videokeChoice === "Yes") {
        $videokeFee = $VideokePrice;
        $videoke = "Videoke";
    } elseif ($videokeChoice === "No") {
        $videokeFee = 0;
    }


    if ($adultCount !== "" && $childrenCount !== "" && $additionalRequest !== "") {
        // $totalPax = (int)$adultCount + (int)$childrenCount;
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
        bookingStatus, addOns, paymentMethod, bookingType) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?, ?)");
        $insertBooking->bind_param(
            "isiissddisss",
            $userID,
            // $serviceID,
            $additionalRequest,
            $totalPax,
            $numHours,
            $startDate,
            $endDate,
            $totalCost,
            $downPayment,
            $bookingStatus,
            $addOns,
            $paymentMethod,
            $bookingType
        );
        if ($insertBooking->execute()) {
            $bookingID = $conn->insert_id;

            $insertBookingServices = $conn->prepare("INSERT INTO 
            bookingservices(bookingID, serviceID, guests, bookingServicePrice)
            VALUES(?,?,?,?)");

            if ($adultCount > 0 && isset($adultServiceID)) {
                $insertBookingServices->bind_param("iiis", $bookingID, $adultServiceID, $adultCount, $totalAdultFee);
                $insertBookingServices->execute();
            }

            if ($childrenCount > 0 && isset($childrenServiceID)) {
                $insertBookingServices->bind_param("iiis", $bookingID, $childrenServiceID, $childrenCount, $totalChildFee);
                $insertBookingServices->execute();
            }

            if (!empty($serviceID)) {
                $insertBookingServices->bind_param("iiis", $bookingID, $serviceID, $serviceCapacity, $servicePrice);
                $insertBookingServices->execute();
            }

            $receiver = 'Admin';
            $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request';
            $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
            $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
            $insertBookingNotificationRequest->execute();

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




//Set Date and Time
    // if ($tourSelections === 'Overnight') {
    //     $startDateObj = new DateTime($scheduledDate . ' 20:00:00');
    //     $endDateObj = clone $startDateObj;
    //     $endDateObj->modify('+1 day')->setTime(5, 0, 0);
    // } elseif ($tourSelections === 'Day') {
    //     $startDateObj = new DateTime($scheduledDate . ' 9:00:00');
    //     $endDateObj = clone $startDateObj;
    //     $endDateObj->setTime(16, 0, 0);
    // } elseif ($tourSelections === 'Night') {
    //     $startDateObj = new DateTime($scheduledDate . ' 20:00:00');
    //     $endDateObj = clone $startDateObj;
    //     $endDateObj->setTime(5, 0, 0);
    // } else {
    //     $startDateObj = new DateTime($scheduledDate);
    //     $endDateObj = clone $startDateObj;
    // }


          // //Get the number of hours
    // $serviceQuery = "SELECT * FROM entrancetimeranges
    // WHERE session_type = '$tourSelections'";
    // $serviceResult = mysqli_query($conn, $serviceQuery);
    // if (mysqli_num_rows($serviceResult) > 0) {
    //     $data = mysqli_fetch_assoc($serviceResult);
    //     $tourType = $data['session_type'];
    //     if ($tourType === "Day") {
    //         $hours = '7 hours';
    //     } elseif ($tourType === "Night") {
    //         $hours = '8 hours';
    //     } elseif ($tourType === "Overnight") {
    //         $hours = '9 hours';
    //     }
    // }