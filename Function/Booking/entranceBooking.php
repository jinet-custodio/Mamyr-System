<?php

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
    $videokeChoice = mysqli_real_escape_string($conn, $_POST['videokeChoice']);
    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);

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
    $query = "SELECT * FROM entranceRates WHERE sessionType = '$tourSelections'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($rates = mysqli_fetch_assoc($result)) {
            if ($rates['ERcategory'] === 'Adult') {
                $adultRate = $rates['ERprice'];
            } elseif ($rates['ERcategory'] === 'Child') {
                $childRate = $rates['ERprice'];
            }
        }
    }


    if ($tourSelections === 'Overnight') {
        $endDateObj = new DateTime($scheduledDate);
        $endDateObj->modify('+1 day');

        $startDate = $scheduledDate;
        $endDate = $endDateObj->format('Y-m-d H:i:s');
    } else {
        $startDate = $scheduledDate;
        $endDate =  $scheduledDate;
    }



    //Get the serviceID
    $serviceQuery = "SELECT s.*, etr.* FROM services s
    INNER JOIN entrancetimerange etr ON s.swimmingID = etr.timeRangeID
    WHERE session_type = '$tourSelections'";
    $serviceResult = mysqli_query($conn, $serviceQuery);
    if (mysqli_num_rows($serviceResult) > 0) {
        $data = mysqli_fetch_assoc($serviceResult);
        $serviceID = $data['serviceID'];
        $tourType = $data['session_type'];
        if ($tourType === "Day") {
            $hours = 7;
        } elseif ($tourType === "Night") {
            $hours = 8;
        } elseif ($tourType === "Overnight") {
            $hours = 9;
        }
    }

    if ($adultCount !== "" && $childrenCount !== "" && $additionalRequest !== "") {
        $totalCost = ($adultRate * $adultCount) + ($childRate * $childrenCount);
        $totalPax = $adultCount + $childrenCount;
        $downPayment = $totalCost * 0.3;
        $bookingStatus = 1;
        $insertBooking = $conn->prepare("INSERT INTO 
        bookings(userID, serviceID, additionalRequest,  paxNum, hoursNum, 
        startDate, endDate, 
        totalCost, downpayment, 
        bookingStatus) 
        VALUES(?,?,?,?,?,?,?,?,?,?) ");
        $insertBooking->bind_param(
            "iisiissddi",
            $userID,
            $serviceID,
            $additionalRequest,
            $totalPax,
            $hours,
            $startDate,
            $endDate,
            $totalCost,
            $downPayment,
            $bookingStatus
        );
        if ($insertBooking->execute()) {
            header('Location: ../../Pages/Customer/bookNow.php?action=success');
            exit();
        } else {
            echo "Booking failed: " . $insertBooking->error;
        }
    } else {
        echo 'or Dito ba error';
    }
} else {
    echo 'or Dito or error';
}
