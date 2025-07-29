<?php


require '../../Config/dbcon.php';


session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);


function addition($a, $b, $c)
{
    return $a + $b + $c;
}

function multiplication($a, $b)
{
    return $a * $b;
}


if (isset($_POST['bookRates'])) {
    // $date = mysqli_real_escape_string($conn, $_POST['date']);
    // $timeRange = mysqli_real_escape_string($conn, $_POST['timeRange']);
    // $services = isset($_POST['services']) ? $_POST['services'] : [];

    $bookingStatus = 1;
    $scheduledStartDate = mysqli_real_escape_string($conn, $_POST['scheduledStartDate']);
    $scheduledEndDate = mysqli_real_escape_string($conn, $_POST['scheduledEndDate']);
    $hoursNumber = mysqli_real_escape_string($conn, $_POST['hoursNumber']);

    $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
    $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);
    $totalPax = addition($adultCount, $childrenCount, 0);

    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);

    $totalCost = mysqli_real_escape_string($conn, $_POST['totalCost']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);

    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);


    $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);  //Day, Night, Overnight
    $childRate = mysqli_real_escape_string($conn, $_POST['childrenRate']);
    $adultRate = mysqli_real_escape_string($conn, $_POST['adultRate']);
    $childrenServiceID = mysqli_real_escape_string($conn, $_POST['childrenServiceID']);
    $adultServiceID = mysqli_real_escape_string($conn, $_POST['adultServiceID']);

    $cottageChoices = !empty($_POST['cottageSelections']) ? array_map('trim', explode(', ', $_POST['cottageSelections'])) : [];
    $roomChoices = !empty($_POST['roomSelections']) ? array_map('trim', explode(', ', $_POST['roomSelections'])) : [];
    $addOnsServices = !empty($_POST['addOnsServices']) ? array_map('trim', explode(', ', $_POST['addOnsServices'])) : [];




    $serviceIDs = [];
    $servicePrices = [];
    $serviceCapacity = [];
    $services = [];

    if (!empty($cottageChoices)) { //get selected cottages
        $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription FROM services s
            INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
            WHERE RServiceName = ?";

        $getServiceChoiceQuery = $conn->prepare($sql);

        foreach ($cottageChoices as $selectedCottage) {
            $selectedCottage = trim($selectedCottage);
            $getServiceChoiceQuery->bind_param('s', $selectedCottage);
            $getServiceChoiceQuery->execute();
            $getServiceChoiceResult = $getServiceChoiceQuery->get_result();

            if ($getServiceChoiceResult->num_rows > 0) {
                while ($data = $getServiceChoiceResult->fetch_assoc()) {
                    $serviceIDs[] = $data['serviceID'];
                    $servicePrices[] = $data['RSprice'];
                    $serviceCapacity[] = $data['RScapacity'];
                    $services[] = $data['RServiceName'];
                    // $description[] = $data['RSdescription'];
                }
            } else {
                echo "Service not found for: " . htmlspecialchars($selectedCottage);
                exit();
            }
        }
    } elseif (!empty($roomChoices)) { //Get selected rooms
        $duration = '11 hours';
        $trimmedDuration = trim($duration);

        $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription FROM services s
            INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
            WHERE rs.RServiceName = ? AND rs.RSduration = ?";

        $getServiceChoiceQuery = $conn->prepare($sql);

        foreach ($roomChoices as $selectedRoom) {
            $selectedRoom = trim($selectedRoom);
            $getServiceChoiceQuery->bind_param('ss', $selectedRoom,  $duration);
            $getServiceChoiceQuery->execute();
            $getServiceChoiceResult = $getServiceChoiceQuery->get_result();

            if ($getServiceChoiceResult->num_rows > 0) {
                while ($data = $getServiceChoiceResult->fetch_assoc()) {
                    $serviceIDs[] = $data['serviceID'];
                    $servicePrices[] = $data['RSprice'];
                    $serviceCapacity[] = $data['RScapacity'];
                    $services[] = $data['RServiceName'];
                    // $description[] = $data['RSdescription'];
                }
            } else {
                echo "Service not found for: " . htmlspecialchars($selectedRoom);
                exit();
            }
        }
    }

    //Get Selected Entertainment 
    $getEntertainment = $conn->prepare("SELECT s.serviceID, rs.RSprice, rs.RServiceName, rs.RScapacity
            FROM services s
            INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
            WHERE RServiceName = ?");

    foreach ($addOnsServices as $entertainment) {
        $selectedEntertainment = trim($entertainment);
        $getEntertainment->bind_param('s',  $selectedEntertainment);
        $getEntertainment->execute();
        $resultGetEntertainment = $getEntertainment->get_result();

        if ($resultGetEntertainment->num_rows > 0) {
            while ($row = $resultGetEntertainment->fetch_assoc()) {
                $serviceIDs[] = $row['serviceID'];
                $servicePrices[] = $row['RSprice'];
                $services[] = $row['RServiceName'];
                $serviceCapacity[] = $data['RScapacity'] ?? 0;
            }
        } else {
            echo "Service not found for: " . htmlspecialchars($selectedEntertainment);
            exit();
        }
    }



    $totalAdultFee = multiplication($adultRate, $adultCount);

    $totalAdultFee = multiplication($adultCount, $adultRate);
    $totalChildFee =  multiplication($childrenCount, $childRate);
    $totalEntranceFee = addition($totalAdultFee, $totalChildFee, 0);

    $downPayment = 0.00;
    $addOns = is_array($addOnsServices) ? implode(', ', $addOnsServices) : $addOnsServices;

$insertBooking = $conn->prepare("INSERT INTO 
        bookings(userID, additionalRequest,  paxNum, hoursNum, 
        startDate, endDate, 
        totalCost, downpayment, 
        addOns, paymentMethod, bookingStatus, bookingType) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
    $insertBooking->bind_param(
        "isiissddssis",
        $userID,
        $additionalRequest,
        $totalPax,
        $hoursNumber,
        $scheduledStartDate,
        $scheduledEndDate,
        $totalCost,
        $downPayment,
        $addOns,
        $paymentMethod,
        $bookingStatus,
        $bookingType
    );
    if ($insertBooking->execute()) {
        $bookingID = $conn->insert_id;

        $insertBookingServices = $conn->prepare("INSERT INTO 
    bookingservices(bookingID, serviceID, guests, bookingServicePrice)
    VALUES(?,?,?,?)");

        // Insert Adult Service if exists
        if ($adultCount > 0 && isset($adultServiceID)) {
            $insertBookingServices->bind_param("iiid", $bookingID, $adultServiceID, $adultCount, $totalAdultFee);
            $insertBookingServices->execute();
        }

        // Insert Children Service if exists
        if ($childrenCount > 0 && isset($childrenServiceID)) {
            $insertBookingServices->bind_param("iiid", $bookingID, $childrenServiceID, $childrenCount, $totalChildFee);
            $insertBookingServices->execute();
        }

        // Insert Other Services (Cottages, Rooms, Add-ons)
        if (!empty($serviceIDs)) {
            for ($i = 0; $i < count($serviceIDs); $i++) {
                $serviceID = $serviceIDs[$i];
                $capacity = $serviceCapacity[$i];
                $servicePrice = $servicePrices[$i];

                $insertBookingServices->bind_param("iiid", $bookingID, $serviceID, $capacity, $servicePrice);
                $insertBookingServices->execute();
            }
        }


        $receiver = 'Admin';
        $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request.';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        $insertBookingNotificationRequest->execute();

        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        exit();
    } else {
        echo "Booking failed: " . $insertBooking->error;
    }
}