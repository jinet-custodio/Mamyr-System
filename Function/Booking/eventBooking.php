<?php

require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');

$userID = intval($_SESSION['userID']);
$userRole = intval($_SESSION['userRole']);

if (isset($_POST['eventBook'])) {
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
    $guestNo = intval($_POST['guestNo']);
    $eventVenue = mysqli_real_escape_string($conn, $_POST['eventVenue']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);

    //Date and time
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDateTime']);
    $eventStartTime = mysqli_real_escape_string($conn, $_POST['eventStartTime']);
    $eventEndTime = mysqli_real_escape_string($conn, $_POST['eventEndTime']);

    //Food 
    $chickenSelected = !empty($_POST['chickenSelections']) ? array_map('trim', explode(',', $_POST['chickenSelections'])) : [];
    $porkSelected = !empty($_POST['porkSelections']) ? array_map('trim', explode(',', $_POST['porkSelections'])) : [];
    $pastaSelected = !empty($_POST['pastaSelections']) ? array_map('trim', explode(',', $_POST['pastaSelections'])) : [];
    $beefSelected = !empty($_POST['beefSelections']) ? array_map('trim', explode(',', $_POST['beefSelections'])) : [];
    $vegieSelected = !empty($_POST['vegieSelections']) ? array_map('trim', explode(',', $_POST['vegieSelections'])) : [];
    $seafoodSelected = !empty($_POST['seafoodSelections']) ? array_map('trim', explode(',', $_POST['seafoodSelections'])) : [];
    $drinkSelected = !empty($_POST['drinkSelections']) ? array_map('trim', explode(',', $_POST['drinkSelections'])) : [];
    $dessertSelected = !empty($_POST['dessertSelections']) ? array_map('trim', explode(',', $_POST['dessertSelections'])) : [];

    $startDateObj = new DateTime($eventDate);
    $endDateObj = clone $startDateObj;

    $startTime = strtotime($eventStartTime);
    $endTime = strtotime($eventEndTime);

    $startDateObj->setTimestamp($startTime);
    $endDateObj->setTimestamp($endTime);

    $startDate = $startDateObj->format('Y-m-d H:i:s');
    $endDate = $endDateObj->format('Y-m-d H:i:s');






    //if  babaguhin yung data type ng dates to datetime, ito magiging code 
    // $startDateStr = $startDateTime->format('Y-m-d H:i:s');
    // $endDateStr = $endDateTime->format('Y-m-d H:i:s');


    if ($eventPackage != '' || $additionalRequest != '') {
        $booking = "INSERT INTO bookings( `userID`, `packageID`, `additionalRequest`, `startDate`, `endDate`)
        VALUES('$userID', '$eventPackage', '$additionalRequest', '$eventDate', '$endDate')";

        $bookingResult = mysqli_query($conn, $booking);

        if ($bookingResult) {
            header("Location: ../../Pages/Customer/dashboard.php");
        } else {
            echo "Ahahha male";
        }
    }
}
