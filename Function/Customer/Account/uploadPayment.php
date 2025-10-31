<?php

require '../../../Config/dbcon.php';

session_start();

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


if (isset($_POST['submitDownpaymentImage'])) {
    error_log(print_r($_POST, true));
    $bookingID = (int) $_POST['bookingID'];
    $confirmedBookingID = (int) $_POST['confirmedBookingID'];
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $paymentAmount = (float) $_POST['payment-amount'];
    $downpayment = (float) $_POST['downpayment'];

    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);

    $serviceIDs = $_POST['serviceIDs'];

    $imageMaxSize = 5 * 1024 * 1024; // 5 MB max
    $allowedExt = ['jpg', 'jpeg', 'png'];

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
        // $today = new DateTime();
        // $expiresAt = $today->modify('+24 hours')->format('Y-m-d H:i:s');
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
        $message = 'A payment proof has been uploaded for Booking ID: ' . $bookingID . '. Please verify if its exactly ₱' . number_format($paymentAmount, 2);
        $insertNotificationQuery = $conn->prepare("INSERT INTO notification (receiver, senderID, bookingID, message) VALUES (?, ?, ?, ?) ");
        $insertNotificationQuery->bind_param('siis', $receiver, $userID, $bookingID, $message);
        $insertNotificationQuery->execute();

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
