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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="body">

    <!-- Get name of customer -->
    <?php
    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $middleInitial = trim($data['middleInitial']);
        $name = ucfirst($data['firstName']) . " " . ucfirst($data['middleInitial']) . " "  . ucfirst($data['lastName']);
    }
    ?>

    <!-- For Resort Booking -->
    <?php
    if (isset($_POST['bookRates'])) {
        $scheduledDate = mysqli_real_escape_string($conn, $_POST['resortBookingDate']);
        $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);
        $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
        $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);
        $cottageChoice = isset($_POST['cottageSelections']) ? mysqli_real_escape_string($conn, $_POST['cottageSelections']) : "";
        $roomChoice = isset($_POST['roomSelections']) ? mysqli_real_escape_string($conn, $_POST['roomSelections']) : "";
        $videokeChoice = mysqli_real_escape_string($conn, $_POST['videokeChoice']);
        $additionalRequest = mysqli_real_escape_string($conn, $_POST['additionalRequest']);
        $paymentMethod = mysqli_real_escape_string($conn, $_POST['PaymentMethod']);
        // $paymentMethod = 'Cash';
        $bookingType = 'Resort';
        $page = 'resort-page';
        $buttonName = 'bookRates';
        $services = [];
        $addOns = [];
        $description = [];
        $bookingFunctionPage = 'entranceBooking.php';

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
        $query = "SELECT er.*, s.serviceID 
            FROM entranceRates er
            JOIN services s ON s.entranceRateID = er.entranceRateID
            WHERE er.sessionType = '$tourSelections'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            while ($rates = mysqli_fetch_assoc($result)) {
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

        $getServiceChoice = '';
        if (!empty($cottageChoice)) {
            $getServiceChoice = $cottageChoice;
        } elseif (!empty($roomChoice)) {
            $getServiceChoice = $roomChoice;
        } else {
            $getServiceChoice = "";
        }

        $getServiceChoiceQuery = "SELECT s.*, rs.* FROM services s
            INNER JOIN resortAmenities rs ON s.resortServiceID = rs.resortServiceID 
            WHERE RServiceName = '$getServiceChoice'";
        $getServiceChoiceResult = mysqli_query($conn, $getServiceChoiceQuery);
        if (mysqli_num_rows($getServiceChoiceResult) > 0) {
            $data = mysqli_fetch_assoc($getServiceChoiceResult);
            $serviceID = $data['serviceID'];  //Makukuha nito is cottage or hotel 
            $servicePrice = $data['RSprice'];
            $serviceCapacity = $data['RScapacity'];
            $services[] = $data['RServiceName'];
            $description[] = $data['RSdescription'];
        } else {
            echo "Service not found. MySQL error: " . mysqli_error($conn);
            echo "<br>Query: $getServiceChoiceQuery";
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
            $services[] = $videoke;
            $addOns[] = $videoke;
        } elseif ($videokeChoice === "No") {
            $videokeFee = 0;
            $addOns[] = "None";
        }


        $totalPax = (int)$adultCount + (int)$childrenCount;
        $totalAdultFee = ($adultRate * $adultCount);
        $totalChildFee = ($childRate * $childrenCount);
        $totalEntrance = $totalAdultFee + $totalChildFee;
        $totalCost = $totalEntrance + $servicePrice + $videokeFee;

        $downPayment = $totalCost * 0.3;
    }
    ?>

    <!-- For Hotel Booking -->

    <?php
    if (isset($_POST['hotelBooking'])) {

        $hoursSelected = mysqli_real_escape_string($conn, $_POST['hoursSelected']);
        $checkInDate = mysqli_real_escape_string($conn, $_POST['checkInDate']);
        $checkOutDate = mysqli_real_escape_string($conn, $_POST['checkOutDate']);
        $adultCount = mysqli_real_escape_string($conn, $_POST['adultCount']);
        $childrenCount = mysqli_real_escape_string($conn, $_POST['childrenCount']);
        $selectedHotel = mysqli_real_escape_string($conn, $_POST['selectedHotel']);
        // $hotelNotes = mysqli_real_escape_string($conn, $_POST['hotelNotes']);
        $paymentMethod = mysqli_real_escape_string($conn, $_POST['PaymentMethod']);

        $buttonName = 'hotelBooking';
        $bookingType = 'Hotel';
        $page = 'hotel-page';
        $services = [];
        $addOns = [];
        $description = [];
        $bookingFunctionPage = 'hotelBooking.php';
        $additionalRequest = "None";
        $excessChargePerPerson = 250;
        $totalPax = $childrenCount + $adultCount;
        $additionalCharge = 0;
        $additionalGuest = 0;
        $totalCost = 0;
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
            $description[] = $data['RSdescription'];
            $services[] = $data['RServiceName'];
        }

        if ($maxCapacity < $totalPax) {
            $additionalGuest = $totalPax - $maxCapacity;
            $additionalCharge = $additionalGuest * $excessChargePerPerson;
            $totalCost = $hotelPrice + $additionalCharge;
            $addOns[] = "Additional Guest:" . $additionalGuest;
        } else {
            $totalCost =  $hotelPrice;
        }

        $downPayment = $totalCost * 0.3;

        $startDateObj = new DateTime($checkInDate);
        $endDateObj = new DateTime($checkOutDate);
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


    <div class="button-container">
        <a href="bookNow.php#<?= $page ?>" class="btn"><img src="../../Assets/Images/Icon/back-button.png" alt="Back"></a>
    </div>

    <form action="../../Function/Booking/<?= htmlspecialchars($bookingFunctionPage) ?>" method="POST">
        <div class="card display-confirmation">
            <h5 class="card-header">Booking Summary</h5>
            <div class="card-body">
                <div class="info">
                    <h5 class="card-title">Booking Type:</h5>
                    <input type="text" id="bookingType" name="bookingType" value="<?= $bookingType ?> Booking" class="form-control" readonly>
                </div>
                <div class="info">
                    <h5 class="card-title">Customer Name:</h5>
                    <input type="text" name="customerName" value="<?= $name ?>" class="form-control" readonly>
                </div>
                <div class="info">
                    <h5 class="card-title">Services:</h5>
                    <input type="text" name="services" value="<?= htmlspecialchars(implode(', ', $services)) ?>" class="form-control" readonly>
                </div>

                <div class="info">
                    <h5 class="card-title">Description</h5>
                    <input type="text" name="description" value="<?= htmlspecialchars(implode(', ', array_unique($description))) ?>" class="form-control" readonly>
                </div>

                <div class="info">
                    <h5 class="card-title">Date:</h5>
                    <input type="text" name="date" value="<?= $date ?>" class="form-control" readonly>
                </div>
                <div class="info">
                    <h5 class="card-title">Time Range:</h5>
                    <input type="text" name="timeRange" value="<?= $timeRange ?>" class="form-control" readonly>
                </div>
                <div class="info">
                    <h5 class="card-title">Number of People:</h5>
                    <input type="text" name="totalPax" value="<?= $totalPax ?>" class="form-control" readonly>
                </div>

                <div class="info">
                    <h5 class="card-title">Additional:</h5>
                    <input type="text" name="addOns" value="<?= htmlspecialchars(implode(', ', $addOns)) ?>" class="form-control" readonly>
                </div>

                <div class="info" id="addRequest" style="display: none;">
                    <h5 class=" card-title">Additional Request</h5>
                    <input type="text" name="additionalRequest" value="<?= htmlspecialchars($additionalRequest) ?>" class="form-control" readonly>
                </div>

                <div class="info">
                    <h5 class="card-title">Payment Method:</h5>
                    <input type="text" name="paymentMethod" value="<?= htmlspecialchars($paymentMethod) ?>" class="form-control" readonly>
                </div>

                <div class="info">
                    <h5 class="card-title">Total Cost:</h5>
                    <input type="text" name="totalCost" value="₱ <?= number_format($totalCost, 2) ?>" class="form-control" readonly>
                </div>

                <div class="info" id="downpayment" style="display: none;">
                    <h5 class="card-title">Downpayment:</h5>
                    <input type="text" name="downPayment" value="₱ <?= number_format($downPayment, 2) ?>" class="form-control" readonly>
                </div>
            </div>

            <input type="hidden" name="resortBookingDate" value="<?= htmlspecialchars($scheduledDate) ?>">
            <input type="hidden" name="tourSelections" value="<?= htmlspecialchars($tourSelections) ?>">
            <input type="hidden" name="adultCount" value="<?= htmlspecialchars($adultCount) ?>">
            <input type="hidden" name="childrenCount" value="<?= htmlspecialchars($childrenCount) ?>">
            <input type="hidden" name="cottageSelections" value="<?= htmlspecialchars($cottageChoice) ?>">
            <input type="hidden" name="videokeChoice" value="<?= htmlspecialchars($videokeChoice) ?>">

            <input type="hidden" name="checkInDate" value="<?= htmlspecialchars($checkInDate) ?>">
            <input type="hidden" name="checkOutDate" value="<?= htmlspecialchars($checkOutDate) ?>">
            <input type="hidden" name="selectedHotel" value="<?= htmlspecialchars($selectedHotel) ?>">
            <input type="hidden" name="hoursSelected" value="<?= htmlspecialchars($hoursSelected) ?>">

            <div class="mt-auto button-container">
                <button type="submit" class="btn btn-primary btn-md w-100" name="<?= $buttonName ?>">Book Now</button>
            </div>

        </div>
    </form>

    <footer class="py-1 " id="footer" style="margin-top: 5vw !important;">
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

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js"></script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Hide downpayment and show addOns pag resort booking -->
    <script>
        const bookingType = document.getElementById("bookingType").value;
        const downpaymentDiv = document.getElementById('downpayment');
        const addRequestDiv = document.getElementById('addRequest');
        // const addOnsDiv = document.getElementById('addOns');

        if (bookingType === "Resort Booking") {
            downpaymentDiv.style.display = "none";
            // descriptionDiv.style.display = "none";
            addRequestDiv.style.display = "block";
            // addOnsDiv.style.display = "block";
        } else if (bookingType === "Hotel Booking") {
            downpaymentDiv.style.display = "block";
            // descriptionDiv.style.display = "block";
            addRequestDiv.style.display = "none";
        } else {
            downpaymentDiv.style.display = "block";
            // descriptionDiv.style.display = "block";
            addRequestDiv.style.display = "block";
            // addOnsDiv.style.display = "none";
        }
    </script>
</body>

</html>