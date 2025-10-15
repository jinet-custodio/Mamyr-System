<?php

require '../../Config/dbcon.php';
require '../Helpers/userFunctions.php';

$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';

require '../emailSenderFunction.php';
session_start();
date_default_timezone_set('Asia/Manila');

$userID = (int) $_SESSION['userID'];
$userRole = (int) $_SESSION['userRole'];
$adminID = (int) $_SESSION['adminID'];


function getMessageReceiver($userRoleID)
{
    switch ($userRoleID) {
        case 1:
            $receiver = 'Customer';
            break;
        case 2:
            $receiver = 'Partner';
            break;
        case 3:
            $receiver = 'Admin';
            break;
        default:
            $receiver = 'Customer';
    }
    return $receiver;
}


$getAdminName = $conn->prepare("SELECT adminID, fullName FROM admin WHERE userID = ?");
$getAdminName->bind_param('i', $userID);
if (!$getAdminName->execute()) {
    error_log("Failed Executing Admin Query. Error: " . $getAdminName->error);
}

$result = $getAdminName->get_result();

if ($result->num_rows === 0) {
    error_log('NO DATA  ' . $userID);
    $approvedBy = 'Unknown';
}

$data = $result->fetch_assoc();

$approvedBy = $data['fullName'];

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

//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $customPackageID = (int) $_POST['customPackageID'];
    $tourType = isset($_POST['tourType'])
        ? '&mdash; ' . mysqli_real_escape_string($conn, $_POST['tourType'])
        : '';
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $serviceIDs = [];
    $serviceIDs = !empty($_POST['serviceIDs']) ? array_map('trim',   $_POST['serviceIDs']) : [];
    // $notes = isset($_POST['approvalNotes']) ? mysqli_real_escape_string($conn, $_POST['approvalNotes']) : 'N/A';
    //*Date and Time
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    $discountAmount = (float) $_POST['discountAmount'];
    // $rawOriginalBill = mysqli_real_escape_string($conn, $_POST['originalBill']);


    // $discount = (float) str_replace(['₱', ','], '', $discountAmount);
    // $originalBill = (float) str_replace(['₱', ','], '', $rawOriginalBill);
    $rawFinalBill = mysqli_real_escape_string($conn, $_POST['finalBill']);
    $finalBill = (float) str_replace(['₱', ','], '', $rawFinalBill);

    if ($discountAmount != 0.00) {
        $finalBill -= $discountAmount;
    }

    $downpayment = $finalBill * .3;

    // Options in approval
    // $selectedOption = mysqli_real_escape_string($conn, $_POST['adjustOption']) ?? '';

    // $discountAmount = 0.00;
    // if ($selectedOption === 'editBill') {
    //     $editedFinalBill = floatval($_POST['editedFinalBill']);
    //     $finalBill =  ($editedFinalBill !== $finalBill && $editedFinalBill !== 0) ? $editedFinalBill : $finalBill;
    // } elseif ($selectedOption === 'discount') {
    //     $discountAmount = floatval($_POST['discountAmount']);
    //     $finalBill = $finalBill - $discountAmount;
    // }

    // $applyAdditionalCharge = mysqli_real_escape_string($conn, $_POST['applyAdditionalCharge']) ?? '';
    // $additionalCharge = !empty($applyAdditionalCharge) ? floatval($_POST['additionalCharge']) : 0.00;

    // switch (strtolower($bookingType)) {
    //     case 'resort':
    //         $type = 'TOUR';
    //         break;
    //     case 'hotel':
    //         $type = 'HTL';
    //         break;
    //     case 'event':
    //         $type = 'EVT';
    //         break;
    //     default:
    //         $type = 'MAMYR';
    //         break;
    // }

    $bookingCode = mysqli_real_escape_string($conn, $_POST['bookingCode']);
    // strtoupper($type) . date('ymd') . generateCode(5)
    if ($bookingType === 'Event') {
        $rawVenuePrice = mysqli_real_escape_string($conn, $_POST['venuePrice']);
        $rawTotalFoodPrice = mysqli_real_escape_string($conn, $_POST['foodPriceTotal']);
        $venuePrice = (float) str_replace(['₱', ','], '', $rawVenuePrice) ?? 0;
        $foodPriceTotal = (float) str_replace(['₱', ','], '', $rawTotalFoodPrice) ?? 0;
        // $foodIDs = !empty($_POST['foodIDs']) ? array_map('trim',  $_POST['foodIDs']) : [];

        $venueName = mysqli_real_escape_string($conn, $_POST['venue']);

        if (stripos($venueName, 'Main') !== false) {
            $miniVenue = 'Mini Function Hall';
            $getMiniIDQuery = $conn->prepare("SELECT ra.resortServiceID, s.serviceID FROM resortamenity ra 
            LEFT JOIN service s ON ra.resortServiceID = s.resortServiceID
            WHERE ra.RServiceName = ?");
            $getMiniIDQuery->bind_param('s', $miniVenue);
            if (!$getMiniIDQuery->execute()) {
                error_log('Error getting mini function hall ID' . $getMiniIDQuery->error);
            }

            $result = $getMiniIDQuery->get_result();
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $serviceIDs[] = $data['serviceID'];
            }
        }
    }

    $availabilityID = 2;
    $conn->begin_transaction();
    try {
        // $getServicesQuery = $conn->prepare("SELECT * FROM service WHERE serviceID = ?");


        //* Insert this to unavailable dates
        // foreach ($serviceIDs as $serviceID) {
        //     $getServicesQuery->bind_param("i", $serviceID);
        //     if (!$getServicesQuery->execute()) {
        //         $conn->rollback();
        //         throw new Exception("Failed to fetch service for ID: $serviceID");
        //     }

        //     $getServicesQueryResult = $getServicesQuery->get_result();
        //     if ($getServicesQueryResult->num_rows === 0) {
        //         $conn->rollback();
        //         throw new Exception("No service found for ID: $serviceID");
        //     }

        //     $row = $getServicesQueryResult->fetch_assoc();
        //     $serviceType = $row['serviceType'];

        //     switch ($serviceType) {
        //         case 'Resort':
        //             $resortServiceID = $row['resortServiceID'];
        //             $insertToUnavailableDates = $conn->prepare("INSERT INTO serviceunavailabledate(resortServiceID, unavailableStartDate, unavailableEndDate) VALUES (?, ?, ?)");
        //             $insertToUnavailableDates->bind_param('iss', $resortServiceID, $startDate, $endDate);
        //             if (!$insertToUnavailableDates->execute()) {
        //                 $conn->rollback();
        //                 throw new Exception("Failed to insert unavailable date for resort service ID: $resortServiceID");
        //             }
        //             $insertToUnavailableDates->close();
        //             break;

        //         case 'Partner':
        //             $partnershipServiceID = $row['partnershipServiceID'];
        //             $insertToUnavailableDates = $conn->prepare("INSERT INTO serviceunavailabledate(partnershipServiceID, unavailableStartDate, unavailableEndDate) VALUES (?, ?, ?)");
        //             $insertToUnavailableDates->bind_param('iss', $partnershipServiceID, $startDate, $endDate);
        //             if (!$insertToUnavailableDates->execute()) {
        //                 $conn->rollback();
        //                 throw new Exception("Failed to insert unavailable date for partner service ID: $partnershipServiceID");
        //             }
        //             $insertToUnavailableDates->close();
        //             break;

        //         default:
        //             $conn->rollback();
        //             throw new Exception("Unknown service type: $serviceType for service ID: $serviceID");
        //     }
        // }

        //Update Booking Table Status
        $approvedStatus = 2;
        $approvedDate = date('Y-m-d h:i:s');
        $updateStatus = $conn->prepare("UPDATE booking SET bookingCode,  downpayment = ?, bookingStatus = ?, approvedBy = ?, approvedDate =?  WHERE bookingID = ?");
        $updateStatus->bind_param("sdsssi", $bookingCode, $downpayment, $approvedStatus, $approvedBy, $approvedDate, $bookingID);
        if (!$updateStatus->execute()) {
            $conn->rollback();
            throw new Exception("Failed updating the status for bookingID: $bookingID");
        }

        $startDateObj = new DateTime($startDate);
        $startDateObj->modify('-1 day');
        $downpaymentDueDate = $startDateObj->format('Y-m-d H:i:s');

        //Insert into Confirmed Booking
        $insertConfirmed = $conn->prepare("INSERT INTO confirmedbooking(bookingID, discountAmount, finalBill, userBalance, downpaymentDueDate, paymentDueDate)
            VALUES(?,?,?,?,?,?)");
        $insertConfirmed->bind_param(
            "idddss",
            $bookingID,
            $discountAmount,
            $finalBill,
            $finalBill,
            $downpaymentDueDate,
            $startDate,
        );

        if (!$insertConfirmed->execute()) {
            $conn->rollback();
            throw new Exception("Failed to insert in confirmed booking table.");
        }

        $receiver = getMessageReceiver($userRoleID);
        $message = 'Your ' . $bookingType . ' booking has been approved successfully. Please complete your payment within 24 hours to confirm your reservation. Kindly check your email for more details.';
        $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, senderID, receiverID, message, receiver) VALUES(?,?,?,?,?)");
        $insertNotification->bind_param('iiiss', $bookingID, $userID, $customerID, $message, $receiver);

        if (!$insertNotification->execute()) {
            $conn->rollback();
            throw new Exception("Failed to insert in notifcation table");
        }

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
                                        Booking Reference: <strong>' . $bookingCode . '</strong> &nbsp;|&nbsp; Created on ' . $dateCreated .
            '
                                    </p>

                                    <p style="font-size: 14px; margin: 20px 0 10px;">Hello <strong> ' . $firstName . '</strong>,</p>

                                    <p style="font-size: 14px; margin: 20px 0 10px;">Here are your booking details:</p>

                                    <p style="font-size: 14px; margin: 8px 0;">Booking Reference: <strong>' . $bookingCode . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Booking Date: <strong>' .  $startDate . '</strong>
                                    </p>
                                    <p style="font-size: 14px; margin: 8px 0;">Booking Type: <strong>' . htmlspecialchars($tourType) . ' Booking ' . htmlspecialchars($tourType) . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Grand Total: <strong>₱' . number_format($finalBill, 2) .
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

        $isSend =  false;
        if (sendEmail($email, $firstName, $subject, $email_message, $env)) {
            $isSend = true;
        }

        if (!$isSend) {
            $conn->rollback();
            throw new Exception('Failed Sending Email');
        }

        $conn->commit();
        $updateStatus->close();
        $insertConfirmed->close();
        $insertNotification->close();

        unset($_SESSION['bookingID']);
        header('Location: ../../Pages/Admin/booking.php?action=approvedSuccess');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error " . $e->getMessage());
        header("Location: ../../Pages/Admin/viewBooking.php?error=exception&action=approvalFailed");
        exit();
    }
}



//Reject Button is Click
if (isset($_POST['rejectBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $bookingStatusID = (int) $_POST['bookingStatusID'];
    $customerID = (int) $_POST['customerID'];
    $reason = (int) $_POST['rejection-reason'];
    $otherReason = mysqli_real_escape_string($conn, $_POST['reasonDescription']) ?? NULL;
    $userRoleID = (int) $_POST['userRoleID'];

    error_log('reasonID: ' . $reason);
    error_log("AdminID: " . $adminID);

    if (empty($reason) && empty($otherReason)) {
        header('Location: ../../Pages/Admin/viewBooking.php?action=rejectionEmpty');
        exit();
    }
    $conn->begin_transaction();
    try {
        $bookingQuery = $conn->prepare("SELECT * FROM booking WHERE bookingID = ? AND bookingStatus = ?");
        $bookingQuery->bind_param("is", $bookingID, $bookingStatusID);
        $bookingQuery->execute();
        $result = $bookingQuery->get_result();

        if ($result->num_rows === 0) {
            $conn->rollback();
            throw new Exception("Booking does not exist or has already been processed.");
        }

        $rejectedStatus = 5; //Rejected
        $updateStatus = $conn->prepare("UPDATE booking SET bookingStatus = ? WHERE bookingID = ?");
        $updateStatus->bind_param("ii", $rejectedStatus, $bookingID);

        if (!$updateStatus->execute()) {
            $conn->rollback();
            throw new Exception("Failed to update booking status.");
        }

        $insertRejectionReason = $conn->prepare("INSERT INTO `booking_rejection`(`bookingID`, `adminID`, `reasonID`, `otherReason`) VALUES (?,?,?,?)");
        $insertRejectionReason->bind_param("iiis", $bookingID, $adminID, $reason, $otherReason);
        if (!$insertRejectionReason->execute()) {
            $conn->rollback();
            throw new Exception("Failed executing insertion of reason" . $insertRejectionReason->error);
        }

        if (!empty($reason)) {
            $getMessage = $conn->prepare("SELECT reasonDescription FROM `reason` WHERE `reasonID` = ?");
            $getMessage->bind_param('i', $reason);
            if (!$getMessage->execute()) {
                $conn->rollback();
                throw new Exception("Failed getting the reason" . $getMessage->error);
            }

            $result = $getMessage->get_result();

            $row = $result->fetch_assoc();

            $message = "Booking Rejected: " . $row['reasonDescription'];
        } else {
            $message = "Booking Rejected: " . $otherReason;
        }

        $receiver = getMessageReceiver($userRoleID);
        $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, senderID, receiverID,  message, receiver) VALUES(?,?,?,?,?)");
        $insertNotification->bind_param('iiiss', $bookingID, $userID, $customerID, $message, $receiver);

        if (!$insertNotification->execute()) {
            $conn->rollback();
            throw new Exception("Failed to insert notification.");
        }

        $conn->commit();
        $updateStatus->close();
        $insertNotification->close();
        unset($_SESSION['bookingID']);
        header('Location: ../../Pages/Admin/booking.php?action=rejectedSuccess');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Booking rejection error: " . $e->getMessage());
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewBooking.php?action=rejectionFailed');
        exit();
    }
}
