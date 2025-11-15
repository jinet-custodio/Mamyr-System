<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];
$env = parse_ini_file(__DIR__ . '/../../../.env');
require __DIR__ . '/../../../vendor/autoload.php';
require '../../emailSenderFunction.php';

// $gcashDetails = '';
// $resortInfoName = 'gcashNumber';
// $getPaymentDetails = $conn->prepare("SELECT resortInfoDetail FROM resortinfo WHERE resortInfoName = ?");
// $getPaymentDetails->bind_param('s', $resortInfoName);
// $getPaymentDetails->execute();
// $result = $getPaymentDetails->get_result();

// if ($result->num_rows > 0) {
//     $row = $result->fetch_assoc();
//     $gcashDetails = 'Here is our gcash details where you can send the downpayment. <br> <strong>' . $row['resortInfoDetail'] . '</strong>';
// }

$email = 'jeanette.arkurus@gmail.com';


if (isset($_POST['submitDownpaymentImage'])) {
    // error_log(print_r($_POST, true));
    $bookingID = (int) $_POST['bookingID'];
    $confirmedBookingID = (int) $_POST['confirmedBookingID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $paymentAmount = (float) $_POST['payment-amount'];
    $downpayment = (float) $_POST['downpayment'];
    $finalBill = (float) $_POST['finalBill'];
    $customerName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $bookingCode = mysqli_real_escape_string($conn, $_POST['bookingCode']);

    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);


    $startDateObj = new DateTime($startDate);

    $bookingDate = $startDateObj->format('F d, Y g:i A');

    $serviceIDs = $_POST['serviceIDs'];

    $imageMaxSize = 5 * 1024 * 1024; // 5 MB max
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $storeProofPath = __DIR__ . '/../../../Assets/Images/PaymentProof/';
    $tempUploadPath = __DIR__ . '/../../../Assets/Images/TempUploads/';

    if (!is_dir($storeProofPath)) mkdir($storeProofPath, 0755, true);
    if (!is_dir($tempUploadPath)) mkdir($tempUploadPath, 0755, true);

    $_SESSION['bookingID'] = $bookingID;
    $_SESSION['payment-amount'] = $paymentAmount;
    $_SESSION['bookingType'] = $bookingType;

    if (isset($_FILES['downpaymentPic']) && is_uploaded_file($_FILES['downpaymentPic']['tmp_name'])) {

        $originalName = $_FILES['downpaymentPic']['name'];
        $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $imageSize = $_FILES['downpaymentPic']['size'];

        if (!in_array($imageExt, $allowedExt)) {
            unset($_SESSION['tempImage']);
            header("Location: ../../../Pages/Account/reservationSummary.php?action=extError");
            exit();
        }

        if ($imageSize > $imageMaxSize) {
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageSize");
            exit();
        }

        $tempFileName = 'temp_' . uniqid() . '_' . $bookingID . '.' . $imageExt;
        $tempFilePath = $tempUploadPath . $tempFileName;

        if (!move_uploaded_file($_FILES['downpaymentPic']['tmp_name'], $tempFilePath)) {
            header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
            exit();
        }

        $_SESSION['tempImage'] = $tempFileName;
    } else if (!empty($_SESSION['tempImage'])) {
        $tempFileName = $_SESSION['tempImage'];
    } else {
        header("Location: ../../../Pages/Account/reservationSummary.php?action=imageFailed");
        exit();
    }

    if ($paymentAmount < $downpayment) {
        $_SESSION['uploadError'] = 'Amount is less than required downpayment.';
        header('Location: ../../../Pages/Account/reservationSummary.php?action=lessAmount');
        exit();
    }

    $finalFileName = str_pad($bookingID, 4, '0', STR_PAD_LEFT) . '_' . basename($_SESSION['tempImage']);
    $finalFilePath = $storeProofPath . $finalFileName;

    rename($tempUploadPath . $_SESSION['tempImage'], $finalFilePath);


    $paymentSentID = 5;
    $userID = $_SESSION['userID'] ?? null;
    if (!$userID) {
        header("Location: ../../../Pages/Account/reservationSummary.php?action=noUser");
        exit();
    }

    $conn->begin_transaction();
    try {
        $expiresAt = null;
        $searchBookingID = $conn->prepare("SELECT bookingID FROM serviceunavailabledate WHERE bookingID = ? AND expiresAt IS NOT NULL");
        $searchBookingID->bind_param('i', $bookingID);
        if (!$searchBookingID->execute()) {
            $conn->rollback();
            throw new Exception("Failed executing (searchBookingID) booking ID: $bookingID");
        }

        $searchID = $searchBookingID->get_result();
        $hold = 'hold';
        $availableServices = [];
        if ($searchID->num_rows > 0) {
            $updateUnavailableDates = $conn->prepare("UPDATE `serviceunavailabledate` SET `expiresAt`= ? WHERE `bookingID`= ?");
            $updateUnavailableDates->bind_param('si', $expiresAt,  $bookingID);
            if (!$updateUnavailableDates->execute()) {
                $conn->rollback();
                throw new Exception("Failed to update unavailable date for booking ID: $bookingID");
            }
            $updateUnavailableDates->close();
        } else {
            foreach ($serviceIDs as $type => $ids) {
                $type = strtolower(trim($type));
                foreach ($ids as $id) {
                    $id = (int) $id;

                    if ($type === 'resort') {

                        $searchAmenity = $conn->prepare("SELECT RServiceName FROM resortamenity WHERE resortServiceID = ?");
                        $searchAmenity->bind_param('i', $id);

                        if (!$searchAmenity->execute()) {
                            $conn->rollback();
                            throw new Exception("Failed to search for resort service. Error: " . $searchAmenity->error);
                        }

                        $amenity = $searchAmenity->get_result();

                        if ($amenity->num_rows > 0) {
                            $row = $amenity->fetch_assoc();
                            $serviceName = $row['RServiceName'];

                            if (strpos($serviceName, 'Room') !== false) {
                                $getSameServiceName = $conn->prepare("SELECT resortServiceID  FROM resortamenity WHERE RServiceName = ? AND resortServiceID != ?");

                                $getSameServiceName->bind_param('si', $serviceName, $id);
                                $getSameServiceName->execute();
                                $getSameServiceResult = $getSameServiceName->get_result();

                                if ($getSameServiceResult->num_rows > 0) {
                                    $data = $getSameServiceResult->fetch_assoc();
                                    $resortServiceID = $data['resortServiceID'];

                                    $searchService = $conn->prepare("SELECT resortServiceID FROM serviceunavailabledate WHERE resortServiceID = ? AND `unavailableStartDate` <= ?  AND `unavailableEndDate` >= ?");
                                    $searchService->bind_param("iss", $resortServiceID, $endDate, $startDate);
                                    if (!$searchService->execute()) {
                                        $conn->rollback();
                                        throw new Exception("Failed to search for unavailable service. Error: " . $searchService->error);
                                    }

                                    $result = $searchService->get_result();
                                    if ($result->num_rows > 0) {
                                        header("Location: ../../../../../Pages/Account/reservationSummary.php?action=serviceUnavailable");
                                        exit();
                                    } else {
                                        $availableServices['resort'][] = $resortServiceID;
                                    }
                                }
                            }

                            $searchService = $conn->prepare("SELECT resortServiceID FROM serviceunavailabledate WHERE resortServiceID = ? AND `unavailableStartDate` <= ?  AND `unavailableEndDate` >= ?");
                            $searchService->bind_param("iss", $id, $endDate, $startDate);
                            if (!$searchService->execute()) {
                                $conn->rollback();
                                throw new Exception("Failed to search for unavailable service. Error: " . $searchService->error);
                            }

                            $result = $searchService->get_result();
                            if ($result->num_rows > 0) {
                                header("Location: ../../../../../Pages/Account/reservationSummary.php?action=serviceUnavailable");
                                exit();
                            } else {
                                $availableServices['resort'][] = $id;
                            }
                        }
                    } elseif ($type === 'partner') {
                        $searchService = $conn->prepare("SELECT partnershipServiceID FROM serviceunavailabledate WHERE partnershipServiceID = ? AND status = ? AND `unavailableStartDate` <= ?  AND `unavailableEndDate` >= ?");
                        $searchService->bind_param("isss", $id, $hold, $endDate, $startDate);
                        if (!$searchService->execute()) {
                            $conn->rollback();
                            throw new Exception("Failed to search for unavailable partner service. Error: " . $searchService->error);
                        }

                        $result = $searchService->get_result();
                        if ($result->num_rows > 0) {
                            header("Location: ../../../../../Pages/Account/reservationSummary.php?action=serviceUnavailable");
                            exit();
                        } else {
                            $availableServices['partner'][] = $id;
                        }
                        $searchService->close();
                    }
                }
            }
        }

        if (!empty($availableServices)) {
            foreach ($availableServices as $type => $ids) {
                foreach ($ids as $id) {
                    if ($type === 'resort') {
                        $sql = "INSERT INTO `serviceunavailabledate`(`bookingID`, `resortServiceID`, `unavailableStartDate`, `unavailableEndDate`, `expiresAt`) VALUES (?,?,?,?,?)";
                    } elseif ($type === 'partner') {
                        $sql = "INSERT INTO `serviceunavailabledate`(`bookingID`, `partnershipServiceID`, `unavailableStartDate`, `unavailableEndDate`, `expiresAt`) VALUES (?,?,?,?,?)";
                    }

                    $insertService = $conn->prepare($sql);
                    $insertService->bind_param('iisss', $bookingID, $id, $startDate, $endDate, $expiresAt);
                    if (!$insertService->execute()) {
                        $conn->rollback();
                        throw new Exception("Error inserting services into unavailable" . $insertService->error);
                    }
                }
            }
        }


        $downpaymentImageQuery = $conn->prepare("UPDATE confirmedbooking
                                                    SET downpaymentImage = ?, paymentStatus = ?
                                                    WHERE bookingID = ?
                                                ");
        $downpaymentImageQuery->bind_param("sii", $finalFileName, $paymentSentID, $bookingID);
        $downpaymentImageQuery->execute();

        $today = date('Y-m-d H:i:s');
        $insertPaymentQuery = $conn->prepare("INSERT INTO payment (amount, downpaymentImage, paymentDate, confirmedBookingID) VALUES (?, ?, ?, ?)");
        $insertPaymentQuery->bind_param('dssi', $paymentAmount, $finalFileName, $today, $confirmedBookingID);
        $insertPaymentQuery->execute();

        $receiver = 'Admin';
        $message = '<strong> #' . $bookingCode . '</strong><br>A payment proof has been uploaded for Booking ID: ' . $bookingID . '. Please verify if its exactly ₱' . number_format($paymentAmount, 2) . '. Click  <a href="transaction.php">here</a> to view';
        $insertNotificationQuery = $conn->prepare("INSERT INTO notification (receiver, senderID, bookingID, message) VALUES (?, ?, ?, ?) ");
        $insertNotificationQuery->bind_param('siis', $receiver, $userID, $bookingID, $message);
        $insertNotificationQuery->execute();

        $today = new DateTime();
        $dateCreated = $today->format("d M Y");
        $paymentDate = $today->format('F. d, Y g:i A');
        $paymentCheck = $today->modify('+24 hours')->format('F. d, Y g:i A');

        $email_message = '
                    <body style="font-family: Poppins, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
                        <table align="center" width="100%" cellpadding="0" cellspacing="0"
                            style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                            <!-- Header -->
                            <tr style="background-color: #365CCE;">
                                <td style="text-align:center; padding: 30px;">
                                    <h4
                                        style="font-family: Poppins, sans-serif;  font-weight: 700; font-size: 18px; color: #ffffff; font-size: 18px; margin: 0;">
                                        CUSTOMER PAYMENT!
                                    </h4>
                                    <h2
                                        style="font-family: Poppins, sans-serif; font-weight: 200; font-size: 16px;  color: #ffffff; margin: 10px 0 0;">
                                        A customer has submitted a payment for their booking
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

                                    <p style="font-size: 14px; margin: 20px 0 10px;">Hello <strong> Admin </strong>, </p>

                                    <p style="font-size: 14px; margin: 20px 0 10px;">A customer has successfully submitted a payment for their booking. Below are the details: </p>
                                    <p style="font-size: 14px; margin: 8px 0;">Booking ID: <strong>' . $bookingID . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Customer Name: <strong>' . $customerName . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Booking Date: <strong>' .  $bookingDate . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Grand Total: <strong>₱' . number_format($finalBill, 2) . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Required Downpayment: <strong>₱' . number_format($downpayment, 2) . '</strong></p>
                                    <p style="font-size: 14px; margin: 8px 0;">Total Payment Sent: <strong>₱' . number_format($paymentAmount, 2) . '</strong></p>

                                    <p style="font-size: 14px;">
                                        The customer has uploaded their payment receipt through the website. <br> 
                                        Kindly verify the payment and then approve or reject the booking in the admin payment page. 
                                        <a href="https://mintcream-parrot-792763.hostingersite.com/Pages/Admin/transaction.php" style="color: #007bff; text-decoration: none;">Click here.</a>
                                    </p>

                                    <p style="font-size: 16px; margin: 30px 0 0;">Regards,</p>
                                    <p style="font-size: 16px; font-weight: bold; margin: 8px 0 0;">Mamyr Resort and Events Place System Notification</p>
                                </td>
                            </tr>
                        </table>
                    </body>
            ';

        $subject = "New Payment Received – Booking Reference: $bookingCode";

        $isSend =  false;
        if (sendEmail($email, 'Mamyr Admin', $subject, $email_message, $env)) {
            $isSend = true;
        }

        if (!$isSend) {
            $conn->rollback();
            throw new Exception('Failed Sending Email');
        }

        $updateUnavailableService = $conn->prepare("UPDATE serviceunavailabledate SET expiresAt = NULL WHERE bookingID = ?");
        $updateUnavailableService->bind_param('i', $bookingID);
        $updateUnavailableService->execute();

        $conn->commit();
        unset($_SESSION['tempImage']);
        unset($_SESSION['payment-amount'], $_SESSION['bookingType'], $_SESSION['bookingID']);
        header("Location: ../../../Pages/Account/bookingHistory.php?action=paymentSuccess");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        header("Location: ../../../Pages/Account/reservationSummary.php?action=error");
        exit();
    }
}
