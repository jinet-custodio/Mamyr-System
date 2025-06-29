<?php
require '../../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../../register.php?session=expired");
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
    <title>Reservation Summary - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/reservationSummary.css" />
</head>

<body>
    <div class="container">
        <div class="backButtonContainer">
            <a href="bookingHistory.php"><img src="../../../Assets/Images/Icon/arrow.png" alt="Back Button"
                    class="backButton"></a>
        </div>

        <div class="PendingContainer">

            <div class="leftPendingContainer">

                <img src="../../../Assets/Images/Icon/pending.png" alt="Pending Icon" class="PendingIcon">
                <h4 class="pendingTitle">Your reservation is pending for approval </h4>
                <h6 class="pendingSubtitle">Your request has been sent to the admin. Please wait for the approval of
                    your reservation.</h6>

                <!-- <button type="button" class="btn btn-success w-100 mt-3" data-bs-toggle="modal"
                    data-bs-target="#modeofPaymentModal">Make a Down Payment</button> -->
                <a href="../bookNow.php" class="btn btn-primary w-100 mt-2">Make Another Reservation</a>
            </div>

            <!-- Get user data -->
            <?php
            $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
            $confirmedBookingID = mysqli_real_escape_string($conn, $_POST['confirmedBookingID']);
            $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $getData = "SELECT bookings.*, users.firstName, users.middleInitial, users.lastName, users.phoneNumber, users.userAddress FROM bookings 
            JOIN users ON bookings.userID = users.userID
            WHERE bookings.userID = '$userID' AND bookings.bookingID = '$bookingID'";
            $resultData = mysqli_query($conn, $getData);
            if (mysqli_num_rows($resultData) > 0) {
                $clientInfo = mysqli_fetch_assoc($resultData);
                $middleInitial = trim($clientInfo['middleInitial']);
                $name = ucfirst($clientInfo['firstName']) . " " . ucfirst($clientInfo['middleInitial']) . " "  . ucfirst($clientInfo['lastName']);
            }
            ?>
            <div class="rightPendingContainer">
                <h3 class="rightContainerTitle">Reservation Summary</h3>

                <div class="firstRow">
                    <div class="clientContainer">
                        <h6 class="header">Client</h6>
                        <h6 class="content" id="clientName"><?= htmlspecialchars($name) ?></h6>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Contact Number</h6>
                        <h6 class="content" id="contactNumber"><?= $clientInfo['phoneNumber'] ? $clientInfo['phoneNumber'] : 'Not Available' ?></h6>
                    </div>
                </div>

                <div class="secondRow">
                    <input type="hidden" name="bookingType" id="bookingType" value="<?= $bookingType ?>">
                    <div class="reservationTypeContainer">
                        <h6 class="header">Reservation Type</h6>
                        <h6 class="content" id="reservation"><?= $bookingType ?> Booking</h6>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Address</h6>
                        <h6 class="content" id="address"><?= $clientInfo['userAddress'] ? $clientInfo['userAddress'] : 'Not Available' ?></h6>
                    </div>
                </div>

                <div class="card" id="summaryDetails" style="width: 25.6rem;">
                    <ul class="list-group list-group-flush">

                        <?php
                        $getBookingInfo = $conn->prepare("SELECT 
                                    b.*, b.totalCost AS bookingCost , st.*,
                                    p.*, cp.*, cpi.*, 
                                    bs.*, s.*, 
                                    ra.*, rsc.categoryName AS serviceName, ec.categoryName AS eventName,
                                    er.*, ps.*  
                                    
                                FROM bookings b

                                LEFT JOIN packages p ON b.packageID = p.packageID
                                LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID

                                LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID
                                LEFT JOIN custompackageitems cpi ON cp.customPackageID = cpi.customPackageID

                                LEFT JOIN bookingservices bs ON b.bookingID = bs.bookingID
                                LEFT JOIN statuses st ON st.statusID = b.bookingStatus

                                -- LEFT JOIN bookingsservices bs ON b.bookingID = bs.bookingID
                                LEFT JOIN services s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)

                                LEFT JOIN resortamenities ra ON s.resortServiceID = ra.resortServiceID
                                LEFT JOIN resortservicescategories rsc ON rsc.categoryID = ra.RScategoryID

                                LEFT JOIN entranceRates er ON s.entranceRateID = er.entranceRateID

                                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
                            WHERE b.bookingID = ?");
                        $getBookingInfo->bind_param("i", $bookingID);
                        $getBookingInfo->execute();
                        $getBookingInfoResult = $getBookingInfo->get_result();
                        if ($getBookingInfoResult->num_rows > 0) {

                            $services = [];
                            $allDescriptions = [];
                            $AddRequest = "";


                            $adultCount = 0;
                            $kidsCount = 0;

                            $package = "";
                            $customPackage = "";
                            $status = "";

                            while ($data = $getBookingInfoResult->fetch_assoc()) {
                                // echo "<pre>";
                                // print_r($data);
                                // echo "</pre>";
                                $startDate = date("j F Y", strtotime($data['startDate'])); //Bookings
                                $time = date("g:i A", strtotime($data['startDate'])) . " - " . date("g:i A", strtotime($data['endDate'])); //Bookings
                                $duration = $data['hoursNum'] . " hours"; //Bookings
                                $pax = $data['paxNum']; //Bookings

                                $totalCost = $data['bookingCost'];  //Booking
                                $discount = $data['discountAmount'];  //Bookings
                                $downpayment = $data['downpayment'];    //Bookings

                                $addOns = [];
                                $addOns[] = $data['addOns'];  //Bookings

                                if ($bookingType === 'Resort') {
                                    foreach ($addOns as $addOns) {
                                        if (stripos($addOns, 'Videoke') !== false) {
                                            $videokeChoice = "Yes";
                                        } else {
                                            $videokeChoice = "No";
                                        }
                                    }

                                    if (!empty($data['serviceID'])) {
                                        $services[] = $data['sessionType'] . " Swimming";
                                        $cardHeader = "Type of Tour";
                                        if ($data['ERcategory'] === "Kids") {
                                            $kidsCount = $data['guests'];
                                        } elseif ($data['ERcategory'] === "Adult") {
                                            $adultCount = $data['guests'];
                                        }

                                        $guests = [];

                                        if ($adultCount > 0) {
                                            $guests[] = "Adult: $adultCount";
                                        }

                                        if ($kidsCount > 0) {
                                            $guests[] = "Kid: $kidsCount";
                                        }

                                        $resortGuest = implode(" & ", $guests);
                                    }
                                    if (!empty($data['resortServiceID'])) {
                                        $services[] = $data['RServiceName'];
                                    }
                                } else if ($bookingType === 'Hotel') {
                                    $addOns = [];
                                    if (!empty($data['serviceID'])) {
                                        if (!empty($data['resortServiceID'])) {
                                            $services[] = $data['RServiceName'];
                                            $items = array_map('trim', explode(',', $data['RSdescription']));
                                            $allDescriptions = array_merge($allDescriptions, $items);
                                            $allDescriptions = array_unique($allDescriptions);
                                        }

                                        $downpaymentNote = "Please pay for the down payment amount for the approval of your booking
                            withinseven (7) business days.";
                                    }
                                }



                                $status = $data['statusName'];
                                $package = $data['eventName'];
                                $customPackageID = $data['customPackageID'];
                                $AddRequest = $data['additionalRequest'];

                                if (!empty($package)) {
                                    $pax = $data['paxNum'];
                                    $serviceName = $data['packageName'];
                                    $items = array_map('trim', explode(',', $data['packageDescription']));
                                    $allDescriptions = array_merge($allDescriptions, $items);
                                    $allDescriptions = array_unique($allDescriptions);
                                }

                                if (!empty($customPackageID)) {
                                    $pax = $data['paxNum'];
                                    if (!empty($data['serviceID'])) {
                                        if (!empty($data['entranceRateID'])) {
                                            $services[] = $data['sessionType'] . " Swimming";
                                            if ($data['ERcategory'] === "Kids") {
                                                $kidsCount = $data['guests'];
                                            } elseif ($data['ERcategory'] === "Adult") {
                                                $adultCount = $data['guests'];
                                            }
                                        }
                                        if (!empty($data['partnershipServiceID'])) {
                                            $services[] = $data['PBName'];
                                        }
                                        if (!empty($data['resortServiceID'])) {
                                            $services[] = $data['RServiceName'];
                                            $items = array_map('trim', explode(',', $data['RSdescription']));
                                            $allDescriptions = array_merge($allDescriptions, $items);
                                            $allDescriptions = array_unique($allDescriptions);
                                        }
                                    }
                                }
                            }

                            //Get the room or cottage
                            $cottageRoom = [];

                            foreach ($services as $service) {
                                if (stripos($service, 'cottage') !== false) {
                                    $serviceVenue = "Cottage";
                                    $cottageRoom[] = $service;
                                } elseif (stripos($service, 'room') !== false) {
                                    $serviceVenue = "Room";
                                    $cottageRoom[] = $service;
                                }
                                if (stripos($service, 'Day') !== false) {
                                    $tourType = "Day Tour";
                                } elseif (stripos($service, 'Night') !== false) {
                                    $tourType = "Night Tour";
                                } elseif (stripos($service, 'Overnight') !== false) {
                                    $tourType = "Overnight Tour";
                                }
                            }

                            if (!empty($kidsCount) || !empty($adultCount)) {
                                $guest = $resortGuest;
                            } else {
                                $guest = $pax;
                            }
                            $totalBill =  $totalCost - $discount;
                        }
                        ?>

                        <li class=" list-group-item" id="tourType">
                            <h6 class="cardHeader"><?= $cardHeader  ?></h6>
                            <h6 class="cardContent" id="eventDate"><?= $tourType ?></h6>
                        </li>

                        <li class=" list-group-item">
                            <h6 class="cardHeader">Date</h6>
                            <h6 class="cardContent" id="eventDate"><?= $startDate ?></h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Time</h6>
                            <h6 class="cardContent" id="eventTime"><?= $time ?></h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader"><?= $serviceVenue ?></h6>
                            <h6 class="cardContent" id="venue"><?= implode(', ', array_unique($cottageRoom)) ?></h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Duration</h6>
                            <h6 class="cardContent" id="eventDuration"><?= $duration ?></h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Number of Guests</h6>
                            <h6 class="cardContent" id="guestNo"><?= $guest ?></h6>
                        </li>

                        <li class=" list-group-item" id="addOns">
                            <h6 class="cardHeader">Add Ons</h6>
                            <h6 class="cardContent"><?= !empty($addOns) ? htmlspecialchars($addOns) : "None" ?></h6>
                        </li>

                        <li class=" list-group-item">
                            <h6 class="cardHeader">Request/Notes</h6>
                            <h6 class="cardContent" id="request">
                                <?= !empty($AddRequest) ? htmlspecialchars($AddRequest) : "None" ?>
                            </h6>
                        </li>
                        <!-- <li class=" list-group-item">
                            <h6 class="cardHeader">Package Type</h6>
                            <h6 class="cardContent" id="packageType">Wedding <img
                                    src="../../../Assets/Images/Icon/information.png" alt="More Details"
                                    class="infoIcon">
                            </h6>
                        </li> -->
                        <li class=" list-group-item" id="totalAmountSection">
                            <h6 class="cardHeader">Total Amount:</h6>
                            <h6 class="cardContentBill" id="totalAmount">₱ <?= number_format($totalCost, 2) ?></h6>
                        </li>
                        <li class=" list-group-item" id="promoSection">
                            <h6 class="cardHeader">Promo/Discount:</h6>
                            <h6 class="cardContentBill" id="promoDiscount"> <?= $discount ?></h6>
                        </li>
                        <li class=" list-group-item" id="totalBillSection">
                            <h6 class="cardHeader">Grand Total:</h6>
                            <h6 class="cardContentBill" id="totalBill">₱ <?= number_format($totalCost, 2) ?></h6>
                        </li>
                    </ul>
                </div>

                <div class="downpaymentNoteContainer" id="downpaymentNoteContainer" style="display: none;">
                    <div class="downpayment">
                        <h6 class="header">Down Payment Amount (30%):</h6>
                        <h6 class="content" id="downPaymentAmount">₱ <?= number_format($downpayment, 2) ?></h6>
                    </div>
                    <div class="note">
                        <h6 class="note">Note: <?= $downpaymentNote ?></h6>
                    </div>
                </div>
            </div>
        </div>


        <!-- modal -->
        <div class="modal fade" id="modeofPaymentModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Mode of Payment</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="modeofPaymentModalBody">
                        <button class="btn btn-primary w-75 m-auto" data-bs-target="#gcashPaymentModal"
                            data-bs-toggle="modal">Gcash
                            Down Payment</button>
                        <button class="btn btn-info w-75 m-auto">On-site Down Payment</button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="gcashPaymentModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Upload Your Screenshot</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>


                    <div class="modal-body" id="gcashModalBody">
                        Please upload a screenshot of your Gcash down payment below.

                        <div class="form-group">
                            <input type="file" class="form-control-file " id="fileInput"
                                accept=".jpeg, .png, image/jpeg, image/png">
                        </div>
                    </div>



                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-target="#modeofPaymentModal"
                            data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </div>




















    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const bookingType = document.getElementById("bookingType").value;

            const downpaymentNoteContainer = document.getElementById("downpaymentNoteContainer");
            const addOnsContainer = document.getElementById("addOns");
            const tourTypeContainer = document.getElementById("tourType");

            if (bookingType === "Resort") {
                downpaymentNoteContainer.style.display = "none";
                addOnsContainer.style.display = "flex";
                tourTypeContainer.style.display = "flex";
            } else if (bookingType === "Hotel") {
                downpaymentNoteContainer.style.display = "block";
                addOnsContainer.style.display = "none";
                tourTypeContainer.style.display = "none";
            } else {
                downpaymentNoteContainer.style.display = "block";
                addOnsContainer.style.display = "none";
            }
        });
    </script>

</body>

</html>