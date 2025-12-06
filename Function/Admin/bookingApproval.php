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
            $receiver = 'Business Partner';
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
    $gcashDetails = 'For GCASH payment, here is our gcash details where you can send the downpayment. <br> <strong>' . $row['resortInfoDetail'] . '</strong>';
}

//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $userRoleID = (int) $_POST['userRoleID'];
    $customerID = (int) $_POST['customerID'];
    $customPackageID = (int) $_POST['customPackageID'];
    // $serviceIDs = $_POST['serviceIDs'];
    $tourType = isset($_POST['tourType'])
        ? '&mdash; ' . mysqli_real_escape_string($conn, $_POST['tourType'])
        : '';
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);

    //*Date and Time
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    $discountAmount = (float) $_POST['discountAmount'];

    $finalBill = mysqli_real_escape_string($conn, $_POST['finalBill']);
    $downpayment = $finalBill * .3;

    $bookingCode = mysqli_real_escape_string($conn, $_POST['bookingCode']);
    // strtoupper($type) . date('ymd') . generateCode(5)
    if ($bookingType === 'Event') {
        $rawVenuePrice = mysqli_real_escape_string($conn, $_POST['venuePrice']);
        $totalFoodPrice = !empty($_POST['foodPrice']) ? mysqli_real_escape_string($conn, $_POST['foodPrice']) : 0;
        $newFoodPrice = !empty($_POST['newFoodPrice']) ? (float) $_POST['newFoodPrice'] : $totalFoodPrice;
        $venuePrice = (float) str_replace(['₱', ','], '', $rawVenuePrice) ?? 0;


        $customerChoice = mysqli_real_escape_string($conn, $_POST['customerChoice']);

        if (!empty($_POST['partnerServices'])) {
            // $businessApprovalStatus = mysqli_real_escape_string($conn, $_POST['businessApprovalStatus']) ?? '';

            foreach ($_POST['partnerServices'] as $partnerID => $services) {
                foreach ($services as $service) {
                    $serviceID = intval($service['id']);
                    $status = strtolower(trim($service['status']));
                    $price = floatval($service['price']);

                    if ($customerChoice === 'cancel' && $status === 'rejected') {
                        header('Location: ../../../../Pages/Admin/viewBooking.php?action=addOnsService-rejected');
                        exit();
                    }

                    if ($status === 'rejected') {
                        $finalBill -= $price;
                    }
                }
            }
        }
    }



    $conn->begin_transaction();
    try {
        $today = new DateTime();
        $expiresAt = $today->modify('+24 hours')->format('Y-m-d H:i:s');
        //Update status and expiration of booking unavailable
        $searchBookingID = $conn->prepare("SELECT bookingID FROM serviceunavailabledate WHERE bookingID = ?");
        $searchBookingID->bind_param('i', $bookingID);
        if (!$searchBookingID->execute()) {
            $conn->rollback();
            throw new Exception("Failed executing (searchBookingID) booking ID: $bookingID");
        }

        $searchID = $searchBookingID->get_result();
        $hold = 'hold';
        if ($searchID->num_rows > 0) {
            $updateUnavailableDates = $conn->prepare("UPDATE `serviceunavailabledate` SET `expiresAt`= ? WHERE `bookingID`= ? AND `status` = ?");
            $updateUnavailableDates->bind_param('sis', $expiresAt,  $bookingID, $hold);
            if (!$updateUnavailableDates->execute()) {
                $conn->rollback();
                throw new Exception("Failed to update unavailable date for booking ID: $bookingID");
            }
            $updateUnavailableDates->close();
        }

        //Update customer package
        if (!empty($customPackageID)) {
            $updateFoodPrice = $conn->prepare("UPDATE `custompackage` SET `totalFoodPrice`= ? WHERE customPackageID = ?");
            $updateFoodPrice->bind_param('di', $newFoodPrice, $customPackageID);
            if (!$updateFoodPrice->execute()) {
                $conn->rollback();
                throw new Exception("Failed updating the foodPrice in custome package id: $customPackageID");
            }
        }

        //Update Booking Table Status
        $approvedStatus = 2;
        $approvedDate = date('Y-m-d H:i:s');
        $updateStatus = $conn->prepare("UPDATE booking SET bookingCode = ?,  downpayment = ?, bookingStatus = ?, approvedBy = ?, approvedDate =?  WHERE bookingID = ?");
        $updateStatus->bind_param("sdsssi", $bookingCode, $downpayment, $approvedStatus, $approvedBy, $approvedDate, $bookingID);
        if (!$updateStatus->execute()) {
            $conn->rollback();
            throw new Exception("Failed updating the status for bookingID: $bookingID");
        }

        $startDateObj = new DateTime($startDate);
        $startDateObj->modify('-1 day');
        $downpaymentDueDate = $startDateObj->format('Y-m-d H:i:s');

        $bookingDate = $startDateObj->format('F d, Y g:i A');

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
            $startDate
        );

        if (!$insertConfirmed->execute()) {
            $conn->rollback();
            throw new Exception("Failed to insert in confirmed booking table.");
        }

        $receiver = getMessageReceiver($userRoleID);
        $message = '<strong> #' . $bookingCode . '</strong><br>Your ' . $bookingType . ' booking has been approved successfully. Please complete your payment within 24 hours to confirm your reservation. Kindly check your email for more details.';
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
                                    <p style="font-size: 14px; margin: 8px 0;">Booking Date: <strong>' .  $bookingDate . '</strong>
                                    </p>
                                    <p style="font-size: 14px; margin: 8px 0;">Booking Type: <strong>' . htmlspecialchars($tourType) . ' Booking ' . htmlspecialchars($tourType) . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Grand Total: <strong>₱' . number_format($finalBill, 2) .
            '</strong></p>

                                    <p style="font-size: 14px;">
                                        <strong>To confirm your reservation</strong>, a downpayment of <strong>
                                        ₱' .
            number_format($downpayment, 2) .
            '</strong> must
                                        be paid within <strong>24 hours</strong>.
                                    </p>

                                    <p style="font-size: 14px;">If we do not receive the payment within this timeframe, your booking may be
                                        given to other customers. Make sure to upload the receipt in the website.</p>
                                    <p style="margin: 10px 0 0;"> Website Link: <a
                                            href=""
                                            style="color: #007bff; text-decoration: none;"> Mamyr Resort & Event Place</a> </p>
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
        $_SESSION['bookingID'] = $bookingID;
        error_log("Error " . $e->getMessage());
        header("Location: ../../Pages/Admin/viewBooking.php?action=approvalFailed");
        exit();
    }
}


//Reject Button is Click
if (isset($_POST['rejectBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $bookingStatusID = (int) $_POST['bookingStatusID'];
    $customerID = (int) $_POST['customerID'];
    $reason = (int) $_POST['rejection-reason'];
    $serviceIDs = $_POST['serviceIDs'];
    $otherReason = mysqli_real_escape_string($conn, $_POST['reasonDescription']) ?? NULL;
    $userRoleID = (int) $_POST['userRoleID'];
    // $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    // error_log('reasonID: ' . $reason);
    // error_log("AdminID: " . $adminID);

    if (empty($reason) && empty($otherReason)) {
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewBooking.php?action=rejectionEmpty');
        exit();
    }
    $conn->begin_transaction();
    try {
        if (strtolower($bookingType) === 'resort') {
            $getServicesQuery = $conn->prepare("SELECT * FROM service WHERE serviceID = ?");
            //* Insert this to unavailable dates
            foreach ($serviceIDs as $category => $ids) {
                foreach ($ids as $serviceID) {
                    $getServicesQuery->bind_param("i", $serviceID);
                    if (!$getServicesQuery->execute()) {
                        $conn->rollback();
                        throw new Exception("Failed to fetch service for ID: $serviceID");
                    }

                    $getServicesQueryResult = $getServicesQuery->get_result();
                    if ($getServicesQueryResult->num_rows === 0) {
                        $conn->rollback();
                        throw new Exception("No service found for ID: $serviceID");
                    }

                    $row = $getServicesQueryResult->fetch_assoc();
                    $serviceType = $row['serviceType'];
                    switch ($serviceType) {
                        case 'Resort':
                            $resortServiceID = $row['resortServiceID'];
                            $removeFromUnavailableDates = $conn->prepare("DELETE FROM `serviceunavailabledate` WHERE `resortServiceID`= ?");
                            $removeFromUnavailableDates->bind_param('i', $resortServiceID);
                            if (!$removeFromUnavailableDates->execute()) {
                                $conn->rollback();
                                throw new Exception("Failed to delete unavailable date for resort service ID: $resortServiceID");
                            }
                            $removeFromUnavailableDates->close();
                            break;

                        default:
                            $conn->rollback();
                            throw new Exception("Unknown service type: $serviceType for service ID: $serviceID");
                    }
                }
            }
        }



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
        $errorMsg = $e->getMessage();

        if (strpos($errorMsg, 'email') !== false) {
            $friendlyMessage = 'Failed to send confirmation email.';
        } elseif (strpos($errorMsg, 'database') !== false) {
            $friendlyMessage = 'Database error occurred while approving the booking.';
        } else {
            $friendlyMessage = 'An unexpected error occurred during approval.';
        }
        $_SESSION['bookingID'] = $bookingID;
        $_SESSION['approvalError'] = $friendlyMessage;
        error_log("Error " . $errorMsg);
        header("Location: ../../Pages/Admin/viewBooking.php?action=approvalFailed");
        exit();
    }
}

if (isset($_POST['submitCharges'])) {

    $bookingID = (int) $_POST['bookingID'];
    $finalBill = (float) $_POST['new-bill'];
    $additionalCharge = (float) $_POST['additional-charge'];
    $confirmedBookingID = (int) $_POST['confirmedBookingID'];

    //Service charge
    $additionalCharges = $_POST['additionalCharges'];

    $downpayment = $finalBill * .3;

    $conn->begin_transaction();
    try {

        $insertCharges = $conn->prepare("INSERT INTO `additionalcharge`( `bookingID`, `chargeDescription`, `amount`) VALUES (?,?,?)");
        foreach ($additionalCharges as $name => $items) {
            $name = strtolower($name);
            if ($name === 'others') {
                $description = $items['quantity'] . ' — ' . $items['name'];
            } else {
                $description = $items['quantity'] . ' — ' . ucfirst($name);
            }

            $amount = $items['amount'];
            $insertCharges->bind_param('isd', $bookingID, $description, $amount);
            if (!$insertCharges->execute()) {
                $conn->rollback();
                throw new Exception('Failed Inserting charges' . $insertCharges->error);
            }
        }




        if (!empty($confirmedBookingID)) {
            $updateConfirmedBooking = $conn->prepare("UPDATE confirmedbooking SET additionalCharge = ?, finalBill = ? WHERE bookingID = ? ");
            $updateConfirmedBooking->bind_param('ddi', $additionalCharge, $finalBill,  $bookingID);
            if (!$updateConfirmedBooking->execute()) {
                $conn->rollback();
                throw new Exception('Failed Updating: ' . $updateConfirmedBooking->error);
            }
        } else {
            $updateBooking = $conn->prepare("UPDATE booking SET additionalCharge = ?, totalCost = ?, downpayment = ? WHERE bookingID = ? ");
            $updateBooking->bind_param('dddi', $additionalCharge, $finalBill, $downpayment, $bookingID);
            if (!$updateBooking->execute()) {
                $conn->rollback();
                throw new Exception('Failed Updating: ' . $updateBooking->error);
            }
        }



        $conn->commit();
        $_SESSION['bookingID'] = $bookingID;
        header('Location: ../../Pages/Admin/viewBooking.php?action=chargesAdded');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['bookingID'] = $bookingID;
        error_log('Exception Error: ' . $e->getMessage());
        header('Location: ../../Pages/Admin/viewBooking.php?action=chargesError');
        exit();
    }
}


if (isset($_POST['reschedBtn'])) {

    error_log(print_r($_POST, true));
    try {
        //IDs
        $bookingID = (int) $_POST['bookingID'];
        $serviceIDs = $_POST['serviceIDs'];
        $customerID = (int) $_POST['customerID'];
        $userRoleID = (int) $_POST['userRoleID'];
        $bookingCode = $_POST['bookingCode'];

        // Date and Time
        $newStartDateTime = $_POST['newStartDateTime'];
        $newEndDateTime = $_POST['newEndDateTime'];


        //Search for bookingID then check the dates
        $searchBooking = $conn->prepare("SELECT * FROM serviceunavailabledate WHERE bookingID = ? AND unavailableStartDate > NOW()");
        $searchBooking->bind_param("i", $bookingID);
        if (!$searchBooking->execute()) {
            throw new Exception("Executing search for booking failed. Error: " . $searchBooking->error);
        }

        $bookingResult = $searchBooking->get_result();

        if ($bookingResult->num_rows > 0) {
            $servicesIDs = [];
            while ($row = $bookingResult->fetch_assoc()) {
                $resortServiceID = (int) $row['resortServiceID'];
                $partnershipServiceID = (int) $row['partnershipServiceID'];

                if (!empty($row['resortServiceID'])) {
                    $searchNewDates = $conn->prepare("SELECT serviceUnavailableID FROM serviceunavailabledate WHERE resortServiceID = ? AND unavailableEndDate > ? AND unavailableStartDate < ? AND status != 'cancelled' AND bookingID != ? ");
                    $searchNewDates->bind_param("issi", $resortServiceID, $newStartDateTime, $newEndDateTime, $bookingID);
                    if (!$searchNewDates->execute()) {
                        throw new Exception("Executing search for new dates failed. Error: " . $searchNewDates->error);
                    }

                    $result = $searchNewDates->get_result();

                    if ($result->num_rows > 0) {
                        $_SESSION['bookingID'] = $bookingID;
                        header("Location: ../../../../Pages/Admin/viewBooking.php?action=someServiceNotAvailable");
                        exit();
                    }
                } else {
                    $searchNewDates = $conn->prepare("SELECT serviceUnavailableID FROM serviceunavailabledate WHERE partnershipServiceID = ? AND unavailableEndDate > ? AND unavailableStartDate < ? AND status != 'cancelled' AND bookingID != ?");
                    $searchNewDates->bind_param("issi", $partnershipServiceID, $newStartDateTime, $newEndDateTime, $bookingID);
                    if (!$searchNewDates->execute()) {
                        throw new Exception("Executing search for new dates failed. Error: " . $searchNewDates->error);
                    }

                    $result = $searchNewDates->get_result();

                    if ($result->num_rows > 0) {
                        $_SESSION['bookingID'] = $bookingID;
                        header("Location: ../../../../Pages/Admin/viewBooking.php?action=someServiceNotAvailable");
                        exit();
                    }
                }
            }

            $conn->begin_transaction();
            $updateDates = $conn->prepare("UPDATE `serviceunavailabledate` SET `unavailableStartDate`= ?,`unavailableEndDate`= ? WHERE bookingID = ?");
            $updateDates->bind_param("ssi", $newStartDateTime, $newEndDateTime, $bookingID);
            if (!$updateDates->execute()) {
                $conn->rollback();
                throw new Exception("Executing updating query for new dates failed. Error: " .  $updateDates->error);
            }

            $updateBooking = $conn->prepare("UPDATE booking SET startDate= ?, endDate= ? WHERE bookingID = ?");
            $updateBooking->bind_param("ssi", $newStartDateTime, $newEndDateTime, $bookingID);
            if (!$updateBooking->execute()) {
                $conn->rollback();
                throw new Exception("Executing updating query for new dates failed. Error: " .  $updateBooking->error);
            }


            // Insert notification
            $receiver = getMessageReceiver($userRoleID);
            $message = "<strong>$bookingCode</strong> <br>Your booking has been successfully rescheduled. Check your updated details.";
            $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, senderID, receiverID, message, receiver) VALUES(?, ?, ?, ?, ?)");
            $insertNotification->bind_param("iiiss", $bookingID, $userID, $userRoleID, $message, $receiver);
            if (!$insertNotification->execute()) {
                $conn->rollback();
                throw new Exception("Failed to insert notification");
            }

            $conn->commit();
            header("Location: ../../../../Pages/Admin/viewBooking.php?action=reschedSuccess");
            exit();
        } else {
            $conn->begin_transaction();

            $searchNewDates = $conn->prepare("SELECT serviceUnavailableID FROM serviceunavailabledate WHERE resortServiceID = ? AND partnershipServiceID = ? AND unavailableEndDate > ? AND unavailableStartDate < ? AND status != 'cancelled' AND bookingID != ? ");
            foreach ($serviceIDs as $category => $ids) {
                foreach ($ids as $serviceID) {
                    if (strtolower($category) === 'resort') {
                        $resortServiceID = $serviceID;
                        $partnershipServiceID = null;
                    } else {
                        $resortServiceID = null;
                        $partnershipServiceID = $serviceID;
                    }

                    $searchNewDates->bind_param("iiiss", $bookingID, $resortServiceID, $partnershipServiceID, $newStartDateTime, $newEndDateTime);
                    if (!$searchNewDates->execute()) {
                        throw new Exception("Executing search for new dates failed. Error: " . $searchNewDates->error);
                    }

                    $result = $searchNewDates->get_result();

                    if ($result->num_rows > 0) {
                        $_SESSION['bookingID'] = $bookingID;
                        header("Location: ../../../../Pages/Admin/viewBooking.php?action=someServiceNotAvailable");
                        exit();
                    }
                }
            }

            $insertUnavailable = $conn->prepare("INSERT INTO serviceunavailabledate (bookingID, resortServiceID, partnershipServiceID, unavailableStartDate, unavailableEndDate, status) VALUES (?, ?, ?, ?, ?, 'hold')");

            foreach ($serviceIDs as $category => $ids) {
                foreach ($ids as $serviceID) {
                    if (strtolower($category) === 'resort') {
                        $resortServiceID = $serviceID;
                        $partnershipServiceID = null;
                    } else {
                        $resortServiceID = null;
                        $partnershipServiceID = $serviceID;
                    }

                    $insertUnavailable->bind_param("iiiss", $bookingID, $resortServiceID, $partnershipServiceID, $newStartDateTime, $newEndDateTime);
                    if (!$insertUnavailable->execute()) {
                        $conn->rollback();
                        throw new Exception("Failed to insert new unavailable date. Error: " . $insertUnavailable->error);
                    }
                }
            }


            // Insert notification
            $receiver = getMessageReceiver($userRoleID);
            $message = "<strong>$bookingCode</strong> <br>Your booking has been successfully rescheduled. Check your updated details.";
            $insertNotification = $conn->prepare("INSERT INTO notification(bookingID, senderID, receiverID, message, receiver) VALUES(?, ?, ?, ?, ?)");
            $insertNotification->bind_param("iiiss", $bookingID, $userID, $userRoleID, $message, $receiver);
            if (!$insertNotification->execute()) {
                $conn->rollback();
                throw new Exception("Failed to insert notification");
            }

            $conn->commit();
            $_SESSION['bookingID'] = $bookingID;
            header("Location: ../../../../Pages/Admin/viewBooking.php?action=reschedNotAllowed");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['bookingID'] = $bookingID;
        error_log("Error: " . $e->getMessage());
        header("Location: ../../../../Pages/Admin/viewBooking.php?action=reschedError");
        exit();
    }
}
