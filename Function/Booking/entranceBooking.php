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
    $scheduledStartDate = mysqli_real_escape_string($conn, $_POST['scheduledStartDate']);
    $scheduledEndDate = mysqli_real_escape_string($conn, $_POST['scheduledEndDate']);
    $hoursNumber = mysqli_real_escape_string($conn, $_POST['hoursNumber']);

    $adultCount = (int) $_POST['adultCount'];
    $childrenCount = (int) $_POST['childrenCount'];
    $toddlerCount = (int) $_POST['toddlerCount'];
    $totalPax = addition($adultCount, $childrenCount, 0);

    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);

    $totalCost = (float) $_POST['totalCost'];
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);

    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);


    $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);  //Day, Night, Overnight
    $childRate = (float)  $_POST['childrenRate'];
    $adultRate = (float)  $_POST['adultRate'];
    $childrenServiceID = (int) $_POST['childrenServiceID'];
    $adultServiceID = (int) $_POST['adultServiceID'];

    $cottageChoices = !empty($_POST['cottageSelections']) ? array_map('trim', explode(', ', $_POST['cottageSelections'])) : [];
    $roomChoices = !empty($_POST['roomSelections']) ? array_map('trim', explode(', ', $_POST['roomSelections'])) : [];
    $addOnsServices = !empty($_POST['addOnsServices']) ? array_map('trim', explode(', ', $_POST['addOnsServices'])) : [];




    $serviceIDs = [];
    $servicePrices = [];
    $serviceCapacity = [];
    $services = [];
    $resortServiceIDs = [];

    if (!empty($cottageChoices)) { //get selected cottages
        $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription, rs.resortServiceID FROM services s
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
                    $resortServiceIDs[] = $data['resortServiceID'];
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
    }

    if (!empty($roomChoices)) { //Get selected rooms
        $duration = '11 hours';
        $trimmedDuration = trim($duration);

        $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription, rs.resortServiceID FROM services s
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
                    $resortServiceIDs[] = $data['resortServiceID'];
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
        $getEntertainment = $conn->prepare("SELECT s.serviceID, rs.RSprice, rs.RServiceName, rs.RScapacity, rs.resortServiceID
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
                    $resortServiceIDs[] = $row['resortServiceID'];
                    $serviceIDs[] = $row['serviceID'];
                    $servicePrices[] = $row['RSprice'];
                    $services[] = $row['RServiceName'];
                    $serviceCapacity[] = $row['RScapacity'] ?? 0;
                }
            } 
        }
 


    $totalAdultFee = multiplication($adultCount, $adultRate);
    $totalChildFee =  multiplication($childrenCount, $childRate);
    $totalEntranceFee = addition($totalAdultFee, $totalChildFee, 0);

    $downPayment = 0.00;
    $addOns = is_array($addOnsServices) ? implode(', ', $addOnsServices) : $addOnsServices;


    date_default_timezone_set('Asia/Manila');

    $scheduledStartDateObj = new DateTime($scheduledStartDate);
    $dateScheduled = $scheduledStartDateObj->format('F');


    $insertBooking = $conn->prepare("INSERT INTO 
        bookings(userID, additionalRequest, toddlerCount,  paxNum, hoursNum, 
        startDate, endDate, 
        totalCost, downpayment, 
        addOns, paymentMethod, bookingStatus, bookingType) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");

    if ($dateScheduled === 'March' || $dateScheduled === 'April' || $dateScheduled === 'May') {
        $bookingStatus = 1;
    } else {
        $bookingStatus = 2;
    }


    $insertBooking->bind_param(
        "isiiissddssis",
        $userID,
        $additionalRequest,
        $toddlerCount,
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


        if ($adultCount > 0 && isset($adultServiceID)) {
            $insertBookingServices->bind_param("iiid", $bookingID, $adultServiceID, $adultCount, $totalAdultFee);
            $insertBookingServices->execute();
        }


        if ($childrenCount > 0 && isset($childrenServiceID)) {
            $insertBookingServices->bind_param("iiid", $bookingID, $childrenServiceID, $childrenCount, $totalChildFee);
            $insertBookingServices->execute();
        }


        if (!empty($serviceIDs)) {
            for ($i = 0; $i < count($serviceIDs); $i++) {
                $serviceID = $serviceIDs[$i];
                $capacity = $serviceCapacity[$i];
                $servicePrice = $servicePrices[$i];

                $insertBookingServices->bind_param("iiid", $bookingID, $serviceID, $capacity, $servicePrice);
                $insertBookingServices->execute();
            }
        }

        $insertBookingServices->close();


        if ($bookingStatus ===  1) {
            $receiver = 'Admin';
            $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request.';
            $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notifications(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
            $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
            $insertBookingNotificationRequest->execute();

            header('Location: ../../Pages/Customer/bookNow.php?action=success');
            exit();
        } elseif ($bookingStatus === 2) {
            $today = date('Y m d');
            if ($today === $scheduledStartDate) {
                $downpaymentDueDate = $today;
            } else {
                $scheduledStartDateObj->modify('-1 day');
                $downpaymentDueDate  = $scheduledStartDateObj->format('Y-m-d');
            }

            $insertConfirmedBooking = $conn->prepare("INSERT INTO confirmedBookings(bookingID, CBpaymentMethod, CBdownpayment, CBtotalCost, userBalance, paymentDueDate )
                VALUES(?,?,?,?,?, ?)");
            $insertConfirmedBooking->bind_param("issdds", $bookingID, $paymentMethod, $downPayment, $totalCost, $totalCost, $downpaymentDueDate);
            $insertConfirmedBooking->execute();
            $insertConfirmedBooking->close();

            $insertUnavailableService = $conn->prepare("INSERT INTO serviceunavailabledates(resortServiceID, unavailableStartDate, unavailableEndDate) VALUES (?,?,?)");
            if (!empty($resortServiceIDs)) {
                for ($i = 0; $i < count($resortServiceIDs); $i++) {
                    $resortServiceID = $resortServiceIDs[$i];
                    $insertUnavailableService->bind_param("iss", $resortServiceID, $scheduledStartDate, $scheduledEndDate);
                    $insertUnavailableService->execute();
                }
            }
            $insertUnavailableService->close();

            $occupiedID = 2;

            $updateAvailabilityID = $conn->prepare("UPDATE resortAmenities SET RSAvailabilityID = ? WHERE resortServiceID = ?");
            if (!empty($resortServiceIDs)) {
                for ($i = 0; $i < count($resortServiceIDs); $i++) {
                    $resortServiceID = $resortServiceIDs[$i];
                    $updateAvailabilityID->bind_param("ii", $occupiedID, $resortServiceID);
                    $updateAvailabilityID->execute();
                }
            }

            header('Location: ../../Pages/Customer/bookNow.php?action=success');
            exit();
        }
        $conn->close();
    } else {
        echo "Booking failed: " . $insertBooking->error;
    }
}