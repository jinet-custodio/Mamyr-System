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


if (isset($_POST['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
} elseif (isset($_SESSION['bookingID'])) {
    $bookingID = mysqli_real_escape_string($conn, $_SESSION['bookingID']);
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
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/viewBooking.css" />
</head>

<body>
    <!-- Guest Information Container -->
    <div class="guest-container">
        <!-- Back Button -->
        <div class="page-container">
            <a href="booking.php" class="btn btn-primary back"><img src="../../Assets/Images/Icon/whiteArrow.png" alt="Back Button"></a>
            <h5 class="page-title">Guest Booking Information</h5>
        </div>
        <!-- Information -->


        <?php
        $getUserInfo = $conn->prepare("SELECT u.*, b.*  FROM bookings b
        INNER JOIN users u ON b.userID = u.userID
        WHERE b.bookingID = ?");
        $getUserInfo->bind_param("i", $bookingID);
        $getUserInfo->execute();
        $resultUserInfo = $getUserInfo->get_result();
        if ($resultUserInfo->num_rows > 0) {
            $data = $resultUserInfo->fetch_assoc();
            $middleInitial = trim($data['middleInitial']);
            $name = ucfirst($data['firstName']) . " " . ucfirst($data['middleInitial']) . " "  . ucfirst($data['lastName']);
            $email = $data['email'];
            $phoneNumber = $data['phoneNumber'];
            $address = $data['userAddress'];

            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $imageData = $data['userProfile'];
            $mime_type = finfo_buffer($file_info, $imageData);
            finfo_close($file_info);

            $userProfile = 'data:' . $mime_type . ';base64,' . base64_encode($imageData);

            if (!empty($phoneNumber)) {
                $phoneNumber;
            } else {
                $phoneNumber = "--";
            }
        }
        ?>

        <!-- Display the information -->
        <div class="card">
            <form action="../../Function/Admin/bookingApproval.php" method="POST">
                <div class="booking-info-name-pic-btn">
                    <div class="user-info">
                        <img src="<?= htmlspecialchars($userProfile) ?>" class="img-fluid rounded-start">
                        <div class="booking-info-contact">
                            <p class="card-text name"><?= htmlspecialchars($name) ?></p>
                            <p class="card-text sub-name"><?= htmlspecialchars($email) ?> | <?= htmlspecialchars($phoneNumber) ?> </p>
                            <p class="card-text sub-name"><?= htmlspecialchars($address) ?></p>
                        </div>
                    </div>

                    <div class="button-container" id="button-container">
                        <button type="submit" class="btn btn-primary" name="approveBtn">Approve</button>
                        <button type="submit" class="btn btn-danger" name="rejectBtn">Reject</button>
                    </div>
                </div>

                <!-- Get booking information to the database -->
                <?php

                $getBookingInfo = $conn->prepare("SELECT 
                                    b.*, b.totalCost AS bookingCost , st.*,
                                    p.*, cp.*, cpi.*, 
                                    bs.*, s.*, 
                                    ra.*, rsc.categoryName AS serviceName, ec.categoryName AS eventName,
                                    er.*, ps.*, bps.statusName AS paymentStatus 
                                    
                                FROM bookings b
                                LEFT JOIN confirmedBookings cb ON b.bookingID = cb.bookingID
                                LEFT JOIN bookingpaymentstatus bps ON cb.paymentStatus = bps.paymentStatusID

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

                        $startDate = date("M d, Y", strtotime($data['startDate']));
                        $endDate = date("M d, Y", strtotime($data['endDate']));

                        if ($startDate === $endDate) {
                            $date = date("F d, Y", strtotime($data['startDate']));
                        } else {
                            $date = $startDate . " - " . $endDate;
                        }

                        $time = date("g:i A", strtotime($data['startDate'])) . " - " . date("g:i A", strtotime($data['endDate']));
                        $duration = $data['hoursNum'] . " hours";
                        $pax = $data['paxNum'];

                        $totalCost = $data['bookingCost'];  //Booking
                        $discount = $data['discountAmount'];
                        $downpayment = $data['downpayment'];
                        $bookingType = $data['bookingType'];
                        $paymentMethod = $data['paymentMethod'];
                        $paymentStatus = $data['paymentStatus'];

                        if ($paymentMethod === 'Cash') {
                            $paymentMethod = $paymentMethod . ' - Onsite Payment';
                        } else {
                            $paymentMethod = $paymentMethod;
                        }

                        if ($bookingType === 'Resort') {
                            $pax = $data['paxNum'] . " Guest";
                            $addOns = $data['addOns'];

                            if ($addOns === "Videoke") {
                                $videoke = 'Videoke';
                            } else {
                                $videoke = 'None';
                            }

                            if (!empty($data['serviceID'])) {
                                if (!empty($data['sessionType'])) {
                                    $services[] = $data['sessionType'] . ' Swimming';
                                } else {
                                    $services[] = NULL;
                                }

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
                                $items = array_map('trim', explode(',', $data['RSdescription']));
                                $allDescriptions = array_merge($allDescriptions, $items);
                                $allDescriptions = array_unique($allDescriptions);
                            }
                        } else if ($bookingType === 'Hotel') {
                            $addOns = "";
                            $pax = $data['paxNum'] . " Guest";
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
                            $pax = $data['paxNum'] . " Guest";
                            if (!empty($data['serviceID'])) {
                                if (!empty($data['entranceRateID'])) {
                                    $services[] = trim($data['sessionType']) . " Swimming";
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

                        // echo "<pre>";
                        // print_r($paymentMethod);
                        // echo "</pre>";
                    }

                    //Get the room or cottage
                    $cottageRoom = [];

                    // foreach ($services as $service) {
                    //     if (stripos($service, 'cottage') !== false) {
                    //         $serviceVenue = "Cottage";
                    //         $cottageRoom[] = $service;
                    //     } elseif (stripos($service, 'room') !== false) {
                    //         $serviceVenue = "Room";
                    //         $cottageRoom[] = $service;
                    //     }
                    //     if (stripos($service, 'Day') !== false) {
                    //         $tourType = "Day Tour";
                    //     } elseif (stripos($service, 'Night') !== false) {
                    //         $tourType = "Night Tour";
                    //     } elseif (stripos($service, 'Overnight') !== false) {
                    //         $tourType = "Overnight Tour";
                    //     }
                    // }

                    if (!empty($kidsCount) || !empty($adultCount)) {
                        $guest = $resortGuest;
                    } else {
                        $guest = $pax;
                    }
                    $totalBill =  $totalCost - $discount;
                }
                ?>

                <!-- Display the information -->
                <div class="card-body">
                    <input type="hidden" id="bookingType" value="<?= htmlspecialchars($bookingType) ?>">
                    <input type="hidden" id="status" name="bookingStatus" value="<?= htmlspecialchars($status) ?>">
                    <input type="hidden" id="videoke" value="<?= htmlspecialchars($videoke) ?>">
                    <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                    <div class="two-item-row">
                        <div class="guest-info">
                            <h4 class="card-title important-title">Booking Type</h4>
                            <p class="card-text important-sub-title"><?= ucfirst($bookingType) ?> Booking</p>
                        </div>
                        <div class="guest-info">
                            <h4 class="card-title important-title">Service/s</h4>
                            <p class="card-text important-sub-title">
                                <?= !empty($services) ? implode(" | ", array_filter(array_unique($services))) : 'Not Available' ?>
                            </p>
                        </div>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Time Range</h4>
                        <p class="card-text"> <?= $time ?> </p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Schedule</h4>
                        <p class="card-text"> <?= $date ?> </p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Stay Duration</h4>
                        <p class="card-text"><?= !empty($duration) ? $duration  : 'Not Available' ?></p>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Guest</h4>
                        <p class="card-text"> <?= htmlspecialchars($guest)  ?></p>
                    </div>

                    <div class="guest-info addOns">
                        <h4 class="card-title">Add Ons</h4>
                        <input type="hidden" name="addOns" value="<?= !empty(htmlspecialchars($addOns)) ? htmlspecialchars($addOns) : "None" ?>">
                        <p class="card-text" id="addOns"><?= !empty(htmlspecialchars($addOns)) ? htmlspecialchars($addOns) : "None" ?></p>
                    </div>

                    <div class="guest-info" id="videokeSelectionContainer" style="display: none;">
                        <h4 class="card-title">Videoke</h4>
                        <select class="form-select" name="videokeChoice">
                            <option value="" selected disabled>Assign a videoke</option>
                            <?php
                            $selectHotel = "SELECT * FROM resortAmenities WHERE 
                            (RServiceName = 'Videoke 1' OR RServiceName = 'Videoke 2') AND RSAvailabilityID = 1";

                            $result = mysqli_query($conn, $selectHotel);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                    <option value="<?= $row['RServiceName'] ?>">
                                        <?= $row['RServiceName'] ?> — ₱<?= number_format($row['RSprice'], 2) ?>
                                    </option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="guest-info">
                        <h4 class="card-title">Total Price</h4>
                        <p class="card-text">₱ <?= number_format($totalBill, 2) ?></p>
                    </div>

                    <div class="guest-info" id="downpayment">
                        <h4 class="card-title">Downpayment</h4>
                        <p class="card-text">₱ <?= number_format($downpayment, 2) ?></p>
                    </div>

                    <div class="guest-info payment" id="paymentMethod">
                        <h4 class="card-title">Payment Method</h4>
                        <p class="card-text"><?= htmlspecialchars($paymentMethod) ?> </p>
                    </div>

                    <div class="guest-info" id="paymentStatus" style="display: none;">
                        <h4 class="card-title">Payment Status</h4>
                        <p class="card-text"><?= htmlspecialchars($paymentStatus) ?> </p>
                    </div>

                    <div class="two-item-row">
                        <div class="guest-info">
                            <h4 class="card-title">Service Description</h4>
                            <pre class="card-text description"><?= !empty($allDescriptions) ? implode("<br>", $allDescriptions) : 'None' ?></pre>
                        </div>
                        <div class="guest-info">
                            <h4 class="card-title">Additional Request</h4>
                            <p class="card-text"><?= !empty($AddRequest) ? $AddRequest : 'None' ?> </p>
                        </div>
                    </div>
                </div>
            </form>

        </div>

    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <script>
        const videoke = document.getElementById("videoke").value;
        const bookingType = document.getElementById("bookingType").value;
        const status = document.getElementById("status").value;
        const addOns = document.getElementById("addOns").textContent.trim();

        const buttonContainer = document.getElementById("button-container")
        const videokeSelectionContainer = document.getElementById("videokeSelectionContainer");
        const downpaymentContainer = document.getElementById("downpayment");
        const paymentStatusContainer = document.getElementById("paymentStatus");

        if (videoke === 'Videoke') {
            videokeSelectionContainer.style.display = "block";
            videokeSelectionContainer.required = true;
        }

        if (bookingType === "Resort") {
            downpaymentContainer.style.display = "none";
            document.querySelector(".guest-info.payment").classList.add("fullWidth");
        }

        if (status === "Approved") {
            buttonContainer.style.display = "none";
            videokeSelectionContainer.style.display = "none";
            document.querySelector(".guest-info.addOns").classList.add("fullWidth");
            document.querySelector(".guest-info.payment").classList.remove("fullWidth");
            paymentStatusContainer.style.display = "block";
        }

        if (addOns === "None") {
            document.querySelector(".guest-info.addOns").classList.add("fullWidth");
        }

        if (status === "Cancelled" || status === "Rejected") {
            buttonContainer.style.display = "none";
        }
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');

        if (paramValue === "videoke") {
            Swal.fire({
                title: "Oops!",
                text: "Please assign a videoke.",
                icon: 'warning',
            });
        } else if (paramValue === "error") {
            Swal.fire({
                title: "Failed!",
                text: "The booking request could not be approved. Please try again later.",
                icon: 'error',
            });
        }
    </script>


</body>

</html>