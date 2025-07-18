<?php

require '../Config/dbcon.php';
session_start();
$env = parse_ini_file(__DIR__ . '/../.env');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;


// require '../phpmailer/src/PHPMailer.php';
// require '../phpmailer/src/Exception.php';
// require '../phpmailer/src/SMTP.php';


if (isset($_POST['verify-btn'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $action = mysqli_real_escape_string($conn, $_SESSION['action']);
    echo $action;
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
        if ($data) {
            $storedOTP = $data['userOTP'];
            $userStatus = $data['userStatusID'];
            $stored_expiration = $data['OTP_expiration_at'];
            date_default_timezone_set('Asia/Manila');
            $time_now = date('Y-m-d H:i:s');
            if (!empty($storedOTP)) {
                $userStat = 2;
                if ($stored_expiration > $time_now) {
                    if ($storedOTP == $enteredOTP) {
                        if ($action === 'register') {
                            $userOTP = NULL;
                            $otpExpirationDate = NULL;
                            $changeStatus = $conn->prepare("UPDATE users SET userStatusID = ?, userOTP = ?, OTP_expiration_at = ? WHERE email = ?");
                            $changeStatus->bind_param("isss", $userStat, $userOTP, $otpExpirationDate, $email);
                            if ($changeStatus->execute()) {
                                // $_SESSION['success'] = "Verified successfully!";
                                header("Location: ../Pages/register.php?action=successVerification");
                                exit;
                            }
                        } elseif ($action === 'partner') {
                            // Fetch the user ID
                            $getUserID = $conn->prepare("SELECT userID FROM users WHERE email = ?");
                            $getUserID->bind_param("s", $email);
                            $getUserID->execute();
                            $userIDResult = $getUserID->get_result();

                            if ($userIDResult->num_rows > 0) {
                                $userData = $userIDResult->fetch_assoc();
                                $storedUserID = $userData['userID'];

                                // Get partner data from session
                                $partnerData = $_SESSION['partnerData'];
                                $companyName = mysqli_real_escape_string($conn, $partnerData['companyName']);
                                $partnerType = mysqli_real_escape_string($conn, $partnerData['partnerType']);
                                $partnerAddress = mysqli_real_escape_string($conn, $partnerData['partnerAddress']);
                                $partnerProofLink = mysqli_real_escape_string($conn, $partnerData['proofLink']);
                                $partnerPhoneNumber = mysqli_real_escape_string($conn, $partnerData['phoneNumber']);
                                $partnerData = $_SESSION['partnerData'] ?? null;
                                if (!$partnerData) {
                                    $_SESSION['error'] = "Partner information missing from session.";
                                    header("Location: ../Pages/register.php");
                                    exit;
                                } else {
                                    $updateUser = $conn->prepare("UPDATE users SET  phoneNumber = ? WHERE userID = ?");
                                    $updateUser->bind_param("ii",  $partnerPhoneNumber, $storedUserID);
                                    $updateUser->execute();
                                    $updateUser->close();

                                    //Select partnerType ID
                                    $partnerTypes = $conn->prepare("SELECT * FROM partnershipTypes WHERE partnerType = ?");
                                    $partnerTypes->bind_param("s", $partnerType);
                                    $partnerTypes->execute();
                                    $partnerTypeResult = $partnerTypes->get_result();
                                    if ($partnerTypeResult->num_rows > 0) {
                                        $data = $partnerTypeResult->fetch_assoc();
                                        $partnerTypeID = $data['partnerTypeID'];
                                    }

                                    // Insert into partnerships table
                                    $insertPartner = $conn->prepare("INSERT INTO partnerships(userID, partnerAddress, companyName, partnerTypeID, businessEmail, documentLink)
                                        VALUES (?,?,?,?,?,?)");
                                    $insertPartner->bind_param("ississ", $storedUserID, $partnerAddress, $companyName, $partnerTypeID, $email, $partnerProofLink);


                                    // Cleanup
                                    unset($_SESSION['partnerData']);
                                    if ($insertPartner->execute()) {
                                        $userOTP = NULL;
                                        $otpExpirationDate = NULL;
                                        $changeStatus = $conn->prepare("UPDATE users SET userStatusID = ?, userOTP = ?, OTP_expiration_at = ? WHERE email = ?");
                                        $changeStatus->bind_param("isss", $userStat, $userOTP, $otpExpirationDate, $email);
                                        if ($changeStatus->execute()) {
                                            $_SESSION['success'] = "Partner registered and verified successfully!";
                                            header("Location: ../Pages/register.php");
                                            exit;
                                        }
                                    }
                                }
                            } else {
                                $_SESSION['error'] = "User not found after verification.";
                                header("Location: ../Pages/verify_email.php");
                                exit;
                            }
                        } elseif ($action === 'forgot-password') {
                            $_SESSION['success'] = "Email Verification Success!";
                            header("Location: ../Pages/forgotPassword.php");
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
            }
        } else {
            $_SESSION['error'] = "Invalid request.";
            header("Location: ../Pages/verify_email.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Email doesn`t exist";
        header("Location: ../Pages/verify_email.php");
        exit;
    }
}



if (isset($_POST['resend_code'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $newOtp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    date_default_timezone_set('Asia/Manila');
    $new_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $storedData = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $storedData->bind_param("s", $email);
    $storedData->execute();
    $storedDataResult = $storedData->get_result();
    if ($storedDataResult->num_rows > 0) {
        $data = $storedDataResult->fetch_assoc();
        if ($data) {
            $stored_expiration = $data['OTP_expiration_at'];
            $storedFirstName = $data['firstName'];
            $storedStatus = $data['userStatusID'];
            $storedOTP = $data['userOTP'];
            date_default_timezone_set('Asia/Manila');
            $time_now = date('Y-m-d H:i:s');
            $time_left = strtotime($stored_expiration) - strtotime($time_now);
            if ($time_left < 300) {
                $minutes_left = ceil($time_left / 60);
                $_SESSION['time'] = "Wait for " . $minutes_left . " more minute(s) to request again.";
                header("Location: ../Pages/verify_email.php");
                exit;
            } else {
                $updateOTP = $conn->prepare("UPDATE users SET userOTP = ?, OTP_expiration_at = ? WHERE  email = ?");
                $updateOTP->bind_param("sss", $newOtp, $new_time, $email);
                if ($updateOTP->execute()) {
                    $mail = new PHPmailer(true);
                    try {
                        $_SESSION['email'] = $email;
                        $mail->isSMTP();
                        $mail->Host       =  $env['SMTP_HOST'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $env['SMTP_USER'];
                        $mail->Password   =  $env['SMTP_PASS'];
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       =  $env['SMTP_PORT'];


                        $mail->setFrom($env['SMTP_USER'], 'Mamyr Resort and Event Place');
                        $mail->addAddress($email, $storedFirstName);

                        $message = '<body style="font-family: Arial, sans-serif;                  background-color: #f4f4f4; padding: 20px; margin: 0;">
                                <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                                    <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <h2 style="color: #333333; margin-top: 0;">Your New One-Time Password (OTP) Code</h2>
                                        <p style="font-size: 16px; margin: 20px 0 10px;">Hello,</p>


                                        <div style="text-align: center; margin: 30px 0;">
                                        <span style="display: inline-block; background-color: #00e1ff; color: #0c0605; font-size: 24px; padding: 15px 30px; border-radius: 6px; font-weight: bold;">
                                           ' . $newOtp . '
                                        </span>
                                        </div>
                                        <p style="font-size: 16px; margin: 10px 0;">This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
                                        <p style="font-size: 16px; margin: 10px 0;">If you did not request this code, please ignore this email.</p>
                                        <br>
                                        <p style="font-size: 16px;">Thank you,</p>
                                        <p style="font-size: 16px; font-weight: bold;">Mamyr.</p>
                                    </td>
                                    </tr>
                                </table>
                                </body>
                                ';

                        $mail->isHTML(true);
                        $mail->Subject = 'Here’s Your New OTP Code from Mamyr';
                        $mail->Body    = $message;
                        // $mail->AltBody = 'Body in plain text for non-HTML mail clients';
                        if (!$mail->send()) {
                            $_SESSION['OTP'] = 'Failed to send OTP. Try again.';
                            header("Location: ../Pages/verify_email.php");
                            exit;
                        } else {
                            header("Location: ../Pages/verify_email.php");
                            exit;
                        }
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }
        }
    }
}
