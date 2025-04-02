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
            $userStatus = $data['userStatus'];
            $stored_expiration = $data['OTP_expiration_at'];
            date_default_timezone_set('Asia/Manila');
            $time_now = date('Y-m-d H:i:s');
            if ($storedOTP !== "") {
                if ($stored_expiration > $time_now) {
                    if ($storedOTP == $enteredOTP) {
                        $changeStatus = "UPDATE users SET userStatus = 'Verified', userOTP = NULL, OTP_expiration_at = NULL WHERE email = '$email'";
                        $result = mysqli_query($conn, $changeStatus);
                        if ($result) {
                            $_SESSION['sucess'] = "Verified successfully!";
                            header("Location: ../Pages/register.php");
                            exit;
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
        // $_SESSION['error'] = "Not found.";
        echo 'No Email found.';
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
        $data = mysqli_fetch_row($result);
        if ($data) {
            $stored_expiration = $data['OTP_expiration_at'];
            $firstName = $data['firstName'];
            $status = $data['userStatus'];
            $storedOTP = $data['userOTP'];
            date_default_timezone_set('Asia/Manila');
            $time_now = date('Y-m-d H:i:s');
            $time_left = strtotime($time_now) - strtotime($stored_expiration);
            if ($time_left < 300) {
                $minutes_left = ceil($time_remaining / 60);
                $_SESSION['time'] = "Wait for " . $minutes_left . " more minute(s) to request again.";
                header("Location: ../Pages/verify_email.php");
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
                                        <h2 style='color: #333;'>Your New OTP Code for Account Verification</h2>
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
