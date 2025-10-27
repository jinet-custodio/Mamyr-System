<?php

require '../../Config/dbcon.php';
require '../Helpers/userFunctions.php';

session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);


$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';

require_once '../emailSenderFunction.php';

$gcashDetails = '';
$resortInfoName = 'gcashNumber';
$getPaymentDetails = $conn->prepare("SELECT resortInfoDetail FROM resortinfo WHERE resortInfoName = ?");
$getPaymentDetails->bind_param('s', $resortInfoName);
$getPaymentDetails->execute();
$result = $getPaymentDetails->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $gcashDetails = 'Here is our gcash details where you can send the downpayment. <br> <strong>' . $row['resortInfoDetail'] . '</strong>';
}



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

unset($_SESSION['hotelFormData']);

if (isset($_POST['hotelBooking'])) {
    $selectedHotels = [];

    //User data
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);

    $hoursSelected = mysqli_real_escape_string($conn, $_POST['hoursSelected']);
    $checkInDate = mysqli_real_escape_string($conn, $_POST['scheduledStartDate']);
    $checkOutDate = mysqli_real_escape_string($conn, $_POST['scheduledEndDate']);
    $arrivalTime = mysqli_real_escape_string($conn, $_POST['arrivalTime']);

    $adultCount = (int)$_POST['adultCount'] ?? 0;
    $childrenCount = (int) $_POST['childrenCount'] ?? 0;
    $toddlerCount = (int) $_POST['toddlerCount'] ?? 0;
    $totalPax = (int) $_POST['totalPax'];
    $totalCapacity = (int) $_POST['capacity'];
    $additionalGuest = (int) $_POST['additionalGuest'];

    $selectedHotels = !empty($_POST['hotelSelections']) ? $_POST['hotelSelections'] : [];

    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);

    $downpayment = (float) $_POST['downPayment'];
    $totalCost = (float) $_POST['totalCost'];
    // $additionalCharge = (int) $_POST['additionalFee'];

    $bookingStatus = 1;
    $serviceIDs = [];
    $hotelPrices = [];
    $hotelCapacity = [];
    $resortServiceIDs = [];

    $arrivalTimeObj = new DateTime($arrivalTime);
    $arrivalTime = $arrivalTimeObj->format('H:i:s');

    if (empty($selectedHotels)) {
        header("Location: ../../Pages/Customer/hotelBooking.php");
    }

    if (empty($phoneNumber)) {
        header("Location: ../../Pages/Customer/hotelBooking.php?action=phoneNumber");
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
            $data = $resultHotelQuery->fetch_assoc();
            $serviceIDs[] = $data['serviceID'];
            $hotelPrices[] = $data['RSprice'];
            $hotelCapacity[] = $data['RScapacity'];
            $resortServiceIDs[] = $data['resortServiceID'];
        }
    }

    $getSameServiceName = $conn->prepare("SELECT s.serviceID, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
            WHERE rs.RServiceName = ? AND rs.RSduration = '11 hours'");
    foreach ($selectedHotels as $selectedRoom) {
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




    $getSameServiceName = $conn->prepare("SELECT s.serviceID, rs.resortServiceID FROM service s
            INNER JOIN resortamenity rs ON s.resortServiceID = rs.resortServiceID 
            WHERE rs.RServiceName = ? AND rs.RSduration = '11 hours'");
    foreach ($selectedHotels as $selectedRoom) {
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



    $hoursNum = str_replace(" hours", "", $hoursSelected);

    $bookingCode = 'HTL' . date('ymd') . generateCode(5);

    $conn->begin_transaction();
    try {
        $approvedBy = 'System';
        $approvedStatus = 2;
        //Insert Booking
        $insertBooking = $conn->prepare("INSERT INTO booking(userID, toddlerCount, adultCount, kidCount, guestCount, durationCount, startDate, endDate, 
                    paymentMethod,  totalCost, downpayment, bookingStatus, bookingType, arrivalTime, bookingCode, approvedBy, bookingStatus) 
                    VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insertBooking->bind_param(
            "iiiiiisssddisss",
            $userID,
            $toddlerCount,
            $adultCount,
            $childrenCount,
            $totalPax,
            $hoursNum,
            $checkInDate,
            $checkOutDate,
            $paymentMethod,
            // $additionalCharge,
            $totalCost,
            $downpayment,
            $bookingStatus,
            $bookingType,
            $arrivalTime,
            $bookingCode,
            $approvedBy,
            $approvedStatus

        );
        if (!$insertBooking->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' . $insertBooking->error);
        }

        $bookingID = $conn->insert_id;

        $insertBookingServices = $conn->prepare("INSERT INTO bookingservice(bookingID, serviceID, guests, bookingServicePrice)
        VALUES(?,?,?,?)");
        if (!empty($serviceIDs)) {
            for ($i = 0; $i < count($serviceIDs); $i++) {
                $serviceID = $serviceIDs[$i];
                $servicePrice = $hotelPrices[$i];
                $serviceCapacity = $hotelCapacity[$i];

                $insertBookingServices->bind_param("iiid", $bookingID, $serviceID, $serviceCapacity, $servicePrice);
                if (!$insertBookingServices->execute()) {
                    $conn->rollback();
                    throw new Exception('Error: ' . $insertBookingServices->error);
                }
            }
        }
        $insertBookingServices->close();

        $receiver = 'Admin';
        $message = 'A customer has submitted a new ' . strtolower($bookingType) . ' booking request. <a href="booking.php">View here.</a>';
        $insertBookingNotificationRequest = $conn->prepare("INSERT INTO notification(bookingID, senderID, message, receiver)
            VALUES(?,?,?,?)");
        $insertBookingNotificationRequest->bind_param("iiss", $bookingID, $userID, $message, $receiver);
        if (! $insertBookingNotificationRequest->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' .  $insertBookingNotificationRequest->error);
        }
        $insertBookingNotificationRequest->close();

        $today = new DateTime();
        $dateCreated = $today->format('d M Y');
        $add24hrs = $today->modify('+24 hours');
        $expiresAt = $add24hrs->format('Y-m-d H:i:s');
        $startDate = new DateTime($checkInDate);
        $bookingDate = $startDate->format('M. d, Y g:i A');


        $downpaymentDueDate = $startDate->modify('-1 day')->format('Y-m-d H:i:s');


        $insertIntoConfirmedBooking = $conn->prepare("INSERT INTO `confirmedbooking`(`bookingID`, `finalBill`, `userBalance`, `paymentDueDate`, `downpaymentDueDate`) VALUES (?,?,?,?,?)");
        $insertIntoConfirmedBooking->bind_param('iddss', $bookingID, $totalCost, $totalCost, $checkInDate, $downpaymentDueDate);

        if (!$insertIntoConfirmedBooking->execute()) {
            $conn->rollback();
            throw new Exception('Error: ' .  $insertIntoConfirmedBooking->error);
        }

        $insertUnavailableService = $conn->prepare("INSERT INTO serviceunavailabledate(bookingID,resortServiceID, unavailableStartDate, unavailableEndDate, expiresAt) VALUES (?,?,?,?,?)");
        if (!empty($resortServiceIDs)) {
            for ($i = 0; $i < count($resortServiceIDs); $i++) {
                $resortServiceID = $resortServiceIDs[$i];
                $insertUnavailableService->bind_param("iisss", $bookingID, $resortServiceID, $checkInDate, $checkOutDate, $expiresAt);
                if (!$insertUnavailableService->execute()) {
                    $conn->rollback();
                    throw new Exception('Error :' . $insertUnavailableService->error);
                }
            }
        }
        $insertUnavailableService->close();

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
                                        <p style="font-size: 14px; margin: 8px 0;">Booking Type: <strong>' . $bookingType . ' Booking </strong></p>
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

                                        <p><strong> ' . $gcashDetails . '. </strong></p>
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
        $isSend = false;
        if (sendEmail($email, $firstName, $subject, $email_message, $env)) {
            $isSend = true;
        }

        if (!$isSend) {
            $conn->rollback();
            throw new Exception("Failed Sending Email");
        }


        $conn->commit();
        header('Location: ../../Pages/Customer/bookNow.php?action=success');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Error: ' . $e->getMessage());
        $_SESSION['hotelFormData'] = $_POST;
        header('Location: ../../../../Pages/Customer/hotelBooking.php?action=errorBooking');
    }
}
