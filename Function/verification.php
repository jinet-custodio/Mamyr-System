<?php

require '../Config/dbcon.php';
session_start();

$env = parse_ini_file(__DIR__ . '/../.env');

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;


require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/SMTP.php';


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

    $getOTP = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $getOTP);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        if ($data) {
            $storedOTP = $data['userOTP'];
            $userStatus = $data['userStatusID'];
            $stored_expiration = $data['OTP_expiration_at'];
            date_default_timezone_set('Asia/Manila');
            $time_now = date('Y-m-d H:i:s');
            if ($storedOTP !== "") {
                if ($stored_expiration > $time_now) {
                    if ($storedOTP == $enteredOTP) {
                        $changeStatus = "UPDATE users SET userStatusID = '2', userOTP = NULL, OTP_expiration_at = NULL WHERE email = '$email'";
                        $result = mysqli_query($conn, $changeStatus);
                        if ($result) {
                            if ($action === 'register') {
                                $_SESSION['success'] = "Verified successfully!";
                                header("Location: ../Pages/register.php");
                                exit;
                            } elseif ($action === 'partner') {
                                // Fetch the user ID
                                $getUserID = mysqli_query($conn, "SELECT userID FROM users WHERE email = '$email'");
                                if (mysqli_num_rows($getUserID) > 0) {
                                    $userData = mysqli_fetch_assoc($getUserID);
                                    $userID = $userData['userID'];

                                    // Get partner data from session
                                    $p = $_SESSION['partnerData'];
                                    $companyName = mysqli_real_escape_string($conn, $p['companyName']);
                                    $partnerType = mysqli_real_escape_string($conn, $p['partnerType']);
                                    $partnerAddress = mysqli_real_escape_string($conn, $p['partnerAddress']);
                                    $proofLink = mysqli_real_escape_string($conn, $p['proofLink']);
                                    $phoneNumber = mysqli_real_escape_string($conn, $p['phoneNumber']);
                                    $p = $_SESSION['partnerData'] ?? null;
                                    if (!$p) {
                                        $_SESSION['error'] = "Partner information missing from session.";
                                        header("Location: ../Pages/register.php");
                                        exit;
                                    }

                                    // Update user role to Partner (role = 2)
                                    $updateUser = "UPDATE users SET userRole = 2, phoneNumber = '$phoneNumber' WHERE userID = '$userID'";
                                    mysqli_query($conn, $updateUser);

                                    // Insert into partnerships table
                                    $insertPartner = "INSERT INTO partnerships(userID, partnerAddress, companyName, partnerType, businessEmail, documentLink)
                          VALUES ('$userID', '$partnerAddress', '$companyName', '$partnerType', '$email', '$proofLink')";
                                    mysqli_query($conn, $insertPartner);

                                    // Cleanup
                                    unset($_SESSION['partnerData']);

                                    $_SESSION['success'] = "Partner registered and verified successfully!";
                                    header("Location: ../Pages/register.php");
                                    exit;
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
                        }
                    } else {
                        $_SESSION['error'] = "Invalid OTP.";
                        header("Location: ../Pages/verify_email.php");
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Expired OTP.";
                    header("Location: ../Pages/verify_email.php");
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

    $storeData = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $storeData);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);;
        if ($data) {
            $stored_expiration = $data['OTP_expiration_at'];
            $firstName = $data['firstName'];
            $status = $data['userStatusID'];
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
                $updateOTP = "UPDATE users SET userOTP = '$newOtp', OTP_expiration_at = '$new_time' WHERE  email = '$email'";
                $result = mysqli_query($conn, $updateOTP);
                if ($result) {
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
                        $mail->addAddress($email, $firstName);

                        $message = "
                                        <h2 style='color: #333;'>Your New OTP Code</h2>
                                        <p>Hello,</p>
                                        <p>Your One-Time Password (OTP) for account verification is:</p>
                                        <h2 style='color:rgb(12, 6, 5); font-size: 24px; margin-left:120px;'> $newOtp </h2>
                                        <p>This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
                                        <p>If you did not request this code, please ignore this email.</p>
                                        <br>
                                        <p>Thank you,</p>
                                        <p><strong>Mamyr</strong></p>
                                        ";

                        $mail->isHTML(true);
                        $mail->Subject = 'Hello';
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
