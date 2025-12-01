<?php

require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');
session_start();

require_once '../emailSenderFunction.php';

$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';

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
    // error_log(print_r($_POST, true));
    $_SESSION['partnerID'] = (int) $_POST['partnerID'];
    $partnerStatusID = intval($_POST['partnerStatus']);
    $partnerUserID = intval($_POST['partnerUserID']);
    $startDate = date('Y-m-d H:i:s');
    $businessEmail = mysqli_real_escape_string($conn, $_POST['businessEmail']);
    $partnerName = mysqli_real_escape_string($conn, $_POST['applicantName']);

    $partnerID = $_SESSION['partnerID'];

    $partnerTypes = isset($_POST['partnerTypes']) ?  $_POST['partnerTypes'] : [];
    // error_log(print_r($partnerTypes, true));
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
        $updatePartnerTypeTrue = $conn->prepare("UPDATE `partnership_partnertype` SET `isApproved`= ? WHERE partnershipID = ? AND pptID = ? AND partnerTypeID = ?");
        foreach ($partnerTypes as $id => $type) {
            foreach ($type as $pptID => $name) {
                $isApproved = true;
                // error_log("PPTID: " . $pptID . "ID: " . $id);
                $updatePartnerTypeTrue->bind_param("iiii", $isApproved, $partnerID, $pptID, $id);
                if (!$updatePartnerTypeTrue->execute()) {
                    $conn->rollback();
                    throw new Exception("Updating partner type failed: " . $updatePartnerTypeTrue->error);
                }
            }
        }

        //Not Approved
        // $updatePartnerTypeFalse = $conn->prepare("UPDATE `partnership_partnertype` SET `isApproved`= ? WHERE partnershipID = ? AND pptID != ? AND partnerTypeID != ?");
        // foreach ($partnerTypes as $id => $type) {
        //     foreach ($type as $pptID) {
        //         $isApproved = false;
        //         $updatePartnerTypeFalse->bind_param("iiii", $isApproved, $partnerID, $pptID, $id);
        //         error_log("PPTID: " . $pptID . "ID: " . $id);
        //         if (!$updatePartnerTypeFalse->execute()) {
        //             $conn->rollback();
        //             throw new Exception("Updating partner type failed: " . $updatePartnerTypeFalse->error);
        //         }
        //     }
        // }

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

        $subject = 'Your Business Partnership Request Has Been Approved';
        $email_message = '<body style="font-family: Arial, sans-serif;         background-color: #f4f4f4; padding: 20px; margin: 0;">

                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <tr style="background-color:#365CCE;">
                                    <td style="text-align:center; ">
                                        <h4 style="font-family:Poppins Light; color:#ffffff; font-size: 18px;  margin-top: 25px">You’re Officially a Mamyr Resort Partner!</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: 10px 0 10px;">Hello, Partner</p>
                                        <p style="font-size: 12px; margin: 8px 0;">Your business partnership request with Mamyr Resort and Events Place has been approved!
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;">You now have full access to integrate your services directly into our event form, allowing customers to select and use your offerings seamlessly when planning their events. 
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;">We’re excited to have you on board and look forward to seeing your services available to our users. 
                                        </p>
                                        <br>
                                        <p style="font-size: 14px;">Warm regards,</p>
                                        <p style="font-size: 14px; font-weight: bold;">Mamyr Resort and Events Place.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
        ';

        if (!sendEmail($businessEmail, $partnerName, $subject, $email_message, $env)) {
            header('Location: ../../Pages/Admin/partnership.php?container=4&action=emailFailed');
            exit();
        }

        $conn->commit();
        unset($_SESSION['partnerID']);
        header('Location: ../../Pages/Admin/displayPartnership.php?action=approved');
    } catch (Exception $e) {
        $_SESSION['partnerID'] =  $partnerID;
        error_log('Error: ' . $e->getMessage());
        // $_SESSION['error-partnership'] = 'The request could not be approved. Please try again later.';
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=approvalFailed');
        exit();
    }
}



//Decline Button is Click
if (isset($_POST['rejectApplicant'])) {
    $_SESSION['partnerID'] = intval($_POST['partnerID']);
    $partnerStatusID = intval($_POST['partnerStatus']);
    $otherReason = mysqli_real_escape_string($conn, $_POST['rejection-entered-reason']);
    $reasonID = (int) $_POST['rejection-reason'];
    $partnerUserID = intval($_POST['partnerUserID']);
    $partnerID = $_SESSION['partnerID'];

    if (empty($reasonID)) {
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=noReason');
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


        if ($otherReason !== "") {
            $receiver = 'Partner Applicant';
            $message = $otherReason;
            $bookingID = Null;
        } else if (!empty($reasonID)) {
            $receiver = 'Partner Applicant';
            $getMessage = $conn->prepare("SELECT `reasonDescription` FROM `reason` WHERE reasonID = ?");
            $getMessage->bind_param('i', $reasonID);
            if (!$getMessage->execute()) {
                $conn->rollback();
                throw new Exception('Failed selecting the message for reasonID:' . $reasonID);
            }

            $result = $getMessage->get_result();

            if ($result->num_rows === 0) {
                $message = 'We couldn’t verify your applicant request at this time. Please contact the resort administrator for assistance.';
            }

            $row = $result->fetch_assoc();

            $message = $row['reasonDescription'];
        } else {
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
        header('Location: ../../Pages/Admin/displayPartnership.php?container=2&action=rejected');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['partnerID'] = $partnerID;
        error_log("Error: " . $e->getMessage());
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=rejectionFailed');
        exit();
    }
}
