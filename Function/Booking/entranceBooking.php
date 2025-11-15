<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');
require '../../Config/dbcon.php';
require '../Helpers/userFunctions.php';
$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';

require_once '../emailSenderFunction.php';

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

$gcashDetails = '';
$resortInfoName = 'gcashNumber';
$getPaymentDetails = $conn->prepare("SELECT resortInfoDetail FROM resortinfo WHERE resortInfoName = ?");
$getPaymentDetails->bind_param('s', $resortInfoName);
$getPaymentDetails->execute();
$result = $getPaymentDetails->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $gcashDetails = 'For GCASH payment, here are our GCASH details where you can send the down payment. <br> <strong>' . $row['resortInfoDetail'] . '</strong>';
}

if (isset($_POST['bookRates'])) {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    $serviceIDs = [];
    $servicePrices = [];
    $serviceCapacity = [];
    $services = [];
    $resortServiceIDs = [];

    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $tourType = mysqli_real_escape_string($conn, $_POST['tourType']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
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

    $cottageChoices = !empty($_POST['cottageOptions']) ? $_POST['cottageOptions'] : [];
    $roomChoices = !empty($_POST['roomOptions']) ?  $_POST['roomOptions'] : [];
    $addOnsServices = !empty($_POST['addOnsServices']) ?  $_POST['addOnsServices'] : [];

    if (empty($phoneNumber)) {
        header('Location: ../../Pages/Customer/resortBooking.php?action=phoneNumber');
        exit;
    }



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

        $getSameServiceName = $conn->prepare("SELECT s.serviceID, rs.RSprice, rs.RScapacity, rs.RServiceName, rs.RSdescription, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
            WHERE rs.RServiceName = ? AND rs.RSduration = '22 hours'");
        foreach ($roomChoices as $selectedRoom) {
            $selectedRoom = trim($selectedRoom);
            $getSameServiceName->bind_param('s', $selectedRoom);
            $getSameServiceName->execute();
            $getSameServiceResult = $getSameServiceName->get_result();

            if ($getSameServiceResult->num_rows > 0) {
                while ($data = $getSameServiceResult->fetch_assoc()) {
                    $resortServiceIDs[] = $data['resortServiceID'];
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

    $bookingCode = 'TOUR' . date('ymd') . generateCode(5);

    $totalAdultFee = multiplication($adultCount, $adultRate);
    $totalChildFee =  multiplication($childrenCount, $childRate);
    $totalEntranceFee = addition($totalAdultFee, $totalChildFee, 0);

    $addOns = is_array($addOnsServices) ? implode(', ', $addOnsServices) : $addOnsServices;


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
        addOns, paymentMethod, bookingStatus, bookingType, arrivalTime, bookingCode) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $insertBooking->bind_param(
            "isiiiiissddssisss",
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
            $arrivalTime,
            $bookingCode
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
        $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request. <a href="booking.php">View here.</a>';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, senderID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);

        if (!$insertBookingNotificationRequest->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' . $insertBookingNotificationRequest->error);
        }
        $expiresAt = NULL;

        if ($bookingStatus === 2) {
            $isSend = false;
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
            $approvedStatusID = 2;
            $updateApproval = $conn->prepare("UPDATE `booking` SET bookingStatus = ?, `approvedBy`= ?,`approvedDate`= ? WHERE bookingID = ?");
            $updateApproval->bind_param('issi', $approvedStatusID, $approvedBy, $today, $bookingID);
            if (!$updateApproval->execute()) {
                $conn->rollback();
                throw new Exception('Error :' . $insertConfirmedBooking->error);
            }
            $updateApproval->close();

            $insertConfirmedBooking = $conn->prepare("INSERT INTO confirmedbooking(bookingID, finalBill, userBalance, downpaymentDueDate, paymentDueDate )
                VALUES(?,?,?,?,?)");
            $insertConfirmedBooking->bind_param("iddss", $bookingID,  $totalCost, $totalCost, $downpaymentDueDate, $paymentDueDate);
            if (!$insertConfirmedBooking->execute()) {
                $conn->rollback();
                throw new Exception('Error :' . $insertConfirmedBooking->error);
            }
            $insertConfirmedBooking->close();

            $confirmedBookingID = $conn->insert_id;

            $receiver = 'Customer';
            $message = 'Your booking has been approved (#' . $bookingCode  . '). Please complete your payment within 24 hours to confirm your reservation. Kindly check your email for more details.';
            $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, receiverID, message, receiver)
            VALUES(?,?,?,?)");
            $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);

            if (!$insertBookingNotificationRequest->execute()) {
                $conn->rollback();
                throw new Exception('Error: ' . $insertBookingNotificationRequest->error);
            }
            $startDate = new Datetime($scheduledStartDate);
            $bookingDate = $startDate->format('M. d, Y g:i A');
            $dateCreated = date('d F Y');
            $email_message = '
                        <body style="font-family: Poppins, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <!-- Header -->
                                <tr style="background-color: #365CCE;">
                                    <td style="text-align:center; padding: 30px;">
                                        <h4
                                            style="font-family: Poppins, sans-serif;  font-weight: 700; font-size: 18px; color: #ffffff; font-size: 18px; margin: 0;">
                                            THANKS FOR BOOKING WITH MAMYR!
                                        </h4>
                                        <h2
                                            style="font-family: Poppins, sans-serif; font-weight: 200; font-size: 16px;  color: #ffffff; margin: 10px 0 0;">
                                            Confirm Your Reservation with Payment
                                        </h2>
                                    </td>
                                </tr>

                                <!-- Body -->
                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: -20PX 0 20px; font-style: italic;">
                                            Booking Reference: <strong>' . $bookingCode . '</strong> &nbsp;|&nbsp; Created on ' . $dateCreated . '
                                        </p>

                                        <p style="font-size: 14px; margin: 20px 0 10px;">Hello <strong> ' . $firstName . '</strong>,</p>

                                        <p style="font-size: 14px; margin: 20px 0 10px;">Here are your booking details:</p>

                                        <p style="font-size: 14px; margin: 8px 0;">Booking Reference: <strong>' . $bookingCode . '</strong></p>
                                        <p style="font-size: 14px; margin: 8px 0;">Booking Date: <strong>' . $bookingDate . '</strong>
                                        </p>
                                        <p style="font-size: 14px; margin: 8px 0;">Booking Type: <strong>' . $bookingType . ' Booking &mdash; '
                . $tourType . '</strong></p>
                                                                                        <p style="font-size: 14px; margin: 8px 0;">Grand Total: <strong>₱' . number_format($totalCost, 2) .
                '</strong></p>

                                                                                        <p style="font-size: 14px;">
                                                                                            <strong>To confirm your reservation</strong>, a <strong>downpayment</strong> of
                                                                                            ₱' .
                number_format($downpayment, 2) .
                ' must
                                            be paid within <strong>24 hours</strong>.
                                        </p>

                                        <p style="font-size: 14px;">If we do not receive the payment within this timeframe, your booking may be
                                            given to other customers. Make sure to upload the receipt in the website.</p>

                                        <p>' . $gcashDetails . '</p>
                                        <p> While for cash payment, please proceed to the resort to settle your downpayment with the admin/staff. </p>
                                        <p style="margin: 10px 0 0;"> You can contact us directly here: <a
                                                href="https://www.facebook.com/messages/t/100888189251567"
                                                style="color: #007bff; text-decoration: none;"> Message us on Facebook</a> </p>

                                        <p style=" font-size: 14px; margin: 20px 0 0;">We look forward to welcoming you soon!</p>



                                        <p style="font-size: 16px; margin: 30px 0 0;">Thank you,</p>
                                        <p style="font-size: 16px; font-weight: bold; margin: 8px 0 0;">Mamyr Resort and Events Place</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
                    ';

            $subject = 'Booking Confirmation';

            if (sendEmail($email, $firstName, $subject, $email_message, $env)) {
                $isSend = true;
            };

            $today = new DateTime();
            $expiresAt = $today->modify('+24 hours')->format('Y-m-d H:i:s');
            if (!$isSend) {
                $conn->rollback();
                throw new Exception('Failed Sending Email');
            }
        }

        $insertUnavailableService = $conn->prepare("INSERT INTO serviceunavailabledate(bookingID, resortServiceID, unavailableStartDate, unavailableEndDate, expiresAt) VALUES (?,?,?,?,?)");
        if (!empty($resortServiceIDs)) {
            for ($i = 0; $i < count($resortServiceIDs); $i++) {
                $resortServiceID = $resortServiceIDs[$i];
                $insertUnavailableService->bind_param("iisss", $bookingID, $resortServiceID, $scheduledStartDate, $scheduledEndDate, $expiresAt);
                if (!$insertUnavailableService->execute()) {
                    $conn->rollback();
                    throw new Exception('Error :' . $insertUnavailableService->error);
                }
            }
        }
        $insertUnavailableService->close();



        unset($_SESSION['resortFormData']);
        $conn->commit();
        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        $insertBookingServices->close();
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Error: ' . $e->getMessage());
        $_SESSION['resortFormData'] = $_POST;
        header('Location: ../../../../Pages/Customer/resortBooking.php?action=errorBooking');
        exit;
    }
}
