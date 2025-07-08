<?php

require '../../Config/dbcon.php';


session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);

if (isset($_POST['hotelBooking'])) {

    $checkInDate = mysqli_real_escape_string($conn, $_POST['checkInDate']);
    $checkOutDate = mysqli_real_escape_string($conn, $_POST['checkOutDate']);
    $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
    $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);
    $selectedHotel = mysqli_real_escape_string($conn, $_POST['selectedHotel']);
    // $hotelNotes = mysqli_real_escape_string($conn, $_POST['hotelNotes']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);

    $excessChargePerPerson = 250;
    $totalGuest = $childrenCount + $adultCount; //Get total number of people
    $additionalCharge = 0;
    $additionalGuest = 0;
    $totalPrice = 0;
    $bookingStatus = 1;
    $selectHotelQuery = "SELECT * FROM services s
    JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
    WHERE ra.RServiceName = '$selectedHotel'";
    $resultHotelQuery = mysqli_query($conn, $selectHotelQuery);
    if (mysqli_num_rows($resultHotelQuery) > 0) {
        $data = mysqli_fetch_assoc($resultHotelQuery);
        $serviceID = $data['serviceID'];
        $maxCapacity = $data['RScapacity'];
        $hotelPrice = $data['RSprice'];
        $stayDuration = $data['RSduration'];
    }

    if ($maxCapacity < $totalGuest) {
        $additionalGuest = $totalGuest - $maxCapacity;
        $additionalCharge = $additionalGuest * $excessChargePerPerson;
        $totalPrice = $hotelPrice + $additionalCharge;
    } else {
        $totalPrice =  $hotelPrice;
    }

    $downpayment = $totalPrice * 0.3;


    //Insert Booking
    $insertBooking = $conn->prepare("INSERT INTO bookings(userID, paxNum, hoursNum, startDate, endDate, 
    paymentMethod, additionalCharge, totalCost, downpayment, bookingStatus, bookingType) 
    VALUES(?,?,?,?,?,?,?,?,?,?,?)");
    $insertBooking->bind_param(
        "iiissssssis",
        $userID,
        $totalGuest,
        $stayDuration,
        $checkInDate,
        $checkOutDate,
        $paymentMethod,
        $additionalCharge,
        $totalPrice,
        $downpayment,
        $bookingStatus,
        $bookingType
    );
    if ($insertBooking->execute()) {
        $bookingID = $conn->insert_id;

        $insertBookingServices = $conn->prepare("INSERT INTO bookingservices(bookingID, serviceID, guests, bookingServicePrice)
        VALUES(?,?,?,?)");
        $insertBookingServices->bind_param("iiss", $bookingID, $serviceID, $totalGuest, $totalPrice);
        $insertBookingServices->execute();

        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        exit();
    } else {
        echo "Booking failed: " . $insertBooking->error;
    }
}
