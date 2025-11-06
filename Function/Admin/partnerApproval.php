<?php

require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');
session_start();

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

$userID = (int)$_SESSION['userID'];
$userRole = (int) $_SESSION['userRole'];
//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $_SESSION['partnerID'] = (int) $_POST['partnerID'];
    $partnerStatusID = intval($_POST['partnerStatus']);
    $partnerUserID = intval($_POST['partnerUserID']);
    $startDate = date('Y-m-d H:i:s');

    $partnerID = $_SESSION['partnerID'];

    $partnerTypes = isset($_POST['partnerTypes']) ? array_map('trim', $_POST['partnerTypes']) : [];

    if (empty($partnerTypes)) {
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=emptyPartnerTypes');
        exit();
    }
    $conn->begin_transaction();
    try {
        $findPartnerQuery = $conn->prepare("SELECT * FROM partnership
    WHERE partnershipID = ? AND partnerStatusID = ?");
        $findPartnerQuery->bind_param("ii", $partnerID, $partnerStatusID);

        if (!$findPartnerQuery->execute()) {
            throw new Exception("Error: " . $findPartnerQuery->error);
        }
        $result = $findPartnerQuery->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['partnerID'] =  $partnerID;
            header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed');
            exit();
        }

        //Approved
        $updatePartnerTypeTrue = $conn->prepare("UPDATE `partnership_partnertype` SET `isApproved`= ? WHERE partnershipID = ? AND partnerTypeID = ?");
        foreach ($partnerTypes as $id) {
            $isApproved = true;
            $updatePartnerTypeTrue->bind_param("iii", $isApproved, $partnerID, $id);

            if (!$updatePartnerTypeTrue->execute()) {
                $conn->rollback();
                throw new Exception("Updating partner type failed: " . $updatePartnerTypeTrue->error);
            }
        }

        //Not Approved
        $updatePartnerTypeFalse = $conn->prepare("UPDATE `partnership_partnertype` SET `isApproved`= ? WHERE partnershipID = ? AND partnerTypeID != ?");
        foreach ($partnerTypes as $id) {
            $isApproved = false;
            $updatePartnerTypeFalse->bind_param("iii", $isApproved, $partnerID, $id);

            if (!$updatePartnerTypeFalse->execute()) {
                $conn->rollback();
                throw new Exception("Updating partner type failed: " . $updatePartnerTypeFalse->error);
            }
        }

        $approvedPartnerID = 2;
        $updatePartnerStatus = $conn->prepare("UPDATE partnership 
                        SET partnerStatusID = ?, startDate = ?
                        WHERE partnershipID = ?");
        $updatePartnerStatus->bind_param("isi", $approvedPartnerID, $startDate, $partnerID);
        if (!$updatePartnerStatus->execute()) {
            $conn->rollback();
            throw new Exception("Updating partner approval status: " . $updatePartnerStatus->error);
        }

        $partnerRoleID = 2;
        $updateRole = $conn->prepare("UPDATE user
        SET userRole = ?
        WHERE userID = ?");
        $updateRole->bind_param("ii", $partnerRoleID, $partnerUserID);

        if (!$updateRole->execute()) {
            $conn->rollback();
            throw new Exception("Updating partner role failed: " .  $updateRole->error);
        }
        $receiver = getMessageReceiver($partnerRoleID);
        $message = 'Your request for a business partner has been reviewed and approved. You can now proceed to add your services: <a href="../Account/bpServices.php">Click here.</a>';
        $insertNotification = $conn->prepare("INSERT INTO `notification`(`partnershipID`, `receiverID`, `senderID`, `message`, `receiver`) VALUES (?,?,?,?,?)");
        $insertNotification->bind_param('iiiss', $partnerID, $partnerUserID, $userID,  $message, $receiver);

        if (!$insertNotification->execute()) {
            $conn->rollback();
            throw new Exception("Failed inserting notification: " .  $insertNotification->error);
        }
        $conn->commit();
        unset($_SESSION['partnerID']);
        header('Location: ../../Pages/Admin/displayPartnership.php?action=approved');
    } catch (Exception $e) {
        $_SESSION['partnerID'] =  $partnerID;
        error_log('Error: ' . $e->getMessage());
        // $_SESSION['error-partnership'] = 'The request could not be approved. Please try again later.';
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed1');
        exit();
    }
}



//Decline Button is Click
if (isset($_POST['declineBtn'])) {
    $_SESSION['partnerID'] = intval($_POST['partnerID']);
    $partnerStatusID = intval($_POST['partnerStatus']);
    $rejectionReason = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $partnerUserID = intval($_POST['partnerUserID']);
    $partnerID = $_SESSION['partnerID'];


    $conn->begin_transaction();
    try {
        $findPartnerQuery = $conn->prepare("SELECT * FROM partnership 
            WHERE partnershipID = ? AND partnerStatusID = ?");
        $findPartnerQuery->bind_param("ii", $partnerID, $partnerStatusID);
        if (!$findPartnerQuery->execute()) {
            throw new Exception("Error: " . $findPartnerQuery->error);
        }

        $result = $findPartnerQuery->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['partnerID'] =  $partnerID;
            header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed');
            exit();
        }

        //Not Approved
        $updatePartnerTypeFalse = $conn->prepare("UPDATE `partnership_partnertype` SET `isApproved`= ? WHERE partnershipID = ?");
        $isApproved = false;
        $updatePartnerTypeFalse->bind_param("ii", $isApproved, $partnerID);

        if (!$updatePartnerTypeFalse->execute()) {
            $conn->rollback();
            throw new Exception("Updating partner type failed: " . $updatePartnerTypeFalse->error);
        }
        $rejectedStatusID = 3;
        $updatePartnerStatus = $conn->prepare("UPDATE partnership SET partnerStatusID = ? WHERE partnershipID = ?");
        $updatePartnerStatus->bind_param("ii", $rejectedStatusID, $partnerID);
        if (!$updatePartnerStatus->execute()) {
            $conn->rollback();
            throw new Exception("Updating partner rejected status: " . $updatePartnerStatus->error);
        }


        if ($rejectionReason !== "") {
            $receiver = 'Partner Applicant';
            $message = $rejectionReason;
            $bookingID = Null;
        } else {
            // TODO: Please change the message! Gpt be
            $receiver =  'Partner Applicant';
            $message = "Thank you for reaching out and considering our venue for your project. We’re currently being selective with partnerships to ensure alignment with our brand and guest experience. At this time, we won’t be moving forward with this opportunity, but we truly appreciate your interest and wish you all the best.";
            $bookingID = Null;
        }

        $insertNotif = $conn->prepare("INSERT INTO notification(partnershipID, senderID, receiverID, message, bookingID, receiver)
        VALUES(?,?,?,?,?,?)");
        $insertNotif->bind_param("iiisis", $partnerID,  $partnerUserID, $userID, $message, $bookingID, $receiver);

        if (!$insertNotif->execute()) {
            $conn->rollback();
            throw new Exception("Failed inserting notification: " .  $insertNotif->error);
        }
        $conn->commit();
        unset($_SESSION['partnerID']);
        header('Location: ../../Pages/Admin/displayPartnership.php?action=rejected');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['partnerID'] = $partnerID;
        error_log("Error: " . $e->getMessage());
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed2');
        exit();
    }
}
