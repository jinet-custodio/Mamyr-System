<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();


$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
//for setting image paths in 'include' statements
$baseURL = '../..';

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 3:
        $role = "Admin";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}


unset($_SESSION['formData']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/confirmBooking.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">

    <!-- Font Awesome Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Boxicon Link -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="body">

    <!-- Get name of customer -->

    <?php
    $name = '';
    $getUserInfo = $conn->prepare("SELECT firstName, lastName, middleInitial, email, phoneNumber FROM user WHERE userID = ? AND userRole = ?");
    $getUserInfo->bind_param("ii", $userID, $userRole);
    $getUserInfo->execute();
    $getUserInfoResult = $getUserInfo->get_result();
    if ($getUserInfoResult->num_rows > 0) {
        $data =  $getUserInfoResult->fetch_assoc();
        $firstName = $data['firstName'] ?? "";
        $middleInitial = trim($data['middleInitial']  ?? "");
        $name = ucfirst($firstName)  . " " . ucfirst($middleInitial) . ". "  . ucfirst($data['lastName'] ?? "");
        $email = $data['email'] ?? '';
        $phoneNumber = $data['phoneNumber'] ?? null;
    }
    ?>



    <!-- Functions  -->
    <?php


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

    ?>

    <!-- For Resort Booking -->
    <?php
    $cottageChoices = [];
    $roomChoices = [];
    $entertainmentChoices = [];
    if (isset($_POST['bookRates'])) {
        $scheduledDate = mysqli_real_escape_string($conn, $_POST['resortBookingDate']);
        $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);

        $adultCount = (int) $_POST['adultCount'] ?? 0;
        $childrenCount = (int) $_POST['childrenCount'] ?? 0;
        $toddlerCount = (int) $_POST['toddlerCount'] ?? 0;

        $cottageChoices = isset($_POST['cottageOptions']) ? $_POST['cottageOptions'] : [];
        $roomChoices = isset($_POST['roomOptions']) ? $_POST['roomOptions'] : [];
        $entertainmentChoices = isset($_POST['entertainmentOptions']) ? $_POST['entertainmentOptions'] : [];

        $additionalRequest = isset($_POST['additionalRequest']) && trim($_POST['additionalRequest']) !== ''
            ? mysqli_real_escape_string($conn, $_POST['additionalRequest'])
            : 'None';


        $paymentMethod = "GCash";
        $bookingType = 'Resort';
        $page = 'resortBooking.php';
        $buttonName = 'bookRates';
        $bookingFunctionPage = 'entranceBooking.php';

        $adultRate = 0;
        $childRate = 0;

        $serviceIDs = [];
        $servicePrices = [];
        $serviceCapacity = [];
        $services = [];
        $items = [];
        $tourType = '';

        //Get the rates
        $query = $conn->prepare("SELECT er.ERprice, er.ERcategory, s.serviceID 
            FROM entrancerate er
            JOIN service s ON s.entranceRateID = er.entranceRateID
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
        $getTimeRange = $conn->prepare("SELECT * FROM entrancetimerange");
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
            $tourType =  $services[] =  "Overnight Tour";
            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
        } elseif ($tourSelections === 'Day') {
            $startDateObj = new DateTime($scheduledDate . ' ' . $dayStartTime);
            $endDateObj = new DateTime($scheduledDate . ' ' . $dayEndTime);

            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
            $tourType = $services[] =  "Day Tour";
        } elseif ($tourSelections === 'Night') {
            $startDateObj = new DateTime($scheduledDate . ' ' . $nightStartTime);
            $endDateObj = new DateTime($scheduledDate . ' ' . $nightEndTime);

            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
            $tourType = $services[] = "Night Tour";
        } else {
            $startDateObj = new DateTime($scheduledDate);
            $endDateObj = clone $startDateObj;

            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
        }

        $startDate = $startDateObj->format("F d, Y");
        $endDate = $endDateObj->format("F d, Y");

        if ($startDate === $endDate) {
            $date = $startDate;
        } else {
            $date = $startDate . " - " . $endDate;
        }

        $timeRange = $startDateObj->format("g:i A") . " - " . $endDateObj->format("g:i A");

        $scheduledStartDate = $startDateObj->format('Y-m-d H:i:s');
        $scheduledEndDate =  $endDateObj->format('Y-m-d H:i:s');

        if (!empty($cottageChoices)) { //get selected cottages
            $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription FROM service s
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
                        $serviceIDs[] = $data['serviceID'];
                        $servicePrices[] = $data['RSprice'];
                        $serviceCapacity[] = $data['RScapacity'];
                        $services[] = $data['RServiceName'];


                        $items[] = [
                            'serviceName' =>  ucfirst($data['RServiceName'] ?? 'N/A'),
                            'description' => ucfirst($data['RSdescription'] ?? 'N/A'),
                            'price' => (float) $data['RSprice']
                        ];
                    }
                } else {
                    echo "Service not found. MySQL error: " . mysqli_error($conn);
                    echo "<br>Query: $getServiceChoiceQuery";
                    exit();
                }
            }
        }
        if (!empty($roomChoices)) { //Get selected rooms
            $duration = '11 hours';
            $trimmedDuration = trim($duration);

            $sql = "SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription FROM service s
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
                        $serviceIDs[] = $data['serviceID'];
                        $servicePrices[] = $data['RSprice'];
                        $serviceCapacity[] = $data['RScapacity'];
                        $services[] = $data['RServiceName'];

                        $items[] = [
                            'serviceName' =>  $data['RServiceName'],
                            'description' =>  "Good for " . $data['RScapacity'] . " pax",
                            'price' => $data['RSprice'],
                        ];
                    }
                } else {
                    echo "Service not found. MySQL error: " . htmlspecialchars($selectedRoom);
                    echo "<br>Query: $sql";
                    exit();
                }
            }
        }



        $entertainmentPrice = [];
        $entertainmentName = [];
        $entertainmentIDs = [];
        $addOnsServices = [];
        //Get Selected Entertainment 
        $getEntertainment = $conn->prepare("SELECT s.serviceID, rs.RSprice, rs.RServiceName  
            FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
            WHERE RServiceName = ?");

        foreach ($entertainmentChoices as $entertainment) {
            $selectedEntertainment = trim($entertainment);
            $getEntertainment->bind_param('s',  $selectedEntertainment);
            $getEntertainment->execute();
            $resultGetEntertainment = $getEntertainment->get_result();

            if ($resultGetEntertainment->num_rows > 0) {
                while ($row = $resultGetEntertainment->fetch_assoc()) {
                    $entertainmentPrice[] = $row['RSprice'];
                    $entertainmentName[] = $row['RServiceName'];
                    $entertainmentIDs[] = $row['serviceID'];
                    $addOnsServices[] = $row['RServiceName'];
                    // $addOns[] = $row['RServiceName'] . ' - ₱' . number_format($row['RSprice'], 2);
                }
            }
        }


        $totalPax = addition($adultCount, $childrenCount, $toddlerCount);
        $adultKidsCount = addition($adultCount, $childrenCount, 0);
        $totalAdultFee = multiplication($adultCount, $adultRate);
        $totalChildFee =  multiplication($childrenCount, $childRate);
        $totalEntranceFee = addition($totalAdultFee, $totalChildFee, 0);

        $totalCapacity = arrayAddition($serviceCapacity);

        $totalServicePrice = arrayAddition($servicePrices);
        $totalEntertainmentPrice = arrayAddition($entertainmentPrice);
        $totalCost = addition($totalEntertainmentPrice, $totalServicePrice, $totalEntranceFee);

        $numberOfPeople =
            ($adultCount > 0 ? "{$adultCount} Adults" : '') .
            ($childrenCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$childrenCount} Kids" : '') .
            ($toddlerCount > 0 ? (($adultCount > 0 || $childrenCount > 0) ? ' & ' : '') . "{$toddlerCount} toddlers" : '');


        $downPayment = $servicePrices[0];

        $_SESSION['resortFormData'] = $_POST;
    }

    // error_log("Services " . print_r($services, true));
    // error_log("Add Ons " . print_r($addOnsServices, true));
    ?>

    <!-- For Hotel Booking -->

    <?php
    $selectedHotels = [];
    if (isset($_POST['hotelBooking'])) {
        $hoursSelected = "22 hours";
        $arrivalTime = isset($_POST['arrivalTime']) ? mysqli_real_escape_string($conn, $_POST['arrivalTime']) : '';
        $scheduledStartDate = mysqli_real_escape_string($conn, $_POST['checkInDate']);
        $scheduledEndDate = mysqli_real_escape_string($conn, $_POST['checkOutDate']);

        $adultCount = (int) $_POST['adultCount'] ?? 0;
        $childrenCount = (int)  $_POST['childrenCount'] ?? 0;
        $toddlerCount = (int) $_POST['toddlerCount'] ?? 0;

        $selectedHotels = isset($_POST['hotelSelections']) ? $_POST['hotelSelections'] : [];
        $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);

        $buttonName = 'hotelBooking';
        $bookingType = 'Hotel';
        $page = 'hotelBooking.php';
        $bookingFunctionPage = 'hotelBooking.php';
        $additionalRequest = "None";
        $chargeType = 'Room';
        $perHead = 'Per Head';
        $perHour = 'Per Hour';
        $excessChargePerPerson = 0;
        $hourlyFee = 0;
        $getServicePricingHead = $conn->prepare("SELECT `price` FROM `servicepricing` WHERE pricingType = ? AND `chargeType` = ?");
        $getServicePricingHead->bind_param('ss',  $perHead, $chargeType);
        $getServicePricingHead->execute();
        $result = $getServicePricingHead->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $excessChargePerPerson = $row['price'];
        } else {
            $excessChargePerPerson = 250;
        }

        $getServicePricingHour = $conn->prepare("SELECT `price` FROM `servicepricing` WHERE pricingType = ? AND `chargeType` = ?");
        $getServicePricingHour->bind_param('ss', $perHour, $chargeType);
        $getServicePricingHour->execute();
        $result = $getServicePricingHour->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hourlyFee = $row['price'];
        } else {
            $hourlyFee = 500;
        }

        $additionalCharge = 0;
        $additionalGuest = 0;
        $totalCost = 0;

        $services = [];
        $serviceIDs = [];
        $capacity = [];
        $hotelPrices = [];
        $descriptions = [];
        $items = [];
        if ($arrivalTime) {
            $arrivalTimeObj = new DateTime($arrivalTime);
            $arrivalTimeText = $arrivalTime = $arrivalTimeObj->format('g:i a');
        } else {
            $arrivalTimeText = 'Not Stated';
            $arrivalTime = '00:00:00';
        }


        $selectedHotelQuery = $conn->prepare("SELECT * FROM service s
            JOIN resortamenity ra ON s.resortServiceID = ra.resortServiceID
            WHERE ra.RServiceName = ? AND ra.RSduration = ?");

        foreach ($selectedHotels as $selectedHotel) {
            $selectedHotel = trim($selectedHotel);

            $selectedHotelQuery->bind_param("ss", $selectedHotel, $hoursSelected);
            $selectedHotelQuery->execute();
            $resultHotelQuery = $selectedHotelQuery->get_result();
            if ($resultHotelQuery->num_rows > 0) {

                while ($data = $resultHotelQuery->fetch_assoc()) {
                    $serviceIDs[] = $data['serviceID'];
                    $capacity[] = $data['RScapacity'];
                    $hotelPrices[] = $data['RSprice'];
                    $descriptions[] = $data['RSdescription'];
                    $services[] = $data['RServiceName'];

                    $items[] = [
                        'serviceName' =>  $data['RServiceName'],
                        'description' => $data['RSdescription'],
                        'price' => $data['RSprice']
                    ];
                }
            }
        }



        $numberOfPeople =
            ($adultCount > 0 ? "{$adultCount} Adults" : '') .
            ($childrenCount > 0 ? ($adultCount > 0 ? ' & ' : '') . "{$childrenCount} Kids" : '') .
            ($toddlerCount > 0 ? (($adultCount > 0 || $childrenCount > 0) ? ' & ' : ' ') . "{$toddlerCount} toddler" : '');


        $startDateObj = new DateTime($scheduledStartDate);
        $endDateObj = new DateTime($scheduledEndDate);
        $startDate = $startDateObj->format("F d, Y");
        $endDate = $endDateObj->format("F d, Y");

        if ($startDate === $endDate) {
            $date = $startDate;
        } else {
            $date = $startDate . " - " . $endDate;
        }

        $interval = $startDateObj->diff($endDateObj);

        $durationParts = [];
        if ($interval->d > 0) $durationParts[] = $interval->d . " day" . ($interval->d > 1 ? "s" : "");
        if ($interval->h > 0) $durationParts[] = $interval->h . " hour" . ($interval->h > 1 ? "s" : "");
        if ($interval->i > 0) $durationParts[] = $interval->i . " minute" . ($interval->i > 1 ? "s" : "");

        $duration = implode(" & ", $durationParts);

        $timeRange = $startDateObj->format("g:i A") . " - " . $endDateObj->format("g:i A") . ' ( ' . $duration . ')';

        $numHours = ($interval->d * 24) + $interval->h + ($interval->i / 60);
        $computedHotelPrice = [];
        $additionalFeePerHour = 0;
        $remainingHours = 0;
        $fullBlocks = 0;
        // print_r($numHours);
        if ($numHours <= 24 && $numHours <= 22) {
            $fullBlocks = floor($numHours / 22);
        } else {
            $remainingHours = fmod($numHours, 24);
            $fullBlocks = floor(($numHours - $remainingHours) / 24);
            if ($remainingHours == 22) {
                $remainingHours = 0;
                $fullBlocks += 1;
            } else {
                $remainingHours;
            }
        }

        foreach ($hotelPrices as $hotelPrice) {
            if ($remainingHours != 0) {
                $additionalFeePerHour = $remainingHours * $hourlyFee;
            }
            $computedHotelPrice[] = ($fullBlocks * $hotelPrice) + $additionalFeePerHour;
        }

        // print_r('remaininghours ' . $remainingHours);
        // print_r($computedHotelPrice);
        $totalCapacity = arrayAddition($capacity);
        $totalPax = addition($childrenCount, $adultCount, $toddlerCount);
        $adultChildrenCount = addition($childrenCount, $adultCount, 0);
        $totalHotelPrice = arrayAddition($computedHotelPrice);


        if ($totalCapacity < $adultChildrenCount) {
            $additionalGuest = subtraction($adultChildrenCount, $totalCapacity,  0);
            $additionalCharge = multiplication($additionalGuest, $excessChargePerPerson);
            $totalCost = addition($totalHotelPrice, $additionalCharge, 0);
        } else {
            $totalCost =  $totalHotelPrice;
        }

        $totalServicePrice = $totalHotelPrice;

        $downPayment = multiplication($totalCost, .3);

        $_SESSION['hotelFormData'] = $_POST;
    }
    ?>

    <form action="../../Function/Booking/<?= htmlspecialchars($bookingFunctionPage) ?>" method="POST">

        <div class="page-header">

            <a href="<?= $page ?>" class="btn"><img src="../../Assets/Images/Icon/arrowBtnBlue.png"
                    alt="Back Button Image"></a>

            <h2 class="page-header-title">Booking Summary</h2>
        </div>

        <div class="container">
            <input type="hidden" name="firstName" value="<?= ucfirst($firstName ?? '') ?>">
            <input type="hidden" name="email" value="<?= $email ?? '' ?>">
            <input type="hidden" name="phoneNumber" value="<?= $phoneNumber ?? null ?>">

            <div class="card booking-summary" style="width: 50%;">
                <div class="card-info">
                    <h5 class="info-title">Booking Type:</h5>
                    <p> <?= $bookingType ?> Booking </p>
                    <input type="hidden" id="bookingType" name="bookingType" value="<?= $bookingType ?>">
                </div>

                <div class="card-info">
                    <h5 class="info-title">Customer Name:</h5>
                    <p class="card-text"><?= $name ?></p>
                    <input type="hidden" name="customerName" value="<?= $name ?>">
                </div>
                <?php if ($bookingType === 'Resort'): ?>
                    <div class="card-info">
                        <h5 class="info-title">Tour Type:</h5>
                        <p class="card-text"><?= htmlspecialchars($tourSelections) ?> Swimming</p>
                    </div>
                <?php endif; ?>

                <div class="card-info">
                    <h5 class="info-title" id='date'>Date:</h5>
                    <p class="card-text"><?= $date ?></p>
                    <input type="hidden" name="date" value="<?= $date ?>">
                </div>

                <div class="card-info">
                    <h5 class="info-title">Time Range:</h5>
                    <p class="card-text"><?= $timeRange ?></p>
                    <input type="hidden" name="timeRange" value="<?= $timeRange ?>">
                </div>

                <?php if ($bookingType === 'Hotel') { ?>
                    <div class="card-info">
                        <h5 class="info-title">Arrival Time:</h5>
                        <p class="card-text"><?= $arrivalTimeText ?></p>
                        <input type="hidden" name="arrivalTime" value="<?= $arrivalTime ?>">
                    </div>
                <?php } ?>

                <div class="card-info">
                    <h5 class="info-title">Number of People:</h5>
                    <p class="card-text"><?= $numberOfPeople ?></p>
                    <input type="hidden" name="totalPax" value="<?= $totalPax ?>">
                </div>


                <div class="card-info" id="descriptionContainer">
                    <h5 class="info-title">Services & Description: </h5>
                    <ul class="card-text">
                        <?php foreach ($items as $service): ?>
                            <li>
                                <strong><?= htmlspecialchars($service['serviceName']) ?> &mdash; ₱<?= number_format($service['price'], 2) ?></strong>
                                <ul>
                                    <li class="features"><?= htmlspecialchars($service['description']) ?></li>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <input type="hidden" name="capacity" value="<?= $totalCapacity ?>">
                </div>


                <?php if ($bookingType === 'Resort') { ?>
                    <div class="card-info">
                        <h5 class="info-title">Additional Services:</h5>
                        <p class="card-text">
                            <?= !empty($addOnsServices) ? htmlspecialchars(implode(', ', $addOnsServices)) : 'None' ?></p>
                        <?php foreach ($addOnsServices as $addOns): ?>
                            <input type="hidden" name="addOnsServices[]" value="<?= htmlspecialchars($addOns) ?>">
                        <?php endforeach; ?>
                    </div>
                <?php } else if ($bookingType === 'Hotel') { ?>
                    <div class="card-info">
                        <h5 class="info-title">Additional Guest:</h5>
                        <p class="card-text"><?= !empty($additionalGuest) ? htmlspecialchars($additionalGuest) : 'None' ?>
                        </p>
                        <input type="hidden" name="additionalGuest"
                            value="<?= !empty($additionalGuest) ? $additionalGuest : 0 ?>">
                    </div>
                <?php } ?>

                <div class="card-info" id="addRequest">
                    <h5 class="info-title">Additional Request:</h5>
                    <p class="card-text"><?= htmlspecialchars($additionalRequest) ?></p>
                    <input type="hidden" name="additionalRequest" value="<?= htmlspecialchars($additionalRequest) ?>"
                        class="form-control ">
                </div>
            </div>

            <div class="card payment-summary" style="width:40%;">

                <h5 class="cardHeader">Payment Summary</h5>

                <ul class="list-group list-group-flash">

                    <li class="list-group-item payment-info">
                        <h5 class="card-title">Payment Method:</h5>
                        <p class="card-text"><?= htmlspecialchars($paymentMethod) ?></p>
                        <input type="hidden" name="paymentMethod" value="<?= htmlspecialchars($paymentMethod) ?>"
                            class="card-content">
                    </li>

                    <?php if ($bookingType === "Resort") { ?>
                        <li class="list-group-item payment-info" id="entranceFeeDiv">
                            <h5 class="card-title">Entrance Fee:</h5>
                            <p class="card-text">₱ <?= htmlspecialchars(number_format($totalEntranceFee, 2)) ?></p>
                            <input type="hidden" name="entranceFee" value="<?= htmlspecialchars($totalEntranceFee) ?>"
                                class="card-content">
                        </li>
                    <?php }  ?>

                    <li class="list-group-item payment-info">
                        <h5 class="card-title">Service Fee:</h5>
                        <p class="card-text">₱ <?= htmlspecialchars(number_format($totalServicePrice, 2)) ?></p>
                        <input type="hidden" name="serviceFee" value="<?= htmlspecialchars($totalServicePrice) ?>"
                            class="card-content">
                    </li>

                    <?php if ($bookingType === "Hotel") { ?>
                        <li class="list-group-item payment-info" id="additionalFeesDiv">
                            <h5 class="card-title mb-2">Additional Fee:</h5>

                            <div class="fee-item">
                                <span>Per Head: (₱<?= htmlspecialchars(number_format($excessChargePerPerson, 2)) ?>)</span>
                                <span>₱<?= htmlspecialchars(number_format($additionalCharge, 2)) ?></span>
                            </div>

                            <div class="fee-item">
                                <span>Per Hour: (₱<?= htmlspecialchars(number_format($hourlyFee, 2)) ?>)</span>
                                <span>₱<?= htmlspecialchars(number_format($additionalFeePerHour, 2)) ?></span>
                            </div>

                            <input type="hidden" name="additionalFeePerHead" value="<?= htmlspecialchars($additionalCharge) ?>" class="card-content">
                            <input type="hidden" name="additionalFeePerHour" value="<?= htmlspecialchars($additionalFeePerHour) ?>" class="card-content">
                        </li>

                    <?php } else { ?>
                        <li class="list-group-item payment-info" id="entertainmentDiv">
                            <h5 class="card-title">Additional Service(s) Fee:</h5>
                            <p class="card-text">₱ <?= htmlspecialchars(number_format($totalEntertainmentPrice, 2)) ?></p>
                            <input type="hidden" name="additionalServiceFee"
                                value="<?= htmlspecialchars($totalEntertainmentPrice) ?>" class="card-content">
                        </li>
                    <?php } ?>

                    <li class="list-group-item payment-info downpayment-container">
                        <h5 class=" card-title">Downpayment: <br> <small class="text-muted">This amount secures your booking.</small></h5>
                        <p class="card-text">₱ <?= number_format($downPayment, 2) ?> </p>
                        <input type="hidden" name="downPayment" value="<?= $downPayment ?>" class="card-content">
                    </li>

                    <li class="list-group-item payment-info">
                        <h5 class="card-title">Grand Total:</h5>
                        <p class="card-text">₱ <?= number_format($totalCost, 2) ?> </p>
                        <input type="hidden" name="totalCost" value="<?= $totalCost ?>" class="card-content">
                    </li>
                </ul>

                <div class="button-container w-100">
                    <button type="submit" class="btn btn-primary w-75 loaderTrigger" name="<?= $buttonName ?>">Book Now</button>
                </div>

            </div>

        </div>

        <!--  Get mamyr contacts -->

        <?php
        $find = 'ContactNum';
        $getContactQuery = $conn->prepare("SELECT resortInfoName, resortInfoDetail FROM resortinfo WHERE resortInfoName = ?");
        $getContactQuery->bind_param('s', $find);
        if (!$getContactQuery->execute()) {
            error_log('Error: ' . $getContactQuery->error);
        }

        $result = $getContactQuery->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $contactNumber = $row['resortInfoDetail'] ?? 'Not Stated';
        }

        ?>

        <div class="note">
            <?php if ($bookingType === "Resort") { ?>
                <ul>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        Payment for the cottage reservation must be made through the resort's GCash account.
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        If you reserve more than one cottage, a down payment is required for only one cottage.
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        The remaining balance should be paid directly at the resort upon arrival.
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        Upon arrival, the staff will verify the number of guests.
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        For any questions, please contact <strong><?= $contactNumber ?></strong>.
                    </li>

                </ul>
            <?php } else if ($bookingType === 'Hotel') { ?>
                <ul>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        Downpayment(30%) is required. Once you pay the downpayment its not refundable but you can still
                        cancel the booking after 5-7 days of approval.
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        For any questions, contact <strong><?= $contactNumber ?></strong>.
                    </li>
                </ul>
            <?php } ?>
        </div>

        <div style="display: none;">
            <input type="hidden" name="resortBookingDate" value="<?= $scheduledDate ?? '' ?>">
            <input type="hidden" name="scheduledStartDate" value="<?= htmlspecialchars($scheduledStartDate ?? '') ?>">
            <input type="hidden" name="scheduledEndDate" value="<?= htmlspecialchars($scheduledEndDate ?? '') ?>">
            <input type="hidden" name="checkInDate" value="<?= htmlspecialchars($scheduledStartDate ?? '') ?>">
            <input type="hidden" name="checkOutDate" value="<?= htmlspecialchars($scheduledEndDate ?? '') ?>">
            <?php foreach ($cottageChoices as $choice): ?>
                <input type="hidden" name="cottageOptions[]" value="<?= htmlspecialchars($choice) ?>">
            <?php endforeach; ?>
            <?php foreach ($roomChoices as $choice): ?>
                <input type="hidden" name="roomOptions[]" value="<?= htmlspecialchars($choice) ?>">
            <?php endforeach; ?>
            <?php foreach ($selectedHotels as $choice): ?>
                <input type="hidden" name="hotelSelections[]" value="<?= htmlspecialchars($choice) ?>">
            <?php endforeach; ?>
            <input type="hidden" name="hoursSelected" value="<?= htmlspecialchars($hoursSelected ?? '') ?>">
            <input type="hidden" name="hoursNumber" value="<?= htmlspecialchars($numHours ?? '') ?>">

            <input type="hidden" name="tourSelections" value="<?= htmlspecialchars($tourSelections ?? '') ?>">

            <input type="hidden" name="adultServiceID" value="<?= htmlspecialchars($adultServiceID ?? '') ?>">
            <input type="hidden" name="childrenServiceID" value="<?= htmlspecialchars($childrenServiceID ?? '') ?>">
            <input type="hidden" name="adultCount" value="<?= htmlspecialchars($adultCount ?? 0) ?>">
            <input type="hidden" name="childrenCount" value="<?= htmlspecialchars($childrenCount ?? 0) ?>">
            <input type="hidden" name="toddlerCount" value="<?= htmlspecialchars($toddlerCount ?? 0) ?>">
            <input type="hidden" name="adultRate" value="<?= htmlspecialchars($adultRate ?? 0) ?>">
            <input type="hidden" name="childrenRate" value="<?= htmlspecialchars($childRate ?? 0) ?>">
            <input type="hidden" name="tourType" value="<?= htmlspecialchars($tourType ?? 'N/A') ?>">
        </div>

    </form>
    <?php include 'loader.php'; ?>
    </div>
    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js">
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>