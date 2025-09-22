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
    $serviceIDs = [];
    $servicePrices = [];
    $serviceCapacity = [];
    $services = [];
    $resortServiceIDs = [];

    $resortBookingDate = mysqli_real_escape_string($conn, $_POST['resortBookingDate']);
    $scheduledStartDate = mysqli_real_escape_string($conn, $_POST['scheduledStartDate']);
    $scheduledEndDate = mysqli_real_escape_string($conn, $_POST['scheduledEndDate']);
    $hoursNumber = mysqli_real_escape_string($conn, $_POST['hoursNumber']);

    $adultCount = (int) $_POST['adultCount'];
    $childrenCount = (int) $_POST['childrenCount'];
    $toddlerCount = (int) $_POST['toddlerCount'];
    $totalPax = addition($adultCount, $childrenCount, 0);

    $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);

    $totalCost = (float) $_POST['totalCost'];
    $downpayment = (float) $_POST['downPayment'];
    // $additionalCharge = (float) $_POST['additionalServiceFee'];
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);

    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);


    $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);  //Day, Night, Overnight
    $childRate = (float)  $_POST['childrenRate'];
    $adultRate = (float)  $_POST['adultRate'];
    $childrenServiceID = (int) $_POST['childrenServiceID'];
    $adultServiceID = (int) $_POST['adultServiceID'];

    $cottageChoices = !empty($_POST['cottageSelections']) ? $_POST['cottageSelections'] : [];
    $roomChoices = !empty($_POST['roomSelections']) ?  $_POST['roomSelections'] : [];
    $addOnsServices = !empty($_POST['addOnsServices']) ?  $_POST['addOnsServices'] : [];



    if (!empty($cottageChoices)) { //get selected cottages
        $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
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

        $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
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
            FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
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

    $addOns = is_array($addOnsServices) ? implode(', ', $addOnsServices) : $addOnsServices;


    date_default_timezone_set('Asia/Manila');

    $scheduledStartDateObj = new DateTime($scheduledStartDate);
    $dateScheduled = $scheduledStartDateObj->format('F');
    $arrivalTime = $scheduledStartDateObj->format('h:i:s');
    $conn->begin_transaction();

    try {

        $bookingStatus = ($dateScheduled === 'March' || $dateScheduled === 'April' || $dateScheduled === 'May') ? 1 : 2;

        $insertBooking = $conn->prepare("INSERT INTO 
        booking(userID, additionalRequest, toddlerCount, kidCount, adultCount, guestCount, durationCount, 
        startDate, endDate,
        totalCost, downpayment, 
        addOns, paymentMethod, bookingStatus, bookingType, arrivalTime) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $insertBooking->bind_param(
            "isiiiiissddssiss",
            $userID,
            $additionalRequest,
            $toddlerCount,
            $childrenCount,
            $adultCount,
            $totalPax,
            $hoursNumber,
            $scheduledStartDate,
            $scheduledEndDate,
            // $additionalCharge,
            $totalCost,
            $downpayment,
            $addOns,
            $paymentMethod,
            $bookingStatus,
            $bookingType,
            $arrivalTime
        );

        if (!$insertBooking->execute()) {
            $conn->rollback();
            throw new Exception('Error executing: ' . $insertBooking->error);
        }

        $bookingID = $conn->insert_id;

        $insertBookingServices = $conn->prepare("INSERT INTO 
                    bookingservice(bookingID, serviceID, guests, bookingServicePrice)
                    VALUES(?,?,?,?)");

        if ($adultCount > 0 && isset($adultServiceID)) {
            $insertBookingServices->bind_param("iiid", $bookingID, $adultServiceID, $adultCount, $totalAdultFee);
            if (!$insertBookingServices->execute()) {
                $conn->rollback();
                throw new Exception('Error insertion of services:' . $insertBookingServices->error);
            }
        }


        if ($childrenCount > 0 && isset($childrenServiceID)) {
            $insertBookingServices->bind_param("iiid", $bookingID, $childrenServiceID, $childrenCount, $totalChildFee);
            if (!$insertBookingServices->execute()) {
                $conn->rollback();
                throw new Exception('Error insertion of services:' . $insertBookingServices->error);
            }
        }


        if (!empty($serviceIDs)) {
            for ($i = 0; $i < count($serviceIDs); $i++) {
                $serviceID = $serviceIDs[$i];
                $capacity = $serviceCapacity[$i];
                $servicePrice = $servicePrices[$i];

                $insertBookingServices->bind_param("iiid", $bookingID, $serviceID, $capacity, $servicePrice);
                if (!$insertBookingServices->execute()) {
                    $conn->rollback();
                    throw new Exception('Error insertion of services:' . $insertBookingServices->error);
                }
            }
        }

        $receiver = 'Admin';
        $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request.';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, userID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);

        if (!$insertBookingNotificationRequest->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' . $insertBookingNotificationRequest->error);
        }

        if ($bookingStatus === 2) {
            $today = date('Y m d');
            if ($today === $scheduledStartDate) {
                $paymentDueDate = $downpaymentDueDate = $today;
            } else {
                $scheduledStartDateObj->modify('-1 day');
                $downpaymentDueDate  = $scheduledStartDateObj->format('Y-m-d');
                $paymentDueDate = $scheduledStartDate;
            }

            $approvedBy = 'System';
            $today = date('Y-m-d h:i:s');
            $updateApproval = $conn->prepare("UPDATE `booking` SET `approvedBy`= ?,`approvedDate`= ? WHERE bookingID = ?");
            $updateApproval->bind_param('ssi', $approvedBy, $today, $bookingID);
            if (!$updateApproval->execute()) {
                $conn->rollback();
                throw new Exception('Error :' . $insertConfirmedBooking->error);
            }
            $updateApproval->close();
            $insertConfirmedBooking = $conn->prepare("INSERT INTO confirmedbooking(bookingID, confirmedFinalBill, userBalance, downpaymentDueDate, paymentDueDate )
                VALUES(?,?,?,?,?)");
            $insertConfirmedBooking->bind_param("iddss", $bookingID,  $totalCost, $totalCost, $downpaymentDueDate, $paymentDueDate);
            if (!$insertConfirmedBooking->execute()) {
                $conn->rollback();
                throw new Exception('Error :' . $insertConfirmedBooking->error);
            }
            $insertConfirmedBooking->close();


            $insertUnavailableService = $conn->prepare("INSERT INTO serviceunavailabledate(resortServiceID, unavailableStartDate, unavailableEndDate) VALUES (?,?,?)");
            if (!empty($resortServiceIDs)) {
                for ($i = 0; $i < count($resortServiceIDs); $i++) {
                    $resortServiceID = $resortServiceIDs[$i];
                    $insertUnavailableService->bind_param("iss", $resortServiceID, $scheduledStartDate, $scheduledEndDate);
                    if (!$insertUnavailableService->execute()) {
                        $conn->rollback();
                        throw new Exception('Error :' . $insertUnavailableService->error);
                    }
                }
            }
            $insertUnavailableService->close();
        }

        unset($_SESSION['resortFormData']);
        $conn->commit();
        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Error: ' . $e->getMessage());
        $_SESSION['resortFormData'] = $_POST;
        header('Location: ../../../../Pages/Customer/resortBooking.php?action=errorBooking');
    } finally {
        $insertBookingServices->close();
    }
}
