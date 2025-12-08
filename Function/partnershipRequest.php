<?php

require '../Config/dbcon.php';
session_start();

require_once 'Helpers/categoryFunctions.php';


if (isset($_POST['submit_request'])) {

    error_log(print_r($_POST, true));
    $_SESSION['partnerData'] = $_POST;
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userRole = mysqli_real_escape_string($conn, $_POST['userRole']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']) ?? NULL;
    $businessEmail = mysqli_real_escape_string($conn, $_POST['businessEmail']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
    $partnerTypes = $_POST['partnerType'] ?? [];
    $otherPartnerTypes = $_POST['other-partner-type'] ?? [];
    $streetAddress = mysqli_real_escape_string($conn, $_POST['streetAddress']) ?? '';
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']) ?? '';
    $city = mysqli_real_escape_string($conn, $_POST['city']) ?? '';
    $province = mysqli_real_escape_string($conn, $_POST['province']) ?? '';
    $zip = mysqli_real_escape_string($conn, $_POST['zip']) ?? '';
    if (!is_numeric($zip)) {
        header("Location: ../../../Pages/Customer/partnerApplication.php?result=zipCode");
        exit();
    }
    if ($streetAddress === '') {
        $partnerAddress = $barangay . ", " . $city . ", " . $province . ", " . $zip;
    } else {
        $partnerAddress = $streetAddress . " " . $barangay . ", " . $city . ", " . $province . ", " . $zip;
    }


    $imageMaxSize = 5 * 1024 * 1024; // 5 MB max
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $storeProofPath = __DIR__ . '/../../../Assets/Images/BusinessPartnerIDs/';
    $tempUploadPath = __DIR__ . '/../../../Assets/Images/TempUploads/';

    if (!is_dir($storeProofPath)) mkdir($storeProofPath, 0755, true);
    if (!is_dir($tempUploadPath)) mkdir($tempUploadPath, 0755, true);

    if (isset($_FILES['validID']) && is_uploaded_file($_FILES['validID']['tmp_name'])) {

        $originalName = $_FILES['validID']['name'];
        $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $imageSize = $_FILES['validID']['size'];

        if (!in_array($imageExt, $allowedExt)) {
            unset($_SESSION['imageName']);
            header("Location: ../../../Pages/Customer/partnerApplication.php?result=extError");
            exit();
        }

        if ($imageSize > $imageMaxSize) {
            header("Location: ../../../Pages/Customer/partnerApplication.php?result=imageSize");
            exit();
        }

        $tempFileName = 'temp_' . uniqid() . '.' . $imageExt;
        $tempFilePath = $tempUploadPath . $tempFileName;

        if (!move_uploaded_file($_FILES['validID']['tmp_name'], $tempFilePath)) {
            header("Location: ../../../Pages/Customer/partnerApplication.php?result=imageFailed");
            exit();
        }

        $_SESSION['imageName'] = $tempFileName;
    } else if (!empty($_SESSION['imageName'])) {
        $tempFileName = $_SESSION['imageName'];
    } else {
        header("Location: ../../../Pages/Customer/partnerApplication.php?result=imageFailed");
        exit();
    }

    $finalFileName = $firstName . '_' . basename($_SESSION['imageName']);
    $finalFilePath = $storeProofPath . $finalFileName;

    rename($tempUploadPath . $_SESSION['imageName'], $finalFilePath);



    $proofLink = mysqli_real_escape_string($conn, $_POST['proofLink']);

    $conn->begin_transaction();
    try {
        //Get the partner information based on the business Email
        $partnerQuery = $conn->prepare("SELECT * from partnership WHERE businessEmail = ? AND userID != ?");
        $partnerQuery->bind_param("si", $businessEmail, $userID);
        $partnerQuery->execute();
        $partnerResult = $partnerQuery->get_result();
        $_SESSION['partnerData'] = $_POST;
        if ($partnerResult->num_rows > 0) {
            header("Location: ../Pages/partnerApplication.php?result=emailExist");
            $partnerResult->close();
        } else {
            $userRoleID = 4; //Partner Request

            //Updating customer information
            $updateQuery = $conn->prepare("UPDATE user SET firstName = ?, middleInitial = ?, lastName = ?, phoneNumber = ?, userRole = ? WHERE userID = ?");
            $updateQuery->bind_param("ssssii", $firstName, $middleInitial, $lastName, $phoneNumber, $userRoleID, $userID);
            if (!$updateQuery->execute()) {
                // $_SESSION['success'] = "Profile updated successfully.";
                $conn->rollback();
                $_SESSION['message'] = "Failed to update profile.";
                throw new Exception('Error updating profile information. ' . $updateQuery->error);
            }

            if ($userRole ==  1) {
                $insertQuery = $conn->prepare("INSERT INTO partnership(userID, partnerAddress, companyName,  businessEmail, documentLink, validID) VALUES (?,?,?,?,?,?)");
                $insertQuery->bind_param("isssss", $userID, $partnerAddress, $companyName, $businessEmail, $proofLink, $finalFileName);
                if (!$insertQuery->execute()) {
                    $conn->rollback();
                    $_SESSION['message'] = 'Partnership Request Failed';
                    throw new Exception('Error sending partnership request. ' . $updateQuery->error);
                }

                $partnershipID = $conn->insert_id;

                //Get other id
                $partnerTypes = getPartnerType($conn);
                $otherID = 0;
                foreach ($partnerTypes as $id => $partner) {
                    $typeName = strtolower($partner['partnerType']);

                    if ($typeName === 'other') {
                        $otherID = (int) $partner['partnerTypeID'];
                    };
                }


                $insertPartnerTypes = $conn->prepare("INSERT INTO `partnership_partnertype`(`partnershipID`, `partnerTypeID`, `otherPartnerType`) VALUES (?,?,?)");
                if (!empty($partnerTypes)) {

                    foreach ($partnerTypes as $id):
                        $id = intval($id);

                        if ($id === $otherID) { //Means they select the other
                            if (!empty(array_filter($otherPartnerType))) { //Check if may value be or element sa array
                                $otherPartnerTypeFiltered = array_values(array_filter($otherPartnerType));
                                foreach ($otherPartnerTypeFiltered as $other) {
                                    $other = trim($other);
                                    $insertPartnerTypes->bind_param('iis', $partnershipID, $id, $other);
                                    if (!$insertPartnerTypes->execute()) {
                                        $conn->rollback();
                                        $_SESSION['message'] = 'Server problem. An error occured, try again later!';
                                        throw new Exception('Error inserting partnership types. ' . $insertPartnerTypes->error);
                                    }
                                }
                            } else {
                                $other = NULL;
                                $insertPartnerTypes->bind_param('iis', $partnershipID, $id, $other);
                                if (!$insertPartnerTypes->execute()) {
                                    $conn->rollback();
                                    $_SESSION['message'] = 'Server problem. An error occured, try again later!';
                                    throw new Exception('Error inserting partnership types. ' . $insertPartnerTypes->error);
                                }
                            }
                        }

                        if (!$insertPartnerTypes->execute()) {
                            $conn->rollback();
                            $_SESSION['message'] = 'Server problem. An error occured, try again later!';
                            throw new Exception('Error inserting partnership types. ' . $insertPartnerTypes->error);
                        }
                    endforeach;
                } else {
                    header("Location: ../Pages/Customer/partnerApplication.php?result=selectPartner");
                    exit;
                }

                // Insert notification
                $receiver = "Customer";
                $message = "Your request has been submitted and is currently awaiting admin approval. We’ll notify you once your request has been reviewed.";
                $insertNotification = $conn->prepare("INSERT INTO notification(partnershipID, receiverID, message, receiver) VALUES(?, ?, ?, ?)");
                $insertNotification->bind_param("iiss", $partnershipID, $storedUserID, $message, $receiver);
                if (!$insertNotification->execute()) {
                    $conn->rollback();
                    throw new Exception("Failed to insert notification");
                }

                $admin = "Admin";
                $requestNotif = "A new partnership application has been submitted and is currently awaiting your review and approval. Please review the application as soon as possible. Click <a href='displayPartnership.php?container=2'>here</a> to view the application";
                $insertAdminNotification = $conn->prepare("INSERT INTO notification(partnershipID, senderID, message, receiver) VALUES(?, ?, ?, ?)");
                $insertAdminNotification->bind_param("iiss", $partnershipID, $storedUserID, $requestNotif, $admin);
                if (!$insertNotification->execute()) {
                    $conn->rollback();
                    throw new Exception("Failed to insert admin notification");
                }

                $_SESSION['success'] = 'Partnership Request Sent Successfully';
                $conn->commit();
                header("Location: ../Pages/Customer/partnerApplication.php");
                exit;
            } elseif ($userTypeID == 2) {
                $_SESSION['message'] = 'You are already a partner of Mamyr Resort and Events Place. 
                You cannot file for another partnership.';
                header("Location: ../Pages/Customer/partnerApplication.php");
                exit;
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Error: ' . $e->getMessage());
        header("Location: ../Pages/Customer/partnerApplication.php");
        exit;
    }
}
