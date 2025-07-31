<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
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
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Font Awesome Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

    <!-- Boxicon Link -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="body">

    <!-- Get name of customer -->

    <?php
    $getUserInfo = $conn->prepare("SELECT * FROM users WHERE userID = ? AND userRole = ?");
    $getUserInfo->bind_param("ii", $userID, $userRole);
    $getUserInfo->execute();
    $getUserInfoResult = $getUserInfo->get_result();
    if ($getUserInfoResult->num_rows > 0) {
        $data =  $getUserInfoResult->fetch_assoc();
        $middleInitial = trim($data['middleInitial']);
        $name = ucfirst($data['firstName']) . " " . ucfirst($data['middleInitial']) . " "  . ucfirst($data['lastName']);
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
    if (isset($_POST['bookRates'])) {
        $scheduledDate = mysqli_real_escape_string($conn, $_POST['resortBookingDate']);
        $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);

        $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
        $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);

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
        $description = [];

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
            $services[] =  "Overnight Tour";
            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
        } elseif ($tourSelections === 'Day') {
            $startDateObj = new DateTime($scheduledDate . ' ' . $dayStartTime);
            $endDateObj = new DateTime($scheduledDate . ' ' . $dayEndTime);

            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
            $services[] =  "Day Tour";
        } elseif ($tourSelections === 'Night') {
            $startDateObj = new DateTime($scheduledDate . ' ' . $nightStartTime);
            $endDateObj = new DateTime($scheduledDate . ' ' . $nightEndTime);

            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
            $services[] = "Night Tour";
        } else {
            $startDateObj = new DateTime($scheduledDate);
            $endDateObj = clone $startDateObj;

            $interval = $startDateObj->diff($endDateObj);
            $numHours = $interval->h + ($interval->days * 24);
        }

        $startDate = $startDateObj->format("d F Y");
        $endDate = $endDateObj->format("d F Y");

        if ($startDate === $endDate) {
            $date = $startDate;
        } else {
            $date = $startDate . " - " . $endDate;
        }

        $timeRange = $startDateObj->format("g:i A") . " - " . $endDateObj->format("g:i A");

        $scheduledStartDate = $startDateObj->format('Y-m-d H:i:s');
        $scheduledEndDate =  $endDateObj->format('Y-m-d H:i:s');

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
                    echo "Service not found. MySQL error: " . mysqli_error($conn);
                    echo "<br>Query: $getServiceChoiceQuery";
                    exit();
                }
            }
        }
        if (!empty($roomChoices)) { //Get selected rooms
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
            FROM services s
            INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
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
                    $addOns[] = $row['RServiceName'];
                }
            }
        }

        $adultCount = (int)(empty($adultCount) ? 0 : $adultCount);
        $childrenCount = (int)(empty($childrenCount) ? 0 : $childrenCount);



        $totalPax = addition($adultCount, $childrenCount, 0);
        $totalAdultFee = multiplication($adultCount, $adultRate);
        $totalChildFee =  multiplication($childrenCount, $childRate);
        $totalEntranceFee = addition($totalAdultFee, $totalChildFee, 0);

        $totalCapacity = arrayAddition($serviceCapacity);

        $totalServicePrice = arrayAddition($servicePrices);
        $totalEntertainmentPrice = arrayAddition($entertainmentPrice);
        $totalCost = addition($totalEntertainmentPrice, $totalServicePrice, $totalEntranceFee);

        $numberOfPeople =
            ($adultCount > 0 ? "{$adultCount} Adults" : '') .
            ($childrenCount > 0 ? ($adultCount > 0 ? ' and ' : '') . "{$childrenCount} Kids" : '');

        $downPayment = $servicePrices[0];
    }
    ?>

    <!-- For Hotel Booking -->

    <?php
    if (isset($_POST['hotelBooking'])) {

        $hoursSelected = mysqli_real_escape_string($conn, $_POST['hoursSelected']);
        $arrivalTime = mysqli_real_escape_string($conn, $_POST['arrivalTime']);
        $scheduledStartDate = mysqli_real_escape_string($conn, $_POST['checkInDate']);
        $scheduledEndDate = mysqli_real_escape_string($conn, $_POST['checkOutDate']);
        $adultCount = (int) mysqli_real_escape_string($conn, $_POST['adultCount']);
        $childrenCount = (int) mysqli_real_escape_string($conn, $_POST['childrenCount']);
        $selectedHotels = isset($_POST['hotelSelections']) ? $_POST['hotelSelections'] : [];
        $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);

        $buttonName = 'hotelBooking';
        $bookingType = 'Hotel';
        $page = 'hotelBooking.php';
        $bookingFunctionPage = 'hotelBooking.php';
        $additionalRequest = "None";
        $excessChargePerPerson = 250;

        $additionalCharge = 0;
        $additionalGuest = 0;
        $totalCost = 0;

        $services = [];
        $serviceIDs = [];
        $capacity = [];
        $hotelPrices = [];
        $descriptions = [];
        $items = [];

        $selectedHotelQuery = $conn->prepare("SELECT * FROM services s
            JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
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
                        'description' => $data['RSdescription']
                    ];
                }
            }
        }

        $totalCapacity = arrayAddition($capacity);
        $totalPax = addition($childrenCount, $adultCount, 0);
        $totalHotelPrice = arrayAddition($hotelPrices);


        if ($totalCapacity < $totalPax) {
            $additionalGuest = subtraction($totalPax, $totalCapacity,  0);
            $additionalCharge = multiplication($additionalGuest, $excessChargePerPerson);
            $totalCost = addition($totalHotelPrice, $additionalCharge, 0);
        } else {
            $totalCost =  $totalHotelPrice;
        }

        $totalServicePrice = $totalHotelPrice;

        $numberOfPeople =
            ($adultCount > 0 ? "{$adultCount} Adults" : '') .
            ($childrenCount > 0 ? ($adultCount > 0 ? ' and ' : '') . "{$childrenCount} Kids" : '');

        $downPayment = multiplication($totalCost, .3);

        $startDateObj = new DateTime($scheduledStartDate);
        $endDateObj = new DateTime($scheduledEndDate);
        $startDate = $startDateObj->format("d F Y");
        $endDate = $endDateObj->format("d F Y");

        if ($startDate === $endDate) {
            $date = $startDate;
        } else {
            $date = $startDate . " - " . $endDate;
        }

        $timeRange = $startDateObj->format("g:i A") . " - " . $endDateObj->format("g:i A");
    }
    ?>

    <form action="../../Function/Booking/<?= htmlspecialchars($bookingFunctionPage) ?>" method="POST">

        <div class="page-header">
            <a href="<?= $page ?>" class="btn"><img src="../../Assets/Images/Icon/back-button.png"
                    alt="Back Button Image"></a>

            <h2 class="page-header-title">Booking Summary</h2>
        </div>

        <div class="container-fluid">
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
                <div class="card-info">
                    <h5 class="info-title">Services:</h5>
                    <p class="card-text"><?= htmlspecialchars(implode(', ', $services)) ?></p>
                </div>

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

                <div class="card-info">
                    <h5 class="info-title">Number of People:</h5>
                    <p class="card-text"><?= $numberOfPeople ?></p>
                    <input type="hidden" name="totalPax" value="<?= $totalPax ?>">
                </div>

                <?php if ($bookingType === 'Resort') { ?>
                    <div class="card-info" id="capacityContainer">
                        <h5 class="info-title">Description:</h5>
                        <p class="card-text">Good for <?= $totalCapacity ?> people</p>
                        <input type="hidden" name="capacity"
                            value="<?= $totalCapacity ?>">
                    </div>
                <?php } else if ($bookingType === 'Hotel') { ?>
                    <div class="card-info" id="descriptionContainer">
                        <h5 class="info-title">Description:</h5>
                        <ul class="card-text">
                            <?php foreach ($items as $service): ?>
                                <li>
                                    <strong><?= htmlspecialchars($service['serviceName']) ?></strong>
                                    <ul>
                                        <?php foreach (explode(',', $service['description']) as $feature): ?>
                                            <li class="features"><?= htmlspecialchars(trim($feature)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <input type="hidden" name="capacity"
                            value="<?= $totalCapacity ?>">
                    </div>
                <?php } ?>

                <?php if ($bookingType === 'Resort') { ?>
                    <div class="card-info">
                        <h5 class="info-title">Additional Services:</h5>
                        <p class="card-text"><?= !empty($addOnsServices) ? htmlspecialchars(implode(', ', $addOnsServices)) : 'None' ?></p>
                        <input type="hidden" name="addOnsServices" value="<?= !empty($addOnsServices) ? htmlspecialchars(implode(', ', $addOnsServices)) : 'None' ?>">
                    </div>
                <?php } else if ($bookingType === 'Hotel') { ?>
                    <div class="card-info">
                        <h5 class="info-title">Additional Guest:</h5>
                        <p class="card-text"><?= !empty($additionalGuest) ? htmlspecialchars($additionalGuest) : 'None' ?></p>
                        <input type="hidden" name="additionalGuest" value="<?= !empty($additionalGuest) ? $additionalGuest : 0 ?>">
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
                        <input type="hidden" name="paymentMethod" value="<?= htmlspecialchars($paymentMethod) ?>" class="card-content">
                    </li>

                    <?php if ($bookingType === "Resort") { ?>
                        <li class="list-group-item payment-info" id="entranceFeeDiv">
                            <h5 class="card-title">Entrance Fee:</h5>
                            <p class="card-text">₱ <?= htmlspecialchars(number_format($totalEntranceFee, 2)) ?></p>
                            <input type="hidden" name="entranceFee" value="<?= htmlspecialchars($totalEntranceFee) ?>" class="card-content">
                        </li>
                    <?php } ?>

                    <li class="list-group-item payment-info">
                        <h5 class="card-title">Service Fee:</h5>
                        <p class="card-text">₱ <?= htmlspecialchars(number_format($totalServicePrice, 2)) ?></p>
                        <input type="hidden" name="serviceFee" value="<?= htmlspecialchars($totalServicePrice) ?>" class="card-content">
                    </li>

                    <?php if ($bookingType === "Hotel" || $bookingType === "Event") { ?>
                        <li class="list-group-item payment-info" id="entertainmentDiv">
                            <h5 class="card-title">Additional Fee:</h5>
                            <p class="card-text">₱ <?= htmlspecialchars(number_format($additionalCharge, 2)) ?></p>
                            <input type="hidden" name="additionalFee" value="<?= htmlspecialchars($additionalCharge) ?>" class="card-content">
                        </li>
                    <?php } else { ?>
                        <li class="list-group-item payment-info" id="entertainmentDiv">
                            <h5 class="card-title">Additional Service Fee:</h5>
                            <p class="card-text">₱ <?= htmlspecialchars(number_format($totalEntertainmentPrice, 2)) ?></p>
                            <input type="hidden" name="additionalServiceFee" value="<?= htmlspecialchars($totalEntertainmentPrice) ?>" class="card-content">
                        </li>
                    <?php } ?>

                    <li class="list-group-item payment-info">
                        <h5 class=" card-title">Downpayment (30%):</h5>
                        <p class="card-text">₱ <?= number_format($downPayment, 2) ?> </p>
                        <input type="hidden" name="downPayment" value="<?= $downPayment ?>" class="card-content">
                    </li>

                    <li class="list-group-item payment-info">
                        <h5 class="card-title">Total Cost:</h5>
                        <p class="card-text">₱ <?= number_format($totalCost, 2) ?> </p>
                        <input type="hidden" name="totalCost" value="<?= $totalCost ?>" class="card-content">

                    </li>
                </ul>

                <div class="button-container w-100">
                    <button type="submit" class="btn btn-primary w-75" name="<?= $buttonName ?>">Book Now</button>
                </div>

            </div>

        </div>

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
                        For any questions, please contact <strong>0900-000-0000</strong>.
                    </li>

                </ul>
            <?php } else if ($bookingType === 'Hotel') { ?>
                <ul>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        Downpayment(30%) is required. Once you pay the downpayment its not refundable but you can still cancel the booking after 5-7 days of approval.
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-info" style="color: #74C0FC;"></i>
                        For any questions, contact <strong>0900-000-0000</strong>.
                    </li>
                </ul>
            <?php } ?>
        </div>

        <div style="display: none;">
            <input type="hidden" name="scheduledStartDate" value="<?= htmlspecialchars($scheduledStartDate ?? '') ?>">
            <input type="hidden" name="scheduledEndDate" value="<?= htmlspecialchars($scheduledEndDate ?? '') ?>">
            <input type="hidden" name="cottageSelections" value="<?= htmlspecialchars(implode(', ', $cottageChoices ?? [])) ?>">
            <input type="hidden" name="roomSelections" value="<?= htmlspecialchars(implode(', ', $roomChoices ?? [])) ?>">
            <input type="hidden" name="hotelSelections" value="<?= htmlspecialchars(implode(', ', $selectedHotels ?? [])) ?>">
            <input type="hidden" name="hoursSelected" value="<?= htmlspecialchars($hoursSelected ?? '') ?>">
            <input type="hidden" name="hoursNumber" value="<?= htmlspecialchars($numHours ?? '') ?>">
            <input type="hidden" name="arrivalTime" value="<?= htmlspecialchars($arrivalTime ?? '') ?>">

            <input type="hidden" name="tourSelections" value="<?= htmlspecialchars($tourSelections ?? '') ?>">

            <input type="hidden" name="adultServiceID" value="<?= htmlspecialchars($adultServiceID ?? '') ?>">
            <input type="hidden" name="childrenServiceID" value="<?= htmlspecialchars($childrenServiceID ?? '') ?>">
            <input type="hidden" name="adultCount" value="<?= htmlspecialchars($adultCount ?? 0) ?>">
            <input type="hidden" name="childrenCount" value="<?= htmlspecialchars($childrenCount ?? 0) ?>">
            <input type="hidden" name="adultRate" value="<?= htmlspecialchars($adultRate ?? 0) ?>">
            <input type="hidden" name="childrenRate" value="<?= htmlspecialchars($childRate ?? 0) ?>">

        </div>

    </form>



    <footer class="py-1" id="footer" style="margin-top: 5rem;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            </a>
            <h3 class="mb-0">MAMYR RESORT AND EVENTS PLACE</h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter">(0998) 962 4697 </h4>
                <h4 class="emailAddressTextFooter">mamyr@gmail.com</h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter">Sitio Colonia, Gabihan, San Ildefonso, Bulacan</h4>

            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="https://www.facebook.com/p/Mamyr-Resort-Restaurant-Events-Place-100083298304476/"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="https://workspace.google.com/intl/en-US/gmail/"><i class='bx bxl-gmail'></i></a>
            <a href="tel:+09989624697">
                <i class='bx bxs-phone'></i>
            </a>
        </div>

    </footer>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>