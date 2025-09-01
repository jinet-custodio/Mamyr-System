<?php

require '../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');
session_start();
$env = parse_ini_file(__DIR__ . '/../.env');
require '../vendor/autoload.php';
require_once 'emailSenderFunction.php';
require_once 'functions.php';


if (isset($_POST['verify-btn'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $action = mysqli_real_escape_string($conn, $_SESSION['action']);
    $enteredOTP = mysqli_real_escape_string($conn, $_POST['pin1']) .
        mysqli_real_escape_string($conn, $_POST['pin2']) .
        mysqli_real_escape_string($conn, $_POST['pin3']) .
        mysqli_real_escape_string($conn, $_POST['pin4']) .
        mysqli_real_escape_string($conn, $_POST['pin5']) .
        mysqli_real_escape_string($conn, $_POST['pin6']);

    $getStoredOTP = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $getStoredOTP->bind_param("s", $email);
    $getStoredOTP->execute();
    $storedOTPResult = $getStoredOTP->get_result();
    if ($storedOTPResult->num_rows > 0) {
        $data = $storedOTPResult->fetch_assoc();

        $storedOTP = $data['userOTP'];
        $userStatus = intval($data['userStatusID']);
        $stored_expiration = $data['OTP_expiration_at'];
        $storedUserID = intval($data['userID']);
        $time_now = date('Y-m-d H:i:s');
        if (!empty($storedOTP)) {
            if ($storedOTP === $enteredOTP) {
                $userStat = 2; //Verified
                if ($stored_expiration > $time_now) {
                    if (strtotime($stored_expiration) > strtotime($time_now)) {
                        if ($action === 'Register') {
                            $changeStatus = $conn->prepare("UPDATE users SET userStatusID = ?, userOTP = NULL, OTP_expiration_at = NULL WHERE email = ?");
                            $changeStatus->bind_param("is", $userStat, $email);
                            if ($changeStatus->execute()) {
                                header("Location: ../Pages/register.php?action=successVerification");
                                exit;
                            } else {
                                error_log('Error Updating Status' . $changeStatus->error);
                            }
                            $changeStatus->close();
                        } elseif ($action === 'Partner') {
                            // Get partner data from session
                            $partnerData = $_SESSION['partnerData'] ?? null;
                            $companyName = mysqli_real_escape_string($conn, $partnerData['companyName']);
                            $partnerType = intval($_POST['partnerType']);
                            $partnerAddress = mysqli_real_escape_string($conn, $partnerData['partnerAddress']);
                            $partnerProofLink = mysqli_real_escape_string($conn, $partnerData['proofLink']);
                            $partnerPhoneNumber = mysqli_real_escape_string($conn, $partnerData['phoneNumber']);

                            if (!$partnerData) {
                                $_SESSION['error'] = "Partner information is missing. Please restart the registration process.";
                                header("Location: ../Pages/register.php");
                                exit;
                            }

                            $conn->begin_transaction();
                            try {
                                // Update the user phonenumber
                                $updateUser = $conn->prepare("UPDATE users SET phoneNumber = ? WHERE userID = ?");
                                $updateUser->bind_param("si",  $partnerPhoneNumber, $storedUserID);

                                if (!$updateUser->execute()) {
                                    throw new Exception("Failed to update user");
                                }

                                // Insert into partnerships table
                                $insertPartner = $conn->prepare("INSERT INTO partnerships(userID, partnerAddress, companyName, partnerTypeID, businessEmail, documentLink)
                                                    VALUES (?,?,?,?,?,?)");
                                $insertPartner->bind_param("ississ", $storedUserID, $partnerAddress, $companyName, $partnerType, $email, $partnerProofLink);


                                if (!$insertPartner->execute()) {
                                    throw new Exception("Failed to insert partnership data");
                                }

                                // Update user status and reset the otps
                                $partnershipID = $conn->insert_id;
                                $changeStatus = $conn->prepare("UPDATE users SET userStatusID = ?, userOTP = NULL, OTP_expiration_at = NULL WHERE email = ?");
                                $changeStatus->bind_param("is", $userStat, $email);
                                if (!$changeStatus->execute()) {
                                    throw new Exception("Failed to change the status");
                                }

                                // Insert notification
                                $receiver = "Customer";
                                $message = "Your request has been submitted and is currently awaiting admin approval. We’ll notify you once your request has been reviewed.";
                                $insertNotification = $conn->prepare("INSERT INTO notifications(partnershipID, userID, message, receiver) VALUES(?, ?, ?, ?)");
                                $insertNotification->bind_param("iiss", $partnershipID, $storedUserID, $message, $receiver);
                                if (!$insertNotification->execute()) {
                                    throw new Exception("Failed to insert notification");
                                }

                                $conn->commit();

                                unset($_SESSION['partnerData']);
                                $_SESSION['success'] = "Partner has been successfully registered and verified.";
                                header("Location: ../Pages/register.php");
                                exit;
                            } catch (Exception $e) {
                                $conn->rollback();
                                error_log("Partner Registration Error: " . $e->getMessage());
                                $_SESSION['error'] = "An error occurred during partner registration. Please try again.";
                                header("Location: ../Pages/register.php");
                                exit;
                            } finally {
                                $updateUser->close();
                                $insertPartner->close();
                                $changeStatus->close();
                                $insertNotification->close();
                            }
                        } elseif ($action === 'forgot-password') {
                            $_SESSION['email'] = $email;
                            header("Location: ../Pages/forgotPassword.php");
                            exit;
                        } else {
                            $_SESSION['error'] = "Unrecognized action during verification. Please try again.";
                            header("Location: ../Pages/verify_email.php");
                            exit;
                        }
                    } else {
                        // $_SESSION['error'] = "Invalid OTP.";
                        header("Location: ../Pages/verify_email.php?action=invalidOTP");
                        exit;
                    }
                } else {
                    // $_SESSION['error'] = "Expired OTP.";
                    header("Location: ../Pages/verify_email.php?action=expiredOTP");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Invalid OTP. Please try again.";
                header("Location: ../Pages/verify_email.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "OTP is required. Please enter the code sent to your email.";
            header("Location: ../Pages/verify_email.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "The email address provided does not exist. Please try again!";
        header("Location: ../Pages/verify_email.php");
        exit;
    }
}




if (isset($_POST['resend_code'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $newOtp = generateOTP(6);
    date_default_timezone_set('Asia/Manila');
    $new_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $storedData = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $storedData->bind_param("s", $email);
    $storedData->execute();
    $storedDataResult = $storedData->get_result();
    if ($storedDataResult->num_rows > 0) {
        $data = $storedDataResult->fetch_assoc();

        $stored_expiration = $data['OTP_expiration_at'];
        $storedFirstName = $data['firstName'];
        $storedStatus = $data['userStatusID'];
        $storedOTP = $data['userOTP'];
        $time_now = date('Y-m-d H:i:s');
        $time_left = strtotime($stored_expiration) - strtotime($time_now);
        if ($time_left > 0) {
            $minutes_left = ceil($time_left / 60);
            $_SESSION['time'] = "Wait for " . $minutes_left . " more minute(s) to request again.";
            header("Location: ../Pages/verify_email.php");
            exit;
        } else {

            try {
                $updateOTP = $conn->prepare("UPDATE users SET userOTP = ?, OTP_expiration_at = ? WHERE  email = ?");
                $updateOTP->bind_param("sss", $newOtp, $new_time, $email);
                if ($updateOTP->execute()) {
                    $message = '<body style="font-family: Arial, sans-serif;                background-color: #f4f4f4; padding: 20px; margin: 0;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0"
        style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 30px; text-align: left; color: #333333;">
                <h2 style="color: #333333; margin-top: 0;">Your New One-Time Password (OTP) Code</h2>
                <p style="font-size: 16px; margin: 20px 0 10px;">Hello,</p>


                <div style="text-align: center; margin: 30px 0;">
                    <span
                        style="display: inline-block; background-color: #365CCE; color: #ffff; font-size: 24px; padding: 15px 30px; border-radius: 6px; font-weight: bold;">
                        ' . $newOtp . '
                    </span>
                </div>
                <p style="font-size: 16px; margin: 10px 0;">This OTP is valid for <strong>5 minutes</strong>. Do not
                    share it with anyone.</p>
                <p style="font-size: 16px; margin: 10px 0;">If you did not request this code, please ignore this email.
                </p>
                <br>
                <p style="font-size: 16px;">Thank you,</p>
                <p style="font-size: 16px; font-weight: bold;">Mamyr.</p>
            </td>
        </tr>
    </table>
</body>
                                ';
                    $subject = 'Here\'s Your New OTP Code from Mamyr Resort and Events Place';

                    if (sendEmail($email, $storedFirstName, $subject, $message, $env)) {
                        $_SESSION['email'] = $email;
                        header("Location: ../Pages/verify_email.php");
                        exit;
                    } else {
                        $_SESSION['OTP'] = 'Failed to send OTP. Try again.';
                        header("Location: ../Pages/verify_email.php");
                        exit;
                    }
                } else {
                    error_log("Error: " . $updateOTP->error);
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
            } finally {
                $updateOTP->close();
            }
        }
    } else {
        $_SESSION['error'] = 'User Not Found';
        header('Location: ../Pages/verify_email.php');
    }
}
