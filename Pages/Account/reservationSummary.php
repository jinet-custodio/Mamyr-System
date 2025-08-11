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
    <title>Reservation Summary - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/reservationSummary.css" />
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">


</head>

<body>
    <div class="container">
        <div class="backButtonContainer">
            <a href="bookingHistory.php"><img src="../../Assets/Images/Icon/arrow.png" alt="Back Button"
                    class="backButton"></a>
        </div>

        <div class="statusContainer">
            <!-- Get user data -->
            <?php
            $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
            $confirmedBookingID = mysqli_real_escape_string($conn, $_POST['confirmedBookingID']);
            $bookingID = $bookingID;
            // $bookingID = mysqli_real_escape_string($conn, $_POST['bookingID']);
            // $status = mysqli_real_escape_string($conn, $_POST['status']);
            $getData = $conn->prepare("SELECT bookings.*, users.firstName, users.middleInitial, users.lastName, users.phoneNumber, users.userAddress FROM bookings 
            JOIN users ON bookings.userID = users.userID
            WHERE bookings.userID = ? AND bookings.bookingID =?");
            $getData->bind_param("ii", $userID, $bookingID);
            $getData->execute();
            $resultData = $getData->get_result();
            if ($resultData->num_rows > 0) {
                $clientInfo = $resultData->fetch_assoc();
                $middleInitial = trim($clientInfo['middleInitial']);
                $name = ucfirst($clientInfo['firstName']) . " " . ucfirst($clientInfo['middleInitial']) . " "  . ucfirst($clientInfo['lastName']);
            }
            ?>

            <!-- Get booking info -->
            <?php
            $getBookingInfo = $conn->prepare("SELECT 
                                    b.*, b.totalCost AS bookingCost , 
                                    st.statusName AS confirmedBookingStatus, 
                                    stat.statusName AS bookingStatus,
                                    bps.statusName AS paymentStatus,
                                    p.*, cp.*, cpi.*, 
                                    bs.*, s.*, 
                                    ra.*, rsc.categoryName AS serviceName, ec.categoryName AS eventName,
                                    er.*, ps.*, cb.downpaymentImage
                                    
                                FROM bookings b

                                LEFT JOIN confirmedBookings cb ON b.bookingID = cb.bookingID
                                LEFT JOIN statuses st ON cb.confirmedBookingStatus = st.statusID
                                LEFT JOIN statuses stat ON b.bookingStatus = stat.statusID
                                LEFT JOIN bookingPaymentStatus bps ON cb.paymentStatus = bps.paymentStatusID

                                LEFT JOIN packages p ON b.packageID = p.packageID
                                LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID

                                LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID
                                LEFT JOIN custompackageitems cpi ON cp.customPackageID = cpi.customPackageID

                                LEFT JOIN bookingservices bs ON b.bookingID = bs.bookingID
                                

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
                $status = '';

                while ($data = $getBookingInfoResult->fetch_assoc()) {
                    // echo "<pre>";
                    // print_r($data);
                    // echo "</pre>";
                    $startDate = date("F j, Y", strtotime($data['startDate'])); //Bookings
                    $time = date("g:i A", strtotime($data['startDate'])) . " - " . date("g:i A", strtotime($data['endDate'])); //Bookings
                    $duration = $data['hoursNum'] . " hours"; //Bookings
                    $pax = $data['paxNum']; //Bookings

                    $totalCost = $data['bookingCost'];  //Booking
                    $discount = $data['discountAmount'];  //Bookings
                    $downpayment = $data['downpayment'];    //Bookings

                    $paymentMethod = $data['paymentMethod'];

                    $addOns = [];
                    $addOns[] = $data['addOns'];  //Bookings


                    $imageData = $data['downpaymentImage'];

                    if (!empty($imageData)) {
                        // Try to detect MIME type from binary data
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_buffer($finfo, $imageData);
                        finfo_close($finfo);
                        $image = "data:" . $mimeType . ";base64," . base64_encode($imageData);
                    } else {
                        $image = "../../Assets/Images/defaultDownpayment.png"; // Default image if no data
                    }


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
                    $package = $data['packageID'];
                    $customPackageID = $data['customPackageID'];
                    // $AddRequest = $data['additionalRequest'];

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



                    $bookingStatus = $data['bookingStatus'];
                    $confirmedBookingStatus = $data['confirmedBookingStatus'];
                    $paymentStatus = $data['paymentStatus'];
                    if ($bookingStatus === 'Pending') {
                        $status = strtolower($bookingStatus) ?? NUll;
                        $statusTitle = "Your reservation is pending for approval";
                        $statusSubtitle = 'Your request has been sent to the admin. Please wait for the approval of
                    your reservation.';
                    } elseif ($bookingStatus === 'Rejected') {
                        $status = strtolower($bookingStatus) ?? NUll;
                        $statusTitle = "Booking Rejected!";
                        $statusSubtitle = "We regret to inform you that your reservation has been rejected. Please contact us for more details.";
                    } elseif ($bookingStatus === 'Cancelled') {
                        $status = strtolower($bookingStatus) ?? null;
                        $statusTitle = "Booking Cancelled";
                        $statusSubtitle = "You have cancelled your reservation. If this was a mistake or you wish to rebook, please contact us.";
                    } elseif ($bookingStatus === 'Approved' && $confirmedBookingStatus === 'Rejected') {
                        $status = strtolower($bookingStatus) ?? null;
                        $statusTitle = "Payment Rejected";
                        $statusSubtitle = "Your reservation was approved, but the submitted payment was rejected. Please check the payment details and try again, or contact the admin for assistance.";
                    } elseif ($bookingStatus === 'Approved' && $confirmedBookingStatus === 'Pending') {
                        $status = strtolower($bookingStatus) ?? NUll;
                        $statusTitle = "Your reservation has been approved.";
                        if ($paymentMethod === 'GCash') {
                            $statusSubtitle = "Your reservation request has been approved by the admin. You may now proceed with the down payment via GCash.";
                        } elseif ($paymentMethod === 'Cash') {
                            if ($bookingType === 'Resort') {
                                $statusSubtitle = "Your reservation has been approved by the admin. Please proceed on your scheduled swimming date and complete the payment on that day.";
                            } else {
                                $statusSubtitle = "Your reservation request has been approved by the admin. You may now proceed to the resort to make your downpayment.";
                            }
                        }
                    } elseif ($confirmedBookingStatus === 'Approved' && $paymentStatus === 'Partially Paid') {
                        $status = strtolower($bookingStatus) ?? NUll;
                        $statusTitle = "Payment approved successfully.";
                        $statusSubtitle = "We have received and reviewed your payment. The service you booked is now reserved. Thank you!";
                    } elseif ($confirmedBookingStatus === 'Approved' && $paymentStatus === 'Fully Paid') {
                        $status = strtolower($bookingStatus) ?? NUll;
                        $statusTitle = "Payment done successfully.";
                        $statusSubtitle = "Thank you! We have received your full payment. You may now enjoy your stay at the resort.";
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
            // echo $status;
            ?>

            <div class="leftStatusContainer">

                <input type="hidden" name="bookingStatus" id="bookingStatus"
                    value="<?= htmlspecialchars($bookingStatus) ?>">
                <input type="hidden" name="confirmedBookingStatus" id="confirmedBookingStatus"
                    value="<?= htmlspecialchars($confirmedBookingStatus) ?>">
                <input type="hidden" name="paymentStatus" id="paymentStatus"
                    value="<?= htmlspecialchars($paymentStatus) ?>">
                <input type="hidden" name="paymentMethod" id="paymentMethod"
                    value="<?= htmlspecialchars($paymentMethod) ?>">

                <img src="../../Assets/Images/Icon/<?= htmlspecialchars(ucfirst($status)) ?>.png"
                    alt="<?= ucfirst(htmlspecialchars($status)) ?> Icon" class="statusIcon">

                <h4 class="statusTitle"><?= htmlspecialchars($statusTitle) ?></h4>
                <h6 class="statusSubtitle"><?= htmlspecialchars($statusSubtitle) ?></h6>

                <div class="button-container">
                    <button type="button" class="btn btn-success w-100 mt-3" id="makeDownpaymentBtn"
                        data-bs-toggle="modal" data-bs-target="#gcashPaymentModal">Make a Down Payment</button>
                    <!-- <a href="../bookNow.php" class="btn btn-primary w-100 mt-3" id="newReservationBtn">Make Another
                        Reservation</a> -->
                    <form action="../../Function/Customer/receiptPDF.php" method="POST">
                        <input type="hidden" name="totalCost" value="<?= $totalBill ?>">
                        <input type="hidden" name="name" value="<?= $name ?>">
                        <input type="hidden" name="bookingID" value="<?= $bookingID ?>">
                        <input type="hidden" name="bookingType" value="<?= $bookingType ?>">
                        <button type="submit" class="btn btn-primary w-100 mt-3" name="downloadReceiptBtn" id="downloadReceiptBtn">Download Receipt </button>
                    </form>
                </div>
            </div>

            <div class="rightStatusContainer">
                <h3 class="rightContainerTitle">Reservation Summary</h3>

                <div class="firstRow">
                    <div class="clientContainer">
                        <h6 class="header">Client</h6>
                        <p class="content" id="clientName"><?= htmlspecialchars($name) ?></p>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Contact Number</h6>
                        <p class="content" id="contactNumber">
                            <?= $clientInfo['phoneNumber'] ? $clientInfo['phoneNumber'] : 'Not Available' ?></p>
                    </div>

                    <input type="hidden" name="bookingType" id="bookingType" value="<?= $bookingType ?>">
                    <div class="reservationTypeContainer">
                        <h6 class="header">Reservation Type</h6>
                        <p class="content" id="reservation"><?= $bookingType ?> Booking</p>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Address</h6>
                        <p class="content" id="address">
                            <?= $clientInfo['userAddress'] ? $clientInfo['userAddress'] : 'Not Available' ?></p>
                    </div>
                </div>

                <div class="card" id="summaryDetails" style="width: 25.6rem;">
                    <ul class="list-group list-group-flush">
                        <?php if ($bookingType === 'Resort') { ?>
                            <li class="list-group-item" id="tourType">
                                <h6 class="cardHeader"><?= $cardHeader ?></h6>
                                <p class="cardContent" id="eventDate"><?= $tourType ?></p>
                            </li>
                        <?php } ?>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Date</h6>
                            <p class="cardContent" id="eventDate"><?= $startDate ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Time</h6>
                            <p class="cardContent" id="eventTime"><?= $time ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader"><?= $serviceVenue ?></h6>
                            <p class="cardContent" id="venue"><?= implode(', ', array_unique($cottageRoom)) ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Duration</h6>
                            <p class="cardContent" id="eventDuration"><?= $duration ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Number of Guests</h6>
                            <p class="cardContent" id="guestNo"><?= $guest ?></p>
                        </li>

                        <li class="list-group-item" id="addOns">
                            <h6 class="cardHeader">Add Ons</h6>
                            <p class="cardContent"><?= !empty($addOns) ? htmlspecialchars($addOns) : "None" ?></p>
                        </li>

                        <li class="list-group-item">
                            <h6 class="cardHeader">Request/Notes</h6>
                            <p class="cardContent" id="request">
                                <?= !empty($AddRequest) ? htmlspecialchars($AddRequest) : "None" ?>
                            </p>
                        </li>
                        <!-- <li class="list-group-item">
                            <h6 class="cardHeader">Package Type</h6>
                            <p class="cardContent" id="packageType">Wedding <img
                                    src="../../Assets/Images/Icon/information.png" alt="More Details"
                                    class="infoIcon">
        </p>
                        </li> -->
                        <li class="list-group-item" id="totalAmountSection">
                            <h6 class="cardHeader">Total Amount:</h6>
                            <h6 class="cardContentBill" id="totalAmount">₱ <?= number_format($totalCost, 2) ?></h6>
                        </li>

                        <li class="list-group-item" id="promoSection">
                            <h6 class="cardHeader">Promo/Discount:</h6>
                            <h6 class="cardContentBill" id="promoDiscount"> <?= $discount ?></h6>
                        </li>

                        <li class="list-group-item" id="totalBillSection">
                            <h6 class="cardHeader">Grand Total:</h6>
                            <h6 class="cardContentBill" id="totalBill">₱ <?= number_format($totalCost, 2) ?></h6>
                        </li>
                    </ul>
                </div>

                <div class="downpaymentNoteContainer" id="downpaymentNoteContainer" style="display: none;">
                    <div class="downpayment">
                        <h6 class="header">Down Payment Amount (30%):</h6>
                        <p class="content" id="downPaymentAmount">₱ <?= number_format($downpayment, 2) ?></p>
                    </div>
                    <div class="note">
                        <h6 class="note">Note: <?= $downpaymentNote ?></h6>
                    </div>
                </div>
            </div>
        </div>


        <!-- modal -->
        <!-- <div class="modal fade" id="modeofPaymentModal" tabindex="-1" aria-labelledby="exampleModalLabel"
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
        </div> -->
        <form action="../../Function/Customer/Account/uploadPayment.php" method="POST" enctype="multipart/form-data">
            <div class="modal fade" id="gcashPaymentModal" aria-hidden="true" aria-labelledby="gcashPaymentModal"
                tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h1 class="modal-title">Upload Your Screenshot</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body" id="gcashModalBody">
                            Please upload a screenshot of your Gcash down payment below.
                            <img src="<?= htmlspecialchars($image) ?>" alt="Downpayment Image" id="preview"
                                class="downpaymentPic">
                            <input type="hidden" name="bookingID" id="bookingID" value="<?= $bookingID ?>">
                            <input type="file" name="downpaymentPic" id="downpaymentPic" hidden>
                            <label for="downpaymentPic" class="custom-file-button btn btn-outline-primary mt-2">Upload
                                Payment Receipt</label>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="submitDownpaymentImage">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <script>
        //Hide the make a downpayment button
        const paymentStatus = document.getElementById("paymentStatus").value;
        const bookingStatus = document.getElementById("bookingStatus").value;
        const confirmedBookingStatus = document.getElementById("confirmedBookingStatus").value;
        const paymentMethod = document.getElementById("paymentMethod").value;
        if (bookingStatus === "Pending" && confirmedBookingStatus === '') {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (bookingStatus === "Approved" && confirmedBookingStatus === "Pending" && paymentStatus === "Unpaid") {
            document.getElementById("makeDownpaymentBtn").style.display = "show";
        } else if (confirmedBookingStatus === "Approved" && paymentStatus === "Partially Paid") {
            document.getElementById("makeDownpaymentBtn").style.display = "show";
        } else if (confirmedBookingStatus === "Approved" && paymentStatus === "Fully Paid") {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (paymentMethod === 'Cash') {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        } else if (paymentMethod === 'GCash') {
            document.getElementById("makeDownpaymentBtn").style.display = "block";
        } else {
            document.getElementById("makeDownpaymentBtn").style.display = "none";
        }
    </script>


    <script>
        //Show the image preview
        document.querySelector("input[type='file']").addEventListener("change", function(event) {
            let reader = new FileReader();
            reader.onload = function() {
                let preview = document.getElementById("preview");
                preview.src = reader.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(event.target.files[0]);
        });
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


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');
        if (paramValue === "imageError") {
            Swal.fire({
                title: "Oops!",
                text: "Failed to upload downpayment receipt image",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === "imageFailed") {
            Swal.fire({
                title: "Oops!",
                text: "No downpayment image submitted.",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === "imageSize") {
            Swal.fire({
                title: "Oops!",
                text: "File is too large. Maximum allowed size is 64MB.",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        }


        if (paramValue) {
            const url = new URL(window.location.href);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>


</body>

</html>