<?php

require '../../Config/dbcon.php';
session_start();

// Made only for event booking, hotel and  resort booking still to follow

// Made only for event booking, hotel and  resort booking still to follow

if (isset($_POST['eventBook'])) {
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
    $eventPackage = mysqli_real_escape_string($conn, $_POST['eventPackage']);
    $additionalNotes = mysqli_real_escape_string($conn, $_POST['additionalNotes']);
    $other_input = mysqli_real_escape_string($conn, $_POST['other_input']);
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $userID =  $_SESSION['userID'];
    $eventDuration = mysqli_real_escape_string($conn, $_POST['eventDuration']);

    $duration = floatval($eventDuration);

    $startDateTime = new DateTime($eventDate);
    $intervalSpec = 'PT' . (int)round($duration * 60) . 'M'; // Convert hours to minutes
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval($intervalSpec));

    $startDateStr = $startDateTime->format('Y-m-d');
    $endDate = $endDateTime->format('Y-m-d');

    //if  babaguhin yung data type ng dates to datetime, ito magiging code 
    // $startDateStr = $startDateTime->format('Y-m-d H:i:s');
    // $endDateStr = $endDateTime->format('Y-m-d H:i:s');


    if ($eventPackage != '' || $additionalNotes != '') {
        $booking = "INSERT INTO bookings( `userID`, `packageID`, `additionalRequest`, `startDate`, `endDate`)
        VALUES('$userID', '$eventPackage', '$additionalNotes', '$eventDate', '$endDate')";

        $bookingResult = mysqli_query($conn, $booking);

        if ($bookingResult) {
            header("Location: ../../Pages/Customer/dashboard.php");
        } else {
            echo "Ahahha male";
        }
    }
}

//Get all the services
// function getServices($conn)
// {
//     $selectServices = "SELECT * FROM services";
//     $resultServices = mysqli_query($conn, $selectServices);
//     if (mysqli_num_rows($resultServices) > 0) {
//         $servicesData = mysqli_fetch_assoc($resultServices);
//         while ($servicesData) {
//             $services[] = $servicesData;
//         }
//     }

//     return $services;
// }

//Get all the packages
// function getPackages($conn)
// {
//     $selectPackages = "SELECT * FROM packages";
//     $resultPackages = mysqli_query($conn, $selectPackages);
//     if (mysqli_num_rows($resultPackages) > 0) {
//         $packagesData = mysqli_fetch_assoc($resultPackages);
//         while ($packagesData) {
//             $packages[] = $packagesData;
//         }
//     }

//     return $packages;
// }


//Get all the custom packages
// function getCustomPackages($conn)
// {
//     $selectCustomPackages = "SELECT * FROM custompackages";
//     $resultCustomPackages = mysqli_query($conn, $selectCustomPackages);
//     if (mysqli_num_rows($resultCustomPackages) > 0) {
//         $custompackagesData = mysqli_fetch_assoc($resultCustomPackages);
//         while ($custompackagesData) {
//             $customPackages[] = $custompackagesData;
//         }
//     }

//     return $customPackages;
// }
